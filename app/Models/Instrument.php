<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Instrument extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_code',
        'name',
        'category',
        'description',
        'stock_quantity',
        'unit',
        'reorder_level',
        'storage_location',
        'status',
    ];

    public static function generateItemCode(string $prefix = 'INST'): string
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

    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0;
    }

    public function computeStatus(): string
    {
        if ($this->stock_quantity <= 0) {
            return 'out_of_stock';
        }
        if ($this->stock_quantity <= $this->reorder_level) {
            return 'low_stock';
        }
        return 'in_stock';
    }

    public function restockLogs()
    {
        return $this->hasMany(InstrumentRestockLog::class)->latest();
    }
}
