<template>
    <Head title="Login" />
    <div class="flex items-center justify-center min-h-screen bg-gray-900 px-4">
        <div class="w-full max-w-md">
            <div class="bg-white bg-opacity-90 p-6 sm:p-8 rounded-2xl shadow-lg">
                <div class="text-center mb-6">
                    <img src="../../../assets/img/pos.png" alt="Logo" class="mx-auto mb-4 w-20 sm:w-24 rounded">
                    <h2 class="text-2xl font-bold text-gray-800">MSU Canteen POS</h2>
                    <p class="text-gray-600">Please sign in to continue</p>
                </div>
                <form @submit.prevent="submit">
                    <div class="mb-4">
                        <label class="block text-gray-700 font-semibold mb-2">Email</label>
                        <input
                            type="email"
                            v-model="form.email"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter your email"
                        >
                        <div v-if="form.errors.email" class="text-red-500 text-sm">{{ form.errors.email }}</div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 font-semibold mb-2">Password</label>
                        <input
                            type="password"
                            v-model="form.password"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter your password"
                        >
                        <div v-if="form.errors.password" class="text-red-500 text-sm">{{ form.errors.password }}</div>
                    </div>
                    <button
                        type="submit"
                        :disabled="loading"
                        class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition"
                    >
                        <span v-if="loading">Signing In...</span>
                        <span v-else>Sign In</span>
                    </button>
                    <div v-if="form.errors.error" class="text-red-500 text-sm">{{ form.errors.error }}</div>
                </form>
                <!-- Register link below the login form -->
                <div class="text-center mt-4">
                    <p class="text-gray-600">Want to modify configurations?
                        <Link href="/configs" class="text-blue-600 hover:underline"> Configuration</Link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>


<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
});

const loading = ref(false);

const submit = () => {
    loading.value = true;
    form.post('/login', {
        onFinish: () => (loading.value = false),
    });
};
</script>
