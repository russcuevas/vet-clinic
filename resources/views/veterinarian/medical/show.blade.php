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

    <!-- Inpatient Confinement Status Banner -->
    @if(isset($existingAdmission) && $existingAdmission)
        <div style="background: rgba(147, 51, 234, 0.12); border: 1.5px solid rgba(147, 51, 234, 0.45); border-radius: var(--radius-sm); padding: 0.85rem 1.25rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <span style="font-size: 1.5rem;">🏥</span>
                <div>
                    <div style="font-weight: 700; color: #c084fc; font-size: 0.95rem; display: flex; align-items: center; gap: 0.45rem;">
                        <span>Inpatient Confinement Active — {{ $existingAdmission->boarding_days }} Day(s) Stay</span>
                        <span class="badge badge-gold" style="font-size: 0.7rem; padding: 2px 7px;">🏥 Confined</span>
                    </div>
                    <div style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 2px;">
                        Admission Code: <strong>{{ $existingAdmission->appointment_code }}</strong> • Rate: <strong>₱{{ number_format($existingAdmission->daily_rate, 2) }}/day</strong> (Total: <strong>₱{{ number_format($existingAdmission->total_price, 2) }}</strong>) • Care & Cage Notes: {{ $existingAdmission->purpose_examination_notes ?? 'In-clinic observation' }}
                    </div>
                </div>
            </div>
            <a href="{{ route('vet.admission.index') }}" class="btn btn-navy btn-sm" style="font-size: 0.8rem; border-color: rgba(147, 51, 234, 0.5);">
                🏥 View in Pet Admission Desk →
            </a>
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

                    <!-- Laboratory Tests & Medical Services Performed -->
                    @if(!empty($record->prescribed_items) && is_array($record->prescribed_items) && count($record->prescribed_items) > 0)
                        <div style="margin-top: 1.5rem; background: var(--navy-dark); border: 1px solid var(--gold-border); border-radius: var(--radius-sm); padding: 1.25rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem; flex-wrap: wrap; gap: 0.5rem;">
                                <h4 style="font-size: 0.88rem; font-weight: 700; color: var(--gold-light); text-transform: uppercase; letter-spacing: 0.05em; margin: 0; display: flex; align-items: center; gap: 0.4rem;">
                                    <span>🔬</span> Laboratory Tests & Medical Services Performed
                                </h4>
                                <span class="badge badge-gold" style="font-size: 0.72rem;">
                                    Queued to Cashier Billing
                                </span>
                            </div>

                            <div style="border: 1px solid var(--black-border); border-radius: var(--radius-sm); overflow: hidden; background: rgba(4, 7, 13, 0.4);">
                                <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                                    <thead>
                                        <tr style="background: rgba(11, 25, 44, 0.8); border-bottom: 1px solid var(--black-border);">
                                            <th style="padding: 10px 14px; text-align: left; color: var(--gold-light); font-size: 0.78rem; text-transform: uppercase;">Laboratory Test / Medical Service</th>
                                            <th style="padding: 10px 14px; text-align: center; width: 90px; color: var(--gold-light); font-size: 0.78rem; text-transform: uppercase;">Qty</th>
                                            <th style="padding: 10px 14px; text-align: right; width: 120px; color: var(--gold-light); font-size: 0.78rem; text-transform: uppercase;">Rate / Price</th>
                                            <th style="padding: 10px 14px; text-align: right; width: 120px; color: var(--gold-light); font-size: 0.78rem; text-transform: uppercase;">Total</th>
                                            <th style="padding: 10px 14px; text-align: left; color: var(--gold-light); font-size: 0.78rem; text-transform: uppercase;">Directions / Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $servSum = 0; @endphp
                                        @foreach($record->prescribed_items as $pItem)
                                            @php
                                                $q = floatval($pItem['quantity'] ?? 1);
                                                $p = floatval($pItem['price'] ?? 0);
                                                $tot = $q * $p;
                                                $servSum += $tot;
                                            @endphp
                                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                                                <td style="padding: 9px 14px; font-weight: 600; color: var(--white); display: flex; align-items: center; gap: 0.4rem;">
                                                    <span>🔬</span>
                                                    <span>{{ $pItem['name'] ?? '' }}</span>
                                                </td>
                                                <td style="padding: 9px 14px; text-align: center; color: var(--gold-light);">{{ $pItem['quantity'] ?? '1' }}</td>
                                                <td style="padding: 9px 14px; text-align: right; color: var(--text-secondary);">
                                                    {{ !empty($pItem['price']) ? '₱' . number_format($pItem['price'], 2) : '₱0.00' }}
                                                </td>
                                                <td style="padding: 9px 14px; text-align: right; font-weight: 700; color: var(--gold-primary);">
                                                    ₱{{ number_format($tot, 2) }}
                                                </td>
                                                <td style="padding: 9px 14px; color: var(--text-secondary); font-size: 0.82rem;">
                                                    {{ $pItem['instructions'] ?? ($pItem['remarks'] ?? '—') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr style="background: rgba(212, 175, 55, 0.08); border-top: 1px solid var(--gold-border);">
                                            <td colspan="3" style="padding: 8px 14px; font-weight: 700; color: var(--gold-light); text-align: right;">Tests & Services Subtotal:</td>
                                            <td style="padding: 8px 14px; text-align: right; font-weight: 800; color: var(--gold-primary); font-size: 0.95rem;">₱{{ number_format($servSum, 2) }}</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
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
        <div class="modal-dialog modal-xl" style="max-width: 1240px; width: 95vw;">
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
                <div class="modal-body" style="padding: 1.25rem 1.5rem; max-height: calc(100vh - 175px); overflow-y: auto;">
                    
                    <!-- ==================== SECTION 1: CLINICAL OBSERVATION & DIAGNOSIS (2-Column Grid) ==================== -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem; align-items: start;">
                        
                        <!-- Left Column: Visit Date, Vitals, Complaint & Assessment -->
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <!-- 1. Date of Visit -->
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-weight: 700; color: var(--gold-primary); font-size: 0.85rem;">📅 Date of Visit <span class="req">*</span></label>
                                <input type="date" name="visit_date" class="form-control" value="{{ $record->visit_date ? $record->visit_date->format('Y-m-d') : ($record->created_at ? $record->created_at->format('Y-m-d') : date('Y-m-d')) }}" required>
                            </div>

                            <!-- 2. Physical Vitals -->
                            <div style="background: rgba(11, 25, 44, 0.5); border: 1px solid var(--navy-border); border-radius: var(--radius-sm); padding: 0.9rem;">
                                <h5 style="color: var(--gold-primary); font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.65rem;">
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

                            <!-- 3. Purpose / Exam Note / Complaint -->
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-weight: 700; color: var(--gold-primary); font-size: 0.85rem;">📝 Purpose / Exam Note / Complaint</label>
                                <textarea name="history_taking" class="form-control" rows="3" placeholder="Symptoms, observed condition, patient history, client complaint...">{{ $record->history_taking }}</textarea>
                            </div>

                            <!-- 4. Clinical Assessment (Diagnosis) -->
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-weight: 700; color: var(--gold-primary); font-size: 0.85rem;">🩺 Clinical Assessment / Diagnosis <span class="req">*</span></label>
                                <textarea name="diagnosis" class="form-control" rows="2" placeholder="e.g. Acute Gastroenteritis, Canine Parvovirus, Otitis Externa..." required>{{ $record->diagnosis }}</textarea>
                            </div>
                        </div>

                        <!-- Right Column: Medication, Vet Notes, Lab Notes & Take-Home Rx -->
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <!-- In-Clinic Medication / Treatment (Optional) -->
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.82rem; font-weight: 700; color: var(--text-secondary);">💊 Medication / In-Clinic Treatment (Optional)</label>
                                <textarea name="medication_treatment" class="form-control" rows="2" placeholder="Injections, intravenous fluids, administered treatments...">{{ $record->medication_treatment }}</textarea>
                            </div>

                            <!-- Veterinarian Clinical Notes / Advice -->
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-weight: 700; color: var(--gold-light); font-size: 0.82rem;">👨‍⚕️ Veterinarian Notes / Advice</label>
                                <textarea name="veterinarians_notes" class="form-control" rows="2" placeholder="Diet recommendations, home-care instructions, advice for pet owner...">{{ $record->veterinarians_notes }}</textarea>
                            </div>

                            <!-- Laboratory / Diagnostic Notes & File Attachment -->
                            <div style="background: rgba(11, 25, 44, 0.5); border: 1px dashed var(--gold-border); border-radius: var(--radius-sm); padding: 0.85rem;">
                                <label class="form-label" style="font-weight: 700; color: var(--gold-light); font-size: 0.82rem; margin-bottom: 0.35rem;">🔬 Laboratory / Test Notes</label>
                                <textarea name="laboratory_notes" class="form-control" rows="2" placeholder="e.g. CBC Normal, Parvo Rapid Test Negative, X-Ray clear" style="margin-bottom: 0.5rem; font-size: 0.82rem;">{{ $record->laboratory_notes }}</textarea>
                                
                                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                                    <label class="form-label" style="margin-bottom: 0; font-size: 0.75rem; color: var(--text-muted);">📎 Attach / Replace Lab Result File</label>
                                    @if($record->attached_lab_results)
                                        <span style="font-size: 0.72rem; color: var(--gold-light);">
                                            Current: <strong>{{ basename($record->attached_lab_results) }}</strong>
                                        </span>
                                    @endif
                                </div>
                                <input type="file" name="lab_results" class="form-control" accept="image/*,.pdf,.doc,.docx" style="padding: 4px; font-size: 0.8rem; margin-top: 4px;">
                            </div>

                            <!-- Rx Prescribe Take-Home Medicine -->
                            <div style="background: rgba(245, 186, 49, 0.05); border: 1px solid var(--gold-border); border-radius: var(--radius-sm); padding: 0.85rem;">
                                <h5 style="color: var(--gold-light); font-size: 0.82rem; font-weight: 700; text-transform: uppercase; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.4rem;">
                                    <span>💊</span> Rx Prescribe Take-Home Medicine
                                </h5>
                                <div class="form-group" style="margin-bottom: 0.5rem;">
                                    <textarea name="prescribe_rx" class="form-control" rows="2" placeholder="Medication details, strength, dosage & frequency..." style="font-size: 0.82rem;">{{ $record->prescription ? $record->prescription->rx_details : '' }}</textarea>
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <input type="text" name="rx_instructions" class="form-control" placeholder="Special directions (e.g. Give after meals with food)" value="{{ $record->prescription ? $record->prescription->instructions : '' }}" style="font-size: 0.82rem;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== SECTION 2: 🔬 LABORATORY TESTS & MEDICAL SERVICES (FULL-WIDTH EXPANDED ROW) ==================== -->
                    <div style="background: var(--navy-dark); border: 1.5px solid var(--gold-border); border-radius: var(--radius-sm); padding: 1.25rem; margin-bottom: 1.25rem; box-shadow: 0 4px 14px rgba(0,0,0,0.25);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem; flex-wrap: wrap; gap: 0.75rem;">
                            <div>
                                <h4 style="color: var(--gold-light); font-size: 0.95rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; margin: 0 0 2px 0; display: flex; align-items: center; gap: 0.5rem;">
                                    <span style="font-size: 1.2rem;">🔬</span> LABORATORY TESTS & MEDICAL SERVICES PERFORMED
                                </h4>
                                <span style="font-size: 0.78rem; color: var(--text-muted);">
                                    Lahat ng laboratory tests at medical procedures na ilalagay dito ay <strong>awtomatikong ipapasa sa Cashier Billing queue</strong>.
                                </span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.65rem;">
                                <span class="badge badge-gold" style="font-size: 0.75rem; padding: 5px 10px;">
                                    Queued to Cashier Billing
                                </span>
                                <button type="button" id="btn-add-exam-item" class="btn btn-gold btn-sm" style="font-size: 0.82rem; padding: 6px 14px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem;">
                                    <span>➕</span> Add Lab / Service Item
                                </button>
                            </div>
                        </div>

                        <!-- Quick Service Suggestion Pills (Full Row) -->
                        <div style="display: flex; gap: 0.4rem; flex-wrap: wrap; align-items: center; margin-bottom: 0.85rem; background: rgba(4, 7, 13, 0.4); padding: 0.6rem 0.85rem; border-radius: var(--radius-sm); border: 1px solid var(--navy-border);">
                            <span style="font-size: 0.75rem; font-weight: 700; color: var(--gold-primary); display: inline-flex; align-items: center; gap: 0.25rem;">
                                ⚡ Quick Add:
                            </span>
                            <button type="button" class="btn btn-navy btn-sm btn-quick-service" data-name="Complete Blood Count (CBC)" data-price="550.00" style="font-size: 0.75rem; padding: 3px 9px;">+ CBC (₱550)</button>
                            <button type="button" class="btn btn-navy btn-sm btn-quick-service" data-name="Blood Chemistry Panel" data-price="1200.00" style="font-size: 0.75rem; padding: 3px 9px;">+ Blood Chem (₱1.2k)</button>
                            <button type="button" class="btn btn-navy btn-sm btn-quick-service" data-name="Parvo / Distemper Ag Rapid Test" data-price="650.00" style="font-size: 0.75rem; padding: 3px 9px;">+ Parvo Test (₱650)</button>
                            <button type="button" class="btn btn-navy btn-sm btn-quick-service" data-name="Ultrasound Examination" data-price="900.00" style="font-size: 0.75rem; padding: 3px 9px;">+ Ultrasound (₱900)</button>
                            <button type="button" class="btn btn-navy btn-sm btn-quick-service" data-name="Digital X-Ray (1 View)" data-price="850.00" style="font-size: 0.75rem; padding: 3px 9px;">+ X-Ray (₱850)</button>
                            <button type="button" class="btn btn-navy btn-sm btn-quick-service" data-name="Urinary Catheterization" data-price="800.00" style="font-size: 0.75rem; padding: 3px 9px;">+ Catheter (₱800)</button>
                            <button type="button" class="btn btn-navy btn-sm btn-quick-service" data-name="IV Fluid Therapy & Cannulation" data-price="450.00" style="font-size: 0.75rem; padding: 3px 9px;">+ IV Therapy (₱450)</button>
                            <button type="button" class="btn btn-navy btn-sm btn-quick-service" data-name="Urinalysis Complete" data-price="350.00" style="font-size: 0.75rem; padding: 3px 9px;">+ Urinalysis (₱350)</button>
                            <button type="button" class="btn btn-navy btn-sm btn-quick-service" data-name="Fecalysis Examination" data-price="300.00" style="font-size: 0.75rem; padding: 3px 9px;">+ Fecalysis (₱300)</button>
                        </div>

                        <!-- Wide, Expanded Laboratory & Medical Services Table -->
                        <div style="max-height: 320px; overflow-y: auto; border: 1px solid var(--black-border); border-radius: var(--radius-sm); background: rgba(4, 7, 13, 0.55);">
                            <table style="width: 100%; border-collapse: collapse; font-size: 0.88rem;">
                                <thead style="position: sticky; top: 0; background: rgba(11, 25, 44, 0.95); z-index: 2; border-bottom: 1.5px solid var(--black-border);">
                                    <tr>
                                        <th style="padding: 10px 14px; text-align: left; color: var(--gold-light); font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.04em;">Laboratory Test / Medical Service</th>
                                        <th style="padding: 10px 14px; width: 90px; text-align: center; color: var(--gold-light); font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.04em;">Qty</th>
                                        <th style="padding: 10px 14px; width: 130px; text-align: right; color: var(--gold-light); font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.04em;">Price (₱)</th>
                                        <th style="padding: 10px 14px; width: 130px; text-align: right; color: var(--gold-light); font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.04em;">Subtotal</th>
                                        <th style="padding: 10px 14px; text-align: left; color: var(--gold-light); font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.04em;">Directions / Clinical Remarks / Findings</th>
                                        <th style="padding: 10px 14px; width: 45px; text-align: center;"></th>
                                    </tr>
                                </thead>
                                <tbody id="exam-items-tbody">
                                    @php
                                        $initialItems = !empty($record->prescribed_items) ? $record->prescribed_items : [];
                                    @endphp

                                    @forelse($initialItems as $idx => $it)
                                        @php
                                            $q = floatval($it['quantity'] ?? 1);
                                            $p = floatval($it['price'] ?? 0);
                                            $t = $q * $p;
                                        @endphp
                                        <tr class="exam-item-row" style="border-bottom: 1px solid rgba(255,255,255,0.06); transition: background 0.15s ease;">
                                            <td style="padding: 8px 12px;">
                                                <input type="text" name="items[{{ $idx }}][name]" class="form-control item-name" placeholder="e.g. Complete Blood Count (CBC)" value="{{ $it['name'] ?? '' }}" style="font-size: 0.88rem;" required>
                                            </td>
                                            <td style="padding: 8px 12px; width: 90px;">
                                                <input type="number" step="0.01" min="0.01" name="items[{{ $idx }}][quantity]" class="form-control item-qty" placeholder="1" value="{{ $it['quantity'] ?? '1' }}" style="font-size: 0.88rem; text-align: center;">
                                            </td>
                                            <td style="padding: 8px 12px; width: 130px;">
                                                <input type="number" step="0.01" min="0" name="items[{{ $idx }}][price]" class="form-control item-price" placeholder="0.00" value="{{ $it['price'] ?? '' }}" style="font-size: 0.88rem; text-align: right;">
                                            </td>
                                            <td style="padding: 8px 12px; width: 130px; text-align: right; font-weight: 700; color: var(--gold-primary); font-size: 0.92rem;" class="item-row-total">
                                                ₱{{ number_format($t, 2) }}
                                            </td>
                                            <td style="padding: 8px 12px;">
                                                <input type="text" name="items[{{ $idx }}][remarks]" class="form-control" placeholder="e.g. In-house STAT / Normal findings" value="{{ $it['instructions'] ?? ($it['remarks'] ?? '') }}" style="font-size: 0.88rem;">
                                            </td>
                                            <td style="padding: 8px 12px; width: 45px; text-align: center;">
                                                <button type="button" class="btn btn-ghost btn-sm btn-remove-item" style="color: #ef4444; padding: 4px 8px; font-size: 0.95rem; border-radius: 4px;" title="Remove row">
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

                        <div id="no-items-placeholder" style="{{ count($initialItems) > 0 ? 'display: none;' : '' }} text-align: center; padding: 1.25rem; color: var(--text-muted); font-size: 0.85rem; font-style: italic; background: rgba(4, 7, 13, 0.3); border-radius: var(--radius-sm); margin-top: 0.5rem;">
                            🔬 No laboratory tests or special medical services added yet. Click <strong>"➕ Add Lab / Service Item"</strong> or select one of the quick add buttons above.
                        </div>

                        <!-- Subtotal summary bar of tests -->
                        <div style="background: rgba(4, 7, 13, 0.7); border: 1px solid var(--navy-border); border-radius: var(--radius-sm); padding: 0.75rem 1rem; display: flex; justify-content: space-between; align-items: center; margin-top: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
                            <div style="font-size: 0.82rem; color: var(--text-secondary); display: flex; align-items: center; gap: 0.4rem;">
                                <span>📋</span> Diagnostic & Medical Services Subtotal:
                            </div>
                            <div style="font-weight: 800; color: var(--gold-light); font-size: 1.05rem;" id="modal_lab_subtotal">
                                ₱0.00
                            </div>
                        </div>
                    </div>

                    <!-- ==================== SECTION 3: 🏥 INPATIENT ADMISSION & CONFINEMENT ==================== -->
                    <div style="background: rgba(11, 25, 44, 0.65); border: 1.5px solid var(--navy-border); border-radius: var(--radius-sm); padding: 1.15rem; margin-bottom: 1.25rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                            <label style="display: flex; align-items: center; gap: 0.75rem; margin: 0; cursor: pointer; user-select: none;">
                                <input type="checkbox" name="is_admission" id="toggle_exam_admission" value="1" {{ !empty($existingAdmission) ? 'checked' : '' }} style="width: 19px; height: 19px; accent-color: var(--gold-primary); cursor: pointer;">
                                <div>
                                    <strong style="color: var(--gold-light); font-size: 0.95rem; display: flex; align-items: center; gap: 0.45rem;">
                                        <span>🏥</span> Admit Pet for Inpatient Care & Confinement
                                    </strong>
                                    <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">
                                        I-admit ang pasyente sa clinic para sa IV fluid therapy, post-op monitoring, o emergency confinement. Awtomatikong mairerehistro sa <strong>Pet Admission desk</strong> at maidadagdag sa Cashier invoice.
                                    </div>
                                </div>
                            </label>
                            <span class="badge {{ !empty($existingAdmission) ? 'badge-gold' : 'badge-navy' }}" id="admission_active_badge" style="font-size: 0.75rem; padding: 4px 10px; display: {{ !empty($existingAdmission) ? 'inline-block' : 'none' }};">
                                🏥 Inpatient Active
                            </span>
                        </div>

                        <!-- Collapsible Admission Inpatient Fields -->
                        <div id="exam_admission_fields" style="display: {{ !empty($existingAdmission) ? 'block' : 'none' }}; margin-top: 1rem; border-top: 1px dashed var(--navy-border); padding-top: 1rem;">
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 0.85rem;">
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label" style="font-size: 0.8rem; font-weight: 700;">Days of Confinement <span class="req">*</span></label>
                                    <input type="number" min="1" name="admission_days" id="exam_adm_days" class="form-control" value="{{ $existingAdmission->boarding_days ?? 1 }}" style="font-weight: 700; text-align: center; font-size: 0.9rem;">
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label" style="font-size: 0.8rem; font-weight: 700;">Daily Inpatient Rate (₱) <span class="req">*</span></label>
                                    <input type="number" step="0.01" min="0" name="daily_rate" id="exam_adm_rate" class="form-control" value="{{ $existingAdmission->daily_rate ?? 450.00 }}" style="font-weight: 700; text-align: right; font-size: 0.9rem;">
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label" style="font-size: 0.8rem; font-weight: 700;">Inpatient Subtotal</label>
                                    <div style="background: rgba(4, 7, 13, 0.6); border: 1.5px solid var(--gold-border); border-radius: var(--radius-sm); padding: 0.55rem 0.85rem; font-weight: 800; color: var(--gold-primary); font-size: 1.15rem; text-align: right; height: 38px; display: flex; align-items: center; justify-content: flex-end;" id="exam_adm_total_display">
                                        ₱{{ number_format(($existingAdmission->boarding_days ?? 1) * ($existingAdmission->daily_rate ?? 450.00), 2) }}
                                    </div>
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label" style="font-size: 0.8rem; font-weight: 700;">Assigned Attending Staff</label>
                                    <select name="assigned_employee_id" class="form-select" style="font-size: 0.85rem;">
                                        <option value="">-- Attending Staff (Optional) --</option>
                                        @if(isset($staffMembers))
                                            @foreach($staffMembers as $staff)
                                                <option value="{{ $staff->id }}" {{ (!empty($existingAdmission) && $existingAdmission->assigned_employee_id == $staff->id) ? 'selected' : '' }}>
                                                    {{ $staff->full_name }} ({{ $staff->position }})
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.8rem; font-weight: 700;">Cage No. & Care / Monitoring Instructions</label>
                                <input type="text" name="admission_notes" class="form-control" placeholder="e.g. Cage 2 - Continuous IV DLR 15 drops/min, fasting, observe urination and vomiting" value="{{ $existingAdmission->purpose_examination_notes ?? '' }}" style="font-size: 0.85rem;">
                            </div>
                        </div>
                    </div>

                    <!-- ==================== SECTION 4: BILLING TOTALS & STATUS SELECTOR (2-Column Unified Layout) ==================== -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; align-items: stretch;">
                        
                        <!-- Left: Base Consultation Fee & Grand Total Summary Box -->
                        <div style="background: rgba(245, 186, 49, 0.08); border: 1.5px solid var(--gold-border); border-radius: var(--radius-sm); padding: 1rem; display: flex; flex-direction: column; justify-content: space-between; gap: 0.85rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
                                <div>
                                    <label class="form-label" style="font-weight: 700; color: var(--gold-light); font-size: 0.88rem; margin-bottom: 2px; display: flex; align-items: center; gap: 0.4rem;">
                                        <span>🩺</span> Base Consultation / Examination Fee (₱) <span class="req">*</span>
                                    </label>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">
                                        Standard checkup / professional consultation fee.
                                    </div>
                                </div>
                                <input type="number" step="0.01" min="0" name="service_fee" id="exam_modal_service_fee" class="form-control" value="{{ $record->service_fee ?? 450.00 }}" required style="max-width: 160px; font-weight: 800; text-align: right; color: var(--gold-primary); font-size: 1.15rem; border-color: var(--gold-border);">
                            </div>

                            <!-- Live Cashier Billing Queue Grand Total Banner -->
                            <div style="background: rgba(4, 7, 13, 0.65); border: 1px solid var(--gold-border); border-radius: var(--radius-sm); padding: 0.85rem 1rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                                <div>
                                    <div style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-muted); font-weight: 600;">
                                        Total Amount Queued to Cashier:
                                    </div>
                                    <div style="font-size: 0.72rem; color: var(--text-secondary);">
                                        Consultation Fee + Lab & Services + Inpatient Admission
                                    </div>
                                </div>
                                <div style="font-weight: 800; color: var(--gold-primary); font-size: 1.35rem;" id="modal_exam_grand_total">
                                    ₱0.00
                                </div>
                            </div>
                        </div>

                        <!-- Right: Follow-Up Schedule & STATUS Selector -->
                        <div style="background: rgba(11, 25, 44, 0.5); border: 1px solid var(--navy-border); border-radius: var(--radius-sm); padding: 1rem; display: flex; flex-direction: column; justify-content: space-between; gap: 0.85rem;">
                            <!-- Follow-Up Schedule -->
                            <div>
                                <h5 style="color: var(--gold-light); font-size: 0.82rem; font-weight: 700; text-transform: uppercase; margin-bottom: 0.45rem;">
                                    🗓️ Follow-Up Schedule
                                </h5>
                                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 0.65rem;">
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label class="form-label" style="font-size: 0.72rem;">Follow Up Date</label>
                                        <input type="date" name="follow_up_date" class="form-control" value="{{ $record->follow_up_date ? $record->follow_up_date->format('Y-m-d') : '' }}" style="font-size: 0.82rem;">
                                    </div>
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label class="form-label" style="font-size: 0.72rem;">Purpose / Notes</label>
                                        <input type="text" name="follow_up_notes" class="form-control" placeholder="e.g. Re-eval / suture removal" value="{{ $record->follow_up_notes }}" style="font-size: 0.82rem;">
                                    </div>
                                </div>
                            </div>

                            <!-- STATUS Selector -->
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-weight: 700; color: var(--gold-light); font-size: 0.82rem;">🏷️ CASE STATUS <span class="req">*</span></label>
                                <select name="status" class="form-select" required style="font-weight: 600; font-size: 0.88rem;">
                                    <option value="completed" {{ $record->status === 'completed' || $record->status === 'ongoing' ? 'selected' : '' }}>✅ Completed (Send to Cashier Billing)</option>
                                    <option value="ongoing" {{ $record->status === 'ongoing' ? '' : '' }}>🟡 Ongoing / In-Progress</option>
                                    <option value="billed" {{ $record->status === 'billed' ? 'selected' : '' }}>🟢 Billed / Settled</option>
                                </select>
                                <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 3px;">
                                    💡 Selecting <strong>"Completed"</strong> automatically routes the total bill to the Cashier Desk.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Action Buttons (Cancel & Save) -->
                <div class="modal-footer" style="padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--black-border);">
                    <button type="button" class="btn btn-ghost" data-modal-close style="font-size: 0.88rem; padding: 0.6rem 1.25rem;">Cancel</button>
                    <button type="submit" class="btn btn-gold" style="font-weight: 700; font-size: 0.95rem; padding: 0.65rem 1.75rem; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: var(--gold-shadow);">
                        💾 SAVE Examination & Send to Cashier
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script for Laboratory Tests & Medical Services Table -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const tableBody = document.getElementById('exam-items-tbody');
        const addItemBtn = document.getElementById('btn-add-exam-item');
        const noItemsPlaceholder = document.getElementById('no-items-placeholder');
        const feeInput = document.getElementById('exam_modal_service_fee');
        const labSubtotalDisplay = document.getElementById('modal_lab_subtotal');
        const grandTotalDisplay = document.getElementById('modal_exam_grand_total');

        // Inpatient Admission Elements
        const toggleAdm = document.getElementById('toggle_exam_admission');
        const admFields = document.getElementById('exam_admission_fields');
        const admBadge = document.getElementById('admission_active_badge');
        const admDays = document.getElementById('exam_adm_days');
        const admRate = document.getElementById('exam_adm_rate');
        const admTotalDisplay = document.getElementById('exam_adm_total_display');

        if (!tableBody) return;

        function recalcGrandTotal() {
            let labSubtotal = 0;
            tableBody.querySelectorAll('tr.exam-item-row').forEach(function (row) {
                const qty = parseFloat(row.querySelector('.item-qty')?.value || 1) || 1;
                const price = parseFloat(row.querySelector('.item-price')?.value || 0) || 0;
                const rowTotal = qty * price;
                const totalCell = row.querySelector('.item-row-total');
                if (totalCell) {
                    totalCell.textContent = '₱' + rowTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
                labSubtotal += rowTotal;
            });

            if (labSubtotalDisplay) {
                labSubtotalDisplay.textContent = '₱' + labSubtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            // Inpatient Admission calculation
            let admTotal = 0;
            if (toggleAdm && toggleAdm.checked) {
                const d = parseFloat(admDays ? admDays.value : 1) || 0;
                const r = parseFloat(admRate ? admRate.value : 0) || 0;
                admTotal = d * r;
                if (admTotalDisplay) {
                    admTotalDisplay.textContent = '₱' + admTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
            }

            const baseFee = parseFloat(feeInput ? feeInput.value : 0) || 0;
            const grandTotal = baseFee + labSubtotal + admTotal;

            if (grandTotalDisplay) {
                grandTotalDisplay.textContent = '₱' + grandTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        }

        function updatePlaceholder() {
            const rows = tableBody.querySelectorAll('tr.exam-item-row');
            if (noItemsPlaceholder) {
                noItemsPlaceholder.style.display = rows.length === 0 ? 'block' : 'none';
            }
            recalcGrandTotal();
        }

        function createRow(name = '', qty = '1', price = '', remarks = '') {
            const index = tableBody.querySelectorAll('tr.exam-item-row').length + '_' + Date.now();
            const tr = document.createElement('tr');
            tr.className = 'exam-item-row';
            tr.style.borderBottom = '1px solid rgba(255,255,255,0.06)';
            tr.style.transition = 'background 0.15s ease';
            const q = parseFloat(qty) || 1;
            const p = parseFloat(price) || 0;
            const rowTot = q * p;
            tr.innerHTML = `
                <td style="padding: 8px 12px;">
                    <input type="text" name="items[${index}][name]" class="form-control item-name" placeholder="e.g. Complete Blood Count (CBC)" value="${name.replace(/"/g, '&quot;')}" style="font-size: 0.88rem;" required autofocus>
                </td>
                <td style="padding: 8px 12px; width: 90px;">
                    <input type="number" step="0.01" min="0.01" name="items[${index}][quantity]" class="form-control item-qty" placeholder="1" value="${qty}" style="font-size: 0.88rem; text-align: center;">
                </td>
                <td style="padding: 8px 12px; width: 130px;">
                    <input type="number" step="0.01" min="0" name="items[${index}][price]" class="form-control item-price" placeholder="0.00" value="${price}" style="font-size: 0.88rem; text-align: right;">
                </td>
                <td style="padding: 8px 12px; width: 130px; text-align: right; font-weight: 700; color: var(--gold-primary); font-size: 0.92rem;" class="item-row-total">
                    ₱${rowTot.toFixed(2)}
                </td>
                <td style="padding: 8px 12px;">
                    <input type="text" name="items[${index}][remarks]" class="form-control" placeholder="e.g. In-house STAT / Normal findings" value="${remarks.replace(/"/g, '&quot;')}" style="font-size: 0.88rem;">
                </td>
                <td style="padding: 8px 12px; width: 45px; text-align: center;">
                    <button type="button" class="btn btn-ghost btn-sm btn-remove-item" style="color: #ef4444; padding: 4px 8px; font-size: 0.95rem; border-radius: 4px;" title="Remove row">
                        🗑️
                    </button>
                </td>
            `;
            tableBody.appendChild(tr);

            tr.querySelector('.btn-remove-item').addEventListener('click', function () {
                tr.remove();
                updatePlaceholder();
            });

            tr.querySelector('.item-qty').addEventListener('input', recalcGrandTotal);
            tr.querySelector('.item-price').addEventListener('input', recalcGrandTotal);

            updatePlaceholder();
        }

        if (addItemBtn) {
            addItemBtn.addEventListener('click', function () {
                createRow('', '1', '', '');
            });
        }

        // Quick service buttons
        document.querySelectorAll('.btn-quick-service').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const sName = btn.getAttribute('data-name');
                const sPrice = btn.getAttribute('data-price');
                createRow(sName, '1', sPrice, '');
            });
        });

        // Attach listeners to initial rendered rows
        tableBody.querySelectorAll('tr.exam-item-row').forEach(function (row) {
            const rmBtn = row.querySelector('.btn-remove-item');
            if (rmBtn) {
                rmBtn.addEventListener('click', function () {
                    row.remove();
                    updatePlaceholder();
                });
            }
            const q = row.querySelector('.item-qty');
            const p = row.querySelector('.item-price');
            if (q) q.addEventListener('input', recalcGrandTotal);
            if (p) p.addEventListener('input', recalcGrandTotal);
        });

        if (feeInput) {
            feeInput.addEventListener('input', recalcGrandTotal);
        }

        // Admission toggle & inputs event listeners
        if (toggleAdm && admFields) {
            toggleAdm.addEventListener('change', function () {
                admFields.style.display = this.checked ? 'block' : 'none';
                if (admBadge) admBadge.style.display = this.checked ? 'inline-block' : 'none';
                recalcGrandTotal();
            });
        }
        if (admDays) admDays.addEventListener('input', recalcGrandTotal);
        if (admRate) admRate.addEventListener('input', recalcGrandTotal);

        updatePlaceholder();
    });
    </script>
@endsection
