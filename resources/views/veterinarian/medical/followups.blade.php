@extends('layouts.app')

@php
    $title = 'Incoming Follow-up Schedule';
    $headerTitle = 'Incoming Follow-up Monitoring';
    $breadcrumb = 'Medical / Follow-ups';
    $today = \Carbon\Carbon::today();
@endphp

@section('content')
    <!-- Top Statistics Cards -->
    <div class="stat-grid" style="margin-bottom: 1.5rem;">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Today's Follow-ups</span>
                <div class="stat-icon-wrapper" style="background: rgba(212, 175, 55, 0.15); color: var(--gold-primary);">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="stat-value" style="color: var(--gold-primary);">{{ $todayCount }}</div>
            <div class="stat-desc">Scheduled for checkup today</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">This Week's Schedule</span>
                <div class="stat-icon-wrapper" style="background: rgba(59, 130, 246, 0.15); color: #60a5fa;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <div class="stat-value" style="color: #93c5fd;">{{ $thisWeekCount }}</div>
            <div class="stat-desc">Current week follow-ups</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Upcoming (Future)</span>
                <div class="stat-icon-wrapper" style="background: rgba(16, 185, 129, 0.15); color: #34d399;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
            <div class="stat-value" style="color: #34d399;">{{ $upcomingCount }}</div>
            <div class="stat-desc">Scheduled ahead of time</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Overdue / Missed</span>
                <div class="stat-icon-wrapper" style="background: rgba(239, 68, 68, 0.15); color: #f87171;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>
            <div class="stat-value" style="{{ $overdueCount > 0 ? 'color: #f87171;' : 'color: var(--text-muted);' }}">{{ $overdueCount }}</div>
            <div class="stat-desc">Requires client reminder</div>
        </div>
    </div>

    <!-- Action Bar & Filter Toolbar -->
    <div class="table-toolbar">
        <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <div class="search-input-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                <input type="text" class="form-control" placeholder="Search patient, owner, contact, notes..." data-table-search="vet-followups-table">
            </div>

            <!-- Date Filter Pills -->
            <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                <a href="{{ route('vet.followups.index') }}" class="btn btn-sm {{ !request('filter') || request('filter') === 'all' ? 'btn-gold' : 'btn-navy' }}">
                    All ({{ $totalCount }})
                </a>
                <a href="{{ route('vet.followups.index', ['filter' => 'today']) }}" class="btn btn-sm {{ request('filter') === 'today' ? 'btn-gold' : 'btn-navy' }}">
                    📅 Today ({{ $todayCount }})
                </a>
                <a href="{{ route('vet.followups.index', ['filter' => 'this_week']) }}" class="btn btn-sm {{ request('filter') === 'this_week' ? 'btn-gold' : 'btn-navy' }}">
                    This Week ({{ $thisWeekCount }})
                </a>
                <a href="{{ route('vet.followups.index', ['filter' => 'upcoming']) }}" class="btn btn-sm {{ request('filter') === 'upcoming' ? 'btn-gold' : 'btn-navy' }}">
                    Upcoming ({{ $upcomingCount }})
                </a>
                <a href="{{ route('vet.followups.index', ['filter' => 'overdue']) }}" class="btn btn-sm {{ request('filter') === 'overdue' ? 'btn-danger' : 'btn-navy' }}">
                    ⚠️ Overdue ({{ $overdueCount }})
                </a>
            </div>
        </div>

        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('vet.medical.index') }}" class="btn btn-navy">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                <span>Medical Records</span>
            </a>
        </div>
    </div>

    <!-- Follow-ups Table Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-title-group">
                <h3 class="card-title">Incoming Patient Follow-ups</h3>
                <span class="card-subtitle">Perform follow-up re-examinations, pull medical record history, administer services, and queue to cashier</span>
            </div>
            <span class="badge badge-gold" style="font-size: 0.78rem;">
                {{ $followUps->count() }} Records Found
            </span>
        </div>

        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table" id="vet-followups-table">
                    <thead>
                        <tr>
                            <th style="min-width: 140px;">Follow-up Schedule</th>
                            <th style="min-width: 170px;">Patient & Pet Info</th>
                            <th style="min-width: 170px;">Client / Owner Contact</th>
                            <th style="min-width: 200px;">Purpose & Clinical Notes</th>
                            <th style="min-width: 150px;">Origin Examination</th>
                            <th class="no-sort" data-orderable="false" style="text-align: right; min-width: 180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($followUps as $record)
                            @php
                                $followDate = \Carbon\Carbon::parse($record->follow_up_date);
                                $isToday = $followDate->isToday();
                                $isPast = $followDate->isPast() && !$isToday;
                                $isFuture = $followDate->isFuture();
                                $diffInDays = $today->diffInDays($followDate, false);
                            @endphp
                            <tr style="{{ $isToday ? 'background: rgba(212, 175, 55, 0.06);' : ($isPast ? 'background: rgba(239, 68, 68, 0.04);' : '') }}">
                                <td>
                                    <!-- Schedule Badge -->
                                    <div style="font-weight: 800; font-size: 0.95rem; color: {{ $isToday ? 'var(--gold-primary)' : ($isPast ? '#f87171' : 'var(--white)') }}; display: flex; align-items: center; gap: 0.35rem;">
                                        📅 {{ $followDate->format('M d, Y') }}
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                                        {{ $followDate->format('l') }}
                                    </div>

                                    @if($isToday)
                                        <span class="badge badge-gold" style="font-size: 0.72rem; margin-top: 5px; font-weight: 800;">
                                            ⭐ DUE TODAY
                                        </span>
                                    @elseif($isPast)
                                        <span class="badge" style="background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); font-size: 0.72rem; margin-top: 5px;">
                                            ⚠️ {{ abs($diffInDays) }} {{ abs($diffInDays) == 1 ? 'day' : 'days' }} Overdue
                                        </span>
                                    @else
                                        <span class="badge badge-navy" style="font-size: 0.72rem; margin-top: 5px; color: #34d399; border-color: rgba(52, 211, 153, 0.3);">
                                            ⏳ In {{ $diffInDays }} {{ $diffInDays == 1 ? 'day' : 'days' }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-weight: 800; color: var(--white); font-size: 0.95rem;">
                                        🐾 {{ $record->pet->name ?? 'N/A' }}
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--gold-light);">
                                        {{ $record->pet->species ?? '' }} @if($record->pet?->breed) • {{ $record->pet->breed }} @endif
                                    </div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">
                                        Code: <strong>{{ $record->pet->pet_code ?? '' }}</strong>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--white); font-size: 0.9rem;">
                                        👤 {{ $record->owner->full_name ?? 'N/A' }}
                                    </div>
                                    <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">
                                        📞 {{ $record->owner->contact_number ?? 'No contact' }}
                                    </div>
                                </td>
                                <td>
                                    <div style="background: rgba(11, 25, 44, 0.5); border-left: 3px solid var(--gold-primary); padding: 0.45rem 0.65rem; border-radius: var(--radius-sm);">
                                        <div style="font-size: 0.75rem; color: var(--gold-light); font-weight: 700; text-transform: uppercase; margin-bottom: 2px;">
                                            Follow-Up Purpose:
                                        </div>
                                        <div style="font-size: 0.85rem; color: var(--white); font-weight: 600;">
                                            {{ $record->follow_up_notes ?: 'General re-examination & checkup' }}
                                        </div>
                                    </div>

                                    @if($record->medication_treatment)
                                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 5px;">
                                            <strong>Rx/Tx:</strong> {{ Str::limit($record->medication_treatment, 50) }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-size: 0.82rem; font-weight: 700; color: var(--gold-light);">
                                        {{ $record->record_code }}
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">
                                        Exam Date: {{ $record->visit_date ? $record->visit_date->format('m/d/Y') : $record->created_at->format('m/d/Y') }}
                                    </div>
                                    @if($record->diagnosis)
                                        <div style="font-size: 0.75rem; color: var(--white); margin-top: 2px;">
                                            <strong>Dx:</strong> {{ Str::limit($record->diagnosis, 30) }}
                                        </div>
                                    @endif
                                    <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">
                                        👨‍⚕️ {{ $record->veterinarian->name ?? 'Attending Vet' }}
                                    </div>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: flex; gap: 0.35rem; justify-content: flex-end; flex-wrap: wrap;">
                                        <!-- Primary Action: Start Examination / Perform Follow-up -->
                                        <button type="button" class="btn btn-sm btn-gold" style="font-weight: 700;"
                                            data-modal-target="modal-perform-fu-{{ $record->id }}">
                                            🩺 Start Examination
                                        </button>

                                        <!-- Quick Reschedule -->
                                        <button type="button" class="btn btn-sm btn-navy" title="Reschedule or Update Note" data-modal-target="modal-vet-edit-fu-{{ $record->id }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 14px; height: 14px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 3rem 1rem;">
                                    <div style="font-size: 2.5rem; margin-bottom: 0.75rem;">🗓️</div>
                                    <h4 style="color: var(--white); font-weight: 700; margin-bottom: 0.25rem;">No Incoming Follow-ups Found</h4>
                                    <p style="color: var(--text-muted); font-size: 0.85rem; max-width: 400px; margin: 0 auto;">
                                        @if(request('filter'))
                                            No follow-up records found matching filter "{{ request('filter') }}".
                                        @else
                                            When you set a follow-up date during an examination, it will automatically appear here for tracking!
                                        @endif
                                    </p>
                                    <div style="margin-top: 1rem;">
                                        <a href="{{ route('vet.medical.index') }}" class="btn btn-gold btn-sm">
                                            Go to Medical Records
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ==================== MODALS CONTAINER ==================== -->
    @foreach($followUps as $record)
        <!-- 1. FULL FOLLOW-UP EXAMINATION MODAL (Pull Up Full History -> Exam -> Services -> Cashier -> Next Follow-up) -->
        <div class="modal-backdrop" id="modal-perform-fu-{{ $record->id }}">
            <div class="modal-dialog modal-xl" style="max-width: 1200px;">
                <div class="modal-header">
                    <div class="modal-title-group">
                        <div class="modal-icon">🩺</div>
                        <div>
                            <h4 class="modal-title">Perform Follow-Up Examination — Patient: {{ $record->pet->name ?? 'Pet' }}</h4>
                            <span style="font-size: 0.75rem; color: var(--gold-light);">
                                Client: <strong>{{ $record->owner->full_name ?? 'Client' }}</strong> • Pet: <strong>{{ $record->pet->name ?? '' }}</strong> ({{ $record->pet->species ?? '' }} - {{ $record->pet->breed ?? '' }}) • Origin: {{ $record->record_code }}
                            </span>
                        </div>
                    </div>
                    <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
                </div>

                <form action="{{ route('vet.followups.perform') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="origin_record_id" value="{{ $record->id }}">
                    <input type="hidden" name="owner_id" value="{{ $record->owner_id }}">
                    <input type="hidden" name="pet_id" value="{{ $record->pet_id }}">

                    <div class="modal-body" style="padding: 1.25rem 1.5rem; max-height: calc(100vh - 170px); overflow-y: auto;">
                        
                        <!-- SECTION 1: PULL UP MEDICAL RECORD FULL HISTORY -->
                        <div style="background: rgba(11, 25, 44, 0.6); border: 1.5px solid var(--gold-border); border-radius: var(--radius-sm); padding: 1rem; margin-bottom: 1.25rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
                                <h5 style="color: var(--gold-primary); font-size: 0.88rem; font-weight: 800; text-transform: uppercase; margin: 0; display: flex; align-items: center; gap: 0.4rem;">
                                    <span>📋</span> Medical Record History for {{ $record->pet->name ?? 'Patient' }}
                                </h5>
                                <span class="badge badge-navy" style="font-size: 0.75rem;">
                                    {{ $record->pet ? $record->pet->medicalRecords->count() : 0 }} Past Visit(s) Recorded
                                </span>
                            </div>

                            <div style="max-height: 180px; overflow-y: auto; border: 1px solid var(--navy-border); border-radius: 4px; background: rgba(4, 7, 13, 0.5);">
                                <table style="width: 100%; border-collapse: collapse; font-size: 0.8rem;">
                                    <thead style="position: sticky; top: 0; background: var(--navy-dark); z-index: 1;">
                                        <tr style="border-bottom: 1px solid var(--navy-border);">
                                            <th style="padding: 6px 10px; text-align: left; color: var(--gold-light);">Visit Date & Code</th>
                                            <th style="padding: 6px 10px; text-align: left; color: var(--gold-light);">Vitals</th>
                                            <th style="padding: 6px 10px; text-align: left; color: var(--gold-light);">Past Diagnosis / Complaint</th>
                                            <th style="padding: 6px 10px; text-align: left; color: var(--gold-light);">Medications & Treatment</th>
                                            <th style="padding: 6px 10px; text-align: left; color: var(--gold-light);">Attending Vet</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($record->pet && $record->pet->medicalRecords)
                                            @foreach($record->pet->medicalRecords->sortByDesc('created_at') as $pastRec)
                                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); {{ $pastRec->id === $record->id ? 'background: rgba(212,175,55,0.08);' : '' }}">
                                                    <td style="padding: 6px 10px;">
                                                        <strong style="color: var(--gold-light);">{{ $pastRec->visit_date ? $pastRec->visit_date->format('m/d/Y') : $pastRec->created_at->format('m/d/Y') }}</strong>
                                                        <div style="font-size: 0.7rem; color: var(--text-muted);">{{ $pastRec->record_code }}</div>
                                                    </td>
                                                    <td style="padding: 6px 10px; color: var(--white);">
                                                        {{ $pastRec->temperature ?: '—' }} • {{ $pastRec->body_weight ?: '—' }}
                                                    </td>
                                                    <td style="padding: 6px 10px; color: var(--text-secondary);">
                                                        <strong style="color: var(--white);">{{ $pastRec->diagnosis ?: 'N/A' }}</strong>
                                                        <div style="font-size: 0.72rem; color: var(--text-muted);">{{ Str::limit($pastRec->history_taking, 45) }}</div>
                                                    </td>
                                                    <td style="padding: 6px 10px; color: var(--text-secondary);">
                                                        {{ Str::limit($pastRec->medication_treatment ?: ($pastRec->prescription?->rx_details ?? '—'), 50) }}
                                                    </td>
                                                    <td style="padding: 6px 10px; color: var(--text-muted);">
                                                        {{ $pastRec->veterinarian->name ?? 'Vet' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- SECTION 2: 2-COLUMN PERFORM FOLLOW-UP EXAMINATION FORM -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; align-items: start;">
                            
                            <!-- LEFT COLUMN -->
                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                <!-- 1. Visit Date & Physical Vitals -->
                                <div style="background: rgba(11, 25, 44, 0.4); border: 1px solid var(--navy-border); border-radius: var(--radius-sm); padding: 1rem;">
                                    <div class="form-group" style="margin-bottom: 0.75rem;">
                                        <label class="form-label" style="font-weight: 700; color: var(--gold-primary);">📅 Follow-Up Visit Date <span class="req">*</span></label>
                                        <input type="date" name="visit_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                    </div>

                                    <h5 style="color: var(--gold-light); font-size: 0.8rem; font-weight: 700; text-transform: uppercase; margin-bottom: 0.5rem;">
                                        🌡️ Physical Vitals (Today)
                                    </h5>
                                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.65rem;">
                                        <div class="form-group" style="margin-bottom: 0;">
                                            <label class="form-label" style="font-size: 0.75rem;">Temp (°C)</label>
                                            <input type="text" name="temperature" class="form-control" placeholder="e.g. 38.3 °C" value="{{ $record->temperature }}">
                                        </div>
                                        <div class="form-group" style="margin-bottom: 0;">
                                            <label class="form-label" style="font-size: 0.75rem;">Weight (BW)</label>
                                            <input type="text" name="body_weight" class="form-control" placeholder="e.g. 4.2 kg" value="{{ $record->body_weight }}">
                                        </div>
                                        <div class="form-group" style="margin-bottom: 0;">
                                            <label class="form-label" style="font-size: 0.75rem;">Body Score</label>
                                            <input type="text" name="body_score" class="form-control" placeholder="e.g. 3/5" value="{{ $record->body_score }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. Follow-Up Progress & Exam Findings -->
                                <div class="form-group">
                                    <label class="form-label" style="font-weight: 700; color: var(--gold-primary);">📝 Follow-Up Examination Notes / Progress Evaluation <span class="req">*</span></label>
                                    <textarea name="history_taking" class="form-control" rows="3" placeholder="Symptoms response, catheter check, wound recovery, client observations..." required>{{ $record->follow_up_notes ? "Re-evaluation for: {$record->follow_up_notes}" : '' }}</textarea>
                                </div>

                                <!-- 3. Diagnosis / Assessment -->
                                <div class="form-group">
                                    <label class="form-label" style="font-weight: 700; color: var(--gold-primary);">🩺 Follow-Up Diagnosis / Assessment <span class="req">*</span></label>
                                    <input type="text" name="diagnosis" class="form-control" value="{{ $record->diagnosis ?: 'Follow-up re-evaluation' }}" required>
                                </div>

                                <!-- 4. Follow-up Service Fee -->
                                <div style="background: rgba(245, 186, 49, 0.08); border: 1.5px solid var(--gold-border); border-radius: var(--radius-sm); padding: 0.85rem 1.15rem; display: flex; justify-content: space-between; align-items: center; gap: 1rem;">
                                    <div>
                                        <label class="form-label" style="font-weight: 700; color: var(--gold-light); font-size: 0.88rem; margin-bottom: 2px;">
                                            🩺 Follow-up Consultation Fee (₱) <span class="req">*</span>
                                        </label>
                                        <div style="font-size: 0.72rem; color: var(--text-muted);">
                                            Ipapasa sa Cashier Billing.
                                        </div>
                                    </div>
                                    <input type="number" step="0.01" min="0" name="service_fee" id="fu_service_fee_{{ $record->id }}" class="form-control fu-fee-input" value="350.00" required style="max-width: 140px; font-weight: 800; text-align: right; color: var(--gold-primary); font-size: 1.1rem; border-color: var(--gold-border);">
                                </div>

                                <!-- 5. Next Follow-Up Schedule -->
                                <div style="background: rgba(11, 25, 44, 0.4); border: 1px solid var(--navy-border); border-radius: var(--radius-sm); padding: 0.85rem 1rem;">
                                    <h5 style="color: var(--gold-light); font-size: 0.8rem; font-weight: 700; text-transform: uppercase; margin-bottom: 0.5rem;">
                                        🗓️ Next Follow-Up Schedule (If Needed)
                                    </h5>
                                    <div class="form-grid">
                                        <div class="form-group" style="margin-bottom: 0;">
                                            <label class="form-label" style="font-size: 0.75rem;">Next Follow Up Date</label>
                                            <input type="date" name="next_follow_up_date" class="form-control">
                                        </div>
                                        <div class="form-group" style="margin-bottom: 0;">
                                            <label class="form-label" style="font-size: 0.75rem;">Purpose / Next Goal</label>
                                            <input type="text" name="next_follow_up_notes" class="form-control" placeholder="e.g. final checkup / suture removal">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- RIGHT COLUMN -->
                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                <!-- 1. Medication / Treatment Administered -->
                                <div class="form-group">
                                    <label class="form-label" style="font-size: 0.8rem; color: var(--text-secondary);">💊 In-Clinic Medication / Treatment Administered</label>
                                    <textarea name="medication_treatment" class="form-control" rows="2" placeholder="Injections, catheter flush, cleaning, IV fluids..."></textarea>
                                </div>

                                <!-- 2. Laboratory / Medical Services Performed -->
                                <div style="background: var(--navy-dark); border: 1.5px solid var(--navy-border); border-radius: var(--radius-sm); padding: 0.85rem 1rem;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.35rem;">
                                        <div>
                                            <h5 style="color: var(--gold-light); font-size: 0.82rem; font-weight: 800; text-transform: uppercase; margin: 0;">
                                                🔬 LABORATORY TEST & MEDICAL SERVICES
                                            </h5>
                                            <span style="font-size: 0.72rem; color: var(--text-muted);">
                                                Awtomatikong ipapasa sa Cashier Billing ang bawat line item.
                                            </span>
                                        </div>
                                        <button type="button" class="btn btn-gold btn-sm btn-add-fu-item" data-target="fu-tbody-{{ $record->id }}" style="font-size: 0.72rem; padding: 3px 8px; font-weight: 700;">
                                            ➕ Add Service
                                        </button>
                                    </div>

                                    <!-- Quick suggestion buttons -->
                                    <div style="display: flex; gap: 0.3rem; flex-wrap: wrap; margin-bottom: 0.5rem;">
                                        <button type="button" class="btn btn-navy btn-sm btn-quick-fu-svc" data-target="fu-tbody-{{ $record->id }}" data-name="Repeat CBC (Blood Count)" data-price="450.00" style="font-size: 0.68rem; padding: 2px 6px;">+ Repeat CBC (₱450)</button>
                                        <button type="button" class="btn btn-navy btn-sm btn-quick-fu-svc" data-target="fu-tbody-{{ $record->id }}" data-name="Catheter Removal & Flush" data-price="350.00" style="font-size: 0.68rem; padding: 2px 6px;">+ Catheter Removal (₱350)</button>
                                        <button type="button" class="btn btn-navy btn-sm btn-quick-fu-svc" data-target="fu-tbody-{{ $record->id }}" data-name="Suture Removal & Wound Dressing" data-price="300.00" style="font-size: 0.68rem; padding: 2px 6px;">+ Suture Removal (₱300)</button>
                                        <button type="button" class="btn btn-navy btn-sm btn-quick-fu-svc" data-target="fu-tbody-{{ $record->id }}" data-name="Blood Chem Re-check" data-price="850.00" style="font-size: 0.68rem; padding: 2px 6px;">+ Blood Chem (₱850)</button>
                                    </div>

                                    <div style="max-height: 180px; overflow-y: auto; border: 1px solid var(--black-border); border-radius: var(--radius-sm); background: rgba(4, 7, 13, 0.4);">
                                        <table style="width: 100%; border-collapse: collapse; font-size: 0.8rem;">
                                            <thead style="position: sticky; top: 0; background: var(--navy-dark); z-index: 2;">
                                                <tr style="border-bottom: 1px solid var(--black-border);">
                                                    <th style="padding: 6px 8px; text-align: left; color: var(--gold-light); font-size: 0.72rem; text-transform: uppercase;">Service / Test</th>
                                                    <th style="padding: 6px 8px; width: 60px; text-align: center; color: var(--gold-light); font-size: 0.72rem; text-transform: uppercase;">Qty</th>
                                                    <th style="padding: 6px 8px; width: 85px; text-align: right; color: var(--gold-light); font-size: 0.72rem; text-transform: uppercase;">Price (₱)</th>
                                                    <th style="padding: 6px 8px; width: 85px; text-align: right; color: var(--gold-light); font-size: 0.72rem; text-transform: uppercase;">Total</th>
                                                    <th style="padding: 6px 8px; width: 30px;"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="fu-tbody-{{ $record->id }}">
                                                <!-- Dynamic items added here -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Computed cashier sum -->
                                    <div style="background: rgba(4, 7, 13, 0.6); border: 1px solid var(--gold-border); border-radius: var(--radius-sm); padding: 0.5rem 0.75rem; display: flex; justify-content: space-between; align-items: center; margin-top: 0.4rem;">
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">
                                            Total Queued to Cashier:
                                        </div>
                                        <div style="font-weight: 800; color: var(--gold-primary); font-size: 1rem;" id="fu_grand_total_{{ $record->id }}">
                                            ₱350.00
                                        </div>
                                    </div>
                                </div>

                                <!-- 3. Prescription Take-Home Meds -->
                                <div style="background: rgba(245, 186, 49, 0.05); border: 1px solid var(--gold-border); border-radius: var(--radius-sm); padding: 0.85rem 1rem;">
                                    <h5 style="color: var(--gold-light); font-size: 0.8rem; font-weight: 700; text-transform: uppercase; margin-bottom: 0.4rem;">
                                        💊 Take-Home Prescription (Rx)
                                    </h5>
                                    <div class="form-group" style="margin-bottom: 0.5rem;">
                                        <label class="form-label" style="font-size: 0.75rem;">Medication Details, Strength & Dosage</label>
                                        <textarea name="prescribe_rx" class="form-control" rows="2" placeholder="e.g. 1. Continue Co-Amoxiclav 250mg - 1 tab BID for 5 days"></textarea>
                                    </div>
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label class="form-label" style="font-size: 0.75rem;">Rx Instructions</label>
                                        <input type="text" name="rx_instructions" class="form-control" placeholder="e.g. Give after meals">
                                    </div>
                                </div>

                                <!-- 4. Attach Lab Results / Doctor Notes -->
                                <div class="form-grid">
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label class="form-label" style="font-size: 0.75rem;">Doctor's Advice / Notes</label>
                                        <input type="text" name="veterinarians_notes" class="form-control" placeholder="e.g. Patient is active, clear for discharge">
                                    </div>
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label class="form-label" style="font-size: 0.75rem;">Attach Lab File (Optional)</label>
                                        <input type="file" name="lab_results" class="form-control" accept="image/*,.pdf,.doc,.docx" style="padding: 3px; font-size: 0.75rem;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer" style="padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                        <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                        <button type="submit" class="btn btn-gold" style="font-weight: 700; font-size: 0.92rem; padding: 0.65rem 1.5rem; display: inline-flex; align-items: center; gap: 0.5rem;">
                            💾 Save Follow-Up Medical Record & Transfer to Cashier Billing
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 2. RESCHEDULE MODAL -->
        <div class="modal-backdrop" id="modal-vet-edit-fu-{{ $record->id }}">
            <div class="modal-dialog modal-md">
                <div class="modal-header">
                    <h4 class="modal-title">🗓️ Update Follow-Up Schedule</h4>
                    <button type="button" class="modal-close" data-modal-close>&times;</button>
                </div>
                <form action="{{ route('vet.followups.update', $record->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body" style="display: flex; flex-direction: column; gap: 1rem;">
                        <div style="background: rgba(11, 25, 44, 0.4); padding: 0.75rem; border-radius: var(--radius-sm); border: 1px solid var(--navy-border);">
                            <div style="font-weight: 700; color: var(--gold-light);">Patient: {{ $record->pet->name ?? 'Pet' }} (Owner: {{ $record->owner->full_name ?? 'Owner' }})</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">Record: {{ $record->record_code }} • Diagnosis: {{ $record->diagnosis ?? 'N/A' }}</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Follow-Up Date <span class="req">*</span></label>
                            <input type="date" name="follow_up_date" class="form-control" value="{{ $record->follow_up_date ? $record->follow_up_date->format('Y-m-d') : '' }}" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Follow-Up Purpose / Clinical Instructions</label>
                            <textarea name="follow_up_notes" class="form-control" rows="3" placeholder="e.g. Catheter removal, suture removal, repeat CBC, CBC re-check...">{{ $record->follow_up_notes }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-navy" data-modal-close>Cancel</button>
                        <button type="submit" class="btn btn-gold">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    <!-- JavaScript for Dynamic Follow-up Items & Total Calculation -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        function recalculateFUTotal(containerId) {
            const tableBody = document.getElementById(containerId);
            if (!tableBody) return;
            const modalId = containerId.replace('fu-tbody-', '');
            const feeInput = document.getElementById('fu_service_fee_' + modalId);
            const totalDisplay = document.getElementById('fu_grand_total_' + modalId);

            let total = parseFloat(feeInput ? feeInput.value : 0) || 0;
            tableBody.querySelectorAll('tr.fu-item-row').forEach(function (row) {
                const qty = parseFloat(row.querySelector('.fu-item-qty')?.value || 1) || 1;
                const price = parseFloat(row.querySelector('.fu-item-price')?.value || 0) || 0;
                const rowTot = qty * price;
                const totCell = row.querySelector('.fu-item-row-tot');
                if (totCell) totCell.textContent = '₱' + rowTot.toFixed(2);
                total += rowTot;
            });

            if (totalDisplay) {
                totalDisplay.textContent = '₱' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        }

        function createFURow(targetTbodyId, name = '', qty = '1', price = '') {
            const tableBody = document.getElementById(targetTbodyId);
            if (!tableBody) return;

            const index = tableBody.querySelectorAll('tr.fu-item-row').length + '_' + Date.now();
            const tr = document.createElement('tr');
            tr.className = 'fu-item-row';
            tr.style.borderBottom = '1px solid rgba(255,255,255,0.05)';
            const q = parseFloat(qty) || 1;
            const p = parseFloat(price) || 0;
            const t = q * p;

            tr.innerHTML = `
                <td style="padding: 4px 6px;">
                    <input type="text" name="items[${index}][name]" class="form-control form-control-sm fu-item-name" placeholder="Service / Test name" value="${name.replace(/"/g, '&quot;')}" style="font-size: 0.78rem; padding: 3px 6px;" required autofocus>
                </td>
                <td style="padding: 4px 6px; width: 60px;">
                    <input type="number" step="0.01" min="0.01" name="items[${index}][quantity]" class="form-control form-control-sm fu-item-qty" placeholder="1" value="${qty}" style="font-size: 0.78rem; padding: 3px 4px; text-align: center;">
                </td>
                <td style="padding: 4px 6px; width: 85px;">
                    <input type="number" step="0.01" min="0" name="items[${index}][price]" class="form-control form-control-sm fu-item-price" placeholder="0.00" value="${price}" style="font-size: 0.78rem; padding: 3px 4px; text-align: right;">
                </td>
                <td style="padding: 4px 6px; width: 85px; text-align: right; font-weight: 700; color: var(--gold-primary); font-size: 0.78rem;" class="fu-item-row-tot">
                    ₱${t.toFixed(2)}
                </td>
                <td style="padding: 4px 6px; width: 30px; text-align: center;">
                    <button type="button" class="btn btn-ghost btn-sm btn-remove-fu-row" style="color: #ef4444; padding: 1px 3px; font-size: 0.8rem;" title="Remove">
                        🗑️
                    </button>
                </td>
            `;

            tableBody.appendChild(tr);

            tr.querySelector('.btn-remove-fu-row').addEventListener('click', function () {
                tr.remove();
                recalculateFUTotal(targetTbodyId);
            });

            tr.querySelector('.fu-item-qty').addEventListener('input', () => recalculateFUTotal(targetTbodyId));
            tr.querySelector('.fu-item-price').addEventListener('input', () => recalculateFUTotal(targetTbodyId));

            recalculateFUTotal(targetTbodyId);
        }

        // Add service buttons
        document.querySelectorAll('.btn-add-fu-item').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const target = btn.getAttribute('data-target');
                createFURow(target, '', '1', '');
            });
        });

        // Quick service buttons
        document.querySelectorAll('.btn-quick-fu-svc').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const target = btn.getAttribute('data-target');
                const name = btn.getAttribute('data-name');
                const price = btn.getAttribute('data-price');
                createFURow(target, name, '1', price);
            });
        });

        // Listen for base fee changes
        document.querySelectorAll('.fu-fee-input').forEach(function (inp) {
            inp.addEventListener('input', function () {
                const modalId = inp.id.replace('fu_service_fee_', '');
                recalculateFUTotal('fu-tbody-' + modalId);
            });
        });
    });
    </script>
@endsection
