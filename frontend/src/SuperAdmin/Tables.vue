<template>
    <div class="p-6 bg-gray-50 min-h-screen">
        <!-- Header Section -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Tables Management</h1>
                <p class="text-gray-600 mt-1">Manage your restaurant tables</p>
            </div>
            <button @click="goToAddTable"
                class="bg-amber-700 hover:bg-amber-800 text-white px-6 py-3 rounded-lg flex items-center gap-2 transition-all shadow-md hover:shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                        clip-rule="evenodd" />
                </svg>
                Add Tables
            </button>
        </div>

        <!-- Search Section -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
            <div class="relative">
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input v-model="searchQuery" type="text" placeholder="Search Tables"
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none" />
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="bg-white rounded-lg shadow-sm p-12 text-center">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-amber-700"></div>
            <p class="text-gray-600 mt-4">Loading tables...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="filteredTables.length === 0" class="bg-white rounded-lg shadow-sm p-12 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 mb-4" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">No Tables Found</h3>
            <p class="text-gray-500">
                {{ searchQuery ? "Try different search keywords" : "Start by adding your first table" }}
            </p>
        </div>

        <!-- Tables List -->
        <div v-else class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">
                                Tables Number
                            </th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">
                                Capacity
                            </th>

                            <!-- 🔥 TYPE COLUMN ADDED -->
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">
                                Type
                            </th>

                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">
                                Status
                            </th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">
                                QR-Link
                            </th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="table in filteredTables" :key="table.id" class="hover:bg-gray-50 transition-colors">
                            <!-- Table Number -->
                            <td class="px-6 py-4 text-gray-800 font-medium">
                                {{ table.table_number }}
                            </td>

                            <!-- Capacity -->
                            <td class="px-6 py-4 text-gray-600">
                                {{ table.capacity }} People
                            </td>

                            <!-- 🔥 TYPE VALUE ADDED -->
                            <td class="px-6 py-4 text-gray-700 font-medium">
                                {{ table.type || "—" }}
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4">
                                <span :class="getStatusClass(table.status)"
                                    class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-medium">
                                    <span class="w-2 h-2 rounded-full" :class="getStatusDotClass(table.status)"></span>
                                    {{ formatStatus(table.status) }}
                                </span>
                            </td>

                            <!-- QR Code -->
                            <td class="px-6 py-4">
                                <button @click="showQRCode(table)"
                                    class="text-gray-700 hover:text-amber-700 transition-colors" title="View QR Code">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                    </svg>
                                </button>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-3">
                                    <button @click="editTable(table.id)"
                                        class="text-blue-600 hover:text-blue-800 transition-colors" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path
                                                d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                        </svg>
                                    </button>

                                    <button @click="deleteTable(table.id, table.table_number)"
                                        class="text-red-600 hover:text-red-800 transition-colors" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </td>

                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</template>

<script>
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import Swal from "sweetalert2";

export default {
    name: "Tables",
    setup() {
        const router = useRouter();
        const tables = ref([]);
        const loading = ref(true);
        const searchQuery = ref("");

        // Fetch tables data
        const fetchTables = async () => {
            loading.value = true;
            try {
                const response = await axios.get("http://localhost:8000/api/tables");
                if (response.data.success) {
                    tables.value = response.data.data;
                }
            } catch (error) {
                console.error("Error fetching tables:", error);
                Swal.fire({
                    icon: "error",
                    title: "Error!",
                    text: "Failed to load tables data.",
                    confirmButtonColor: "#92400e",
                });
            } finally {
                loading.value = false;
            }
        };

        // Filter tables based on search query
        const filteredTables = computed(() => {
            if (!searchQuery.value) return tables.value;

            return tables.value.filter((table) => {
                const query = searchQuery.value.toLowerCase();
                return (
                    table.table_number?.toLowerCase().includes(query) ||
                    table.capacity?.toString().includes(query) ||
                    table.status?.toLowerCase().includes(query)
                );
            });
        });

        // Delete table
        const deleteTable = async (id, tableName) => {
            const result = await Swal.fire({
                title: "Are you sure?",
                html: `You are about to delete <strong>${tableName}</strong>.<br>This action cannot be undone!`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#dc2626",
                cancelButtonColor: "#6b7280",
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "Cancel",
                reverseButtons: true,
            });

            if (result.isConfirmed) {
                try {
                    const response = await axios.delete(`http://localhost:8000/api/tables/${id}`);

                    if (response.data.success) {
                        // Remove from local array
                        tables.value = tables.value.filter((table) => table.id !== id);

                        // Success notification
                        Swal.fire({
                            icon: "success",
                            title: "Deleted!",
                            text: `${tableName} has been successfully deleted.`,
                            confirmButtonColor: "#92400e",
                            timer: 2000,
                        });
                    }
                } catch (error) {
                    console.error("Error deleting table:", error);
                    Swal.fire({
                        icon: "error",
                        title: "Delete Failed!",
                        text: error.response?.data?.message || "Failed to delete table.",
                        confirmButtonColor: "#92400e",
                    });
                }
            }
        };

        // Show QR Code
        const showQRCode = (table) => {
            if (table.qr_code_url) {
                Swal.fire({
                    title: `QR Code - ${table.table_number}`,
                    imageUrl: table.qr_code_url,
                    imageWidth: 300,
                    imageHeight: 300,
                    imageAlt: "QR Code",
                    confirmButtonColor: "#92400e",
                    showCloseButton: true,
                    confirmButtonText: "Close",
                });
            } else {
                Swal.fire({
                    icon: "info",
                    title: "No QR Code",
                    text: "QR Code not available for this table.",
                    confirmButtonColor: "#92400e",
                });
            }
        };

        // Navigate to add table
        const goToAddTable = () => {
            router.push("/superadmin/tables/add");
        };

        // Navigate to Edit Table Page
        const editTable = (id) => {
            router.push(`/superadmin/tables/edit/${id}`);
        };

        // Status helpers
        const getStatusClass = (status) => {
            const classes = {
                available: "bg-green-100 text-green-800",
                occupied: "bg-red-100 text-red-800",
                reserved: "bg-yellow-100 text-yellow-800",
            };
            return classes[status?.toLowerCase()] || "bg-gray-100 text-gray-800";
        };

        const getStatusDotClass = (status) => {
            const classes = {
                available: "bg-green-500",
                occupied: "bg-red-500",
                reserved: "bg-yellow-500",
            };
            return classes[status?.toLowerCase()] || "bg-gray-500";
        };

        const formatStatus = (status) => {
            return status?.charAt(0).toUpperCase() + status?.slice(1).toLowerCase();
        };

        onMounted(() => {
            fetchTables();
        });

        return {
            tables,
            loading,
            searchQuery,
            filteredTables,
            deleteTable,
            showQRCode,
            goToAddTable,
            editTable,
            getStatusClass,
            getStatusDotClass,
            formatStatus,
        };
    },
};
</script>

<style scoped>
/* Optional: Add custom animations */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

tbody tr {
    animation: fadeIn 0.3s ease-in-out;
}
</style>