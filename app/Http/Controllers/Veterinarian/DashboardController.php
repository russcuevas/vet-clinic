<?php

namespace App\Http\Controllers\Veterinarian;

use App\Http\Controllers\Controller;
use App\Models\MedicalRecord;
use App\Models\GroomingRecord;
use App\Models\Pet;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $stats = [
            'today_consultations' => MedicalRecord::whereDate('created_at', $today)->where('service_type', 'consultation')->count(),
            'today_followups' => MedicalRecord::whereDate('created_at', $today)->where('service_type', 'follow_up')->count(),
            'today_wellness' => MedicalRecord::whereDate('created_at', $today)->where('service_type', 'wellness')->count(),
            'total_grooming' => GroomingRecord::count(),
        ];

        $recentRecords = MedicalRecord::with(['owner', 'pet', 'prescription'])
            ->latest()
            ->take(8)
            ->get();

        $recentGrooming = GroomingRecord::with(['owner', 'pet'])
            ->latest()
            ->take(6)
            ->get();

        return view('veterinarian.dashboard', compact('stats', 'recentRecords', 'recentGrooming'));
    }
}
