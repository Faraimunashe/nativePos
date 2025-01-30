<template>
    <Head title="Point Of Sale" />
    <div class="flex space-x-6">
        <!-- Items List -->
        <div class="w-3/4 bg-white shadow-lg rounded-lg p-6" style="height: calc(90vh - 80px); overflow-y: auto;">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Point of Sale</h2>

            <div class="flex justify-between items-center mb-4">
                <input type="text" v-model="search" placeholder="Search items..."
                    class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div v-for="item in items" :key="item.id" class="bg-white p-4 rounded-lg shadow-md cursor-pointer hover:bg-blue-100"
                @click="addToCart(item)">
                    <h3 class="text-lg font-semibold">{{ item.name }}</h3>
                    <p class="text-gray-600">USD {{ item.price }}</p>
                </div>
            </div>
        </div>

        <!-- Cart Section -->
        <div class="w-1/4 bg-gray-200 p-4 rounded-lg shadow-lg flex flex-col" style="height: 80vh;">
            <h3 class="text-xl font-semibold mb-2">Cart</h3>

            <!-- Currency Selection -->
            <select v-model="selectedCurrency" @change="updatePrices" class="mb-4 p-2 border rounded-lg">
                <option v-for="currency in currencies" :key="currency.code" :value="currency.currency_code">
                    {{ currency.currency_code }}
                </option>
            </select>

            <div v-if="cart.length === 0" class="text-gray-600">No items in cart</div>
            <div class="cart-items flex-grow" style="max-height: calc(100% - 120px); overflow-y: auto;">
                <div v-for="(cartItem, index) in cart" :key="index" class="flex justify-between items-center bg-white p-3 rounded-lg mb-2">
                    <span>{{ cartItem.name }} ({{ cartItem.quantity }})</span>
                    <span>{{ selectedCurrency }} {{ (cartItem.quantity * cartItem.price * conversionRate).toFixed(2) }}</span>
                    <button @click="removeItem(index)" class="text-red-500 hover:text-red-700">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
            <div class="text-lg font-bold text-gray-800 mt-4">Total: {{ selectedCurrency }} {{ (totalPrice * conversionRate).toFixed(2) }}</div>
            <div class="grid grid-cols-1 gap-2 mt-4 flex-shrink-0">
                <button class="bg-green-500 text-white py-2 rounded-lg hover:bg-green-600" @click="payWithCash">Cash Payment</button>
                <button class="bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600" @click="payWithCard">Card Payment</button>
                <button class="bg-red-500 text-white py-2 rounded-lg hover:bg-red-600" @click="resetCart">Reset Cart</button>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed, watch, onMounted } from "vue";
import { router } from "@inertiajs/vue3";
import Layout from "../Shared/Layout.vue";

export default {
    layout: Layout,
    props: {
        items: Array,
        currencies: Array,
    },
    setup(props) {
        const search = ref("");
        const cart = ref([]);
        const selectedCurrency = ref("USD");
        const conversionRate = ref(1);

        const updatePrices = () => {
            const currency = props.currencies.find(c => c.currency_code === selectedCurrency.value);
            conversionRate.value = currency ? currency.conversion_rate : 1;
        };

        const addToCart = (item) => {
            const existingItem = cart.value.find(i => i.id === item.id);
            if (existingItem) {
                existingItem.quantity++;
            } else {
                cart.value.push({ ...item, quantity: 1 });
            }
        };

        const removeItem = (index) => {
            cart.value.splice(index, 1);
        };

        const resetCart = () => {
            cart.value = [];
        };

        const totalPrice = computed(() => {
            return cart.value.reduce((sum, item) => sum + item.price * item.quantity, 0);
        });

        const payWithCash = () => {
            alert("Processing Cash Payment...");
        };

        const payWithCard = () => {
            alert("Processing Card Payment...");
        };

        watch(selectedCurrency, () => {
            updatePrices();
        });

        watch(search, (value) => {
            router.get(
                "/pos",
                { search: value },
                {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                }
            );
        });

        onMounted(() => {
            updatePrices();
        });

        return {
            search,
            cart,
            selectedCurrency,
            conversionRate,
            addToCart,
            removeItem,
            resetCart,
            payWithCash,
            payWithCard,
            totalPrice,
            updatePrices
        };
    },
};
</script>
