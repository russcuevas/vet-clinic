<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstrumentRestockLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'instrument_id',
        'user_id',
        'quantity_added',
        'quantity_before',
        'quantity_after',
        'action_type',
        'remarks',
    ];

    public function instrument()
    {
        return $this->belongsTo(Instrument::class);
    }

    public function restockedBy()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
