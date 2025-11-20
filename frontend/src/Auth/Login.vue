  <template>
    <div
      class="min-h-screen flex items-center justify-center bg-gradient-to-br from-amber-50 via-orange-50 to-yellow-50 p-4">
      <!-- Background Pattern -->
      <div class="absolute inset-0 opacity-5">
        <div class="absolute inset-0"
          style="background-image: radial-gradient(circle, #78350f 1px, transparent 1px); background-size: 20px 20px;">
        </div>
      </div>

      <!-- Login Card -->
      <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
        <!-- Decorative Header -->
        <div class="bg-gradient-to-br from-amber-700 to-amber-900 h-32 relative overflow-hidden">
          <div class="absolute inset-0 opacity-20">
            <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
              <defs>
                <pattern id="coffee-pattern" x="0" y="0" width="40" height="40" patternUnits="userSpaceOnUse">
                  <circle cx="20" cy="20" r="2" fill="white" opacity="0.3" />
                </pattern>
              </defs>
              <rect width="100%" height="100%" fill="url(#coffee-pattern)" />
            </svg>
          </div>
        </div>

        <!-- Logo & Title -->
        <div class="relative -mt-16 px-8">
          <div class="bg-white rounded-2xl shadow-xl p-6 mb-6">
            <div class="flex flex-col items-center">
              <!-- Logo -->
              <div
                class="w-24 h-24 flex items-center justify-center mb-4 rounded-xl hover:scale-105 transition-transform duration-300">
                <img :src="logo" alt="Logo" class="w-20 h-20 object-contain" />
              </div>


              <!-- Title -->
              <h2 class="text-3xl font-extrabold text-gray-800 mb-1">
                Selamat Datang
              </h2>
              <p class="text-gray-500 text-sm">Masuk ke akun Anda</p>
            </div>
          </div>

          <!-- Login Form -->
          <form @submit.prevent="loginUser" class="space-y-5 pb-8">
            <!-- Username Field -->
            <div>
              <label class="block mb-2 font-semibold text-gray-700 text-sm">
                <span class="flex items-center gap-2">
                  <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                  </svg>
                  Username
                </span>
              </label>
              <div class="relative">
                <input type="text" v-model="form.username"
                  class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 pr-10 focus:outline-none focus:border-amber-700 focus:ring-2 focus:ring-amber-200 transition-all duration-200"
                  placeholder="Masukkan username" required />
                <div class="absolute right-3 top-1/2 -translate-y-1/2">
                  <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                  </svg>
                </div>
              </div>
            </div>

            <!-- Password Field -->
            <div>
              <label class="block mb-2 font-semibold text-gray-700 text-sm">
                <span class="flex items-center gap-2">
                  <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                    </path>
                  </svg>
                  Password
                </span>
              </label>
              <div class="relative">
                <input :type="showPassword ? 'text' : 'password'" v-model="form.password"
                  class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 pr-10 focus:outline-none focus:border-amber-700 focus:ring-2 focus:ring-amber-200 transition-all duration-200"
                  placeholder="Masukkan password" required />
                <button type="button" @click="showPassword = !showPassword"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                  <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                    </path>
                  </svg>
                  <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21">
                    </path>
                  </svg>
                </button>
              </div>
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between text-sm">
              <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox"
                  class="w-4 h-4 rounded border-gray-300 text-amber-700 focus:ring-amber-700 focus:ring-2" />
                <span class="text-gray-600">Ingat saya</span>
              </label>
              <a href="#" class="text-amber-700 hover:text-amber-900 font-semibold transition-colors">
                Lupa password?
              </a>
            </div>

            <!-- Login Button -->
            <button type="submit"
              class="w-full bg-gradient-to-r from-amber-700 to-amber-900 hover:from-amber-800 hover:to-amber-950 text-white py-3.5 rounded-xl font-bold shadow-lg transform transition-all duration-200 ease-in-out hover:scale-[1.02] hover:shadow-xl flex items-center justify-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
              </svg>
              Masuk
            </button>
          </form>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 py-4 px-8 text-center border-t border-gray-100">
          <p class="text-sm text-gray-600">
            Belum punya akun?
            <a href="#" class="text-amber-700 hover:text-amber-900 font-semibold transition-colors">
              Daftar sekarang
            </a>
          </p>
        </div>
      </div>

      <!-- Decorative Coffee Beans -->
      <div class="absolute bottom-10 left-10 opacity-10 pointer-events-none hidden lg:block">
        <svg class="w-32 h-32 text-amber-900" viewBox="0 0 100 100" fill="currentColor">
          <ellipse cx="30" cy="50" rx="15" ry="25" transform="rotate(-20 30 50)" />
          <ellipse cx="70" cy="50" rx="15" ry="25" transform="rotate(20 70 50)" />
        </svg>
      </div>

      <div class="absolute top-10 right-10 opacity-10 pointer-events-none hidden lg:block">
        <svg class="w-40 h-40 text-amber-900" viewBox="0 0 100 100" fill="currentColor">
          <ellipse cx="40" cy="50" rx="18" ry="30" transform="rotate(-15 40 50)" />
          <ellipse cx="60" cy="50" rx="18" ry="30" transform="rotate(15 60 50)" />
        </svg>
      </div>
    </div>
  </template>

  <script setup>
  import { ref } from "vue";
  import axios from "axios";
  import { useRouter } from "vue-router";
  import Swal from "sweetalert2";
  import logo from '../assets/img/logo.png';

  const router = useRouter();

  const form = ref({
    username: "",
    password: "",
  });

  const showPassword = ref(false);

  const loginUser = async () => {
    try {
      const res = await axios.post("http://127.0.0.1:8000/api/login", form.value);
      const user = res.data.data;

      localStorage.setItem("user", JSON.stringify(user));
      localStorage.setItem("token", user.token);

      axios.defaults.headers.common["Authorization"] = `Bearer ${user.token}`;

      // Success Alert
      await Swal.fire({
        icon: "success",
        title: "Login Berhasil!",
        text: `Selamat datang, ${user.name}. Anda masuk sebagai ${user.role}.`,
        showConfirmButton: false,
        timer: 1800,
        background: '#fff',
        iconColor: '#92400e',
        customClass: {
          popup: 'rounded-2xl'
        }
      });

      if (user.role === "superadmin") router.push("/superadmin/dashboard");
      else if (user.role === "admin") router.push("/admin/dashboard");
    } catch (err) {
      // Error Alert
      const errorMessage =
        err.response?.data?.message || "Terjadi kesalahan server. Coba lagi.";

      Swal.fire({
        icon: "error",
        title: "Login Gagal!",
        text: errorMessage,
        confirmButtonColor: "#92400e",
        customClass: {
          popup: 'rounded-2xl'
        }
      });
    }
  };
  </script>

  <style scoped>
  /* Custom scrollbar for better aesthetics */
  ::-webkit-scrollbar {
    width: 8px;
  }

  ::-webkit-scrollbar-track {
    background: #f1f1f1;
  }

  ::-webkit-scrollbar-thumb {
    background: #92400e;
    border-radius: 4px;
  }

  ::-webkit-scrollbar-thumb:hover {
    background: #78350f;
  }
  </style>