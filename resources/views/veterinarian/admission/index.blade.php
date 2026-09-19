@extends('layouts.app')

@php
    $title = 'Pet Admission & Inpatient Desk';
    $headerTitle = 'Pet Admission & Inpatient Care';
    $breadcrumb = 'Doctor Workstation / Pet Admission';
@endphp

@section('content')
    <!-- Statistics Cards -->
    <div class="stat-grid" style="margin-bottom: 1.5rem;">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Currently Admitted</span>
                <div class="stat-icon-wrapper" style="background: rgba(212, 175, 55, 0.15); color: var(--gold-primary);">
                    🏥
                </div>
            </div>
            <div class="stat-value" style="color: var(--gold-primary);">{{ $activeAdmitted }}</div>
            <div class="stat-desc">In-clinic patients confined & monitored</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Admitted Today</span>
                <div class="stat-icon-wrapper" style="background: rgba(59, 130, 246, 0.15); color: #60a5fa;">
                    📅
                </div>
            </div>
            <div class="stat-value" style="color: #93c5fd;">{{ $todayAdmitted }}</div>
            <div class="stat-desc">New admissions today</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Discharged / Completed</span>
                <div class="stat-icon-wrapper" style="background: rgba(16, 185, 129, 0.15); color: #34d399;">
                    ✅
                </div>
            </div>
            <div class="stat-value" style="color: #34d399;">{{ $dischargedCount }}</div>
            <div class="stat-desc">Successfully treated & discharged</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Total Admissions</span>
                <div class="stat-icon-wrapper" style="background: rgba(147, 51, 234, 0.15); color: #c084fc;">
                    📊
                </div>
            </div>
            <div class="stat-value" style="color: #c084fc;">{{ $totalAdmissions }}</div>
            <div class="stat-desc">Lifetime inpatient records</div>
        </div>
    </div>

    <!-- Action Bar & Filter Toolbar -->
    <div class="table-toolbar" style="margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <div class="search-input-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                <input type="text" class="form-control" placeholder="Search patient, client, cage..." data-table-search="vet-admissions-table">
            </div>

            <!-- Quick Filter Pills -->
            <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                <a href="{{ route('vet.admission.index') }}" class="btn btn-sm {{ !request('date_filter') || request('date_filter') === 'all' ? 'btn-gold' : 'btn-navy' }}">All</a>
                <a href="{{ route('vet.admission.index', ['date_filter' => 'active']) }}" class="btn btn-sm {{ request('date_filter') === 'active' ? 'btn-gold' : 'btn-navy' }}">🏥 Active Inpatient ({{ $activeAdmitted }})</a>
                <a href="{{ route('vet.admission.index', ['date_filter' => 'today']) }}" class="btn btn-sm {{ request('date_filter') === 'today' ? 'btn-gold' : 'btn-navy' }}">📅 Today ({{ $todayAdmitted }})</a>
                <a href="{{ route('vet.admission.index', ['status' => 'completed']) }}" class="btn btn-sm {{ request('status') === 'completed' ? 'btn-gold' : 'btn-navy' }}">Discharged ({{ $dischargedCount }})</a>
            </div>
        </div>

        <button type="button" class="btn btn-gold" data-modal-target="modal-new-admission">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            <span>+ Admit Patient (Confinement)</span>
        </button>
    </div>

    <!-- Table Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-title-group">
                <h3 class="card-title">Pet Admission & Confinement Ledger</h3>
                <span class="card-subtitle">Inpatient care, confinement monitoring, diagnosis, attending personnel, and discharge status</span>
            </div>
            <span class="badge badge-gold">{{ $admissions->total() }} Admissions</span>
        </div>

        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table" id="vet-admissions-table">
                    <thead>
                        <tr>
                            <th style="min-width: 120px;">Admission Code</th>
                            <th style="min-width: 170px;">Patient & Owner</th>
                            <th style="min-width: 140px;">Admission Date / Duration</th>
                            <th style="min-width: 180px;">Reason / Diagnosis & Care Notes</th>
                            <th style="min-width: 140px;">Rate & Fee</th>
                            <th style="min-width: 130px;">Assigned Personnel</th>
                            <th style="min-width: 110px;">Status</th>
                            <th class="no-sort" data-orderable="false" style="text-align: right; min-width: 110px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admissions as $adm)
                            <tr>
                                <td>
                                    <div style="font-weight: 800; color: var(--gold-primary); font-size: 0.9rem;">
                                        {{ $adm->appointment_code }}
                                    </div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">
                                        {{ $adm->created_at->format('M d, Y h:i A') }}
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 800; color: var(--white); font-size: 0.95rem;">
                                        🐾 {{ $adm->pet->name ?? 'N/A' }}
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--gold-light);">
                                        {{ $adm->pet->species ?? '' }} @if($adm->pet?->breed) • {{ $adm->pet->breed }} @endif
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 3px;">
                                        👤 <strong>{{ $adm->owner->full_name ?? 'N/A' }}</strong>
                                        @if($adm->owner?->contact_number)
                                            <span style="color: var(--text-muted);">({{ $adm->owner->contact_number }})</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--white); font-size: 0.88rem;">
                                        📅 {{ $adm->appointment_date ? $adm->appointment_date->format('M d, Y') : 'N/A' }}
                                    </div>
                                    <div style="font-size: 0.78rem; color: var(--gold-light); margin-top: 2px;">
                                        ⏱️ <strong>{{ $adm->boarding_days ?? 1 }} Day(s)</strong> Stay
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size: 0.82rem; color: var(--text-secondary); line-height: 1.4;">
                                        {{ $adm->purpose_examination_notes ?? 'Inpatient Confinement & Monitoring' }}
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size: 0.78rem; color: var(--text-muted);">
                                        ₱{{ number_format($adm->daily_rate ?? 0, 2) }} / day
                                    </div>
                                    <div style="font-weight: 800; color: var(--gold-primary); font-size: 0.95rem; margin-top: 2px;">
                                        ₱{{ number_format($adm->total_price ?? 0, 2) }}
                                    </div>
                                </td>
                                <td>
                                    @if($adm->assignedEmployee)
                                        <div style="font-weight: 700; color: var(--white); font-size: 0.82rem;">
                                            🧹 {{ $adm->assignedEmployee->full_name }}
                                        </div>
                                        <div style="font-size: 0.72rem; color: var(--gold-light);">
                                            {{ $adm->assignedEmployee->position }}
                                        </div>
                                    @elseif($adm->veterinarian)
                                        <div style="font-weight: 700; color: var(--white); font-size: 0.82rem;">
                                            👨‍⚕️ {{ $adm->veterinarian->name }}
                                        </div>
                                    @else
                                        <span style="color: var(--text-muted); font-size: 0.78rem;">Unassigned</span>
                                    @endif
                                </td>
                                <td>
                                    @if($adm->status === 'checked_in')
                                        <span class="badge badge-warning" style="font-size: 0.75rem;">🏥 In Confinement</span>
                                    @elseif($adm->status === 'completed')
                                        <span class="badge badge-success" style="font-size: 0.75rem;">✅ Discharged</span>
                                    @elseif($adm->status === 'confirmed')
                                        <span class="badge badge-info" style="font-size: 0.75rem;">Confirmed</span>
                                    @elseif($adm->status === 'cancelled')
                                        <span class="badge badge-danger" style="font-size: 0.75rem;">Cancelled</span>
                                    @else
                                        <span class="badge badge-navy" style="font-size: 0.75rem;">{{ ucfirst($adm->status) }}</span>
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: flex; gap: 0.35rem; justify-content: flex-end; flex-wrap: wrap;">
                                        <button type="button" class="btn btn-sm btn-navy"
                                            data-modal-target="modal-edit-admission-{{ $adm->id }}">
                                            Edit
                                        </button>
                                        <form action="{{ route('vet.admission.destroy', $adm->id) }}" method="POST" onsubmit="return confirm('Delete this admission record?');" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-ghost" style="color: #ef4444; padding: 4px 6px;" title="Delete">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
                                    <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🏥</div>
                                    <h4 style="color: var(--white); font-weight: 700; margin-bottom: 0.25rem;">No Inpatient Admissions Found</h4>
                                    <p style="font-size: 0.85rem; max-width: 450px; margin: 0 auto 1rem auto;">
                                        Click <strong>"+ Admit Patient"</strong> to admit and confine a patient for medical observation, post-operative care, or boarding stay.
                                    </p>
                                    <button type="button" class="btn btn-gold btn-sm" data-modal-target="modal-new-admission">
                                        + Admit Patient
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($admissions->hasPages())
                <div style="padding: 1rem 1.25rem; border-top: 1px solid var(--navy-border);">
                    {{ $admissions->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal: New Admission -->
    <div class="modal-backdrop" id="modal-new-admission">
        <div class="modal-dialog modal-lg">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">🏥</div>
                    <div>
                        <h4 class="modal-title">New Patient Admission & Confinement</h4>
                        <span style="font-size: 0.75rem; color: var(--gold-light);">Admit patient for clinical observation, post-op recovery, or inpatient care</span>
                    </div>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form action="{{ route('vet.admission.store') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding: 1.25rem 1.5rem;">
                    <!-- Client & Pet Selector Component -->
                    <x-client-pet-selector :owners="$owners" />

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-top: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Admission Date <span class="req">*</span></label>
                            <input type="date" name="admission_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Estimated Days of Stay <span class="req">*</span></label>
                            <input type="number" name="boarding_days" id="new_adm_days" class="form-control" value="1" min="1" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Daily Inpatient Rate (₱) <span class="req">*</span></label>
                            <input type="number" step="0.01" name="daily_rate" id="new_adm_rate" class="form-control" value="450.00" required>
                        </div>
                    </div>

                    <!-- Computed Total Fee Banner -->
                    <div style="background: rgba(212, 175, 55, 0.08); border: 1.5px solid var(--gold-border); border-radius: var(--radius-sm); padding: 0.75rem 1rem; margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.85rem; color: var(--gold-light); font-weight: 700;">Computed Inpatient Total Fee:</span>
                        <strong style="font-size: 1.2rem; color: var(--gold-primary);" id="new_adm_total_display">₱450.00</strong>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Assigned Attending Staff / Kennel Personnel (Incentive)</label>
                        <select name="assigned_employee_id" class="form-select">
                            <option value="">-- Select Personnel (Optional) --</option>
                            @foreach($staffMembers as $staff)
                                <option value="{{ $staff->id }}">{{ $staff->full_name }} ({{ $staff->position }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Reason for Admission / Clinical Diagnosis & Cage Care Notes</label>
                        <textarea name="purpose_examination_notes" class="form-control" rows="3" placeholder="e.g. FLUTD observation, urinary catheter in place, post-op ovariohysterectomy care, Cage 2..."></textarea>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Initial Admission Status <span class="req">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="checked_in" selected>🏥 In Confinement (Checked-in)</option>
                                <option value="confirmed">Confirmed Booking</option>
                                <option value="completed">Completed / Discharged</option>
                            </select>
                        </div>
                        <div class="form-group" style="display: flex; align-items: center; margin-top: 1.5rem;">
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                <input type="checkbox" name="send_to_billing" value="1" checked style="accent-color: var(--gold-primary); width: 17px; height: 17px;">
                                <span style="font-size: 0.82rem; color: var(--white); font-weight: 600;">Queue to Cashier Billing</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold">🏥 Save & Admit Patient</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Admission Modals -->
    @foreach($admissions as $adm)
        <div class="modal-backdrop" id="modal-edit-admission-{{ $adm->id }}">
            <div class="modal-dialog modal-lg">
                <div class="modal-header">
                    <div class="modal-title-group">
                        <div class="modal-icon">✏️</div>
                        <div>
                            <h4 class="modal-title">Update Admission — {{ $adm->appointment_code }}</h4>
                            <span style="font-size: 0.75rem; color: var(--gold-light);">Patient: <strong>{{ $adm->pet->name ?? 'N/A' }}</strong> • Owner: <strong>{{ $adm->owner->full_name ?? 'N/A' }}</strong></span>
                        </div>
                    </div>
                    <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
                </div>
                <form action="{{ route('vet.admission.update', $adm->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body" style="padding: 1.25rem 1.5rem;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                            <div class="form-group">
                                <label class="form-label">Admission Date <span class="req">*</span></label>
                                <input type="date" name="appointment_date" class="form-control" value="{{ $adm->appointment_date ? $adm->appointment_date->format('Y-m-d') : date('Y-m-d') }}" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Days of Stay <span class="req">*</span></label>
                                <input type="number" name="boarding_days" class="form-control" value="{{ $adm->boarding_days ?? 1 }}" min="1" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Daily Rate (₱) <span class="req">*</span></label>
                                <input type="number" step="0.01" name="daily_rate" class="form-control" value="{{ $adm->daily_rate ?? 450.00 }}" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Assigned Attending Staff / Kennel Personnel</label>
                            <select name="assigned_employee_id" class="form-select">
                                <option value="">-- Select Personnel --</option>
                                @foreach($staffMembers as $staff)
                                    <option value="{{ $staff->id }}" {{ $adm->assigned_employee_id == $staff->id ? 'selected' : '' }}>
                                        {{ $staff->full_name }} ({{ $staff->position }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Reason for Admission / Notes</label>
                            <textarea name="purpose_examination_notes" class="form-control" rows="3">{{ $adm->purpose_examination_notes }}</textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Status <span class="req">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="checked_in" {{ $adm->status === 'checked_in' ? 'selected' : '' }}>🏥 In Confinement (Checked-in)</option>
                                <option value="completed" {{ $adm->status === 'completed' ? 'selected' : '' }}>✅ Discharged / Completed</option>
                                <option value="confirmed" {{ $adm->status === 'confirmed' ? 'selected' : '' }}>Confirmed Booking</option>
                                <option value="cancelled" {{ $adm->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                        <button type="submit" class="btn btn-gold">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const daysInput = document.getElementById('new_adm_days');
        const rateInput = document.getElementById('new_adm_rate');
        const totalDisplay = document.getElementById('new_adm_total_display');

        function recalcNewAdmission() {
            if (!daysInput || !rateInput || !totalDisplay) return;
            const days = parseFloat(daysInput.value) || 0;
            const rate = parseFloat(rateInput.value) || 0;
            const total = days * rate;
            totalDisplay.textContent = '₱' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        if (daysInput && rateInput) {
            daysInput.addEventListener('input', recalcNewAdmission);
            rateInput.addEventListener('input', recalcNewAdmission);
        }
    });
    </script>
@endsection
