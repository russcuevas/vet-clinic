<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncentiveRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_name',
        'title',
        'scheme_type',
        'default_rate',
        'threshold_count',
        'tier2_rate',
        'unit_label',
        'description',
        'is_active',
    ];

    protected $casts = [
        'default_rate' => 'decimal:2',
        'tier2_rate' => 'decimal:2',
        'threshold_count' => 'integer',
        'is_active' => 'boolean',
    ];

    public function employeeIncentives()
    {
        return $this->hasMany(EmployeeIncentive::class);
    }
}
