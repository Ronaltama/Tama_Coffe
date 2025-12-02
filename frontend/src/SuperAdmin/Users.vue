<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import Swal from 'sweetalert2'

const router = useRouter()

// === STATE ===
const users = ref([])
const loading = ref(true)
const error = ref(null)
const searchQuery = ref('')
const selectedStatus = ref('all')

// === API BASE URL ===
const API_BASE = 'http://localhost:8000/api'

// === COMPUTED ===
const filteredUsers = computed(() => {
  let result = users.value

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(user => 
      user.name.toLowerCase().includes(query) || 
      user.email.toLowerCase().includes(query)
    )
  }

  return result
})

// === FETCH USERS ===
const fetchUsers = async () => {
  loading.value = true
  error.value = null
  try {
    const token = localStorage.getItem('token')
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
    
    const response = await axios.get(`${API_BASE}/users`)
    users.value = response.data
  } catch (err) {
    console.error(err)
    error.value = 'Failed to fetch users'
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to load users data',
    })
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchUsers()
})

// === HELPER FUNCTIONS ===
const getInitials = (name) => {
  return name
    .split(' ')
    .map(word => word.charAt(0).toUpperCase())
    .slice(0, 2)
    .join('')
}

const getAvatarColor = (name) => {
  const colors = [
    'bg-blue-500',
    'bg-green-500',
    'bg-purple-500',
    'bg-pink-500',
    'bg-yellow-500',
    'bg-indigo-500',
  ]
  const index = name.charCodeAt(0) % colors.length
  return colors[index]
}

// === ACTIONS ===
const addUser = () => {
  router.push('/superadmin/users/add')
}

const editUser = (id) => {
  router.push(`/superadmin/users/edit/${id}`)
}

const deleteUser = async (id, userName) => {
  const result = await Swal.fire({
    title: 'Are you sure?',
    text: `Do you want to delete user "${userName}"? This action cannot be undone!`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Yes, delete it!',
    cancelButtonText: 'Cancel',
    reverseButtons: true
  })

  if (result.isConfirmed) {
    try {
      await axios.delete(`${API_BASE}/users/${id}`)
      users.value = users.value.filter(u => u.id !== id)
      
      Swal.fire({
        icon: 'success',
        title: 'Deleted!',
        text: 'User has been deleted successfully.',
        timer: 2000,
        showConfirmButton: false
      })
    } catch (err) {
      console.error(err)
      Swal.fire({
        icon: 'error',
        title: 'Delete Failed',
        text: err.response?.data?.message || 'Failed to delete user',
      })
    }
  }
}
</script>

