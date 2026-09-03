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
}
