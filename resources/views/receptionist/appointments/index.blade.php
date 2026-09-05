@extends('layouts.app')

@php
    $title = 'Reception & Appointments';
    $headerTitle = 'Reception Desk & Appointment Scheduler';
    $breadcrumb = 'Reception / Appointments';
@endphp

@section('content')
    <!-- Top Statistics Grid -->
    <div class="stat-grid" style="margin-bottom: 1.5rem;">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Today's Appointments</span>
                <div class="stat-icon-wrapper" style="background: rgba(59, 130, 246, 0.15); color: #3b82f6;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <div class="stat-value">{{ $todayTotal }} <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">Bookings</span></div>
            <div class="stat-desc">{{ $upcomingCount }} total upcoming appointments</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">🏥 Clinic Services</span>
                <div class="stat-icon-wrapper" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>
            <div class="stat-value" style="color: #34d399;">{{ $todayClinic }} <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">Patients Today</span></div>
            <div class="stat-desc">Consultation, Follow up & Wellness</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">✂️ Grooming Salon</span>
                <div class="stat-icon-wrapper" style="background: rgba(245, 186, 49, 0.15); color: var(--gold-primary);">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879a3 3 0 11-4.242-4.242L10.758 7.758a3 3 0 014.242 4.242z" />
                    </svg>
                </div>
            </div>
            <div class="stat-value" style="color: var(--gold-light);">{{ $todayGrooming }} <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">Pets Today</span></div>
            <div class="stat-desc">Bath, styling & coat care</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Checked In & In Clinic</span>
                <div class="stat-icon-wrapper" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="stat-value" style="color: #60a5fa;">{{ $todayCheckedIn }} <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">Active</span></div>
            <div class="stat-desc">Transferred to Vet / Grooming queue</div>
        </div>
    </div>

    <!-- Action Bar & Filter Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <!-- Left Filter Group -->
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
            <a href="{{ route('receptionist.appointments.index', ['date_filter' => 'today']) }}" 
               class="btn btn-sm {{ $dateFilter === 'today' ? 'btn-gold' : 'btn-ghost' }}">
               📅 Today
            </a>
            <a href="{{ route('receptionist.appointments.index', ['date_filter' => 'upcoming']) }}" 
               class="btn btn-sm {{ $dateFilter === 'upcoming' ? 'btn-gold' : 'btn-ghost' }}">
               ⏳ Upcoming
            </a>
            <a href="{{ route('receptionist.appointments.index', ['category' => 'clinic', 'date_filter' => $dateFilter]) }}" 
               class="btn btn-sm {{ $category === 'clinic' ? 'btn-gold' : 'btn-ghost' }}">
               🏥 Clinic
            </a>
            <a href="{{ route('receptionist.appointments.index', ['category' => 'grooming', 'date_filter' => $dateFilter]) }}" 
               class="btn btn-sm {{ $category === 'grooming' ? 'btn-gold' : 'btn-ghost' }}">
               ✂️ Grooming
            </a>
            <a href="{{ route('receptionist.appointments.index', ['date_filter' => 'all']) }}" 
               class="btn btn-sm {{ $dateFilter === 'all' && !$category ? 'btn-gold' : 'btn-ghost' }}">
               All Bookings
            </a>
        </div>

        <!-- Right Book Button -->
        <button type="button" class="btn btn-gold" data-modal-target="modal-book-appointment" style="font-weight: 700; padding: 0.6rem 1.25rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>+ Book New Appointment (Clinic / Grooming)</span>
        </button>
    </div>

    <!-- Appointments Table Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-title-group">
                <h3 class="card-title">Appointments & Booking Schedule</h3>
                <span class="card-subtitle">Showing scheduled client appointments, service categories, and check-in status</span>
            </div>

            <!-- Search form -->
            <form action="{{ route('receptionist.appointments.index') }}" method="GET" style="display: flex; gap: 0.5rem;">
                <input type="hidden" name="date_filter" value="{{ $dateFilter }}">
                @if($category) <input type="hidden" name="category" value="{{ $category }}"> @endif
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search client, pet, code..." value="{{ $search ?? '' }}" style="width: 220px;">
                <button type="submit" class="btn btn-navy btn-sm">Search</button>
                @if($search)
                    <a href="{{ route('receptionist.appointments.index', ['date_filter' => $dateFilter, 'category' => $category]) }}" class="btn btn-ghost btn-sm">Clear</a>
                @endif
            </form>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Code & Time</th>
                            <th>Client / Owner</th>
                            <th>Patient / Pet</th>
                            <th>Service Category</th>
                            <th>Purpose / Notes</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $apt)
                            <tr>
                                <td>
                                    <strong style="color: var(--gold-light); font-size: 0.85rem;">{{ $apt->appointment_code }}</strong>
                                    <div style="font-size: 0.75rem; color: var(--white); font-weight: 600; margin-top: 2px;">
                                        📅 {{ $apt->appointment_date->format('M d, Y') }}
                                    </div>
                                    <div style="font-size: 0.72rem; color: #60a5fa; font-weight: 600;">
                                        ⏰ {{ date('h:i A', strtotime($apt->appointment_time)) }}
                                    </div>
                                </td>
                                <td>
                                    <strong style="color: var(--white);">{{ $apt->owner->full_name }}</strong>
                                    @if($apt->is_new_client)
                                        <span class="badge badge-gold" style="font-size: 0.62rem; padding: 0.1rem 0.35rem;">New Client</span>
                                    @endif
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $apt->owner->contact_number }}</div>
                                    <div style="font-size: 0.68rem; color: var(--text-muted); max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        {{ $apt->owner->address }}
                                    </div>
                                </td>
                                <td>
                                    <strong style="color: var(--white);">🐾 {{ $apt->pet->name }}</strong>
                                    @if($apt->is_new_pet)
                                        <span class="badge badge-blue" style="font-size: 0.62rem; padding: 0.1rem 0.35rem;">New Pet</span>
                                    @endif
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">
                                        {{ $apt->pet->species }} • {{ $apt->pet->breed ?: 'Breed unstated' }}
                                    </div>
                                    @if($apt->pet->age || $apt->pet->sex)
                                        <div style="font-size: 0.68rem; color: var(--text-secondary);">
                                            {{ $apt->pet->sex }} {{ $apt->pet->age ? '• ' . $apt->pet->age : '' }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($apt->service_category === 'clinic')
                                        <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); font-size: 0.72rem;">
                                            🏥 Clinic: {{ ucfirst(str_replace('_', ' ', $apt->service_type)) }}
                                        </span>
                                    @else
                                        <span class="badge" style="background: rgba(245, 186, 49, 0.15); color: #fbbf24; border: 1px solid rgba(245, 186, 49, 0.3); font-size: 0.72rem;">
                                            ✂️ Grooming
                                        </span>
                                    @endif
                                </td>
                                <td style="max-width: 240px; font-size: 0.75rem; color: var(--text-secondary);">
                                    <div style="line-height: 1.35;">{{ $apt->purpose_examination_notes ?: 'No specific examination notes stated.' }}</div>
                                </td>
                                <td>
                                    @php
                                        $statusClass = match($apt->status) {
                                            'confirmed' => 'badge-blue',
                                            'checked_in' => 'badge-gold',
                                            'completed' => 'badge-success',
                                            'cancelled' => 'badge-danger',
                                            default => 'badge-warning',
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}" style="font-size: 0.72rem;">
                                        {{ ucfirst(str_replace('_', ' ', $apt->status)) }}
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.35rem; align-items: center; flex-wrap: wrap;">
                                        @if($apt->status !== 'checked_in' && $apt->status !== 'completed' && $apt->status !== 'cancelled')
                                            <form action="{{ route('receptionist.appointments.status', $apt) }}" method="POST" style="display: inline;">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="checked_in">
                                                <button type="submit" class="btn btn-gold btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.72rem;" title="Check in patient to Vet / Grooming queue">
                                                    ✓ Check-In
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Status Modal Trigger -->
                                        <button type="button" class="btn btn-navy btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.72rem;"
                                            data-modal-target="modal-status-apt-{{ $apt->id }}" title="Change Status">
                                            Status
                                        </button>

                                        <!-- Delete -->
                                        <form action="{{ route('receptionist.appointments.destroy', $apt) }}" method="POST" style="display: inline;"
                                            onsubmit="return confirm('Cancel and delete appointment {{ $apt->appointment_code }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-ghost btn-sm" style="padding: 0.25rem 0.45rem; color: #ef4444;" title="Delete">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- Status Change Modal -->
                            <div class="modal-backdrop" id="modal-status-apt-{{ $apt->id }}">
                                <div class="modal-dialog" style="max-width: 440px;">
                                    <div class="modal-header">
                                        <div class="modal-title-group">
                                            <h4 class="modal-title">Update Status: {{ $apt->appointment_code }}</h4>
                                            <span style="font-size: 0.72rem; color: var(--gold-light);">{{ $apt->owner->full_name }} • {{ $apt->pet->name }}</span>
                                        </div>
                                        <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
                                    </div>
                                    <form action="{{ route('receptionist.appointments.status', $apt) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body" style="padding: 1.5rem;">
                                            <div class="form-group">
                                                <label class="form-label">Appointment Status *</label>
                                                <select name="status" class="form-control" required>
                                                    <option value="confirmed" {{ $apt->status === 'confirmed' ? 'selected' : '' }}>Confirmed (Scheduled)</option>
                                                    <option value="checked_in" {{ $apt->status === 'checked_in' ? 'selected' : '' }}>Checked In (Arrived at Clinic / In Queue)</option>
                                                    <option value="completed" {{ $apt->status === 'completed' ? 'selected' : '' }}>Completed (Service Finished)</option>
                                                    <option value="cancelled" {{ $apt->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                    <option value="pending" {{ $apt->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                </select>
                                            </div>
                                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem; line-height: 1.35;">
                                                💡 <em>Selecting "Checked In" will automatically transfer this patient to the active doctor's or groomer's queue for today.</em>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                                            <button type="submit" class="btn btn-gold">Update Status</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 3rem 1rem;">
                                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">📅</div>
                                    <strong style="color: var(--white);">No appointments found for this filter.</strong>
                                    <div style="font-size: 0.8rem; margin-top: 0.25rem;">Click "+ Book New Appointment" to schedule an existing or new client.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($appointments->hasPages())
                <div style="padding: 1rem;">
                    {{ $appointments->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MASTER BOOKING MODAL (Clinic vs Grooming | Existing vs New Client/Pet) -->
    <!-- ========================================================================= -->
    <div class="modal-backdrop" id="modal-book-appointment">
        <div class="modal-dialog" style="max-width: 920px; width: 95vw;">
            <div class="modal-header" style="padding: 1.25rem 1.75rem;">
                <div class="modal-title-group">
                    <h4 class="modal-title" style="font-size: 1.15rem;">Book New Appointment</h4>
                    <span style="font-size: 0.75rem; color: var(--gold-light);">San Modesto Reception Booking System</span>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form action="{{ route('receptionist.appointments.store') }}" method="POST" id="form-book-appointment">
                @csrf
                <div class="modal-body" style="padding: 1.75rem; max-height: 80vh; overflow-y: auto;">
                    
                    <!-- STEP 1: SERVICE CATEGORY SELECTION -->
                    <div style="margin-bottom: 1.5rem;">
                        <label class="form-label" style="font-size: 0.9rem; font-weight: 700; color: var(--gold-light); margin-bottom: 0.6rem; display: block;">
                            1. Select Service Category *
                        </label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <label style="cursor: pointer; display: block;">
                                <input type="radio" name="service_category" value="clinic" checked id="radio_service_clinic" style="display: none;">
                                <div id="card_service_clinic" class="service-option-card" style="border: 2px solid #10b981; background: rgba(16, 185, 129, 0.12); padding: 1rem 1.25rem; border-radius: 10px; text-align: center; transition: all 0.2s;">
                                    <div style="font-size: 1.6rem; margin-bottom: 0.35rem;">🏥</div>
                                    <strong style="color: #34d399; font-size: 1rem; display: block;">Veterinary Clinic</strong>
                                    <span style="font-size: 0.75rem; color: var(--text-muted);">Consultation, Follow up, Wellness</span>
                                </div>
                            </label>
                            <label style="cursor: pointer; display: block;">
                                <input type="radio" name="service_category" value="grooming" id="radio_service_grooming" style="display: none;">
                                <div id="card_service_grooming" class="service-option-card" style="border: 1px solid var(--navy-border); background: rgba(255, 255, 255, 0.03); padding: 1rem 1.25rem; border-radius: 10px; text-align: center; transition: all 0.2s;">
                                    <div style="font-size: 1.6rem; margin-bottom: 0.35rem;">✂️</div>
                                    <strong style="color: var(--white); font-size: 1rem; display: block;">Grooming Salon</strong>
                                    <span style="font-size: 0.75rem; color: var(--text-muted);">Bath, Teddy bear cut, Coat care</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- CLINIC SPECIFIC TYPE OF SERVICE -->
                    <div id="section_clinic_service_type" style="margin-bottom: 1.5rem; background: rgba(16, 185, 129, 0.06); border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 10px; padding: 1rem 1.25rem;">
                        <label class="form-label" style="font-weight: 700; color: #34d399; font-size: 0.85rem; margin-bottom: 0.5rem; display: block;">
                            Clinic Type of Service *
                        </label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.75rem;">
                            <label style="cursor: pointer; display: flex; align-items: center; gap: 0.5rem; background: rgba(0,0,0,0.25); padding: 0.6rem 0.85rem; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); font-size: 0.85rem; color: var(--white); font-weight: 600;">
                                <input type="radio" name="service_type" value="consultation" checked>
                                <span>Consultation</span>
                            </label>
                            <label style="cursor: pointer; display: flex; align-items: center; gap: 0.5rem; background: rgba(0,0,0,0.25); padding: 0.6rem 0.85rem; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); font-size: 0.85rem; color: var(--white); font-weight: 600;">
                                <input type="radio" name="service_type" value="follow_up">
                                <span>Follow up</span>
                            </label>
                            <label style="cursor: pointer; display: flex; align-items: center; gap: 0.5rem; background: rgba(0,0,0,0.25); padding: 0.6rem 0.85rem; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); font-size: 0.85rem; color: var(--white); font-weight: 600;">
                                <input type="radio" name="service_type" value="wellness">
                                <span>Wellness</span>
                            </label>
                        </div>
                    </div>

                    <!-- STEP 2: CLIENT SELECTION (Existing vs New) -->
                    <div style="margin-bottom: 1.5rem;">
                        <label class="form-label" style="font-size: 0.9rem; font-weight: 700; color: var(--gold-light); margin-bottom: 0.6rem; display: block;">
                            2. Client Status *
                        </label>
                        <div style="display: flex; gap: 2rem; background: rgba(255, 255, 255, 0.03); padding: 0.75rem 1.25rem; border-radius: 10px; border: 1px solid var(--navy-border);">
                            <label style="cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; color: var(--white); font-weight: 600;">
                                <input type="radio" name="client_mode" value="existing" checked id="radio_client_existing">
                                <span>🔍 Existing / Old Client</span>
                            </label>
                            <label style="cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; color: var(--white); font-weight: 600;">
                                <input type="radio" name="client_mode" value="new" id="radio_client_new">
                                <span>👤 + New Client / Owner</span>
                            </label>
                        </div>
                    </div>

                    <!-- SECTION A: EXISTING CLIENT WORKFLOW -->
                    <div id="section_existing_client" style="border: 1px solid var(--navy-border); border-radius: 10px; padding: 1.25rem; background: rgba(0, 0, 0, 0.2); margin-bottom: 1.5rem;">
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 700; font-size: 0.85rem;">Search Client / Owner (Select2) *</label>
                            <select name="owner_id" id="select_existing_owner" class="form-control select2-searchable" style="width: 100%;">
                                <option value="">-- Type to search client name, code, or phone --</option>
                                @foreach($owners as $own)
                                    <option value="{{ $own->id }}" 
                                            data-name="{{ $own->full_name }}" 
                                            data-phone="{{ $own->contact_number }}" 
                                            data-address="{{ $own->address }}">
                                        {{ $own->full_name }} ({{ $own->client_code }}) • {{ $own->contact_number }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Pet Selection under Existing Client -->
                        <div style="margin-top: 1.25rem; border-top: 1px dashed rgba(255, 255, 255, 0.12); padding-top: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <label class="form-label" style="margin: 0; font-weight: 700; color: #60a5fa; font-size: 0.85rem;">🐾 Patient / Pet Selection *</label>
                                <div style="display: flex; gap: 1rem; font-size: 0.8rem;">
                                    <label style="cursor: pointer; display: flex; align-items: center; gap: 0.35rem; color: var(--white); font-weight: 600;">
                                        <input type="radio" name="pet_mode" value="existing" checked id="radio_pet_existing">
                                        <span>Select Pet</span>
                                    </label>
                                    <label style="cursor: pointer; display: flex; align-items: center; gap: 0.35rem; color: var(--gold-light); font-weight: 600;">
                                        <input type="radio" name="pet_mode" value="new" id="radio_pet_new">
                                        <span>+ Add New Pet</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Dropdown for Existing Pet -->
                            <div id="container_select_pet">
                                <select name="pet_id" id="select_existing_pet" class="form-control" style="width: 100%;">
                                    <option value="">-- Select Client First to Load Pets --</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION B: NEW OWNER DETAILS (Visible if client_mode === 'new') -->
                    <div id="section_new_owner_details" style="display: none; border: 1px solid rgba(212, 175, 55, 0.35); border-radius: 10px; padding: 1.25rem; background: rgba(212, 175, 55, 0.05); margin-bottom: 1.5rem;">
                        <h5 style="color: var(--gold-light); font-size: 0.9rem; font-weight: 700; margin: 0 0 1rem 0; display: flex; align-items: center; gap: 0.4rem;">
                            <span>👤</span> New Client / Owner Details
                        </h5>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="owner_name" id="input_owner_name" class="form-control" placeholder="e.g. Maria Clara Santos">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Contact Number *</label>
                                <input type="text" name="owner_contact" id="input_owner_contact" class="form-control" placeholder="e.g. 0917-123-4567">
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                            <div class="form-group">
                                <label class="form-label">Complete Address *</label>
                                <input type="text" name="owner_address" id="input_owner_address" class="form-control" placeholder="e.g. 124 Rizal St, San Modesto">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email Address (Optional)</label>
                                <input type="email" name="owner_email" id="input_owner_email" class="form-control" placeholder="e.g. owner@example.com">
                            </div>
                        </div>
                    </div>

                    <!-- SECTION C: NEW PET DETAILS (Visible if client_mode === 'new' OR pet_mode === 'new') -->
                    <div id="section_new_pet_details" style="display: none; border: 1px solid rgba(96, 165, 250, 0.35); border-radius: 10px; padding: 1.25rem; background: rgba(59, 130, 246, 0.05); margin-bottom: 1.5rem;">
                        <h5 style="color: #60a5fa; font-size: 0.9rem; font-weight: 700; margin: 0 0 1rem 0; display: flex; align-items: center; gap: 0.4rem;">
                            <span>🐾</span> New Pet Information
                        </h5>
                        <div style="display: grid; grid-template-columns: 1.3fr 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label class="form-label">Pet Name *</label>
                                <input type="text" name="pet_name" id="input_pet_name" class="form-control" placeholder="e.g. Browny, Luna, Max">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Species *</label>
                                <select name="pet_species" id="input_pet_species" class="form-control">
                                    <option value="">-- Select Species --</option>
                                    <option value="Dog">Dog</option>
                                    <option value="Cat">Cat</option>
                                    <option value="Bird">Bird</option>
                                    <option value="Rabbit">Rabbit</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Breed</label>
                                <input type="text" name="pet_breed" id="input_pet_breed" class="form-control" placeholder="e.g. Persian, Shih Tzu">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1.3fr; gap: 1rem; margin-top: 0.75rem;">
                            <div class="form-group">
                                <label class="form-label">Birthdate (Optional)</label>
                                <input type="date" name="pet_birth_date" id="input_pet_birth_date" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Age</label>
                                <input type="text" name="pet_age" id="input_pet_age" class="form-control" placeholder="e.g. 2 yrs, 6 mos">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Sex</label>
                                <select name="pet_sex" id="input_pet_sex" class="form-control">
                                    <option value="">-- Choose Sex --</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Neutered Male">Neutered Male</option>
                                    <option value="Spayed Female">Spayed Female</option>
                                    <option value="Unknown">Unknown</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Color / Markings</label>
                                <input type="text" name="pet_color" id="input_pet_color" class="form-control" placeholder="e.g. White with brown patches">
                            </div>
                        </div>
                    </div>

                    <!-- STEP 3: APPOINTMENT DATE, TIME & PURPOSE NOTES -->
                    <div style="border: 1px solid var(--navy-border); border-radius: 10px; padding: 1.25rem; background: rgba(255, 255, 255, 0.02);">
                        <label class="form-label" style="font-size: 0.9rem; font-weight: 700; color: var(--gold-light); margin-bottom: 0.6rem; display: block;">
                            3. Appointment Schedule & Purpose *
                        </label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label class="form-label">Date of Appointment *</label>
                                <input type="date" name="appointment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Time of Appointment *</label>
                                <input type="time" name="appointment_time" class="form-control" value="{{ date('H:i') }}" required>
                            </div>
                        </div>

                        <div class="form-group" style="margin-top: 1rem;">
                            <label class="form-label">Purpose of Examination / Service Notes</label>
                            <textarea name="purpose_examination_notes" class="form-control" rows="3" placeholder="State reason for visit, symptoms, or special grooming styling notes..."></textarea>
                        </div>
                    </div>

                </div>
                <div class="modal-footer" style="padding: 1.25rem 1.75rem;">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold" style="font-weight: 700; padding: 0.6rem 2rem; font-size: 0.9rem;">
                        Confirm & Save Appointment
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
<style>
.service-option-card:hover {
    filter: brightness(1.15);
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/select2.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Service Category Card Toggle (Clinic vs Grooming)
    const radioClinic = document.getElementById('radio_service_clinic');
    const radioGrooming = document.getElementById('radio_service_grooming');
    const cardClinic = document.getElementById('card_service_clinic');
    const cardGrooming = document.getElementById('card_service_grooming');
    const clinicTypeSection = document.getElementById('section_clinic_service_type');

    function updateServiceCategoryUI() {
        if (radioClinic.checked) {
            cardClinic.style.border = '2px solid #10b981';
            cardClinic.style.background = 'rgba(16, 185, 129, 0.15)';
            cardGrooming.style.border = '1px solid var(--navy-border)';
            cardGrooming.style.background = 'rgba(255, 255, 255, 0.03)';
            clinicTypeSection.style.display = 'block';
        } else {
            cardGrooming.style.border = '2px solid var(--gold-primary)';
            cardGrooming.style.background = 'rgba(245, 186, 49, 0.15)';
            cardClinic.style.border = '1px solid var(--navy-border)';
            cardClinic.style.background = 'rgba(255, 255, 255, 0.03)';
            clinicTypeSection.style.display = 'none';
        }
    }

    radioClinic.addEventListener('change', updateServiceCategoryUI);
    radioGrooming.addEventListener('change', updateServiceCategoryUI);
    updateServiceCategoryUI();

    // 2. Client Mode Toggle (Existing vs New)
    const radioClientExisting = document.getElementById('radio_client_existing');
    const radioClientNew = document.getElementById('radio_client_new');
    const sectionExistingClient = document.getElementById('section_existing_client');
    const sectionNewOwner = document.getElementById('section_new_owner_details');
    const sectionNewPet = document.getElementById('section_new_pet_details');
    const radioPetExisting = document.getElementById('radio_pet_existing');
    const radioPetNew = document.getElementById('radio_pet_new');
    const containerSelectPet = document.getElementById('container_select_pet');

    function updateClientAndPetUI() {
        if (radioClientExisting.checked) {
            sectionExistingClient.style.display = 'block';
            sectionNewOwner.style.display = 'none';

            // Pet mode sub-toggle
            if (radioPetNew.checked) {
                containerSelectPet.style.display = 'none';
                sectionNewPet.style.display = 'block';
            } else {
                containerSelectPet.style.display = 'block';
                sectionNewPet.style.display = 'none';
            }
        } else {
            // New Client requires both new owner and new pet
            sectionExistingClient.style.display = 'none';
            sectionNewOwner.style.display = 'block';
            sectionNewPet.style.display = 'block';
        }
    }

    radioClientExisting.addEventListener('change', updateClientAndPetUI);
    radioClientNew.addEventListener('change', updateClientAndPetUI);
    radioPetExisting.addEventListener('change', updateClientAndPetUI);
    radioPetNew.addEventListener('change', updateClientAndPetUI);
    updateClientAndPetUI();

    // 3. Dynamic Pet Fetcher via AJAX when selecting owner
    if (typeof jQuery !== 'undefined') {
        const selectOwner = jQuery('#select_existing_owner');
        const selectPet = jQuery('#select_existing_pet');

        selectOwner.on('change', function() {
            const ownerId = jQuery(this).val();
            selectPet.empty();

            if (!ownerId) {
                selectPet.append('<option value="">-- Select Client First to Load Pets --</option>');
                return;
            }

            selectPet.append('<option value="">⏳ Loading pets...</option>');

            fetch('{{ url("receptionist/api/owners") }}/' + ownerId + '/pets')
                .then(res => res.json())
                .then(data => {
                    selectPet.empty();
                    if (data.pets && data.pets.length > 0) {
                        selectPet.append('<option value="">-- Choose Pet (' + data.pets.length + ' registered) --</option>');
                        data.pets.forEach(pet => {
                            const details = [pet.species, pet.breed, pet.sex].filter(Boolean).join(' • ');
                            selectPet.append('<option value="' + pet.id + '">🐾 ' + pet.name + ' (' + details + ')</option>');
                        });
                    } else {
                        selectPet.append('<option value="">No pets registered yet. Click "+ Add New Pet" above!</option>');
                        radioPetNew.checked = true;
                        updateClientAndPetUI();
                    }
                })
                .catch(err => {
                    selectPet.empty();
                    selectPet.append('<option value="">Failed to load pets</option>');
                });
        });
    }
});
</script>
@endpush
