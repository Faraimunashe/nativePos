<template>
    <vue3-snackbar top right :duration="4000" />

    <Head title="My Profile" />

    <div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-4xl mx-auto space-y-10">

        <!-- Profile Info Card -->
        <div class="bg-white shadow-lg rounded-2xl p-6 md:p-8">
          <h2 class="text-2xl font-bold text-gray-800 mb-4">👤 Profile Information</h2>
          <div class="space-y-2 text-gray-700 text-base">
            <p><span class="font-semibold">Name:</span> {{ user.name }}</p>
            <p><span class="font-semibold">Email:</span> {{ user.email }}</p>
          </div>
        </div>

        <!-- Change Password Card -->
        <div class="bg-white shadow-lg rounded-2xl p-6 md:p-8">
          <h2 class="text-2xl font-bold text-gray-800 mb-6">🔐 Change Password</h2>

          <form @submit.prevent="submit" class="space-y-6">
            <div>
              <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
              <input
                v-model="form.current_password"
                type="password"
                id="current_password"
                class="w-full border border-gray-300 rounded-xl px-4 py-2 shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
              />
              <p v-if="errors?.current_password" class="text-red-500 text-sm mt-1">{{ errors?.current_password }}</p>
            </div>

            <div>
              <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
              <input
                v-model="form.new_password"
                type="password"
                id="new_password"
                class="w-full border border-gray-300 rounded-xl px-4 py-2 shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
              />
              <p v-if="errors?.new_password" class="text-red-500 text-sm mt-1">{{ errors?.new_password }}</p>
            </div>

            <div>
              <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
              <input
                v-model="form.new_password_confirmation"
                type="password"
                id="new_password_confirmation"
                class="w-full border border-gray-300 rounded-xl px-4 py-2 shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
              />
              <p v-if="errors?.new_password_confirmation" class="text-red-500 text-sm mt-1">{{ errors?.new_password_confirmation }}</p>
            </div>

            <div class="pt-4">
              <button
                type="submit"
                class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-xl transition duration-200 ease-in-out"
                :disabled="form.processing"
              >
                Change Password
              </button>
            </div>
          </form>
        </div>

      </div>
    </div>
</template>


<script>
import Layout from '../Shared/Layout.vue';
import { useForm } from '@inertiajs/vue3';
import { useSnackbar } from 'vue3-snackbar';

export default {
  layout: Layout,
  props: {
    user: Object,
    errors: Object
  },
  data() {
    return {
      form: useForm({
        current_password: '',
        new_password: '',
        new_password_confirmation: ''
      }),
      snackbar: null
    };
  },
  mounted() {
    this.snackbar = useSnackbar();
  },
  methods: {
    submit() {
      this.form.post('/profile', {
        onError: (errors) => {
          this.notify({
            text: errors?.error || 'An error occurred. Please contact support!',
            type: 'error'
          });
        },
        onSuccess: () => {
          this.notify({
            text: 'Password changed successfully!',
            type: 'success'
          });
          this.form.reset();
        }
      });
    },
    notify({ text, type }) {
      this.snackbar?.add({
        type,
        text,
        group: 'default',
        duration: 5000
      });
    }
  }
};
</script>

