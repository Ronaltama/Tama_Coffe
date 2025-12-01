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
        // Set Midtrans configuration
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');

        // Validate configuration
        if (empty(Config::$serverKey) || Config::$serverKey === 'SB-Mid-server-YOUR_SERVER_KEY') {
            Log::error('Midtrans Server Key not configured properly. Please update .env file.');
        }
    }

    /**
     * Create Midtrans Snap Token
     */
    public function createSnapToken(Request $request)
    {
        try {
            // Check if Midtrans is configured
            if (empty(Config::$serverKey) || Config::$serverKey === 'SB-Mid-server-YOUR_SERVER_KEY') {
                return response()->json([
                    'success' => false,
                    'message' => 'Midtrans belum dikonfigurasi. Silakan hubungi administrator.',
                    'error' => 'MIDTRANS_NOT_CONFIGURED',
                ], 500);
            }

            Log::info('Creating Midtrans Snap Token', ['request' => $request->all()]);

            $request->validate([
                'order_id' => 'required|string|exists:orders,id',
            ]);

            $order = Order::with(['orderDetails.product'])->findOrFail($request->order_id);
            Log::info('Order found', ['order_id' => $order->id, 'total' => $order->total_price]);

            // Prepare item details
            $itemDetails = [];
            foreach ($order->orderDetails as $detail) {
                $itemDetails[] = [
                    'id' => $detail->product_id,
                    'price' => (int) $detail->unit_price,
                    'quantity' => $detail->quantity,
                    'name' => $detail->product->name . ($detail->variant ? ' (' . $detail->variant . ')' : ''),
                ];
            }

            // Prepare transaction details
            $transactionDetails = [
                'order_id' => $order->id . '-' . time(), // Append timestamp untuk unique order_id
                'gross_amount' => (int) $order->total_price,
            ];

            // Prepare customer details
            $customerDetails = [
                'first_name' => $order->customer_name ?? 'Guest',
                'phone' => $order->customer_phone ?? '',
            ];

            // Prepare transaction params
            $params = [
                'transaction_details' => $transactionDetails,
                'item_details' => $itemDetails,
                'customer_details' => $customerDetails,
            ];

            // Get Snap Token
            $snapToken = Snap::getSnapToken($params);

            Log::info('Snap token created successfully', ['snap_token' => substr($snapToken, 0, 20) . '...']);

            return response()->json([
                'success' => true,
                'snap_token' => $snapToken,
                'order_id' => $order->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create snap token', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat token pembayaran',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle Midtrans Notification/Callback
     */
    public function notification(Request $request)
    {
        try {
            $notification = new \Midtrans\Notification();

            $transactionStatus = $notification->transaction_status;
            $fraudStatus = $notification->fraud_status;
            $orderId = $notification->order_id;

            // Extract original order_id (remove timestamp)
            $originalOrderId = explode('-', $orderId)[0];

            $order = Order::findOrFail($originalOrderId);
            $payment = Payment::where('order_id', $originalOrderId)->first();

            DB::beginTransaction();

            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'accept') {
                    // Payment success
                    $this->updatePaymentStatus($payment, $order, 'paid', $notification);
                }
            } elseif ($transactionStatus == 'settlement') {
                // Payment success
                $this->updatePaymentStatus($payment, $order, 'paid', $notification);
            } elseif ($transactionStatus == 'pending') {
                // Payment pending
                $this->updatePaymentStatus($payment, $order, 'pending', $notification);
            } elseif ($transactionStatus == 'deny' || $transactionStatus == 'expire' || $transactionStatus == 'cancel') {
                // Payment failed
                $this->updatePaymentStatus($payment, $order, 'failed', $notification);
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update payment and order status
     */
    private function updatePaymentStatus($payment, $order, $status, $notification)
    {
        if ($payment) {
            $payment->update([
                'status' => $status,
                'payment_type' => $notification->payment_type ?? 'online',
                'transaction_id' => $notification->transaction_id ?? null,
                'date' => now(),
            ]);
        }

        // Update order status based on payment status
        if ($status === 'paid') {
            $order->update(['status' => 'confirmed']);

            // Update table status to occupied if there's a table
            if ($order->table_id && $order->order_type === 'Dine In') {
                DB::table('tables')
                    ->where('id', $order->table_id)
                    ->update([
                        'status' => 'occupied',
                        'updated_at' => now()
                    ]);
            }
        } elseif ($status === 'failed') {
            $order->update(['status' => 'cancelled']);
        }
    }

    /**
     * Check payment status
     */
    public function checkStatus(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required|string|exists:orders,id',
            ]);

            $payment = Payment::where('order_id', $request->order_id)->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'status' => $payment->status,
                    'payment_type' => $payment->payment_type,
                    'transaction_id' => $payment->transaction_id,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
