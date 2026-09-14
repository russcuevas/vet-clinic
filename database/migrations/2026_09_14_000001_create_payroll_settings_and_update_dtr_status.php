<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create payroll_settings table
        if (!Schema::hasTable('payroll_settings')) {
            Schema::create('payroll_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key', 100)->unique();
                $table->string('name', 150);
                $table->decimal('multiplier', 6, 2)->default(1.00);
                $table->text('description')->nullable();
                $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
            });

            // Seed default multiplier settings
            $now = now();
            DB::table('payroll_settings')->insert([
                [
                    'key' => 'holiday_multiplier',
                    'name' => 'Regular Holiday Multiplier',
                    'multiplier' => 2.00,
                    'description' => 'Multiplier applied when an employee works on a Regular Holiday (Default: x2.00 / 200%).',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'key' => 'special_holiday_multiplier',
                    'name' => 'Special Holiday Multiplier',
                    'multiplier' => 1.30,
                    'description' => 'Multiplier applied when an employee works on a Special Non-Working Holiday (Default: x1.30 / 130%).',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'key' => 'rest_day_multiplier',
                    'name' => 'Rest Day Multiplier',
                    'multiplier' => 1.30,
                    'description' => 'Multiplier applied when an employee works on their scheduled Rest Day (Default: x1.30 / 130%).',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'key' => 'overtime_multiplier',
                    'name' => 'Overtime Multiplier',
                    'multiplier' => 1.30,
                    'description' => 'Multiplier applied to hourly rate for overtime hours worked (Default: x1.30 / 130%).',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }

        // 2. Modify dtr_records.status column to string so it can store custom tags
        if (Schema::hasTable('dtr_records')) {
            Schema::table('dtr_records', function (Blueprint $table) {
                $table->string('status', 50)->default('present')->change();
            });
        }

        // 3. Add breakdown columns to payroll_records
        if (Schema::hasTable('payroll_records')) {
            Schema::table('payroll_records', function (Blueprint $table) {
                if (!Schema::hasColumn('payroll_records', 'holiday_days')) {
                    $table->decimal('holiday_days', 5, 2)->default(0.00)->after('ot_pay');
                }
                if (!Schema::hasColumn('payroll_records', 'holiday_pay')) {
                    $table->decimal('holiday_pay', 10, 2)->default(0.00)->after('holiday_days');
                }
                if (!Schema::hasColumn('payroll_records', 'special_holiday_days')) {
                    $table->decimal('special_holiday_days', 5, 2)->default(0.00)->after('holiday_pay');
                }
                if (!Schema::hasColumn('payroll_records', 'special_holiday_pay')) {
                    $table->decimal('special_holiday_pay', 10, 2)->default(0.00)->after('special_holiday_days');
                }
                if (!Schema::hasColumn('payroll_records', 'rest_day_days')) {
                    $table->decimal('rest_day_days', 5, 2)->default(0.00)->after('special_holiday_pay');
                }
                if (!Schema::hasColumn('payroll_records', 'rest_day_pay')) {
                    $table->decimal('rest_day_pay', 10, 2)->default(0.00)->after('rest_day_days');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payroll_records')) {
            Schema::table('payroll_records', function (Blueprint $table) {
                $table->dropColumn([
                    'holiday_days',
                    'holiday_pay',
                    'special_holiday_days',
                    'special_holiday_pay',
                    'rest_day_days',
                    'rest_day_pay'
                ]);
            });
        }

        Schema::dropIfExists('payroll_settings');
    }
};
