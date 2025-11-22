<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Table;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Get table info by ID (untuk scan QR atau simulasi)
     * Endpoint: GET /api/guest/table-info/{tableId}
     */
    public function getTableInfo($tableId)
    {
        try {
            $table = Table::find($tableId);

            if (!$table) {
                return response()->json([
                    'success' => false,
                    'message' => 'Meja tidak ditemukan'
                ], 404);
            }

            // Cek status meja
            if ($table->status === 'maintenance') {
                return response()->json([
                    'success' => false,
                    'message' => 'Meja sedang dalam perbaikan',
                    'data' => [
                        'table_number' => $table->table_number,
                        'status' => $table->status
                    ]
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data meja berhasil diambil',
                'data' => [
                    'id' => $table->id,
                    'table_number' => $table->table_number,
                    'capacity' => $table->capacity,
                    'type' => $table->type,
                    'status' => $table->status,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data meja',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update status meja menjadi occupied ketika user mulai order
     * Endpoint: POST /api/guest/table/{tableId}/occupy
     */
    public function occupyTable($tableId)
    {
        try {
            $table = Table::find($tableId);

            if (!$table) {
                return response()->json([
                    'success' => false,
                    'message' => 'Meja tidak ditemukan'
                ], 404);
            }

            // Update status ke occupied
            $table->update(['status' => 'occupied']);

            return response()->json([
                'success' => true,
                'message' => 'Status meja berhasil diupdate',
                'data' => [
                    'id' => $table->id,
                    'table_number' => $table->table_number,
                    'status' => $table->status
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate status meja',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
