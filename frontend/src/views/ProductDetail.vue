<template>
    <div class="bg-neutral-100 min-h-screen">
        <div class="max-w-md mx-auto bg-white min-h-screen font-sans">
            <!-- Product Image Section -->
            <section class="relative">
                <img 
                    :src="product.imageUrl" 
                    :alt="product.name" 
                    class="w-full h-80 object-cover"
                />
                
                <!-- Close Button -->
                <router-link 
                    to="/user/menu"
                    class="absolute top-5 right-4 w-10 h-10 bg-white rounded-full shadow-lg flex items-center justify-center hover:bg-gray-50 transition-colors"
                    aria-label="Close and return to menu"
                >
                    <svg 
                        xmlns="http://www.w3.org/2000/svg" 
                        class="h-6 w-6 text-gray-700"
                        fill="none" 
                        viewBox="0 0 24 24" 
                        stroke="currentColor"
                    >
                        <path 
                            stroke-linecap="round" 
                            stroke-linejoin="round" 
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </router-link>
            </section>

            <!-- Product Details Section -->
            <section class="bg-white px-6 py-6">
                <!-- Product Info -->
                <div class="mb-4">
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ product.name }}</h1>
                    <p class="text-2xl font-bold text-orange-600">Rp {{ formatPrice(product.price) }}</p>
                </div>

                <!-- Product Description -->
                <div class="mb-6">
                    <p class="text-gray-600 text-sm leading-relaxed">
                        {{ product.description }}
                    </p>
                </div>

                <!-- Variants Selection -->
                <div class="mb-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-3">Variants</h2>
                    <div class="flex gap-3">
                        <button 
                            @click="selectedTemperature = 'iced'"
                            :class="[
                                'flex-1 py-3 px-4 rounded-lg font-medium transition-colors border-2',
                                selectedTemperature === 'iced'
                                    ? 'bg-orange-500 text-white border-orange-500'
                                    : 'bg-white text-gray-700 border-gray-300 hover:border-orange-500'
                            ]"
                            :aria-pressed="selectedTemperature === 'iced'"
                        >
                            ❄️ Iced
                        </button>
                        <button 
                            @click="selectedTemperature = 'hot'"
                            :class="[
                                'flex-1 py-3 px-4 rounded-lg font-medium transition-colors border-2',
                                selectedTemperature === 'hot'
                                    ? 'bg-orange-500 text-white border-orange-500'
                                    : 'bg-white text-gray-700 border-gray-300 hover:border-orange-500'
                            ]"
                            :aria-pressed="selectedTemperature === 'hot'"
                        >
                            🔥 Hot
                        </button>
                    </div>
                </div>

                <!-- Order Controls -->
                <div class="bg-white border-t border-gray-200 pt-6 pb-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-lg font-bold text-gray-900">Total Order</span>
                        <div class="flex items-center space-x-3">
                            <!-- Decrement Button -->
                            <button 
                                @click="decrementTotal"
                                :disabled="totalQuantity <= 1"
                                :class="[
                                    'w-10 h-10 rounded-full border-2 flex items-center justify-center transition-colors',
                                    totalQuantity <= 1
                                        ? 'border-gray-300 text-gray-300 cursor-not-allowed'
                                        : 'border-orange-500 text-orange-500 hover:bg-orange-50'
                                ]"
                                aria-label="Decrease quantity"
                            >
                                <svg 
                                    xmlns="http://www.w3.org/2000/svg" 
                                    class="h-5 w-5"
                                    viewBox="0 0 20 20" 
                                    fill="currentColor"
                                >
                                    <path 
                                        fill-rule="evenodd" 
                                        d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </button>
                            
                            <!-- Quantity Display -->
                            <span class="text-xl font-bold text-gray-900 w-8 text-center">
                                {{ totalQuantity }}
                            </span>
                            
                            <!-- Increment Button -->
                            <button 
                                @click="incrementTotal"
                                :disabled="totalQuantity >= 99"
                                class="w-10 h-10 rounded-full bg-orange-500 text-white flex items-center justify-center hover:bg-orange-600 transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed"
                                aria-label="Increase quantity"
                            >
                                <svg 
                                    xmlns="http://www.w3.org/2000/svg" 
                                    class="h-5 w-5"
                                    viewBox="0 0 20 20" 
                                    fill="currentColor"
                                >
                                    <path 
                                        fill-rule="evenodd"
                                        d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Add to Cart Button -->
                    <button 
                        @click="handleAddToCart"
                        class="w-full py-4 bg-orange-600 hover:bg-orange-700 text-white text-lg font-bold rounded-xl transition-colors shadow-lg disabled:bg-gray-400 disabled:cursor-not-allowed"
                        :disabled="isAddingToCart"
                    >
                        {{ isAddingToCart ? 'Adding...' : `Add Orders - Rp ${totalPrice}` }}
                    </button>
                </div>
            </section>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';

// Router instance
const router = useRouter();

// Product data (in real app, this would come from props or API)
const product = ref({
    id: 'c1',
    name: 'Caramel Macchiato',
    price: 25000, // Store as number for easier calculations
    imageUrl: 'https://images.unsplash.com/photo-1517487881594-2787fef5ebf7?w=800&q=80',
    description: 'A delicious blend of rich espresso, creamy milk, and sweet caramel syrup, served over ice. A perfect pick-me-up for any time of day.'
});

// State
const selectedTemperature = ref('iced');
const totalQuantity = ref(1);
const isAddingToCart = ref(false);

// Methods
const incrementTotal = () => {
    if (totalQuantity.value < 99) {
        totalQuantity.value++;
    }
};

const decrementTotal = () => {
    if (totalQuantity.value > 1) {
        totalQuantity.value--;
    }
};

const formatPrice = (price) => {
    return price.toLocaleString('id-ID');
};

const handleAddToCart = async () => {
    try {
        isAddingToCart.value = true;

        // Get current cart from localStorage
        let cart = JSON.parse(localStorage.getItem('cart') || '[]');

        // Create unique ID for this cart item (product + variant)
        const cartItemId = `${product.value.id}-${selectedTemperature.value}`;

        // Check if item with same variant already exists
        const existingItemIndex = cart.findIndex(item => item.id === cartItemId);

        if (existingItemIndex !== -1) {
            // Update existing item quantity
            cart[existingItemIndex].quantity += totalQuantity.value;
        } else {
            // Add new item to cart
            const newItem = {
                id: cartItemId,
                name: product.value.name,
                price: product.value.price,
                quantity: totalQuantity.value,
                imageUrl: product.value.imageUrl,
                productId: product.value.id,
                variant: selectedTemperature.value
            };
            cart.push(newItem);
        }

        // Save updated cart to localStorage
        localStorage.setItem('cart', JSON.stringify(cart));

        // Small delay for better UX
        await new Promise(resolve => setTimeout(resolve, 300));

        // Navigate back to menu
        router.push('/user/menu');
    } catch (error) {
        console.error('Error adding to cart:', error);
        alert('Failed to add item to cart. Please try again.');
    } finally {
        isAddingToCart.value = false;
    }
};

// Computed properties
const totalPrice = computed(() => {
    return formatPrice(product.value.price * totalQuantity.value);
});
</script>

<style scoped>

</style>