<template>
    <vue3-snackbar top right :duration="4000"></vue3-snackbar>
    <Head title="Point Of Sale" />
    <div class="flex space-x-6">
        <!-- Items List -->
        <div class="w-3/4 bg-white shadow-lg rounded-lg p-6" style="height: calc(90vh - 80px); overflow-y: auto;">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Point of Sale</h2>
            <div class="flex justify-between items-center mb-4">
                <input type="text" v-model="search" placeholder="Search items..." class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div v-for="item in items" :key="item.id" class="bg-white p-4 rounded-lg shadow-md cursor-pointer hover:bg-blue-100" @click="addToCart(item)">
                    <h3 class="text-lg font-semibold">{{ item.name }}</h3>
                    <p class="text-gray-600">USD {{ item.price }}</p>
                </div>
            </div>
        </div>

        <!-- Cart Section -->
        <div class="w-1/4 bg-gray-200 p-4 rounded-lg shadow-lg flex flex-col" style="height: 80vh;">
            <h3 class="text-xl font-semibold mb-2">Cart</h3>
            <select v-model="selectedCurrency" @change="updatePrices" class="mb-4 p-2 border rounded-lg">
                <option v-for="currency in currencies" :key="currency.code" :value="currency.currency_code">{{ currency.currency_code }}</option>
            </select>
            <div v-if="cart.length === 0" class="text-gray-600">No items in cart</div>
            <div class="cart-items flex-grow" style="max-height: calc(100% - 120px); overflow-y: auto;">
                <div v-for="(cartItem, index) in cart" :key="index" class="flex justify-between items-center bg-white p-3 rounded-lg mb-2">
                    <span>{{ cartItem.name }} ({{ cartItem.quantity }})</span>
                    <span>{{ selectedCurrency }} {{ (cartItem.quantity * cartItem.price * conversionRate).toFixed(2) }}</span>
                    <button @click="removeItem(index)" class="text-red-500 hover:text-red-700"><i class="fas fa-trash-alt"></i></button>
                </div>
            </div>
            <div class="text-lg font-bold text-gray-800 mt-4">Total: {{ selectedCurrency }} {{ (totalPrice * conversionRate).toFixed(2) }}</div>
            <div class="grid grid-cols-1 gap-2 mt-4 flex-shrink-0">
                <div class="flex flex-col gap-2">
                    <button v-if="enabled_payments == 'CASH' || enabled_payments == 'BOTH'" class="bg-green-500 text-white py-2 rounded-lg hover:bg-green-600" @click="openCashModal">Cash Payment</button>
                    <button v-if="enabled_payments == 'CARD' || enabled_payments == 'BOTH'" class="bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600" @click="showCardModal = true">Card Payment</button>
                </div>
                <div class="flex gap-2">
                    <button class="bg-gray-500 text-white py-2 rounded-lg hover:bg-gray-600 w-full" @click="specialSale">Special Sale</button>
                    <button class="bg-red-500 text-white py-2 rounded-lg hover:bg-red-600 w-full" @click="resetCart">Reset</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cash Payment Modal -->
    <div v-if="showCashModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
            <h2 class="text-xl font-bold mb-4">Cash Payment</h2>
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Total Amount</label>
                <input type="text" :value="(totalPrice * conversionRate).toFixed(2)" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-200" readonly>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Cash Received</label>
                <input type="number" v-model="cashReceived" @input="calculateChange" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Change</label>
                <input type="text" :value="change.toFixed(2)" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-200" readonly>
            </div>
            <div class="flex justify-between mt-4">
                <button @click="processCashPayment" class="bg-green-500 text-white py-2 px-4 rounded-lg hover:bg-green-600">Process Payment</button>
                <button @click="closeCashModal" class="bg-gray-400 text-white py-2 px-4 rounded-lg hover:bg-gray-500">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Card Payment Modal -->
    <div v-if="showCardModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
            <h2 class="text-xl font-bold mb-4">Card Payment</h2>
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Total Amount</label>
                <input type="text" :value="(totalPrice * conversionRate).toFixed(2)" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-200" readonly>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Selected Currency</label>
                <input type="text" :value="selectedCurrency" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-200" readonly>
            </div>
            <div class="flex justify-between mt-4">
                <button @click="processCardPayment" class="bg-green-500 text-white py-2 px-4 rounded-lg hover:bg-green-600">Process Payment</button>
                <button @click="showCardModal = false" class="bg-gray-400 text-white py-2 px-4 rounded-lg hover:bg-gray-500">Cancel</button>
            </div>
        </div>
    </div>
