<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'deduction_type',
        'title',
        'total_amount',
        'monthly_amortization',
        'remaining_balance',
        'effective_date',
        'status',
        'remarks',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'monthly_amortization' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'effective_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
