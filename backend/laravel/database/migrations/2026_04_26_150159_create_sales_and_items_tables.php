<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Table para sa summary ng benta
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->foreignId('user_id')->nullable(); // Cashier
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->string('discount_type')->nullable(); // Senior, PWD, etc.
            $table->decimal('total_amount', 10, 2);
            $table->decimal('cash_received', 10, 2);
            $table->decimal('change_amount', 10, 2);
            $table->timestamps();
        });

        // Table para sa mga produkto sa loob ng benta
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id'); 
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};