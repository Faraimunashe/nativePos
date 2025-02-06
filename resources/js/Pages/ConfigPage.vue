<template>
    <Head title="Configurations" />
    <div class="max-w-4xl mx-auto p-8 bg-white shadow-lg rounded-lg mt-10 border border-gray-200">
        <h2 class="text-2xl font-bold mb-6 text-gray-700">System Configurations</h2>
        <div v-if="hasConfig" class="space-y-6">
            <div v-if="!isEditing">
                <div class="grid grid-cols-2 gap-6">
                    <p class="text-gray-600"><strong class="text-gray-800">Server IP:</strong> {{ form.server_ip }}</p>
                    <p class="text-gray-600"><strong class="text-gray-800">Server Version:</strong> {{ form.server_version }}</p>
                    <p class="text-gray-600"><strong class="text-gray-800">Default Currency:</strong> {{ form.default_currency }}</p>
                    <p class="text-gray-600"><strong class="text-gray-800">Enabled Payments:</strong> {{ form.enabled_payments }}</p>
                    <p class="text-gray-600"><strong class="text-gray-800">Socket IP:</strong> {{ form.socket_ip }}</p>
                    <p class="text-gray-600"><strong class="text-gray-800">Socket Port:</strong> {{ form.socket_port }}</p>
                    <p class="text-gray-600"><strong class="text-gray-800">Location:</strong> {{ form.location }}</p>
                    <p class="text-gray-600"><strong class="text-gray-800">Terminal:</strong> {{ form.terminal }}</p>
                    <p class="text-gray-600"><strong class="text-gray-800">Printer:</strong> {{ form.selected_printer }}</p>
                </div>
                <button @click="isEditing = true" class="mt-6 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow">Edit</button>
            </div>
            <div v-else>
                <form @submit.prevent="saveConfig" class="space-y-4">
                    <div class="grid grid-cols-2 gap-6">
                        <input v-model="form.server_ip" class="input-field" placeholder="Server IP" />
                        <input v-model="form.server_version" class="input-field" placeholder="Server Version" />
                        <input v-model="form.default_currency" class="input-field" placeholder="Default Currency" />
                        <input v-model="form.enabled_payments" class="input-field" placeholder="Enabled Payments" />
                        <input v-model="form.socket_ip" class="input-field" placeholder="Socket IP" />
                        <input v-model="form.socket_port" class="input-field" placeholder="Socket Port" />
                        <input v-model="form.location" class="input-field" placeholder="Location" />
                        <input v-model="form.terminal" class="input-field" placeholder="Terminal" />
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <label for="printer" class="text-gray-800">Select Printer:</label>
                        <select v-model="form.selected_printer" id="printer" class="input-field">
                            <option v-for="printer in printers" :key="printer" :value="printer">{{ printer }}</option>
                        </select>
                    </div>
                    <div class="flex gap-4">
                        <button type="submit" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg shadow">Save</button>
                        <button @click="isEditing = false" type="button" class="px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white rounded-lg shadow">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        <div v-else>
            <p class="text-gray-600">No configurations found. Please set them.</p>
            <form @submit.prevent="saveConfig" class="space-y-4 mt-4">
                <div class="grid grid-cols-2 gap-6">
                    <input v-model="form.server_ip" class="input-field" placeholder="Server IP" />
                    <input v-model="form.server_version" class="input-field" placeholder="Server Version" />
                    <input v-model="form.default_currency" class="input-field" placeholder="Default Currency" />
                    <input v-model="form.enabled_payments" class="input-field" placeholder="Enabled Payments" />
                    <input v-model="form.socket_ip" class="input-field" placeholder="Socket IP" />
                    <input v-model="form.socket_port" class="input-field" placeholder="Socket Port" />
                    <input v-model="form.location" class="input-field" placeholder="Location" />
                    <input v-model="form.terminal" class="input-field" placeholder="Terminal" />
                </div>
                <div class="grid grid-cols-2 gap-6">
                    <label for="printer" class="text-gray-800">Select Printer:</label>
                    <select v-model="form.selected_printer" id="printer" class="input-field">
                        <option v-for="printer in printers" :key="printer" :value="printer">{{ printer }}</option>
                    </select>
                </div>
                <button type="submit" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg shadow">Save</button>
            </form>
        </div>
        <div class="mt-6">
            <a href="/pos" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow inline-block">Go to POS</a>
        </div>
    </div>
</template>

<script>
import { defineComponent, ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

export default defineComponent({
    props: {
        config: Object,
        printers: Array
    },
    setup(props) {
        const isEditing = ref(false);
        const form = useForm({
            server_ip: props.config?.server_ip || 'http://127.0.0.1:8080/api',
            server_version: props.config?.server_version || 'v1',
            default_currency: props.config?.default_currency || 'USD',
            enabled_payments: props.config?.enabled_payments || 'CASH',
            socket_ip: props.config?.socket_ip || '127.0.0.1',
            socket_port: props.config?.socket_port || '23001',
            location: props.config?.location || 'Development Desk',
            terminal: props.config?.terminal || 'ESADZA01',
            selected_printer: props.config?.printer || '', // New property for selected printer
        });

        const hasConfig = computed(() => !!props.config);

        const saveConfig = () => {
            form.post('/configs', {
                preserveScroll: true,
                onSuccess: () => isEditing.value = false
            });
        };

        return { isEditing, form, hasConfig, saveConfig };
    }
});
</script>

<style>
.input-field {
    border: 1px solid #ccc;
    padding: 12px;
    border-radius: 8px;
    width: 100%;
    background-color: #f9fafb;
    transition: all 0.2s;
}
.input-field:focus {
    border-color: #2563eb;
    outline: none;
    box-shadow: 0 0 4px rgba(37, 99, 235, 0.5);
}
</style>
