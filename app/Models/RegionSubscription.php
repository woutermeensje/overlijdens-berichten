<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegionSubscription extends Model
{
    protected $fillable = ['email', 'region', 'token'];
}
