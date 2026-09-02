<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'period_name',
        'period_type',
        'start_date',
        'end_date',
        'payout_date',
        'status',
        'remarks',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'payout_date' => 'date',
    ];

    public function records()
    {
        return $this->hasMany(PayrollRecord::class);
    }

    public function getTotalGrossPayAttribute(): float
    {
        return (float) $this->records->sum('gross_pay');
    }

    public function getTotalIncentivesAttribute(): float
    {
        return (float) $this->records->sum('incentives_total');
    }

    public function getTotalDeductionsAttribute(): float
    {
        return (float) $this->records->sum('total_deductions');
    }

    public function getTotalNetPayAttribute(): float
    {
        return (float) $this->records->sum('net_pay');
    }
}
