<script setup>
import { ref, onMounted, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";
import Swal from "sweetalert2";

const route = useRoute();
const router = useRouter();
const API_BASE = "http://localhost:8000/api";

const orderData = ref(null);
const loading = ref(true);
const orderId = route.params.id;

// Fetch order detail
const fetchOrderDetail = async () => {
    try {
        loading.value = true;
        const token = localStorage.getItem("token");

        const res = await axios.get(`${API_BASE}/orders/${orderId}/detail`, {
            headers: { Authorization: `Bearer ${token}` },
        });

        if (res.data.success) {
            orderData.value = res.data.data;
        }
    } catch (err) {
        console.error("Error fetching order detail:", err);
        Swal.fire({
            icon: "error",
            title: "Gagal Memuat Data",
            text: err.response?.data?.message || "Terjadi kesalahan",
        }).then(() => {
            router.push("/admin/confirm-order");
        });
    } finally {
        loading.value = false;
    }
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

// Format helpers
const formatCurrency = (value) => {
    return new Intl.NumberFormat("id-ID").format(value || 0);
};

const formatDate = (datetime) => {
    if (!datetime) return "-";
    const date = new Date(datetime);
    return date.toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const getStatusBadge = (status) => {
    const badges = {
        pending: { class: "bg-yellow-100 text-yellow-700", text: "Menunggu" },
        processing: { class: "bg-blue-100 text-blue-700", text: "Diproses" },
        completed: { class: "bg-green-100 text-green-700", text: "Selesai" },
        cancelled: { class: "bg-red-100 text-red-700", text: "Dibatalkan" },
    };
    return badges[status] || badges.pending;
};

const getPaymentMethodText = (method, type) => {
    if (method === "cash") return "Cash / Tunai";
    if (method === "midtrans" && type === "qris") return "QRIS";
    return type?.toUpperCase() || "Online Payment";
};

onMounted(() => {
    fetchOrderDetail();
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 print:bg-white">
        <!-- Loading -->
        <div v-if="loading" class="flex items-center justify-center min-h-screen">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-600"></div>
        </div>

        <!-- Content -->
        <div v-else-if="orderData" class="max-w-4xl mx-auto p-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6 print:hidden">
                <button @click="router.push('/admin/confirm-order')"
                    class="flex items-center gap-2 text-gray-600 hover:text-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span class="font-medium">Kembali</span>
                </button>

                <button v-if="orderData.order.status === 'completed'" @click="printReceipt"
                    class="flex items-center gap-2 px-4 py-2 text-white rounded-lg font-medium transition-colors"
                    style="background-color: #B85814;"
                    onmouseover="this.style.backgroundColor='#A04D12'"
                    onmouseout="this.style.backgroundColor='#B85814'">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Cetak Struk
                </button>
            </div>

            <!-- Receipt Hidden Area -->
            <div id="receipt-content" class="hidden">
                <div class="receipt-container"
                    style="width:270px; margin:0 auto; font-family:'Courier New', monospace; font-size:12px; line-height:1.3;">

                    <!-- Header -->
                    <div style="text-align:center; margin-bottom:10px;">
                        <div style="font-weight:bold; font-size:14px;">TAMA COFFEE</div>
                        <div style="font-size:10px;">Jl. Contoh No. 123, Kota Anda</div>
                        <div style="font-size:10px;">Telp: (021) 1234-5678</div>
                    </div>

                    <hr style="border-top:1px dashed #000; margin:8px 0;">

                    <!-- Order Info -->
                    <div style="font-size:12px; margin-bottom:8px;">
                        <div style="display:flex; justify-content:space-between;">
                            <span>Order ID:</span>
                            <span>{{ orderData.order.id }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between;">
                            <span>Tanggal:</span>
                            <span>{{ formatDate(orderData.order.created_at) }}</span>
                        </div>
                    </div>

                    <hr style="border-top:1px dashed #000; margin:8px 0;">

                    <!-- Customer -->
                    <div style="text-align:center; font-weight:bold; margin-bottom:4px;">CUSTOMER</div>
                    <div style="font-size:12px; margin-bottom:8px;">
                        <div style="display:flex; justify-content:space-between;">
                            <span>Nama:</span>
                            <span>{{ orderData.order.customer_name }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between;">
                            <span>Telepon:</span>
                            <span>{{ orderData.order.customer_phone || '-' }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between;">
                            <span>Meja:</span>
                            <span>{{ orderData.order.table_number || 'Take Away' }}</span>
                        </div>
                    </div>

                    <hr style="border-top:1px dashed #000; margin:8px 0;">

                    <!-- Payment Method -->
                    <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                        <span>Metode Bayar:</span>
                        <span>{{ getPaymentMethodText(orderData.payment?.method, orderData.payment?.payment_type)
                            }}</span>
                    </div>

                    <hr style="border-top:1px dashed #000; margin:8px 0;">

                    <!-- ITEMS -->
                    <div style="text-align:center; font-weight:bold; margin-bottom:4px;">ITEMS</div>

                    <div class="receipt-items" style="font-size:12px;">
                        <div v-for="item in orderData.items" :key="item.id" style="margin-bottom:10px;">
                            <div style="font-weight:bold;">{{ item.product_name }}</div>
                            <div style="font-size:11px;">{{ item.quantity }} × Rp{{ formatCurrency(item.unit_price) }}
                            </div>
                            <div style="font-size:11px; text-align:right; font-weight:bold;">Rp{{
                                formatCurrency(item.subtotal) }}</div>
                        </div>
                    </div>

                    <hr style="border-top:1px dashed #000; margin:8px 0;">

                    <!-- Notes -->
                    <div v-if="orderData.order.note" style="margin-bottom:10px;">
                        <div style="text-align:center; font-weight:bold;">CATATAN</div>
                        <div style="font-style:italic; font-size:11px; text-align:center;">{{ orderData.order.note }}
                        </div>
                    </div>

                    <hr style="border-top:1px dashed #000; margin:8px 0;">

                    <!-- Summary -->
                    <div style="text-align:center; font-weight:bold;">RINGKASAN</div>

                    <div style="font-size:12px; margin:8px 0;">
                        <div style="display:flex; justify-content:space-between;">
                            <span>Subtotal:</span>
                            <span>Rp{{ formatCurrency(orderData.order.total_price) }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between;">
                            <span>Pajak:</span>
                            <span>Rp0</span>
                        </div>
                        <div style="display:flex; justify-content:space-between;">
                            <span>Biaya Layanan:</span>
                            <span>Rp0</span>
                        </div>
                    </div>

                    <div style="border-top:2px solid #000; padding-top:5px; margin-top:5px;">
                        <div style="display:flex; justify-content:space-between; font-weight:bold; font-size:13px;">
                            <span>TOTAL:</span>
                            <span>Rp{{ formatCurrency(orderData.order.total_price) }}</span>
                        </div>
                    </div>

                    <hr style="border-top:1px dashed #000; margin:8px 0;">

                    <!-- Footer -->
                    <div style="text-align:center; font-size:10px;">
                        <div>Terima kasih atas kunjungan Anda</div>
                        <div>*** Struk ini sebagai bukti pembayaran ***</div>
                    </div>
                </div>
            </div>



            <!-- Main Card -->
            <div class="bg-white rounded-xl shadow-lg p-8 print:shadow-none">
                <!-- Order Header -->
                <div class="flex justify-between items-start mb-6 pb-6 border-b">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">
                            {{ orderData.order.id }}
                        </h1>
                        <p class="text-gray-500 text-sm">{{ formatDate(orderData.order.created_at) }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-gray-500 text-sm mb-1">Total</p>
                        <p class="text-3xl font-bold text-orange-600">
                            Rp{{ formatCurrency(orderData.order.total_price) }}
                        </p>
                    </div>
                </div>

                <!-- Customer Info -->
                <div class="mb-6 pb-6 border-b">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Informasi Customer</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Nama Customer</p>
                            <p class="font-medium text-gray-900">{{ orderData.order.customer_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">No. Telepon</p>
                            <p class="font-medium text-gray-900">
                                {{ orderData.order.customer_phone || "-" }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Nomor Meja</p>
                            <p class="font-medium text-gray-900">
                                {{ orderData.order.table_number || "Take Away" }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Metode Pembayaran</p>
                            <p class="font-medium text-gray-900">
                                {{
                                    getPaymentMethodText(
                                        orderData.payment?.method,
                                        orderData.payment?.payment_type
                                    )
                                }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Status Badge -->
                <div class="mb-6">
                    <span :class="getStatusBadge(orderData.order.status).class"
                        class="inline-block px-4 py-2 rounded-full text-sm font-semibold">
                        {{ getStatusBadge(orderData.order.status).text }}
                    </span>
                </div>

                <!-- Order Items -->
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Item Pesanan</h2>

                    <!-- Table Header -->
                    <div
                        class="hidden md:grid md:grid-cols-12 gap-4 mb-3 pb-3 border-b font-semibold text-gray-700 text-sm">
                        <div class="col-span-1">Img</div>
                        <div class="col-span-5">Produk</div>
                        <div class="col-span-2 text-center">Harga</div>
                        <div class="col-span-2 text-center">Qty</div>
                        <div class="col-span-2 text-right">Total</div>
                    </div>

                    <!-- Items -->
                    <div class="space-y-4">
                        <div v-for="item in orderData.items" :key="item.id"
                            class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center py-3 border-b last:border-0">
                            <!-- Image -->
                            <div class="col-span-1">
                                <img :src="item.product_image ? `http://localhost:8000/storage/${item.product_image}` : 'https://via.placeholder.com/80'"
                                    :alt="item.product_name" class="w-16 h-16 rounded-lg object-cover"
                                    @error="$event.target.src = 'https://via.placeholder.com/80'" />
                            </div>

                            <!-- Product Info -->
                            <div class="col-span-5">
                                <h3 class="font-semibold text-gray-900">{{ item.product_name }}</h3>
                                <p v-if="item.variant" class="text-sm text-gray-500">
                                    Varian: {{ item.variant }}
                                </p>
                            </div>

                            <!-- Price -->
                            <div class="col-span-2 text-center">
                                <p class="font-medium text-gray-700">Rp{{ formatCurrency(item.unit_price) }}</p>
                            </div>

                            <!-- Quantity -->
                            <div class="col-span-2 text-center">
                                <p class="font-semibold text-gray-900">{{ item.quantity }}</p>
                            </div>

                            <!-- Subtotal -->
                            <div class="col-span-2 text-right">
                                <p class="font-bold text-gray-900">Rp{{ formatCurrency(item.subtotal) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Note -->
                <div v-if="orderData.order.note" class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Catatan Customer</h3>
                    <p class="text-gray-600">{{ orderData.order.note }}</p>
                </div>

                <!-- Order Summary -->
                <div class="border-t pt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Pesanan</h2>
                    <div class="space-y-2">
                        <div class="flex justify-between text-gray-700">
                            <span>Subtotal ({{ orderData.items.length }} item)</span>
                            <span>Rp{{ formatCurrency(orderData.order.total_price) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-700">
                            <span>Pajak</span>
                            <span>Rp0</span>
                        </div>
                        <div class="flex justify-between text-xl font-bold text-gray-900 pt-2 border-t">
                            <span>Total Bayar</span>
                            <span class="text-orange-600">Rp{{ formatCurrency(orderData.order.total_price) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Payment Status -->
                <div v-if="orderData.payment" class="mt-6 p-4 bg-blue-50 rounded-lg">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-600">Status Pembayaran</p>
                            <p class="font-semibold text-gray-900">
                                {{
                                    orderData.payment.status === "paid"
                                        ? "Sudah Dibayar"
                                        : orderData.payment.status === "pending"
                                            ? "Menunggu Pembayaran"
                                            : "Gagal"
                                }}
                            </p>
                        </div>
                        <div :class="orderData.payment.status === 'paid'
                            ? 'bg-green-500'
                            : 'bg-yellow-500'
                            " class="w-3 h-3 rounded-full"></div>
                    </div>
                </div>

                <!-- Processed By -->
                <div class="mt-6 text-center text-sm text-gray-500">
                    <p>Diproses oleh: {{ orderData.order.processed_by || "System" }}</p>
                </div>
            </div>
        </div>

        <!-- No Data -->
        <div v-else class="flex items-center justify-center min-h-screen">
            <div class="text-center">
                <p class="text-gray-500 mb-4">Data order tidak ditemukan</p>
                <button @click="router.push('/admin/confirm-order')" class="text-orange-600 hover:underline">
                    Kembali ke Dashboard
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Mobile Responsive Styles */
@media (max-width: 768px) {
    .p-6 {
        padding: 1rem !important;
    }
    
    .p-8 {
        padding: 1.5rem !important;
    }
    
    .text-3xl {
        font-size: 1.5rem !important;
        line-height: 2rem !important;
    }
    
    /* Stack customer info on mobile */
    .grid-cols-2 {
        grid-template-columns: 1fr !important;
    }

    /* Adjust item list spacing */
    .space-y-4 > div {
        padding-bottom: 1rem !important;
        margin-bottom: 1rem !important;
    }
}

/* Hide receipt content in normal view */
#receipt-content {
    display: none;
}

/* Print styles */
@media print {
    body * {
        visibility: hidden;
    }

    #receipt-content,
    #receipt-content * {
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