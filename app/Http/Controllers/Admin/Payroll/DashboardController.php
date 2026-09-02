<?php

namespace App\Http\Controllers\Admin\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\DtrRecord;
use App\Models\LeaveApplication;
use App\Models\Deduction;
use App\Models\EmployeeIncentive;
use App\Models\PayrollPeriod;
use App\Models\PayrollRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;

        // Statistics
        $totalEmployees = Employee::where('status', 'active')->count();
        $totalMonthlyPayroll = PayrollRecord::whereHas('payrollPeriod', function ($q) use ($currentMonth, $currentYear) {
            $q->whereMonth('start_date', $currentMonth)->whereYear('start_date', $currentYear);
        })->sum('net_pay');

        $pendingLeaves = LeaveApplication::where('status', 'pending')->count();
        $totalIncentivesMonth = EmployeeIncentive::whereMonth('date_earned', $currentMonth)
            ->whereYear('date_earned', $currentYear)
            ->sum('total_incentive');

        $activeLoansBalance = Deduction::where('status', 'active')
            ->whereIn('deduction_type', ['loan', 'cash_advance'])
            ->sum('remaining_balance');

        $todayDtrCount = DtrRecord::whereDate('record_date', Carbon::today())
            ->whereIn('status', ['present', 'late'])
            ->count();

        // Recent items
        $recentPeriods = PayrollPeriod::withCount('records')
            ->with(['records' => function ($q) {
                $q->select('id', 'payroll_period_id', 'gross_pay', 'net_pay');
            }])
            ->latest()
            ->take(5)
            ->get();

        $pendingLeaveRequests = LeaveApplication::with('employee')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        $recentIncentives = EmployeeIncentive::with('employee')
            ->latest('date_earned')
            ->take(6)
            ->get();

        return view('admin.payroll.dashboard', compact(
            'totalEmployees',
            'totalMonthlyPayroll',
            'pendingLeaves',
            'totalIncentivesMonth',
            'activeLoansBalance',
            'todayDtrCount',
            'recentPeriods',
            'pendingLeaveRequests',
            'recentIncentives'
        ));
    }
}
