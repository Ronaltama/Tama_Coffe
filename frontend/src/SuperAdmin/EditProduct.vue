<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'
import Swal from 'sweetalert2'

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
    const token = localStorage.getItem('token')
    const config = token ? { headers: { Authorization: `Bearer ${token}` } } : {}
    
    const res = await axios.get(`${API_BASE}/categories`, config)
    categories.value = res.data.data
  } catch (err) {
    console.error(err)
    error.value = 'Failed to fetch categories'
  }
}

// === FETCH PRODUCT DATA ===
const fetchProduct = async () => {
  try {
    const token = localStorage.getItem('token')
    const config = token ? { headers: { Authorization: `Bearer ${token}` } } : {}
    
    const res = await axios.get(`${API_BASE}/products/${productId.value}`, config)
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
    Swal.fire({
      icon: 'error',
      title: 'Failed to load product',
      text: error.value,
      confirmButtonColor: '#854D0E'
    })
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

const handleCategoryChange = () => {
  subCategoryId.value = ''
}

// === IMAGE HANDLING ===
const handleImageChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    if (file.size > 2048 * 1024) {
      Swal.fire({
        icon: 'error',
        title: 'File too large',
        text: 'Maximum file size is 2MB',
        confirmButtonColor: '#854D0E'
      })
      event.target.value = ''
      return
    }

    const validTypes = ['image/jpeg', 'image/jpg', 'image/png']
    if (!validTypes.includes(file.type)) {
      Swal.fire({
        icon: 'error',
        title: 'Invalid format',
        text: 'Please use JPG, JPEG, or PNG',
        confirmButtonColor: '#854D0E'
      })
      event.target.value = ''
      return
    }

    imageFile.value = file

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
  if (!productName.value.trim()) {
    return Swal.fire({ 
      icon: 'warning', 
      title: 'Product name required!',
      confirmButtonColor: '#854D0E'
    })
  }
  if (!categoryId.value) {
    return Swal.fire({ 
      icon: 'warning', 
      title: 'Category required!',
      confirmButtonColor: '#854D0E'
    })
  }
  if (!price.value || price.value <= 0) {
    return Swal.fire({ 
      icon: 'warning', 
      title: 'Price must be greater than 0!',
      confirmButtonColor: '#854D0E'
    })
  }

  loading.value = true
  error.value = null

  try {
    const token = localStorage.getItem('token')
    
    const formData = new FormData()
    formData.append('_method', 'PUT')
    formData.append('name', productName.value)
    formData.append('category_id', categoryId.value)
    if (subCategoryId.value) formData.append('sub_category_id', subCategoryId.value)
    if (description.value) formData.append('description', description.value)
    formData.append('price', price.value)
    formData.append('status', status.value)
    if (imageFile.value) formData.append('image', imageFile.value)

    const response = await axios.post(`${API_BASE}/products/${productId.value}`, formData, {
      headers: { 
        'Content-Type': 'multipart/form-data',
        'Authorization': `Bearer ${token}`
      }
    })

    if (response.data.success) {
      Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: 'Product has been updated successfully!',
        confirmButtonColor: '#854D0E',
        timer: 1500,
        showConfirmButton: false
      })
      setTimeout(() => {
        router.push('/superadmin/products')
      }, 1500)
    }
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Failed to update product'

    Swal.fire({
      icon: 'error',
      title: 'Failed to update',
      text: error.value,
      confirmButtonColor: '#854D0E'
    })
  } finally {
    loading.value = false
  }
}

const handleCancel = () => {
  router.push('/superadmin/products')
}
</script>

