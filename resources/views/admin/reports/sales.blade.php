@extends('layouts.app')

@php
    $title = 'Sales Report';
    $headerTitle = 'Sales Report & Analytics';
    $breadcrumb = 'Financial Reports';
@endphp

@section('content')
    <!-- Report Filter Toolbar (Flowchart: Sales Report on date, by Month, by Year) -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body" style="padding: 1.25rem 1.5rem;">
            <form action="{{ route('admin.reports.sales') }}" method="GET"
                style="display: flex; gap: 1.25rem; align-items: flex-end; flex-wrap: wrap;">
                <div class="form-group" style="margin-bottom: 0; min-width: 180px;">
                    <label class="form-label">Report Period</label>
                    <select name="filter_type" id="filter_type" class="form-select"
                        onchange="toggleFilterInputs(this.value)">
                        <option value="date" {{ $filterType === 'date' ? 'selected' : '' }}>Daily (Specific Date)</option>
                        <option value="month" {{ $filterType === 'month' ? 'selected' : '' }}>Monthly (By Month)</option>
                        <option value="year" {{ $filterType === 'year' ? 'selected' : '' }}>Annual (By Year)</option>
                    </select>
                </div>

                <div class="form-group" id="input-group-date"
                    style="margin-bottom: 0; {{ $filterType !== 'date' ? 'display: none;' : '' }}">
                    <label class="form-label">Select Date</label>
                    <input type="date" name="selected_date" class="form-control" value="{{ $selectedDate }}">
                </div>

                <div class="form-group" id="input-group-month"
                    style="margin-bottom: 0; {{ $filterType !== 'month' ? 'display: none;' : '' }}">
                    <label class="form-label">Select Month</label>
                    <input type="month" name="selected_month" class="form-control" value="{{ $selectedMonth }}">
                </div>

                <div class="form-group" id="input-group-year"
                    style="margin-bottom: 0; {{ $filterType !== 'year' ? 'display: none;' : '' }}">
                    <label class="form-label">Select Year</label>
                    <input type="number" name="selected_year" class="form-control" min="2020" max="2035"
                        value="{{ $selectedYear }}">
                </div>

                <button type="submit" class="btn btn-gold">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    <span>Generate Report</span>
                </button>


            </form>
        </div>
    </div>

    <!-- Revenue Breakdown Cards -->
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Grand Total Revenue</span>
                <div class="stat-icon-wrapper">₱</div>
            </div>
            <div class="stat-value">₱{{ number_format($totalRevenue, 2) }}</div>
            <div class="stat-desc">{{ $bills->count() }} completed transactions</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Veterinary Services</span>
                <div class="stat-icon-wrapper">🩺</div>
            </div>
            <div class="stat-value" style="color: var(--gold-light);">₱{{ number_format($vetRevenue, 2) }}</div>
            <div class="stat-desc">Consultations & Wellness</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Grooming Services</span>
                <div class="stat-icon-wrapper">✂️</div>
            </div>
            <div class="stat-value">₱{{ number_format($groomingRevenue, 2) }}</div>
            <div class="stat-desc">Full Groom & Styling</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Pet Supplies</span>
                <div class="stat-icon-wrapper">🛍️</div>
            </div>
            <div class="stat-value">₱{{ number_format($suppliesRevenue, 2) }}</div>
            <div class="stat-desc">Retail POS items</div>
        </div>
    </div>

    <!-- Flowchart Table: Date, Name, Item, Amount, Transaction -->
    <div class="card">
        <div class="card-header">
            <div class="card-title-group">
                <h3 class="card-title">{{ $reportTitle }}</h3>
                <span class="card-subtitle">Detailed transaction ledger conforming to system flowchart</span>
            </div>
        </div>

        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Client Name</th>
                            <th>Service / Department</th>
                            <th>Purchased Items & Description</th>
                            <th>Amount</th>
                            <th>Transaction & Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bills as $bill)
                            <tr>
                                <td>
                                    <strong
                                        style="color: var(--white);">{{ $bill->transaction_date->format('Y-m-d') }}</strong>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">
                                        {{ $bill->transaction_date->format('h:i A') }}</div>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--white);">{{ $bill->client_name }}</div>
                                    @if ($bill->pet)
                                        <div style="font-size: 0.75rem; color: var(--gold-light);">Pet:
                                            {{ $bill->pet->name }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-navy"
                                        style="background: var(--navy-dark); border: 1px solid var(--navy-border); color: var(--gold-light);">
                                        {{ ucfirst(str_replace('_', ' ', $bill->service_type)) }}
                                    </span>
                                </td>
                                <td style="max-width: 280px;">
                                    <div style="font-size: 0.82rem; color: var(--text-secondary);">
                                        @foreach ($bill->items as $item)
                                            <div>• {{ $item->item_name }} (Qty: {{ $item->quantity }}) -
                                                ₱{{ number_format($item->total_price, 2) }}</div>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    <strong
                                        style="color: var(--gold-primary); font-size: 1.05rem;">₱{{ number_format($bill->total_amount, 2) }}</strong>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: var(--white);">{{ $bill->invoice_no }}</div>
                                    <span class="badge badge-gold"
                                        style="font-size: 0.72rem;">{{ strtoupper($bill->payment_method) }}</span>
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
