<script setup>
import axios from "axios";
import { RouterLink, RouterView, useRouter } from "vue-router";
import { ref } from "vue";
import Swal from "sweetalert2";
import logo from "../assets/img/logo.png";

const router = useRouter();
const isSidebarOpen = ref(false);

defineProps({
  role: {
    type: String,
    required: true,
  },
});

const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value;
};

const closeSidebar = () => {
  isSidebarOpen.value = false;
};

const handleLogout = async () => {
  const result = await Swal.fire({
    title: "Yakin Ingin Keluar?",
    text: "Anda akan mengakhiri sesi dan diarahkan kembali ke halaman login.",
    icon: "question",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Ya, Logout!",
    cancelButtonText: "Batal",
  });

  if (result.isConfirmed) {
    try {
      await axios.post("http://127.0.0.1:8000/api/logout");
      await Swal.fire({
        icon: "success",
        title: "Berhasil Logout!",
        text: "Sesi Anda telah berakhir. Sampai jumpa kembali.",
        showConfirmButton: false,
        timer: 1500,
      });
    } catch (error) {
      console.error("Error saat mencoba logout dari API:", error);
      await Swal.fire({
        icon: "warning",
        title: "Sesi Habis!",
        text: "Terjadi masalah koneksi atau sesi sudah kadaluarsa. Anda akan dipaksa logout.",
        confirmButtonText: "OK",
      });
    } finally {
      localStorage.removeItem("user");
      localStorage.removeItem("token");
      delete axios.defaults.headers.common["Authorization"];
      router.push("/login");
    }
  }
};
</script>

<template>
  <div class="flex h-screen relative">
    <!-- MOBILE HEADER (HAMBURGER) -->
    <div class="md:hidden fixed top-0 left-0 right-0 bg-[#FBE8D9] p-4 flex items-center justify-between z-30 border-b border-orange-200 shadow-sm">
      <div class="flex items-center gap-3">
        <img :src="logo" alt="Logo" class="w-8 h-8 object-contain" />
        <span class="font-bold text-color-brown">Tama Coffee</span>
      </div>
      <button @click="toggleSidebar" class="text-color-brown p-2 rounded-lg hover:bg-orange-200 transition">
        <i class="fas fa-bars fa-lg"></i>
      </button>
    </div>

    <!-- OVERLAY (MOBILE ONLY) -->
    <div 
      v-if="isSidebarOpen" 
      @click="closeSidebar"
      class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden transition-opacity"
    ></div>

    <!-- SIDEBAR -->
    <aside 
      :class="[
        'w-64 flex-shrink-0 bg-[#FBE8D9] flex flex-col transition-transform duration-300 ease-in-out z-50',
        'fixed inset-y-0 left-0 md:relative md:translate-x-0',
        isSidebarOpen ? 'translate-x-0' : '-translate-x-full'
      ]"
    >
      <!-- HEADER LOGO (DESKTOP) -->
      <div
        class="py-5 px-6 border-b border-orange-200 flex items-center justify-center gap-3 hidden md:flex"
      >
        <img
          :src="logo"
          alt="Logo Tama Coffee"
          class="w-20 h-20 object-contain"
        />
      </div>

      <!-- MOBILE SIDEBAR HEADER (CLOSE BUTTON) -->
      <div class="md:hidden p-4 flex items-center justify-between border-b border-orange-200">
        <span class="font-bold text-lg text-color-brown">Menu</span>
        <button @click="closeSidebar" class="text-gray-500 hover:text-gray-700">
          <i class="fas fa-times fa-lg"></i>
        </button>
      </div>

      <!-- NAVIGATION -->
      <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
        <!-- SUPERADMIN MENU -->
        <template v-if="role === 'superadmin'">
          <RouterLink
            to="/superadmin/dashboard"
            class="nav-link"
            active-class="sidebar-active"
            @click="closeSidebar"
          >
            <i class="fas fa-chart-line fa-fw"></i>
            <span>Dashboard</span>
          </RouterLink>

          <RouterLink
            to="/superadmin/users"
            class="nav-link"
            active-class="sidebar-active"
            @click="closeSidebar"
          >
            <i class="fas fa-users fa-fw"></i>
            <span>Users</span>
          </RouterLink>

          <RouterLink
            to="/superadmin/products"
            class="nav-link"
            active-class="sidebar-active"
            @click="closeSidebar"
          >
            <i class="fas fa-box fa-fw"></i>
            <span>Products</span>
          </RouterLink>

          <RouterLink
            to="/superadmin/tables"
            class="nav-link"
            active-class="sidebar-active"
            @click="closeSidebar"
          >
            <i class="fas fa-table fa-fw"></i>
            <span>Tables</span>
          </RouterLink>

          <RouterLink
            to="/superadmin/history"
            class="nav-link"
            active-class="sidebar-active"
            @click="closeSidebar"
          >
            <i class="fas fa-receipt fa-fw"></i>
            <span>Order History</span>
          </RouterLink>
        </template>

        <!-- ADMIN MENU -->
        <template v-if="role === 'admin'">
          <RouterLink
            to="/admin/dashboard"
            class="nav-link"
            active-class="sidebar-active"
            @click="closeSidebar"
          >
            <i class="fas fa-chart-line fa-fw"></i>
            <span>Dashboard</span>
          </RouterLink>

          <RouterLink
            to="/admin/history"
            class="nav-link"
            active-class="sidebar-active"
            @click="closeSidebar"
          >
            <i class="fas fa-receipt fa-fw"></i>
            <span>Order History</span>
          </RouterLink>

          <RouterLink
            to="/admin/add-order"
            class="nav-link"
            active-class="sidebar-active"
            @click="closeSidebar"
          >
            <i class="fas fa-plus-circle fa-fw"></i>
            <span>Add Order</span>
          </RouterLink>

          <RouterLink
            to="/admin/confirm-order"
            class="nav-link"
            active-class="sidebar-active"
            @click="closeSidebar"
          >
            <i class="fas fa-check-circle fa-fw"></i>
            <span>Confirm Order</span>
          </RouterLink>

          <RouterLink
            to="/admin/reservations"
            class="nav-link"
            active-class="sidebar-active"
            @click="closeSidebar"
          >
            <i class="fas fa-calendar-check fa-fw"></i>
            <span>Reservasi</span>
          </RouterLink>
        </template>
      </nav>

      <!-- LOGOUT -->
      <div class="p-4 mt-auto border-t border-orange-200">
        <button
          @click="handleLogout"
          class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition"
        >
          <i class="fas fa-sign-out-alt fa-fw"></i>
          <span>Log out</span>
        </button>
      </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 p-8 overflow-y-auto bg-[#FFF5EE] pt-20 md:pt-8">
      <RouterView />
    </main>
  </div>
</template>

<style scoped>
.nav-link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
  color: #374151;
  transition: background-color 0.15s ease-in-out, color 0.15s ease-in-out;
  text-decoration: none;
}

.nav-link:hover {
  background-color: #fed7aa;
  color: #000;
}

.sidebar-active {
  background-color: #8b4113;
  color: white;
}

.fa-fw {
  width: 1.25em;
  text-align: center;
}
</style>
