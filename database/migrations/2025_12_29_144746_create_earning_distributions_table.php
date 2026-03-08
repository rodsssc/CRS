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
        Schema::create('earning_distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->constrained('rentals')->onDelete('cascade');
            $table->decimal('owner_share', 10, 2);
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->decimal('gross_ammount', 10, 2);
            $table->foreignId('commission_rate_id')->constrained('commissions')->onDelete('cascade');
            $table->decimal('admin_commission_ammount', 10, 2);
            $table->decimal('owner_net_ammount', 10, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('earning_distributions');
    }
};
