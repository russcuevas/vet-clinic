<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_annual_ledgers', function (Blueprint $table) {
            $table->id();
            $table->integer('year'); // e.g. 2026
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('employee_code');
            $table->string('employee_name');
            $table->string('position');
            $table->decimal('basic_salary', 10, 2)->default(0.00);
            $table->decimal('daily_rate', 10, 2)->default(0.00);

            // Quotas and usage for the year
            $table->integer('vl_quota')->default(5);
            $table->integer('vl_used')->default(0);
            $table->integer('vl_forfeited')->default(0);

            $table->integer('sl_quota')->default(5);
            $table->integer('sl_used')->default(0);
            $table->integer('sl_unused')->default(0);
            $table->decimal('sl_payout_amount', 10, 2)->default(0.00);
            $table->boolean('sl_credited')->default(false);

            $table->integer('spl_quota')->default(2);
            $table->integer('spl_used')->default(0);
            $table->integer('spl_forfeited')->default(0);

            $table->integer('total_quota')->default(12);
            $table->integer('total_used')->default(0);

            $table->timestamp('archived_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['year', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_annual_ledgers');
    }
};
