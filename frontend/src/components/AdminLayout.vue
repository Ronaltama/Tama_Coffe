<script setup>
import { RouterLink, RouterView } from 'vue-router';

// Menerima 'role' sebagai prop dari router
defineProps({
  role: {
    type: String,
    required: true
  }
});

// Fungsi untuk logout
const handleLogout = () => {
  console.log('Logout initiated...');
  // Contoh: router.push('/login');
};
</script>

<template>
  <div class="flex h-screen">

    <aside class="w-64 flex-shrink-0 bg-[#FBE8D9] flex flex-col">

      <div class="p-6 border-b border-orange-200">
        <h2 class="text-xl font-bold text-gray-800 text-center">
          Tama Coffee
        </h2>
      </div>

      <nav class="flex-1 p-4 space-y-2 overflow-y-auto">

        <template v-if="role === 'superadmin'">
          <RouterLink
            to="/superadmin/dashboard"
            class="nav-link"
            active-class="sidebar-active"
          >
            <span class="fa-fw">📊</span>
            <span>Dashboard</span>
          </RouterLink>
          <RouterLink
            to="/superadmin/users"
            class="nav-link"
            active-class="sidebar-active"
          >
            <span class="fa-fw">👥</span>
            <span>Users</span>
          </RouterLink>
          <RouterLink
            to="/superadmin/products"
            class="nav-link"
            active-class="sidebar-active"
          >
            <span class="fa-fw">📦</span>
            <span>Products</span>
          </RouterLink>
          <RouterLink
            to="/superadmin/history"
            class="nav-link"
            active-class="sidebar-active"
          >
            <span class="fa-fw">📜</span>
            <span>Order History</span>
          </RouterLink>
        </template>

        <template v-if="role === 'admin'">
          <RouterLink
            to="/admin/dashboard"
            class="nav-link"
            active-class="sidebar-active"
          >
            <span class="fa-fw">📊</span>
            <span>Dashboard</span>
          </RouterLink>
          <RouterLink
            to="/admin/add-order"
            class="nav-link"
            active-class="sidebar-active"
          >
            <span class="fa-fw">📜</span>
            <span>Order History</span>
          </RouterLink>
          <RouterLink
            to="/admin/add-order"
            class="nav-link"
            active-class="sidebar-active"
          >
            <span class="fa-fw">➕</span>
            <span>add order</span>
          </RouterLink>
          <RouterLink
            to="/admin/confirm-order"
            class="nav-link"
            active-class="sidebar-active"
          >
            <span class="fa-fw">✔️</span>
            <span>confirm-order</span>
          </RouterLink>
        </template>
      </nav>

      <div class="p-4 mt-auto border-t border-orange-200">
        <button
          @click="handleLogout"
          class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition"
        >
          <span class="fa-fw">➔</span>
          <span>Logout</span>
        </button>
      </div>
    </aside>

    <main class="flex-1 p-8 overflow-y-auto bg-[#FFF5EE]">
      <RouterView />
    </main>
  </div>
</template>

<style scoped>
/* Kelas styling dasar untuk semua link navigasi */
.nav-link {
  display: flex;
  align-items: center;
  gap: 0.75rem; /* 12px */
  padding: 0.75rem 1rem; /* 12px 16px */
  border-radius: 0.5rem; /* 8px */
  color: #374151; /* text-gray-700 */
  transition: background-color 0.15s ease-in-out;
  text-decoration: none; /* Menghilangkan garis bawah default */
}

/* Efek hover untuk link */
.nav-link:hover {
  background-color: #FED7AA; /* bg-orange-200 (sedikit lebih gelap dari nav) */
}

/* Kelas untuk link yang aktif (warna coklat tua) */
.sidebar-active {
  background-color: #8B4113; /* Coklat sedikit lebih cerah */
  color: white;
}

/* Memberi "fixed width" pada icon emoji */
.fa-fw {
  width: 1.25em; 
  text-align: center;
}
</style>