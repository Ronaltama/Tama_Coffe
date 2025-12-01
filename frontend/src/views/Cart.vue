<template>
  <div class="bg-neutral-50 min-h-screen flex flex-col">
    <div
      class="max-w-md mx-auto bg-white w-full min-h-screen flex flex-col relative shadow-xl"
    >
      <!-- Header -->
      <header
        class="bg-white border-b border-gray-100 flex-none sticky top-0 z-20"
      >
        <div class="flex items-center justify-between px-4 py-4">
          <router-link
            :to="`/order/${tableId}/menu`"
            class="p-2 -ml-2 text-gray-800 hover:text-orange-600 transition-colors"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-6 w-6"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="2.5"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M15 19l-7-7 7-7"
              />
            </svg>
          </router-link>

          <h1 class="text-xl font-bold text-gray-900">Your Order</h1>
          <div class="w-8"></div>
        </div>
      </header>

      <!-- Main Content -->
      <main class="flex-1 overflow-y-auto">
        <!-- Cart Items -->
        <div v-if="cartItems.length > 0" class="p-4">
          <!-- Items Section -->
          <div class="mb-6">
            <h2
              class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-4"
            >
              Items
            </h2>

            <div class="space-y-3">
              <div
                v-for="item in cartItems"
                :key="item.id"
                class="flex items-center gap-4 p-3 bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow"
              >
                <!-- Product Image -->
                <div
                  class="w-20 h-20 rounded-xl overflow-hidden flex-shrink-0 bg-gray-50"
                >
                  <img
                    :src="item.imageUrl"
                    :alt="item.name"
                    class="w-full h-full object-cover"
                    @error="handleImageError"
                  />
                </div>

                <!-- Product Info -->
                <div class="flex-1 min-w-0">
                  <h3 class="text-base font-bold text-gray-900 mb-1">
                    {{ item.name }}
                  </h3>
                  <p
                    v-if="item.variant !== 'food'"
                    class="text-xs text-gray-500 mb-2"
                  >
                    {{ item.variant }}
                  </p>
                  <p class="text-base font-bold text-gray-900">
                    Rp{{ formatPrice(item.price) }}
                  </p>
                </div>

                <!-- Quantity Controls -->
                <div class="flex items-center gap-2">
                  <button
                    @click="decrementCart(item.id)"
                    class="w-9 h-9 rounded-lg flex items-center justify-center transition-all"
                    :class="
                      item.quantity === 1
                        ? 'bg-gray-100 text-gray-400'
                        : 'bg-white border-2 border-orange-600 text-orange-600 hover:bg-orange-50'
                    "
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-4 w-4"
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

                  <span
                    class="text-base font-bold text-gray-900 w-8 text-center"
                    >{{ item.quantity }}</span
                  >

                  <button
                    @click="incrementCart(item.id)"
                    class="w-9 h-9 rounded-lg bg-orange-600 text-white flex items-center justify-center hover:bg-orange-700 transition-colors shadow-sm"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-4 w-4"
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
            </div>
          </div>

          <!-- Notes Section -->
          <div class="mb-6">
            <h2
              class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-4"
            >
              Notes
            </h2>
            <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100">
              <textarea
                v-model="notes"
                rows="3"
                class="w-full bg-transparent text-sm text-gray-700 placeholder-gray-400 focus:outline-none resize-none"
                placeholder="E.g, less ice, please"
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div
          v-else
          class="h-full flex flex-col items-center justify-center text-center p-8 -mt-10"
        >
          <div
            class="w-24 h-24 bg-orange-50 rounded-full flex items-center justify-center mb-4"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-12 w-12 text-orange-400"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"
              />
            </svg>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-2">Cart is Empty</h3>
          <p class="text-gray-500 text-sm mb-6 max-w-xs">
            You haven't added any items yet.
          </p>
          <router-link
            :to="`/order/${tableId}/menu`"
            class="px-8 py-3 bg-orange-600 text-white rounded-xl font-bold text-sm hover:bg-orange-700 transition-colors shadow-lg"
          >
            Start Ordering
          </router-link>
        </div>
      </main>

      <!-- Footer -->
      <footer
        v-if="cartItems.length > 0"
        class="bg-white border-t border-gray-100 p-4 flex-none shadow-[0_-4px_12px_rgba(0,0,0,0.05)]"
      >
        <div class="mb-4">
          <div class="flex justify-between items-center">
            <span class="text-base font-bold text-gray-900">Total</span>
            <span class="text-2xl font-bold text-gray-900"
              >Rp{{ totalPrice }}</span
            >
          </div>
        </div>

        <button
          @click="goToPayment"
          class="w-full py-4 bg-orange-600 hover:bg-orange-700 text-white text-base font-bold rounded-xl transition-all shadow-lg active:scale-[0.98]"
        >
          Place Order
        </button>
      </footer>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { useRouter, useRoute } from "vue-router";

const router = useRouter();

// Props untuk tableId
const props = defineProps({
  tableId: {
    type: String,
    required: false,
  },
});

const route = useRoute();

const cartItems = ref(JSON.parse(localStorage.getItem("cart") || "[]"));
const notes = ref(localStorage.getItem("cartNotes") || "");

// Watchers untuk menyimpan state
watch(
  cartItems,
  (newCart) => {
    localStorage.setItem("cart", JSON.stringify(newCart));
  },
  { deep: true }
);

watch(notes, (newNotes) => {
  localStorage.setItem("cartNotes", newNotes);
});

// Handle image error
const handleImageError = (e) => {
  e.target.src =
    "https://images.unsplash.com/photo-1517487881594-2787fef5ebf7?w=400&q=80";
};

// Formatting
const formatPrice = (price) => {
  if (price === undefined || price === null) return "0";
  return price.toLocaleString("id-ID");
};

// Cart Logic
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
  }
};

const goToPayment = () => {
  // Validasi cart tidak boleh kosong
  if (cartItems.value.length === 0) {
    alert("Keranjang masih kosong. Silakan tambah menu terlebih dahulu.");
    return;
  }

  // Simpan cart & notes sebelum navigate
  localStorage.setItem("cart", JSON.stringify(cartItems.value));
  localStorage.setItem("cartNotes", notes.value);

  // Ambil tableId dengan prioritas: props, route params, localStorage
  const tableId =
    props.tableId ||
    route.params.tableId ||
    localStorage.getItem("currentTableId");

  if (!tableId) {
    const orderType = localStorage.getItem("orderType");
    const fallbackPath =
      orderType === "Reservasi" ? "/reservation" : "/simulasi";
    alert("Sesi Anda berakhir. Silakan mulai ulang.");
    router.push(fallbackPath);
    return;
  }

  // Navigate langsung ke payment form
  router.push(`/order/${tableId}/payment`);
};

const totalPrice = computed(() => {
  const total = cartItems.value.reduce((sum, item) => {
    return sum + item.price * item.quantity;
  }, 0);
  return formatPrice(total);
});
</script>

<style scoped>
/* Custom scrollbar */
main::-webkit-scrollbar {
  width: 6px;
}
main::-webkit-scrollbar-track {
  background: #f5f5f5;
}
main::-webkit-scrollbar-thumb {
  background-color: #ddd;
  border-radius: 20px;
}
main::-webkit-scrollbar-thumb:hover {
  background-color: #ccc;
}
</style>
