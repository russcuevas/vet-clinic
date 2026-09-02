@extends('layouts.app')

@php
    $title = 'Admin Dashboard';
    $headerTitle = 'Administrator Command Center';
    $breadcrumb = 'Dashboard Overview';
@endphp

@section('content')
    <!-- Top Statistics Grid -->
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Today's Revenue</span>
                <div class="stat-icon-wrapper">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>
            <div class="stat-value">₱{{ number_format($stats['today_sales'], 2) }}</div>
            <div class="stat-desc">Monthly: ₱{{ number_format($stats['month_sales'], 2) }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Registered Clients</span>
                <div class="stat-icon-wrapper">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                </div>
            </div>
            <div class="stat-value">{{ $stats['total_clients'] }}</div>
            <div class="stat-desc">Total Pets: {{ $stats['total_pets'] }} registered</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Veterinary Records</span>
                <div class="stat-icon-wrapper">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                </div>
            </div>
            <div class="stat-value">{{ $stats['medical_count'] }}</div>
            <div class="stat-desc">Grooming sessions: {{ $stats['grooming_count'] }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Stock & Pending Bills</span>
                <div class="stat-icon-wrapper">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
            </div>
            <div class="stat-value" style="{{ $stats['low_stock_count'] > 0 ? 'color: var(--warning);' : '' }}">{{ $stats['low_stock_count'] }} <span style="font-size: 0.9rem; color: var(--text-muted);">Low Stock</span></div>
            <div class="stat-desc">{{ $stats['pending_bills_count'] }} unpaid bills in queue</div>
        </div>
    </div>

    <!-- Quick Action Bar -->
    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 2rem;">
        <a href="{{ route('admin.clients.index') }}" class="btn btn-gold">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
            <span>+ Register Client / Pet</span>
        </a>
        <a href="{{ route('admin.veterinary.index') }}" class="btn btn-navy">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            <span>+ New Consultation / Wellness</span>
        </a>
        <a href="{{ route('admin.grooming.index') }}" class="btn btn-navy">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879a3 3 0 11-4.242-4.242L10.758 7.758a3 3 0 014.242 4.242z" /></svg>
            <span>+ New Grooming Record</span>
        </a>
        <a href="{{ route('admin.supplies.index') }}" class="btn btn-navy">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
            <span>Pet Supplies POS</span>
        </a>
    </div>

    <!-- Dual Column Grid: Recent Medical & Billing Records -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(460px, 1fr)); gap: 1.5rem;">
        <!-- Recent Medical Records -->
        <div class="card">
            <div class="card-header">
                <div class="card-title-group">
                    <h3 class="card-title">Recent Veterinary Examinations</h3>
                    <span class="card-subtitle">Consultation, Follow-up, Wellness</span>
                </div>
                <a href="{{ route('admin.veterinary.index') }}" class="btn btn-ghost btn-sm">View All</a>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Pet & Owner</th>
                                <th>Service</th>
                                <th>Attending Vet</th>
                                <th>Fee</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentMedical as $record)
                                <tr>
                                    <td><strong style="color: var(--gold-primary);">{{ $record->record_code }}</strong></td>
                                    <td>
                                        <div style="font-weight: 600;">{{ $record->pet->name ?? 'Unknown Pet' }}</div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $record->owner->full_name ?? 'N/A' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ ucfirst($record->service_type) }}</span>
                                    </td>
                                    <td>{{ $record->veterinarian->name ?? 'Dr. On Duty' }}</td>
                                    <td><strong>₱{{ number_format($record->service_fee, 2) }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Billing Transactions -->
        <div class="card">
            <div class="card-header">
                <div class="card-title-group">
                    <h3 class="card-title">Recent Invoices & Transactions</h3>
                    <span class="card-subtitle">Aggregated from all clinic branches</span>
                </div>
                <a href="{{ route('admin.billing.index') }}" class="btn btn-ghost btn-sm">View All</a>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Client Name</th>
                                <th>Service Type</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentBills as $bill)
                                <tr>
                                    <td><strong style="color: var(--gold-primary);">{{ $bill->invoice_no }}</strong></td>
                                    <td>{{ $bill->client_name }}</td>
                                    <td>
                                        <span class="badge badge-gold">{{ ucfirst(str_replace('_', ' ', $bill->service_type)) }}</span>
                                    </td>
                                    <td><strong>₱{{ number_format($bill->total_amount, 2) }}</strong></td>
                                    <td>
                                        <span class="badge {{ $bill->payment_status === 'paid' ? 'badge-success' : 'badge-danger' }}">
                                            {{ ucfirst($bill->payment_status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
