<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

// === STATE ===
const categories = ref([])
const products = ref([])
const activeTab = ref(null)
const activeSubTab = ref(null)
const loading = ref(true)
const error = ref(null)
const searchQuery = ref('')
const selectedCategory = ref('all')

// === API BASE URL ===
const API_BASE = 'http://localhost:8000/api' // ganti jika port backend berbeda

// === FETCH DATA CATEGORY DAN PRODUK ===
const fetchCategories = async () => {
  try {
    const res = await axios.get(`${API_BASE}/categories`)
    categories.value = res.data.data
    if (categories.value.length > 0) {
      activeTab.value = categories.value[0].name
      const firstSub = categories.value[0].sub_categories[0]
      activeSubTab.value = firstSub ? firstSub.name : null
    }
  } catch (err) {
    console.error(err)
    error.value = 'Failed to fetch categories'
  }
}

const fetchProducts = async () => {
  try {
    const res = await axios.get(`${API_BASE}/products`)
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
const getImageUrl = (imagePath) => {
  if (!imagePath) return 'https://via.placeholder.com/60x60?text=No+Image'
  // Jika imagePath sudah full URL, return as is
  if (imagePath.startsWith('http')) return imagePath
  // Jika imagePath relatif (misal: products/xxx.jpg), gabungkan dengan base URL
  return `http://localhost:8000/storage/${imagePath}`
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(value)
}

const getStatusClasses = (status) => {
  if (status === 'available') return 'bg-green-100 text-green-700'
  if (status === 'unavailable') return 'bg-red-100 text-red-700'
  return 'bg-gray-100 text-gray-700'
}

const getStatusText = (status) => {
  if (status === 'available') return 'Availabel'
  if (status === 'unavailable') return 'Unavailabel'
  return status
}

// === ACTIONS ===
const setActiveTab = (tabName) => {
  activeTab.value = tabName
  const category = categories.value.find(c => c.name === tabName)
  const firstSub = category?.sub_categories[0]
  activeSubTab.value = firstSub ? firstSub.name : null
  selectedCategory.value = 'all' // Reset dropdown filter
  searchQuery.value = '' // Reset search
}

const addProduct = () => {
  router.push('/superadmin/products/AddProducts')
}

const editProduct = (id) => {
  router.push(`/superadmin/products/edit/${id}`)
}

const deleteProduct = async (id) => {
  if (!confirm('Yakin ingin menghapus produk ini?')) return
  try {
    await axios.delete(`${API_BASE}/products/${id}`)
    products.value = products.value.filter(p => p.id !== id)
    alert('Produk berhasil dihapus!')
  } catch (err) {
    console.error(err)
    alert('Gagal menghapus produk.')
  }
}
</script>

<template>
  <div class="p-6 bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Products Management</h1>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <p class="text-gray-500">Loading...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="text-center py-8">
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
              'py-3 px-1 text-base font-medium transition-colors relative',
              activeTab === category.name
                ? 'text-amber-700 border-b-2 border-amber-700'
                : 'text-gray-500 hover:text-gray-700'
            ]"
          >
            {{ category.name }}
          </button>
        </nav>
      </div>

      <!-- Actions Bar -->
      <div class="flex justify-end mb-6">
        <button 
          @click="addProduct"
          class="bg-amber-700 hover:bg-amber-800 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
          </svg>
          Add Products
        </button>
      </div>

      <!-- Search and Filter Bar -->
      <div class="flex gap-4 mb-6">
        <!-- Search Input -->
        <div class="flex-1 relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
            </svg>
          </div>
          <input 
            v-model="searchQuery"
            type="text" 
            :placeholder="`Search ${activeTab}`"
            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none"
          >
        </div>

        <!-- Category Filter Dropdown -->
        <div class="relative">
          <select 
            v-model="selectedCategory"
            class="appearance-none bg-white border border-gray-300 rounded-lg px-4 py-2 pr-10 focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none cursor-pointer"
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
          <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Products Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <!-- Table Header -->
        <div class="grid grid-cols-12 gap-4 px-6 py-4 bg-gray-50 border-b border-gray-200">
          <div class="col-span-1 text-sm font-medium text-gray-700">Image</div>
          <div class="col-span-3 text-sm font-medium text-gray-700">Products Name</div>
          <div class="col-span-2 text-sm font-medium text-gray-700">Category</div>
          <div class="col-span-2 text-sm font-medium text-gray-700">Status</div>
          <div class="col-span-2 text-sm font-medium text-gray-700">Price</div>
          <div class="col-span-2 text-sm font-medium text-gray-700 text-right">Actions</div>
        </div>

        <!-- Table Body -->
        <div v-if="filteredProducts.length === 0" class="px-6 py-12 text-center">
          <div class="flex flex-col items-center justify-center">
            <svg class="h-16 w-16 text-gray-400 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <p class="text-gray-500 text-lg font-medium">No products found</p>
            <p class="text-gray-400 text-sm mt-1">Try adjusting your search or filter to find what you're looking for.</p>
          </div>
        </div>

        <div v-else>
          <div 
            v-for="product in filteredProducts" 
            :key="product.id"
            class="grid grid-cols-12 gap-4 px-6 py-4 border-b border-gray-200 hover:bg-gray-50 transition-colors items-center"
          >
            <!-- Product Image -->
            <div class="col-span-1">
              <img 
                :src="getImageUrl(product.image)" 
                :alt="product.name"
                class="w-14 h-14 object-cover rounded-lg border border-gray-200"
                @error="$event.target.src='https://via.placeholder.com/60x60?text=No+Image'"
              >
            </div>

            <!-- Product Name -->
            <div class="col-span-3 text-sm font-medium text-gray-900">
              {{ product.name }}
            </div>

            <!-- Category -->
            <div class="col-span-2 text-sm text-gray-600">
              {{ product.sub_category?.name || product.category?.name || '-' }}
            </div>

            <!-- Status -->
            <div class="col-span-2">
              <span 
                :class="[
                  'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium',
                  getStatusClasses(product.status)
                ]"
              >
                <span 
                  :class="[
                    'w-1.5 h-1.5 rounded-full mr-2',
                    product.status === 'available' ? 'bg-green-600' : 'bg-red-600'
                  ]"
                ></span>
                {{ getStatusText(product.status) }}
              </span>
            </div>

            <!-- Price -->
            <div class="col-span-2 text-sm font-medium text-gray-900">
              {{ formatCurrency(product.price) }}
            </div>

            <!-- Actions -->
            <div class="col-span-2 flex justify-end gap-2">
              <button 
                @click="editProduct(product.id)"
                class="p-2 text-gray-600 hover:text-amber-700 hover:bg-amber-50 rounded transition-colors"
                title="Edit"
              >
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                </svg>
              </button>
              <button 
                @click="deleteProduct(product.id)"
                class="p-2 text-gray-600 hover:text-red-700 hover:bg-red-50 rounded transition-colors"
                title="Delete"
              >
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Custom scrollbar untuk table jika diperlukan */
.overflow-x-auto::-webkit-scrollbar {
  height: 8px;
}

.overflow-x-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: #555;
}
</style>