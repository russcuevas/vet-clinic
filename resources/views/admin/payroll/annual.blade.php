@extends('layouts.app')

@php
    $title = "Annual Payroll Records ({$year})";
    $headerTitle = "Annual Compensation & Tax Summary ({$year})";
    $breadcrumb = "Payroll / Annual Summary";
@endphp

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--white); margin-bottom: 0.25rem;">Annual Payroll Register ({{ $year }})</h2>
            <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0;">Comprehensive yearly summary of staff earnings, cumulative incentives, and statutory deductions.</p>
        </div>
        <form method="GET" action="{{ route('admin.payroll.annual') }}" style="display: flex; gap: 0.5rem; align-items: center;">
            <select name="year" class="form-control" onchange="this.form.submit()">
                @for($y = now()->year - 3; $y <= now()->year + 1; $y++)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>Year {{ $y }}</option>
                @endfor
            </select>
            <a href="{{ route('admin.payroll.periods.index') }}" class="btn btn-navy btn-sm">Cycles</a>
        </form>
    </div>

    <!-- Annual Stat Cards -->
    <div class="stat-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 1.5rem;">
        <div class="stat-card">
            <span class="stat-title">Annual Gross Disbursed</span>
            <div class="stat-value">₱{{ number_format(collect($annualData)->sum('total_gross'), 2) }}</div>
            <div class="stat-desc">All payroll runs in {{ $year }}</div>
        </div>
        <div class="stat-card">
            <span class="stat-title">Cumulative Incentives</span>
            <div class="stat-value" style="color: #10b981;">₱{{ number_format(collect($annualData)->sum('total_incentives'), 2) }}</div>
            <div class="stat-desc">Commissions & bonuses</div>
        </div>
        <div class="stat-card">
            <span class="stat-title">Cumulative Deductions</span>
            <div class="stat-value" style="color: #ef4444;">₱{{ number_format(collect($annualData)->sum('total_deductions'), 2) }}</div>
            <div class="stat-desc">Gov, loans & CA deductions</div>
        </div>
        <div class="stat-card">
            <span class="stat-title">Total Annual Net Pay</span>
            <div class="stat-value" style="color: var(--gold-light);">₱{{ number_format(collect($annualData)->sum('total_net'), 2) }}</div>
            <div class="stat-desc">Take-home salary total</div>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-title-group">
                <h3 class="card-title">Staff Yearly Summary Table</h3>
                <span class="card-subtitle">Year-to-date totals per active clinic employee</span>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>EMP #</th>
                            <th>Staff Member</th>
                            <th>Position & Dept</th>
                            <th>Base Monthly</th>
                            <th>Periods Processed</th>
                            <th>Gross Total (₱)</th>
                            <th>Incentives Total (₱)</th>
                            <th>Deductions Total (₱)</th>
                            <th>Net Pay Total (₱)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($annualData as $data)
                            <tr>
                                <td>
                                    <span style="font-family: monospace; font-weight: 700; color: var(--gold-light);">
                                        {{ $data['employee']->employee_code }}
                                    </span>
                                </td>
                                <td>
                                    <strong style="color: var(--white);">{{ $data['employee']->full_name }}</strong>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $data['employee']->email ?: '-' }}</div>
                                </td>
                                <td>
                                    <span class="badge badge-navy" style="font-size: 0.7rem;">{{ $data['employee']->position }}</span>
                                    <div style="font-size: 0.7rem; color: var(--text-muted);">{{ $data['employee']->department }}</div>
                                </td>
                                <td>₱{{ number_format($data['employee']->basic_salary, 2) }}</td>
                                <td>{{ $data['periods_count'] }} runs</td>
                                <td style="font-weight: 700; color: var(--white);">₱{{ number_format($data['total_gross'], 2) }}</td>
                                <td style="font-weight: 700; color: #10b981;">+₱{{ number_format($data['total_incentives'], 2) }}</td>
                                <td style="font-weight: 700; color: #ef4444;">-₱{{ number_format($data['total_deductions'], 2) }}</td>
                                <td>
                                    <strong style="color: var(--gold-light); font-size: 1rem;">
                                        ₱{{ number_format($data['total_net'], 2) }}
                                    </strong>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 2.5rem;">
                                    No records found for year {{ $year }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
