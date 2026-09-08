@extends('layouts.app')

@php
    $title = 'Clients & Pets Directory';
    $headerTitle = 'Client & Patient Directory';
    $breadcrumb = 'Reception / Clients';
@endphp

@section('content')
    <!-- Top Stats -->
    <div class="stat-grid" style="margin-bottom: 1.5rem;">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Registered Clients</span>
                <div class="stat-icon-wrapper" style="background: rgba(59, 130, 246, 0.15); color: #3b82f6;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
            <div class="stat-value">{{ $totalClients }} <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">Owners</span></div>
            <div class="stat-desc">Active pet owners on record</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Registered Pets / Patients</span>
                <div class="stat-icon-wrapper" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </div>
            </div>
            <div class="stat-value" style="color: #34d399;">{{ $totalPets }} <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">Pets</span></div>
            <div class="stat-desc">Dogs, Cats, and other animal patients</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Quick Actions</span>
                <div class="stat-icon-wrapper" style="background: rgba(245, 186, 49, 0.15); color: var(--gold-primary);">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
            </div>
            <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
                <button type="button" class="btn btn-gold btn-sm" data-modal-target="modal-add-client">
                    + New Client
                </button>
                <a href="{{ route('receptionist.appointments.index') }}" class="btn btn-navy btn-sm">
                    Book Appointment
                </a>
            </div>
        </div>
    </div>

    <!-- Clients Table Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-title-group">
                <h3 class="card-title">Client Records & Registered Pets</h3>
                <span class="card-subtitle">Search and view pet owner information and registered animals</span>
            </div>

            <form action="{{ route('receptionist.clients.index') }}" method="GET" style="display: flex; gap: 0.5rem;">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search owner, pet, code..." value="{{ $search ?? '' }}" style="width: 240px;">
                <button type="submit" class="btn btn-navy btn-sm">Search</button>
                @if($search)
                    <a href="{{ route('receptionist.clients.index') }}" class="btn btn-ghost btn-sm">Clear</a>
                @endif
            </form>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Client Code & Name</th>
                            <th>Contact Info</th>
                            <th>Address</th>
                            <th>Registered Pets</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clients as $client)
                            <tr>
                                <td>
                                    <strong style="color: var(--white); font-size: 0.9rem;">{{ $client->full_name }}</strong>
                                    <div style="font-size: 0.72rem; color: var(--gold-light); font-weight: 600;">{{ $client->client_code }}</div>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: var(--white); font-size: 0.8rem;">{{ $client->contact_number }}</div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $client->email ?: 'No email on file' }}</div>
                                </td>
                                <td style="font-size: 0.8rem; color: var(--text-secondary); max-width: 220px;">
                                    {{ $client->address }}
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                                        @forelse($client->pets as $pet)
                                            <span class="badge badge-navy" style="font-size: 0.72rem;">
                                                🐾 {{ $pet->name }} ({{ $pet->species }})
                                            </span>
                                        @empty
                                            <span style="font-size: 0.72rem; color: var(--text-muted);">No pets registered</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.35rem;">
                                        <button type="button" class="btn btn-gold btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.72rem;"
                                            data-modal-target="modal-add-pet-{{ $client->id }}" title="Add Pet for Client">
                                            + Pet
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Modal: Add Pet for Client -->
                            <div class="modal-backdrop" id="modal-add-pet-{{ $client->id }}">
                                <div class="modal-dialog" style="max-width: 500px;">
                                    <div class="modal-header">
                                        <div class="modal-title-group">
                                            <h4 class="modal-title">Register Pet for {{ $client->full_name }}</h4>
                                            <span style="font-size: 0.72rem; color: var(--gold-light);">{{ $client->client_code }}</span>
                                        </div>
                                        <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
                                    </div>
                                    <form action="{{ route('receptionist.clients.pet.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="owner_id" value="{{ $client->id }}">
                                        <div class="modal-body" style="padding: 1.5rem;">
                                            <div class="form-group">
                                                <label class="form-label">Pet Name *</label>
                                                <input type="text" name="name" class="form-control" placeholder="e.g. Browny, Luna" required>
                                            </div>

                                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-top: 0.75rem;">
                                                <div class="form-group">
                                                    <label class="form-label">Species *</label>
                                                    <select name="species" class="form-control" required>
                                                        <option value="Dog">Dog</option>
                                                        <option value="Cat">Cat</option>
                                                        <option value="Bird">Bird</option>
                                                        <option value="Rabbit">Rabbit</option>
                                                        <option value="Other">Other</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label">Breed</label>
                                                    <input type="text" name="breed" class="form-control" placeholder="e.g. Persian, Shih Tzu">
                                                </div>
                                            </div>

                                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-top: 0.75rem;">
                                                <div class="form-group">
                                                    <label class="form-label">Birthdate <span style="color: var(--text-muted); font-size: 0.72rem;">(Optional)</span></label>
                                                    <input type="date" name="birth_date" class="form-control">
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label">Age</label>
                                                    <input type="text" name="age" class="form-control" placeholder="Auto or e.g. 2 yrs, 6 mos">
                                                </div>
                                            </div>

                                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-top: 0.75rem;">
                                                <div class="form-group">
                                                    <label class="form-label">Sex</label>
                                                    <select name="sex" class="form-control">
                                                        <option value="">-- Choose Sex --</option>
                                                        <option value="Male">Male</option>
                                                        <option value="Female">Female</option>
                                                        <option value="Neutered Male">Neutered Male</option>
                                                        <option value="Spayed Female">Spayed Female</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label">Color / Markings</label>
                                                    <input type="text" name="color" class="form-control" placeholder="e.g. White with brown patches">
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
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2.5rem;">
                                    No client records found. Click "+ New Client" to register.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($clients->hasPages())
                <div style="padding: 1rem;">
                    {{ $clients->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal: Register New Client -->
    <div class="modal-backdrop" id="modal-add-client">
        <div class="modal-dialog" style="max-width: 500px;">
            <div class="modal-header">
                <div class="modal-title-group">
                    <h4 class="modal-title">Register New Client / Owner</h4>
                    <span style="font-size: 0.72rem; color: var(--gold-light);">Front Desk Registration</span>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form action="{{ route('receptionist.clients.store') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="full_name" class="form-control" placeholder="e.g. Maria Clara Santos" required>
                    </div>

                    <div class="form-group" style="margin-top: 0.75rem;">
                        <label class="form-label">Contact Number *</label>
                        <input type="text" name="contact_number" class="form-control" placeholder="e.g. 0917-123-4567" required>
                    </div>

                    <div class="form-group" style="margin-top: 0.75rem;">
                        <label class="form-label">Complete Address *</label>
                        <input type="text" name="address" class="form-control" placeholder="e.g. 124 Rizal St, San Modesto" required>
                    </div>

                    <div class="form-group" style="margin-top: 0.75rem;">
                        <label class="form-label">Email Address (Optional)</label>
                        <input type="email" name="email" class="form-control" placeholder="e.g. owner@example.com">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold">Register Client</button>
                </div>
            </form>
        </div>
    </div>
@endsection
