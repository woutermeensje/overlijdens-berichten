<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemorialNoticeCondolence extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'message',
    ];

    public function notice(): BelongsTo
    {
        return $this->belongsTo(MemorialNotice::class, 'memorial_notice_id');
    }
}
