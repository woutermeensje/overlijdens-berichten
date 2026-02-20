<?php

namespace Database\Seeders;

use App\Models\MemorialNotice;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoMemorialSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@overlijdens-berichten.nl'],
            ['name' => 'Demo Beheer', 'password' => bcrypt('demo12345')]
        );

        $items = [
            [
                'title' => 'In memoriam Anna de Vries',
                'deceased_first_name' => 'Anna',
                'deceased_last_name' => 'de Vries',
                'type' => 'overlijdensbericht',
                'city' => 'Utrecht',
                'province' => 'Utrecht',
                'born_date' => '1948-03-12',
                'died_date' => '2026-02-11',
                'photo_url' => 'https://images.unsplash.com/photo-1542204625-de293a0f8ff6?auto=format&fit=crop&w=300&q=80',
            ],
            [
                'title' => 'Familiebericht voor Karel Jansen',
                'deceased_first_name' => 'Karel',
                'deceased_last_name' => 'Jansen',
                'type' => 'familiebericht',
                'city' => 'Rotterdam',
                'province' => 'Zuid-Holland',
                'born_date' => '1955-07-02',
                'died_date' => '2026-02-14',
                'photo_url' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=300&q=80',
            ],
            [
                'title' => 'Rouwadvertentie Maria van Dijk',
                'deceased_first_name' => 'Maria',
                'deceased_last_name' => 'van Dijk',
                'type' => 'rouwadvertentie',
                'city' => 'Eindhoven',
                'province' => 'Noord-Brabant',
                'born_date' => '1939-11-21',
                'died_date' => '2026-02-09',
                'photo_url' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=300&q=80',
            ],
        ];

        foreach ($items as $item) {
            MemorialNotice::updateOrCreate(
                ['slug' => Str::slug($item['title'])],
                array_merge($item, [
                    'user_id' => $user->id,
                    'excerpt' => 'Testbericht voor ontwerpcontrole.',
                    'content' => 'Dit is een testbericht om de kaartweergave op de homepage te controleren.',
                    'status' => 'published',
                    'published_at' => now(),
                ])
            );
        }
    }
}
