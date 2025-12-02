<script setup>
import { ref, onMounted, watch, nextTick } from 'vue'
import axios from 'axios'
import {
  Chart,
  CategoryScale,
  LinearScale,
  BarElement,
  BarController,
  LineElement,
  LineController,
  PointElement,
  Title,
  Tooltip,
  Legend,
  Filler,
} from 'chart.js'

// Register Chart.js components
Chart.register(
  CategoryScale,
  LinearScale,
  BarElement,
  BarController,
  LineElement,
  LineController,
  PointElement,
  Title,
  Tooltip,
  Legend,
  Filler
)

// State
const stats = ref({
  totalUsers: 0,
  totalOrders: 0,
  totalTables: 0,
  totalProducts: 0,
  totalRevenue: 0,
  totalAdmins: 0,
  totalSuperadmin: 0
})
const recentAdmins = ref([])
const topProducts = ref([])
const loading = ref(true)
const activeTab = ref('daily')
const salesChart = ref(null)
const chartCanvas = ref(null)
const salesData = ref([])

// Format currency
const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(value)
}

// Fetch dashboard data
const fetchDashboard = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token')
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`

    // Fetch stats
    const dashRes = await axios.get('http://127.0.0.1:8000/api/superadmin/dashboard')
    stats.value.totalOrders = dashRes.data.total_orders || 0
    stats.value.totalProducts = dashRes.data.total_products || 0
    stats.value.totalUsers = dashRes.data.total_users || 0
    stats.value.totalTables = dashRes.data.total_tables || 0
    stats.value.totalAdmins = dashRes.data.total_admins || 0
    stats.value.totalSuperadmin = dashRes.data.total_superadmin || 0
    stats.value.totalRevenue = 0

    // Fetch users
    const userRes = await axios.get('http://127.0.0.1:8000/api/users')
    const userList = userRes.data ?? []
    recentAdmins.value = userList.map((u) => ({
      name: u.name,
      email: u.email,
      role: 'Admin',
      status: 'Active',
    }))

    // Fetch products
    const productRes = await axios.get('http://127.0.0.1:8000/api/products')
    const productList = productRes.data.data ?? []
    topProducts.value = productList.slice(0, 3).map((p) => ({
      name: p.name,
      units_sold: 0,
      revenue: 0,
    }))

  } catch (error) {
    console.error('Error fetching dashboard:', error)
  } finally {
    loading.value = false
  }
}

// Fetch sales data
const fetchSalesData = async (period) => {
  try {
    const token = localStorage.getItem('token')
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`

    const response = await axios.get(
      `http://127.0.0.1:8000/api/superadmin/sales-data?period=${period}`
    )

    salesData.value = response.data
    renderChart()
  } catch (error) {
    console.error('Error fetching sales data:', error)
    salesData.value = []
  }
}

// Render chart
const renderChart = () => {
  if (!chartCanvas.value) {
    console.error('chartCanvas is null!')
    return
  }

  // Destroy existing chart
  if (salesChart.value) {
    salesChart.value.destroy()
  }

  const ctx = chartCanvas.value.getContext('2d')
  const labels = salesData.value.map((item) => item.label)
  const values = salesData.value.map((item) => item.value)

  const chartType = activeTab.value === 'product' ? 'bar' : 'line'

  salesChart.value = new Chart(ctx, {
    type: chartType,
    data: {
      labels: labels,
      datasets: [
        {
          label: 'Sales (IDR)',
          data: values,
          backgroundColor:
            activeTab.value === 'product'
              ? 'rgba(133, 77, 14, 0.7)'
              : 'rgba(133, 77, 14, 0.2)',
          borderColor: 'rgba(133, 77, 14, 1)',
          borderWidth: 2,
          fill: true,
          tension: 0.4,
          pointBackgroundColor: 'rgba(133, 77, 14, 1)',
          pointBorderColor: '#fff',
          pointBorderWidth: 2,
          pointRadius: 4,
          pointHoverRadius: 6,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false,
        },
        tooltip: {
          backgroundColor: 'rgba(0, 0, 0, 0.8)',
          padding: 12,
          titleFont: {
            size: 14,
            weight: 'bold',
          },
          bodyFont: {
            size: 13,
          },
          callbacks: {
            label: function (context) {
              return 'Sales: ' + formatCurrency(context.parsed.y)
            },
          },
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function (value) {
              return 'Rp ' + value / 1000 + 'k'
            },
          },
          grid: {
            color: 'rgba(0, 0, 0, 0.05)',
          },
        },
        x: {
          grid: {
            display: false,
          },
        },
      },
    },
  })
}

// Watch for tab changes
watch(activeTab, (newTab) => {
  fetchSalesData(newTab)
})

