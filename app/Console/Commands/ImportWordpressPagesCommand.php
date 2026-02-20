<?php

namespace App\Console\Commands;

use App\Models\ContentPage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ImportWordpressPagesCommand extends Command
{
    protected $signature = 'ob:import-pages
        {--sitemap=https://overlijdens-berichten.nl/sitemap_index.xml : XML sitemap URL}
        {--limit=0 : Maximaal aantal pagina\'s (0 = alles)}';

    protected $description = 'Importeer pagina\'s van een WordPress-site naar content_pages';

    public function handle(): int
    {
        $sitemap = (string) $this->option('sitemap');
        $limit = (int) $this->option('limit');

        $this->info('Sitemap ophalen: '.$sitemap);
        $urls = $this->collectUrlsFromSitemap($sitemap);

        if ($urls === []) {
            $this->error('Geen URLs gevonden in sitemap.');

            return self::FAILURE;
        }

        $urls = array_values(array_filter($urls, fn (string $url) => $this->isLikelyPageUrl($url)));
        if ($limit > 0) {
            $urls = array_slice($urls, 0, $limit);
        }

        $this->info('Te importeren pagina\'s: '.count($urls));

        $bar = $this->output->createProgressBar(count($urls));
        $bar->start();

        $imported = 0;

        foreach ($urls as $url) {
            $payload = $this->fetchPagePayload($url);
            if ($payload === null) {
                $bar->advance();
                continue;
            }

            ContentPage::query()->updateOrCreate(
                ['path' => $payload['path']],
                [
                    'title' => $payload['title'],
                    'slug' => $payload['slug'],
                    'source_url' => $url,
                    'meta_description' => $payload['meta_description'],
                    'content_html' => $payload['content_html'],
                    'is_active' => true,
                    'is_imported' => true,
                ]
            );

            $imported++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Klaar. Geimporteerd: '.$imported);

        return self::SUCCESS;
    }

    private function collectUrlsFromSitemap(string $url): array
    {
        $response = Http::timeout(20)->get($url);
        if (!$response->ok()) {
            return [];
        }

        $xml = @simplexml_load_string($response->body());
        if (!$xml) {
            return [];
        }

        $urls = [];

        if (isset($xml->sitemap)) {
            foreach ($xml->sitemap as $sitemap) {
                $child = (string) $sitemap->loc;
                $urls = array_merge($urls, $this->collectUrlsFromSitemap($child));
            }
        }

        if (isset($xml->url)) {
            foreach ($xml->url as $entry) {
                $loc = (string) $entry->loc;
                if ($loc !== '') {
                    $urls[] = $loc;
                }
            }
        }

        return array_values(array_unique($urls));
    }

    private function isLikelyPageUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (!$host || !Str::contains($host, 'overlijdens-berichten.nl')) {
            return false;
        }

        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');

        if ($path === '' || Str::startsWith($path, ['wp-', 'feed'])) {
            return false;
        }

        $excludedPrefixes = [
            'tag/',
            'author/',
            'category/',
            'search/',
            'page/',
        ];

        foreach ($excludedPrefixes as $prefix) {
            if (Str::startsWith($path, $prefix)) {
                return false;
            }
        }

        return true;
    }

    private function fetchPagePayload(string $url): ?array
    {
        $response = Http::timeout(20)->get($url);
        if (!$response->ok()) {
            return null;
        }

        $html = $response->body();

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        $xpath = new \DOMXPath($dom);

        $titleNode = $xpath->query('//title')->item(0);
        $title = $titleNode ? trim($titleNode->textContent) : $url;

        $metaDescription = '';
        $metaNode = $xpath->query('//meta[@name="description"]')->item(0);
        if ($metaNode instanceof \DOMElement) {
            $metaDescription = trim((string) $metaNode->getAttribute('content'));
        }

        $mainNode = $xpath->query('//main')->item(0);
        $bodyNode = $xpath->query('//body')->item(0);
        $contentNode = $mainNode ?: $bodyNode;

        if (!$contentNode) {
            return null;
        }

        $contentHtml = '';
        foreach ($contentNode->childNodes as $child) {
            $contentHtml .= $dom->saveHTML($child);
        }

        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
        if ($path === '') {
            return null;
        }

        return [
            'title' => $title,
            'slug' => Str::slug(basename($path)),
            'path' => $path,
            'meta_description' => Str::limit($metaDescription, 190, ''),
            'content_html' => $contentHtml,
        ];
    }
}
