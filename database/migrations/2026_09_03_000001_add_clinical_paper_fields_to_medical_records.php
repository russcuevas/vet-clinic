<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->date('visit_date')->nullable()->after('service_type');
            $table->text('medication_treatment')->nullable()->after('diagnosis');
            $table->text('laboratory_notes')->nullable()->after('attached_lab_results');
            $table->date('follow_up_date')->nullable()->after('service_fee');
            $table->text('follow_up_notes')->nullable()->after('follow_up_date');
        });
    }

    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropColumn([
                'visit_date',
                'medication_treatment',
                'laboratory_notes',
                'follow_up_date',
                'follow_up_notes',
            ]);
        });
    }
};
