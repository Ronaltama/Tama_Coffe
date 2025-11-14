<!-- <script setup>
import { ref } from 'vue';
// import { useRouter } from 'vue-router'; // Uncomment jika perlu navigasi setelah submit

// const router = useRouter(); // Uncomment jika perlu

const productName = ref('');
const category = ref('');
const price = ref(0);
const status = ref('Available');

const handleSubmit = () => {
  alert(
    `Menyimpan produk baru:\nNama: ${productName.value}\nKategori: ${category.value}\nHarga: ${price.value}\nStatus: ${status.value}`
  );
  // Di sini Anda akan memanggil API untuk menyimpan data
  
  // Setelah sukses, kembali ke halaman daftar produk
  // router.push('/superadmin/products');
};
</script>

<template>
  <div class="space-y-6">
    <h1 class="text-3xl font-bold text-gray-900">Add New Product</h1>

    <form @submit.prevent="handleSubmit" class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm max-w-2xl">
      <div class="space-y-4">
        
        <div>
          <label for="productName" class="block text-sm font-medium text-gray-700">Product Name</label>
          <input
            v-model="productName"
            type="text"
            id="productName"
            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-amber-500 focus:border-amber-500"
            required
          >
        </div>

        <div>
          <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
          <select
            v-model="category"
            id="category"
            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-amber-500 focus:border-amber-500"
            required
          >
            <option value="" disabled>Select a category</option>
            <option value="Coffee">Coffee</option>
            <option value="Non Coffee">Non Coffee</option>
            <option value="Snack">Snack</option>
            <option value="Main Course">Main Course</option>
            <option value="Dessert">Dessert</option>
          </select>
        </div>

        <div>
          <label for="price" class="block text-sm font-medium text-gray-700">Price (IDR)</label>
          <input
            v-model.number="price"
            type="number"
            id="price"
            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-amber-500 focus:border-amber-500"
            required
            min="0"
          >
        </div>

        <div>
          <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
          <select
            v-model="status"
            id="status"
            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-amber-500 focus:border-amber-500"
          >
            <option value="Available">Available</option>
            <option value="Out of Stock">Out of Stock</option>
          </select>
        </div>

        <div class="pt-2">
          <button
            type="submit"
            class="px-5 py-2.5 bg-amber-700 text-white rounded-lg hover:bg-amber-800 transition shadow-sm"
          >
            Save Product
          </button>
        </div>

      </div>
    </form>
  </div>
</template> -->