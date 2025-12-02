<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import Swal from 'sweetalert2'

const router = useRouter()

// === STATE ===
const categories = ref([])
const products = ref([])
const activeTab = ref(null)
const loading = ref(true)
const error = ref(null)
const searchQuery = ref('')
const selectedCategory = ref('all')

// === API BASE URL ===
const API_BASE = 'http://localhost:8000/api'

// === FETCH DATA CATEGORY DAN PRODUK ===
const fetchCategories = async () => {
  try {
    const token = localStorage.getItem('token')
    const config = token ? { headers: { Authorization: `Bearer ${token}` } } : {}
    
    const res = await axios.get(`${API_BASE}/categories`, config)
    categories.value = res.data.data
    if (categories.value.length > 0) {
      activeTab.value = categories.value[0].name
    }
  } catch (err) {
    console.error(err)
    error.value = 'Failed to fetch categories'
  }
}

const fetchProducts = async () => {
  try {
    const token = localStorage.getItem('token')
    const config = token ? { headers: { Authorization: `Bearer ${token}` } } : {}

    const res = await axios.get(`${API_BASE}/products`, config)
    products.value = res.data.data
  } catch (err) {
    console.error(err)
    error.value = 'Failed to fetch products'
  }
}

onMounted(async () => {
  await Promise.all([fetchCategories(), fetchProducts()])
  loading.value = false
})

// === FILTER PRODUK BERDASARKAN TAB AKTIF ===
const filteredProducts = computed(() => {
  if (!activeTab.value) return []

  const activeCategory = categories.value.find(c => c.name === activeTab.value)
  if (!activeCategory) return []

  // Ambil produk berdasarkan kategori
  let filtered = products.value.filter(p => p.category?.id === activeCategory.id)

  // Hanya filter subcategory kalau user pilih manual (dropdown)
  if (selectedCategory.value !== 'all') {
    filtered = filtered.filter(
      p => p.sub_category?.name === selectedCategory.value
    )
  }

  // Filter by search query
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(p => 
      p.name?.toLowerCase().includes(query)
    )
  }

  return filtered
})

// Get subcategories for dropdown based on active tab
const subCategoriesForDropdown = computed(() => {
  const activeCategory = categories.value.find(c => c.name === activeTab.value)
  return activeCategory?.sub_categories || []
})

// === HELPER === 
const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(value)
}

// === ACTIONS ===
const setActiveTab = (tabName) => {
  activeTab.value = tabName
  selectedCategory.value = 'all'
  searchQuery.value = ''
}

const addProduct = () => {
  router.push('/superadmin/products/AddProducts')
}

const editProduct = (id) => {
  router.push(`/superadmin/products/edit/${id}`)
}

const deleteProduct = async (product) => {
  const result = await Swal.fire({
    title: `Delete ${product.name}?`,
    text: "This action cannot be undone.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#dc2626",
    cancelButtonColor: "#6b7280",
    confirmButtonText: "Yes, delete it!",
    cancelButtonText: "Cancel",
    reverseButtons: true
  })

  if (result.isConfirmed) {
    try {
      const token = localStorage.getItem('token')
      const config = token ? { headers: { Authorization: `Bearer ${token}` } } : {}
      await axios.delete(`${API_BASE}/products/${product.id}`, config)
      
      products.value = products.value.filter(p => p.id !== product.id)
      
      Swal.fire({
        icon: "success",
        title: "Deleted!",
        text: `Product ${product.name} has been deleted.`,
        confirmButtonColor: "#854D0E",
        timer: 1500,
        showConfirmButton: false
      })

    } catch (err) {
      console.error(err)
      Swal.fire({
        icon: "error",
        title: "Failed!",
        text: "Failed to delete product. Please try again.",
        confirmButtonColor: "#854D0E",
      })
    }
  }
}
</script>

