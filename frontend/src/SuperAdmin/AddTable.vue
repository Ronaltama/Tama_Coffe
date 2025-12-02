<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import Swal from 'sweetalert2'

const router = useRouter()

// STATE
const tableNumber = ref('')
const capacity = ref('')
const status = ref('available')
const loading = ref(false)

const API_BASE = 'http://localhost:8000/api'

// === SUBMIT FORM ===
const handleSubmit = async () => {
  // VALIDASI
  if (!tableNumber.value.trim()) {
    Swal.fire({
      icon: 'warning',
      title: 'Oops!',
      text: 'Table number/name is required!',
      confirmButtonColor: '#854D0E',
    })
    return
  }

  if (!capacity.value || capacity.value <= 0) {
    Swal.fire({
      icon: 'warning',
      title: 'Invalid Capacity',
      text: 'Capacity must be greater than 0!',
      confirmButtonColor: '#854D0E',
    })
    return
  }

  loading.value = true

  try {
    const token = localStorage.getItem('token')
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
    
    const payload = {
      table_number: tableNumber.value,
      capacity: capacity.value,
      status: status.value
    }

    const response = await axios.post(`${API_BASE}/tables`, payload)

    if (response.data.success) {
      await Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: 'Table has been added successfully!',
        confirmButtonColor: '#854D0E',
        timer: 1500,
        showConfirmButton: false
      })

      router.push('/superadmin/tables')
    }

  } catch (err) {
    Swal.fire({
      icon: 'error',
      title: 'Failed',
      text: err.response?.data?.message || 'An error occurred on the server',
      confirmButtonColor: '#854D0E',
    })
  } finally {
    loading.value = false
  }
}

const handleCancel = () => {
  router.push('/superadmin/tables')
}
</script>

<template>
  <div class="flex-1 overflow-auto font-inter">
    <div class="p-8 max-w-4xl mx-auto">
      <!-- Page Title with Back Button -->
      <div class="mb-6 flex items-center gap-4">
        <button 
          @click="handleCancel"
          class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
          title="Back to Tables"
        >
          <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        <div>
          <h1 class="text-color-azure-11 text-4xl font-bold font-inter leading-tight">Add Table</h1>
          <p class="text-color-grey-46 text-sm mt-1">Create a new table for your restaurant</p>
        </div>
      </div>

      <!-- Form Card -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <form @submit.prevent="handleSubmit" class="p-8 space-y-6">
          
          <!-- Table Number/Name -->
          <div>
            <label class="text-color-azure-11 text-sm font-medium font-inter mb-2 block">
              Table Number/Name <span class="text-red-500">*</span>
            </label>
            <input
              v-model="tableNumber"
              type="text"
              placeholder="e.g., Table 1 or Patio-5"
              class="w-full px-4 py-2.5 bg-white rounded-xl border border-gray-200 text-sm font-normal font-inter text-gray-700 placeholder-gray-400 outline-none focus:border-color-brown focus:ring-2 focus:ring-orange-100 transition-all duration-200"
            />
          </div>

          <!-- Capacity & Current Status -->
          <div class="grid grid-cols-2 gap-4">
            <!-- Capacity -->
            <div>
              <label class="text-color-azure-11 text-sm font-medium font-inter mb-2 block">
                Capacity <span class="text-red-500">*</span>
              </label>
              <input
                v-model.number="capacity"
                type="number"
                min="1"
                step="1"
                placeholder="e.g., 4"
                class="w-full px-4 py-2.5 bg-white rounded-xl border border-gray-200 text-sm font-normal font-inter text-gray-700 placeholder-gray-400 outline-none focus:border-color-brown focus:ring-2 focus:ring-orange-100 transition-all duration-200"
              />
            </div>

            <!-- Current Status -->
            <div>
              <label class="text-color-azure-11 text-sm font-medium font-inter mb-2 block">
                Current Status <span class="text-red-500">*</span>
              </label>
              <select
                v-model="status"
                class="w-full px-4 py-2.5 bg-white rounded-xl border border-gray-200 text-sm font-normal font-inter text-gray-700 outline-none focus:border-color-brown focus:ring-2 focus:ring-orange-100 transition-all duration-200 appearance-none cursor-pointer"
                style="background-image: url('data:image/svg+xml,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 fill=%27none%27 viewBox=%270 0 20 20%27%3e%3cpath stroke=%27%236b7280%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27 stroke-width=%271.5%27 d=%27M6 8l4 4 4-4%27/%3e%3c/svg%3e'); background-position: right 0.75rem center; background-repeat: no-repeat; background-size: 1.25em 1.25em; padding-right: 2.5rem;"
              >
                <option value="available">Available</option>
                <option value="occupied">Occupied</option>
                <option value="reserved">Reserved</option>
                <option value="maintenance">Maintenance</option>
              </select>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-end gap-3 pt-4">
            <button
              type="button"
              @click="handleCancel"
              class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 text-sm font-medium hover:bg-gray-50 transition-colors"
            >
              Cancel
            </button>

            <button
              type="submit"
              :disabled="loading"
              style="background-color: #854D0E"
              class="px-6 py-2.5 text-white rounded-lg text-sm font-medium hover:opacity-90 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
            >
              <svg v-if="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ loading ? "Saving..." : "Save Table" }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

.font-inter {
  font-family: 'Inter', sans-serif;
}
</style>
