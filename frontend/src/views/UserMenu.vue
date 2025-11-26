<template>
  <div class="bg-neutral-100 min-h-screen">
    <div class="max-w-md mx-auto bg-white min-h-screen font-sans">

      <div class="relative">
        <img src="https://images.unsplash.com/photo-1511920170033-f8396924c348?w=800&q=80" alt="Coffee brewing setup"
          class="w-full h-48 object-cover" />
        <button
          class="absolute top-4 right-4 w-10 h-10 bg-white rounded-full shadow-lg flex items-center justify-center hover:bg-gray-50 transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </button>
      </div>

      <div class="px-4 py-3 bg-white">
        <h1 class="text-2xl font-bold text-gray-900">Tama Caffeine</h1>
        <p class="text-sm text-gray-500">Open today, 07:00-22:00</p>
      </div>

      <!-- Status Reservasi / Table Number -->
      <div v-if="orderType === 'Reservasi'" class="bg-blue-50 py-4 text-center border-b border-blue-100">
        <p class="text-blue-800 font-semibold">Status: Reservasi</p>
        <p class="text-xs text-blue-600 mt-1">{{ reservationInfo }}</p>
      </div>
      <div v-else class="bg-amber-50 py-4 text-center">
        <p class="text-amber-800 font-semibold">Table Number: {{ tableNumber }}</p>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="flex justify-center items-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-600"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="px-4 py-8 text-center">
        <p class="text-red-600 mb-4">{{ error }}</p>
        <button @click="fetchProducts" class="px-6 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
          Coba Lagi
        </button>
      </div>

      <!-- Content -->
      <template v-else>
        <div class="flex border-b border-gray-200 bg-white sticky top-0 z-10">
          <button @click="activeTab = 'all'" :class="[
            'flex-1 py-3 font-medium transition-colors',
            activeTab === 'all'
              ? 'text-amber-700 border-b-2 border-amber-700'
              : 'text-gray-500 hover:text-gray-700'
          ]">
            ALL
          </button>
          <button v-for="category in categories" :key="category.id" @click="activeTab = category.id" :class="[
            'flex-1 py-3 font-medium transition-colors uppercase',
            activeTab === category.id
              ? 'text-amber-700 border-b-2 border-amber-700'
              : 'text-gray-500 hover:text-gray-700'
          ]">
            {{ category.name }}
          </button>
        </div>

        <main class="px-4 py-6 bg-white pb-40">
          <h2 class="text-lg font-bold text-gray-900 mb-4">
            {{ activeTabTitle }}<br />
            <span class="text-2xl">{{ activeTabSubtitle }}</span>
          </h2>

          <!-- Empty State -->
          <div v-if="filteredProducts.length === 0" class="text-center py-12">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300 mb-4" fill="none"
              viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <p class="text-gray-500">Belum ada produk tersedia</p>
          </div>

          <!-- Product Grid -->
          <div v-else class="grid grid-cols-2 gap-4">
            <div v-for="product in filteredProducts" :key="product.id"
              class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow flex flex-col">
              <img :src="product.imageUrl" :alt="product.name" class="w-full h-40 object-cover"
                @error="handleImageError" />

              <div class="p-3 flex flex-col flex-grow">
                <h3 class="font-bold text-base text-gray-900 line-clamp-2">{{ product.name }}</h3>
                <p class="text-sm text-gray-600 mb-3">Rp{{ formatPrice(product.price) }}</p>

                <button @click="handleProductClick(product)"
                  class="w-full py-2 bg-white hover:bg-amber-600 text-amber-600 hover:text-white border border-amber-600 font-medium rounded-md transition-colors text-center mt-auto">
                  Add
                </button>
              </div>
            </div>
          </div>
        </main>
      </template>

      <!-- Floating Cart -->
      <footer v-if="cartItems.length > 0" class="fixed bottom-0 left-0 right-0 z-50">
        <div class="max-w-md mx-auto bg-white border-t border-gray-100 p-4">
          <button @click="goToCart"
            class="w-full bg-orange-600 hover:bg-orange-700 rounded-2xl shadow-lg transition-all overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4">
              <div class="flex items-center space-x-3">
                <div class="relative">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                  <span
                    class="absolute -top-2 -right-2 bg-white text-orange-600 text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
                    {{ totalItems }}
                  </span>
                </div>
                <div class="text-left">
                  <p class="text-white text-xs font-medium">Total</p>
                  <p class="text-white text-lg font-bold">Rp{{ totalPrice }}</p>
                </div>
              </div>

              <div class="bg-white text-orange-600 px-6 py-2 rounded-lg font-bold text-sm">
                CHECK OUT ({{ totalItems }})
              </div>
            </div>

            <div class="bg-white px-4 py-3">
              <div v-for="item in cartItems" :key="item.id"
                class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                <div class="flex items-center space-x-3 flex-1">
                  <div class="flex items-center space-x-2 bg-gray-100 rounded-full px-2 py-1">
                    <button @click.stop="decrementCart(item.id)"
                      class="w-6 h-6 rounded-full bg-white flex items-center justify-center hover:bg-gray-200 transition-colors">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-gray-700" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                          clip-rule="evenodd" />
                      </svg>
                    </button>
                    <span class="text-sm font-bold text-gray-900 w-4 text-center">{{ item.quantity }}</span>
                    <button @click.stop="incrementCart(item.id)"
                      class="w-6 h-6 rounded-full bg-white flex items-center justify-center hover:bg-gray-200 transition-colors">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-gray-700" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                          d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                          clip-rule="evenodd" />
                      </svg>
                    </button>
                  </div>
                  <span class="text-sm font-medium text-gray-900">
                    {{ item.name }} {{ item.variant !== 'food' ? `(${item.variant})` : '' }}
                  </span>
                </div>
                <span class="text-sm font-bold text-gray-900">Rp{{ formatPrice(item.price * item.quantity) }}</span>
              </div>
            </div>
          </button>
        </div>
      </footer>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const route = useRoute();

