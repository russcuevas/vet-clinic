<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Employees Table
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code')->unique(); // e.g. EMP-2026-0001
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('position'); // e.g. Veterinarian, Groomer, Janitor, Cashier, Receptionist, Manager
            $table->string('department')->default('Operations');
            $table->enum('employment_type', ['full_time', 'part_time', 'contract'])->default('full_time');
            $table->decimal('basic_salary', 10, 2)->default(0.00); // Monthly basic salary
            $table->decimal('daily_rate', 10, 2)->default(0.00);
            $table->decimal('hourly_rate', 10, 2)->default(0.00);
            $table->string('sss_no')->nullable();
            $table->string('philhealth_no')->nullable();
            $table->string('pagibig_no')->nullable();
            $table->string('tin_no')->nullable();
            $table->date('date_hired')->nullable();
            $table->enum('status', ['active', 'inactive', 'on_leave'])->default('active');
            $table->timestamps();
        });

        // 2. DTR (Daily Time Record)
        Schema::create('dtr_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('record_date');
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->decimal('regular_hours', 5, 2)->default(0.00);
            $table->integer('late_minutes')->default(0);
            $table->integer('undertime_minutes')->default(0);
            $table->decimal('ot_hours', 5, 2)->default(0.00);
            $table->enum('status', ['present', 'late', 'absent', 'on_leave', 'rest_day'])->default('present');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'record_date']);
        });

        // 3. Leave Applications
        Schema::create('leave_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->enum('leave_type', ['vacation_leave', 'sick_leave', 'special_leave'])->default('sick_leave');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('days_count')->default(1);
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->boolean('is_paid')->default(true);
            $table->boolean('exceeded_limit')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('admin_remarks')->nullable();
            $table->timestamps();
        });

        // 4. Financial & Deductions (Loans, Cash Advances, Gov Contributions)
        Schema::create('deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->enum('deduction_type', ['loan', 'cash_advance', 'sss', 'philhealth', 'pagibig', 'tax', 'tardiness_absence', 'other']);
            $table->string('title');
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->decimal('monthly_amortization', 10, 2)->default(0.00); // Amount to deduct per period/month
            $table->decimal('remaining_balance', 10, 2)->default(0.00);
            $table->date('effective_date');
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        // 5. Incentive Rules (Configurable schemes for roles)
        Schema::create('incentive_rules', function (Blueprint $table) {
            $table->id();
            $table->string('role_name'); // e.g. groomer, veterinarian, janitor
            $table->string('title'); // e.g. Grooming Commission, Vet Consultation Incentive, Boarding Bonus
            $table->enum('scheme_type', ['percentage', 'fixed_per_unit'])->default('percentage');
            $table->decimal('default_rate', 8, 2)->default(10.00); // 10% or P200
            $table->integer('threshold_count')->default(0); // e.g. 100 pets
            $table->decimal('tier2_rate', 8, 2)->nullable(); // e.g. 20% if >= 100 pets
            $table->string('unit_label')->default('pets'); // pets, consultations, days
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 6. Employee Incentives (Logged/editable incentives per employee)
        Schema::create('employee_incentives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('incentive_rule_id')->nullable()->constrained('incentive_rules')->onDelete('set null');
            $table->string('title');
            $table->enum('calculation_basis', ['percentage', 'fixed_per_unit'])->default('percentage');
            $table->decimal('rate_applied', 8, 2)->default(0.00); // % or fixed amount per unit
            $table->decimal('base_amount_or_count', 10, 2)->default(0.00); // Total sales or pet count
            $table->decimal('total_incentive', 10, 2)->default(0.00); // Computed/editable final incentive
            $table->date('date_earned');
            $table->unsignedBigInteger('payroll_period_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 7. Payroll Periods
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();
            $table->string('period_name'); // e.g. "March 1 - 15, 2026", "March 2026 Full Month"
            $table->enum('period_type', ['15_days', '30_days', 'annual'])->default('15_days');
            $table->date('start_date');
            $table->date('end_date');
            $table->date('payout_date');
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        // 8. Payroll Records
        Schema::create('payroll_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_period_id')->constrained('payroll_periods')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->decimal('basic_salary', 10, 2)->default(0.00);
            $table->decimal('days_worked', 5, 2)->default(0.00);
            $table->decimal('regular_pay', 10, 2)->default(0.00);
            $table->decimal('ot_hours', 5, 2)->default(0.00);
            $table->decimal('ot_pay', 10, 2)->default(0.00);
            $table->decimal('incentives_total', 10, 2)->default(0.00);
            $table->decimal('gross_pay', 10, 2)->default(0.00);

            // Deductions breakdown
            $table->decimal('sss_deduction', 10, 2)->default(0.00);
            $table->decimal('philhealth_deduction', 10, 2)->default(0.00);
            $table->decimal('pagibig_deduction', 10, 2)->default(0.00);
            $table->decimal('tax_deduction', 10, 2)->default(0.00);
            $table->decimal('loan_deduction', 10, 2)->default(0.00);
            $table->decimal('cash_advance_deduction', 10, 2)->default(0.00);
            $table->decimal('absence_tardiness_deduction', 10, 2)->default(0.00);
            $table->decimal('total_deductions', 10, 2)->default(0.00);

            // Net Pay
            $table->decimal('net_pay', 10, 2)->default(0.00);
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['payroll_period_id', 'employee_id']);
        });

        // Add optional groomer_id to grooming_records if not exists
        if (Schema::hasTable('grooming_records') && !Schema::hasColumn('grooming_records', 'groomer_id')) {
            Schema::table('grooming_records', function (Blueprint $table) {
                $table->foreignId('groomer_id')->nullable()->after('pet_id')->constrained('employees')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('grooming_records') && Schema::hasColumn('grooming_records', 'groomer_id')) {
            Schema::table('grooming_records', function (Blueprint $table) {
                $table->dropForeign(['groomer_id']);
                $table->dropColumn('groomer_id');
            });
        }

        Schema::dropIfExists('payroll_records');
        Schema::dropIfExists('payroll_periods');
        Schema::dropIfExists('employee_incentives');
        Schema::dropIfExists('incentive_rules');
        Schema::dropIfExists('deductions');
        Schema::dropIfExists('leave_applications');
        Schema::dropIfExists('dtr_records');
        Schema::dropIfExists('employees');
    }
};
