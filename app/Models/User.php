<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'is_admin',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_admin' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    /**
     * E-mailadressen van alle admins, aangevuld met config('app.admin_email').
     *
     * @return \Illuminate\Support\Collection<int, string>
     */
    public static function adminEmails(): \Illuminate\Support\Collection
    {
        $emails = static::query()
            ->where('is_admin', true)
            ->pluck('email')
            ->push(config('app.admin_email'))
            ->filter(fn ($email): bool => is_string($email) && trim($email) !== '')
            ->map(fn (string $email): string => trim($email));

        return $emails->unique()->values();
    }

    public function memorialNotices(): HasMany
    {
        return $this->hasMany(MemorialNotice::class);
    }
}
