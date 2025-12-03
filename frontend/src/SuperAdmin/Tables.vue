<script setup>
import { ref, computed, onMounted } from "vue"
import { useRouter } from "vue-router"
import axios from "axios"
import Swal from "sweetalert2"

const router = useRouter()
const tables = ref([])
const loading = ref(true)
const searchQuery = ref("")

// Fetch tables data
const fetchTables = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token')
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
    
    const response = await axios.get("http://localhost:8000/api/tables")
    if (response.data.success) {
      tables.value = response.data.data
    }
  } catch (error) {
    console.error("Error fetching tables:", error)
    Swal.fire({
      icon: "error",
      title: "Error!",
      text: "Failed to load tables data.",
      confirmButtonColor: "#854D0E",
    })
  } finally {
    loading.value = false
  }
}

// Filter tables based on search query
const filteredTables = computed(() => {
  if (!searchQuery.value) return tables.value

  return tables.value.filter((table) => {
    const query = searchQuery.value.toLowerCase()
    return (
      table.table_number?.toLowerCase().includes(query) ||
      table.capacity?.toString().includes(query) ||
      table.status?.toLowerCase().includes(query) ||
      table.type?.toLowerCase().includes(query)
    )
  })
})

// Delete table
const deleteTable = async (id, tableName) => {
  const result = await Swal.fire({
    title: "Are you sure?",
    html: `You are about to delete <strong>${tableName}</strong>.<br>This action cannot be undone!`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#dc2626",
    cancelButtonColor: "#6b7280",
    confirmButtonText: "Yes, delete it!",
    cancelButtonText: "Cancel",
    reverseButtons: true,
  })

  if (result.isConfirmed) {
    try {
      const response = await axios.delete(`http://localhost:8000/api/tables/${id}`)

      if (response.data.success) {
        tables.value = tables.value.filter((table) => table.id !== id)

        Swal.fire({
          icon: "success",
          title: "Deleted!",
          text: `${tableName} has been successfully deleted.`,
          confirmButtonColor: "#854D0E",
          timer: 2000,
        })
      }
    } catch (error) {
      console.error("Error deleting table:", error)
      Swal.fire({
        icon: "error",
        title: "Delete Failed!",
        text: error.response?.data?.message || "Failed to delete table.",
        confirmButtonColor: "#854D0E",
      })
    }
  }
}

// Show QR Code
const showQRCode = (table) => {
  if (table.qr_code_url) {
    Swal.fire({
      title: `QR Code - ${table.table_number}`,
      imageUrl: table.qr_code_url,
      imageWidth: 300,
      imageHeight: 300,
      imageAlt: "QR Code",
      confirmButtonColor: "#854D0E",
      showCloseButton: true,
      confirmButtonText: "Close",
    })
  } else {
    Swal.fire({
      icon: "info",
      title: "No QR Code",
      text: "QR Code not available for this table.",
      confirmButtonColor: "#854D0E",
    })
  }
}

// Navigate functions
const goToAddTable = () => {
  router.push("/superadmin/tables/add")
}

const editTable = (id) => {
  router.push(`/superadmin/tables/edit/${id}`)
}

// Status helpers
const getStatusClass = (status) => {
  const classes = {
    available: "text-green-600",
    occupied: "text-red-600",
    reserved: "text-yellow-600",
  }
  return classes[status?.toLowerCase()] || "text-gray-600"
}

onMounted(() => {
  fetchTables()
})
</script>

