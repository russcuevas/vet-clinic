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
                                            <div style="display: flex; align-items: center; justify-content: space-between; background: var(--navy-dark); padding: 4px 8px; border-radius: 6px; border: 1px solid var(--navy-border); max-width: 280px;">
                                                <div>
                                                    <span style="color: var(--gold-light); font-weight: 600;">{{ $pet->name }}</span>
                                                    <span style="font-size: 0.72rem; color: var(--text-muted);">({{ $pet->species }} - {{ $pet->breed }})</span>
                                                    <div style="font-size: 0.68rem; color: var(--text-muted);">Key: <code style="color: var(--gold-primary);">{{ $pet->pet_code }}</code> | {{ $pet->age }}</div>
                                                </div>
                                                <div style="display: flex; gap: 0.2rem; align-items: center;">
                                                    <button type="button" class="btn btn-ghost btn-sm" style="padding: 2px 5px; font-size: 0.8rem;" 
                                                        data-modal-target="modal-edit-pet"
                                                        data-action-url="{{ route('admin.pets.update', $pet->id) }}"
                                                        data-field-name="{{ $pet->name }}"
                                                        data-field-species="{{ $pet->species }}"
                                                        data-field-breed="{{ $pet->breed }}"
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
        <div class="modal-dialog modal-lg">
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
                <div class="modal-body">
                    <h5 style="color: var(--gold-primary); font-size: 0.85rem; text-transform: uppercase; margin-bottom: 0.85rem; letter-spacing: 0.05em;">Owner Information Database</h5>
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

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Address <span class="req">*</span></label>
                            <input type="text" name="address" class="form-control" placeholder="Street, Barangay, City" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email Address (Optional)</label>
                            <input type="email" name="email" class="form-control" placeholder="juan@example.com">
                        </div>
                    </div>

                    <hr style="border: 0; border-top: 1px solid var(--black-border); margin: 1.25rem 0;">

                    <h5 style="color: var(--gold-primary); font-size: 0.85rem; text-transform: uppercase; margin-bottom: 0.85rem; letter-spacing: 0.05em;">Initial Pet Information (Optional)</h5>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Pet Name</label>
                            <input type="text" name="pet_name" class="form-control" placeholder="e.g. Milo">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Species</label>
                            <select name="species" class="form-select">
                                <option value="Dog">Dog</option>
                                <option value="Cat">Cat</option>
                                <option value="Bird">Bird</option>
                                <option value="Rabbit">Rabbit</option>
                                <option value="Exotic">Exotic</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Breed</label>
                            <input type="text" name="breed" class="form-control" placeholder="e.g. Golden Retriever / Persian">
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Age</label>
                            <input type="text" name="age" class="form-control" placeholder="e.g. 2 years old">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sex</label>
                            <select name="sex" class="form-select">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Neutered Male">Neutered Male</option>
                                <option value="Spayed Female">Spayed Female</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Color / Markings</label>
                            <input type="text" name="color" class="form-control" placeholder="e.g. Golden / Tri-color">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold">Save & Generate Key Code</button>
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
                            <label class="form-label">Age <span class="req">*</span></label>
                            <input type="text" name="age" class="form-control" placeholder="e.g. 1 year old" required>
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
                            <label class="form-label">Color</label>
                            <input type="text" name="color" class="form-control" placeholder="e.g. Brown & White">
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
                            <label class="form-label">Age <span class="req">*</span></label>
                            <input type="text" name="age" class="form-control" required>
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
                        <label class="form-label">Color</label>
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
@endsection
