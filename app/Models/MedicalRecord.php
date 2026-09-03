<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'record_code',
        'owner_id',
        'pet_id',
        'veterinarian_id',
        'service_type', // consultation, follow_up, wellness
        'visit_date',
        'body_weight',
        'temperature',
        'body_score',
        'history_taking',
        'attached_lab_results',
        'laboratory_notes',
        'diagnosis',
        'medication_treatment',
        'veterinarians_notes',
        'service_fee',
        'follow_up_date',
        'follow_up_notes',
        'status', // ongoing, completed, billed
    ];

    protected $casts = [
        'visit_date' => 'date',
        'follow_up_date' => 'date',
    ];

    public static function generateRecordCode(): string
    {
        $year = date('Y');
        $latest = self::where('record_code', 'LIKE', "MED-{$year}-%")->latest('id')->first();
        if ($latest) {
            $parts = explode('-', $latest->record_code);
            $num = intval(end($parts)) + 1;
        } else {
            $num = 1;
        }
        return sprintf("MED-%s-%04d", $year, $num);
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

    public function prescription()
    {
        return $this->hasOne(Prescription::class);
    }

    public function bill()
    {
        return $this->hasOne(Bill::class);
    }
}
