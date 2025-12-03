<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\IdGenerator;

class ReservationController extends Controller
{
    /**
     * Store new reservation (Guest)
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'customer_name' => 'required|string|max:255',
                'customer_phone' => 'required|string|max:20',
                'table_id' => 'required|string|exists:tables,id',
                'reservation_date' => 'required|date|after_or_equal:today',
                'reservation_time' => 'required|string',
            ]);

            // Check if table is available on that date/time
            $existingReservation = Reservation::where('table_id', $request->table_id)
                ->where('date', $request->reservation_date)
                ->first();

            if ($existingReservation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Meja sudah direservasi pada tanggal dan waktu tersebut'
                ], 422);
            }

            // Generate Reservation ID dan Booking Code
            $reservationId = IdGenerator::generate('reservations', 'RSV');
            $bookingCode = 'RES' . date('ymd') . strtoupper(substr(uniqid(), -4));

            // Create reservation
            $reservation = Reservation::create([
                'id' => $reservationId,
                'table_id' => $request->table_id,
                'name' => $request->customer_name,
                'phone' => $request->customer_phone,
                'date' => $request->reservation_date,
                'time' => $request->reservation_time,
                'booking_code' => $bookingCode,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reservasi berhasil dibuat',
                'data' => [
                    'id' => $reservation->id,
                    'booking_code' => $bookingCode,
                    'customer_name' => $reservation->name,
                    'table_id' => $reservation->table_id,
                    'date' => $reservation->date,
                    'time' => $reservation->time
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat reservasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get reservation status (Guest)
     */
    public function getStatus($id)
    {
        try {
            $reservation = Reservation::with(['order', 'order.orderDetails'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $reservation->id,
                    'booking_code' => $reservation->booking_code,
                    'customer_name' => $reservation->name,
                    'phone' => $reservation->phone,
                    'date' => $reservation->date,
                    'time' => $reservation->time,
                    'table_id' => $reservation->table_id,
                    'order' => $reservation->order,
                    'created_at' => $reservation->created_at,
                    'updated_at' => $reservation->updated_at
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Reservasi tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil status reservasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all reservations (Admin)
     */
    /**
     * Get all reservations (Admin)
     */
    public function index()
    {
        try {
            // Fix eager loading: use orderDetails instead of items, or just load order
            $reservations = Reservation::with(['order', 'order.orderDetails', 'order.orderDetails.product'])
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

            // Get reserved tables for that date (no status filter karena semua reservasi dianggap valid)
            $reservedTableIds = Reservation::where('date', $date)
                ->pluck('table_id')
                ->toArray();

            // Filter available tables
            $availableTables = $tables->filter(function ($table) use ($reservedTableIds) {
                return !in_array($table->id, $reservedTableIds) && $table->status !== 'maintenance';
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
