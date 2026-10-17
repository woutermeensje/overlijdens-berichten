<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MemorialNoticeView extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'memorial_notice_id',
        'date',
        'views',
    ];

    protected $casts = [
        'date' => 'date',
        'views' => 'integer',
    ];

    public function notice(): BelongsTo
    {
        return $this->belongsTo(MemorialNotice::class, 'memorial_notice_id');
    }

    // Telt een weergave van het bericht voor vandaag; bots en admins tellen niet mee.
    public static function record(MemorialNotice $notice, Request $request): void
    {
        if ($request->user()?->isAdmin() || self::isBot($request->userAgent())) {
            return;
        }

        static::query()->upsert(
            [[
                'memorial_notice_id' => $notice->id,
                'date' => now('Europe/Amsterdam')->toDateString(),
                'views' => 1,
            ]],
            ['memorial_notice_id', 'date'],
            ['views' => DB::raw('views + 1')],
        );
    }

    private static function isBot(?string $userAgent): bool
    {
        if ($userAgent === null || $userAgent === '') {
            return true;
        }

        return (bool) preg_match('/bot|crawl|spider|slurp|facebookexternalhit|preview|headless|curl|wget|python|scrapy/i', $userAgent);
    }
}
