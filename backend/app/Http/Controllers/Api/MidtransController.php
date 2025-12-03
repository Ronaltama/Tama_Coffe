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
     * Create Snap Token and save midtrans_order_id on payment record
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
     * Midtrans webhook (notification)
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

    /**
     * API - check payment status (used by frontend for UX)
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
}
