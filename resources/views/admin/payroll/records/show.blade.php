@extends('layouts.app')

@php
    $title = $period->period_name;
    $headerTitle = $period->period_name;
    $breadcrumb = 'Payroll / ' . $period->period_name;
@endphp

@section('content')
    <!-- Top Summary Banner -->
    <div class="card" style="margin-bottom: 1.5rem; border: 1px solid var(--gold-border);">
        <div class="card-body" style="padding: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.5rem;">
                        <span class="badge {{ $period->period_type === '15_days' ? 'badge-blue' : 'badge-gold' }}">
                            {{ $period->period_type === '15_days' ? '15-Day Semi-Monthly Cycle' : '30-Day Monthly Cycle' }}
                        </span>
                        <span style="font-size: 0.8rem; color: var(--text-muted);">
                            Coverage: {{ $period->start_date->format('M d, Y') }} — {{ $period->end_date->format('M d, Y') }}
                        </span>
                    </div>
                    <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--white); margin-bottom: 0.25rem;">
                        {{ $period->period_name }}
                    </h2>
                    <p style="font-size: 0.82rem; color: var(--text-secondary); margin: 0;">
                        Payout Date: <strong style="color: var(--gold-light);">{{ $period->payout_date->format('F d, Y') }}</strong>
                        @if($period->remarks) • {{ $period->remarks }} @endif
                    </p>
                </div>

                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <a href="{{ route('admin.payroll.summary.print', $period) }}" target="_blank" class="btn btn-gold">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <span>Print Period Summary Sheet</span>
                    </a>
                    <a href="{{ route('admin.payroll.periods.index') }}" class="btn btn-navy">
                        &larr; Back to Cycles
                    </a>
                </div>
            </div>

            <!-- Financial Totals Row -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-top: 1.5rem; border-top: 1px solid var(--navy-border); padding-top: 1.25rem;">
                <div>
                    <span style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Total Staff</span>
                    <div style="font-size: 1.25rem; font-weight: 700; color: var(--white);">{{ $period->records->count() }} employees</div>
                </div>
                <div>
                    <span style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Gross Total</span>
                    <div style="font-size: 1.25rem; font-weight: 700; color: var(--white);">₱{{ number_format($period->total_gross_pay, 2) }}</div>
                </div>
                <div>
                    <span style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Total Incentives Paid</span>
                    <div style="font-size: 1.25rem; font-weight: 700; color: #10b981;">+₱{{ number_format($period->total_incentives, 2) }}</div>
                </div>
                <div>
                    <span style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Total Deductions</span>
                    <div style="font-size: 1.25rem; font-weight: 700; color: #ef4444;">-₱{{ number_format($period->total_deductions, 2) }}</div>
                </div>
                <div>
                    <span style="font-size: 0.72rem; color: var(--gold-light); text-transform: uppercase; font-weight: 700;">Net Payroll Payable</span>
                    <div style="font-size: 1.4rem; font-weight: 800; color: var(--gold-light);">₱{{ number_format($period->total_net_pay, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Payroll Register Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-title-group">
                <h3 class="card-title">Employee Payroll Register</h3>
                <span class="card-subtitle">Detailed breakdown of gross pay, overtime, incentives, deductions, and net pay</span>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>EMP #</th>
                            <th>Staff & Position</th>
                            <th>Regular Pay</th>
                            <th>OT Pay</th>
                            <th>Incentives (₱)</th>
                            <th>Gross Pay (₱)</th>
                            <th>Deductions (₱)</th>
                            <th>Net Pay (₱)</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($period->records as $rec)
                            <tr>
                                <td>
                                    <span style="font-family: monospace; font-weight: 700; color: var(--gold-light);">
                                        {{ $rec->employee->employee_code }}
                                    </span>
                                </td>
                                <td>
                                    <strong style="color: var(--white);">{{ $rec->employee->full_name }}</strong>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">
                                        {{ $rec->employee->position }} • {{ $rec->days_worked }} days worked
                                    </div>
                                </td>
                                <td>₱{{ number_format($rec->regular_pay, 2) }}</td>
                                <td>
                                    @if($rec->ot_pay > 0)
                                        <span style="color: var(--gold-light);">+₱{{ number_format($rec->ot_pay, 2) }}</span>
                                        <div style="font-size: 0.68rem; color: var(--text-muted);">({{ $rec->ot_hours }} hrs)</div>
                                    @else
                                        <span style="color: var(--text-muted);">₱0.00</span>
                                    @endif
                                </td>
                                <td>
                                    @if($rec->incentives_total > 0)
                                        <strong style="color: #10b981;">+₱{{ number_format($rec->incentives_total, 2) }}</strong>
                                    @else
                                        <span style="color: var(--text-muted);">₱0.00</span>
                                    @endif
                                </td>
                                <td>
                                    <strong style="color: var(--white);">₱{{ number_format($rec->gross_pay, 2) }}</strong>
                                </td>
                                <td>
                                    <span style="color: #ef4444; font-weight: 600;">-₱{{ number_format($rec->total_deductions, 2) }}</span>
                                    <div style="font-size: 0.68rem; color: var(--text-muted);">
                                        Gov: ₱{{ number_format($rec->sss_deduction + $rec->philhealth_deduction + $rec->pagibig_deduction, 2) }}
                                        @if($rec->loan_deduction > 0) | Loan: ₱{{ number_format($rec->loan_deduction, 2) }} @endif
                                        @if($rec->cash_advance_deduction > 0) | CA: ₱{{ number_format($rec->cash_advance_deduction, 2) }} @endif
                                    </div>
                                </td>
                                <td>
                                    <strong style="font-size: 1.05rem; color: var(--gold-light);">
                                        ₱{{ number_format($rec->net_pay, 2) }}
                                    </strong>
                                </td>
                                <td>
                                    @if($rec->payment_status === 'paid')
                                        <span class="badge badge-success" style="font-size: 0.7rem;">Paid</span>
                                    @else
                                        <span class="badge badge-warning" style="font-size: 0.7rem;">Unpaid</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.35rem;">
                                        <a href="{{ route('admin.payroll.payslip.print', $rec) }}" target="_blank"
                                            class="btn btn-navy btn-sm" style="padding: 0.25rem 0.5rem;" title="Print Employee Payslip">
                                            📄 Payslip
                                        </a>
                                        <button type="button" class="btn btn-ghost btn-sm" style="padding: 0.25rem 0.5rem;"
                                            data-modal-target="modal-edit-rec-{{ $rec->id }}" title="Edit Overrides">
                                            ✏️
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Modal: Edit Single Record Override -->
                            <div class="modal-backdrop" id="modal-edit-rec-{{ $rec->id }}">
                                <div class="modal-dialog modal-lg" style="max-width: 900px; width: 95%;">
                                    <div class="modal-header">
                                        <div class="modal-title-group">
                                            <h4 class="modal-title">Edit Payroll Record: {{ $rec->employee->full_name }}</h4>
                                            <span style="font-size: 0.72rem; color: var(--gold-light);">Manual adjust regular pay, incentives, deductions, or payment status</span>
                                        </div>
                                        <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
                                    </div>
                                    <form action="{{ route('admin.payroll.records.update', $rec) }}" method="POST" id="form-rec-{{ $rec->id }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body" style="padding: 1.5rem;">
                                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.75rem;">
                                                <div class="form-group">
                                                    <label class="form-label">Days Worked</label>
                                                    <input type="number" step="0.5" name="days_worked" class="form-control" value="{{ $rec->days_worked }}" required>
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label">Regular Pay (₱)</label>
                                                    <input type="number" step="0.01" name="regular_pay" class="form-control" value="{{ $rec->regular_pay }}" required>
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label">OT Pay (₱)</label>
                                                    <input type="number" step="0.01" name="ot_pay" class="form-control" value="{{ $rec->ot_pay }}" required>
                                                </div>
                                            </div>

                                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                                                <div class="form-group">
                                                    <label class="form-label">Role Incentives (₱) *</label>
                                                    <input type="number" step="0.01" name="incentives_total" class="form-control" value="{{ $rec->incentives_total }}" required>
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label">Gross Pay (₱) *</label>
                                                    <input type="number" step="0.01" name="gross_pay" class="form-control" value="{{ $rec->gross_pay }}" required>
                                                </div>
                                            </div>

                                            <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid var(--navy-border); border-radius: 8px; padding: 0.75rem; margin-top: 0.75rem;">
                                                <span style="font-size: 0.75rem; color: var(--gold-light); font-weight: 700; display: block; margin-bottom: 0.5rem;">Deductions Breakdown</span>
                                                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.5rem;">
                                                    <div>
                                                        <label style="font-size: 0.7rem; color: var(--text-muted);">SSS (₱)</label>
                                                        <input type="number" step="0.01" name="sss_deduction" class="form-control" value="{{ $rec->sss_deduction }}">
                                                    </div>
                                                    <div>
                                                        <label style="font-size: 0.7rem; color: var(--text-muted);">PhilHealth (₱)</label>
                                                        <input type="number" step="0.01" name="philhealth_deduction" class="form-control" value="{{ $rec->philhealth_deduction }}">
                                                    </div>
                                                    <div>
                                                        <label style="font-size: 0.7rem; color: var(--text-muted);">Pag-IBIG (₱)</label>
                                                        <input type="number" step="0.01" name="pagibig_deduction" class="form-control" value="{{ $rec->pagibig_deduction }}">
                                                    </div>
                                                    <div>
                                                        <label style="font-size: 0.7rem; color: var(--text-muted);">Loan (₱)</label>
                                                        <input type="number" step="0.01" name="loan_deduction" class="form-control" value="{{ $rec->loan_deduction }}">
                                                    </div>
                                                    <div>
                                                        <label style="font-size: 0.7rem; color: var(--text-muted);">Cash Adv (₱)</label>
                                                        <input type="number" step="0.01" name="cash_advance_deduction" class="form-control" value="{{ $rec->cash_advance_deduction }}">
                                                    </div>
                                                    <div>
                                                        <label style="font-size: 0.7rem; color: var(--text-muted);">Absence/Late (₱)</label>
                                                        <input type="number" step="0.01" name="absence_tardiness_deduction" class="form-control" value="{{ $rec->absence_tardiness_deduction }}">
                                                    </div>
                                                </div>
                                            </div>

                                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.75rem; margin-top: 0.75rem;">
                                                <div class="form-group">
                                                    <label class="form-label">Tax (₱)</label>
                                                    <input type="number" step="0.01" name="tax_deduction" class="form-control" value="{{ $rec->tax_deduction }}">
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label">Total Deductions (₱) *</label>
                                                    <input type="number" step="0.01" name="total_deductions" class="form-control" value="{{ $rec->total_deductions }}" required>
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label">Net Pay (₱) *</label>
                                                    <input type="number" step="0.01" name="net_pay" class="form-control" value="{{ $rec->net_pay }}" required style="font-weight: 700; color: #10b981;">
                                                </div>
                                            </div>

                                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                                                <div class="form-group">
                                                    <label class="form-label">Payment Status *</label>
                                                    <select name="payment_status" class="form-control" required>
                                                        <option value="unpaid" {{ $rec->payment_status === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                                        <option value="paid" {{ $rec->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label">Notes</label>
                                                    <input type="text" name="notes" class="form-control" value="{{ $rec->notes }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                                            <button type="submit" class="btn btn-gold">Save Overrides</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
