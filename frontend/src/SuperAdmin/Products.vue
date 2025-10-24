<script setup>
import { ref } from 'vue';

// --- DATA CONTOH ---
// Ganti ini dengan data dari API Anda
const products = ref([
  { id: 1, name: 'Artisan Coffee Beans', category: 'Coffee', price: 150000, status: 'Available' },
  { id: 2, name: 'Gourmet Chocolate Bar', category: 'Snack', price: 80000, status: 'Available' },
  { id: 3, name: 'Organic Tea Selection', category: 'Non-Coffee', price: 150000, status: 'Out of Stock' },
]);
// --- AKHIR DATA CONTOH ---

// Helper untuk format mata uang
const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(value);
};

// Helper untuk mendapatkan kelas CSS berdasarkan Status
const getStatusClasses = (status) => {
  if (status === 'Available') {
    return 'bg-green-100 text-green-700';
  }
  if (status === 'Out of Stock') {
    return 'bg-red-100 text-red-700'; // Sesuai gambar, out of stock warnanya merah/pink
  }
  return 'bg-gray-100 text-gray-700';
};

// Fungsi untuk tombol (ganti dengan logika Anda)
const addProduct = () => {
  alert('Membuka halaman tambah produk...');
  // Contoh: router.push('/superadmin/products/create');
};

const editProduct = (id) => {
  alert(`Mengedit produk dengan ID: ${id}`);
  // Contoh: router.push(`/superadmin/products/${id}/edit`);
};

const deleteProduct = (id) => {
  if (confirm(`Apakah Anda yakin ingin menghapus produk ID: ${id}?`)) {
    alert(`Menghapus produk dengan ID: ${id}`);
    // Logika panggil API delete
  }
};
</script>

<template>
  <div class="space-y-8">

    <div class="flex items-center justify-between">
      <h1 class="text-3xl font-bold text-gray-900">Product Management</h1>
      <button
        @click="addProduct"
        class="px-5 py-2.5 bg-amber-700 text-white rounded-lg hover:bg-amber-800 transition shadow-sm"
      >
        + Add Product
      </button>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
      <div class="overflow-x-auto">
        <table class="w-full min-w-[640px]">
          <thead>
            <tr class="border-b border-gray-200 text-left text-sm font-semibold text-gray-700">
              <th class="py-3 px-4">Product Name</th>
              <th class="py-3 px-4">Category</th>
              <th class="py-3 px-4">Price</th>
              <th class="py-3 px-4">Status</th>
              <th class="py-3 px-4">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <template v-if="products.length > 0">
              <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50">
                
                <td class="py-3 px-4 text-sm text-gray-900 font-medium">
                  {{ product.name }}
                </td>

                <td class="py-3 px-4 text-sm text-gray-700">
                  {{ product.category }}
                </td>
                
                <td class="py-3 px-4 text-sm text-gray-700">
                  {{ formatCurrency(product.price) }}
                </td>

                <td class="py-3 px-4">
                   <span
                    class="inline-block px-3 py-1 text-xs rounded-full font-medium"
                    :class="getStatusClasses(product.status)"
                  >
                    {{ product.status }}
                  </span>
                </td>
                
                <td class="py-3 px-4 text-sm">
                  <button
                    @click="editProduct(product.id)"
                    class="text-blue-600 hover:text-blue-800 mr-3 font-medium"
                  >
                    Edit
                  </button>
                  <button
                    @click="deleteProduct(product.id)"
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
                  No products found.
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</template>