<template>
  <div class="flex-1 overflow-auto font-inter">
    <div class="p-8 max-w-[1920px] mx-auto">
      <!-- Page Title -->
      <div class="mb-6">
        <h1 class="text-color-azure-11 text-4xl font-bold font-inter leading-tight">Products Management</h1>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="bg-white rounded-2xl shadow-sm p-16 text-center">
        <div class="inline-flex items-center gap-3">
          <div class="w-5 h-5 border-2 border-color-brown border-t-transparent rounded-full animate-spin"></div>
          <div class="text-color-grey-46 text-sm font-medium">Loading products...</div>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-white rounded-2xl shadow-sm p-16 text-center">
        <p class="text-red-500">{{ error }}</p>
      </div>

      <!-- Main Content -->
      <div v-else>
        <!-- Tab Navigation: Drink / Food -->
        <div class="border-b border-gray-200 mb-6">
          <nav class="flex space-x-8">
            <button 
              v-for="category in categories" 
              :key="category.id" 
              @click="setActiveTab(category.name)" 
              :class="[
                'py-3 px-1 text-base font-semibold font-inter transition-colors relative',
                activeTab === category.name
                  ? 'text-color-brown border-b-2'
                  : 'text-gray-500 hover:text-gray-700'
              ]"
              :style="activeTab === category.name ? 'border-color: #854D0E' : ''"
            >
              {{ category.name }}
            </button>
          </nav>
        </div>

        <!-- Add Products Button -->
        <div class="mb-6 flex justify-end">
          <button
            @click="addProduct"
            style="background-color: #854D0E"
            class="px-5 py-2.5 text-white rounded-lg flex items-center gap-2 transition-all duration-200 hover:opacity-90 hover:shadow-md active:scale-95 font-medium"
          >
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
            </svg>
            <span class="text-sm font-medium font-inter">Add Products</span>
          </button>
        </div>

        <!-- Table Card -->
        <div class="rounded-2xl overflow-hidden">
          <!-- Search and Filter Bar -->
          <div class="p-6 bg-white border-b border-gray-100 flex gap-4">
            <!-- Search Input -->
            <div class="flex-1 relative">
              <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="11" cy="11" r="6" stroke="currentColor" stroke-width="2"/>
                <path d="M20 20L17 17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
              <input
                v-model="searchQuery"
                type="text"
                :placeholder="`Search ${activeTab}`"
                class="w-full pl-10 pr-4 py-2.5 bg-white rounded-xl border border-gray-200 text-sm font-normal font-inter text-gray-700 placeholder-gray-400 outline-none focus:border-color-brown focus:ring-2 focus:ring-orange-100 transition-all duration-200"
              />
            </div>

            <!-- Category Filter Dropdown -->
            <div class="relative">
              <select
                v-model="selectedCategory"
                class="appearance-none bg-white border border-gray-200 rounded-xl px-4 py-2.5 pr-10 text-sm font-normal font-inter text-gray-700 outline-none focus:border-color-brown focus:ring-2 focus:ring-orange-100 cursor-pointer transition-all duration-200"
                style="background-image: url('data:image/svg+xml,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 fill=%27none%27 viewBox=%270 0 20 20%27%3e%3cpath stroke=%27%236b7280%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27 stroke-width=%271.5%27 d=%27M6 8l4 4 4-4%27/%3e%3c/svg%3e'); background-position: right 0.75rem center; background-repeat: no-repeat; background-size: 1.25em 1.25em; padding-right: 2.5rem;"
              >
                <option value="all">Category</option>
                <option 
                  v-for="sub in subCategoriesForDropdown" 
                  :key="sub.id" 
                  :value="sub.name"
                >
                  {{ sub.name }}
                </option>
              </select>
            </div>
          </div>

          <!-- Products Table -->
          <div class="overflow-x-auto bg-white">
            <table class="w-full">
              <!-- Table Header -->
              <thead class="bg-gray-50">
                <tr class="border-b border-gray-100">
                  <th class="px-6 py-4 text-left">
                    <div class="text-color-azure-34 text-xs font-semibold font-inter uppercase tracking-wider">Products Name</div>
                  </th>
                  <th class="px-6 py-4 text-center">
                    <div class="text-color-azure-34 text-xs font-semibold font-inter uppercase tracking-wider">Category</div>
                  </th>
                  <th class="px-6 py-4 text-center">
                    <div class="text-color-azure-34 text-xs font-semibold font-inter uppercase tracking-wider">Status</div>
                  </th>
                  <th class="px-6 py-4 text-right">
                    <div class="text-color-azure-34 text-xs font-semibold font-inter uppercase tracking-wider">Price</div>
                  </th>
                  <th class="px-6 py-4 text-center">
                    <div class="text-color-azure-34 text-xs font-semibold font-inter uppercase tracking-wider">Actions</div>
                  </th>
                </tr>
              </thead>

              <!-- Table Body -->
              <tbody class="bg-white divide-y divide-gray-100">
                <template v-if="filteredProducts.length === 0">
                  <tr>
                    <td colspan="5" class="p-12 text-center">
                      <div class="flex flex-col items-center gap-3">
                        <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <div class="text-color-grey-46 text-sm font-medium">No products found</div>
                        <div class="text-gray-400 text-xs">{{ searchQuery ? 'Try different search keywords' : 'Start by adding your first product' }}</div>
                      </div>
                    </td>
                  </tr>
                </template>

                <template v-else>
                  <tr
                    v-for="product in filteredProducts"
                    :key="product.id"
                    class="hover:bg-gray-50 transition-colors duration-150"
                  >
                    <!-- Product Name -->
                    <td class="px-6 py-4">
                      <div class="text-color-azure-11 text-sm font-semibold font-inter">{{ product.name }}</div>
                    </td>

                    <!-- Category -->
                    <td class="px-6 py-4 text-center">
                      <div class="text-color-grey-46 text-sm font-normal font-inter">{{ product.sub_category?.name || product.category?.name || '-' }}</div>
                    </td>

                    <!-- Status -->
                    <td class="px-6 py-4">
                      <div class="flex justify-center items-center gap-2">
                        <div :class="product.status === 'available' ? 'bg-green-500' : 'bg-red-500'" class="w-2 h-2 rounded-full shadow-sm"></div>
                        <div :class="product.status === 'available' ? 'text-green-600' : 'text-red-600'" class="text-sm font-semibold font-inter capitalize">
                          {{ product.status === 'available' ? 'Available' : 'Unavailable' }}
                        </div>
                      </div>
                    </td>

                    <!-- Price -->
                    <td class="px-6 py-4 text-right">
                      <div class="text-color-grey-46 text-sm font-normal font-inter">{{ formatCurrency(product.price) }}</div>
                    </td>

                    <!-- Actions -->
                    <td class="px-6 py-4">
                      <div class="flex justify-center items-center gap-1">
                        <!-- Edit Button -->
                        <button
                          @click="editProduct(product.id)"
                          class="p-2 text-gray-500 hover:text-color-brown hover:bg-orange-50 rounded-lg transition-all duration-200 active:scale-95"
                          title="Edit product"
                        >
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                          </svg>
                        </button>

                        <!-- Delete Button -->
                        <button
                          @click="deleteProduct(product)"
                          class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all duration-200 active:scale-95"
                          title="Delete product"
                        >
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

.font-inter {
  font-family: 'Inter', sans-serif;
}

/* Custom scrollbar */
.overflow-x-auto::-webkit-scrollbar {
  height: 8px;
}

.overflow-x-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 10px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}
</style>