<script setup>
import { ref } from "vue"
import axios from "axios"
import { useRouter } from "vue-router"
import Swal from "sweetalert2"

const router = useRouter()

const name = ref("")
const username = ref("")
const email = ref("")
const password = ref("")
const confirmPassword = ref("")

const loading = ref(false)
const showPassword = ref(false)
const showConfirmPassword = ref(false)

const handleSubmit = async () => {
  if (!name.value || !username.value || !email.value || !password.value || !confirmPassword.value) {
    Swal.fire({
      icon: "warning",
      title: "Incomplete Data",
      text: "Please fill all fields before submitting.",
    })
    return
  }

  if (password.value !== confirmPassword.value) {
    Swal.fire({
      icon: "error",
      title: "Password Mismatch",
      text: "Password and confirm password do not match.",
    })
    return
  }

  loading.value = true

  try {
    const token = localStorage.getItem('token')
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
    
    await axios.post("http://localhost:8000/api/users", {
      name: name.value,
      username: username.value,
      email: email.value,
      password: password.value,
      role_id: "RL002", // DEFAULT ADMIN
    })

    await Swal.fire({
      icon: "success",
      title: "User Created",
      text: "Admin user has been added successfully!",
      confirmButtonColor: "#854D0E",
    })

    router.push("/superadmin/users")
  } catch (err) {
    console.log(err)

    Swal.fire({
      icon: "error",
      title: "Failed",
      text: err.response?.data?.message || "Unable to create user.",
    })

  } finally {
    loading.value = false
  }
}

const handleCancel = () => {
  router.push("/superadmin/users")
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
          title="Back to Users"
        >
          <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        <div>
          <h1 class="text-color-azure-11 text-4xl font-bold font-inter leading-tight">Add Admin</h1>
          <p class="text-color-grey-46 text-sm mt-1">Create a new admin user account</p>
        </div>
      </div>

      <!-- Form Card -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <form @submit.prevent="handleSubmit" class="p-8 space-y-6">
          
          <!-- Full Name -->
          <div>
            <label class="text-color-azure-11 text-sm font-medium font-inter mb-2 block">
              Full Name <span class="text-red-500">*</span>
            </label>
            <input
              v-model="name"
              type="text"
              placeholder="e.g., Iqbal Mio Mirza"
              class="w-full px-4 py-2.5 bg-white rounded-xl border border-gray-200 text-sm font-normal font-inter text-gray-700 placeholder-gray-400 outline-none focus:border-color-brown focus:ring-2 focus:ring-orange-100 transition-all duration-200"
            />
          </div>

          <!-- Username -->
          <div>
            <label class="text-color-azure-11 text-sm font-medium font-inter mb-2 block">
              Username <span class="text-red-500">*</span>
            </label>
            <input
              v-model="username"
              type="text"
              placeholder="e.g., Iqbal Joki Balap Karung"
              class="w-full px-4 py-2.5 bg-white rounded-xl border border-gray-200 text-sm font-normal font-inter text-gray-700 placeholder-gray-400 outline-none focus:border-color-brown focus:ring-2 focus:ring-orange-100 transition-all duration-200"
            />
          </div>

          <!-- Email Address -->
          <div>
            <label class="text-color-azure-11 text-sm font-medium font-inter mb-2 block">
              Email Address <span class="text-red-500">*</span>
            </label>
            <input
              v-model="email"
              type="email"
              placeholder="e.g., iqbalboyolali@yahoo.com"
              class="w-full px-4 py-2.5 bg-white rounded-xl border border-gray-200 text-sm font-normal font-inter text-gray-700 placeholder-gray-400 outline-none focus:border-color-brown focus:ring-2 focus:ring-orange-100 transition-all duration-200"
            />
          </div>

          <!-- Password & Confirm Password -->
          <div class="grid grid-cols-2 gap-4">
            <!-- Password -->
            <div>
              <label class="text-color-azure-11 text-sm font-medium font-inter mb-2 block">
                Password <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <input
                  v-model="password"
                  :type="showPassword ? 'text' : 'password'"
                  placeholder="••••••"
                  class="w-full px-4 py-2.5 pr-10 bg-white rounded-xl border border-gray-200 text-sm font-normal font-inter text-gray-700 placeholder-gray-400 outline-none focus:border-color-brown focus:ring-2 focus:ring-orange-100 transition-all duration-200"
                />
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                >
                  <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Confirm Password -->
            <div>
              <label class="text-color-azure-11 text-sm font-medium font-inter mb-2 block">
                Confirm Password <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <input
                  v-model="confirmPassword"
                  :type="showConfirmPassword ? 'text' : 'password'"
                  placeholder="••••••"
                  class="w-full px-4 py-2.5 pr-10 bg-white rounded-xl border border-gray-200 text-sm font-normal font-inter text-gray-700 placeholder-gray-400 outline-none focus:border-color-brown focus:ring-2 focus:ring-orange-100 transition-all duration-200"
                />
                <button
                  type="button"
                  @click="showConfirmPassword = !showConfirmPassword"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                >
                  <svg v-if="!showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                  </svg>
                </button>
              </div>
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
              {{ loading ? "Saving..." : "Save Admin" }}
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
