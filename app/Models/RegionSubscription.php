<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Een alert-aanmelding voor een stad (region gevuld), een hele provincie (alleen province)
 * of heel Nederland (beide leeg).
 */
class RegionSubscription extends Model
{
    public const PROVINCES = [
        'Drenthe', 'Flevoland', 'Fryslân', 'Gelderland', 'Groningen', 'Limburg',
        'Noord-Brabant', 'Noord-Holland', 'Overijssel', 'Utrecht', 'Zeeland', 'Zuid-Holland',
    ];

    protected $fillable = ['email', 'region', 'province', 'token'];

    public function areaLabel(): string
    {
        return match (true) {
            $this->region !== null => $this->region,
            $this->province !== null => 'provincie '.$this->province,
            default => 'heel Nederland',
        };
    }
}
