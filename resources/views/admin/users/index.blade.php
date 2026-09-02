@extends('layouts.app')

@php
    $title = 'Staff Accounts';
    $headerTitle = 'Clinic Staff & Roles';
    $breadcrumb = 'User Management';
@endphp

@section('content')
    <div class="table-toolbar">
        <div class="search-input-wrapper">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            <input type="text" class="form-control" placeholder="Search staff members..." data-table-search="users-table">
        </div>

        <button type="button" class="btn btn-gold" data-modal-target="modal-new-user">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
            <span>Add Staff Account</span>
        </button>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title-group">
                <h3 class="card-title">Staff Directory</h3>
                <span class="card-subtitle">Accounts for Admin, Cashier, Veterinarian, and Manager</span>
            </div>
        </div>

        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table" id="users-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Assigned Role</th>
                            <th>License / Contact</th>
                            <th>Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>
                                    <div style="font-weight: 700; color: var(--white);">{{ $user->name }}</div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="badge badge-gold">{{ ucfirst($user->role) }}</span>
                                </td>
                                <td>
                                    <div style="font-size: 0.85rem;">{{ $user->contact_number ?? 'No contact' }}</div>
                                    @if($user->license_no)
                                        <div style="font-size: 0.72rem; color: var(--gold-light);">Lic: {{ $user->license_no }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $user->status === 'active' ? 'badge-success' : 'badge-danger' }}">
                                        {{ ucfirst($user->status) }}
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 0.4rem;">
                                        <button type="button" class="btn btn-navy btn-sm"
                                            data-modal-target="modal-edit-user"
                                            data-action-url="{{ route('admin.users.update', $user->id) }}"
                                            data-field-name="{{ $user->name }}"
                                            data-field-email="{{ $user->email }}"
                                            data-field-role="{{ $user->role }}"
                                            data-field-license_no="{{ $user->license_no }}"
                                            data-field-contact_number="{{ $user->contact_number }}"
                                            data-field-status="{{ $user->status }}">
                                            Edit
                                        </button>

                                        @if($user->id !== auth()->id())
                                            <button type="button" class="btn btn-danger btn-sm"
                                                data-modal-target="modal-delete-user"
                                                data-action-url="{{ route('admin.users.destroy', $user->id) }}"
                                                data-field-target_name="{{ $user->name }} ({{ $user->role }})">
                                                Delete
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal: New Staff -->
    <div class="modal-backdrop" id="modal-new-user">
        <div class="modal-dialog">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">👤</div>
                    <h4 class="modal-title">Create Staff Account</h4>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Full Name <span class="req">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Dr. Maria Santos" required>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Email Address <span class="req">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="maria@sanmodesto.com" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Initial Password <span class="req">*</span></label>
                            <input type="password" name="password" class="form-control" value="password" required>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Role <span class="req">*</span></label>
                            <select name="role" class="form-select" required>
                                <option value="veterinarian">Veterinarian</option>
                                <option value="cashier">Cashier</option>
                                <option value="manager">Manager</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">License No (If Vet)</label>
                            <input type="text" name="license_no" class="form-control" placeholder="PRC-VET-XXXX">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Contact Number</label>
                        <input type="text" name="contact_number" class="form-control" placeholder="0917-XXX-XXXX">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold">Create Account</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Edit Staff -->
    <div class="modal-backdrop" id="modal-edit-user">
        <div class="modal-dialog">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">✏️</div>
                    <h4 class="modal-title">Edit Staff Account</h4>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Full Name <span class="req">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Email Address <span class="req">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">New Password (Leave blank to keep current)</label>
                            <input type="password" name="password" class="form-control" placeholder="••••••••">
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Role <span class="req">*</span></label>
                            <select name="role" class="form-select" required>
                                <option value="veterinarian">Veterinarian</option>
                                <option value="cashier">Cashier</option>
                                <option value="manager">Manager</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status <span class="req">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">License No</label>
                            <input type="text" name="license_no" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Contact Number</label>
                            <input type="text" name="contact_number" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold">Update Staff Details</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Delete Staff -->
    <div class="modal-backdrop" id="modal-delete-user">
        <div class="modal-dialog modal-danger modal-sm">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">🗑️</div>
                    <h4 class="modal-title">Delete Staff Account?</h4>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p style="color: var(--text-secondary); font-size: 0.9rem;">
                        Are you sure you want to permanently delete user <strong style="color: var(--white);" data-bind="target_name"></strong>?
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
