@extends('layouts.app')

@php
    $title = 'Leave Applications';
    $headerTitle = 'Leave Requests & 12-Day Annual Quota Tracking';
    $breadcrumb = 'Payroll / Leaves';
@endphp

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--white); margin-bottom: 0.25rem;">Leave Application & Quota Command Center</h2>
            <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0;">12 Days Annual Paid Leave: <strong>5 Vacation Leave (VL)</strong>, <strong>5 Sick Leave (SL)</strong>, <strong>2 Special Leave (SPL)</strong>.</p>
        </div>
        <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <!-- Year Selector -->
            <div style="display: flex; align-items: center; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--navy-border); border-radius: 6px; padding: 0.2rem 0.5rem; gap: 0.35rem;">
                <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">Year:</span>
                @foreach([$year - 1, $year, $year + 1] as $yr)
                    <a href="{{ route('admin.payroll.leaves.index', array_merge(request()->query(), ['year' => $yr])) }}"
                       class="btn btn-sm {{ $year == $yr ? 'btn-gold' : 'btn-ghost' }}"
                       style="padding: 0.15rem 0.55rem; font-size: 0.75rem;">
                        {{ $yr }}
                    </a>
                @endforeach
            </div>

            <button type="button" class="btn btn-gold" data-modal-target="modal-file-leave">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>+ File Leave Application</span>
            </button>
        </div>
    </div>

    <!-- Policy Banner: Jan 1 Reset & SL Monetization Rules -->
    <div style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.25) 0%, rgba(212, 175, 55, 0.12) 100%); border: 1px solid rgba(212, 175, 55, 0.3); border-radius: 10px; padding: 1rem 1.25rem; margin-bottom: 1.5rem;">
        <div style="display: flex; align-items: flex-start; gap: 1rem; flex-wrap: wrap;">
            <div style="background: var(--gold-primary); color: #0a1128; border-radius: 50%; width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem; flex-shrink: 0;">
                💡
            </div>
            <div style="flex: 1; min-width: 260px;">
                <h4 style="font-size: 0.92rem; font-weight: 700; color: var(--gold-light); margin: 0 0 0.25rem 0;">Annual Reset & Sick Leave (SL) Monetization Rules (Effective Jan 1)</h4>
                <div style="font-size: 0.8rem; color: var(--text-secondary); line-height: 1.5;">
                    <div>• <strong>Annual Automatic Reset (Jan 1):</strong> Every January 1, all employee leave balances automatically reset to a fresh <strong>12-day paid quota</strong> (5 VL, 5 SL, 2 SPL).</div>
                    <div>• <strong>Sick Leave (SL) Conversion:</strong> Unconsumed Sick Leave (SL - up to 5 days) is <strong>convertible to cash / paid out</strong> at year-end based on the employee's daily rate.</div>
                    <div>• <strong>VL & SPL Non-Convertible:</strong> Vacation Leave (5d) and Special Leave (2d) are forfeited if unused (use-it-or-lose-it) and are non-payable.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Annual 12-Day Quota Tracker Grid (5 VL, 5 SL, 2 SPL) -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header" style="padding-bottom: 0.5rem;">
            <div class="card-title-group">
                <h3 class="card-title" style="font-size: 0.95rem;">Annual Leave Quota Tracker (Year {{ $year }}) — 12 Days Total (5 VL, 5 SL, 2 SPL)</h3>
                <span class="card-subtitle">Per-employee balances for {{ $year }}. Automatic fresh reset applied on January 1, {{ $year }}</span>
            </div>
        </div>
        <div class="card-body" style="padding: 1rem 1.25rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem;">
                @foreach($employees as $emp)
                    @php
                        $stat = $leaveStats[$emp->id] ?? [
                            'vl' => ['used' => 0, 'limit' => 5, 'remaining' => 5],
                            'sl' => ['used' => 0, 'limit' => 5, 'remaining' => 5],
                            'spl' => ['used' => 0, 'limit' => 2, 'remaining' => 2],
                            'total' => ['used' => 0, 'limit' => 12, 'remaining' => 12],
                        ];
                        $totalUsed = $stat['total']['used'] ?? 0;
                        $pct = min(100, ($totalUsed / 12) * 100);
                    @endphp
                    <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid var(--navy-border); border-radius: 8px; padding: 0.85rem;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.35rem;">
                            <div>
                                <strong style="font-size: 0.85rem; color: var(--white);">{{ $emp->full_name }}</strong>
                                <div style="font-size: 0.7rem; color: var(--text-muted);">{{ $emp->position }}</div>
                            </div>
                            <span class="badge {{ $stat['total']['remaining'] > 0 ? 'badge-blue' : 'badge-danger' }}" style="font-size: 0.68rem; font-weight: 700;">
                                {{ $stat['total']['remaining'] }}/12d left
                            </span>
                        </div>

                        <!-- Progress Bar -->
                        <div style="height: 6px; width: 100%; background: rgba(255, 255, 255, 0.1); border-radius: 3px; overflow: hidden; margin-top: 6px;">
                            <div style="height: 100%; width: {{ $pct }}%; background: {{ $totalUsed >= 12 ? '#ef4444' : ($totalUsed >= 9 ? '#f59e0b' : '#10b981') }};"></div>
                        </div>

                        <!-- Sub-breakdown: VL, SL, SPL chips -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.35rem; margin-top: 0.65rem;">
                            <div style="background: rgba(59, 130, 246, 0.08); border: 1px solid rgba(59, 130, 246, 0.2); border-radius: 4px; padding: 0.25rem 0.35rem; text-align: center;">
                                <div style="font-size: 0.62rem; color: #60a5fa; font-weight: 600;">VL (Vacation)</div>
                                <div style="font-size: 0.72rem; font-weight: 700; color: {{ $stat['vl']['used'] >= 5 ? '#ef4444' : 'var(--white)' }};">
                                    {{ $stat['vl']['used'] }}/5
                                </div>
                            </div>
                            <div style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 4px; padding: 0.25rem 0.35rem; text-align: center;">
                                <div style="font-size: 0.62rem; color: #34d399; font-weight: 600;">SL (Sick)</div>
                                <div style="font-size: 0.72rem; font-weight: 700; color: {{ $stat['sl']['used'] >= 5 ? '#ef4444' : 'var(--white)' }};">
                                    {{ $stat['sl']['used'] }}/5
                                </div>
                            </div>
                            <div style="background: rgba(245, 186, 49, 0.08); border: 1px solid rgba(245, 186, 49, 0.2); border-radius: 4px; padding: 0.25rem 0.35rem; text-align: center;">
                                <div style="font-size: 0.62rem; color: #fbbf24; font-weight: 600;">SPL (Special)</div>
                                <div style="font-size: 0.72rem; font-weight: 700; color: {{ $stat['spl']['used'] >= 2 ? '#ef4444' : 'var(--white)' }};">
                                    {{ $stat['spl']['used'] }}/2
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Year-End Sick Leave (SL) Monetization & Cash Conversion Payout Table -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <div class="card-title-group">
                <h3 class="card-title" style="font-size: 0.95rem;">💵 Sick Leave (SL) Cash Monetization Payouts ({{ $year }})</h3>
                <span class="card-subtitle">Auto-archived every Jan 1 @ 1:00 AM for permanent file keeping. (Unused SL Days × Daily Rate). VL and SPL are non-convertible.</span>
            </div>
            <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                <form action="{{ route('admin.payroll.leaves.archive') }}" method="POST" style="display: inline;">
                    @csrf
                    <input type="hidden" name="year" value="{{ $year }}">
                    <button type="submit" class="btn btn-navy btn-sm" title="Take a permanent snapshot of this year's records for file keeping">
                        📁 Snapshot & Archive Year {{ $year }}
                    </button>
                </form>
                <a href="{{ route('admin.payroll.leaves.print_ledger', $year) }}" target="_blank" class="btn btn-gold btn-sm">
                    🖨️ Print Annual Ledger
                </a>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Daily Rate</th>
                            <th>SL Quota</th>
                            <th>SL Used</th>
                            <th>Unused SL (To Pay)</th>
                            <th>Cash Conversion Payout</th>
                            <th>Status & Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($slMonetization as $item)
                            <tr>
                                <td>
                                    <strong style="color: var(--white);">{{ $item['employee']->full_name }}</strong>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $item['employee']->position }}</div>
                                </td>
                                <td>₱{{ number_format($item['daily_rate'], 2) }}/day</td>
                                <td>5 days</td>
                                <td><strong>{{ $item['sl_used'] }}</strong> day(s)</td>
                                <td>
                                    <span class="badge {{ $item['sl_unused'] > 0 ? 'badge-success' : 'badge-navy' }}" style="font-size: 0.75rem;">
                                        {{ $item['sl_unused'] }} unused day(s)
                                    </span>
                                </td>
                                <td style="font-weight: 700; font-size: 0.9rem; color: {{ $item['cash_payout'] > 0 ? 'var(--gold-light)' : 'var(--text-muted)' }};">
                                    ₱{{ number_format($item['cash_payout'], 2) }}
                                </td>
                                <td>
                                    @if($item['is_credited'])
                                        <span class="badge badge-success" style="font-size: 0.72rem; padding: 0.35rem 0.65rem;">
                                            ✓ Credited to Payroll
                                        </span>
                                    @elseif($item['sl_unused'] > 0)
                                        <form action="{{ route('admin.payroll.leaves.convert_sl') }}" method="POST" style="display: inline;"
                                              onsubmit="return confirm('Credit ₱{{ number_format($item['cash_payout'], 2) }} ({{ $item['sl_unused'] }} unused SL days) to {{ $item['employee']->full_name }} payroll incentives?');">
                                            @csrf
                                            <input type="hidden" name="employee_id" value="{{ $item['employee']->id }}">
                                            <input type="hidden" name="year" value="{{ $year }}">
                                            <button type="submit" class="btn btn-gold btn-sm" style="padding: 0.25rem 0.65rem;">
                                                + Credit SL Payout
                                            </button>
                                        </form>
                                    @else
                                        <span style="font-size: 0.72rem; color: var(--text-muted);">Fully Consumed</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Leaves List Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-title-group">
                <h3 class="card-title">Leave History & Requests</h3>
                <span class="card-subtitle">All leave applications with quota verification and deduction status</span>
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
                            <th>Quota Check</th>
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
                                    @php
                                        $typeBadgeStyle = match($leave->leave_type) {
                                            'vacation_leave' => 'background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3);',
                                            'sick_leave' => 'background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3);',
                                            'special_leave' => 'background: rgba(245, 186, 49, 0.15); color: #fbbf24; border: 1px solid rgba(245, 186, 49, 0.3);',
                                            default => 'background: rgba(255, 255, 255, 0.1); color: var(--text-secondary);',
                                        };
                                        $typeLabel = match($leave->leave_type) {
                                            'vacation_leave' => 'Vacation Leave (VL)',
                                            'sick_leave' => 'Sick Leave (SL)',
                                            'special_leave' => 'Special Leave (SPL)',
                                            default => ucfirst(str_replace('_', ' ', $leave->leave_type)),
                                        };
                                    @endphp
                                    <span class="badge" style="{{ $typeBadgeStyle }} font-size: 0.72rem;">
                                        {{ $typeLabel }}
                                    </span>
                                </td>
                                <td>
                                    {{ $leave->start_date->format('M d') }} - {{ $leave->end_date->format('M d, Y') }}
                                </td>
                                <td><strong>{{ $leave->days_count }}</strong> day(s)</td>
                                <td>
                                    @if($leave->exceeded_limit)
                                        <span class="badge badge-danger" style="font-size: 0.68rem;" title="Exceeded specific type or annual limit">
                                            ⚠️ Exceeded Quota
                                        </span>
                                    @else
                                        <span class="badge badge-success" style="font-size: 0.68rem;">
                                            ✓ Within Quota
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
                                <td style="max-width: 240px; font-size: 0.75rem; color: var(--text-secondary);">
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
                                                    <option value="1" {{ $leave->is_paid ? 'selected' : '' }}>Paid Leave (Within quota or company granted)</option>
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

    <!-- Modal: File Leave Application (Enlarged & Select2 Searchable) -->
    <div class="modal-backdrop" id="modal-file-leave">
        <div class="modal-dialog modal-lg" style="max-width: 860px;">
            <div class="modal-header" style="background: linear-gradient(135deg, rgba(13, 30, 51, 0.95), rgba(26, 44, 76, 0.95)); border-bottom: 1.5px solid var(--gold-border); padding: 1.25rem 1.5rem;">
                <div class="modal-title-group">
                    <h4 class="modal-title" style="color: var(--gold-light); font-size: 1.2rem; display: flex; align-items: center; gap: 0.5rem;">
                        <span>🏖️</span> File Leave Application
                    </h4>
                    <span style="font-size: 0.78rem; color: var(--text-muted);">Select2 Searchable Staff Picker • 12-Day Annual Quota Tracking (5 VL, 5 SL, 2 SPL)</span>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form action="{{ route('admin.payroll.leaves.store') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding: 1.75rem;">
                    
                    <!-- 1. Employee Select2 Selector -->
                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label" style="font-weight: 700; color: var(--gold-light); font-size: 0.88rem; margin-bottom: 0.4rem; display: flex; justify-content: space-between;">
                            <span>👤 Select Employee / Staff Member <span class="req" style="color: #ef4444;">*</span></span>
                            <span style="font-size: 0.74rem; font-weight: normal; color: var(--text-muted);">Type name or EMP code to search</span>
                        </label>
                        <select name="employee_id" id="leave_employee_select" class="form-control select2" style="width: 100%;" required>
                            <option value="">-- Type or Select Employee --</option>
                            @foreach($employees as $emp)
                                @php
                                    $stat = $leaveStats[$emp->id] ?? [
                                        'vl' => ['remaining' => 5],
                                        'sl' => ['remaining' => 5],
                                        'spl' => ['remaining' => 2],
                                        'total' => ['remaining' => 12],
                                    ];
                                @endphp
                                <option value="{{ $emp->id }}"
                                    data-code="{{ $emp->employee_code }}"
                                    data-name="{{ $emp->full_name }}"
                                    data-position="{{ $emp->position }}"
                                    data-department="{{ $emp->department }}"
                                    data-vl="{{ $stat['vl']['remaining'] }}"
                                    data-sl="{{ $stat['sl']['remaining'] }}"
                                    data-spl="{{ $stat['spl']['remaining'] }}"
                                    data-total="{{ $stat['total']['remaining'] }}">
                                    {{ $emp->employee_code }} — {{ $emp->full_name }} ({{ $emp->position }} • {{ $emp->department }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Live Quota Balance Card (Shown when employee is selected) -->
                    <div id="emp_balance_card" style="display: none; background: rgba(13, 30, 51, 0.7); border: 1.5px solid rgba(212, 175, 55, 0.35); border-radius: 8px; padding: 1rem 1.25rem; margin-bottom: 1.25rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.65rem; border-bottom: 1px dashed rgba(255, 255, 255, 0.1); padding-bottom: 0.4rem;">
                            <span style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--gold-light); font-weight: 700;">
                                📊 Real-Time Annual Leave Quota for Year {{ $year }}
                            </span>
                            <span id="card_emp_name" style="font-size: 0.8rem; font-weight: 700; color: var(--white);"></span>
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem; text-align: center;">
                            <div style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 6px; padding: 0.6rem 0.5rem;">
                                <div style="font-size: 0.7rem; color: #93c5fd; font-weight: 700; text-transform: uppercase;">🌴 Vacation (VL)</div>
                                <div id="badge_emp_vl" style="font-size: 1.05rem; font-weight: 800; color: #60a5fa; margin-top: 2px;">5 / 5 days</div>
                                <div style="font-size: 0.65rem; color: var(--text-muted);">Non-convertible</div>
                            </div>
                            <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 6px; padding: 0.6rem 0.5rem;">
                                <div style="font-size: 0.7rem; color: #6ee7b7; font-weight: 700; text-transform: uppercase;">🩺 Sick (SL)</div>
                                <div id="badge_emp_sl" style="font-size: 1.05rem; font-weight: 800; color: #34d399; margin-top: 2px;">5 / 5 days</div>
                                <div style="font-size: 0.65rem; color: #10b981;">Cash convertible</div>
                            </div>
                            <div style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 6px; padding: 0.6rem 0.5rem;">
                                <div style="font-size: 0.7rem; color: #fcd34d; font-weight: 700; text-transform: uppercase;">⭐ Special (SPL)</div>
                                <div id="badge_emp_spl" style="font-size: 1.05rem; font-weight: 800; color: #fbbf24; margin-top: 2px;">2 / 2 days</div>
                                <div style="font-size: 0.65rem; color: var(--text-muted);">Non-convertible</div>
                            </div>
                            <div style="background: rgba(212, 175, 55, 0.1); border: 1px solid var(--gold-border); border-radius: 6px; padding: 0.6rem 0.5rem;">
                                <div style="font-size: 0.7rem; color: var(--gold-light); font-weight: 700; text-transform: uppercase;">📅 Total Left</div>
                                <div id="badge_emp_total" style="font-size: 1.05rem; font-weight: 800; color: var(--gold-primary); margin-top: 2px;">12 / 12 days</div>
                                <div style="font-size: 0.65rem; color: var(--text-muted);">Max 12d Quota</div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Leave Type Selection -->
                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label" style="font-weight: 700; color: var(--gold-light); font-size: 0.88rem; margin-bottom: 0.4rem;">
                            📋 Type of Leave Application <span class="req" style="color: #ef4444;">*</span>
                        </label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.85rem;">
                            <label style="cursor: pointer; border: 1.5px solid var(--navy-border); border-radius: 8px; padding: 0.85rem; background: rgba(7, 19, 34, 0.6); display: flex; flex-direction: column; gap: 0.25rem; transition: all 0.2s;">
                                <div style="display: flex; align-items: center; gap: 0.45rem;">
                                    <input type="radio" name="leave_type" value="vacation_leave" checked style="accent-color: #3b82f6;">
                                    <strong style="color: #60a5fa; font-size: 0.88rem;">🌴 Vacation (VL)</strong>
                                </div>
                                <span style="font-size: 0.72rem; color: var(--text-muted); margin-left: 1.35rem;">Max 5 days / year (Rest & Recreation)</span>
                            </label>

                            <label style="cursor: pointer; border: 1.5px solid var(--navy-border); border-radius: 8px; padding: 0.85rem; background: rgba(7, 19, 34, 0.6); display: flex; flex-direction: column; gap: 0.25rem; transition: all 0.2s;">
                                <div style="display: flex; align-items: center; gap: 0.45rem;">
                                    <input type="radio" name="leave_type" value="sick_leave" style="accent-color: #10b981;">
                                    <strong style="color: #34d399; font-size: 0.88rem;">🩺 Sick Leave (SL)</strong>
                                </div>
                                <span style="font-size: 0.72rem; color: #10b981; margin-left: 1.35rem;">Max 5 days (Cash-convertible if unused)</span>
                            </label>

                            <label style="cursor: pointer; border: 1.5px solid var(--navy-border); border-radius: 8px; padding: 0.85rem; background: rgba(7, 19, 34, 0.6); display: flex; flex-direction: column; gap: 0.25rem; transition: all 0.2s;">
                                <div style="display: flex; align-items: center; gap: 0.45rem;">
                                    <input type="radio" name="leave_type" value="special_leave" style="accent-color: #f59e0b;">
                                    <strong style="color: #fbbf24; font-size: 0.88rem;">⭐ Special Leave</strong>
                                </div>
                                <span style="font-size: 0.72rem; color: var(--text-muted); margin-left: 1.35rem;">Max 2 days / year (SPL Quota)</span>
                            </label>
                        </div>
                    </div>

                    <!-- 3. Leave Period & Days Count Display -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem; align-items: flex-end;">
                        <div class="form-group" style="margin: 0;">
                            <label class="form-label" style="font-weight: 700; color: var(--white); font-size: 0.84rem;">📅 Start Date *</label>
                            <input type="date" name="start_date" id="leave_start_date" class="form-control" value="{{ date('Y-m-d') }}" style="height: 40px; font-weight: 700;" required>
                        </div>

                        <div class="form-group" style="margin: 0;">
                            <label class="form-label" style="font-weight: 700; color: var(--white); font-size: 0.84rem;">📅 End Date *</label>
                            <input type="date" name="end_date" id="leave_end_date" class="form-control" value="{{ date('Y-m-d') }}" style="height: 40px; font-weight: 700;" required>
                        </div>

                        <div style="background: rgba(212, 175, 55, 0.08); border: 1.5px solid var(--gold-border); border-radius: 6px; padding: 0.5rem 0.85rem; height: 40px; display: flex; align-items: center; justify-content: space-between;">
                            <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">Total Duration:</span>
                            <span id="leave_days_display" style="font-size: 0.92rem; font-weight: 800; color: var(--gold-light);">1 day(s)</span>
                        </div>
                    </div>

                    <!-- 4. Reason / Notes -->
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 700; color: var(--white); font-size: 0.84rem;">📝 Reason / Justification *</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Provide medical or personal reason for filing leave application..." style="resize: vertical;" required></textarea>
                    </div>

                </div>
                <div class="modal-footer" style="padding: 1rem 1.5rem; background: rgba(13, 30, 51, 0.8); border-top: 1px solid var(--navy-border);">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold" style="padding: 0.5rem 1.5rem; font-weight: 700;">
                        <span>✓ File & Process Leave Application</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2 on Employee picker inside modal
    if ($.fn.select2) {
        $('#leave_employee_select').select2({
            dropdownParent: $('#modal-file-leave'),
            placeholder: '🔍 Search employee name or code...',
            allowClear: true,
            width: '100%'
        });
    }

    // Live update of balance card when employee is selected
    $('#leave_employee_select').on('change', function() {
        var opt = $(this).find(':selected');
        if (opt.val()) {
            var name = opt.data('name') || '';
            var vl = opt.data('vl') !== undefined ? opt.data('vl') : 5;
            var sl = opt.data('sl') !== undefined ? opt.data('sl') : 5;
            var spl = opt.data('spl') !== undefined ? opt.data('spl') : 2;
            var total = opt.data('total') !== undefined ? opt.data('total') : 12;

            $('#card_emp_name').text(name);
            $('#badge_emp_vl').text(vl + ' / 5 days');
            $('#badge_emp_sl').text(sl + ' / 5 days');
            $('#badge_emp_spl').text(spl + ' / 2 days');
            $('#badge_emp_total').text(total + ' / 12 days');
            $('#emp_balance_card').slideDown(150);
        } else {
            $('#emp_balance_card').slideUp(150);
        }
    });

    // Auto calculate days duration
    function calcLeaveDays() {
        var start = $('#leave_start_date').val();
        var end = $('#leave_end_date').val();
        if (start && end) {
            var d1 = new Date(start);
            var d2 = new Date(end);
            if (d2 >= d1) {
                var diffTime = Math.abs(d2 - d1);
                var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                $('#leave_days_display').text(diffDays + ' day(s)');
            } else {
                $('#leave_days_display').text('Invalid date range');
            }
        }
    }
    $('#leave_start_date, #leave_end_date').on('change', calcLeaveDays);
});
</script>
@endpush
