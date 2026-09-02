<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\InventoryItem;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $todayCollected = Bill::whereDate('transaction_date', $today)
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        $pendingBillsCount = Bill::where('payment_status', 'unpaid')->count();
        $recentTransactions = Bill::with(['owner', 'pet', 'items'])
            ->latest('transaction_date')
            ->take(8)
            ->get();

        $unpaidQueue = Bill::with(['owner', 'pet', 'items'])
            ->where('payment_status', 'unpaid')
            ->latest()
            ->take(10)
            ->get();

        return view('cashier.dashboard', compact('todayCollected', 'pendingBillsCount', 'recentTransactions', 'unpaidQueue'));
    }
}
