<template>
  <div class="bg-neutral-100 min-h-screen">
    <div class="max-w-md mx-auto bg-white min-h-screen font-sans">
      
      <!-- Loading State -->
      <div v-if="isLoading" class="flex justify-center items-center min-h-screen">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-600"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="flex flex-col justify-center items-center min-h-screen px-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-red-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="text-gray-600 mb-4 text-center">{{ error }}</p>
        <router-link 
          to="/order/menu"
          class="px-6 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700"
        >
          Kembali ke Menu
        </router-link>
      </div>

      <!-- Product Content -->
      <template v-else-if="product">
        <!-- Product Image Section -->
        <section class="relative">
          <img 
            :src="product.imageUrl" 
            :alt="product.name" 
            class="w-full h-80 object-cover"
            @error="handleImageError"
          />
          
          <!-- Close Button -->
          <router-link 
            to="/order/menu"
            class="absolute top-5 right-4 w-10 h-10 bg-white rounded-full shadow-lg flex items-center justify-center hover:bg-gray-50 transition-colors"
            aria-label="Close and return to menu"
          >
            <svg 
              xmlns="http://www.w3.org/2000/svg" 
              class="h-6 w-6 text-gray-700"
              fill="none" 
              viewBox="0 0 24 24" 
              stroke="currentColor"
            >
              <path 
                stroke-linecap="round" 
                stroke-linejoin="round" 
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </router-link>
        </section>

        <!-- Product Details Section -->
        <section class="bg-white px-6 py-6">
          <!-- Product Info -->
          <div class="mb-4">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ product.name }}</h1>
            <p class="text-2xl font-bold text-orange-600">Rp {{ formatPrice(product.price) }}</p>
          </div>

          <!-- Product Description -->
          <div class="mb-6">
            <p class="text-gray-600 text-sm leading-relaxed">
              {{ product.description || 'Deskripsi produk tidak tersedia.' }}
            </p>
          </div>

          <!-- Variants Selection -->
          <div class="mb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-3">Variants</h2>
            <div class="flex gap-3">
              <button 
                @click="selectedTemperature = 'iced'"
                :class="[
                  'flex-1 py-3 px-4 rounded-lg font-medium transition-colors border-2',
                  selectedTemperature === 'iced'
                    ? 'text-white border-transparent'
                    : 'bg-white text-gray-700 border-gray-300'
                ]"
                :style="selectedTemperature === 'iced' ? 'background-color: #B85814;' : ''"
                :onmouseover="selectedTemperature === 'iced' ? '' : 'this.style.borderColor=\'#B85814\''"
                :onmouseout="selectedTemperature === 'iced' ? '' : 'this.style.borderColor=\'#d1d5db\''"
                :aria-pressed="selectedTemperature === 'iced'"
              >
                Iced
              </button>
              <button 
                @click="selectedTemperature = 'hot'"
                :class="[
                  'flex-1 py-3 px-4 rounded-lg font-medium transition-colors border-2',
                  selectedTemperature === 'hot'
                    ? 'text-white border-transparent'
                    : 'bg-white text-gray-700 border-gray-300'
                ]"
                :style="selectedTemperature === 'hot' ? 'background-color: #B85814;' : ''"
                :onmouseover="selectedTemperature === 'hot' ? '' : 'this.style.borderColor=\'#B85814\''"
                :onmouseout="selectedTemperature === 'hot' ? '' : 'this.style.borderColor=\'#d1d5db\''"
                :aria-pressed="selectedTemperature === 'hot'"
              >
                Hot
              </button>
            </div>
          </div>

          <!-- Order Controls -->
          <div class="bg-white border-t border-gray-200 pt-6 pb-6">
            <div class="flex items-center justify-between mb-4">
              <span class="text-lg font-bold text-gray-900">Total Order</span>
              <div class="flex items-center space-x-3">
                <!-- Decrement Button -->
                <button 
                  @click="decrementTotal"
                  :disabled="totalQuantity <= 1"
                  :class="[
                    'w-10 h-10 rounded-full border-2 flex items-center justify-center transition-colors',
                    totalQuantity <= 1
                      ? 'border-gray-300 text-gray-300 cursor-not-allowed'
                      : 'text-gray-700'
                  ]"
                  :style="totalQuantity > 1 ? 'border-color: #B85814; color: #B85814;' : ''"
                  :onmouseover="totalQuantity > 1 ? 'this.style.backgroundColor=\'#FFF7ED\'' : ''"
                  :onmouseout="totalQuantity > 1 ? 'this.style.backgroundColor=\'transparent\'' : ''"
                  aria-label="Decrease quantity"
                >
                  <svg 
                    xmlns="http://www.w3.org/2000/svg" 
                    class="h-5 w-5"
                    viewBox="0 0 20 20" 
                    fill="currentColor"
                  >
                    <path 
                      fill-rule="evenodd" 
                      d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                      clip-rule="evenodd"
                    />
                  </svg>
                </button>
                
                <!-- Quantity Display -->
                <span class="text-xl font-bold text-gray-900 w-8 text-center">
                  {{ totalQuantity }}
                </span>
                
                <!-- Increment Button -->
                <button 
                  @click="incrementTotal"
                  :disabled="totalQuantity >= 99"
                  class="w-10 h-10 rounded-full text-white flex items-center justify-center transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed"
                  style="background-color: #B85814;"
                  onmouseover="if(!this.disabled) this.style.backgroundColor='#A04D12'" 
                  onmouseout="if(!this.disabled) this.style.backgroundColor='#B85814'"
                  aria-label="Increase quantity"
                >
                  <svg 
                    xmlns="http://www.w3.org/2000/svg" 
                    class="h-5 w-5"
                    viewBox="0 0 20 20" 
                    fill="currentColor"
                  >
                    <path 
                      fill-rule="evenodd"
                      d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                      clip-rule="evenodd"
                    />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Add to Cart Button -->
            <button 
              @click="handleAddToCart"
              class="w-full py-4 text-white text-lg font-bold rounded-xl transition-colors shadow-lg disabled:bg-gray-400 disabled:cursor-not-allowed"
              style="background-color: #B85814;"
              onmouseover="if(!this.disabled) this.style.backgroundColor='#A04D12'" 
              onmouseout="if(!this.disabled) this.style.backgroundColor='#B85814'"
              :disabled="isAddingToCart"
            >
              {{ isAddingToCart ? 'Adding...' : `Add Orders - Rp ${totalPrice}` }}
            </button>
          </div>
        </section>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const route = useRoute();

