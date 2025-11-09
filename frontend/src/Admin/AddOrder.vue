<script setup>
import { ref, computed } from 'vue';

// --- DATA CONTOH (Nantinya ambil dari API) ---
// Saya sesuaikan kategorinya agar cocok dengan tab di screenshot
const products = ref([
  // Kategori: Coffee
  { id: 1, name: 'Cappuccino', category: 'Coffee', price: 35000, img: '/img/placeholder.jpg' },
  { id: 2, name: 'Espresso', category: 'Coffee', price: 25000, img: '/img/placeholder.jpg' },
  { id: 3, name: 'Americano', category: 'Coffee', price: 30000, img: '/img/placeholder.jpg' },
  { id: 4, name: 'Caffe Latte', category: 'Coffee', price: 35000, img: '/img/placeholder.jpg' },
  { id: 5, name: 'Mocha', category: 'Coffee', price: 38000, img: '/img/placeholder.jpg' },
  
  // Kategori: Tea
  { id: 6, name: 'Green Tea Latte', category: 'Tea', price: 30000, img: '/img/placeholder.jpg' },
  { id: 7, name: 'Thai Tea', category: 'Tea', price: 28000, img: '/img/placeholder.jpg' },
  { id: 8, name: 'Lemon Tea', category: 'Tea', price: 25000, img: '/img/placeholder.jpg' },
  
  // Kategori: Food
  { id: 9, name: 'Nasi Goreng Special', category: 'Food', price: 45000, img: '/img/placeholder.jpg' },
  { id: 10, name: 'Spaghetti Carbonara', category: 'Food', price: 55000, img: '/img/placeholder.jpg' },
  
  // Kategori: Snack
  { id: 11, name: 'French Fries', category: 'Snack', price: 25000, img: '/img/placeholder.jpg' },
  { id: 12, name: 'Croissant', category: 'Snack', price: 20000, img: '/img/placeholder.jpg' },
]);

// State untuk keranjang (cart)
const cart = ref([
  // Contoh item di keranjang
  { id: 1, name: 'Cappuccino', category: 'Coffee', price: 35000, img: '/img/placeholder.jpg', quantity: 3 },
]);

// State untuk tab kategori
const activeTab = ref('Coffee');
const categories = ['All', 'Coffee', 'Tea', 'Food', 'Snack'];

// State untuk search
const searchTerm = ref('');

// Filter produk berdasarkan tab dan search
const filteredProducts = computed(() => {
  let items = products.value;

  // 1. Filter by Tab
  if (activeTab.value !== 'All') {
    items = items.filter(p => p.category === activeTab.value);
  }

  // 2. Filter by Search Term
  if (searchTerm.value) {
    const searchLower = searchTerm.value.toLowerCase();
    items = items.filter(p => 
      p.name.toLowerCase().includes(searchLower)
    );
  }
  return items;
});

// Helper format mata uang
const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(value);
};

// --- LOGIKA KERANJANG ---
const addToCart = (product) => {
  const itemInCart = cart.value.find(item => item.id === product.id);
  if (itemInCart) {
    itemInCart.quantity++;
  } else {
    cart.value.push({ ...product, quantity: 1 });
  }
};

const incrementQuantity = (itemId) => {
  const item = cart.value.find(item => item.id === itemId);
  if (item) item.quantity++;
};

const decrementQuantity = (itemId) => {
  const item = cart.value.find(item => item.id === itemId);
  if (item && item.quantity > 1) {
    item.quantity--;
  } else if (item && item.quantity === 1) {
    removeFromCart(itemId);
  }
};

const removeFromCart = (itemId) => {
  cart.value = cart.value.filter(item => item.id !== itemId);
};

// --- LOGIKA KALKULASI TOTAL ---
const cartSubtotal = computed(() => {
  return cart.value.reduce((total, item) => total + (item.price * item.quantity), 0);
});

const cartTax = computed(() => {
  return cartSubtotal.value * 0.10; // Tax 10%
});

const cartDiscount = ref(0); // Bisa diubah nanti

const cartTotal = computed(() => {
  return (cartSubtotal.value + cartTax.value) - cartDiscount.value;
});

// Fungsi untuk tombol "Continue to Payment"
const handlePayment = () => {
  if (cart.value.length === 0) {
    alert('Keranjang masih kosong!');
    return;
  }
  console.log('Order Details:', {
    items: cart.value,
    subtotal: cartSubtotal.value,
    tax: cartTax.value,
    total: cartTotal.value,
  });
  // Nanti arahkan ke halaman pembayaran
  // router.push('/admin/payment');
  alert('Lanjut ke Pembayaran!');
};
</script>

