@extends('layouts.app')

@php
    $title = 'Pet Boarding Desk';
    $headerTitle = 'Cashier Pet Boarding & Day Care';
    $breadcrumb = 'Cashier / Pet Boarding';
@endphp

@section('content')
    <!-- Top Statistics Grid -->
    <div class="stat-grid" style="margin-bottom: 1.5rem;">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Today's Check-ins</span>
                <div class="stat-icon-wrapper" style="background: rgba(139, 92, 246, 0.15); color: #a78bfa;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </div>
            </div>
            <div class="stat-value" style="color: #c4b5fd;">{{ $todayCount }} <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">Stays</span></div>
            <div class="stat-desc">Scheduled for today</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Active Boarding</span>
                <div class="stat-icon-wrapper" style="background: rgba(59, 130, 246, 0.15); color: #60a5fa;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="stat-value" style="color: #60a5fa;">{{ $activeCount }} <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">In Kennels</span></div>
            <div class="stat-desc">Currently checked-in / confirmed</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Completed Stays</span>
                <div class="stat-icon-wrapper" style="background: rgba(16, 185, 129, 0.15); color: #34d399;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </div>
            <div class="stat-value" style="color: #34d399;">{{ $completedCount }} <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">Released</span></div>
            <div class="stat-desc">Completed boarding</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Total Boarding Revenue</span>
                <div class="stat-icon-wrapper" style="background: rgba(245, 186, 49, 0.15); color: var(--gold-primary);">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="stat-value" style="color: var(--gold-light);">₱{{ number_format($totalSales, 2) }}</div>
            <div class="stat-desc">Gross pet boarding value</div>
        </div>
    </div>

    <!-- Toolbar & Filter Bar -->
    <div class="table-toolbar" style="margin-bottom: 1.5rem;">
        <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <div class="search-input-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                <input type="text" class="form-control" placeholder="Search client, pet, code..." value="{{ $search ?? '' }}" data-table-search="cashier-boarding-table">
            </div>

            <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                <a href="{{ route('cashier.boarding.index', ['date_filter' => 'today']) }}" class="btn btn-sm {{ $dateFilter === 'today' && !$status ? 'btn-gold' : 'btn-navy' }}">📅 Today</a>
                <a href="{{ route('cashier.boarding.index', ['date_filter' => 'upcoming']) }}" class="btn btn-sm {{ $dateFilter === 'upcoming' && !$status ? 'btn-gold' : 'btn-navy' }}">⏳ Upcoming</a>
                <a href="{{ route('cashier.boarding.index', ['status' => 'confirmed']) }}" class="btn btn-sm {{ $status === 'confirmed' ? 'btn-gold' : 'btn-navy' }}">Confirmed</a>
                <a href="{{ route('cashier.boarding.index', ['status' => 'checked_in']) }}" class="btn btn-sm {{ $status === 'checked_in' ? 'btn-gold' : 'btn-navy' }}">In Kennel</a>
                <a href="{{ route('cashier.boarding.index', ['date_filter' => 'all']) }}" class="btn btn-sm {{ $dateFilter === 'all' && !$status ? 'btn-gold' : 'btn-navy' }}">All Stays</a>
            </div>
        </div>

        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('cashier.billing.index') }}" class="btn btn-navy">
                🧾 Checkout & Billing Desk
            </a>
            <button type="button" class="btn btn-gold" data-modal-target="modal-new-boarding">
                + New Boarding Booking
            </button>
        </div>
    </div>

    <!-- Boarding Table Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-title-group">
                <h3 class="card-title">Pet Boarding Schedule & Kennel Care</h3>
                <span class="card-subtitle">Manage client boarding stays, days, fees, and assigned staff</span>
            </div>
        </div>

        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table" id="cashier-boarding-table">
                    <thead>
                        <tr>
                            <th>Code & Date</th>
                            <th>Client / Owner</th>
                            <th>Patient / Pet</th>
                            <th>Caregiver Staff</th>
                            <th>Stay & Pricing Breakdown</th>
                            <th>Status</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($boardings as $b)
                            <tr>
                                <td>
                                    <strong style="color: var(--gold-primary); font-size: 0.85rem;">{{ $b->appointment_code }}</strong>
                                    <div style="font-size: 0.75rem; color: var(--white); font-weight: 600; margin-top: 2px;">
                                        📅 {{ $b->appointment_date->format('M d, Y') }}
                                    </div>
                                    <div style="font-size: 0.72rem; color: #60a5fa;">
                                        ⏰ {{ date('h:i A', strtotime($b->appointment_time)) }}
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--white);">{{ $b->owner->full_name }}</div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $b->owner->contact_number }}</div>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--white);">🐾 {{ $b->pet->name }}</div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $b->pet->species }} • {{ $b->pet->breed ?: 'Breed unstated' }}</div>
                                </td>
                                <td>
                                    @if($b->assignedEmployee)
                                        <div style="font-size: 0.85rem; font-weight: 600; color: #cbd5e1;">
                                            🧹 {{ $b->assignedEmployee->full_name }}
                                        </div>
                                        <div style="font-size: 0.7rem; color: #a78bfa;">{{ $b->assignedEmployee->position }}</div>
                                    @else
                                        <span style="font-size: 0.75rem; color: var(--text-muted);">Unassigned</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge" style="background: rgba(139, 92, 246, 0.15); color: #c4b5fd; border: 1px solid rgba(139, 92, 246, 0.3); font-size: 0.75rem;">
                                        🏨 {{ $b->boarding_days ?? 1 }} Day{{ ($b->boarding_days ?? 1) > 1 ? 's' : '' }} @ ₱{{ number_format($b->daily_rate ?? 350, 2) }}/day
                                    </span>
                                    <div style="font-size: 0.95rem; font-weight: 800; color: var(--gold-light); margin-top: 4px;">
                                        Total: ₱{{ number_format($b->total_price ?? (($b->daily_rate ?? 350) * ($b->boarding_days ?? 1)), 2) }}
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $badgeClass = match($b->status) {
                                            'confirmed' => 'badge-blue',
                                            'checked_in' => 'badge-gold',
                                            'completed' => 'badge-success',
                                            'cancelled' => 'badge-danger',
                                            default => 'badge-warning',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $b->status)) }}
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 0.35rem; align-items: center;">
                                        <a href="{{ route('cashier.billing.index', ['search' => $b->appointment_code]) }}" class="btn btn-gold btn-sm" title="Go to Billing / Pay">
                                            💵 Checkout
                                        </a>

                                        <button type="button" class="btn btn-navy btn-sm"
                                            data-modal-target="modal-edit-boarding-{{ $b->id }}">
                                            Edit
                                        </button>

                                        <form action="{{ route('cashier.boarding.destroy', $b) }}" method="POST" style="display: inline;"
                                            onsubmit="return confirm('Delete boarding booking {{ $b->appointment_code }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-ghost btn-sm" style="color: #ef4444; padding: 0.25rem 0.45rem;">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal-backdrop" id="modal-edit-boarding-{{ $b->id }}">
                                <div class="modal-dialog" style="max-width: 500px;">
                                    <div class="modal-header">
                                        <div class="modal-title-group">
                                            <h4 class="modal-title">Edit Boarding: {{ $b->appointment_code }}</h4>
                                            <span style="font-size: 0.75rem; color: var(--gold-light);">{{ $b->owner->full_name }} • {{ $b->pet->name }}</span>
                                        </div>
                                        <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
                                    </div>
                                    <form action="{{ route('cashier.boarding.update', $b) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body" style="padding: 1.5rem;">
                                            <div class="form-group">
                                                <label class="form-label">Status *</label>
                                                <select name="status" class="form-control" required>
                                                    <option value="confirmed" {{ $b->status === 'confirmed' ? 'selected' : '' }}>Confirmed (Scheduled)</option>
                                                    <option value="checked_in" {{ $b->status === 'checked_in' ? 'selected' : '' }}>Checked In (In Kennel)</option>
                                                    <option value="completed" {{ $b->status === 'completed' ? 'selected' : '' }}>Completed (Released)</option>
                                                    <option value="cancelled" {{ $b->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label class="form-label">Assign Janitor / Kennel Personnel</label>
                                                <select name="assigned_employee_id" class="form-control">
                                                    <option value="">-- None Assigned --</option>
                                                    @foreach($kennelStaff as $staff)
                                                        <option value="{{ $staff->id }}" {{ $b->assigned_employee_id == $staff->id ? 'selected' : '' }}>
                                                            {{ $staff->full_name }} ({{ $staff->position }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                                <div class="form-group">
                                                    <label class="form-label">Duration (Days) *</label>
                                                    <input type="number" name="boarding_days" class="form-control edit-days-input" min="1" value="{{ $b->boarding_days ?? 1 }}" required>
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label">Daily Rate (₱) *</label>
                                                    <input type="number" step="0.01" name="daily_rate" class="form-control edit-rate-input" min="0" value="{{ $b->daily_rate ?? 350.00 }}" required>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="form-label">Care & Dietary Notes</label>
                                                <textarea name="purpose_examination_notes" class="form-control" rows="2">{{ $b->purpose_examination_notes }}</textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                                            <button type="submit" class="btn btn-gold">Update Boarding</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 3rem 1rem;">
                                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">🏨</div>
                                    <strong style="color: var(--white);">No pet boarding bookings found.</strong>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($boardings->hasPages())
                <div style="padding: 1rem;">
                    {{ $boardings->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- New Boarding Booking Modal -->
    <div class="modal-backdrop" id="modal-new-boarding">
        <div class="modal-dialog" style="max-width: 650px;">
            <div class="modal-header">
                <div class="modal-title-group">
                    <h4 class="modal-title">New Pet Boarding Stay</h4>
                    <span style="font-size: 0.75rem; color: var(--gold-light);">Schedule and record boarding stay</span>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form action="{{ route('cashier.boarding.store') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label">Select Client / Owner *</label>
                        <select name="owner_id" id="select_cashier_boarding_owner" class="form-control select2-searchable" required style="width: 100%;">
                            <option value="">-- Select Client --</option>
                            @foreach($owners as $own)
                                <option value="{{ $own->id }}">{{ $own->full_name }} ({{ $own->client_code }}) • {{ $own->contact_number }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Select Pet *</label>
                        <select name="pet_id" id="select_cashier_boarding_pet" class="form-control" required style="width: 100%;">
                            <option value="">-- Choose Client First --</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Assign Janitor / Kennel Personnel (For Incentives)</label>
                        <select name="assigned_employee_id" class="form-control">
                            <option value="">-- Select Staff --</option>
                            @foreach($kennelStaff as $staff)
                                <option value="{{ $staff->id }}">{{ $staff->full_name }} ({{ $staff->position }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Check-in Date *</label>
                            <input type="date" name="appointment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Check-in Time *</label>
                            <input type="time" name="appointment_time" class="form-control" value="{{ date('H:i') }}" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Duration (Days) *</label>
                            <input type="number" name="boarding_days" id="new_b_days" class="form-control" min="1" value="1" required style="text-align: center; font-weight: 700;">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Price / Day (₱) *</label>
                            <input type="number" step="0.01" name="daily_rate" id="new_b_rate" class="form-control" min="0" value="350.00" required style="text-align: right; font-weight: 700;">
                        </div>
                    </div>

                    <!-- Total Live Calculation -->
                    <div style="background: rgba(139, 92, 246, 0.1); border: 1px solid rgba(139, 92, 246, 0.3); border-radius: 8px; padding: 0.85rem 1.25rem; display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <span style="font-size: 0.85rem; color: #c4b5fd; font-weight: 600;" id="new_b_formula">₱350.00 × 1 day = ₱350.00</span>
                        <div style="text-align: right;">
                            <span style="font-size: 0.7rem; color: var(--text-muted); display: block;">Total Boarding:</span>
                            <span style="font-size: 1.2rem; font-weight: 800; color: var(--gold-light);" id="new_b_total">₱350.00</span>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Dietary, Feeding & Care Instructions</label>
                        <textarea name="purpose_examination_notes" class="form-control" rows="2" placeholder="e.g. Feed 2x a day, bring own dry food, special walking schedule..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold">Confirm Boarding & Create Bill</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/select2.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dynamic Pet select
    if (typeof jQuery !== 'undefined') {
        const selectOwner = jQuery('#select_cashier_boarding_owner');
        const selectPet = jQuery('#select_cashier_boarding_pet');

        selectOwner.on('change', function() {
            const ownerId = jQuery(this).val();
            selectPet.empty();

            if (!ownerId) {
                selectPet.append('<option value="">-- Choose Client First --</option>');
                return;
            }

            selectPet.append('<option value="">⏳ Loading pets...</option>');

            fetch('{{ url("receptionist/api/owners") }}/' + ownerId + '/pets')
                .then(res => res.json())
                .then(data => {
                    selectPet.empty();
                    if (data.pets && data.pets.length > 0) {
                        selectPet.append('<option value="">-- Select Pet (' + data.pets.length + ' available) --</option>');
                        data.pets.forEach(pet => {
                            selectPet.append('<option value="' + pet.id + '">🐾 ' + pet.name + ' (' + (pet.species || 'Pet') + ')</option>');
                        });
                    } else {
                        selectPet.append('<option value="">No pets registered for this client</option>');
                    }
                })
                .catch(err => {
                    selectPet.empty();
                    selectPet.append('<option value="">Failed to load pets</option>');
                });
        });
    }

    // Live calculation for new modal
    const daysInput = document.getElementById('new_b_days');
    const rateInput = document.getElementById('new_b_rate');
    const formulaEl = document.getElementById('new_b_formula');
    const totalEl = document.getElementById('new_b_total');

    function updateCalc() {
        if (!daysInput || !rateInput) return;
        const days = Math.max(1, parseInt(daysInput.value) || 1);
        const rate = Math.max(0, parseFloat(rateInput.value) || 0);
        const total = days * rate;

        const fRate = Number(rate).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const fTotal = Number(total).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        if (formulaEl) formulaEl.textContent = `₱${fRate} × ${days} day${days > 1 ? 's' : ''} = ₱${fTotal}`;
        if (totalEl) totalEl.textContent = `₱${fTotal}`;
    }

    if (daysInput) daysInput.addEventListener('input', updateCalc);
    if (rateInput) rateInput.addEventListener('input', updateCalc);
});
</script>
@endpush
