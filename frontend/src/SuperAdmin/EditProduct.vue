<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const route = useRoute()

// === STATE ===
const productId = ref(route.params.id)
const productName = ref('')
const categoryId = ref('')
const subCategoryId = ref('')
const description = ref('')
const price = ref('')
const status = ref('available')
const imageFile = ref(null)
const imagePreview = ref(null)
const existingImage = ref(null)

const categories = ref([])
const loading = ref(false)
const loadingData = ref(true)
const error = ref(null)

// === API BASE URL ===
const API_BASE = 'http://localhost:8000/api'

// === FETCH CATEGORIES ===
const fetchCategories = async () => {
  try {
    const res = await axios.get(`${API_BASE}/categories`)
    categories.value = res.data.data
  } catch (err) {
    console.error(err)
    error.value = 'Failed to fetch categories'
  }
}

// === FETCH PRODUCT DATA ===
const fetchProduct = async () => {
  try {
    const res = await axios.get(`${API_BASE}/products/${productId.value}`)
    const product = res.data.data
    
    productName.value = product.name
    categoryId.value = product.category_id
    subCategoryId.value = product.sub_category_id || ''
    description.value = product.description || ''
    price.value = product.price
    status.value = product.status
    
    if (product.image) {
      existingImage.value = `http://localhost:8000/storage/${product.image}`
      imagePreview.value = existingImage.value
    }
  } catch (err) {
    console.error(err)
    error.value = 'Failed to fetch product data'
    alert('Gagal mengambil data produk')
  } finally {
    loadingData.value = false
  }
}

onMounted(async () => {
  await Promise.all([fetchCategories(), fetchProduct()])
})

// === COMPUTED ===
const subCategories = computed(() => {
  if (!categoryId.value) return []
  const category = categories.value.find(c => c.id === categoryId.value)
  return category?.sub_categories || []
})

// Reset subcategory saat category berubah
const handleCategoryChange = () => {
  subCategoryId.value = ''
}

// === IMAGE HANDLING ===
const handleImageChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    // Validasi ukuran file (max 2MB)
    if (file.size > 2048 * 1024) {
      alert('Ukuran file terlalu besar! Maksimal 2MB')
      event.target.value = ''
      return
    }

    // Validasi tipe file
    const validTypes = ['image/jpeg', 'image/jpg', 'image/png']
    if (!validTypes.includes(file.type)) {
      alert('Format file tidak valid! Gunakan JPG, JPEG, atau PNG')
      event.target.value = ''
      return
    }

    imageFile.value = file

    // Create preview
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

const removeImage = () => {
  imageFile.value = null
  imagePreview.value = existingImage.value
  const fileInput = document.getElementById('image')
  if (fileInput) fileInput.value = ''
}

