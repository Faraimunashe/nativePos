<template>
    <div class="bg-white shadow-md p-4 flex justify-between items-center mb-6 rounded-lg">
        <div>
            <h2 class="text-lg font-semibold">MSU CANTEEN POS</h2>
            <p class="text-gray-600">Location: Main Branch | Terminal ID: 12345</p>
        </div>

        <nav class="flex space-x-6">
            <Link href="/pos" class="text-gray-700 hover:text-blue-600 font-medium flex items-center space-x-2">
                <i class="fas fa-tachometer-alt"></i>
                <span>Pos</span>
            </Link>
            <Link href="/reports" class="text-gray-700 hover:text-blue-600 font-medium flex items-center space-x-2">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </Link>
        </nav>

        <div class="relative">
            <button class="bg-gray-200 px-4 py-2 rounded-lg flex items-center space-x-2" @click="toggleDropdown">
                <i class="fas fa-user"></i>
                <span>{{username}} ▼</span>
            </button>
            <div v-if="dropdownOpen" class="absolute right-0 mt-2 bg-white shadow-lg rounded-lg w-48">
                <Link href="/profile" class="block px-4 py-2 hover:bg-gray-100 flex items-center space-x-2">
                    <i class="fas fa-user-circle"></i>
                    <span>Profile</span>
                </Link>
                <Link href="/settings" class="block px-4 py-2 hover:bg-gray-100 flex items-center space-x-2">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </Link>
                <Link method="POST" href="/logout" class="block px-4 py-2 hover:bg-gray-100 text-red-600 flex items-center space-x-2">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </Link>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed } from 'vue';

export default {
    props: {
        username: String
    },
    data() {
        return {
            dropdownOpen: false
        };
    },
    methods: {
        toggleDropdown() {
            this.dropdownOpen = !this.dropdownOpen;
        }
    }
};
</script>

