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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('day_off', 100)->nullable()->default('Sunday')->after('rest_days_per_week');
            $table->decimal('sss_contribution', 10, 2)->nullable()->default(0.00)->after('hourly_rate');
            $table->decimal('philhealth_contribution', 10, 2)->nullable()->default(0.00)->after('sss_contribution');
            $table->decimal('pagibig_contribution', 10, 2)->nullable()->default(0.00)->after('philhealth_contribution');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'day_off',
                'sss_contribution',
                'philhealth_contribution',
                'pagibig_contribution',
            ]);
        });
    }
};
