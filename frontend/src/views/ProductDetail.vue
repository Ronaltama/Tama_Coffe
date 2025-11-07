<template>
    <div class="bg-neutral-100 min-h-screen">
        <div class="max-w-md mx-auto bg-white min-h-screen font-sans">

            <!-- Header Image Section -->
            <section class="relative">
                <img :src="product.imageUrl" :alt="product.name" class="w-full h-80 object-cover" />

                <!-- Close Button -->
                <router-link to="/user/menu"
                    class="absolute top-5 right-4 w-10 h-10 bg-white rounded-full shadow-lg flex items-center justify-center hover:bg-gray-50 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </router-link>
            </section>

            <!-- Content Section -->
            <section class="bg-white px-6 py-6">

                <!-- Product Title & Price -->
                <div class="mb-4">
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ product.name }}</h1>
                    <p class="text-2xl font-bold text-orange-600">Rp{{ product.price }}</p>
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <p class="text-gray-600 text-sm leading-relaxed">
                        {{ product.description }}
                    </p>
                </div>

                <!-- Add-ons Section -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-lg font-bold text-gray-900">Add-ons</h2>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-500">Must be selected max. 2</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>

                    <!-- Add-on Item -->
                    <div v-for="addon in addons" :key="addon.id" class="bg-gray-50 rounded-lg p-4 mb-3">
                        <div class="flex items-center justify-between">
                            <span class="text-base font-medium text-gray-900">{{ addon.name }}</span>
                            <div class="flex items-center space-x-3">
                                <button @click="decrementAddon(addon.id)"
                                    class="w-8 h-8 rounded-full border-2 border-orange-500 text-orange-500 flex items-center justify-center hover:bg-orange-50 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <span class="text-lg font-bold text-gray-900 w-6 text-center">{{ addon.quantity
                                    }}</span>
                                <button @click="incrementAddon(addon.id)"
                                    class="w-8 h-8 rounded-full bg-orange-500 text-white flex items-center justify-center hover:bg-orange-600 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes Section -->
                <div class="mb-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-2">Notes</h2>
                    <p class="text-sm text-gray-500 mb-3">Optional</p>
                    <textarea v-model="notes" placeholder="Example: Make my coffee extra sweet!" rows="4"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500"></textarea>
                </div>

                <!-- Total Order Section (Inside Content) -->
                <div class="bg-white border-t border-gray-200 pt-6 pb-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-lg font-bold text-gray-900">Total Order</span>
                        <div class="flex items-center space-x-3">
                            <button @click="decrementTotal"
                                class="w-10 h-10 rounded-full border-2 border-orange-500 text-orange-500 flex items-center justify-center hover:bg-orange-50 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                            <span class="text-xl font-bold text-gray-900 w-8 text-center">{{ totalQuantity }}</span>
                            <button @click="incrementTotal"
                                class="w-10 h-10 rounded-full bg-orange-500 text-white flex items-center justify-center hover:bg-orange-600 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button
                        class="w-full py-4 bg-orange-600 hover:bg-orange-700 text-white text-lg font-bold rounded-xl transition-colors shadow-lg">
                        Add Orders - Rp{{ totalPrice }}
                    </button>
                </div>

            </section>

        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

// --- DATA DUMMY ---
const product = ref({
    id: 'c1',
    name: 'Iced Caramel Macchiato',
    price: '25.000',
    imageUrl: 'https://images.unsplash.com/photo-1517487881594-2787fef5ebf7?w=800&q=80',
    description: 'A delicious blend of rich espresso, creamy milk, and sweet caramel syrup, served over ice. A perfect pick-me-up for any time of day.'
});

// Add-ons
const addons = ref([
    { id: 1, name: 'Whipped Cream', quantity: 2 }
]);

// Notes
const notes = ref('');

// Total Order Quantity
const totalQuantity = ref(1);

// --- METHODS ---
const incrementAddon = (id) => {
    const addon = addons.value.find(a => a.id === id);
    if (addon && addon.quantity < 2) {
        addon.quantity++;
    }
};

const decrementAddon = (id) => {
    const addon = addons.value.find(a => a.id === id);
    if (addon && addon.quantity > 0) {
        addon.quantity--;
    }
};

const incrementTotal = () => {
    totalQuantity.value++;
};

const decrementTotal = () => {
    if (totalQuantity.value > 1) {
        totalQuantity.value--;
    }
};

// Computed total price
const totalPrice = computed(() => {
    const basePrice = parseInt(product.value.price.replace('.', ''));
    return (basePrice * totalQuantity.value).toLocaleString('id-ID');
});
</script>

<style scoped>
/* Styling sudah menggunakan Tailwind CSS */
</style>