<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        $filterType = $request->input('filter_type', 'date'); // 'date', 'month', 'year'
        $selectedDate = $request->input('selected_date', Carbon::today()->format('Y-m-d'));
        $selectedMonth = $request->input('selected_month', Carbon::now()->format('Y-m'));
        $selectedYear = $request->input('selected_year', Carbon::now()->format('Y'));

        $query = Bill::with(['items', 'owner', 'pet', 'cashier'])->where('payment_status', 'paid');

        if ($filterType === 'date') {
            $query->whereDate('transaction_date', $selectedDate);
            $reportTitle = "Daily Sales Report - " . Carbon::parse($selectedDate)->format('F d, Y');
        } elseif ($filterType === 'month') {
            $parts = explode('-', $selectedMonth);
            $year = $parts[0] ?? Carbon::now()->year;
            $month = $parts[1] ?? Carbon::now()->month;
            $query->whereYear('transaction_date', $year)->whereMonth('transaction_date', $month);
            $reportTitle = "Monthly Sales Report - " . Carbon::createFromDate($year, $month, 1)->format('F Y');
        } elseif ($filterType === 'year') {
            $query->whereYear('transaction_date', $selectedYear);
            $reportTitle = "Annual Sales Report - Year {$selectedYear}";
        }

        $bills = $query->latest('transaction_date')->get();

        $totalRevenue = $bills->sum('total_amount');
        $vetRevenue = $bills->where('service_type', 'veterinary')->sum('total_amount');
        $groomingRevenue = $bills->where('service_type', 'grooming')->sum('total_amount');
        $suppliesRevenue = $bills->where('service_type', 'pet_supplies')->sum('total_amount');

        return view('manager.reports.sales', compact(
            'bills',
            'filterType',
            'selectedDate',
            'selectedMonth',
            'selectedYear',
            'reportTitle',
            'totalRevenue',
            'vetRevenue',
            'groomingRevenue',
            'suppliesRevenue'
        ));
    }
}
