<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import Swal from "sweetalert2";
import { useRouter } from "vue-router";
import logo from "../../assets/img/logo.png";

const tables = ref([]);
const loading = ref(true);

const router = useRouter();
const API_BASE = "http://localhost:8000/api/guest";

const fetchTables = async () => {
  try {
    const res = await axios.get(`${API_BASE}/tables`);
    tables.value = res.data.data || [];
  } catch (err) {
    Swal.fire({
      icon: "error",
      title: "Gagal memuat meja",
      text: err.response?.data?.message || "Server bermasalah",
    });
  } finally {
    loading.value = false;
  }
};

const goToOrder = (tableId, tableNumber, status, capacity) => {
  if (status === "maintenance") {
    Swal.fire({
      icon: "warning",
      title: "Meja Tidak Tersedia",
      text: "Meja ini sedang dalam perbaikan",
    });
    return;
  }

  if (status === "occupied") {
    Swal.fire({
      icon: "info",
      title: "Meja Sedang Digunakan",
      text: `Meja ${tableNumber} sedang digunakan pelanggan lain`,
    });
    return;
  }

  // Clear previous data
  localStorage.removeItem("cart");
  localStorage.removeItem("cartNotes");
  localStorage.removeItem("pendingOrder");
  localStorage.removeItem("reservationDetails");
  localStorage.removeItem("finishedOrder");

  // Save table info
  localStorage.setItem("orderType", "Dine In");
  localStorage.setItem("currentTableId", tableId);
  localStorage.setItem("currentTableNumber", tableNumber);
  localStorage.setItem("tableCapacity", capacity);

  // Redirect ke menu dengan tableId di URL path
  router.push({
    name: "UserMenu",
    params: { tableId: tableId },
  });
};

onMounted(fetchTables);
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap");
@import url("https://fonts.googleapis.com/icon?family=Material+Icons");

.font-poppins {
  font-family: "Poppins", sans-serif;
}
</style>

<template>
  <div class="min-h-screen bg-orange-50 py-8 px-4">
    <div class="max-w-7xl mx-auto">
      <!-- Header with Logo -->
      <div class="text-center mb-12">
        <div class="flex justify-center mb-6">
          <img
            :src="logo"
            alt="Tama Coffee Logo"
            class="w-32 h-28 object-contain"
          />
        </div>
        <h1 class="text-[#6B6B6B] text-4xl font-semibold font-poppins mb-3">
          Simulasi Scan QR Order
        </h1>
        <p class="text-[#6B6B6B] text-lg font-poppins">
          Klik salah satu meja untuk memulai pemesanan
        </p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-20">
        <div
          class="inline-block animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-[#854D0E]"
        ></div>
        <p class="text-[#6B6B6B] mt-4 text-lg font-poppins">
          Memuat data meja...
        </p>
      </div>

      <!-- Tables Grid -->
      <div
        v-else
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
      >
        <div
          v-for="table in tables"
          :key="table.id"
          @click="
            goToOrder(
              table.id,
              table.table_number,
              table.status,
              table.capacity
            )
          "
          class="group relative bg-white rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 cursor-pointer transform hover:-translate-y-1 overflow-hidden"
          :class="{
            'opacity-60 cursor-not-allowed':
              table.status === 'occupied' || table.status === 'maintenance',
          }"
        >
          <!-- Status Badge -->
          <div class="absolute top-4 right-4 z-10">
            <span
              class="px-3 py-1.5 rounded-full text-xs font-semibold font-poppins text-white shadow-md"
              :class="{
                'bg-green-500': table.status === 'available',
                'bg-yellow-500': table.status === 'reserved',
                'bg-red-500': table.status === 'occupied',
                'bg-gray-500': table.status === 'maintenance',
              }"
            >
              {{ table.status.toUpperCase() }}
            </span>
          </div>

          <div class="p-6">
            <!-- Table Icon -->
            <div class="mb-5 flex justify-center">
              <div
                class="w-20 h-20 bg-[#854D0E] rounded-full flex items-center justify-center shadow-md group-hover:scale-105 transition-transform duration-300"
              >
                <span class="material-icons text-white text-4xl">
                  restaurant
                </span>
              </div>
            </div>

            <!-- Table Info -->
            <div class="text-center">
              <h2
                class="text-2xl font-semibold font-poppins text-[#6B6B6B] mb-3"
              >
                {{ table.table_number }}
              </h2>

              <div class="space-y-2 text-sm text-[#6B6B6B] font-poppins">
                <div class="flex items-center justify-center gap-2">
                  <span class="material-icons text-base"> people </span>
                  <span>Kapasitas: {{ table.capacity }} orang</span>
                </div>

                <div class="flex items-center justify-center gap-2">
                  <span class="material-icons text-base"> meeting_room </span>
                  <span>{{ table.type || "Indoor" }}</span>
                </div>
              </div>
            </div>

            <!-- Action Text -->
            <div class="mt-6 text-center">
              <div
                v-if="table.status === 'available'"
                class="inline-flex items-center gap-2 text-[#854D0E] font-semibold font-poppins group-hover:gap-3 transition-all"
              >
                <span>Mulai Order</span>
                <span class="material-icons text-lg"> arrow_forward </span>
              </div>
              <div
                v-else-if="table.status === 'occupied'"
                class="text-[#6B6B6B] font-medium font-poppins"
              >
                Sedang digunakan
              </div>
              <div
                v-else-if="table.status === 'maintenance'"
                class="text-[#6B6B6B] font-medium font-poppins"
              >
                Dalam perbaikan
              </div>
              <div v-else class="text-yellow-600 font-medium font-poppins">
                Direservasi
              </div>
            </div>
          </div>

          <!-- Bottom Border Animation -->
          <div
            class="absolute bottom-0 left-0 right-0 h-1 bg-[#854D0E] transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"
          ></div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!loading && tables.length === 0" class="text-center py-20">
        <span class="material-icons text-gray-300 text-8xl mb-4">
          event_seat
        </span>
        <p class="text-[#6B6B6B] text-lg font-poppins">
          Belum ada meja yang tersedia
        </p>
      </div>
    </div>
  </div>
</template>
