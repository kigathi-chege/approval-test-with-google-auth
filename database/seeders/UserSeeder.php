<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = [
            'name' => 'Administrator',
            'password' => bcrypt('P@ssword1'),
            'type' => 'admin'
        ];

        \App\Models\User::firstOrCreate(['email' => 'admin@approval.local',], $admin);
    }
}