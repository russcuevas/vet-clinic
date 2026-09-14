<?php

namespace App\Http\Controllers\Manager\Payroll;

use App\Http\Controllers\Controller;
use App\Models\PayrollSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function updateMultipliers(Request $request)
    {
        $validated = $request->validate([
            'holiday_multiplier' => 'required|numeric|min:0|max:10',
            'special_holiday_multiplier' => 'required|numeric|min:0|max:10',
            'rest_day_multiplier' => 'required|numeric|min:0|max:10',
            'overtime_multiplier' => 'required|numeric|min:0|max:10',
        ]);

        $userId = Auth::id();

        PayrollSetting::setMultiplier('holiday_multiplier', (float) $validated['holiday_multiplier'], $userId);
        PayrollSetting::setMultiplier('special_holiday_multiplier', (float) $validated['special_holiday_multiplier'], $userId);
        PayrollSetting::setMultiplier('rest_day_multiplier', (float) $validated['rest_day_multiplier'], $userId);
        PayrollSetting::setMultiplier('overtime_multiplier', (float) $validated['overtime_multiplier'], $userId);

        return redirect()->back()->with('success', 'Payroll calculation multipliers updated successfully in the database!');
    }
}
