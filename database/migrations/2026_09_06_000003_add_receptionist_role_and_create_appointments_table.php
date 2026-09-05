<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Update users table role enum to include 'receptionist'
        try {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'cashier', 'veterinarian', 'manager', 'inventory_officer', 'back_office', 'receptionist') DEFAULT 'admin'");
        } catch (\Exception $e) {
            // SQLite / fallback
        }

        // 2. Create Appointments Table
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('appointment_code')->unique(); // e.g. APT-2026-0001
            $table->enum('service_category', ['clinic', 'grooming'])->default('clinic');
            $table->string('service_type')->nullable(); // consultation, follow_up, wellness, grooming
            $table->foreignId('owner_id')->constrained('owners')->onDelete('cascade');
            $table->foreignId('pet_id')->constrained('pets')->onDelete('cascade');
            $table->foreignId('booked_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('veterinarian_id')->nullable()->constrained('users')->onDelete('set null');
            $table->date('appointment_date');
            $table->time('appointment_time');
            $table->text('purpose_examination_notes')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'checked_in', 'completed', 'cancelled'])->default('confirmed');
            $table->boolean('is_new_client')->default(false);
            $table->boolean('is_new_pet')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');

        try {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'cashier', 'veterinarian', 'manager', 'inventory_officer', 'back_office') DEFAULT 'admin'");
        } catch (\Exception $e) {
            // Fallback
        }
    }
};
