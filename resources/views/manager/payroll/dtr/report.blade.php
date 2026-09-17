<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTR - {{ $employee->full_name }} ({{ $startDate->format('M d') }} - {{ $endDate->format('M d, Y') }})</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
        }

        @page {
            size: portrait;
            margin: 4mm 6mm 4mm 6mm;
        }

        html, body {
            background-color: #f3f4f6;
            color: #111827;
            padding: 10px;
            font-size: 11px;
            line-height: 1.15;
        }

        .dtr-page-container {
            max-width: 820px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #9ca3af;
            padding: 12px 18px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        /* Top Action Bar (hidden when printed) */
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 1.5px solid #e5e7eb;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 12px;
            font-size: 11px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid #374151;
        }

        .btn-print {
            background-color: #111827;
            color: #ffffff;
        }
        .btn-print:hover {
            background-color: #374151;
        }

        .btn-back {
            background-color: #ffffff;
            color: #111827;
        }
        .btn-back:hover {
            background-color: #f3f4f6;
        }

        /* Plain DTR Header */
        .dtr-header {
            text-align: center;
            border-bottom: 1.5px solid #111827;
            padding-bottom: 3px;
            margin-bottom: 4px;
        }

        .dtr-header h1 {
            font-size: 13.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #111827;
            line-height: 1.1;
        }

        .dtr-header h2 {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            margin-top: 1px;
            color: #1f2937;
            line-height: 1.1;
        }

        .dtr-header p {
            font-size: 8px;
            color: #4b5563;
            margin-top: 1px;
        }

        /* Employee Meta Info Box */
        .emp-info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        .emp-info-table td {
            padding: 1px 4px;
            font-size: 10px;
            vertical-align: middle;
            line-height: 1.2;
        }

        .emp-info-table .label {
            font-weight: bold;
            color: #111827;
            width: 105px;
            font-size: 10px;
        }

        .emp-info-table .value {
            border-bottom: 1px solid #111827;
            font-weight: 600;
            color: #111827;
            font-size: 10.5px;
        }

        /* Main DTR Table */
        .dtr-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
            font-size: 12px;
        }

        .dtr-table th, .dtr-table td {
            border: 1px solid #111827;
            padding: 1.5px 3px;
            text-align: center;
            line-height: 1.15;
        }

        .dtr-table th {
            background-color: #f3f4f6;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 10.5px;
            padding: 2px 3px;
        }

        .dtr-table tr.sunday-row {
            background-color: #fafafa;
        }

        .dtr-table td {
            font-size: 12px;
        }

        .dtr-table .time-val {
            font-family: 'Courier New', Courier, monospace;
            font-weight: bold;
            font-size: 12.5px;
        }

        .btn-edit-row {
            background: #ffffff;
            border: 1px solid #9ca3af;
            padding: 1px 4px;
            font-size: 9px;
            cursor: pointer;
            border-radius: 2px;
            font-weight: 600;
            line-height: 1;
        }
        .btn-edit-row:hover {
            background: #f3f4f6;
            border-color: #111827;
        }

        /* Totals / Summary Strip */
        .summary-box {
            border: 1px solid #111827;
            background: #f9fafb;
            padding: 3px 6px;
            margin-bottom: 4px;
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 4px;
            text-align: center;
        }

        .summary-box .kpi-label {
            font-weight: bold;
            color: #4b5563;
            text-transform: uppercase;
            font-size: 8px;
            margin-bottom: 1px;
        }

        .summary-box .kpi-val {
            font-size: 11.5px;
            font-weight: 800;
            color: #111827;
        }

        /* Certification & Signatures */
        .certification-text {
            font-size: 8px;
            text-align: justify;
            line-height: 1.15;
            margin-bottom: 4px;
            color: #1f2937;
        }

        .signatures-container {
            display: flex;
            justify-content: space-between;
            margin-top: 2px;
            padding: 0 15px;
        }

        .sig-box {
            width: 42%;
            text-align: center;
        }

        .sig-line {
            border-bottom: 1px solid #111827;
            height: 14px;
            margin-bottom: 2px;
        }

        .sig-name {
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            line-height: 1.1;
        }

        .sig-title {
            font-size: 8px;
            color: #4b5563;
        }

        /* Edit Modal Overlay (no-print) */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .modal-box {
            background: #ffffff;
            width: 100%;
            max-width: 420px;
            border-radius: 6px;
            padding: 16px;
            border: 1px solid #111827;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
        }

        .modal-box h3 {
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 6px;
        }

        .form-row {
            margin-bottom: 10px;
            text-align: left;
        }

        .form-row label {
            display: block;
            font-size: 10.5px;
            font-weight: bold;
            margin-bottom: 2px;
            color: #374151;
        }

        .form-row input, .form-row select {
            width: 100%;
            padding: 5px 7px;
            font-size: 11px;
            border: 1px solid #9ca3af;
            border-radius: 4px;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 6px;
            margin-top: 14px;
        }

        /* PRINT STYLES (Guaranteed Single Page Fit) */
        @media print {
            html, body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
                color: #000000 !important;
                height: auto !important;
            }

            .dtr-page-container {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }

            .no-print {
                display: none !important;
            }

            .dtr-table, .summary-box, .signatures-container, .certification-text {
                page-break-inside: avoid !important;
            }

            .dtr-table {
                margin-bottom: 3px !important;
            }

            .dtr-table th, .dtr-table td {
                border: 1px solid #000000 !important;
                padding: 1px 2px !important;
                line-height: 1.1 !important;
            }

            .dtr-table th {
                font-size: 10px !important;
                padding: 1.5px 2px !important;
                background-color: #f3f4f6 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .dtr-table td {
                font-size: 12px !important;
            }

            .dtr-table .time-val {
                font-size: 12.5px !important;
                font-weight: bold !important;
            }

            .emp-info-table {
                margin-bottom: 3px !important;
            }

            .emp-info-table td {
                padding: 0.8px 2px !important;
                font-size: 9.5px !important;
                line-height: 1.15 !important;
            }

            .emp-info-table .value {
                border-bottom: 1px solid #000000 !important;
                font-size: 10px !important;
            }

            .dtr-header {
                border-bottom: 1.5px solid #000000 !important;
                padding-bottom: 2px !important;
                margin-bottom: 3px !important;
            }

            .dtr-header h1 {
                font-size: 13px !important;
                margin-bottom: 1px !important;
            }

            .dtr-header h2 {
                font-size: 10.5px !important;
                margin-bottom: 1px !important;
            }

            .dtr-header p {
                font-size: 7.5px !important;
            }

            .summary-box {
                border: 1px solid #000000 !important;
                background: #ffffff !important;
                padding: 2px 4px !important;
                margin-bottom: 3px !important;
            }

            .summary-box .kpi-label {
                font-size: 7.5px !important;
            }

            .summary-box .kpi-val {
                font-size: 11px !important;
            }

            .certification-text {
                font-size: 7.5px !important;
                line-height: 1.1 !important;
                margin-bottom: 3px !important;
            }

            .signatures-container {
                margin-top: 2px !important;
                padding: 0 10px !important;
            }

            .sig-line {
                border-bottom: 1px solid #000000 !important;
                height: 13px !important;
                margin-bottom: 1px !important;
            }

            .sig-name {
                font-size: 9.5px !important;
            }

            .sig-title {
                font-size: 7.5px !important;
            }
        }
    </style>
