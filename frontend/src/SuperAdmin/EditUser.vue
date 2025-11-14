<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import Swal from "sweetalert2";
import axios from "axios";

const route = useRoute();
const router = useRouter();

const userId = route.params.id;

const name = ref("");
const username = ref("");
const email = ref("");
const password = ref("");

const loading = ref(false);
const loadingData = ref(true);

// Fetch data user saat halaman dibuka
const fetchUser = async () => {
  try {
    const res = await axios.get(`http://localhost:8000/api/users/${userId}`);
    const user = res.data;

    name.value = user.name;
    username.value = user.username;
    email.value = user.email;
  } catch (err) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "Failed to load user data",
    });
    router.push("/superadmin/users");
  } finally {
    loadingData.value = false;
  }
};

onMounted(() => {
  fetchUser();
});

const handleSubmit = async () => {
  if (!name.value || !username.value || !email.value) {
    Swal.fire({
      icon: "warning",
      title: "Incomplete Data",
      text: "Name, username, and email must be filled!",
    });
    return;
  }

  loading.value = true;

  try {
    await axios.put(`http://localhost:8000/api/users/${userId}`, {
      name: name.value,
      username: username.value,
      email: email.value,
      password: password.value || null, // opsional
    });

    await Swal.fire({
      icon: "success",
      title: "User Updated",
      text: "User information successfully updated!",
      confirmButtonColor: "#B76E3C",
    });

    router.push("/superadmin/users");
  } catch (err) {
    Swal.fire({
      icon: "error",
      title: "Update Failed",
      text: err.response?.data?.message || "Something went wrong",
    });
  } finally {
    loading.value = false;
  }
};

const handleCancel = () => {
  Swal.fire({
    title: "Cancel?",
    text: "Unsaved changes will be lost",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#B76E3C",
    cancelButtonColor: "#888",
    confirmButtonText: "Yes, cancel",
  }).then((res) => {
    if (res.isConfirmed) router.push("/superadmin/users");
  });
};
</script>

<template>
  <div class="p-8 bg-[#f8f8f8] min-h-screen">

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Edit User</h1>
      <p class="text-gray-500 mt-1">Update user information</p>
    </div>

    <!-- Loading State -->
    <div v-if="loadingData" class="text-center py-20 text-gray-500">
      Loading user data...
    </div>

    <!-- Form -->
    <form
      v-else
      @submit.prevent="handleSubmit"
      class="bg-white shadow-md rounded-2xl border border-gray-200 max-w-3xl"
    >
      <div class="p-8 space-y-6">

        <!-- Full Name -->
        <div>
          <label class="text-gray-700 font-medium mb-2 block">
            Full Name <span class="text-red-500">*</span>
          </label>
          <input
            v-model="name"
            type="text"
            placeholder="Enter full name"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-amber-500"
          />
        </div>

        <!-- Username -->
        <div>
          <label class="text-gray-700 font-medium mb-2 block">
            Username <span class="text-red-500">*</span>
          </label>
          <input
            v-model="username"
            type="text"
            placeholder="Enter username"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-amber-500"
          />
        </div>

        <!-- Email -->
        <div>
          <label class="text-gray-700 font-medium mb-2 block">
            Email <span class="text-red-500">*</span>
          </label>
          <input
            v-model="email"
            type="email"
            placeholder="Enter email"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-amber-500"
          />
        </div>

        <!-- Password (optional) -->
        <div>
          <label class="text-gray-700 font-medium mb-2 block">
            Password (leave empty to keep existing)
          </label>
          <input
            v-model="password"
            type="password"
            placeholder="New password (optional)"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-amber-500"
          />
        </div>

      </div>

      <!-- Footer -->
      <div class="bg-gray-50 px-8 py-4 rounded-b-2xl border-t flex justify-end gap-4">
        <button
          type="button"
          @click="handleCancel"
          class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100"
        >
          Cancel
        </button>

        <button
          type="submit"
          :disabled="loading"
          class="px-6 py-2 bg-[#B76E3C] text-white rounded-lg hover:bg-[#9c592e] font-medium disabled:opacity-50"
        >
          {{ loading ? "Saving..." : "Update User" }}
        </button>
      </div>
    </form>
  </div>
</template>
