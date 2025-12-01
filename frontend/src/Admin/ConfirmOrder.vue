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

// Filter orders by today's date only
const isTodayOrder = (orderDate) => {
  if (!orderDate) return false;
  const today = new Date();
  const orderDay = new Date(orderDate);
  return (
    today.getDate() === orderDay.getDate() &&
    today.getMonth() === orderDay.getMonth() &&
    today.getFullYear() === orderDay.getFullYear()
  );
};

// Fetch orders from API
const fetchOrders = async () => {
  try {
    loading.value = true;
    const token = localStorage.getItem("token");

    const res = await axios.get(`${API_BASE}/orders/board`, {
      headers: { Authorization: `Bearer ${token}` },
    });

    if (res.data.success) {
      // Filter only today's orders
      orders.value = {
        waiting: res.data.data.waiting.filter((order) =>
          isTodayOrder(order.created_at)
        ),
        processing: res.data.data.processing.filter((order) =>
          isTodayOrder(order.created_at)
        ),
        finished: res.data.data.finished.filter((order) =>
          isTodayOrder(order.created_at)
        ),
        cancelled: res.data.data.cancelled.filter((order) =>
          isTodayOrder(order.created_at)
        ),
      };
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
    await updateStatus(orderId, "cancelled");
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
        headers: { Authorization: `Bearer ${token}` },
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
  if (!datetime) return "-";
  const date = new Date(datetime);
  const now = new Date();
  const diff = Math.floor((now - date) / 60000); // minutes

  if (diff < 1) return "Baru saja";
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
  <div class="min-h-screen flex flex-col bg-[#F7F7F7]">
    <!-- Loading -->
    <div v-if="loading" class="flex-1 flex items-center justify-center">
      <div
        class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#854D0E]"
      ></div>
    </div>

    <!-- Content -->
    <div v-else class="flex-1 overflow-y-auto p-8">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8 items-start">
        <!-- MENUNGGU (Waiting) -->
        <div class="rounded-lg p-4 bg-yellow-100 h-fit">
          <h2 class="font-bold text-[#1E40AF] text-lg text-center mb-4">
            Waiting
          </h2>

          <div
            v-if="orders.waiting.length === 0"
            class="text-center text-gray-500 py-8 text-sm"
          >
            Tidak ada pesanan
          </div>

          <div
            v-for="item in orders.waiting"
            :key="item.id"
            class="bg-white rounded-lg shadow-sm border border-[#E5E5E5] p-4 mb-4 hover:shadow-md transition cursor-pointer"
            @click="viewDetail(item.id)"
          >
            <div class="flex justify-between items-start mb-2">
              <div class="font-bold text-[#1E293B] text-sm">{{ item.id }}</div>
              <div class="text-[#737373] text-sm">
                {{ formatTime(item.created_at) }}
              </div>
            </div>
            <div class="text-[#334155] text-base mb-1">
              {{ item.customer_name }}
            </div>
            <div class="text-[#64748B] text-base mb-3">
              {{ item.table_number || "Take Away" }}
            </div>
            <div class="text-[#1E40AF] font-bold text-sm mb-4">
              Rp{{ formatCurrency(item.total_price) }}
            </div>

            <div class="flex items-center justify-between mb-3">
              <div class="flex items-center gap-2">
                <span
                  :class="
                    item.payment_status === 'paid'
                      ? 'bg-[#ECFDF5] text-[#059669]'
                      : 'bg-[#F0F0F0] text-[#DC2626]'
                  "
                  class="text-xs px-2 py-0.5 rounded-full"
                >
                  {{
                    item.payment_status === "paid"
                      ? "Sudah Bayar"
                      : "Belum Bayar"
                  }}
                </span>
                <span class="text-xs text-[#64748B]">{{
                  item.payment_type || "CASH"
                }}</span>
              </div>
            </div>

            <div class="flex gap-2">
              <button
                @click.stop="cancelOrder(item.id)"
                class="flex-1 text-sm bg-[#E5E5E5] hover:bg-[#D4D4D4] text-[#1E293B] py-1 px-4 rounded font-bold"
              >
                Cancel
              </button>
              <button
                @click.stop="openPaymentModal(item)"
                class="flex-1 text-sm bg-[#E5E5E5] hover:bg-[#D4D4D4] text-[#1E293B] py-1 px-4 rounded font-bold"
              >
                Process
              </button>
            </div>
          </div>
        </div>

        <!-- DIPROSES (Processing) -->
        <div class="rounded-lg p-4 bg-blue-200/90 h-fit">
          <h2 class="font-bold text-[#EA580C] text-lg mb-4">Processing</h2>

          <div
            v-if="orders.processing.length === 0"
            class="text-center text-gray-500 py-8 text-sm"
          >
            Tidak ada pesanan
          </div>

          <div
            v-for="item in orders.processing"
            :key="item.id"
            class="bg-white rounded-lg shadow-sm border border-[#E5E5E5] p-4 mb-4 hover:shadow-md transition cursor-pointer"
            @click="viewDetail(item.id)"
          >
            <div class="flex justify-between items-start mb-2">
              <div class="font-bold text-[#1E293B] text-sm">{{ item.id }}</div>
              <div class="text-[#737373] text-sm">
                {{ formatTime(item.created_at) }}
              </div>
            </div>
            <div class="text-[#334155] text-base mb-1">
              {{ item.customer_name }}
            </div>
            <div class="text-[#64748B] text-base mb-3">
              {{ item.table_number || "Take Away" }}
            </div>
            <div class="text-[#1E40AF] font-bold text-sm mb-4">
              Rp{{ formatCurrency(item.total_price) }}
            </div>

            <div class="flex items-center justify-between mb-3">
              <div class="flex items-center gap-2">
                <span
                  class="bg-[#ECFDF5] text-[#059669] text-xs px-2 py-0.5 rounded-full"
                >
                  Sudah Bayar
                </span>
                <span class="text-xs text-[#64748B]">{{
                  item.payment_type || "CASH"
                }}</span>
              </div>
            </div>

            <button
              @click.stop="updateStatus(item.id, 'completed')"
              class="w-full text-sm bg-[#E5E5E5] hover:bg-[#D4D4D4] text-[#1E293B] py-1 px-4 rounded font-bold"
            >
              Done
            </button>
          </div>
        </div>

        <!-- SELESAI (Completed) -->
        <div class="rounded-lg p-4 bg-green-100 h-fit">
          <h2 class="font-bold text-[#059669] text-lg mb-4">Completed</h2>

          <div
            v-if="orders.finished.length === 0"
            class="text-center text-gray-500 py-8 text-sm"
          >
            Tidak ada pesanan
          </div>

          <div
            v-for="item in orders.finished"
            :key="item.id"
            class="bg-white rounded-lg shadow-sm border border-[#E5E5E5] p-4 mb-4 hover:shadow-md transition cursor-pointer"
            @click="viewDetail(item.id)"
          >
            <div class="flex justify-between items-start mb-2">
              <div class="font-bold text-[#1E293B] text-sm">{{ item.id }}</div>
              <div class="text-[#737373] text-sm">
                {{ formatTime(item.created_at) }}
              </div>
            </div>
            <div class="text-[#334155] text-base mb-1">
              {{ item.customer_name }}
            </div>
            <div class="text-[#64748B] text-base mb-3">
              {{ item.table_number || "Take Away" }}
            </div>
            <div class="text-[#1E40AF] font-bold text-sm mb-4">
              Rp{{ formatCurrency(item.total_price) }}
            </div>

            <div class="flex items-center gap-2">
              <span
                class="bg-[#ECFDF5] text-[#059669] text-xs px-2 py-0.5 rounded-full"
              >
                Sudah Bayar
              </span>
              <span class="text-xs text-[#64748B]">{{
                item.payment_type || "CASH"
              }}</span>
            </div>
          </div>
        </div>

        <!-- BATAL (Cancelled) -->
        <div class="rounded-lg p-4 bg-red-100 h-fit">
          <h2 class="font-bold text-[#DC2626] text-lg mb-4">Cancelled</h2>

          <div
            v-if="orders.cancelled.length === 0"
            class="text-center text-gray-500 py-8 text-sm"
          >
            Tidak ada pesanan
          </div>

          <div
            v-for="item in orders.cancelled"
            :key="item.id"
            class="bg-white rounded-lg shadow-sm border border-[#E5E5E5] p-4 mb-4 hover:shadow-md transition cursor-pointer"
            @click="viewDetail(item.id)"
          >
            <div class="flex justify-between items-start mb-2">
              <div class="font-bold text-[#1E293B] text-sm">{{ item.id }}</div>
              <div class="text-[#737373] text-sm">
                {{ formatTime(item.created_at) }}
              </div>
            </div>
            <div class="text-[#334155] text-base mb-1">
              {{ item.customer_name }}
            </div>
            <div class="text-[#64748B] text-base mb-3">
              {{ item.table_number || "Take Away" }}
            </div>
            <div class="text-[#1E40AF] font-bold text-sm mb-4">
              Rp{{ formatCurrency(item.total_price) }}
            </div>

            <div class="flex items-center gap-2">
              <span
                class="bg-[#FEF3C7] text-[#EA580C] text-xs px-2 py-0.5 rounded-full"
              >
                Pending
              </span>
              <span class="text-xs text-[#64748B]">{{
                item.payment_type || "CASH"
              }}</span>
            </div>
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
            <span class="font-semibold">{{
              selectedOrder?.customer_name
            }}</span>
          </div>
          <div class="flex justify-between text-lg">
            <span class="font-semibold">Total:</span>
            <span class="font-bold text-orange-600"
              >Rp{{ formatCurrency(selectedOrder?.total_price) }}</span
            >
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
