<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'المدير',
                'password' => Hash::make('12345678'),
            ]
        );
    }
}
