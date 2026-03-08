<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('car_id')->constrained('cars')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('title', 120);
            $table->text('description')->nullable();

            $table->date('service_date');
            $table->decimal('cost', 12, 2)->default(0);

            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');

            $table->timestamps();

            $table->index(['car_id', 'service_date']);
            $table->index(['status', 'service_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};

