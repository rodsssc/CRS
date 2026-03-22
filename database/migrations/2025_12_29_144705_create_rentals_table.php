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
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('car_id')->constrained('cars')->onDelete('cascade');
            $table->string('destinationFrom')->nullable();
            $table->string('destinationTo')->nullable();
            $table->dateTime('rental_start_date');
            $table->dateTime('rental_end_date');
            $table->integer('total_days');
            $table->decimal('total_hours', 5, 2);
            $table->decimal('car_amount', 10, 2)->default(0);
            $table->decimal('destination_amount',10,2)->default(0);
            $table->decimal('discount_amount',10,2)->default(0);
           
            $table->decimal('final_amount', 10, 2)->default(0);
            $table->enum('status', ['ongoing','pending', 'completed',"cancelled"])->default('pending');
            $table->dateTime('returned_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.a
     */
    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }



};
