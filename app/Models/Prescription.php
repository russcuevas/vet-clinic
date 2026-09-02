<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'prescription_code',
        'medical_record_id',
        'owner_id',
        'pet_id',
        'veterinarian_id',
        'veterinarian_name',
        'license_no',
        'body_weight',
        'rx_details',
        'instructions',
        'date_issued',
    ];

    protected $casts = [
        'date_issued' => 'date',
    ];

    public static function generatePrescriptionCode(): string
    {
        $year = date('Y');
        $latest = self::where('prescription_code', 'LIKE', "RX-{$year}-%")->latest('id')->first();
        if ($latest) {
            $parts = explode('-', $latest->prescription_code);
            $num = intval(end($parts)) + 1;
        } else {
            $num = 1;
        }
        return sprintf("RX-%s-%04d", $year, $num);
    }

    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }

    public function veterinarian()
    {
        return $this->belongsTo(User::class, 'veterinarian_id');
    }
}
