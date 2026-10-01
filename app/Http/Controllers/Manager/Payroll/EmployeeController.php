<?php

namespace App\Http\Controllers\Manager\Payroll;

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
        $definedPositions = Employee::POSITIONS;
        $dbPositions = Employee::distinct()->pluck('position')->filter()->toArray();
        $positions = array_values(array_unique(array_merge($definedPositions, $dbPositions)));
        $users = User::where('status', 'active')->orderBy('name')->get();

        return view('manager.payroll.employees.index', compact('employees', 'positions', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|string|max:20',
            'civil_status' => 'nullable|string|max:30',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:50',
            'education' => 'nullable|string|max:150',
            'position' => 'required|string|max:100',
            'shift_start' => 'nullable|string|max:10',
            'shift_end' => 'nullable|string|max:10',
            'department' => 'nullable|string|max:100',
            'employment_type' => 'required|string|max:50',
            'basic_salary' => 'required|numeric|min:0',
            'divisor_days' => 'nullable|integer|min:1',
            'rest_days_per_week' => 'nullable|integer|min:0|max:7',
            'daily_rate' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'sss_no' => 'nullable|string|max:50',
            'philhealth_no' => 'nullable|string|max:50',
            'pagibig_no' => 'nullable|string|max:50',
            'tin_no' => 'nullable|string|max:50',
            'drivers_license_no' => 'nullable|string|max:50',
            'previous_employer' => 'nullable|string|max:150',
            'previous_position' => 'nullable|string|max:100',
            'previous_salary' => 'nullable|numeric|min:0',
            'years_of_experience' => 'nullable|string|max:50',
            'date_hired' => 'nullable|date',
            'user_id' => 'nullable|exists:users,id',
            'status' => 'required|in:active,inactive,on_leave',
            'police_clearance_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'medical_certificate_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'sss_id_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'philhealth_id_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'pagibig_id_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'drivers_license_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'other_doc_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $isVet = stripos($validated['position'], 'vet') !== false || stripos($validated['department'] ?? '', 'vet') !== false;

        // Non-vet has 1 restday -> 26 days divisor; Vet has 2 restdays -> 22 days divisor
        $divisor = !empty($validated['divisor_days']) ? intval($validated['divisor_days']) : ($isVet ? 22 : 26);
        $restDays = isset($validated['rest_days_per_week']) ? intval($validated['rest_days_per_week']) : ($isVet ? 2 : 1);

        $validated['divisor_days'] = $divisor;
        $validated['rest_days_per_week'] = $restDays;
        $validated['shift_start'] = $validated['shift_start'] ?? '09:00';
        $validated['shift_end'] = $validated['shift_end'] ?? '18:00';

        $basic = floatval($validated['basic_salary']);
        if (empty($validated['daily_rate']) || floatval($validated['daily_rate']) == 0) {
            $validated['daily_rate'] = round($basic / $divisor, 2);
        }
        if (empty($validated['hourly_rate']) || floatval($validated['hourly_rate']) == 0) {
            $validated['hourly_rate'] = round(floatval($validated['daily_rate']) / 8, 2);
        }

        $validated['employee_code'] = Employee::generateEmployeeCode();
        $validated['department'] = $validated['department'] ?? ($isVet ? 'Veterinary' : 'Operations');

        // Handle Direct Document Uploads in public/uploads/employee_docs
        $docFields = [
            'police_clearance_file',
            'medical_certificate_file',
            'sss_id_file',
            'philhealth_id_file',
            'pagibig_id_file',
            'drivers_license_file',
            'other_doc_file',
        ];

        $uploadDir = public_path('uploads/employee_docs');
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        foreach ($docFields as $docField) {
            if ($request->hasFile($docField)) {
                $file = $request->file($docField);
                $filename = time() . '_' . $docField . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
                $file->move($uploadDir, $filename);
                $validated[$docField] = 'uploads/employee_docs/' . $filename;
            }
        }

        Employee::create($validated);

        return redirect()->route('manager.payroll.employees.index')->with('success', "Employee {$validated['first_name']} {$validated['last_name']} successfully registered!");
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|string|max:20',
            'civil_status' => 'nullable|string|max:30',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:50',
            'education' => 'nullable|string|max:150',
            'position' => 'required|string|max:100',
            'shift_start' => 'nullable|string|max:10',
            'shift_end' => 'nullable|string|max:10',
            'department' => 'nullable|string|max:100',
            'employment_type' => 'required|string|max:50',
            'basic_salary' => 'required|numeric|min:0',
            'divisor_days' => 'nullable|integer|min:1',
            'rest_days_per_week' => 'nullable|integer|min:0|max:7',
            'daily_rate' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'sss_no' => 'nullable|string|max:50',
            'philhealth_no' => 'nullable|string|max:50',
            'pagibig_no' => 'nullable|string|max:50',
            'tin_no' => 'nullable|string|max:50',
            'drivers_license_no' => 'nullable|string|max:50',
            'previous_employer' => 'nullable|string|max:150',
            'previous_position' => 'nullable|string|max:100',
            'previous_salary' => 'nullable|numeric|min:0',
            'years_of_experience' => 'nullable|string|max:50',
            'date_hired' => 'nullable|date',
            'user_id' => 'nullable|exists:users,id',
            'status' => 'required|in:active,inactive,on_leave',
            'police_clearance_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'medical_certificate_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'sss_id_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'philhealth_id_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'pagibig_id_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'drivers_license_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'other_doc_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $isVet = stripos($validated['position'], 'vet') !== false || stripos($validated['department'] ?? '', 'vet') !== false;
        $divisor = !empty($validated['divisor_days']) ? intval($validated['divisor_days']) : ($isVet ? 22 : 26);
        $restDays = isset($validated['rest_days_per_week']) ? intval($validated['rest_days_per_week']) : ($isVet ? 2 : 1);

        $validated['divisor_days'] = $divisor;
        $validated['rest_days_per_week'] = $restDays;
        $validated['shift_start'] = $validated['shift_start'] ?? ($employee->shift_start ?? '09:00');
        $validated['shift_end'] = $validated['shift_end'] ?? ($employee->shift_end ?? '18:00');

        $basic = floatval($validated['basic_salary']);
        if (empty($validated['daily_rate']) || floatval($validated['daily_rate']) == 0) {
            $validated['daily_rate'] = round($basic / $divisor, 2);
        }
        if (empty($validated['hourly_rate']) || floatval($validated['hourly_rate']) == 0) {
            $validated['hourly_rate'] = round(floatval($validated['daily_rate']) / 8, 2);
        }

        // Handle Document Uploads in public/uploads/employee_docs
        $docFields = [
            'police_clearance_file',
            'medical_certificate_file',
            'sss_id_file',
            'philhealth_id_file',
            'pagibig_id_file',
            'drivers_license_file',
            'other_doc_file',
        ];

        $uploadDir = public_path('uploads/employee_docs');
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        foreach ($docFields as $docField) {
            if ($request->hasFile($docField)) {
                $file = $request->file($docField);
                $filename = time() . '_' . $docField . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
                $file->move($uploadDir, $filename);
                $validated[$docField] = 'uploads/employee_docs/' . $filename;
            }
        }

        $employee->update($validated);

        return redirect()->route('manager.payroll.employees.index')->with('success', "Employee record updated successfully!");
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->route('manager.payroll.employees.index')->with('success', "Employee deleted successfully.");
    }
}
