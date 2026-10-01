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
            // Employment type string change / expand
            $table->string('employment_type', 50)->default('probationary')->change();

            // Personal Information
            $table->text('address')->nullable()->after('phone');
            $table->date('birth_date')->nullable()->after('address');
            $table->string('gender', 20)->nullable()->after('birth_date');
            $table->string('civil_status', 30)->nullable()->after('gender');
            $table->string('emergency_contact_name', 100)->nullable()->after('civil_status');
            $table->string('emergency_contact_phone', 50)->nullable()->after('emergency_contact_name');
            $table->string('education', 150)->nullable()->after('emergency_contact_phone');
            $table->string('drivers_license_no', 50)->nullable()->after('tin_no');

            // Previous Employment History
            $table->string('previous_employer', 150)->nullable()->after('education');
            $table->string('previous_position', 100)->nullable()->after('previous_employer');
            $table->decimal('previous_salary', 10, 2)->nullable()->after('previous_position');
            $table->string('years_of_experience', 50)->nullable()->after('previous_salary');

            // Attached Documents & IDs (storage paths)
            $table->string('police_clearance_file')->nullable()->after('status');
            $table->string('medical_certificate_file')->nullable()->after('police_clearance_file');
            $table->string('sss_id_file')->nullable()->after('medical_certificate_file');
            $table->string('philhealth_id_file')->nullable()->after('sss_id_file');
            $table->string('pagibig_id_file')->nullable()->after('philhealth_id_file');
            $table->string('drivers_license_file')->nullable()->after('pagibig_id_file');
            $table->string('other_doc_file')->nullable()->after('drivers_license_file');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'address',
                'birth_date',
                'gender',
                'civil_status',
                'emergency_contact_name',
                'emergency_contact_phone',
                'education',
                'drivers_license_no',
                'previous_employer',
                'previous_position',
                'previous_salary',
                'years_of_experience',
                'police_clearance_file',
                'medical_certificate_file',
                'sss_id_file',
                'philhealth_id_file',
                'pagibig_id_file',
                'drivers_license_file',
                'other_doc_file',
            ]);
        });
    }
};
