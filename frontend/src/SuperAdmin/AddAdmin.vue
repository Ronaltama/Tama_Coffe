<script setup>
import { ref } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";
import Swal from "sweetalert2";

const router = useRouter();

const name = ref("");
const username = ref("");
const email = ref("");
const password = ref("");

const loading = ref(false);

const handleSubmit = async () => {
  if (!name.value || !username.value || !email.value || !password.value) {
    Swal.fire({
      icon: "warning",
      title: "Incomplete Data",
      text: "Please fill all fields before submitting.",
    });
    return;
  }

  loading.value = true;

  try {
    await axios.post("http://localhost:8000/api/users", {
      name: name.value,
      username: username.value,
      email: email.value,
      password: password.value,
      role_id: "RL002", // ← DEFAULT ADMIN
    });

    await Swal.fire({
      icon: "success",
      title: "User Created",
      text: "Admin user has been added successfully!",
      confirmButtonColor: "#B76E3C",
    });

    router.push("/superadmin/users");
  } catch (err) {
    console.log(err);

    Swal.fire({
      icon: "error",
      title: "Failed",
      text: err.response?.data?.message || "Unable to create user.",
    });

  } finally {
    loading.value = false;
  }
};

const handleCancel = () => {
  Swal.fire({
    title: "Cancel?",
    text: "Are you sure you want to cancel?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#B76E3C",
    cancelButtonColor: "#888",
    confirmButtonText: "Yes, cancel",
  }).then((result) => {
    if (result.isConfirmed) {
      router.push("/superadmin/users");
    }
  });
};
</script>

<template>
  <div class="p-8 bg-[#f8f8f8] min-h-screen">

    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Add New User</h1>
      <p class="text-gray-500">Fill in the form below to add a new admin user</p>
    </div>

    <form
      class="bg-white shadow-md rounded-2xl border border-gray-200 max-w-3xl"
      @submit.prevent="handleSubmit"
    >
      <div class="p-8 space-y-6">

        <div>
          <label class="text-gray-700 font-medium mb-2 block">
            Full Name <span class="text-red-500">*</span>
          </label>
          <input
            v-model="name"
            type="text"
            placeholder="e.g., John Doe"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-amber-500"
          />
        </div>

        <div>
          <label class="text-gray-700 font-medium mb-2 block">
            Username <span class="text-red-500">*</span>
          </label>
          <input
            v-model="username"
            type="text"
            placeholder="e.g., johndoe123"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-amber-500"
          />
        </div>

        <div>
          <label class="text-gray-700 font-medium mb-2 block">
            Email <span class="text-red-500">*</span>
          </label>
          <input
            v-model="email"
            type="email"
            placeholder="e.g., john@gmail.com"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-amber-500"
          />
        </div>

        <div>
          <label class="text-gray-700 font-medium mb-2 block">
            Password <span class="text-red-500">*</span>
          </label>
          <input
            v-model="password"
            type="password"
            placeholder="Enter password"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-amber-500"
          />
        </div>
      </div>

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
          {{ loading ? "Saving..." : "Save User" }}
        </button>
      </div>
    </form>
  </div>
</template>
