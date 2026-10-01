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
        Schema::create('veterinary_health_certificates', function (Blueprint $table) {
            $table->id();
            $table->string('control_number')->unique(); // e.g. 26-0001
            $table->date('certificate_date');
            $table->foreignId('owner_id')->nullable()->constrained('owners')->onDelete('set null');
            $table->foreignId('pet_id')->nullable()->constrained('pets')->onDelete('set null');
            $table->foreignId('veterinarian_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Owner & Destination Info
            $table->string('owner_name');
            $table->string('residing_at');
            $table->string('contact_number')->nullable();
            $table->string('destination');

            // Pet Description Info
            $table->string('pet_name');
            $table->string('species')->default('Canine');
            $table->string('breed')->nullable();
            $table->string('color')->nullable();
            $table->string('sex')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('age')->nullable();
            $table->string('weight')->nullable();
            $table->string('microchip')->default('None')->nullable();

            // Rabies Vaccination Info
            $table->date('rabies_vaccination_date')->nullable();
            $table->string('rabies_vaccine_name')->default('Rabisin')->nullable();
            $table->string('rabies_lot_number')->nullable();

            // Veterinarian Sign-off Info
            $table->string('veterinarian_name')->default('CARLO EUGENIO N. TUTOR, DVM');
            $table->string('tin_no')->nullable()->default('331-645-364');
            $table->string('ptr_no')->nullable()->default('1601213');
            $table->string('prc_no')->nullable()->default('0009324');
            $table->date('license_expiry_date')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('veterinary_health_certificates');
    }
};
