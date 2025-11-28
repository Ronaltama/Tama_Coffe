<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';

const router = useRouter();
const API_BASE = 'http://localhost:8000/api';

// --- STATE ---
const orderType = ref(''); // 'dine-in' atau 'take-away'
const selectedTable = ref(null);
const tables = ref([]);
const products = ref([]);
const categories = ref([]);
const activeTab = ref('All');
const searchTerm = ref('');
const cart = ref([]);
const customerName = ref('');
const customerPhone = ref('');
const orderNote = ref('');
const loading = ref(false);

// Modal untuk memilih varian
const showVariantModal = ref(false);
const selectedProduct = ref(null);
const selectedVariant = ref('');

// --- FETCH DATA ---
const fetchProducts = async () => {
  try {
    const response = await axios.get(`${API_BASE}/guest/products`);
    if (response.data.success) {
      products.value = response.data.data;
      
      // 🐛 DEBUG: Log untuk cek data produk (unpacked dari Proxy)
      console.log('Products loaded:', products.value.length);
      console.log('Sample product:', JSON.parse(JSON.stringify(products.value[0])));
      console.log('All products:', JSON.parse(JSON.stringify(products.value)));
      
      // Pastikan semua produk punya status
      products.value = products.value.map(product => ({
        ...product,
        status: product.status || 'available' // Default ke available jika null
      }));
    }
  } catch (error) {
    console.error('Error fetching products:', error);
    Swal.fire({
      icon: 'error',
      title: 'Gagal Memuat Data',
      text: 'Tidak dapat memuat daftar produk. Silakan refresh halaman.',
    });
  }
};

const fetchCategories = async () => {
  try {
    const response = await axios.get(`${API_BASE}/guest/categories`);
    if (response.data.success) {
      categories.value = ['All', ...response.data.data.map(cat => cat.name)];
      console.log('Categories loaded:', JSON.parse(JSON.stringify(categories.value)));
    }
  } catch (error) {
    console.error('Error fetching categories:', error);
  }
};

const fetchTables = async () => {
  try {
    // Gunakan endpoint khusus untuk manual order yang sudah filter available
    const response = await axios.get(`${API_BASE}/orders/manual/available-tables`);
    if (response.data.success) {
      tables.value = response.data.data;
    }
  } catch (error) {
    console.error('Error fetching tables:', error);
    Swal.fire({
      icon: 'error',
      title: 'Gagal Memuat Data',
      text: 'Tidak dapat memuat daftar meja. Silakan refresh halaman.',
    });
  }
};

onMounted(() => {
  fetchProducts();
  fetchCategories();
  fetchTables();
});

// --- FILTER PRODUCTS ---
const filteredProducts = computed(() => {
  let items = [...products.value]; // Clone array

  console.log('=== FILTERING START ===');
  console.log('Active Tab:', activeTab.value);
  console.log('Total products:', items.length);

  // Filter by category
  if (activeTab.value !== 'All') {
    items = items.filter(p => {
      const match = p.category_name === activeTab.value;
      console.log(`Product: ${p.name}, Category: "${p.category_name}", Tab: "${activeTab.value}", Match: ${match}`);
      return match;
    });
  }

  // Filter by search
  if (searchTerm.value) {
    const searchLower = searchTerm.value.toLowerCase();
    items = items.filter(p => 
      p.name.toLowerCase().includes(searchLower)
    );
  }

  console.log('Filtered products:', items.length);
  console.log('=== FILTERING END ===');
  return items;
});

// --- HELPER ---
const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(value);
};

// Cek apakah produk adalah minuman (butuh varian)
const isDrink = (categoryName) => {
  if (!categoryName) return false;
  const lowerCategory = categoryName.toLowerCase();
  console.log('Checking isDrink for:', categoryName, '→', lowerCategory === 'drink');
  // Hanya kategori Drink yang butuh varian
  return lowerCategory === 'drink';
};

// --- CART LOGIC ---
const handleAddToCart = (product) => {
  // Jika produk adalah minuman, tampilkan modal untuk pilih varian
  if (isDrink(product.category_name)) {
    selectedProduct.value = product;
    selectedVariant.value = 'Ice'; // Default
    showVariantModal.value = true;
  } else {
    // Langsung tambahkan ke cart tanpa varian
    addToCart(product, null);
  }
};

