<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    public const POSITIONS = [
        'Veterinarian',
        'Veterinary Technician',
        'Groomer',
        'Handler',
        'Receptionist',
        'Cashier',
        'Back Office',
        'Inventory',
        'Janitor / Kennel Staff',
        'Manager',
    ];

    public const EMPLOYMENT_TYPES = [
        'regular' => 'Regular Employee',
        'casual' => 'Casual Employee',
        'probationary' => 'Probationary Employee',
        'contractual' => 'Contractual / Project-Based',
        'part_time' => 'Part-Time / Trainee',
    ];

    protected $fillable = [
        'employee_code',
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'birth_date',
        'gender',
        'civil_status',
        'emergency_contact_name',
        'emergency_contact_phone',
        'education',
        'position',
        'shift_start',
        'shift_end',
        'department',
        'employment_type',
        'basic_salary',
        'divisor_days',
        'rest_days_per_week',
        'daily_rate',
        'hourly_rate',
        'sss_no',
        'philhealth_no',
        'pagibig_no',
        'tin_no',
        'drivers_license_no',
        'previous_employer',
        'previous_position',
        'previous_salary',
        'years_of_experience',
        'date_hired',
        'status',
        'police_clearance_file',
        'medical_certificate_file',
        'sss_id_file',
        'philhealth_id_file',
        'pagibig_id_file',
        'drivers_license_file',
        'other_doc_file',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'previous_salary' => 'decimal:2',
        'divisor_days' => 'integer',
        'rest_days_per_week' => 'integer',
        'date_hired' => 'date',
        'birth_date' => 'date',
    ];

    /**
     * Check if employee is eligible for mandatory government statutory benefits (Casual or Regular only).
     */
    public function isGovEligible(): bool
    {
        $type = strtolower($this->employment_type ?? '');
        return in_array($type, ['regular', 'casual', 'full_time']);
    }

    public function getIsGovEligibleAttribute(): bool
    {
        return $this->isGovEligible();
    }

    /**
     * Estimated Monthly SSS Contribution (Employee Share - 4.5% up to max standard cap ₱1,350)
     */
    public function getSssContributionAttribute(): float
    {
        if (!$this->isGovEligible()) {
            return 0.00;
        }
        $basic = floatval($this->basic_salary);
        return round(min(1350.00, $basic * 0.045), 2);
    }

    /**
     * Estimated Monthly PhilHealth Contribution (Employee Share - 2.5% of monthly salary)
     */
    public function getPhilhealthContributionAttribute(): float
    {
        if (!$this->isGovEligible()) {
            return 0.00;
        }
        $basic = floatval($this->basic_salary);
        return round($basic * 0.025, 2);
    }

    /**
     * Estimated Monthly Pag-IBIG Contribution (Employee Share - Standard ₱100 / ₱200)
     */
    public function getPagibigContributionAttribute(): float
    {
        if (!$this->isGovEligible()) {
            return 0.00;
        }
        return 100.00;
    }

    /**
     * Total Monthly Statutory Government Contributions (Employee Share)
     */
    public function getTotalGovContributionsAttribute(): float
    {
        if (!$this->isGovEligible()) {
            return 0.00;
        }
        return round($this->sss_contribution + $this->philhealth_contribution + $this->pagibig_contribution, 2);
    }

    public static function generateEmployeeCode(): string
    {
        $year = date('Y');
        $latest = self::where('employee_code', 'LIKE', "EMP-{$year}-%")->latest('id')->first();
        if ($latest) {
            $parts = explode('-', $latest->employee_code);
            $num = intval(end($parts)) + 1;
        } else {
            $num = 1;
        }
        return sprintf("EMP-%s-%04d", $year, $num);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dtrRecords()
    {
        return $this->hasMany(DtrRecord::class);
    }

    public function leaveApplications()
    {
        return $this->hasMany(LeaveApplication::class);
    }

    public function deductions()
    {
        return $this->hasMany(Deduction::class);
    }

    public function activeDeductions()
    {
        return $this->hasMany(Deduction::class)->where('status', 'active');
    }

    public function incentives()
    {
        return $this->hasMany(EmployeeIncentive::class);
    }

    public function payrollRecords()
    {
        return $this->hasMany(PayrollRecord::class);
    }

    public function groomingRecords()
    {
        return $this->hasMany(GroomingRecord::class, 'groomer_id');
    }

    public function boardingAppointments()
    {
        return $this->hasMany(Appointment::class, 'assigned_employee_id');
    }
}
