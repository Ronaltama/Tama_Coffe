<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SuperAdmin Dashboard') - Tama Coffee</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        .sidebar-active {
            background-color: #8B4513;
            color: white;
        }
    </style>
</head>
<body class="bg-gray-800">
    <div class="flex h-screen overflow-hidden">

        <!-- Sidebar -->
        <aside class="w-64 bg-orange-50 flex flex-col flex-shrink-0">
            <div class="p-6 border-b border-orange-200">
                <h2 class="text-xl font-bold text-gray-800">Admin Panel</h2>
            </div>

            <nav class="flex-1 p-4 space-y-2">
                <a href="{{ route('superadmin.dashboard') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-orange-100 transition {{ request()->routeIs('superadmin.dashboard') ? 'sidebar-active' : 'text-gray-700' }}">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('superadmin.orders.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-orange-100 transition {{ request()->routeIs('superadmin.orders.*') ? 'sidebar-active' : 'text-gray-700' }}">
                    <i class="fas fa-clock-rotate-left"></i>
                    <span>Order History</span>
                </a>

                <a href="{{ route('superadmin.users.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-orange-100 transition {{ request()->routeIs('superadmin.users.*') || request()->routeIs('superadmin.tables.*') ? 'sidebar-active' : 'text-gray-700' }}">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>

                <a href="{{ route('superadmin.products.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-orange-100 transition {{ request()->routeIs('superadmin.products.*') ? 'sidebar-active' : 'text-gray-700' }}">
                    <i class="fas fa-box"></i>
                    <span>Products</span>
                </a>
            </nav>

            <div class="p-4 border-t border-orange-200">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto bg-white">
            <div class="p-8">
                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>

    </div>

    @stack('scripts')
</body>
</html>
