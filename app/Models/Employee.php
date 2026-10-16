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
        'day_off',
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
        'sss_contribution',
        'philhealth_contribution',
        'pagibig_contribution',
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
        'sss_contribution' => 'decimal:2',
        'philhealth_contribution' => 'decimal:2',
        'pagibig_contribution' => 'decimal:2',
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
     * SSS Contribution (Manual Input from Employee Database)
     */
    public function getSssContributionAttribute($val): float
    {
        return $val !== null ? floatval($val) : 0.00;
    }

    /**
     * PhilHealth Contribution (Manual Input - Nullable, 0 if not registered)
     */
    public function getPhilhealthContributionAttribute($val): float
    {
        return $val !== null ? floatval($val) : 0.00;
    }

    /**
     * Pag-IBIG Contribution (Manual Input - Nullable, 0 if not registered)
     */
    public function getPagibigContributionAttribute($val): float
    {
        return $val !== null ? floatval($val) : 0.00;
    }

    /**
     * Total Monthly Statutory Government Contributions (Employee Share)
     */
    public function getTotalGovContributionsAttribute(): float
    {
        return round($this->sss_contribution + $this->philhealth_contribution + $this->pagibig_contribution, 2);
    }

    /**
     * Check if a given date or day of week is a scheduled day off for this employee
     * 
     * @param \Carbon\Carbon|string $date
     * @return bool
     */
    public function isDayOff($date): bool
    {
        $dayName = $date instanceof \Carbon\Carbon ? $date->format('l') : (string)$date;
        $configured = !empty($this->day_off) ? $this->day_off : 'Sunday';
        $days = array_map('trim', explode(',', strtolower($configured)));
        return in_array(strtolower($dayName), $days);
    }

    /**
     * Get array list of configured days off
     */
    public function getDayOffListAttribute(): array
    {
        $configured = !empty($this->day_off) ? $this->day_off : 'Sunday';
        return array_map('trim', explode(',', $configured));
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

    public function thirteenthMonthPays()
    {
        return $this->hasMany(ThirteenthMonthPay::class);
    }
}
