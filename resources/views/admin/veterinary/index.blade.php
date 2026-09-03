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
                <h3 class="card-title">Clinical Medical Records</h3>
                <span class="card-subtitle">Consultation, Examination Notes, Treatments, Laboratory Tests, and Follow-ups</span>
            </div>
        </div>

        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table" id="records-table">
                    <thead>
                        <tr>
                            <th style="min-width: 110px;">Date & Code</th>
                            <th style="min-width: 170px;">Patient & Owner</th>
                            <th style="min-width: 110px;">Temp & BW</th>
                            <th style="min-width: 180px;">Purpose / Complaint / Notes</th>
                            <th style="min-width: 170px;">Medication / Treatment</th>
                            <th style="min-width: 160px;">Laboratory</th>
                            <th style="min-width: 130px;">Follow Up</th>
                            <th style="min-width: 100px;">Fee & Status</th>
                            <th style="text-align: right; min-width: 100px;">Actions</th>
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
                                    <span class="badge badge-navy" style="font-size: 0.68rem; margin-top: 3px;">
                                        {{ ucfirst(str_replace('_', ' ', $record->service_type)) }}
                                    </span>
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
                                            <a href="{{ route('admin.prescriptions.print', $record->prescription->id) }}" target="_blank" class="badge badge-gold" style="text-decoration: none;" title="Print Rx">
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
                                <td>
                                    <div style="font-weight: 700; color: var(--white); font-size: 0.88rem;">₱{{ number_format($record->service_fee, 2) }}</div>
                                    <span class="badge {{ $record->status === 'billed' ? 'badge-success' : ($record->status === 'completed' ? 'badge-info' : 'badge-warning') }}" style="font-size: 0.68rem; margin-top: 3px;">
                                        {{ ucfirst($record->status) }}
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 0.35rem; align-items: center;">
                                        <!-- Edit Modal Trigger -->
                                        <button type="button" class="btn btn-navy btn-sm" style="padding: 4px 8px; font-size: 0.78rem;"
                                            data-modal-target="modal-edit-exam"
                                            data-action-url="{{ route('admin.veterinary.update', $record->id) }}"
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
                                            data-field-status="{{ $record->status }}">
                                            Edit
                                        </button>

                                        <!-- Delete Record Trigger -->
                                        <button type="button" class="btn btn-danger btn-sm" style="padding: 4px 8px; font-size: 0.78rem;"
                                            data-modal-target="modal-delete-exam"
                                            data-action-url="{{ route('admin.veterinary.destroy', $record->id) }}"
                                            data-field-target_name="{{ $record->record_code }} ({{ $record->pet->name ?? '' }})">
                                            🗑️
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                                    No medical records found. Click <strong>"New Examination / Consultation"</strong> to encode a record.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ==================== MODAL 1: NEW VETERINARY EXAMINATION & MEDICAL RECORD ==================== -->
    <div class="modal-backdrop" id="modal-new-exam">
        <div class="modal-dialog modal-xl">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">🩺</div>
                    <div>
                        <h4 class="modal-title">New Examination & Medical Record</h4>
                        <span style="font-size: 0.75rem; color: var(--gold-light);">Code: <strong>{{ $generatedCode }}</strong> (Client & Pet Sheet)</span>
                    </div>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form action="{{ route('admin.veterinary.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body" style="padding: 1.25rem 1.5rem;">
                    <!-- Client & Pet selection (Select Existing or Register New) -->
                    <x-client-pet-selector :owners="$owners" />

                    <!-- 2-Column Responsive Form Layout -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 1.25rem; align-items: start; margin-top: 0.5rem;">
                        <!-- LEFT COLUMN: Visit info, Vitals, History, Diagnosis -->
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <!-- Visit Information -->
                            <div style="background: rgba(11, 25, 44, 0.4); border: 1px solid var(--navy-border); border-radius: var(--radius-sm); padding: 1rem;">
                                <h5 style="color: var(--gold-primary); font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem;">
                                    <span>📅</span> Visit & Doctor Information
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
                                <div class="form-group" style="margin-top: 0.75rem;">
                                    <label class="form-label">Attending Veterinarian</label>
                                    <select name="veterinarian_id" class="form-select">
                                        @foreach($veterinarians as $vet)
                                            <option value="{{ $vet->id }}">{{ $vet->name }} ({{ $vet->license_no ?? 'PRC-VET' }})</option>
                                        @endforeach
                                    </select>
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

                        <!-- RIGHT COLUMN: Treatment, Laboratory, Follow up, Billing -->
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

                            <!-- Optional Attached File -->
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
                    <button type="submit" class="btn btn-gold">Save Examination & Push to Billing</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL 2: EDIT MEDICAL RECORD ==================== -->
    <div class="modal-backdrop" id="modal-edit-exam">
        <div class="modal-dialog modal-xl">
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

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Service Fee (₱) <span class="req">*</span></label>
                                    <input type="number" step="0.01" name="service_fee" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Status <span class="req">*</span></label>
                                    <select name="status" class="form-select" required>
                                        <option value="ongoing">Ongoing</option>
                                        <option value="completed">Completed</option>
                                        <option value="billed">Billed</option>
                                    </select>
                                </div>
                            </div>
                        </div>
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
    </div>       </form>
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
