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



      <div class="flex space-x-6">
        <!-- Items List -->
        <div class="w-3/4 bg-white shadow-lg rounded-lg p-6" style="height: calc(90vh - 80px); overflow-y: auto;">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Point of Sale</h2>
            <input type="text" v-model="searchQuery" placeholder="Search items..."
                class="w-full p-3 border rounded-lg mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <div class="grid grid-cols-3 gap-4">
                <div v-for="item in filteredItems" :key="item.id" class="bg-white p-4 rounded-lg shadow-md cursor-pointer hover:bg-blue-100"
                @click="addToCart(item)">
                <h3 class="text-lg font-semibold">{{ item.name }}</h3>
                <p class="text-gray-600">${{ item.price.toFixed(2) }}</p>
                </div>
            </div>
        </div>

        <!-- Cart Section -->
        <div class="w-1/4 bg-gray-200 p-4 rounded-lg shadow-lg flex flex-col" style="height: 80vh;">
            <h3 class="text-xl font-semibold mb-2">Cart</h3>
            <div v-if="cart.length === 0" class="text-gray-600">No items in cart</div>
            <div class="cart-items flex-grow" style="max-height: calc(100% - 120px); overflow-y: auto;">
                <div v-for="(cartItem, index) in cart" :key="index" class="flex justify-between items-center bg-white p-3 rounded-lg mb-2">
                    <span>{{ cartItem.name }} ({{ cartItem.quantity }})</span>
                    <span>${{ (cartItem.quantity * cartItem.price).toFixed(2) }}</span>
                    <button @click="removeItem(index)" class="text-red-500 hover:text-red-700">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
            <div class="text-lg font-bold text-gray-800 mt-4">Total: ${{ totalPrice.toFixed(2) }}</div>
            <div class="grid grid-cols-1 gap-2 mt-4 flex-shrink-0">
                <button class="bg-green-500 text-white py-2 rounded-lg hover:bg-green-600" @click="payWithCash">Cash Payment</button>
                <button class="bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600" @click="payWithCard">Card Payment</button>
                <button class="bg-red-500 text-white py-2 rounded-lg hover:bg-red-600" @click="resetCart">Reset Cart</button>
            </div>
        </div>

      </div>
    </div>
  </template>

  <script setup>
  import { ref, computed } from 'vue';

  const searchQuery = ref('');
  const cart = ref([]);
  const dropdownOpen = ref(false);

  const items = ref([
  { "id": 1, "name": "Burger", "price": 5.99 },
  { "id": 2, "name": "Pizza", "price": 8.99 },
  { "id": 3, "name": "Fries", "price": 3.49 },
  { "id": 4, "name": "Coke", "price": 1.99 },
  { "id": 5, "name": "Coffee", "price": 2.99 },
  { "id": 6, "name": "Cheeseburger", "price": 6.49 },
  { "id": 7, "name": "Hot Dog", "price": 4.99 },
  { "id": 8, "name": "Ice Cream", "price": 2.49 },
  { "id": 9, "name": "Salad", "price": 4.49 },
  { "id": 10, "name": "Pasta", "price": 7.99 },
  { "id": 11, "name": "Chicken Wings", "price": 6.99 },
  { "id": 12, "name": "Tacos", "price": 5.49 },
  { "id": 13, "name": "Sandwich", "price": 3.99 },
  { "id": 14, "name": "Onion Rings", "price": 2.99 },
  { "id": 15, "name": "Smoothie", "price": 4.99 },
  { "id": 16, "name": "Lemonade", "price": 2.79 },
  { "id": 17, "name": "Sushi", "price": 9.99 },
  { "id": 18, "name": "Pancakes", "price": 5.79 },
  { "id": 19, "name": "Bagel", "price": 1.79 },
  { "id": 20, "name": "Muffin", "price": 2.29 },
  { "id": 21, "name": "Pita Bread", "price": 1.49 },
  { "id": 22, "name": "Milkshake", "price": 3.99 },
  { "id": 23, "name": "Frappuccino", "price": 4.79 },
  { "id": 24, "name": "Wrap", "price": 6.49 },
  { "id": 25, "name": "Spaghetti", "price": 6.99 },
  { "id": 26, "name": "Grilled Cheese", "price": 4.49 },
  { "id": 27, "name": "Croissant", "price": 2.59 },
  { "id": 28, "name": "Nachos", "price": 5.99 },
  { "id": 29, "name": "Chicken Sandwich", "price": 6.29 },
  { "id": 30, "name": "French Toast", "price": 4.99 }
]
);

  const filteredItems = computed(() => {
    return items.value.filter(item => item.name.toLowerCase().includes(searchQuery.value.toLowerCase()));
  });

  const addToCart = (item) => {
    const existingItem = cart.value.find(cartItem => cartItem.id === item.id);
    if (existingItem) {
      existingItem.quantity++;
    } else {
      cart.value.push({ ...item, quantity: 1 });
    }
  };

  const totalPrice = computed(() => {
    return cart.value.reduce((sum, item) => sum + item.price * item.quantity, 0);
  });

  const toggleDropdown = () => {
    dropdownOpen.value = !dropdownOpen.value;
  };

  const payWithCash = () => alert('Processing Cash Payment...');
  const payWithCard = () => alert('Processing Card Payment...');
  const resetCart = () => cart.value = [];
  </script>