// Terima tableId sebagai prop
const props = defineProps({
  tableId: {
    type: String,
    required: true
  }
});

const activeTab = ref('all');
const cartItems = ref(JSON.parse(localStorage.getItem('cart') || '[]'));
const orderType = ref('Dine In');
const tableNumber = ref('Loading...');
const tableInfo = ref(null);
const reservationInfo = ref('');

// Dynamic data
const products = ref([]);
const categories = ref([]);
const isLoading = ref(true);
const error = ref(null);

// API base URL
const API_URL = 'http://localhost:8000/api/guest';

// Fetch table info menggunakan tableId dari prop
const fetchTableInfo = async () => {
  try {
    const response = await axios.get(`${API_URL}/table-info/${props.tableId}`);
    if (response.data.success) {
      tableInfo.value = response.data.data;
      tableNumber.value = response.data.data.table_number;
      localStorage.setItem('currentTableId', props.tableId);
      localStorage.setItem('currentTableNumber', response.data.data.table_number);
    }
  } catch (err) {
    console.error('Error fetching table info:', err);
    error.value = err.response?.data?.message || 'Gagal memuat informasi meja';
  }
};

// Fetch data on mount
onMounted(async () => {
  await fetchTableInfo();

  const storedOrderType = localStorage.getItem('orderType');
  if (storedOrderType) {
    orderType.value = storedOrderType;
  }

  if (orderType.value === 'Reservasi') {
    const reservationDetails = localStorage.getItem('reservationDetails');
    if (reservationDetails) {
      const details = JSON.parse(reservationDetails);
      reservationInfo.value = `${details.people} orang - ${details.date} ${details.time}`;
    }
  }

  await Promise.all([fetchCategories(), fetchProducts()]);
});

