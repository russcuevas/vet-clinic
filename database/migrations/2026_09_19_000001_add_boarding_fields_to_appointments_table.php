<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Modify service_category enum in appointments
        try {
            DB::statement("ALTER TABLE appointments MODIFY COLUMN service_category ENUM('clinic', 'grooming', 'boarding') DEFAULT 'clinic'");
        } catch (\Exception $e) {
            // Fallback for non-MySQL or if column cannot be modified via raw SQL
        }

        // 2. Modify service_type enum in bills if needed
        try {
            DB::statement("ALTER TABLE bills MODIFY COLUMN service_type ENUM('veterinary', 'grooming', 'pet_supplies', 'combined', 'boarding') DEFAULT 'veterinary'");
        } catch (\Exception $e) {
            // Fallback
        }

        // 3. Add boarding and assigned staff columns to appointments
        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'assigned_employee_id')) {
                $table->foreignId('assigned_employee_id')->nullable()->after('veterinarian_id')->constrained('employees')->onDelete('set null');
            }
            if (!Schema::hasColumn('appointments', 'boarding_days')) {
                $table->integer('boarding_days')->nullable()->default(1)->after('purpose_examination_notes');
            }
            if (!Schema::hasColumn('appointments', 'daily_rate')) {
                $table->decimal('daily_rate', 10, 2)->nullable()->after('boarding_days');
            }
            if (!Schema::hasColumn('appointments', 'total_price')) {
                $table->decimal('total_price', 10, 2)->nullable()->after('daily_rate');
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'assigned_employee_id')) {
                $table->dropForeign(['assigned_employee_id']);
                $table->dropColumn('assigned_employee_id');
            }
            if (Schema::hasColumn('appointments', 'boarding_days')) {
                $table->dropColumn('boarding_days');
            }
            if (Schema::hasColumn('appointments', 'daily_rate')) {
                $table->dropColumn('daily_rate');
            }
            if (Schema::hasColumn('appointments', 'total_price')) {
                $table->dropColumn('total_price');
            }
        });

        try {
            DB::statement("ALTER TABLE appointments MODIFY COLUMN service_category ENUM('clinic', 'grooming') DEFAULT 'clinic'");
        } catch (\Exception $e) {
            // Fallback
        }
    }
};
