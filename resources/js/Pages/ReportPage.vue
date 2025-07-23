<template>
  <Head title="My Reports" />

  <div class="container mx-auto px-4 py-6">
    <!-- Filters & Totals -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <!-- <code class="mb-2 text-red-700 font-small">Showing your sales for today by default</code> -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6">
        <!-- Search -->
        <div class="flex flex-col">
          <label for="reference" class="mb-2 text-gray-700 font-medium">Search</label>
          <input
            v-model="reference"
            id="reference"
            type="text"
            placeholder="Reference, type, or date"
            class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 transition"
          />
        </div>
        <!-- Start Date -->
        <div class="flex flex-col">
          <label for="startDate" class="mb-2 text-gray-700 font-medium">Start Date</label>
          <input
            v-model="startDate"
            id="startDate"
            type="date"
            class="w-full px-4 py-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
          />
        </div>
        <!-- End Date -->
        <div class="flex flex-col">
          <label for="endDate" class="mb-2 text-gray-700 font-medium">End Date</label>
          <input
            v-model="endDate"
            id="endDate"
            type="date"
            class="w-full px-4 py-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
          />
        </div>
        <!-- Currency -->
        <div class="flex flex-col">
          <label for="currency" class="mb-2 text-gray-700 font-medium">Currency</label>
          <select
            v-model="currency"
            id="currency"
            class="w-full px-4 py-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
          >
            <option value="">All</option>
            <option v-for="curr in currencies" :key="curr.id" :value="curr.code">
              {{ curr.code }}
            </option>
          </select>
        </div>
        <!-- Type -->
        <div class="flex flex-col">
          <label for="type" class="mb-2 text-gray-700 font-medium">Type</label>
          <select
            v-model="type"
            id="type"
            class="w-full px-4 py-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
          >
            <option value="">All</option>
            <option v-for="typ in trans_types" :key="typ.type" :value="typ.type">
              {{ typ.type }}
            </option>
          </select>
        </div>
        <!-- Location -->
        <div class="flex flex-col">
          <label for="location_id" class="mb-2 text-gray-700 font-medium">Location</label>
          <select
            v-model="location_id"
            id="location_id"
            class="w-full px-4 py-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
          >
            <option value="">All</option>
            <option v-for="loc in locations" :key="loc.id" :value="loc.id">
              {{ loc.name }}
            </option>
          </select>
        </div>
      </div>

      <!-- Totals Badges -->
      <div class="mt-4 flex flex-wrap gap-3">
        <span
          v-for="total in totals"
          :key="total.currency"
          class="inline-flex items-center px-3 py-2 bg-blue-100 text-blue-800 rounded-lg text-sm sm:text-base font-semibold"
        >
          <span class="mr-1">{{ total.currency.code }}</span>
          <b>{{ total.total }}</b>
        </span>
      </div>
    </div>

    <!-- Sales Table -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 table-auto">
          <thead class="bg-gray-50">
            <tr class="text-gray-700">
              <th class="px-4 py-2 text-left text-sm font-medium">Reference</th>
              <th class="px-4 py-2 text-left text-sm font-medium">Amount</th>
              <th class="px-4 py-2 text-left text-sm font-medium">Currency</th>
              <th class="px-4 py-2 text-left text-sm font-medium">Type</th>
              <th class="px-4 py-2 text-left text-sm font-medium">Location</th>
              <th class="px-4 py-2 text-left text-sm font-medium">Date</th>
              <th class="px-4 py-2 text-left text-sm font-medium">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="sale in sales.data" :key="sale.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 text-sm">{{ sale.reference }}</td>
              <td class="px-4 py-3 text-sm">
                {{ sale.amount }}
              </td>
              <td class="px-4 py-3 text-sm">{{ sale.currency.code }}</td>
              <td class="px-4 py-3 text-sm">{{ sale.type }}</td>
              <td class="px-4 py-3 text-sm">{{ sale.location.name }}</td>
              <td class="px-4 py-3 text-sm">
                {{ formatDate(sale.created_at) }}
              </td>
              <td class="px-4 py-3 text-sm">
                <button
                  @click="showItemsModal(sale)"
                  class="text-blue-600 hover:text-blue-900 focus:outline-none"
                >
                  <i class="fas fa-eye mr-1"></i> Show
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination (uncomment if needed) -->
    <!-- <Pagination :links="sales.links" /> -->

    <!-- Modal -->
    <div
      v-if="isModalVisible"
      class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center px-4 z-50"
    >
      <div class="bg-white rounded-lg shadow-lg w-full max-w-3xl mx-auto p-6 overflow-y-auto max-h-[90vh]">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-bold">Sale Details</h2>
          <button @click="closeModal" class="text-gray-500 hover:text-gray-800 focus:outline-none">
            <i class="fas fa-times text-lg"></i>
          </button>
        </div>
        <!-- Sale Info -->
        <div class="bg-gray-100 p-4 rounded-lg shadow-sm mb-6">
          <p><span class="font-semibold">Reference:</span> {{ selectedSale.reference }}</p>
          <p><span class="font-semibold">Location:</span> {{ selectedSale.location.name }}</p>
          <!-- add more fields as needed -->
        </div>
        <!-- Items Sold -->
        <ul class="divide-y divide-gray-200">
          <li
            v-for="item in selectedSale.sale_items"
            :key="item.id"
            class="flex flex-col sm:flex-row justify-between p-4 hover:bg-gray-50"
          >
            <div>
              <h3 class="text-lg font-semibold">{{ item.item.name }}</h3>
              <p class="text-sm text-gray-600">
                Qty: {{ item.qty }} &middot; Unit Price: {{ item.unit_price, selectedSale.currency.code }}
              </p>
            </div>
            <div class="mt-2 sm:mt-0 text-right">
              <p class="text-sm text-gray-600">Total</p>
              <p class="text-lg font-bold">{{ item.total_price, selectedSale.currency.code }}</p>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout.vue'
import Pagination from '@/Shared/Pagination.vue'

export default {
  layout: Layout,
  props: {
    sales: Object,
    trans_types: Array,
    currencies: Array,
    totals: Array,
    locations: Array,
  },
  components: {
    Pagination,
  },
  setup() {
    const startDate = ref('')
    const endDate = ref('')
    const currency = ref('')
    const type = ref('')
    const reference = ref('')
    const location_id = ref('')

    const isModalVisible = ref(false)
    const selectedSale = ref({})

    watch(
      [startDate, endDate, currency, type, reference, location_id],
      () => {
        router.get(
          '/reports',
          {
            start_date: startDate.value,
            end_date: endDate.value,
            currency: currency.value,
            type: type.value,
            reference: reference.value,
            location_id: location_id.value,
          },
          { preserveState: true, preserveScroll: true, replace: true, debounce: 300 }
        )
      }
    )

    function showItemsModal(sale) {
      selectedSale.value = sale
      isModalVisible.value = true
    }
    function closeModal() {
      isModalVisible.value = false
    }

    const formatCurrency = (value, code) =>
      new Intl.NumberFormat(undefined, {
        style: 'currency',
        currency: code,
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }).format(value)

    const formatDate = (isoString) => {
      const date = new Date(isoString)
      return date.toLocaleString(undefined, {
        year: 'numeric',
        month: 'short',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
      })
    }

    return {
      startDate,
      endDate,
      currency,
      type,
      reference,
      location_id,
      isModalVisible,
      selectedSale,
      showItemsModal,
      closeModal,
      formatCurrency,
      formatDate,
    }
  },
}
</script>
