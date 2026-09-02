@extends('layouts.app')

@php
    $title = 'Veterinary Services';
    $headerTitle = 'Veterinary Services & Medical Records';
    $breadcrumb = 'Medical Examinations';
@endphp

@section('content')
    <!-- Action Bar & Filter Toolbar -->
    <div class="table-toolbar">
        <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <div class="search-input-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                <input type="text" class="form-control" placeholder="Search record code, pet, owner..." data-table-search="records-table">
            </div>

            <!-- Service Filter Pills -->
            <div style="display: flex; gap: 0.35rem;">
                <a href="{{ route('admin.veterinary.index') }}" class="btn btn-sm {{ !request('service_type') ? 'btn-gold' : 'btn-navy' }}">All</a>
                <a href="{{ route('admin.veterinary.index', ['service_type' => 'consultation']) }}" class="btn btn-sm {{ request('service_type') === 'consultation' ? 'btn-gold' : 'btn-navy' }}">Consultation</a>
                <a href="{{ route('admin.veterinary.index', ['service_type' => 'follow_up']) }}" class="btn btn-sm {{ request('service_type') === 'follow_up' ? 'btn-gold' : 'btn-navy' }}">Follow Up</a>
                <a href="{{ route('admin.veterinary.index', ['service_type' => 'wellness']) }}" class="btn btn-sm {{ request('service_type') === 'wellness' ? 'btn-gold' : 'btn-navy' }}">Wellness</a>
            </div>
        </div>

        <button type="button" class="btn btn-gold" data-modal-target="modal-new-exam">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            <span>New Examination / Consultation</span>
        </button>
    </div>

    <!-- Records Table Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-title-group">
                <h3 class="card-title">Medical Record Database</h3>
                <span class="card-subtitle">Consultation, Follow-up, Wellness checkups, and Lab Results</span>
            </div>
        </div>

        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table" id="records-table">
                    <thead>
                        <tr>
                            <th>Record Code</th>
                            <th>Patient & Owner</th>
                            <th>Service Type</th>
                            <th>Vitals (Weight / Temp / Score)</th>
                            <th>Diagnosis & Notes</th>
                            <th>Prescription</th>
                            <th>Fee & Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($records as $record)
                            <tr>
                                <td>
                                    <strong style="color: var(--gold-primary);">{{ $record->record_code }}</strong>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $record->created_at->format('M d, Y h:i A') }}</div>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--white);">{{ $record->pet->name ?? 'N/A' }}</div>
                                    <div style="font-size: 0.75rem; color: var(--gold-light);">{{ $record->pet->species ?? '' }} ({{ $record->pet->breed ?? '' }})</div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">Owner: {{ $record->owner->full_name ?? 'N/A' }} ({{ $record->owner->client_code ?? '' }})</div>
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $record->service_type)) }}</span>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 3px;">Dr. {{ $record->veterinarian->name ?? 'On Duty' }}</div>
                                </td>
                                <td>
                                    <div style="font-size: 0.8rem;">⚖️ {{ $record->body_weight ?? 'N/A' }}</div>
                                    <div style="font-size: 0.8rem;">🌡️ {{ $record->temperature ?? 'N/A' }}</div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">Score: {{ $record->body_score ?? 'N/A' }}</div>
                                </td>
                                <td style="max-width: 250px;">
                                    <div style="font-weight: 600; color: var(--white); font-size: 0.85rem;">{{ Str::limit($record->diagnosis ?? 'No diagnosis entered', 60) }}</div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ Str::limit($record->history_taking, 50) }}</div>
                                    @if($record->attached_lab_results)
                                        <div style="margin-top: 4px;">
                                            <a href="{{ asset($record->attached_lab_results) }}" target="_blank" class="badge badge-gold" style="text-decoration: none;">
                                                🔬 Lab Photo Attached
                                            </a>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($record->prescription)
                                        <div style="display: flex; flex-direction: column; gap: 4px;">
                                            <a href="{{ route('admin.prescriptions.print', $record->prescription->id) }}" target="_blank" class="badge badge-gold" style="text-decoration: none;" title="Print Rx Pad">
                                                💊 {{ $record->prescription->prescription_code }}
                                            </a>
                                            <button type="button" class="btn btn-ghost btn-sm" style="padding: 1px 6px; font-size: 0.72rem; color: var(--gold-light); text-align: left;"
                                                data-modal-target="modal-edit-prescription-admin"
                                                data-action-url="{{ route('admin.prescriptions.update', $record->prescription->id) }}"
                                                data-field-rx_details="{{ $record->prescription->rx_details }}"
                                                data-field-instructions="{{ $record->prescription->instructions }}"
                                                data-field-body_weight="{{ $record->prescription->body_weight }}">
                                                ✏️ Edit Rx
                                            </button>
                                        </div>
                                    @else
                                        <button type="button" class="btn btn-outline-gold btn-sm" style="padding: 3px 9px; font-size: 0.75rem;"
                                            data-modal-target="modal-add-prescription-admin"
                                            data-field-medical_record_id="{{ $record->id }}"
                                            data-field-pet_id="{{ $record->pet_id }}"
                                            data-field-owner_id="{{ $record->owner_id }}"
                                            data-field-patient_display="{{ $record->pet->name ?? 'N/A' }} ({{ $record->pet->species ?? '' }})"
                                            data-field-owner_display="{{ $record->owner->full_name ?? 'N/A' }}"
                                            data-field-diagnosis_display="{{ $record->diagnosis ?? 'Clinical Examination' }}"
                                            data-field-body_weight="{{ $record->body_weight }}">
                                            ➕ Add Rx
                                        </button>
                                    @endif
                                </td>
                                <td>
                                    <div><strong>₱{{ number_format($record->service_fee, 2) }}</strong></div>
                                    <span class="badge {{ $record->status === 'billed' ? 'badge-success' : 'badge-warning' }}">
                                        {{ ucfirst($record->status) }}
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 0.4rem;">
                                        <!-- Edit Modal Trigger -->
                                        <button type="button" class="btn btn-navy btn-sm"
                                            data-modal-target="modal-edit-exam"
                                            data-action-url="{{ route('admin.veterinary.update', $record->id) }}"
                                            data-field-body_weight="{{ $record->body_weight }}"
                                            data-field-temperature="{{ $record->temperature }}"
                                            data-field-body_score="{{ $record->body_score }}"
                                            data-field-history_taking="{{ $record->history_taking }}"
                                            data-field-diagnosis="{{ $record->diagnosis }}"
                                            data-field-veterinarians_notes="{{ $record->veterinarians_notes }}"
                                            data-field-service_fee="{{ $record->service_fee }}"
                                            data-field-status="{{ $record->status }}">
                                            Edit
                                        </button>

                                        <!-- Delete Record Trigger -->
                                        <button type="button" class="btn btn-danger btn-sm"
                                            data-modal-target="modal-delete-exam"
                                            data-action-url="{{ route('admin.veterinary.destroy', $record->id) }}"
                                            data-field-target_name="{{ $record->record_code }} ({{ $record->pet->name ?? '' }})">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ==================== MODAL 1: NEW VETERINARY EXAMINATION & PRESCRIPTION ==================== -->
    <div class="modal-backdrop" id="modal-new-exam">
        <div class="modal-dialog modal-lg">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">🩺</div>
                    <div>
                        <h4 class="modal-title">New Examination & Medical Record</h4>
                        <span style="font-size: 0.75rem; color: var(--gold-light);">Code: <strong>{{ $generatedCode }}</strong> (Flowchart: Medical Record + Billing Data Base)</span>
                    </div>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form action="{{ route('admin.veterinary.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <!-- Client & Pet selection (Flowchart: Enter Code owner + Pet Key Code or Register New) -->
                    <x-client-pet-selector :owners="$owners" />

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Service Type <span class="req">*</span></label>
                            <select name="service_type" class="form-select" required>
                                <option value="consultation">Consultation</option>
                                <option value="follow_up">Follow Up</option>
                                <option value="wellness">Wellness</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Attending Veterinarian</label>
                            <select name="veterinarian_id" class="form-select">
                                @foreach($veterinarians as $vet)
                                    <option value="{{ $vet->id }}">{{ $vet->name }} ({{ $vet->license_no ?? 'PRC-VET' }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Flowchart Specified Fields: Vitals & History -->
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Body Weight</label>
                            <input type="text" name="body_weight" class="form-control" placeholder="e.g. 12.4 kg">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Temperature</label>
                            <input type="text" name="temperature" class="form-control" placeholder="e.g. 38.5 °C">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Body Score</label>
                            <input type="text" name="body_score" class="form-control" placeholder="e.g. 3/5 Ideal">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">History Taking {text type}</label>
                        <textarea name="history_taking" class="form-control" rows="2" placeholder="Describe symptoms, diet history, duration, previous treatments..."></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Diagnosis {text type}</label>
                        <textarea name="diagnosis" class="form-control" rows="2" placeholder="Clinical assessment / tentative / final diagnosis..."></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Veterinarian's Notes {text type}</label>
                        <textarea name="veterinarians_notes" class="form-control" rows="2" placeholder="Physical examination observations, doctor advice..."></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Attached Laboratory Results (Pictures saved to public/uploads/lab_results/)</label>
                        <input type="file" name="lab_results" class="form-control" accept="image/*">
                    </div>


                    <!-- Flowchart: Billing Fee -->
                    <div class="form-group" style="margin-top: 1.25rem;">
                        <label class="form-label">Consultation / Service Fee (₱) <span class="req">*</span></label>
                        <input type="number" step="0.01" name="service_fee" class="form-control" value="450.00" required>
                        <span style="font-size: 0.72rem; color: var(--text-muted);">Will be automatically queued into the Central Billing Data Base.</span>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold">Save Examination & Push to Billing</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL 2: EDIT MEDICAL RECORD ==================== -->
    <div class="modal-backdrop" id="modal-edit-exam">
        <div class="modal-dialog modal-lg">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">✏️</div>
                    <h4 class="modal-title">Update Examination Record</h4>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form method="POST" action="" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Body Weight</label>
                            <input type="text" name="body_weight" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Temperature</label>
                            <input type="text" name="temperature" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Body Score</label>
                            <input type="text" name="body_score" class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">History Taking</label>
                        <textarea name="history_taking" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Diagnosis</label>
                        <textarea name="diagnosis" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Veterinarian's Notes</label>
                        <textarea name="veterinarians_notes" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Service Fee (₱)</label>
                            <input type="number" step="0.01" name="service_fee" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="ongoing">Ongoing</option>
                                <option value="completed">Completed</option>
                                <option value="billed">Billed</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Update Attached Lab Photo (saved to public/)</label>
                        <input type="file" name="lab_results" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold">Update Medical Record</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL 3: DELETE MEDICAL RECORD ==================== -->
    <div class="modal-backdrop" id="modal-delete-exam">
        <div class="modal-dialog modal-danger modal-sm">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">🗑️</div>
                    <h4 class="modal-title">Delete Medical Record?</h4>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p style="color: var(--text-secondary); font-size: 0.9rem;">
                        Are you sure you want to permanently delete record <strong style="color: var(--white);" data-bind="target_name"></strong>?
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm Delete</button>
                </div>
            </form>
        </div>
    </div>
    <!-- ==================== MODAL 4: ADD PRESCRIPTION ==================== -->
    <div class="modal-backdrop" id="modal-add-prescription-admin">
        <div class="modal-dialog modal-lg">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">💊</div>
                    <div>
                        <h4 class="modal-title">Generate Prescription (Rx)</h4>
                        <span style="font-size: 0.75rem; color: var(--gold-light);">Official doctor medical prescription linked to clinical case</span>
                    </div>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form action="{{ route('admin.prescriptions.store') }}" method="POST">
                @csrf
                <input type="hidden" name="medical_record_id" value="">
                <input type="hidden" name="pet_id" value="">
                <input type="hidden" name="owner_id" value="">

                <div class="modal-body">
                    <!-- Case Summary Banner -->
                    <div style="background: var(--navy-dark); border: 1px solid var(--navy-border); padding: 0.85rem 1rem; border-radius: var(--radius-sm); margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                        <div>
                            <span style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Patient & Owner</span>
                            <div style="font-weight: 700; color: var(--white); font-size: 0.92rem;">
                                <span data-bind="patient_display"></span> • <span style="color: var(--gold-light);" data-bind="owner_display"></span>
                            </div>
                        </div>
                        <div>
                            <span style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Diagnosis / Case</span>
                            <div style="font-weight: 600; color: var(--white); font-size: 0.85rem;" data-bind="diagnosis_display"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="admin_rx_weight">Patient Weight (Reference for dosage)</label>
                        <input type="text" name="body_weight" id="admin_rx_weight" class="form-control" placeholder="e.g. 5.2 kg">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="admin_rx_details">Prescription Details (Medications, Strength & Dosage) <span class="req">*</span></label>
                        <textarea name="rx_details" id="admin_rx_details" rows="5" class="form-control" required
                            placeholder="e.g.&#10;1. Amoxicillin + Clavulanic Acid 250mg - 1 tab BID for 7 days&#10;2. Meloxicam 0.5mg/ml - 0.4ml SID after meals for 3 days&#10;3. Eye drop (Tobramycin) - 2 drops both eyes TID for 5 days"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="admin_rx_instructions">Special Instructions / Precautions / Advice</label>
                        <textarea name="instructions" id="admin_rx_instructions" rows="3" class="form-control"
                            placeholder="e.g. Administer with food after meals. Watch for vomiting. Follow up after 7 days."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold">💊 Issue & Attach Prescription</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL 5: EDIT PRESCRIPTION ==================== -->
    <div class="modal-backdrop" id="modal-edit-prescription-admin">
        <div class="modal-dialog modal-lg">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">✏️</div>
                    <h4 class="modal-title">Edit Prescription (Rx)</h4>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Patient Weight</label>
                        <input type="text" name="body_weight" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Prescription Details <span class="req">*</span></label>
                        <textarea name="rx_details" rows="5" class="form-control" required></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Usage Instructions / Precautions</label>
                        <textarea name="instructions" rows="3" class="form-control"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold">Update Prescription</button>
                </div>
            </form>
        </div>
    </div>
@endsection
