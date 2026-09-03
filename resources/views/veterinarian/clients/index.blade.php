@extends('layouts.app')

@php
    $title = 'Clients & Patient History';
    $headerTitle = 'Client & Patient Clinical History Database';
    $breadcrumb = 'Clients & Medical History';
@endphp

@section('content')
    <!-- Action Bar & Filter Toolbar -->
    <div class="table-toolbar">
        <div class="search-input-wrapper">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            <input type="text" class="form-control" placeholder="Search by Client Name, Pet Name, Key Code, Phone..." data-table-search="vet-clients-table">
        </div>

        <a href="{{ route('vet.medical.index') }}" class="btn btn-gold">
            <span>🩺 Open Examination Database</span>
        </a>
    </div>

    <!-- Clients & Patient History Table Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-title-group">
                <h3 class="card-title">Client & Pet Clinical Timeline</h3>
                <span class="card-subtitle">Comprehensive medical history, previous visit dates, and patient profiles</span>
            </div>
        </div>

        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table" id="vet-clients-table">
                    <thead>
                        <tr>
                            <th style="min-width: 120px;">Client Key</th>
                            <th style="min-width: 180px;">Owner & Contact</th>
                            <th style="min-width: 320px;">Registered Pets & Visit Timelines</th>
                            <th style="text-align: right; min-width: 130px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($owners as $owner)
                            <tr>
                                <td>
                                    <span class="badge badge-gold" style="font-size: 0.82rem; font-weight: 700;">{{ $owner->client_code }}</span>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 4px;">
                                        Total Pets: <strong>{{ $owner->pets->count() }}</strong>
                                    </div>
                                    <div style="font-size: 0.72rem; color: var(--gold-light); margin-top: 2px;">
                                        Total Visits: <strong>{{ $owner->medicalRecords->count() }}</strong>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--white); font-size: 0.95rem;">{{ $owner->full_name }}</div>
                                    <div style="font-size: 0.82rem; color: var(--text-secondary); margin-top: 2px;">📞 {{ $owner->contact_number }}</div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); max-width: 250px; margin-top: 2px;">📍 {{ $owner->address }}</div>
                                    @if($owner->email)
                                        <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 1px;">✉️ {{ $owner->email }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; flex-direction: column; gap: 0.6rem;">
                                        @forelse($owner->pets as $pet)
                                            @php
                                                $latestVisit = $pet->medicalRecords->first();
                                                $visitCount = $pet->medicalRecords->count();
                                            @endphp
                                            <div style="background: var(--navy-dark); padding: 10px 12px; border-radius: 6px; border: 1px solid var(--navy-border);">
                                                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem;">
                                                    <div>
                                                        <span style="color: var(--gold-primary); font-weight: 800; font-size: 0.95rem;">{{ $pet->name }}</span>
                                                        <span style="font-size: 0.75rem; color: var(--text-muted);">({{ $pet->species }} • {{ $pet->breed }})</span>
                                                        
                                                        <div style="font-size: 0.72rem; color: var(--text-secondary); margin-top: 2px;">
                                                            <span>Sex: <strong>{{ $pet->sex }}</strong></span>
                                                            @if($pet->color)
                                                                • <span>Color: <strong>{{ $pet->color }}</strong></span>
                                                            @endif
                                                            @if($pet->birth_date)
                                                                • <span>🎂 {{ $pet->birth_date->format('M d, Y') }}</span>
                                                            @endif
                                                        </div>

                                                        <!-- Visit Info Badge -->
                                                        <div style="margin-top: 5px; display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                                                            @if($latestVisit)
                                                                <span class="badge badge-navy" style="font-size: 0.72rem;">
                                                                    🗓️ Last Visit: <strong>{{ $latestVisit->visit_date ? $latestVisit->visit_date->format('m/d/Y') : $latestVisit->created_at->format('m/d/Y') }}</strong>
                                                                </span>
                                                                @if($latestVisit->diagnosis)
                                                                    <span class="badge badge-gold" style="font-size: 0.7rem;" title="Latest Diagnosis">
                                                                        Dx: {{ Str::limit($latestVisit->diagnosis, 25) }}
                                                                    </span>
                                                                @endif
                                                                <span style="font-size: 0.72rem; color: var(--text-muted);">
                                                                    ({{ $visitCount }} total {{ Str::plural('visit', $visitCount) }})
                                                                </span>
                                                            @else
                                                                <span class="badge badge-secondary" style="font-size: 0.7rem; color: var(--text-muted);">
                                                                    No visit history yet
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <button type="button" class="btn btn-outline-gold btn-sm" style="padding: 3px 8px; font-size: 0.75rem; white-space: nowrap;"
                                                        onclick="openPetHistoryModal({{ $pet->id }})">
                                                        📋 Full History ({{ $visitCount }})
                                                    </button>
                                                </div>
                                            </div>
                                        @empty
                                            <span style="color: var(--text-muted); font-size: 0.8rem;">No registered pets</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td style="text-align: right;">
                                    <a href="{{ route('vet.clients.show', $owner->id) }}" class="btn btn-navy btn-sm" style="padding: 5px 10px; font-size: 0.8rem;">
                                        📂 Client File
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                                    No clients found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ==================== PET MEDICAL HISTORY MODAL ==================== -->
    <div class="modal-backdrop" id="modal-pet-history">
        <div class="modal-dialog modal-2xl">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">📋</div>
                    <div>
                        <h4 class="modal-title" id="history-modal-pet-name">Pet Medical History</h4>
                        <span style="font-size: 0.75rem; color: var(--gold-light);" id="history-modal-owner-name">Patient Visit Timeline & Records</span>
                    </div>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <div class="modal-body" style="padding: 1.25rem 1.5rem;">
                <!-- Pet Profile Top Strip -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.75rem; background: rgba(11, 25, 44, 0.5); border: 1px solid var(--navy-border); border-radius: var(--radius-sm); padding: 1rem; margin-bottom: 1.25rem;" id="history-modal-pet-details">
                    <!-- Dynamic details -->
                </div>

                <!-- Visits Timeline / Records Table -->
                <div class="table-responsive">
                    <table class="data-table" style="font-size: 0.84rem;">
                        <thead>
                            <tr>
                                <th style="min-width: 105px;">Visit Date</th>
                                <th style="min-width: 90px;">Record Code</th>
                                <th style="min-width: 100px;">Temp / BW</th>
                                <th style="min-width: 170px;">Purpose / Complaint / History</th>
                                <th style="min-width: 150px;">Medication / Treatment</th>
                                <th style="min-width: 130px;">Laboratory</th>
                                <th style="min-width: 110px;">Follow-Up</th>
                                <th style="text-align: right; min-width: 90px;">Case File</th>
                            </tr>
                        </thead>
                        <tbody id="history-modal-records-body">
                            <!-- Dynamic records injected by Javascript -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" data-modal-close>Close</button>
            </div>
        </div>
    </div>

    <!-- Data Store for Javascript Modal Populating -->
    <script>
        const petsData = {
            @foreach($owners as $owner)
                @foreach($owner->pets as $pet)
                    "{{ $pet->id }}": {
                        name: "{{ addslashes($pet->name) }}",
                        pet_code: "{{ $pet->pet_code }}",
                        species: "{{ $pet->species }}",
                        breed: "{{ addslashes($pet->breed ?? 'N/A') }}",
                        sex: "{{ $pet->sex ?? 'N/A' }}",
                        color: "{{ addslashes($pet->color ?? 'N/A') }}",
                        age: "{{ $pet->age ?? 'N/A' }}",
                        birth_date: "{{ $pet->birth_date ? $pet->birth_date->format('M d, Y') : 'Not specified' }}",
                        owner_name: "{{ addslashes($owner->full_name) }}",
                        owner_code: "{{ $owner->client_code }}",
                        contact: "{{ $owner->contact_number }}",
                        records: [
                            @foreach($pet->medicalRecords as $rec)
                                {
                                    id: {{ $rec->id }},
                                    record_code: "{{ $rec->record_code }}",
                                    visit_date: "{{ $rec->visit_date ? $rec->visit_date->format('m/d/Y') : $rec->created_at->format('m/d/Y') }}",
                                    service_type: "{{ ucfirst(str_replace('_', ' ', $rec->service_type)) }}",
                                    temperature: "{{ addslashes($rec->temperature ?? '—') }}",
                                    body_weight: "{{ addslashes($rec->body_weight ?? '—') }}",
                                    history_taking: "{{ addslashes($rec->history_taking ?? '') }}",
                                    medication_treatment: "{{ addslashes($rec->medication_treatment ?? '') }}",
                                    laboratory_notes: "{{ addslashes($rec->laboratory_notes ?? '') }}",
                                    diagnosis: "{{ addslashes($rec->diagnosis ?? '') }}",
                                    attached_file: "{{ $rec->attached_lab_results ? asset($rec->attached_lab_results) : '' }}",
                                    follow_up: "{{ $rec->follow_up_date ? $rec->follow_up_date->format('m/d/Y') : '' }}",
                                    follow_up_notes: "{{ addslashes($rec->follow_up_notes ?? '') }}",
                                    status: "{{ ucfirst($rec->status) }}",
                                    show_url: "{{ route('vet.medical.show', $rec->id) }}"
                                },
                            @endforeach
                        ]
                    },
                @endforeach
            @endforeach
        };

        function openPetHistoryModal(petId) {
            const pet = petsData[petId];
            if (!pet) return;

            document.getElementById('history-modal-pet-name').innerHTML = `🐾 ${pet.name} <span style="font-size: 0.8rem; color: var(--gold-light);">(${pet.pet_code})</span>`;
            document.getElementById('history-modal-owner-name').innerText = `Owner: ${pet.owner_name} (${pet.owner_code}) • Contact: ${pet.contact}`;

            document.getElementById('history-modal-pet-details').innerHTML = `
                <div><span style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Species & Breed</span><div style="font-weight: 700; color: var(--white);">${pet.species} - ${pet.breed}</div></div>
                <div><span style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Sex & Color</span><div style="font-weight: 700; color: var(--white);">${pet.sex} • ${pet.color}</div></div>
                <div><span style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Birthdate & Age</span><div style="font-weight: 700; color: var(--white);">${pet.birth_date} (${pet.age})</div></div>
                <div><span style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Total Encoded Visits</span><div style="font-weight: 800; color: var(--gold-primary); font-size: 1.1rem;">${pet.records.length}</div></div>
            `;

            const tbody = document.getElementById('history-modal-records-body');
            if (pet.records.length === 0) {
                tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; padding: 2rem; color: var(--text-muted);">No consultation or examination records found for this pet.</td></tr>`;
            } else {
                tbody.innerHTML = pet.records.map(r => `
                    <tr>
                        <td>
                            <strong style="color: var(--gold-primary); font-size: 0.9rem;">📅 ${r.visit_date}</strong>
                            <div style="font-size: 0.72rem; color: var(--text-muted);">${r.service_type}</div>
                        </td>
                        <td>
                            <span class="badge badge-navy" style="font-size: 0.72rem;">${r.record_code}</span>
                        </td>
                        <td>
                            <div style="font-size: 0.78rem;">🌡️ ${r.temperature}</div>
                            <div style="font-size: 0.78rem;">⚖️ ${r.body_weight}</div>
                        </td>
                        <td>
                            <div style="color: var(--white); font-size: 0.8rem; line-height: 1.35; white-space: pre-wrap;">${r.history_taking || '<span style="color: var(--text-muted);">—</span>'}</div>
                            ${r.diagnosis ? `<div style="font-size: 0.74rem; color: var(--gold-light); font-weight: 600; margin-top: 3px;">Dx: ${r.diagnosis}</div>` : ''}
                        </td>
                        <td>
                            <div style="color: var(--text-secondary); font-size: 0.8rem; line-height: 1.35; white-space: pre-wrap;">${r.medication_treatment || '<span style="color: var(--text-muted);">—</span>'}</div>
                        </td>
                        <td>
                            <div style="color: var(--text-secondary); font-size: 0.78rem;">${r.laboratory_notes || '<span style="color: var(--text-muted);">—</span>'}</div>
                            ${r.attached_file ? `<a href="${r.attached_file}" target="_blank" class="badge badge-gold" style="font-size: 0.68rem; text-decoration: none; margin-top: 3px; display: inline-block;">📎 File Attached</a>` : ''}
                        </td>
                        <td>
                            ${r.follow_up ? `<div style="color: var(--gold-light); font-weight: 700; font-size: 0.78rem;">🗓️ ${r.follow_up}</div>` : '<span style="color: var(--text-muted); font-size: 0.75rem;">None</span>'}
                            ${r.follow_up_notes ? `<div style="font-size: 0.72rem; color: var(--text-muted);">${r.follow_up_notes}</div>` : ''}
                        </td>
                        <td style="text-align: right;">
                            <a href="${r.show_url}" class="btn btn-navy btn-sm" style="padding: 3px 8px; font-size: 0.75rem;">
                                📂 Open
                            </a>
                        </td>
                    </tr>
                `).join('');
            }

            const modal = document.getElementById('modal-pet-history');
            modal.classList.add('active');
        }
    </script>
@endsection
