<?php

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
        Schema::create('reservations', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('order_id')->nullable(); // FK akan ditambah setelah order dibuat
            $table->string('table_id')->nullable();
            $table->foreign('table_id')->references('id')->on('tables')->onDelete('set null');
            $table->string('name');
            $table->string('phone');
            $table->date('date');
            $table->time('time');
            $table->string('booking_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
