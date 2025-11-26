<template>
  <div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex justify-between items-start">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Detail Order</h1>
            <div class="flex items-center space-x-4 text-sm text-gray-600">
              <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">
                {{ getStatusText(orderData?.status) }}
              </span>
              <span>{{ formatDate(orderData?.created_at) }}</span>
            </div>
          </div>
          <div class="text-right">
            <div class="text-2xl font-bold text-orange-600 mb-2">
              Rp{{ formatCurrency(orderData?.total_price) }}
            </div>
            <button
              v-if="orderData?.status === 'completed'"
              @click="printReceipt"
              class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium flex items-center space-x-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
              </svg>
              <span>Cetak Struk</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Hidden receipt content for printing -->
      <div id="receipt-content" class="hidden">
        <div class="receipt-container">
          <!-- Header -->
          <div class="receipt-header">
            <h1>TAMA COFFEE</h1>
            <p>Jl. Contoh No. 123, Kota Anda</p>
            <p>Telp: (021) 1234-5678</p>
          </div>

          <!-- Order Info -->
          <div class="receipt-section">
            <div class="receipt-row">
              <span class="label">Order ID:</span>
              <span class="value">{{ orderData?.id }}</span>
            </div>
            <div class="receipt-row">
              <span class="label">Tanggal:</span>
              <span class="value">{{ formatReceiptDate(orderData?.created_at) }}</span>
            </div>
          </div>

          <div class="receipt-divider">---</div>

          <!-- Customer Info -->
          <div class="receipt-section">
            <div class="receipt-title">CUSTOMER</div>
            <div class="receipt-row">
              <span class="label">Nama:</span>
              <span class="value">{{ orderData?.customer_name }}</span>
            </div>
            <div class="receipt-row">
              <span class="label">Telepon:</span>
              <span class="value">{{ orderData?.customer_phone || '-' }}</span>
            </div>
            <div class="receipt-row">
              <span class="label">Meja:</span>
              <span class="value">{{ orderData?.table_number || 'Take Away' }}</span>
            </div>
          </div>

          <div class="receipt-divider">---</div>

          <!-- Payment Method -->
          <div class="receipt-section">
            <div class="receipt-row">
              <span class="label">Metode Bayar:</span>
              <span class="value">{{ getPaymentMethodText(paymentData?.payment_type) }}</span>
            </div>
          </div>

          <div class="receipt-divider">---</div>

          <!-- Items -->
          <div class="receipt-section">
            <div class="receipt-title">ITEMS</div>
            <div class="receipt-items">
              <div v-for="item in orderItems" :key="item.id" class="receipt-item">
                <div class="item-name">{{ item.product_name }}</div>
                <div v-if="item.variant" class="item-variant">{{ item.variant }}</div>
                <div class="item-details">
                  {{ item.quantity }} x Rp{{ formatCurrency(item.unit_price) }}
                </div>
                <div class="item-price">Rp{{ formatCurrency(item.subtotal) }}</div>
              </div>
            </div>
          </div>

          <div class="receipt-divider">---</div>

          <!-- Customer Note -->
          <div v-if="orderData?.note" class="receipt-section">
            <div class="receipt-title">CATATAN</div>
            <div class="receipt-note">{{ orderData.note }}</div>
          </div>

          <div class="receipt-divider">---</div>

          <!-- Order Summary -->
          <div class="receipt-section">
            <div class="receipt-title">RINGKASAN</div>
            <div class="receipt-row">
              <span class="label">Subtotal:</span>
              <span class="value">Rp{{ formatCurrency(orderData?.total_price) }}</span>
            </div>
            <div class="receipt-row">
              <span class="label">Pajak:</span>
              <span class="value">Rp0</span>
            </div>
            <div class="receipt-row">
              <span class="label">Biaya Layanan:</span>
              <span class="value">Rp0</span>
            </div>
            <div class="receipt-divider">---</div>
            <div class="receipt-row total">
              <span class="label">TOTAL:</span>
              <span class="value">Rp{{ formatCurrency(orderData?.total_price) }}</span>
            </div>
          </div>

          <div class="receipt-divider">---</div>

          <!-- Footer -->
          <div class="receipt-footer">
            <p>Terima kasih atas kunjungan Anda</p>
            <p>*** Struk ini sebagai bukti pembayaran ***</p>
          </div>
        </div>
      </div>

      <!-- Rest of your existing content... -->
      <!-- Customer & Order Info -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Customer Information -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Informasi Customer</h2>
          <div class="space-y-3">
            <div>
              <label class="text-sm text-gray-500">Nama</label>
              <p class="font-medium text-gray-900">{{ orderData?.customer_name || '-' }}</p>
            </div>
            <div>
              <label class="text-sm text-gray-500">Telepon</label>
              <p class="font-medium text-gray-900">{{ orderData?.customer_phone || '-' }}</p>
            </div>
            <div>
              <label class="text-sm text-gray-500">Email</label>
              <p class="font-medium text-gray-900">{{ orderData?.customer_email || '-' }}</p>
            </div>
          </div>
        </div>

        <!-- Order Information -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Informasi Order</h2>
          <div class="space-y-3">
            <div>
              <label class="text-sm text-gray-500">Order ID</label>
              <p class="font-medium text-gray-900">{{ orderData?.id || '-' }}</p>
            </div>
            <div>
              <label class="text-sm text-gray-500">Meja</label>
              <p class="font-medium text-gray-900">{{ orderData?.table_number || 'Take Away' }}</p>
            </div>
            <div>
              <label class="text-sm text-gray-500">Tipe Order</label>
              <p class="font-medium text-gray-900">{{ orderData?.order_type || 'Dine In' }}</p>
            </div>
            <div>
              <label class="text-sm text-gray-500">Metode Pembayaran</label>
              <p class="font-medium text-gray-900 uppercase">{{ paymentData?.payment_type || 'CASH' }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Order Items -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Items Pesanan</h2>
        
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-gray-200">
                <th class="text-left py-3 px-4 text-sm font-medium text-gray-500">Produk</th>
                <th class="text-right py-3 px-4 text-sm font-medium text-gray-500">Harga</th>
                <th class="text-center py-3 px-4 text-sm font-medium text-gray-500">Qty</th>
                <th class="text-right py-3 px-4 text-sm font-medium text-gray-500">Subtotal</th>
                <th class="text-center py-3 px-4 text-sm font-medium text-gray-500">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="item in orderItems"
                :key="item.id"
                class="border-b border-gray-100 hover:bg-gray-50"
              >
                <td class="py-4 px-4">
                  <div class="flex items-center space-x-3">
                    <img
                      v-if="getProductImageUrl(item.product_image)"
                      :src="getProductImageUrl(item.product_image)"
                      :alt="item.product_name"
                      class="w-12 h-12 rounded-lg object-cover"
                      @error="handleImageError"
                    />
                    <div
                      v-else
                      class="w-12 h-12 rounded-lg bg-gray-200 flex items-center justify-center"
                    >
                      <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                      </svg>
                    </div>
                    <div>
                      <p class="font-medium text-gray-900">{{ item.product_name }}</p>
                      <p v-if="item.variant" class="text-sm text-gray-500">{{ item.variant }}</p>
                    </div>
                  </div>
                </td>
                <td class="py-4 px-4 text-right font-medium text-gray-900">
                  Rp{{ formatCurrency(item.unit_price) }}
                </td>
                <td class="py-4 px-4 text-center text-gray-900">
                  {{ item.quantity }}
                </td>
                <td class="py-4 px-4 text-right font-medium text-gray-900">
                  Rp{{ formatCurrency(item.subtotal) }}
                </td>
                <td class="py-4 px-4 text-center">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="getItemStatusClass(item)"
                  >
                    {{ getItemStatusText(item) }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Customer Note & Order Summary -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Customer Note -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6" v-if="orderData?.note">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Catatan Customer</h2>
          <p class="text-gray-700 bg-yellow-50 p-4 rounded-lg border border-yellow-100">
            {{ orderData.note }}
          </p>
        </div>

        <!-- Order Summary -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Order</h2>
          <div class="space-y-3">
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Subtotal</span>
              <span class="font-medium text-gray-900">Rp{{ formatCurrency(orderData?.total_price) }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Pajak</span>
              <span class="font-medium text-gray-900">Rp0</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Biaya Layanan</span>
              <span class="font-medium text-gray-900">Rp0</span>
            </div>
            <div class="border-t border-gray-200 pt-3 flex justify-between text-lg font-bold">
              <span class="text-gray-900">Total Dibayar</span>
              <span class="text-orange-600">Rp{{ formatCurrency(orderData?.total_price) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Payment Information -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Informasi Pembayaran</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div>
            <label class="text-sm text-gray-500">Metode</label>
            <p class="font-medium text-gray-900 uppercase">{{ paymentData?.payment_type || 'CASH' }}</p>
          </div>
          <div>
            <label class="text-sm text-gray-500">Status</label>
            <span
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
              :class="getPaymentStatusClass(paymentData?.status)"
            >
              {{ getPaymentStatusText(paymentData?.status) }}
            </span>
          </div>
          <div>
            <label class="text-sm text-gray-500">Tanggal Pembayaran</label>
            <p class="font-medium text-gray-900">{{ formatDate(paymentData?.date) }}</p>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center">
          <button
            @click="$router.back()"
            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium flex items-center space-x-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            <span>Kembali</span>
          </button>
          
          <div class="flex space-x-3" v-if="orderData?.status === 'completed'">
            <button
              @click="printReceipt"
              class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium flex items-center space-x-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
              </svg>
              <span>Cetak Struk</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";
import Swal from "sweetalert2";

const route = useRoute();
const router = useRouter();
const API_BASE = "http://localhost:8000/api";

// Data
const orderData = ref(null);
const orderItems = ref([]);
const paymentData = ref(null);
const loading = ref(true);

// Fetch order detail
const fetchOrderDetail = async () => {
  try {
    loading.value = true;
    const token = localStorage.getItem("token");
    const orderId = route.params.id;

    const res = await axios.get(`${API_BASE}/orders/${orderId}/detail`, {
      headers: { Authorization: `Bearer ${token}` }
    });

    if (res.data.success) {
      orderData.value = res.data.data.order;
      orderItems.value = res.data.data.items;
      paymentData.value = res.data.data.payment;
      
      console.log('Order items:', orderItems.value);
    }
  } catch (err) {
    console.error("Error fetching order detail:", err);
    Swal.fire({
      icon: "error",
      title: "Gagal memuat detail order",
      text: err.response?.data?.message || "Terjadi kesalahan",
    }).then(() => {
      router.back();
    });
  } finally {
    loading.value = false;
  }
};

// Helper functions
const formatCurrency = (value) => {
  return new Intl.NumberFormat("id-ID").format(value || 0);
};

const formatDate = (datetime) => {
  if (!datetime) return '-';
  const date = new Date(datetime);
  return date.toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const formatReceiptDate = (datetime) => {
  if (!datetime) return new Date().toLocaleDateString('id-ID');
  const date = new Date(datetime);
  return date.toLocaleDateString('id-ID', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const getStatusText = (status) => {
  const statusMap = {
    pending: 'Menunggu',
    processing: 'Diproses',
    completed: 'Selesai',
    cancelled: 'Dibatalkan'
  };
  return statusMap[status] || status;
};

const getPaymentStatusText = (status) => {
  const statusMap = {
    pending: 'Menunggu',
    paid: 'Dibayar',
    failed: 'Gagal',
    expired: 'Kadaluarsa'
  };
  return statusMap[status] || status;
};

const getPaymentStatusClass = (status) => {
  const classMap = {
    pending: 'bg-yellow-100 text-yellow-800',
    paid: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800',
    expired: 'bg-gray-100 text-gray-800'
  };
  return classMap[status] || 'bg-gray-100 text-gray-800';
};

const getPaymentMethodText = (method) => {
  const methods = {
    'cash': 'CASH',
    'qris': 'QRIS',
    'bank_transfer': 'BCA',
    'gopay': 'GOPAY'
  };
  return methods[method] || method || 'CASH';
};

const getItemStatusClass = (item) => {
  return 'bg-green-100 text-green-800';
};

const getItemStatusText = (item) => {
  return 'Completed';
};

// Fix image URL
const getProductImageUrl = (imagePath) => {
  if (!imagePath) return null;
  
  if (imagePath.startsWith('http')) {
    return imagePath;
  }
  
  const cleanPath = imagePath.replace('public/', '');
  return `${API_BASE.replace('/api', '')}/storage/${cleanPath}`;
};

// Handle image error
const handleImageError = (event) => {
  console.log('Image failed to load:', event.target.src);
  event.target.style.display = 'none';
  event.target.nextElementSibling.style.display = 'flex';
};

// Print receipt function
const printReceipt = () => {
  // Show loading
  Swal.fire({
    title: 'Mempersiapkan cetak...',
    text: 'Struk sedang dipersiapkan',
    icon: 'info',
    timer: 1000,
    showConfirmButton: false
  });

  // Delay untuk memastikan SweetAlert ditampilkan
  setTimeout(() => {
    const receiptElement = document.getElementById('receipt-content');
    const receiptContent = receiptElement.innerHTML;
    
    // Buat jendela print
    const printWindow = window.open('', '_blank', 'width=400,height=600');
    
    printWindow.document.write(`
      <!DOCTYPE html>
      <html>
      <head>
        <title>Struk ${orderData.value?.id}</title>
        <meta charset="UTF-8">
        <style>
          @import url('https://fonts.googleapis.com/css2?family=Courier+New:ital,wght@0,400;0,700;1,400&display=swap');
          
          * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
          }
          
          body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.2;
            color: #000;
            background: white;
            padding: 10px;
            width: 280px;
            margin: 0 auto;
          }
          
          .receipt-container {
            width: 100%;
            max-width: 280px;
            margin: 0 auto;
          }
          
          .receipt-header {
            text-align: center;
            margin-bottom: 8px;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
          }
          
          .receipt-header h1 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 2px;
          }
          
          .receipt-header p {
            font-size: 10px;
            margin: 1px 0;
          }
          
          .receipt-section {
            margin-bottom: 6px;
          }
          
          .receipt-title {
            font-weight: bold;
            text-align: center;
            margin-bottom: 4px;
            font-size: 11px;
          }
          
          .receipt-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
          }
          
          .receipt-row.total {
            font-weight: bold;
            font-size: 13px;
          }
          
          .receipt-row .label {
            flex: 1;
          }
          
          .receipt-row .value {
            flex: 1;
            text-align: right;
          }
          
          .receipt-divider {
            text-align: center;
            margin: 6px 0;
            font-weight: bold;
          }
          
          .receipt-items {
            margin: 4px 0;
          }
          
          .receipt-item {
            margin-bottom: 4px;
          }
          
          .item-name {
            font-weight: bold;
            margin-bottom: 1px;
          }
          
          .item-variant {
            font-style: italic;
            margin-bottom: 1px;
            font-size: 10px;
          }
          
          .item-details {
            font-size: 10px;
            margin-bottom: 1px;
          }
          
          .item-price {
            text-align: right;
            font-weight: bold;
          }
          
          .receipt-note {
            font-style: italic;
            text-align: center;
            margin: 2px 0;
          }
          
          .receipt-footer {
            text-align: center;
            margin-top: 8px;
            border-top: 1px dashed #000;
            padding-top: 8px;
            font-size: 10px;
          }
          
          .receipt-footer p {
            margin: 2px 0;
          }
          
          @media print {
            body {
              margin: 0;
              padding: 5px;
            }
            .receipt-container {
              box-shadow: none;
            }
          }
        </style>
      </head>
      <body>
        ${receiptContent}
        <script>
          setTimeout(() => {
            window.print();
            setTimeout(() => {
              window.close();
            }, 1000);
          }, 500);
        <\/script>
      </body>
      </html>
    `);
    
    printWindow.document.close();
  }, 1200);
};

onMounted(() => {
  fetchOrderDetail();
});
</script>

<style scoped>
/* Hide receipt content in normal view */
#receipt-content {
  display: none;
}

/* Print styles */
@media print {
  body * {
    visibility: hidden;
  }
  #receipt-content, #receipt-content * {
    visibility: visible;
  }
  #receipt-content {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
  }
}
</style>