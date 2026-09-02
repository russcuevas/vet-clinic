<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DtrRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'record_date',
        'time_in',
        'time_out',
        'regular_hours',
        'late_minutes',
        'undertime_minutes',
        'ot_hours',
        'status',
        'notes',
    ];

    protected $casts = [
        'record_date' => 'date',
        'regular_hours' => 'decimal:2',
        'ot_hours' => 'decimal:2',
        'late_minutes' => 'integer',
        'undertime_minutes' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
