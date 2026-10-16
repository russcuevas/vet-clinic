@extends('layouts.app')

@php
    $title = "13th Month Pay & SL Monetization ({$year})";
    $headerTitle = "13th Month Pay Bonus & Leave Monetization Desk ({$year})";
    $breadcrumb = "Payroll / 13th Month Pay";
@endphp

@section('content')
    <!-- Header with Actions -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--white); margin-bottom: 0.25rem;">
                🎁 13th Month Pay Bonus & Sick Leave (SL) Monetization ({{ $year }})
            </h2>
            <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0;">
                Philippine Labor Code (DOLE) formula: (Total Days Worked × Daily Rate) ÷ 12, plus cash conversion of unused Sick Leave credits.
            </p>
        </div>
        <div style="display: flex; gap: 0.6rem; align-items: center; flex-wrap: wrap;">
            <form method="GET" action="{{ route('manager.payroll.thirteenth_month.index') }}" style="display: flex; gap: 0.4rem; align-items: center;">
                <select name="year" class="form-control" onchange="this.form.submit()" style="padding: 0.4rem 0.8rem; font-weight: 600;">
                    @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>Year {{ $y }}</option>
                    @endfor
                </select>
            </form>

            <form action="{{ route('manager.payroll.thirteenth_month.generate') }}" method="POST" style="display: inline;">
                @csrf
                <input type="hidden" name="year" value="{{ $year }}">
                <button type="submit" class="btn btn-gold" onclick="return confirm('Generate / Recalculate 13th Month Pay and Monetized SL for all active employees for Year {{ $year }}?')">
                    ⚡ Generate All ({{ $year }})
                </button>
            </form>

            <a href="{{ route('manager.payroll.thirteenth_month.summary_print', $year) }}" target="_blank" class="btn btn-navy">
                🖨️ Print Summary Report
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success" style="margin-bottom: 1.5rem;">
            ✓ {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger" style="margin-bottom: 1.5rem;">
            ⚠️ {{ session('error') }}
        </div>
    @endif

    <!-- Annual Stat Cards -->
    <div class="stat-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 1.5rem;">
        <div class="stat-card">
            <span class="stat-title">13th Month Pay Pool</span>
            <div class="stat-value" style="color: var(--gold-light);">₱{{ number_format($total13thPool, 2) }}</div>
            <div class="stat-desc">Formula: Total 12-Mo Earnings / 12</div>
        </div>
        <div class="stat-card">
            <span class="stat-title">Monetized Sick Leave (SL)</span>
            <div class="stat-value" style="color: #38bdf8;">₱{{ number_format($totalSlPool, 2) }}</div>
            <div class="stat-desc">Cash conversion of unused SL</div>
        </div>
        <div class="stat-card">
            <span class="stat-title">Total Year-End Payout</span>
            <div class="stat-value" style="color: #10b981;">₱{{ number_format($totalNetPayout, 2) }}</div>
            <div class="stat-desc">13th Month + Monetized SL</div>
        </div>
        <div class="stat-card">
            <span class="stat-title">Active Clinic Staff</span>
            <div class="stat-value" style="color: var(--white);">{{ count($staffList) }}</div>
            <div class="stat-desc">Eligible employees in {{ $year }}</div>
        </div>
    </div>

    <!-- Formula Guide Banner -->
    <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid var(--navy-border); border-left: 4px solid var(--gold); border-radius: 8px; padding: 1rem 1.25rem; margin-bottom: 1.5rem;">
        <div style="font-weight: 700; color: var(--gold-light); font-size: 0.85rem; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.5rem;">
            <span>📐 Approved Client Computation Formula</span>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 0.75rem; font-size: 0.78rem; color: #cbd5e1;">
            <div>
                <strong>1. Monthly Bonus Computation:</strong><br>
                <code style="color: #38bdf8;">Days Worked/Present in Month × Daily Rate</code>
            </div>
            <div>
                <strong>2. 13th Month Bonus Pay:</strong><br>
                <code style="color: #38bdf8;">Total 12-Month Bonus ÷ 12</code>
            </div>
            <div>
                <strong>3. Monetized Sick Leave (SL):</strong><br>
                <code style="color: #38bdf8;">Unused SL Credits (out of 5) × Daily Rate</code>
            </div>
            <div>
                <strong>4. Year-End Net Disbursed:</strong><br>
                <code style="color: #10b981;">13th Month Pay + Monetized SL</code>
            </div>
        </div>
    </div>

    <!-- Staff Register Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-title-group">
                <h3 class="card-title">Staff 13th Month & Monetized SL Register</h3>
                <span class="card-subtitle">Detailed breakdown, days worked per month, unused SL credits, and disbursement status</span>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>EMP #</th>
                            <th>Staff Member</th>
                            <th>Position & Rate</th>
                            <th>Total Basic (₱)</th>
                            <th>13th Month Pay (₱)</th>
                            <th>Monetized SL (₱)</th>
                            <th>Total Payout (₱)</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staffList as $item)
                            @php
                                $emp = $item['employee'];
                                $rec = $item['record'];
                                $isSaved = $item['is_saved'];
                            @endphp
                            <tr>
                                <td>
                                    <span style="font-family: monospace; font-weight: 700; color: var(--gold-light);">
                                        {{ $emp->employee_code }}
                                    </span>
                                </td>
                                <td>
                                    <strong style="color: var(--white);">{{ $emp->full_name }}</strong>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">
                                        Hired: {{ $emp->date_hired ? $emp->date_hired->format('M d, Y') : 'N/A' }}
                                    </div>
                                </td>
                                <td>
                                    <div>{{ $emp->position }}</div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">
                                        ₱{{ number_format($rec->daily_rate, 2) }}/day (₱{{ number_format($emp->basic_salary, 2) }}/mo)
                                    </div>
                                </td>
                                <td>
                                    <strong style="color: var(--white);">₱{{ number_format($rec->total_basic_earned, 2) }}</strong>
                                    <div style="font-size: 0.68rem; color: var(--text-muted);">Sum of 12 months</div>
                                </td>
                                <td>
                                    <strong style="color: var(--gold-light); font-size: 0.95rem;">
                                        ₱{{ number_format($rec->thirteenth_month_pay, 2) }}
                                    </strong>
                                    <div style="font-size: 0.68rem; color: var(--text-muted);">Basic ÷ 12</div>
                                </td>
                                <td>
                                    <strong style="color: #38bdf8;">+₱{{ number_format($rec->sl_monetized_amount, 2) }}</strong>
                                    <div style="font-size: 0.68rem; color: var(--text-muted);">
                                        {{ floatval($rec->sl_days_remaining) }} unused SL day(s)
                                    </div>
                                </td>
                                <td>
                                    <strong style="color: #10b981; font-size: 1.05rem;">
                                        ₱{{ number_format($rec->total_payout, 2) }}
                                    </strong>
                                </td>
                                <td>
                                    @if($isSaved)
                                        <span class="badge {{ $rec->payment_status === 'paid' ? 'badge-success' : 'badge-warning' }}">
                                            {{ ucfirst($rec->payment_status) }}
                                        </span>
                                    @else
                                        <span class="badge badge-navy" style="font-size: 0.68rem;">Preview (Not Saved)</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.35rem;">
                                        @if($isSaved)
                                            <a href="{{ route('manager.payroll.thirteenth_month.payslip', $rec) }}" target="_blank"
                                                class="btn btn-navy btn-sm" style="padding: 0.25rem 0.5rem;" title="Print 13th Month Payslip">
                                                📄 Payslip
                                            </a>
                                            <button type="button" class="btn btn-ghost btn-sm" style="padding: 0.25rem 0.5rem;"
                                                data-modal-target="modal-edit-13th-{{ $rec->id }}" title="Option Edit">
                                                ✏️ Edit
                                            </button>
                                        @else
                                            <form action="{{ route('manager.payroll.thirteenth_month.generate') }}" method="POST" style="display: inline;">
                                                @csrf
                                                <input type="hidden" name="year" value="{{ $year }}">
                                                <input type="hidden" name="employee_id" value="{{ $emp->id }}">
                                                <button type="submit" class="btn btn-gold btn-sm" style="padding: 0.25rem 0.5rem;" title="Save & Generate">
                                                    💾 Save
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                                    No active employees found for {{ $year }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Modals (Option Edit) -->
    @foreach($staffList as $item)
        @if($item['is_saved'])
            @php
                $rec = $item['record'];
                $emp = $item['employee'];
                $breakdown = $rec->monthly_breakdown ?? [];
            @endphp
            <div class="modal-backdrop" id="modal-edit-13th-{{ $rec->id }}">
                <div class="modal-dialog modal-lg" style="max-width: 780px; width: 95%;">
                    <div class="modal-header">
                        <div class="modal-title-group">
                            <h4 class="modal-title">Edit 13th Month Pay & SL Monetization</h4>
                            <span style="font-size: 0.75rem; color: var(--gold-light);">
                                {{ $emp->full_name }} ({{ $emp->employee_code }}) • {{ $emp->position }} • Year {{ $rec->year }}
                            </span>
                        </div>
                        <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
                    </div>
                    <form action="{{ route('manager.payroll.thirteenth_month.update', $rec) }}" method="POST" id="form-13th-{{ $rec->id }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-body" style="padding: 1.5rem; max-height: 80vh; overflow-y: auto;">
                            <div style="font-size: 0.76rem; color: #cbd5e1; background: rgba(0,0,0,0.25); border-left: 3px solid #38bdf8; padding: 0.65rem 0.85rem; border-radius: 4px; margin-bottom: 1rem;">
                                💡 <strong>Live Recalculation:</strong> Ang pag-edit sa days worked ng bawat buwan o daily rate ay awtomatikong magko-compute ng <code>Month Bonus</code>, <code>13th Month Pay (/12)</code>, <code>Monetized SL</code>, at <code>Total Year-End Payout</code>.
                            </div>

                            <!-- Daily Rate & Base Salary -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                                <div class="form-group">
                                    <label class="form-label">Daily Rate (₱) *</label>
                                    <input type="number" step="0.01" min="0" name="daily_rate" class="form-control"
                                        value="{{ $rec->daily_rate }}" required style="font-weight: 700;">
                                    <span style="font-size: 0.68rem; color: var(--text-muted);">Used for (Days × Rate) & SL Monetization</span>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Basic Monthly Salary (Reference)</label>
                                    <input type="text" class="form-control" value="₱{{ number_format($emp->basic_salary, 2) }}" readonly style="opacity: 0.7;">
                                </div>
                            </div>

                            <!-- 12-Month Breakdown Grid (Jan to Dec) -->
                            <div style="background: rgba(11, 25, 44, 0.4); border: 1px solid var(--navy-border); border-radius: 8px; padding: 0.85rem; margin-bottom: 1.25rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.65rem;">
                                    <span style="font-size: 0.8rem; color: var(--gold-light); font-weight: 700; text-transform: uppercase;">
                                        📅 Monthly Days Worked & Bonus Breakdown (Jan–Dec {{ $rec->year }})
                                    </span>
                                    <span style="font-size: 0.7rem; color: var(--text-muted);">Formula: Days × Rate = Month Bonus</span>
                                </div>

                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 0.65rem;">
                                    @for($m = 1; $m <= 12; $m++)
                                        @php
                                            $mMonth = Carbon::createFromDate($rec->year, $m, 1);
                                            $mName = $mMonth->format('M');
                                            $itemData = $breakdown[$m - 1] ?? null;
                                            $daysVal = isset($itemData['days_worked']) ? floatval($itemData['days_worked']) : 22;
                                            $amountVal = isset($itemData['amount']) ? floatval($itemData['amount']) : round($daysVal * floatval($rec->daily_rate), 2);
                                        @endphp
                                        <div style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.08); border-radius: 6px; padding: 0.5rem;">
                                            <div style="display: flex; justify-content: space-between; font-size: 0.72rem; font-weight: 700; color: var(--white); margin-bottom: 3px;">
                                                <span>{{ $mName }}</span>
                                                <span class="m-amt-label-{{ $m }}" style="color: #38bdf8; font-size: 0.68rem;">₱{{ number_format($amountVal, 2) }}</span>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 0.35rem;">
                                                <input type="number" step="0.5" min="0" max="31"
                                                    name="monthly_days[{{ $m }}]"
                                                    class="form-control form-control-sm m-day-input"
                                                    data-month="{{ $m }}"
                                                    value="{{ $daysVal }}"
                                                    style="padding: 0.25rem 0.4rem; font-size: 0.8rem; text-align: center;">
                                                <span style="font-size: 0.68rem; color: var(--text-muted);">days</span>
                                            </div>
                                        </div>
                                    @endfor
                                </div>

                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.85rem; padding-top: 0.65rem; border-top: 1px solid rgba(255,255,255,0.1);">
                                    <span style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted);">Total Basic Earned (Sum of 12 Mos):</span>
                                    <span class="lbl-total-basic" style="font-size: 0.95rem; font-weight: 800; color: var(--white);">
                                        ₱{{ number_format($rec->total_basic_earned, 2) }}
                                    </span>
                                </div>
                            </div>

                            <!-- 13th Month Pay & Monetized SL Grid -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                                <div class="form-group">
                                    <label class="form-label" style="font-weight: 700; color: var(--gold-light);">
                                        13th Month Bonus Pay (₱) *
                                    </label>
                                    <input type="number" step="0.01" min="0" name="thirteenth_month_pay" class="form-control"
                                        value="{{ $rec->thirteenth_month_pay }}" required style="font-weight: 700; color: var(--gold-light); font-size: 1.05rem;">
                                    <span style="font-size: 0.68rem; color: var(--text-muted);">= Total Basic Earned ÷ 12 (or manual override)</span>
                                </div>

                                <!-- Monetized Sick Leave -->
                                <div class="form-group">
                                    <label class="form-label" style="font-weight: 700; color: #38bdf8;">
                                        Monetized Sick Leave (SL) Amount (₱) *
                                    </label>
                                    <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 0.5rem;">
                                        <div>
                                            <input type="number" step="0.5" min="0" max="30" name="sl_days_remaining"
                                                class="form-control input-sl-days" value="{{ $rec->sl_days_remaining }}" placeholder="Days" title="Unused SL Days">
                                            <span style="font-size: 0.65rem; color: var(--text-muted);">Unused SL Days</span>
                                        </div>
                                        <div>
                                            <input type="number" step="0.01" min="0" name="sl_monetized_amount"
                                                class="form-control input-sl-amount" value="{{ $rec->sl_monetized_amount }}" style="font-weight: 700; color: #38bdf8;">
                                            <span style="font-size: 0.65rem; color: var(--text-muted);">= SL Days × Daily Rate</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Adjustments & Total Payout -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1.4fr; gap: 0.75rem; margin-bottom: 1rem;">
                                <div class="form-group">
                                    <label class="form-label">Other Bonus (₱)</label>
                                    <input type="number" step="0.01" min="0" name="other_bonus" class="form-control input-other-bonus" value="{{ $rec->other_bonus }}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Deductions (₱)</label>
                                    <input type="number" step="0.01" min="0" name="deductions" class="form-control input-deductions" value="{{ $rec->deductions }}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label" style="font-weight: 800; color: #10b981;">
                                        Total Year-End Payout (₱) *
                                    </label>
                                    <input type="number" step="0.01" min="0" name="total_payout" class="form-control input-total-payout"
                                        value="{{ $rec->total_payout }}" required
                                        style="font-weight: 800; color: #10b981; font-size: 1.1rem; background: rgba(16, 185, 129, 0.1); border-color: #10b981;">
                                    <span style="font-size: 0.66rem; color: #34d399;">= 13th Month + Monetized SL + Other</span>
                                </div>
                            </div>

                            <!-- Payment Status & Notes -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1.5fr; gap: 0.75rem;">
                                <div class="form-group">
                                    <label class="form-label">Payment Status *</label>
                                    <select name="payment_status" class="form-control" required>
                                        <option value="unpaid" {{ $rec->payment_status === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                        <option value="paid" {{ $rec->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Target Payout Date</label>
                                    <input type="date" name="payout_date" class="form-control" value="{{ $rec->payout_date ? $rec->payout_date->format('Y-m-d') : '' }}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Notes / Remarks</label>
                                    <input type="text" name="notes" class="form-control" value="{{ $rec->notes }}" placeholder="e.g. SL Monetized based on 5 remaining days">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer" style="padding: 1rem 1.5rem; background: rgba(0,0,0,0.2);">
                            <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                            <button type="submit" class="btn btn-gold">Save Overrides</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endforeach
@endsection

@push('scripts')
<script>
    (function () {
        function recalculate13thModal(form, sourceInput) {
            if (!form) return;

            const rate = parseFloat(form.querySelector('input[name="daily_rate"]')?.value || 0) || 0;

            let totalBasicEarned = 0;
            for (let m = 1; m <= 12; m++) {
                const dayInput = form.querySelector(`input[name="monthly_days[${m}]"]`);
                const days = parseFloat(dayInput?.value || 0) || 0;
                const monthAmt = Math.round((days * rate) * 100) / 100;
                totalBasicEarned += monthAmt;

                const lblAmt = form.querySelector(`.m-amt-label-${m}`);
                if (lblAmt) {
                    lblAmt.textContent = '₱' + monthAmt.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
            }

            const lblTotalBasic = form.querySelector('.lbl-total-basic');
            if (lblTotalBasic) {
                lblTotalBasic.textContent = '₱' + totalBasicEarned.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            const calc13th = Math.round((totalBasicEarned / 12) * 100) / 100;
            const input13th = form.querySelector('input[name="thirteenth_month_pay"]');
            if (input13th && sourceInput !== input13th) {
                input13th.value = calc13th.toFixed(2);
            }
            const active13th = parseFloat(input13th?.value || calc13th) || 0;

            const slDaysInput = form.querySelector('input[name="sl_days_remaining"]');
            const slDays = parseFloat(slDaysInput?.value || 0) || 0;
            const calcSlAmt = Math.round((slDays * rate) * 100) / 100;
            const slAmtInput = form.querySelector('input[name="sl_monetized_amount"]');

            if (slAmtInput && sourceInput !== slAmtInput) {
                slAmtInput.value = calcSlAmt.toFixed(2);
            }
            const activeSlAmt = parseFloat(slAmtInput?.value || calcSlAmt) || 0;

            const otherBonus = parseFloat(form.querySelector('input[name="other_bonus"]')?.value || 0) || 0;
            const deductions = parseFloat(form.querySelector('input[name="deductions"]')?.value || 0) || 0;

            const calcTotalPayout = Math.max(0, Math.round((active13th + activeSlAmt + otherBonus - deductions) * 100) / 100);
            const totalPayoutInput = form.querySelector('input[name="total_payout"]');

            if (totalPayoutInput && sourceInput !== totalPayoutInput) {
                totalPayoutInput.value = calcTotalPayout.toFixed(2);
            }
        }

        document.addEventListener('input', function (e) {
            const form = e.target.closest('form[id^="form-13th-"]');
            if (form) {
                recalculate13thModal(form, e.target);
            }
        });

        document.addEventListener('change', function (e) {
            const form = e.target.closest('form[id^="form-13th-"]');
            if (form) {
                recalculate13thModal(form, e.target);
            }
        });

        document.addEventListener('submit', function (e) {
            const form = e.target.closest('form[id^="form-13th-"]');
            if (form) {
                recalculate13thModal(form, null);
            }
        });
    })();
</script>
@endpush
