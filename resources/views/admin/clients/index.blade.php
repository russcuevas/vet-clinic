@extends('layouts.app')

@php
    $title = 'Clients & Pets';
    $headerTitle = 'Client & Pet Information Database';
    $breadcrumb = 'Clients & Pet Database';
@endphp

@section('content')
    <!-- Action Bar & Filter Toolbar -->
    <div class="table-toolbar">
        <div class="search-input-wrapper">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            <input type="text" class="form-control" placeholder="Search by Key Code, Name, Phone..." data-table-search="clients-table">
        </div>

        <button type="button" class="btn btn-gold" data-modal-target="modal-new-client">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
            <span>New Client Registration</span>
        </button>
    </div>

    <!-- Clients Table Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-title-group">
                <h3 class="card-title">Owner Information Database</h3>
                <span class="card-subtitle">List of registered pet owners and their associated pets</span>
            </div>
        </div>

        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table" id="clients-table">
                    <thead>
                        <tr>
                            <th>Client Key Code</th>
                            <th>Owner Name</th>
                            <th>Contact & Address</th>
                            <th>Registered Pets</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($owners as $owner)
                            <tr>
                                <td>
                                    <span class="badge badge-gold" style="font-size: 0.82rem;">{{ $owner->client_code }}</span>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--white); font-size: 0.95rem;">{{ $owner->full_name }}</div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $owner->email ?? 'No email provided' }}</div>
                                </td>
                                <td>
                                    <div style="color: var(--text-secondary);"><strong style="color: var(--white);">{{ $owner->contact_number }}</strong></div>
                                    <div style="font-size: 0.78rem; color: var(--text-muted); max-width: 250px;">{{ $owner->address }}</div>
                                </td>
                                <td>
                                    <div style="display: flex; flex-direction: column; gap: 0.4rem;">
                                        @foreach($owner->pets as $pet)
                                            @php
                                                $latestVisit = $pet->medicalRecords->first();
                                                $visitCount = $pet->medicalRecords->count();
                                            @endphp
                                            <div style="background: var(--navy-dark); padding: 8px 12px; border-radius: 6px; border: 1px solid var(--navy-border); max-width: 380px;">
                                                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 0.5rem;">
                                                    <div>
                                                        <span style="color: var(--gold-light); font-weight: 800; font-size: 0.92rem;">{{ $pet->name }}</span>
                                                        <span style="font-size: 0.75rem; color: var(--text-muted);">({{ $pet->species }} - {{ $pet->breed }})</span>
                                                        <div style="font-size: 0.72rem; color: var(--text-secondary); margin-top: 2px;">
                                                            <span>Sex: <strong style="color: var(--white);">{{ $pet->sex }}</strong></span>
                                                            @if($pet->color)
                                                                • <span>Color: <strong style="color: var(--white);">{{ $pet->color }}</strong></span>
                                                            @endif
                                                        </div>
                                                        <div style="font-size: 0.68rem; color: var(--text-muted); margin-top: 2px;">
                                                            Key: <code style="color: var(--gold-primary);">{{ $pet->pet_code }}</code> 
                                                            @if($pet->birth_date)
                                                                | 🎂 {{ $pet->birth_date->format('M d, Y') }}
                                                            @endif
                                                            | {{ $pet->age }}
                                                        </div>
                                                    </div>

                                                    <div style="display: flex; gap: 0.2rem; align-items: center;">
                                                        <button type="button" class="btn btn-ghost btn-sm" style="padding: 2px 5px; font-size: 0.8rem;" 
                                                            data-modal-target="modal-edit-pet"
                                                            data-action-url="{{ route('admin.pets.update', $pet->id) }}"
                                                            data-field-name="{{ $pet->name }}"
                                                            data-field-species="{{ $pet->species }}"
                                                            data-field-breed="{{ $pet->breed }}"
                                                            data-field-birth_date="{{ $pet->birth_date ? $pet->birth_date->format('Y-m-d') : '' }}"
                                                            data-field-age="{{ $pet->age }}"
                                                            data-field-sex="{{ $pet->sex }}"
                                                            data-field-color="{{ $pet->color ?? '' }}"
                                                            title="Edit Pet">
                                                            ✏️
                                                        </button>
                                                        <button type="button" class="btn btn-ghost btn-sm" style="padding: 2px 5px; font-size: 0.8rem; color: var(--danger);" 
                                                            data-modal-target="modal-delete-pet"
                                                            data-action-url="{{ route('admin.pets.destroy', $pet->id) }}"
                                                            data-field-target_name="{{ $pet->name }} ({{ $pet->pet_code }})"
                                                            title="Delete Pet">
                                                            🗑️
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- Visit Information & History Button -->
                                                <div style="margin-top: 6px; padding-top: 6px; border-top: 1px dashed rgba(255, 255, 255, 0.08); display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; flex-wrap: wrap;">
                                                    <div>
                                                        @if($latestVisit)
                                                            <span class="badge badge-navy" style="font-size: 0.7rem;">
                                                                🗓️ Last Visit: <strong>{{ $latestVisit->visit_date ? $latestVisit->visit_date->format('m/d/Y') : $latestVisit->created_at->format('m/d/Y') }}</strong>
                                                            </span>
                                                        @else
                                                            <span style="font-size: 0.7rem; color: var(--text-muted);">No visits yet</span>
                                                        @endif
                                                    </div>

                                                    <button type="button" class="btn btn-outline-gold btn-sm" style="padding: 2px 8px; font-size: 0.72rem;"
                                                        onclick="openAdminPetHistoryModal({{ $pet->id }})">
                                                        📋 History ({{ $visitCount }})
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach

                                        <!-- Add pet button -->
                                        <button type="button" class="btn btn-outline-gold btn-sm" style="padding: 2px 8px; font-size: 0.72rem; width: fit-content;"
                                            data-modal-target="modal-add-pet"
                                            data-field-owner_id="{{ $owner->id }}"
                                            data-field-owner_name="{{ $owner->full_name }}">
                                            + Add Pet
                                        </button>
                                    </div>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 0.4rem;">
                                        <!-- Edit Client Trigger -->
                                        <button type="button" class="btn btn-navy btn-sm"
                                            data-modal-target="modal-edit-client"
                                            data-action-url="{{ route('admin.clients.update', $owner->id) }}"
                                            data-field-full_name="{{ $owner->full_name }}"
                                            data-field-contact_number="{{ $owner->contact_number }}"
                                            data-field-address="{{ $owner->address }}"
                                            data-field-email="{{ $owner->email ?? '' }}">
                                            Edit
                                        </button>

                                        <!-- Delete Client Trigger -->
                                        <button type="button" class="btn btn-danger btn-sm"
                                            data-modal-target="modal-delete-client"
                                            data-action-url="{{ route('admin.clients.destroy', $owner->id) }}"
                                            data-field-target_name="{{ $owner->full_name }} ({{ $owner->client_code }})">
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

    <!-- ==================== MODAL 1: NEW CLIENT REGISTRATION ==================== -->
    <div class="modal-backdrop" id="modal-new-client">
        <div class="modal-dialog modal-xl">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                    </div>
                    <div>
                        <h4 class="modal-title">New Client Registration</h4>
                        <span style="font-size: 0.75rem; color: var(--gold-light);">Auto-Generated Key Code: <strong>{{ $generatedCode }}</strong></span>
                    </div>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form action="{{ route('admin.clients.store') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding: 1.25rem 1.5rem;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 1.25rem; align-items: start;">
                        <!-- LEFT COLUMN: Client & Pet Details -->
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <!-- Owner Info Card -->
                            <div style="background: rgba(11, 25, 44, 0.4); border: 1px solid var(--navy-border); border-radius: var(--radius-sm); padding: 1rem;">
                                <h5 style="color: var(--gold-primary); font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem;">
                                    <span>👤</span> Owner / Client Information
                                </h5>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label class="form-label">Full Name <span class="req">*</span></label>
                                        <input type="text" name="full_name" class="form-control" placeholder="e.g. Juan Dela Cruz" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Contact Number <span class="req">*</span></label>
                                        <input type="text" name="contact_number" class="form-control" placeholder="e.g. 0917-123-4567" required>
                                    </div>
                                </div>
                                <div class="form-grid" style="margin-top: 0.5rem;">
                                    <div class="form-group">
                                        <label class="form-label">Complete Address <span class="req">*</span></label>
                                        <input type="text" name="address" class="form-control" placeholder="Street, Barangay, City" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Email Address (Optional)</label>
                                        <input type="email" name="email" class="form-control" placeholder="juan@example.com">
                                    </div>
                                </div>
                            </div>

                            <!-- Pet Info Card -->
                            <div style="background: rgba(11, 25, 44, 0.4); border: 1px solid var(--navy-border); border-radius: var(--radius-sm); padding: 1rem;">
                                <h5 style="color: var(--gold-primary); font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem;">
                                    <span>🐾</span> Initial Pet Information
                                </h5>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label class="form-label">Pet Name <span class="req">*</span></label>
                                        <input type="text" name="pet_name" class="form-control" placeholder="e.g. Ponton / Milo">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Species <span class="req">*</span></label>
                                        <select name="species" class="form-select">
                                            <option value="Canine (Dog)">Canine (Dog)</option>
                                            <option value="Feline (Cat)">Feline (Cat)</option>
                                            <option value="Avian (Bird)">Avian (Bird)</option>
                                            <option value="Rabbit">Rabbit</option>
                                            <option value="Exotic">Exotic</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-grid" style="margin-top: 0.5rem;">
                                    <div class="form-group">
                                        <label class="form-label">Breed</label>
                                        <input type="text" name="breed" class="form-control" placeholder="e.g. Persian / Shih Tzu">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Birthdate</label>
                                        <input type="date" name="birth_date" class="form-control">
                                    </div>
                                </div>
                                <div class="form-grid" style="margin-top: 0.5rem;">
                                    <div class="form-group">
                                        <label class="form-label">Sex</label>
                                        <select name="sex" class="form-select">
                                            <option value="Male">Male (Intact)</option>
                                            <option value="Female">Female (Intact)</option>
                                            <option value="Neutered Male">Neutered Male</option>
                                            <option value="Spayed Female">Spayed Female</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Color / Markings</label>
                                        <input type="text" name="color" class="form-control" placeholder="e.g. Gray / Tri-color">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT COLUMN: Past / Historical Consultation (Auto-Paid) -->
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <div style="background: linear-gradient(135deg, rgba(212, 175, 55, 0.08) 0%, rgba(11, 25, 44, 0.6) 100%); border: 1.5px solid var(--gold-border); border-radius: var(--radius-sm); padding: 1rem;">
                                <div style="display: flex; align-items: flex-start; gap: 0.6rem; margin-bottom: 0.75rem;">
                                    <input type="checkbox" name="include_medical_record" id="chk-include-med" value="1" style="margin-top: 4px; accent-color: var(--gold-primary); width: 18px; height: 18px; cursor: pointer;" onchange="document.getElementById('historical-med-section').style.display = this.checked ? 'block' : 'none';">
                                    <label for="chk-include-med" style="cursor: pointer; margin: 0;">
                                        <div style="font-weight: 700; color: var(--gold-light); font-size: 0.9rem;">
                                            📑 Encode Past / Old Consultation Record
                                        </div>
                                        <div style="font-size: 0.72rem; color: var(--text-muted); line-height: 1.3; margin-top: 2px;">
                                            Check this if you are encoding an existing paper record/last month's visit. This will create the medical checkup and <strong>AUTOMATICALLY MARK IT AS PAID</strong> without needing to go to Cashier/Billing!
                                        </div>
                                    </label>
                                </div>

                                <!-- Collapsible Historical Record Inputs -->
                                <div id="historical-med-section" style="display: none; border-top: 1px dashed var(--gold-border); padding-top: 0.85rem; margin-top: 0.85rem;">
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label class="form-label">📅 Date of Visit <span class="req">*</span></label>
                                            <input type="date" name="med_visit_date" class="form-control" value="{{ date('Y-m-d') }}">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Service Type</label>
                                            <select name="med_service_type" class="form-select">
                                                <option value="consultation">Consultation</option>
                                                <option value="follow_up">Follow Up</option>
                                                <option value="wellness">Wellness</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group" style="margin-top: 0.5rem;">
                                        <label class="form-label">Attending Veterinarian</label>
                                        <select name="med_veterinarian_id" class="form-select">
                                            @foreach($veterinarians as $vet)
                                                <option value="{{ $vet->id }}">{{ $vet->name }} ({{ $vet->license_no ?? 'PRC-VET' }})</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-top: 0.5rem;">
                                        <div class="form-group">
                                            <label class="form-label" style="font-size: 0.75rem;">🌡️ Temp (°C)</label>
                                            <input type="text" name="med_temperature" class="form-control" placeholder="e.g. 38.2">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" style="font-size: 0.75rem;">⚖️ Weight (BW)</label>
                                            <input type="text" name="med_body_weight" class="form-control" placeholder="e.g. 3.3 kg">
                                        </div>
                                    </div>

                                    <div class="form-group" style="margin-top: 0.5rem;">
                                        <label class="form-label">📝 Purpose / Examination Notes / Complaint</label>
                                        <textarea name="med_history_taking" class="form-control" rows="2" placeholder="e.g. 2 days inappetence, distended bladder..."></textarea>
                                    </div>

                                    <div class="form-group" style="margin-top: 0.5rem;">
                                        <label class="form-label">💊 Medication / Treatment</label>
                                        <textarea name="med_medication_treatment" class="form-control" rows="2" placeholder="e.g. Co-amox, Kidney support, Special Cat..."></textarea>
                                    </div>

                                    <div class="form-group" style="margin-top: 0.5rem;">
                                        <label class="form-label">🔬 Laboratory</label>
                                        <textarea name="med_laboratory_notes" class="form-control" rows="2" placeholder="e.g. CBC, catheterization 125ml..."></textarea>
                                    </div>

                                    <div class="form-grid" style="margin-top: 0.5rem;">
                                        <div class="form-group">
                                            <label class="form-label">Diagnosis / Assessment</label>
                                            <input type="text" name="med_diagnosis" class="form-control" placeholder="e.g. FLUTD / Blockage">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Service Fee (₱) <span class="badge badge-success" style="font-size: 0.68rem;">Auto-Paid</span></label>
                                            <input type="number" step="0.01" name="med_service_fee" class="form-control" value="450.00" style="font-weight: 700; color: var(--gold-primary);">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold">Save Client, Pet & Records</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL 2: ADD PET TO EXISTING CLIENT ==================== -->
    <div class="modal-backdrop" id="modal-add-pet">
        <div class="modal-dialog">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    </div>
                    <div>
                        <h4 class="modal-title">Add Pet to Client</h4>
                        <span style="font-size: 0.75rem; color: var(--gold-light);">Owner: <span id="owner_name_display" data-bind="owner_name"></span></span>
                    </div>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form action="{{ route('admin.pets.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="owner_id" id="owner_id" data-bind="owner_id">

                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Pet Name <span class="req">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Bantay" required>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Species <span class="req">*</span></label>
                            <select name="species" class="form-select" required>
                                <option value="Dog">Dog</option>
                                <option value="Cat">Cat</option>
                                <option value="Bird">Bird</option>
                                <option value="Rabbit">Rabbit</option>
                                <option value="Exotic">Exotic</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Breed <span class="req">*</span></label>
                            <input type="text" name="breed" class="form-control" placeholder="e.g. Shih Tzu" required>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Birthdate <span style="color: var(--text-muted); font-size: 0.72rem;">(Optional)</span></label>
                            <input type="date" name="birth_date" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Age</label>
                            <input type="text" name="age" class="form-control" placeholder="e.g. 1 year old">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sex <span class="req">*</span></label>
                            <select name="sex" class="form-select" required>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Neutered Male">Neutered Male</option>
                                <option value="Spayed Female">Spayed Female</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Color / Markings</label>
                            <input type="text" name="color" class="form-control" placeholder="e.g. Brown & White, Gray">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Pet Photo (Stored in public/)</label>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold">Save Pet</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL 3: EDIT CLIENT ==================== -->
    <div class="modal-backdrop" id="modal-edit-client">
        <div class="modal-dialog">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">✏️</div>
                    <h4 class="modal-title">Edit Client Information</h4>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Full Name <span class="req">*</span></label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Number <span class="req">*</span></label>
                        <input type="text" name="contact_number" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Address <span class="req">*</span></label>
                        <textarea name="address" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold">Update Client</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL 4: DELETE CLIENT CONFIRMATION ==================== -->
    <div class="modal-backdrop" id="modal-delete-client">
        <div class="modal-dialog modal-danger modal-sm">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">🗑️</div>
                    <h4 class="modal-title">Delete Client Record?</h4>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p style="color: var(--text-secondary); font-size: 0.9rem;">
                        Are you sure you want to permanently remove <strong style="color: var(--white);" data-bind="target_name">this client</strong> and all associated pet medical histories?
                    </p>
                    <p style="color: var(--danger); font-size: 0.78rem; margin-top: 0.5rem;">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm Delete</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL 5: EDIT PET ==================== -->
    <div class="modal-backdrop" id="modal-edit-pet">
        <div class="modal-dialog">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">🐾</div>
                    <h4 class="modal-title">Edit Pet Record</h4>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form method="POST" action="" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Pet Name <span class="req">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Species <span class="req">*</span></label>
                            <select name="species" class="form-select" required>
                                <option value="Dog">Dog</option>
                                <option value="Cat">Cat</option>
                                <option value="Bird">Bird</option>
                                <option value="Rabbit">Rabbit</option>
                                <option value="Exotic">Exotic</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Breed <span class="req">*</span></label>
                            <input type="text" name="breed" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Birthdate <span style="color: var(--text-muted); font-size: 0.72rem;">(Optional)</span></label>
                            <input type="date" name="birth_date" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Age</label>
                            <input type="text" name="age" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sex <span class="req">*</span></label>
                            <select name="sex" class="form-select" required>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Neutered Male">Neutered Male</option>
                                <option value="Spayed Female">Spayed Female</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Color / Markings</label>
                        <input type="text" name="color" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Replace Photo (Optional, saved to public/)</label>
                        <input type="file" name="photo" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold">Update Pet Details</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL 6: DELETE PET CONFIRMATION ==================== -->
    <div class="modal-backdrop" id="modal-delete-pet">
        <div class="modal-dialog modal-sm modal-danger">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">⚠️</div>
                    <h4 class="modal-title">Delete Pet Record</h4>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p style="color: var(--text-secondary); font-size: 0.9rem;">
                        Are you sure you want to delete <strong style="color: var(--danger);" data-bind="target_name">this pet</strong> from the clinic database?
                    </p>
                    <p style="color: var(--text-muted); font-size: 0.78rem; margin-top: 0.65rem;">
                        This action will permanently remove the pet's registration record and attached photos.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm Delete</button>
                </div>
            </form>
        </div>
    </div>
    <!-- ==================== MODAL 7: PET MEDICAL HISTORY TIMELINE ==================== -->
    <div class="modal-backdrop" id="modal-admin-pet-history">
        <div class="modal-dialog modal-2xl">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">📋</div>
                    <div>
                        <h4 class="modal-title" id="admin-history-modal-pet-name">Pet Medical History</h4>
                        <span style="font-size: 0.75rem; color: var(--gold-light);" id="admin-history-modal-owner-name">Patient Visit Timeline & Records</span>
                    </div>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <div class="modal-body" style="padding: 1.25rem 1.5rem;">
                <!-- Pet Profile Top Strip -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.75rem; background: rgba(11, 25, 44, 0.5); border: 1px solid var(--navy-border); border-radius: var(--radius-sm); padding: 1rem; margin-bottom: 1.25rem;" id="admin-history-modal-pet-details">
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
                                <th style="min-width: 100px;">Doctor / Fee</th>
                            </tr>
                        </thead>
                        <tbody id="admin-history-modal-records-body">
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
        const adminPetsData = {
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
                                    fee: "₱{{ number_format($rec->service_fee, 2) }}",
                                    status: "{{ ucfirst($rec->status) }}"
                                },
                            @endforeach
                        ]
                    },
                @endforeach
            @endforeach
        };

        function openAdminPetHistoryModal(petId) {
            const pet = adminPetsData[petId];
            if (!pet) return;

            document.getElementById('admin-history-modal-pet-name').innerHTML = `🐾 ${pet.name} <span style="font-size: 0.8rem; color: var(--gold-light);">(${pet.pet_code})</span>`;
            document.getElementById('admin-history-modal-owner-name').innerText = `Owner: ${pet.owner_name} (${pet.owner_code}) • Contact: ${pet.contact}`;

            document.getElementById('admin-history-modal-pet-details').innerHTML = `
                <div><span style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Species & Breed</span><div style="font-weight: 700; color: var(--white);">${pet.species} - ${pet.breed}</div></div>
                <div><span style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Sex & Color</span><div style="font-weight: 700; color: var(--white);">${pet.sex} • ${pet.color}</div></div>
                <div><span style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Birthdate & Age</span><div style="font-weight: 700; color: var(--white);">${pet.birth_date} (${pet.age})</div></div>
                <div><span style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Total Encoded Visits</span><div style="font-weight: 800; color: var(--gold-primary); font-size: 1.1rem;">${pet.records.length}</div></div>
            `;

            const tbody = document.getElementById('admin-history-modal-records-body');
            if (pet.records.length === 0) {
                tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; padding: 2rem; color: var(--text-muted);">No clinical examination or visit records found for this pet.</td></tr>`;
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
                        <td>
                            <div style="font-weight: 700; color: var(--white); font-size: 0.82rem;">${r.fee}</div>
                            <span class="badge ${r.status === 'Completed' || r.status === 'Billed' ? 'badge-success' : 'badge-warning'}" style="font-size: 0.68rem;">
                                ${r.status}
                            </span>
                        </td>
                    </tr>
                `).join('');
            }

            const modal = document.getElementById('modal-admin-pet-history');
            modal.classList.add('active');
        }
    </script>
@endsection
