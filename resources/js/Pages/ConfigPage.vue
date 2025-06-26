<template>
  <Head title="Configurations" />
  <div class="max-w-5xl mx-auto p-8 bg-white shadow-lg rounded-xl mt-10 border border-blue-100">
    <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-2">
      <i class="fas fa-cogs text-blue-600"></i> System Configurations
    </h2>

    <div v-if="hasConfig">
      <!-- View Mode -->
      <div v-if="!isEditing" class="grid grid-cols-2 gap-6 text-gray-700 mt-6">
        <p><strong>Server IP:</strong> {{ form.server_ip }}</p>
        <p><strong>Server Version:</strong> {{ form.server_version }}</p>
        <p><strong>Socket IP:</strong> {{ form.socket_ip }}</p>
        <p><strong>Socket Port:</strong> {{ form.socket_port }}</p>
        <p><strong>Printer:</strong> {{ form.printer }}</p>
        <p><strong>Token:</strong> {{ form.token }}</p>
        <div class="col-span-2">
          <button @click="isEditing = true" class="mt-4 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow">
            Edit Configuration
          </button>
        </div>
      </div>

      <!-- Edit Mode -->
      <div v-else class="mt-6">
        <form @submit.prevent="saveConfig" class="grid grid-cols-2 gap-6">
          <div>
            <label class="label">Server IP</label>
            <input v-model="form.server_ip" class="input" type="text" />
          </div>
          <div>
            <label class="label">Server Version</label>
            <input v-model="form.server_version" class="input" type="text" />
          </div>
          <div>
            <label class="label">Socket IP</label>
            <input v-model="form.socket_ip" class="input" type="text" />
          </div>
          <div>
            <label class="label">Socket Port</label>
            <input v-model="form.socket_port" class="input" type="text" />
          </div>
          <div>
            <label class="label">Printer</label>
            <input v-model="form.printer" class="input" type="text" />
          </div>
          <div>
            <label class="label">Token</label>
            <input v-model="form.token" class="input" type="text" />
          </div>
          <div class="col-span-2 flex gap-4 mt-4">
            <button type="submit" class="btn-green">Save</button>
            <button type="button" @click="isEditing = false" class="btn-gray">Cancel</button>
          </div>
        </form>
      </div>
    </div>

    <!-- No Config Yet -->
    <div v-else>
      <p class="text-gray-600 mt-6">No configurations found. Please set them.</p>
      <form @submit.prevent="saveConfig" class="grid grid-cols-2 gap-6 mt-6">
        <div>
          <label class="label">Server IP</label>
          <input v-model="form.server_ip" class="input" type="text" />
        </div>
        <div>
          <label class="label">Server Version</label>
          <input v-model="form.server_version" class="input" type="text" />
        </div>
        <div>
          <label class="label">Socket IP</label>
          <input v-model="form.socket_ip" class="input" type="text" />
        </div>
        <div>
          <label class="label">Socket Port</label>
          <input v-model="form.socket_port" class="input" type="text" />
        </div>
        <div>
          <label class="label">Printer</label>
          <input v-model="form.printer" class="input" type="text" />
        </div>
        <div>
          <label class="label">Token</label>
          <input v-model="form.token" class="input" type="text" />
        </div>
        <div class="col-span-2 mt-4">
          <button type="submit" class="btn-green">Save</button>
        </div>
      </form>
    </div>

    <div class="mt-8">
      <a href="/pos" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow">
        Go to POS
      </a>
    </div>
  </div>
</template>

<script>
import { defineComponent, ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

export default defineComponent({
  props: {
    config: Object
  },
  setup(props) {
    const isEditing = ref(false);

    const form = useForm({
      server_ip: props.config?.server_ip || 'http://127.0.0.1:8080/api',
      server_version: props.config?.server_version || 'v1',
      socket_ip: props.config?.socket_ip || '127.0.0.1',
      socket_port: props.config?.socket_port || '23001',
      printer: props.config?.printer || 'Microsoft Print to PDF',
      token: props.config?.token || ''
    });

    const hasConfig = computed(() => !!props.config);

    const saveConfig = () => {
      form.post('/configs', {
        preserveScroll: true,
        onSuccess: () => (isEditing.value = false)
      });
    };

    return { isEditing, form, hasConfig, saveConfig };
  }
});
</script>

<style scoped>
.label {
  display: block;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
}
.input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  background-color: #f9fafb;
  transition: border 0.2s, box-shadow 0.2s;
}
.input:focus {
  border-color: #2563eb;
  outline: none;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
}
.btn-green {
  background-color: #16a34a;
  color: white;
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: 600;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}
.btn-green:hover {
  background-color: #15803d;
}
.btn-gray {
  background-color: #6b7280;
  color: white;
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: 600;
}
.btn-gray:hover {
  background-color: #4b5563;
}
</style>
