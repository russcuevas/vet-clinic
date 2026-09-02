<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Owner extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_code',
        'full_name',
        'contact_number',
        'address',
        'email',
        'status',
    ];

    public static function generateClientCode(): string
    {
        $year = date('Y');
        $latest = self::where('client_code', 'LIKE', "OWN-{$year}-%")->latest('id')->first();
        if ($latest) {
            $parts = explode('-', $latest->client_code);
            $num = intval(end($parts)) + 1;
        } else {
            $num = 1;
        }
        return sprintf("OWN-%s-%04d", $year, $num);
    }

    public function pets()
    {
        return $this->hasMany(Pet::class);
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function groomingRecords()
    {
        return $this->hasMany(GroomingRecord::class);
    }

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }
}
