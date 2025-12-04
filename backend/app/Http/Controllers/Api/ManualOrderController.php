<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Manual Orders",
 *     description="API untuk admin membuat order manual di pos"
 * )
 */
class ManualOrderController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/orders/manual",
     *     tags={"Manual Orders"},
     *     summary="Buat order manual by admin",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"order_type", "customer_name", "items", "payment"},
     *             @OA\Property(property="order_type", type="string", enum={"dine-in", "take-away"}, example="dine-in"),
     *             @OA\Property(property="table_id", type="string", example="TB001"),
     *             @OA\Property(property="customer_name", type="string", example="John Doe"),
     *             @OA\Property(property="customer_phone", type="string", example="08123456789"),
     *             @OA\Property(property="note", type="string"),
     *             @OA\Property(property="items", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="product_id", type="string"),
     *                     @OA\Property(property="quantity", type="integer"),
     *                     @OA\Property(property="variant", type="string", enum={"Ice", "Hot"}),
     *                     @OA\Property(property="unit_price", type="number")
     *                 )
     *             ),
     *             @OA\Property(property="payment", type="object",
     *                 @OA\Property(property="method", type="string", enum={"cash", "qris"}),
     *                 @OA\Property(property="amount", type="number")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order berhasil dibuat",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="order_id", type="string"),
     *                 @OA\Property(property="payment_id", type="string"),
     *                 @OA\Property(property="total", type="number")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validasi gagal"),
     *     @OA\Response(response=500, description="Gagal membuat order")
     * )
     */
    public function createManualOrder(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'order_type' => 'required|in:dine-in,take-away',
            'table_id' => 'required_if:order_type,dine-in|nullable|exists:tables,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.variant' => 'nullable|string|in:Ice,Hot',
            'items.*.unit_price' => 'required|numeric|min:0',
            'payment.method' => 'required|in:cash,qris',
            'payment.amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            // 1. Generate Order ID
            $lastOrder = Order::latest('id')->first();
            $lastNumber = $lastOrder ? (int) substr($lastOrder->id, 2) : 0;
            $orderId = 'OR' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

            // 2. Hitung total price (tanpa pajak)
            $totalPrice = 0;
            foreach ($request->items as $item) {
                $subtotal = $item['unit_price'] * $item['quantity'];
                $totalPrice += $subtotal;
            }

            // 3. Buat Order
            $order = Order::create([
                'id' => $orderId,
                'user_id' => Auth::user()->id, // Admin yang membuat order
                'table_id' => $request->order_type === 'dine-in' ? $request->table_id : null,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'total_price' => $totalPrice,
                'note' => $request->note,
                // Ubah dari 'pending' menjadi 'processing'
                'status' => 'processing',
            ]);

            // 4. Buat Order Details
            foreach ($request->items as $index => $item) {
                $detailId = $orderId . 'D' . str_pad($index + 1, 2, '0', STR_PAD_LEFT);
                $subtotal = $item['unit_price'] * $item['quantity'];

                OrderDetail::create([
                    'id' => $detailId,
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'variant' => $item['variant'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotal,
                ]);
            }

            // 5. Buat Payment Record
            $lastPayment = Payment::latest('id')->first();
            $lastPaymentNumber = $lastPayment ? (int) substr($lastPayment->id, 2) : 0;
            $paymentId = 'PM' . str_pad($lastPaymentNumber + 1, 3, '0', STR_PAD_LEFT);

            $paymentData = [
                'id' => $paymentId,
                'order_id' => $orderId,
                'amount' => $totalPrice,
                'method' => $request->payment['method'],
                'status' => 'pending', // Pending dulu, nanti diproses di Confirm Order
                'date' => now(),
            ];

            // Jika cash, simpan jumlah yang dibayar untuk referensi
            if ($request->payment['method'] === 'cash') {
                $paymentData['callback_payload'] = json_encode([
                    'paid_amount' => $request->payment['amount'],
                    'change' => $request->payment['amount'] - $totalPrice
                ]);
            }

            Payment::create($paymentData);

            // 6. JANGAN update status meja dulu, biarkan sampai order di-process di Confirm Order
            // Meja akan di-update saat admin klik "Process" di Confirm Order board

            // Tandai meja terpakai (opsional)
            if ($request->order_type === 'dine-in' && $request->table_id) {
                Table::where('id', $request->table_id)->update(['status' => 'occupied']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dibuat',
                'data' => [
                    'order_id' => $orderId,
                    'total' => $totalPrice,
                    'payment_id' => $paymentId,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/orders/manual/available-tables",
     *     tags={"Manual Orders"},
     *     summary="Dapatkan daftar meja yang tersedia",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List meja tersedia",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string"),
     *                     @OA\Property(property="table_number", type="string"),
     *                     @OA\Property(property="capacity", type="integer"),
     *                     @OA\Property(property="status", type="string")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getAvailableTables()
    {
        $tables = Table::where('status', 'available')
            ->orderBy('table_number')
            ->get(['id', 'table_number', 'capacity', 'type', 'status']);

        return response()->json([
            'success' => true,
            'data' => $tables
        ]);
    }
}
