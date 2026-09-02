<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grooming_records', function (Blueprint $table) {
            $table->id();
            $table->string('grooming_code')->unique(); // e.g. GRM-2026-0001
            $table->foreignId('owner_id')->constrained('owners')->onDelete('cascade');
            $table->foreignId('pet_id')->constrained('pets')->onDelete('cascade');
            $table->string('body_weight')->nullable();
            $table->string('temperature')->nullable();
            $table->string('body_score')->nullable();
            $table->string('style'); // Style text type (e.g. Puppy cut, Teddy bear cut, Bath & Blowdry)
            $table->text('groomer_observation_notes')->nullable(); // Groomer observation Notes (text type)
            $table->decimal('price', 10, 2)->default(0.00);
            $table->enum('status', ['queued', 'in_progress', 'completed', 'billed'])->default('queued');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grooming_records');
    }
};
