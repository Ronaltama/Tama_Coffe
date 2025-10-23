<script setup>
import { RouterLink, RouterView } from 'vue-router'

const props = defineProps({
  role: {
    type: String,
    required: true
  }
})

const handleLogout = () => {
  console.log('Logout initiated...')
  // implement real logout: remove token, call API, router.push('/login'), dll.
}
</script>

<template>
  <div class="admin-layout flex">
    <aside class="w-64 bg-white border-r p-4">
      <div class="mb-6 font-semibold text-lg">Tama Coffe</div>

      <nav class="space-y-2">
        <template v-if="props.role === 'superadmin'">
          <RouterLink to="/super-admin/dashboard" class="nav-link" active-class="sidebar-active">
            <span class="fa-fw">📊</span><span>Dashboard</span>
          </RouterLink>

          <RouterLink to="/super-admin/users" class="nav-link" active-class="sidebar-active">
            <span class="fa-fw">👥</span><span>Users</span>
          </RouterLink>

          <RouterLink to="/super-admin/products" class="nav-link" active-class="sidebar-active">
            <span class="fa-fw">📦</span><span>Products</span>
          </RouterLink>

          <RouterLink to="/super-admin/history" class="nav-link" active-class="sidebar-active">
            <span class="fa-fw">📜</span><span>Order History</span>
          </RouterLink>
        </template>

        <template v-else-if="props.role === 'admin'">
          <RouterLink to="/admin/dashboard" class="nav-link" active-class="sidebar-active">
            <span class="fa-fw">📊</span><span>Dashboard</span>
          </RouterLink>

          <RouterLink to="/admin/add-order" class="nav-link" active-class="sidebar-active">
            <span class="fa-fw">➕</span><span>Add Order</span>
          </RouterLink>

          <RouterLink to="/admin/history" class="nav-link" active-class="sidebar-active">
            <span class="fa-fw">📜</span><span>Order History</span>
          </RouterLink>
        </template>
      </nav>

      <div class="mt-100">
        <button @click="handleLogout" class="nav-link" style="width:100%;">🔒 Logout</button>
      </div>
    </aside>

    <main class="flex-1 p-6">
      <RouterView />
    </main>
  </div>
</template>

<style scoped>
.nav-link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.6rem 0.9rem;
  border-radius: 0.5rem;
  color: #374151;
  text-decoration: none;
}
.nav-link:hover { background-color: #FED7AA; }
.sidebar-active { background-color: #8B4513; color: white; }
.fa-fw { width: 1.25em; text-align: center; }
</style>