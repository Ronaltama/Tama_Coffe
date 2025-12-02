<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

// State
const stats = ref({
  totalUsers: 0,
  totalOrders: 0,
  totalTables: 0,
  totalProducts: 0
})
const users = ref([])
const topProducts = ref([])
const loading = ref(true)
const activeTab = ref('daily')

// Get current date formatted
const getCurrentDate = () => {
  const options = { year: 'numeric', month: 'long', day: 'numeric' }
  return new Date().toLocaleDateString('en-US', options)
}

// Fetch dashboard data
const fetchDashboard = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token')
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
    
    // Fetch stats
    const statsResponse = await axios.get('http://127.0.0.1:8000/api/superadmin/dashboard')
    stats.value = {
      totalUsers: statsResponse.data.total_users || 0,
      totalOrders: statsResponse.data.total_orders || 0,
      totalTables: statsResponse.data.total_tables || 0,
      totalProducts: statsResponse.data.total_products || 0
    }
    
    // Fetch users
    const usersResponse = await axios.get('http://127.0.0.1:8000/api/users')
    users.value = usersResponse.data.slice(0, 5) // Top 5 users
    
    // Fetch products
    const productsResponse = await axios.get('http://127.0.0.1:8000/api/products')
    topProducts.value = productsResponse.data.slice(0, 5) // Top 5 products
    
  } catch (error) {
    console.error('Error fetching dashboard:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchDashboard()
})
</script>

