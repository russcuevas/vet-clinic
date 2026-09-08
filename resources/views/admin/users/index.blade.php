@extends('layouts.app')

@php
    $title = 'Account Management';
    $headerTitle = 'User Accounts & Access Management';
    $breadcrumb = 'Administration / Account Management';
@endphp

@section('content')
    <!-- Stat Grid -->
    <div class="stat-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 1.5rem;">
        <div class="stat-card">
            <span class="stat-title">Total Accounts</span>
            <div class="stat-value">{{ $users->count() }}</div>
            <div class="stat-desc">Registered system users</div>
        </div>
        <div class="stat-card">
            <span class="stat-title">Active Access</span>
            <div class="stat-value" style="color: #10b981;">{{ $users->where('status', 'active')->count() }}</div>
            <div class="stat-desc">Can log in to portal</div>
        </div>
        <div class="stat-card">
            <span class="stat-title">Administrators</span>
            <div class="stat-value" style="color: var(--gold-light);">{{ $users->where('role', 'admin')->count() }}</div>
            <div class="stat-desc">Full clinic & payroll privileges</div>
        </div>
        <div class="stat-card">
            <span class="stat-title">Doctors & Staff</span>
            <div class="stat-value" style="color: #38bdf8;">{{ $users->whereIn('role', ['veterinarian', 'cashier', 'manager', 'inventory_officer', 'back_office', 'receptionist'])->count() }}</div>
            <div class="stat-desc">Clinical, desk, front desk & stock personnel</div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="table-toolbar">
        <div class="search-input-wrapper">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input type="text" class="form-control" placeholder="Search account name, email, or role..." data-table-search="users-table">
        </div>

        <button type="button" class="btn btn-gold" data-modal-target="modal-new-user">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            <span>+ Create New Account</span>
        </button>
    </div>

    <!-- Accounts Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-title-group">
                <h3 class="card-title">System Account Directory</h3>
                <span class="card-subtitle">Manage login credentials, authorization roles, and account statuses</span>
            </div>
        </div>

        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table" id="users-table">
                    <thead>
                        <tr>
                            <th>User Account</th>
                            <th>Email Address</th>
                            <th>Role & Permissions</th>
                            <th>License / Contact</th>
                            <th>Access Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--navy-surface); border: 1px solid var(--gold-border); display: flex; align-items: center; justify-content: center; font-weight: 700; color: var(--gold-light); font-size: 0.85rem;">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div style="font-weight: 700; color: var(--white);">
                                                {{ $user->name }}
                                                @if($user->id === auth()->id())
                                                    <span class="badge badge-gold" style="font-size: 0.65rem; margin-left: 4px;">You</span>
                                                @endif
                                            </div>
                                            <div style="font-size: 0.72rem; color: var(--text-muted);">Joined {{ $user->created_at->format('M d, Y') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-family: monospace; font-size: 0.82rem; color: var(--text-secondary);">{{ $user->email }}</span>
                                </td>
                                <td>
                                    @if($user->role === 'admin')
                                        <span class="badge badge-gold" style="font-size: 0.72rem;">👑 Admin (Full Access)</span>
                                    @elseif($user->role === 'veterinarian')
                                        <span class="badge badge-blue" style="font-size: 0.72rem;">🩺 Veterinarian</span>
                                    @elseif($user->role === 'cashier')
                                        <span class="badge badge-success" style="font-size: 0.72rem;">💳 Cashier & Billing</span>
                                    @elseif($user->role === 'manager')
                                        <span class="badge badge-purple" style="font-size: 0.72rem;">💼 Clinic Manager</span>
                                    @elseif($user->role === 'inventory_officer')
                                        <span class="badge badge-gold" style="font-size: 0.72rem; background: rgba(212, 175, 55, 0.2);">📦 Inventory Officer</span>
                                    @elseif($user->role === 'back_office')
                                        <span class="badge badge-navy" style="font-size: 0.72rem; border-color: var(--blue-accent); color: #38bdf8;">🏢 Back Office</span>
                                    @elseif($user->role === 'receptionist')
                                        <span class="badge badge-purple" style="font-size: 0.72rem; background: rgba(168, 85, 247, 0.15); color: #c084fc; border-color: rgba(168, 85, 247, 0.3);">📅 Receptionist</span>
                                    @else
                                        <span class="badge badge-navy" style="font-size: 0.72rem;">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-size: 0.85rem;">{{ $user->contact_number ?: '-' }}</div>
                                    @if($user->license_no)
                                        <div style="font-size: 0.72rem; color: var(--gold-light);">PRC: {{ $user->license_no }}</div>
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
                                            data-field-status="{{ $user->status }}"
                                            title="Edit Account Details">
                                            ✏️ Edit
                                        </button>

                                        @if($user->id !== auth()->id())
                                            <button type="button" class="btn btn-danger btn-sm"
                                                data-modal-target="modal-delete-user"
                                                data-action-url="{{ route('admin.users.destroy', $user->id) }}"
                                                data-field-target_name="{{ $user->name }} ({{ $user->role }})"
                                                title="Delete Account">
                                                🗑️ Delete
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

    <!-- Modal: Create New Account -->
    <div class="modal-backdrop" id="modal-new-user">
        <div class="modal-dialog modal-lg" style="max-width: 760px; width: 95%;">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">👤</div>
                    <h4 class="modal-title">Create System User Account</h4>
                    <span style="font-size: 0.72rem; color: var(--gold-light);">Add staff credentials for portal authentication</span>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding: 1.75rem;">
                    <div class="form-group">
                        <label class="form-label">Full Name <span class="req">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Dr. Juan Dela Cruz" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Email Address (Login Username) <span class="req">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="staff@sanmodesto.com" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Initial Password <span class="req">*</span></label>
                            <input type="password" name="password" class="form-control" value="password" required>
                            <span style="font-size: 0.7rem; color: var(--text-muted);">Default is 'password' (can be updated later)</span>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Assigned Role <span class="req">*</span></label>
                            <select name="role" class="form-control" required>
                                <option value="veterinarian">Veterinarian (Clinical & Prescriptions)</option>
                                <option value="cashier">Cashier (Billing & Cash Register)</option>
                                <option value="receptionist">Receptionist (Appointments & Front Desk)</option>
                                <option value="manager">Manager (Operations & Review)</option>
                                <option value="inventory_officer">Inventory Officer (Instruments & Restock)</option>
                                <option value="back_office">Back Office (Instruments & Records)</option>
                                <option value="admin">Administrator (Full Clinic & Payroll)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">PRC License No. (Optional for Doctors)</label>
                            <input type="text" name="license_no" class="form-control" placeholder="PRC-VET-0012345">
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 0.75rem;">
                        <label class="form-label">Contact Number</label>
                        <input type="text" name="contact_number" class="form-control" placeholder="0917-123-4567">
                    </div>
                </div>
                <div class="modal-footer" style="padding: 1.25rem 1.75rem;">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold" style="padding: 0.6rem 1.5rem;">Create User Account</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Edit Staff Account -->
    <div class="modal-backdrop" id="modal-edit-user">
        <div class="modal-dialog modal-lg" style="max-width: 760px; width: 95%;">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">✏️</div>
                    <h4 class="modal-title">Edit User Account & Credentials</h4>
                    <span style="font-size: 0.72rem; color: var(--gold-light);">Update user role, password, and access status</span>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-body" style="padding: 1.75rem;">
                    <div class="form-group">
                        <label class="form-label">Full Name <span class="req">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Email Address (Login Username) <span class="req">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
                            <span style="font-size: 0.7rem; color: var(--text-muted);">Only enter if changing password</span>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Assigned Role <span class="req">*</span></label>
                            <select name="role" class="form-control" required>
                                <option value="veterinarian">Veterinarian (Clinical & Prescriptions)</option>
                                <option value="cashier">Cashier (Billing & Cash Register)</option>
                                <option value="receptionist">Receptionist (Appointments & Front Desk)</option>
                                <option value="manager">Manager (Operations & Review)</option>
                                <option value="inventory_officer">Inventory Officer (Instruments & Restock)</option>
                                <option value="back_office">Back Office (Instruments & Records)</option>
                                <option value="admin">Administrator (Full Clinic & Payroll)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Account Access Status <span class="req">*</span></label>
                            <select name="status" class="form-control" required>
                                <option value="active">Active (Can log in)</option>
                                <option value="inactive">Inactive (Access Suspended)</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">PRC License No.</label>
                            <input type="text" name="license_no" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Contact Number</label>
                            <input type="text" name="contact_number" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 1.25rem 1.75rem;">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold" style="padding: 0.6rem 1.5rem;">Update Account</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Delete Account Confirmation -->
    <div class="modal-backdrop" id="modal-delete-user">
        <div class="modal-dialog modal-danger modal-sm" style="max-width: 460px;">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">🗑️</div>
                    <h4 class="modal-title">Delete User Account?</h4>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="modal-body" style="padding: 1.5rem;">
                    <p style="color: var(--text-secondary); font-size: 0.9rem; line-height: 1.5; margin: 0;">
                        Are you sure you want to permanently delete the account for <strong style="color: var(--white);" data-bind="target_name"></strong>? This user will no longer be able to log in.
                    </p>
                </div>
                <div class="modal-footer" style="padding: 1.25rem 1.5rem;">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-danger" style="padding: 0.55rem 1.3rem;">Confirm Delete</button>
                </div>
            </form>
        </div>
    </div>
@endsection
