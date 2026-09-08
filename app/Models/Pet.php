<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pet extends Model
{
    use HasFactory;

    protected $fillable = [
        'pet_code',
        'owner_id',
        'name',
        'species',
        'breed',
        'age',
        'sex',
        'color',
        'birth_date',
        'photo',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function getAgeAttribute($value)
    {
        if (!empty($value) && $value !== 'Not specified' && $value !== 'N/A') {
            return $value;
        }

        if ($this->birth_date) {
            $diff = $this->birth_date->diff(now());
            if ($diff->y >= 2) {
                return $diff->m > 0 ? "{$diff->y} yrs, {$diff->m} mos" : "{$diff->y} yrs old";
            } elseif ($diff->y === 1) {
                return $diff->m > 0 ? "1 yr, {$diff->m} mos" : "1 yr old";
            } elseif ($diff->m >= 1) {
                return $diff->m > 1 ? "{$diff->m} months old" : "1 month old";
            } elseif ($diff->d >= 7) {
                $weeks = floor($diff->d / 7);
                return $weeks > 1 ? "{$weeks} weeks old" : "1 week old";
            } else {
                return $diff->d > 1 ? "{$diff->d} days old" : ($diff->d === 1 ? "1 day old" : "Newborn");
            }
        }

        return $value ?: 'Not specified';
    }

    public static function generatePetCode(): string
    {
        $year = date('Y');
        $latest = self::where('pet_code', 'LIKE', "PET-{$year}-%")->latest('id')->first();
        if ($latest) {
            $parts = explode('-', $latest->pet_code);
            $num = intval(end($parts)) + 1;
        } else {
            $num = 1;
        }
        return sprintf("PET-%s-%04d", $year, $num);
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    public function groomingRecords()
    {
        return $this->hasMany(GroomingRecord::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
