<template>
  <div class="order-page p-4 max-w-4xl mx-auto">
    <header class="mb-6">
      <h1 class="text-2xl font-semibold">Meja {{ tableId }}</h1>
      <p v-if="tableInfo">Status: <strong>{{ tableInfo.status ?? '-' }}</strong></p>
      <p v-else class="text-sm text-gray-500">Memuat info meja...</p>
    </header>

    <section class="grid lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2">
        <h2 class="text-lg font-medium mb-3">Daftar Produk</h2>
        <div v-if="loadingProducts">Memuat produk...</div>
        <div v-else class="grid sm:grid-cols-2 gap-4">
          <div v-for="p in products" :key="p.id" class="p-3 border rounded flex justify-between">
            <div>
              <div class="font-semibold">{{ p.name }}</div>
              <div class="text-sm text-gray-600" v-if="p.description">{{ p.description }}</div>
              <div class="text-indigo-600 font-semibold mt-2">Rp {{ formatPrice(p.price) }}</div>
            </div>
            <div>
              <button @click="addToCart(p)" class="px-3 py-1 bg-green-600 text-white rounded">+</button>
            </div>
          </div>
        </div>
      </div>

      <aside class="bg-white border rounded p-4">
        <h2 class="text-lg font-medium mb-3">Keranjang</h2>
        <div v-if="!cart.length" class="text-sm text-gray-500">Keranjang kosong</div>

        <ul v-else class="space-y-2">
          <li v-for="item in cart" :key="item.product.id" class="flex justify-between items-center">
            <div>
              <div class="font-medium">{{ item.product.name }}</div>
              <div class="text-sm text-gray-600">Rp {{ formatPrice(item.product.price) }} x {{ item.qty }}</div>
            </div>
            <div class="flex items-center gap-2">
              <button @click="decrease(item)" class="px-2">-</button>
              <div>{{ item.qty }}</div>
              <button @click="increase(item)" class="px-2">+</button>
            </div>
          </li>
        </ul>

        <div class="mt-4 border-t pt-3">
          <div class="flex justify-between mb-3">
            <div class="font-semibold">Total</div>
            <div class="font-semibold">Rp {{ formatPrice(cartTotal) }}</div>
          </div>
          <button @click="submitOrder" :disabled="submitting || !cart.length"
            class="w-full py-2 bg-blue-600 text-white rounded">
            {{ submitting ? 'Mengirim...' : 'Kirim Pesanan' }}
          </button>
          <div v-if="success" class="text-green-600 mt-2">{{ success }}</div>
          <div v-if="error" class="text-red-600 mt-2">{{ error }}</div>
        </div>
      </aside>
    </section>
  </div>
</template>

<script>
export default {
  name: 'ScanOrderPage',
  data() {
    return {
      tableId: this.$route.params.id || null,
      tableInfo: null,
      products: [],
      loadingProducts: true,
      cart: [],
      error: null,
      success: null,
      submitting: false,
    };
  },
  computed: {
    cartTotal() {
      return this.cart.reduce((s, i) => s + (Number(i.product.price || 0) * i.qty), 0);
    }
  },
  mounted() {
    if (!this.tableId) {
      this.error = 'ID meja tidak ditemukan.';
      return;
    }
    this.fetchTable(); // ambil info meja melalui API
    this.fetchProducts(); // ambil daftar produk melalui API
  },
  methods: {
    // ambil info meja
    async fetchTable() {
      try {
        const res = await fetch(`/api/order/${encodeURIComponent(this.tableId)}`);
        if (!res.ok) throw new Error('Gagal memuat data meja');
        this.tableInfo = await res.json();
      } catch (e) {
        this.error = e.message;
      }
    },
    // ambil daftar produk
    async fetchProducts() {
      this.loadingProducts = true;
      try {
        const res = await fetch('/api/products');
        if (!res.ok) throw new Error('Gagal memuat produk');
        const data = await res.json();
        this.products = Array.isArray(data) ? data : (data.data ?? []);
      } catch (e) { 
        this.error = e.message;
      } finally {
        this.loadingProducts = false;
      }
    },
    addToCart(product) {
      const found = this.cart.find(i => i.product.id === product.id);
      if (found) found.qty++;
      else this.cart.push({ product, qty: 1 });
    },
    increase(item) { item.qty++; },
    decrease(item) {
      if (item.qty > 1) item.qty--; else this.remove(item);
    },
    remove(item) {
      this.cart = this.cart.filter(i => i !== item);
    },
    formatPrice(v) {
      return Number(v || 0).toLocaleString('id-ID');
    },
    async submitOrder() {
      if (!this.cart.length) return;
      this.submitting = true;
      this.error = null;
      this.success = null;

      const payload = {
        table_id: this.tableId,
        items: this.cart.map(i => ({ product_id: i.product.id, qty: i.qty }))
      };

      try {
        const res = await fetch('/api/orders', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        });
        if (!res.ok) {
          const err = await res.json().catch(() => null);
          throw new Error(err?.message || 'Gagal mengirim pesanan');
        }
        this.success = 'Pesanan berhasil dikirim';
        this.cart = [];
      } catch (e) {
        this.error = e.message;
      } finally {
        this.submitting = false;
      }
    }
  }
};
</script>

<style scoped>
.order-page {
  max-width: 1100px;
  margin: 0 auto;
}
</style>