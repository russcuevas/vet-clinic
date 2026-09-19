<?php

namespace App\Http\Controllers\Veterinarian;

use App\Http\Controllers\Controller;
use App\Models\MedicalRecord;
use App\Models\Appointment;
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
            'total_admissions' => Appointment::where('service_category', 'boarding')->whereIn('status', ['confirmed', 'checked_in'])->count(),
        ];

        $activeQueue = MedicalRecord::with(['owner', 'pet', 'prescription'])
            ->where('status', 'ongoing')
            ->latest()
            ->get();

        $recentRecords = MedicalRecord::with(['owner', 'pet', 'prescription'])
            ->latest()
            ->take(8)
            ->get();

        $recentAdmissions = Appointment::where('service_category', 'boarding')
            ->with(['owner', 'pet', 'assignedEmployee'])
            ->latest()
            ->take(6)
            ->get();

        return view('veterinarian.dashboard', compact('stats', 'activeQueue', 'recentRecords', 'recentAdmissions'));
    }
}
