<template>
  <div class="bg-neutral-100 min-h-screen">
    <div
      class="max-w-md mx-auto bg-white min-h-screen font-sans relative shadow-2xl"
    >
      <div class="relative">
        <img
          src="https://images.unsplash.com/photo-1511920170033-f8396924c348?w=800&q=80"
          alt="Coffee brewing setup"
          class="w-full h-48 object-cover"
        />
        <button
          class="absolute top-4 right-4 w-10 h-10 bg-white rounded-full shadow-lg flex items-center justify-center hover:bg-gray-50 transition-colors"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5 text-gray-700"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
            />
          </svg>
        </button>
      </div>

      <div class="px-4 py-3 bg-white">
        <h1 class="text-2xl font-bold text-gray-900">Tama Caffeine</h1>
        <p class="text-sm text-gray-500">Open today, 07:00-22:00</p>
      </div>

      <div
        v-if="orderType === 'Reservasi'"
        class="bg-blue-50 py-4 text-center border-b border-blue-100"
      >
        <p class="text-blue-800 font-semibold">Status: Reservasi</p>
        <p class="text-xs text-blue-600 mt-1">{{ reservationInfo }}</p>
      </div>
      <div v-else class="bg-amber-50 py-4 text-center">
        <p class="text-amber-800 font-semibold">
          Table Number: {{ tableNumber }}
        </p>
      </div>

      <div v-if="isLoading" class="flex justify-center items-center py-20">
        <div
          class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-600"
        ></div>
      </div>

      <div v-else-if="error" class="px-4 py-8 text-center">
        <p class="text-red-600 mb-4">{{ error }}</p>
        <button
          @click="fetchProducts"
          class="px-6 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700"
        >
          Coba Lagi
        </button>
      </div>

      <template v-else>
        <div class="flex border-b border-gray-200 bg-white sticky top-0 z-10">
          <button
            @click="activeTab = 'all'"
            :class="[
              'flex-1 py-3 font-medium transition-colors',
              activeTab === 'all'
                ? 'text-amber-700 border-b-2 border-amber-700'
                : 'text-gray-500 hover:text-gray-700',
            ]"
          >
            ALL
          </button>
          <button
            v-for="category in categories"
            :key="category.id"
            @click="activeTab = category.id"
            :class="[
              'flex-1 py-3 font-medium transition-colors uppercase',
              activeTab === category.id
                ? 'text-amber-700 border-b-2 border-amber-700'
                : 'text-gray-500 hover:text-gray-700',
            ]"
          >
            {{ category.name }}
          </button>
        </div>

        <main class="px-4 py-6 bg-white pb-32">
          <h2 class="text-lg font-bold text-gray-900 mb-4">
            {{ activeTabTitle }}<br />
            <span class="text-2xl">{{ activeTabSubtitle }}</span>
          </h2>

          <div v-if="filteredProducts.length === 0" class="text-center py-12">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-16 w-16 mx-auto text-gray-300 mb-4"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
              />
            </svg>
            <p class="text-gray-500">Belum ada produk tersedia</p>
          </div>

          <div v-else class="grid grid-cols-2 gap-4">
            <div
              v-for="product in filteredProducts"
              :key="product.id"
              class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow flex flex-col"
            >
              <img
                :src="product.imageUrl"
                :alt="product.name"
                class="w-full h-40 object-cover"
                @error="handleImageError"
              />

              <div class="p-3 flex flex-col flex-grow">
                <h3 class="font-bold text-base text-gray-900 line-clamp-2">
                  {{ product.name }}
                </h3>
                <p class="text-sm text-gray-600 mb-3">
                  Rp{{ formatPrice(product.price) }}
                </p>

                <button
                  @click="handleProductClick(product)"
                  class="w-full py-2 bg-white hover:bg-amber-600 text-amber-600 hover:text-white border border-amber-600 font-medium rounded-md transition-colors text-center mt-auto"
                >
                  Add
                </button>
              </div>
            </div>
          </div>
        </main>
      </template>

      <div
        v-if="isCartOpen && cartItems.length > 0"
        class="fixed inset-0 bg-black/60 z-40 transition-opacity duration-300 backdrop-blur-sm"
        @click="isCartOpen = false"
      ></div>

      <footer
        v-if="cartItems.length > 0"
        class="fixed bottom-0 left-0 right-0 z-50 max-w-md mx-auto"
      >
        <div
          class="bg-white shadow-[0_-4px_20px_rgba(0,0,0,0.15)] rounded-t-2xl overflow-hidden border-t border-gray-100"
        >
          <div
            @click="isCartOpen = !isCartOpen"
            class="flex items-center justify-between p-4 bg-white cursor-pointer hover:bg-gray-50 active:bg-gray-100 transition-colors select-none"
          >
            <div class="flex items-center gap-3">
              <div class="relative p-2 bg-orange-50 rounded-xl text-orange-600">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-6 w-6"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"
                  />
                </svg>
                <span
                  class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold min-w-[18px] h-[18px] flex items-center justify-center rounded-full shadow-sm border border-white"
                >
                  {{ totalItems }}
                </span>
              </div>

              <div class="flex flex-col">
                <span class="text-xs text-gray-500 font-medium"
                  >Total Estimasi</span
                >
                <span class="font-bold text-lg text-gray-900 leading-tight"
                  >Rp{{ totalPrice }}</span
                >
              </div>
            </div>

            <div class="flex items-center gap-2 text-gray-400">
              <span class="text-xs font-medium text-gray-500">{{
                isCartOpen ? "Tutup" : "Lihat Detail"
              }}</span>
              <div
                class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center transition-transform duration-300"
                :class="{ 'rotate-180': isCartOpen }"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-5 w-5"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M5 15l7-7 7 7"
                  />
                </svg>
              </div>
            </div>
          </div>

          <transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="max-h-0 opacity-0"
            enter-to-class="max-h-[70vh] opacity-100"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="max-h-[70vh] opacity-100"
            leave-to-class="max-h-0 opacity-0"
          >
            <div v-if="isCartOpen" class="bg-gray-50 border-t border-gray-100">
              <div
                class="overflow-y-auto custom-scrollbar p-4 space-y-3"
                style="max-height: 50vh"
              >
                <div
                  v-for="item in cartItems"
                  :key="item.id"
                  class="flex items-center justify-between bg-white p-3 rounded-xl shadow-sm border border-gray-200/60"
                >
                  <div class="flex items-center gap-3 flex-1 min-w-0">
                    <div
                      class="flex items-center bg-gray-100 rounded-lg p-1 border border-gray-200"
                    >
                      <button
                        @click.stop="decrementCart(item.id)"
                        class="w-6 h-6 flex items-center justify-center bg-white rounded shadow-sm text-gray-600 hover:text-red-600 active:scale-90 transition-transform"
                      >
                        <span class="text-xs font-bold leading-none">-</span>
                      </button>
                      <span
                        class="text-xs font-bold w-6 text-center text-gray-700"
                        >{{ item.quantity }}</span
                      >
                      <button
                        @click.stop="incrementCart(item.id)"
                        class="w-6 h-6 flex items-center justify-center bg-white rounded shadow-sm text-gray-600 hover:text-green-600 active:scale-90 transition-transform"
                      >
                        <span class="text-xs font-bold leading-none">+</span>
                      </button>
                    </div>

                    <div class="flex flex-col overflow-hidden">
                      <span class="text-sm font-bold text-gray-900 truncate">{{
                        item.name
                      }}</span>
                      <span
                        v-if="item.variant !== 'food'"
                        class="text-[10px] text-gray-500 uppercase tracking-wider"
                        >{{ item.variant }}</span
                      >
                    </div>
                  </div>

                  <span
                    class="text-sm font-bold text-orange-600 ml-2 whitespace-nowrap"
                  >
                    Rp{{ formatPrice(item.price * item.quantity) }}
                  </span>
                </div>
              </div>

              <div class="p-4 bg-white border-t border-gray-100">
                <button
                  @click="goToPayment"
                  class="w-full bg-gradient-to-r from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-orange-200 transform transition-all active:scale-[0.98] flex items-center justify-center gap-2"
                >
                  <span>Lanjut Pembayaran</span>
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M14 5l7 7m0 0l-7 7m7-7H3"
                    />
                  </svg>
                </button>
              </div>
            </div>
          </transition>
        </div>
      </footer>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import axios from "axios";

