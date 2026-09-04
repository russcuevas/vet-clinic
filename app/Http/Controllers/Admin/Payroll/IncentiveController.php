<?php

namespace App\Http\Controllers\Admin\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\IncentiveRule;
use App\Models\EmployeeIncentive;
use App\Models\GroomingRecord;
use App\Models\MedicalRecord;
use App\Models\DtrRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;

class IncentiveController extends Controller
{
    public function index(Request $request)
    {
        $employeeId = $request->query('employee_id');
        $month = $request->query('month', Carbon::now()->month);
        $year = $request->query('year', Carbon::now()->year);

        // Incentive Rules
        $rules = IncentiveRule::orderBy('role_name')->get();

        // Employee Incentives Log
        $query = EmployeeIncentive::with(['employee', 'rule'])->latest('date_earned');

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        if ($month && $year) {
            $query->whereMonth('date_earned', $month)->whereYear('date_earned', $year);
        }

        $incentives = $query->paginate(15)->withQueryString();
        $employees = Employee::where('status', 'active')->orderBy('first_name')->get();

        $totalIncentivesPaid = EmployeeIncentive::whereMonth('date_earned', $month)
            ->whereYear('date_earned', $year)
            ->sum('total_incentive');

        return view('admin.payroll.incentives.index', compact(
            'rules',
            'incentives',
            'employees',
            'month',
            'year',
            'totalIncentivesPaid',
            'employeeId'
        ));
    }

    /**
     * Update default role rules (e.g. Groomer 10% / 20%, Vet P200, Janitor P35)
     */
    public function updateRule(Request $request, IncentiveRule $rule)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'scheme_type' => 'required|in:percentage,fixed_per_unit',
            'default_rate' => 'required|numeric|min:0',
            'threshold_count' => 'nullable|integer|min:0',
            'tier2_rate' => 'nullable|numeric|min:0',
            'unit_label' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['threshold_count'] = $validated['threshold_count'] ?? 0;

        $rule->update($validated);