</template>
<script>
import { ref, computed, watch, onMounted } from "vue";
import { router } from "@inertiajs/vue3";
import Layout from "../Shared/Layout.vue";
import { useSnackbar } from "vue3-snackbar";

export default {
    layout: Layout,
    props: {
        items: Array,
        currencies: Array,
        terminal: String,
        location: String
    },
    computed: {
        enabled_payments() {
            return this.$page.props.auth.env.enabled_payments
        },
    },
    setup(props) {
        const search = ref("");
        const cart = ref([]);
        const selectedCurrency = ref("USD");
        const conversionRate = ref(1);
        const showCashModal = ref(false);
        const cashReceived = ref(0);
        const change = ref(0);
        const snackbar = useSnackbar();
        const showCardModal = ref(false);
        const currencyEftCode = ref("840");

        const updatePrices = () => {
            const currency = props.currencies.find(c => c.currency_code === selectedCurrency.value);
            conversionRate.value = currency ? currency.conversion_rate : 1;
            currencyEftCode.value = currency ? currency.eft_code : "840";
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

        const openCashModal = () => {
            showCashModal.value = true;
            cashReceived.value = 0;
            change.value = 0;
        };

        const closeCashModal = () => {
            showCashModal.value = false;
        };

        const calculateChange = () => {
            const total = totalPrice.value * conversionRate.value;
            change.value = Math.max(cashReceived.value - total, 0);
        };

        const processCashPayment = () => {
            if (cashReceived.value < totalPrice.value * conversionRate.value) {
                alert("Insufficient cash received!");
                return;
            }

            const paymentData = {
                amount: totalPrice.value * conversionRate.value,
                type: "CASH",
                currency: selectedCurrency.value,
                items: cart.value.map(item => ({
                    item_id: item.id,
                    name: item.name,
                    qty: item.quantity,
                    price: item.price * conversionRate.value,
                    total_price: (item.price * conversionRate.value) * item.quantity
                })),
                change: change.value,
                cash: cashReceived.value,
                terminal: props.terminal,
                location: props.location
            };

            router.post("/cash", paymentData, {
                onSuccess: () => {
                    snackbar.add({ type: 'success', text: 'Payment was successful' });
                    //printReceipt(paymentData)
                    resetCart();
                    closeCashModal();
                },
                onError: (errors) => {
                    snackbar.add({ type: 'error', text: errors.error });
                }
            });
        };

        const payWithCard = () => {
            showCardModal.value = true;
        };

        const closeCardModal = () => {
            showCardModal.value = false;
        };

        const processCardPayment = () => {
            const paymentData = {
                amount: totalPrice.value * conversionRate.value,
                type: "EFT",
                eft_code: currencyEftCode.value,
                currency: selectedCurrency.value,
                items: cart.value.map(item => ({
                    item_id: item.id,
                    name: item.name,
                    qty: item.quantity,
                    price: item.price * conversionRate.value,
                    total_price: (item.price * conversionRate.value) * item.quantity
                })),
                change: 0,
                cash: 0,
                terminal: props.terminal,
                location: props.location
            };

            router.post("/card", paymentData, {
                onSuccess: () => {
                    snackbar.add({ type: 'success', text: 'Payment was successful' });
                    resetCart();
                    closeCardModal();
                },
                onError: (errors) => {
                    snackbar.add({ type: 'error', text: errors.error });
                }
            });
        };

        watch(selectedCurrency, updatePrices);

        watch(search, (value) => {
            router.get("/pos", { search: value }, { preserveState: true, preserveScroll: true, replace: true });
        });

        onMounted(updatePrices);

        return {
            search,
            cart,
            selectedCurrency,
            conversionRate,
            addToCart,
            removeItem,
            resetCart,
            openCashModal,
            closeCashModal,
            cashReceived,
            change,
            calculateChange,
            processCashPayment,
            payWithCard,
            closeCardModal,
            processCardPayment,
            totalPrice,
            showCashModal,
            showCardModal,
            updatePrices
        };
    }
};
</script>
