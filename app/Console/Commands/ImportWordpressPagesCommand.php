<?php

namespace App\Console\Commands;

use App\Models\ContentPage;
use App\Models\MemorialNotice;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ImportWordpressPagesCommand extends Command
{
    protected $signature = 'ob:import-pages
        {--base-url=https://overlijdens-berichten.nl : WordPress basis URL}
        {--limit=0 : Maximaal aantal items per contenttype (0 = alles)}
        {--with-posts=1 : Importeer ook WordPress posts}
        {--with-obituaries=1 : Importeer ook ob_bericht custom post type}';

    protected $description = 'Importeer WordPress pagina\'s en berichten naar deze Laravel applicatie';

    public function handle(): int
    {
        $baseUrl = rtrim((string) $this->option('base-url'), '/');
        $limit = (int) $this->option('limit');
        $withPosts = (bool) (int) $this->option('with-posts');
        $withObituaries = (bool) (int) $this->option('with-obituaries');

        $this->info('WordPress import gestart vanaf: '.$baseUrl);

        $pages = $this->fetchWpCollection($baseUrl.'/wp-json/wp/v2/pages', [
            'per_page' => 100,
            'status' => 'publish',
        ], $limit);

        $this->info('Te importeren pagina\'s: '.count($pages));
        $importedPages = $this->importPages($pages);
        $this->info('Pagina\'s geimporteerd: '.$importedPages);

        $importedPosts = 0;
        $importedObituaries = 0;

        if ($withPosts || $withObituaries) {
            $importUser = User::query()->firstOrCreate(
                ['email' => 'import-bot@overlijdens-berichten.nl'],
                ['name' => 'WordPress Import Bot', 'password' => bcrypt(Str::random(24))]
            );

            // Reset eerdere import-bot berichten, daarna bouwen we de juiste set opnieuw op.
            MemorialNotice::query()->where('user_id', $importUser->id)->delete();

            if ($withPosts) {
                $posts = $this->fetchWpCollection($baseUrl.'/wp-json/wp/v2/posts', [
                    'per_page' => 100,
                    'status' => 'publish',
                    '_embed' => 1,
                ], $limit);

                $this->info('Te importeren posts: '.count($posts));
                $importedPosts = $this->importPostLikeItems($posts, $importUser, 'post');
                $this->info('Posts geimporteerd: '.$importedPosts);
            }

            if ($withObituaries) {
                $obItems = $this->fetchWpCollection($baseUrl.'/wp-json/wp/v2/ob_bericht', [
                    'per_page' => 100,
                    'status' => 'publish',
                    '_embed' => 1,
                ], $limit);

                $this->info('Te importeren ob_bericht items: '.count($obItems));
                $importedObituaries = $this->importPostLikeItems($obItems, $importUser, 'ob_bericht');
                $this->info('ob_bericht geimporteerd: '.$importedObituaries);
            }
        }

        $this->newLine();
        $this->info('Import voltooid.');
        $this->line('- Pagina\'s: '.$importedPages);
        $this->line('- Posts: '.$importedPosts);
        $this->line('- ob_bericht: '.$importedObituaries);

        return self::SUCCESS;
    }

    private function importPages(array $pages): int
    {
        $count = 0;
        $bar = $this->output->createProgressBar(count($pages));
        $bar->start();

        foreach ($pages as $page) {
            $payload = $this->normalizeWpContentToPagePayload($page);
            if ($payload === null) {
                $bar->advance();
                continue;
            }

            ContentPage::query()->updateOrCreate(
                ['path' => $payload['path']],
                [
                    'title' => $payload['title'],
                    'slug' => $payload['slug'],
                    'source_url' => $payload['source_url'],
                    'meta_description' => $payload['meta_description'],
                    'content_html' => $payload['content_html'],
                    'content_type' => 'page',
                    'is_active' => true,
                    'is_imported' => true,
                ]
            );

            $count++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        return $count;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetchWpCollection(string $endpoint, array $query, int $limit = 0): array
    {
        $all = [];
        $page = 1;
        $totalPages = 1;

        do {
            $response = Http::timeout(30)->acceptJson()->get($endpoint, array_merge($query, ['page' => $page]));

            if (!$response->ok()) {
                break;
            }

            $chunk = $response->json();
            if (!is_array($chunk)) {
                break;
            }

            foreach ($chunk as $item) {
                if (is_array($item)) {
                    $all[] = $item;
                    if ($limit > 0 && count($all) >= $limit) {
                        return array_slice($all, 0, $limit);
                    }
                }
            }

            $headerPages = (int) $response->header('X-WP-TotalPages', '1');
            $totalPages = max($headerPages, 1);
            $page++;
        } while ($page <= $totalPages);

        return $all;
    }

    private function normalizeWpContentToPagePayload(array $item): ?array
    {
        $sourceUrl = (string) ($item['link'] ?? '');
        $path = $this->normalizePathFromUrl($sourceUrl);
        if ($path === null) {
            return null;
        }

        $title = trim((string) data_get($item, 'title.rendered', ''));
        if ($title === '') {
            $title = Str::headline((string) ($item['slug'] ?? $path));
        }

        $contentHtml = (string) data_get($item, 'content.rendered', '');
        $metaDescription = trim((string) data_get($item, 'excerpt.rendered', ''));
        $metaDescription = Str::limit(strip_tags(html_entity_decode($metaDescription)), 190, '');

        return [
            'title' => strip_tags(html_entity_decode($title)),
            'slug' => Str::slug((string) ($item['slug'] ?? basename($path))),
            'path' => $path,
            'source_url' => $sourceUrl,
            'meta_description' => $metaDescription,
            'content_html' => $contentHtml,
        ];
    }

    private function importPostLikeItems(array $items, User $user, string $sourceType): int
    {
        $count = 0;
        $bar = $this->output->createProgressBar(count($items));
        $bar->start();

        foreach ($items as $item) {
            $slug = trim((string) ($item['slug'] ?? ''));
            $title = strip_tags(html_entity_decode((string) data_get($item, 'title.rendered', '')));
            if ($slug === '' || $title === '') {
                $bar->advance();
                continue;
            }

            $excerpt = strip_tags(html_entity_decode((string) data_get($item, 'excerpt.rendered', '')));
            $content = (string) data_get($item, 'content.rendered', '');
            $publishedAt = (string) ($item['date_gmt'] ?? $item['date'] ?? '');
            $featuredImage = (string) data_get($item, '_embedded.wp:featuredmedia.0.source_url', '');

            $isNameRecord = $sourceType === 'ob_bericht' || $this->looksLikePersonName($title);
            $contentType = $isNameRecord ? 'memorial-source' : 'blog';

            $pagePayload = $this->normalizeWpContentToPagePayload($item);
            if ($pagePayload !== null) {
                ContentPage::query()->updateOrCreate(
                    ['path' => $pagePayload['path']],
                    [
                        'title' => $pagePayload['title'],
                        'slug' => $pagePayload['slug'],
                        'source_url' => $pagePayload['source_url'],
                        'meta_description' => $pagePayload['meta_description'],
                        'content_html' => $pagePayload['content_html'],
                        'content_type' => $contentType,
                        'is_active' => true,
                        'is_imported' => true,
                    ]
                );
            }

            if ($isNameRecord) {
                $fullName = preg_replace('/\s+/', ' ', trim($title)) ?? $title;
                $parts = preg_split('/\s+/', $fullName) ?: [];
                $firstName = $parts[0] ?? null;
                $lastName = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : null;

                MemorialNotice::query()->updateOrCreate(
                    ['slug' => $slug],
                    [
                        'user_id' => $user->id,
                        'title' => $title,
                        'type' => $this->inferNoticeType($title, $excerpt, $sourceType),
                        'deceased_first_name' => $firstName,
                        'deceased_last_name' => $lastName,
                        'excerpt' => Str::limit(trim($excerpt), 600, ''),
                        'photo_url' => $featuredImage !== '' ? $featuredImage : null,
                        'content' => $content !== '' ? $content : $excerpt,
                        'status' => 'published',
                        'published_at' => $publishedAt !== '' ? Carbon::parse($publishedAt) : now(),
                    ]
                );

                $count++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        return $count;
    }

    private function looksLikePersonName(string $title): bool
    {
        $plain = trim($title);
        if ($plain === '' || mb_strlen($plain) > 80) {
            return false;
        }

        if (preg_match('/[0-9\?\!\:\;\(\)]/u', $plain)) {
            return false;
        }

        $words = preg_split('/\s+/u', $plain) ?: [];
        $wordCount = count($words);
        if ($wordCount < 2 || $wordCount > 5) {
            return false;
        }

        $blocked = [
            'wat', 'hoe', 'waarom', 'tips', 'top', 'privacyregels', 'rondom', 'in', 'nederland',
            'overlijdensbericht', 'overlijdensberichten', 'crematorium', 'begraafplaats', 'uitvaart',
        ];

        foreach ($words as $word) {
            $normalized = Str::lower(trim($word, " .,-'\""));
            if (in_array($normalized, $blocked, true)) {
                return false;
            }
        }

        return true;
    }

    private function inferNoticeType(string $title, string $excerpt, string $sourceType): string
    {
        $haystack = Str::lower($title.' '.$excerpt);

        if (Str::contains($haystack, 'familiebericht')) {
            return MemorialNotice::TYPE_FAMILIEBERICHT;
        }

        if (Str::contains($haystack, 'rouwadvertentie')) {
            return MemorialNotice::TYPE_ROUWADVERTENTIE;
        }

        if ($sourceType === 'ob_bericht') {
            return MemorialNotice::TYPE_OVERLIJDENSBERICHT;
        }

        return MemorialNotice::TYPE_OVERLIJDENSBERICHT;
    }

    private function normalizePathFromUrl(string $url): ?string
    {
        if ($url === '') {
            return null;
        }

        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
        if ($path === '') {
            return null;
        }

        return $path;
    }
}
