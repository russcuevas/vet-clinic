@extends('layouts.app')

@php
    $title = 'Case ' . $record->record_code;
    $headerTitle = 'Clinical Case File: ' . $record->record_code;
    $breadcrumb = 'Patient Case';
@endphp

@section('content')
    <div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
        <a href="{{ route('vet.medical.index') }}" class="btn btn-navy btn-sm">← Back to Medical Records</a>
        @if($record->prescription)
            <a href="{{ route('vet.prescriptions.print', $record->prescription->id) }}" target="_blank" class="btn btn-gold btn-sm">
                🖨️ Print Prescription
            </a>
        @endif
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; align-items: start;">
        <!-- Left: Case File Breakdown -->
        <div>
            <!-- Vitals & Consultation Header -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title-group">
                        <h3 class="card-title">Case {{ $record->record_code }} - {{ ucfirst(str_replace('_', ' ', $record->service_type)) }}</h3>
                        <span class="card-subtitle">Examined on {{ $record->created_at->format('F d, Y h:i A') }}</span>
                    </div>
                    <span class="badge badge-gold">{{ ucfirst($record->status) }}</span>
                </div>

                <div class="card-body">
                    <!-- Flowchart: Vitals -->
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; background: var(--navy-dark); padding: 1.25rem; border-radius: var(--radius-sm); border: 1px solid var(--navy-border); margin-bottom: 1.5rem;">
                        <div>
                            <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); display: block;">Body Weight</span>
                            <strong style="font-size: 1.15rem; color: var(--white);">{{ $record->body_weight ?? 'Not taken' }}</strong>
                        </div>
                        <div>
                            <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); display: block;">Temperature</span>
                            <strong style="font-size: 1.15rem; color: var(--white);">{{ $record->temperature ?? 'Not taken' }}</strong>
                        </div>
                        <div>
                            <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); display: block;">Body Score</span>
                            <strong style="font-size: 1.15rem; color: var(--gold-light);">{{ $record->body_score ?? 'Not taken' }}</strong>
                        </div>
                    </div>

                    <!-- Flowchart: History Taking {text type} -->
                    <div style="margin-bottom: 1.5rem;">
                        <h4 style="font-size: 0.85rem; font-weight: 700; color: var(--gold-primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">
                            History Taking
                        </h4>
                        <div style="background: rgba(11, 25, 44, 0.3); padding: 1rem; border-radius: var(--radius-sm); color: var(--text-secondary); line-height: 1.6;">
                            {{ $record->history_taking ?? 'No history recorded.' }}
                        </div>
                    </div>

                    <!-- Flowchart: Diagnosis {text type} -->
                    <div style="margin-bottom: 1.5rem;">
                        <h4 style="font-size: 0.85rem; font-weight: 700; color: var(--gold-primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">
                            Clinical Diagnosis
                        </h4>
                        <div style="background: rgba(11, 25, 44, 0.3); padding: 1rem; border-radius: var(--radius-sm); color: var(--white); font-weight: 600; line-height: 1.6;">
                            {{ $record->diagnosis ?? 'No formal diagnosis entered.' }}
                        </div>
                    </div>

                    <!-- Flowchart: Veterinarians Notes {text type} -->
                    <div style="margin-bottom: 1.5rem;">
                        <h4 style="font-size: 0.85rem; font-weight: 700; color: var(--gold-primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">
                            Veterinarian's Clinical Notes
                        </h4>
                        <div style="background: rgba(11, 25, 44, 0.3); padding: 1rem; border-radius: var(--radius-sm); color: var(--text-secondary); line-height: 1.6;">
                            {{ $record->veterinarians_notes ?? 'No notes entered.' }}
                        </div>
                    </div>

                    <!-- Flowchart: Attached Laboratory results {pictures} -->
                    @if($record->attached_lab_results)
                        <div>
                            <h4 style="font-size: 0.85rem; font-weight: 700; color: var(--gold-primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">
                                Attached Laboratory Results
                            </h4>
                            <div style="border: 1px solid var(--black-border); border-radius: var(--radius-sm); overflow: hidden; max-width: 400px;">
                                <a href="{{ asset($record->attached_lab_results) }}" target="_blank">
                                    <img src="{{ asset($record->attached_lab_results) }}" alt="Laboratory Result Picture" style="width: 100%; display: block;">
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right: Patient Info & Prescription -->
        <div>
            <!-- Patient & Owner Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Patient Profile</h3>
                </div>
                <div class="card-body">
                    <div style="margin-bottom: 1rem;">
                        <span style="font-size: 0.72rem; text-transform: uppercase; color: var(--text-muted);">Pet Name</span>
                        <div style="font-size: 1.25rem; font-weight: 800; color: var(--white);">{{ $record->pet->name ?? 'N/A' }}</div>
                        <div style="font-size: 0.82rem; color: var(--gold-light);">Key: <strong>{{ $record->pet->pet_code ?? '' }}</strong></div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; font-size: 0.85rem; margin-bottom: 1.25rem;">
                        <div>Species: <strong style="color: var(--white);">{{ $record->pet->species ?? 'N/A' }}</strong></div>
                        <div>Breed: <strong style="color: var(--white);">{{ $record->pet->breed ?? 'N/A' }}</strong></div>
                        <div>Age: <strong style="color: var(--white);">{{ $record->pet->age ?? 'N/A' }}</strong></div>
                        <div>Sex: <strong style="color: var(--white);">{{ $record->pet->sex ?? 'N/A' }}</strong></div>
                    </div>

                    <hr style="border: 0; border-top: 1px solid var(--black-border); margin: 1rem 0;">

                    <div>
                        <span style="font-size: 0.72rem; text-transform: uppercase; color: var(--text-muted);">Owner Details</span>
                        <div style="font-weight: 700; color: var(--white);">{{ $record->owner->full_name ?? 'N/A' }}</div>
                        <div style="font-size: 0.8rem; color: var(--gold-light);">Key: {{ $record->owner->client_code ?? '' }}</div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">{{ $record->owner->contact_number ?? '' }}</div>
                        <div style="font-size: 0.78rem; color: var(--text-muted);">{{ $record->owner->address ?? '' }}</div>
                    </div>
                </div>
            </div>

            <!-- Prescription Details Side Card -->
            @if($record->prescription)
                <div class="card" style="border: 1px solid var(--gold-border);">
                    <div class="card-header" style="background: rgba(245, 186, 49, 0.08);">
                        <div class="card-title-group">
                            <h3 class="card-title" style="color: var(--gold-light);">Rx {{ $record->prescription->prescription_code }}</h3>
                            <span class="card-subtitle">Issued by {{ $record->prescription->veterinarian_name }}</span>
                        </div>
                        <span class="badge badge-gold">{{ $record->prescription->date_issued->format('M d, Y') }}</span>
                    </div>
                    <div class="card-body">
                        <div style="background: var(--navy-dark); padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--gold-border); margin-bottom: 1rem;">
                            <span style="font-size: 0.72rem; color: var(--gold-primary); font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em;">Prescribed Medications</span>
                            <pre style="font-family: inherit; font-size: 0.85rem; color: var(--white); white-space: pre-wrap; margin-top: 0.4rem; line-height: 1.6;">{{ $record->prescription->rx_details }}</pre>
                        </div>

                        @if($record->prescription->instructions)
                            <div style="font-size: 0.82rem; color: var(--text-secondary); margin-bottom: 1.25rem; background: rgba(11, 25, 44, 0.4); padding: 0.75rem; border-radius: var(--radius-sm); border: 1px solid var(--black-border);">
                                <strong style="color: var(--white);">Usage Instructions:</strong> {{ $record->prescription->instructions }}
                            </div>
                        @endif

                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                            <a href="{{ route('vet.prescriptions.print', $record->prescription->id) }}" target="_blank" class="btn btn-gold btn-sm" style="width: 100%;">
                                🖨️ Print Prescription (Official Rx)
                            </a>
                            <button type="button" class="btn btn-navy btn-sm" style="width: 100%;"
                                data-modal-target="modal-edit-prescription"
                                data-action-url="{{ route('vet.prescriptions.update', $record->prescription->id) }}"
                                data-field-rx_details="{{ $record->prescription->rx_details }}"
                                data-field-instructions="{{ $record->prescription->instructions }}"
                                data-field-body_weight="{{ $record->prescription->body_weight }}">
                                ✏️ Edit Prescription
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <!-- No Prescription Yet: Side Card to Issue Directly From Case -->
                <div class="card" style="border: 1.5px dashed var(--gold-border); background: linear-gradient(180deg, rgba(16, 35, 61, 0.45) 0%, var(--black-card) 100%);">
                    <div class="card-header">
                        <div class="card-title-group">
                            <h3 class="card-title" style="color: var(--gold-primary);">💊 Clinical Prescription</h3>
                            <span class="card-subtitle">No prescription issued for this case yet</span>
                        </div>
                    </div>
                    <div class="card-body" style="text-align: center; padding: 1.75rem 1.25rem;">
                        <div style="width: 50px; height: 50px; border-radius: 50%; background: var(--navy-dark); border: 1.5px solid var(--gold-border); color: var(--gold-primary); font-size: 1.35rem; font-weight: 800; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.85rem auto; box-shadow: var(--gold-shadow);">
                            Rx
                        </div>
                        <h4 style="color: var(--white); font-size: 0.95rem; margin-bottom: 0.4rem;">Prescribe Medications</h4>
                        <p style="font-size: 0.78rem; color: var(--text-muted); margin-bottom: 1.25rem; line-height: 1.5;">
                            Issue medicines, dosage, and intake directions directly tied to {{ $record->pet->name ?? 'this patient' }}.
                        </p>

                        <button type="button" class="btn btn-gold btn-sm" style="width: 100%; font-weight: 700; padding: 0.7rem;"
                            data-modal-target="modal-add-prescription-side">
                            ➕ Add Prescription
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- ==================== MODAL: ADD PRESCRIPTION ON THE SIDE ==================== -->
    <div class="modal-backdrop" id="modal-add-prescription-side">
        <div class="modal-dialog modal-lg">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">💊</div>
                    <div>
                        <h4 class="modal-title">Add Prescription for Case {{ $record->record_code }}</h4>
                        <span style="font-size: 0.75rem; color: var(--gold-light);">
                            Patient: <strong>{{ $record->pet->name ?? 'N/A' }}</strong> ({{ $record->pet->species ?? '' }}) • Owner: <strong>{{ $record->owner->full_name ?? 'N/A' }}</strong>
                        </span>
                    </div>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form action="{{ route('vet.prescriptions.store') }}" method="POST">
                @csrf
                <input type="hidden" name="medical_record_id" value="{{ $record->id }}">
                <input type="hidden" name="pet_id" value="{{ $record->pet_id }}">
                <input type="hidden" name="owner_id" value="{{ $record->owner_id }}">

                <div class="modal-body">
                    <!-- Consultation Summary Banner -->
                    <div style="background: var(--navy-dark); border: 1px solid var(--navy-border); padding: 0.85rem 1rem; border-radius: var(--radius-sm); margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                        <div>
                            <span style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Diagnosis</span>
                            <div style="font-weight: 600; color: var(--white); font-size: 0.88rem;">{{ $record->diagnosis ?? 'Routine checkup / consultation' }}</div>
                        </div>
                        <div>
                            <span style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Current Weight</span>
                            <div style="font-weight: 700; color: var(--gold-light); font-size: 0.88rem;">{{ $record->body_weight ?? 'N/A' }}</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="presc_body_weight">Patient Weight (for dosage reference)</label>
                        <input type="text" name="body_weight" id="presc_body_weight" class="form-control" value="{{ $record->body_weight }}" placeholder="e.g. 4.5 kg">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="presc_rx_details">Prescription Details (Medication, Strength & Dosage) <span class="req">*</span></label>
                        <textarea name="rx_details" id="presc_rx_details" rows="5" class="form-control" required
                            placeholder="e.g.&#10;1. Amoxicillin + Clavulanic Acid 250mg - 1 tab BID for 7 days&#10;2. Meloxicam 0.5mg/ml oral suspension - 0.4ml SID after meals for 3 days&#10;3. Eye drop (Tobramycin) - 2 drops both eyes TID for 5 days"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="presc_instructions">Special Instructions / Precautions / Advice</label>
                        <textarea name="instructions" id="presc_instructions" rows="3" class="form-control"
                            placeholder="e.g. Administer after meals with food. Do not skip doses. Keep refrigerated. Return for re-evaluation in 7 days."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold">💊 Issue & Attach Prescription</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL: EDIT PRESCRIPTION ==================== -->
    @if($record->prescription)
    <div class="modal-backdrop" id="modal-edit-prescription">
        <div class="modal-dialog modal-lg">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">✏️</div>
                    <div>
                        <h4 class="modal-title">Edit Prescription {{ $record->prescription->prescription_code }}</h4>
                        <span style="font-size: 0.75rem; color: var(--gold-light);">Update medication details or usage instructions</span>
                    </div>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form action="{{ route('vet.prescriptions.update', $record->prescription->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Patient Weight</label>
                        <input type="text" name="body_weight" class="form-control" value="{{ $record->prescription->body_weight }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Medications & Dosage <span class="req">*</span></label>
                        <textarea name="rx_details" rows="5" class="form-control" required>{{ $record->prescription->rx_details }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Instructions / Advice</label>
                        <textarea name="instructions" rows="3" class="form-control">{{ $record->prescription->instructions }}</textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
    @endif
@endsection
