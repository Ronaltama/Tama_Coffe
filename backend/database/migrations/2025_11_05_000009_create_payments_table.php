<?php

// use Illuminate\Database\Migrations\Migration;
// use Illuminate\Database\Schema\Blueprint;
// use Illuminate\Support\Facades\Schema;

// return new class extends Migration
// {
//     /**
//      * Run the migrations.
//      */
//     public function up(): void
//     {
//         Schema::create('payments', function (Blueprint $table) {
//             $table->string('id')->primary();
//             $table->string('order_id');
//             $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
//             $table->decimal('amount', 10, 2);
//             $table->string('method');
//             $table->string('proof')->nullable();
//             $table->enum('status', ['pending', 'paid', 'failed'])->default('pending');
//             $table->date('date');
//             $table->timestamps();
//         });
//     }

//     /**
//      * Reverse the migrations.
//      */
//     public function down(): void
//     {
//         Schema::dropIfExists('payments');
//     }
// };

// --------------------------------------------------------------------

// MIDTRANS UPDATE ==

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->string('id')->primary(); // PM001 dll

            $table->string('order_id');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');

            $table->decimal('amount', 10, 2);

            // Pembeda metode pembayaran
            $table->enum('method', ['cash', 'midtrans'])->default('midtrans');

            // Khusus Midtrans
            $table->string('midtrans_transaction_id')->nullable();  // contoh: 123465677
            $table->string('midtrans_order_id')->nullable();        // ID unik Midtrans (biasanya sama dengan order_id tapi bisa beda)
            $table->string('payment_type')->nullable();             // qris, bank_transfer, gopay, dll
            $table->string('transaction_status')->nullable();       // pending, settlement, expire, deny, cancel, etc

            $table->json('va_numbers')->nullable();                 // Jika bank transfer
            $table->json('callback_payload')->nullable();           // Data webhook

            $table->enum('status', ['pending', 'paid', 'failed', 'expired'])
                  ->default('pending');

            $table->dateTime('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};






