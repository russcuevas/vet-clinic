<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>13th Month Pay & SL Summary Report ({{ $year }})</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        body {
            background: #f8fafc;
            color: #0f172a;
            padding: 2rem 1.5rem;
        }
        .report-container {
            max-width: 1050px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
            padding: 2rem 2.5rem;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 1.25rem;
            margin-bottom: 1.5rem;
        }
        .brand h1 {
            font-size: 1.35rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
        }
        .brand p {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 2px;
        }
        .badge {
            text-align: right;
        }
        .badge .title {
            background: #b88212;
            color: #ffffff;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0.35rem 0.75rem;
            border-radius: 4px;
            display: inline-block;
        }
        .badge .date {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 4px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
            margin-bottom: 2rem;
        }
        .summary-table th {
            background: #0f172a;
            color: #ffffff;
            font-weight: 700;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0.65rem 0.6rem;
            text-align: left;
        }
        .summary-table th.num, .summary-table td.num {
            text-align: right;
        }
        .summary-table td {
            padding: 0.6rem 0.6rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .summary-table tr:nth-child(even) {
            background: #f8fafc;
        }
        .summary-table tfoot td {
            background: #f1f5f9;
            font-weight: 800;
            border-top: 2px solid #0f172a;
            padding: 0.75rem 0.6rem;
        }

        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 2rem;
            margin-top: 3rem;
            padding-top: 1.5rem;
        }
        .sig-block {
            text-align: center;
        }
        .sig-line {
            border-bottom: 1px solid #0f172a;
            height: 35px;
            margin-bottom: 4px;
        }
        .sig-lbl {
            font-size: 0.75rem;
            font-weight: 700;
            color: #0f172a;
        }
        .sig-sub {
            font-size: 0.68rem;
            color: #64748b;
        }

        .print-btn-bar {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            display: flex;
            gap: 0.5rem;
            z-index: 100;
        }
        .btn {
            background: #b88212;
            color: #ffffff;
            border: none;
            padding: 0.55rem 1.1rem;
            border-radius: 6px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
        .btn:hover {
            background: #94680c;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .report-container { border: none; box-shadow: none; max-width: 100%; padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="print-btn-bar no-print">
        <button class="btn" onclick="window.print()">🖨️ Print Report</button>
        <button class="btn" style="background: #475569;" onclick="window.close()">Close</button>
    </div>

    <div class="report-container">
        <!-- Header -->
        <div class="header">
            <div class="brand">
                <h1>San Modesto Veterinary Services</h1>
                <p>Annual 13th Month Pay Bonus & Sick Leave (SL) Monetization Master Summary</p>
                <p style="font-size: 0.7rem; color: #94a3b8;">Philippine Labor Code (DOLE) Annual Statutory Payroll Voucher Report</p>
            </div>
            <div class="badge">
                <div class="title">Year {{ $year }} Master Register</div>
                <div class="date">Printed: {{ date('M d, Y • h:i A') }}</div>
            </div>
        </div>

        <!-- Table -->
        <table class="summary-table">
            <thead>
                <tr>
                    <th>EMP #</th>
                    <th>Employee Name</th>
                    <th>Position</th>
                    <th class="num">Daily Rate (₱)</th>
                    <th class="num">Total Basic (₱)</th>
                    <th class="num">13th Month Pay (₱)</th>
                    <th class="num">SL Days</th>
                    <th class="num">Monetized SL (₱)</th>
                    <th class="num">Total Disbursed (₱)</th>
                    <th style="width: 110px; text-align: center;">Signature</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $rec)
                    <tr>
                        <td style="font-family: monospace; font-weight: 700;">{{ $rec->employee->employee_code }}</td>
                        <td><strong>{{ $rec->employee->full_name }}</strong></td>
                        <td>{{ $rec->employee->position }}</td>
                        <td class="num">₱{{ number_format($rec->daily_rate, 2) }}</td>
                        <td class="num">₱{{ number_format($rec->total_basic_earned, 2) }}</td>
                        <td class="num" style="color: #b88212; font-weight: 700;">₱{{ number_format($rec->thirteenth_month_pay, 2) }}</td>
                        <td class="num">{{ floatval($rec->sl_days_remaining) }}d</td>
                        <td class="num" style="color: #0284c7; font-weight: 700;">+₱{{ number_format($rec->sl_monetized_amount, 2) }}</td>
                        <td class="num"><strong style="color: #15803d; font-size: 0.85rem;">₱{{ number_format($rec->total_payout, 2) }}</strong></td>
                        <td style="border-bottom: 1px dotted #cbd5e1;"></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 2rem; color: #94a3b8;">
                            No generated 13th month records for Year {{ $year }}. Click "Generate All" to calculate.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4">ANNUAL CLINIC TOTALS</td>
                    <td class="num">₱{{ number_format($records->sum('total_basic_earned'), 2) }}</td>
                    <td class="num" style="color: #b88212;">₱{{ number_format($records->sum('thirteenth_month_pay'), 2) }}</td>
                    <td class="num">{{ floatval($records->sum('sl_days_remaining')) }}d</td>
                    <td class="num" style="color: #0284c7;">+₱{{ number_format($records->sum('sl_monetized_amount'), 2) }}</td>
                    <td class="num" style="font-size: 0.95rem; color: #15803d;">₱{{ number_format($records->sum('total_payout'), 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <!-- Signatures -->
        <div class="signatures">
            <div class="sig-block">
                <div class="sig-line"></div>
                <div class="sig-lbl">Prepared By</div>
                <div class="sig-sub">Payroll Officer / HR Dept</div>
            </div>
            <div class="sig-block">
                <div class="sig-line"></div>
                <div class="sig-lbl">Audited & Verified By</div>
                <div class="sig-sub">Finance & Internal Audit</div>
            </div>
            <div class="sig-block">
                <div class="sig-line"></div>
                <div class="sig-lbl">Approved For Release</div>
                <div class="sig-sub">Dr. Modesto Reyes / Clinic Director</div>
            </div>
        </div>
    </div>
</body>
</html>
