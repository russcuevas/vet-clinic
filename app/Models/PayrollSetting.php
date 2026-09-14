<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'multiplier',
        'description',
        'updated_by',
    ];

    protected $casts = [
        'multiplier' => 'float',
    ];

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get multiplier value by key with optional fallback.
     */
    public static function getMultiplier(string $key, float $default = 1.00): float
    {
        $setting = static::where('key', $key)->first();
        return $setting ? (float) $setting->multiplier : $default;
    }

    /**
     * Update or create a multiplier setting.
     */
    public static function setMultiplier(string $key, float $multiplier, ?int $userId = null): self
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'multiplier' => $multiplier,
                'updated_by' => $userId,
            ]
        );
    }
}