onMounted(() => {
  fetchDashboard()
  nextTick(() => {
    fetchSalesData(activeTab.value)
  })
})
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 p-6 lg:p-8">
    <div class="max-w-7xl mx-auto space-y-8">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-4xl font-bold text-gray-900 mb-2">
            Superadmin Dashboard
          </h1>
          <p class="text-gray-600">
            Welcome back! Here's what's happening today.
          </p>
        </div>
        <div class="hidden lg:flex items-center gap-3">
          <div class="bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200">
            <p class="text-xs text-gray-500">Today</p>
            <p class="text-sm font-semibold text-gray-900">
              {{
                new Date().toLocaleDateString('id-ID', {
                  weekday: 'long',
                  year: 'numeric',
                  month: 'long',
                  day: 'numeric',
                })
              }}
            </p>
          </div>
        </div>
      </div>

      <!-- Stats Cards Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Total Orders -->
        <router-link
          to="/superadmin/history"
          class="block group bg-gradient-to-br from-amber-700 to-amber-900 rounded-2xl p-6 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 cursor-pointer"
        >
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <p class="text-amber-100 text-sm font-medium mb-1">
                Total Orders
              </p>
              <h3 class="text-4xl font-bold text-white mb-2">
                {{ stats.totalOrders }}
              </h3>
              <p class="text-amber-100 text-xs">All time orders</p>
            </div>
            <div class="bg-white/20 backdrop-blur-sm p-3 rounded-xl group-hover:bg-white/30 transition-all">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
              </svg>
            </div>
          </div>
        </router-link>

        <!-- Total Products -->
        <router-link
          to="/superadmin/products"
          class="block group bg-gradient-to-br from-yellow-600 to-yellow-800 rounded-2xl p-6 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 cursor-pointer"
        >
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <p class="text-yellow-100 text-sm font-medium mb-1">
                Total Products
              </p>
              <h3 class="text-4xl font-bold text-white mb-2">
                {{ stats.totalProducts }}
              </h3>
              <p class="text-yellow-100 text-xs">Menu items</p>
            </div>
            <div class="bg-white/20 backdrop-blur-sm p-3 rounded-xl group-hover:bg-white/30 transition-all">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
              </svg>
            </div>
          </div>
        </router-link>

        <!-- Total Users -->
        <router-link
          to="/superadmin/users"
          class="block group bg-gradient-to-br from-orange-700 to-orange-900 rounded-2xl p-6 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 cursor-pointer"
        >
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <p class="text-orange-100 text-sm font-medium mb-1">
                Total Users
              </p>
              <h3 class="text-4xl font-bold text-white mb-2">
                {{ stats.totalUsers }}
              </h3>
              <p class="text-orange-100 text-xs">Registered users</p>
            </div>
            <div class="bg-white/20 backdrop-blur-sm p-3 rounded-xl group-hover:bg-white/30 transition-all">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
              </svg>
            </div>
          </div>
        </router-link>

        <!-- Admin Accounts -->
        <router-link
          to="/superadmin/users"
          class="block group bg-gradient-to-br from-amber-600 to-amber-800 rounded-2xl p-6 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 cursor-pointer"
        >
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <p class="text-amber-100 text-sm font-medium mb-1">
                Admin Accounts
              </p>
              <h3 class="text-4xl font-bold text-white mb-2">
                {{ stats.totalAdmins }}
              </h3>
              <p class="text-amber-100 text-xs">Active admins</p>
            </div>
            <div class="bg-white/20 backdrop-blur-sm p-3 rounded-xl group-hover:bg-white/30 transition-all">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
              </svg>
            </div>
          </div>
        </router-link>

        <!-- Superadmin -->
        <router-link
          to="/superadmin/users"
          class="block group bg-gradient-to-br from-red-800 to-red-950 rounded-2xl p-6 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 cursor-pointer"
        >
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <p class="text-red-100 text-sm font-medium mb-1">Superadmin</p>
              <h3 class="text-4xl font-bold text-white mb-2">
                {{ stats.totalSuperadmin }}
              </h3>
              <p class="text-red-100 text-xs">Super accounts</p>
            </div>
            <div class="bg-white/20 backdrop-blur-sm p-3 rounded-xl group-hover:bg-white/30 transition-all">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
              </svg>
            </div>
          </div>
        </router-link>

        <!-- Tables -->
        <router-link
          to="/superadmin/tables"
          class="block group bg-gradient-to-br from-stone-700 to-stone-900 rounded-2xl p-6 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 cursor-pointer"
        >
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <p class="text-stone-100 text-sm font-medium mb-1">Tables</p>
              <h3 class="text-4xl font-bold text-white mb-2">
                {{ stats.totalTables }}
              </h3>
              <p class="text-stone-100 text-xs">Available tables</p>
            </div>
            <div class="bg-white/20 backdrop-blur-sm p-3 rounded-xl group-hover:bg-white/30 transition-all">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
              </svg>
            </div>
          </div>
        </router-link>
      </div>

      <!-- Sales Report Section -->
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100">
        <div class="p-6 border-b border-gray-100">
          <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
              <h2 class="text-2xl font-bold text-gray-900">Sales Reports</h2>
              <p class="text-sm text-gray-500 mt-1">
                Track your sales performance
              </p>
            </div>

            <div class="flex gap-1 bg-gray-100 rounded-xl p-1.5">
              <button
                @click="activeTab = 'daily'"
                :class="
                  activeTab === 'daily'
                    ? 'bg-white text-amber-700 shadow-sm'
                    : 'text-gray-600 hover:text-gray-900'
                "
                class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200"
              >
                Daily
              </button>
              <button
                @click="activeTab = 'weekly'"
                :class="
                  activeTab === 'weekly'
                    ? 'bg-white text-amber-700 shadow-sm'
                    : 'text-gray-600 hover:text-gray-900'
                "
                class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200"
              >
                Weekly
              </button>
              <button
                @click="activeTab = 'monthly'"
                :class="
                  activeTab === 'monthly'
                    ? 'bg-white text-amber-700 shadow-sm'
                    : 'text-gray-600 hover:text-gray-900'
                "
                class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200"
              >
                Monthly
              </button>
              <button
                @click="activeTab = 'product'"
                :class="
                  activeTab === 'product'
                    ? 'bg-white text-amber-700 shadow-sm'
                    : 'text-gray-600 hover:text-gray-900'
                "
                class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200"
              >
                Per Product
              </button>
            </div>
          </div>
        </div>

        <div class="p-6">
          <!-- Sales Chart -->
          <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-4 mb-8" style="height: 320px">
            <canvas ref="chartCanvas"></canvas>
          </div>

          <!-- Top Products -->
          <div>
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-bold text-gray-900">
                🔥 Top Selling Products Today
              </h3>
              <span class="text-sm text-gray-500">Last updated: now</span>
            </div>

            <div class="overflow-x-auto rounded-xl border border-gray-200">
              <table class="w-full min-w-[640px]">
                <thead class="bg-gray-50">
                  <tr class="text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    <th class="py-4 px-6">Product</th>
                    <th class="py-4 px-6">Units Sold</th>
                    <th class="py-4 px-6">Revenue</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                  <tr
                    v-for="(p, idx) in topProducts"
                    :key="p.name"
                    class="hover:bg-gray-50 transition-colors"
                  >
                    <td class="py-4 px-6">
                      <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-amber-400 to-amber-600 text-white font-bold text-sm">
                          {{ idx + 1 }}
                        </div>
                        <span class="font-medium text-gray-900">{{ p.name }}</span>
                      </div>
                    </td>
                    <td class="py-4 px-6">
                      <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-700">
                        {{ p.units_sold }}
                      </span>
                    </td>
                    <td class="py-4 px-6">
                      <span class="font-semibold text-green-600">{{ formatCurrency(p.revenue) }}</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Admin List -->
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100">
        <div class="p-6 border-b border-gray-100">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-2xl font-bold text-gray-900">Admin Users</h2>
              <p class="text-sm text-gray-500 mt-1">
                Manage admin accounts and permissions
              </p>
            </div>
            <router-link
              to="/superadmin/users/add"
              class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-medium text-sm transition-colors duration-200 flex items-center gap-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
              </svg>
              Add Admin
            </router-link>
          </div>
        </div>

        <div class="p-6">
          <div class="overflow-x-auto rounded-xl border border-gray-200">
            <table class="w-full min-w-[640px]">
              <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  <th class="py-4 px-6">Name</th>
                  <th class="py-4 px-6">Email</th>
                  <th class="py-4 px-6">Role</th>
                  <th class="py-4 px-6">Status</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-100">
                <tr
                  v-for="admin in recentAdmins"
                  :key="admin.email"
                  class="hover:bg-gray-50 transition-colors"
                >
                  <td class="py-4 px-6">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-400 to-pink-500 flex items-center justify-center text-white font-semibold text-sm">
                        {{ admin.name.charAt(0).toUpperCase() }}
                      </div>
                      <span class="font-medium text-gray-900">{{ admin.name }}</span>
                    </div>
                  </td>
                  <td class="py-4 px-6 text-gray-600">{{ admin.email }}</td>
                  <td class="py-4 px-6">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-pink-100 text-pink-700">
                      <svg class="w-3 h-3 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                      </svg>
                      Admin
                    </span>
                  </td>
                  <td class="py-4 px-6">
                    <span class="inline-flex items-center text-sm font-medium text-green-600">
                      <span class="h-2 w-2 rounded-full bg-green-500 mr-2 animate-pulse"></span>
                      Active
                    </span>
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
