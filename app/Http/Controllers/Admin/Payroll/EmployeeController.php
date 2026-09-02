<?php

namespace App\Http\Controllers\Admin\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::query()->with('user');

        if ($request->filled('position')) {
            $query->where('position', $request->position);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                    ->orWhere('last_name', 'like', "%{$s}%")
                    ->orWhere('employee_code', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%");
            });
        }

        $employees = $query->latest()->paginate(15)->withQueryString();
        $positions = Employee::distinct()->pluck('position')->filter();
        $users = User::where('status', 'active')->orderBy('name')->get();

        return view('admin.payroll.employees.index', compact('employees', 'positions', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:50',
            'position' => 'required|string|max:100',
            'department' => 'nullable|string|max:100',
            'employment_type' => 'required|in:full_time,part_time,contract',
            'basic_salary' => 'required|numeric|min:0',
            'daily_rate' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'sss_no' => 'nullable|string|max:50',
            'philhealth_no' => 'nullable|string|max:50',
            'pagibig_no' => 'nullable|string|max:50',
            'tin_no' => 'nullable|string|max:50',
            'date_hired' => 'nullable|date',
            'user_id' => 'nullable|exists:users,id',
            'status' => 'required|in:active,inactive,on_leave',
        ]);

        $basic = floatval($validated['basic_salary']);
        if (empty($validated['daily_rate']) || floatval($validated['daily_rate']) == 0) {
            $validated['daily_rate'] = round($basic / 22, 2);
        }
        if (empty($validated['hourly_rate']) || floatval($validated['hourly_rate']) == 0) {
            $validated['hourly_rate'] = round(floatval($validated['daily_rate']) / 8, 2);
        }

        $validated['employee_code'] = Employee::generateEmployeeCode();
        $validated['department'] = $validated['department'] ?? 'Operations';

        Employee::create($validated);

        return redirect()->route('admin.payroll.employees.index')->with('success', "Employee {$validated['first_name']} {$validated['last_name']} successfully registered!");
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:50',
            'position' => 'required|string|max:100',
            'department' => 'nullable|string|max:100',
            'employment_type' => 'required|in:full_time,part_time,contract',
            'basic_salary' => 'required|numeric|min:0',
            'daily_rate' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'sss_no' => 'nullable|string|max:50',
            'philhealth_no' => 'nullable|string|max:50',
            'pagibig_no' => 'nullable|string|max:50',
            'tin_no' => 'nullable|string|max:50',
            'date_hired' => 'nullable|date',
            'user_id' => 'nullable|exists:users,id',
            'status' => 'required|in:active,inactive,on_leave',
        ]);

        $basic = floatval($validated['basic_salary']);
        if (empty($validated['daily_rate']) || floatval($validated['daily_rate']) == 0) {
            $validated['daily_rate'] = round($basic / 22, 2);
        }
        if (empty($validated['hourly_rate']) || floatval($validated['hourly_rate']) == 0) {
            $validated['hourly_rate'] = round(floatval($validated['daily_rate']) / 8, 2);
        }

        $employee->update($validated);

        return redirect()->route('admin.payroll.employees.index')->with('success', "Employee record updated successfully!");
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->route('admin.payroll.employees.index')->with('success', "Employee deleted successfully.");
    }
}
