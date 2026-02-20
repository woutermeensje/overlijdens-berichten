<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
