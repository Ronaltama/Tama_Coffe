<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

// --- DATA CONTOH ---
const products = ref([
  { id: 1, name: 'Artisan Coffee Beans', category: 'Drink', subCategory: 'Kopi', price: 150000, status: 'Available' },
  { id: 2, name: 'Cappuccino', category: 'Drink', subCategory: 'Kopi', price: 35000, status: 'Available' },
  { id: 3, name: 'Green Tea Latte', category: 'Drink', subCategory: 'Non-Kopi', price: 30000, status: 'Available' },
  { id: 4, name: 'Chocolate Milkshake', category: 'Drink', subCategory: 'Non-Kopi', price: 40000, status: 'Out of Stock' },
  { id: 5, name: 'Nasi Goreng Special', category: 'Food', subCategory: null, price: 45000, status: 'Available' },
  { id: 6, name: 'French Fries', category: 'Food', subCategory: null, price: 25000, status: 'Available' },
]);

// State untuk tabs
const activeTab = ref('Drink');
const activeSubTab = ref('Kopi');

// Filter produk berdasarkan tab aktif
const filteredProducts = computed(() => {
  let filtered = products.value.filter(p => p.category === activeTab.value);
  
  if (activeTab.value === 'Drink' && activeSubTab.value) {
    filtered = filtered.filter(p => p.subCategory === activeSubTab.value);
  }
  
  return filtered;
});

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
    return 'bg-red-100 text-red-700';
  }
  return 'bg-gray-100 text-gray-700';
};

// Fungsi untuk mengganti tab
const setActiveTab = (tab) => {
  activeTab.value = tab;
  if (tab === 'Drink') {
    activeSubTab.value = 'Kopi';
  }
};

// Fungsi untuk tombol
const addProduct = () => {
  router.push('/superadmin/products/AddProducts');
};

const editProduct = (id) => {
  console.log(`Editing product ${id}`);
  // Implement edit functionality
  // router.push(`/superadmin/products/edit/${id}`);
};

const deleteProduct = (id) => {
  if (confirm('Are you sure you want to delete this product?')) {
    console.log(`Deleting product ${id}`);
    // Implement delete functionality
    // products.value = products.value.filter(p => p.id !== id);
  }
};
</script>

<template>
  <!-- Your existing template code stays the same -->
  <div class="space-y-8">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <h1 class="text-3xl font-bold text-gray-900">Products Management</h1>
      <button
        @click="addProduct"
        class="px-5 py-2.5 bg-amber-700 text-white rounded-lg hover:bg-amber-800 transition shadow-sm"
      >
        + Add Product
      </button>
    </div>

    <!-- Tab Navigation: Drink / Food -->
    <div class="border-b border-gray-200">
      <nav class="flex space-x-8">
        <button
          @click="setActiveTab('Drink')"
          :class="[
            'py-3 px-1 text-base font-medium transition-colors relative',
            activeTab === 'Drink'
              ? 'text-amber-700 border-b-2 border-amber-700'
              : 'text-gray-500 hover:text-gray-700'
          ]"
        >
          Drink
        </button>
        <button
          @click="setActiveTab('Food')"
          :class="[
            'py-3 px-1 text-base font-medium transition-colors relative',
            activeTab === 'Food'
              ? 'text-amber-700 border-b-2 border-amber-700'
              : 'text-gray-500 hover:text-gray-700'
          ]"
        >
          Food
        </button>
      </nav>
    </div>

    <!-- Sub-tab untuk Drink: Kopi / Non-Kopi -->
    <div v-if="activeTab === 'Drink'" class="flex space-x-6">
      <button
        @click="activeSubTab = 'Kopi'"
        :class="[
          'text-sm font-medium transition-colors pb-1',
          activeSubTab === 'Kopi'
            ? 'text-amber-700 border-b-2 border-amber-700'
            : 'text-gray-500 hover:text-gray-700'
        ]"
      >
        Kopi
      </button>
      <button
        @click="activeSubTab = 'Non-Kopi'"
        :class="[
          'text-sm font-medium transition-colors pb-1',
          activeSubTab === 'Non-Kopi'
            ? 'text-amber-700 border-b-2 border-amber-700'
            : 'text-gray-500 hover:text-gray-700'
        ]"
      >
        Non-Kopi
      </button>
    </div>

    <!-- Table -->
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
            <template v-if="filteredProducts.length > 0">
              <tr v-for="product in filteredProducts" :key="product.id" class="hover:bg-gray-50">
                <td class="py-3 px-4 text-sm text-gray-900 font-medium">
                  {{ product.name }}
                </td>
                <td class="py-3 px-4 text-sm text-gray-700">
                  {{ product.subCategory || product.category }}
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

<style scoped>
/* Tambahan style jika diperlukan */
</style>