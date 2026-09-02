@extends('layouts.app')

@php
    $title = 'Payroll Processing';
    $headerTitle = '15-Day & 30-Day Payroll Cycles';
    $breadcrumb = 'Payroll / Runs';
@endphp

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--white); margin-bottom: 0.25rem;">Payroll Periods & Disbursements</h2>
            <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0;">Generate and process 15-day semi-monthly or 30-day monthly payroll cycles with automated DTR, incentives, and deductions.</p>
        </div>
        <button type="button" class="btn btn-gold" data-modal-target="modal-generate-payroll">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>+ Generate Payroll Cycle</span>
        </button>
    </div>

    <!-- Filter Tabs (15 Days / 30 Days) -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body" style="padding: 1rem 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; gap: 0.5rem;">
                <a href="{{ route('admin.payroll.periods.index') }}" class="btn btn-ghost btn-sm {{ !request('type') ? 'btn-gold' : '' }}">
                    All Cycles
                </a>
                <a href="{{ route('admin.payroll.periods.index', ['type' => '15_days']) }}" class="btn btn-ghost btn-sm {{ request('type') === '15_days' ? 'btn-gold' : '' }}">
                    🗓️ 15 Days (Semi-Monthly)
                </a>
                <a href="{{ route('admin.payroll.periods.index', ['type' => '30_days']) }}" class="btn btn-ghost btn-sm {{ request('type') === '30_days' ? 'btn-gold' : '' }}">
                    📅 30 Days (Monthly)
                </a>
            </div>
            <a href="{{ route('admin.payroll.annual') }}" class="btn btn-navy btn-sm">
                📊 View Annual Records
            </a>
        </div>
    </div>

    <!-- Payroll Periods Table -->
    <div class="card">
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Period Name</th>
                            <th>Cycle Type</th>
                            <th>Date Coverage</th>
                            <th>Payout Date</th>
                            <th>Staff Count</th>
                            <th>Gross Pay (₱)</th>
                            <th>Incentives (₱)</th>
                            <th>Deductions (₱)</th>
                            <th>Net Pay (₱)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($periods as $period)
                            <tr>
                                <td>
                                    <strong style="color: var(--white); font-size: 0.95rem;">{{ $period->period_name }}</strong>
                                    @if($period->remarks)
                                        <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $period->remarks }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $period->period_type === '15_days' ? 'badge-blue' : 'badge-gold' }}" style="font-size: 0.7rem;">
                                        {{ $period->period_type === '15_days' ? '15-Day Semi-Monthly' : '30-Day Monthly' }}
                                    </span>
                                </td>
                                <td>{{ $period->start_date->format('M d') }} - {{ $period->end_date->format('M d, Y') }}</td>
                                <td>{{ $period->payout_date->format('M d, Y') }}</td>
                                <td>{{ $period->records_count }} staff</td>
                                <td>₱{{ number_format($period->total_gross_pay, 2) }}</td>
                                <td style="color: #10b981;">+₱{{ number_format($period->total_incentives, 2) }}</td>
                                <td style="color: #ef4444;">-₱{{ number_format($period->total_deductions, 2) }}</td>
                                <td>
                                    <strong style="color: var(--gold-light); font-size: 1rem;">
                                        ₱{{ number_format($period->total_net_pay, 2) }}
                                    </strong>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.35rem;">
                                        <a href="{{ route('admin.payroll.periods.show', $period) }}" class="btn btn-navy btn-sm" style="padding: 0.3rem 0.6rem;">
                                            Review & Payslips
                                        </a>
                                        <a href="{{ route('admin.payroll.summary.print', $period) }}" target="_blank" class="btn btn-ghost btn-sm" style="padding: 0.3rem 0.5rem;" title="Print Summary Report">
                                            🖨️
                                        </a>
                                        <form action="{{ route('admin.payroll.periods.destroy', $period) }}" method="POST"
                                            onsubmit="return confirm('Delete this payroll cycle and all its records?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-ghost btn-sm" style="padding: 0.3rem 0.5rem; color: #ef4444;" title="Delete">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" style="text-align: center; color: var(--text-muted); padding: 2.5rem;">
                                    No payroll cycles created yet. Click "+ Generate Payroll Cycle" to create one!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($periods->hasPages())
                <div style="padding: 1rem;">
                    {{ $periods->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal: Generate Payroll -->
    <div class="modal-backdrop" id="modal-generate-payroll">
        <div class="modal-dialog" style="max-width: 550px;">
            <div class="modal-header">
                <div class="modal-title-group">
                    <h4 class="modal-title">Generate Payroll Cycle</h4>
                    <span style="font-size: 0.72rem; color: var(--gold-light);">Automatic calculation of regular pay, DTR OT, role incentives, and deductions</span>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form action="{{ route('admin.payroll.periods.generate') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label">Period Cycle Name *</label>
                        <input type="text" name="period_name" class="form-control" placeholder="e.g. March 1 - 15, 2026 Payroll" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Cycle Type *</label>
                            <select name="period_type" id="cycle_type_select" class="form-control" required onchange="adjustPeriodDates()">
                                <option value="15_days" selected>15 Days (Semi-Monthly: 50% basic)</option>
                                <option value="30_days">30 Days (Monthly: 100% basic)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Payout Date *</label>
                            <input type="date" name="payout_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Coverage Start Date *</label>
                            <input type="date" name="start_date" id="start_date_input" class="form-control" value="{{ date('Y-m-01') }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Coverage End Date *</label>
                            <input type="date" name="end_date" id="end_date_input" class="form-control" value="{{ date('Y-m-15') }}" required>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 0.75rem;">
                        <label class="form-label">Remarks (Optional)</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="e.g. Regular 1st half payroll cycle"></textarea>
                    </div>

                    <div style="background: rgba(212, 175, 55, 0.08); border: 1px solid var(--gold-border); border-radius: 6px; padding: 0.75rem; margin-top: 1rem; font-size: 0.75rem; color: var(--gold-light);">
                        💡 <strong>Automated Logic:</strong><br>
                        • Base salary will be prorated (50% for 15-day, 100% for 30-day).<br>
                        • DTR overtime & tardiness between the dates will be calculated.<br>
                        • Role incentives earned in the period will be added to Gross Pay.<br>
                        • Active loans, cash advances, and statutory taxes will be deducted.<br>
                        • You can still edit any individual employee record after generation!
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold">Process & Generate Records</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function adjustPeriodDates() {
            const type = document.getElementById('cycle_type_select').value;
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');

            if (type === '15_days') {
                document.getElementById('start_date_input').value = `${year}-${month}-01`;
                document.getElementById('end_date_input').value = `${year}-${month}-15`;
            } else {
                const lastDay = new Date(year, now.getMonth() + 1, 0).getDate();
                document.getElementById('start_date_input').value = `${year}-${month}-01`;
                document.getElementById('end_date_input').value = `${year}-${month}-${lastDay}`;
            }
        }
    </script>
@endsection
