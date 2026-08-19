<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KeySeeder extends Seeder
{
    public function run(): void
    {
        $keys = [
            ['name' => 'Phone',        'key' => 'phone',        'is_active' => true,  'icon' => 'phone'],
            ['name' => 'Email',        'key' => 'email',        'is_active' => true,  'icon' => 'mail'],
            ['name' => 'WhatsApp',     'key' => 'whatsapp',     'is_active' => true,  'icon' => 'whatsapp'],
            ['name' => 'Address',      'key' => 'address',      'is_active' => true,  'icon' => 'map-pin'],
            ['name' => 'Website',      'key' => 'website',      'is_active' => true,  'icon' => 'globe'],
            ['name' => 'Facebook',     'key' => 'facebook',     'is_active' => true,  'icon' => 'facebook'],
            ['name' => 'Instagram',    'key' => 'instagram',    'is_active' => true,  'icon' => 'instagram'],
            ['name' => 'Twitter / X',  'key' => 'twitter',      'is_active' => true,  'icon' => 'twitter'],
            ['name' => 'LinkedIn',     'key' => 'linkedin',     'is_active' => true,  'icon' => 'linkedin'],
            ['name' => 'YouTube',      'key' => 'youtube',      'is_active' => false, 'icon' => 'youtube'],
            ['name' => 'Fax',          'key' => 'fax',          'is_active' => false, 'icon' => 'printer'],
            ['name' => 'P.O. Box',     'key' => 'po_box',       'is_active' => false, 'icon' => 'inbox'],
        ];

        foreach ($keys as $key) {
            DB::table('keys')->updateOrInsert(
                ['key' => $key['key']],
                array_merge($key, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
