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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->nullable()->constrained('rentals')->onDelete('cascade');
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('payment_type',['downpayment','full_payment'])->default('full_payment');
            $table->enum('payment_method', ['credit_card', 'gcash', 'maya', 'bank_transfer', 'cash'])->default('cash');
            
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->dateTime('payment_date')->nullable();
            $table->text('notes')->nullable(); 
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
