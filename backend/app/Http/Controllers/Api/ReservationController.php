<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    /**
     * Get all reservations (Admin)
     */
    public function index()
    {
        try {
            $reservations = Reservation::with(['order', 'order.items', 'order.items.product'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $reservations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data reservasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update reservation status (Admin)
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:confirmed,rejected,completed,cancelled'
            ]);

            $reservation = Reservation::findOrFail($id);
            $reservation->status = $request->status;
            $reservation->save();

            // If confirmed, ensure table is marked as reserved/occupied if needed
            // For now, we just update the status.

            return response()->json([
                'success' => true,
                'message' => 'Status reservasi berhasil diperbarui',
                'data' => $reservation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status reservasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check table availability
     */
    public function checkAvailability(Request $request)
    {
        try {
            $request->validate([
                'date' => 'required|date',
                'time' => 'required'
            ]);

            $date = $request->date;
            $time = $request->time;

            // Get all tables
            $tables = Table::all();

            // Get reserved tables for that date and time slot (assuming 2 hour slot)
            // This is a simplified check. In a real app, you'd check overlapping time ranges.
            $reservedTableIds = Reservation::where('date', $date)
                ->where('status', '!=', 'cancelled')
                ->where('status', '!=', 'rejected')
                ->pluck('table_id')
                ->toArray();

            // Filter available tables
            $availableTables = $tables->filter(function ($table) use ($reservedTableIds) {
                return !in_array($table->id, $reservedTableIds) && $table->status === 'available';
            })->values();

            return response()->json([
                'success' => true,
                'data' => $availableTables
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengecek ketersediaan meja',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
