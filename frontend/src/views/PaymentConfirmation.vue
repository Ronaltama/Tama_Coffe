<template>
  <div class="bg-neutral-100 min-h-screen">
    <div class="max-w-md mx-auto bg-white min-h-screen font-sans flex flex-col">
      <header
        class="bg-white border-b border-gray-200 px-4 py-4 flex items-center justify-between"
      >
        <router-link
          :to="`/order/${tableId}/payment`"
          class="p-1 -ml-2 text-gray-800 hover:text-orange-600 transition-colors"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-6 w-6"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M15 19l-7-7 7-7"
            />
          </svg>
        </router-link>
        <h1 class="text-lg font-bold text-gray-900">Order Summary</h1>
        <div class="w-6"></div>
      </header>

      <main
        class="flex-1 pb-24 px-4 overflow-y-auto bg-white"
        v-if="orderData && !isSubmitting"
      >
        <section class="my-4">
          <div
            class="border-2 rounded-xl px-4 py-3 flex items-center justify-between"
            style="border-color: #B85814;"
          >
            <p class="text-sm text-gray-600">Order Type</p>
            <div class="flex items-center gap-2">
              <p class="font-bold text-base text-gray-900">
                {{ orderData.orderType }}
              </p>
              <div
                class="w-7 h-7 rounded-full flex items-center justify-center"
                style="background-color: #B85814;"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-4 w-4 text-white"
                  viewBox="0 0 20 20"
                  fill="currentColor"
                >
                  <path
                    fill-rule="evenodd"
                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                    clip-rule="evenodd"
                  />
                </svg>
              </div>
            </div>
          </div>
        </section>

        <section class="mb-6 text-center">
          <h2 class="text-base font-semibold text-gray-900 mb-3">
            Order Preview
          </h2>
          <div class="bg-gray-50 rounded-xl p-4 mb-4">
            <div class="space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Customer</span>
                <span class="font-semibold text-gray-900">{{
                  orderData.customer.name
                }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Table</span>
                <span class="font-semibold text-gray-900">{{
                  orderData.table
                }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Payment</span>
                <span class="font-semibold text-gray-900">{{
                  orderData.payment === "cash" ? "Bayar di Kasir" : "QRIS"
                }}</span>
              </div>
            </div>
          </div>
        </section>

        <section class="mb-6">
          <h2 class="text-base font-bold text-gray-900 mb-4">Ordered Items</h2>

          <div class="space-y-3">
            <div v-for="item in orderData.items" :key="item.id">
              <div class="flex items-start justify-between mb-1">
                <div class="flex-1">
                  <h3 class="text-sm font-bold text-gray-900">
                    {{ item.quantity }}x {{ item.name }}
                  </h3>
                </div>
                <span class="text-sm font-bold text-gray-900 ml-2"
                  >Rp{{ formatPrice(item.price * item.quantity) }}</span
                >
              </div>

              <div class="ml-4 space-y-0.5">
                <p class="text-xs text-gray-600">
                  • {{ item.variant || "Standard" }}
                </p>
              </div>
            </div>
          </div>
        </section>

        <div class="border-t border-gray-200 my-4"></div>

        <section class="mb-6">
          <div class="space-y-2 mb-3">
            <div
              class="flex justify-between items-center text-sm text-gray-600"
            >
              <span>Subtotal ({{ orderData.items.length }} menu)</span>
              <span>Rp{{ formatPrice(orderData.totals.subTotal) }}</span>
            </div>
          </div>

          <div
            class="flex justify-between items-center font-bold text-base text-gray-900"
          >
            <span>Total</span>
            <span style="color: #B85814;"
              >Rp{{ formatPrice(orderData.totals.total) }}</span
            >
          </div>
        </section>

        <!-- Error Message -->
        <div
          v-if="errorMessage"
          class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg"
        >
          <p class="text-sm text-red-600">{{ errorMessage }}</p>
        </div>
      </main>

      <!-- Loading State -->
      <main
        v-else-if="isSubmitting"
        class="flex-1 flex flex-col items-center justify-center bg-white"
      >
        <div
          class="animate-spin rounded-full h-16 w-16 border-b-4 border-orange-600 mb-4"
        ></div>
        <p class="text-gray-600 font-medium">Memproses pesanan...</p>
        <p class="text-sm text-gray-500 mt-2">Mohon tunggu sebentar</p>
      </main>

      <!-- No Data -->
      <main v-else class="flex-1 flex items-center justify-center bg-white">
        <div class="text-center">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-20 w-20 mx-auto text-gray-300 mb-4"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
            />
          </svg>
          <p class="text-gray-500 mb-4">Tidak ada data pesanan.</p>
          <router-link
            :to="`/order/${tableId}/menu`"
            class="text-orange-600 hover:underline"
          >
            Kembali ke Menu
          </router-link>
        </div>
      </main>

      <footer
        class="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-white border-t border-gray-200 pb-4 pt-3 px-4"
      >
        <button
          @click="processFinalOrder"
          :disabled="isSubmitting"
          class="w-full py-3 text-white text-sm font-bold rounded-xl transition-all shadow-lg active:scale-[0.98] disabled:bg-gray-400 disabled:cursor-not-allowed"
          style="background-color: #B85814;"
          @mouseover="!isSubmitting ? ($event.target.style.backgroundColor='#A04D12') : null"
          @mouseout="!isSubmitting ? ($event.target.style.backgroundColor='#B85814') : null"
        >
          {{ isSubmitting ? "Processing..." : "Submit Order" }}
        </button>
      </footer>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import axios from "axios";

const router = useRouter();
const route = useRoute();

// Terima tableId sebagai prop
const props = defineProps({
  tableId: {
    type: String,
    required: true,
  },
});

const orderData = ref(null);
const isSubmitting = ref(false);
const errorMessage = ref("");

const API_URL = "http://localhost:8000/api/guest";

onMounted(() => {
  const pendingData = localStorage.getItem("pendingOrder");
  if (pendingData) {
    orderData.value = JSON.parse(pendingData);
  } else {
    router.push(`/order/${props.tableId}/menu`);
  }
});

const formatPrice = (price) => {
  return (price || 0).toLocaleString("id-ID");
};

const handlePaymentSuccess = (responseData) => {
  // Simpan response order untuk ditampilkan di success page
  const finalOrderData = {
    ...orderData.value,
    orderId: responseData.order_id,
    paymentId: responseData.payment_id,
    orderStatus: responseData.status,
    transactionTime: new Date().toISOString(),
    finalOrderId: responseData.order_id,
    // Tambahkan booking code dan reservation ID untuk reservasi
    reservationMeta: responseData.booking_code
      ? {
          bookingCode: responseData.booking_code,
          reservationId: responseData.reservation_id,
          reservationStatus: responseData.reservation_status,
        }
      : orderData.value.reservationMeta, // Keep existing meta if not from backend
  };

  localStorage.setItem("finishedOrder", JSON.stringify(finalOrderData));

  // Bersihkan data temporary
  localStorage.removeItem("cart");
  localStorage.removeItem("cartNotes");
  localStorage.removeItem("pendingOrder");
  localStorage.removeItem("reservationDetails"); // Clear reservation details after success

  // Redirect ke success page
  router.push(`/order/${props.tableId}/payment/success`);
};

const processFinalOrder = async () => {
  try {
    isSubmitting.value = true;
    errorMessage.value = "";

    // Ambil table_id dari localStorage atau order metadata
    let tableId = localStorage.getItem("currentTableId");

    // Jika reservasi, ambil dari reservation metadata
    if (
      orderData.value.orderType === "Reservasi" &&
      orderData.value.reservationMeta
    ) {
      tableId = orderData.value.reservationMeta.tableId;
    }

    // Format data untuk backend
    const orderPayload = {
      customer_name: orderData.value.customer.name,
      customer_phone: orderData.value.customer.phone || "",
      customer_email: orderData.value.customer.email || "",
      table_id: tableId || null,
      table_number: orderData.value.table,
      order_type: orderData.value.orderType,
      payment_method: orderData.value.payment,
      notes: orderData.value.notes || "",
      items: orderData.value.items.map((item) => ({
        product_id: item.productId,
        quantity: item.quantity,
        variant: item.variant,
        price: item.price,
      })),
      // Untuk reservasi, kirim data reservasi lengkap (akan dibuat di backend bersamaan dengan order)
      reservation_data:
        orderData.value.orderType === "Reservasi" &&
        orderData.value.reservationMeta
          ? {
              customer_name: orderData.value.customer.name,
              customer_phone: orderData.value.customer.phone,
              table_id: tableId,
              reservation_date: orderData.value.reservationDate,
              reservation_time: orderData.value.reservationTime,
            }
          : null,
    };

    console.log("Sending order to backend:", orderPayload);

    // Submit ke backend
    const response = await axios.post(`${API_URL}/orders`, orderPayload);

    if (response.data.success) {
      console.log("Order berhasil:", response.data);

      // Jika payment method = qris (online), redirect ke Midtrans
      if (orderData.value.payment === "qris") {
        try {
          const snapTokenResponse = await axios.post(
            `${API_URL}/midtrans/create-snap-token`,
            {
              order_id: response.data.data.order_id,
            }
          );

          if (snapTokenResponse.data.success) {
            const snapToken = snapTokenResponse.data.snap_token;

            // Load Midtrans Snap
            window.snap.pay(snapToken, {
              onSuccess: function (result) {
                console.log("Payment success:", result);
                handlePaymentSuccess(response.data.data);
              },
              onPending: function (result) {
                console.log("Payment pending:", result);
                handlePaymentSuccess(response.data.data);
              },
              onError: function (result) {
                console.log("Payment error:", result);
                errorMessage.value = "Pembayaran gagal. Silakan coba lagi.";
                isSubmitting.value = false;
              },
              onClose: function () {
                console.log("Payment popup closed");
                isSubmitting.value = false;
              },
            });
          }
        } catch (error) {
          console.error("Error creating snap token:", error);
          errorMessage.value =
            "Gagal membuat token pembayaran. Silakan coba lagi.";
          isSubmitting.value = false;
        }
      } else {
        // Cash payment - langsung ke success
        handlePaymentSuccess(response.data.data);
      }
    } else {
      errorMessage.value = response.data.message || "Gagal memproses pesanan";
    }
  } catch (error) {
    console.error("Error submitting order:", error);

    if (error.response) {
      // Error dari server
      errorMessage.value =
        error.response.data.message ||
        "Gagal memproses pesanan. Silakan coba lagi.";

      // Tampilkan error validasi jika ada
      if (error.response.data.errors) {
        const errors = Object.values(error.response.data.errors).flat();
        errorMessage.value = errors.join(", ");
      }
    } else if (error.request) {
      // Request terkirim tapi tidak ada response
      errorMessage.value =
        "Tidak dapat terhubung ke server. Periksa koneksi internet Anda.";
    } else {
      // Error lainnya
      errorMessage.value = "Terjadi kesalahan. Silakan coba lagi.";
    }
  } finally {
    isSubmitting.value = false;
  }
};
</script>

<style scoped>
main {
  overflow-y: auto;
}
</style>