        return redirect()->route('admin.payroll.incentives.index')->with('success', "Incentive rule '{$rule->title}' successfully updated!");
    }

    /**
     * Store new Incentive Rule
     */
    public function storeRule(Request $request)
    {
        $validated = $request->validate([
            'role_name' => 'required|string|max:100',
            'title' => 'required|string|max:150',
            'scheme_type' => 'required|in:percentage,fixed_per_unit',
            'default_rate' => 'required|numeric|min:0',
            'threshold_count' => 'nullable|integer|min:0',
            'tier2_rate' => 'nullable|numeric|min:0',
            'unit_label' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
        ]);

        $validated['threshold_count'] = $validated['threshold_count'] ?? 0;
        $validated['is_active'] = true;

        IncentiveRule::create($validated);

        return redirect()->route('admin.payroll.incentives.index')->with('success', 'New incentive scheme created successfully!');
    }

    /**
     * Record / Add Incentive to an Employee with fully editable percentage and base amount
     */
    public function storeEmployeeIncentive(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'incentive_rule_id' => 'nullable|exists:incentive_rules,id',
            'title' => 'required|string|max:150',
            'calculation_basis' => 'required|in:percentage,fixed_per_unit',
            'rate_applied' => 'required|numeric|min:0', // Percentage (e.g. 10 or 20) or fixed amount (e.g. 200 or 35)
            'base_amount_or_count' => 'required|numeric|min:0', // Sales amount or pet count
            'total_incentive' => 'required|numeric|min:0', // Fully editable total computed
            'date_earned' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        EmployeeIncentive::create($validated);

        return redirect()->route('admin.payroll.incentives.index', [
            'month' => Carbon::parse($validated['date_earned'])->month,
            'year' => Carbon::parse($validated['date_earned'])->year,
        ])->with('success', 'Incentive successfully recorded for employee!');
    }

    /**
     * Quick helper endpoint (JSON) to suggest count/sales from clinic records
     */
    public function suggestStats(Request $request)
    {
        $employeeId = $request->query('employee_id');
        $month = $request->query('month', Carbon::now()->month);
        $year = $request->query('year', Carbon::now()->year);

        $employee = Employee::with('user')->find($employeeId);
        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Employee not found']);
        }

        $position = strtolower($employee->position);
        $count = 0;
        $totalSales = 0;
        $suggestedRate = 10;
        $tier = 'tier1';
        $basis = 'percentage';

        // Check if Groomer (Strictly for staff with 'Groomer' role)
        if (str_contains($position, 'groom')) {
            // 1. Count ONLY pets handled by staff with GROOMER role (exclude Admin, Vet, Cashier, etc.)
            $groomerEmployeeIds = Employee::where('status', 'active')
                ->where('position', 'like', '%groom%')
                ->pluck('id');

            $teamTotalPets = GroomingRecord::whereIn('groomer_id', $groomerEmployeeIds)
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->count();

            // 2. Count ONLY this specific groomer's individual pet count and sales
            $individualQuery = GroomingRecord::where('groomer_id', $employee->id)
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year);

            $count = $individualQuery->count();
            $totalSales = (float) $individualQuery->sum('price');

            // 3. Find incentive rule
            $rule = IncentiveRule::where('role_name', 'like', '%groom%')->first();
            $tier1Rate = $rule ? $rule->default_rate : 10.00;
            $threshold = $rule ? $rule->threshold_count : 100;
            $tier2Rate = $rule ? ($rule->tier2_rate ?: 20.00) : 20.00;

            // Combined threshold check: Groomer 1 + Groomer 2 >= 100
            if ($teamTotalPets >= $threshold && $threshold > 0) {
                $suggestedRate = $tier2Rate; // 20%
                $tier = "Team Target Met ({$teamTotalPets}/{$threshold} pets >= 100) -> 20% Bonus Tier!";
            } else {
                $suggestedRate = $tier1Rate; // 10%
                $tier = "Team Target: {$teamTotalPets}/{$threshold} pets -> Base {$tier1Rate}% Tier";
            }
            $basis = 'percentage';

            return response()->json([
                'success' => true,
                'position' => $employee->position,
                'count' => $count,
                'team_count' => $teamTotalPets,
                'threshold' => $threshold,
                'total_sales' => $totalSales,
                'suggested_rate' => $suggestedRate,
                'basis' => $basis,
                'tier_info' => $tier,
            ]);
        }
        // Check if Vet
        elseif (str_contains($position, 'vet')) {
            $vetUserId = $employee->user_id;
            if ($vetUserId) {
                $query = MedicalRecord::where('veterinarian_id', $vetUserId)
                    ->whereMonth('created_at', $month)
                    ->whereYear('created_at', $year);

                $count = $query->count();
                $totalSales = (float) $query->sum('service_fee');
            } else {
                $count = 0;
                $totalSales = 0;
            }

            $rule = IncentiveRule::where('role_name', 'like', '%vet%')->first();
            $suggestedRate = $rule ? $rule->default_rate : 200.00;
            $basis = 'fixed_per_unit';
            $tier = "Consultations: {$count}";
        }
        // Janitor / Staff boarding
        elseif (str_contains($position, 'janitor') || str_contains($position, 'kennel') || str_contains($position, 'utility')) {
            $rule = IncentiveRule::where('role_name', 'like', '%janitor%')->first();
            $suggestedRate = $rule ? $rule->default_rate : 35.00;
            $basis = 'fixed_per_unit';

            // Dynamically pulled from DTR: number of active duty days maintaining clinic kennels/boarding
            $dtrDays = DtrRecord::where('employee_id', $employee->id)
                ->whereMonth('record_date', $month)
                ->whereYear('record_date', $year)
                ->whereIn('status', ['present', 'late'])
                ->count();

            $count = $dtrDays;
            $tier = "DTR Kennel Duty: {$count} days worked";
        } else {
            $rule = IncentiveRule::where('role_name', 'like', "%{$position}%")->first();
            if ($rule) {
                $suggestedRate = $rule->default_rate;
                $basis = $rule->scheme_type;
            }
        }

        return response()->json([
            'success' => true,
            'position' => $employee->position,
            'count' => $count,
            'total_sales' => $totalSales,
            'suggested_rate' => $suggestedRate,
            'basis' => $basis,
            'tier_info' => $tier,
        ]);
    }

    public function destroy(EmployeeIncentive $incentive)
    {
        $incentive->delete();
        return redirect()->back()->with('success', 'Incentive entry deleted.');
    }
}
