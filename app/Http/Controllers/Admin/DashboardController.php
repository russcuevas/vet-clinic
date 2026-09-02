<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Owner;
use App\Models\Pet;
use App\Models\MedicalRecord;
use App\Models\GroomingRecord;
use App\Models\Bill;
use App\Models\InventoryItem;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->month;
        $thisYear = Carbon::now()->year;

        $stats = [
            'total_clients' => Owner::count(),
            'total_pets' => Pet::count(),
            'today_sales' => Bill::whereDate('transaction_date', $today)->where('payment_status', 'paid')->sum('total_amount'),
            'month_sales' => Bill::whereMonth('transaction_date', $thisMonth)->whereYear('transaction_date', $thisYear)->where('payment_status', 'paid')->sum('total_amount'),
            'medical_count' => MedicalRecord::count(),
            'grooming_count' => GroomingRecord::count(),
            'low_stock_count' => InventoryItem::whereColumn('stock_quantity', '<=', 'reorder_level')->count(),
            'pending_bills_count' => Bill::where('payment_status', 'unpaid')->count(),
        ];

        $recentMedical = MedicalRecord::with(['owner', 'pet', 'veterinarian'])->latest()->take(5)->get();
        $recentGrooming = GroomingRecord::with(['owner', 'pet'])->latest()->take(5)->get();
        $recentBills = Bill::with(['owner', 'pet'])->latest()->take(6)->get();
        $lowStockItems = InventoryItem::whereColumn('stock_quantity', '<=', 'reorder_level')->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentMedical', 'recentGrooming', 'recentBills', 'lowStockItems'));
    }
}
