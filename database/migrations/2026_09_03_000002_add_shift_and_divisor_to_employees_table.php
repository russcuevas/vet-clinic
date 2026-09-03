<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('shift_start', 10)->default('09:00')->after('position');
            $table->string('shift_end', 10)->default('18:00')->after('shift_start');
            $table->integer('divisor_days')->default(26)->after('basic_salary');
            $table->integer('rest_days_per_week')->default(1)->after('divisor_days');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'shift_start',
                'shift_end',
                'divisor_days',
                'rest_days_per_week',
            ]);
        });
    }
};
