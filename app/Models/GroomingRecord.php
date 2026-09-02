<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroomingRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'grooming_code',
        'owner_id',
        'pet_id',
        'groomer_id',
        'body_weight',
        'temperature',
        'body_score',
        'style',
        'groomer_observation_notes',
        'price',
        'status', // queued, in_progress, completed, billed
    ];

    public static function generateGroomingCode(): string
    {
        $year = date('Y');
        $latest = self::where('grooming_code', 'LIKE', "GRM-{$year}-%")->latest('id')->first();
        if ($latest) {
            $parts = explode('-', $latest->grooming_code);
            $num = intval(end($parts)) + 1;
        } else {
            $num = 1;
        }
        return sprintf("GRM-%s-%04d", $year, $num);
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }

    public function groomer()
    {
        return $this->belongsTo(Employee::class, 'groomer_id');
    }

    public function bill()
    {
        return $this->hasOne(Bill::class);
    }
}
