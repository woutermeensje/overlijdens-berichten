<?php

namespace App\Support;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

/**
 * Spamfilter op basis van tijd: het formulier krijgt een versleutelde tijdstempel mee.
 * Direct geposte (zonder token) of razendsnel ingevulde formulieren komen van bots.
 */
class FormTimer
{
    public const FIELD = 'form_token';

    /** Mensen hebben minimaal zoveel seconden nodig om een formulier in te vullen; bots niet. */
    private const MIN_FILL_SECONDS = 3;

    private const MAX_FORM_AGE_SECONDS = 6 * 60 * 60;

    public static function token(): string
    {
        return Crypt::encryptString((string) now()->timestamp);
    }

    public static function field(): string
    {
        return '<input type="hidden" name="'.self::FIELD.'" value="'.e(self::token()).'">';
    }

    /**
     * @return 'ok'|'bot'|'expired'
     */
    public static function check(Request $request): string
    {
        try {
            $age = now()->timestamp - (int) Crypt::decryptString((string) $request->input(self::FIELD));
        } catch (DecryptException) {
            return 'bot';
        }

        return match (true) {
            $age < self::MIN_FILL_SECONDS => 'bot',
            $age > self::MAX_FORM_AGE_SECONDS => 'expired',
            default => 'ok',
        };
    }
}
