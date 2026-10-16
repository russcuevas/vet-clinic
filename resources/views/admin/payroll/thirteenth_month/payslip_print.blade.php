<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>13th Month & SL Payslip - {{ $record->employee->full_name }} ({{ $record->year }})</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        body {
            background: #f1f5f9;
            color: #0f172a;
            padding: 2rem 1rem;
        }
        .payslip-container {
            max-width: 820px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            padding: 2.5rem;
        }
        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 1.25rem;
            margin-bottom: 1.5rem;
        }
        .clinic-brand h1 {
            font-size: 1.45rem;
            color: #0f172a;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .clinic-brand p {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 2px;
        }
        .voucher-badge {
            text-align: right;
        }
        .voucher-title {
            background: #b88212;
            color: #ffffff;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0.35rem 0.75rem;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 0.35rem;
        }
        .voucher-period {
            font-size: 1.1rem;
            font-weight: 800;
            color: #0f172a;
        }
        .voucher-date {
            font-size: 0.75rem;
            color: #64748b;
        }

        /* Employee Info */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            font-size: 0.82rem;
        }
        .info-item {
            display: flex;
            margin-bottom: 0.35rem;
        }
        .info-item:last-child {
            margin-bottom: 0;
        }
        .info-item .label {
            width: 140px;
            color: #64748b;
            font-weight: 600;
        }
        .info-item .val {
            font-weight: 700;
            color: #0f172a;
        }

        /* Monthly Table */
        .section-title {
            font-size: 0.85rem;
            font-weight: 800;
            text-transform: uppercase;
            color: #0f172a;
            letter-spacing: 0.5px;
            margin-bottom: 0.6rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .month-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
            margin-bottom: 1.25rem;
            border: 1px solid #e2e8f0;
        }
        .month-table th {
            background: #0f172a;
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.5px;
            padding: 0.5rem 0.75rem;
            text-align: left;
        }
        .month-table th.num, .month-table td.num {
            text-align: right;
        }
        .month-table td {
            padding: 0.45rem 0.75rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .month-table tr:nth-child(even) {
            background: #f8fafc;
        }
        .month-table tfoot td {
            background: #f1f5f9;
            font-weight: 800;
            border-top: 2px solid #cbd5e1;
            padding: 0.6rem 0.75rem;
        }

        /* Formula & SL Box */
        .cards-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
            margin-bottom: 1.5rem;
        }
        .formula-box {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 1rem;
            background: #fafaf9;
        }
        .formula-box .box-title {
            font-size: 0.78rem;
            font-weight: 800;
            text-transform: uppercase;
            color: #b88212;
            margin-bottom: 0.5rem;
        }
        .formula-box p {
            font-size: 0.76rem;
            color: #475569;
            line-height: 1.4;
            margin-bottom: 0.35rem;
        }
        .formula-box .highlight {
            font-size: 1.25rem;
            font-weight: 800;
            color: #b88212;
            margin-top: 0.5rem;
        }

        .sl-box {
            border: 1px solid #bae6fd;
            border-radius: 6px;
            padding: 1rem;
            background: #f0f9ff;
        }
        .sl-box .box-title {
            font-size: 0.78rem;
            font-weight: 800;
            text-transform: uppercase;
            color: #0284c7;
            margin-bottom: 0.5rem;
        }
        .sl-box p {
            font-size: 0.76rem;
            color: #0369a1;
            line-height: 1.4;
            margin-bottom: 0.35rem;
        }
        .sl-box .highlight {
            font-size: 1.25rem;
            font-weight: 800;
            color: #0284c7;
            margin-top: 0.5rem;
        }

        /* Net Pay Banner */
        .net-pay-banner {
            background: #0f172a;
            color: #ffffff;
            border-radius: 6px;
            padding: 1.25rem 1.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .net-pay-banner .lbl {
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .net-pay-banner .sub {
            font-size: 0.72rem;
            color: #94a3b8;
            margin-top: 2px;
        }
        .net-pay-banner .amt {
            font-size: 1.85rem;
            font-weight: 900;
            color: #fbbf24;
        }

        /* Signatures */
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
            height: 45px;
            margin-bottom: 6px;
        }
        .sig-title {
            font-size: 0.82rem;
            font-weight: 700;
            color: #0f172a;
        }
        .sig-sub {
            font-size: 0.7rem;
            color: #64748b;
        }

        /* Print Toolbar */
        .print-toolbar {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            display: flex;
            gap: 0.5rem;
            z-index: 1000;
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
        <button type="button" class="btn-print" onclick="window.print()">🖨️ Print 13th Month Payslip</button>
        <button type="button" class="btn-print" style="background: #475569;" onclick="window.close()">Close</button>
    </div>

    <div class="payslip-container">
        <!-- Header -->
        <div class="header-bar">
            <div class="clinic-brand">
                <h1>San Modesto Veterinary Services</h1>
                <p>Official 13th Month Pay & Sick Leave (SL) Monetization Voucher</p>
                <p style="font-size: 0.72rem; color: #94a3b8;">San Modesto Vet Clinic • Animal Wellness & Surgical Hospital</p>
            </div>
            <div class="voucher-badge">
                <div class="voucher-title">Year-End Bonus Voucher</div>
                <div class="voucher-period">Year {{ $record->year }}</div>
                <div class="voucher-date">
                    Payout Date: {{ $record->payout_date ? $record->payout_date->format('M d, Y') : date('M d, Y') }}
                </div>
            </div>
        </div>

        <!-- Employee Info -->
        <div class="info-grid">
            <div>
                <div class="info-item">
                    <span class="label">Employee Name:</span>
                    <span class="val">{{ $record->employee->full_name }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Employee Code:</span>
                    <span class="val" style="font-family: monospace;">{{ $record->employee->employee_code }}</span>
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
                    <span class="label">Date Hired:</span>
                    <span class="val">{{ $record->employee->date_hired ? $record->employee->date_hired->format('F d, Y') : 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Daily Rate:</span>
                    <span class="val">₱{{ number_format($record->daily_rate, 2) }} / day</span>
                </div>
                <div class="info-item">
                    <span class="label">Monthly Basic:</span>
                    <span class="val">₱{{ number_format($record->basic_salary, 2) }} / month</span>
                </div>
                <div class="info-item">
                    <span class="label">Payment Status:</span>
                    <span class="val" style="color: {{ $record->payment_status === 'paid' ? '#15803d' : '#b45309' }};">
                        {{ strtoupper($record->payment_status) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Monthly Breakdown Table (12 Months) -->
        <div class="section-title">
            <span>📅 12-Month Computation Breakdown (Jan–Dec {{ $record->year }})</span>
            <span style="font-size: 0.72rem; color: #64748b; font-weight: normal;">Formula: Days Worked in Month × Daily Rate</span>
        </div>

        <table class="month-table">
            <thead>
                <tr>
                    <th>Month</th>
                    <th class="num">Days Worked / Present</th>
                    <th class="num">Daily Rate (₱)</th>
                    <th class="num">Month Bonus Computation (₱)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $breakdown = $record->monthly_breakdown ?? [];
                    $totalDays = 0;
                @endphp
                @for($m = 1; $m <= 12; $m++)
                    @php
                        $mDate = \Carbon\Carbon::createFromDate($record->year, $m, 1);
                        $mName = $mDate->format('F');
                        $itemData = $breakdown[$m - 1] ?? null;
                        $days = isset($itemData['days_worked']) ? floatval($itemData['days_worked']) : 22;
                        $amt = isset($itemData['amount']) ? floatval($itemData['amount']) : round($days * floatval($record->daily_rate), 2);
                        $totalDays += $days;
                    @endphp
                    <tr>
                        <td><strong>{{ $mName }}</strong></td>
                        <td class="num">{{ number_format($days, 1) }} day(s)</td>
                        <td class="num">₱{{ number_format($record->daily_rate, 2) }}</td>
                        <td class="num" style="font-weight: 600;">₱{{ number_format($amt, 2) }}</td>
                    </tr>
                @endfor
            </tbody>
            <tfoot>
                <tr>
                    <td>CUMULATIVE 12-MONTH TOTAL</td>
                    <td class="num">{{ number_format($totalDays, 1) }} days</td>
                    <td class="num"></td>
                    <td class="num" style="color: #0f172a; font-size: 0.95rem;">
                        ₱{{ number_format($record->total_basic_earned, 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Formula & Monetized SL Summary Cards -->
        <div class="cards-row">
            <!-- 13th Month Pay -->
            <div class="formula-box">
                <div class="box-title">1. 13th Month Bonus Pay</div>
                <p><strong>Philippine DOLE Statutory Formula:</strong></p>
                <p>Total Basic Salary Earned (Jan–Dec) ÷ 12</p>
                <p style="font-family: monospace; color: #64748b;">
                    ₱{{ number_format($record->total_basic_earned, 2) }} ÷ 12
                </p>
                <div class="highlight">
                    ₱{{ number_format($record->thirteenth_month_pay, 2) }}
                </div>
            </div>

            <!-- Monetized Sick Leave -->
            <div class="sl-box">
                <div class="box-title">2. Monetized Sick Leave (SL)</div>
                <p><strong>Annual SL Conversion to Cash:</strong></p>
                <p>
                    • Annual Quota: <strong>{{ floatval($record->sl_total_quota ?: 5) }} days</strong><br>
                    • Unconsumed SL Monetized: <strong>{{ floatval($record->sl_days_remaining) }} day(s)</strong><br>
                    • Computation: {{ floatval($record->sl_days_remaining) }} days × ₱{{ number_format($record->daily_rate, 2) }}
                </p>
                <div class="highlight">
                    +₱{{ number_format($record->sl_monetized_amount, 2) }}
                </div>
            </div>
        </div>

        @if($record->other_bonus > 0 || $record->deductions > 0)
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.75rem 1rem; margin-bottom: 1.5rem; font-size: 0.8rem; display: flex; justify-content: space-between;">
                @if($record->other_bonus > 0)
                    <div><strong>Other Year-End Incentive:</strong> <span style="color: #15803d;">+₱{{ number_format($record->other_bonus, 2) }}</span></div>
                @endif
                @if($record->deductions > 0)
                    <div><strong>Withholding / Deductions:</strong> <span style="color: #dc2626;">-₱{{ number_format($record->deductions, 2) }}</span></div>
                @endif
            </div>
        @endif

        <!-- Net Disbursed Banner -->
        <div class="net-pay-banner">
            <div>
                <div class="lbl">Total Year-End Net Disbursed</div>
                <div class="sub">13th Month Bonus Pay + Monetized Sick Leave (SL)</div>
            </div>
            <div class="amt">
                ₱{{ number_format($record->total_payout, 2) }}
            </div>
        </div>

        <!-- Remarks -->
        @if($record->notes)
            <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 1.5rem; font-style: italic;">
                <strong>Notes / Remarks:</strong> {{ $record->notes }}
            </div>
        @endif

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
