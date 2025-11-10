<script setup>
import { ref } from "vue";

// Data contoh
const orders = ref({
  waiting: [
    { id: "#12345", name: "Sophia Clark", table: "Meja 5", total: 120000, time: "10 menit lalu", status: "Belum Bayar", payment: "CASH" },
    { id: "#67890", name: "Liam Brown", table: "Take Away", total: 85000, time: "15 menit lalu", status: "Sudah Bayar", payment: "QRIS" },
  ],
  processing: [
    { id: "#24680", name: "Olivia Davis", table: "Meja 2", total: 150000, time: "20 menit lalu", status: "Sudah Bayar", payment: "QRIS" },
  ],
  finished: [
    { id: "#13579", name: "Noah Miller", table: "Meja 8", total: 200000, time: "30 menit lalu", status: "Sudah Bayar", payment: "QRIS" },
    { id: "#11223", name: "Ava Jones", table: "Meja 1", total: 175000, time: "35 menit lalu", status: "Sudah Bayar", payment: "Cash" },
  ],
  cancelled: [
    { id: "#97531", name: "Emma Wilson", table: "Meja 10", total: 50000, time: "45 menit lalu", status: "Pending", payment: "Cash" },
  ],
});

// Helper format uang
const formatCurrency = (value) =>
  new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(value);
</script>

<template>
  <div class="min-h-screen flex flex-col bg-[#FFF7ED]">

    <!-- ✅ Konten scrollable -->
    <div class="flex-1 overflow-y-auto p-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- MENUNGGU -->
        <div class="rounded-xl p-4 border border-yellow-200 bg-[#FEF9C3]">
          <h2 class="font-semibold text-yellow-700 mb-3">Menunggu</h2>
          <div
            v-for="(item, index) in orders.waiting"
            :key="index"
            class="bg-white rounded-lg shadow-sm border border-yellow-100 p-4 mb-3 transition hover:shadow-md"
          >
            <div class="text-sm text-gray-500 text-right">{{ item.time }}</div>
            <div class="font-semibold text-gray-800">{{ item.id }}</div>
            <div class="text-gray-700 text-sm">{{ item.name }}</div>
            <div class="text-gray-500 text-sm mb-1">{{ item.table }}</div>
            <div class="text-blue-700 font-semibold">{{ formatCurrency(item.total) }}</div>

            <div class="flex items-center justify-between mt-2">
              <span
                class="text-xs px-2 py-1 rounded-full"
                :class="item.status === 'Sudah Bayar' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
              >
                {{ item.status }}
              </span>
              <span class="text-xs text-gray-600">{{ item.payment }}</span>
            </div>

            <button
              class="mt-3 w-full text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 py-1 rounded"
            >
              Aksi
            </button>
          </div>
        </div>

        <!-- DIPROSES -->
        <div class="rounded-xl p-4 border border-blue-200 bg-[#ADCCFF]">
          <h2 class="font-semibold text-blue-700 mb-3">Diproses</h2>
          <div
            v-for="(item, index) in orders.processing"
            :key="index"
            class="bg-white rounded-lg shadow-sm border border-blue-100 p-4 mb-3 transition hover:shadow-md"
          >
            <div class="text-sm text-gray-500 text-right">{{ item.time }}</div>
            <div class="font-semibold text-gray-800">{{ item.id }}</div>
            <div class="text-gray-700 text-sm">{{ item.name }}</div>
            <div class="text-gray-500 text-sm mb-1">{{ item.table }}</div>
            <div class="text-blue-700 font-semibold">{{ formatCurrency(item.total) }}</div>

            <div class="flex items-center justify-between mt-2">
              <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700">
                {{ item.status }}
              </span>
              <span class="text-xs text-gray-600">{{ item.payment }}</span>
            </div>

            <button
              class="mt-3 w-full text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 py-1 rounded"
            >
              Aksi
            </button>
          </div>
        </div>

        <!-- SELESAI -->
        <div class="rounded-xl p-4 border border-green-200 bg-[#DCFCE7]">
          <h2 class="font-semibold text-green-700 mb-3">Selesai</h2>
          <div
            v-for="(item, index) in orders.finished"
            :key="index"
            class="bg-white rounded-lg shadow-sm border border-green-100 p-4 mb-3 transition hover:shadow-md"
          >
            <div class="text-sm text-gray-500 text-right">{{ item.time }}</div>
            <div class="font-semibold text-gray-800">{{ item.id }}</div>
            <div class="text-gray-700 text-sm">{{ item.name }}</div>
            <div class="text-gray-500 text-sm mb-1">{{ item.table }}</div>
            <div class="text-blue-700 font-semibold">{{ formatCurrency(item.total) }}</div>

            <div class="flex items-center justify-between mt-2">
              <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700">
                {{ item.status }}
              </span>
              <span class="text-xs text-gray-600">{{ item.payment }}</span>
            </div>

            <button
              class="mt-3 w-full text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 py-1 rounded"
            >
              Aksi
            </button>
          </div>
        </div>

        <!-- BATAL -->
        <div class="rounded-xl p-4 border border-red-200 bg-[#FEE2E2]">
          <h2 class="font-semibold text-red-700 mb-3">Batal</h2>
          <div
            v-for="(item, index) in orders.cancelled"
            :key="index"
            class="bg-white rounded-lg shadow-sm border border-red-100 p-4 mb-3 transition hover:shadow-md"
          >
            <div class="text-sm text-gray-500 text-right">{{ item.time }}</div>
            <div class="font-semibold text-gray-800">{{ item.id }}</div>
            <div class="text-gray-700 text-sm">{{ item.name }}</div>
            <div class="text-gray-500 text-sm mb-1">{{ item.table }}</div>
            <div class="text-blue-700 font-semibold">{{ formatCurrency(item.total) }}</div>

            <div class="flex items-center justify-between mt-2">
              <span class="text-xs px-2 py-1 rounded-full bg-yellow-100 text-yellow-700">
                {{ item.status }}
              </span>
              <span class="text-xs text-gray-600">{{ item.payment }}</span>
            </div>

            <button
              class="mt-3 w-full text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 py-1 rounded"
            >
              Aksi
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