// === FORM SUBMIT ===
const handleSubmit = async () => {
  // Validasi
  if (!productName.value.trim()) {
    alert('Nama produk harus diisi!')
    return
  }
  if (!categoryId.value) {
    alert('Kategori harus dipilih!')
    return
  }
  if (!price.value || price.value <= 0) {
    alert('Harga harus lebih dari 0!')
    return
  }

  loading.value = true
  error.value = null

  try {
    // Buat FormData untuk upload file
    const formData = new FormData()
    formData.append('_method', 'PUT') // Laravel method spoofing
    formData.append('name', productName.value)
    formData.append('category_id', categoryId.value)
    if (subCategoryId.value) {
      formData.append('sub_category_id', subCategoryId.value)
    }
    if (description.value) {
      formData.append('description', description.value)
    }
    formData.append('price', price.value)
    formData.append('status', status.value)
    if (imageFile.value) {
      formData.append('image', imageFile.value)
    }

    const response = await axios.post(`${API_BASE}/products/${productId.value}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    if (response.data.success) {
      alert('Produk berhasil diupdate!')
      router.push('/superadmin/products')
    }
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Gagal mengupdate produk'
    alert(error.value)
  } finally {
    loading.value = false
  }
}

const handleCancel = () => {
  router.push('/superadmin/products')
}
</script>

<template>
  <div class="p-6 bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Edit Product</h1>
      <p class="text-gray-600 mt-1">Update product information</p>
    </div>

    <!-- Loading State -->
    <div v-if="loadingData" class="bg-white rounded-xl shadow-sm max-w-3xl p-12 text-center">
      <div class="flex items-center justify-center">
        <svg class="animate-spin h-8 w-8 text-amber-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="ml-3 text-gray-600">Loading product data...</span>
      </div>
    </div>

    <!-- Form -->
    <form v-else @submit.prevent="handleSubmit" class="bg-white rounded-xl shadow-sm max-w-3xl">
      <div class="p-6 space-y-6">
        
        <!-- Product Name -->
        <div>
          <label for="productName" class="block text-sm font-medium text-gray-700 mb-2">
            Product Name <span class="text-red-500">*</span>
          </label>
          <input
            v-model="productName"
            type="text"
            id="productName"
            placeholder="e.g., Classic Espresso"
            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none transition"
            required
          >
        </div>

        <!-- Category & Subcategory Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Category -->
          <div>
            <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
              Category <span class="text-red-500">*</span>
            </label>
            <select
              v-model="categoryId"
              @change="handleCategoryChange"
              id="category"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none transition appearance-none bg-white"
              required
            >
              <option value="" disabled>Select category</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>
          </div>

          <!-- Subcategory -->
          <div>
            <label for="subCategory" class="block text-sm font-medium text-gray-700 mb-2">
              Subcategory
            </label>
            <select
              v-model="subCategoryId"
              id="subCategory"
              :disabled="!categoryId || subCategories.length === 0"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none transition appearance-none bg-white disabled:bg-gray-100 disabled:cursor-not-allowed"
            >
              <option value="">Select subcategory (optional)</option>
              <option v-for="sub in subCategories" :key="sub.id" :value="sub.id">
                {{ sub.name }}
              </option>
            </select>
          </div>
        </div>

        <!-- Description -->
        <div>
          <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
            Description
          </label>
          <textarea
            v-model="description"
            id="description"
            rows="4"
            placeholder="Enter product description..."
            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none transition resize-none"
          ></textarea>
        </div>

        <!-- Price & Status Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Price -->
          <div>
            <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
              Price (IDR) <span class="text-red-500">*</span>
            </label>
            <input
              v-model.number="price"
              type="number"
              id="price"
              placeholder="e.g., 25000"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none transition"
              required
              min="0"
              step="1"
            >
          </div>

          <!-- Status -->
          <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
              Status <span class="text-red-500">*</span>
            </label>
            <select
              v-model="status"
              id="status"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none transition appearance-none bg-white"
            >
              <option value="available">Available</option>
              <option value="unavailable">Unavailable</option>
            </select>
          </div>
        </div>

        <!-- Image Upload -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Product Image
          </label>
          
          <!-- Current Image or Upload Area -->
          <div v-if="!imagePreview" class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-amber-500 transition">
            <input
              type="file"
              id="image"
              accept="image/jpeg,image/jpg,image/png"
              @change="handleImageChange"
              class="hidden"
            >
            <label for="image" class="cursor-pointer">
              <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
              <p class="mt-2 text-sm text-gray-600">
                <span class="font-medium text-amber-700 hover:text-amber-800">Click to upload</span> or drag and drop
              </p>
              <p class="text-xs text-gray-500 mt-1">PNG, JPG, JPEG up to 2MB</p>
            </label>
          </div>

          <!-- Image Preview -->
          <div v-else class="relative">
            <img :src="imagePreview" alt="Preview" class="w-full h-64 object-cover rounded-lg border border-gray-300">
            <div class="absolute top-2 right-2 flex gap-2">
              <button
                type="button"
                @click="removeImage"
                class="bg-red-600 hover:bg-red-700 text-white rounded-full p-2 shadow-lg transition"
                title="Remove image"
              >
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
              <label for="image" class="bg-amber-600 hover:bg-amber-700 text-white rounded-full p-2 shadow-lg transition cursor-pointer" title="Change image">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                </svg>
                <input
                  type="file"
                  id="image"
                  accept="image/jpeg,image/jpg,image/png"
                  @change="handleImageChange"
                  class="hidden"
                >
              </label>
            </div>
          </div>
        </div>

      </div>

      <!-- Form Actions -->
      <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-xl flex justify-end gap-3">
        <button
          type="button"
          @click="handleCancel"
          class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition font-medium"
          :disabled="loading"
        >
          Cancel
        </button>
        <button
          type="submit"
          class="px-5 py-2.5 bg-amber-700 text-white rounded-lg hover:bg-amber-800 transition font-medium disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
          :disabled="loading"
        >
          <svg v-if="loading" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ loading ? 'Updating...' : 'Update Product' }}
        </button>
      </div>
    </form>
  </div>
</template>

<style scoped>
/* Custom select arrow */
select {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 0.5rem center;
  background-repeat: no-repeat;
  background-size: 1.5em 1.5em;
  padding-right: 2.5rem;
}
</style>