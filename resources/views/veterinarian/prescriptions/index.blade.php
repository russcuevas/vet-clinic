@extends('layouts.app')

@php
    $title = 'Prescription Pad';
    $headerTitle = 'Veterinary Prescription Pad';
    $breadcrumb = 'Prescriptions';
@endphp

@section('content')
    <div class="table-toolbar">
        <div class="search-input-wrapper">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            <input type="text" class="form-control" placeholder="Search Rx code, pet, owner..." data-table-search="rx-table">
        </div>

        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <a href="{{ route('vet.medical.index') }}" class="btn btn-navy">
                <span>🩺 Prescribe from Medical Cases</span>
            </a>
            <button type="button" class="btn btn-gold" data-modal-target="modal-new-rx">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                <span>Standalone Rx</span>
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title-group">
                <h3 class="card-title">Issued Prescriptions</h3>
                <span class="card-subtitle">Official doctor medical orders</span>
            </div>
        </div>

        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table" id="rx-table">
                    <thead>
                        <tr>
                            <th>Rx Code</th>
                            <th>Date Issued</th>
                            <th>Pet Name</th>
                            <th>Owner Name</th>
                            <th>Attending Doctor</th>
                            <th>Rx Medication Details</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($prescriptions as $rx)
                            <tr>
                                <td><strong style="color: var(--gold-primary);">{{ $rx->prescription_code }}</strong></td>
                                <td>{{ $rx->date_issued->format('M d, Y') }}</td>
                                <td>
                                    <strong style="color: var(--white);">{{ $rx->pet->name ?? 'N/A' }}</strong>
                                    <div style="font-size: 0.75rem; color: var(--gold-light);">{{ $rx->pet->species ?? '' }} ({{ $rx->pet->breed ?? '' }})</div>
                                </td>
                                <td>{{ $rx->owner->full_name ?? 'N/A' }}</td>
                                <td>
                                    <div>{{ $rx->veterinarian_name }}</div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">Lic: {{ $rx->license_no ?? 'PRC-VET' }}</div>
                                </td>
                                <td style="max-width: 250px;">
                                    <div style="font-size: 0.8rem; color: var(--text-secondary); white-space: pre-line;">{{ Str::limit($rx->rx_details, 60) }}</div>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 0.35rem;">
                                        @if($rx->medical_record_id)
                                            <a href="{{ route('vet.medical.show', $rx->medical_record_id) }}" class="btn btn-navy btn-sm" title="View Associated Medical Case">
                                                📂 Case
                                            </a>
                                        @endif
                                        <a href="{{ route('vet.prescriptions.print', $rx->id) }}" target="_blank" class="btn btn-gold btn-sm">
                                            🖨️ Print Rx
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal: Generate Prescription -->
    <div class="modal-backdrop" id="modal-new-rx">
        <div class="modal-dialog modal-lg">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">💊</div>
                    <div>
                        <h4 class="modal-title">Generate Prescription</h4>
                        <span style="font-size: 0.75rem; color: var(--gold-light);">Rx Code: <strong>{{ $generatedCode }}</strong></span>
                    </div>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form action="{{ route('vet.prescriptions.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Select Client / Owner <span class="req">*</span></label>
                            <select name="owner_id" id="select_owner_id" class="form-select" required>
                                <option value="">-- Choose Owner --</option>
                                @foreach($owners as $owner)
                                    <option value="{{ $owner->id }}">{{ $owner->client_code }} - {{ $owner->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Select Pet <span class="req">*</span></label>
                            <select name="pet_id" id="select_pet_id" class="form-select" required>
                                <option value="">-- Choose Pet --</option>
                                @foreach($owners as $owner)
                                    @foreach($owner->pets as $pet)
                                        <option value="{{ $pet->id }}" data-owner-id="{{ $owner->id }}">
                                            {{ $pet->pet_code }} - {{ $pet->name }} ({{ $pet->species }}, {{ $pet->breed }})
                                        </option>
                                    @endforeach
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Body Weight</label>
                        <input type="text" name="body_weight" class="form-control" placeholder="e.g. 10.5 kg">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Rx: Prescribed Medicines & Dosage <span class="req">*</span></label>
                        <textarea name="rx_details" class="form-control" rows="4" placeholder="1. Amoxicillin Trihydrate 250mg&#10;   Sig: 1 capsule every 12 hours for 7 days.&#10;&#10;2. Multivitamins with Zinc Syrup&#10;   Sig: 5mL orally once daily for 14 days." required></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">General Advice / Instructions</label>
                        <textarea name="instructions" class="form-control" rows="2" placeholder="Dietary instructions, precautions, return for checkup if symptoms persist..."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold">Generate & Print Prescription</button>
                </div>
            </form>
        </div>
    </div>
@endsection
