@extends('layouts.app')

@php
    $title = 'Client Profile & History';
    $headerTitle = "Client Case File: {$owner->full_name}";
    $breadcrumb = 'Client Clinical History';
@endphp

@section('content')
    <div style="margin-bottom: 1.25rem;">
        <a href="{{ route('vet.clients.index') }}" class="btn btn-ghost btn-sm" style="display: inline-flex; align-items: center; gap: 0.4rem;">
            ← Back to Clients & History List
        </a>
    </div>

    <!-- Top Card: Client Info Overview -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <div class="card-title-group">
                <h3 class="card-title">👤 {{ $owner->full_name }}</h3>
                <span class="card-subtitle">Client Key: <strong>{{ $owner->client_code }}</strong></span>
            </div>
            <a href="{{ route('vet.medical.index') }}" class="btn btn-gold btn-sm">
                + New Patient Examination
            </a>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div>
                    <span style="font-size: 0.72rem; text-transform: uppercase; color: var(--text-muted);">Contact Number</span>
                    <div style="font-weight: 700; color: var(--white); font-size: 1rem;">📞 {{ $owner->contact_number }}</div>
                </div>
                <div>
                    <span style="font-size: 0.72rem; text-transform: uppercase; color: var(--text-muted);">Address</span>
                    <div style="font-weight: 600; color: var(--text-secondary); font-size: 0.9rem;">📍 {{ $owner->address }}</div>
                </div>
                <div>
                    <span style="font-size: 0.72rem; text-transform: uppercase; color: var(--text-muted);">Email</span>
                    <div style="font-size: 0.85rem; color: var(--text-secondary);">{{ $owner->email ?? 'None provided' }}</div>
                </div>
                <div>
                    <span style="font-size: 0.72rem; text-transform: uppercase; color: var(--text-muted);">Total Registered Pets</span>
                    <div style="font-weight: 800; color: var(--gold-primary); font-size: 1.15rem;">🐾 {{ $owner->pets->count() }} Pets</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pet Cards & Individual Visit Histories -->
    <h3 style="font-size: 1.1rem; color: var(--white); font-weight: 800; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
        <span>🐾</span> Registered Patients & Medical Records
    </h3>

    @forelse($owner->pets as $pet)
        <div class="card" style="margin-bottom: 1.5rem; border: 1px solid var(--navy-border);">
            <div class="card-header" style="background: rgba(11, 25, 44, 0.4);">
                <div class="card-title-group">
                    <h3 class="card-title" style="color: var(--gold-primary); font-size: 1.1rem;">
                        {{ $pet->name }}
                        <span style="font-size: 0.8rem; font-weight: normal; color: var(--text-muted);">({{ $pet->pet_code }})</span>
                    </h3>
                    <span class="card-subtitle">
                        {{ $pet->species }} • {{ $pet->breed }} 
                        @if($pet->sex) • {{ $pet->sex }} @endif
                        @if($pet->color) • Color: {{ $pet->color }} @endif
                        @if($pet->birth_date) • 🎂 {{ $pet->birth_date->format('M d, Y') }} @endif
                    </span>
                </div>
                <span class="badge badge-gold" style="font-size: 0.8rem;">
                    {{ $pet->medicalRecords->count() }} {{ Str::plural('Visit', $pet->medicalRecords->count()) }}
                </span>
            </div>

            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="data-table" style="font-size: 0.85rem;">
                        <thead>
                            <tr>
                                <th style="min-width: 110px;">Visit Date</th>
                                <th style="min-width: 90px;">Record Code</th>
                                <th style="min-width: 100px;">Temp / BW</th>
                                <th style="min-width: 180px;">Purpose / Notes / Complaint</th>
                                <th style="min-width: 150px;">Medication / Treatment</th>
                                <th style="min-width: 130px;">Laboratory</th>
                                <th style="min-width: 110px;">Follow-Up</th>
                                <th style="text-align: right; min-width: 90px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pet->medicalRecords as $record)
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: var(--gold-primary); font-size: 0.9rem;">
                                            📅 {{ $record->visit_date ? $record->visit_date->format('m/d/Y') : $record->created_at->format('m/d/Y') }}
                                        </div>
                                        <div style="font-size: 0.72rem; color: var(--text-muted);">{{ ucfirst(str_replace('_', ' ', $record->service_type)) }}</div>
                                    </td>
                                    <td>
                                        <span class="badge badge-navy" style="font-size: 0.72rem;">{{ $record->record_code }}</span>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.78rem;">🌡️ {{ $record->temperature ?? '—' }}</div>
                                        <div style="font-size: 0.78rem;">⚖️ {{ $record->body_weight ?? '—' }}</div>
                                    </td>
                                    <td>
                                        <div style="color: var(--white); font-size: 0.82rem; line-height: 1.4; white-space: pre-wrap;">{{ $record->history_taking ?? '—' }}</div>
                                        @if($record->diagnosis)
                                            <div style="font-size: 0.75rem; color: var(--gold-light); font-weight: 600; margin-top: 3px;">
                                                Dx: {{ $record->diagnosis }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="color: var(--text-secondary); font-size: 0.82rem; line-height: 1.4; white-space: pre-wrap;">{{ $record->medication_treatment ?? '—' }}</div>
                                        @if($record->prescription)
                                            <div style="margin-top: 3px;">
                                                <a href="{{ route('vet.prescriptions.print', $record->prescription->id) }}" target="_blank" class="badge badge-gold" style="text-decoration: none;">
                                                    💊 {{ $record->prescription->prescription_code }}
                                                </a>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="color: var(--text-secondary); font-size: 0.8rem;">{{ $record->laboratory_notes ?? '—' }}</div>
                                        @if($record->attached_lab_results)
                                            <div style="margin-top: 3px;">
                                                <a href="{{ asset($record->attached_lab_results) }}" target="_blank" class="badge badge-gold" style="text-decoration: none; font-size: 0.7rem;">
                                                    📎 Attached File
                                                </a>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($record->follow_up_date)
                                            <div style="font-weight: 700; color: var(--gold-light); font-size: 0.8rem;">
                                                🗓️ {{ $record->follow_up_date->format('m/d/Y') }}
                                            </div>
                                        @endif
                                        @if($record->follow_up_notes)
                                            <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $record->follow_up_notes }}</div>
                                        @endif
                                        @if(!$record->follow_up_date && !$record->follow_up_notes)
                                            <span style="color: var(--text-muted); font-size: 0.75rem;">None</span>
                                        @endif
                                    </td>
                                    <td style="text-align: right;">
                                        <a href="{{ route('vet.medical.show', $record->id) }}" class="btn btn-navy btn-sm" style="padding: 3px 8px; font-size: 0.75rem;">
                                            📂 Case File
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 1.5rem; color: var(--text-muted);">
                                        No clinical examination records for this pet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="card">
            <div class="card-body" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                No pets registered for this client.
            </div>
        </div>
    @endforelse
@endsection
