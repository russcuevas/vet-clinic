@extends('layouts.app')

@php
    $title = 'Manager Sales Reports';
    $headerTitle = 'Manager Sales Reports & Audits';
    $breadcrumb = 'Financial Analysis';
@endphp

@section('content')
    <!-- Report Filter Toolbar -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body" style="padding: 1.25rem 1.5rem;">
            <form action="{{ route('manager.reports.sales') }}" method="GET"
                style="display: flex; gap: 1.25rem; align-items: flex-end; flex-wrap: wrap;">
                <div class="form-group" style="margin-bottom: 0; min-width: 180px;">
                    <label class="form-label">Select Period</label>
                    <select name="filter_type" id="mgr_filter_type" class="form-select"
                        onchange="toggleFilterInputs(this.value)">
                        <option value="date" {{ $filterType === 'date' ? 'selected' : '' }}>Daily (Specific Date)</option>
                        <option value="month" {{ $filterType === 'month' ? 'selected' : '' }}>Monthly (By Month)</option>
                        <option value="year" {{ $filterType === 'year' ? 'selected' : '' }}>Annual (By Year)</option>
                    </select>
                </div>

                <div class="form-group" id="input-group-date"
                    style="margin-bottom: 0; {{ $filterType !== 'date' ? 'display: none;' : '' }}">
                    <label class="form-label">Date</label>
                    <input type="date" name="selected_date" class="form-control" value="{{ $selectedDate }}">
                </div>

                <div class="form-group" id="input-group-month"
                    style="margin-bottom: 0; {{ $filterType !== 'month' ? 'display: none;' : '' }}">
                    <label class="form-label">Month</label>
                    <input type="month" name="selected_month" class="form-control" value="{{ $selectedMonth }}">
                </div>

                <div class="form-group" id="input-group-year"
                    style="margin-bottom: 0; {{ $filterType !== 'year' ? 'display: none;' : '' }}">
                    <label class="form-label">Year</label>
                    <input type="number" name="selected_year" class="form-control" min="2020" max="2035"
                        value="{{ $selectedYear }}">
                </div>

                <button type="submit" class="btn btn-gold">
                    <span>Generate Analysis</span>
                </button>


            </form>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Period Revenue</span>
                <div class="stat-icon-wrapper">₱</div>
            </div>
            <div class="stat-value">₱{{ number_format($totalRevenue, 2) }}</div>
            <div class="stat-desc">{{ $bills->count() }} Settled Bills</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Veterinary Share</span>
                <div class="stat-icon-wrapper">🩺</div>
            </div>
            <div class="stat-value" style="color: var(--gold-light);">₱{{ number_format($vetRevenue, 2) }}</div>
            <div class="stat-desc">Consultations & Medical</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Grooming Share</span>
                <div class="stat-icon-wrapper">✂️</div>
            </div>
            <div class="stat-value">₱{{ number_format($groomingRevenue, 2) }}</div>
            <div class="stat-desc">Styling & Baths</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Pet Supplies Share</span>
                <div class="stat-icon-wrapper">🛍️</div>
            </div>
            <div class="stat-value">₱{{ number_format($suppliesRevenue, 2) }}</div>
            <div class="stat-desc">Retail POS</div>
        </div>
    </div>

    <!-- Sales Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-title-group">
                <h3 class="card-title">{{ $reportTitle }}</h3>
                <span class="card-subtitle">Conforming to Flowchart: Date, Name, Item amount transaction</span>
            </div>
        </div>

        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Customer / Client</th>
                            <th>Department</th>
                            <th>Item Purchased / Service Rendered</th>
                            <th>Amount</th>
                            <th>Transaction & Cashier</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bills as $bill)
                            <tr>
                                <td>
                                    <strong>{{ $bill->transaction_date->format('Y-m-d') }}</strong>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">
                                        {{ $bill->transaction_date->format('h:i A') }}</div>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--white);">{{ $bill->client_name }}</div>
                                </td>
                                <td>
                                    <span class="badge badge-navy"
                                        style="background: var(--navy-dark); border: 1px solid var(--navy-border); color: var(--gold-light);">
                                        {{ ucfirst(str_replace('_', ' ', $bill->service_type)) }}
                                    </span>
                                </td>
                                <td style="max-width: 260px;">
                                    <div style="font-size: 0.82rem; color: var(--text-secondary);">
                                        @foreach ($bill->items as $item)
                                            <div>• {{ $item->item_name }} (x{{ $item->quantity }}) -
                                                ₱{{ number_format($item->total_price, 2) }}</div>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    <strong
                                        style="color: var(--gold-primary); font-size: 1.05rem;">₱{{ number_format($bill->total_amount, 2) }}</strong>
                                </td>
                                <td>
                                    <div style="font-weight: 600;">{{ $bill->invoice_no }}</div>
                                    <span class="badge badge-gold"
                                        style="font-size: 0.72rem;">{{ strtoupper($bill->payment_method) }}</span>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">
                                        {{ $bill->cashier->name ?? 'System' }}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function toggleFilterInputs(type) {
            document.getElementById('input-group-date').style.display = type === 'date' ? 'block' : 'none';
            document.getElementById('input-group-month').style.display = type === 'month' ? 'block' : 'none';
            document.getElementById('input-group-year').style.display = type === 'year' ? 'block' : 'none';
        }
    </script>
@endpush
