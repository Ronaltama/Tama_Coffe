<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

// === STATE ===
const tableNumber = ref('')
const capacity = ref('')
const status = ref('available')
const loading = ref(false)
const error = ref(null)

// === API BASE URL ===
const API_BASE = 'http://localhost:8000/api'

// === FORM SUBMIT ===
const handleSubmit = async () => {
  // Validasi
  if (!tableNumber.value.trim()) {
    alert('Nomor/Nama meja harus diisi!')
    return
  }
  if (!capacity.value || capacity.value <= 0) {
    alert('Kapasitas harus lebih dari 0!')
    return
  }

  loading.value = true
  error.value = null

  try {
    const payload = {
      table_number: tableNumber.value,
      capacity: capacity.value,
      status: status.value
    }

    const response = await axios.post(`${API_BASE}/tables`, payload)

    if (response.data.success) {
      alert('Meja berhasil ditambahkan!')
      router.push('/superadmin/tables')
    }
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Gagal menambahkan meja'
    alert(error.value)
  } finally {
    loading.value = false
  }
}

const handleCancel = () => {
  router.push('/superadmin/tables')
}
</script>

<template>
  <div class="p-6 bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Add New Table</h1>
      <p class="text-gray-600 mt-1">Fill in the form below to add a new table</p>
    </div>

    <!-- Form -->
    <form @submit.prevent="handleSubmit" class="bg-white rounded-xl shadow-sm max-w-3xl">
      <div class="p-6 space-y-6">
        
        <!-- Table Number/Name -->
        <div>
          <label for="tableNumber" class="block text-sm font-medium text-gray-700 mb-2">
            Table Number/Name <span class="text-red-500">*</span>
          </label>
          <input
            v-model="tableNumber"
            type="text"
            id="tableNumber"
            placeholder="e.g., Table 1, A-01"
            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none transition"
            required
          >
        </div>

        <!-- Capacity & Status Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Capacity -->
          <div>
            <label for="capacity" class="block text-sm font-medium text-gray-700 mb-2">
              Capacity (Persons) <span class="text-red-500">*</span>
            </label>
            <input
              v-model.number="capacity"
              type="number"
              id="capacity"
              placeholder="e.g., 4"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none transition"
              required
              min="1"
              max="20"
              step="1"
            >
          </div>

          <!-- Status -->
          <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
              Current Status <span class="text-red-500">*</span>
            </label>
            <select
              v-model="status"
              id="status"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none transition appearance-none bg-white"
            >
              <option value="available">Available</option>
              <option value="occupied">Occupied</option>
              <option value="reserved">Reserved</option>
              <option value="maintenance">Maintenance</option>
            </select>
          </div>
        </div>

      </div>

      <!-- Form Actions -->
      <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-xl flex justify-end gap-3">
        <button
          type="button"
          @click="handleCancel"
          class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition font-medium"
          :disabled="loading"
        >
          Cancel
        </button>
        <button
          type="submit"
          class="px-5 py-2.5 bg-amber-700 text-white rounded-lg hover:bg-amber-800 transition font-medium disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
          :disabled="loading"
        >
          <svg v-if="loading" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ loading ? 'Saving...' : 'Save Table' }}
        </button>
      </div>
    </form>
  </div>
</template>

<style scoped>
/* Custom select arrow */
select {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 0.5rem center;
  background-repeat: no-repeat;
  background-size: 1.5em 1.5em;
  padding-right: 2.5rem;
}
</style>