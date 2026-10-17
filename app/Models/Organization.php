<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Een organisatie uit de uitvaartbranche, aangemeld via /organisatie-toevoegen.
 */
class Organization extends Model
{
    public const STATUS_PUBLISHED = 'published';

    public const CATEGORIES = [
        'uitvaartonderneming' => 'Uitvaartonderneming',
        'uitvaartcentrum' => 'Uitvaartcentrum of rouwcentrum',
        'crematorium' => 'Crematorium',
        'begraafplaats' => 'Begraafplaats of natuurbegraafplaats',
        'steenhouwer' => 'Steenhouwer of grafmonumenten',
        'bloemist' => 'Bloemist (rouwbloemwerk)',
        'drukwerk' => 'Rouwdrukwerk en rouwkaarten',
        'verzekeraar' => 'Uitvaartverzekeraar',
        'notaris' => 'Notaris of erfrecht',
        'rouwbegeleiding' => 'Rouwbegeleiding of rouwtherapie',
        'spreker' => 'Uitvaartspreker of ritueelbegeleider',
        'muziek' => 'Muziek bij de uitvaart',
        'catering' => 'Catering en condoleance',
        'vervoer' => 'Rouwvervoer',
        'gedenksieraden' => 'Gedenksieraden en urnen',
        'overig' => 'Overig',
    ];

    protected $fillable = [
        'name', 'category', 'email', 'phone', 'website', 'address',
        'city', 'province', 'place_id', 'about', 'status',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /** Website zonder https:// en afsluitende slash, om te tonen. */
    public function websiteLabel(): ?string
    {
        return $this->website ? rtrim(preg_replace('#^https?://(www\.)?#i', '', $this->website), '/') : null;
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }
}
