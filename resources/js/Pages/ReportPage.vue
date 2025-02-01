<template>
    <Head title="My Reports" />
    <div class="max-w-7xl py-6">
        <div class="bg-white shadow-md p-6 rounded-lg mb-6 flex flex-wrap gap-6">
            <div class="flex flex-col w-full sm:w-1/6">
                <label for="searchQuery" class="mb-2 text-gray-700 font-medium">Search</label>
                <input v-model="searchQuery" type="text" placeholder="Search by reference, type, or date"
                    class="p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 transition duration-200 ease-in-out w-full">
            </div>

            <div class="flex flex-col w-full sm:w-1/6">
                <label for="startDate" class="mb-2 text-gray-700 font-medium">Start Date</label>
                <input v-model="startDate" type="date" id="startDate"
                    class="px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-300 ease-in-out w-full">
            </div>

            <div class="flex flex-col w-full sm:w-1/6">
                <label for="endDate" class="mb-2 text-gray-700 font-medium">End Date</label>
                <input v-model="endDate" type="date" id="endDate"
                    class="px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-300 ease-in-out w-full">
            </div>

            <div class="flex flex-col w-full sm:w-1/6">
                <label for="currency" class="mb-2 text-gray-700 font-medium">Currency</label>
                <select v-model="currency" id="currency"
                        class="px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-300 ease-in-out w-full">
                    <option value="">Select Currency</option>
                    <option v-for="curr in currencies" :key="curr.id" :value="curr.currency_code">{{ curr.currency_code }}</option>
                </select>
            </div>

            <div class="flex flex-col w-full sm:w-1/6">
                <label for="type" class="mb-2 text-gray-700 font-medium">Type</label>
                <select v-model="type" id="type"
                        class="px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-300 ease-in-out w-full">
                    <option value="">Select Type</option>
                    <option v-for="typ in trans_types" :key="typ.type" :value="typ.type">{{ typ.type }}</option>
                </select>
            </div>
            <span v-for="total in totals" :key="total.currency" class="inline-flex items-center px-3 py-2 bg-blue-100 text-blue-800 rounded-lg text-lg font-semibold shadow-md">
                <span class="mr-1">{{ total.currency }}</span> {{ total.total_amount }}
            </span>
        </div>


      <!-- Sales Table with Pagination -->
      <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <!-- Table Header -->
          <thead class="bg-gray-50">
            <tr class="bg-gray-200 text-gray-700">
                <th class="border px-4 py-2 text-left">Reference</th>
                <th class="border px-4 py-2 text-left">Amount</th>
                <th class="border px-4 py-2 text-left">Currency</th>
                <th class="border px-4 py-2 text-left">Type</th>
                <th class="border px-4 py-2 text-left">Date</th>
                <th class="border px-4 py-2 text-left">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="sale in sales" :key="sale.id">
              <td class="py-4 px-6 text-sm font-medium text-gray-900">{{ sale.reference }}</td>
              <td class="py-4 px-6 text-sm text-gray-500">${{ sale.amount }}</td>
              <td class="py-4 px-6 text-sm text-gray-500">{{ sale.currency }}</td>
              <td class="py-4 px-6 text-sm text-gray-500">{{ sale.type }}</td>
              <td class="py-4 px-6 text-sm text-gray-500">{{ sale.created_at }}</td>
              <td class="py-4 px-6 text-sm font-medium">
                <Button @click="showItemsModal(sale)" type="button" class="text-blue-600 hover:text-blue-900">
                  <i class="fas fa-eye"></i>
                  show items
                </Button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Modal for Showing Sale Items -->
      <div v-if="isModalVisible" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-1/2 p-6">
          <!-- Modal Content for Item Details -->
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Sale Details</h2>
            <button @click="closeModal" class="text-gray-500 hover:text-gray-800">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <!-- Sale Information -->
          <div class="bg-gray-100 p-4 rounded-lg shadow-sm mb-6">
            <p class="text-sm text-gray-600">
              <span class="font-semibold text-gray-800">Reference:</span> {{ selectedSale.reference }}
            </p>
            <!-- More sale info here -->
          </div>
          <!-- Items Sold -->
          <ul class="divide-y divide-gray-200 bg-white shadow-md rounded-lg">
            <li v-for="item in selectedSaleItems" :key="item.id" class="flex items-center justify-between p-4 hover:bg-gray-50">
              <div>
                <h3 class="text-lg font-semibold text-gray-800">{{ item.item.name }}</h3>
                <p class="text-sm text-gray-500">Quantity: {{ item.qty }} | Unit Price: ${{ item.unit_price }}</p>
              </div>
              <div class="text-right">
                <p class="text-sm text-gray-500">Total:</p>
                <p class="text-lg font-bold text-blue-600">${{ item.total_price }}</p>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </template>

  <script>
  import { ref, onMounted, watch } from 'vue';
  import { router } from '@inertiajs/vue3';
  import Layout from "../Shared/Layout.vue";

  export default {
    layout: Layout,
    props: {
      sales: Array,
      trans_types: Array,
      currencies: Array,
      totals: Array,
    },
    setup() {
      const startDate = ref('');
      const endDate = ref('');
      const currency = ref('');
      const type = ref('');
      const reference = ref('');

      const isModalVisible = ref(false);
      const selectedSale = ref({});
      const selectedSaleItems = ref([]);

        watch([startDate, endDate, currency, type, reference], () => {
            router.get('/reports', {
                start_date: startDate.value,
                end_date: endDate.value,
                currency: currency.value,
                type: type.value,
                reference: reference.value
            }, {
                preserveState: true,
                preserveScroll: true,
                replace: true
            });
        });

      function showItemsModal(sale) {
        selectedSale.value = sale;
        selectedSaleItems.value = sale.items;
        isModalVisible.value = true;
      }

      function closeModal() {
        isModalVisible.value = false;
      }

      return {
        startDate,
        endDate,
        currency,
        type,
        reference,
        isModalVisible,
        selectedSale,
        selectedSaleItems,
        showItemsModal,
        closeModal,
      };
    }
  };
  </script>

