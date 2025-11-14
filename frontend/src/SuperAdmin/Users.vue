<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import Swal from 'sweetalert2'

const router = useRouter()

// === STATE ===
const users = ref([])
const loading = ref(true)
const error = ref(null)

// === API BASE URL ===
const API_BASE = 'http://localhost:8000/api'

// === FETCH USERS ===
const fetchUsers = async () => {
  loading.value = true
  error.value = null
  try {
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
const getRoleClasses = (role) => {
  const roleName = role?.name || role
  if (roleName === 'superadmin' || roleName === 'Super Admin') {
    return 'bg-blue-100 text-blue-700'
  }
  if (roleName === 'admin' || roleName === 'Admin') {
    return 'bg-pink-100 text-pink-700'
  }
  return 'bg-gray-100 text-gray-700'
}

const getRoleName = (role) => {
  const roleName = role?.name || role
  if (roleName === 'superadmin') return 'Super Admin'
  if (roleName === 'admin') return 'Admin'
  return roleName
}

const getStatusClasses = (status) => {
  // Anda bisa sesuaikan dengan field status di database
  // Untuk sementara, semua user dianggap active
  return 'bg-green-100 text-green-700'
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
      
      // Update local state
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
  <div class="p-6 bg-gray-50 min-h-screen">
    <div class="space-y-8">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">User Management</h1>
        <button
          @click="addUser"
          class="px-5 py-2.5 bg-amber-700 text-white rounded-lg hover:bg-amber-800 transition shadow-sm flex items-center gap-2"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
          </svg>
          Add User
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="bg-white border border-gray-200 rounded-xl p-12 shadow-sm text-center">
        <div class="flex items-center justify-center">
          <svg class="animate-spin h-8 w-8 text-amber-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span class="ml-3 text-gray-600">Loading users...</span>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-white border border-gray-200 rounded-xl p-12 shadow-sm text-center">
        <div class="text-red-500">
          <svg class="h-12 w-12 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="text-lg font-medium">{{ error }}</p>
          <button @click="fetchUsers" class="mt-4 px-4 py-2 bg-amber-700 text-white rounded-lg hover:bg-amber-800 transition">
            Retry
          </button>
        </div>
      </div>

      <!-- Table -->
      <div v-else class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full min-w-[640px]">
            <thead>
              <tr class="border-b border-gray-200 text-left text-sm font-semibold text-gray-700 bg-gray-50">
                <th class="py-4 px-6">Name</th>
                <th class="py-4 px-6">Email</th>
                <th class="py-4 px-6">Username</th>
                <th class="py-4 px-6">Role</th>
                <th class="py-4 px-6">Status</th>
                <th class="py-4 px-6 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <!-- Empty State -->
              <template v-if="users.length === 0">
                <tr>
                  <td colspan="6" class="py-12 text-center">
                    <div class="flex flex-col items-center justify-center">
                      <svg class="h-16 w-16 text-gray-400 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                      </svg>
                      <p class="text-gray-500 text-lg font-medium">No users found</p>
                      <p class="text-gray-400 text-sm mt-1">Start by adding a new user</p>
                    </div>
                  </td>
                </tr>
              </template>

              <!-- User Rows -->
              <template v-else>
                <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50 transition-colors">
                  
                  <td class="py-4 px-6 text-sm text-gray-900 font-medium">
                    {{ user.name }}
                  </td>

                  <td class="py-4 px-6 text-sm text-gray-700">
                    {{ user.email }}
                  </td>

                  <td class="py-4 px-6 text-sm text-gray-700">
                    {{ user.username }}
                  </td>
                  
                  <td class="py-4 px-6">
                    <span
                      class="inline-flex items-center px-3 py-1 text-xs rounded-full font-medium"
                      :class="getRoleClasses(user.role)"
                    >
                      {{ getRoleName(user.role) }}
                    </span>
                  </td>

                  <td class="py-4 px-6">
                    <span
                      class="inline-flex items-center px-3 py-1 text-xs rounded-full font-medium"
                      :class="getStatusClasses('Active')"
                    >
                      <span class="w-1.5 h-1.5 rounded-full bg-green-600 mr-1.5"></span>
                      Active
                    </span>
                  </td>
                  
                  <td class="py-4 px-6 text-sm">
                    <div class="flex justify-end gap-2">
                      <button
                        @click="editUser(user.id)"
                        class="p-2 text-gray-600 hover:text-amber-700 hover:bg-amber-50 rounded transition-colors"
                        title="Edit"
                      >
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                          <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                      </button>
                      <button
                        @click="deleteUser(user.id, user.name)"
                        class="p-2 text-gray-600 hover:text-red-700 hover:bg-red-50 rounded transition-colors"
                        title="Delete"
                      >
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
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