<template>
  <div class="flex-1 overflow-auto font-inter">
    <div class="p-8 max-w-[1920px] mx-auto">
      <!-- Page Title -->
      <div class="mb-6">
        <h1 class="text-color-azure-11 text-4xl font-bold font-inter leading-tight">User Management</h1>
        <p class="text-color-grey-46 text-sm mt-2">Manage admin users and their permissions</p>
      </div>

      <!-- Add User Button -->
      <div class="mb-6 flex justify-end">
        <button
          @click="addUser"
          style="background-color: #854D0E" class="px-5 py-2.5 text-white rounded-lg flex items-center gap-2 transition-all duration-200 hover:shadow-md active:scale-95 font-medium"
        >
          <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
          </svg>
          <span class="text-sm font-medium font-inter">Add Admin User</span>
        </button>
      </div>

      <!-- Table Card -->
      <div class="rounded-2xl overflow-hidden">
        <!-- Search and Filters Bar -->
        <div class="p-6 bg-white border-b border-gray-100">
          <div class="flex gap-3">
            <!-- Search Input -->
            <div class="flex-1 relative">
              <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="11" cy="11" r="6" stroke="currentColor" stroke-width="2"/>
                <path d="M20 20L17 17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search users by name or email..."
                class="w-full pl-10 pr-4 py-2.5 bg-white rounded-xl border border-gray-200 text-sm font-normal font-inter text-gray-700 placeholder-gray-400 outline-none focus:border-color-brown focus:ring-2 focus:ring-orange-100 transition-all duration-200"
              />
            </div>

            <!-- Status Filter -->
            <select
              v-model="selectedStatus"
              class="px-4 py-2.5 bg-white rounded-xl border border-gray-200 text-color-azure-17 text-sm font-medium font-inter outline-none focus:border-color-brown focus:ring-2 focus:ring-orange-100 transition-all duration-200 cursor-pointer"
            >
              <option value="all">Any Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="p-16 text-center bg-white">
          <div class="inline-flex items-center gap-3">
            <div class="w-5 h-5 border-2 border-color-brown border-t-transparent rounded-full animate-spin"></div>
            <div class="text-color-grey-46 text-sm font-medium">Loading users...</div>
          </div>
        </div>

        <!-- Table -->
        <div v-else class="overflow-x-auto bg-white">
          <table class="w-full">
            <!-- Table Header -->
            <thead class="bg-gray-50">
              <tr class="border-b border-gray-100">
                <th class="px-6 py-4 text-left">
                  <div class="text-color-azure-34 text-xs font-semibold font-inter uppercase tracking-wider">User</div>
                </th>
                <th class="px-6 py-4 text-center">
                  <div class="text-color-azure-34 text-xs font-semibold font-inter uppercase tracking-wider">Role</div>
                </th>
                <th class="px-6 py-4 text-center">
                  <div class="text-color-azure-34 text-xs font-semibold font-inter uppercase tracking-wider">Status</div>
                </th>
                <th class="px-6 py-4 text-center">
                  <div class="text-color-azure-34 text-xs font-semibold font-inter uppercase tracking-wider">Actions</div>
                </th>
              </tr>
            </thead>

            <!-- Table Body -->
            <tbody class="bg-white divide-y divide-gray-100">
              <template v-if="filteredUsers.length === 0">
                <tr>
                  <td colspan="4" class="p-12 text-center">
                    <div class="flex flex-col items-center gap-3">
                      <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                      </svg>
                      <div class="text-color-grey-46 text-sm font-medium">No users found</div>
                      <div class="text-gray-400 text-xs">Try adjusting your search or filters</div>
                    </div>
                  </td>
                </tr>
              </template>

              <template v-else>
                <tr
                  v-for="user in filteredUsers"
                  :key="user.id"
                  class="hover:bg-gray-50 transition-colors duration-150"
                >
                  <!-- User Column -->
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <!-- Avatar -->
                      <div :class="getAvatarColor(user.name)" class="w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold text-sm shadow-sm">
                        {{ getInitials(user.name) }}
                      </div>
                      <!-- Name & Email -->
                      <div class="flex flex-col">
                        <div class="text-color-azure-11 text-sm font-semibold font-inter">{{ user.name }}</div>
                        <div class="text-color-grey-46 text-xs font-normal font-inter mt-0.5">{{ user.email }}</div>
                      </div>
                    </div>
                  </td>

                  <!-- Role Column -->
                  <td class="px-6 py-4">
                    <div class="flex justify-center">
                      <div class="px-3 py-1.5 bg-pink-50 border border-pink-100 rounded-full shadow-sm">
                        <div class="text-pink-700 text-xs font-semibold font-inter uppercase tracking-wide">
                          {{ user.role?.name === 'admin' ? 'Admin' : user.role?.name === 'superadmin' ? 'Super Admin' : user.role?.name }}
                        </div>
                      </div>
                    </div>
                  </td>

                  <!-- Status Column -->
                  <td class="px-6 py-4">
                    <div class="flex justify-center items-center gap-2">
                      <div :class="user.status === 'inactive' ? 'bg-red-500' : 'bg-green-500'" class="w-2 h-2 rounded-full shadow-sm"></div>
                      <div :class="user.status === 'inactive' ? 'text-red-600' : 'text-green-600'" class="text-sm font-semibold font-inter">
                        {{ user.status === 'inactive' ? 'Inactive' : 'Active' }}
                      </div>
                    </div>
                  </td>

                  <!-- Actions Column -->
                  <td class="px-6 py-4">
                    <div class="flex justify-center items-center gap-1">
                      <!-- Edit Button -->
                      <button
                        @click="editUser(user.id)"
                        class="p-2 text-gray-500 hover:text-color-brown hover:bg-orange-50 rounded-lg transition-all duration-200 active:scale-95"
                        title="Edit user"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                      </button>

                      <!-- Delete Button -->
                      <button
                        @click="deleteUser(user.id, user.name)"
                        class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all duration-200 active:scale-95"
                        title="Delete user"
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
</style>