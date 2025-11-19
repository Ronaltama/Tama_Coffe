<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Table;
use App\Models\Order;

class DashboardSuperController extends Controller
{
    /**
     * GET /api/superadmin/dashboard
     */
    public function index()
    {
        return response()->json([
            'total_users'      => User::count(),
            'total_admins'     => User::whereHas('role', fn($q) => $q->where('name', 'admin'))->count(),
            'total_superadmin' => User::whereHas('role', fn($q) => $q->where('name', 'superadmin'))->count(),
            'total_products'   => Product::count(),
            'total_tables'     => Table::count(),
            'total_orders'     => Order::count(),
        ]);
    }
}
