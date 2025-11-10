<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Category;
use App\Models\SubCategory;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // === Seed data untuk categories ===
        $categories = [
            [
                'id' => 'CT001',
                'name' => 'Drink',
            ],
            [
                'id' => 'CT002',
                'name' => 'Food',
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['id' => $category['id']], $category);
        }

        // === Seed data untuk sub_categories ===
        $subCategories = [
            // Untuk kategori Drink (CT001)
            [
                'id' => 'SC001',
                'category_id' => 'CT001',
                'name' => 'Coffee',
            ],
            [
                'id' => 'SC002',
                'category_id' => 'CT001',
                'name' => 'Non-Coffee',
            ],

            // Untuk kategori Food (CT002)
            [
                'id' => 'SC003',
                'category_id' => 'CT002',
                'name' => 'Main Dish',
            ],
            [
                'id' => 'SC004',
                'category_id' => 'CT002',
                'name' => 'Snack',
            ],
            [
                'id' => 'SC005',
                'category_id' => 'CT002',
                'name' => 'Dessert',
            ],
        ];

        foreach ($subCategories as $subCategory) {
            SubCategory::updateOrCreate(['id' => $subCategory['id']], $subCategory);
        }
    }
}
