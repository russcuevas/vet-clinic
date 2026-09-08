@extends('layouts.app')

@php
    $title = 'Time In / Time Out Terminal';
    $headerTitle = 'Reception Desk Attendance Terminal';
    $breadcrumb = 'Reception / Timekeeping';
@endphp

@section('content')
    <div style="max-width: 860px; margin: 0 auto;">
        <!-- Header & Clock -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--white); margin-bottom: 0.25rem;">
                    ⏰ Daily Time Record (DTR) Terminal
                </h2>
                <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0;">
                    Live Time In and Time Out station for clinic personnel. Exact time is captured automatically upon punching.
                </p>
            </div>

            <!-- Realtime Digital Clock Widget -->
            <div style="background: linear-gradient(135deg, rgba(212, 175, 55, 0.15), rgba(11, 25, 44, 0.8)); border: 1px solid var(--gold-border); border-radius: 10px; padding: 0.6rem 1.25rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">
                <div>
                    <div style="font-size: 0.68rem; color: var(--gold-light); text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700;">
                        LIVE SYSTEM TIME (NON-EDITABLE)
                    </div>
                    <div id="liveClock" style="font-family: monospace; font-size: 1.35rem; font-weight: 800; color: #fff; letter-spacing: 1px;">
                        --:--:-- --
                    </div>
                </div>
            </div>
        </div>

        <!-- Date Strip & Realtime Attendance Badges Bar -->
        <div class="card" style="margin-bottom: 1.25rem; border: 1px solid var(--navy-border);">
            <div class="card-body" style="padding: 0.85rem 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                        <label style="font-weight: 700; color: var(--gold-light); font-size: 0.85rem; margin: 0;">📅 Active Date:</label>
                        <input type="date" class="form-control" value="{{ $today }}" readonly
                            style="width: auto; font-weight: 700; background: rgba(11, 25, 44, 0.6); cursor: not-allowed;" title="Non-editable active date">
                        <span style="font-size: 0.82rem; color: var(--text-muted);">
                            Today ({{ \Carbon\Carbon::parse($today)->format('M d') }})
                        </span>
                    </div>

                    <!-- Stats summary chips for active date -->
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; font-size: 0.75rem;">
                        @php
                            $dayPresent = $todayRecords->whereIn('status', ['present', 'late'])->count();
                            $dayLate = $todayRecords->where('late_minutes', '>', 0)->count();
                            $dayUndertime = $todayRecords->where('undertime_minutes', '>', 0)->count();
                            $dayAwaitingOut = $todayRecords->filter(fn($r) => !empty($r->time_in) && empty($r->time_out))->count();
                            $dayRestDay = $todayRecords->where('status', 'rest_day')->count();
                        @endphp
                        <span class="badge badge-success" style="font-size: 0.72rem; padding: 0.35rem 0.65rem;">🟢 PRESENT: {{ $dayPresent }}</span>
                        <span class="badge badge-warning" style="font-size: 0.72rem; padding: 0.35rem 0.65rem;">⚠️ LATE: {{ $dayLate }}</span>
                        <span class="badge badge-danger" style="font-size: 0.72rem; padding: 0.35rem 0.65rem;">⏳ UNDERTIME: {{ $dayUndertime }}</span>
                        <span class="badge badge-blue" style="font-size: 0.72rem; padding: 0.35rem 0.65rem;">⌛ ON DUTY (NO OUT YET): {{ $dayAwaitingOut }}</span>
                        <span class="badge badge-navy" style="font-size: 0.72rem; padding: 0.35rem 0.65rem; border: 1px solid rgba(59, 130, 246, 0.35); color: #93c5fd;">🏖️ REST DAY: {{ $dayRestDay }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Punch Station Form Card -->
        <div class="card" style="border: 1px solid var(--gold-border); box-shadow: 0 4px 20px rgba(0,0,0,0.25);">
            <div class="card-header" style="background: rgba(212, 175, 55, 0.08); border-bottom: 1px solid var(--gold-border); padding: 1.1rem 1.5rem;">
                <div class="card-title-group">
                    <h3 class="card-title" style="font-size: 1.05rem; color: var(--gold-light); display: flex; align-items: center; gap: 8px;">
                        <span>📍 Clock In / Clock Out Station</span>
                    </h3>
                    <span class="card-subtitle">Choose employee and punch live attendance</span>
                </div>
            </div>

            <div class="card-body" style="padding: 1.75rem;">
                <!-- Employee Selection Dropdown -->
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label class="form-label" style="font-weight: 700; color: var(--white); margin-bottom: 0.4rem; font-size: 0.9rem;">
                        Select Staff Member <span class="req">*</span>
                    </label>
                    <select id="employeeSelect" class="form-control" style="font-size: 0.95rem; padding: 0.75rem;" onchange="onEmployeeSelected()">
                        <option value="">-- Choose Employee to Punch --</option>
                        @foreach($employees as $emp)
                            @php
                                $rec = $todayRecords[$emp->id] ?? null;
                                $statusLabel = 'Not Clocked In';
                                if ($rec) {
                                    if ($rec->status === 'rest_day') {
                                        $statusLabel = 'Rest Day';
                                    } elseif ($rec->status === 'on_leave') {
                                        $statusLabel = 'On Leave';
                                    } elseif ($rec->time_in && $rec->time_out) {
                                        $statusLabel = 'Completed (' . \Carbon\Carbon::parse($rec->time_in)->format('g:i A') . ' - ' . \Carbon\Carbon::parse($rec->time_out)->format('g:i A') . ')';
                                    } elseif ($rec->time_in) {
                                        $statusLabel = 'On Duty (' . \Carbon\Carbon::parse($rec->time_in)->format('g:i A') . ')';
                                    }
                                }
                            @endphp
                            <option value="{{ $emp->id }}"
                                data-name="{{ $emp->full_name }}"
                                data-code="{{ $emp->employee_code }}"
                                data-position="{{ $emp->position }}"
                                data-department="{{ $emp->department }}"
                                data-shift-start="{{ $emp->shift_start ?: '09:00' }}"
                                data-shift-end="{{ $emp->shift_end ?: '18:00' }}"
                                data-shift="{{ $emp->shift_start ? \Carbon\Carbon::parse($emp->shift_start)->format('g:i A') : '9:00 AM' }} - {{ $emp->shift_end ? \Carbon\Carbon::parse($emp->shift_end)->format('g:i A') : '6:00 PM' }}"
                                data-timein="{{ $rec && $rec->time_in ? \Carbon\Carbon::parse($rec->time_in)->format('g:i A') : '' }}"
                                data-timein-raw="{{ $rec && $rec->time_in ? \Carbon\Carbon::parse($rec->time_in)->format('H:i') : '' }}"
                                data-timeout="{{ $rec && $rec->time_out ? \Carbon\Carbon::parse($rec->time_out)->format('g:i A') : '' }}"
                                data-status="{{ $rec ? $rec->status : 'none' }}"
                                data-notes="{{ $rec && $rec->notes ? $rec->notes : '' }}">
                                {{ $emp->employee_code }} • {{ $emp->full_name }} ({{ $emp->position }}) — [{{ $statusLabel }}]
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Selected Employee Status Card -->
                <div id="empDetailsCard" style="display: none; background: rgba(11, 25, 44, 0.6); border: 1px solid var(--navy-border); border-radius: 8px; padding: 1.25rem; margin-bottom: 1.5rem;">
                    <div style="display: flex; align-items: center; gap: 0.85rem; margin-bottom: 1rem;">
                        <div id="empAvatar" style="width: 48px; height: 48px; border-radius: 50%; background: var(--navy-surface); border: 2px solid var(--gold-primary); display: flex; align-items: center; justify-content: center; font-weight: 800; color: var(--gold-light); font-size: 1.15rem;">
                            --
                        </div>
                        <div style="flex: 1;">
                            <div id="empName" style="font-weight: 700; color: #fff; font-size: 1.05rem;">--</div>
                            <div style="font-size: 0.78rem; color: var(--text-muted);">
                                <span id="empCode" style="color: var(--gold-light); font-family: monospace;">--</span> •
                                <span id="empPosition">--</span>
                            </div>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; font-size: 0.8rem; background: rgba(0,0,0,0.25); padding: 0.85rem; border-radius: 6px;">
                        <div>
                            <span style="color: var(--text-muted); display: block;">⏰ Assigned Shift:</span>
                            <strong id="empShift" style="color: #fff;">--</strong>
                        </div>
                        <div>
                            <span style="color: var(--text-muted); display: block;">📊 Today's Status:</span>
                            <strong id="empStatusBadge" style="color: var(--gold-light);">--</strong>
                        </div>
                    </div>

                    <div id="punchHistoryBox" style="margin-top: 0.85rem; font-size: 0.8rem; display: flex; justify-content: space-between; border-top: 1px dashed rgba(255,255,255,0.1); padding-top: 0.75rem;">
                        <div>Time In: <span id="empTimeInVal" style="color: #10b981; font-weight: 700;">Not Recorded</span></div>
                        <div>Time Out: <span id="empTimeOutVal" style="color: #38bdf8; font-weight: 700;">Not Recorded</span></div>
                    </div>

                    <!-- 2-Hour Cooldown / Lockout Warning Box -->
                    <div id="lockoutNoticeBox" style="display: none; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); border-radius: 6px; padding: 0.65rem 0.85rem; margin-top: 0.85rem;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 1.1rem;">⏳</span>
                            <div style="font-size: 0.78rem; color: #fca5a5; line-height: 1.4;">
                                <strong>Time Out Locked:</strong> Minimum 2-hour shift interval required.
                                <span id="lockoutAvailableTime" style="display: block; color: #fff; font-weight: 700; margin-top: 2px;">Available at --:-- --</span>
                            </div>
                        </div>
                    </div>

                    <!-- Overtime (OT) Detection & Approval Box -->
                    <div id="otApprovalBox" style="display: none; background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.4); border-radius: 6px; padding: 0.75rem 1rem; margin-top: 0.85rem;">
                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.4rem;">
                            <span style="font-size: 0.82rem; font-weight: 700; color: #fbbf24; display: flex; align-items: center; gap: 6px;">
                                ⚡ Overtime Detected (+30 mins past shift)
                            </span>
                            <span id="otDurationLabel" style="font-size: 0.75rem; color: #fff; background: rgba(245, 158, 11, 0.3); padding: 2px 8px; border-radius: 4px; font-weight: 700;">
                                +0.00 hrs
                            </span>
                        </div>
                        <p style="font-size: 0.74rem; color: var(--text-muted); margin: 0 0 0.5rem 0;">
                            Overtime is only credited if authorized by the Clinic Manager or Admin.
                        </p>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; background: rgba(0,0,0,0.3); padding: 0.5rem 0.75rem; border-radius: 6px; border: 1px solid rgba(245, 158, 11, 0.3);">
                            <input type="checkbox" name="ot_approved" id="cb_ot_approved" value="1" style="width: 16px; height: 16px; cursor: pointer; accent-color: #f59e0b;">
                            <span style="font-size: 0.82rem; font-weight: 700; color: #fff;">
                                ✅ Overtime Approved by Admin / Manager
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Punch Action Form -->
                <form action="{{ route('receptionist.dtr.store') }}" method="POST" id="punchForm">
                    @csrf
                    <input type="hidden" name="employee_id" id="formEmployeeId" value="">
                    <input type="hidden" name="action_type" id="formActionType" value="clock_in">
                    <input type="hidden" name="ot_approved" id="formOtApproved" value="0">

                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label class="form-label" style="font-size: 0.8rem;">Optional Remarks / Log Note</label>
                        <input type="text" name="notes" id="formNotes" class="form-control" placeholder="e.g. Regular duty, shift coverage...">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 0.85rem;">
                        <button type="button" class="btn btn-gold" id="btnClockIn" onclick="submitPunch('clock_in')" style="padding: 0.85rem; font-weight: 700; font-size: 0.95rem; justify-content: center; display: flex; align-items: center; gap: 8px;" disabled>
                            <span>🟢 Time In (Clock In)</span>
                        </button>
                        <button type="button" class="btn btn-navy" id="btnClockOut" onclick="submitPunch('clock_out')" style="padding: 0.85rem; font-weight: 700; font-size: 0.95rem; justify-content: center; display: flex; align-items: center; gap: 8px; border-color: #38bdf8; color: #38bdf8;" disabled>
                            <span>🔴 Time Out (Clock Out)</span>
                        </button>
                    </div>

                    <button type="button" class="btn btn-ghost" id="btnRestDay" onclick="submitPunch('rest_day')" style="width: 100%; font-size: 0.82rem; justify-content: center; padding: 0.6rem;" disabled>
                        ☕ Mark as Scheduled Rest Day
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Realtime digital clock script
    function updateClock() {
        const now = new Date();
        let hours = now.getHours();
        let minutes = now.getMinutes();
        let seconds = now.getSeconds();
        const ampm = hours >= 12 ? 'PM' : 'AM';

        hours = hours % 12;
        hours = hours ? hours : 12;
        const strHours = hours < 10 ? '0' + hours : hours;
        const strMinutes = minutes < 10 ? '0' + minutes : minutes;
        const strSeconds = seconds < 10 ? '0' + seconds : seconds;

        const clockEl = document.getElementById('liveClock');
        if (clockEl) {
            clockEl.textContent = `${strHours}:${strMinutes}:${strSeconds} ${ampm}`;
        }
    }
    setInterval(updateClock, 1000);
    updateClock();

    function onEmployeeSelected() {
        const select = document.getElementById('employeeSelect');
        const selectedId = select.value;
        const card = document.getElementById('empDetailsCard');
        const btnIn = document.getElementById('btnClockIn');
        const btnOut = document.getElementById('btnClockOut');
        const btnRest = document.getElementById('btnRestDay');
        const formId = document.getElementById('formEmployeeId');
        const lockoutBox = document.getElementById('lockoutNoticeBox');
        const otBox = document.getElementById('otApprovalBox');

        if (!selectedId) {
            card.style.display = 'none';
            btnIn.disabled = true;
            btnOut.disabled = true;
            btnRest.disabled = true;
            formId.value = '';
            if (lockoutBox) lockoutBox.style.display = 'none';
            if (otBox) otBox.style.display = 'none';
            return;
        }

        const option = select.options[select.selectedIndex];
        formId.value = selectedId;

        const name = option.dataset.name;
        const code = option.dataset.code;
        const position = option.dataset.position;
        const shift = option.dataset.shift;
        const shiftEndStr = option.dataset.shiftEnd || '18:00';
        const timeIn = option.dataset.timein;
        const timeInRaw = option.dataset.timeinRaw;
        const timeOut = option.dataset.timeout;
        const status = option.dataset.status;

        document.getElementById('empAvatar').textContent = name.substring(0, 2).toUpperCase();
        document.getElementById('empName').textContent = name;
        document.getElementById('empCode').textContent = code;
        document.getElementById('empPosition').textContent = position;
        document.getElementById('empShift').textContent = shift;

        let statusText = '⏳ Not Clocked In Yet';
        if (status === 'rest_day') {
            statusText = '☕ On Rest Day';
        } else if (status === 'on_leave') {
            statusText = '🏖️ On Approved Leave';
        } else if (timeIn && timeOut) {
            statusText = '🏁 Shift Completed';
        } else if (timeIn) {
            statusText = '🟢 Currently On Duty';
        }

        document.getElementById('empStatusBadge').textContent = statusText;
        document.getElementById('empTimeInVal').textContent = timeIn || 'Not Recorded';
        document.getElementById('empTimeOutVal').textContent = timeOut || 'Not Recorded';

        card.style.display = 'block';
        btnRest.disabled = false;

        // Reset dynamic boxes
        lockoutBox.style.display = 'none';
        otBox.style.display = 'none';

        const now = new Date();
        const currentMinutes = now.getHours() * 60 + now.getMinutes();

        if (timeInRaw) {
            // Already Clocked In today
            const inParts = timeInRaw.split(':');
            const inMinutes = parseInt(inParts[0]) * 60 + parseInt(inParts[1]);
            const minutesElapsed = currentMinutes - inMinutes;

            // 1. Check 2-Hour Cooldown for Time Out
            if (minutesElapsed < 120 && minutesElapsed >= 0) {
                // Locked: Less than 2 hours passed
                btnOut.disabled = true;
                btnOut.title = 'Time Out is locked for 2 hours after Time In.';
                
                const unlockTotalMins = inMinutes + 120;
                const unlockHours = Math.floor(unlockTotalMins / 60) % 24;
                const unlockMins = unlockTotalMins % 60;
                const unlockAmPm = unlockHours >= 12 ? 'PM' : 'AM';
                const unlockH12 = (unlockHours % 12) || 12;
                const unlockStr = `${unlockH12}:${unlockMins < 10 ? '0' + unlockMins : unlockMins} ${unlockAmPm}`;
                const remaining = 120 - minutesElapsed;

                document.getElementById('lockoutAvailableTime').textContent = `Available at ${unlockStr} (${remaining} mins remaining)`;
                lockoutBox.style.display = 'block';
            } else {
                // Unlocked: 2 hours or more passed
                btnOut.disabled = false;
                btnOut.title = '';
            }

            // 2. Check Overtime (30+ mins past shift_end)
            const shiftEndParts = shiftEndStr.split(':');
            const shiftEndMinutes = parseInt(shiftEndParts[0]) * 60 + parseInt(shiftEndParts[1]);
            if (currentMinutes - shiftEndMinutes >= 30) {
                const pastMins = currentMinutes - shiftEndMinutes;
                const otHrs = (pastMins / 60).toFixed(2);
                document.getElementById('otDurationLabel').textContent = `+${otHrs} hrs (${pastMins} mins past shift)`;
                otBox.style.display = 'block';
            }

            btnIn.disabled = false; // Allow re-clock in if needed
        } else {
            // Not clocked in yet today
            btnIn.disabled = false;
            btnOut.disabled = true; // Cannot clock out without clock in
            btnOut.title = 'Staff must Clock In first.';
        }
    }

    function submitPunch(actionType) {
        const formId = document.getElementById('formEmployeeId').value;
        if (!formId) {
            alert('Please select a staff member first.');
            return;
        }

        const select = document.getElementById('employeeSelect');
        const empName = select.options[select.selectedIndex].dataset.name;

        if (actionType === 'clock_out') {
            const cbOt = document.getElementById('cb_ot_approved');
            const isOtApproved = cbOt && cbOt.checked ? 1 : 0;
            document.getElementById('formOtApproved').value = isOtApproved;
            
            const otBox = document.getElementById('otApprovalBox');
            if (otBox && otBox.style.display !== 'none' && !isOtApproved) {
                if (!confirm(`Notice: Overtime detected but "Approved by Admin/Manager" is UNCHECKED. Overtime will NOT be credited.\n\nProceed with Time Out for ${empName}?`)) {
                    return;
                }
            } else {
                if (!confirm(`Confirm TIME OUT for ${empName}? This will record the current exact time.`)) {
                    return;
                }
            }
        } else {
            const actionText = actionType === 'clock_in' ? 'TIME IN' : 'REST DAY';
            if (!confirm(`Confirm ${actionText} for ${empName}? This will record the current exact time.`)) {
                return;
            }
        }

        document.getElementById('formActionType').value = actionType;
        document.getElementById('punchForm').submit();
    }
</script>
@endpush
