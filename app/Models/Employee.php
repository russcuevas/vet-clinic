<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_code',
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'position',
        'department',
        'employment_type',
        'basic_salary',
        'daily_rate',
        'hourly_rate',
        'sss_no',
        'philhealth_no',
        'pagibig_no',
        'tin_no',
        'date_hired',
        'status',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'date_hired' => 'date',
    ];

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
}
