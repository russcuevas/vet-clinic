@extends('layouts.app')

@php
    $title = 'Timekeeping (DTR)';
    $headerTitle = 'Daily Time Record & Attendance Tracking';
    $breadcrumb = 'Payroll / Timekeeping';
@endphp

@section('content')
    <!-- Top Action & Info Bar -->
    <div
        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--white); margin-bottom: 0.25rem;">Daily Time Records
                (DTR)</h2>
            <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0;">Separate stations for Time In and Time Out
                with automatic duty hours & undertime calculation.</p>
        </div>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <button type="button" class="btn btn-navy" data-modal-target="modal-payroll-multipliers" style="border: 1.5px solid var(--gold); color: var(--gold-light); font-weight: 700;">
                <span>⚙️ Rate Multipliers</span>
            </button>
            <button type="button" class="btn btn-navy" data-modal-target="modal-batch-dtr">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span>⚡ Batch Generate Shift Day</span>
            </button>
            <button type="button" class="btn btn-gold" data-modal-target="modal-add-dtr">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>+ Custom DTR Entry</span>
            </button>
        </div>
    </div>

    <!-- Date Navigation & Quick Filter Strip -->
    <div class="card" style="margin-bottom: 1.25rem;">
        <div class="card-body" style="padding: 0.85rem 1.25rem;">
            <form method="GET" action="{{ route('admin.payroll.dtr.index') }}" id="dtr-date-form"
                style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                    <label style="font-weight: 700; color: var(--gold-light); font-size: 0.85rem; margin: 0;">📅 Active
                        Date:</label>
                    <input type="date" name="date" class="form-control" value="{{ $selectedDate }}"
                        style="width: auto; font-weight: 700;" onchange="document.getElementById('dtr-date-form').submit()">
                    <a href="{{ route('admin.payroll.dtr.index', ['date' => now()->toDateString()]) }}"
                        class="btn btn-ghost btn-sm" style="font-size: 0.75rem;">
                        Today ({{ now()->format('M d') }})
                    </a>
                </div>

                <!-- Stats summary chips for active date -->
                <div style="display: flex; gap: 0.4rem; flex-wrap: wrap; font-size: 0.75rem;">
                    @php
                        $dayPresent = $dayRecords->whereIn('status', ['present', 'late'])->count();
                        $dayLate = $dayRecords->where('late_minutes', '>', 0)->count();
                        $dayUndertime = $dayRecords->where('undertime_minutes', '>', 0)->count();
                        $dayAwaitingOut = $dayRecords->whereNotNull('time_in')->whereNull('time_out')->count();
                        $dayRestDay = $dayRecords->where('status', 'rest_day')->count();
                        $dayHoliday = $dayRecords->whereIn('status', ['holiday', 'regular_holiday', 'special_holiday'])->count();
                        $dayOvertime = $dayRecords->where('status', 'overtime')->count();
                        $dayOffDuty = $dayRecords->where('status', 'off_duty')->count();
                    @endphp
                    <span class="badge badge-success">🟢 Present: {{ $dayPresent }}</span>
                    <span class="badge badge-warning">⚠️ Late: {{ $dayLate }}</span>
                    <span class="badge badge-danger">⏳ Undertime: {{ $dayUndertime }}</span>
                    <span class="badge badge-navy" style="border: 1px solid rgba(59, 130, 246, 0.4); color: #93c5fd;">🏖️ Rest Day: {{ $dayRestDay }}</span>
                    @if($dayHoliday > 0)
                        <span class="badge" style="background: rgba(236, 72, 153, 0.15); color: #f472b6; border: 1px solid rgba(236, 72, 153, 0.35);">🎉 Holiday: {{ $dayHoliday }}</span>
                    @endif
                    @if($dayOvertime > 0)
                        <span class="badge" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35);">⏱️ Overtime: {{ $dayOvertime }}</span>
                    @endif
                    @if($dayOffDuty > 0)
                        <span class="badge" style="background: rgba(148, 163, 184, 0.12); color: #cbd5e1; border: 1px solid rgba(148, 163, 184, 0.35);">💤 Off Duty: {{ $dayOffDuty }}</span>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== TAB NAVIGATION BAR ==================== -->
    <div
        style="display: flex; gap: 0.5rem; border-bottom: 2px solid var(--navy-border); margin-bottom: 1.25rem; overflow-x: auto; padding-bottom: 2px;">
        <button type="button" class="tab-btn active" id="tab-btn-timein" onclick="switchDtrTab('timein')"
            style="padding: 0.75rem 1.25rem; font-weight: 700; font-size: 0.9rem; border-radius: 8px 8px 0 0; cursor: pointer; border: 1px solid transparent; background: transparent; color: var(--text-muted); display: flex; align-items: center; gap: 0.5rem; transition: all 0.2s;">
            <span>🟢 1. Time In Station</span>
            <span class="badge badge-navy" style="font-size: 0.72rem;">{{ $employees->count() }} Staff</span>
        </button>

        <button type="button" class="tab-btn" id="tab-btn-timeout" onclick="switchDtrTab('timeout')"
            style="padding: 0.75rem 1.25rem; font-weight: 700; font-size: 0.9rem; border-radius: 8px 8px 0 0; cursor: pointer; border: 1px solid transparent; background: transparent; color: var(--text-muted); display: flex; align-items: center; gap: 0.5rem; transition: all 0.2s;">
            <span>🏁 2. Time Out Station</span>
            <span class="badge badge-gold" style="font-size: 0.72rem;">{{ $dayAwaitingOut }} On Duty</span>
        </button>

        <button type="button" class="tab-btn" id="tab-btn-master" onclick="switchDtrTab('master')"
            style="padding: 0.75rem 1.25rem; font-weight: 700; font-size: 0.9rem; border-radius: 8px 8px 0 0; cursor: pointer; border: 1px solid transparent; background: transparent; color: var(--text-muted); display: flex; align-items: center; gap: 0.5rem; transition: all 0.2s;">
            <span>📋 3. Master Attendance Log</span>
        </button>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 1: TIME IN STATION (Morning / Clock-In) -->
    <!-- ========================================================================= -->
    <div id="tab-pane-timein" class="dtr-tab-pane">
        <div class="card" style="border: 1px solid var(--navy-border);">
            <div class="card-header"
                style="background: rgba(16, 185, 129, 0.08); border-bottom: 1px solid rgba(16, 185, 129, 0.2); padding: 1rem 1.25rem;">
                <div class="card-title-group">
                    <h3 class="card-title"
                        style="color: #10b981; font-size: 1.1rem; display: flex; align-items: center; gap: 0.4rem;">
                        <span>🟢</span> Morning Clock-In Station
                    </h3>
                    <span class="card-subtitle">Log or edit Time In entries for
                        {{ \Carbon\Carbon::parse($selectedDate)->format('l, F d, Y') }}. Tardiness is auto-detected against
                        employee shifts.</span>
                </div>
            </div>

            <div class="card-body" style="padding: 0;">
                <div style="overflow-x: auto; width: 100%;">
                    <table class="dtr-station-table" style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr style="background: #0d1e33; border-bottom: 1.5px solid var(--navy-border);">
                                <th
                                    style="padding: 12px 16px; color: var(--gold-light); font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; width: 140px;">
                                    EMP #</th>
                                <th
                                    style="padding: 12px 16px; color: var(--gold-light); font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; min-width: 200px;">
                                    Employee</th>
                                <th
                                    style="padding: 12px 16px; color: var(--gold-light); font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; min-width: 180px;">
                                    Assigned Shift</th>
                                <th
                                    style="padding: 12px 16px; color: var(--gold-light); font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; min-width: 170px;">
                                    Clock-In Status</th>
                                <th
                                    style="padding: 12px 16px; color: var(--gold-light); font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; min-width: 420px;">
                                    Time In Selection & Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($employees as $emp)
                                @php
                                    $dtr = $dayRecords->firstWhere('employee_id', $emp->id);
                                    $hasTimedIn = $dtr && !empty($dtr->time_in);
                                    $currentStatus = $dtr ? $dtr->status : '';
                                    $isRestDay = $currentStatus === 'rest_day';
                                    $isOvertime = $currentStatus === 'overtime';
                                    $isHoliday = in_array($currentStatus, ['holiday', 'regular_holiday']);
                                    $isSpecialHoliday = $currentStatus === 'special_holiday';
                                    $isOffDuty = $currentStatus === 'off_duty';
                                    $isOnLeave = $currentStatus === 'on_leave';
                                    $shiftStartFormatted = $emp->shift_start
                                        ? \Carbon\Carbon::parse($emp->shift_start)->format('g:i A')
                                        : '9:00 AM';
                                    $shiftEndFormatted = $emp->shift_end
                                        ? \Carbon\Carbon::parse($emp->shift_end)->format('g:i A')
                                        : '6:00 PM';
                                    $defaultTimeInVal = $hasTimedIn
                                        ? date('H:i', strtotime($dtr->time_in))
                                        : ($emp->shift_start ?: '09:00');
                                @endphp
                                <tr
                                    style="border-bottom: 1px solid rgba(255, 255, 255, 0.05); {{ $hasTimedIn ? 'background: rgba(16, 185, 129, 0.03);' : ($isRestDay ? 'background: rgba(59, 130, 246, 0.03);' : ($isHoliday ? 'background: rgba(236, 72, 153, 0.03);' : '')) }}">
                                    <td style="padding: 14px 16px; vertical-align: middle;">
                                        <span
                                            style="font-family: monospace; font-weight: 700; color: var(--gold-light); font-size: 0.88rem;">
                                            {{ $emp->employee_code }}
                                        </span>
                                    </td>
                                    <td style="padding: 14px 16px; vertical-align: middle;">
                                        <div style="font-weight: 700; color: var(--white); font-size: 0.95rem;">
                                            {{ $emp->full_name }}</div>
                                        <div style="font-size: 0.74rem; color: var(--text-muted); margin-top: 2px;">
                                            {{ $emp->position }} • {{ $emp->department }}</div>
                                    </td>
                                    <td style="padding: 14px 16px; vertical-align: middle;">
                                        <span class="badge badge-navy"
                                            style="font-size: 0.78rem; font-weight: 700; padding: 4px 8px;">
                                            ⏰ {{ $shiftStartFormatted }} - {{ $shiftEndFormatted }}
                                        </span>
                                    </td>
                                    <td style="padding: 14px 16px; vertical-align: middle;">
                                        @if ($hasTimedIn)
                                            <div>
                                                <span class="badge badge-success"
                                                    style="font-size: 0.82rem; font-weight: 700;">
                                                    🟢 {{ date('h:i A', strtotime($dtr->time_in)) }}
                                                </span>
                                            </div>
                                            @if ($isHoliday)
                                                <div style="margin-top: 3px;"><span class="badge" style="background: rgba(236, 72, 153, 0.2); color: #f472b6; border: 1px solid rgba(236, 72, 153, 0.4); font-size: 0.72rem;">🎉 Holiday (x{{ $payrollSettings['holiday_multiplier']->multiplier ?? '2.0' }})</span></div>
                                            @elseif ($isSpecialHoliday)
                                                <div style="margin-top: 3px;"><span class="badge" style="background: rgba(56, 189, 248, 0.2); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.4); font-size: 0.72rem;">✨ Special Holiday (x{{ $payrollSettings['special_holiday_multiplier']->multiplier ?? '1.3' }})</span></div>
                                            @elseif ($isRestDay)
                                                <div style="margin-top: 3px;"><span class="badge" style="background: rgba(59, 130, 246, 0.2); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.4); font-size: 0.72rem;">🏖️ Rest Day Duty (x{{ $payrollSettings['rest_day_multiplier']->multiplier ?? '1.3' }})</span></div>
                                            @elseif ($isOvertime)
                                                <div style="margin-top: 3px;"><span class="badge" style="background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); font-size: 0.72rem;">⏱️ Overtime Tagged</span></div>
                                            @elseif ($dtr->late_minutes > 0)
                                                <div
                                                    style="font-size: 0.74rem; color: #f59e0b; font-weight: 700; margin-top: 3px;">
                                                    ⚠️ Late: {{ $dtr->formatted_late }}
                                                </div>
                                            @else
                                                <div style="font-size: 0.72rem; color: #10b981; margin-top: 3px;">✓ On Time
                                                </div>
                                            @endif
                                        @elseif ($isRestDay)
                                            <span class="badge badge-navy"
                                                style="font-size: 0.80rem; padding: 4px 8px; border: 1px solid rgba(59, 130, 246, 0.4); color: #93c5fd; background: rgba(59, 130, 246, 0.12);">
                                                🏖️ Rest Day (x{{ $payrollSettings['rest_day_multiplier']->multiplier ?? '1.3' }})
                                            </span>
                                        @elseif ($isOvertime)
                                            <span class="badge badge-warning"
                                                style="font-size: 0.80rem; padding: 4px 8px; border: 1px solid rgba(245, 158, 11, 0.4); color: #fef08a; background: rgba(245, 158, 11, 0.15);">
                                                ⏱️ Overtime Tagged
                                            </span>
                                        @elseif ($isHoliday)
                                            <span class="badge"
                                                style="font-size: 0.80rem; padding: 4px 8px; border: 1px solid rgba(236, 72, 153, 0.4); color: #f472b6; background: rgba(236, 72, 153, 0.15);">
                                                🎉 Regular Holiday (x{{ $payrollSettings['holiday_multiplier']->multiplier ?? '2.0' }})
                                            </span>
                                        @elseif ($isSpecialHoliday)
                                            <span class="badge"
                                                style="font-size: 0.80rem; padding: 4px 8px; border: 1px solid rgba(56, 189, 248, 0.4); color: #38bdf8; background: rgba(56, 189, 248, 0.15);">
                                                ✨ Special Holiday (x{{ $payrollSettings['special_holiday_multiplier']->multiplier ?? '1.3' }})
                                            </span>
                                        @elseif ($isOffDuty)
                                            <span class="badge"
                                                style="font-size: 0.80rem; padding: 4px 8px; border: 1px solid rgba(148, 163, 184, 0.4); color: #cbd5e1; background: rgba(148, 163, 184, 0.12);">
                                                💤 Off Duty
                                            </span>
                                        @elseif ($isOnLeave)
                                            <span class="badge badge-blue" style="font-size: 0.80rem; padding: 4px 8px;">
                                                🏖️ On Leave
                                            </span>
                                        @else
                                            <span class="badge badge-danger"
                                                style="font-size: 0.75rem; padding: 4px 8px;">
                                                Not Clocked In Yet
                                            </span>
                                        @endif
                                    </td>
                                    <td style="padding: 14px 16px; vertical-align: middle;">
                                        <div
                                            style="display: flex; align-items: center; gap: 6px; margin: 0; flex-wrap: nowrap;">
                                            <!-- Clock In Form -->
                                            <form action="{{ route('admin.payroll.dtr.store') }}" method="POST"
                                                style="display: flex; align-items: center; gap: 6px; margin: 0; flex-wrap: nowrap;">
                                                @csrf
                                                <input type="hidden" name="employee_id" value="{{ $emp->id }}">
                                                <input type="hidden" name="record_date" value="{{ $selectedDate }}">
                                                <input type="hidden" name="status" value="{{ in_array($currentStatus, ['holiday', 'special_holiday', 'overtime', 'rest_day']) ? $currentStatus : 'present' }}">
                                                @if ($dtr && $dtr->time_out)
                                                    <input type="hidden" name="time_out" value="{{ $dtr->time_out }}">
                                                @endif

                                                <!-- Exact Inputtable Time Picker -->
                                                <input type="time" name="time_in" id="time_in_{{ $emp->id }}"
                                                    class="form-control" value="{{ $defaultTimeInVal }}"
                                                    style="width: 125px; height: 38px; font-weight: 700; font-size: 0.90rem; background: #071322; border: 1.5px solid rgba(212, 175, 55, 0.45); border-radius: 6px; color: #fff; text-align: center;"
                                                    required>

                                                <button type="button" class="btn btn-navy btn-sm"
                                                    style="height: 38px; padding: 0 8px; font-size: 0.75rem; font-weight: 700; white-space: nowrap;"
                                                    onclick="setExactNow('time_in_{{ $emp->id }}')"
                                                    title="Set Current Live Time (Hours & Minutes)">
                                                    ⚡ Now
                                                </button>

                                                <button type="submit"
                                                    class="btn {{ $hasTimedIn ? 'btn-ghost' : 'btn-gold' }}"
                                                    style="height: 38px; padding: 0 12px; font-size: 0.82rem; font-weight: 700; white-space: nowrap;">
                                                    {{ $hasTimedIn ? 'Update In' : '⏱️ Clock In' }}
                                                </button>
                                            </form>

                                            <!-- Status Tag Dropdown (Rest Day, Overtime, Holiday, Special Holiday, Off Duty) -->
                                            <form action="{{ route('admin.payroll.dtr.store') }}" method="POST" id="tag_form_{{ $emp->id }}" style="margin: 0; display: inline-flex; align-items: center;">
                                                @csrf
                                                <input type="hidden" name="employee_id" value="{{ $emp->id }}">
                                                <input type="hidden" name="record_date" value="{{ $selectedDate }}">
                                                <input type="hidden" name="status" id="tag_status_{{ $emp->id }}" value="{{ $currentStatus }}">

                                                <div style="position: relative; display: inline-block;">
                                                    <select class="status-tag-dropdown"
                                                        id="status_select_{{ $emp->id }}"
                                                        data-emp-name="{{ addslashes($emp->full_name) }}"
                                                        data-original-val="{{ $currentStatus }}"
                                                        onchange="handleDtrStatusDropdownChange(this, '{{ addslashes($emp->full_name) }}', 'tag_form_{{ $emp->id }}', 'tag_status_{{ $emp->id }}')"
                                                        style="height: 38px; font-size: 0.80rem; font-weight: 700; border-radius: 6px; padding: 0 28px 0 10px; cursor: pointer; outline: none; appearance: none; -webkit-appearance: none; -moz-appearance: none; background: #071322; border: 1.5px solid {{ $isRestDay || $isHoliday || $isSpecialHoliday || $isOvertime || $isOffDuty ? '#d4af37' : 'rgba(212, 175, 55, 0.4)' }}; color: {{ $isHoliday ? '#f472b6' : ($isSpecialHoliday ? '#38bdf8' : ($isRestDay ? '#93c5fd' : ($isOvertime ? '#fbbf24' : ($isOffDuty ? '#94a3b8' : '#e2e8f0')))) }}; background-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23d4af37'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E&quot;); background-repeat: no-repeat; background-position: right 8px center; background-size: 13px;">
                                                        <option value="" disabled {{ empty($currentStatus) || in_array($currentStatus, ['present', 'late']) ? 'selected' : '' }} style="background: #0d1e33; color: #64748b;">🏷️ Duty Tag...</option>
                                                        <option value="rest_day" {{ $currentStatus === 'rest_day' ? 'selected' : '' }} style="background: #0d1e33; color: #93c5fd;">🏖️ Rest Day</option>
                                                        <option value="overtime" {{ $currentStatus === 'overtime' ? 'selected' : '' }} style="background: #0d1e33; color: #fbbf24;">⏱️ Overtime</option>
                                                        <option value="holiday" {{ in_array($currentStatus, ['holiday', 'regular_holiday']) ? 'selected' : '' }} style="background: #0d1e33; color: #f472b6;">🎉 Holiday</option>
                                                        <option value="special_holiday" {{ $currentStatus === 'special_holiday' ? 'selected' : '' }} style="background: #0d1e33; color: #38bdf8;">✨ Special Holiday</option>
                                                        <option value="off_duty" {{ $currentStatus === 'off_duty' ? 'selected' : '' }} style="background: #0d1e33; color: #cbd5e1;">💤 Off Duty</option>
                                                        @if(!empty($currentStatus) && !in_array($currentStatus, ['present', 'late']))
                                                            <option value="present" style="background: #0d1e33; color: #10b981;">🔄 Clear Tag (Normal Present)</option>
                                                        @endif
                                                    </select>
                                                </div>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 2: TIME OUT STATION (Evening / Clock-Out & Undertime Calculator) -->
    <!-- ========================================================================= -->
    <div id="tab-pane-timeout" class="dtr-tab-pane" style="display: none;">
        <div class="card" style="border: 1px solid var(--navy-border);">
            <div class="card-header"
                style="background: rgba(212, 175, 55, 0.08); border-bottom: 1px solid var(--gold-border); padding: 1rem 1.25rem;">
                <div class="card-title-group">
                    <h3 class="card-title"
                        style="color: var(--gold-light); font-size: 1.1rem; display: flex; align-items: center; gap: 0.4rem;">
                        <span>🏁</span> End-of-Shift Clock-Out Station
                    </h3>
                    <span class="card-subtitle">Log exact Time Out hours and minutes to automatically calculate worked duty
                        hours, undertime penalties, and overtime.</span>
                </div>
            </div>

            <div class="card-body" style="padding: 0;">
                <div style="overflow-x: auto; width: 100%;">
                    <table class="dtr-station-table" style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr style="background: #0d1e33; border-bottom: 1.5px solid var(--navy-border);">
                                <th
                                    style="padding: 12px 16px; color: var(--gold-light); font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; width: 140px;">
                                    EMP #</th>
                                <th
                                    style="padding: 12px 16px; color: var(--gold-light); font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; min-width: 190px;">
                                    Employee</th>
                                <th
                                    style="padding: 12px 16px; color: var(--gold-light); font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; min-width: 180px;">
                                    Shift & Time In</th>
                                <th
                                    style="padding: 12px 16px; color: var(--gold-light); font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; min-width: 180px;">
                                    Duty Hours & UT/OT</th>
                                <th
                                    style="padding: 12px 16px; color: var(--gold-light); font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; min-width: 440px;">
                                    Exact Time Out & Undertime Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($employees as $emp)
                                @php
                                    $dtr = $dayRecords->firstWhere('employee_id', $emp->id);
                                    $hasTimedIn = $dtr && !empty($dtr->time_in);
                                    $hasTimedOut = $dtr && !empty($dtr->time_out);
                                    $isRestDay = $dtr && $dtr->status === 'rest_day';
                                    $isOnLeave = $dtr && $dtr->status === 'on_leave';
                                    $shiftStartFormatted = $emp->shift_start
                                        ? \Carbon\Carbon::parse($emp->shift_start)->format('g:i A')
                                        : '9:00 AM';
                                    $shiftEndFormatted = $emp->shift_end
                                        ? \Carbon\Carbon::parse($emp->shift_end)->format('g:i A')
                                        : '6:00 PM';
                                    $defaultTimeOutVal = $hasTimedOut
                                        ? date('H:i', strtotime($dtr->time_out))
                                        : ($emp->shift_end ?:
                                        '18:00');
                                @endphp
                                <tr
                                    style="border-bottom: 1px solid rgba(255, 255, 255, 0.05); {{ $hasTimedOut ? 'background: rgba(212, 175, 55, 0.03);' : ($isRestDay ? 'background: rgba(59, 130, 246, 0.03);' : '') }}">
                                    <td style="padding: 14px 16px; vertical-align: middle;">
                                        <span
                                            style="font-family: monospace; font-weight: 700; color: var(--gold-light); font-size: 0.88rem;">
                                            {{ $emp->employee_code }}
                                        </span>
                                    </td>
                                    <td style="padding: 14px 16px; vertical-align: middle;">
                                        <div style="font-weight: 700; color: var(--white); font-size: 0.95rem;">
                                            {{ $emp->full_name }}</div>
                                        <div style="font-size: 0.74rem; color: var(--text-muted); margin-top: 2px;">
                                            {{ $emp->position }}</div>
                                    </td>
                                    <td style="padding: 14px 16px; vertical-align: middle;">
                                        <div style="font-size: 0.76rem; color: var(--text-muted);">Shift:
                                            {{ $shiftStartFormatted }} - {{ $shiftEndFormatted }}</div>
                                        @if ($hasTimedIn)
                                            <div
                                                style="font-size: 0.82rem; font-weight: 700; color: #10b981; margin-top: 3px;">
                                                🟢 In: {{ date('h:i A', strtotime($dtr->time_in)) }}
                                            </div>
                                        @elseif ($isRestDay)
                                            <div
                                                style="font-size: 0.80rem; font-weight: 700; color: #93c5fd; margin-top: 3px;">
                                                🏖️ Rest Day
                                            </div>
                                        @elseif ($isOnLeave)
                                            <div
                                                style="font-size: 0.80rem; font-weight: 700; color: #60a5fa; margin-top: 3px;">
                                                🏖️ On Leave
                                            </div>
                                        @else
                                            <div
                                                style="font-size: 0.74rem; color: #ef4444; font-weight: 700; margin-top: 3px;">
                                                ⚠️ No Time In Logged</div>
                                        @endif
                                    </td>
                                    <td style="padding: 14px 16px; vertical-align: middle;">
                                        @if ($hasTimedOut)
                                            <strong style="color: var(--gold-primary); font-size: 0.95rem;">
                                                {{ number_format($dtr->regular_hours, 2) }} hrs
                                            </strong>
                                            @if ($dtr->undertime_minutes > 0)
                                                <div
                                                    style="font-size: 0.74rem; color: #ef4444; font-weight: 700; margin-top: 2px;">
                                                    ⏳ Undertime: {{ $dtr->formatted_undertime }}
                                                </div>
                                                @if ($dtr->undertime_reason)
                                                    <div
                                                        style="font-size: 0.72rem; color: var(--gold-light); font-style: italic; margin-top: 1px;">
                                                        📝 {{ $dtr->undertime_reason }}
                                                    </div>
                                                @endif
                                            @endif
                                            @if ($dtr->ot_hours > 0)
                                                <div
                                                    style="font-size: 0.74rem; color: var(--gold-light); font-weight: 700; margin-top: 2px;">
                                                    ⚡ +{{ number_format($dtr->ot_hours, 2) }}h OT
                                                </div>
                                            @endif
                                            @if ($dtr->undertime_minutes == 0 && $dtr->ot_hours == 0)
                                                <div style="font-size: 0.72rem; color: #10b981; margin-top: 2px;">✓
                                                    Completed Full Shift</div>
                                            @endif
                                        @elseif ($isRestDay)
                                            <span class="badge badge-navy"
                                                style="font-size: 0.75rem; padding: 4px 8px; color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.4);">🏖️
                                                Rest Day</span>
                                        @elseif ($isOnLeave)
                                            <span class="badge badge-blue"
                                                style="font-size: 0.75rem; padding: 4px 8px;">🏖️ On Leave</span>
                                        @elseif ($hasTimedIn)
                                            <span class="badge badge-warning"
                                                style="font-size: 0.75rem; padding: 4px 8px;">Currently on Duty</span>
                                        @else
                                            <span style="color: var(--text-muted); font-size: 0.78rem;">--</span>
                                        @endif
                                    </td>
                                    <td style="padding: 14px 16px; vertical-align: middle;">
                                        <form action="{{ route('admin.payroll.dtr.store') }}" method="POST"
                                            style="display: flex; align-items: center; gap: 6px; margin: 0; flex-wrap: wrap;">
                                            @csrf
                                            <input type="hidden" name="employee_id" value="{{ $emp->id }}">
                                            <input type="hidden" name="record_date" value="{{ $selectedDate }}">
                                            <input type="hidden" name="status" value="present">
                                            @if ($hasTimedIn)
                                                <input type="hidden" name="time_in" value="{{ $dtr->time_in }}">
                                            @else
                                                <input type="hidden" name="time_in"
                                                    value="{{ $emp->shift_start ?: '09:00' }}">
                                            @endif

                                            <!-- Exact Inputtable Time Out Picker -->
                                            <input type="time" name="time_out" id="time_out_{{ $emp->id }}"
                                                class="form-control"
                                                value="{{ $hasTimedOut ? date('H:i', strtotime($dtr->time_out)) : $defaultTimeOutVal }}"
                                                style="width: 120px; height: 38px; font-weight: 700; font-size: 0.90rem; background: #071322; border: 1.5px solid rgba(212, 175, 55, 0.45); border-radius: 6px; color: #fff; text-align: center;"
                                                required>

                                            <button type="button" class="btn btn-navy btn-sm"
                                                style="height: 38px; padding: 0 8px; font-size: 0.75rem; font-weight: 700; white-space: nowrap;"
                                                onclick="setExactNow('time_out_{{ $emp->id }}')"
                                                title="Set Current Live Time (Hours & Minutes)">
                                                ⚡ Now
                                            </button>

                                            <!-- Inputtable Undertime Reason / Note -->
                                            <input type="text" name="undertime_reason" class="form-control"
                                                value="{{ $dtr->undertime_reason ?? '' }}"
                                                placeholder="Undertime reason (if early)..."
                                                style="width: 190px; height: 38px; font-size: 0.80rem; background: #071322; border: 1.5px solid rgba(212, 175, 55, 0.35); border-radius: 6px; color: #fff;"
                                                title="Enter reason for early departure or undertime">

                                            <button type="submit"
                                                class="btn {{ $hasTimedOut ? 'btn-ghost' : 'btn-navy' }}"
                                                style="height: 38px; padding: 0 12px; font-size: 0.82rem; font-weight: 700; white-space: nowrap; background: #2563eb;">
                                                {{ $hasTimedOut ? 'Update Out' : '🏁 Clock Out' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 3: MASTER ATTENDANCE LOG (Complete Filterable History & Overview) -->
    <!-- ========================================================================= -->
    <div id="tab-pane-master" class="dtr-tab-pane" style="display: none;">
        <div class="card">
            <div class="card-header">
                <div class="card-title-group">
                    <h3 class="card-title">📋 Master Attendance Log & Detailed Breakdown</h3>
                    <span class="card-subtitle">Comprehensive list of attendance, undertime minutes, tardiness, and
                        overtime logs</span>
                </div>
            </div>

            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>EMP #</th>
                                <th>Staff Member</th>
                                <th>Position & Shift</th>
                                <th>Log Date</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th>Hours of Duty</th>
                                <th>Late / Undertime</th>
                                <th>OT Hours</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $dtr)
                                @php
                                    $emp = $dtr->employee;
                                    $shiftStartFormatted = $emp->shift_start
                                        ? \Carbon\Carbon::parse($emp->shift_start)->format('g:i A')
                                        : '9:00 AM';
                                    $shiftEndFormatted = $emp->shift_end
                                        ? \Carbon\Carbon::parse($emp->shift_end)->format('g:i A')
                                        : '6:00 PM';
                                @endphp
                                <tr>
                                    <td>
                                        <span style="font-family: monospace; font-weight: 700; color: var(--gold-light);">
                                            {{ $emp->employee_code }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong style="color: var(--white);">{{ $emp->full_name }}</strong>
                                        <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $emp->position }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-navy" style="font-size: 0.7rem;">
                                            ⏰ {{ $shiftStartFormatted }} - {{ $shiftEndFormatted }}
                                        </span>
                                    </td>
                                    <td>{{ $dtr->record_date->format('M d, Y') }}</td>
                                    <td>
                                        @if ($dtr->time_in)
                                            <span style="font-family: monospace; font-weight: 700; color: var(--white);">
                                                🟢 {{ date('h:i A', strtotime($dtr->time_in)) }}
                                            </span>
                                        @else
                                            <span style="color: var(--text-muted);">--:--</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($dtr->time_out)
                                            <span style="font-family: monospace; font-weight: 700; color: var(--white);">
                                                🏁 {{ date('h:i A', strtotime($dtr->time_out)) }}
                                            </span>
                                        @elseif($dtr->time_in)
                                            <span class="badge badge-warning" style="font-size: 0.68rem;">Pending
                                                Out</span>
                                        @else
                                            <span style="color: var(--text-muted);">--:--</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong
                                            style="color: var(--gold-primary);">{{ number_format($dtr->regular_hours, 2) }}
                                            hrs</strong>
                                    </td>
                                    <td>
                                        @if ($dtr->late_minutes > 0)
                                            <div style="font-size: 0.72rem; color: #f59e0b; font-weight: 700;">
                                                ⚠️ Late: {{ $dtr->formatted_late }}
                                            </div>
                                        @endif
                                        @if ($dtr->undertime_minutes > 0)
                                            <div style="font-size: 0.72rem; color: #ef4444; font-weight: 700;">
                                                ⏳ UT: {{ $dtr->formatted_undertime }}
                                            </div>
                                            @if ($dtr->undertime_reason)
                                                <div
                                                    style="font-size: 0.70rem; color: var(--gold-light); font-style: italic; margin-top: 1px;">
                                                    📝 {{ $dtr->undertime_reason }}
                                                </div>
                                            @endif
                                        @endif
                                        @if ($dtr->late_minutes == 0 && $dtr->undertime_minutes == 0)
                                            <span style="color: #10b981; font-size: 0.75rem;">✓ On Time</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($dtr->ot_hours > 0)
                                            <span class="badge badge-gold"
                                                style="font-size: 0.7rem;">+{{ number_format($dtr->ot_hours, 2) }}h</span>
                                        @else
                                            <span style="color: var(--text-muted); font-size: 0.8rem;">0</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($dtr->status === 'present')
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
                                    <td>
                                        <div style="display: flex; gap: 0.35rem; align-items: center;">
                                            <button type="button" class="btn btn-navy btn-sm"
                                                style="padding: 0.25rem 0.6rem; font-size: 0.75rem; font-weight: 700; white-space: nowrap; display: inline-flex; align-items: center; gap: 4px; border: 1.5px solid var(--gold); color: var(--gold-light);"
                                                onclick="openGenerateDtrModal('{{ $emp->id }}', '{{ addslashes($emp->full_name) }}', '{{ addslashes($emp->position) }}', '{{ $dtr->record_date->format('m') }}', '{{ $dtr->record_date->format('Y') }}')"
                                                title="Generate DTR for {{ $emp->full_name }}">
                                                <span>📄 Generate DTR</span>
                                            </button>
                                            <button type="button" class="btn btn-ghost btn-sm"
                                                style="padding: 0.25rem 0.45rem;"
                                                data-modal-target="modal-edit-dtr-{{ $dtr->id }}"
                                                title="Edit Full DTR Entry">
                                                ✏️
                                            </button>
                                            <form action="{{ route('admin.payroll.dtr.destroy', $dtr) }}" method="POST"
                                                onsubmit="return confirm('Delete this DTR log entry?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-ghost btn-sm"
                                                    style="padding: 0.25rem 0.45rem; color: #ef4444;" title="Delete">
                                                    🗑️
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal: Edit Single DTR Entry -->
                                <div class="modal-backdrop" id="modal-edit-dtr-{{ $dtr->id }}">
                                    <div class="modal-dialog modal-lg" style="max-width: 680px;">
                                        <div class="modal-header">
                                            <div class="modal-title-group">
                                                <h4 class="modal-title">Edit DTR Entry: {{ $emp->full_name }}</h4>
                                                <span style="font-size: 0.72rem; color: var(--gold-light);">Date:
                                                    {{ $dtr->record_date->format('F d, Y') }}
                                                    ({{ $emp->position }})
                                                </span>
                                            </div>
                                            <button type="button" class="modal-close-btn"
                                                data-modal-close>&times;</button>
                                        </div>
                                        <form action="{{ route('admin.payroll.dtr.store') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="employee_id" value="{{ $dtr->employee_id }}">
                                            <input type="hidden" name="record_date"
                                                value="{{ $dtr->record_date->format('Y-m-d') }}">
                                            <div class="modal-body" style="padding: 1.5rem;">
                                                <div
                                                    style="background: rgba(212, 175, 55, 0.08); border: 1px solid var(--gold-border); border-radius: 6px; padding: 0.75rem 1rem; margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center;">
                                                    <div>
                                                        <div
                                                            style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">
                                                            Assigned Employee Shift:</div>
                                                        <div
                                                            style="font-weight: 800; color: var(--gold-primary); font-size: 1rem;">
                                                            ⏰ {{ $shiftStartFormatted }} - {{ $shiftEndFormatted }}
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="form-label">Attendance Status *</label>
                                                    <select name="status" class="form-control" required>
                                                        <option value="present"
                                                            {{ $dtr->status === 'present' || $dtr->status === 'absent' ? 'selected' : '' }}>
                                                            Present (Normal Shift)</option>
                                                        <option value="late"
                                                            {{ $dtr->status === 'late' ? 'selected' : '' }}>Late Arrival
                                                        </option>
                                                        <option value="absent"
                                                            {{ $dtr->status === 'absent' ? 'selected' : '' }}>Absent
                                                        </option>
                                                        <option value="on_leave"
                                                            {{ $dtr->status === 'on_leave' ? 'selected' : '' }}>On Leave
                                                        </option>
                                                        <option value="rest_day"
                                                            {{ $dtr->status === 'rest_day' ? 'selected' : '' }}>Rest Day
                                                        </option>
                                                    </select>
                                                </div>

                                                <div
                                                    style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem; margin-top: 1rem;">
                                                    <div class="form-group">
                                                        <label class="form-label">🟢 Time In</label>
                                                        <input type="time" name="time_in" class="form-control"
                                                            value="{{ $dtr->time_in ? date('H:i', strtotime($dtr->time_in)) : '09:00' }}">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">🏁 Time Out</label>
                                                        <input type="time" name="time_out" class="form-control"
                                                            value="{{ $dtr->time_out ? date('H:i', strtotime($dtr->time_out)) : '' }}">
                                                    </div>
                                                </div>

                                                <div class="form-group" style="margin-top: 0.85rem;">
                                                    <label class="form-label">⏳ Undertime Reason / Note (Optional)</label>
                                                    <input type="text" name="undertime_reason" class="form-control"
                                                        value="{{ $dtr->undertime_reason }}"
                                                        placeholder="e.g. Approved medical checkup / Family emergency / Early pass">
                                                </div>

                                                <div class="form-group" style="margin-top: 0.85rem;">
                                                    <label class="form-label">OT Hours</label>
                                                    <input type="number" step="0.5" name="ot_hours"
                                                        class="form-control" value="{{ $dtr->ot_hours ?? 0.0 }}"
                                                        min="0">
                                                </div>
                                                <div class="form-group" style="margin-top: 0.85rem;">
                                                    <label class="form-label">General Notes / Remarks</label>
                                                    <input type="text" name="notes" class="form-control"
                                                        value="{{ $dtr->notes }}"
                                                        placeholder="e.g. Regular shift notes">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-ghost"
                                                    data-modal-close>Cancel</button>
                                                <button type="submit" class="btn btn-gold">Update Entry</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="11"
                                        style="text-align: center; color: var(--text-muted); padding: 2.5rem;">
                                        No attendance records found for this query.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($records->hasPages())
                    <div style="padding: 1rem;">
                        {{ $records->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal: Add / Log Custom DTR Entry -->
    <div class="modal-backdrop" id="modal-add-dtr">
        <div class="modal-dialog" style="max-width: 600px;">
            <div class="modal-header">
                <div class="modal-title-group">
                    <h4 class="modal-title">Log Custom DTR Entry</h4>
                    <span style="font-size: 0.72rem; color: var(--gold-light);">DTR Time In & Time Out with Auto-Duty
                        Calculation</span>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form action="{{ route('admin.payroll.dtr.store') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label">Select Employee *</label>
                        <select name="employee_id" class="form-control" required id="select_emp_add_dtr"
                            onchange="updateShiftHint(this)">
                            <option value="">-- Choose Employee --</option>
                            @foreach ($employees as $emp)
                                <option value="{{ $emp->id }}"
                                    data-shift-start="{{ $emp->shift_start ?: '09:00' }}"
                                    data-shift-end="{{ $emp->shift_end ?: '18:00' }}"
                                    data-shift-text="{{ $emp->shift_start ? \Carbon\Carbon::parse($emp->shift_start)->format('g:i A') : '9:00 AM' }} - {{ $emp->shift_end ? \Carbon\Carbon::parse($emp->shift_end)->format('g:i A') : '6:00 PM' }}">
                                    {{ $emp->employee_code }} - {{ $emp->full_name }} ({{ $emp->position }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="add_dtr_shift_tip"
                        style="display: none; background: rgba(212, 175, 55, 0.08); border: 1px solid var(--gold-border); border-radius: 6px; padding: 0.5rem 0.85rem; margin-top: 0.5rem; font-size: 0.78rem; color: var(--gold-light);">
                        Assigned Shift: <strong id="add_dtr_shift_text">9:00 AM - 6:00 PM</strong>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Record Date *</label>
                            <input type="date" name="record_date" class="form-control" value="{{ $selectedDate }}"
                                required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Attendance Status *</label>
                            <select name="status" class="form-control" required>
                                <option value="present" selected>Present (Normal Shift)</option>
                                <option value="late">Late Arrival</option>
                                <option value="absent">Absent</option>
                                <option value="on_leave">On Leave</option>
                                <option value="rest_day">Rest Day</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-top: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">🟢 Time In</label>
                            <div style="display: flex; gap: 4px; align-items: center;">
                                <input type="time" name="time_in" id="custom_modal_time_in" class="form-control"
                                    value="09:00"
                                    style="font-weight: 700; font-size: 0.92rem; background: #071322; border: 1.5px solid rgba(212, 175, 55, 0.45); border-radius: 6px; color: #fff; text-align: center;">
                                <button type="button" class="btn btn-navy btn-sm"
                                    style="padding: 0 8px; font-size: 0.72rem; height: 38px; white-space: nowrap;"
                                    onclick="setExactNow('custom_modal_time_in')" title="Set Current Live Time">
                                    ⚡ Time now
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">🏁 Time Out</label>
                            <div style="display: flex; gap: 4px; align-items: center;">
                                <input type="time" name="time_out" id="custom_modal_time_out" class="form-control"
                                    value="18:00"
                                    style="font-weight: 700; font-size: 0.92rem; background: #071322; border: 1.5px solid rgba(212, 175, 55, 0.45); border-radius: 6px; color: #fff; text-align: center;">
                                <button type="button" class="btn btn-navy btn-sm"
                                    style="padding: 0 8px; font-size: 0.72rem; height: 38px; white-space: nowrap;"
                                    onclick="setExactNow('custom_modal_time_out')" title="Set Current Live Time">
                                    ⚡ Time now
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 0.75rem;">
                        <label class="form-label">⏳ Undertime Reason / Note (Optional)</label>
                        <input type="text" name="undertime_reason" class="form-control"
                            placeholder="e.g. Approved early departure / Medical appointment">
                    </div>

                    <div class="form-group" style="margin-top: 0.75rem;">
                        <label class="form-label">OT Hours</label>
                        <input type="number" step="0.5" name="ot_hours" class="form-control" value="0.0"
                            min="0" style="height: 38px;">
                    </div>
                    <div class="form-group" style="margin-top: 0.75rem;">
                        <label class="form-label">General Notes / Remarks</label>
                        <input type="text" name="notes" class="form-control"
                            placeholder="e.g. Regular shift / approved overtime">
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
                    <h4 class="modal-title">⚡ Batch Generate Shift Day</h4>
                    <span style="font-size: 0.72rem; color: var(--gold-light);">Populate each staff member according to
                        their individual assigned shift</span>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form action="{{ route('admin.payroll.dtr.batch') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label">Select Date to Populate *</label>
                        <input type="date" name="date" class="form-control" value="{{ $selectedDate }}"
                            required>
                    </div>
                    <div class="form-group" style="margin-top: 0.75rem;">
                        <label class="form-label">Default Attendance Status *</label>
                        <select name="default_status" class="form-control" required>
                            <option value="present" selected>Present (Auto Uses Each Employee's Assigned Shift: 9am-6pm or
                                10am-7pm)</option>
                            <option value="rest_day">Rest Day (0 hrs)</option>
                        </select>
                    </div>
                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 1rem;">
                        Note: Existing entries on this date and approved leave applications will NOT be overwritten.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-navy">Generate Batch Records</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Payroll Rate Multipliers & Settings (Admin Only) -->
    <div class="modal-backdrop" id="modal-payroll-multipliers">
        <div class="modal-dialog" style="max-width: 540px;">
            <div class="modal-header" style="background: rgba(212, 175, 55, 0.08); border-bottom: 1px solid var(--gold-border);">
                <div class="modal-title-group">
                    <h4 class="modal-title" style="color: var(--gold-light); display: flex; align-items: center; gap: 0.5rem; font-size: 1.1rem;">
                        <span>⚙️</span> Payroll Multipliers & Computation Settings
                    </h4>
                    <span style="font-size: 0.72rem; color: var(--gold-light);">Editable strictly by Admin & Manager. Stored directly in the database.</span>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form action="{{ route('admin.payroll.settings.multipliers.update') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding: 1.5rem;">
                    <div style="background: rgba(59, 130, 246, 0.08); border-left: 3px solid #3b82f6; padding: 0.75rem 1rem; border-radius: 4px; margin-bottom: 1.25rem; font-size: 0.8rem; color: #93c5fd;">
                        ℹ️ <strong>Direct Database Settings:</strong> When generating payroll, every tagged duty day (Holiday, Special Holiday, Rest Day, Overtime) is automatically multiplied by these rates into Gross Pay.
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr; gap: 1.15rem;">
                        <!-- Regular Holiday Multiplier -->
                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="display: flex; justify-content: space-between; font-weight: 700; color: #f472b6; font-size: 0.85rem; margin-bottom: 0.35rem;">
                                <span>🎉 Regular Holiday Multiplier</span>
                                <span style="font-weight: 600; color: var(--text-muted); font-size: 0.75rem;">Default: x2.00 (200%)</span>
                            </label>
                            <div style="position: relative;">
                                <input type="number" step="0.01" min="0" max="10" name="holiday_multiplier" class="form-control"
                                    value="{{ number_format($payrollSettings['holiday_multiplier']->multiplier ?? 2.00, 2) }}"
                                    style="padding-left: 2rem; font-weight: 700; font-size: 1rem; color: #fff; background: #071322; border-color: rgba(236, 72, 153, 0.5);" required>
                                <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #f472b6; font-weight: 700;">×</span>
                            </div>
                            <span style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px; display: block;">Formula: Daily Rate × Multiplier (e.g. ₱500 × 2.0 = ₱1,000)</span>
                        </div>

                        <!-- Special Holiday Multiplier -->
                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="display: flex; justify-content: space-between; font-weight: 700; color: #38bdf8; font-size: 0.85rem; margin-bottom: 0.35rem;">
                                <span>✨ Special Holiday Multiplier</span>
                                <span style="font-weight: 600; color: var(--text-muted); font-size: 0.75rem;">Default: x1.30 (130%)</span>
                            </label>
                            <div style="position: relative;">
                                <input type="number" step="0.01" min="0" max="10" name="special_holiday_multiplier" class="form-control"
                                    value="{{ number_format($payrollSettings['special_holiday_multiplier']->multiplier ?? 1.30, 2) }}"
                                    style="padding-left: 2rem; font-weight: 700; font-size: 1rem; color: #fff; background: #071322; border-color: rgba(56, 189, 248, 0.5);" required>
                                <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #38bdf8; font-weight: 700;">×</span>
                            </div>
                            <span style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px; display: block;">Formula: Daily Rate × Multiplier (e.g. ₱500 × 1.3 = ₱650)</span>
                        </div>

                        <!-- Rest Day Multiplier -->
                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="display: flex; justify-content: space-between; font-weight: 700; color: #93c5fd; font-size: 0.85rem; margin-bottom: 0.35rem;">
                                <span>🏖️ Rest Day Multiplier</span>
                                <span style="font-weight: 600; color: var(--text-muted); font-size: 0.75rem;">Default: x1.30 (130%)</span>
                            </label>
                            <div style="position: relative;">
                                <input type="number" step="0.01" min="0" max="10" name="rest_day_multiplier" class="form-control"
                                    value="{{ number_format($payrollSettings['rest_day_multiplier']->multiplier ?? 1.30, 2) }}"
                                    style="padding-left: 2rem; font-weight: 700; font-size: 1rem; color: #fff; background: #071322; border-color: rgba(59, 130, 246, 0.5);" required>
                                <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #93c5fd; font-weight: 700;">×</span>
                            </div>
                            <span style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px; display: block;">Formula: Daily Rate × Multiplier for duty performed on scheduled rest day</span>
                        </div>

                        <!-- Overtime Multiplier -->
                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="display: flex; justify-content: space-between; font-weight: 700; color: #fbbf24; font-size: 0.85rem; margin-bottom: 0.35rem;">
                                <span>⏱️ Overtime (OT) Multiplier</span>
                                <span style="font-weight: 600; color: var(--text-muted); font-size: 0.75rem;">Default: x1.30 (130%)</span>
                            </label>
                            <div style="position: relative;">
                                <input type="number" step="0.01" min="0" max="10" name="overtime_multiplier" class="form-control"
                                    value="{{ number_format($payrollSettings['overtime_multiplier']->multiplier ?? 1.30, 2) }}"
                                    style="padding-left: 2rem; font-weight: 700; font-size: 1rem; color: #fff; background: #071322; border-color: rgba(245, 158, 11, 0.5);" required>
                                <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #fbbf24; font-weight: 700;">×</span>
                            </div>
                            <span style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px; display: block;">Formula: Hourly Rate × Multiplier × OT Hours</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid var(--navy-border); padding: 1rem 1.5rem; display: flex; justify-content: flex-end; gap: 0.75rem;">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold" style="font-weight: 700;">
                        💾 Save Multipliers to Database
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .tab-btn.active {
            background: var(--navy-dark) !important;
            color: var(--gold-primary) !important;
            border-bottom: 3px solid var(--gold-primary) !important;
            font-weight: 800 !important;
        }

        .dtr-station-table tr:hover td {
            background: rgba(212, 175, 55, 0.04) !important;
        }
    </style>

    <script>
        function handleDtrStatusDropdownChange(selectElem, empName, formId, hiddenInputId) {
            const selectedVal = selectElem.value;
            const originalVal = selectElem.getAttribute('data-original-val') || '';
            
            if (!selectedVal || selectedVal === originalVal) return;

            const labels = {
                'rest_day': 'Rest Day',
                'overtime': 'Overtime',
                'holiday': 'Holiday',
                'special_holiday': 'Special Holiday',
                'off_duty': 'Off Duty',
                'present': 'Normal Present Duty'
            };

            const targetLabel = labels[selectedVal] || selectedVal;
            const confirmMsg = `Do you want to update ${empName} as ${targetLabel}?`;
            
            if (confirm(confirmMsg)) {
                document.getElementById(hiddenInputId).value = selectedVal;
                document.getElementById(formId).submit();
            } else {
                selectElem.value = originalVal;
            }
        }

        function switchDtrTab(tabKey) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.dtr-tab-pane').forEach(pane => pane.style.display = 'none');

            document.getElementById(`tab-btn-${tabKey}`).classList.add('active');
            document.getElementById(`tab-pane-${tabKey}`).style.display = 'block';

            // Store in sessionStorage so active tab stays after submit
            sessionStorage.setItem('active_dtr_tab', tabKey);

            // Re-adjust DataTables column widths if master tab is opened
            if (window.jQuery && $.fn.DataTable) {
                setTimeout(function() {
                    $.fn.dataTable.tables({
                        visible: true,
                        api: true
                    }).columns.adjust();
                }, 50);
            }
        }

        // Restore tab on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedTab = sessionStorage.getItem('active_dtr_tab');
            if (savedTab && document.getElementById(`tab-btn-${savedTab}`)) {
                switchDtrTab(savedTab);
            }
        });

        function setExactNow(inputId) {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const elem = document.getElementById(inputId);
            if (elem) {
                elem.value = `${hours}:${minutes}`;
            }
        }

        function updateShiftHint(selectElem) {
            const selectedOpt = selectElem.options[selectElem.selectedIndex];
            const shiftText = selectedOpt.getAttribute('data-shift-text');
            const shiftStart = selectedOpt.getAttribute('data-shift-start');
            const shiftEnd = selectedOpt.getAttribute('data-shift-end');
            const tipElem = document.getElementById('add_dtr_shift_tip');
            const textElem = document.getElementById('add_dtr_shift_text');

            if (shiftText) {
                textElem.innerText = shiftText;
                tipElem.style.display = 'block';

                if (shiftStart) {
                    const inInput = document.getElementById('custom_modal_time_in');
                    if (inInput) inInput.value = shiftStart;
                }
                if (shiftEnd) {
                    const outInput = document.getElementById('custom_modal_time_out');
                    if (outInput) outInput.value = shiftEnd;
                }
            } else {
                tipElem.style.display = 'none';
            }
        }

        function openGenerateDtrModal(employeeId, employeeName, employeePosition, defaultMonth, defaultYear) {
            const months = [
                { val: '1', name: 'January' },
                { val: '2', name: 'February' },
                { val: '3', name: 'March' },
                { val: '4', name: 'April' },
                { val: '5', name: 'May' },
                { val: '6', name: 'June' },
                { val: '7', name: 'July' },
                { val: '8', name: 'August' },
                { val: '9', name: 'September' },
                { val: '10', name: 'October' },
                { val: '11', name: 'November' },
                { val: '12', name: 'December' }
            ];

            const currentYear = new Date().getFullYear();
            const curMonth = parseInt(defaultMonth || (new Date().getMonth() + 1), 10);
            const curYear = parseInt(defaultYear || currentYear, 10);

            let monthOptions = '';
            months.forEach(m => {
                const sel = parseInt(m.val, 10) === curMonth ? 'selected' : '';
                monthOptions += `<option value="${m.val}" ${sel}>${m.name}</option>`;
            });

            let yearOptions = '';
            for (let y = currentYear - 2; y <= currentYear + 2; y++) {
                const sel = y === curYear ? 'selected' : '';
                yearOptions += `<option value="${y}" ${sel}>${y}</option>`;
            }

            Swal.fire({
                title: '<span style="color: #d4af37; font-size: 1.25rem; font-weight: 800;">📄 Generate DTR (15th / 30th)</span>',
                html: `
                    <div style="text-align: left; background: #071322; border: 1px solid rgba(212, 175, 55, 0.35); border-radius: 8px; padding: 12px; margin-bottom: 16px;">
                        <div style="font-size: 0.95rem; font-weight: 700; color: #ffffff;">👤 ${employeeName}</div>
                        <div style="font-size: 0.78rem; color: #94a3b8; margin-top: 2px;">💼 ${employeePosition}</div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 16px; text-align: left;">
                        <div>
                            <label style="font-size: 0.80rem; font-weight: 700; color: #cbd5e1; display: block; margin-bottom: 5px;">📅 Month:</label>
                            <select id="swal_month" style="width: 100%; height: 38px; border-radius: 6px; background: #071322; border: 1.5px solid rgba(212, 175, 55, 0.5); color: #fff; font-weight: 700; padding: 0 8px;">
                                ${monthOptions}
                            </select>
                        </div>
                        <div>
                            <label style="font-size: 0.80rem; font-weight: 700; color: #cbd5e1; display: block; margin-bottom: 5px;">🗓️ Year:</label>
                            <select id="swal_year" style="width: 100%; height: 38px; border-radius: 6px; background: #071322; border: 1.5px solid rgba(212, 175, 55, 0.5); color: #fff; font-weight: 700; padding: 0 8px;">
                                ${yearOptions}
                            </select>
                        </div>
                    </div>

                    <div style="text-align: left; margin-bottom: 10px;">
                        <label style="font-size: 0.82rem; font-weight: 700; color: #cbd5e1; display: block; margin-bottom: 8px;">⏱️ Select Cutoff Period:</label>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <label style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; background: #071322; border: 1.5px solid rgba(212, 175, 55, 0.4); border-radius: 6px; cursor: pointer;">
                                <input type="radio" name="swal_cutoff" value="15" checked style="accent-color: #d4af37; width: 18px; height: 18px;">
                                <div>
                                    <strong style="color: #ffffff; font-size: 0.88rem;">15th Cutoff (1st - 15th)</strong>
                                    <div style="font-size: 0.72rem; color: #94a3b8;">Days 1 to 15 (First Half of Month)</div>
                                </div>
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; background: #071322; border: 1.5px solid rgba(212, 175, 55, 0.4); border-radius: 6px; cursor: pointer;">
                                <input type="radio" name="swal_cutoff" value="30" style="accent-color: #d4af37; width: 18px; height: 18px;">
                                <div>
                                    <strong style="color: #ffffff; font-size: 0.88rem;">30th Cutoff (16th - End of Month)</strong>
                                    <div style="font-size: 0.72rem; color: #94a3b8;">Days 16 to 30/31 (Second Half of Month)</div>
                                </div>
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; padding: 8px 14px; background: #071322; border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 6px; cursor: pointer;">
                                <input type="radio" name="swal_cutoff" value="full" style="accent-color: #d4af37; width: 16px; height: 16px;">
                                <div>
                                    <strong style="color: #cbd5e1; font-size: 0.84rem;">Full Month (1st - End of Month)</strong>
                                    <div style="font-size: 0.70rem; color: #64748b;">Entire month comprehensive DTR</div>
                                </div>
                            </label>
                        </div>
                    </div>
                `,
                background: '#0d1e33',
                showCancelButton: true,
                confirmButtonText: '⚡ Generate DTR Card',
                confirmButtonColor: '#d4af37',
                cancelButtonText: 'Cancel',
                cancelButtonColor: '#475569',
                focusConfirm: false,
                preConfirm: () => {
                    const m = document.getElementById('swal_month').value;
                    const y = document.getElementById('swal_year').value;
                    const cutoffElem = document.querySelector('input[name="swal_cutoff"]:checked');
                    const cutoff = cutoffElem ? cutoffElem.value : '15';
                    return { month: m, year: y, cutoff: cutoff };
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    const baseUrl = "{{ route('admin.payroll.dtr.report', ['employee' => ':id']) }}".replace(':id', employeeId);
                    const reportUrl = `${baseUrl}?month=${result.value.month}&year=${result.value.year}&cutoff=${result.value.cutoff}`;
                    window.open(reportUrl, '_blank');
                }
            });
        }
    </script>
@endsection
