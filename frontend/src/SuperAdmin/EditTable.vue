<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";
import Swal from "sweetalert2";

const route = useRoute();
const router = useRouter();

const id = route.params.id;

// === STATE ===
const tableNumber = ref("");
const capacity = ref("");
const type = ref("");
const status = ref("");
const loading = ref(false);
const loadingData = ref(true);

// API BASE
const API_BASE = "http://localhost:8000/api";

// === LOAD DATA ===
const loadTable = async () => {
    try {
        const response = await axios.get(`${API_BASE}/tables/${id}`);

        const t = response.data.data;
        tableNumber.value = t.table_number;
        capacity.value = t.capacity;
        type.value = t.type;
        status.value = t.status;

    } catch (err) {
        Swal.fire({
            icon: "error",
            title: "Failed to Load",
            text: "Data meja tidak ditemukan!",
        });
        router.push("/superadmin/tables");
    } finally {
        loadingData.value = false;
    }
};

// === UPDATE TABLE ===
const handleUpdate = async () => {
    if (!tableNumber.value.trim()) {
        Swal.fire("Error", "Nomor meja tidak boleh kosong", "error");
        return;
    }
    if (!capacity.value || capacity.value <= 0) {
        Swal.fire("Error", "Kapasitas harus lebih dari 0", "error");
        return;
    }
    if (!type.value) {
        Swal.fire("Error", "Type harus dipilih", "error");
        return;
    }

    loading.value = true;

    try {
        await axios.put(`${API_BASE}/tables/${id}`, {
            table_number: tableNumber.value,
            capacity: capacity.value,
            type: type.value,
            status: status.value,
        });

        Swal.fire({
            icon: "success",
            title: "Updated",
            text: "Meja berhasil diperbarui!",
            timer: 1500,
            showConfirmButton: false,
        });

        router.push("/superadmin/tables");

    } catch (err) {
        Swal.fire({
            icon: "error",
            title: "Oops...",
            text: err.response?.data?.message || "Gagal mengupdate meja",
        });
    } finally {
        loading.value = false;
    }
};

const cancelEdit = () => router.push("/superadmin/tables");

// Run Load
onMounted(() => loadTable());
</script>

<template>
    <div class="p-6 bg-gray-50 min-h-screen">

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Edit Table</h1>
            <p class="text-gray-600 mt-1">Update table data below</p>
        </div>

        <!-- LOADING -->
        <div v-if="loadingData" class="text-gray-600">Loading data...</div>

        <!-- FORM -->
        <form v-else @submit.prevent="handleUpdate" class="bg-white rounded-xl shadow-sm max-w-3xl">
            <div class="p-6 space-y-6">

                <!-- Table Number -->
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-2 block">
                        Table Number/Name <span class="text-red-500">*</span>
                    </label>
                    <input v-model="tableNumber" type="text" class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 
                   focus:ring-amber-500 outline-none" placeholder="Table 1, A-03" />
                </div>

                <!-- Row of Fields -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <!-- Capacity -->
                    <div>
                        <label class="text-sm font-medium text-gray-700 mb-2 block">
                            Capacity <span class="text-red-500">*</span>
                        </label>
                        <input v-model="capacity" type="number" min="1" class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 
                     focus:ring-amber-500 outline-none" />
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="text-sm font-medium text-gray-700 mb-2 block">
                            Status
                        </label>
                        <select v-model="status" class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 
                     focus:ring-amber-500 outline-none bg-white">
                            <option value="available">Available</option>
                            <option value="occupied">Occupied</option>
                            <option value="reserved">Reserved</option>
                        </select>
                    </div>

                    <!-- Type -->
                    <div>
                        <label class="text-sm font-medium text-gray-700 mb-2 block">
                            Table Type <span class="text-red-500">*</span>
                        </label>
                        <select v-model="type" class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 
           focus:ring-amber-500 outline-none bg-white">
                            <option disabled value="">-- Select Type --</option>
                            <option value="Indoor">Indoor</option>
                            <option value="Outdoor">Outdoor</option>
                            <option value="VIP">VIP</option>
                        </select>
                    </div>


                </div>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="px-6 py-4 bg-gray-50 border-t rounded-b-xl flex justify-end gap-3">

                <button type="button" @click="cancelEdit" class="px-5 py-2.5 border rounded-lg hover:bg-gray-100">
                    Cancel
                </button>

                <button type="submit" :disabled="loading" class="px-5 py-2.5 bg-amber-700 text-white rounded-lg hover:bg-amber-800 
                 disabled:opacity-50 flex items-center gap-2">
                    <svg v-if="loading" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.3 0 0 5.3 0 12h4z"></path>
                    </svg>
                    {{ loading ? "Updating..." : "Save Changes" }}
                </button>

            </div>
        </form>
    </div>
</template>