const confirmVariant = () => {
  if (selectedProduct.value && selectedVariant.value) {
    addToCart(selectedProduct.value, selectedVariant.value);
    showVariantModal.value = false;
    selectedProduct.value = null;
    selectedVariant.value = '';
  }
};

const addToCart = (product, variant = null) => {
  const cartKey = variant ? `${product.id}-${variant}` : product.id;
  
  const itemInCart = cart.value.find(item => {
    if (variant) {
      return item.id === product.id && item.variant === variant;
    }
    return item.id === product.id && !item.variant;
  });

  if (itemInCart) {
    itemInCart.quantity++;
  } else {
    cart.value.push({
      id: product.id,
      name: product.name,
      category_name: product.category_name,
      price: parseFloat(product.price),
      variant: variant,
      quantity: 1,
    });
  }
};

const incrementQuantity = (index) => {
  cart.value[index].quantity++;
};

const decrementQuantity = (index) => {
  if (cart.value[index].quantity > 1) {
    cart.value[index].quantity--;
  } else {
    removeFromCart(index);
  }
};

const removeFromCart = (index) => {
  cart.value.splice(index, 1);
};

// --- CALCULATION ---
const cartSubtotal = computed(() => {
  return cart.value.reduce((total, item) => total + (item.price * item.quantity), 0);
});

const cartTax = computed(() => {
  return 0; // Tidak ada pajak
});

const cartTotal = computed(() => {
  return cartSubtotal.value + cartTax.value;
});

// --- VALIDATION ---
const canProceedToPayment = computed(() => {
  if (!orderType.value) return false;
  if (orderType.value === 'dine-in' && !selectedTable.value) return false;
  if (cart.value.length === 0) return false;
  if (!customerName.value.trim() || !customerPhone.value.trim()) return false;
  return true;
});

// --- PAYMENT CALCULATOR ---
const showPaymentModal = ref(false);
const paymentAmount = ref('');
const paymentMethod = ref('cash');

const paymentChange = computed(() => {
  const amount = parseFloat(paymentAmount.value) || 0;
  return amount - cartTotal.value;
});

const handlePayment = () => {
  if (!canProceedToPayment.value) {
    Swal.fire({
      icon: 'warning',
      title: 'Data Tidak Lengkap',
      text: 'Mohon lengkapi semua data order terlebih dahulu!',
    });
    return;
  }
  showPaymentModal.value = true;
  paymentAmount.value = cartTotal.value.toString();
};

const quickAmount = (amount) => {
  paymentAmount.value = amount.toString();
};

