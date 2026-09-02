<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_period_id',
        'employee_id',
        'basic_salary',
        'days_worked',
        'regular_pay',
        'ot_hours',
        'ot_pay',
        'incentives_total',
        'gross_pay',
        'sss_deduction',
        'philhealth_deduction',
        'pagibig_deduction',
        'tax_deduction',
        'loan_deduction',
        'cash_advance_deduction',
        'absence_tardiness_deduction',
        'total_deductions',
        'net_pay',
        'payment_status',
        'notes',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'days_worked' => 'decimal:2',
        'regular_pay' => 'decimal:2',
        'ot_hours' => 'decimal:2',
        'ot_pay' => 'decimal:2',
        'incentives_total' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'sss_deduction' => 'decimal:2',
        'philhealth_deduction' => 'decimal:2',
        'pagibig_deduction' => 'decimal:2',
        'tax_deduction' => 'decimal:2',
        'loan_deduction' => 'decimal:2',
        'cash_advance_deduction' => 'decimal:2',
        'absence_tardiness_deduction' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_pay' => 'decimal:2',
    ];

    public function payrollPeriod()
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