<template>
  <div class="flex-1 overflow-auto font-inter">
    <div class="p-8 max-w-4xl mx-auto">
      <!-- Page Title with Back Button -->
      <div class="mb-6 flex items-center gap-4">
        <button 
          @click="handleCancel"
          class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
          title="Back to Products"
        >
          <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        <div>
          <h1 class="text-color-azure-11 text-4xl font-bold font-inter leading-tight">Edit Product</h1>
          <p class="text-color-grey-46 text-sm mt-1">Update product information</p>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loadingData" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-16 text-center">
        <div class="inline-flex items-center gap-3">
          <div class="w-5 h-5 border-2 border-color-brown border-t-transparent rounded-full animate-spin"></div>
          <div class="text-color-grey-46 text-sm font-medium">Loading product data...</div>
        </div>
      </div>

      <!-- Form Card -->
      <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <form @submit.prevent="handleSubmit" class="p-8 space-y-6">
          
          <!-- Product Name -->
          <div>
            <label class="text-color-azure-11 text-sm font-medium font-inter mb-2 block">
              Product Name <span class="text-red-500">*</span>
            </label>
            <input
              v-model="productName"
              type="text"
              placeholder="Caramel Machiato"
              class="w-full px-4 py-2.5 bg-white rounded-xl border border-gray-200 text-sm font-normal font-inter text-gray-700 placeholder-gray-400 outline-none focus:border-color-brown focus:ring-2 focus:ring-orange-100 transition-all duration-200"
            />
          </div>

          <!-- Description -->
          <div>
            <label class="text-color-azure-11 text-sm font-medium font-inter mb-2 block">
              Description
            </label>
            <textarea
              v-model="description"
              rows="3"
              placeholder="A delicious blend of rich espresso, creamy milk, and sweet caramel syrup, served over ice. A perfect pick-me-up for any time of day."
              class="w-full px-4 py-2.5 bg-white rounded-xl border border-gray-200 text-sm font-normal font-inter text-gray-700 placeholder-gray-400 outline-none focus:border-color-brown focus:ring-2 focus:ring-orange-100 transition-all duration-200 resize-none"
            ></textarea>
          </div>

          <!-- Price & Status -->
          <div class="grid grid-cols-2 gap-4">
            <!-- Price -->
            <div>
              <label class="text-color-azure-11 text-sm font-medium font-inter mb-2 block">
                Price <span class="text-red-500">*</span>
              </label>
              <input
                v-model.number="price"
                type="number"
                min="0"
                step="1"
                placeholder="Rp 25000"
                class="w-full px-4 py-2.5 bg-white rounded-xl border border-gray-200 text-sm font-normal font-inter text-gray-700 placeholder-gray-400 outline-none focus:border-color-brown focus:ring-2 focus:ring-orange-100 transition-all duration-200"
              />
            </div>

            <!-- Status -->
            <div>
              <label class="text-color-azure-11 text-sm font-medium font-inter mb-2 block">
                Status <span class="text-red-500">*</span>
              </label>
              <select
                v-model="status"
                class="w-full px-4 py-2.5 bg-white rounded-xl border border-gray-200 text-sm font-normal font-inter text-gray-700 outline-none focus:border-color-brown focus:ring-2 focus:ring-orange-100 transition-all duration-200 appearance-none cursor-pointer"
                style="background-image: url('data:image/svg+xml,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 fill=%27none%27 viewBox=%270 0 20 20%27%3e%3cpath stroke=%27%236b7280%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27 stroke-width=%271.5%27 d=%27M6 8l4 4 4-4%27/%3e%3c/svg%3e'); background-position: right 0.75rem center; background-repeat: no-repeat; background-size: 1.25em 1.25em; padding-right: 2.5rem;"
              >
                <option value="available">Available</option>
                <option value="unavailable">Unavailable</option>
              </select>
            </div>
          </div>

          <!-- Product Image -->
          <div>
            <label class="text-color-azure-11 text-sm font-medium font-inter mb-2 block">
              Product Image
            </label>
            
            <!-- Image Preview or Upload -->
            <div v-if="imagePreview" class="relative border-2 border-gray-200 rounded-xl overflow-hidden">
              <img :src="imagePreview" alt="Product preview" class="w-full h-64 object-cover">
              <div class="absolute bottom-3 right-3">
                <label 
                  for="image" 
                  style="background-color: #854D0E"
                  class="px-4 py-2 text-white rounded-lg text-sm font-medium hover:opacity-90 transition-all cursor-pointer flex items-center gap-2 shadow-lg"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                  </svg>
                  Edit Picture
                  <input
                    type="file"
                    id="image"
                    accept="image/jpeg,image/jpg,image/png"
                    @change="handleImageChange"
                    class="hidden"
                  />
                </label>
              </div>
            </div>
            <div v-else class="border-2 border-dashed border-gray-300 rounded-xl p-12 text-center hover:border-color-brown transition-colors">
              <input
                type="file"
                id="image"
                accept="image/jpeg,image/jpg,image/png"
                @change="handleImageChange"
                class="hidden"
              />
              <label for="image" class="cursor-pointer">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                <p class="mt-2 text-sm text-gray-600">
                  <span class="font-medium hover:underline" style="color: #854D0E">Upload a file</span> or drag and drop
                </p>
                <p class="text-xs text-gray-500 mt-1">PNG, JPG, GIF up to 10MB</p>
              </label>
            </div>
          </div>

          <!-- Category -->
          <div>
            <label class="text-color-azure-11 text-sm font-medium font-inter mb-2 block">
              Category <span class="text-red-500">*</span>
            </label>
            <select
              v-model="categoryId"
              @change="handleCategoryChange"
              class="w-full px-4 py-2.5 bg-white rounded-xl border border-gray-200 text-sm font-normal font-inter text-gray-700 outline-none focus:border-color-brown focus:ring-2 focus:ring-orange-100 transition-all duration-200 appearance-none cursor-pointer"
              style="background-image: url('data:image/svg+xml,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 fill=%27none%27 viewBox=%270 0 20 20%27%3e%3cpath stroke=%27%236b7280%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27 stroke-width=%271.5%27 d=%27M6 8l4 4 4-4%27/%3e%3c/svg%3e'); background-position: right 0.75rem center; background-repeat: no-repeat; background-size: 1.25em 1.25em; padding-right: 2.5rem;"
            >
              <option value="" disabled>Select category</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-end gap-3 pt-4">
            <button
              type="button"
              @click="handleCancel"
              class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 text-sm font-medium hover:bg-gray-50 transition-colors"
            >
              Cancel
            </button>

            <button
              type="submit"
              :disabled="loading"
              style="background-color: #854D0E"
              class="px-6 py-2.5 text-white rounded-lg text-sm font-medium hover:opacity-90 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
            >
              <svg v-if="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ loading ? "Updating..." : "Update Product" }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

.font-inter {
  font-family: 'Inter', sans-serif;
}
</style>