<template>
  <div class="flex-1 overflow-auto">
    <div class="p-8">
      <!-- Dashboard Title -->
      <div class="mb-8">
        <h1 class="text-color-azure-11 text-4xl font-bold font-inter leading-10">Dashboard</h1>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-3 gap-6 mb-8">
        <!-- Total Orders -->
        <div class="bg-white rounded-xl shadow p-6">
          <div class="text-color-grey-46 text-sm font-normal font-inter mb-1">Total Orders</div>
          <div class="text-color-azure-11 text-3xl font-bold font-inter">{{ stats.totalOrders.toLocaleString() }}</div>
        </div>
        <!-- Active Users -->
        <div class="bg-white rounded-xl shadow p-6">
          <div class="text-color-grey-46 text-sm font-normal font-inter mb-1">Active Users</div>
          <div class="text-color-azure-11 text-3xl font-bold font-inter">{{ stats.totalUsers.toLocaleString() }}</div>
        </div>
        <!-- Revenue -->
        <div class="bg-white rounded-xl shadow p-6">
          <div class="text-color-grey-46 text-sm font-normal font-inter mb-1">Revenue</div>
          <div class="text-color-azure-11 text-3xl font-bold font-inter">0</div>
        </div>
      </div>

      <!-- Sales Reports Section -->
      <div class="bg-white rounded-xl shadow p-6 mb-8">
        <!-- Section Header -->
        <div class="flex items-center justify-between mb-6">
          <div class="text-color-azure-11 text-xl font-bold font-inter">
            Sales Reports
          </div>
          <div class="flex gap-2">
            <button 
              @click="activeTab = 'daily'" 
              :style="activeTab === 'daily' ? 'background-color: #854D0E' : ''"
              :class="activeTab === 'daily' ? 'text-white' : 'text-gray-600'"
              class="px-4 py-2 rounded-lg text-sm font-medium font-inter transition-colors"
            >
              Daily
            </button>
            <button 
              @click="activeTab = 'weekly'" 
              :style="activeTab === 'weekly' ? 'background-color: #854D0E' : ''"
              :class="activeTab === 'weekly' ? 'text-white' : 'text-gray-600'"
              class="px-4 py-2 rounded-lg text-sm font-medium font-inter transition-colors"
            >
              Weekly
            </button>
            <button 
              @click="activeTab = 'monthly'" 
              :style="activeTab === 'monthly' ? 'background-color: #854D0E' : ''"
              :class="activeTab === 'monthly' ? 'text-white' : 'text-gray-600'"
              class="px-4 py-2 rounded-lg text-sm font-medium font-inter transition-colors"
            >
              Monthly
            </button>
          </div>
        </div>

        <!-- Daily Sales Info & Total Revenue -->
        <div class="flex items-start justify-between mb-4">
          <div>
            <div class="text-color-azure-11 text-base font-semibold font-inter">Daily Sales</div>
            <div class="text-color-grey-46 text-sm font-normal font-inter">{{ getCurrentDate() }}</div>
          </div>
          <div class="text-right">
            <div class="text-color-grey-46 text-sm font-normal font-inter">Total Revenue</div>
            <div class="text-color-azure-11 text-2xl font-bold font-inter">0</div>
          </div>
        </div>

        <!-- Chart Area -->
        <div class="bg-gray-100 rounded-lg p-20 flex items-center justify-center">
          <div class="text-color-grey-46 text-sm font-normal font-inter">
            Sales chart will be displayed here.
          </div>
        </div>
      </div>

      <!-- Top Selling Products & User Management -->
      <div class="grid grid-cols-1 gap-6">
        <!-- Top Selling Products -->
        <div class="bg-white rounded-xl shadow p-6">
          <div class="text-color-azure-11 text-xl font-bold font-inter mb-4">
            Top Selling Products Today
          </div>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="border-b border-gray-200">
                <tr>
                  <th class="text-left py-3 px-4">
                    <div class="text-color-grey-46 text-sm font-semibold font-inter">Product</div>
                  </th>
                  <th class="text-center py-3 px-4">
                    <div class="text-color-grey-46 text-sm font-semibold font-inter">Units Sold</div>
                  </th>
                  <th class="text-right py-3 px-4">
                    <div class="text-color-grey-46 text-sm font-semibold font-inter">Revenue</div>
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="product in topProducts" :key="product.id" class="border-b border-gray-100">
                  <td class="py-3 px-4">
                    <div class="text-color-azure-11 text-sm font-normal font-inter">{{ product.name }}</div>
                  </td>
                  <td class="py-3 px-4 text-center">
                    <div class="text-color-grey-46 text-sm font-normal font-inter">0</div>
                  </td>
                  <td class="py-3 px-4 text-right">
                    <div class="text-color-grey-46 text-sm font-normal font-inter">0</div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- User Management -->
        <div class="bg-white rounded-xl shadow p-6">
          <div class="text-color-azure-11 text-xl font-bold font-inter mb-4">
            User Management
          </div>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="border-b border-gray-200">
                <tr>
                  <th class="text-left py-3 px-4">
                    <div class="text-color-grey-46 text-sm font-semibold font-inter">Name</div>
                  </th>
                  <th class="text-left py-3 px-4">
                    <div class="text-color-grey-46 text-sm font-semibold font-inter">Email</div>
                  </th>
                  <th class="text-center py-3 px-4">
                    <div class="text-color-grey-46 text-sm font-semibold font-inter">Role</div>
                  </th>
                  <th class="text-center py-3 px-4">
                    <div class="text-color-grey-46 text-sm font-semibold font-inter">Status</div>
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="user in users" :key="user.id" class="border-b border-gray-100">
                  <td class="py-3 px-4">
                    <div class="text-color-azure-11 text-sm font-normal font-inter">{{ user.name }}</div>
                  </td>
                  <td class="py-3 px-4">
                    <div class="text-color-grey-46 text-sm font-normal font-inter">{{ user.email }}</div>
                  </td>
                  <td class="py-3 px-4 text-center">
                    <span class="inline-block px-3 py-1 bg-purple-50 text-purple-600 rounded-full text-xs font-medium">
                      {{ user.role?.name === 'admin' ? 'Admin' : user.role?.name === 'superadmin' ? 'Super Admin' : user.role?.name }}
                    </span>
                  </td>
                  <td class="py-3 px-4">
                    <div class="flex items-center justify-center gap-2">
                      <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                      <span class="text-green-600 text-sm font-medium">Active</span>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
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
</style>
