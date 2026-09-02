<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - {{ $record->employee->full_name }} ({{ $record->payrollPeriod->period_name }})</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        body {
            background: #f1f5f9;
            color: #0f172a;
            padding: 2rem;
            display: flex;
            justify-content: center;
        }
        .payslip-container {
            background: #ffffff;
            width: 100%;
            max-width: 780px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 2.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 1.25rem;
            margin-bottom: 1.5rem;
        }
        .clinic-brand h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            color: #0f172a;
            letter-spacing: 0.5px;
        }
        .clinic-brand p {
            font-size: 0.78rem;
            color: #64748b;
            margin-top: 2px;
        }
        .payslip-badge {
            text-align: right;
        }
        .payslip-title {
            font-size: 1.35rem;
            font-weight: 800;
            color: #b88212;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .period-desc {
            font-size: 0.8rem;
            color: #475569;
            margin-top: 2px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            font-size: 0.82rem;
        }
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
        }
        .info-item .label {
            color: #64748b;
            font-weight: 500;
        }
        .info-item .val {
            color: #0f172a;
            font-weight: 700;
        }
        /* Attendance Summary Bar */
        .attendance-bar {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .attendance-bar-header {
            background: #0f172a;
            color: #ffffff;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0.45rem 1rem;
            display: flex;
            justify-content: space-between;
        }
        .attendance-columns {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr 1fr;
            background: #ffffff;
        }
        .att-card {
            padding: 0.65rem 0.85rem;
            border-right: 1px solid #e2e8f0;
            text-align: center;
        }
        .att-card:last-child {
            border-right: none;
        }
        .att-card .att-lbl {
            font-size: 0.68rem;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: 3px;
        }
        .att-card .att-val {
            font-size: 1.1rem;
            font-weight: 800;
            color: #0f172a;
        }
        .att-card.present {
            background: #f0fdf4;
        }
        .att-card.present .att-val {
            color: #15803d;
        }
        .att-card.absent {
            background: #fef2f2;
        }
        .att-card.absent .att-val {
            color: #b91c1c;
        }

        .breakdown-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .section-box {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
        }
        .section-header {
            background: #0f172a;
            color: #ffffff;
            font-size: 0.85rem;
            font-weight: 700;
            padding: 0.6rem 1rem;
            display: flex;
            justify-content: space-between;
        }
        .section-header.deductions {
            background: #475569;
        }
        .item-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.82rem;
        }
        .item-table td {
            padding: 0.55rem 1rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .item-table tr:last-child td {
            border-bottom: none;
        }
        .item-table .amt {
            text-align: right;
            font-weight: 600;
        }
        .section-total {
            background: #f8fafc;
            border-top: 2px solid #e2e8f0;
            padding: 0.65rem 1rem;
            display: flex;
            justify-content: space-between;
            font-weight: 800;
            font-size: 0.88rem;
        }
        .net-pay-banner {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 2px solid #f59e0b;
            border-radius: 8px;
            padding: 1.25rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .net-pay-label {
            font-size: 0.95rem;
            font-weight: 700;
            color: #78350f;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .net-pay-val {
            font-size: 1.8rem;
            font-weight: 800;
            color: #0f172a;
        }
        .signatures-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            margin-top: 2.5rem;
            padding-top: 1rem;
        }
        .sig-block {
            text-align: center;
        }
        .sig-line {
            border-bottom: 1px solid #0f172a;
            margin-bottom: 0.4rem;
            height: 40px;
        }
        .sig-title {
            font-size: 0.78rem;
            font-weight: 700;
            color: #0f172a;
        }
        .sig-sub {
            font-size: 0.68rem;
            color: #64748b;
        }
        .print-toolbar {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            display: flex;
            gap: 0.5rem;
            z-index: 100;
        }
        .btn-print {
            background: #b88212;
            color: #ffffff;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }
        .btn-print:hover {
            background: #94680c;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .payslip-container {
                box-shadow: none;
                border: none;
                padding: 0;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="print-toolbar no-print">
        <button type="button" class="btn-print" onclick="window.print()">🖨️ Print Payslip</button>
        <button type="button" class="btn-print" style="background: #475569;" onclick="window.close()">Close</button>
    </div>

    <div class="payslip-container">
        <!-- Header -->
        <div class="header-bar">
            <div class="clinic-brand">
                <h1>San Modesto Veterinary Services</h1>
                <p>Official Employee Compensation Voucher</p>
                <p style="font-size: 0.72rem; color: #94a3b8;">San Modesto Vet Clinic, Philippines</p>
            </div>
            <div class="payslip-badge">
                <div class="payslip-title">Payslip</div>
                <div class="period-desc">{{ $record->payrollPeriod->period_name }}</div>
                <div class="period-desc" style="font-weight: 600;">Payout: {{ $record->payrollPeriod->payout_date->format('M d, Y') }}</div>
            </div>
        </div>

        <!-- Employee Info Summary -->
        <div class="info-grid">
            <div>
                <div class="info-item">
                    <span class="label">Employee Name:</span>
                    <span class="val">{{ $record->employee->full_name }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Employee ID:</span>
                    <span class="val">{{ $record->employee->employee_code }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Position / Role:</span>
                    <span class="val">{{ $record->employee->position }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Department:</span>
                    <span class="val">{{ $record->employee->department }}</span>
                </div>
            </div>
            <div>
                <div class="info-item">
                    <span class="label">Monthly Base:</span>
                    <span class="val">₱{{ number_format($record->basic_salary, 2) }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Days Worked:</span>
                    <span class="val">{{ $record->days_worked }} days</span>
                </div>
                <div class="info-item">
                    <span class="label">Coverage:</span>
                    <span class="val">{{ $record->payrollPeriod->start_date->format('M d') }} - {{ $record->payrollPeriod->end_date->format('M d, Y') }}</span>
                </div>
                <div class="info-item">
                    <span class="label">SSS / PhilHealth / Pag-IBIG:</span>
                    <span class="val" style="font-size: 0.72rem;">{{ $record->employee->sss_no ?: '-' }} / {{ $record->employee->philhealth_no ?: '-' }} / {{ $record->employee->pagibig_no ?: '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Transparent Timekeeping & Attendance Summary (Present vs Absent Columns) -->
        <div class="attendance-bar">
            <div class="attendance-bar-header">
                <span>⏱️ Attendance & Timekeeping Summary (DTR)</span>
                <span>Period: {{ $record->payrollPeriod->start_date->format('M d') }} — {{ $record->payrollPeriod->end_date->format('M d, Y') }}</span>
            </div>
            <div class="attendance-columns">
                <div class="att-card present">
                    <div class="att-lbl">Present Days</div>
                    <div class="att-val">{{ $presentDays ?? $record->days_worked }} <span style="font-size: 0.72rem; font-weight: 600; color: #15803d;">days</span></div>
                </div>
                <div class="att-card absent">
                    <div class="att-lbl">Absent Days</div>
                    <div class="att-val">{{ $absentDays ?? 0 }} <span style="font-size: 0.72rem; font-weight: 600; color: #b91c1c;">days</span></div>
                </div>
                <div class="att-card">
                    <div class="att-lbl">Late / Undertime</div>
                    <div class="att-val">{{ $lateMins ?? 0 }} <span style="font-size: 0.72rem; font-weight: 600; color: #64748b;">mins</span></div>
                </div>
                <div class="att-card">
                    <div class="att-lbl">Overtime (OT)</div>
                    <div class="att-val">{{ $record->ot_hours }} <span style="font-size: 0.72rem; font-weight: 600; color: #64748b;">hrs</span></div>
                </div>
                <div class="att-card">
                    <div class="att-lbl">Daily Rate</div>
                    <div class="att-val" style="font-size: 1rem; color: #b88212;">₱{{ number_format($record->employee->daily_rate ?: ($record->employee->basic_salary / 22), 2) }}</div>
                </div>
            </div>
        </div>

        <!-- Earnings & Deductions Dual Grid -->
        <div class="breakdown-grid">
            <!-- Earnings -->
            <div class="section-box">
                <div class="section-header">
                    <span>EARNINGS & INCENTIVES</span>
                    <span>AMOUNT (₱)</span>
                </div>
                <table class="item-table">
                    <tbody>
                        <tr>
                            <td>Regular Period Salary</td>
                            <td class="amt">₱{{ number_format($record->regular_pay, 2) }}</td>
                        </tr>
                        <tr>
                            <td>
                                Overtime Pay
                                @if($record->ot_hours > 0)
                                    <span style="font-size: 0.7rem; color: #64748b;">({{ $record->ot_hours }} hrs)</span>
                                @endif
                            </td>
                            <td class="amt">₱{{ number_format($record->ot_pay, 2) }}</td>
                        </tr>
                        @if(isset($incentives) && $incentives->isNotEmpty())
                            @foreach($incentives as $inc)
                                <tr>
                                    <td>
                                        <strong style="color: #16a34a;">+ {{ $inc->title }}</strong>
                                        <div style="font-size: 0.68rem; color: #64748b;">
                                            @if($inc->calculation_basis === 'percentage')
                                                Rate: {{ $inc->rate_applied }}% on ₱{{ number_format($inc->base_amount_or_count, 2) }} sales
                                            @else
                                                Rate: ₱{{ number_format($inc->rate_applied, 2) }} × {{ number_format($inc->base_amount_or_count, 0) }} units
                                            @endif
                                            @if($inc->notes) • {{ $inc->notes }} @endif
                                        </div>
                                    </td>
                                    <td class="amt" style="color: #16a34a; font-weight: 700;">+₱{{ number_format($inc->total_incentive, 2) }}</td>
                                </tr>
                            @endforeach
                        @elseif($record->incentives_total > 0)
                            <tr>
                                <td>
                                    <strong>Role Incentives & Commissions</strong>
                                    <div style="font-size: 0.68rem; color: #16a34a;">(Grooming %, Vet Consults, Boarding)</div>
                                </td>
                                <td class="amt" style="color: #16a34a; font-weight: 700;">+₱{{ number_format($record->incentives_total, 2) }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                <div class="section-total">
                    <span>GROSS EARNINGS</span>
                    <span>₱{{ number_format($record->gross_pay, 2) }}</span>
                </div>
            </div>

            <!-- Deductions -->
            <div class="section-box">
                <div class="section-header deductions">
                    <span>DEDUCTIONS</span>
                    <span>AMOUNT (₱)</span>
                </div>
                <table class="item-table">
                    <tbody>
                        <tr>
                            <td>SSS Contribution</td>
                            <td class="amt">₱{{ number_format($record->sss_deduction, 2) }}</td>
                        </tr>
                        <tr>
                            <td>PhilHealth Contribution</td>
                            <td class="amt">₱{{ number_format($record->philhealth_deduction, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Pag-IBIG Contribution</td>
                            <td class="amt">₱{{ number_format($record->pagibig_deduction, 2) }}</td>
                        </tr>
                        @if($record->tax_deduction > 0)
                            <tr>
                                <td>Withholding Tax</td>
                                <td class="amt">₱{{ number_format($record->tax_deduction, 2) }}</td>
                            </tr>
                        @endif
                        @if($record->loan_deduction > 0)
                            <tr>
                                <td>Company / Gov Loan Amortization</td>
                                <td class="amt">₱{{ number_format($record->loan_deduction, 2) }}</td>
                            </tr>
                        @endif
                        @if($record->cash_advance_deduction > 0)
                            <tr>
                                <td>Cash Advance (CA) Repayment</td>
                                <td class="amt">₱{{ number_format($record->cash_advance_deduction, 2) }}</td>
                            </tr>
                        @endif
                        <!-- Absences & Tardiness Deduction Row -->
                        <tr>
                            <td>
                                <strong style="color: {{ (($absentDays ?? 0) > 0 || $record->absence_tardiness_deduction > 0) ? '#dc2626' : '#0f172a' }};">
                                    Absences & Tardiness
                                </strong>
                                <div style="font-size: 0.68rem; color: #64748b;">
                                    @if(($absentDays ?? 0) > 0)
                                        <span style="color: #dc2626; font-weight: 700;">{{ $absentDays }} day(s) absent</span> (₱{{ number_format($record->employee->daily_rate ?: ($record->employee->basic_salary / 22), 2) }} × {{ $absentDays }})
                                    @endif
                                    @if(($lateMins ?? 0) > 0)
                                        • {{ $lateMins }} min(s) late
                                    @endif
                                    @if(($absentDays ?? 0) == 0 && ($lateMins ?? 0) == 0 && $record->absence_tardiness_deduction == 0)
                                        <span style="color: #15803d; font-weight: 600;">✓ Perfect attendance (0 absences)</span>
                                    @endif
                                </div>
                            </td>
                            <td class="amt" style="color: {{ (($absentDays ?? 0) > 0 || $record->absence_tardiness_deduction > 0) ? '#dc2626' : '#64748b' }}; font-weight: 700;">
                                @if($record->absence_tardiness_deduction > 0)
                                    -₱{{ number_format($record->absence_tardiness_deduction, 2) }}
                                @elseif(($absentDays ?? 0) > 0)
                                    -₱{{ number_format(($absentDays ?? 0) * ($record->employee->daily_rate ?: ($record->employee->basic_salary / 22)), 2) }}
                                @else
                                    ₱0.00
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="section-total" style="color: #dc2626;">
                    <span>TOTAL DEDUCTIONS</span>
                    <span>-₱{{ number_format($record->total_deductions, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Net Pay Highlight -->
        <div class="net-pay-banner">
            <div>
                <div class="net-pay-label">Total Net Pay Disbursed</div>
                <div style="font-size: 0.72rem; color: #92400e;">(Gross Earnings minus Total Deductions)</div>
            </div>
            <div class="net-pay-val">
                ₱{{ number_format($record->net_pay, 2) }}
            </div>
        </div>

        <!-- Signatures -->
        <div class="signatures-grid">
            <div class="sig-block">
                <div class="sig-line"></div>
                <div class="sig-title">{{ $record->employee->full_name }}</div>
                <div class="sig-sub">Employee Acknowledgment & Signature</div>
            </div>
            <div class="sig-block">
                <div class="sig-line"></div>
                <div class="sig-title">Dr. Modesto Reyes / Authorized Officer</div>
                <div class="sig-sub">Approved & Released by Admin / Management</div>
            </div>
        </div>
    </div>
</body>
</html>
