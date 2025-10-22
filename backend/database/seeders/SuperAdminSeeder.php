<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Category;
use App\Models\Product;
use App\Models\Table;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create SuperAdmin User
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'email' => 'superadmin@tamacoffee.com',
            'password' => Hash::make('password'),
        ]);

        Role::create([
            'user_id' => $superAdmin->id,
            'name' => 'superadmin'
        ]);

        // Create Admin Users
        $admin1 = User::create([
            'name' => 'Rijal Cilacap',
            'username' => 'rijal',
            'email' => 'rijal@yahoo.com',
            'password' => Hash::make('password'),
        ]);

        Role::create([
            'user_id' => $admin1->id,
            'name' => 'admin'
        ]);

        $admin2 = User::create([
            'name' => 'Sahroni',
            'username' => 'sahroni',
            'email' => 'sahroni@yahoo.com',
            'password' => Hash::make('password'),
        ]);

        Role::create([
            'user_id' => $admin2->id,
            'name' => 'admin'
        ]);

        // Create Categories
        $coffeeCategory = Category::create(['name' => 'Coffee']);
        $nonCoffeeCategory = Category::create(['name' => 'Non-Coffee']);
        $snackCategory = Category::create(['name' => 'Snack']);
        $mainCourseCategory = Category::create(['name' => 'Main Course']);
        $dessertCategory = Category::create(['name' => 'Dessert']);

        // Create Drink Products
        Product::create([
            'category_id' => $coffeeCategory->id,
            'name' => 'Classic Espresso',
            'description' => 'Rich and bold espresso shot',
            'price' => 25000,
            'status' => 'available'
        ]);

        Product::create([
            'category_id' => $coffeeCategory->id,
            'name' => 'Caramel Machiato',
            'description' => 'Sweet caramel with espresso',
            'price' => 27000,
            'status' => 'available'
        ]);

        Product::create([
            'category_id' => $nonCoffeeCategory->id,
            'name' => 'Chocolatos',
            'description' => 'Rich chocolate drink',
            'price' => 17000,
            'status' => 'available'
        ]);

        Product::create([
            'category_id' => $nonCoffeeCategory->id,
            'name' => 'Lemon Tea',
            'description' => 'Refreshing lemon tea',
            'price' => 10000,
            'status' => 'available'
        ]);

        // Create Food Products
        Product::create([
            'category_id' => $snackCategory->id,
            'name' => 'French Fries',
            'description' => 'Crispy golden french fries',
            'price' => 15000,
            'status' => 'available'
        ]);

        Product::create([
            'category_id' => $snackCategory->id,
            'name' => 'Tahu Crispy',
            'description' => 'Crispy fried tofu',
            'price' => 10000,
            'status' => 'available'
        ]);

        Product::create([
            'category_id' => $mainCourseCategory->id,
            'name' => 'Spaghetti Bolognese',
            'description' => 'Italian spaghetti with meat sauce',
            'price' => 35000,
            'status' => 'available'
        ]);

        Product::create([
            'category_id' => $dessertCategory->id,
            'name' => 'Pancake Strawberry',
            'description' => 'Fluffy pancakes with fresh strawberries',
            'price' => 28000,
            'status' => 'available'
        ]);

        // Create Tables
        Table::create([
            'table_number' => 'T-001',
            'capacity' => 4,
            'status' => 'available'
        ]);

        Table::create([
            'table_number' => 'T-002',
            'capacity' => 6,
            'status' => 'available'
        ]);

        Table::create([
            'table_number' => 'T-003',
            'capacity' => 2,
            'status' => 'available'
        ]);

        // Create Sample Orders
        $order1 = Order::create([
            'user_id' => $admin1->id,
            'table_id' => 1,
            'customer_name' => 'Sophia Clark',
            'customer_phone' => '081234567890',
            'total_price' => 50000,
            'status' => 'completed',
            'created_at' => now()->subDays(1)
        ]);

        OrderDetail::create([
            'order_id' => $order1->id,
            'product_id' => 1,
            'quantity' => 2,
            'unit_price' => 25000,
            'subtotal' => 50000
        ]);
    }
}