const processPayment = async () => {
  if (paymentMethod.value === 'cash') {
    const amount = parseFloat(paymentAmount.value) || 0;
    if (amount < cartTotal.value) {
      Swal.fire({
        icon: 'error',
        title: 'Pembayaran Kurang',
        text: `Jumlah pembayaran kurang Rp ${formatCurrency(cartTotal.value - amount)}`,
      });
      return;
    }
  }

  loading.value = true;

  try {
    // Prepare order data
    const orderData = {
      order_type: orderType.value,
      table_id: orderType.value === 'dine-in' ? selectedTable.value : null,
      customer_name: customerName.value,
      customer_phone: customerPhone.value,
      note: orderNote.value,
      items: cart.value.map(item => ({
        product_id: item.id,
        quantity: item.quantity,
        variant: item.variant,
        unit_price: item.price,
      })),
      payment: {
        method: paymentMethod.value,
        amount: paymentMethod.value === 'cash' ? parseFloat(paymentAmount.value) : cartTotal.value,
      }
    };

    const response = await axios.post(`${API_BASE}/orders/manual`, orderData);

    if (response.data.success) {
      showPaymentModal.value = false;
      
      await Swal.fire({
        icon: 'success',
        title: 'Order Berhasil!',
        text: `Order #${response.data.data.order_id} telah dibuat`,
        timer: 2000,
        showConfirmButton: false,
      });

      // Redirect ke detail order
      router.push(`/admin/order-detail/${response.data.data.order_id}`);
    }
  } catch (error) {
    console.error('Error processing order:', error);
    Swal.fire({
      icon: 'error',
      title: 'Gagal Memproses Order',
      text: error.response?.data?.message || 'Terjadi kesalahan saat memproses order',
    });
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Tambah Order Manual</h1>
        <p class="text-gray-600 mt-1">Buat order baru untuk customer</p>
      </div>
    </div>

    <!-- Step 1: Pilih Tipe Order -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <h2 class="text-xl font-semibold text-gray-900 mb-4">1. Tipe Order</h2>
      <div class="grid grid-cols-2 gap-4 max-w-md">
        <button
          @click="orderType = 'dine-in'"
          :class="[
            'p-4 rounded-lg border-2 transition-all',
            orderType === 'dine-in'
              ? 'border-amber-500 bg-amber-50 text-amber-700'
              : 'border-gray-200 hover:border-gray-300'
          ]"
        >
          <i class="fas fa-utensils text-2xl mb-2"></i>
          <div class="font-semibold">Dine In</div>
        </button>
        <button
          @click="orderType = 'take-away'; selectedTable = null;"
          :class="[
            'p-4 rounded-lg border-2 transition-all',
            orderType === 'take-away'
              ? 'border-amber-500 bg-amber-50 text-amber-700'
              : 'border-gray-200 hover:border-gray-300'
          ]"
        >
          <i class="fas fa-shopping-bag text-2xl mb-2"></i>
          <div class="font-semibold">Take Away</div>
        </button>
      </div>
    </div>

    <!-- Step 2: Pilih Meja (jika dine-in) -->
    <div v-if="orderType === 'dine-in'" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <h2 class="text-xl font-semibold text-gray-900 mb-4">2. Pilih Meja</h2>
      <div class="grid grid-cols-3 md:grid-cols-5 lg:grid-cols-7 gap-3">
        <button
          v-for="table in tables"
          :key="table.id"
          @click="selectedTable = table.id"
          :class="[
            'p-4 rounded-lg border-2 transition-all text-center',
            selectedTable === table.id
              ? 'border-amber-500 bg-amber-50'
              : 'border-gray-200 hover:border-gray-300'
          ]"
        >
          <div class="font-bold text-lg">{{ table.table_number }}</div>
          <div class="text-xs text-gray-600">{{ table.capacity }} orang</div>
        </button>
      </div>
      <p v-if="tables.length === 0" class="text-gray-500 text-center py-4">
        Tidak ada meja tersedia
      </p>
    </div>

    <!-- Step 3: Data Customer -->
    <div v-if="orderType" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <h2 class="text-xl font-semibold text-gray-900 mb-4">
        {{ orderType === 'dine-in' ? '3' : '2' }}. Data Customer
      </h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-2xl">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Nama Customer</label>
          <input
            v-model="customerName"
            type="text"
            placeholder="Masukkan nama customer"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-amber-500 focus:border-amber-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">No. Telepon</label>
          <input
            v-model="customerPhone"
            type="tel"
            placeholder="08xxxxxxxxxx"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-amber-500 focus:border-amber-500"
          />
        </div>
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
          <textarea
            v-model="orderNote"
            rows="2"
            placeholder="Tambahkan catatan untuk order ini..."
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-amber-500 focus:border-amber-500"
          ></textarea>
        </div>
      </div>
    </div>

    <!-- Step 4: Pilih Produk -->
    <div v-if="orderType" class="flex flex-col lg:flex-row gap-6">
      <!-- Product List -->
      <div class="flex-1 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
          {{ orderType === 'dine-in' ? '4' : '3' }}. Pilih Menu
        </h2>

        <!-- Search -->
        <div class="relative mb-4">
          <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <i class="fas fa-search text-gray-400"></i>
          </div>
          <input
            type="text"
            v-model="searchTerm"
            placeholder="Cari menu..."
            class="pl-12 pr-4 py-3 border border-gray-200 rounded-xl w-full focus:ring-amber-500 focus:border-amber-500"
          />
        </div>

        <!-- Category Tabs -->
        <div class="border-b border-gray-200 mb-4">
          <nav class="flex space-x-4 -mb-px overflow-x-auto">
            <button
              v-for="category in categories"
              :key="category"
              @click="activeTab = category"
              :class="[
                'py-3 px-2 text-sm font-medium whitespace-nowrap transition-colors',
                activeTab === category
                  ? 'text-amber-700 border-b-2 border-amber-700'
                  : 'text-gray-500 hover:text-gray-700'
              ]"
            >
              {{ category }}
            </button>
          </nav>
        </div>

        <!-- Products Grid -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 max-h-[600px] overflow-y-auto">
          <div
            v-for="product in filteredProducts"
            :key="product.id"
            class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 flex flex-col"
          >
            <div class="w-full h-24 bg-gray-100 rounded-lg mb-3 flex items-center justify-center">
              <i class="fas fa-mug-hot text-gray-400 text-3xl"></i>
            </div>
            <h3 class="font-semibold text-sm text-gray-800 flex-1">{{ product.name }}</h3>
            <p class="text-sm text-gray-600 mt-1">{{ formatCurrency(product.price) }}</p>
            <button
              @click="handleAddToCart(product)"
              :disabled="product.status === 'unavailable'"
              class="mt-3 w-full px-3 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition text-sm font-semibold disabled:bg-gray-300 disabled:cursor-not-allowed"
            >
              {{ product.status === 'available' ? 'Tambah' : 'Tidak Tersedia' }}
            </button>
          </div>

          <div v-if="filteredProducts.length === 0" class="col-span-full text-center py-10 text-gray-500">
            <p>Tidak ada menu yang ditemukan</p>
          </div>
        </div>
      </div>

      <!-- Cart -->
      <div class="w-full lg:w-96">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 sticky top-6">
          <div class="p-5 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Keranjang</h2>
            <p class="text-sm text-gray-500">{{ cart.length }} item</p>
          </div>

          <div class="p-5 space-y-3 max-h-[400px] overflow-y-auto">
            <div v-if="cart.length === 0" class="text-center py-10 text-gray-500">
              <i class="fas fa-shopping-cart text-4xl mb-2"></i>
              <p>Keranjang masih kosong</p>
            </div>

            <div
              v-for="(item, index) in cart"
              :key="`${item.id}-${item.variant || 'no-variant'}`"
              class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg"
            >
              <div class="flex-1">
                <h4 class="font-medium text-sm text-gray-900">{{ item.name }}</h4>
                <p v-if="item.variant" class="text-xs text-amber-600">{{ item.variant }}</p>
                <p class="text-xs text-gray-600">{{ formatCurrency(item.price) }}</p>
              </div>

              <div class="flex items-center gap-2">
                <button
                  @click="decrementQuantity(index)"
                  class="w-7 h-7 rounded-md bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm"
                >
                  -
                </button>
                <span class="w-8 text-center text-sm font-medium">{{ item.quantity }}</span>
                <button
                  @click="incrementQuantity(index)"
                  class="w-7 h-7 rounded-md bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm"
                >
                  +
                </button>
              </div>

              <button
                @click="removeFromCart(index)"
                class="text-red-500 hover:text-red-700 ml-2"
              >
                <i class="fas fa-trash-alt text-sm"></i>
              </button>
            </div>
          </div>

          <div class="p-5 border-t border-gray-200 space-y-3">
            <div class="flex justify-between text-sm text-gray-700">
              <span>Subtotal:</span>
              <span class="font-medium">{{ formatCurrency(cartSubtotal) }}</span>
            </div>
            <hr class="border-gray-200">
            <div class="flex justify-between text-lg font-bold">
              <span>Total:</span>
              <span class="text-amber-700">{{ formatCurrency(cartTotal) }}</span>
            </div>

            <button
              @click="handlePayment"
              :disabled="!canProceedToPayment"
              class="w-full px-5 py-3 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition font-semibold disabled:bg-gray-300 disabled:cursor-not-allowed"
            >
              Proses Pembayaran
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal: Pilih Varian -->
    <div v-if="showVariantModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Pilih Varian</h3>
        <p class="text-gray-600 mb-4">{{ selectedProduct?.name }}</p>
        
        <div class="space-y-2 mb-6">
          <label class="flex items-center gap-3 p-3 border-2 rounded-lg cursor-pointer hover:bg-gray-50"
            :class="selectedVariant === 'Ice' ? 'border-amber-500 bg-amber-50' : 'border-gray-200'">
            <input type="radio" v-model="selectedVariant" value="Ice" class="text-amber-600">
            <span class="font-medium">Ice</span>
          </label>
          <label class="flex items-center gap-3 p-3 border-2 rounded-lg cursor-pointer hover:bg-gray-50"
            :class="selectedVariant === 'Hot' ? 'border-amber-500 bg-amber-50' : 'border-gray-200'">
            <input type="radio" v-model="selectedVariant" value="Hot" class="text-amber-600">
            <span class="font-medium">Hot</span>
          </label>
        </div>

        <div class="flex gap-3">
          <button
            @click="showVariantModal = false"
            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
          >
            Batal
          </button>
          <button
            @click="confirmVariant"
            class="flex-1 px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700"
          >
            Tambahkan
          </button>
        </div>
      </div>
    </div>

    <!-- Modal: Pembayaran -->
    <div v-if="showPaymentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Proses Pembayaran</h3>

        <!-- Total -->
        <div class="bg-amber-50 rounded-lg p-4 mb-4">
          <div class="text-sm text-gray-600 mb-1">Total Pembayaran</div>
          <div class="text-2xl font-bold text-amber-700">{{ formatCurrency(cartTotal) }}</div>
        </div>

        <!-- Payment Method -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Metode Pembayaran</label>
          <div class="grid grid-cols-2 gap-2">
            <button
              @click="paymentMethod = 'cash'"
              :class="[
                'p-3 rounded-lg border-2 text-sm font-medium',
                paymentMethod === 'cash'
                  ? 'border-amber-500 bg-amber-50 text-amber-700'
                  : 'border-gray-200 hover:border-gray-300'
              ]"
            >
              Cash
            </button>
            <button
              @click="paymentMethod = 'qris'"
              :class="[
                'p-3 rounded-lg border-2 text-sm font-medium',
                paymentMethod === 'qris'
                  ? 'border-amber-500 bg-amber-50 text-amber-700'
                  : 'border-gray-200 hover:border-gray-300'
              ]"
            >
              QRIS
            </button>
          </div>
        </div>

        <!-- Input Jumlah (untuk cash) -->
        <div v-if="paymentMethod === 'cash'" class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Bayar</label>
          <input
            v-model="paymentAmount"
            type="number"
            placeholder="0"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-amber-500 focus:border-amber-500 text-lg font-semibold"
          />
          
          <!-- Quick Amount Buttons -->
          <div class="grid grid-cols-3 gap-2 mt-2">
            <button
              v-for="amount in [50000, 100000, 150000]"
              :key="amount"
              @click="quickAmount(amount)"
              class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium"
            >
              {{ formatCurrency(amount) }}
            </button>
          </div>

          <!-- Change -->
          <div v-if="paymentChange >= 0" class="mt-3 p-3 bg-green-50 rounded-lg">
            <div class="text-sm text-gray-600">Kembalian</div>
            <div class="text-xl font-bold text-green-700">{{ formatCurrency(paymentChange) }}</div>
          </div>
          <div v-else class="mt-3 p-3 bg-red-50 rounded-lg">
            <div class="text-sm text-red-600">Pembayaran Kurang</div>
            <div class="text-xl font-bold text-red-700">{{ formatCurrency(Math.abs(paymentChange)) }}</div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-3">
          <button
            @click="showPaymentModal = false"
            class="flex-1 px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 font-semibold"
          >
            Batal
          </button>
          <button
            @click="processPayment"
            :disabled="loading"
            class="flex-1 px-4 py-3 bg-amber-600 text-white rounded-lg hover:bg-amber-700 font-semibold disabled:bg-gray-400"
          >
            <span v-if="!loading">Konfirmasi</span>
            <span v-else>
              <i class="fas fa-spinner fa-spin mr-2"></i>
              Memproses...
            </span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>