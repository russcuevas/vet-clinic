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
                <span class="card-subtitle">Consultation, Examination Notes, Treatments, Laboratory Tests, and Follow-ups</span>
            </div>
        </div>

        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table" id="vet-records-table">
                    <thead>
                        <tr>
                            <th style="min-width: 110px;">Date & Code</th>
                            <th style="min-width: 170px;">Patient & Owner</th>
                            <th style="min-width: 110px;">Temp & BW</th>
                            <th style="min-width: 180px;">Purpose / Complaint / Notes</th>
                            <th style="min-width: 170px;">Medication / Treatment</th>
                            <th style="min-width: 160px;">Laboratory</th>
                            <th style="min-width: 130px;">Follow Up</th>
                            <th style="text-align: right; min-width: 110px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                            <tr>
                                <td>
                                    <div style="font-weight: 700; color: var(--gold-primary); font-size: 0.88rem;">
                                        📅 {{ $record->visit_date ? $record->visit_date->format('m/d/Y') : $record->created_at->format('m/d/Y') }}
                                    </div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">
                                        {{ $record->record_code }}
                                    </div>
                                    <div style="display: flex; gap: 3px; flex-wrap: wrap; margin-top: 4px;">
                                        <span class="badge badge-navy" style="font-size: 0.68rem;">
                                            {{ ucfirst(str_replace('_', ' ', $record->service_type)) }}
                                        </span>
                                        @if($record->status === 'ongoing')
                                            <span class="badge badge-warning" style="font-size: 0.68rem;">In Exam</span>
                                        @elseif($record->status === 'completed')
                                            <span class="badge badge-info" style="font-size: 0.68rem;">Sent to Cashier</span>
                                        @elseif($record->status === 'billed')
                                            <span class="badge badge-success" style="font-size: 0.68rem;">Billed / Paid</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 800; color: var(--white); font-size: 0.95rem;">
                                        {{ $record->pet->name ?? 'N/A' }}
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--gold-light);">
                                        {{ $record->pet->species ?? '' }} • {{ $record->pet->breed ?? '' }}
                                        @if($record->pet?->sex) ({{ $record->pet->sex }}) @endif
                                    </div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 3px;">
                                        👤 <strong>{{ $record->owner->full_name ?? 'N/A' }}</strong>
                                        @if($record->owner?->contact_number)
                                            <div style="color: var(--text-muted);">📞 {{ $record->owner->contact_number }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size: 0.82rem; margin-bottom: 2px;">
                                        <span style="color: var(--text-muted);">Temp:</span> 
                                        <strong style="color: {{ $record->temperature ? 'var(--white)' : 'var(--text-muted)' }};">
                                            {{ $record->temperature ?? '—' }}
                                        </strong>
                                    </div>
                                    <div style="font-size: 0.82rem;">
                                        <span style="color: var(--text-muted);">BW:</span> 
                                        <strong style="color: {{ $record->body_weight ? 'var(--gold-light)' : 'var(--text-muted)' }};">
                                            {{ $record->body_weight ?? '—' }}
                                        </strong>
                                    </div>
                                    @if($record->body_score)
                                        <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 2px;">Score: {{ $record->body_score }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($record->history_taking)
                                        <div style="color: var(--white); font-size: 0.82rem; line-height: 1.4; white-space: pre-wrap;">{{ Str::limit($record->history_taking, 120) }}</div>
                                    @endif
                                    @if($record->diagnosis)
                                        <div style="font-size: 0.75rem; color: var(--gold-light); margin-top: 4px; font-weight: 600;">
                                            Dx: {{ Str::limit($record->diagnosis, 60) }}
                                        </div>
                                    @endif
                                    @if(!$record->history_taking && !$record->diagnosis)
                                        <span style="color: var(--text-muted); font-size: 0.78rem;">No notes entered</span>
                                    @endif
                                </td>
                                <td>
                                    @if($record->medication_treatment)
                                        <div style="color: var(--text-secondary); font-size: 0.82rem; line-height: 1.4; white-space: pre-wrap;">{{ Str::limit($record->medication_treatment, 100) }}</div>
                                    @endif
                                    @if($record->prescription)
                                        <div style="margin-top: 4px;">
                                            <a href="{{ route('vet.prescriptions.print', $record->prescription->id) }}" target="_blank" class="badge badge-gold" style="text-decoration: none;" title="Print Rx">
                                                💊 {{ $record->prescription->prescription_code }}
                                            </a>
                                        </div>
                                    @elseif(!$record->medication_treatment)
                                        <span style="color: var(--text-muted); font-size: 0.78rem;">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($record->laboratory_notes)
                                        <div style="color: var(--text-secondary); font-size: 0.8rem; line-height: 1.35; white-space: pre-wrap;">{{ Str::limit($record->laboratory_notes, 80) }}</div>
                                    @endif
                                    @if($record->attached_lab_results)
                                        <div style="margin-top: 4px;">
                                            <a href="{{ asset($record->attached_lab_results) }}" target="_blank" class="badge badge-gold" style="text-decoration: none; font-size: 0.72rem;">
                                                📎 Attached File
                                            </a>
                                        </div>
                                    @elseif(!$record->laboratory_notes)
                                        <span style="color: var(--text-muted); font-size: 0.78rem;">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($record->follow_up_date)
                                        <div style="font-weight: 700; color: var(--gold-light); font-size: 0.82rem;">
                                            🗓️ {{ $record->follow_up_date->format('m/d/Y') }}
                                        </div>
                                    @endif
                                    @if($record->follow_up_notes)
                                        <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 2px;">{{ Str::limit($record->follow_up_notes, 50) }}</div>
                                    @endif
                                    @if(!$record->follow_up_date && !$record->follow_up_notes)
                                        <span style="color: var(--text-muted); font-size: 0.78rem;">None</span>
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 0.35rem; align-items: center;">
                                        <a href="{{ route('vet.medical.show', $record->id) }}" class="btn btn-navy btn-sm" style="padding: 4px 8px; font-size: 0.78rem;">
                                            📂 Open Case
                                        </a>
                                        <button type="button" class="btn btn-ghost btn-sm" style="padding: 4px 8px; font-size: 0.78rem;"
                                            data-modal-target="modal-edit-exam"
                                            data-action-url="{{ route('vet.medical.update', $record->id) }}"
                                            data-field-visit_date="{{ $record->visit_date ? $record->visit_date->format('Y-m-d') : $record->created_at->format('Y-m-d') }}"
                                            data-field-body_weight="{{ $record->body_weight }}"
                                            data-field-temperature="{{ $record->temperature }}"
                                            data-field-body_score="{{ $record->body_score }}"
                                            data-field-history_taking="{{ $record->history_taking }}"
                                            data-field-medication_treatment="{{ $record->medication_treatment }}"
                                            data-field-laboratory_notes="{{ $record->laboratory_notes }}"
                                            data-field-diagnosis="{{ $record->diagnosis }}"
                                            data-field-veterinarians_notes="{{ $record->veterinarians_notes }}"
                                            data-field-follow_up_date="{{ $record->follow_up_date ? $record->follow_up_date->format('Y-m-d') : '' }}"
                                            data-field-follow_up_notes="{{ $record->follow_up_notes }}"
                                            data-field-service_fee="{{ $record->service_fee }}"
                                            data-field-status="{{ $record->status }}"
                                            data-field-prescribe_rx="{{ $record->prescription ? $record->prescription->rx_details : '' }}"
                                            data-field-rx_instructions="{{ $record->prescription ? $record->prescription->instructions : '' }}">
                                            Edit
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                                    No medical records found. Click <strong>"New Patient Checkup"</strong> to encode a record.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal: New Examination -->
    <div class="modal-backdrop" id="modal-new-exam">
        <div class="modal-dialog modal-xl">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">🩺</div>
                    <div>
                        <h4 class="modal-title">New Patient Examination</h4>
                        <span style="font-size: 0.75rem; color: var(--gold-light);">Code: <strong>{{ $generatedCode }}</strong> (Client & Pet Sheet)</span>
                    </div>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form action="{{ route('vet.medical.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body" style="padding: 1.25rem 1.5rem;">
                    <!-- Client & Pet Selection -->
                    <x-client-pet-selector :owners="$owners" />

                    <!-- 2-Column Responsive Layout -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 1.25rem; align-items: start; margin-top: 0.5rem;">
                        <!-- LEFT COLUMN -->
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <!-- Visit Information -->
                            <div style="background: rgba(11, 25, 44, 0.4); border: 1px solid var(--navy-border); border-radius: var(--radius-sm); padding: 1rem;">
                                <h5 style="color: var(--gold-primary); font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem;">
                                    <span>📅</span> Visit Information
                                </h5>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label class="form-label">Date of Visit <span class="req">*</span></label>
                                        <input type="date" name="visit_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Service Type <span class="req">*</span></label>
                                        <select name="service_type" class="form-select" required>
                                            <option value="consultation">Consultation</option>
                                            <option value="follow_up">Follow Up</option>
                                            <option value="wellness">Wellness</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Physical Vitals -->
                            <div style="background: rgba(11, 25, 44, 0.4); border: 1px solid var(--navy-border); border-radius: var(--radius-sm); padding: 1rem;">
                                <h5 style="color: var(--gold-primary); font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem;">
                                    <span>🌡️</span> Physical Vitals
                                </h5>
                                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem;">
                                    <div class="form-group">
                                        <label class="form-label" style="font-size: 0.75rem;">Temp (°C)</label>
                                        <input type="text" name="temperature" class="form-control" placeholder="e.g. 38.2 °C">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" style="font-size: 0.75rem;">Weight (BW)</label>
                                        <input type="text" name="body_weight" class="form-control" placeholder="e.g. 3.3 KG">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" style="font-size: 0.75rem;">Body Score</label>
                                        <input type="text" name="body_score" class="form-control" placeholder="e.g. 3/5 Ideal">
                                    </div>
                                </div>
                            </div>

                            <!-- Purpose / Complaint / Notes -->
                            <div class="form-group">
                                <label class="form-label" style="display: flex; align-items: center; gap: 0.35rem;">
                                    <span>📝</span> Purpose / Examination Notes / Complaint
                                </label>
                                <textarea name="history_taking" class="form-control" rows="3" placeholder="e.g. 2 days inappetence, intact male, distended bladder, lethargy..."></textarea>
                            </div>

                            <!-- Diagnosis & Remarks -->
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Diagnosis / Assessment</label>
                                    <input type="text" name="diagnosis" class="form-control" placeholder="e.g. FLUTD / Urinary Blockage">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Doctor's Clinical Notes</label>
                                    <input type="text" name="veterinarians_notes" class="form-control" placeholder="e.g. Confined for observation">
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT COLUMN -->
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <!-- Medication / Treatment -->
                            <div class="form-group">
                                <label class="form-label" style="display: flex; align-items: center; gap: 0.35rem;">
                                    <span>💊</span> Medication / Treatment
                                </label>
                                <textarea name="medication_treatment" class="form-control" rows="3" placeholder="e.g. Co-amox, Kidney support, Special Cat, NSS..."></textarea>
                            </div>

                            <!-- Laboratory -->
                            <div class="form-group">
                                <label class="form-label" style="display: flex; align-items: center; gap: 0.35rem;">
                                    <span>🔬</span> Laboratory (Tests, Procedures, Requests)
                                </label>
                                <textarea name="laboratory_notes" class="form-control" rows="2" placeholder="e.g. CBC, For: serum chem, admission, catheterization (125 ml)..."></textarea>
                            </div>

                            <!-- Attached File -->
                            <div style="background: rgba(11, 25, 44, 0.4); border: 1px dashed var(--gold-border); border-radius: var(--radius-sm); padding: 0.85rem 1rem;">
                                <label class="form-label" style="margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.35rem;">
                                    <span>📎</span> Attach Lab Result / Medical File <span style="font-weight: normal; color: var(--text-muted); font-size: 0.72rem;">(Optional)</span>
                                </label>
                                <input type="file" name="lab_results" class="form-control" accept="image/*,.pdf,.doc,.docx" style="padding: 5px;">
                            </div>

                            <!-- Follow Up Schedule -->
                            <div style="background: rgba(11, 25, 44, 0.4); border: 1px solid var(--navy-border); border-radius: var(--radius-sm); padding: 1rem;">
                                <h5 style="color: var(--gold-light); font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem;">
                                    <span>🗓️</span> Follow-Up Schedule (Optional)
                                </h5>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label class="form-label">Follow-Up Date</label>
                                        <input type="date" name="follow_up_date" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Purpose / Notes</label>
                                        <input type="text" name="follow_up_notes" class="form-control" placeholder="e.g. catheter removal">
                                    </div>
                                </div>
                            </div>

                            <!-- Billing Service Fee & Old History Payment Toggle -->
                            <div style="background: linear-gradient(135deg, rgba(212, 175, 55, 0.12) 0%, rgba(11, 25, 44, 0.6) 100%); border: 1.5px solid var(--gold-border); border-radius: var(--radius-sm); padding: 1.1rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 0.85rem;">
                                    <div style="flex: 1;">
                                        <label class="form-label" style="color: var(--gold-light); font-weight: 700; margin-bottom: 0.25rem;">
                                            Consultation / Service Fee (₱) <span class="req">*</span>
                                        </label>
                                        <input type="number" step="0.01" name="service_fee" class="form-control" value="450.00" required style="font-size: 1.1rem; font-weight: 800; color: var(--gold-primary);">
                                    </div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); max-width: 170px; line-height: 1.3;">
                                        💡 Automatically queued into the Central Billing Database.
                                    </div>
                                </div>

                                <!-- Checkbox: Old / Historical Record -> Auto-Mark as Paid -->
                                <div style="background: rgba(4, 7, 13, 0.55); border: 1px solid rgba(212, 175, 55, 0.3); border-radius: 6px; padding: 0.75rem 0.85rem;">
                                    <label style="display: flex; align-items: flex-start; gap: 0.6rem; margin: 0; cursor: pointer;">
                                        <input type="checkbox" name="is_already_paid" value="1" style="margin-top: 3px; accent-color: var(--gold-primary); width: 17px; height: 17px; cursor: pointer;">
                                        <div>
                                            <div style="font-weight: 700; color: var(--gold-light); font-size: 0.84rem;">
                                                ✅ Old / Historical Record (Mark as Already PAID)
                                            </div>
                                            <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px; line-height: 1.3;">
                                                Check this for past/backdated records. It will instantly mark the consultation and invoice as <strong>PAID & COMPLETED</strong> so you don't need to go to Billing/Cashier.
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
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
        <div class="modal-dialog modal-xl">
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
                <div class="modal-body" style="padding: 1.25rem 1.5rem;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 1.25rem; align-items: start;">
                        <!-- LEFT COLUMN -->
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <div class="form-group">
                                <label class="form-label">📅 Date of Visit</label>
                                <input type="date" name="visit_date" class="form-control">
                            </div>

                            <div style="background: rgba(11, 25, 44, 0.4); border: 1px solid var(--navy-border); border-radius: var(--radius-sm); padding: 1rem;">
                                <h5 style="color: var(--gold-primary); font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">
                                    🌡️ Physical Vitals
                                </h5>
                                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem;">
                                    <div class="form-group">
                                        <label class="form-label" style="font-size: 0.75rem;">Temp (°C)</label>
                                        <input type="text" name="temperature" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" style="font-size: 0.75rem;">Weight (BW)</label>
                                        <input type="text" name="body_weight" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" style="font-size: 0.75rem;">Body Score</label>
                                        <input type="text" name="body_score" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">📝 Purpose / Examination Notes / Complaint</label>
                                <textarea name="history_taking" class="form-control" rows="3"></textarea>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Diagnosis</label>
                                    <input type="text" name="diagnosis" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Veterinarian's Notes</label>
                                    <input type="text" name="veterinarians_notes" class="form-control">
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT COLUMN -->
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <div class="form-group">
                                <label class="form-label">💊 Medication / Treatment</label>
                                <textarea name="medication_treatment" class="form-control" rows="3"></textarea>
                            </div>

                            <div class="form-group">
                                <label class="form-label">🔬 Laboratory</label>
                                <textarea name="laboratory_notes" class="form-control" rows="2"></textarea>
                            </div>

                            <div style="background: rgba(11, 25, 44, 0.4); border: 1px dashed var(--gold-border); border-radius: var(--radius-sm); padding: 0.85rem 1rem;">
                                <label class="form-label" style="margin-bottom: 0.35rem;">📎 Update Attached File (Optional)</label>
                                <input type="file" name="lab_results" class="form-control" accept="image/*,.pdf,.doc,.docx" style="padding: 5px;">
                            </div>

                            <div style="background: rgba(11, 25, 44, 0.4); border: 1px solid var(--navy-border); border-radius: var(--radius-sm); padding: 1rem;">
                                <h5 style="color: var(--gold-light); font-size: 0.82rem; font-weight: 700; text-transform: uppercase; margin-bottom: 0.75rem;">
                                    🗓️ Follow-Up Schedule
                                </h5>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label class="form-label">Follow Up Date</label>
                                        <input type="date" name="follow_up_date" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Purpose / Notes</label>
                                        <input type="text" name="follow_up_notes" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <!-- Prescription Area -->
                            <div style="background: rgba(245, 186, 49, 0.05); border: 1px solid var(--gold-border); border-radius: var(--radius-sm); padding: 0.85rem 1rem;">
                                <h5 style="color: var(--gold-light); font-size: 0.82rem; font-weight: 700; text-transform: uppercase; margin-bottom: 0.5rem;">
                                    💊 Prescribe Medications (Rx)
                                </h5>
                                <div class="form-group" style="margin-bottom: 0.5rem;">
                                    <label class="form-label" style="font-size: 0.75rem;">Medications & Dosage</label>
                                    <textarea name="prescribe_rx" class="form-control" rows="2" placeholder="e.g. 1. Amoxicillin 250mg - 1 tab BID for 7 days"></textarea>
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label" style="font-size: 0.75rem;">Rx Instructions</label>
                                    <input type="text" name="rx_instructions" class="form-control" placeholder="e.g. After meals">
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Service Fee (₱) <span class="req">*</span></label>
                                    <input type="number" step="0.01" name="service_fee" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Status <span class="req">*</span></label>
                                    <select name="status" class="form-select" required>
                                        <option value="completed">Completed (Send to Cashier)</option>
                                        <option value="ongoing">Ongoing / In-Progress</option>
                                        <option value="billed">Billed</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold">💾 Save & Send to Cashier</button>
                </div>
            </form>
        </div>
    </div>
@endsection
