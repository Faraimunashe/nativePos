<template>
    <div class="min-h-screen bg-gray-100 p-6 flex flex-col">
      <!-- Top Navigation -->
        <div class="bg-white shadow-md p-4 flex justify-between items-center mb-6 rounded-lg">
            <!-- Left Section: Branding & Location Info -->
            <div>
                <h2 class="text-lg font-semibold">MSU CANTEEN POS</h2>
                <p class="text-gray-600">Location: Main Branch | Terminal ID: 12345</p>
            </div>

            <!-- Middle Section: Navigation Links -->
            <nav class="flex space-x-6">
                <a href="/pos" class="text-gray-700 hover:text-blue-600 font-medium flex items-center space-x-2">
                <i class="fas fa-tachometer-alt"></i>
                <span>Pos</span>
                </a>
                <a href="/reports" class="text-gray-700 hover:text-blue-600 font-medium flex items-center space-x-2">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
                </a>
            </nav>

            <!-- Right Section: User Profile Dropdown -->
            <div class="relative">
                <button class="bg-gray-200 px-4 py-2 rounded-lg flex items-center space-x-2" @click="toggleDropdown">
                <i class="fas fa-user"></i>
                <span>Faraimunashe Manjeese ▼</span>
                </button>
                <div v-if="dropdownOpen" class="absolute right-0 mt-2 bg-white shadow-lg rounded-lg w-48">
                <a href="#" class="block px-4 py-2 hover:bg-gray-100 flex items-center space-x-2">
                    <i class="fas fa-user-circle"></i>
                    <span>Profile</span>
                </a>
                <a href="#" class="block px-4 py-2 hover:bg-gray-100 flex items-center space-x-2">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
                <a href="#" class="block px-4 py-2 hover:bg-gray-100 text-red-600 flex items-center space-x-2">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
                </div>
            </div>
        </div>

      <!-- Search Bar -->
      <div class="bg-white shadow-md p-4 rounded-lg mb-4 flex items-center space-x-4">
        <i class="fas fa-search text-gray-500"></i>
        <input v-model="searchQuery" type="text" placeholder="Search by reference, type, or date"
               class="w-full p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
      </div>

      <!-- Sales Reports Table -->
      <div class="bg-white shadow-lg rounded-lg p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Sales Reports</h2>
        <div class="overflow-x-auto">
          <table class="w-full border-collapse border border-gray-300">
            <thead>
              <tr class="bg-gray-200 text-gray-700">
                <th class="border px-4 py-2 text-left">Reference</th>
                <th class="border px-4 py-2 text-left">Amount</th>
                <th class="border px-4 py-2 text-left">Currency</th>
                <th class="border px-4 py-2 text-left">Type</th>
                <th class="border px-4 py-2 text-left">Date</th>
                <th class="border px-4 py-2 text-left">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(sale, index) in filteredSales" :key="sale.reference" class="hover:bg-gray-100 cursor-pointer">
                <td class="border px-4 py-2">{{ sale.reference }}</td>
                <td class="border px-4 py-2">${{ sale.amount.toFixed(2) }}</td>
                <td class="border px-4 py-2">{{ sale.currency }}</td>
                <td class="border px-4 py-2">{{ sale.type }}</td>
                <td class="border px-4 py-2">{{ sale.created_at }}</td>
                <td class="border px-4 py-2 text-center">
                  <button @click="toggleSaleItems(index)" class="text-blue-500 hover:underline">
                    <i class="fas fa-eye"></i> View Items
                  </button>
                </td>
              </tr>
              <tr v-if="selectedSale !== null">
                <td colspan="6" class="bg-gray-50 p-4">
                  <h3 class="text-lg font-semibold text-gray-700 mb-2">Sale Items</h3>
                  <table class="w-full border-collapse border border-gray-300">
                    <thead>
                      <tr class="bg-gray-200 text-gray-700">
                        <th class="border px-4 py-2 text-left">Item Name</th>
                        <th class="border px-4 py-2 text-left">Qty</th>
                        <th class="border px-4 py-2 text-left">Unit Price</th>
                        <th class="border px-4 py-2 text-left">Total Price</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="item in sales[selectedSale].items" :key="item.name">
                        <td class="border px-4 py-2">{{ item.name }}</td>
                        <td class="border px-4 py-2">{{ item.qty }}</td>
                        <td class="border px-4 py-2">${{ item.unit_price.toFixed(2) }}</td>
                        <td class="border px-4 py-2">${{ item.total_price.toFixed(2) }}</td>
                      </tr>
                    </tbody>
                  </table>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </template>

  <script setup>
  import { ref, computed } from "vue";

  const searchQuery = ref("");
  const selectedSale = ref(null);
  const sales = ref([
    {
      reference: "INV-001",
      amount: 50.99,
      currency: "USD",
      type: "Cash",
      created_at: "2024-01-01",
      items: [
        { name: "Burger", qty: 2, unit_price: 5.99, total_price: 11.98 },
        { name: "Coke", qty: 1, unit_price: 2.99, total_price: 2.99 },
      ],
    },
    {
      reference: "INV-002",
      amount: 30.50,
      currency: "USD",
      type: "Card",
      created_at: "2024-01-02",
      items: [
        { name: "Pizza", qty: 1, unit_price: 15.50, total_price: 15.50 },
        { name: "Juice", qty: 2, unit_price: 7.50, total_price: 15.00 },
      ],
    },
  ]);

  const filteredSales = computed(() => {
    return sales.value.filter(sale =>
      sale.reference.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      sale.type.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      sale.created_at.includes(searchQuery.value)
    );
  });

  const toggleSaleItems = (index) => {
    selectedSale.value = selectedSale.value === index ? null : index;
  };
  </script>
