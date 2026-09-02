<?php

namespace App\Http\Controllers\Admin\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Deduction;
use Illuminate\Http\Request;

class DeductionController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type');
        $status = $request->query('status', 'active');
        $employeeId = $request->query('employee_id');

        $query = Deduction::with('employee')->latest();

        if ($type) {
            $query->where('deduction_type', $type);
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $deductions = $query->paginate(15)->withQueryString();
        $employees = Employee::where('status', 'active')->orderBy('first_name')->get();

        $totalActiveLoans = Deduction::where('status', 'active')->where('deduction_type', 'loan')->sum('remaining_balance');
        $totalActiveCashAdvance = Deduction::where('status', 'active')->where('deduction_type', 'cash_advance')->sum('remaining_balance');

        return view('admin.payroll.deductions.index', compact('deductions', 'employees', 'totalActiveLoans', 'totalActiveCashAdvance'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'deduction_type' => 'required|in:loan,cash_advance,sss,philhealth,pagibig,tax,tardiness_absence,other',
            'title' => 'required|string|max:150',
            'total_amount' => 'required|numeric|min:0.01',
            'monthly_amortization' => 'nullable|numeric|min:0',
            'effective_date' => 'required|date',
            'remarks' => 'nullable|string|max:500',
        ]);

        $total = floatval($validated['total_amount']);
        $amortization = !empty($validated['monthly_amortization']) && floatval($validated['monthly_amortization']) > 0
            ? floatval($validated['monthly_amortization'])
            : $total;

        Deduction::create([
            'employee_id' => $validated['employee_id'],
            'deduction_type' => $validated['deduction_type'],
            'title' => $validated['title'],
            'total_amount' => $total,
            'monthly_amortization' => $amortization,
            'remaining_balance' => $total,
            'effective_date' => $validated['effective_date'],
            'status' => 'active',
            'remarks' => $validated['remarks'] ?? null,
        ]);

        return redirect()->route('admin.payroll.deductions.index')->with('success', 'Deduction / Financial account logged successfully!');
    }

    public function update(Request $request, Deduction $deduction)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'monthly_amortization' => 'required|numeric|min:0',
            'remaining_balance' => 'required|numeric|min:0',
            'status' => 'required|in:active,completed,cancelled',
            'remarks' => 'nullable|string|max:500',
        ]);

        if (floatval($validated['remaining_balance']) <= 0) {
            $validated['status'] = 'completed';
        }

        $deduction->update($validated);

        return redirect()->route('admin.payroll.deductions.index')->with('success', 'Deduction record updated successfully!');
    }

    public function destroy(Deduction $deduction)
    {
        $deduction->delete();
        return redirect()->route('admin.payroll.deductions.index')->with('success', 'Deduction deleted.');
    }
}
