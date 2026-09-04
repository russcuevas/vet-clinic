@extends('layouts.app')

@php
    $title = 'Role Incentives & Commissions';
    $headerTitle = 'Dynamic & Editable Incentive Schemes';
    $breadcrumb = 'Payroll / Incentives';
@endphp

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--white); margin-bottom: 0.25rem;">Role-Based Incentive Management</h2>
            <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0;">
                Configurable commission schemes for Groomers, Vets, and Janitors. All percentages, unit rates, and thresholds are <strong>100% editable</strong>.
            </p>
        </div>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <button type="button" class="btn btn-navy" data-modal-target="modal-add-rule">
                <span>⚙️ New Scheme Rule</span>
            </button>
            <button type="button" class="btn btn-gold" data-modal-target="modal-add-employee-incentive">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>+ Award / Record Incentive</span>
            </button>
        </div>
    </div>

    <!-- Active Incentive Schemes / Rules (Editable Cards) -->
    <div style="margin-bottom: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
            <h3 style="font-size: 1rem; font-weight: 700; color: var(--gold-light); margin: 0;">Active Role Schemes & Threshold Rules</h3>
            <span style="font-size: 0.75rem; color: var(--text-muted);">Click ✏️ Edit on any card to adjust default % or thresholds</span>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.25rem;">
            @forelse($rules as $rule)
                <div class="card" style="border: 1px solid {{ str_contains(strtolower($rule->role_name), 'groom') ? 'rgba(245, 186, 49, 0.4)' : (str_contains(strtolower($rule->role_name), 'vet') ? 'rgba(59, 130, 246, 0.4)' : 'var(--navy-border)') }};">
                    <div class="card-header" style="padding: 1rem 1.25rem;">
                        <div>
                            <span class="badge {{ str_contains(strtolower($rule->role_name), 'groom') ? 'badge-gold' : 'badge-navy' }}" style="font-size: 0.68rem; margin-bottom: 0.25rem;">
                                {{ ucfirst($rule->role_name) }}
                            </span>
                            <h4 style="font-size: 1rem; font-weight: 700; color: var(--white); margin: 0;">{{ $rule->title }}</h4>
                        </div>
                        <button type="button" class="btn btn-ghost btn-sm" style="color: var(--gold-light);"
                            data-modal-target="modal-edit-rule-{{ $rule->id }}" title="Edit Rule & Percentage">
                            ✏️ Edit
                        </button>
                    </div>
                    <div class="card-body" style="padding: 1rem 1.25rem;">
                        <p style="font-size: 0.78rem; color: var(--text-secondary); margin-bottom: 1rem;">
                            {{ $rule->description ?: 'Configured incentive formula for clinic staff.' }}
                        </p>

                        <div style="background: rgba(0, 0, 0, 0.3); border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 0.5rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                                <span style="font-size: 0.75rem; color: var(--text-muted);">Base Rate:</span>
                                <strong style="font-size: 0.95rem; color: var(--gold-light);">
                                    @if($rule->scheme_type === 'percentage')
                                        {{ $rule->default_rate }}% commission
                                    @else
                                        ₱{{ number_format($rule->default_rate, 2) }} / {{ $rule->unit_label }}
                                    @endif
                                </strong>
                            </div>

                            @if($rule->threshold_count > 0 && $rule->tier2_rate)
                                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px dashed rgba(255, 255, 255, 0.1); padding-top: 0.35rem; margin-top: 0.35rem;">
                                    <span style="font-size: 0.75rem; color: #10b981;">Bonus Tier (≥ {{ $rule->threshold_count }} {{ $rule->unit_label }}/mo):</span>
                                    <strong style="font-size: 0.95rem; color: #10b981;">
                                        {{ $rule->tier2_rate }}% commission
                                    </strong>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Edit Rule Modal -->
                <div class="modal-backdrop" id="modal-edit-rule-{{ $rule->id }}">
                    <div class="modal-dialog" style="max-width: 520px;">
                        <div class="modal-header">
                            <div class="modal-title-group">
                                <h4 class="modal-title">Edit Scheme: {{ $rule->title }}</h4>
                                <span style="font-size: 0.72rem; color: var(--gold-light);">Target Role: {{ ucfirst($rule->role_name) }}</span>
                            </div>
                            <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
                        </div>
                        <form action="{{ route('admin.payroll.incentives.rules.update', $rule) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-body" style="padding: 1.5rem;">
                                <div class="form-group">
                                    <label class="form-label">Scheme Title *</label>
                                    <input type="text" name="title" class="form-control" value="{{ $rule->title }}" required>
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                                    <div class="form-group">
                                        <label class="form-label">Scheme Type *</label>
                                        <select name="scheme_type" class="form-control" required>
                                            <option value="percentage" {{ $rule->scheme_type === 'percentage' ? 'selected' : '' }}>Percentage (%) of Sales</option>
                                            <option value="fixed_per_unit" {{ $rule->scheme_type === 'fixed_per_unit' ? 'selected' : '' }}>Fixed ₱ Per Unit / Day</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Base Rate / % *</label>
                                        <input type="number" step="0.01" name="default_rate" class="form-control" value="{{ $rule->default_rate }}" required>
                                    </div>
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                                    <div class="form-group">
                                        <label class="form-label">Threshold Count (e.g. 100 pets)</label>
                                        <input type="number" name="threshold_count" class="form-control" value="{{ $rule->threshold_count }}" placeholder="0 if none">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Tier 2 Rate (e.g. 20% if reached)</label>
                                        <input type="number" step="0.01" name="tier2_rate" class="form-control" value="{{ $rule->tier2_rate }}" placeholder="Optional">
                                    </div>
                                </div>

                                <div class="form-group" style="margin-top: 0.75rem;">
                                    <label class="form-label">Unit Label (e.g. pets, consultations, days)</label>
                                    <input type="text" name="unit_label" class="form-control" value="{{ $rule->unit_label }}" required>
                                </div>

                                <div class="form-group" style="margin-top: 0.75rem;">
                                    <label class="form-label">Description / Rules Explanation</label>
                                    <textarea name="description" class="form-control" rows="2">{{ $rule->description }}</textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                                <button type="submit" class="btn btn-gold">Update Scheme Rules</button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 2rem;">
                    No incentive schemes configured yet. Click "⚙️ New Scheme Rule" to add one!
                </div>
            @endforelse
        </div>
    </div>

    <!-- Monthly Filter & Total Paid Bar -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body" style="padding: 1rem 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <form method="GET" action="{{ route('admin.payroll.incentives.index') }}" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
                <div>
                    <select name="month" class="form-control">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div>
                    <select name="year" class="form-control">
                        @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <select name="employee_id" class="form-control">
                        <option value="">-- All Staff --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ $employeeId == $emp->id ? 'selected' : '' }}>
                                {{ $emp->full_name }} ({{ $emp->position }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-navy btn-sm">Filter</button>
            </form>

            <div>
                <span style="font-size: 0.8rem; color: var(--text-muted); margin-right: 0.5rem;">Total Incentives for Period:</span>
                <span style="font-size: 1.25rem; font-weight: 800; color: #10b981;">₱{{ number_format($totalIncentivesPaid, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Recorded Incentives Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-title-group">
                <h3 class="card-title">Employee Incentive Records</h3>
                <span class="card-subtitle">Logged incentives automatically credited during payroll generation</span>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table" id="incentives-table">
                    <thead>
                        <tr>
                            <th>Date Earned</th>
                            <th>Staff Member</th>
                            <th>Role</th>
                            <th>Incentive Title</th>
                            <th>Rate Applied</th>
                            <th>Base Count / Sales</th>
                            <th>Total Incentive (₱)</th>
                            <th>Notes</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($incentives as $inc)
                            <tr>
                                <td>{{ $inc->date_earned->format('M d, Y') }}</td>
                                <td><strong style="color: var(--white);">{{ $inc->employee->full_name }}</strong></td>
                                <td><span class="badge badge-navy" style="font-size: 0.7rem;">{{ $inc->employee->position }}</span></td>
                                <td>{{ $inc->title }}</td>
                                <td>
                                    @if($inc->calculation_basis === 'percentage')
                                        <span class="badge badge-gold" style="font-size: 0.72rem;">{{ $inc->rate_applied }}%</span>
                                    @else
                                        <span class="badge badge-blue" style="font-size: 0.72rem;">₱{{ number_format($inc->rate_applied, 2) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($inc->calculation_basis === 'percentage')
                                        ₱{{ number_format($inc->base_amount_or_count, 2) }}
                                    @else
                                        {{ number_format($inc->base_amount_or_count, 0) }} units
                                    @endif
                                </td>
                                <td>
                                    <strong style="color: #10b981; font-size: 0.95rem;">+₱{{ number_format($inc->total_incentive, 2) }}</strong>
                                </td>
                                <td style="font-size: 0.75rem; color: var(--text-muted);">{{ $inc->notes ?: '-' }}</td>
                                <td>
                                    <form action="{{ route('admin.payroll.incentives.destroy', $inc) }}" method="POST"
                                        onsubmit="return confirm('Delete this incentive entry?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-sm" style="padding: 0.25rem 0.5rem; color: #ef4444;" title="Delete">
                                            🗑️
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($incentives->hasPages())
                <div style="padding: 1rem;">
                    {{ $incentives->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal: Record Employee Incentive (With Interactive % Calculator & Auto-Detect) -->
    <div class="modal-backdrop" id="modal-add-employee-incentive">
        <div class="modal-dialog" style="max-width: 600px;">
            <div class="modal-header">
                <div class="modal-title-group">
                    <h4 class="modal-title">Record / Award Employee Incentive</h4>
                    <span style="font-size: 0.72rem; color: var(--gold-light);">Editable rate % and base amount with clinic auto-detection</span>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form action="{{ route('admin.payroll.incentives.records.store') }}" method="POST" id="incentiveForm">
                @csrf
                <div class="modal-body" style="padding: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label">Employee *</label>
                        <select name="employee_id" id="inc_employee_id" class="form-control" required onchange="fetchClinicStats()">
                            <option value="">-- Select Staff Member --</option>
                            @php
                                $groomersList = $employees->filter(fn($e) => str_contains(strtolower($e->position), 'groom'));
                                $vetsList = $employees->filter(fn($e) => str_contains(strtolower($e->position), 'vet'));
                                $janitorsList = $employees->filter(fn($e) => str_contains(strtolower($e->position), 'janitor') || str_contains(strtolower($e->position), 'kennel'));
                                $othersList = $employees->reject(fn($e) => str_contains(strtolower($e->position), 'groom') || str_contains(strtolower($e->position), 'vet') || str_contains(strtolower($e->position), 'janitor') || str_contains(strtolower($e->position), 'kennel'));
                            @endphp

                            @if($groomersList->isNotEmpty())
                                <optgroup label="✂️ Pet Groomers (Grooming Commission: 10% / 20%)">
                                    @foreach($groomersList as $emp)
                                        <option value="{{ $emp->id }}" data-position="{{ strtolower($emp->position) }}">
                                            {{ $emp->employee_code }} - {{ $emp->full_name }} ({{ $emp->position }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif

                            @if($vetsList->isNotEmpty())
                                <optgroup label="🩺 Veterinarians / Doctors (Consultation Incentive: ₱200/case)">
                                    @foreach($vetsList as $emp)
                                        <option value="{{ $emp->id }}" data-position="{{ strtolower($emp->position) }}">
                                            {{ $emp->employee_code }} - {{ $emp->full_name }} ({{ $emp->position }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif

                            @if($janitorsList->isNotEmpty())
                                <optgroup label="🧹 Janitor & Kennel Care (Boarding Incentive: ₱35/day)">
                                    @foreach($janitorsList as $emp)
                                        <option value="{{ $emp->id }}" data-position="{{ strtolower($emp->position) }}">
                                            {{ $emp->employee_code }} - {{ $emp->full_name }} ({{ $emp->position }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif

                            @if($othersList->isNotEmpty())
                                <optgroup label="💼 Administrative & Other Staff">
                                    @foreach($othersList as $emp)
                                        <option value="{{ $emp->id }}" data-position="{{ strtolower($emp->position) }}">
                                            {{ $emp->employee_code }} - {{ $emp->full_name }} ({{ $emp->position }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                    </div>

                    <!-- Auto-detect helper bar -->
                    <div id="autoDetectBar" style="display: flex; justify-content: space-between; align-items: center; background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); padding: 0.6rem 0.85rem; border-radius: 6px; margin: 0.75rem 0;">
                        <span id="autoDetectStatus" style="font-size: 0.75rem; color: #38bdf8;">
                            Select an employee to check completed monthly pet grooms or vet consults
                        </span>
                        <button type="button" class="btn btn-navy btn-sm" style="font-size: 0.7rem; padding: 0.25rem 0.6rem;" onclick="fetchClinicStats()">
                            🔄 Check Records
                        </button>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Incentive Scheme / Title *</label>
                            <input type="text" name="title" id="inc_title" class="form-control" value="Performance Incentive" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Calculation Basis *</label>
                            <select name="calculation_basis" id="inc_basis" class="form-control" required onchange="calculateIncentiveTotal()">
                                <option value="percentage">Percentage (%) of Pet Grooming Sales</option>
                                <option value="fixed_per_unit">Fixed ₱ per Consult / Pet Day</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label" id="lbl_rate">Applied Percentage (%) *</label>
                            <input type="number" step="0.01" name="rate_applied" id="inc_rate" class="form-control" value="10.00" required oninput="calculateIncentiveTotal()">
                            <span style="font-size: 0.68rem; color: var(--gold-light);">Editable: Change to 10%, 20%, or any % you desire</span>
                        </div>
                        <div class="form-group">
                            <label class="form-label" id="lbl_base">Base Sales or Count *</label>
                            <input type="number" step="0.01" name="base_amount_or_count" id="inc_base" class="form-control" value="0.00" required oninput="calculateIncentiveTotal()">
                            <span style="font-size: 0.68rem; color: var(--text-muted);">Total sales amount or # of pets</span>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Final Incentive Amount (₱) *</label>
                            <input type="number" step="0.01" name="total_incentive" id="inc_total" class="form-control" value="0.00" required
                                style="font-size: 1.1rem; font-weight: 800; color: #10b981; background: rgba(16, 185, 129, 0.08); border-color: rgba(16, 185, 129, 0.4);">
                            <span style="font-size: 0.68rem; color: var(--text-muted);">Auto-calculated, or directly type custom override</span>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Date Earned *</label>
                            <input type="date" name="date_earned" id="inc_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 0.75rem;">
                        <label class="form-label">Notes / Justification</label>
                        <input type="text" name="notes" id="inc_notes" class="form-control" placeholder="e.g. Reached 105 pet grooms in March (20% bonus tier)">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold">Record Incentive</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Add New Scheme Rule -->
    <div class="modal-backdrop" id="modal-add-rule">
        <div class="modal-dialog" style="max-width: 520px;">
            <div class="modal-header">
                <div class="modal-title-group">
                    <h4 class="modal-title">Create New Incentive Scheme</h4>
                    <span style="font-size: 0.72rem; color: var(--gold-light);">Define commission rates & threshold tiers for roles</span>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form action="{{ route('admin.payroll.incentives.rules.store') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding: 1.5rem;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Role Name *</label>
                            <input type="text" name="role_name" class="form-control" placeholder="e.g. Groomer, Vet, Janitor" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Scheme Title *</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Pet Grooming Commission" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Calculation Type *</label>
                            <select name="scheme_type" class="form-control" required>
                                <option value="percentage">Percentage (%) of Total Sales</option>
                                <option value="fixed_per_unit">Fixed ₱ per Unit / Day</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Default Rate / % *</label>
                            <input type="number" step="0.01" name="default_rate" class="form-control" placeholder="e.g. 10 for 10%" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Threshold Count (e.g. 100 pets)</label>
                            <input type="number" name="threshold_count" class="form-control" value="0" placeholder="0 if no tier">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tier 2 Rate (e.g. 20% if reached)</label>
                            <input type="number" step="0.01" name="tier2_rate" class="form-control" placeholder="Optional">
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 0.75rem;">
                        <label class="form-label">Unit Label *</label>
                        <input type="text" name="unit_label" class="form-control" value="pets" placeholder="e.g. pets, consultations, boarding days" required>
                    </div>

                    <div class="form-group" style="margin-top: 0.75rem;">
                        <label class="form-label">Description / Guidelines</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="e.g. 10% base grooming commission, jumps to 20% if >= 100 pets in month."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-navy">Save Scheme Rule</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Client-side Interactive Calculator Script -->
    <script>
        function calculateIncentiveTotal() {
            const basis = document.getElementById('inc_basis').value;
            const rate = parseFloat(document.getElementById('inc_rate').value) || 0;
            const base = parseFloat(document.getElementById('inc_base').value) || 0;
            const totalInput = document.getElementById('inc_total');
            const lblRate = document.getElementById('lbl_rate');
            const lblBase = document.getElementById('lbl_base');

            if (basis === 'percentage') {
                lblRate.textContent = 'Applied Percentage (%) *';
                lblBase.textContent = 'Base Sales Amount (₱) *';
                totalInput.value = ((base * rate) / 100).toFixed(2);
            } else {
                lblRate.textContent = 'Rate Per Unit (₱) *';
                lblBase.textContent = 'Count of Units / Days *';
                totalInput.value = (base * rate).toFixed(2);
            }
        }

        async function fetchClinicStats() {
            const empSelect = document.getElementById('inc_employee_id');
            const empId = empSelect.value;
            const dateVal = document.getElementById('inc_date').value;
            const statusBox = document.getElementById('autoDetectStatus');

            if (!empId) {
                statusBox.textContent = 'Select an employee first.';
                return;
            }

            statusBox.textContent = 'Checking clinic records...';

            const d = new Date(dateVal || new Date());
            const month = d.getMonth() + 1;
            const year = d.getFullYear();

            try {
                const response = await fetch(`{{ route('admin.payroll.incentives.suggest') }}?employee_id=${empId}&month=${month}&year=${year}`);
                const data = await response.json();

                if (data.success) {
                    const pos = data.position.toLowerCase();
                    document.getElementById('inc_basis').value = data.basis;

                    if (pos.includes('groom')) {
                        document.getElementById('inc_title').value = 'Grooming Commission';
                        document.getElementById('inc_rate').value = data.suggested_rate;
                        document.getElementById('inc_base').value = data.total_sales > 0 ? data.total_sales : 0;
                        document.getElementById('inc_notes').value = `Individual: ${data.count} pets (₱${Number(data.total_sales).toLocaleString(undefined, {minimumFractionDigits: 2})}) | Groomers Total: ${data.team_count || data.count}/${data.threshold || 100} pets (${data.suggested_rate}%)`;
                        if (data.count > 0 || (data.team_count && data.team_count > 0)) {
                            statusBox.innerHTML = `🐾 Individual Output: <strong>${data.count}</strong> pets (Sales: ₱<strong>${Number(data.total_sales).toLocaleString(undefined, {minimumFractionDigits: 2})}</strong>) &bull; 👥 <strong>Groomers Team Total: ${data.team_count} / ${data.threshold} pets</strong> &rarr; Rate: <strong>${data.suggested_rate}%</strong> (${data.tier_info})`;
                        } else {
                            statusBox.innerHTML = `🐾 Found <strong>0</strong> pets groomed for this groomer. Groomers Team Total: <strong>${data.team_count || 0}</strong> pets.`;
                        }
                    } else if (pos.includes('vet')) {
                        document.getElementById('inc_title').value = 'Veterinary Consultation Incentive';
                        document.getElementById('inc_rate').value = data.suggested_rate; // 200
                        document.getElementById('inc_base').value = data.count;
                        document.getElementById('inc_notes').value = `Consultations recorded: ${data.count} @ ₱${data.suggested_rate}/case`;
                        if (data.count > 0) {
                            statusBox.innerHTML = `🩺 Found <strong>${data.count}</strong> consultations. Rate: <strong>₱${data.suggested_rate}</strong>/consultation (₱${(data.count * data.suggested_rate).toLocaleString(undefined, {minimumFractionDigits: 2})})`;
                        } else {
                            statusBox.innerHTML = `🩺 Found <strong>0</strong> consultations in month. No clinical records logged for this doctor.`;
                        }
                    } else if (pos.includes('janitor') || pos.includes('kennel') || pos.includes('utility')) {
                        document.getElementById('inc_title').value = 'Pet Boarding Care Incentive';
                        document.getElementById('inc_rate').value = data.suggested_rate; // 35
                        document.getElementById('inc_base').value = data.count;
                        document.getElementById('inc_notes').value = `Boarding days: ${data.count} days @ ₱${data.suggested_rate}/day`;
                        if (data.count > 0) {
                            statusBox.innerHTML = `🧹 Found <strong>${data.count}</strong> active duty days in DTR. Rate: <strong>₱${data.suggested_rate}</strong>/day`;
                        } else {
                            statusBox.innerHTML = `🧹 Found <strong>0</strong> active duty days in DTR for this month.`;
                        }
                    } else {
                        document.getElementById('inc_rate').value = data.suggested_rate;
                        document.getElementById('inc_base').value = 0;
                        statusBox.textContent = `Staff Role: ${data.position}`;
                    }

                    calculateIncentiveTotal();
                }
            } catch (err) {
                statusBox.textContent = 'Could not auto-fetch records. You can manually type the percentage and count.';
            }
        }
    </script>
@endsection
