<script setup>
import { ref, onMounted, watch, nextTick, computed } from 'vue'
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

// Computed properties for chart
const chartTotalRevenue = computed(() => {
  return salesData.value.reduce((sum, item) => sum + (parseFloat(item.value) || 0), 0)
})

const chartTitle = computed(() => {
  switch (activeTab.value) {
    case 'daily': return 'Daily Sales'
    case 'weekly': return 'Weekly Sales'
    case 'monthly': return 'Monthly Sales'
    case 'product': return 'Product Sales'
    default: return 'Sales Reports'
  }
})

const chartSubtitle = computed(() => {
  return new Date().toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
})

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

    // Fetch stats including revenue and top products
    const dashRes = await axios.get('http://127.0.0.1:8000/api/superadmin/dashboard')
    stats.value.totalOrders = dashRes.data.total_orders || 0
    stats.value.totalProducts = dashRes.data.total_products || 0
    stats.value.totalUsers = dashRes.data.total_users || 0
    stats.value.totalTables = dashRes.data.total_tables || 0
    stats.value.totalAdmins = dashRes.data.total_admins || 0
    stats.value.totalSuperadmin = dashRes.data.total_superadmin || 0
    stats.value.totalRevenue = dashRes.data.total_revenue || 0

    // Set top products from backend
    topProducts.value = (dashRes.data.top_products || []).map((p) => ({
      name: p.name,
      units_sold: p.units_sold || 0,
      revenue: p.revenue || 0,
    }))

    // Fetch users for user management table
    const userRes = await axios.get('http://127.0.0.1:8000/api/users')
    const userList = userRes.data ?? []
    recentAdmins.value = userList.map((u) => ({
      name: u.name,
      email: u.email,
      role: 'Admin',
      status: 'Active',
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
  <div class="flex-1 p-8 overflow-auto">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-4xl font-bold text-gray-900 mb-2">Dashboard</h1>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <!-- Total Orders -->
      <router-link
        to="/superadmin/history"
        class="bg-white p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow cursor-pointer"
      >
        <p class="text-lg font-semibold text-gray-700 mb-2">Total Orders</p>
        <h3 class="text-4xl font-bold text-gray-900">{{ stats.totalOrders.toLocaleString() }}</h3>
      </router-link>

      <!-- Active Users -->
      <router-link
        to="/superadmin/users"
        class="bg-white p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow cursor-pointer"
      >
        <p class="text-lg font-semibold text-gray-700 mb-2">Active Users</p>
        <h3 class="text-4xl font-bold text-gray-900">{{ stats.totalUsers.toLocaleString() }}</h3>
      </router-link>

      <!-- Revenue -->
      <div class="bg-white p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow">
        <p class="text-lg font-semibold text-gray-700 mb-2">Revenue</p>
        <h3 class="text-4xl font-bold text-gray-900">{{ formatCurrency(stats.totalRevenue) }}</h3>
      </div>

      <!-- Admin Accounts -->
      <router-link
        to="/superadmin/users"
        class="bg-white p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow cursor-pointer"
      >
        <p class="text-lg font-semibold text-gray-700 mb-2">Admin Accounts</p>
        <h3 class="text-4xl font-bold text-gray-900">{{ stats.totalAdmins.toLocaleString() }}</h3>
      </router-link>

      <!-- Superadmin -->
      <router-link
        to="/superadmin/users"
        class="bg-white p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow cursor-pointer"
      >
        <p class="text-lg font-semibold text-gray-700 mb-2">Superadmin</p>
        <h3 class="text-4xl font-bold text-gray-900">{{ stats.totalSuperadmin.toLocaleString() }}</h3>
      </router-link>

      <!-- Tables -->
      <router-link
        to="/superadmin/tables"
        class="bg-white p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow cursor-pointer"
      >
        <p class="text-lg font-semibold text-gray-700 mb-2">Tables</p>
        <h3 class="text-4xl font-bold text-gray-900">{{ stats.totalTables.toLocaleString() }}</h3>
      </router-link>
    </div>

    <!-- Sales Reports Section -->
    <div class="bg-white rounded-xl shadow-sm mb-8">
      <!-- Header with Tabs -->
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h2 class="text-2xl font-bold text-gray-900">Sales Reports</h2>
          <div class="flex gap-2">
            <button
              @click="activeTab = 'daily'"
              :class="
                activeTab === 'daily'
                  ? 'bg-amber-700 text-white'
                  : 'bg-white text-gray-700 hover:bg-gray-50'
              "
              class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
            >
              Daily
            </button>
            <button
              @click="activeTab = 'weekly'"
              :class="
                activeTab === 'weekly'
                  ? 'bg-amber-700 text-white'
                  : 'bg-white text-gray-700 hover:bg-gray-50'
              "
              class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
            >
              Weekly
            </button>
            <button
              @click="activeTab = 'monthly'"
              :class="
                activeTab === 'monthly'
                  ? 'bg-amber-700 text-white'
                  : 'bg-white text-gray-700 hover:bg-gray-50'
              "
              class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
            >
              Monthly
            </button>
          </div>
        </div>
      </div>

      <!-- Chart Area -->
      <div class="p-6">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">{{ chartTitle }}</h3>
            <p class="text-sm text-gray-500">{{ chartSubtitle }}</p>
          </div>
          <div class="text-right">
            <p class="text-sm text-gray-500">Total Revenue</p>
            <h3 class="text-2xl font-bold text-gray-900">{{ formatCurrency(chartTotalRevenue) }}</h3>
          </div>
        </div>

        <!-- Chart Canvas -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6" style="height: 300px">
          <canvas ref="chartCanvas"></canvas>
        </div>

        <!-- Top Selling Products -->
        <div class="pt-4 border-t border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Selling Products Today</h3>
          
          <div class="overflow-hidden rounded-lg border border-gray-200">
            <table class="w-full">
              <thead class="bg-white border-b border-gray-200">
                <tr>
                  <th class="px-6 py-3 text-left text-base font-semibold text-gray-800">Product</th>
                  <th class="px-6 py-3 text-center text-base font-semibold text-gray-800">Units Sold</th>
                  <th class="px-6 py-3 text-center text-base font-semibold text-gray-800">Revenue</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(p, idx) in topProducts" :key="p.name" class="hover:bg-gray-50">
                  <td class="px-6 py-3 text-base text-gray-800">{{ p.name }}</td>
                  <td class="px-6 py-3 text-center text-base text-gray-800">{{ p.units_sold }}</td>
                  <td class="px-6 py-3 text-center text-base text-gray-800">{{ formatCurrency(p.revenue) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- User Management -->
    <div class="bg-white rounded-xl shadow-sm">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-2xl font-bold text-gray-900">User Management</h2>
      </div>

      <div class="overflow-hidden">
        <table class="w-full">
          <thead class="bg-white border-b border-gray-200">
            <tr>
              <th class="px-6 py-4 text-left text-base font-semibold text-gray-800">Name</th>
              <th class="px-6 py-4 text-center text-base font-semibold text-gray-800">Email</th>
              <th class="px-6 py-4 text-center text-base font-semibold text-gray-800">Role</th>
              <th class="px-6 py-4 text-center text-base font-semibold text-gray-800">Status</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="admin in recentAdmins.slice(0, 2)" :key="admin.email" class="hover:bg-gray-50">
              <td class="px-6 py-4 text-base text-gray-800">{{ admin.name }}</td>
              <td class="px-6 py-4 text-center text-base text-gray-800">{{ admin.email }}</td>
              <td class="px-6 py-4 text-center">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-blue-700">
                  Admin
                </span>
              </td>
              <td class="px-6 py-4 text-center">
                <span class="inline-flex items-center gap-2 text-base font-semibold" :class="admin.status === 'Active' ? 'text-green-600' : 'text-red-500'">
                  <span class="h-2 w-2 rounded-full" :class="admin.status === 'Active' ? 'bg-green-500' : 'bg-red-500'"></span>
                  {{ admin.status }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
