<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import Swal from "sweetalert2";
import { useRouter } from "vue-router";

const router = useRouter();
const API_BASE = "http://localhost:8000/api";

// Data orders
const orders = ref({
  waiting: [],
  processing: [],
  finished: [],
  cancelled: [],
});

const loading = ref(true);

// Modal payment
const showPaymentModal = ref(false);
const selectedOrder = ref(null);
const amountPaid = ref(0);

// Fetch orders from API
const fetchOrders = async () => {
  try {
    loading.value = true;
    const token = localStorage.getItem("token");
    
    const res = await axios.get(`${API_BASE}/orders/board`, {
      headers: { Authorization: `Bearer ${token}` }
    });

    if (res.data.success) {
      orders.value = res.data.data;
    }
  } catch (err) {
    console.error("Error fetching orders:", err);
    Swal.fire({
      icon: "error",
      title: "Gagal memuat data",
      text: err.response?.data?.message || "Terjadi kesalahan",
    });
  } finally {
    loading.value = false;
  }
};

// Open payment modal
const openPaymentModal = (order) => {
  selectedOrder.value = order;
  amountPaid.value = order.total_price;
  showPaymentModal.value = true;
};

// Calculate change
const changeAmount = computed(() => {
  if (!selectedOrder.value) return 0;
  return amountPaid.value - selectedOrder.value.total_price;
});

// Process payment
const processPayment = async () => {
  if (changeAmount.value < 0) {
    Swal.fire({
      icon: "warning",
      title: "Jumlah Kurang",
      text: "Jumlah pembayaran kurang dari total",
    });
    return;
  }

  try {
    const token = localStorage.getItem("token");
    
    const res = await axios.post(
      `${API_BASE}/orders/${selectedOrder.value.id}/process-payment`,
      { amount_paid: amountPaid.value },
      { headers: { Authorization: `Bearer ${token}` } }
    );

    if (res.data.success) {
      Swal.fire({
        icon: "success",
        title: "Pembayaran Berhasil",
        text: `Kembalian: Rp${formatCurrency(res.data.data.change)}`,
      });

      showPaymentModal.value = false;
      fetchOrders();
    }
  } catch (err) {
    Swal.fire({
      icon: "error",
      title: "Gagal Memproses",
      text: err.response?.data?.message || "Terjadi kesalahan",
    });
  }
};

// Update status order
const updateStatus = async (orderId, newStatus) => {
  try {
    const token = localStorage.getItem("token");
    
    const res = await axios.patch(
      `${API_BASE}/orders/${orderId}/status`,
      { status: newStatus },
      { headers: { Authorization: `Bearer ${token}` } }
    );

    if (res.data.success) {
      Swal.fire({
        icon: "success",
        title: "Status Diupdate",
        timer: 1500,
        showConfirmButton: false,
      });
      fetchOrders();
    }
  } catch (err) {
    Swal.fire({
      icon: "error",
      title: "Gagal Update Status",
      text: err.response?.data?.message || "Terjadi kesalahan",
    });
  }
};

// Cancel order
const cancelOrder = async (orderId) => {
  const result = await Swal.fire({
    title: "Batalkan Order?",
    text: "Order akan dibatalkan",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Ya, Batalkan",
    cancelButtonText: "Tidak",
  });

  if (result.isConfirmed) {
    await updateStatus(orderId, 'cancelled');
  }
};

// Delete order
const deleteOrder = async (orderId) => {
  const result = await Swal.fire({
    title: "Hapus Order?",
    text: "Data akan dihapus permanen",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    confirmButtonText: "Ya, Hapus",
    cancelButtonText: "Batal",
  });

  if (result.isConfirmed) {
    try {
      const token = localStorage.getItem("token");
      
      await axios.delete(`${API_BASE}/orders/${orderId}`, {
        headers: { Authorization: `Bearer ${token}` }
      });

      Swal.fire("Terhapus!", "Order telah dihapus.", "success");
      fetchOrders();
    } catch (err) {
      Swal.fire({
        icon: "error",
        title: "Gagal Menghapus",
        text: err.response?.data?.message || "Terjadi kesalahan",
      });
    }
  }
};

// View detail
const viewDetail = (orderId) => {
  router.push(`/admin/order-detail/${orderId}`);
};

// Helper format
const formatCurrency = (value) => {
  return new Intl.NumberFormat("id-ID").format(value || 0);
};

const formatTime = (datetime) => {
  if (!datetime) return '-';
  const date = new Date(datetime);
  const now = new Date();
  const diff = Math.floor((now - date) / 60000); // minutes

  if (diff < 1) return 'Baru saja';
  if (diff < 60) return `${diff} menit lalu`;
  if (diff < 1440) return `${Math.floor(diff / 60)} jam lalu`;
  return `${Math.floor(diff / 1440)} hari lalu`;
};

