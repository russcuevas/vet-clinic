@extends('layouts.app')

@php
    $title = 'Payroll Dashboard';
    $headerTitle = 'Payroll & Compensation Command Center';
    $breadcrumb = 'Payroll / Overview';
@endphp

@section('content')
    <!-- Top Statistics Grid -->
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Active Personnel</span>
                <div class="stat-icon-wrapper" style="background: rgba(59, 130, 246, 0.15); color: #3b82f6;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
            <div class="stat-value">{{ $totalEmployees }} <span style="font-size: 0.9rem; color: var(--text-muted); font-weight: 500;">Staff</span></div>
            <div class="stat-desc">Today's Present DTR: {{ $todayDtrCount }} on duty</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Monthly Payroll Disbursed</span>
                <div class="stat-icon-wrapper" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="stat-value">₱{{ number_format($totalMonthlyPayroll, 2) }}</div>
            <div class="stat-desc">Net pay for {{ now()->format('F Y') }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Incentives & Commissions</span>
                <div class="stat-icon-wrapper" style="background: rgba(245, 186, 49, 0.15); color: var(--gold-primary);">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
            <div class="stat-value" style="color: var(--gold-light);">₱{{ number_format($totalIncentivesMonth, 2) }}</div>
            <div class="stat-desc">Groomer %, Vet Consults & Boarding</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Pending Approvals</span>
                <div class="stat-icon-wrapper" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>
            <div class="stat-value" style="{{ $pendingLeaves > 0 ? 'color: var(--warning);' : '' }}">{{ $pendingLeaves }} <span style="font-size: 0.9rem; color: var(--text-muted); font-weight: 500;">Leaves</span></div>
            <div class="stat-desc">Active Loans Balance: ₱{{ number_format($activeLoansBalance, 2) }}</div>
        </div>
    </div>

    <!-- Quick Action Bar -->
    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 2rem;">
        <a href="{{ route('admin.payroll.periods.index') }}" class="btn btn-gold">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>+ Generate Payroll (15/30 Days)</span>
        </a>
        <a href="{{ route('admin.payroll.employees.index') }}" class="btn btn-navy">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            <span>+ Add Employee</span>
        </a>
        <a href="{{ route('admin.payroll.incentives.index') }}" class="btn btn-navy">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Record Incentive %</span>
        </a>
        <a href="{{ route('admin.payroll.dtr.index') }}" class="btn btn-navy">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Timekeeping (DTR)</span>
        </a>
        <a href="{{ route('admin.payroll.leaves.index') }}" class="btn btn-navy">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>Leave Applications (12-Day Quota)</span>
        </a>
    </div>

    <!-- Dual Column Section: Recent Payroll Runs & Pending Leaves -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(460px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <!-- Recent Payroll Runs -->
        <div class="card">
            <div class="card-header">
                <div class="card-title-group">
                    <h3 class="card-title">Recent Payroll Periods</h3>
                    <span class="card-subtitle">15-day semi-monthly & 30-day monthly disbursements</span>
                </div>
                <a href="{{ route('admin.payroll.periods.index') }}" class="btn btn-ghost btn-sm">View All</a>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Period Name</th>
                                <th>Type</th>
                                <th>Payout Date</th>
                                <th>Total Net Pay</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPeriods as $period)
                                <tr>
                                    <td>
                                        <strong style="color: var(--white);">{{ $period->period_name }}</strong>
                                        <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $period->records_count }} employees</div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $period->period_type === '15_days' ? 'badge-blue' : 'badge-gold' }}" style="font-size: 0.7rem;">
                                            {{ $period->period_type === '15_days' ? '15 Days (Semi)' : '30 Days (Full)' }}
                                        </span>
                                    </td>
                                    <td>{{ $period->payout_date->format('M d, Y') }}</td>
                                    <td style="font-weight: 700; color: var(--gold-light);">₱{{ number_format($period->total_net_pay, 2) }}</td>
                                    <td>
                                        <a href="{{ route('admin.payroll.periods.show', $period) }}" class="btn btn-navy btn-sm" style="padding: 0.3rem 0.65rem;">
                                            Review
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                                        No payroll runs created yet. Click "+ Generate Payroll" to start!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pending Leave Applications with 12-Day Quota Indicator -->
        <div class="card">
            <div class="card-header">
                <div class="card-title-group">
                    <h3 class="card-title">Pending Leave Applications</h3>
                    <span class="card-subtitle">Enforcing the 12-day annual quota (5 VL, 5 SL, 2 SPL)</span>
                </div>
                <a href="{{ route('admin.payroll.leaves.index') }}" class="btn btn-ghost btn-sm">View Leaves</a>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Type</th>
                                <th>Duration</th>
                                <th>Quota Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingLeaveRequests as $leave)
                                <tr>
                                    <td>
                                        <strong style="color: var(--white);">{{ $leave->employee->full_name }}</strong>
                                        <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $leave->employee->position }}</div>
                                    </td>
                                    <td>
                                        <span class="badge badge-navy" style="font-size: 0.7rem;">
                                            {{ ucfirst(str_replace('_', ' ', $leave->leave_type)) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $leave->start_date->format('M d') }} - {{ $leave->end_date->format('M d, Y') }}
                                        <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $leave->days_count }} day(s)</div>
                                    </td>
                                    <td>
                                        @if($leave->exceeded_limit)
                                            <span class="badge badge-danger" style="font-size: 0.68rem;">Exceeded (Unpaid)</span>
                                        @else
                                            <span class="badge badge-success" style="font-size: 0.68rem;">Within Quota</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.payroll.leaves.index') }}" class="btn btn-gold btn-sm" style="padding: 0.3rem 0.65rem;">
                                            Action
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                                        No pending leave applications. Everything is up to date!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Role Incentives Log -->
    <div class="card">
        <div class="card-header">
            <div class="card-title-group">
                <h3 class="card-title">Recent Incentives Distributed</h3>
                <span class="card-subtitle">Groomers (10% / 20% tier), Vets (₱200 consult), Janitor boarding (₱35/day)</span>
            </div>
            <a href="{{ route('admin.payroll.incentives.index') }}" class="btn btn-ghost btn-sm">Configure & Manage</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Staff Member</th>
                            <th>Role</th>
                            <th>Incentive Title</th>
                            <th>Calculation Basis & Rate</th>
                            <th>Base Count / Sales</th>
                            <th>Total Incentive</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentIncentives as $inc)
                            <tr>
                                <td>{{ $inc->date_earned->format('M d, Y') }}</td>
                                <td><strong style="color: var(--white);">{{ $inc->employee->full_name }}</strong></td>
                                <td><span class="badge badge-navy" style="font-size: 0.7rem;">{{ $inc->employee->position }}</span></td>
                                <td>{{ $inc->title }}</td>
                                <td>
                                    @if($inc->calculation_basis === 'percentage')
                                        <span class="badge badge-gold" style="font-size: 0.7rem;">{{ $inc->rate_applied }}% Commission</span>
                                    @else
                                        <span class="badge badge-blue" style="font-size: 0.7rem;">₱{{ number_format($inc->rate_applied, 2) }} / unit</span>
                                    @endif
                                </td>
                                <td>
                                    @if($inc->calculation_basis === 'percentage')
                                        ₱{{ number_format($inc->base_amount_or_count, 2) }} sales
                                    @else
                                        {{ number_format($inc->base_amount_or_count, 0) }} units
                                    @endif
                                </td>
                                <td style="font-weight: 700; color: #10b981;">+₱{{ number_format($inc->total_incentive, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                                    No incentives recorded yet this month. Visit <a href="{{ route('admin.payroll.incentives.index') }}" style="color: var(--gold-light);">Role Incentives</a> to record!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