// Product data
const product = ref(null);
const isLoading = ref(true);
const error = ref(null);

// State
const selectedTemperature = ref('iced');
const totalQuantity = ref(1);
const isAddingToCart = ref(false);

// API base URL
const API_URL = 'http://localhost:8000/api/guest';

// Fetch product on mount
onMounted(async () => {
  await fetchProduct();
});

const fetchProduct = async () => {
  try {
    isLoading.value = true;
    error.value = null;
    
    const productId = route.params.id;
    const response = await axios.get(`${API_URL}/products/${productId}`);
    
    if (response.data.success) {
      product.value = response.data.data;
    } else {
      error.value = 'Produk tidak ditemukan';
    }
  } catch (err) {
    console.error('Error fetching product:', err);
    if (err.response?.status === 404) {
      error.value = 'Produk tidak ditemukan';
    } else {
      error.value = 'Gagal memuat detail produk';
    }
  } finally {
    isLoading.value = false;
  }
};

const handleImageError = (e) => {
  e.target.src = 'https://images.unsplash.com/photo-1517487881594-2787fef5ebf7?w=800&q=80';
};

const incrementTotal = () => {
  if (totalQuantity.value < 99) {
    totalQuantity.value++;
  }
};

const decrementTotal = () => {
  if (totalQuantity.value > 1) {
    totalQuantity.value--;
  }
};

const formatPrice = (price) => {
  return parseFloat(price).toLocaleString('id-ID');
};

const handleAddToCart = async () => {
  try {
    isAddingToCart.value = true;

    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const cartItemId = `${product.value.id}-${selectedTemperature.value}`;

    const existingItemIndex = cart.findIndex(item => item.id === cartItemId);

    if (existingItemIndex !== -1) {
      cart[existingItemIndex].quantity += totalQuantity.value;
    } else {
      const newItem = {
        id: cartItemId,
        name: product.value.name,
        price: product.value.price,
        quantity: totalQuantity.value,
        imageUrl: product.value.imageUrl,
        productId: product.value.id,
        variant: selectedTemperature.value
      };
      cart.push(newItem);
    }

    localStorage.setItem('cart', JSON.stringify(cart));

    await new Promise(resolve => setTimeout(resolve, 300));

    router.push('/order/menu');
  } catch (error) {
    console.error('Error adding to cart:', error);
    alert('Failed to add item to cart. Please try again.');
  } finally {
    isAddingToCart.value = false;
  }
};

const totalPrice = computed(() => {
  if (!product.value) return '0';
  return formatPrice(product.value.price * totalQuantity.value);
});
</script>

<style scoped>
/* Add any additional styles here */
</style>