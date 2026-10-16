<?php

namespace App\Models;

use App\Services\Geocoder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MemorialNotice extends Model
{
    use HasFactory;

    public const TYPE_OVERLIJDENSBERICHT = 'overlijdensbericht';
    public const TYPE_FAMILIEBERICHT = 'familiebericht';
    public const TYPE_ROUWADVERTENTIE = 'rouwadvertentie';

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
        'status',
        'published_at',
    ];

    protected $casts = [
        'born_date' => 'date',
        'died_date' => 'date',
        'published_at' => 'datetime',
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
    public static function typeLabels(): array
    {
        return [
            self::TYPE_OVERLIJDENSBERICHT => 'Overlijdensbericht',
            self::TYPE_FAMILIEBERICHT => 'Familiebericht',
            self::TYPE_ROUWADVERTENTIE => 'Rouwadvertentie',
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

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
