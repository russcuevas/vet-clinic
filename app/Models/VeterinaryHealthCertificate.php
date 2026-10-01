<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class VeterinaryHealthCertificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'control_number',
        'certificate_date',
        'owner_id',
        'pet_id',
        'veterinarian_id',
        'owner_name',
        'residing_at',
        'contact_number',
        'destination',
        'pet_name',
        'species',
        'breed',
        'color',
        'sex',
        'birth_date',
        'age',
        'weight',
        'microchip',
        'rabies_vaccination_date',
        'rabies_vaccine_name',
        'rabies_lot_number',
        'veterinarian_name',
        'tin_no',
        'ptr_no',
        'prc_no',
        'license_expiry_date',
        'notes',
    ];

    protected $casts = [
        'certificate_date' => 'date',
        'birth_date' => 'date',
        'rabies_vaccination_date' => 'date',
        'license_expiry_date' => 'date',
    ];

    /**
     * Sequence format: 2 digit year - 0000 counter increment 1 (e.g. 26-0001, 26-0019)
     */
    public static function generateControlNumber(?Carbon $date = null): string
    {
        $year2 = ($date ?: Carbon::now())->format('y'); // e.g. "26"
        $prefix = "{$year2}-";

        $latest = self::where('control_number', 'LIKE', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($latest && preg_match('/^(\d{2})-(\d{4})$/', $latest->control_number, $matches)) {
            $num = intval($matches[2]) + 1;
        } else {
            $num = 1;
        }

        return sprintf("%s-%04d", $year2, $num);
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
