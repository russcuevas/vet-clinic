<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_code',
        'service_category',
        'service_type',
        'owner_id',
        'pet_id',
        'booked_by',
        'veterinarian_id',
        'appointment_date',
        'appointment_time',
        'purpose_examination_notes',
        'status',
        'is_new_client',
        'is_new_pet',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'is_new_client' => 'boolean',
        'is_new_pet' => 'boolean',
    ];

    public static function generateAppointmentCode(): string
    {
        $year = date('Y');
        $latest = self::where('appointment_code', 'LIKE', "APT-{$year}-%")->latest('id')->first();
        if ($latest) {
            $parts = explode('-', $latest->appointment_code);
            $num = intval(end($parts)) + 1;
        } else {
            $num = 1;
        }
        return sprintf("APT-%s-%04d", $year, $num);
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }

    public function bookedBy()
    {
        return $this->belongsTo(User::class, 'booked_by');
    }

    public function veterinarian()
    {
        return $this->belongsTo(User::class, 'veterinarian_id');
    }
}
