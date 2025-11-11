<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white p-8 rounded-2xl shadow-md w-96">
      <h2 class="text-2xl font-bold text-center mb-6">Login</h2>

      <form @submit.prevent="loginUser">
        <div class="mb-4">
          <label class="block mb-1 font-medium">Username</label>
          <input
            type="text"
            v-model="form.username"
            class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Masukkan username"
          />
        </div>

        <div class="mb-4">
          <label class="block mb-1 font-medium">Password</label>
          <input
            type="password"
            v-model="form.password"
            class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Masukkan password"
          />
        </div>

        <button
          type="submit"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-semibold"
        >
          Login
        </button>

        <p v-if="error" class="text-red-600 mt-3 text-center">{{ error }}</p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";

const router = useRouter();

const form = ref({
  username: "",
  password: "",
});

const error = ref("");

const loginUser = async () => {
  error.value = "";

  try {
    const res = await axios.post("http://127.0.0.1:8000/api/login", form.value);
    const user = res.data.data;

    // Simpan ke localStorage biar bisa dipakai di dashboard
    localStorage.setItem("user", JSON.stringify(user));

    if (user.role === "superadmin") {
      router.push("/superadmin/dashboard");
    } else if (user.role === "admin") {
      router.push("/admin/dashboard");
    } else {
      error.value = "Role tidak dikenali";
    }
  } catch (err) {
    error.value = err.response?.data?.message || "Terjadi kesalahan server";
  }
};
</script>

<style scoped>
body {
  font-family: "Inter", sans-serif;
}
</style>
    