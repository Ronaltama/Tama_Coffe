<script setup>
import { ref } from 'vue';

// Terima prop 'role' untuk menentukan menu mana yang ditampilkan
// Nilainya bisa 'admin' atau 'superadmin'
const props = defineProps({
  role: {
    type: String,
    default: 'admin' // Default ke admin jika tidak ada role
  }
});

// Anda bisa ganti ini dengan rute Vue Router nanti
const activeMenu = ref('dashboard');

// Fungsi logout (ganti dengan logika auth Anda)
const logout = () => {
  alert('Logging out...');
  // Contoh: window.location.href = '/logout';
};
</script>

<template>
  <div class="flex min-h-screen">
    <aside class="w-64 flex-shrink-0 bg-[#FBE8D9] flex flex-col">
      <div class="p-6 border-b border-orange-200">
        <h2 class="text-xl font-bold text-gray-800 text-center">
          Tama Coffee
        </h2>
      </div>

      <nav class="flex-1 p-4 space-y-2">
        <template v-if="role === 'superadmin'">
          <a
            href="#"
            @click="activeMenu = 'dashboard'"
            :class="activeMenu === 'dashboard' ? 'sidebar-active' : 'text-gray-700'"
            class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-orange-100 transition"
          >
            <span class="fa-fw">📊</span>
            <span>Dashboard</span>
          </a>
          <a
            href="#"
            @click="activeMenu = 'users'"
            :class="activeMenu === 'users' ? 'sidebar-active' : 'text-gray-700'"
            class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-orange-100 transition"
          >
            <span class="fa-fw">👥</span>
            <span>Users</span>
          </a>
          <a
            href="#"
            @click="activeMenu = 'products'"
            :class="activeMenu === 'products' ? 'sidebar-active' : 'text-gray-700'"
            class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-orange-100 transition"
          >
            <span class="fa-fw">📦</span>
            <span>Products</span>
          </a>
          <a
            href="#"
            @click="activeMenu = 'history'"
            :class="activeMenu === 'history' ? 'sidebar-active' : 'text-gray-700'"
            class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-orange-100 transition"
          >
            <span class="fa-fw">📜</span>
            <span>Order History</span>
          </a>
        </template>

        <template v-if="role === 'admin'">
          <a
            href="#"
            @click="activeMenu = 'dashboard'"
            :class="activeMenu === 'dashboard' ? 'sidebar-active' : 'text-gray-700'"
            class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-orange-100 transition"
          >
            <span class="fa-fw">📊</span>
            <span>Dashboard</span>
          </a>
          <a
            href="#"
            @click="activeMenu = 'add-order'"
            :class="activeMenu === 'add-order' ? 'sidebar-active' : 'text-gray-700'"
            class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-orange-100 transition"
          >
            <span class="fa-fw">➕</span>
            <span>Add Order</span>
          </a>
          <a
            href="#"
            @click="activeMenu = 'confirm-order'"
            :class="activeMenu === 'confirm-order' ? 'sidebar-active' : 'text-gray-700'"
            class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-orange-100 transition"
          >
            <span class="fa-fw">✔️</span>
            <span>Confirm Order</span>
          </a>
          <a
            href="#"
            @click="activeMenu = 'history'"
            :class="activeMenu === 'history' ? 'sidebar-active' : 'text-gray-700'"
            class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-orange-100 transition"
          >
            <span class="fa-fw">📜</span>
            <span>Order History</span>
          </a>
        </template>
      </nav>

      <div class="p-4 mt-auto border-t border-orange-200">
        <button
          @click="logout"
          class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition"
        >
          <span>➔</span>
          <span>Logout</span>
        </button>
      </div>
    </aside>

    <main class="flex-1 p-8 overflow-y-auto bg-[#FFF5EE]">
      <slot></slot>
    </main>
  </div>
</template>

<style scoped>
/* Anda bisa tambahkan style global di src/style.css */
.sidebar-active {
  background-color: #8B4513; /* Warna coklat tua sesuai gambar */
  color: white;
}
</style>