<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white p-8 rounded-2xl shadow-xl w-96">
      <h2 class="text-3xl font-extrabold text-center mb-6 text-blue-600">
        Masuk Aplikasi
      </h2>

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
        </div>

        <!-- Tombol login -->
        <button
          type="submit"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg font-bold shadow-lg transform transition duration-150 ease-in-out hover:scale-[1.01]"
        >
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
</style>
