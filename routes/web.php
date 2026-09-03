<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

/*
|--------------------------------------------------------------------------
| Web Routes - San Modesto Vet Clinic
| Role-based Separation of Concerns: Admin, Cashier, Veterinarian, Manager
|--------------------------------------------------------------------------
*/

// Public Authentication Routes
Route::get('/', function () {
    if (auth()->check()) {
        return match (auth()->user()->role) {
            'cashier' => redirect()->route('cashier.dashboard'),
            'veterinarian' => redirect()->route('vet.dashboard'),
            'manager' => redirect()->route('manager.dashboard'),
            default => redirect()->route('admin.dashboard'),
        };
    }
    return redirect()->route('login');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update')->middleware('auth');

// ==========================================
// 1. ADMIN MODULE (Full Clinic Access)
// ==========================================
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['auth', 'role:admin']], function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // Clients & Pets (Owner & Pet Information Database)
    Route::get('/clients', [\App\Http\Controllers\Admin\ClientController::class, 'index'])->name('clients.index');
    Route::post('/clients', [\App\Http\Controllers\Admin\ClientController::class, 'store'])->name('clients.store');
    Route::put('/clients/{owner}', [\App\Http\Controllers\Admin\ClientController::class, 'update'])->name('clients.update');
    Route::delete('/clients/{owner}', [\App\Http\Controllers\Admin\ClientController::class, 'destroy'])->name('clients.destroy');

    Route::post('/pets', [\App\Http\Controllers\Admin\PetController::class, 'store'])->name('pets.store');
    Route::put('/pets/{pet}', [\App\Http\Controllers\Admin\PetController::class, 'update'])->name('pets.update');
    Route::delete('/pets/{pet}', [\App\Http\Controllers\Admin\PetController::class, 'destroy'])->name('pets.destroy');

    // Veterinary Services (Consultation, Follow up, Wellness, Medical Records)
    Route::get('/veterinary', [\App\Http\Controllers\Admin\VeterinaryController::class, 'index'])->name('veterinary.index');
    Route::post('/veterinary', [\App\Http\Controllers\Admin\VeterinaryController::class, 'store'])->name('veterinary.store');
    Route::put('/veterinary/{record}', [\App\Http\Controllers\Admin\VeterinaryController::class, 'update'])->name('veterinary.update');
    Route::delete('/veterinary/{record}', [\App\Http\Controllers\Admin\VeterinaryController::class, 'destroy'])->name('veterinary.destroy');

    // Admin Prescriptions (Linked to Clinical Examinations)
    Route::post('/prescriptions', [\App\Http\Controllers\Admin\PrescriptionController::class, 'store'])->name('prescriptions.store');
    Route::put('/prescriptions/{prescription}', [\App\Http\Controllers\Admin\PrescriptionController::class, 'update'])->name('prescriptions.update');
    Route::get('/prescriptions/{prescription}/print', [\App\Http\Controllers\Admin\PrescriptionController::class, 'print'])->name('prescriptions.print');

    // Grooming Module (Grooming Database)
    Route::get('/grooming', [\App\Http\Controllers\Admin\GroomingController::class, 'index'])->name('grooming.index');
    Route::post('/grooming', [\App\Http\Controllers\Admin\GroomingController::class, 'store'])->name('grooming.store');
    Route::put('/grooming/{grooming}', [\App\Http\Controllers\Admin\GroomingController::class, 'update'])->name('grooming.update');
    Route::delete('/grooming/{grooming}', [\App\Http\Controllers\Admin\GroomingController::class, 'destroy'])->name('grooming.destroy');

    // Pet Supplies POS
    Route::get('/supplies', [\App\Http\Controllers\Admin\SupplyController::class, 'index'])->name('supplies.index');
    Route::post('/supplies', [\App\Http\Controllers\Admin\SupplyController::class, 'store'])->name('supplies.store');

    // Inventory Management
    Route::get('/inventory', [\App\Http\Controllers\Admin\InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory', [\App\Http\Controllers\Admin\InventoryController::class, 'store'])->name('inventory.store');
    Route::put('/inventory/{item}', [\App\Http\Controllers\Admin\InventoryController::class, 'update'])->name('inventory.update');
    Route::post('/inventory/{item}/restock', [\App\Http\Controllers\Admin\InventoryController::class, 'restock'])->name('inventory.restock');
    Route::delete('/inventory/{item}', [\App\Http\Controllers\Admin\InventoryController::class, 'destroy'])->name('inventory.destroy');

    // Central Billing Database
    Route::get('/billing', [\App\Http\Controllers\Admin\BillingController::class, 'index'])->name('billing.index');
    Route::post('/billing/{bill}/pay', [\App\Http\Controllers\Admin\BillingController::class, 'processPayment'])->name('billing.pay');
    Route::get('/billing/{bill}', [\App\Http\Controllers\Admin\BillingController::class, 'show'])->name('billing.show');
    Route::delete('/billing/{bill}', [\App\Http\Controllers\Admin\BillingController::class, 'destroy'])->name('billing.destroy');

    // Sales Reports (Date, Month, Year)
    Route::get('/reports/sales', [\App\Http\Controllers\Admin\ReportController::class, 'sales'])->name('reports.sales');

    // Staff User Management
    Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::post('/users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');

    // ==========================================
    // PAYROLL MANAGEMENT SYSTEM (Admin Exclusive)
    // ==========================================
    Route::group(['prefix' => 'payroll', 'as' => 'payroll.'], function () {
        // Payroll Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Admin\Payroll\DashboardController::class, 'index'])->name('dashboard');

        // Employee Management
        Route::get('/employees', [\App\Http\Controllers\Admin\Payroll\EmployeeController::class, 'index'])->name('employees.index');
        Route::post('/employees', [\App\Http\Controllers\Admin\Payroll\EmployeeController::class, 'store'])->name('employees.store');
        Route::put('/employees/{employee}', [\App\Http\Controllers\Admin\Payroll\EmployeeController::class, 'update'])->name('employees.update');
        Route::delete('/employees/{employee}', [\App\Http\Controllers\Admin\Payroll\EmployeeController::class, 'destroy'])->name('employees.destroy');

        // Timekeeping (DTR)
        Route::get('/dtr', [\App\Http\Controllers\Admin\Payroll\DtrController::class, 'index'])->name('dtr.index');
        Route::post('/dtr', [\App\Http\Controllers\Admin\Payroll\DtrController::class, 'store'])->name('dtr.store');
        Route::post('/dtr/batch', [\App\Http\Controllers\Admin\Payroll\DtrController::class, 'batchGenerate'])->name('dtr.batch');
        Route::delete('/dtr/{dtr}', [\App\Http\Controllers\Admin\Payroll\DtrController::class, 'destroy'])->name('dtr.destroy');

        // Leave Applications
        Route::get('/leaves', [\App\Http\Controllers\Admin\Payroll\LeaveController::class, 'index'])->name('leaves.index');
        Route::post('/leaves', [\App\Http\Controllers\Admin\Payroll\LeaveController::class, 'store'])->name('leaves.store');
        Route::put('/leaves/{leave}/status', [\App\Http\Controllers\Admin\Payroll\LeaveController::class, 'updateStatus'])->name('leaves.status');
        Route::delete('/leaves/{leave}', [\App\Http\Controllers\Admin\Payroll\LeaveController::class, 'destroy'])->name('leaves.destroy');

        // Deductions & Loans
        Route::get('/deductions', [\App\Http\Controllers\Admin\Payroll\DeductionController::class, 'index'])->name('deductions.index');
        Route::post('/deductions', [\App\Http\Controllers\Admin\Payroll\DeductionController::class, 'store'])->name('deductions.store');
        Route::put('/deductions/{deduction}', [\App\Http\Controllers\Admin\Payroll\DeductionController::class, 'update'])->name('deductions.update');
        Route::delete('/deductions/{deduction}', [\App\Http\Controllers\Admin\Payroll\DeductionController::class, 'destroy'])->name('deductions.destroy');

        // Incentives Management (Editable rules and records)
        Route::get('/incentives', [\App\Http\Controllers\Admin\Payroll\IncentiveController::class, 'index'])->name('incentives.index');
        Route::post('/incentives/rules', [\App\Http\Controllers\Admin\Payroll\IncentiveController::class, 'storeRule'])->name('incentives.rules.store');
        Route::put('/incentives/rules/{rule}', [\App\Http\Controllers\Admin\Payroll\IncentiveController::class, 'updateRule'])->name('incentives.rules.update');
        Route::post('/incentives/records', [\App\Http\Controllers\Admin\Payroll\IncentiveController::class, 'storeEmployeeIncentive'])->name('incentives.records.store');
        Route::get('/incentives/suggest-stats', [\App\Http\Controllers\Admin\Payroll\IncentiveController::class, 'suggestStats'])->name('incentives.suggest');
        Route::delete('/incentives/records/{incentive}', [\App\Http\Controllers\Admin\Payroll\IncentiveController::class, 'destroy'])->name('incentives.destroy');

        // Payroll Periods & Processing (15-day, 30-day, Annual)
        Route::get('/periods', [\App\Http\Controllers\Admin\Payroll\PayrollController::class, 'index'])->name('periods.index');
        Route::post('/periods/generate', [\App\Http\Controllers\Admin\Payroll\PayrollController::class, 'generate'])->name('periods.generate');
        Route::get('/periods/{period}', [\App\Http\Controllers\Admin\Payroll\PayrollController::class, 'show'])->name('periods.show');
        Route::delete('/periods/{period}', [\App\Http\Controllers\Admin\Payroll\PayrollController::class, 'destroyPeriod'])->name('periods.destroy');
        Route::put('/records/{record}', [\App\Http\Controllers\Admin\Payroll\PayrollController::class, 'updateRecord'])->name('records.update');

        // Print & Reporting
        Route::get('/payslip/{record}/print', [\App\Http\Controllers\Admin\Payroll\PayrollController::class, 'printPayslip'])->name('payslip.print');
        Route::get('/periods/{period}/print', [\App\Http\Controllers\Admin\Payroll\PayrollController::class, 'printSummary'])->name('summary.print');
        Route::get('/annual', [\App\Http\Controllers\Admin\Payroll\PayrollController::class, 'annual'])->name('annual');
    });
});

// ==========================================
// 2. CASHIER MODULE (Checkout, POS, Receipts)
// ==========================================
Route::group(['prefix' => 'cashier', 'as' => 'cashier.', 'middleware' => ['auth', 'role:cashier']], function () {
    Route::get('/dashboard', [\App\Http\Controllers\Cashier\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/billing', [\App\Http\Controllers\Cashier\BillingController::class, 'index'])->name('billing.index');
    Route::post('/billing/{bill}/pay', [\App\Http\Controllers\Cashier\BillingController::class, 'processPayment'])->name('billing.pay');
    Route::get('/billing/{bill}/invoice', [\App\Http\Controllers\Cashier\BillingController::class, 'invoice'])->name('billing.invoice');

    Route::get('/pos', [\App\Http\Controllers\Cashier\SuppliesPOSController::class, 'index'])->name('pos.index');
    Route::post('/pos', [\App\Http\Controllers\Cashier\SuppliesPOSController::class, 'store'])->name('pos.store');
});

// ==========================================
// 3. VETERINARIAN MODULE (Medical Records & Rx)
// ==========================================
Route::group(['prefix' => 'veterinarian', 'as' => 'vet.', 'middleware' => ['auth', 'role:veterinarian']], function () {
    Route::get('/dashboard', [\App\Http\Controllers\Veterinarian\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/medical', [\App\Http\Controllers\Veterinarian\MedicalRecordController::class, 'index'])->name('medical.index');
    Route::post('/medical', [\App\Http\Controllers\Veterinarian\MedicalRecordController::class, 'store'])->name('medical.store');
    Route::get('/medical/{record}', [\App\Http\Controllers\Veterinarian\MedicalRecordController::class, 'show'])->name('medical.show');
    Route::put('/medical/{record}', [\App\Http\Controllers\Veterinarian\MedicalRecordController::class, 'update'])->name('medical.update');

    Route::get('/grooming', [\App\Http\Controllers\Veterinarian\GroomingController::class, 'index'])->name('grooming.index');
    Route::post('/grooming', [\App\Http\Controllers\Veterinarian\GroomingController::class, 'store'])->name('grooming.store');
    Route::put('/grooming/{grooming}', [\App\Http\Controllers\Veterinarian\GroomingController::class, 'update'])->name('grooming.update');
    Route::delete('/grooming/{grooming}', [\App\Http\Controllers\Veterinarian\GroomingController::class, 'destroy'])->name('grooming.destroy');

    // Clients & Patient History Database
    Route::get('/clients', [\App\Http\Controllers\Veterinarian\ClientHistoryController::class, 'index'])->name('clients.index');
    Route::get('/clients/{owner}', [\App\Http\Controllers\Veterinarian\ClientHistoryController::class, 'show'])->name('clients.show');
    Route::get('/pets/{pet}/history', [\App\Http\Controllers\Veterinarian\ClientHistoryController::class, 'petHistory'])->name('pets.history');

    // Case-linked Prescriptions
    Route::post('/prescriptions', [\App\Http\Controllers\Veterinarian\PrescriptionController::class, 'store'])->name('prescriptions.store');
    Route::put('/prescriptions/{prescription}', [\App\Http\Controllers\Veterinarian\PrescriptionController::class, 'update'])->name('prescriptions.update');
    Route::get('/prescriptions/{prescription}/print', [\App\Http\Controllers\Veterinarian\PrescriptionController::class, 'print'])->name('prescriptions.print');
});

// ==========================================
// 4. MANAGER MODULE (Audits & Performance)
// ==========================================
Route::group(['prefix' => 'manager', 'as' => 'manager.', 'middleware' => ['auth', 'role:manager']], function () {
    Route::get('/dashboard', [\App\Http\Controllers\Manager\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/reports/sales', [\App\Http\Controllers\Manager\SalesReportController::class, 'index'])->name('reports.sales');
    Route::get('/inventory/audit', [\App\Http\Controllers\Manager\InventoryAuditController::class, 'index'])->name('inventory.audit');
});
