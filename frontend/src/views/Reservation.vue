<template>
  <div class="bg-white min-h-screen">
    <div class="max-w-md mx-auto bg-white min-h-screen font-sans flex flex-col">
      <!-- Header -->
      <header class="bg-white border-b border-gray-200 px-4 py-4">
        <h1 class="text-xl font-bold text-gray-900 text-center">Reservation</h1>
      </header>

      <!-- Main Content -->
      <main class="flex-1 px-6 py-6 overflow-y-auto bg-white">
        <!-- Title Section -->
        <section class="mb-6">
          <h2 class="text-2xl font-bold text-gray-900 mb-2">
            Reserve Your Table
          </h2>
          <p class="text-sm text-gray-500 leading-relaxed">
            Please fill in the form below to make a reservation.
          </p>
        </section>

        <!-- Form Section -->
        <section class="space-y-5">
          <!-- Nama Lengkap -->
          <div>
            <label class="text-sm font-semibold text-gray-700 mb-2 block"
              >Nama Lengkap</label
            >
            <div class="relative">
              <div
                class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-5 w-5 text-gray-400"
                  viewBox="0 0 20 20"
                  fill="currentColor"
                >
                  <path
                    fill-rule="evenodd"
                    d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                    clip-rule="evenodd"
                  />
                </svg>
              </div>
              <input
                v-model="form.name"
                type="text"
                placeholder="Masukkan nama Anda"
                class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-100 transition-all placeholder:text-gray-400"
              />
            </div>
          </div>

          <!-- Nomor Telepon -->
          <div>
            <label class="text-sm font-semibold text-gray-700 mb-2 block"
              >Nomor Telepon</label
            >
            <div class="relative">
              <div
                class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-5 w-5 text-gray-400"
                  viewBox="0 0 20 20"
                  fill="currentColor"
                >
                  <path
                    d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"
                  />
                </svg>
              </div>
              <input
                v-model="form.phone"
                type="tel"
                placeholder="Masukkan nomor telepon"
                class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-100 transition-all placeholder:text-gray-400"
              />
            </div>
          </div>

          <!-- Tanggal & Jam -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="text-sm font-semibold text-gray-700 mb-2 block"
                >Tanggal</label
              >
              <div class="relative">
                <div
                  class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5 text-gray-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                    />
                  </svg>
                </div>
                <input
                  v-model="form.date"
                  type="date"
                  :min="minDate"
                  @change="checkAvailability"
                  placeholder="mm/dd/yyyy"
                  class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-100 transition-all"
                />
              </div>
            </div>
            <div>
              <label class="text-sm font-semibold text-gray-700 mb-2 block"
                >Jam</label
              >
              <div class="relative">
                <div
                  class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5 text-gray-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                </div>
                <input
                  v-model="form.time"
                  type="time"
                  @change="checkAvailability"
                  placeholder="--:-- --"
                  class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-100 transition-all"
                />
              </div>
            </div>
          </div>

          <!-- Pilih Meja -->
          <div v-if="availableTables.length > 0">
            <label class="text-sm font-semibold text-gray-700 mb-2 block"
              >Pilih Meja</label
            >
            <div class="grid grid-cols-3 gap-3">
              <button
                v-for="table in availableTables"
                :key="table.id"
                @click="form.tableId = table.id"
                :class="[
                  'py-3 px-2 rounded-xl border text-sm font-medium transition-all',
                  form.tableId === table.id
                    ? 'border-orange-500 bg-orange-50 text-orange-700 ring-2 ring-orange-100'
                    : 'border-gray-200 bg-white text-gray-600 hover:border-orange-300',
                ]"
              >
                {{ table.table_number }}
                <span class="block text-xs font-normal text-gray-400 mt-1"
                  >{{ table.capacity }} Orang</span
                >
              </button>
            </div>
          </div>
          <div
            v-else-if="form.date && form.time && !isLoading"
            class="text-center py-4 bg-gray-50 rounded-xl border border-dashed border-gray-300"
          >
            <p class="text-sm text-gray-500">
              Tidak ada meja tersedia pada waktu ini.
            </p>
          </div>
          <div v-else-if="isLoading" class="text-center py-4">
            <p class="text-sm text-gray-500">Mengecek ketersediaan...</p>
          </div>
        </section>
      </main>

      <!-- Footer Button -->
      <footer class="bg-white border-t border-gray-200 pb-6 pt-4 px-6">
        <button
          @click="submitReservation"
          :disabled="!isFormValid"
          :class="[
            'w-full py-4 text-white text-base font-bold rounded-xl transition-all shadow-lg active:scale-[0.98]',
            isFormValid
              ? 'bg-[#B85814] hover:bg-[#A04D12]'
              : 'bg-gray-300 cursor-not-allowed',
          ]"
        >
          Buat Reservasi
        </button>
      </footer>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";

const router = useRouter();

const form = ref({
  name: "",
  phone: "",
  date: "",
  time: "",
  tableId: "",
});

const availableTables = ref([]);
const isLoading = ref(false);

// Calculate min date (tomorrow)
const minDate = computed(() => {
  const tomorrow = new Date();
  tomorrow.setDate(tomorrow.getDate() + 1);
  return tomorrow.toISOString().split("T")[0];
});

const isFormValid = computed(() => {
  return (
    form.value.name &&
    form.value.phone &&
    form.value.date &&
    form.value.time &&
    form.value.tableId
  );
});

const checkAvailability = async () => {
  if (!form.value.date || !form.value.time) return;

  isLoading.value = true;
  availableTables.value = [];
  form.value.tableId = ""; // Reset selection

  try {
    const response = await axios.get(
      "http://localhost:8000/api/reservations/availability",
      {
        params: {
          date: form.value.date,
          time: form.value.time,
        },
      }
    );

    if (response.data.success) {
      availableTables.value = response.data.data;
    }
  } catch (error) {
    console.error("Error checking availability:", error);
    // Silent error, just keep empty table list
  } finally {
    isLoading.value = false;
  }
};

const submitReservation = async () => {
  if (!isFormValid.value) {
    // Form not valid, just return without alert
    return;
  }

  try {
    // TIDAK submit ke backend dulu, hanya simpan data ke localStorage
    // Reservasi akan dibuat di PaymentConfirmation bersamaan dengan Order
    const selectedTable = availableTables.value.find(
      (t) => t.id === form.value.tableId
    );

    // Simpan data reservasi ke localStorage untuk diproses nanti
    localStorage.setItem("orderType", "Reservasi");
    localStorage.setItem("currentTableId", form.value.tableId);
    localStorage.setItem(
      "currentTableNumber",
      selectedTable ? selectedTable.table_number : ""
    );

    // Data reservasi yang akan dikirim nanti di PaymentConfirmation
    const reservationData = {
      name: form.value.name,
      phone: form.value.phone,
      date: form.value.date,
      time: form.value.time,
      tableId: form.value.tableId,
      tableNumber: selectedTable ? selectedTable.table_number : "",
      people: selectedTable ? selectedTable.capacity : 2,
      status: "pending",
    };

    localStorage.setItem("reservationDetails", JSON.stringify(reservationData));

    // Clear cart data
    localStorage.removeItem("cart");
    localStorage.removeItem("cartNotes");

    // Redirect langsung tanpa alert
    router.push(`/order/${form.value.tableId}/menu`);
  } catch (error) {
    console.error("Error saving reservation data:", error);
    // Redirect tetap jalan meskipun ada error
    router.push(`/order/${form.value.tableId}/menu`);
  }
};
</script>

<style scoped>
main {
  overflow-y: auto;
}
</style>
