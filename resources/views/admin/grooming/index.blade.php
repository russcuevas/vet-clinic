@extends('layouts.app')

@php
    $title = 'Grooming Services';
    $headerTitle = 'Pet Grooming Center';
    $breadcrumb = 'Grooming Records';
@endphp

@section('content')
    <!-- Action Bar & Filter Toolbar -->
    <div class="table-toolbar">
        <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <div class="search-input-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                <input type="text" class="form-control" placeholder="Search grooming code, pet, owner..." data-table-search="grooming-table">
            </div>

            <!-- Status Filter Pills -->
            <div style="display: flex; gap: 0.35rem;">
                <a href="{{ route('admin.grooming.index') }}" class="btn btn-sm {{ !request('status') ? 'btn-gold' : 'btn-navy' }}">All</a>
                <a href="{{ route('admin.grooming.index', ['status' => 'queued']) }}" class="btn btn-sm {{ request('status') === 'queued' ? 'btn-gold' : 'btn-navy' }}">Queued</a>
                <a href="{{ route('admin.grooming.index', ['status' => 'in_progress']) }}" class="btn btn-sm {{ request('status') === 'in_progress' ? 'btn-gold' : 'btn-navy' }}">In Progress</a>
                <a href="{{ route('admin.grooming.index', ['status' => 'completed']) }}" class="btn btn-sm {{ request('status') === 'completed' ? 'btn-gold' : 'btn-navy' }}">Completed</a>
            </div>
        </div>

        <button type="button" class="btn btn-gold" data-modal-target="modal-new-grooming">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879a3 3 0 11-4.242-4.242L10.758 7.758a3 3 0 014.242 4.242z" /></svg>
            <span>New Grooming Session</span>
        </button>
    </div>

    <!-- Grooming Table Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-title-group">
                <h3 class="card-title">Grooming Database</h3>
                <span class="card-subtitle">Grooming records, styling details, notes, and billing transfers</span>
            </div>
        </div>

        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table" id="grooming-table">
                    <thead>
                        <tr>
                            <th>Grooming Code</th>
                            <th>Pet & Owner Details</th>
                            <th>Vitals (Weight / Temp / Score)</th>
                            <th>Style [text type]</th>
                            <th>Attending Groomer</th>
                            <th>Groomer Observation Notes</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($records as $groom)
                            <tr>
                                <td>
                                    <strong style="color: var(--gold-primary);">{{ $groom->grooming_code }}</strong>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $groom->created_at->format('M d, Y') }}</div>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--white);">{{ $groom->pet->name ?? 'N/A' }}</div>
                                    <div style="font-size: 0.75rem; color: var(--gold-light);">{{ $groom->pet->species ?? '' }} ({{ $groom->pet->breed ?? '' }})</div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">Owner: {{ $groom->owner->full_name ?? 'N/A' }}</div>
                                </td>
                                <td>
                                    <div style="font-size: 0.8rem;">⚖️ {{ $groom->body_weight ?? 'N/A' }}</div>
                                    <div style="font-size: 0.8rem;">🌡️ {{ $groom->temperature ?? 'N/A' }}</div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">Score: {{ $groom->body_score ?? 'N/A' }}</div>
                                </td>
                                <td>
                                    <strong style="color: var(--white);">{{ $groom->style }}</strong>
                                </td>
                                <td>
                                    @if($groom->groomer)
                                        <span class="badge badge-gold" style="font-size: 0.75rem;">✂️ {{ $groom->groomer->full_name }}</span>
                                    @else
                                        <span style="color: var(--text-muted); font-size: 0.75rem;">Unassigned</span>
                                    @endif
                                </td>
                                <td style="max-width: 250px;">
                                    <div style="font-size: 0.8rem; color: var(--text-secondary);">
                                        {{ $groom->groomer_observation_notes ?? 'No observation notes' }}
                                    </div>
                                </td>
                                <td>
                                    <strong>₱{{ number_format($groom->price, 2) }}</strong>
                                </td>
                                <td>
                                    <span class="badge {{ $groom->status === 'completed' || $groom->status === 'billed' ? 'badge-success' : ($groom->status === 'in_progress' ? 'badge-info' : 'badge-warning') }}">
                                        {{ ucfirst(str_replace('_', ' ', $groom->status)) }}
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 0.4rem;">
                                        <!-- Edit Modal Trigger -->
                                        <button type="button" class="btn btn-navy btn-sm"
                                            data-modal-target="modal-edit-grooming"
                                            data-action-url="{{ route('admin.grooming.update', $groom->id) }}"
                                            data-field-groomer_id="{{ $groom->groomer_id }}"
                                            data-field-body_weight="{{ $groom->body_weight }}"
                                            data-field-temperature="{{ $groom->temperature }}"
                                            data-field-body_score="{{ $groom->body_score }}"
                                            data-field-style="{{ $groom->style }}"
                                            data-field-groomer_observation_notes="{{ $groom->groomer_observation_notes }}"
                                            data-field-price="{{ $groom->price }}"
                                            data-field-status="{{ $groom->status }}">
                                            Edit
                                        </button>

                                        <!-- Delete Record Trigger -->
                                        <button type="button" class="btn btn-danger btn-sm"
                                            data-modal-target="modal-delete-grooming"
                                            data-action-url="{{ route('admin.grooming.destroy', $groom->id) }}"
                                            data-field-target_name="{{ $groom->grooming_code }} ({{ $groom->pet->name ?? '' }})">
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

    <!-- ==================== MODAL 1: NEW GROOMING RECORD ==================== -->
    <div class="modal-backdrop" id="modal-new-grooming">
        <div class="modal-dialog modal-lg">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">✂️</div>
                    <div>
                        <h4 class="modal-title">New Grooming Session</h4>
                        <span style="font-size: 0.75rem; color: var(--gold-light);">Code: <strong>{{ $generatedCode }}</strong> (Flowchart: Grooming Database -> Billing)</span>
                    </div>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form action="{{ route('admin.grooming.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <x-client-pet-selector :owners="$owners" />

                    <!-- Flowchart Fields: Weight, Temp, Score -->
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Body Weight</label>
                            <input type="text" name="body_weight" class="form-control" placeholder="e.g. 5.5 kg">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Temperature</label>
                            <input type="text" name="temperature" class="form-control" placeholder="e.g. 38.2 °C">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Body Score</label>
                            <input type="text" name="body_score" class="form-control" placeholder="e.g. 3/5 Ideal">
                        </div>
                    </div>

                    <!-- Attending Groomer (Linked to Payroll Incentives) -->
                    <div class="form-group">
                        <label class="form-label">Attending Groomer</label>
                        <select name="groomer_id" class="form-control">
                            <option value="">-- Unassigned (General Grooming) --</option>
                            @foreach($groomers as $groomer)
                                <option value="{{ $groomer->id }}" {{ str_contains(strtolower($groomer->position), 'groom') ? 'selected' : '' }}>
                                    {{ $groomer->full_name }} ({{ $groomer->position }})
                                </option>
                            @endforeach
                        </select>
                        <span style="font-size: 0.72rem; color: var(--gold-light);">Credited to Groomer's monthly incentive calculation count.</span>
                    </div>

                    <!-- Flowchart Field: Style [text type] -->
                    <div class="form-group">
                        <label class="form-label">Style [text type] <span class="req">*</span></label>
                        <input type="text" name="style" class="form-control" placeholder="e.g. Puppy Cut, Teddy Bear Cut, Full Summer Shave, Bath & Blowdry" required>
                    </div>

                    <!-- Flowchart Field: Groomer observation Notes [text type] -->
                    <div class="form-group">
                        <label class="form-label">Groomer Observation Notes [text type]</label>
                        <textarea name="groomer_observation_notes" class="form-control" rows="3" placeholder="Condition of coat, skin redness, behavior during wash, nail trimming status..."></textarea>
                    </div>

                    <!-- Flowchart: Grooming Fee -->
                    <div class="form-group">
                        <label class="form-label">Grooming Price (₱) <span class="req">*</span></label>
                        <input type="number" step="0.01" name="price" class="form-control" value="650.00" required>
                        <span style="font-size: 0.72rem; color: var(--text-muted);">Will be automatically queued into the Central Billing Data Base.</span>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold">Save & Queue to Billing</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL 2: EDIT GROOMING RECORD ==================== -->
    <div class="modal-backdrop" id="modal-edit-grooming">
        <div class="modal-dialog modal-lg">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">✏️</div>
                    <h4 class="modal-title">Update Grooming Record</h4>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form method="POST" action="">
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

                    <!-- Attending Groomer -->
                    <div class="form-group">
                        <label class="form-label">Attending Groomer</label>
                        <select name="groomer_id" class="form-control">
                            <option value="">-- Unassigned (General Grooming) --</option>
                            @foreach($groomers as $groomer)
                                <option value="{{ $groomer->id }}">
                                    {{ $groomer->full_name }} ({{ $groomer->position }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Style [text type] <span class="req">*</span></label>
                        <input type="text" name="style" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Groomer Observation Notes</label>
                        <textarea name="groomer_observation_notes" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Price (₱) <span class="req">*</span></label>
                            <input type="number" step="0.01" name="price" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="queued">Queued</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="billed">Billed</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold">Update Grooming Details</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL 3: DELETE GROOMING RECORD ==================== -->
    <div class="modal-backdrop" id="modal-delete-grooming">
        <div class="modal-dialog modal-danger modal-sm">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">🗑️</div>
                    <h4 class="modal-title">Delete Grooming Record?</h4>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p style="color: var(--text-secondary); font-size: 0.9rem;">
                        Are you sure you want to permanently delete grooming record <strong style="color: var(--white);" data-bind="target_name"></strong>?
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
