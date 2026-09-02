<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->string('prescription_code')->unique(); // e.g. RX-2026-0001
            $table->foreignId('medical_record_id')->nullable()->constrained('medical_records')->onDelete('cascade');
            $table->foreignId('owner_id')->constrained('owners')->onDelete('cascade');
            $table->foreignId('pet_id')->constrained('pets')->onDelete('cascade');
            $table->foreignId('veterinarian_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('veterinarian_name');
            $table->string('license_no')->nullable();
            $table->string('body_weight')->nullable();
            $table->longText('rx_details'); // Prescribed medicines, dosage, instructions
            $table->text('instructions')->nullable(); // General care notes
            $table->date('date_issued');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
