<template>
    <div v-if="show" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
            <h2 class="text-xl font-bold mb-4">Card Payment</h2>

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Total Amount</label>
                <input type="text" :value="(totalPrice * conversionRate).toFixed(2)"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-200" readonly>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Selected Currency</label>
                <input type="text" :value="selectedCurrency"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-200" readonly>
            </div>


            <div class="flex justify-between mt-4">
                <button @click="processCardPayment"
                        class="bg-green-500 text-white py-2 px-4 rounded-lg hover:bg-green-600">
                    Pay Now
                </button>
                <button @click="closeModal"
                        class="bg-gray-400 text-white py-2 px-4 rounded-lg hover:bg-gray-500">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed, watch, onMounted } from "vue";
import { router } from "@inertiajs/vue3";

export default {
    props: {
        show: Boolean,
        totalPrice: Number,
        conversionRate: Number,
        selectedCurrency: String,
        cart: Array,
        eftCode: String
    },
    methods: {
        processPayment() {
            alert("Payment Successful!");
            this.$emit("close");
        },
        closeModal() {
            this.$emit("close");
        },
        processCardPayment () {
            const paymentData = {
                amount: this.totalPrice * this.conversionRate,
                type: "EFT",
                eft_code: this.eftCode,
                currency: this.selectedCurrency,
                items: this.cart.map(item => ({
                    item_id: item.id,
                    qty: item.quantity,
                    price: item.price * this.conversionRate,
                    total_price: (item.price * this.conversionRate) * item.quantity
                })),
                change: 0,
                cash: 0,
                terminal: 'MSUGWE01',
                location: 'DEVELOPMENT DESK'
            };

            router.post("/card", paymentData, {
                onSuccess: () => {
                    alert('Payment was successful');
                    this.$emit("resetCart");
                    this.closeModal();
                },
                onError: (errors) => {
                    alert(errors.error);
                }
            });
        }
    },

};
</script>
