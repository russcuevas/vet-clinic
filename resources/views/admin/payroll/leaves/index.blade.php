@extends('layouts.app')

@php
    $title = 'Leave Applications';
    $headerTitle = 'Leave Requests & 5-Day Annual Limit Tracking';
    $breadcrumb = 'Payroll / Leaves';
@endphp

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--white); margin-bottom: 0.25rem;">Leave Application & Quota Tracker</h2>
            <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0;">Sick Leave (SL), Vacation Leave (VL), and Special Leave. Standard 5-day annual paid limit; excess days automatically convert to unpaid absence deductions.</p>
        </div>
        <button type="button" class="btn btn-gold" data-modal-target="modal-file-leave">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>+ File Leave Application</span>
        </button>
    </div>

    <!-- Annual 5-Day Quota Tracker Grid -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header" style="padding-bottom: 0.5rem;">
            <div class="card-title-group">
                <h3 class="card-title" style="font-size: 0.95rem;">Annual 5-Day Paid Leave Quota ({{ now()->year }})</h3>
                <span class="card-subtitle">Employees who exceed 5 days have excess days tagged as unpaid deductions</span>
            </div>
        </div>
        <div class="card-body" style="padding: 1rem 1.25rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1rem;">
                @foreach($employees as $emp)
                    @php
                        $stat = $leaveStats[$emp->id] ?? ['used' => 0, 'remaining' => 5, 'limit' => 5];
                        $pct = min(100, ($stat['used'] / 5) * 100);
                    @endphp
                    <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid var(--navy-border); border-radius: 8px; padding: 0.75rem;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.35rem;">
                            <div>
                                <strong style="font-size: 0.85rem; color: var(--white);">{{ $emp->full_name }}</strong>
                                <div style="font-size: 0.7rem; color: var(--text-muted);">{{ $emp->position }}</div>
                            </div>
                            <span class="badge {{ $stat['remaining'] > 0 ? 'badge-blue' : 'badge-danger' }}" style="font-size: 0.65rem;">
                                {{ $stat['remaining'] }}d left
                            </span>
                        </div>
                        <div style="height: 6px; width: 100%; background: rgba(255, 255, 255, 0.1); border-radius: 3px; overflow: hidden; margin-top: 6px;">
                            <div style="height: 100%; width: {{ $pct }}%; background: {{ $stat['used'] > 5 ? '#ef4444' : ($stat['used'] >= 4 ? '#f59e0b' : '#10b981') }};"></div>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.68rem; color: var(--text-muted); margin-top: 4px;">
                            <span>Used: <strong>{{ $stat['used'] }}</strong> / 5 days</span>
                            <span>{{ $stat['remaining'] == 0 ? 'Exhausted' : 'Available' }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Leaves List Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-title-group">
                <h3 class="card-title">Leave History & Requests</h3>
                <span class="card-subtitle">All leave logs with approval status and limit evaluation</span>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <a href="{{ route('admin.payroll.leaves.index') }}" class="btn btn-ghost btn-sm {{ !request('status') ? 'btn-gold' : '' }}">All</a>
                <a href="{{ route('admin.payroll.leaves.index', ['status' => 'pending']) }}" class="btn btn-ghost btn-sm {{ request('status') == 'pending' ? 'btn-gold' : '' }}">Pending</a>
                <a href="{{ route('admin.payroll.leaves.index', ['status' => 'approved']) }}" class="btn btn-ghost btn-sm {{ request('status') == 'approved' ? 'btn-gold' : '' }}">Approved</a>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Leave Type</th>
                            <th>Period</th>
                            <th>Days</th>
                            <th>5-Day Limit Check</th>
                            <th>Pay Status</th>
                            <th>Approval Status</th>
                            <th>Reason / Remarks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaves as $leave)
                            <tr>
                                <td>
                                    <strong style="color: var(--white);">{{ $leave->employee->full_name }}</strong>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $leave->employee->position }}</div>
                                </td>
                                <td>
                                    <span class="badge badge-navy" style="font-size: 0.72rem;">
                                        {{ ucfirst(str_replace('_', ' ', $leave->leave_type)) }}
                                    </span>
                                </td>
                                <td>
                                    {{ $leave->start_date->format('M d') }} - {{ $leave->end_date->format('M d, Y') }}
                                </td>
                                <td><strong>{{ $leave->days_count }}</strong> day(s)</td>
                                <td>
                                    @if($leave->exceeded_limit)
                                        <span class="badge badge-danger" style="font-size: 0.68rem;" title="Days beyond 5-day annual quota">
                                            ⚠️ Exceeded Limit
                                        </span>
                                    @else
                                        <span class="badge badge-success" style="font-size: 0.68rem;">
                                            ✓ Within 5 Days
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($leave->is_paid)
                                        <span class="badge badge-success" style="font-size: 0.68rem;">Paid Leave</span>
                                    @else
                                        <span class="badge badge-danger" style="font-size: 0.68rem;">Unpaid (Deducted)</span>
                                    @endif
                                </td>
                                <td>
                                    @if($leave->status === 'approved')
                                        <span class="badge badge-gold" style="font-size: 0.7rem;">Approved</span>
                                    @elseif($leave->status === 'rejected')
                                        <span class="badge badge-danger" style="font-size: 0.7rem;">Rejected</span>
                                    @else
                                        <span class="badge badge-warning" style="font-size: 0.7rem;">Pending</span>
                                    @endif
                                </td>
                                <td style="max-width: 220px; font-size: 0.75rem; color: var(--text-secondary);">
                                    <div>{{ $leave->reason }}</div>
                                    @if($leave->admin_remarks)
                                        <div style="font-size: 0.68rem; color: var(--gold-light); margin-top: 2px;">
                                            Note: {{ $leave->admin_remarks }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.35rem;">
                                        <button type="button" class="btn btn-navy btn-sm" style="padding: 0.25rem 0.5rem;"
                                            data-modal-target="modal-status-leave-{{ $leave->id }}" title="Change Status">
                                            Status
                                        </button>
                                        <form action="{{ route('admin.payroll.leaves.destroy', $leave) }}" method="POST"
                                            onsubmit="return confirm('Delete this leave record?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-ghost btn-sm" style="padding: 0.25rem 0.5rem; color: #ef4444;">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- Status Modal -->
                            <div class="modal-backdrop" id="modal-status-leave-{{ $leave->id }}">
                                <div class="modal-dialog" style="max-width: 480px;">
                                    <div class="modal-header">
                                        <div class="modal-title-group">
                                            <h4 class="modal-title">Review Leave: {{ $leave->employee->full_name }}</h4>
                                            <span style="font-size: 0.72rem; color: var(--gold-light);">{{ $leave->days_count }} days ({{ $leave->start_date->format('M d') }} - {{ $leave->end_date->format('M d, Y') }})</span>
                                        </div>
                                        <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
                                    </div>
                                    <form action="{{ route('admin.payroll.leaves.status', $leave) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body" style="padding: 1.5rem;">
                                            <div class="form-group">
                                                <label class="form-label">Application Status *</label>
                                                <select name="status" class="form-control" required>
                                                    <option value="approved" {{ $leave->status === 'approved' ? 'selected' : '' }}>Approved</option>
                                                    <option value="rejected" {{ $leave->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                                    <option value="pending" {{ $leave->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                </select>
                                            </div>

                                            <div class="form-group" style="margin-top: 0.75rem;">
                                                <label class="form-label">Payment Category</label>
                                                <select name="is_paid" class="form-control">
                                                    <option value="1" {{ $leave->is_paid ? 'selected' : '' }}>Paid Leave (Within limit or company granted)</option>
                                                    <option value="0" {{ !$leave->is_paid ? 'selected' : '' }}>Unpaid Leave (Generates deduction)</option>
                                                </select>
                                            </div>

                                            <div class="form-group" style="margin-top: 0.75rem;">
                                                <label class="form-label">Admin Remarks / Notes</label>
                                                <input type="text" name="admin_remarks" class="form-control" value="{{ $leave->admin_remarks }}" placeholder="e.g. Approved with medical certificate presented">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                                            <button type="submit" class="btn btn-gold">Update Leave Status</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 2.5rem;">
                                    No leave applications recorded. Click "+ File Leave Application" to record.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($leaves->hasPages())
                <div style="padding: 1rem;">
                    {{ $leaves->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal: File Leave Application -->
    <div class="modal-backdrop" id="modal-file-leave">
        <div class="modal-dialog" style="max-width: 550px;">
            <div class="modal-header">
                <div class="modal-title-group">
                    <h4 class="modal-title">File Leave Application</h4>
                    <span style="font-size: 0.72rem; color: var(--gold-light);">Automatic limit check against 5-day annual quota</span>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form action="{{ route('admin.payroll.leaves.store') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label">Employee *</label>
                        <select name="employee_id" class="form-control" required>
                            <option value="">-- Select Employee --</option>
                            @foreach($employees as $emp)
                                @php $stat = $leaveStats[$emp->id] ?? ['used' => 0, 'remaining' => 5]; @endphp
                                <option value="{{ $emp->id }}">
                                    {{ $emp->employee_code }} - {{ $emp->full_name }} ({{ $stat['remaining'] }} paid days left)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Leave Type *</label>
                            <select name="leave_type" class="form-control" required>
                                <option value="sick_leave">Sick Leave (SL)</option>
                                <option value="vacation_leave">Vacation Leave (VL)</option>
                                <option value="special_leave">Special Leave (SL)</option>
                                <option value="emergency_leave">Emergency Leave</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Annual Quota Rule</label>
                            <div style="background: rgba(212, 175, 55, 0.1); border: 1px solid var(--gold-border); padding: 0.5rem 0.75rem; border-radius: 6px; font-size: 0.72rem; color: var(--gold-light);">
                                Limit: 5 days/year. Days beyond limit are marked unpaid.
                            </div>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Start Date *</label>
                            <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">End Date *</label>
                            <input type="date" name="end_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 0.75rem;">
                        <label class="form-label">Reason / Justification *</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Provide medical or personal reason for leave..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold">File & Process Leave</button>
                </div>
            </form>
        </div>
    </div>
@endsection
