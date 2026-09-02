@extends('layouts.app')

@php
    $title = 'Manager Portal';
    $headerTitle = 'Manager Center & Operations';
    $breadcrumb = 'Executive Dashboard';
@endphp

@section('content')
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Today's Revenue</span>
                <div class="stat-icon-wrapper">₱</div>
            </div>
            <div class="stat-value">₱{{ number_format($stats['today_sales'], 2) }}</div>
            <div class="stat-desc">Daily clinical collections</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Month-To-Date</span>
                <div class="stat-icon-wrapper" style="color: var(--gold-light);">📅</div>
            </div>
            <div class="stat-value">₱{{ number_format($stats['month_sales'], 2) }}</div>
            <div class="stat-desc">Gross month earnings</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Annual Performance</span>
                <div class="stat-icon-wrapper" style="color: var(--success);">📈</div>
            </div>
            <div class="stat-value">₱{{ number_format($stats['year_sales'], 2) }}</div>
            <div class="stat-desc">Year {{ now()->year }} revenue</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Inventory Valuation</span>
                <div class="stat-icon-wrapper">📦</div>
            </div>
            <div class="stat-value">₱{{ number_format($stats['inventory_value'], 2) }}</div>
            <div class="stat-desc">{{ $stats['low_stock_count'] }} items require reorder</div>
        </div>
    </div>

    <!-- Manager Quick Buttons -->
    <div style="display: flex; gap: 0.75rem; margin-bottom: 1.75rem;">
        <a href="{{ route('manager.reports.sales') }}" class="btn btn-gold">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
            <span>Sales Reports (Day / Month / Year)</span>
        </a>
        <a href="{{ route('manager.inventory.audit') }}" class="btn btn-navy">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            <span>Inventory Valuation Audit</span>
        </a>
    </div>

    <!-- Transactions Audit Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-title-group">
                <h3 class="card-title">Recent Settled Transactions</h3>
                <span class="card-subtitle">Audited sales across all clinic branches</span>
            </div>
            <a href="{{ route('manager.reports.sales') }}" class="btn btn-ghost btn-sm">Generate Full Report →</a>
        </div>

        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Time</th>
                            <th>Client Name</th>
                            <th>Department</th>
                            <th>Items / Services</th>
                            <th>Total</th>
                            <th>Payment</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentTransactions as $bill)
                            <tr>
                                <td><strong style="color: var(--gold-primary);">{{ $bill->invoice_no }}</strong></td>
                                <td>{{ $bill->transaction_date->format('M d, Y h:i A') }}</td>
                                <td>{{ $bill->client_name }}</td>
                                <td>
                                    <span class="badge badge-navy" style="background: var(--navy-dark); border: 1px solid var(--navy-border); color: var(--gold-light);">
                                        {{ ucfirst(str_replace('_', ' ', $bill->service_type)) }}
                                    </span>
                                </td>
                                <td>
                                    @foreach($bill->items as $item)
                                        <div style="font-size: 0.8rem; color: var(--text-secondary);">• {{ $item->item_name }} (x{{ $item->quantity }})</div>
                                    @endforeach
                                </td>
                                <td>
                                    <strong style="color: var(--gold-primary); font-size: 1.05rem;">₱{{ number_format($bill->total_amount, 2) }}</strong>
                                </td>
                                <td>
                                    <span class="badge badge-success">{{ strtoupper($bill->payment_method) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
