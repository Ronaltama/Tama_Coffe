<script setup>
import axios from 'axios'
import { RouterLink, RouterView, useRouter } from 'vue-router'
import { ref } from 'vue'
import Swal from 'sweetalert2' // 💡 Import SweetAlert2
// Ganti path logo sesuai dengan struktur proyek Anda
import logo from '../assets/img/logo.png'

// router instance
const router = useRouter()

// menerima prop role
defineProps({
  role: {
    type: String,
    required: true
  }
})

// 💡 Fungsi handleLogout yang diupdate dengan SweetAlert2
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
      // Panggil API Logout (menggunakan try-catch untuk memastikan data lokal terhapus)
      await axios.post('http://127.0.0.1:8000/api/logout');

      // 🟢 Notifikasi Sukses
      await Swal.fire({
        icon: "success",
        title: "Berhasil Logout!",
        text: "Sesi Anda telah berakhir. Sampai jumpa kembali.",
        showConfirmButton: false,
        timer: 1500,
      });

    } catch (error) {
      // Tangani error, tapi tetap paksa logout di sisi klien
      console.error("Error saat mencoba logout dari API:", error);

      // ⚠️ Tampilkan peringatan jika terjadi masalah API (misalnya token sudah kadaluarsa)
      await Swal.fire({
        icon: "warning",
        title: "Sesi Habis!",
        text: "Terjadi masalah koneksi atau sesi sudah kadaluarsa. Anda akan dipaksa logout.",
        confirmButtonText: "OK",
      });
    } finally {
      // Blok finally wajib untuk memastikan penghapusan token di klien
      localStorage.removeItem('user'); // Hapus data user juga
      localStorage.removeItem('token');
      delete axios.defaults.headers.common["Authorization"];

      // Redirect ke login
      router.push('/login');
    }
  }
};
</script>


<template>
  <div class="flex h-screen">

    <!-- SIDEBAR -->
    <aside class="w-64 flex-shrink-0 bg-[#FBE8D9] flex flex-col">

      <!-- HEADER LOGO -->
      <div class="py-5 px-6 border-b border-orange-200 flex items-center justify-center gap-3">
        <img :src="logo" alt="Logo Tama Coffee" class="w-20 h-20 object-contain" />
      </div>

      <!-- NAVIGATION -->
      <nav class="flex-1 p-4 space-y-2 overflow-y-auto">

        <!-- SUPERADMIN MENU -->
        <template v-if="role === 'superadmin'">
          <RouterLink to="/superadmin/dashboard" class="nav-link" active-class="sidebar-active">
            <i class="fas fa-chart-line fa-fw"></i>
            <span>Dashboard</span>
          </RouterLink>

          <RouterLink to="/superadmin/users" class="nav-link" active-class="sidebar-active">
            <i class="fas fa-users fa-fw"></i>
            <span>Users</span>
          </RouterLink>

          <RouterLink to="/superadmin/products" class="nav-link" active-class="sidebar-active">
            <i class="fas fa-box fa-fw"></i>
            <span>Products</span>
          </RouterLink>

          <!-- ✅ MENU TABLES BARU -->
          <RouterLink to="/superadmin/tables" class="nav-link" active-class="sidebar-active">
            <i class="fas fa-table fa-fw"></i>
            <span>Tables</span>
          </RouterLink>

          <RouterLink to="/superadmin/history" class="nav-link" active-class="sidebar-active">
            <i class="fas fa-receipt fa-fw"></i>
            <span>Order History</span>
          </RouterLink>
        </template>

        <!-- ADMIN MENU -->
        <template v-if="role === 'admin'">
          <RouterLink to="/admin/dashboard" class="nav-link" active-class="sidebar-active">
            <i class="fas fa-chart-line fa-fw"></i>
            <span>Dashboard</span>
          </RouterLink>

          <RouterLink to="/admin/history" class="nav-link" active-class="sidebar-active">
            <i class="fas fa-receipt fa-fw"></i>
            <span>Order History</span>
          </RouterLink>

          <RouterLink to="/admin/add-order" class="nav-link" active-class="sidebar-active">
            <i class="fas fa-plus-circle fa-fw"></i>
            <span>Add Order</span>
          </RouterLink>

          <RouterLink to="/admin/confirm-order" class="nav-link" active-class="sidebar-active">
            <i class="fas fa-check-circle fa-fw"></i>
            <span>Confirm Order</span>
          </RouterLink>

          <RouterLink to="/admin/confirm-order" class="nav-link" active-class="sidebar-active">
            <i class="fas fa-check-circle fa-fw"></i>
            <span>Manage Reservation</span>
          </RouterLink>
        </template>
      </nav>


      <!-- LOGOUT -->
      <div class="p-4 mt-auto border-t border-orange-200">
        <button @click="handleLogout"
          class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
          <i class="fas fa-sign-out-alt fa-fw"></i>
          <span>Log out</span>
        </button>
      </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 p-8 overflow-y-auto bg-[#FFF5EE]">
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
  background-color: #FED7AA;
  color: #000;
}

.sidebar-active {
  background-color: #8B4113;
  color: white;
}

.fa-fw {
  width: 1.25em;
  text-align: center;
}
</style>