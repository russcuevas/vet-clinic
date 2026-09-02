<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\InventoryItem;
use App\Models\MedicalRecord;
use App\Models\GroomingRecord;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->month;
        $thisYear = Carbon::now()->year;

        $stats = [
            'today_sales' => Bill::whereDate('transaction_date', $today)->where('payment_status', 'paid')->sum('total_amount'),
            'month_sales' => Bill::whereMonth('transaction_date', $thisMonth)->whereYear('transaction_date', $thisYear)->where('payment_status', 'paid')->sum('total_amount'),
            'year_sales' => Bill::whereYear('transaction_date', $thisYear)->where('payment_status', 'paid')->sum('total_amount'),
            'inventory_value' => InventoryItem::selectRaw('SUM(stock_quantity * unit_price) as val')->value('val') ?? 0,
            'low_stock_count' => InventoryItem::whereColumn('stock_quantity', '<=', 'reorder_level')->count(),
            'total_transactions' => Bill::where('payment_status', 'paid')->count(),
        ];

        $recentTransactions = Bill::with(['owner', 'pet', 'items'])
            ->where('payment_status', 'paid')
            ->latest('transaction_date')
            ->take(8)
            ->get();

        return view('manager.dashboard', compact('stats', 'recentTransactions'));
    }
}
