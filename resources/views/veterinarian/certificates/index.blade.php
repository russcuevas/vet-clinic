@extends('layouts.app')

@php
    $title = 'Veterinary Health Certificates';
    $headerTitle = 'Veterinary Health Certificates & Travel Passes';
    $breadcrumb = 'Veterinary / Health Certificates';
@endphp

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--white); margin-bottom: 0.25rem;">
                📜 Veterinary Health Certificate Terminal
            </h2>
            <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0;">
                Generate, manage, and print official Veterinary Health Certificates for pet travel and clearance.
            </p>
        </div>
        <button type="button" class="btn btn-gold" data-modal-target="modal-generate-certificate">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>+ Generate Health Certificate</span>
        </button>
    </div>

    <!-- Quick Stats Bar -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div class="card" style="border: 1px solid var(--gold-border); background: linear-gradient(135deg, rgba(212, 175, 55, 0.08), rgba(11, 25, 44, 0.6));">
            <div class="card-body" style="padding: 1.1rem 1.25rem;">
                <div style="font-size: 0.72rem; color: var(--gold-light); text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">
                    NEXT CONTROL NUMBER
                </div>
                <div style="font-size: 1.5rem; font-weight: 800; color: #fff; font-family: monospace; margin-top: 2px;">
                    {{ $nextControlNumber }}
                </div>
                <div style="font-size: 0.72rem; color: var(--text-muted);">
                    Sequence: YY-0001 increment
                </div>
            </div>
        </div>

        <div class="card" style="border: 1px solid var(--navy-border);">
            <div class="card-body" style="padding: 1.1rem 1.25rem;">
                <div style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">
                    TOTAL CERTIFICATES
                </div>
                <div style="font-size: 1.5rem; font-weight: 800; color: var(--white); margin-top: 2px;">
                    {{ $certificates->total() }}
                </div>
                <div style="font-size: 0.72rem; color: #10b981;">
                    Official Issued Records
                </div>
            </div>
        </div>

        <div class="card" style="border: 1px solid var(--navy-border);">
            <div class="card-body" style="padding: 1.1rem 1.25rem;">
                <div style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">
                    ISSUED THIS MONTH
                </div>
                <div style="font-size: 1.5rem; font-weight: 800; color: #38bdf8; margin-top: 2px;">
                    {{ \App\Models\VeterinaryHealthCertificate::whereMonth('certificate_date', \Carbon\Carbon::today()->month)->count() }}
                </div>
                <div style="font-size: 0.72rem; color: var(--text-muted);">
                    {{ \Carbon\Carbon::today()->format('F Y') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body" style="padding: 1rem 1.25rem;">
            <form method="GET" action="{{ route('vet.certificates.index') }}" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
                <div style="flex: 1; min-width: 220px;">
                    <input type="text" name="search" class="form-control" placeholder="Search by control #, pet name, owner, or destination..." value="{{ request('search') }}">
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <label style="font-size: 0.78rem; color: var(--text-muted); margin: 0;">Date From:</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" style="width: auto;">
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <label style="font-size: 0.78rem; color: var(--text-muted); margin: 0;">Date To:</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" style="width: auto;">
                </div>
                <button type="submit" class="btn btn-navy btn-sm">Filter</button>
                @if (request()->anyFilled(['search', 'date_from', 'date_to']))
                    <a href="{{ route('vet.certificates.index') }}" class="btn btn-ghost btn-sm">Reset</a>
                @endif
            </form>
        </div>
    </div>

    <!-- Certificates Table -->
    <div class="card">
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Control #</th>
                            <th>Date Issued</th>
                            <th>Pet Description</th>
                            <th>Owner Details</th>
                            <th>Destination</th>
                            <th>Rabies Vaccination</th>
                            <th>Veterinarian</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($certificates as $cert)
                            <tr>
                                <td>
                                    <span style="font-family: monospace; font-weight: 800; color: #ef4444; font-size: 0.9rem; background: rgba(239, 68, 68, 0.1); padding: 3px 8px; border-radius: 4px; border: 1px solid rgba(239, 68, 68, 0.3);">
                                        {{ $cert->control_number }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--white); font-size: 0.85rem;">
                                        {{ $cert->certificate_date->format('M d, Y') }}
                                    </div>
                                    <div style="font-size: 0.7rem; color: var(--text-muted);">
                                        {{ $cert->certificate_date->diffForHumans() }}
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--gold-light); font-size: 0.9rem;">
                                        🐾 {{ $cert->pet_name }}
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--white);">
                                        {{ $cert->species }} • {{ $cert->breed ?: 'Mixed' }} ({{ $cert->sex ?: '-' }})
                                    </div>
                                    <div style="font-size: 0.7rem; color: var(--text-muted);">
                                        Color: {{ $cert->color ?: '-' }} | Wt: {{ $cert->weight ?: '-' }} | Age: {{ $cert->age ?: '-' }}
                                    </div>
                                    @if($cert->microchip && $cert->microchip !== 'None')
                                        <div style="font-size: 0.68rem; color: #38bdf8;">
                                            Microchip: {{ $cert->microchip }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--white);">{{ $cert->owner_name }}</div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">
                                        📍 {{ $cert->residing_at }}
                                    </div>
                                    <div style="font-size: 0.72rem; color: var(--gold-light);">
                                        📞 {{ $cert->contact_number ?: 'N/A' }}
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: #38bdf8; font-size: 0.82rem;">
                                        ✈️ {{ $cert->destination }}
                                    </div>
                                </td>
                                <td>
                                    @if($cert->rabies_vaccination_date)
                                        <div style="font-size: 0.78rem; color: #10b981; font-weight: 700;">
                                            💉 {{ $cert->rabies_vaccine_name ?: 'Rabisin' }}
                                        </div>
                                        <div style="font-size: 0.72rem; color: var(--text-muted);">
                                            Date: {{ $cert->rabies_vaccination_date->format('M d, Y') }}
                                        </div>
                                        <div style="font-size: 0.68rem; color: var(--gold-light);">
                                            Lot: {{ $cert->rabies_lot_number ?: 'N/A' }}
                                        </div>
                                    @else
                                        <span style="font-size: 0.75rem; color: var(--text-muted);">Not specified</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-size: 0.8rem; font-weight: 700; color: var(--white);">
                                        {{ $cert->veterinarian_name }}
                                    </div>
                                    <div style="font-size: 0.68rem; color: var(--text-muted);">
                                        PRC: {{ $cert->prc_no ?: '0009324' }}
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.35rem; align-items: center;">
                                        <a href="{{ route('vet.certificates.print', $cert) }}" target="_blank" class="btn btn-gold btn-sm" style="padding: 0.3rem 0.65rem;" title="Print Certificate Document">
                                            🖨️ Print
                                        </a>
                                        <button type="button" class="btn btn-navy btn-sm" style="padding: 0.3rem 0.5rem;" data-modal-target="modal-edit-cert-{{ $cert->id }}" title="Edit Details">
                                            ✏️
                                        </button>
                                        <form action="{{ route('vet.certificates.destroy', $cert) }}" method="POST" onsubmit="return confirm('Delete Veterinary Health Certificate {{ $cert->control_number }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-ghost btn-sm" style="padding: 0.3rem 0.5rem; color: #ef4444;" title="Delete Record">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- Edit Modal for this Certificate -->
                            <div class="modal-backdrop" id="modal-edit-cert-{{ $cert->id }}">
                                <div class="modal-dialog modal-lg" style="max-width: 900px; width: 95%;">
                                    <div class="modal-header">
                                        <div class="modal-title-group">
                                            <h4 class="modal-title">Edit Veterinary Health Certificate</h4>
                                            <span style="font-size: 0.75rem; color: #ef4444; font-weight: 800;">Control Number: {{ $cert->control_number }}</span>
                                        </div>
                                        <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
                                    </div>
                                    <form action="{{ route('vet.certificates.update', $cert) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body" style="padding: 1.5rem; max-height: 80vh; overflow-y: auto;">
                                            <!-- Date & Control Info -->
                                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                                                <div class="form-group">
                                                    <label class="form-label">Examination / Certificate Date *</label>
                                                    <input type="date" name="certificate_date" class="form-control" value="{{ $cert->certificate_date->format('Y-m-d') }}" required>
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label">Control Number</label>
                                                    <input type="text" class="form-control" value="{{ $cert->control_number }}" readonly style="font-weight: 800; color: #ef4444; background: rgba(0,0,0,0.3);">
                                                </div>
                                            </div>

                                            <!-- Section 1: Owner Information -->
                                            <div style="background: rgba(11, 25, 44, 0.6); border: 1px solid var(--navy-border); border-radius: 8px; padding: 1rem; margin-bottom: 1.25rem;">
                                                <h5 style="font-size: 0.85rem; font-weight: 700; color: var(--gold-light); text-transform: uppercase; margin-bottom: 0.75rem;">
                                                    👤 Owner & Travel Destination
                                                </h5>
                                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                                    <div class="form-group">
                                                        <label class="form-label">Owner Name (Owned by) *</label>
                                                        <input type="text" name="owner_name" class="form-control" value="{{ $cert->owner_name }}" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Contact Number *</label>
                                                        <input type="text" name="contact_number" class="form-control" value="{{ $cert->contact_number }}">
                                                    </div>
                                                </div>
                                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                                                    <div class="form-group">
                                                        <label class="form-label">Residing Address (Residing At) *</label>
                                                        <input type="text" name="residing_at" class="form-control" value="{{ $cert->residing_at }}" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Travel Destination *</label>
                                                        <input type="text" name="destination" class="form-control" value="{{ $cert->destination }}" placeholder="e.g. Brgy. Ermita Maripipi, Naval, Biliran" required>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section 2: Pet Description -->
                                            <div style="background: rgba(11, 25, 44, 0.6); border: 1px solid var(--navy-border); border-radius: 8px; padding: 1rem; margin-bottom: 1.25rem;">
                                                <h5 style="font-size: 0.85rem; font-weight: 700; color: var(--gold-light); text-transform: uppercase; margin-bottom: 0.75rem;">
                                                    🐾 Pet Description (Patient Details)
                                                </h5>
                                                <div style="display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 1rem;">
                                                    <div class="form-group">
                                                        <label class="form-label">Name of Pet *</label>
                                                        <input type="text" name="pet_name" class="form-control" value="{{ $cert->pet_name }}" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Species *</label>
                                                        <select name="species" class="form-control" required>
                                                            <option value="Canine" {{ $cert->species == 'Canine' ? 'selected' : '' }}>Canine (Dog)</option>
                                                            <option value="Feline" {{ $cert->species == 'Feline' ? 'selected' : '' }}>Feline (Cat)</option>
                                                            <option value="Avian" {{ $cert->species == 'Avian' ? 'selected' : '' }}>Avian (Bird)</option>
                                                            <option value="Other" {{ !in_array($cert->species, ['Canine','Feline','Avian']) ? 'selected' : '' }}>Other</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Breed</label>
                                                        <input type="text" name="breed" class="form-control" value="{{ $cert->breed }}" placeholder="e.g. Pug">
                                                    </div>
                                                </div>
                                                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                                                    <div class="form-group">
                                                        <label class="form-label">Color</label>
                                                        <input type="text" name="color" class="form-control" value="{{ $cert->color }}" placeholder="e.g. Fawn">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Sex</label>
                                                        <select name="sex" class="form-control">
                                                            <option value="Male" {{ $cert->sex == 'Male' ? 'selected' : '' }}>Male</option>
                                                            <option value="Female" {{ $cert->sex == 'Female' ? 'selected' : '' }}>Female</option>
                                                            <option value="Neutered Male" {{ $cert->sex == 'Neutered Male' ? 'selected' : '' }}>Neutered Male</option>
                                                            <option value="Spayed Female" {{ $cert->sex == 'Spayed Female' ? 'selected' : '' }}>Spayed Female</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Date of Birth</label>
                                                        <input type="date" name="birth_date" class="form-control" value="{{ $cert->birth_date ? $cert->birth_date->format('Y-m-d') : '' }}">
                                                    </div>
                                                </div>
                                                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                                                    <div class="form-group">
                                                        <label class="form-label">Age Display</label>
                                                        <input type="text" name="age" class="form-control" value="{{ $cert->age }}" placeholder="e.g. 19 weeks / 4 months">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Weight</label>
                                                        <input type="text" name="weight" class="form-control" value="{{ $cert->weight }}" placeholder="e.g. 3.1 kg">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Microchip</label>
                                                        <input type="text" name="microchip" class="form-control" value="{{ $cert->microchip }}" placeholder="None or chip number">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section 3: Rabies Vaccination Info -->
                                            <div style="background: rgba(11, 25, 44, 0.6); border: 1px solid var(--navy-border); border-radius: 8px; padding: 1rem; margin-bottom: 1.25rem;">
                                                <h5 style="font-size: 0.85rem; font-weight: 700; color: var(--gold-light); text-transform: uppercase; margin-bottom: 0.75rem;">
                                                    💉 Rabies Vaccination Details
                                                </h5>
                                                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                                                    <div class="form-group">
                                                        <label class="form-label">Rabies Vaccination Date</label>
                                                        <input type="date" name="rabies_vaccination_date" class="form-control" value="{{ $cert->rabies_vaccination_date ? $cert->rabies_vaccination_date->format('Y-m-d') : '' }}">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Vaccine Brand / Name</label>
                                                        <input type="text" name="rabies_vaccine_name" class="form-control" value="{{ $cert->rabies_vaccine_name ?: 'Rabisin' }}">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Serial / Lot Number</label>
                                                        <input type="text" name="rabies_lot_number" class="form-control" value="{{ $cert->rabies_lot_number }}" placeholder="e.g. G64884">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section 4: Veterinarian Credentials -->
                                            <div style="background: rgba(11, 25, 44, 0.6); border: 1px solid var(--navy-border); border-radius: 8px; padding: 1rem;">
                                                <h5 style="font-size: 0.85rem; font-weight: 700; color: var(--gold-light); text-transform: uppercase; margin-bottom: 0.75rem;">
                                                    🩺 Attending Veterinarian Sign-off
                                                </h5>
                                                <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 1rem;">
                                                    <div class="form-group">
                                                        <label class="form-label">Veterinarian Name *</label>
                                                        <input type="text" name="veterinarian_name" class="form-control" value="{{ $cert->veterinarian_name }}" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">TIN #</label>
                                                        <input type="text" name="tin_no" class="form-control" value="{{ $cert->tin_no }}">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">PTR #</label>
                                                        <input type="text" name="ptr_no" class="form-control" value="{{ $cert->ptr_no }}">
                                                    </div>
                                                </div>
                                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                                                    <div class="form-group">
                                                        <label class="form-label">PRC License #</label>
                                                        <input type="text" name="prc_no" class="form-control" value="{{ $cert->prc_no }}">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">License Expiry Date</label>
                                                        <input type="date" name="license_expiry_date" class="form-control" value="{{ $cert->license_expiry_date ? $cert->license_expiry_date->format('Y-m-d') : '2026-12-24' }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer" style="padding: 1.25rem 1.75rem; display: flex; justify-content: space-between;">
                                            <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                                            <div style="display: flex; gap: 0.5rem;">
                                                <button type="submit" name="action_type" value="save" class="btn btn-navy">Save Updates</button>
                                                <button type="submit" name="action_type" value="save_and_print" class="btn btn-gold">🖨️ Save & Print</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 2.5rem;">
                                    No Veterinary Health Certificates found. Click "+ Generate Health Certificate" to create one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($certificates->hasPages())
                <div style="padding: 1rem;">
                    {{ $certificates->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal: Generate New Health Certificate -->
    <div class="modal-backdrop" id="modal-generate-certificate">
        <div class="modal-dialog modal-lg" style="max-width: 900px; width: 95%;">
            <div class="modal-header">
                <div class="modal-title-group">
                    <h4 class="modal-title">Generate Veterinary Health Certificate</h4>
                    <span style="font-size: 0.75rem; color: #ef4444; font-weight: 800;">
                        Auto-Generated Control Number: {{ $nextControlNumber }} (Sequence: YY-0001)
                    </span>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form action="{{ route('vet.certificates.store') }}" method="POST" id="generateCertForm">
                @csrf
                <div class="modal-body" style="padding: 1.5rem; max-height: 80vh; overflow-y: auto;">
                    <!-- Auto-Fill from Registered Client & Pet -->
                    <div style="background: rgba(212, 175, 55, 0.08); border: 1px solid var(--gold-border); border-radius: 8px; padding: 1rem; margin-bottom: 1.25rem;">
                        <label class="form-label" style="font-weight: 700; color: var(--gold-light); margin-bottom: 0.4rem;">
                            ⚡ Quick-Fill from Existing Client & Pet Record (Optional)
                        </label>
                        <select id="quickClientSelect" class="form-control" onchange="onQuickClientSelected(this)">
                            <option value="">-- Choose Registered Patient to Auto-Fill Data --</option>
                            @foreach($owners as $owner)
                                @foreach($owner->pets as $p)
                                    <option value="{{ $p->id }}"
                                        data-owner-id="{{ $owner->id }}"
                                        data-owner-name="{{ $owner->full_name }}"
                                        data-owner-address="{{ $owner->address }}"
                                        data-owner-phone="{{ $owner->contact_number }}"
                                        data-pet-id="{{ $p->id }}"
                                        data-pet-name="{{ $p->name }}"
                                        data-pet-species="{{ $p->species }}"
                                        data-pet-breed="{{ $p->breed }}"
                                        data-pet-color="{{ $p->color }}"
                                        data-pet-sex="{{ $p->sex }}"
                                        data-pet-dob="{{ $p->birth_date ? $p->birth_date->format('Y-m-d') : '' }}"
                                        data-pet-age="{{ $p->age }}"
                                        data-pet-weight="{{ $p->medicalRecords()->latest()->first()->body_weight ?? '' }}">
                                        {{ $owner->full_name }} • [{{ $p->name }} ({{ $p->species }} - {{ $p->breed }})]
                                    </option>
                                @endforeach
                            @endforeach
                        </select>
                        <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 4px;">
                            You can also manually fill or customize all information below.
                        </div>
                    </div>

                    <!-- Date & Control Info -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                        <div class="form-group">
                            <label class="form-label">Certificate Examination Date *</label>
                            <input type="date" name="certificate_date" id="input_cert_date" class="form-control" value="{{ $today }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Control Number Sequence</label>
                            <input type="text" class="form-control" value="{{ $nextControlNumber }}" readonly style="font-weight: 800; color: #ef4444; background: rgba(0,0,0,0.3);" title="Auto-assigned upon save">
                        </div>
                    </div>

                    <!-- Section 1: Owner Information -->
                    <div style="background: rgba(11, 25, 44, 0.6); border: 1px solid var(--navy-border); border-radius: 8px; padding: 1rem; margin-bottom: 1.25rem;">
                        <h5 style="font-size: 0.85rem; font-weight: 700; color: var(--gold-light); text-transform: uppercase; margin-bottom: 0.75rem;">
                            👤 Owner & Travel Destination
                        </h5>
                        <input type="hidden" name="owner_id" id="form_owner_id" value="">
                        <input type="hidden" name="pet_id" id="form_pet_id" value="">

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label class="form-label">Owner Name (Owned by) *</label>
                                <input type="text" name="owner_name" id="input_owner_name" class="form-control" placeholder="e.g. Eljun Menancillo" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Contact Number</label>
                                <input type="text" name="contact_number" id="input_owner_phone" class="form-control" placeholder="e.g. 09677 649 6264">
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                            <div class="form-group">
                                <label class="form-label">Residing Address (Residing At) *</label>
                                <input type="text" name="residing_at" id="input_owner_address" class="form-control" placeholder="e.g. Escario St. Brgy. Kamputhaw, Cebu City, Cebu" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Travel Destination *</label>
                                <input type="text" name="destination" id="input_destination" class="form-control" placeholder="e.g. Brgy. Ermita Maripipi, Naval, Biliran" required>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Pet Description -->
                    <div style="background: rgba(11, 25, 44, 0.6); border: 1px solid var(--navy-border); border-radius: 8px; padding: 1rem; margin-bottom: 1.25rem;">
                        <h5 style="font-size: 0.85rem; font-weight: 700; color: var(--gold-light); text-transform: uppercase; margin-bottom: 0.75rem;">
                            🐾 Pet Description (Patient Details)
                        </h5>
                        <div style="display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label class="form-label">Name of Pet *</label>
                                <input type="text" name="pet_name" id="input_pet_name" class="form-control" placeholder="e.g. Puppy 2" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Species *</label>
                                <select name="species" id="input_pet_species" class="form-control" required>
                                    <option value="Canine" selected>Canine (Dog)</option>
                                    <option value="Feline">Feline (Cat)</option>
                                    <option value="Avian">Avian (Bird)</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Breed</label>
                                <input type="text" name="breed" id="input_pet_breed" class="form-control" placeholder="e.g. Pug">
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                            <div class="form-group">
                                <label class="form-label">Color</label>
                                <input type="text" name="color" id="input_pet_color" class="form-control" placeholder="e.g. Fawn">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Sex</label>
                                <select name="sex" id="input_pet_sex" class="form-control">
                                    <option value="Male" selected>Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Neutered Male">Neutered Male</option>
                                    <option value="Spayed Female">Spayed Female</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="birth_date" id="input_pet_dob" class="form-control">
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                            <div class="form-group">
                                <label class="form-label">Age Display</label>
                                <input type="text" name="age" id="input_pet_age" class="form-control" placeholder="e.g. 19 weeks / 4 months">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Weight</label>
                                <input type="text" name="weight" id="input_pet_weight" class="form-control" placeholder="e.g. 3.1 kg">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Microchip</label>
                                <input type="text" name="microchip" id="input_pet_microchip" class="form-control" value="None" placeholder="None or 15-digit chip">
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Rabies Vaccination Info -->
                    <div style="background: rgba(11, 25, 44, 0.6); border: 1px solid var(--navy-border); border-radius: 8px; padding: 1rem; margin-bottom: 1.25rem;">
                        <h5 style="font-size: 0.85rem; font-weight: 700; color: var(--gold-light); text-transform: uppercase; margin-bottom: 0.75rem;">
                            💉 Rabies Vaccination Details
                        </h5>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label class="form-label">Rabies Vaccination Date</label>
                                <input type="date" name="rabies_vaccination_date" class="form-control" value="{{ $today }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Vaccine Brand / Name</label>
                                <input type="text" name="rabies_vaccine_name" class="form-control" value="Rabisin" placeholder="e.g. Rabisin">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Serial / Lot Number</label>
                                <input type="text" name="rabies_lot_number" class="form-control" placeholder="e.g. G64884">
                            </div>
                        </div>
                    </div>

                    <!-- Section 4: Attending Veterinarian Credentials -->
                    <div style="background: rgba(11, 25, 44, 0.6); border: 1px solid var(--navy-border); border-radius: 8px; padding: 1rem;">
                        <h5 style="font-size: 0.85rem; font-weight: 700; color: var(--gold-light); text-transform: uppercase; margin-bottom: 0.75rem;">
                            🩺 Attending Veterinarian Credentials
                        </h5>
                        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label class="form-label">Veterinarian Name *</label>
                                <input type="text" name="veterinarian_name" class="form-control" value="CARLO EUGENIO N. TUTOR, DVM" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">TIN #</label>
                                <input type="text" name="tin_no" class="form-control" value="331-645-364">
                            </div>
                            <div class="form-group">
                                <label class="form-label">PTR #</label>
                                <input type="text" name="ptr_no" class="form-control" value="1601213">
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                            <div class="form-group">
                                <label class="form-label">PRC License #</label>
                                <input type="text" name="prc_no" class="form-control" value="0009324">
                            </div>
                            <div class="form-group">
                                <label class="form-label">License Expiry Date</label>
                                <input type="date" name="license_expiry_date" class="form-control" value="2026-12-24">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 1.25rem 1.75rem; display: flex; justify-content: space-between;">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <div style="display: flex; gap: 0.5rem;">
                        <button type="submit" name="action_type" value="save" class="btn btn-navy">Save Certificate</button>
                        <button type="submit" name="action_type" value="save_and_print" class="btn btn-gold">🖨️ Save & Print Certificate</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function onQuickClientSelected(select) {
        const option = select.options[select.selectedIndex];
        if (!option.value) return;

        document.getElementById('form_owner_id').value = option.dataset.ownerId || '';
        document.getElementById('form_pet_id').value = option.dataset.petId || '';

        document.getElementById('input_owner_name').value = option.dataset.ownerName || '';
        document.getElementById('input_owner_address').value = option.dataset.ownerAddress || '';
        document.getElementById('input_owner_phone').value = option.dataset.ownerPhone || '';

        document.getElementById('input_pet_name').value = option.dataset.petName || '';
        
        const species = option.dataset.petSpecies || 'Canine';
        const speciesSelect = document.getElementById('input_pet_species');
        if (speciesSelect) {
            let found = false;
            for (let i = 0; i < speciesSelect.options.length; i++) {
                if (speciesSelect.options[i].value.toLowerCase() === species.toLowerCase()) {
                    speciesSelect.selectedIndex = i;
                    found = true;
                    break;
                }
            }
            if (!found) speciesSelect.value = 'Other';
        }

        document.getElementById('input_pet_breed').value = option.dataset.petBreed || '';
        document.getElementById('input_pet_color').value = option.dataset.petColor || '';
        document.getElementById('input_pet_sex').value = option.dataset.petSex || 'Male';
        document.getElementById('input_pet_dob').value = option.dataset.petDob || '';
        document.getElementById('input_pet_age').value = option.dataset.petAge || '';
        if (option.dataset.petWeight) {
            document.getElementById('input_pet_weight').value = option.dataset.petWeight + ' kg';
        }
    }
</script>
@endpush
