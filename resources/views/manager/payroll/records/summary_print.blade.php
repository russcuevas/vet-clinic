<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Summary Report - {{ $period->period_name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        body {
            background: #f8fafc;
            color: #0f172a;
            padding: 2rem;
        }
        .report-container {
            background: #ffffff;
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 1.25rem;
            margin-bottom: 1.5rem;
        }
        .header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            color: #0f172a;
        }
        .header p {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 2px;
        }
        .meta-box {
            text-align: right;
            font-size: 0.82rem;
        }
        .meta-box .title {
            font-size: 1.25rem;
            font-weight: 800;
            color: #b88212;
            text-transform: uppercase;
        }
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 0.75rem 1rem;
        }
        .card .lbl {
            font-size: 0.72rem;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
        }
        .card .val {
            font-size: 1.15rem;
            font-weight: 800;
            color: #0f172a;
            margin-top: 2px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.78rem;
            margin-bottom: 1.5rem;
        }
        th, td {
            padding: 0.55rem 0.65rem;
            border: 1px solid #cbd5e1;
            text-align: left;
        }
        th {
            background: #0f172a;
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.72rem;
        }
        .num {
            text-align: right;
        }
        tr:nth-child(even) {
            background: #f8fafc;
        }
        tfoot tr {
            background: #f1f5f9;
            font-weight: 800;
        }
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 2rem;
            margin-top: 2.5rem;
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
        @media print {
            body { background: #fff; padding: 0; }
            .report-container { border: none; box-shadow: none; max-width: 100%; padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="print-btn-bar no-print">
        <button type="button" class="btn" onclick="window.print()">🖨️ Print Summary Sheet</button>
        <button type="button" class="btn" style="background: #475569;" onclick="window.close()">Close</button>
    </div>

    <div class="report-container">
        <div class="header">
            <div>
                <h1>San Modesto Veterinary Services</h1>
                <p>Official Payroll Register & Disbursement Summary</p>
                <p>Period: <strong>{{ $period->start_date->format('F d, Y') }} — {{ $period->end_date->format('F d, Y') }}</strong></p>
            </div>
            <div class="meta-box">
                <div class="title">{{ $period->period_type === '15_days' ? '15-Day Semi-Monthly' : '30-Day Monthly' }}</div>
                <div>Cycle: <strong>{{ $period->period_name }}</strong></div>
                <div>Payout Date: <strong>{{ $period->payout_date->format('M d, Y') }}</strong></div>
            </div>
        </div>

        <div class="summary-cards">
            <div class="card">
                <div class="lbl">Total Personnel</div>
                <div class="val">{{ $period->records->count() }} Employees</div>
            </div>
            <div class="card">
                <div class="lbl">Gross Earnings</div>
                <div class="val">₱{{ number_format($period->total_gross_pay, 2) }}</div>
            </div>
            <div class="card">
                <div class="lbl">Total Role Incentives</div>
                <div class="val" style="color: #16a34a;">+₱{{ number_format($period->total_incentives, 2) }}</div>
            </div>
            <div class="card">
                <div class="lbl">Total Net Payable</div>
                <div class="val" style="color: #b88212;">₱{{ number_format($period->total_net_pay, 2) }}</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>EMP #</th>
                    <th>Employee Name</th>
                    <th>Position</th>
                    <th class="num">Days</th>
                    <th class="num">Basic Pay (₱)</th>
                    <th class="num">OT Pay (₱)</th>
                    <th class="num">Incentives (₱)</th>
                    <th class="num">Gross Pay (₱)</th>
                    <th class="num">Gov Deduct (₱)</th>
                    <th class="num">Loans/CA (₱)</th>
                    <th class="num">Total Deduct (₱)</th>
                    <th class="num">Net Pay (₱)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($period->records as $rec)
                    <tr>
                        <td style="font-family: monospace; font-weight: 700;">{{ $rec->employee->employee_code }}</td>
                        <td><strong>{{ $rec->employee->full_name }}</strong></td>
                        <td>{{ $rec->employee->position }}</td>
                        <td class="num">{{ $rec->days_worked }}</td>
                        <td class="num">₱{{ number_format($rec->regular_pay, 2) }}</td>
                        <td class="num">₱{{ number_format($rec->ot_pay, 2) }}</td>
                        <td class="num" style="color: #16a34a;">₱{{ number_format($rec->incentives_total, 2) }}</td>
                        <td class="num"><strong>₱{{ number_format($rec->gross_pay, 2) }}</strong></td>
                        <td class="num">₱{{ number_format($rec->sss_deduction + $rec->philhealth_deduction + $rec->pagibig_deduction + $rec->tax_deduction, 2) }}</td>
                        <td class="num">₱{{ number_format($rec->loan_deduction + $rec->cash_advance_deduction, 2) }}</td>
                        <td class="num" style="color: #dc2626;">-₱{{ number_format($rec->total_deductions, 2) }}</td>
                        <td class="num"><strong style="color: #0f172a; font-size: 0.85rem;">₱{{ number_format($rec->net_pay, 2) }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4">PERIOD TOTALS</td>
                    <td class="num">₱{{ number_format($period->records->sum('regular_pay'), 2) }}</td>
                    <td class="num">₱{{ number_format($period->records->sum('ot_pay'), 2) }}</td>
                    <td class="num" style="color: #16a34a;">₱{{ number_format($period->total_incentives, 2) }}</td>
                    <td class="num">₱{{ number_format($period->total_gross_pay, 2) }}</td>
                    <td class="num" colspan="2"></td>
                    <td class="num" style="color: #dc2626;">-₱{{ number_format($period->total_deductions, 2) }}</td>
                    <td class="num" style="font-size: 0.95rem; color: #b88212;">₱{{ number_format($period->total_net_pay, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="signatures">
            <div class="sig-block">
                <div class="sig-line"></div>
                <div class="sig-lbl">Maria Santos</div>
                <div class="sig-sub">Prepared by / Cashier Desk</div>
            </div>
            <div class="sig-block">
                <div class="sig-line"></div>
                <div class="sig-lbl">Carlos Mendoza</div>
                <div class="sig-sub">Reviewed by / Clinic Manager</div>
            </div>
            <div class="sig-block">
                <div class="sig-line"></div>
                <div class="sig-lbl">Dr. Modesto Reyes</div>
                <div class="sig-sub">Approved by / Administrator & Owner</div>
            </div>
        </div>
    </div>
</body>
</html>
