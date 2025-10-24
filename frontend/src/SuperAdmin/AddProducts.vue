<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

// Form data
const productName = ref('');
const description = ref('');
const price = ref('');
const status = ref('');
const category = ref('');
const productImage = ref(null);
const imagePreview = ref('');

// Handle image upload
const handleImageUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    productImage.value = file;
    const reader = new FileReader();
    reader.onload = (e) => {
      imagePreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

// Trigger file input
const triggerFileInput = () => {
  document.getElementById('fileInput').click();
};

// Handle drag and drop
const handleDrop = (event) => {
  event.preventDefault();
  const file = event.dataTransfer.files[0];
  if (file && file.type.startsWith('image/')) {
    productImage.value = file;
    const reader = new FileReader();
    reader.onload = (e) => {
      imagePreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const handleDragOver = (event) => {
  event.preventDefault();
};

// Save product
const saveProduct = () => {
  const newProduct = {
    name: productName.value,
    description: description.value,
    price: price.value,
    status: status.value,
    category: category.value,
    image: imagePreview.value
  };
  console.log('Saving product:', newProduct);
  alert('Product saved successfully!');
  router.push('/superadmin/products');
};

// Cancel
const cancel = () => {
  router.push('/superadmin/products');
};

// Navigate back
const goBack = () => {
  router.push('/superadmin/products');
};
</script>

<template>
  <div class="min-h-screen">
    <div class="max-w-4xl mx-auto py-8 px-4">
      <!-- Header -->
      <div class="flex items-center space-x-3 mb-6">
        <button @click="goBack" class="text-gray-600 hover:text-gray-900">
          ← Back
        </button>
        <h1 class="text-xl font-semibold text-gray-900">Add Product</h1>
      </div>

      <!-- Form -->
      <div class="bg-white p-6 rounded-lg shadow-sm">
        <div class="space-y-6">
          <!-- Product Name -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
            <input
              v-model="productName"
              type="text"
              placeholder="e.g., Es Kopi Susu"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none"
            />
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea
              v-model="description"
              rows="3"
              placeholder="A short description of the product"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none resize-none"
            ></textarea>
          </div>

          <div class="grid grid-cols-2 gap-6">
            <!-- Price -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
              <div class="relative">
                <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                <input
                  v-model="price"
                  type="number"
                  placeholder="25000"
                  class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none"
                />
              </div>
            </div>

            <!-- Status -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select
                v-model="status"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none"
              >
                <option value="" disabled selected>Select Status</option>
                <option value="Available">Available</option>
                <option value="Out of Stock">Out of Stock</option>
              </select>
            </div>
          </div>

          <!-- Product Image -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Product Image</label>
            <div
              @click="triggerFileInput"
              @drop="handleDrop"
              @dragover="handleDragOver"
              class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:border-amber-500 transition-colors"
            >
              <input
                id="fileInput"
                type="file"
                accept="image/*"
                @change="handleImageUpload"
                class="hidden"
              />
              
              <div v-if="!imagePreview">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                  <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <p class="mt-2 text-sm text-gray-600">
                  <span class="text-amber-700 font-medium">Upload a file</span> or drag and drop
                </p>
                <p class="mt-1 text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
              </div>
              
              <div v-else class="relative">
                <img :src="imagePreview" alt="Preview" class="mx-auto max-h-32 rounded" />
                <p class="mt-2 text-xs text-gray-500">Click to change image</p>
              </div>
            </div>
          </div>

          <!-- Category -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
            <select
              v-model="category"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none"
            >
              <option value="" disabled selected>Select category</option>
              <option value="Drink">Drink</option>
              <option value="Food">Food</option>
            </select>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-end space-x-3 pt-4">
            <button
              @click="cancel"
              class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
            >
              Cancel
            </button>
            <button
              @click="saveProduct"
              class="px-4 py-2 text-sm font-medium text-white bg-amber-700 hover:bg-amber-800 rounded-lg transition-colors"
            >
              Save Product
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>