const router = useRouter();
const route = useRoute();
const activeTab = ref("all");
const cartItems = ref(JSON.parse(localStorage.getItem("cart") || "[]"));
const orderType = ref("Dine In");
const tableNumber = ref("Loading...");
const tableInfo = ref(null);
const reservationInfo = ref("");
const isCartOpen = ref(false);

// Dynamic data
const products = ref([]);
const categories = ref([]);
const isLoading = ref(true);
const error = ref(null);

const API_URL = "http://localhost:8000/api/guest";

const validateReservation = async () => {
  try {
    const reservationDetails = localStorage.getItem("reservationDetails");
    if (reservationDetails) {
      const details = JSON.parse(reservationDetails);

      // Check reservation status from backend
      if (details.reservationId) {
        const response = await axios.get(
          `${API_URL}/reservations/${details.reservationId}/status`
        );
        if (response.data.success) {
          const reservation = response.data.data;

          // Update reservation info with real status
          let statusText = "";
          switch (reservation.status) {
            case "pending":
              statusText = "⏳ Menunggu Konfirmasi";
              break;
            case "confirmed":
              statusText = "✅ Dikonfirmasi";
              break;
            case "rejected":
              statusText = "❌ Ditolak";
              break;
            default:
              statusText = reservation.status;
          }

          reservationInfo.value = `${statusText} - ${details.people} orang - ${details.date} ${details.time}`;

          // If rejected, redirect back
          if (reservation.status === "rejected") {
            alert("Maaf, reservasi Anda ditolak. Silakan buat reservasi baru.");
            router.push("/reservation");
            return;
          }
        }
      } else {
        // Fallback untuk data local
        reservationInfo.value = `${details.people} orang - ${details.date} ${details.time}`;
      }
    }
  } catch (error) {
    console.error("Error validating reservation:", error);
    // Fallback ke data local jika ada masalah dengan backend
    const reservationDetails = localStorage.getItem("reservationDetails");
    if (reservationDetails) {
      const details = JSON.parse(reservationDetails);
      reservationInfo.value = `${details.people} orang - ${details.date} ${details.time}`;
    }
  }
};

