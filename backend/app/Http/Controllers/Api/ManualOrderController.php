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

class ManualOrderController extends Controller
{
    /**
     * Create manual order by admin
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
                'status' => 'pending', // Mulai dari pending agar masuk ke Confirm Order board
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
     * Get available tables for manual order
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