</head>
<body>

    <div class="dtr-page-container">
        <!-- Action Toolbar -->
        <div class="toolbar no-print">
            <div>
                <a href="{{ route('manager.payroll.dtr.index') }}" class="btn btn-back">
                    ⬅️ Back to DTR Station
                </a>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <button type="button" onclick="window.print()" class="btn btn-print">
                    🖨️ Print DTR Card
                </button>
            </div>
        </div>

        <!-- Official Plain DTR Header -->
        <div class="dtr-header">
            <h1>San Modesto Veterinary Clinic</h1>
            <h2>Daily Time Record (DTR)</h2>
            <p>Official Attendance & Timekeeping Audit Sheet</p>
        </div>

        <!-- Employee Info Table (Plain) -->
        <table class="emp-info-table">
            <tr>
                <td class="label">Employee Name:</td>
                <td class="value" style="font-size: 13px;">{{ $employee->full_name }}</td>
                <td class="label" style="padding-left: 20px;">EMP ID No:</td>
                <td class="value">{{ $employee->employee_code }}</td>
            </tr>
            <tr>
                <td class="label">Position / Role:</td>
                <td class="value">{{ $employee->position }}</td>
                <td class="label" style="padding-left: 20px;">Department:</td>
                <td class="value">{{ $employee->department ?? 'General Clinic' }}</td>
            </tr>
            <tr>
                <td class="label">Period Covered:</td>
                <td class="value">
                    {{ $startDate->format('F d, Y') }} &mdash; {{ $endDate->format('F d, Y') }} ({{ $periodLabel }})
                </td>
                <td class="label" style="padding-left: 20px;">Assigned Shift:</td>
                <td class="value">
                    {{ $employee->shift_start ? \Carbon\Carbon::parse($employee->shift_start)->format('g:i A') : '9:00 AM' }}
                    &mdash;
                    {{ $employee->shift_end ? \Carbon\Carbon::parse($employee->shift_end)->format('g:i A') : '6:00 PM' }}
                </td>
            </tr>
        </table>

        <!-- Daily Time Record Log Table -->
        <table class="dtr-table">
            <thead>
                <tr>
                    <th style="width: 85px;">Date</th>
                    <th style="width: 50px;">Day</th>
                    <th style="width: 85px;">Time In</th>
                    <th style="width: 85px;">Time Out</th>
                    <th style="width: 65px;">Worked Hrs</th>
                    <th style="width: 60px;">Late</th>
                    <th style="width: 60px;">Undertime</th>
                    <th style="width: 55px;">OT Hrs</th>
                    <th style="width: 110px;">Status / Tag</th>
                    <th class="no-print" style="width: 55px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($dailyLogs as $log)
                    @php
                        $inFormatted = $log->time_in ? date('h:i A', strtotime($log->time_in)) : '--:--';
                        $outFormatted = $log->time_out ? date('h:i A', strtotime($log->time_out)) : '--:--';
                        $statusLabel = match($log->status) {
                            'present' => 'Present',
                            'late' => 'Late',
                            'absent' => 'Absent',
                            'rest_day' => 'Rest Day',
                            'holiday' => 'Regular Holiday',
                            'regular_holiday' => 'Regular Holiday',
                            'special_holiday' => 'Special Holiday',
                            'overtime' => 'Overtime Tagged',
                            'off_duty' => 'Off Duty',
                            'on_leave' => 'On Leave',
                            default => $log->is_sunday ? 'Rest Day' : '-'
                        };
                    @endphp
                    <tr class="{{ $log->is_sunday ? 'sunday-row' : '' }}">
                        <td style="font-weight: 600;">{{ $log->date->format('M d, Y') }}</td>
                        <td>{{ $log->day_name }}</td>
                        <td class="time-val">{{ $inFormatted }}</td>
                        <td class="time-val">{{ $outFormatted }}</td>
                        <td style="font-weight: bold;">
                            {{ $log->regular_hours > 0 ? number_format($log->regular_hours, 2) : '-' }}
                        </td>
                        <td>
                            {{ $log->late_minutes > 0 ? $log->late_minutes . 'm' : '-' }}
                        </td>
                        <td>
                            {{ $log->undertime_minutes > 0 ? $log->undertime_minutes . 'm' : '-' }}
                        </td>
                        <td>
                            {{ $log->ot_hours > 0 ? number_format($log->ot_hours, 2) : '-' }}
                        </td>
                        <td>
                            <span>{{ $statusLabel }}</span>
                            @if ($log->notes)
                                <div style="font-size: 9.5px; color: #6b7280;">({{ $log->notes }})</div>
                            @endif
                        </td>
                        <td class="no-print">
                            <button type="button" class="btn-edit-row"
                                onclick="openEditDayModal(
                                    '{{ $log->date_str }}',
                                    '{{ $log->date->format('l, F d, Y') }}',
                                    '{{ $log->time_in ? date('H:i', strtotime($log->time_in)) : '' }}',
                                    '{{ $log->time_out ? date('H:i', strtotime($log->time_out)) : '' }}',
                                    '{{ $log->status }}',
                                    '{{ $log->ot_hours }}',
                                    '{{ addslashes($log->notes ?? '') }}'
                                )"
                                title="Edit DTR entry for this day">
                                ✏️ Edit
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary Totals Strip -->
        <div class="summary-box">
            <div>
                <div class="kpi-label">Days Present</div>
                <div class="kpi-val">{{ $summaryTotals['days_present'] }}</div>
            </div>
            <div>
                <div class="kpi-label">Duty Hours Worked</div>
                <div class="kpi-val">{{ number_format($summaryTotals['total_regular_hours'], 2) }} hrs</div>
            </div>
            <div>
                <div class="kpi-label">Total Tardiness</div>
                <div class="kpi-val">{{ $summaryTotals['formatted_late'] }}</div>
            </div>
            <div>
                <div class="kpi-label">Total Undertime</div>
                <div class="kpi-val">{{ $summaryTotals['formatted_undertime'] }}</div>
            </div>
            <div>
                <div class="kpi-label">Total Overtime</div>
                <div class="kpi-val">{{ number_format($summaryTotals['total_ot_hours'], 2) }} hrs</div>
            </div>
        </div>

        <!-- Certification Paragraph -->
        <div class="certification-text">
            I certify on my honor that the above is a true and correct record of the hours of work performed, record of daily arrival and departure at this station, in accordance with the assigned working hours.
        </div>

        <!-- Signature Lines -->
        <div class="signatures-container">
            <div class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-name">{{ $employee->full_name }}</div>
                <div class="sig-title">Employee Signature ({{ $employee->position }})</div>
            </div>
            <div class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-name">{{ auth()->check() ? auth()->user()->name : 'Clinic Manager' }}</div>
                <div class="sig-title">Verified & Approved In-Charge / Clinic Manager</div>
            </div>
        </div>
    </div>

    <!-- Quick Day Edit Modal (no-print) -->
    <div class="modal-overlay no-print" id="editDayModal">
        <div class="modal-box">
            <h3 id="modalDayTitle">Edit DTR Log Entry</h3>
            <form action="{{ route('manager.payroll.dtr.quick_update') }}" method="POST" id="quickEditForm">
                @csrf
                <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                <input type="hidden" name="record_date" id="modalRecordDate">

                <div class="form-row">
                    <label>Duty Status / Tag:</label>
                    <select name="status" id="modalStatus" required>
                        <option value="present">🟢 Present</option>
                        <option value="late">⚠️ Late</option>
                        <option value="rest_day">🏖️ Rest Day</option>
                        <option value="holiday">🎉 Regular Holiday</option>
                        <option value="special_holiday">✨ Special Holiday</option>
                        <option value="overtime">⏱️ Overtime Tagged</option>
                        <option value="off_duty">💤 Off Duty</option>
                        <option value="on_leave">🏖️ On Leave</option>
                        <option value="absent">❌ Absent</option>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div class="form-row">
                        <label>Time In (AM/PM):</label>
                        <input type="time" name="time_in" id="modalTimeIn">
                    </div>
                    <div class="form-row">
                        <label>Time Out (AM/PM):</label>
                        <input type="time" name="time_out" id="modalTimeOut">
                    </div>
                </div>

                <div class="form-row">
                    <label>Overtime Hours (Optional):</label>
                    <input type="number" step="0.5" min="0" name="ot_hours" id="modalOtHours" placeholder="e.g. 1.5">
                </div>

                <div class="form-row">
                    <label>Remarks / Notes (Optional):</label>
                    <input type="text" name="notes" id="modalNotes" placeholder="Reason or explanation...">
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-back" onclick="closeEditDayModal()">Cancel</button>
                    <button type="submit" class="btn btn-print">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditDayModal(dateStr, displayDate, timeIn, timeOut, status, otHours, notes) {
            document.getElementById('modalRecordDate').value = dateStr;
            document.getElementById('modalDayTitle').innerText = 'Edit DTR: ' + displayDate;
            document.getElementById('modalTimeIn').value = timeIn || '';
            document.getElementById('modalTimeOut').value = timeOut || '';
            document.getElementById('modalStatus').value = status || 'present';
            document.getElementById('modalOtHours').value = (otHours && otHours > 0) ? otHours : '';
            document.getElementById('modalNotes').value = notes || '';

            document.getElementById('editDayModal').style.display = 'flex';
        }

        function closeEditDayModal() {
            document.getElementById('editDayModal').style.display = 'none';
        }

        // Close when clicking overlay outside box
        window.addEventListener('click', function(e) {
            const modal = document.getElementById('editDayModal');
            if (e.target === modal) {
                closeEditDayModal();
            }
        });
    </script>
</body>
</html>
