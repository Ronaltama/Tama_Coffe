<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">History Reservasi</h1>
        <p class="text-sm text-gray-500 mt-1">
          Lihat semua riwayat reservasi dan pesanan
        </p>
      </div>
      <button
        @click="fetchReservations"
        class="p-2 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5 text-gray-600"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
          />
        </svg>
      </button>
    </div>

    <!-- Filter Tanggal -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-4 border border-gray-200">
      <div class="flex flex-wrap items-center gap-4">
        <div class="flex-1 min-w-[200px]">
          <label class="block text-sm font-medium text-gray-700 mb-1"
            >Dari Tanggal</label
          >
          <input
            v-model="filterStartDate"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500"
          />
        </div>
        <div class="flex-1 min-w-[200px]">
          <label class="block text-sm font-medium text-gray-700 mb-1"
            >Sampai Tanggal</label
          >
          <input
            v-model="filterEndDate"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500"
          />
        </div>
        <div class="flex gap-2 items-end">
          <button
            @click="applyFilter"
            class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 font-medium"
          >
            Filter
          </button>
          <button
            @click="resetFilter"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium"
          >
            Reset
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center py-12">
      <div
        class="animate-spin rounded-full h-8 w-8 border-b-2 border-orange-500"
      ></div>
    </div>

    <!-- Empty State -->
    <div
      v-else-if="filteredReservations.length === 0"
      class="text-center py-12 bg-white rounded-xl shadow-sm"
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        class="h-12 w-12 text-gray-300 mx-auto mb-4"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
        />
      </svg>
      <p class="text-gray-500">Tidak ada data reservasi pada periode ini.</p>
    </div>

    <!-- Reservation List (Table Style) -->
    <div v-else class="bg-white rounded-xl shadow-sm overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Booking Code
            </th>
            <th
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Customer
            </th>
            <th
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Tanggal & Waktu
            </th>
            <th
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Meja
            </th>
            <th
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Total
            </th>
            <th
              class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Aksi
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <template
            v-for="reservation in filteredReservations"
            :key="reservation.id"
          >
            <!-- Main Row -->
            <tr
              class="hover:bg-gray-50 cursor-pointer"
              @click="toggleDetail(reservation.id)"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-orange-600">
                  {{ reservation.booking_code }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">
                  {{ reservation.name }}
                </div>
                <div class="text-xs text-gray-500">
                  {{ reservation.phone }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                  {{ formatDateShort(reservation.date) }}
                </div>
                <div class="text-xs text-gray-500">
                  {{ reservation.time }} WIB
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ reservation.table?.table_number || "-" }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div
                  v-if="reservation.order"
                  class="text-sm font-semibold text-gray-900"
                >
                  Rp{{ formatPrice(reservation.order.total_price) }}
                </div>
                <div v-else class="text-xs text-gray-400 italic">
                  Belum order
                </div>
              </td>
              <td
                class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium"
              >
                <button
                  @click.stop="toggleDetail(reservation.id)"
                  class="text-orange-600 hover:text-orange-900"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5 transition-transform"
                    :class="{ 'rotate-180': expandedId === reservation.id }"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M19 9l-7 7-7-7"
                    />
                  </svg>
                </button>
              </td>
            </tr>

            <!-- Detail Row (Expanded) -->
            <tr v-if="expandedId === reservation.id" class="bg-gray-50">
              <td colspan="6" class="px-6 py-4">
                <div class="space-y-4">
                  <!-- Order Details -->
                  <div v-if="reservation.order">
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">
                      Detail Pesanan
                    </h4>
                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                      <div class="flex justify-between items-center mb-3">
                        <span class="text-xs text-gray-500"
                          >Order ID: {{ reservation.order.id }}</span
                        >
                        <span
                          class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-700"
                        >
                          {{ reservation.order.payment_method || "Cash" }}
                        </span>
                      </div>

                      <ul class="space-y-2 mb-3">
                        <li
                          v-for="item in reservation.order.order_details"
                          :key="item.id"
                          class="flex justify-between text-sm"
                        >
                          <span class="text-gray-700">
                            <span class="font-medium"
                              >{{ item.quantity }}x</span
                            >
                            {{ item.product?.name || "Product" }}
                            <span
                              v-if="item.variant"
                              class="text-gray-400 text-xs ml-1"
                              >({{ item.variant }})</span
                            >
                          </span>
                          <span class="text-gray-600"
                            >Rp{{
                              formatPrice(item.price * item.quantity)
                            }}</span
                          >
                        </li>
                      </ul>

                      <div
                        class="pt-3 border-t border-gray-200 flex justify-between items-center"
                      >
                        <span class="text-sm font-semibold text-gray-700"
                          >Total</span
                        >
                        <span class="text-lg font-bold text-orange-600">
                          Rp{{ formatPrice(reservation.order.total_price) }}
                        </span>
                      </div>
                    </div>
                  </div>

                  <!-- No Order Yet -->
                  <div v-else class="text-sm text-gray-400 italic">
                    Belum ada pesanan terkait dengan reservasi ini
                  </div>
                </div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";

const reservations = ref([]);
const isLoading = ref(false);
const expandedId = ref(null);

// Filter tanggal
const filterStartDate = ref("");
const filterEndDate = ref("");

const fetchReservations = async () => {
  isLoading.value = true;
  try {
    const response = await axios.get(
      "http://localhost:8000/api/admin/reservations"
    );
    if (response.data.success) {
      reservations.value = response.data.data;
    }
  } catch (error) {
    console.error("Error fetching reservations:", error);
  } finally {
    isLoading.value = false;
  }
};

// Filtered reservations based on date range
const filteredReservations = computed(() => {
  if (!filterStartDate.value && !filterEndDate.value) {
    return reservations.value;
  }

  return reservations.value.filter((reservation) => {
    const reservationDate = new Date(reservation.date);
    const startDate = filterStartDate.value
      ? new Date(filterStartDate.value)
      : null;
    const endDate = filterEndDate.value ? new Date(filterEndDate.value) : null;

    if (startDate && reservationDate < startDate) return false;
    if (endDate && reservationDate > endDate) return false;

    return true;
  });
});

const applyFilter = () => {
  // Filter sudah otomatis karena computed property
  // Fungsi ini hanya untuk feedback visual jika diperlukan
};

const resetFilter = () => {
  filterStartDate.value = "";
  filterEndDate.value = "";
};

const toggleDetail = (id) => {
  expandedId.value = expandedId.value === id ? null : id;
};

const formatDate = (dateString) => {
  if (!dateString) return "-";
  const options = {
    weekday: "long",
    year: "numeric",
    month: "long",
    day: "numeric",
  };
  return new Date(dateString).toLocaleDateString("id-ID", options);
};

const formatDateShort = (dateString) => {
  if (!dateString) return "-";
  const options = {
    day: "2-digit",
    month: "short",
    year: "numeric",
  };
  return new Date(dateString).toLocaleDateString("id-ID", options);
};

const formatPrice = (price) => {
  return (price || 0).toLocaleString("id-ID");
};

onMounted(() => {
  fetchReservations();
});
</script>
