<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thirteenth_month_pays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->integer('year');
            $table->decimal('basic_salary', 10, 2)->default(0.00);
            $table->decimal('daily_rate', 10, 2)->default(0.00);
            $table->json('monthly_breakdown')->nullable();
            $table->decimal('total_basic_earned', 10, 2)->default(0.00); // Sum of 12 months bonus computation
            $table->decimal('thirteenth_month_pay', 10, 2)->default(0.00); // Total / 12
            $table->decimal('sl_total_quota', 5, 2)->default(5.00); // Sick Leave quota (5 days)
            $table->decimal('sl_days_used', 5, 2)->default(0.00);
            $table->decimal('sl_days_remaining', 5, 2)->default(5.00); // Unused SL days to monetize
            $table->decimal('sl_monetized_amount', 10, 2)->default(0.00); // Remaining SL * Daily Rate
            $table->decimal('other_bonus', 10, 2)->default(0.00);
            $table->decimal('deductions', 10, 2)->default(0.00);
            $table->decimal('total_payout', 10, 2)->default(0.00); // 13th month + SL monetized + other_bonus - deductions
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid');
            $table->date('payout_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thirteenth_month_pays');
    }
};
