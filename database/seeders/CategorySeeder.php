<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'مواد غذائية',
            'مشروبات',
            'ألبان ومجمدات',
            'منظفات',
            'عناية شخصية',
            'أخرى',
        ];

        foreach ($categories as $name) {
            Category::query()->firstOrCreate(['name' => $name]);
        }
    }
}
