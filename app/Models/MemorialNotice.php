<?php

namespace App\Models;

use App\Services\Geocoder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MemorialNotice extends Model
{
    use HasFactory;

    public const TYPE_OVERLIJDENSBERICHT = 'overlijdensbericht';
    public const TYPE_FAMILIEBERICHT = 'familiebericht';
    public const TYPE_ROUWADVERTENTIE = 'rouwadvertentie';
    public const TYPE_IN_MEMORIAM = 'in_memoriam';

    // Betaalde optie in de wizard: een overlijdensbericht dat bovenaan de lijsten staat.
    public const FEATURED_OPTION = 'uitgelicht';
    public const FEATURED_PRICE_EXCL_VAT = 175.00;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'type',
        'deceased_first_name',
        'deceased_last_name',
        'excerpt',
        'photo_url',
        'content',
        'province',
        'city',
        'latitude',
        'longitude',
        'born_date',
        'died_date',
        'funeral_company_name',
        'funeral_company_contact',
        'funeral_company_phone',
        'funeral_company_email',
        'funeral_company_url',
        'next_of_kin_first_name',
        'next_of_kin_last_name',
        'next_of_kin_email',
        'condolence_url',
        'placer_name',
        'placer_email',
        'placer_phone',
        'status',
        'is_featured',
        'billing_details',
        'published_at',
    ];

    protected $casts = [
        'born_date' => 'date',
        'died_date' => 'date',
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'billing_details' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    protected static function booted(): void
    {
        static::saving(function (MemorialNotice $notice): void {
            $locationChanged = $notice->isDirty(['city', 'province']);
            $missingCoordinates = $notice->latitude === null && ! $notice->isDirty(['latitude', 'longitude']);

            if (! $locationChanged && ! $missingCoordinates) {
                return;
            }

            $notice->geocode();
        });
    }

    /**
     * @return array<string, string>
     */
    /**
     * Korte samenvatting voor in de berichtenlijst: de eerste 12 woorden van de berichttekst,
     * of een vaste zin als er geen tekst is.
     */
    public function listExcerpt(int $words = 12): string
    {
        $text = Str::squish(html_entity_decode(strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], ' ', (string) $this->content))));

        if ($text !== '') {
            return Str::words($text, $words, '…');
        }

        $type = Str::lower(self::typeLabels()[$this->type] ?? 'bericht');
        $article = $this->type === self::TYPE_ROUWADVERTENTIE ? 'de' : 'het';
        $name = trim(($this->deceased_first_name ?? '').' '.($this->deceased_last_name ?? '')) ?: $this->title;

        return 'Bekijk '.$article.' '.$type.' van '.$name.'.';
    }

    public static function typeLabels(): array
    {
        return [
            self::TYPE_OVERLIJDENSBERICHT => 'Overlijdensbericht',
            self::TYPE_FAMILIEBERICHT => 'Familiebericht',
            self::TYPE_ROUWADVERTENTIE => 'Rouwadvertentie',
            self::TYPE_IN_MEMORIAM => 'In memoriam',
        ];
    }

    public function geocode(): void
    {
        $city = trim((string) $this->city);
        $match = $city !== ''
            ? app(Geocoder::class)->locate(trim($city.' '.$this->province))
            : null;

        $this->latitude = $match['lat'] ?? null;
        $this->longitude = $match['lng'] ?? null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function condolences(): HasMany
    {
        return $this->hasMany(MemorialNoticeCondolence::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(MemorialNoticeView::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
