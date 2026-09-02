@extends('layouts.app')

@php
    $title = 'Timekeeping (DTR)';
    $headerTitle = 'Daily Time Record & Attendance Tracking';
    $breadcrumb = 'Payroll / Timekeeping';
@endphp

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--white); margin-bottom: 0.25rem;">Daily Time Records (DTR)</h2>
            <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0;">Record clock-in, clock-out, overtime (OT), and absence tracking for payroll computation.</p>
        </div>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <button type="button" class="btn btn-navy" data-modal-target="modal-batch-dtr">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span>⚡ Batch Generate Day</span>
            </button>
            <button type="button" class="btn btn-gold" data-modal-target="modal-add-dtr">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>+ Log Time Entry</span>
            </button>
        </div>
    </div>

    <!-- Summary Badges for Selected Date -->
    <div class="stat-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 1.5rem;">
        <div class="stat-card" style="padding: 1rem 1.25rem;">
            <span class="stat-title">Present on Duty</span>
            <div class="stat-value" style="color: #10b981; font-size: 1.5rem;">{{ $summary['present'] }}</div>
            <div class="stat-desc">Standard shift</div>
        </div>
        <div class="stat-card" style="padding: 1rem 1.25rem;">
            <span class="stat-title">Late / Tardy</span>
            <div class="stat-value" style="color: #f59e0b; font-size: 1.5rem;">{{ $summary['late'] }}</div>
            <div class="stat-desc">Deducted from base</div>
        </div>
        <div class="stat-card" style="padding: 1rem 1.25rem;">
            <span class="stat-title">Absent</span>
            <div class="stat-value" style="color: #ef4444; font-size: 1.5rem;">{{ $summary['absent'] }}</div>
            <div class="stat-desc">Unexcused absence</div>
        </div>
        <div class="stat-card" style="padding: 1rem 1.25rem;">
            <span class="stat-title">On Leave</span>
            <div class="stat-value" style="color: #38bdf8; font-size: 1.5rem;">{{ $summary['on_leave'] }}</div>
            <div class="stat-desc">Filed leave</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body" style="padding: 1rem 1.25rem;">
            <form method="GET" action="{{ route('admin.payroll.dtr.index') }}" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
                <div style="min-width: 180px;">
                    <label style="font-size: 0.72rem; color: var(--text-muted); display: block; margin-bottom: 2px;">Filter Date:</label>
                    <input type="date" name="date" class="form-control" value="{{ $selectedDate }}">
                </div>
                <div style="flex: 1; min-width: 220px;">
                    <label style="font-size: 0.72rem; color: var(--text-muted); display: block; margin-bottom: 2px;">Filter Staff Member:</label>
                    <select name="employee_id" class="form-control">
                        <option value="">-- All Active Employees --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ $selectedEmployeeId == $emp->id ? 'selected' : '' }}>
                                {{ $emp->employee_code }} - {{ $emp->full_name }} ({{ $emp->position }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div style="margin-top: 1.25rem;">
                    <button type="submit" class="btn btn-navy btn-sm">Filter DTR</button>
                    <a href="{{ route('admin.payroll.dtr.index', ['date' => now()->toDateString()]) }}" class="btn btn-ghost btn-sm">Today</a>
                </div>
            </form>
        </div>
    </div>

    <!-- DTR Table -->
    <div class="card">
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>EMP #</th>
                            <th>Staff Member</th>
                            <th>Position</th>
                            <th>Log Date</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Regular Hrs</th>
                            <th>OT Hours</th>
                            <th>Status</th>
                            <th>Notes</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $dtr)
                            <tr>
                                <td>
                                    <span style="font-family: monospace; font-weight: 700; color: var(--gold-light);">
                                        {{ $dtr->employee->employee_code }}
                                    </span>
                                </td>
                                <td><strong style="color: var(--white);">{{ $dtr->employee->full_name }}</strong></td>
                                <td><span class="badge badge-navy" style="font-size: 0.7rem;">{{ $dtr->employee->position }}</span></td>
                                <td>{{ $dtr->record_date->format('M d, Y') }}</td>
                                <td>
                                    <span style="font-family: monospace; font-weight: 600; color: var(--white);">
                                        {{ $dtr->time_in ? date('h:i A', strtotime($dtr->time_in)) : '--:--' }}
                                    </span>
                                    @if($dtr->late_minutes > 0)
                                        <div style="font-size: 0.68rem; color: #f59e0b;">Late: {{ $dtr->late_minutes }}m</div>
                                    @endif
                                </td>
                                <td>
                                    <span style="font-family: monospace; font-weight: 600; color: var(--white);">
                                        {{ $dtr->time_out ? date('h:i A', strtotime($dtr->time_out)) : '--:--' }}
                                    </span>
                                </td>
                                <td>{{ $dtr->regular_hours }} hrs</td>
                                <td>
                                    @if($dtr->ot_hours > 0)
                                        <span class="badge badge-gold" style="font-size: 0.7rem;">+{{ $dtr->ot_hours }} hrs OT</span>
                                    @else
                                        <span style="color: var(--text-muted); font-size: 0.8rem;">0</span>
                                    @endif
                                </td>
                                <td>
                                    @if($dtr->status === 'present')
                                        <span class="badge badge-success" style="font-size: 0.7rem;">Present</span>
                                    @elseif($dtr->status === 'late')
                                        <span class="badge badge-warning" style="font-size: 0.7rem;">Late</span>
                                    @elseif($dtr->status === 'on_leave')
                                        <span class="badge badge-blue" style="font-size: 0.7rem;">On Leave</span>
                                    @elseif($dtr->status === 'rest_day')
                                        <span class="badge badge-navy" style="font-size: 0.7rem;">Rest Day</span>
                                    @else
                                        <span class="badge badge-danger" style="font-size: 0.7rem;">Absent</span>
                                    @endif
                                </td>
                                <td style="font-size: 0.75rem; color: var(--text-muted);">{{ $dtr->notes ?: '-' }}</td>
                                <td>
                                    <div style="display: flex; gap: 0.35rem; align-items: center;">
                                        @if($dtr->status === 'absent')
                                            <button type="button" class="btn btn-gold btn-sm" style="padding: 0.2rem 0.55rem; font-size: 0.72rem; font-weight: 700;"
                                                data-modal-target="modal-edit-dtr-{{ $dtr->id }}" title="Log Time-In for this staff">
                                                ⏱️ Time-In
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-ghost btn-sm" style="padding: 0.25rem 0.45rem;"
                                                data-modal-target="modal-edit-dtr-{{ $dtr->id }}" title="Edit Attendance Times">
                                                ✏️
                                            </button>
                                        @endif

                                        <form action="{{ route('admin.payroll.dtr.destroy', $dtr) }}" method="POST"
                                            onsubmit="return confirm('Delete this DTR log entry?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-ghost btn-sm" style="padding: 0.25rem 0.45rem; color: #ef4444;" title="Delete">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- Modal: Log Time-In / Edit DTR for this row -->
                            <div class="modal-backdrop" id="modal-edit-dtr-{{ $dtr->id }}">
                                <div class="modal-dialog modal-lg" style="max-width: 680px;">
                                    <div class="modal-header">
                                        <div class="modal-title-group">
                                            <h4 class="modal-title">Time-In / Attendance Log: {{ $dtr->employee->full_name }}</h4>
                                            <span style="font-size: 0.72rem; color: var(--gold-light);">Date: {{ $dtr->record_date->format('F d, Y') }} ({{ $dtr->employee->position }})</span>
                                        </div>
                                        <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
                                    </div>
                                    <form action="{{ route('admin.payroll.dtr.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="employee_id" value="{{ $dtr->employee_id }}">
                                        <input type="hidden" name="record_date" value="{{ $dtr->record_date->format('Y-m-d') }}">
                                        <div class="modal-body" style="padding: 1.5rem;">
                                            <div style="background: rgba(212, 175, 55, 0.08); border: 1px solid var(--gold-border); border-radius: 6px; padding: 0.75rem 1rem; margin-bottom: 1rem;">
                                                <div style="font-size: 0.78rem; color: var(--text-muted);">Current Status:</div>
                                                <div style="font-weight: 700; color: var(--white); font-size: 0.95rem;">
                                                    {{ ucfirst(str_replace('_', ' ', $dtr->status)) }} 
                                                    @if($dtr->status === 'absent')
                                                        <span style="color: #ef4444; font-size: 0.75rem;">(Unlogged - Enter Time In to mark as Present)</span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="form-label">Attendance Status *</label>
                                                <select name="status" class="form-control" required>
                                                    <option value="present" {{ ($dtr->status === 'present' || $dtr->status === 'absent') ? 'selected' : '' }}>Present (Attended)</option>
                                                    <option value="late" {{ $dtr->status === 'late' ? 'selected' : '' }}>Late Arrival</option>
                                                    <option value="absent">Absent</option>
                                                    <option value="on_leave" {{ $dtr->status === 'on_leave' ? 'selected' : '' }}>On Leave</option>
                                                    <option value="rest_day" {{ $dtr->status === 'rest_day' ? 'selected' : '' }}>Rest Day</option>
                                                </select>
                                            </div>

                                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.75rem; margin-top: 0.75rem;">
                                                <div class="form-group">
                                                    <label class="form-label">Time In (24h)</label>
                                                    <input type="time" name="time_in" class="form-control" value="{{ $dtr->time_in ? date('H:i', strtotime($dtr->time_in)) : '08:00' }}">
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label">Time Out (24h)</label>
                                                    <input type="time" name="time_out" class="form-control" value="{{ $dtr->time_out ? date('H:i', strtotime($dtr->time_out)) : '17:00' }}">
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label">OT Hours</label>
                                                    <input type="number" step="0.5" name="ot_hours" class="form-control" value="{{ $dtr->ot_hours ?? 0.0 }}" min="0">
                                                </div>
                                            </div>

                                            <div class="form-group" style="margin-top: 0.75rem;">
                                                <label class="form-label">Notes / Remarks</label>
                                                <input type="text" name="notes" class="form-control" value="{{ $dtr->status === 'absent' ? 'Logged attendance' : $dtr->notes }}">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                                            <button type="submit" class="btn btn-gold">Save Time-In Record</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="11" style="text-align: center; color: var(--text-muted); padding: 2.5rem;">
                                    No attendance records found for this selection. Use "+ Log Time Entry" or "⚡ Batch Generate Day".
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($records->hasPages())
                <div style="padding: 1rem;">
                    {{ $records->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal: Add / Log DTR Entry -->
    <div class="modal-backdrop" id="modal-add-dtr">
        <div class="modal-dialog" style="max-width: 550px;">
            <div class="modal-header">
                <div class="modal-title-group">
                    <h4 class="modal-title">Log Employee Attendance</h4>
                    <span style="font-size: 0.72rem; color: var(--gold-light);">DTR Time In, Time Out, and Overtime</span>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form action="{{ route('admin.payroll.dtr.store') }}" method="POST">
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
                            <label class="form-label">Record Date *</label>
                            <input type="date" name="record_date" class="form-control" value="{{ $selectedDate }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Attendance Status *</label>
                            <select name="status" class="form-control" required>
                                <option value="present" selected>Present (Normal)</option>
                                <option value="late">Late Arrival</option>
                                <option value="absent">Absent</option>
                                <option value="on_leave">On Leave</option>
                                <option value="rest_day">Rest Day</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.75rem; margin-top: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Time In (24h)</label>
                            <input type="time" name="time_in" class="form-control" value="08:00">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Time Out (24h)</label>
                            <input type="time" name="time_out" class="form-control" value="17:00">
                        </div>
                        <div class="form-group">
                            <label class="form-label">OT Hours</label>
                            <input type="number" step="0.5" name="ot_hours" class="form-control" value="0.0" min="0">
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 0.75rem;">
                        <label class="form-label">Notes / Remarks</label>
                        <input type="text" name="notes" class="form-control" placeholder="e.g. Approved overtime for emergency surgery">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold">Save Attendance Log</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Batch Generate DTR -->
    <div class="modal-backdrop" id="modal-batch-dtr">
        <div class="modal-dialog" style="max-width: 500px;">
            <div class="modal-header">
                <div class="modal-title-group">
                    <h4 class="modal-title">⚡ Batch Generate Attendance</h4>
                    <span style="font-size: 0.72rem; color: var(--gold-light);">Create standard 8:00 AM - 5:00 PM entries for all staff</span>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form action="{{ route('admin.payroll.dtr.batch') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label">Select Date to Populate *</label>
                        <input type="date" name="date" class="form-control" value="{{ $selectedDate }}" required>
                    </div>
                    <div class="form-group" style="margin-top: 0.75rem;">
                        <label class="form-label">Default Attendance Status *</label>
                        <select name="default_status" class="form-control" required>
                            <option value="present" selected>Present (8:00 AM - 5:00 PM, 8 hrs)</option>
                            <option value="rest_day">Rest Day (0 hrs)</option>
                        </select>
                    </div>
                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 1rem;">
                        Note: Existing entries on this date will NOT be overwritten. Only missing staff records will be created.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-navy">Generate Batch Records</button>
                </div>
            </form>
        </div>
    </div>
@endsection