<template>
  <div class="flex-1 overflow-auto font-inter">
    <div class="p-8 max-w-[1920px] mx-auto">
      <!-- Page Title -->
      <div class="mb-6">
        <h1 class="text-color-azure-11 text-4xl font-bold font-inter leading-tight">Tables Management</h1>
        <p class="text-color-grey-46 text-sm mt-2">Manage your restaurant tables</p>
      </div>

      <!-- Add Table Button -->
      <div class="mb-6 flex justify-end">
        <button
          @click="goToAddTable"
          style="background-color: #854D0E"
          class="px-5 py-2.5 text-white rounded-lg flex items-center gap-2 transition-all duration-200 hover:opacity-90 hover:shadow-md active:scale-95 font-medium"
        >
          <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
          </svg>
          <span class="text-sm font-medium font-inter">Add Tables</span>
        </button>
      </div>

      <!-- Table Card -->
      <div class="rounded-2xl overflow-hidden">
        <!-- Search Bar -->
        <div class="p-6 bg-white border-b border-gray-100">
          <div class="relative">
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="11" cy="11" r="6" stroke="currentColor" stroke-width="2"/>
              <path d="M20 20L17 17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search tables..."
              class="w-full pl-10 pr-4 py-2.5 bg-white rounded-xl border border-gray-200 text-sm font-normal font-inter text-gray-700 placeholder-gray-400 outline-none focus:border-color-brown focus:ring-2 focus:ring-orange-100 transition-all duration-200"
            />
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="p-16 text-center bg-white">
          <div class="inline-flex items-center gap-3">
            <div class="w-5 h-5 border-2 border-color-brown border-t-transparent rounded-full animate-spin"></div>
            <div class="text-color-grey-46 text-sm font-medium">Loading tables...</div>
          </div>
        </div>

        <!-- Table -->
        <div v-else class="overflow-x-auto bg-white">
          <table class="w-full">
            <!-- Table Header -->
            <thead class="bg-gray-50">
              <tr class="border-b border-gray-100">
                <th class="px-6 py-4 text-left">
                  <div class="text-color-azure-34 text-xs font-semibold font-inter uppercase tracking-wider">Tables Number</div>
                </th>
                <th class="px-6 py-4 text-center">
                  <div class="text-color-azure-34 text-xs font-semibold font-inter uppercase tracking-wider">Capacity</div>
                </th>
                <th class="px-6 py-4 text-center">
                  <div class="text-color-azure-34 text-xs font-semibold font-inter uppercase tracking-wider">Status</div>
                </th>
                <th class="px-6 py-4 text-center">
                  <div class="text-color-azure-34 text-xs font-semibold font-inter uppercase tracking-wider">QR-Link</div>
                </th>
                <th class="px-6 py-4 text-center">
                  <div class="text-color-azure-34 text-xs font-semibold font-inter uppercase tracking-wider">Actions</div>
                </th>
              </tr>
            </thead>

            <!-- Table Body -->
            <tbody class="bg-white divide-y divide-gray-100">
              <template v-if="filteredTables.length === 0">
                <tr>
                  <td colspan="5" class="p-12 text-center">
                    <div class="flex flex-col items-center gap-3">
                      <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                      </svg>
                      <div class="text-color-grey-46 text-sm font-medium">No tables found</div>
                      <div class="text-gray-400 text-xs">{{ searchQuery ? 'Try different search keywords' : 'Start by adding your first table' }}</div>
                    </div>
                  </td>
                </tr>
              </template>

              <template v-else>
                <tr
                  v-for="table in filteredTables"
                  :key="table.id"
                  class="hover:bg-gray-50 transition-colors duration-150"
                >
                  <!-- Table Number -->
                  <td class="px-6 py-4">
                    <div class="text-color-azure-11 text-sm font-semibold font-inter">{{ table.table_number }}</div>
                  </td>

                  <!-- Capacity -->
                  <td class="px-6 py-4 text-center">
                    <div class="text-color-grey-46 text-sm font-normal font-inter">{{ table.capacity }} People</div>
                  </td>

                  <!-- Status -->
                  <td class="px-6 py-4">
                    <div class="flex justify-center items-center gap-2">
                      <div :class="table.status === 'available' ? 'bg-green-500' : table.status === 'occupied' ? 'bg-red-500' : 'bg-yellow-500'" class="w-2 h-2 rounded-full shadow-sm"></div>
                      <div :class="getStatusClass(table.status)" class="text-sm font-semibold font-inter capitalize">
                        {{ table.status }}
                      </div>
                    </div>
                  </td>

                  <!-- QR Link -->
                  <td class="px-6 py-4 text-center">
                    <button
                      @click="showQRCode(table)"
                      class="p-2 text-gray-500 hover:text-color-brown hover:bg-orange-50 rounded-lg transition-all duration-200 active:scale-95"
                      title="View QR Code"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                      </svg>
                    </button>
                  </td>

                  <!-- Actions -->
                  <td class="px-6 py-4">
                    <div class="flex justify-center items-center gap-1">
                      <!-- Edit Button -->
                      <button
                        @click="editTable(table.id)"
                        class="p-2 text-gray-500 hover:text-color-brown hover:bg-orange-50 rounded-lg transition-all duration-200 active:scale-95"
                        title="Edit table"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                      </button>

                      <!-- Delete Button -->
                      <button
                        @click="deleteTable(table.id, table.table_number)"
                        class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all duration-200 active:scale-95"
                        title="Delete table"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

.font-inter {
  font-family: 'Inter', sans-serif;
}

/* Custom scrollbar */
.overflow-x-auto::-webkit-scrollbar {
  height: 8px;
}

.overflow-x-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 10px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}

/* Mobile Responsive Styles */
@media (max-width: 768px) {
  /* Reduce padding on mobile */
  .p-8 {
    padding: 1rem !important;
  }
  
  /* Smaller title on mobile */
  .text-4xl {
    font-size: 1.5rem !important;
    line-height: 2rem !important;
  }
  
  /* Smaller button text and padding on mobile */
  button {
    padding: 0.5rem 1rem !important;
    font-size: 0.875rem !important;
  }
  
  /* Reduce table cell padding on mobile */
  table th,
  table td {
    padding: 0.75rem 0.5rem !important;
    font-size: 0.813rem !important;
  }
  
  /* Smaller action buttons on mobile */
  table button {
    padding: 0.375rem !important;
  }
  
  /* Make table scrollable horizontally */
  .overflow-x-auto {
    -webkit-overflow-scrolling: touch;
  }
}
</style>
