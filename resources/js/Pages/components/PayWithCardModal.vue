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
                <button @click="processPayment"
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
export default {
    props: {
        show: Boolean,
        totalPrice: Number,
        conversionRate: Number,
        selectedCurrency: String
    },
    methods: {
        processPayment() {
                alert("Payment Successful!");
                this.$emit("close");
        },
        closeModal() {
            this.$emit("close");
        }
    }
};
</script>
