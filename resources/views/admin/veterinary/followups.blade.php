@extends('layouts.app')

@php
    $title = 'Incoming Follow-up Schedule';
    $headerTitle = 'Incoming Follow-up Schedules & Monitoring';
    $breadcrumb = 'Clinic Services / Follow-ups';
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
            <div class="stat-desc">Scheduled for visit today</div>
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
            <div class="stat-desc">Requires client callback</div>
        </div>
    </div>

    <!-- Action Bar & Filter Toolbar -->
    <div class="table-toolbar">
        <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <div class="search-input-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                <input type="text" class="form-control" placeholder="Search patient, owner, contact, notes..." data-table-search="followups-table">
            </div>

            <!-- Date Filter Pills -->
            <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                <a href="{{ route('admin.veterinary.followups') }}" class="btn btn-sm {{ !request('filter') || request('filter') === 'all' ? 'btn-gold' : 'btn-navy' }}">
                    All ({{ $totalCount }})
                </a>
                <a href="{{ route('admin.veterinary.followups', ['filter' => 'today']) }}" class="btn btn-sm {{ request('filter') === 'today' ? 'btn-gold' : 'btn-navy' }}">
                    📅 Today ({{ $todayCount }})
                </a>
                <a href="{{ route('admin.veterinary.followups', ['filter' => 'this_week']) }}" class="btn btn-sm {{ request('filter') === 'this_week' ? 'btn-gold' : 'btn-navy' }}">
                    This Week ({{ $thisWeekCount }})
                </a>
                <a href="{{ route('admin.veterinary.followups', ['filter' => 'upcoming']) }}" class="btn btn-sm {{ request('filter') === 'upcoming' ? 'btn-gold' : 'btn-navy' }}">
                    Upcoming ({{ $upcomingCount }})
                </a>
                <a href="{{ route('admin.veterinary.followups', ['filter' => 'overdue']) }}" class="btn btn-sm {{ request('filter') === 'overdue' ? 'btn-danger' : 'btn-navy' }}">
                    ⚠️ Overdue ({{ $overdueCount }})
                </a>
            </div>
        </div>

        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('admin.veterinary.index') }}" class="btn btn-navy">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                <span>All Medical Records</span>
            </a>
        </div>
    </div>

    <!-- Follow-ups Table Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-title-group">
                <h3 class="card-title">Incoming Patient Follow-ups</h3>
                <span class="card-subtitle">Monitor scheduled checkups, catheter removals, post-op monitoring, and repeat laboratory tests</span>
            </div>
            <span class="badge badge-gold" style="font-size: 0.78rem;">
                {{ $followUps->count() }} Records Found
            </span>
        </div>

        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table" id="followups-table">
                    <thead>
                        <tr>
                            <th style="min-width: 140px;">Follow-up Schedule</th>
                            <th style="min-width: 170px;">Patient & Pet Info</th>
                            <th style="min-width: 170px;">Client / Owner Contact</th>
                            <th style="min-width: 200px;">Purpose & Clinical Notes</th>
                            <th style="min-width: 150px;">Origin Examination</th>
                            <th class="no-sort" data-orderable="false" style="text-align: right; min-width: 140px;">Actions</th>
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
                                    <div style="font-weight: 800; color: var(--white); font-size: 0.96rem;">
                                        🐾 {{ $record->pet->name ?? 'N/A' }}
                                    </div>
                                    <div style="font-size: 0.78rem; color: var(--gold-light);">
                                        {{ $record->pet->species ?? '' }} • {{ $record->pet->breed ?? 'Mixed' }}
                                        @if($record->pet?->sex) ({{ $record->pet->sex }}) @endif
                                    </div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">
                                        ID: {{ $record->pet->pet_code ?? 'N/A' }}
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--white); font-size: 0.9rem;">
                                        👤 {{ $record->owner->full_name ?? 'N/A' }}
                                    </div>
                                    @if($record->owner?->contact_number)
                                        <div style="margin-top: 4px;">
                                            <a href="tel:{{ $record->owner->contact_number }}" class="btn btn-sm btn-navy" style="font-size: 0.75rem; padding: 2px 8px; display: inline-flex; align-items: center; gap: 0.3rem;">
                                                📞 {{ $record->owner->contact_number }}
                                            </a>
                                        </div>
                                    @else
                                        <span style="font-size: 0.75rem; color: var(--text-muted);">No contact number</span>
                                    @endif
                                    @if($record->owner?->address)
                                        <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">
                                            📍 {{ Str::limit($record->owner->address, 25) }}
                                        </div>
                                    @endif
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
                                        <!-- Quick Edit/Reschedule Schedule -->
                                        <button type="button" class="btn btn-sm btn-navy" title="Reschedule or Update Note" data-modal-target="modal-edit-fu-{{ $record->id }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 14px; height: 14px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            <span>Reschedule</span>
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
                                            When you set a follow-up date during a New Examination, it will automatically appear here for real-time tracking!
                                        @endif
                                    </p>
                                    <div style="margin-top: 1rem;">
                                        <a href="{{ route('admin.veterinary.index') }}" class="btn btn-gold btn-sm">
                                            Go to Veterinary Services
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

    <!-- MODALS CONTAINER (Placed outside of table for proper DOM rendering) -->
    @foreach($followUps as $record)
        <!-- RESCHEDULE MODAL -->
        <div class="modal-backdrop" id="modal-edit-fu-{{ $record->id }}">
            <div class="modal-dialog modal-md">
                <div class="modal-header">
                    <h4 class="modal-title">🗓️ Update Follow-Up Schedule</h4>
                    <button type="button" class="modal-close" data-modal-close>&times;</button>
                </div>
                <form action="{{ route('admin.veterinary.followup.update', $record->id) }}" method="POST">
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
@endsection
