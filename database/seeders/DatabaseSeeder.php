<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'      => 'Admin',
                'password'  => Hash::make('password'),
                'role'      => 'admin',
                'avatar'    => 'https://ui-avatars.com/api/?background=465fff&color=fff&name=Admin',
                'is_active' => true,
            ],
        );

        $team = [
            ['name' => 'Marina Costa', 'email' => 'marina@example.com', 'role' => 'editor'],
            ['name' => 'Rafael Lima', 'email' => 'rafael@example.com', 'role' => 'editor'],
            ['name' => 'Júlia Santos', 'email' => 'julia@example.com', 'role' => 'viewer'],
        ];

        foreach ($team as $member) {
            User::firstOrCreate(
                ['email' => $member['email']],
                [
                    'name'      => $member['name'],
                    'password'  => Hash::make('password'),
                    'role'      => $member['role'],
                    'avatar'    => 'https://ui-avatars.com/api/?background=465fff&color=fff&name=' . urlencode($member['name']),
                    'is_active' => true,
                ],
            );
        }

        $this->call(DemoSeeder::class);
    }
}
