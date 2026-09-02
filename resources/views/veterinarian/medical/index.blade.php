@extends('layouts.app')

@php
    $title = 'Medical Records';
    $headerTitle = 'Veterinary Medical Records';
    $breadcrumb = 'Medical Database';
@endphp

@section('content')
    <!-- Action Bar & Filter Toolbar -->
    <div class="table-toolbar">
        <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <div class="search-input-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                <input type="text" class="form-control" placeholder="Search records, pets, owners..." data-table-search="vet-records-table">
            </div>

            <!-- Service Filter Pills -->
            <div style="display: flex; gap: 0.35rem;">
                <a href="{{ route('vet.medical.index') }}" class="btn btn-sm {{ !request('service_type') ? 'btn-gold' : 'btn-navy' }}">All</a>
                <a href="{{ route('vet.medical.index', ['service_type' => 'consultation']) }}" class="btn btn-sm {{ request('service_type') === 'consultation' ? 'btn-gold' : 'btn-navy' }}">Consultation</a>
                <a href="{{ route('vet.medical.index', ['service_type' => 'follow_up']) }}" class="btn btn-sm {{ request('service_type') === 'follow_up' ? 'btn-gold' : 'btn-navy' }}">Follow Up</a>
                <a href="{{ route('vet.medical.index', ['service_type' => 'wellness']) }}" class="btn btn-sm {{ request('service_type') === 'wellness' ? 'btn-gold' : 'btn-navy' }}">Wellness</a>
            </div>
        </div>

        <button type="button" class="btn btn-gold" data-modal-target="modal-new-exam">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            <span>New Patient Checkup</span>
        </button>
    </div>

    <!-- Table Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-title-group">
                <h3 class="card-title">Patient Medical Database</h3>
                <span class="card-subtitle">Consultation, Follow-up, Wellness checkups</span>
            </div>
        </div>

        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table" id="vet-records-table">
                    <thead>
                        <tr>
                            <th>Record Code</th>
                            <th>Patient / Pet</th>
                            <th>Owner Name</th>
                            <th>Service Type</th>
                            <th>Vitals (Weight / Temp / Score)</th>
                            <th>Diagnosis & Assessment</th>
                            <th>Prescription</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($records as $record)
                            <tr>
                                <td>
                                    <strong style="color: var(--gold-primary);">{{ $record->record_code }}</strong>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $record->created_at->format('M d, Y') }}</div>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--white);">{{ $record->pet->name ?? 'N/A' }}</div>
                                    <div style="font-size: 0.75rem; color: var(--gold-light);">{{ $record->pet->species ?? '' }} - {{ $record->pet->breed ?? '' }}</div>
                                </td>
                                <td>
                                    <div style="font-weight: 600;">{{ $record->owner->full_name ?? 'N/A' }}</div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">Key: {{ $record->owner->client_code ?? '' }}</div>
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $record->service_type)) }}</span>
                                </td>
                                <td>
                                    <div style="font-size: 0.78rem;">⚖️ {{ $record->body_weight ?? 'N/A' }}</div>
                                    <div style="font-size: 0.78rem;">🌡️ {{ $record->temperature ?? 'N/A' }}</div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">Score: {{ $record->body_score ?? 'N/A' }}</div>
                                </td>
                                <td style="max-width: 250px;">
                                    <div style="font-size: 0.85rem; font-weight: 600; color: var(--white);">{{ Str::limit($record->diagnosis ?? 'No diagnosis', 60) }}</div>
                                    @if($record->attached_lab_results)
                                        <a href="{{ asset($record->attached_lab_results) }}" target="_blank" class="badge badge-gold" style="font-size: 0.7rem; text-decoration: none; margin-top: 3px;">
                                            🔬 Lab Picture Attached
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    @if($record->prescription)
                                        <a href="{{ route('vet.prescriptions.print', $record->prescription->id) }}" target="_blank" class="badge badge-gold" style="text-decoration: none;" title="Print Official Rx">
                                            💊 {{ $record->prescription->prescription_code }}
                                        </a>
                                    @else
                                        <a href="{{ route('vet.medical.show', $record->id) }}" class="btn btn-outline-gold btn-sm" style="padding: 2px 8px; font-size: 0.72rem;" title="Open case and add prescription on the side">
                                            + Add Rx
                                        </a>
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 0.35rem;">
                                        <a href="{{ route('vet.medical.show', $record->id) }}" class="btn btn-navy btn-sm">
                                            📂 Open Case
                                        </a>
                                        <button type="button" class="btn btn-ghost btn-sm"
                                            data-modal-target="modal-edit-exam"
                                            data-action-url="{{ route('vet.medical.update', $record->id) }}"
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
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal: New Examination -->
    <div class="modal-backdrop" id="modal-new-exam">
        <div class="modal-dialog modal-lg">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">🩺</div>
                    <div>
                        <h4 class="modal-title">New Patient Examination</h4>
                        <span style="font-size: 0.75rem; color: var(--gold-light);">Code: <strong>{{ $generatedCode }}</strong> (Flowchart: Consultation / Follow up / Wellness)</span>
                    </div>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form action="{{ route('vet.medical.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <x-client-pet-selector :owners="$owners" />

                    <div class="form-group">
                        <label class="form-label">Service Type <span class="req">*</span></label>
                        <select name="service_type" class="form-select" required>
                            <option value="consultation">Consultation</option>
                            <option value="follow_up">Follow Up</option>
                            <option value="wellness">Wellness</option>
                        </select>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Body Weight</label>
                            <input type="text" name="body_weight" class="form-control" placeholder="e.g. 8.2 kg">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Temperature</label>
                            <input type="text" name="temperature" class="form-control" placeholder="e.g. 38.6 °C">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Body Score</label>
                            <input type="text" name="body_score" class="form-control" placeholder="e.g. 3/5 Ideal">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">History Taking {text type}</label>
                        <textarea name="history_taking" class="form-control" rows="2" placeholder="Owner complaints, duration, prior medication..."></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Diagnosis {text type}</label>
                        <textarea name="diagnosis" class="form-control" rows="2" placeholder="Diagnosis / Clinical findings..."></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Veterinarian's Notes {text type}</label>
                        <textarea name="veterinarians_notes" class="form-control" rows="2" placeholder="Treatment performed, patient response, instructions..."></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Attached Laboratory Results (Pictures saved to public/uploads/lab_results/)</label>
                        <input type="file" name="lab_results" class="form-control" accept="image/*">
                    </div>


                    <div class="form-group" style="margin-top: 1.25rem;">
                        <label class="form-label">Consultation / Service Fee (₱) <span class="req">*</span></label>
                        <input type="number" step="0.01" name="service_fee" class="form-control" value="450.00" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold">Save Examination & Transfer to Billing</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Edit Medical Record -->
    <div class="modal-backdrop" id="modal-edit-exam">
        <div class="modal-dialog modal-lg">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">✏️</div>
                    <h4 class="modal-title">Update Examination Details</h4>
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
                            <label class="form-label">Fee (₱)</label>
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
                        <label class="form-label">Replace Lab Picture (saved in public/)</label>
                        <input type="file" name="lab_results" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold">Update Details</button>
                </div>
            </form>
        </div>
    </div>
@endsection