onMounted(() => {
  fetchOrders();
  // Auto refresh every 30 seconds
  setInterval(fetchOrders, 30000);
});
</script>

<template>
  <div class="min-h-screen flex flex-col bg-[#FFF7ED]">
    <!-- Loading -->
    <div v-if="loading" class="flex-1 flex items-center justify-center">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-600"></div>
    </div>

    <!-- Content -->
    <div v-else class="flex-1 overflow-y-auto p-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        
        <!-- MENUNGGU (Pending) -->
        <div class="rounded-xl p-4 border border-yellow-200 bg-[#FEF9C3]">
          <h2 class="font-semibold text-yellow-700 mb-3">
            Menunggu ({{ orders.waiting.length }})
          </h2>
          
          <div v-if="orders.waiting.length === 0" class="text-center text-gray-500 py-8 text-sm">
            Tidak ada pesanan
          </div>

          <div
            v-for="item in orders.waiting"
            :key="item.id"
            class="bg-white rounded-lg shadow-sm border border-yellow-100 p-4 mb-3 hover:shadow-md transition cursor-pointer"
            @click="viewDetail(item.id)"
          >
            <div class="text-xs text-gray-500 text-right">{{ formatTime(item.created_at) }}</div>
            <div class="font-semibold text-gray-800">{{ item.id }}</div>
            <div class="text-gray-700 text-sm">{{ item.customer_name }}</div>
            <div class="text-gray-500 text-sm mb-1">{{ item.table_number || 'Take Away' }}</div>
            <div class="text-blue-700 font-semibold">Rp{{ formatCurrency(item.total_price) }}</div>

            <div class="flex items-center justify-between mt-2">
              <span class="text-xs px-2 py-1 rounded-full bg-red-100 text-red-700">
                {{ item.payment_status === 'paid' ? 'Sudah Bayar' : 'Belum Bayar' }}
              </span>
              <span class="text-xs text-gray-600 uppercase">{{ item.payment_type || 'CASH' }}</span>
            </div>

            <div class="grid grid-cols-2 gap-2 mt-3">
              <button
                @click.stop="openPaymentModal(item)"
                class="text-sm bg-green-500 hover:bg-green-600 text-white py-1.5 rounded font-medium"
              >
                Proses
              </button>
              <button
                @click.stop="cancelOrder(item.id)"
                class="text-sm bg-red-500 hover:bg-red-600 text-white py-1.5 rounded font-medium"
              >
                Batal
              </button>
            </div>
          </div>
        </div>

        <!-- DIPROSES (Processing) -->
        <div class="rounded-xl p-4 border border-blue-200 bg-[#ADCCFF]">
          <h2 class="font-semibold text-blue-700 mb-3">
            Diproses ({{ orders.processing.length }})
          </h2>

          <div v-if="orders.processing.length === 0" class="text-center text-gray-500 py-8 text-sm">
            Tidak ada pesanan
          </div>

          <div
            v-for="item in orders.processing"
            :key="item.id"
            class="bg-white rounded-lg shadow-sm border border-blue-100 p-4 mb-3 hover:shadow-md transition cursor-pointer"
            @click="viewDetail(item.id)"
          >
            <div class="text-xs text-gray-500 text-right">{{ formatTime(item.created_at) }}</div>
            <div class="font-semibold text-gray-800">{{ item.id }}</div>
            <div class="text-gray-700 text-sm">{{ item.customer_name }}</div>
            <div class="text-gray-500 text-sm mb-1">{{ item.table_number || 'Take Away' }}</div>
            <div class="text-blue-700 font-semibold">Rp{{ formatCurrency(item.total_price) }}</div>

            <div class="flex items-center justify-between mt-2">
              <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700">
                Sudah Bayar
              </span>
              <span class="text-xs text-gray-600 uppercase">{{ item.payment_type || 'CASH' }}</span>
            </div>

            <button
              @click.stop="updateStatus(item.id, 'completed')"
              class="mt-3 w-full text-sm bg-blue-500 hover:bg-blue-600 text-white py-1.5 rounded font-medium"
            >
              Selesai
            </button>
          </div>
        </div>

        <!-- SELESAI (Completed) -->
        <div class="rounded-xl p-4 border border-green-200 bg-[#DCFCE7]">
          <h2 class="font-semibold text-green-700 mb-3">
            Selesai ({{ orders.finished.length }})
          </h2>

          <div v-if="orders.finished.length === 0" class="text-center text-gray-500 py-8 text-sm">
            Tidak ada pesanan
          </div>

          <div
            v-for="item in orders.finished"
            :key="item.id"
            class="bg-white rounded-lg shadow-sm border border-green-100 p-4 mb-3 hover:shadow-md transition cursor-pointer"
            @click="viewDetail(item.id)"
          >
            <div class="text-xs text-gray-500 text-right">{{ formatTime(item.created_at) }}</div>
            <div class="font-semibold text-gray-800">{{ item.id }}</div>
            <div class="text-gray-700 text-sm">{{ item.customer_name }}</div>
            <div class="text-gray-500 text-sm mb-1">{{ item.table_number || 'Take Away' }}</div>
            <div class="text-blue-700 font-semibold">Rp{{ formatCurrency(item.total_price) }}</div>

            <div class="flex items-center justify-between mt-2">
              <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700">
                Selesai
              </span>
              <span class="text-xs text-gray-600 uppercase">{{ item.payment_type || 'CASH' }}</span>
            </div>

            <div class="grid grid-cols-2 gap-2 mt-3">
              <button
                @click.stop="viewDetail(item.id)"
                class="text-sm bg-gray-500 hover:bg-gray-600 text-white py-1.5 rounded font-medium"
              >
                Detail
              </button>
              <button
                class="text-sm bg-orange-500 hover:bg-orange-600 text-white py-1.5 rounded font-medium"
              >
                Print
              </button>
            </div>
          </div>
        </div>

        <!-- BATAL (Cancelled) -->
        <div class="rounded-xl p-4 border border-red-200 bg-[#FEE2E2]">
          <h2 class="font-semibold text-red-700 mb-3">
            Batal ({{ orders.cancelled.length }})
          </h2>

          <div v-if="orders.cancelled.length === 0" class="text-center text-gray-500 py-8 text-sm">
            Tidak ada pesanan
          </div>

          <div
            v-for="item in orders.cancelled"
            :key="item.id"
            class="bg-white rounded-lg shadow-sm border border-red-100 p-4 mb-3 hover:shadow-md transition cursor-pointer"
            @click="viewDetail(item.id)"
          >
            <div class="text-xs text-gray-500 text-right">{{ formatTime(item.created_at) }}</div>
            <div class="font-semibold text-gray-800">{{ item.id }}</div>
            <div class="text-gray-700 text-sm">{{ item.customer_name }}</div>
            <div class="text-gray-500 text-sm mb-1">{{ item.table_number || 'Take Away' }}</div>
            <div class="text-blue-700 font-semibold">Rp{{ formatCurrency(item.total_price) }}</div>

            <div class="flex items-center justify-between mt-2">
              <span class="text-xs px-2 py-1 rounded-full bg-red-100 text-red-700">
                Dibatalkan
              </span>
              <span class="text-xs text-gray-600 uppercase">{{ item.payment_type || 'CASH' }}</span>
            </div>

            <button
              @click.stop="deleteOrder(item.id)"
              class="mt-3 w-full text-sm bg-red-500 hover:bg-red-600 text-white py-1.5 rounded font-medium"
            >
              Hapus
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Payment Modal -->
    <div
      v-if="showPaymentModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="showPaymentModal = false"
    >
      <div class="bg-white rounded-xl p-6 w-96 shadow-2xl">
        <h3 class="text-xl font-bold mb-4">Proses Pembayaran</h3>
        
        <div class="space-y-3 mb-6">
          <div class="flex justify-between">
            <span class="text-gray-600">Order ID:</span>
            <span class="font-semibold">{{ selectedOrder?.id }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">Customer:</span>
            <span class="font-semibold">{{ selectedOrder?.customer_name }}</span>
          </div>
          <div class="flex justify-between text-lg">
            <span class="font-semibold">Total:</span>
            <span class="font-bold text-orange-600">Rp{{ formatCurrency(selectedOrder?.total_price) }}</span>
          </div>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium mb-2">Jumlah Dibayar</label>
          <input
            v-model.number="amountPaid"
            type="number"
            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none"
            placeholder="Masukkan jumlah"
          />
        </div>

        <div class="bg-gray-50 p-3 rounded-lg mb-6">
          <div class="flex justify-between text-lg">
            <span class="font-semibold">Kembalian:</span>
            <span
              :class="changeAmount >= 0 ? 'text-green-600' : 'text-red-600'"
              class="font-bold"
            >
              Rp{{ formatCurrency(Math.abs(changeAmount)) }}
            </span>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <button
            @click="showPaymentModal = false"
            class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg font-medium"
          >
            Batal
          </button>
          <button
            @click="processPayment"
            class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-medium"
          >
            Bayar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>