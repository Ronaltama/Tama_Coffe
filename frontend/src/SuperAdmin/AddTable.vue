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
const type = ref('')
const loading = ref(false)

const API_BASE = 'http://localhost:8000/api'

// === SUBMIT FORM ===
const handleSubmit = async () => {
  // VALIDASI
  if (!tableNumber.value.trim()) {
    Swal.fire({
      icon: 'warning',
      title: 'Oops!',
      text: 'Nomor atau nama meja harus diisi!',
    })
    return
  }

  if (!capacity.value || capacity.value <= 0) {
    Swal.fire({
      icon: 'warning',
      title: 'Invalid Capacity',
      text: 'Kapasitas harus lebih dari 0!',
    })
    return
  }

  if (!type.value) {
    Swal.fire({
      icon: 'warning',
      title: 'Type Required',
      text: 'Tipe meja harus dipilih!',
    })
    return
  }

  loading.value = true

  Swal.fire({
    title: 'Saving...',
    text: 'Please wait',
    allowOutsideClick: false,
    didOpen: () => Swal.showLoading()
  })

  try {
    const payload = {
      table_number: tableNumber.value,
      capacity: capacity.value,
      status: status.value,
      type: type.value
    }

    const response = await axios.post(`${API_BASE}/tables`, payload)

    Swal.close()

    if (response.data.success) {
      Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: 'Meja berhasil ditambahkan!',
        timer: 1500,
        showConfirmButton: false
      })

      setTimeout(() => {
        router.push('/superadmin/tables')
      }, 1500)
    }

  } catch (err) {
    Swal.close()
    Swal.fire({
      icon: 'error',
      title: 'Gagal Menambahkan',
      text: err.response?.data?.message || 'Terjadi kesalahan pada server'
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
  <div class="p-6 bg-gray-50 min-h-screen">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Add New Table</h1>
      <p class="text-gray-600 mt-1">Fill in the form below to add a new table</p>
    </div>

    <form @submit.prevent="handleSubmit" class="bg-white rounded-xl shadow-sm max-w-3xl">
      <div class="p-6 space-y-6">

        <!-- Table Number -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Table Number/Name *</label>
          <input v-model="tableNumber" type="text" placeholder="e.g., Table 1, A-01"
            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500" />
        </div>

        <!-- Capacity + Status + Type -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

          <!-- Capacity -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Capacity *</label>
            <input v-model.number="capacity" type="number" min="1" step="1"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500" />
          </div>

          <!-- Type -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Type *</label>
            <select v-model="type"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 bg-white">
              <option disabled value="">-- Select Type --</option>
              <option value="Indoor">Indoor</option>
              <option value="Outdoor">Outdoor</option>
              <option value="VIP">VIP</option>
            </select>
          </div>


          <!-- Status -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
            <select v-model="status"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 bg-white">
              <option value="available">Available</option>
              <option value="occupied">Occupied</option>
              <option value="reserved">Reserved</option>
              <option value="maintenance">Maintenance</option>
            </select>
          </div>

        </div>

      </div>

      <!-- Action Buttons -->
      <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-xl flex justify-end gap-3">
        <button type="button" @click="handleCancel"
          class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100">
          Cancel
        </button>

        <button type="submit"
          class="px-5 py-2.5 bg-amber-700 text-white rounded-lg hover:bg-amber-800 disabled:opacity-50"
          :disabled="loading">
          Save Table
        </button>
      </div>
    </form>
  </div>
</template>

<style scoped>
select {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 0.5rem center;
  background-repeat: no-repeat;
  background-size: 1.5em 1.5em;
  padding-right: 2.5rem;
}
</style>
