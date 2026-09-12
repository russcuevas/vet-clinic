@extends('layouts.app')

@php
    $title = 'Veterinarian Portal';
    $headerTitle = 'Veterinarian Clinical Desk';
    $breadcrumb = 'Doctor Workstation';
@endphp

@section('content')
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Today's Consultations</span>
                <div class="stat-icon-wrapper">🩺</div>
            </div>
            <div class="stat-value">{{ $stats['today_consultations'] }}</div>
            <div class="stat-desc">Primary diagnostic checkups</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Today's Follow-ups</span>
                <div class="stat-icon-wrapper" style="color: var(--gold-light);">🔄</div>
            </div>
            <div class="stat-value">{{ $stats['today_followups'] }}</div>
            <div class="stat-desc">Post-treatment re-evaluations</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Wellness & Vaccines</span>
                <div class="stat-icon-wrapper" style="color: var(--success);">💉</div>
            </div>
            <div class="stat-value">{{ $stats['today_wellness'] }}</div>
            <div class="stat-desc">Preventive care & immunizations</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Grooming Sessions</span>
                <div class="stat-icon-wrapper" style="color: var(--gold-primary);">✂️</div>
            </div>
            <div class="stat-value">{{ $stats['total_grooming'] }}</div>
            <div class="stat-desc">Active, queued & completed</div>
        </div>
    </div>

    <!-- Doctor Action Bar -->
    <div style="display: flex; gap: 0.75rem; margin-bottom: 1.75rem; flex-wrap: wrap;">
        <a href="{{ route('vet.medical.index') }}" class="btn btn-gold">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            <span>+ New Patient Examination</span>
        </a>
        <a href="{{ route('vet.grooming.index') }}" class="btn btn-navy">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879a3 3 0 11-4.242-4.242L10.758 7.758a3 3 0 014.242 4.242z" /></svg>
            <span>Grooming Services</span>
        </a>
    </div>

    <!-- Active Consultation Queue (Patients Waiting / In-Exam) -->
    @if(isset($activeQueue) && $activeQueue->count() > 0)
        <div class="card" style="border: 1.5px solid var(--gold-border); background: linear-gradient(180deg, rgba(245, 186, 49, 0.08) 0%, var(--navy-dark) 100%); margin-bottom: 1.5rem;">
            <div class="card-header" style="background: rgba(245, 186, 49, 0.12); border-bottom: 1px solid var(--gold-border);">
                <div class="card-title-group">
                    <h3 class="card-title" style="color: var(--gold-light); display: flex; align-items: center; gap: 0.5rem;">
                        <span>🩺</span> Active Patient Consultation Queue ({{ $activeQueue->count() }})
                    </h3>
                    <span class="card-subtitle">Checked-in patients from Reception desk waiting for physical examination and diagnosis</span>
                </div>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Case Code</th>
                                <th>Patient / Pet</th>
                                <th>Owner / Contact</th>
                                <th>Purpose / Complaint</th>
                                <th>Service</th>
                                <th>Status</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activeQueue as $queueItem)
                                <tr>
                                    <td><strong style="color: var(--gold-primary);">{{ $queueItem->record_code }}</strong></td>
                                    <td>
                                        <div style="font-weight: 700; color: var(--white);">{{ $queueItem->pet->name ?? 'N/A' }}</div>
                                        <div style="font-size: 0.72rem; color: var(--gold-light);">{{ $queueItem->pet->species ?? '' }} ({{ $queueItem->pet->breed ?? '' }})</div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--white);">{{ $queueItem->owner->full_name ?? 'N/A' }}</div>
                                        <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $queueItem->owner->contact_number ?? '' }}</div>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.82rem; color: var(--text-secondary); max-width: 250px;">
                                            {{ Str::limit($queueItem->history_taking ?? 'Patient arrived for consultation', 60) }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-navy">{{ ucfirst(str_replace('_', ' ', $queueItem->service_type)) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-warning">Waiting for Vet Exam</span>
                                    </td>
                                    <td style="text-align: right;">
                                        <a href="{{ route('vet.medical.show', $queueItem->id) }}" class="btn btn-gold btn-sm" style="font-weight: 700;">
                                            🩺 Examine & Prescribe →
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Recent Patients Under Care -->
    <div class="card">
        <div class="card-header">
            <div class="card-title-group">
                <h3 class="card-title">Recent Clinical Records</h3>
                <span class="card-subtitle">Consultation, Follow-up, and Wellness checkups</span>
            </div>
            <a href="{{ route('vet.medical.index') }}" class="btn btn-ghost btn-sm">Full Medical Database →</a>
        </div>

        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Record Code</th>
                            <th>Patient / Pet</th>
                            <th>Owner Name</th>
                            <th>Service & Status</th>
                            <th>Vitals</th>
                            <th>Diagnosis</th>
                            <th>Prescription</th>
                            <th style="text-align: right;">View</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentRecords as $record)
                            <tr>
                                <td><strong style="color: var(--gold-primary);">{{ $record->record_code }}</strong></td>
                                <td>
                                    <div style="font-weight: 700; color: var(--white);">{{ $record->pet->name ?? 'N/A' }}</div>
                                    <div style="font-size: 0.72rem; color: var(--gold-light);">{{ $record->pet->species ?? '' }} ({{ $record->pet->breed ?? '' }})</div>
                                </td>
                                <td>{{ $record->owner->full_name ?? 'N/A' }}</td>
                                <td>
                                    <div style="display: flex; flex-direction: column; gap: 3px; align-items: flex-start;">
                                        <span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $record->service_type)) }}</span>
                                        @if($record->status === 'ongoing')
                                            <span class="badge badge-warning" style="font-size: 0.65rem;">Ongoing</span>
                                        @elseif($record->status === 'completed')
                                            <span class="badge badge-info" style="font-size: 0.65rem;">At Cashier</span>
                                        @elseif($record->status === 'billed')
                                            <span class="badge badge-success" style="font-size: 0.65rem;">Paid / Billed</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size: 0.78rem;">⚖️ {{ $record->body_weight ?? 'N/A' }}</div>
                                    <div style="font-size: 0.78rem;">🌡️ {{ $record->temperature ?? 'N/A' }}</div>
                                </td>
                                <td style="max-width: 250px;">
                                    <div style="font-size: 0.82rem; color: var(--white); font-weight: 600;">{{ Str::limit($record->diagnosis, 45) }}</div>
                                </td>
                                <td>
                                    @if($record->prescription)
                                        <a href="{{ route('vet.prescriptions.print', $record->prescription->id) }}" target="_blank" class="badge badge-gold" style="text-decoration: none;">
                                            💊 {{ $record->prescription->prescription_code }}
                                        </a>
                                    @else
                                        <a href="{{ route('vet.medical.show', $record->id) }}" class="btn btn-outline-gold btn-sm" style="padding: 2px 7px; font-size: 0.72rem;">
                                            + Add Rx
                                        </a>
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    <a href="{{ route('vet.medical.show', $record->id) }}" class="btn btn-navy btn-sm">
                                        📂 Case File
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Grooming Sessions -->
    <div class="card">
        <div class="card-header">
            <div class="card-title-group">
                <h3 class="card-title">Recent Grooming Queue & Sessions</h3>
                <span class="card-subtitle">Styling, bathing, and care status</span>
            </div>
            <a href="{{ route('vet.grooming.index') }}" class="btn btn-ghost btn-sm">Full Grooming Center →</a>
        </div>

        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Patient / Pet</th>
                            <th>Owner</th>
                            <th>Style</th>
                            <th>Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentGrooming as $groom)
                            <tr>
                                <td><strong style="color: var(--gold-primary);">{{ $groom->grooming_code }}</strong></td>
                                <td>
                                    <div style="font-weight: 700; color: var(--white);">{{ $groom->pet->name ?? 'N/A' }}</div>
                                    <div style="font-size: 0.72rem; color: var(--gold-light);">{{ $groom->pet->species ?? '' }} ({{ $groom->pet->breed ?? '' }})</div>
                                </td>
                                <td>{{ $groom->owner->full_name ?? 'N/A' }}</td>
                                <td><strong>{{ $groom->style }}</strong></td>
                                <td><strong>₱{{ number_format($groom->price, 2) }}</strong></td>
                                <td>
                                    <span class="badge {{ $groom->status === 'completed' || $groom->status === 'billed' ? 'badge-success' : ($groom->status === 'in_progress' ? 'badge-info' : 'badge-warning') }}">
                                        {{ ucfirst(str_replace('_', ' ', $groom->status)) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
