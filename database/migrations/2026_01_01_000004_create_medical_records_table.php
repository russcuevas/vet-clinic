<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->string('record_code')->unique(); // e.g. MED-2026-0001
            $table->foreignId('owner_id')->constrained('owners')->onDelete('cascade');
            $table->foreignId('pet_id')->constrained('pets')->onDelete('cascade');
            $table->foreignId('veterinarian_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('service_type', ['consultation', 'follow_up', 'wellness'])->default('consultation');
            $table->string('body_weight')->nullable(); // e.g. 5.2 kg
            $table->string('temperature')->nullable(); // e.g. 38.6 °C
            $table->string('body_score')->nullable(); // e.g. 3/5 Ideal
            $table->text('history_taking')->nullable();
            $table->string('attached_lab_results')->nullable(); // stored in public/uploads/lab_results/
            $table->text('diagnosis')->nullable();
            $table->text('veterinarians_notes')->nullable();
            $table->decimal('service_fee', 10, 2)->default(0.00);
            $table->enum('status', ['ongoing', 'completed', 'billed'])->default('ongoing');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
