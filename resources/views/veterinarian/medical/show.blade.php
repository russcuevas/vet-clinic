@extends('layouts.app')

@php
    $title = 'Case ' . $record->record_code;
    $headerTitle = 'Clinical Case File: ' . $record->record_code;
    $breadcrumb = 'Patient Case';
@endphp

@section('content')
    <div style="margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <a href="{{ route('vet.medical.index') }}" class="btn btn-navy btn-sm">← Back to Medical Records</a>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <button type="button" class="btn btn-gold btn-sm" data-modal-target="modal-examine-case">
                🩺 {{ $record->status === 'ongoing' ? 'Complete Examination & Send to Billing' : '✏️ Edit Case & Update Billing' }}
            </button>
            @if($record->prescription)
                <a href="{{ route('vet.prescriptions.print', $record->prescription->id) }}" target="_blank" class="btn btn-navy btn-sm">
                    🖨️ Print Prescription
                </a>
            @endif
        </div>
    </div>

    <!-- Billing & Workflow Status Banner -->
    @php
        $bill = $record->bill ?: \App\Models\Bill::where('medical_record_id', $record->id)->first();
    @endphp

    @if($bill && $bill->payment_status === 'paid')
        <div style="background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.35); border-radius: var(--radius-sm); padding: 0.85rem 1.25rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <span style="font-size: 1.5rem;">✅</span>
                <div>
                    <div style="font-weight: 700; color: #4ade80; font-size: 0.95rem;">
                        Consultation Billed & Paid at Cashier Desk
                    </div>
                    <div style="font-size: 0.8rem; color: var(--text-secondary);">
                        Invoice: <strong>{{ $bill->invoice_no }}</strong> • Total Paid: <strong>₱{{ number_format($bill->total_amount, 2) }}</strong> via {{ ucfirst($bill->payment_method ?? 'Cash') }} • {{ $bill->paid_at ? $bill->paid_at->format('M d, Y h:i A') : $bill->updated_at->format('M d, Y') }}
                    </div>
                </div>
            </div>
            <span class="badge badge-success" style="font-size: 0.82rem; padding: 5px 12px;">Paid</span>
        </div>
    @elseif($bill && $bill->payment_status === 'unpaid')
        <div style="background: rgba(245, 186, 49, 0.1); border: 1px solid rgba(245, 186, 49, 0.4); border-radius: var(--radius-sm); padding: 0.85rem 1.25rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <span style="font-size: 1.5rem;">💳</span>
                <div>
                    <div style="font-weight: 700; color: var(--gold-light); font-size: 0.95rem;">
                        Transferred to Cashier Billing Queue — Waiting for Client Payment
                    </div>
                    <div style="font-size: 0.8rem; color: var(--text-secondary);">
                        Invoice: <strong>{{ $bill->invoice_no }}</strong> • Total Due: <strong>₱{{ number_format($bill->total_amount, 2) }}</strong> • The client can now proceed to the Cashier counter to settle the bill.
                    </div>
                </div>
            </div>
            <span class="badge badge-warning" style="font-size: 0.82rem; padding: 5px 12px;">Unpaid in Cashier Queue</span>
        </div>
    @else
        <div style="background: rgba(14, 165, 233, 0.1); border: 1px solid rgba(14, 165, 233, 0.35); border-radius: var(--radius-sm); padding: 0.85rem 1.25rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <span style="font-size: 1.5rem;">🩺</span>
                <div>
                    <div style="font-weight: 700; color: #38bdf8; font-size: 0.95rem;">
                        Patient Checked In — Examination / Consultation In Progress
                    </div>
                    <div style="font-size: 0.8rem; color: var(--text-secondary);">
                        Enter findings, diagnosis, treatment, and consultation fee below. Once done, it will automatically route to the Cashier.
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-gold btn-sm" data-modal-target="modal-examine-case">
                Begin / Save Exam
            </button>
        </div>
    @endif

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; align-items: start;">
        <!-- Left: Case File Breakdown -->
        <div>
            <!-- Vitals & Consultation Header -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title-group">
                        <h3 class="card-title">Case {{ $record->record_code }} - {{ ucfirst(str_replace('_', ' ', $record->service_type)) }}</h3>
                        <span class="card-subtitle">
                            📅 Visit Date: <strong>{{ $record->visit_date ? $record->visit_date->format('F d, Y') : $record->created_at->format('F d, Y') }}</strong>
                            • Encoded on {{ $record->created_at->format('M d, Y h:i A') }}
                        </span>
                    </div>
                    <span class="badge badge-gold">{{ ucfirst($record->status) }}</span>
                </div>

                <div class="card-body">
                    <!-- Flowchart: Vitals -->
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; background: var(--navy-dark); padding: 1.25rem; border-radius: var(--radius-sm); border: 1px solid var(--navy-border); margin-bottom: 1.5rem;">
                        <div>
                            <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); display: block;">Body Weight (BW)</span>
                            <strong style="font-size: 1.15rem; color: var(--white);">{{ $record->body_weight ?? 'Not taken' }}</strong>
                        </div>
                        <div>
                            <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); display: block;">Temperature (Temp)</span>
                            <strong style="font-size: 1.15rem; color: var(--white);">{{ $record->temperature ?? 'Not taken' }}</strong>
                        </div>
                        <div>
                            <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); display: block;">Body Score</span>
                            <strong style="font-size: 1.15rem; color: var(--gold-light);">{{ $record->body_score ?? 'Not taken' }}</strong>
                        </div>
                    </div>

                    <!-- Purpose / Examination Notes / Complaint -->
                    <div style="margin-bottom: 1.5rem;">
                        <h4 style="font-size: 0.85rem; font-weight: 700; color: var(--gold-primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">
                            📝 Purpose / Examination Notes / Complaint
                        </h4>
                        <div style="background: rgba(11, 25, 44, 0.3); padding: 1rem; border-radius: var(--radius-sm); color: var(--text-secondary); line-height: 1.6; white-space: pre-wrap;">
                            {{ $record->history_taking ?? 'No complaint recorded.' }}
                        </div>
                    </div>

                    <!-- Medication / Treatment -->
                    <div style="margin-bottom: 1.5rem;">
                        <h4 style="font-size: 0.85rem; font-weight: 700; color: var(--gold-primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">
                            💊 Medication / Treatment
                        </h4>
                        <div style="background: rgba(11, 25, 44, 0.3); padding: 1rem; border-radius: var(--radius-sm); color: var(--white); line-height: 1.6; white-space: pre-wrap;">
                            {{ $record->medication_treatment ?? 'No medications/treatments listed.' }}
                        </div>
                    </div>

                    <!-- Laboratory Tests & Procedures -->
                    <div style="margin-bottom: 1.5rem;">
                        <h4 style="font-size: 0.85rem; font-weight: 700; color: var(--gold-primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">
                            🔬 Laboratory & Procedures
                        </h4>
                        <div style="background: rgba(11, 25, 44, 0.3); padding: 1rem; border-radius: var(--radius-sm); color: var(--text-secondary); line-height: 1.6; white-space: pre-wrap;">
                            {{ $record->laboratory_notes ?? 'No laboratory tests entered.' }}
                        </div>
                    </div>

                    <!-- Follow-up -->
                    @if($record->follow_up_date || $record->follow_up_notes)
                    <div style="margin-bottom: 1.5rem; background: rgba(245, 186, 49, 0.06); border: 1px solid var(--gold-border); padding: 1rem; border-radius: var(--radius-sm);">
                        <h4 style="font-size: 0.85rem; font-weight: 700; color: var(--gold-light); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.3rem;">
                            🗓️ Follow Up Scheduled
                        </h4>
                        <div style="color: var(--white); font-weight: 600;">
                            Date: {{ $record->follow_up_date ? $record->follow_up_date->format('F d, Y') : 'Date not set' }}
                        </div>
                        @if($record->follow_up_notes)
                            <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 4px;">
                                Purpose: {{ $record->follow_up_notes }}
                            </div>
                        @endif
                    </div>
                    @endif

                    <!-- Clinical Diagnosis -->
                    @if($record->diagnosis)
                    <div style="margin-bottom: 1.5rem;">
                        <h4 style="font-size: 0.85rem; font-weight: 700; color: var(--gold-primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">
                            Clinical Diagnosis / Assessment
                        </h4>
                        <div style="background: rgba(11, 25, 44, 0.3); padding: 1rem; border-radius: var(--radius-sm); color: var(--white); font-weight: 600; line-height: 1.6;">
                            {{ $record->diagnosis }}
                        </div>
                    </div>
                    @endif

                    <!-- Veterinarian's Notes -->
                    @if($record->veterinarians_notes)
                    <div style="margin-bottom: 1.5rem;">
                        <h4 style="font-size: 0.85rem; font-weight: 700; color: var(--gold-primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">
                            Veterinarian's Clinical Notes
                        </h4>
                        <div style="background: rgba(11, 25, 44, 0.3); padding: 1rem; border-radius: var(--radius-sm); color: var(--text-secondary); line-height: 1.6;">
                            {{ $record->veterinarians_notes }}
                        </div>
                    </div>
                    @endif

                    <!-- Attached Laboratory Results / Documents -->
                    @if($record->attached_lab_results)
                        <div>
                            <h4 style="font-size: 0.85rem; font-weight: 700; color: var(--gold-primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">
                                📎 Attached Laboratory / Medical File
                            </h4>
                            <div style="border: 1px solid var(--black-border); border-radius: var(--radius-sm); overflow: hidden; max-width: 450px; padding: 0.5rem; background: var(--navy-dark);">
                                @php
                                    $extension = pathinfo($record->attached_lab_results, PATHINFO_EXTENSION);
                                @endphp
                                @if(in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'webp', 'gif']))
                                    <a href="{{ asset($record->attached_lab_results) }}" target="_blank">
                                        <img src="{{ asset($record->attached_lab_results) }}" alt="Laboratory Result" style="width: 100%; border-radius: 4px; display: block;">
                                    </a>
                                @else
                                    <a href="{{ asset($record->attached_lab_results) }}" target="_blank" class="btn btn-gold btn-sm" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                                        📄 Download / View Attached File ({{ strtoupper($extension) }})
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Prescribed Take-Home Supplies & Clinical Advice (Doctor's Notes) -->
                    @if(!empty($record->prescribed_items) && is_array($record->prescribed_items) && count($record->prescribed_items) > 0)
                        <div style="margin-top: 1.5rem; background: var(--navy-dark); border: 1px solid var(--gold-border); border-radius: var(--radius-sm); padding: 1.25rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem; flex-wrap: wrap; gap: 0.5rem;">
                                <h4 style="font-size: 0.88rem; font-weight: 700; color: var(--gold-light); text-transform: uppercase; letter-spacing: 0.05em; margin: 0; display: flex; align-items: center; gap: 0.4rem;">
                                    <span>📋</span> Prescribed Supplies & Advice Notes
                                </h4>
                                <span class="badge badge-navy" style="font-size: 0.72rem; color: var(--text-muted);">
                                    Clinical Chart Notes
                                </span>
                            </div>

                            <div style="border: 1px solid var(--black-border); border-radius: var(--radius-sm); overflow: hidden; background: rgba(4, 7, 13, 0.4);">
                                <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                                    <thead>
                                        <tr style="background: rgba(11, 25, 44, 0.8); border-bottom: 1px solid var(--black-border);">
                                            <th style="padding: 10px 14px; text-align: left; color: var(--gold-light); font-size: 0.78rem; text-transform: uppercase;">Item / Recommended Supply</th>
                                            <th style="padding: 10px 14px; text-align: center; width: 90px; color: var(--gold-light); font-size: 0.78rem; text-transform: uppercase;">Qty</th>
                                            <th style="padding: 10px 14px; text-align: right; width: 120px; color: var(--gold-light); font-size: 0.78rem; text-transform: uppercase;">Est. Price</th>
                                            <th style="padding: 10px 14px; text-align: left; color: var(--gold-light); font-size: 0.78rem; text-transform: uppercase;">Directions / Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($record->prescribed_items as $pItem)
                                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                                                <td style="padding: 9px 14px; font-weight: 600; color: var(--white); display: flex; align-items: center; gap: 0.4rem;">
                                                    <span>📦</span>
                                                    <span>{{ $pItem['name'] ?? '' }}</span>
                                                </td>
                                                <td style="padding: 9px 14px; text-align: center; color: var(--gold-light);">{{ $pItem['quantity'] ?? '1' }}</td>
                                                <td style="padding: 9px 14px; text-align: right; color: var(--text-secondary);">
                                                    {{ !empty($pItem['price']) ? '₱' . number_format($pItem['price'], 2) : '—' }}
                                                </td>
                                                <td style="padding: 9px 14px; color: var(--text-secondary); font-size: 0.82rem;">
                                                    {{ $pItem['instructions'] ?? ($pItem['remarks'] ?? '—') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- Central Billing & Cashier Invoice Breakdown -->
                    <div style="margin-top: 1.5rem; background: var(--navy-dark); border: 1px solid var(--navy-border); border-radius: var(--radius-sm); padding: 1.25rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem; flex-wrap: wrap; gap: 0.5rem;">
                            <h4 style="font-size: 0.88rem; font-weight: 700; color: var(--gold-primary); text-transform: uppercase; letter-spacing: 0.05em; margin: 0; display: flex; align-items: center; gap: 0.4rem;">
                                <span>🧾</span> Cashier Billing Breakdown
                            </h4>
                            @if($bill)
                                <span class="badge {{ $bill->payment_status === 'paid' ? 'badge-success' : 'badge-warning' }}" style="font-size: 0.75rem;">
                                    {{ ucfirst($bill->payment_status) }} (Invoice: {{ $bill->invoice_no }})
                                </span>
                            @endif
                        </div>

                        <div style="border: 1px solid var(--black-border); border-radius: var(--radius-sm); overflow: hidden; background: rgba(4, 7, 13, 0.4);">
                            <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                                <thead>
                                    <tr style="background: rgba(11, 25, 44, 0.8); border-bottom: 1px solid var(--black-border);">
                                        <th style="padding: 10px 14px; text-align: left; color: var(--gold-light); font-size: 0.78rem; text-transform: uppercase;">Service Description</th>
                                        <th style="padding: 10px 14px; text-align: center; width: 90px; color: var(--gold-light); font-size: 0.78rem; text-transform: uppercase;">Qty</th>
                                        <th style="padding: 10px 14px; text-align: right; width: 120px; color: var(--gold-light); font-size: 0.78rem; text-transform: uppercase;">Rate</th>
                                        <th style="padding: 10px 14px; text-align: right; width: 130px; color: var(--gold-light); font-size: 0.78rem; text-transform: uppercase;">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                                        <td style="padding: 9px 14px; font-weight: 600; color: var(--white); display: flex; align-items: center; gap: 0.4rem;">
                                            <span style="font-size: 0.9rem;">🩺</span>
                                            <span>Veterinary Service: {{ ucfirst(str_replace('_', ' ', $record->service_type)) }}</span>
                                        </td>
                                        <td style="padding: 9px 14px; text-align: center; color: var(--gold-light);">1</td>
                                        <td style="padding: 9px 14px; text-align: right; color: var(--text-secondary);">₱{{ number_format($record->service_fee ?? 450.00, 2) }}</td>
                                        <td style="padding: 9px 14px; text-align: right; font-weight: 700; color: var(--gold-primary);">₱{{ number_format($record->service_fee ?? 450.00, 2) }}</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr style="border-top: 2px solid var(--gold-border); background: rgba(245, 186, 49, 0.06);">
                                        <td colspan="3" style="padding: 10px 14px; text-align: right; font-weight: 800; color: var(--white); text-transform: uppercase; font-size: 0.85rem;">
                                            Total Amount Due to Cashier:
                                        </td>
                                        <td style="padding: 10px 14px; text-align: right; font-size: 1.15rem; font-weight: 800; color: var(--gold-primary);">
                                            ₱{{ number_format($bill ? $bill->total_amount : ($record->service_fee ?? 450.00), 2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
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
                        <div>Sex: <strong style="color: var(--white);">{{ $record->pet->sex ?? 'N/A' }}</strong></div>
                        <div>Color: <strong style="color: var(--white);">{{ $record->pet->color ?? 'N/A' }}</strong></div>
                        <div>Age: <strong style="color: var(--white);">{{ $record->pet->age ?? 'N/A' }}</strong></div>
                        @if($record->pet?->birth_date)
                            <div>Birthdate: <strong style="color: var(--gold-light);">{{ $record->pet->birth_date->format('M d, Y') }}</strong></div>
                        @endif
                    </div>

                    <hr style="border: 0; border-top: 1px solid var(--black-border); margin: 1rem 0;">

                    <div>
                        <span style="font-size: 0.72rem; text-transform: uppercase; color: var(--text-muted);">Owner Details</span>
                        <div style="font-weight: 700; color: var(--white);">{{ $record->owner->full_name ?? 'N/A' }}</div>
                        <div style="font-size: 0.8rem; color: var(--gold-light);">Key: {{ $record->owner->client_code ?? '' }}</div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">{{ $record->owner->contact_number ?? '' }}</div>
                        <div style="font-size: 0.78rem; color: var(--text-muted);">{{ $record->owner->address ?? '' }}</div>
                        @if($record->owner?->email)
                            <div style="font-size: 0.75rem; color: var(--text-muted);">✉️ {{ $record->owner->email }}</div>
                        @endif
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

    <!-- ==================== MODAL: COMPLETE / EDIT EXAMINATION & BILLING ==================== -->
    <div class="modal-backdrop" id="modal-examine-case">
        <div class="modal-dialog modal-xl" style="max-width: 1100px;">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">🩺</div>
                    <div>
                        <h4 class="modal-title">Patient Examination Form — Case {{ $record->record_code }}</h4>
                        <span style="font-size: 0.75rem; color: var(--gold-light);">
                            Patient: <strong>{{ $record->pet->name ?? 'N/A' }}</strong> ({{ $record->pet->species ?? '' }}) • Owner: <strong>{{ $record->owner->full_name ?? 'N/A' }}</strong>
                        </span>
                    </div>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form action="{{ route('vet.medical.update', $record->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body" style="padding: 1.25rem 1.5rem; max-height: calc(100vh - 180px); overflow-y: auto;">
                    <div style="display: grid; grid-template-columns: 1.05fr 1fr; gap: 1.5rem; align-items: start;">
                        
                        <!-- ==================== LEFT COLUMN (Exact Blueprint Layout) ==================== -->
                        <div style="display: flex; flex-direction: column; gap: 1.15rem;">
                            <!-- 1. Date of Visit -->
                            <div class="form-group">
                                <label class="form-label" style="font-weight: 700; color: var(--gold-primary);">📅 Date of Visit <span class="req">*</span></label>
                                <input type="date" name="visit_date" class="form-control" value="{{ $record->visit_date ? $record->visit_date->format('Y-m-d') : ($record->created_at ? $record->created_at->format('Y-m-d') : date('Y-m-d')) }}" required>
                            </div>

                            <!-- 2. Physical Vitals -->
                            <div style="background: rgba(11, 25, 44, 0.45); border: 1px solid var(--navy-border); border-radius: var(--radius-sm); padding: 1rem;">
                                <h5 style="color: var(--gold-primary); font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">
                                    🌡️ Physical Vitals
                                </h5>
                                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.65rem;">
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label class="form-label" style="font-size: 0.75rem;">Temp (°C)</label>
                                        <input type="text" name="temperature" class="form-control" value="{{ $record->temperature }}" placeholder="e.g. 38.5°C">
                                    </div>
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label class="form-label" style="font-size: 0.75rem;">Weight (BW)</label>
                                        <input type="text" name="body_weight" class="form-control" value="{{ $record->body_weight }}" placeholder="e.g. 5.2 kg">
                                    </div>
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label class="form-label" style="font-size: 0.75rem;">Body Score</label>
                                        <input type="text" name="body_score" class="form-control" value="{{ $record->body_score }}" placeholder="e.g. 3/5">
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Veterinarian Notes / Advice -->
                            <div class="form-group">
                                <label class="form-label" style="font-weight: 700; color: var(--gold-light);">👨‍⚕️ Veterinarian Notes / Advice</label>
                                <textarea name="veterinarians_notes" class="form-control" rows="3" placeholder="Diet recommendations, home-care instructions, advice for pet owner...">{{ $record->veterinarians_notes }}</textarea>
                            </div>

                            <!-- 4. Consultation Service Fee Input Banner -->
                            <div style="background: rgba(245, 186, 49, 0.08); border: 1.5px solid var(--gold-border); border-radius: var(--radius-sm); padding: 0.85rem 1.15rem; display: flex; justify-content: space-between; align-items: center; gap: 1rem;">
                                <div>
                                    <label class="form-label" style="font-weight: 700; color: var(--gold-light); font-size: 0.88rem; margin-bottom: 2px; display: flex; align-items: center; gap: 0.4rem;">
                                        <span>🩺</span> Consultation / Service Fee (₱) <span class="req">*</span>
                                    </label>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">
                                        Ang halagang ito lamang ang ipapasa sa Cashier Billing.
                                    </div>
                                </div>
                                <input type="number" step="0.01" min="0" name="service_fee" class="form-control" value="{{ $record->service_fee ?? 450.00 }}" required style="max-width: 150px; font-weight: 800; text-align: right; color: var(--gold-primary); font-size: 1.1rem; border-color: var(--gold-border);">
                            </div>

                            <!-- 5. Prescribed Items & Supplies Advice Table (Clinical Notes only) -->
                            <div style="background: var(--navy-dark); border: 1.5px solid var(--navy-border); border-radius: var(--radius-sm); padding: 1rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.65rem; flex-wrap: wrap; gap: 0.5rem;">
                                    <div>
                                        <h5 style="color: var(--gold-light); font-size: 0.85rem; font-weight: 800; text-transform: uppercase; margin: 0; display: flex; align-items: center; gap: 0.35rem;">
                                            <span>📋</span> Prescribed Items & Supplies Advice
                                        </h5>
                                        <span style="font-size: 0.72rem; color: var(--text-muted);">
                                            Advice / notes para sa pet owner (hal. ULTRA DOG, Dewormer, Vitamins) — <em>Hindi isinasama sa cashier bill</em>
                                        </span>
                                    </div>
                                    <button type="button" id="btn-add-exam-item" class="btn btn-gold btn-sm" style="font-size: 0.75rem; padding: 4px 10px; font-weight: 700;">
                                        ➕ Add Advice Row
                                    </button>
                                </div>

                                <div style="max-height: 220px; overflow-y: auto; margin-bottom: 0.75rem; border: 1px solid var(--black-border); border-radius: var(--radius-sm); background: rgba(4, 7, 13, 0.4);">
                                    <table style="width: 100%; border-collapse: collapse; font-size: 0.82rem;">
                                        <thead style="position: sticky; top: 0; background: var(--navy-dark); z-index: 2; border-bottom: 1px solid var(--black-border);">
                                            <tr>
                                                <th style="padding: 8px; text-align: left; color: var(--gold-light); font-size: 0.75rem; text-transform: uppercase;">Item / Recommended Supply</th>
                                                <th style="padding: 8px; width: 85px; text-align: center; color: var(--gold-light); font-size: 0.75rem; text-transform: uppercase;">Qty</th>
                                                <th style="padding: 8px; width: 100px; text-align: right; color: var(--gold-light); font-size: 0.75rem; text-transform: uppercase;">Est. Price (₱)</th>
                                                <th style="padding: 8px; text-align: left; color: var(--gold-light); font-size: 0.75rem; text-transform: uppercase;">Directions / Remarks</th>
                                                <th style="padding: 8px; width: 35px; text-align: center;"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="exam-items-tbody">
                                            @php
                                                $initialItems = !empty($record->prescribed_items) ? $record->prescribed_items : [];
                                            @endphp

                                            @forelse($initialItems as $idx => $it)
                                                <tr class="exam-item-row" style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                                    <td style="padding: 6px;">
                                                        <input type="text" name="items[{{ $idx }}][name]" class="form-control form-control-sm item-name" placeholder="e.g. ULTRA DOG / Dewormer" value="{{ $it['name'] ?? '' }}" style="font-size: 0.82rem;" required>
                                                    </td>
                                                    <td style="padding: 6px; width: 85px;">
                                                        <input type="text" name="items[{{ $idx }}][quantity]" class="form-control form-control-sm" placeholder="1 bag" value="{{ $it['quantity'] ?? '' }}" style="font-size: 0.82rem; text-align: center;">
                                                    </td>
                                                    <td style="padding: 6px; width: 100px;">
                                                        <input type="number" step="0.01" min="0" name="items[{{ $idx }}][price]" class="form-control form-control-sm" placeholder="0.00" value="{{ $it['price'] ?? '' }}" style="font-size: 0.82rem; text-align: right;">
                                                    </td>
                                                    <td style="padding: 6px;">
                                                        <input type="text" name="items[{{ $idx }}][remarks]" class="form-control form-control-sm" placeholder="e.g. Special diet / Daily with meal" value="{{ $it['instructions'] ?? ($it['remarks'] ?? '') }}" style="font-size: 0.82rem;">
                                                    </td>
                                                    <td style="padding: 6px; width: 35px; text-align: center;">
                                                        <button type="button" class="btn btn-ghost btn-sm btn-remove-item" style="color: #ef4444; padding: 2px 4px; font-size: 0.85rem;" title="Remove row">
                                                            🗑️
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <!-- Row will be added dynamically by JS -->
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div id="no-items-placeholder" style="{{ count($initialItems) > 0 ? 'display: none;' : '' }} text-align: center; padding: 0.6rem; color: var(--text-muted); font-size: 0.78rem; font-style: italic;">
                                    No item recommendations added. Click <strong>"+ Add Advice Row"</strong> to record suggested supplies/food/medicines.
                                </div>

                                <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 0.4rem;">
                                    💡 <em>Tandaan: Ang mga items na ito ay masesave bilang clinical chart advice/notes para sa pet owner at hindi ipapasa sa cashier billing.</em>
                                </div>
                            </div>

                            <!-- 6. Follow-Up Schedule -->
                            <div style="background: rgba(11, 25, 44, 0.45); border: 1px solid var(--navy-border); border-radius: var(--radius-sm); padding: 0.85rem 1rem;">
                                <h5 style="color: var(--gold-light); font-size: 0.82rem; font-weight: 700; text-transform: uppercase; margin-bottom: 0.5rem;">
                                    🗓️ Follow-Up Schedule
                                </h5>
                                <div class="form-grid">
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label class="form-label" style="font-size: 0.75rem;">Follow Up Date</label>
                                        <input type="date" name="follow_up_date" class="form-control" value="{{ $record->follow_up_date ? $record->follow_up_date->format('Y-m-d') : '' }}">
                                    </div>
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label class="form-label" style="font-size: 0.75rem;">Purpose / Notes</label>
                                        <input type="text" name="follow_up_notes" class="form-control" placeholder="e.g. Re-evaluation / suture removal" value="{{ $record->follow_up_notes }}">
                                    </div>
                                </div>
                            </div>

                            <!-- 7. STATUS Selector -->
                            <div class="form-group">
                                <label class="form-label" style="font-weight: 700; color: var(--gold-light);">🏷️ STATUS <span class="req">*</span></label>
                                <select name="status" class="form-select" required style="font-weight: 600; font-size: 0.9rem;">
                                    <option value="completed" {{ $record->status === 'completed' || $record->status === 'ongoing' ? 'selected' : '' }}>✅ Completed (Send to Cashier Billing)</option>
                                    <option value="ongoing" {{ $record->status === 'ongoing' ? '' : '' }}>🟡 Ongoing / In-Progress</option>
                                    <option value="billed" {{ $record->status === 'billed' ? 'selected' : '' }}>🟢 Billed / Settled</option>
                                </select>
                                <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 3px;">
                                    💡 Selecting <strong>"Completed"</strong> automatically routes the consultation fee to the Cashier Desk.
                                </div>
                            </div>
                        </div>

                        <!-- ==================== RIGHT COLUMN (Exact Blueprint Layout) ==================== -->
                        <div style="display: flex; flex-direction: column; gap: 1.15rem;">
                            <!-- 1. Purpose / Exam Note / Complaint -->
                            <div class="form-group">
                                <label class="form-label" style="font-weight: 700; color: var(--gold-primary);">📝 Purpose / Exam Note / Complaint</label>
                                <textarea name="history_taking" class="form-control" rows="4" placeholder="Symptoms, observed condition, patient history, client complaint...">{{ $record->history_taking }}</textarea>
                            </div>

                            <!-- 2. Clinical Assessment (Diagnosis) -->
                            <div class="form-group">
                                <label class="form-label" style="font-weight: 700; color: var(--gold-primary);">🩺 Clinical Assessment / Diagnosis <span class="req">*</span></label>
                                <textarea name="diagnosis" class="form-control" rows="3" placeholder="e.g. Acute Gastroenteritis, Canine Parvovirus, Otitis Externa..." required>{{ $record->diagnosis }}</textarea>
                            </div>

                            <!-- In-Clinic Medication / Treatment (Optional) -->
                            <div class="form-group">
                                <label class="form-label" style="font-size: 0.8rem; color: var(--text-secondary);">💊 Medication / In-Clinic Treatment (Optional)</label>
                                <textarea name="medication_treatment" class="form-control" rows="2" placeholder="Injections, intravenous fluids, administered treatments...">{{ $record->medication_treatment }}</textarea>
                            </div>

                            <!-- 3. Laboratory / Test Notes -->
                            <div style="background: rgba(11, 25, 44, 0.45); border: 1px dashed var(--gold-border); border-radius: var(--radius-sm); padding: 0.85rem 1rem;">
                                <label class="form-label" style="font-weight: 700; color: var(--gold-light); margin-bottom: 0.35rem;">🔬 Laboratory / Test Notes</label>
                                <textarea name="laboratory_notes" class="form-control" rows="2" placeholder="e.g. CBC Normal, Parvo Rapid Test Negative, X-Ray clear" style="margin-bottom: 0.5rem;">{{ $record->laboratory_notes }}</textarea>
                                
                                <label class="form-label" style="margin-bottom: 0.25rem; font-size: 0.75rem; color: var(--text-muted);">📎 Attach / Replace Lab Result File</label>
                                <input type="file" name="lab_results" class="form-control" accept="image/*,.pdf,.doc,.docx" style="padding: 4px; font-size: 0.8rem;">
                                @if($record->attached_lab_results)
                                    <div style="font-size: 0.72rem; color: var(--gold-light); margin-top: 3px;">
                                        Current file: {{ basename($record->attached_lab_results) }}
                                    </div>
                                @endif
                            </div>

                            <!-- 4. Rx Prescribe Take-Home Medicine -->
                            <div style="background: rgba(245, 186, 49, 0.05); border: 1px solid var(--gold-border); border-radius: var(--radius-sm); padding: 1rem;">
                                <h5 style="color: var(--gold-light); font-size: 0.84rem; font-weight: 700; text-transform: uppercase; margin-bottom: 0.65rem; display: flex; align-items: center; gap: 0.4rem;">
                                    <span>💊</span> Rx Prescribe Take-Home Medicine
                                </h5>
                                <div class="form-group" style="margin-bottom: 0.75rem;">
                                    <label class="form-label" style="font-size: 0.78rem;">Medication Details, Strength, Dosage & Frequency</label>
                                    <textarea name="prescribe_rx" class="form-control" rows="4" placeholder="e.g.&#10;1. Amoxicillin 250mg - 1 tab BID for 7 days&#10;2. Nutriplus Gel - 1 tsp daily&#10;3. Eye Drops - 2 drops TID">{{ $record->prescription ? $record->prescription->rx_details : '' }}</textarea>
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label" style="font-size: 0.78rem;">Rx Instructions / Precautions</label>
                                    <input type="text" name="rx_instructions" class="form-control" placeholder="e.g. Give after meals. Keep refrigerated. Finish full antibiotic course." value="{{ $record->prescription ? $record->prescription->instructions : '' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Action Buttons (Cancel & Save) -->
                <div class="modal-footer" style="padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                    <button type="button" class="btn btn-ghost" data-modal-close style="font-size: 0.88rem; padding: 0.6rem 1.25rem;">Cancel</button>
                    <button type="submit" class="btn btn-gold" style="font-weight: 700; font-size: 0.92rem; padding: 0.65rem 1.5rem; display: inline-flex; align-items: center; gap: 0.5rem;">
                        💾 SAVE Examination & Send to Cashier
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script for Prescribed Items & Supplies Advice Table -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const tableBody = document.getElementById('exam-items-tbody');
        const addItemBtn = document.getElementById('btn-add-exam-item');
        const noItemsPlaceholder = document.getElementById('no-items-placeholder');

        if (!tableBody) return;

        function updatePlaceholder() {
            const rows = tableBody.querySelectorAll('tr.exam-item-row');
            if (noItemsPlaceholder) {
                noItemsPlaceholder.style.display = rows.length === 0 ? 'block' : 'none';
            }
        }

        function createRow(name = '', qty = '', price = '', remarks = '') {
            const index = tableBody.querySelectorAll('tr.exam-item-row').length + '_' + Date.now();
            const tr = document.createElement('tr');
            tr.className = 'exam-item-row';
            tr.style.borderBottom = '1px solid rgba(255,255,255,0.05)';
            tr.innerHTML = `
                <td style="padding: 6px;">
                    <input type="text" name="items[${index}][name]" class="form-control form-control-sm item-name" placeholder="e.g. ULTRA DOG / Dewormer" value="${name.replace(/"/g, '&quot;')}" style="font-size: 0.82rem;" required autofocus>
                </td>
                <td style="padding: 6px; width: 85px;">
                    <input type="text" name="items[${index}][quantity]" class="form-control form-control-sm" placeholder="1 bag" value="${qty}" style="font-size: 0.82rem; text-align: center;">
                </td>
                <td style="padding: 6px; width: 100px;">
                    <input type="number" step="0.01" min="0" name="items[${index}][price]" class="form-control form-control-sm" placeholder="0.00" value="${price}" style="font-size: 0.82rem; text-align: right;">
                </td>
                <td style="padding: 6px;">
                    <input type="text" name="items[${index}][remarks]" class="form-control form-control-sm" placeholder="e.g. Special diet / Daily with meal" value="${remarks.replace(/"/g, '&quot;')}" style="font-size: 0.82rem;">
                </td>
                <td style="padding: 6px; width: 35px; text-align: center;">
                    <button type="button" class="btn btn-ghost btn-sm btn-remove-item" style="color: #ef4444; padding: 2px 4px; font-size: 0.85rem;" title="Remove row">
                        🗑️
                    </button>
                </td>
            `;
            tableBody.appendChild(tr);

            tr.querySelector('.btn-remove-item').addEventListener('click', function () {
                tr.remove();
                updatePlaceholder();
            });

            updatePlaceholder();
        }

        if (addItemBtn) {
            addItemBtn.addEventListener('click', function () {
                createRow('', '1', '', '');
            });
        }

        // Attach listeners to initial rendered rows
        tableBody.querySelectorAll('tr.exam-item-row').forEach(function (row) {
            const rmBtn = row.querySelector('.btn-remove-item');
            if (rmBtn) {
                rmBtn.addEventListener('click', function () {
                    row.remove();
                    updatePlaceholder();
                });
            }
        });

        updatePlaceholder();
    });
    </script>
@endsection
