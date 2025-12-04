<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Guest Orders",
 *     description="API untuk customer membuat dan mengelola order"
 * )
 */
class OrderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/guest/table-info/{tableId}",
     *     tags={"Guest Orders"},
     *     summary="Dapatkan info meja (untuk scan QR atau simulasi)",
     *     @OA\Parameter(
     *         name="tableId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", example="TB001")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Info meja berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string"),
     *                 @OA\Property(property="table_number", type="string"),
     *                 @OA\Property(property="capacity", type="integer"),
     *                 @OA\Property(property="status", type="string", enum={"available", "occupied", "reserved", "maintenance"})
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Meja tidak ditemukan"),
     *     @OA\Response(response=400, description="Meja sedang dalam perbaikan")
     * )
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
     * @OA\Post(
     *     path="/api/guest/orders",
     *     tags={"Guest Orders"},
     *     summary="Submit order baru dari customer (Dine In atau Reservasi)",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"customer_name", "order_type", "payment_method", "items"},
     *             @OA\Property(property="customer_name", type="string", example="John Doe"),
     *             @OA\Property(property="customer_phone", type="string", example="08123456789"),
     *             @OA\Property(property="table_id", type="string", example="TB001"),
     *             @OA\Property(property="order_type", type="string", enum={"Dine In", "Reservasi"}, example="Dine In"),
     *             @OA\Property(property="payment_method", type="string", enum={"cash", "qris"}, example="qris"),
     *             @OA\Property(property="notes", type="string", example="Tanpa gula"),
     *             @OA\Property(property="items", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="product_id", type="string", example="PR001"),
     *                     @OA\Property(property="quantity", type="integer", example=2),
     *                     @OA\Property(property="variant", type="string", example="iced"),
     *                     @OA\Property(property="price", type="number", example=25000)
     *                 )
     *             ),
     *             @OA\Property(property="reservation_data", type="object",
     *                 @OA\Property(property="customer_name", type="string"),
     *                 @OA\Property(property="customer_phone", type="string"),
     *                 @OA\Property(property="table_id", type="string"),
     *                 @OA\Property(property="reservation_date", type="string", format="date"),
     *                 @OA\Property(property="reservation_time", type="string", example="19:00")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order berhasil dibuat",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="order_id", type="string", example="OR001"),
     *                 @OA\Property(property="payment_id", type="string", example="PM001"),
     *                 @OA\Property(property="total_price", type="number"),
     *                 @OA\Property(property="booking_code", type="string", example="BOOK1234AB")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validasi gagal atau meja sudah direservasi"),
     *     @OA\Response(response=500, description="Gagal membuat order")
     * )
     */
    public function submitOrder(Request $request)
    {
        try {
            Log::info('Order Request Received:', $request->all());

            $validated = $request->validate([
                'customer_name' => 'required|string|max:255',
                'customer_phone' => 'nullable|string|max:20',
                'customer_email' => 'nullable|email',
                'table_id' => 'nullable|string|exists:tables,id',
                'table_number' => 'nullable|string',
                'order_type' => 'required|in:Dine In,Reservasi',
                'payment_method' => 'required|in:cash,qris',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|string|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.variant' => 'nullable|string',
                'items.*.price' => 'required|numeric|min:0',
                'reservation_data' => 'nullable|array',
                'reservation_data.customer_name' => 'required_with:reservation_data|string|max:255',
                'reservation_data.customer_phone' => 'required_with:reservation_data|string|max:20',
                'reservation_data.table_id' => 'required_with:reservation_data|string|exists:tables,id',
                'reservation_data.reservation_date' => 'required_with:reservation_data|date',
                'reservation_data.reservation_time' => 'required_with:reservation_data',
            ]);

            DB::beginTransaction();

            $reservationId = null;
            if ($validated['order_type'] === 'Reservasi' && !empty($validated['reservation_data'])) {
                $isReserved = \App\Models\Reservation::where('date', $validated['reservation_data']['reservation_date'])
                    ->where('table_id', $validated['reservation_data']['table_id'])
                    ->exists();

                if ($isReserved) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Meja sudah direservasi pada tanggal tersebut.'
                    ], 422);
                }

                $reservationId = 'RES' . time() . rand(100, 999);
                $bookingCode = 'BOOK' . strtoupper(substr(md5($reservationId), 0, 6));

                \App\Models\Reservation::create([
                    'id' => $reservationId,
                    'order_id' => null,
                    'name' => $validated['reservation_data']['customer_name'],
                    'phone' => $validated['reservation_data']['customer_phone'],
                    'table_id' => $validated['reservation_data']['table_id'],
                    'date' => $validated['reservation_data']['reservation_date'],
                    'time' => $validated['reservation_data']['reservation_time'],
                    'booking_code' => $bookingCode,
                ]);
            }

            $orderCount = DB::table('orders')->count();
            $orderId = 'OR' . str_pad($orderCount + 1, 4, '0', STR_PAD_LEFT);

            $totalPrice = 0;
            foreach ($validated['items'] as $item) {
                $totalPrice += $item['price'] * $item['quantity'];
            }

            DB::table('orders')->insert([
                'id' => $orderId,
                'user_id' => null,
                'table_id' => $validated['table_id'] ?? null,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'] ?? '',
                'total_price' => $totalPrice,
                'note' => $validated['notes'] ?? null,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (!empty($reservationId)) {
                DB::table('reservations')
                    ->where('id', $reservationId)
                    ->update(['order_id' => $orderId, 'updated_at' => now()]);
            }

            foreach ($validated['items'] as $item) {
                $detailCount = DB::table('order_details')->count();
                $detailId = 'OD' . str_pad($detailCount + 1, 4, '0', STR_PAD_LEFT);
                $subtotal = $item['price'] * $item['quantity'];

                DB::table('order_details')->insert([
                    'id' => $detailId,
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'variant' => $item['variant'] ?? null,
                    'unit_price' => $item['price'],
                    'subtotal' => $subtotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $paymentCount = DB::table('payments')->count();
            $paymentId = 'PM' . str_pad($paymentCount + 1, 4, '0', STR_PAD_LEFT);

            DB::table('payments')->insert([
                'id' => $paymentId,
                'order_id' => $orderId,
                'amount' => $totalPrice,
                'method' => $validated['payment_method'] === 'qris' ? 'midtrans' : 'cash',
                'payment_type' => $validated['payment_method'],
                'status' => 'pending',
                'date' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($validated['table_id'] && $validated['order_type'] === 'Dine In') {
                DB::table('tables')
                    ->where('id', $validated['table_id'])
                    ->update([
                        'status' => 'occupied',
                        'updated_at' => now()
                    ]);
            }

            DB::commit();

            $responseData = [
                'order_id' => $orderId,
                'payment_id' => $paymentId,
                'total_price' => $totalPrice,
                'status' => 'pending',
                'payment_method' => $validated['payment_method'],
            ];

            if ($validated['order_type'] === 'Reservasi' && !empty($reservationId)) {
                $reservation = \App\Models\Reservation::find($reservationId);
                $responseData['reservation_id'] = $reservationId;
                $responseData['booking_code'] = $reservation->booking_code;
            }

            return response()->json([
                'success' => true,
                'message' => $validated['order_type'] === 'Reservasi'
                    ? 'Reservasi dan order berhasil dibuat.'
                    : 'Order berhasil dibuat',
                'data' => $responseData
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation failed:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/guest/orders/{orderId}/cancel",
     *     tags={"Guest Orders"},
     *     summary="Batalkan order (rollback semua data)",
     *     @OA\Parameter(
     *         name="orderId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", example="OR001")
     *     ),
     *     @OA\Response(response=200, description="Order berhasil dibatalkan"),
     *     @OA\Response(response=404, description="Order tidak ditemukan"),
     *     @OA\Response(response=500, description="Gagal membatalkan order")
     * )
     */
    public function cancelOrder($orderId)
    {
        try {
            DB::beginTransaction();

            $order = DB::table('orders')->where('id', $orderId)->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order tidak ditemukan'
                ], 404);
            }

            DB::table('payments')->where('order_id', $orderId)->delete();
            DB::table('order_details')->where('order_id', $orderId)->delete();
            DB::table('reservations')->where('order_id', $orderId)->delete();
            DB::table('orders')->where('id', $orderId)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dibatalkan dan data telah dihapus'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order cancellation failed:', [
                'order_id' => $orderId,
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan order',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
