<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_code',
        'name',
        'category',
        'description',
        'stock_quantity',
        'unit',
        'unit_price',
        'cost_price',
        'reorder_level',
        'image',
    ];

    public static function generateItemCode(string $prefix = 'ITM'): string
    {
        $latest = self::where('item_code', 'LIKE', "{$prefix}-%")->latest('id')->first();
        if ($latest) {
            $parts = explode('-', $latest->item_code);
            $num = intval(end($parts)) + 1;
        } else {
            $num = 1;
        }
        return sprintf("%s-%04d", $prefix, $num);
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->reorder_level;
    }

    public function billItems()
    {
        return $this->hasMany(BillItem::class);
    }
}
