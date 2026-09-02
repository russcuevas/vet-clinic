<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no',
        'owner_id',
        'pet_id',
        'cashier_id',
        'medical_record_id',
        'grooming_record_id',
        'client_name',
        'service_type', // veterinary, grooming, pet_supplies, combined
        'subtotal',
        'discount',
        'tax',
        'total_amount',
        'paid_amount',
        'change_amount',
        'payment_method',
        'payment_status', // unpaid, paid, refunded, cancelled
        'transaction_date',
        'notes',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
    ];

    public static function generateInvoiceNo(): string
    {
        $year = date('Y');
        $latest = self::where('invoice_no', 'LIKE', "INV-{$year}-%")->latest('id')->first();
        if ($latest) {
            $parts = explode('-', $latest->invoice_no);
            $num = intval(end($parts)) + 1;
        } else {
            $num = 1;
        }
        return sprintf("INV-%s-%04d", $year, $num);
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function groomingRecord()
    {
        return $this->belongsTo(GroomingRecord::class);
    }

    public function items()
    {
        return $this->hasMany(BillItem::class);
    }
}