const fetchTableInfo = async (tableId) => {
  try {
    const response = await axios.get(`${API_URL}/table-info/${tableId}`);
    if (response.data.success) {
      tableInfo.value = response.data.data;
      tableNumber.value = response.data.data.table_number;
      localStorage.setItem("currentTableId", tableId);
      localStorage.setItem(
        "currentTableNumber",
        response.data.data.table_number
      );
    }
  } catch (err) {
    console.error("Error fetching table info:", err);
    error.value = err.response?.data?.message || "Gagal memuat informasi meja";
  }
};

onMounted(async () => {
  const tableId = route.params.tableId || route.query.table;

  if (tableId) {
    await fetchTableInfo(tableId);
  } else {
    const storedTableId = localStorage.getItem("currentTableId");
    const storedTableNumber = localStorage.getItem("currentTableNumber");
    if (storedTableId && storedTableNumber) {
      tableNumber.value = storedTableNumber;
    } else {
      tableNumber.value = "-";
    }
  }

  const storedOrderType = localStorage.getItem("orderType");
  if (storedOrderType) {
    orderType.value = storedOrderType;

    if (orderType.value === "Reservasi") {
      await validateReservation();
    }
  }

  await Promise.all([fetchCategories(), fetchProducts()]);
});

const fetchCategories = async () => {
  try {
    const response = await axios.get(`${API_URL}/categories`);
    if (response.data.success) categories.value = response.data.data;
  } catch (err) {
    console.error("Error fetching categories:", err);
  }
};

