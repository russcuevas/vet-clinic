@extends('layouts.app')

@php
    $title = 'Financial & Deductions';
    $headerTitle = 'Loans, Cash Advances & Statutory Deductions';
    $breadcrumb = 'Payroll / Deductions';
@endphp

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--white); margin-bottom: 0.25rem;">Financial Deductions Database</h2>
            <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0;">Manage employee loans, cash advances (CA), government contributions (SSS, PhilHealth, Pag-IBIG), and tardiness/unpaid leave deductions.</p>
        </div>
        <button type="button" class="btn btn-gold" data-modal-target="modal-add-deduction">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>+ Add Loan / Deduction</span>
        </button>
    </div>

    <!-- Metric Summary Grid -->
    <div class="stat-grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); margin-bottom: 1.5rem;">
        <div class="stat-card">
            <span class="stat-title">Active Company Loans</span>
            <div class="stat-value" style="color: var(--gold-light);">₱{{ number_format($totalActiveLoans, 2) }}</div>
            <div class="stat-desc">Remaining outstanding balance</div>
        </div>
        <div class="stat-card">
            <span class="stat-title">Active Cash Advances (CA)</span>
            <div class="stat-value" style="color: #38bdf8;">₱{{ number_format($totalActiveCashAdvance, 2) }}</div>
            <div class="stat-desc">To be amortized in upcoming payroll</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body" style="padding: 1rem 1.25rem;">
            <form method="GET" action="{{ route('admin.payroll.deductions.index') }}" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
                <div style="min-width: 180px;">
                    <select name="type" class="form-control">
                        <option value="">All Deduction Types</option>
                        <option value="loan" {{ request('type') == 'loan' ? 'selected' : '' }}>Company / Gov Loans</option>
                        <option value="cash_advance" {{ request('type') == 'cash_advance' ? 'selected' : '' }}>Cash Advance (CA)</option>
                        <option value="sss" {{ request('type') == 'sss' ? 'selected' : '' }}>SSS Contribution</option>
                        <option value="philhealth" {{ request('type') == 'philhealth' ? 'selected' : '' }}>PhilHealth</option>
                        <option value="pagibig" {{ request('type') == 'pagibig' ? 'selected' : '' }}>Pag-IBIG</option>
                        <option value="tardiness_absence" {{ request('type') == 'tardiness_absence' ? 'selected' : '' }}>Tardiness & Absences</option>
                        <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div style="min-width: 140px;">
                    <select name="status" class="form-control">
                        <option value="active" {{ request('status', 'active') == 'active' ? 'selected' : '' }}>Active Only</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed / Paid</option>
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Records</option>
                    </select>
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <select name="employee_id" class="form-control">
                        <option value="">-- All Employees --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->employee_code }} - {{ $emp->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-navy btn-sm">Filter</button>
                @if(request()->anyFilled(['type', 'status', 'employee_id']))
                    <a href="{{ route('admin.payroll.deductions.index') }}" class="btn btn-ghost btn-sm">Reset</a>
                @endif
            </form>
        </div>
    </div>

    <!-- Deductions Table -->
    <div class="card">
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Staff Member</th>
                            <th>Category</th>
                            <th>Title / Purpose</th>
                            <th>Total Principal</th>
                            <th>Amortization / Mo.</th>
                            <th>Remaining Balance</th>
                            <th>Effective Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deductions as $ded)
                            <tr>
                                <td>
                                    <strong style="color: var(--white);">{{ $ded->employee->full_name }}</strong>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $ded->employee->position }}</div>
                                </td>
                                <td>
                                    @if($ded->deduction_type === 'loan')
                                        <span class="badge badge-gold" style="font-size: 0.7rem;">Loan</span>
                                    @elseif($ded->deduction_type === 'cash_advance')
                                        <span class="badge badge-blue" style="font-size: 0.7rem;">Cash Advance</span>
                                    @elseif($ded->deduction_type === 'tardiness_absence')
                                        <span class="badge badge-danger" style="font-size: 0.7rem;">Absence / Leave</span>
                                    @else
                                        <span class="badge badge-navy" style="font-size: 0.7rem;">{{ ucfirst($ded->deduction_type) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: var(--white);">{{ $ded->title }}</div>
                                    @if($ded->remarks)
                                        <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $ded->remarks }}</div>
                                    @endif
                                </td>
                                <td>₱{{ number_format($ded->total_amount, 2) }}</td>
                                <td>₱{{ number_format($ded->monthly_amortization, 2) }}</td>
                                <td>
                                    <strong style="color: {{ $ded->remaining_balance > 0 ? '#ef4444' : '#10b981' }}; font-size: 0.95rem;">
                                        ₱{{ number_format($ded->remaining_balance, 2) }}
                                    </strong>
                                </td>
                                <td>{{ $ded->effective_date->format('M d, Y') }}</td>
                                <td>
                                    @if($ded->status === 'active')
                                        <span class="badge badge-success" style="font-size: 0.7rem;">Active</span>
                                    @elseif($ded->status === 'completed')
                                        <span class="badge badge-blue" style="font-size: 0.7rem;">Completed</span>
                                    @else
                                        <span class="badge badge-danger" style="font-size: 0.7rem;">Cancelled</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.35rem;">
                                        <button type="button" class="btn btn-navy btn-sm" style="padding: 0.25rem 0.5rem;"
                                            data-modal-target="modal-edit-ded-{{ $ded->id }}" title="Edit Balance / Terms">
                                            ✏️
                                        </button>
                                        <form action="{{ route('admin.payroll.deductions.destroy', $ded) }}" method="POST"
                                            onsubmit="return confirm('Delete this deduction record?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-ghost btn-sm" style="padding: 0.25rem 0.5rem; color: #ef4444;">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- Edit Deduction Modal -->
                            <div class="modal-backdrop" id="modal-edit-ded-{{ $ded->id }}">
                                <div class="modal-dialog" style="max-width: 500px;">
                                    <div class="modal-header">
                                        <div class="modal-title-group">
                                            <h4 class="modal-title">Update Deduction: {{ $ded->title }}</h4>
                                            <span style="font-size: 0.72rem; color: var(--gold-light);">Employee: {{ $ded->employee->full_name }}</span>
                                        </div>
                                        <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
                                    </div>
                                    <form action="{{ route('admin.payroll.deductions.update', $ded) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body" style="padding: 1.5rem;">
                                            <div class="form-group">
                                                <label class="form-label">Title / Description *</label>
                                                <input type="text" name="title" class="form-control" value="{{ $ded->title }}" required>
                                            </div>

                                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                                                <div class="form-group">
                                                    <label class="form-label">Monthly Amortization (₱) *</label>
                                                    <input type="number" step="0.01" name="monthly_amortization" class="form-control" value="{{ $ded->monthly_amortization }}" required>
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label">Remaining Balance (₱) *</label>
                                                    <input type="number" step="0.01" name="remaining_balance" class="form-control" value="{{ $ded->remaining_balance }}" required>
                                                </div>
                                            </div>

                                            <div class="form-group" style="margin-top: 0.75rem;">
                                                <label class="form-label">Status *</label>
                                                <select name="status" class="form-control" required>
                                                    <option value="active" {{ $ded->status === 'active' ? 'selected' : '' }}>Active (Will deduct on payroll)</option>
                                                    <option value="completed" {{ $ded->status === 'completed' ? 'selected' : '' }}>Completed (Fully paid)</option>
                                                    <option value="cancelled" {{ $ded->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                </select>
                                            </div>

                                            <div class="form-group" style="margin-top: 0.75rem;">
                                                <label class="form-label">Remarks</label>
                                                <input type="text" name="remarks" class="form-control" value="{{ $ded->remarks }}">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                                            <button type="submit" class="btn btn-gold">Update Deduction</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 2.5rem;">
                                    No deduction records found. Click "+ Add Loan / Deduction" to add a new record.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($deductions->hasPages())
                <div style="padding: 1rem;">
                    {{ $deductions->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal: Add Deduction / Loan -->
    <div class="modal-backdrop" id="modal-add-deduction">
        <div class="modal-dialog" style="max-width: 550px;">
            <div class="modal-header">
                <div class="modal-title-group">
                    <h4 class="modal-title">Record Financial Deduction / Loan</h4>
                    <span style="font-size: 0.72rem; color: var(--gold-light);">Loans, Cash Advances, and Statutory Amortizations</span>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form action="{{ route('admin.payroll.deductions.store') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label">Employee *</label>
                        <select name="employee_id" class="form-control" required>
                            <option value="">-- Select Employee --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->employee_code }} - {{ $emp->full_name }} ({{ $emp->position }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Deduction Type *</label>
                            <select name="deduction_type" class="form-control" required>
                                <option value="loan">Company / Salary Loan</option>
                                <option value="cash_advance">Cash Advance (CA)</option>
                                <option value="sss">SSS Salary Loan / Amortization</option>
                                <option value="pagibig">Pag-IBIG Calamity / Multi-purpose Loan</option>
                                <option value="other">Other Company Deduction</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Effective Date *</label>
                            <input type="date" name="effective_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 0.75rem;">
                        <label class="form-label">Title / Description *</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Emergency Salary Loan, March Mid-Month CA" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Total Amount (₱) *</label>
                            <input type="number" step="0.01" name="total_amount" class="form-control" placeholder="e.g. 5000" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Monthly Amortization (₱)</label>
                            <input type="number" step="0.01" name="monthly_amortization" class="form-control" placeholder="e.g. 1000 (Full amount if blank)">
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 0.75rem;">
                        <label class="form-label">Remarks / Notes</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="Optional details (terms, promissory note reference, etc.)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold">Record Deduction</button>
                </div>
            </form>
        </div>
    </div>
@endsection
