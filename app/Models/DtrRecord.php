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

    public static function formatMinutesToHuman(int $minutes): string
    {
        $minutes = abs($minutes);
        if ($minutes === 0) {
            return '0 mins';
        }

        $hours = intdiv($minutes, 60);
        $remMinutes = $minutes % 60;

        if ($hours > 0 && $remMinutes > 0) {
            $hrLabel = $hours === 1 ? '1 hr' : "{$hours} hrs";
            $minLabel = $remMinutes === 1 ? '1 min' : "{$remMinutes} mins";
            return "{$hrLabel} and {$minLabel}";
        } elseif ($hours > 0) {
            return $hours === 1 ? '1 hr' : "{$hours} hrs";
        } else {
            return $remMinutes === 1 ? '1 min' : "{$remMinutes} mins";
        }
    }

    public function getFormattedLateAttribute(): string
    {
        return self::formatMinutesToHuman($this->late_minutes ?? 0);
    }

    public function getFormattedUndertimeAttribute(): string
    {
        return self::formatMinutesToHuman($this->undertime_minutes ?? 0);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
