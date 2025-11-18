<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white p-8 rounded-2xl shadow-xl w-96">
      <h2 class="text-3xl font-extrabold text-center mb-6 text-blue-600">
        Masuk Aplikasi
      </h2>
  <div class="min-h-screen flex items-center justify-center bg-[#FFF3EB]">
    <div
      class="bg-white px-8 py-10 rounded-2xl shadow-lg w-full max-w-md text-center"
    >
      <!-- Logo -->
      <img
        src="@/assets/img/logo.png"
        alt="Tama Coffee"
        class="w-24 mx-auto mb-6"
      />

      <!-- Judul -->
      <h2 class="text-2xl font-bold text-gray-800 mb-2 font-poppins">
        Welcome Back
      </h2>
      <p class="text-gray-500 text-sm mb-6">
        Masuk ke akun kamu untuk melanjutkan
      </p>

      <form @submit.prevent="loginUser">
        <div class="mb-5">
          <label class="block mb-1 font-medium text-gray-700">Username</label>
          <input
            type="text"
            v-model="form.username"
            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-150"
            placeholder="Masukkan username"
            required
          />
      <!-- Form -->
      <form @submit.prevent="loginUser" class="text-left">
        <!-- Username -->
        <div class="mb-4">
          <label class="block mb-1 font-medium text-gray-700">Username</label>
          <div class="relative">
            <i
              class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
            ></i>
            <input
              type="text"
              v-model="form.username"
              class="w-full border rounded-lg pl-10 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-brown-500 bg-gray-100"
              placeholder="Masukkan username"
            />
          </div>
        </div>

        <div class="mb-6">
          <label class="block mb-1 font-medium text-gray-700">Password</label>
          <input
            type="password"
            v-model="form.password"
            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-150"
            placeholder="Masukkan password"
            required
          />
        <!-- Password -->
        <div class="mb-4">
          <label class="block mb-1 font-medium text-gray-700">Password</label>
          <div class="relative">
            <i
              class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
            ></i>
            <input
              :type="showPassword ? 'text' : 'password'"
              v-model="form.password"
              class="w-full border rounded-lg pl-10 pr-10 py-2 focus:outline-none focus:ring-2 focus:ring-brown-500 bg-gray-100"
              placeholder="Masukkan password"
            />
            <i
              :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 cursor-pointer"
              @click="showPassword = !showPassword"
            ></i>
          </div>
        </div>

        <!-- Tombol login -->
        <button
          type="submit"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg font-bold shadow-lg transform transition duration-150 ease-in-out hover:scale-[1.01]"
          class="w-full bg-[#7B3F00] hover:bg-[#5A2D00] text-white py-2 rounded-lg font-semibold transition duration-200"
        >
          Login
        </button>

        <!-- Error -->
        <p v-if="error" class="text-red-600 mt-3 text-center text-sm">
          {{ error }}
        </p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";
import Swal from "sweetalert2"; // 💡 Import SweetAlert2

const router = useRouter();
const showPassword = ref(false);

const form = ref({
  username: "",
  password: "",
});

const loginUser = async () => {
  try {
    const res = await axios.post("http://127.0.0.1:8000/api/login", form.value);
    const user = res.data.data;

    // Simpan token & user ke localStorage
    localStorage.setItem("user", JSON.stringify(user));
    localStorage.setItem("token", user.token);
    axios.defaults.headers.common["Authorization"] = `Bearer ${user.token}`;

    // 🟢 SweetAlert2 for Success
    await Swal.fire({
      icon: "success",
      title: "Login Berhasil!",
      text: `Selamat datang, ${user.name}. Anda masuk sebagai ${user.role}.`,
      showConfirmButton: false,
      timer: 1800,
    });

    // Redirect sesuai role
    if (user.role === "superadmin") router.push("/superadmin/dashboard");
    else if (user.role === "admin") router.push("/admin/dashboard");
    else router.push("/");
  } catch (err) {
    // 🔴 SweetAlert2 for Failure
    const errorMessage =
      err.response?.data?.message || "Terjadi kesalahan server. Coba lagi.";

    Swal.fire({
      icon: "error",
      title: "Login Gagal!",
      text: errorMessage,
      confirmButtonColor: "#d33",
    });
  }
};
</script>

<style scoped>
/* No changes needed here, just ensuring the style tag is present */
@import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap");

.font-poppins {
  font-family: "Poppins", sans-serif;
}

.bg-brown-500 {
  --tw-bg-opacity: 1;
  background-color: rgb(123 63 0 / var(--tw-bg-opacity));
}

.focus\:ring-brown-500:focus {
  --tw-ring-color: rgb(123 63 0 / 0.5);
}
</style>