const fetchProducts = async () => {
  try {
    isLoading.value = true;
    error.value = null;
    const response = await axios.get(`${API_URL}/products`);
    if (response.data.success) {
      // Backend sudah mengirim imageUrl
      products.value = response.data.data;
      console.log("Products loaded:", products.value);
    }
  } catch (err) {
    console.error("Error fetching products:", err);
    error.value = "Gagal memuat produk. Silakan coba lagi.";
  } finally {
    isLoading.value = false;
  }
};

const handleImageError = (e) => {
  e.target.src =
    "https://images.unsplash.com/photo-1517487881594-2787fef5ebf7?w=400&q=80";
};

watch(
  cartItems,
  (newCart) => {
    localStorage.setItem("cart", JSON.stringify(newCart));
  },
  { deep: true }
);

const formatPrice = (price) => {
  if (price === undefined || price === null) return "0";
  return parseFloat(price).toLocaleString("id-ID");
};

const handleProductClick = (product) => {
  const tableId = route.query.table || localStorage.getItem("currentTableId");

  if (product.type === "food") {
    addToCart(product);
  } else if (product.type === "drink") {
    router.push(`/order/${tableId}/product/${product.id}`);
  }
};

const addToCart = (product) => {
  const cartItemId = product.id.toString();
  const existingItem = cartItems.value.find((item) => item.id === cartItemId);

  if (existingItem) {
    existingItem.quantity++;
  } else {
    cartItems.value.push({
      id: cartItemId,
      name: product.name,
      price: product.price,
      quantity: 1,
      imageUrl: product.imageUrl,
      productId: product.id,
      variant: "food",
    });
  }
};

const incrementCart = (id) => {
  const item = cartItems.value.find((i) => i.id === id);
  if (item) item.quantity++;
};

const decrementCart = (id) => {
  const item = cartItems.value.find((i) => i.id === id);
  if (item && item.quantity > 1) {
    item.quantity--;
  } else if (item && item.quantity === 1) {
    cartItems.value = cartItems.value.filter((i) => i.id !== id);
    if (cartItems.value.length === 0) isCartOpen.value = false;
  }
};

// FIX: Navigate ke Cart (Your Order) untuk review & edit
const goToPayment = () => {
  // Validasi cart tidak kosong
  if (cartItems.value.length === 0) {
    alert("Keranjang masih kosong. Silakan tambah menu terlebih dahulu.");
    return;
  }

  // Simpan cart sebelum navigate
  localStorage.setItem("cart", JSON.stringify(cartItems.value));

  const tableId =
    route.params.tableId ||
    route.query.table ||
    localStorage.getItem("currentTableId");

  if (!tableId) {
    const fallbackPath =
      orderType.value === "Reservasi" ? "/reservation" : "/simulasi";
    alert("Sesi Anda berakhir. Silakan mulai ulang.");
    router.push(fallbackPath);
    return;
  }

  // Navigate ke Cart (Your Order) untuk review & edit items
  router.push(`/order/${tableId}/cart`);
};

const filteredProducts = computed(() => {
  if (activeTab.value === "all") return products.value;
  return products.value.filter((p) => p.category.id === activeTab.value);
});

const activeTabTitle = computed(() => {
  if (activeTab.value === "all") return "ALL MENU";
  const category = categories.value.find((c) => c.id === activeTab.value);
  return category ? category.name.toUpperCase() : "";
});

const activeTabSubtitle = computed(() => {
  if (activeTab.value === "all") return "OUR SIGNATURES";
  const category = categories.value.find((c) => c.id === activeTab.value);
  if (!category) return "";
  return category.name.toLowerCase().includes("drink")
    ? "COFFEE & MOCKTAILS"
    : "PASTRY & SNACKS";
});

const totalItems = computed(() => {
  return cartItems.value.reduce((sum, item) => sum + item.quantity, 0);
});

const totalPrice = computed(() => {
  const total = cartItems.value.reduce((sum, item) => {
    return sum + item.price * item.quantity;
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

.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: #f9fafb;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}
</style>
