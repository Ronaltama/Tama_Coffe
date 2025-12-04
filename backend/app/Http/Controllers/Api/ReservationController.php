<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\IdGenerator;

/**
 * @OA\Tag(
 *     name="Reservations",
 *     description="API untuk manajemen reservasi meja"
 * )
 */
class ReservationController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/guest/reservations",
     *     tags={"Reservations"},
     *     summary="Buat reservasi meja baru",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"customer_name", "customer_phone", "table_id", "reservation_date", "reservation_time"},
     *             @OA\Property(property="customer_name", type="string", example="John Doe"),
     *             @OA\Property(property="customer_phone", type="string", example="08123456789"),
     *             @OA\Property(property="table_id", type="string", example="TB001"),
     *             @OA\Property(property="reservation_date", type="string", format="date", example="2025-12-25"),
     *             @OA\Property(property="reservation_time", type="string", example="19:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Reservasi berhasil dibuat",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="booking_code", type="string"),
     *                 @OA\Property(property="date", type="string"),
     *                 @OA\Property(property="time", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Meja sudah direservasi atau validasi gagal")
     * )
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
     * @OA\Get(
     *     path="/api/guest/reservations/{id}/status",
     *     tags={"Reservations"},
     *     summary="Cek status reservasi",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", example="RSV001")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status reservasi berhasil diambil"
     *     ),
     *     @OA\Response(response=404, description="Reservasi tidak ditemukan")
     * )
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
     * @OA\Get(
     *     path="/api/admin/reservations",
     *     tags={"Reservations"},
     *     summary="Dapatkan semua reservasi (Admin)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List semua reservasi"
     *     )
     * )
     */
    public function index()
    {
        try {
            $reservations = Reservation::with(['table', 'order', 'order.orderDetails', 'order.orderDetails.product', 'order.payments'])
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
     * @OA\Get(
     *     path="/api/reservations/availability",
     *     tags={"Reservations"},
     *     summary="Cek ketersediaan meja untuk tanggal & waktu tertentu",
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="time",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List meja yang tersedia"
     *     )
     * )
     */
    public function checkAvailability(Request $request)
    {
        try {
            $request->validate([
                'date' => 'required|date',
                'time' => 'required'
            ]);

            $date = $request->date;

            $tables = Table::all();

            $reservedTableIds = Reservation::where('date', $date)
                ->pluck('table_id')
                ->toArray();

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
