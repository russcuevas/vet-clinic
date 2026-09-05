<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\User;
use App\Models\IncentiveRule;
use App\Models\EmployeeIncentive;
use App\Models\DtrRecord;
use App\Models\LeaveApplication;
use App\Models\Deduction;
use App\Models\PayrollPeriod;
use App\Models\PayrollRecord;
use Carbon\Carbon;

class PayrollSeeder extends Seeder
{
    public function run(): void
    {
        $adminUser = User::where('role', 'admin')->first();
        $vetUser = User::where('role', 'veterinarian')->first();
        $cashierUser = User::where('role', 'cashier')->first();
        $managerUser = User::where('role', 'manager')->first();

        // 1. Create Default Incentive Rules (As specified in images 2 & 3 + client requests)
        $groomerRule = IncentiveRule::create([
            'role_name' => 'groomer',
            'title' => 'Pet Grooming Commission',
            'scheme_type' => 'percentage',
            'default_rate' => 10.00, // 10%
            'threshold_count' => 100, // 100 pets/month
            'tier2_rate' => 20.00, // 20% if >= 100 pets
            'unit_label' => 'pets',
            'description' => 'For groomers: 10% commission on pet grooming (small, medium, large, XL, XXL). If 100 or more pets groomed in a month, commission increases to 20%.',
            'is_active' => true,
        ]);

        $vetRule = IncentiveRule::create([
            'role_name' => 'veterinarian',
            'title' => 'Clinical Consultation Incentive',
            'scheme_type' => 'fixed_per_unit',
            'default_rate' => 200.00, // ₱200 per consult
            'threshold_count' => 0,
            'tier2_rate' => null,
            'unit_label' => 'consultations',
            'description' => 'For vets: ₱200 per pet consultation and clinical examination case.',
            'is_active' => true,
        ]);

        $janitorRule = IncentiveRule::create([
            'role_name' => 'janitor',
            'title' => 'Boarding Care & Maintenance Incentive',
            'scheme_type' => 'fixed_per_unit',
            'default_rate' => 35.00, // ₱35 per day
            'threshold_count' => 0,
            'tier2_rate' => null,
            'unit_label' => 'boarding days',
            'description' => 'For janitors and kennel assistants: ₱35 per pet boarding day care.',
            'is_active' => true,
        ]);

        // 2. Create Employees
        $employeesData = [
            [
                'employee_code' => 'EMP-2026-0001',
                'user_id' => $adminUser ? $adminUser->id : null,
                'first_name' => 'Dr. Modesto',
                'last_name' => 'Reyes',
                'email' => 'admin@sanmodesto.com',
                'phone' => '0917-123-4567',
                'position' => 'Veterinarian',
                'department' => 'Clinical Operations',
                'employment_type' => 'full_time',
                'basic_salary' => 45000.00,
                'daily_rate' => 2045.45,
                'hourly_rate' => 255.68,
                'sss_no' => '03-9876543-1',
                'philhealth_no' => '12-345678901-2',
                'pagibig_no' => '1210-9876-5432',
                'tin_no' => '234-567-890-000',
                'date_hired' => '2023-01-15',
                'status' => 'active',
            ],
            [
                'employee_code' => 'EMP-2026-0002',
                'user_id' => $vetUser ? $vetUser->id : null,
                'first_name' => 'Dr. Juan',
                'last_name' => 'Dela Cruz',
                'email' => 'vet@sanmodesto.com',
                'phone' => '0920-345-6789',
                'position' => 'Veterinarian',
                'department' => 'Clinical Operations',
                'employment_type' => 'full_time',
                'basic_salary' => 38000.00,
                'daily_rate' => 1727.27,
                'hourly_rate' => 215.91,
                'sss_no' => '03-8765432-2',
                'philhealth_no' => '12-234567890-3',
                'pagibig_no' => '1210-8765-4321',
                'tin_no' => '345-678-901-000',
                'date_hired' => '2023-06-01',
                'status' => 'active',
            ],
            [
                'employee_code' => 'EMP-2026-0003',
                'user_id' => null,
                'first_name' => 'Arnel',
                'last_name' => 'Bautista',
                'email' => 'arnel.groomer@sanmodesto.com',
                'phone' => '0919-555-1234',
                'position' => 'Groomer',
                'department' => 'Grooming Salon',
                'employment_type' => 'full_time',
                'basic_salary' => 17500.00,
                'daily_rate' => 795.45,
                'hourly_rate' => 99.43,
                'sss_no' => '03-7654321-3',
                'philhealth_no' => '12-345678901-4',
                'pagibig_no' => '1210-7654-3210',
                'tin_no' => '456-789-012-000',
                'date_hired' => '2024-02-10',
                'status' => 'active',
            ],
            [
                'employee_code' => 'EMP-2026-0004',
                'user_id' => $cashierUser ? $cashierUser->id : null,
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'email' => 'cashier@sanmodesto.com',
                'phone' => '0918-234-5678',
                'position' => 'Cashier',
                'department' => 'Front Desk & Billing',
                'employment_type' => 'full_time',
                'basic_salary' => 18500.00,
                'daily_rate' => 840.91,
                'hourly_rate' => 105.11,
                'sss_no' => '03-6543210-4',
                'philhealth_no' => '12-456789012-5',
                'pagibig_no' => '1210-6543-2109',
                'tin_no' => '567-890-123-000',
                'date_hired' => '2023-08-15',
                'status' => 'active',
            ],
            [
                'employee_code' => 'EMP-2026-0005',
                'user_id' => $managerUser ? $managerUser->id : null,
                'first_name' => 'Carlos',
                'last_name' => 'Mendoza',
                'email' => 'manager@sanmodesto.com',
                'phone' => '0922-456-7890',
                'position' => 'Manager',
                'department' => 'Administration',
                'employment_type' => 'full_time',
                'basic_salary' => 28000.00,
                'daily_rate' => 1272.73,
                'hourly_rate' => 159.09,
                'sss_no' => '03-5432109-5',
                'philhealth_no' => '12-567890123-6',
                'pagibig_no' => '1210-5432-1098',
                'tin_no' => '678-901-234-000',
                'date_hired' => '2023-03-01',
                'status' => 'active',
            ],
            [
                'employee_code' => 'EMP-2026-0006',
                'user_id' => null,
                'first_name' => 'Pedro',
                'last_name' => 'Reyes',
                'email' => 'pedro.utility@sanmodesto.com',
                'phone' => '0921-777-8899',
                'position' => 'Janitor / Kennel Staff',
                'department' => 'Maintenance & Kennel',
                'employment_type' => 'full_time',
                'basic_salary' => 15000.00,
                'daily_rate' => 681.82,
                'hourly_rate' => 85.23,
                'sss_no' => '03-4321098-6',
                'philhealth_no' => '12-678901234-7',
                'pagibig_no' => '1210-4321-0987',
                'tin_no' => '789-012-345-000',
                'date_hired' => '2024-05-01',
                'status' => 'active',
            ],
        ];

        $createdEmployees = [];
        foreach ($employeesData as $data) {
            $createdEmployees[] = Employee::create($data);
        }

        $arnelGroomer = $createdEmployees[2];
        $drJuan = $createdEmployees[1];
        $pedroJanitor = $createdEmployees[5];
        $mariaCashier = $createdEmployees[3];

        // 3. Seed Sample DTR Records for the past 5 days
        $today = Carbon::today();
        for ($i = 4; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            foreach ($createdEmployees as $emp) {
                DtrRecord::create([
                    'employee_id' => $emp->id,
                    'record_date' => $date->toDateString(),
                    'time_in' => '08:00',
                    'time_out' => '17:00',
                    'regular_hours' => 8.00,
                    'late_minutes' => 0,
                    'undertime_minutes' => 0,
                    'ot_hours' => ($emp->position === 'Groomer' && $i === 1) ? 2.00 : 0.00,
                    'status' => 'present',
                    'notes' => ($emp->position === 'Groomer' && $i === 1) ? 'Overtime on weekend grooming rush' : 'Standard Shift',
                ]);
            }
        }

        // 4. Seed Sample Leave Application (Within 5-day limit)
        LeaveApplication::create([
            'employee_id' => $mariaCashier->id,
            'leave_type' => 'sick_leave',
            'start_date' => $today->copy()->subDays(10)->toDateString(),
            'end_date' => $today->copy()->subDays(9)->toDateString(),
            'days_count' => 2,
            'reason' => 'Acute viral pharyngitis with medical certificate',
            'status' => 'approved',
            'is_paid' => true,
            'exceeded_limit' => false,
            'approved_by' => $adminUser ? $adminUser->id : null,
            'admin_remarks' => 'Approved (2/5 SL used, 2/12 Total Annual)',
        ]);

        // 5. Seed Sample Deductions (Loan & Cash Advance)
        Deduction::create([
            'employee_id' => $arnelGroomer->id,
            'deduction_type' => 'loan',
            'title' => 'Emergency Company Equipment Loan',
            'total_amount' => 5000.00,
            'monthly_amortization' => 1000.00,
            'remaining_balance' => 4000.00,
            'effective_date' => $today->copy()->startOfMonth()->toDateString(),
            'status' => 'active',
            'remarks' => '5-month amortization agreement @ ₱1,000/mo.',
        ]);

        Deduction::create([
            'employee_id' => $pedroJanitor->id,
            'deduction_type' => 'cash_advance',
            'title' => 'Mid-Month Cash Advance',
            'total_amount' => 1500.00,
            'monthly_amortization' => 1500.00,
            'remaining_balance' => 1500.00,
            'effective_date' => $today->toDateString(),
            'status' => 'active',
            'remarks' => 'Salary advance for medical prescription',
        ]);

        // 6. Seed Sample Incentives
        // Arnel: 105 pet grooms in month -> 20% tier!
        EmployeeIncentive::create([
            'employee_id' => $arnelGroomer->id,
            'incentive_rule_id' => $groomerRule->id,
            'title' => 'Pet Grooming Commission (20% Bonus Tier)',
            'calculation_basis' => 'percentage',
            'rate_applied' => 20.00, // 20%
            'base_amount_or_count' => 52500.00, // Total grooming sales
            'total_incentive' => 10500.00, // 20% of 52,500
            'date_earned' => $today->toDateString(),
            'notes' => 'Achieved 105 pets in month! Commission increased to 20% tier.',
        ]);

        // Dr. Juan: 18 consultations x ₱200
        EmployeeIncentive::create([
            'employee_id' => $drJuan->id,
            'incentive_rule_id' => $vetRule->id,
            'title' => 'Veterinary Consultation Incentive',
            'calculation_basis' => 'fixed_per_unit',
            'rate_applied' => 200.00,
            'base_amount_or_count' => 18,
            'total_incentive' => 3600.00, // 18 x 200
            'date_earned' => $today->toDateString(),
            'notes' => '18 clinical consultations recorded.',
        ]);

        // Pedro: 26 boarding days x ₱35
        EmployeeIncentive::create([
            'employee_id' => $pedroJanitor->id,
            'incentive_rule_id' => $janitorRule->id,
            'title' => 'Pet Boarding Care Incentive',
            'calculation_basis' => 'fixed_per_unit',
            'rate_applied' => 35.00,
            'base_amount_or_count' => 26,
            'total_incentive' => 910.00, // 26 x 35
            'date_earned' => $today->toDateString(),
            'notes' => '26 days of pet boarding maintenance completed.',
        ]);

        // 7. Seed a Completed 15-day Payroll Period
        $periodStart = $today->copy()->startOfMonth();
        $periodEnd = $today->copy()->startOfMonth()->addDays(14);
        $periodPayout = $today->copy()->startOfMonth()->addDays(15);

        $period = PayrollPeriod::create([
            'period_name' => $periodStart->format('F 1 - 15, Y') . ' Semi-Monthly Payroll',
            'period_type' => '15_days',
            'start_date' => $periodStart->toDateString(),
            'end_date' => $periodEnd->toDateString(),
            'payout_date' => $periodPayout->toDateString(),
            'status' => 'approved',
            'remarks' => 'First half payroll with incentive commissions',
        ]);

        foreach ($createdEmployees as $emp) {
            $basicMonthly = floatval($emp->basic_salary);
            $regPay = round($basicMonthly * 0.5, 2);
            $otPay = ($emp->position === 'Groomer') ? 248.58 : 0.00;

            $incTotal = 0.00;
            if ($emp->id === $arnelGroomer->id) {
                $incTotal = 5250.00; // Half month incentive
            } elseif ($emp->id === $drJuan->id) {
                $incTotal = 1800.00;
            } elseif ($emp->id === $pedroJanitor->id) {
                $incTotal = 455.00;
            }

            $grossPay = round($regPay + $otPay + $incTotal, 2);

            $sss = round(min(1350.00, $basicMonthly * 0.045) * 0.5, 2);
            $philhealth = round(($basicMonthly * 0.025) * 0.5, 2);
            $pagibig = 50.00;
            $tax = 0.00;
            if ($basicMonthly > 20833) {
                $tax = round(($basicMonthly - 20833) * 0.15 * 0.5, 2);
            }

            $loanDed = ($emp->id === $arnelGroomer->id) ? 500.00 : 0.00;
            $caDed = 0.00;

            $totalDeduct = round($sss + $philhealth + $pagibig + $tax + $loanDed + $caDed, 2);
            $netPay = round($grossPay - $totalDeduct, 2);

            PayrollRecord::create([
                'payroll_period_id' => $period->id,
                'employee_id' => $emp->id,
                'basic_salary' => $basicMonthly,
                'days_worked' => 11.0,
                'regular_pay' => $regPay,
                'ot_hours' => ($emp->position === 'Groomer') ? 2.0 : 0.0,
                'ot_pay' => $otPay,
                'incentives_total' => $incTotal,
                'gross_pay' => $grossPay,
                'sss_deduction' => $sss,
                'philhealth_deduction' => $philhealth,
                'pagibig_deduction' => $pagibig,
                'tax_deduction' => $tax,
                'loan_deduction' => $loanDed,
                'cash_advance_deduction' => $caDed,
                'absence_tardiness_deduction' => 0.00,
                'total_deductions' => $totalDeduct,
                'net_pay' => $netPay,
                'payment_status' => 'paid',
                'notes' => 'Regular 1st half payroll disbursement',
            ]);
        }
    }
}
