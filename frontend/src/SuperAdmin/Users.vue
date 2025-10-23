<script setup>
import { ref } from 'vue';

// --- DATA CONTOH ---
// Ganti ini dengan data dari API Anda
const users = ref([
  { id: 1, name: 'Rijal Cilacap', email: 'rijal@yahoo.com', role: 'Admin', status: 'Active' },
  { id: 2, name: 'Sahroni', email: 'sahroni@yahoo.com', role: 'Admin', status: 'Active' },
  { id: 3, name: 'Super Tama', email: 'super@tama.com', role: 'Super Admin', status: 'Active' },
  { id: 4, name: 'User Nonaktif', email: 'nonaktif@example.com', role: 'Admin', status: 'Inactive' },
]);
// --- AKHIR DATA CONTOH ---

// Helper untuk mendapatkan kelas CSS berdasarkan Role
const getRoleClasses = (role) => {
  if (role === 'Super Admin') {
    return 'bg-blue-100 text-blue-700';
  }
  if (role === 'Admin') {
    return 'bg-pink-100 text-pink-700';
  }
  return 'bg-gray-100 text-gray-700';
};

// Helper untuk mendapatkan kelas CSS berdasarkan Status
const getStatusClasses = (status) => {
  if (status === 'Active') {
    return 'bg-green-100 text-green-700';
  }
  if (status === 'Inactive') {
    return 'bg-gray-100 text-gray-700';
  }
  return 'bg-gray-100 text-gray-700';
};

// Fungsi untuk tombol (ganti dengan logika Anda)
const addUser = () => {
  alert('Membuka halaman tambah user...');
  // Contoh: router.push('/superadmin/users/create');
};

const editUser = (id) => {
  alert(`Mengedit user dengan ID: ${id}`);
  // Contoh: router.push(`/superadmin/users/${id}/edit`);
};

const deleteUser = (id) => {
  if (confirm(`Apakah Anda yakin ingin menghapus user ID: ${id}?`)) {
    alert(`Menghapus user dengan ID: ${id}`);
    // Logika panggil API delete
  }
};
</script>

<template>
  <div class="space-y-8">

    <div class="flex items-center justify-between">
      <h1 class="text-3xl font-bold text-gray-900">User Management</h1>
      <button
        @click="addUser"
        class="px-5 py-2.5 bg-amber-700 text-white rounded-lg hover:bg-amber-800 transition shadow-sm"
      >
        + Add User
      </button>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
      <div class="overflow-x-auto">
        <table class="w-full min-w-[640px]">
          <thead>
            <tr class="border-b border-gray-200 text-left text-sm font-semibold text-gray-700">
              <th class="py-3 px-4">Name</th>
              <th class="py-3 px-4">Email</th>
              <th class="py-3 px-4">Role</th>
              <th class="py-3 px-4">Status</th>
              <th class="py-3 px-4">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <template v-if="users.length > 0">
              <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
                
                <td class="py-3 px-4 text-sm text-gray-900 font-medium">
                  {{ user.name }}
                </td>

                <td class="py-3 px-4 text-sm text-gray-700">
                  {{ user.email }}
                </td>
                
                <td class="py-3 px-4">
                  <span
                    class="inline-block px-3 py-1 text-xs rounded-full font-medium"
                    :class="getRoleClasses(user.role)"
                  >
                    {{ user.role }}
                  </span>
                </td>

                <td class="py-3 px-4">
                   <span
                    class="inline-block px-3 py-1 text-xs rounded-full font-medium"
                    :class="getStatusClasses(user.status)"
                  >
                    {{ user.status }}
                  </span>
                </td>
                
                <td class="py-3 px-4 text-sm">
                  <button
                    @click="editUser(user.id)"
                    class="text-blue-600 hover:text-blue-800 mr-3 font-medium"
                  >
                    Edit
                  </button>
                  <button
                    @click="deleteUser(user.id)"
                    class="text-red-600 hover:text-red-800 font-medium"
                  >
                    Delete
                  </button>
                </td>
              </tr>
            </template>
            
            <template v-else>
              <tr>
                <td colspan="5" class="py-8 text-center text-sm text-gray-500">
                  No users found.
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</template>