<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annual Leave Ledger & SL Monetization Report - Year {{ $year }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fff;
            color: #111;
            margin: 0;
            padding: 24px;
            font-size: 11pt;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0a1128;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .clinic-name {
            font-size: 16pt;
            font-weight: bold;
            color: #0a1128;
            margin-bottom: 4px;
            text-transform: uppercase;
        }
        .report-title {
            font-size: 13pt;
            font-weight: bold;
            color: #b45309;
            margin-top: 4px;
        }
        .meta-bar {
            display: flex;
            justify-content: space-between;
            font-size: 9pt;
            color: #555;
            margin-bottom: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            font-size: 9.5pt;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            font-size: 8.5pt;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .highlight-col { background-color: #fef3c7; font-weight: bold; }
        .policy-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 10px 14px;
            font-size: 8.5pt;
            color: #334155;
            margin-bottom: 24px;
            line-height: 1.4;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }
        .sig-block {
            width: 200px;
            text-align: center;
            border-top: 1px solid #111;
            padding-top: 6px;
            font-size: 9.5pt;
        }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
        <a href="javascript:window.history.back()" style="color: #2563eb; text-decoration: none; font-weight: bold;">&larr; Back to Leaves</a>
        <button onclick="window.print()" style="background: #b45309; color: #fff; border: none; padding: 8px 16px; border-radius: 4px; font-weight: bold; cursor: pointer;">
            🖨️ Print Annual Leave Ledger (File Keeping)
        </button>
    </div>

    <div class="header">
        <div class="clinic-name">San Modesto Veterinary Clinic</div>
        <div style="font-size: 9.5pt; color: #475569;">Official Human Resources & Payroll File Keeping Record</div>
        <div class="report-title">ANNUAL LEAVE LEDGER & SICK LEAVE MONETIZATION AUDIT (YEAR {{ $year }})</div>
    </div>

    <div class="meta-bar">
        <div><strong>Ledger Calendar Year:</strong> January 1, {{ $year }} – December 31, {{ $year }}</div>
        <div><strong>Reset Schedule:</strong> Every Jan 1, 01:00 AM</div>
        <div><strong>Printed At:</strong> {{ now()->format('M d, Y h:i A') }}</div>
    </div>

    <div class="policy-box">
        <strong>Official Clinic Leave Policy (Annual 12-Day Quota):</strong><br>
        1. <strong>Quota:</strong> 5 Days Vacation Leave (VL), 5 Days Sick Leave (SL), 2 Days Special Leave (SPL) = 12 Days Total.<br>
        2. <strong>Sick Leave Monetization:</strong> Unused Sick Leave (up to 5 days) is payable in cash: <em>(Unused SL × Daily Rate)</em>.<br>
        3. <strong>Forfeiture:</strong> Unused Vacation Leave (VL) and Special Leave (SPL) are forfeited at year-end and non-payable.
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" class="text-center" style="width: 30px;">#</th>
                <th rowspan="2">Employee Name</th>
                <th rowspan="2">Position</th>
                <th rowspan="2" class="text-right">Daily Rate</th>
                <th colspan="2" class="text-center" style="background: #e0f2fe;">Vacation (VL - 5d)</th>
                <th colspan="3" class="text-center" style="background: #dcfce7;">Sick Leave (SL - 5d)</th>
                <th colspan="2" class="text-center" style="background: #fef9c3;">Special (SPL - 2d)</th>
                <th rowspan="2" class="text-right highlight-col">SL Monetization Payout</th>
            </tr>
            <tr>
                <th class="text-center" style="background: #e0f2fe; font-size: 7.5pt;">Used</th>
                <th class="text-center" style="background: #e0f2fe; font-size: 7.5pt;">Forfeited</th>
                <th class="text-center" style="background: #dcfce7; font-size: 7.5pt;">Used</th>
                <th class="text-center" style="background: #dcfce7; font-size: 7.5pt;">Unused</th>
                <th class="text-center" style="background: #dcfce7; font-size: 7.5pt;">Status</th>
                <th class="text-center" style="background: #fef9c3; font-size: 7.5pt;">Used</th>
                <th class="text-center" style="background: #fef9c3; font-size: 7.5pt;">Forfeited</th>
            </tr>
        </thead>
        <tbody>
            @php $totalPayout = 0; @endphp
            @forelse($ledgers as $idx => $ledger)
                @php $totalPayout += floatval($ledger->sl_payout_amount); @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $ledger->employee_name }}</strong>
                        <div style="font-size: 7.5pt; color: #64748b;">{{ $ledger->employee_code }}</div>
                    </td>
                    <td>{{ $ledger->position }}</td>
                    <td class="text-right">₱{{ number_format($ledger->daily_rate, 2) }}</td>
                    <td class="text-center">{{ $ledger->vl_used }}d</td>
                    <td class="text-center" style="color: #94a3b8;">{{ $ledger->vl_forfeited }}d</td>
                    <td class="text-center">{{ $ledger->sl_used }}d</td>
                    <td class="text-center" style="font-weight: bold; color: #16a34a;">{{ $ledger->sl_unused }}d</td>
                    <td class="text-center" style="font-size: 7.5pt;">
                        {{ $ledger->sl_credited ? 'Credited' : ($ledger->sl_unused > 0 ? 'Payable' : 'Consumed') }}
                    </td>
                    <td class="text-center">{{ $ledger->spl_used }}d</td>
                    <td class="text-center" style="color: #94a3b8;">{{ $ledger->spl_forfeited }}d</td>
                    <td class="text-right highlight-col">
                        ₱{{ number_format($ledger->sl_payout_amount, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center" style="padding: 16px; color: #64748b;">
                        No ledger entries found for Year {{ $year }}.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background: #f8fafc;">
                <td colspan="11" class="text-right">TOTAL SICK LEAVE (SL) MONETIZATION PAYABLE:</td>
                <td class="text-right highlight-col" style="font-size: 11pt; color: #b45309;">
                    ₱{{ number_format($totalPayout, 2) }}
                </td>
            </tr>
        </tfoot>
    </table>

    <div class="signatures">
        <div class="sig-block">
            <strong>Prepared By:</strong><br><br>
            HR / Payroll Officer
        </div>
        <div class="sig-block">
            <strong>Audited & Verified:</strong><br><br>
            Clinic Administrator
        </div>
        <div class="sig-block">
            <strong>Approved for Payout:</strong><br><br>
            Medical Director / Owner
        </div>
    </div>
</body>
</html>
