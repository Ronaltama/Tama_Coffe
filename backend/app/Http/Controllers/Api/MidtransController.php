<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

/**
 * @OA\Tag(
 *     name="Payments (Midtrans)",
 *     description="API untuk pembayaran menggunakan Midtrans"
 * )
 */
class MidtransController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = filter_var(config('midtrans.is_production'), FILTER_VALIDATE_BOOLEAN);
        Config::$isSanitized = filter_var(config('midtrans.is_sanitized'), FILTER_VALIDATE_BOOLEAN);
        Config::$is3ds = filter_var(config('midtrans.is_3ds'), FILTER_VALIDATE_BOOLEAN);

        if (empty(Config::$serverKey)) {
            Log::error('Midtrans server key is not configured. Please check .env');
        }
    }

    /**
     * @OA\Post(
     *     path="/api/guest/midtrans/create-snap-token",
     *     tags={"Payments (Midtrans)"},
     *     summary="Buat Snap Token untuk pembayaran",
     *     description="Generate token untuk Midtrans Snap payment gateway",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Order ID yang akan dibayar",
     *         @OA\JsonContent(
     *             required={"order_id"},
     *             @OA\Property(property="order_id", type="string", example="OR0001", description="ID order dari database")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Snap token berhasil dibuat",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="snap_token", type="string", description="Token untuk Midtrans Snap"),
     *             @OA\Property(property="order_id", type="string", example="OR0001"),
     *             @OA\Property(property="midtrans_order_id", type="string", example="OR0001-1234567890")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validasi gagal - order tidak ditemukan",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The given data was invalid.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error saat membuat token",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Gagal membuat token pembayaran"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function createSnapToken(Request $request)
    {
        try {
            $request->validate(['order_id' => 'required|string|exists:orders,id']);

            $order = Order::with('orderDetails.product')->findOrFail($request->order_id);

            $itemDetails = [];
            foreach ($order->orderDetails as $detail) {
                $itemDetails[] = [
                    'id' => $detail->product_id,
                    'price' => (int) $detail->unit_price,
                    'quantity' => (int) $detail->quantity,
                    'name' => $detail->product->name . ($detail->variant ? ' (' . $detail->variant . ')' : ''),
                ];
            }

            // Unique midtrans order id (so we can match later)
            $midtransOrderId = $order->id . '-' . time();

            $params = [
                'transaction_details' => [
                    'order_id' => $midtransOrderId,
                    'gross_amount' => (int) $order->total_price,
                ],
                'item_details' => $itemDetails,
                'customer_details' => [
                    'first_name' => $order->customer_name ?? 'Guest',
                    'phone' => $order->customer_phone ?? '',
                ],
            ];

            $snapToken = Snap::getSnapToken($params);

            // Save midtrans_order_id on Payment so webhook can match
            $payment = Payment::where('order_id', $order->id)->first();
            if ($payment) {
                $payment->update([
                    'midtrans_order_id' => $midtransOrderId,
                    'status' => 'pending',
                ]);
            } else {
                // If payment row not exist, create minimal
                Payment::create([
                    'id' => 'PM' . str_pad((int) (Payment::count() + 1), 4, '0', STR_PAD_LEFT),
                    'order_id' => $order->id,
                    'amount' => $order->total_price,
                    'method' => 'midtrans',
                    'midtrans_order_id' => $midtransOrderId,
                    'status' => 'pending',
                    'date' => now(),
                ]);
            }

            Log::info('Snap token created', ['order_id' => $order->id, 'midtrans_order_id' => $midtransOrderId]);

            return response()->json([
                'success' => true,
                'snap_token' => $snapToken,
                'midtrans_order_id' => $midtransOrderId,
                'order_id' => $order->id,
            ]);
        } catch (\Exception $e) {
            Log::error('createSnapToken error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Gagal membuat token pembayaran', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/guest/midtrans/notification",
     *     tags={"Payments (Midtrans)"},
     *     summary="Webhook notification dari Midtrans",
     *     description="Endpoint untuk menerima notifikasi pembayaran dari Midtrans. Dipanggil otomatis oleh Midtrans ketika ada perubahan status transaksi.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Payload notifikasi dari Midtrans",
     *         @OA\JsonContent(
     *             @OA\Property(property="order_id", type="string", description="Unique order ID dari Midtrans"),
     *             @OA\Property(property="transaction_id", type="string", description="Transaction ID dari Midtrans"),
     *             @OA\Property(property="transaction_status", type="string", enum={"settlement", "capture", "pending", "deny", "cancel", "expire", "refund"}, description="Status transaksi"),
     *             @OA\Property(property="payment_type", type="string", enum={"qris", "bank_transfer", "gopay", "credit_card"}, description="Tipe pembayaran"),
     *             @OA\Property(property="fraud_status", type="string", enum={"accept", "challenge", "deny"}, description="Status fraud detection"),
     *             @OA\Property(property="gross_amount", type="number", description="Jumlah transaksi"),
     *             @OA\Property(property="va_numbers", type="array", description="Virtual Account numbers (untuk bank_transfer)",
     *                 @OA\Items(
     *                     @OA\Property(property="bank", type="string"),
     *                     @OA\Property(property="va_number", type="string")
     *                 )
     *             ),
     *             @OA\Property(property="permata_va_number", type="string", description="Permata VA number (untuk bank_transfer)"),
     *             @OA\Property(property="signature_key", type="string", description="Hash signature untuk verifikasi")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notifikasi berhasil diproses",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Gagal memproses notifikasi",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function notification(Request $request)
    {
        try {
            // Log raw payload (useful for debugging)
            Log::info('Midtrans Webhook Raw Payload', ['payload' => $request->all(), 'headers' => $request->headers->all()]);

            // Parse using Midtrans Notification helper (will verify with server key)
            $notification = new \Midtrans\Notification();

            $transactionStatus = $notification->transaction_status ?? null;
            $fraudStatus = $notification->fraud_status ?? null;
            $orderId = $notification->order_id ?? null; // this is the midtrans_order_id we created earlier
            $paymentType = $notification->payment_type ?? null;

            // Extract original order id (before appended timestamp)
            $originalOrderId = null;
            if ($orderId) {
                $parts = explode('-', $orderId);
                $originalOrderId = $parts[0] ?? null;
            }

            $order = $originalOrderId ? Order::find($originalOrderId) : null;
            $payment = $originalOrderId ? Payment::where('order_id', $originalOrderId)->first() : null;

            DB::beginTransaction();

            if ($transactionStatus === 'capture') {
                if ($fraudStatus === 'accept') {
                    $this->updatePaymentStatus($payment, $order, 'paid', $notification);
                } else {
                    $this->updatePaymentStatus($payment, $order, 'pending', $notification);
                }
            } elseif ($transactionStatus === 'settlement') {
                $this->updatePaymentStatus($payment, $order, 'paid', $notification);
            } elseif ($transactionStatus === 'pending') {
                $this->updatePaymentStatus($payment, $order, 'pending', $notification);
            } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                $this->updatePaymentStatus($payment, $order, 'failed', $notification);
            } else {
                Log::warning('Unhandled Midtrans transaction status', ['status' => $transactionStatus, 'notification' => $notification]);
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Midtrans notification error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'payload' => $request->all()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/guest/midtrans/check-status",
     *     tags={"Payments (Midtrans)"},
     *     summary="Cek status pembayaran",
     *     description="Endpoint untuk frontend mengecek status pembayaran secara real-time",
     *     @OA\Parameter(
     *         name="order_id",
     *         in="query",
     *         required=true,
     *         description="Order ID yang ingin dicek statusnya",
     *         @OA\Schema(type="string", example="OR0001")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status pembayaran berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="status", type="string", enum={"pending", "paid", "failed", "expired"}, example="paid"),
     *                 @OA\Property(property="payment_type", type="string", example="qris"),
     *                 @OA\Property(property="midtrans_transaction_id", type="string"),
     *                 @OA\Property(property="midtrans_order_id", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment record tidak ditemukan",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Payment not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error internal server",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function checkStatus(Request $request)
    {
        try {
            $request->validate(['order_id' => 'required|string|exists:orders,id']);

            $payment = Payment::where('order_id', $request->order_id)->first();
            if (!$payment) {
                return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'status' => $payment->status,
                    'payment_type' => $payment->payment_type,
                    'midtrans_transaction_id' => $payment->midtrans_transaction_id,
                    'midtrans_order_id' => $payment->midtrans_order_id,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('checkStatus error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update payment + order based on notification
     */
    private function updatePaymentStatus($payment, $order, $status, $notification)
    {
        Log::info('Updating Payment Status', ['order_id' => $order->id ?? null, 'status' => $status, 'notification' => $notification]);

        if ($payment) {
            // va_numbers may be object/array — convert if present
            $vaNumbers = null;
            if (!empty($notification->va_numbers)) {
                $vaNumbers = json_encode($notification->va_numbers);
            } elseif (!empty($notification->permata_va_number)) {
                $vaNumbers = json_encode(['permata_va_number' => $notification->permata_va_number]);
            }

            $payment->update([
                'status' => $status,
                'payment_type' => $notification->payment_type ?? $payment->payment_type,
                'midtrans_transaction_id' => $notification->transaction_id ?? $payment->midtrans_transaction_id,
                'midtrans_order_id' => $notification->order_id ?? $payment->midtrans_order_id,
                'transaction_status' => $notification->transaction_status ?? $payment->transaction_status,
                'va_numbers' => $vaNumbers,
                'callback_payload' => json_encode($notification),
                'date' => now(),
            ]);
        } else {
            Log::warning('Payment record not found for notification', ['notification' => $notification]);
        }

        if ($order) {
            if ($status === 'paid') {
                $order->update(['status' => 'processing']);
                // If dine in, mark table occupied
                if ($order->table_id && $order->order_type === 'Dine In') {
                    DB::table('tables')->where('id', $order->table_id)->update([
                        'status' => 'occupied',
                        'updated_at' => now()
                    ]);
                }
            } elseif ($status === 'failed') {
                $order->update(['status' => 'cancelled']);
            } elseif ($status === 'pending') {
                $order->update(['status' => 'pending']);
            }
        } else {
            Log::warning('Order not found for notification', ['notification' => $notification]);
        }
    }
}
