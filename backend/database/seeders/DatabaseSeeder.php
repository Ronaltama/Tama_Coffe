<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Category;
use App\Models\Role;
use App\Models\SubCategory;
use App\Models\User;
use App\Models\Product;
use App\Models\Table;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        // === Seed data untuk role ===
        $roles = [
            [
                'id' => 'RL001',
                'name' => 'superadmin',
            ],
            [
                'id' => 'RL002',
                'name' => 'admin',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['id' => $role['id']], $role);
        }

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

        // === Seed User Superadmin ===
        User::updateOrCreate(
            ['email' => 'superadmin@tama.com'], // kondisi unik
            [
                'id' => 'US001',
                'role_id' => 'RL001',
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'email' => 'superadmin@tama.com',
                'password' => Hash::make('password123'),
            ]
        );

        // === Seed User Admin ===
        User::updateOrCreate(
            ['email' => 'admin@tama.com'],
            [
                'id' => 'US002',
                'role_id' => 'RL002',
                'name' => 'Admin',
                'username' => 'admin',
                'email' => 'admin@tama.com',
                'password' => Hash::make('password123'),
            ]
        );

        // // === Seed Table ===
        // Table::updateOrCreate(
        //     ['id' => 'TB001'],
        //     [
        //         'id' => 'TB001',
        //         'table_number' => 'A1',
        //         'capacity' => 4,
        //         'type' => 'indoor',
        //         'status' => 'available',
        //     ]
        // );

        // // === Seed Product ===
        // Product::updateOrCreate(
        //     ['id' => 'PR001'],
        //     [
        //         'id' => 'PR001',
        //         'category_id' => 'CT001', // Drink category
        //         'name' => 'Espresso',
        //         'description' => 'Classic Italian espresso',
        //         'price' => 25000,
        //         'status' => 'available',
        //         'image' => null,
        //     ]
        // );
    }
}
