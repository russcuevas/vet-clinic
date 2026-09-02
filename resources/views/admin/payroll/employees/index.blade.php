@extends('layouts.app')

@php
    $title = 'Employee Database';
    $headerTitle = 'Personnel & Compensation Directory';
    $breadcrumb = 'Payroll / Employees';
@endphp

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--white); margin-bottom: 0.25rem;">Employee Master Database</h2>
            <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0;">Manage clinic staff compensation rates, positions, and statutory government identities.</p>
        </div>
        <button type="button" class="btn btn-gold" data-modal-target="modal-add-employee">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            <span>+ Register New Employee</span>
        </button>
    </div>

    <!-- Filter Bar -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body" style="padding: 1rem 1.25rem;">
            <form method="GET" action="{{ route('admin.payroll.employees.index') }}" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
                <div style="flex: 1; min-width: 200px;">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, employee # or email..." value="{{ request('search') }}">
                </div>
                <div style="min-width: 160px;">
                    <select name="position" class="form-control">
                        <option value="">All Positions</option>
                        @foreach($positions as $pos)
                            <option value="{{ $pos }}" {{ request('position') == $pos ? 'selected' : '' }}>{{ $pos }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="min-width: 140px;">
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="on_leave" {{ request('status') == 'on_leave' ? 'selected' : '' }}>On Leave</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-navy btn-sm">Filter</button>
                @if(request()->anyFilled(['search', 'position', 'status']))
                    <a href="{{ route('admin.payroll.employees.index') }}" class="btn btn-ghost btn-sm">Reset</a>
                @endif
            </form>
        </div>
    </div>

    <!-- Employee Table -->
    <div class="card">
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>EMP #</th>
                            <th>Staff Member</th>
                            <th>Position & Dept</th>
                            <th>Basic Salary (Mo.)</th>
                            <th>Daily / Hourly</th>
                            <th>Gov Accounts</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $emp)
                            <tr>
                                <td>
                                    <span style="font-family: monospace; font-weight: 700; color: var(--gold-light); font-size: 0.85rem;">
                                        {{ $emp->employee_code }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--white);">{{ $emp->full_name }}</div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">
                                        {{ $emp->email ?: 'No email' }} • {{ $emp->phone ?: 'No phone' }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-gold" style="font-size: 0.72rem;">{{ $emp->position }}</span>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">{{ $emp->department }} • {{ ucfirst(str_replace('_', ' ', $emp->employment_type)) }}</div>
                                </td>
                                <td style="font-weight: 700; color: var(--white);">
                                    ₱{{ number_format($emp->basic_salary, 2) }}
                                </td>
                                <td>
                                    <div style="font-size: 0.8rem; color: var(--white);">₱{{ number_format($emp->daily_rate, 2) }}/day</div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">₱{{ number_format($emp->hourly_rate, 2) }}/hr</div>
                                </td>
                                <td>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">
                                        SSS: <span style="color: var(--white);">{{ $emp->sss_no ?: '-' }}</span><br>
                                        PhilHealth: <span style="color: var(--white);">{{ $emp->philhealth_no ?: '-' }}</span><br>
                                        Pag-IBIG: <span style="color: var(--white);">{{ $emp->pagibig_no ?: '-' }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($emp->status === 'active')
                                        <span class="badge badge-success" style="font-size: 0.7rem;">Active</span>
                                    @elseif($emp->status === 'on_leave')
                                        <span class="badge badge-warning" style="font-size: 0.7rem;">On Leave</span>
                                    @else
                                        <span class="badge badge-danger" style="font-size: 0.7rem;">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.35rem;">
                                        <button type="button" class="btn btn-navy btn-sm" style="padding: 0.25rem 0.5rem;"
                                            data-modal-target="modal-edit-emp-{{ $emp->id }}" title="Edit Details">
                                            ✏️
                                        </button>
                                        <form action="{{ route('admin.payroll.employees.destroy', $emp) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to remove this employee?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-ghost btn-sm" style="padding: 0.25rem 0.5rem; color: #ef4444;" title="Delete">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- Edit Modal for this Employee -->
                            <div class="modal-backdrop" id="modal-edit-emp-{{ $emp->id }}">
                                <div class="modal-dialog modal-lg" style="max-width: 920px; width: 95%;">
                                    <div class="modal-header">
                                        <div class="modal-title-group">
                                            <h4 class="modal-title">Edit Employee: {{ $emp->full_name }}</h4>
                                            <span style="font-size: 0.75rem; color: var(--gold-light);">Code: {{ $emp->employee_code }}</span>
                                        </div>
                                        <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
                                    </div>
                                    <form action="{{ route('admin.payroll.employees.update', $emp) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body" style="padding: 1.75rem; max-height: 80vh; overflow-y: auto;">
                                            <!-- Section 1: Personal & Position Details -->
                                            <div style="margin-bottom: 1.25rem;">
                                                <h5 style="font-size: 0.85rem; font-weight: 700; color: var(--gold-light); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 6px;">
                                                    <span>👤 Personal & Position Information</span>
                                                </h5>
                                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                                    <div class="form-group">
                                                        <label class="form-label">First Name *</label>
                                                        <input type="text" name="first_name" class="form-control" value="{{ $emp->first_name }}" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Last Name *</label>
                                                        <input type="text" name="last_name" class="form-control" value="{{ $emp->last_name }}" required>
                                                    </div>
                                                </div>

                                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                                                    <div class="form-group">
                                                        <label class="form-label">Position / Role *</label>
                                                        <select name="position" class="form-control" required>
                                                            <option value="Veterinarian" {{ $emp->position == 'Veterinarian' ? 'selected' : '' }}>Veterinarian</option>
                                                            <option value="Groomer" {{ $emp->position == 'Groomer' ? 'selected' : '' }}>Groomer</option>
                                                            <option value="Janitor / Kennel Staff" {{ $emp->position == 'Janitor / Kennel Staff' ? 'selected' : '' }}>Janitor / Kennel Staff</option>
                                                            <option value="Cashier" {{ $emp->position == 'Cashier' ? 'selected' : '' }}>Cashier</option>
                                                            <option value="Receptionist" {{ $emp->position == 'Receptionist' ? 'selected' : '' }}>Receptionist</option>
                                                            <option value="Manager" {{ $emp->position == 'Manager' ? 'selected' : '' }}>Manager</option>
                                                            <option value="Clinic Assistant" {{ $emp->position == 'Clinic Assistant' ? 'selected' : '' }}>Clinic Assistant</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Department</label>
                                                        <input type="text" name="department" class="form-control" value="{{ $emp->department }}">
                                                    </div>
                                                </div>

                                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                                                    <div class="form-group">
                                                        <label class="form-label">Email Address</label>
                                                        <input type="email" name="email" class="form-control" value="{{ $emp->email }}">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Contact Phone</label>
                                                        <input type="text" name="phone" class="form-control" value="{{ $emp->phone }}">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section 2: Compensation & Rates -->
                                            <div style="margin-bottom: 1.25rem; border-top: 1px solid var(--navy-border); padding-top: 1rem;">
                                                <h5 style="font-size: 0.85rem; font-weight: 700; color: var(--gold-light); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 6px;">
                                                    <span>💵 Compensation & Work Rates</span>
                                                </h5>
                                                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                                                    <div class="form-group">
                                                        <label class="form-label">Basic Salary (₱/Month) *</label>
                                                        <input type="number" step="0.01" name="basic_salary" class="form-control" value="{{ $emp->basic_salary }}" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Daily Rate (₱)</label>
                                                        <input type="number" step="0.01" name="daily_rate" class="form-control" value="{{ $emp->daily_rate }}">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Hourly Rate (₱)</label>
                                                        <input type="number" step="0.01" name="hourly_rate" class="form-control" value="{{ $emp->hourly_rate }}">
                                                    </div>
                                                </div>

                                                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                                                    <div class="form-group">
                                                        <label class="form-label">Employment Type *</label>
                                                        <select name="employment_type" class="form-control" required>
                                                            <option value="full_time" {{ $emp->employment_type == 'full_time' ? 'selected' : '' }}>Full Time</option>
                                                            <option value="part_time" {{ $emp->employment_type == 'part_time' ? 'selected' : '' }}>Part Time</option>
                                                            <option value="contract" {{ $emp->employment_type == 'contract' ? 'selected' : '' }}>Contract</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Status *</label>
                                                        <select name="status" class="form-control" required>
                                                            <option value="active" {{ $emp->status == 'active' ? 'selected' : '' }}>Active</option>
                                                            <option value="inactive" {{ $emp->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                            <option value="on_leave" {{ $emp->status == 'on_leave' ? 'selected' : '' }}>On Leave</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Date Hired</label>
                                                        <input type="date" name="date_hired" class="form-control" value="{{ $emp->date_hired ? $emp->date_hired->format('Y-m-d') : '' }}">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section 3: Statutory IDs -->
                                            <div style="border-top: 1px solid var(--navy-border); padding-top: 1rem;">
                                                <h5 style="font-size: 0.85rem; font-weight: 700; color: var(--gold-light); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 6px;">
                                                    <span>🏛️ Government Contributions & System Account</span>
                                                </h5>
                                                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 0.75rem;">
                                                    <div class="form-group">
                                                        <label class="form-label">SSS No.</label>
                                                        <input type="text" name="sss_no" class="form-control" value="{{ $emp->sss_no }}">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">PhilHealth</label>
                                                        <input type="text" name="philhealth_no" class="form-control" value="{{ $emp->philhealth_no }}">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Pag-IBIG</label>
                                                        <input type="text" name="pagibig_no" class="form-control" value="{{ $emp->pagibig_no }}">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">TIN No.</label>
                                                        <input type="text" name="tin_no" class="form-control" value="{{ $emp->tin_no }}">
                                                    </div>
                                                </div>

                                                <div class="form-group" style="margin-top: 0.75rem;">
                                                    <label class="form-label">Link System User Account (Optional)</label>
                                                    <select name="user_id" class="form-control">
                                                        <option value="">-- None (Standalone Staff) --</option>
                                                        @foreach($users as $user)
                                                            <option value="{{ $user->id }}" {{ $emp->user_id == $user->id ? 'selected' : '' }}>
                                                                {{ $user->name }} ({{ $user->email }} - {{ $user->role }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer" style="padding: 1.25rem 1.75rem;">
                                            <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                                            <button type="submit" class="btn btn-gold" style="padding: 0.6rem 1.5rem;">Update Employee Details</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 2.5rem;">
                                    No employees found. Click "+ Register New Employee" to register clinic staff.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($employees->hasPages())
                <div style="padding: 1rem;">
                    {{ $employees->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal: Register New Employee -->
    <div class="modal-backdrop" id="modal-add-employee">
        <div class="modal-dialog modal-lg" style="max-width: 920px; width: 95%;">
            <div class="modal-header">
                <div class="modal-title-group">
                    <h4 class="modal-title">Register New Employee</h4>
                    <span style="font-size: 0.75rem; color: var(--gold-light);">Automatic Employee ID generation (EMP-YYYY-XXXX)</span>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form action="{{ route('admin.payroll.employees.store') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding: 1.75rem; max-height: 80vh; overflow-y: auto;">
                    <!-- Section 1: Personal & Position Details -->
                    <div style="margin-bottom: 1.25rem;">
                        <h5 style="font-size: 0.85rem; font-weight: 700; color: var(--gold-light); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 6px;">
                            <span>👤 Personal & Position Information</span>
                        </h5>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label class="form-label">First Name *</label>
                                <input type="text" name="first_name" class="form-control" placeholder="e.g. Arnel" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Last Name *</label>
                                <input type="text" name="last_name" class="form-control" placeholder="e.g. Bautista" required>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                            <div class="form-group">
                                <label class="form-label">Position / Role *</label>
                                <select name="position" class="form-control" required>
                                    <option value="Groomer">Groomer</option>
                                    <option value="Veterinarian">Veterinarian</option>
                                    <option value="Janitor / Kennel Staff">Janitor / Kennel Staff</option>
                                    <option value="Cashier">Cashier</option>
                                    <option value="Receptionist">Receptionist</option>
                                    <option value="Clinic Assistant">Clinic Assistant</option>
                                    <option value="Manager">Manager</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Department</label>
                                <input type="text" name="department" class="form-control" value="Operations">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                            <div class="form-group">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" placeholder="employee@sanmodesto.com">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Contact Phone</label>
                                <input type="text" name="phone" class="form-control" placeholder="0917-000-0000">
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Compensation & Salary -->
                    <div style="margin-bottom: 1.25rem; border-top: 1px solid var(--navy-border); padding-top: 1rem;">
                        <h5 style="font-size: 0.85rem; font-weight: 700; color: var(--gold-light); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 6px;">
                            <span>💵 Compensation & Work Rates</span>
                        </h5>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label class="form-label">Monthly Basic Salary (₱) *</label>
                                <input type="number" step="0.01" name="basic_salary" id="add_basic_salary" class="form-control" placeholder="e.g. 18000" required>
                                <span style="font-size: 0.68rem; color: var(--text-muted);">Daily & Hourly auto-computed if blank</span>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Daily Rate (₱)</label>
                                <input type="number" step="0.01" name="daily_rate" class="form-control" placeholder="Auto: Basic / 22">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Hourly Rate (₱)</label>
                                <input type="number" step="0.01" name="hourly_rate" class="form-control" placeholder="Auto: Daily / 8">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                            <div class="form-group">
                                <label class="form-label">Employment Type *</label>
                                <select name="employment_type" class="form-control" required>
                                    <option value="full_time" selected>Full Time</option>
                                    <option value="part_time">Part Time</option>
                                    <option value="contract">Contract</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Status *</label>
                                <select name="status" class="form-control" required>
                                    <option value="active" selected>Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="on_leave">On Leave</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Date Hired</label>
                                <input type="date" name="date_hired" class="form-control" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Statutory Government Identifications -->
                    <div style="border-top: 1px solid var(--navy-border); padding-top: 1rem;">
                        <h5 style="font-size: 0.85rem; font-weight: 700; color: var(--gold-light); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 6px;">
                            <span>🏛️ Government Contributions & System Account</span>
                        </h5>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 0.75rem;">
                            <div class="form-group">
                                <label class="form-label">SSS No.</label>
                                <input type="text" name="sss_no" class="form-control" placeholder="00-0000000-0">
                            </div>
                            <div class="form-group">
                                <label class="form-label">PhilHealth</label>
                                <input type="text" name="philhealth_no" class="form-control" placeholder="00-000000000-0">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Pag-IBIG</label>
                                <input type="text" name="pagibig_no" class="form-control" placeholder="0000-0000-0000">
                            </div>
                            <div class="form-group">
                                <label class="form-label">TIN No.</label>
                                <input type="text" name="tin_no" class="form-control" placeholder="000-000-000">
                            </div>
                        </div>

                        <div class="form-group" style="margin-top: 0.75rem;">
                            <label class="form-label">Link System User Account (Optional)</label>
                            <select name="user_id" class="form-control">
                                <option value="">-- None (Standalone Staff) --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }} - {{ $user->role }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 1.25rem 1.75rem;">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold" style="padding: 0.6rem 1.5rem;">Save Employee Record</button>
                </div>
            </form>
        </div>
    </div>
@endsection