// Fetch categories
const fetchCategories = async () => {
  try {
    const response = await axios.get(`${API_URL}/categories`);
    if (response.data.success) {
      categories.value = response.data.data;
    }
  } catch (err) {
    console.error('Error fetching categories:', err);
  }
};

// Fetch products
const fetchProducts = async () => {
  try {
    isLoading.value = true;
    error.value = null;

    const response = await axios.get(`${API_URL}/products`);

    if (response.data.success) {
      products.value = response.data.data;
    }
  } catch (err) {
    console.error('Error fetching products:', err);
    error.value = 'Gagal memuat produk. Silakan coba lagi.';
  } finally {
    isLoading.value = false;
  }
};

// Fix image URL
const getProductImageUrl = (imagePath) => {
  if (!imagePath) return 'https://images.unsplash.com/photo-1517487881594-2787fef5ebf7?w=400&q=80';

  if (imagePath.startsWith('http')) {
    return imagePath;
  }

  const cleanPath = imagePath.replace('public/', '');
  return `http://localhost:8000/storage/${cleanPath}`;
};

// Handle image error
const handleImageError = (e) => {
  e.target.src = 'https://images.unsplash.com/photo-1517487881594-2787fef5ebf7?w=400&q=80';
};

// Watch cart changes
watch(cartItems, (newCart) => {
  localStorage.setItem('cart', JSON.stringify(newCart));
}, { deep: true });

const formatPrice = (price) => {
  if (price === undefined || price === null) return '0';
  return parseFloat(price).toLocaleString('id-ID');
};

const handleProductClick = (product) => {
  if (product.type === 'food') {
    addToCart(product);
  } else if (product.type === 'drink') {
    router.push(`/order/${props.tableId}/product/${product.id}`);
  }
};

const addToCart = (product) => {
  const cartItemId = product.id.toString();
  const existingItem = cartItems.value.find(item => item.id === cartItemId);

  if (existingItem) {
    existingItem.quantity++;
  } else {
    cartItems.value.push({
      id: cartItemId,
      name: product.name,
      price: product.price,
      quantity: 1,
      imageUrl: getProductImageUrl(product.image),
      productId: product.id,
      variant: 'food'
    });
  }
};

const incrementCart = (id) => {
  const item = cartItems.value.find(i => i.id === id);
  if (item) {
    item.quantity++;
  }
};

const decrementCart = (id) => {
  const item = cartItems.value.find(i => i.id === id);
  if (item && item.quantity > 1) {
    item.quantity--;
  } else if (item && item.quantity === 1) {
    cartItems.value = cartItems.value.filter(i => i.id !== id);
  }
};

const goToCart = () => {
  router.push(`/order/${props.tableId}/cart`);
};

const filteredProducts = computed(() => {
  if (activeTab.value === 'all') {
    return products.value;
  }
  return products.value.filter(p => p.category.id === activeTab.value);
});

const activeTabTitle = computed(() => {
  if (activeTab.value === 'all') return 'ALL MENU';
  const category = categories.value.find(c => c.id === activeTab.value);
  return category ? category.name.toUpperCase() : '';
});

const activeTabSubtitle = computed(() => {
  if (activeTab.value === 'all') return 'OUR SIGNATURES';
  const category = categories.value.find(c => c.id === activeTab.value);
  if (!category) return '';
  return category.name.toLowerCase().includes('drink') ? 'COFFEE & MOCKTAILS' : 'PASTRY & SNACKS';
});

const totalItems = computed(() => {
  return cartItems.value.reduce((sum, item) => sum + item.quantity, 0);
});

const totalPrice = computed(() => {
  const total = cartItems.value.reduce((sum, item) => {
    return sum + (item.price * item.quantity);
  }, 0);
  return formatPrice(total);
});
</script>

<style scoped>
.sticky {
  position: -webkit-sticky;
  position: sticky;
  top: 0;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>