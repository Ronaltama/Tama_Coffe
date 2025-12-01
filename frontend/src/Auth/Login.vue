<template>
  <div class="min-h-screen flex items-center justify-center bg-orange-50 p-4">
    <!-- Login Card -->
    <div
      class="bg-white rounded-lg shadow-lg w-full max-w-[598px] px-8 pt-8 pb-10"
    >
      <!-- Logo -->
      <div class="flex justify-center mb-8">
        <img
          :src="logo"
          alt="Tama Coffee Logo"
          class="w-40 h-36 object-contain"
        />
      </div>

      <!-- Title -->
      <h2
        class="text-center text-[#6B6B6B] text-3xl font-semibold font-poppins leading-7 mb-9"
      >
        Welcome Back
      </h2>

      <!-- Login Form -->
      <form @submit.prevent="loginUser" class="space-y-8">
        <!-- Username Field -->
        <div>
          <label
            class="block text-[#6B6B6B] text-xl font-medium font-poppins leading-5 mb-3"
          >
            Username
          </label>
          <div class="relative h-14 bg-[#C9C8C6] rounded-lg overflow-hidden">
            <span
              class="material-icons absolute left-7 top-1/2 -translate-y-1/2 text-[#6B6B6B] text-2xl"
            >
              person
            </span>
            <input
              type="text"
              v-model="form.username"
              placeholder="Enter your username"
              class="w-full h-full pl-16 pr-4 bg-transparent border-0 text-[#6B6B6B] text-base font-poppins placeholder-[#6B6B6B] placeholder-opacity-60 focus:outline-none focus:bg-[#D9D8D6]"
              required
            />
          </div>
        </div>

        <!-- Password Field -->
        <div>
          <label
            class="block text-[#6B6B6B] text-xl font-medium font-poppins leading-5 mb-3"
          >
            Password
          </label>
          <div class="relative h-14 bg-[#C9C8C6] rounded-lg overflow-hidden">
            <span
              class="material-icons absolute left-7 top-1/2 -translate-y-1/2 text-[#6B6B6B] text-2xl"
            >
              lock
            </span>
            <input
              :type="showPassword ? 'text' : 'password'"
              v-model="form.password"
              placeholder="Enter your password"
              class="w-full h-full pl-16 pr-12 bg-transparent border-0 text-[#6B6B6B] text-base font-poppins placeholder-[#6B6B6B] placeholder-opacity-60 focus:outline-none focus:bg-[#D9D8D6]"
              required
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute right-4 top-1/2 -translate-y-1/2 w-7 h-7"
            >
              <img
                v-if="!showPassword"
                :src="eyeIcon"
                alt="Show password"
                class="w-full h-full object-contain"
              />
              <img
                v-else
                :src="eyeIcon"
                alt="Hide password"
                class="w-full h-full object-contain opacity-60"
              />
            </button>
          </div>
        </div>

        <!-- Remember Me -->
        <div class="flex items-center gap-3">
          <input
            type="checkbox"
            id="remember"
            class="w-6 h-6 rounded border-2 border-[#6B6B6B] text-[#854D0E] focus:ring-[#854D0E]"
          />
          <label
            for="remember"
            class="text-[#6B6B6B] text-xl font-medium font-poppins leading-5 cursor-pointer"
          >
            Remember Me
          </label>
        </div>

        <!-- Login Button -->
        <button
          type="submit"
          class="w-full h-16 bg-[#854D0E] hover:bg-[#6B3D0A] rounded-lg font-bold text-white text-xl font-poppins leading-6 transition-colors duration-200 flex items-center justify-center gap-3 mt-8"
        >
          <img :src="loginIcon" alt="Login" class="w-7 h-7 object-contain" />
          Login
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";
import Swal from "sweetalert2";
import logo from "../assets/img/logo.png";
import eyeIcon from "../assets/img/eye-icon.png";
import loginIcon from "../assets/img/login-icon.png";

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
      background: "#fff",
      iconColor: "#854D0E",
      customClass: {
        popup: "rounded-2xl",
      },
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
      confirmButtonColor: "#854D0E",
      customClass: {
        popup: "rounded-2xl",
      },
    });
  }
};
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap");
@import url("https://fonts.googleapis.com/icon?family=Material+Icons");

.font-poppins {
  font-family: "Poppins", sans-serif;
}

input::placeholder {
  font-family: "Poppins", sans-serif;
}
</style>