<template>
  <div class="flex flex-col md:flex-row gap-8">

    <div class="w-full md:w-[60%] lg:w-2/3 space-y-6">
      
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Menu</h1>
        <p class="text-gray-600">Choose your order</p>
      </div>

      <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
          <i class="fas fa-search text-gray-400"></i>
        </div>
        <input
          type="text"
          v-model="searchTerm"
          placeholder="Search for coffee, tea, food..."
          class="pl-12 pr-4 py-3 border border-gray-200 rounded-xl w-full focus:ring-amber-500 focus:border-amber-500 shadow-sm"
        />
      </div>

      <div class="border-b border-gray-200">
        <nav class="flex space-x-4 -mb-px overflow-x-auto">
          <button
            v-for="category in categories"
            :key="category"
            @click="activeTab = category"
            :class="[
              'py-3 px-2 text-base font-medium whitespace-nowrap transition-colors',
              activeTab === category
                ? 'text-amber-700 border-b-2 border-amber-700'
                : 'text-gray-500 hover:text-gray-700'
            ]"
          >
            {{ category }}
          </button>
        </nav>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <div 
          v-for="product in filteredProducts" 
          :key="product.id"
          class="bg-white border border-gray-200 rounded-xl shadow-sm flex flex-col items-center text-center p-4">
          
          <div class="w-full h-28 bg-gray-100 rounded-lg mb-3 flex items-center justify-center">
            <i class="fas fa-mug-hot text-gray-400 text-4xl"></i>
          </div>

          <h3 class="font-semibold text-gray-800 flex-1">{{ product.name }}</h3>
          <p class="text-sm text-gray-600 mt-1">{{ formatCurrency(product.price) }}</p>
          <button
            @click="addToCart(product)"
            class="mt-3 w-full px-3 py-2 bg-amber-500 text-amber-900 rounded-lg hover:bg-amber-600 transition shadow-sm text-sm font-semibold"
          >
            Add
          </button>
        </div>
        
        <div v-if="filteredProducts.length === 0" class="col-span-full text-center py-10 text-gray-500">
          <p>No products found matching "{{ searchTerm }}".</p>
        </div>
      </div>
    </div>

    <div class="w-full md:w-[40%] lg:w-1/3 align-self-start sticky top-8">
      <div class="bg-white border border-gray-200 rounded-xl shadow-sm flex flex-col h-full">
        
        <div class="p-5 border-b border-gray-200">
          <h2 class="text-2xl font-semibold text-gray-900">Order</h2>
          <p class="text-gray-500">Order #001</p>
        </div>
        
        <div class="flex-1 p-5 space-y-4 overflow-y-auto max-h-[45vh]">
          <template v-if="cart.length === 0">
            <p class="text-center text-gray-500 py-10">Your order is empty.</p>
          </template>
          
          <template v-else>
            <div v-for="item in cart" :key="item.id" class="flex items-center gap-4">
              <div class="w-14 h-14 bg-gray-100 rounded-lg flex-shrink-0 flex items-center justify-center">
                 <i class="fas fa-mug-hot text-gray-400"></i>
              </div>
              
              <div class="flex-1">
                <h4 class="font-medium text-sm text-gray-900">{{ item.name }}</h4>
                <p class="text-xs text-gray-600">{{ formatCurrency(item.price) }}</P>
              </div>
              
              <div class="flex items-center gap-2 flex-shrink-0">
                <button @click="decrementQuantity(item.id)" class="w-6 h-6 rounded-md bg-gray-200 hover:bg-gray-300 text-gray-700">-</button>
                <span class="w-6 text-center text-sm font-medium">{{ item.quantity }}</span>
                <button @click="incrementQuantity(item.id)" class="w-6 h-6 rounded-md bg-gray-200 hover:bg-gray-300 text-gray-700">+</button>
              </div>

              <button @click="removeFromCart(item.id)" class="text-red-500 hover:text-red-700 flex-shrink-0">
                <i class="fas fa-trash-alt"></i>
              </button>
            </div>
          </template>
        </div>
        
        <div class="p-5 border-t border-gray-200 mt-auto space-y-3">
          <div class="flex justify-between items-center text-gray-700">
            <span>Subtotal:</span>
            <span class="font-medium">{{ formatCurrency(cartSubtotal) }}</span>
          </div>
          <div class="flex justify-between items-center text-gray-700">
            <span>Tax (10%):</span>
            <span class="font-medium">{{ formatCurrency(cartTax) }}</span>
          </div>
          <div class="flex justify-between items-center text-gray-700">
            <span>Discount:</span>
            <span class="font-medium">{{ formatCurrency(cartDiscount) }}</span>
            </div>

          <hr class="border-gray-200 border-dashed my-2">

          <div class="flex justify-between items-center text-xl font-bold">
            <span class="text-gray-900">Total:</span>
            <span class="text-amber-800">{{ formatCurrency(cartTotal) }}</span>
          </div>
        </div>

        <div class="p-5 border-t border-gray-200">
          <button 
            @click="handlePayment"
            :disabled="cart.length === 0"
            class="w-full px-5 py-3.5 bg-amber-700 text-white rounded-lg hover:bg-amber-800 transition shadow-sm font-semibold disabled:bg-gray-400 disabled:cursor-not-allowed">
            Continue to Payment
          </button>
        </div>

      </div>
    </div>

  </div>
</template>