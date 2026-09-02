<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'inventory_item_id',
        'item_name',
        'item_type', // service, product, medicine, grooming
        'quantity',
        'unit_price',
        'total_price',
    ];

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
