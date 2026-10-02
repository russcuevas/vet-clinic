@extends('layouts.app')

@php
    $title = 'Employee Database';
    $headerTitle = 'Personnel & Compensation Directory';
    $breadcrumb = 'Payroll / Employees';
@endphp

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--white); margin-bottom: 0.25rem;">
                👥 Employee Master Database
            </h2>
            <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0;">
                Comprehensive staff profiles, previous employment records, statutory benefits, and credentials repository.
            </p>
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
            <form method="GET" action="{{ route('manager.payroll.employees.index') }}" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
                <div style="flex: 1; min-width: 200px;">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, code, email, or previous employer..." value="{{ request('search') }}">
                </div>
                <div style="min-width: 160px;">
                    <select name="position" class="form-control">
                        <option value="">All Positions</option>
                        @foreach ($positions as $pos)
                            <option value="{{ $pos }}" {{ request('position') == $pos ? 'selected' : '' }}>{{ $pos }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="min-width: 150px;">
                    <select name="employment_type" class="form-control">
                        <option value="">All Employment Types</option>
                        <option value="regular" {{ request('employment_type') == 'regular' ? 'selected' : '' }}>Regular</option>
                        <option value="casual" {{ request('employment_type') == 'casual' ? 'selected' : '' }}>Casual</option>
                        <option value="probationary" {{ request('employment_type') == 'probationary' ? 'selected' : '' }}>Probationary</option>
                        <option value="contractual" {{ request('employment_type') == 'contractual' ? 'selected' : '' }}>Contractual</option>
                        <option value="part_time" {{ request('employment_type') == 'part_time' ? 'selected' : '' }}>Part Time</option>
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
                @if (request()->anyFilled(['search', 'position', 'employment_type', 'status']))
                    <a href="{{ route('manager.payroll.employees.index') }}" class="btn btn-ghost btn-sm">Reset</a>
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
                            <th>Position & Shift</th>
                            <th>Employment Type</th>
                            <th>Basic Salary (Mo.)</th>
                            <th>Daily / Hourly (Divisor)</th>
                            <th>Gov Accounts & Benefits</th>
                            <th>Attached Files</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $emp)
                            @php
                                $isGov = method_exists($emp, 'isGovEligible') 
                                    ? $emp->isGovEligible() 
                                    : in_array(strtolower($emp->employment_type ?? ''), ['regular', 'casual', 'full_time']);
                                $docCount = collect([
                                    $emp->police_clearance_file,
                                    $emp->medical_certificate_file,
                                    $emp->sss_id_file,
                                    $emp->philhealth_id_file,
                                    $emp->pagibig_id_file,
                                    $emp->drivers_license_file,
                                    $emp->other_doc_file
                                ])->filter()->count();
                            @endphp
                            <tr>
                                <td>
                                    <span style="font-family: monospace; font-weight: 700; color: var(--gold-light); font-size: 0.85rem;">
                                        {{ $emp->employee_code }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--white);">{{ $emp->full_name }}</div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">
                                        {{ $emp->phone ?: 'No phone' }} • {{ $emp->email ?: 'No email' }}
                                    </div>
                                    @if($emp->emergency_contact_name)
                                        <div style="font-size: 0.68rem; color: #94a3b8;">
                                            🚨 ICE: {{ $emp->emergency_contact_name }} ({{ $emp->emergency_contact_phone }})
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-gold" style="font-size: 0.72rem;">{{ $emp->position }}</span>
                                    <div style="font-size: 0.72rem; color: var(--gold-light); font-weight: 700; margin-top: 3px;">
                                        ⏰ {{ $emp->shift_start ? \Carbon\Carbon::parse($emp->shift_start)->format('g:i A') : '9:00 AM' }} - {{ $emp->shift_end ? \Carbon\Carbon::parse($emp->shift_end)->format('g:i A') : '6:00 PM' }}
                                    </div>
                                    <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 1px;">
                                        {{ $emp->department }}
                                    </div>
                                </td>
                                <td>
                                    @if($emp->employment_type === 'regular')
                                        <span class="badge badge-success" style="font-size: 0.72rem;">⭐ Regular</span>
                                    @elseif($emp->employment_type === 'casual')
                                        <span class="badge badge-blue" style="font-size: 0.72rem;">🔹 Casual</span>
                                    @elseif($emp->employment_type === 'probationary')
                                        <span class="badge badge-warning" style="font-size: 0.72rem;">⏳ Probationary</span>
                                    @elseif($emp->employment_type === 'contractual')
                                        <span class="badge badge-navy" style="font-size: 0.72rem;">📄 Contractual</span>
                                    @else
                                        <span class="badge badge-ghost" style="font-size: 0.72rem;">{{ ucfirst(str_replace('_', ' ', $emp->employment_type ?? 'Staff')) }}</span>
                                    @endif
                                </td>
                                <td style="font-weight: 700; color: var(--white);">
                                    ₱{{ number_format($emp->basic_salary, 2) }}
                                </td>
                                <td>
                                    <div style="font-size: 0.8rem; color: var(--white); font-weight: 700;">
                                        ₱{{ number_format($emp->daily_rate, 2) }}/day
                                    </div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">
                                        ₱{{ number_format($emp->hourly_rate, 2) }}/hr
                                    </div>
                                    <div style="font-size: 0.68rem; color: var(--gold-light); margin-top: 2px;">
                                        Divisor: <strong>{{ $emp->divisor_days ?: 26 }} days</strong> ({{ $emp->rest_days_per_week ?: 1 }} rest {{ Str::plural('day', $emp->rest_days_per_week ?: 1) }})
                                    </div>
                                </td>
                                <td>
                                    @if($isGov)
                                        <div style="font-size: 0.72rem; color: #cbd5e1; line-height: 1.45;">
                                            <div><strong>SSS:</strong> {{ $emp->sss_no ?: 'No ID' }} <span style="color: #10b981;">(₱{{ number_format($emp->sss_contribution, 2) }}/mo)</span></div>
                                            <div><strong>PhilHealth:</strong> {{ $emp->philhealth_no ?: 'No ID' }} <span style="color: #38bdf8;">(₱{{ number_format($emp->philhealth_contribution, 2) }}/mo)</span></div>
                                            <div><strong>Pag-IBIG:</strong> {{ $emp->pagibig_no ?: 'No ID' }} <span style="color: #fbbf24;">(₱{{ number_format($emp->pagibig_contribution, 2) }}/mo)</span></div>
                                            <div style="margin-top: 3px; font-weight: 700; color: var(--gold-light); font-size: 0.7rem;">
                                                Total Ded: ₱{{ number_format($emp->total_gov_contributions, 2) }}/mo
                                            </div>
                                        </div>
                                    @else
                                        <span class="badge badge-navy" style="font-size: 0.7rem; color: #94a3b8; border: 1px dashed rgba(148, 163, 184, 0.4);">
                                            Non-Eligible ({{ ucfirst($emp->employment_type ?? 'Probationary') }})
                                        </span>
                                        <div style="font-size: 0.67rem; color: var(--text-muted); margin-top: 3px;">
                                            Gov benefits start on Casual/Regular status
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($docCount > 0)
                                        <span class="badge badge-gold" style="font-size: 0.72rem; cursor: pointer;" data-modal-target="modal-view-emp-{{ $emp->id }}">
                                            📁 {{ $docCount }} Attached
                                        </span>
                                    @else
                                        <span style="font-size: 0.72rem; color: var(--text-muted);">None</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($emp->status === 'active')
                                        <span class="badge badge-success" style="font-size: 0.7rem;">Active</span>
                                    @elseif($emp->status === 'on_leave')
                                        <span class="badge badge-warning" style="font-size: 0.7rem;">On Leave</span>
                                    @else
                                        <span class="badge badge-danger" style="font-size: 0.7rem;">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.35rem;">
                                        <button type="button" class="btn btn-navy btn-sm" style="padding: 0.25rem 0.5rem;" data-modal-target="modal-view-emp-{{ $emp->id }}" title="View Full Dossier">
                                            👁️
                                        </button>
                                        <button type="button" class="btn btn-gold btn-sm" style="padding: 0.25rem 0.5rem;" data-modal-target="modal-edit-emp-{{ $emp->id }}" title="Edit Details">
                                            ✏️
                                        </button>
                                        <form action="{{ route('manager.payroll.employees.destroy', $emp) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this employee?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-ghost btn-sm" style="padding: 0.25rem 0.5rem; color: #ef4444;" title="Delete">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- View Dossier Modal -->
                            <div class="modal-backdrop" id="modal-view-emp-{{ $emp->id }}">
                                <div class="modal-dialog modal-lg" style="max-width: 860px; width: 95%;">
                                    <div class="modal-header">
                                        <div class="modal-title-group">
                                            <h4 class="modal-title">📄 Personnel Dossier: {{ $emp->full_name }}</h4>
                                            <span style="font-size: 0.75rem; color: var(--gold-light);">EMP Code: {{ $emp->employee_code }} • {{ $emp->position }} ({{ ucfirst($emp->employment_type) }})</span>
                                        </div>
                                        <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
                                    </div>
                                    <div class="modal-body" style="padding: 1.5rem; max-height: 80vh; overflow-y: auto;">
                                        <!-- Personal Details -->
                                        <div style="background: rgba(11, 25, 44, 0.6); border: 1px solid var(--navy-border); border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                                            <div style="font-weight: 700; color: var(--gold-light); font-size: 0.85rem; margin-bottom: 0.75rem; text-transform: uppercase;">
                                                👤 Personal Information & Contact
                                            </div>
                                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.75rem; font-size: 0.8rem;">
                                                <div><span style="color: var(--text-muted); display: block;">Birth Date:</span> <strong>{{ $emp->birth_date ? $emp->birth_date->format('M d, Y') : 'Not Set' }}</strong></div>
                                                <div><span style="color: var(--text-muted); display: block;">Gender / Civil Status:</span> <strong>{{ $emp->gender ?: '-' }} / {{ $emp->civil_status ?: '-' }}</strong></div>
                                                <div><span style="color: var(--text-muted); display: block;">Phone:</span> <strong>{{ $emp->phone ?: 'None' }}</strong></div>
                                                <div><span style="color: var(--text-muted); display: block;">Email:</span> <strong>{{ $emp->email ?: 'None' }}</strong></div>
                                                <div><span style="color: var(--text-muted); display: block;">Address:</span> <strong>{{ $emp->address ?: 'Not Specified' }}</strong></div>
                                                <div><span style="color: var(--text-muted); display: block;">Education:</span> <strong>{{ $emp->education ?: 'Not Specified' }}</strong></div>
                                                <div><span style="color: var(--text-muted); display: block;">Emergency Contact:</span> <strong>{{ $emp->emergency_contact_name ?: '-' }} ({{ $emp->emergency_contact_phone ?: '-' }})</strong></div>
                                            </div>
                                        </div>

                                        <!-- Previous Employment History -->
                                        <div style="background: rgba(11, 25, 44, 0.6); border: 1px solid var(--navy-border); border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                                            <div style="font-weight: 700; color: var(--gold-light); font-size: 0.85rem; margin-bottom: 0.75rem; text-transform: uppercase;">
                                                💼 Previous Employment History
                                            </div>
                                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.75rem; font-size: 0.8rem;">
                                                <div><span style="color: var(--text-muted); display: block;">Previous Employer / Company:</span> <strong>{{ $emp->previous_employer ?: 'None recorded / Fresh Graduate' }}</strong></div>
                                                <div><span style="color: var(--text-muted); display: block;">Previous Position:</span> <strong>{{ $emp->previous_position ?: '-' }}</strong></div>
                                                <div><span style="color: var(--text-muted); display: block;">Previous Monthly Salary:</span> <strong>{{ $emp->previous_salary ? '₱' . number_format($emp->previous_salary, 2) : '-' }}</strong></div>
                                                <div><span style="color: var(--text-muted); display: block;">Experience Duration:</span> <strong>{{ $emp->years_of_experience ?: '-' }}</strong></div>
                                            </div>
                                        </div>

                                        <!-- Statutory & Benefits Breakdown -->
                                        <div style="background: rgba(11, 25, 44, 0.6); border: 1px solid var(--navy-border); border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                                            <div style="font-weight: 700; color: var(--gold-light); font-size: 0.85rem; margin-bottom: 0.75rem; text-transform: uppercase;">
                                                🏛️ Statutory Government Accounts & Contributions
                                            </div>
                                            @if($isGov)
                                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.75rem; font-size: 0.8rem;">
                                                    <div><span style="color: var(--text-muted); display: block;">SSS No:</span> <strong>{{ $emp->sss_no ?: '-' }}</strong> <span style="color: #10b981; display: block; font-size: 0.75rem;">₱{{ number_format($emp->sss_contribution, 2) }}/mo</span></div>
                                                    <div><span style="color: var(--text-muted); display: block;">PhilHealth No:</span> <strong>{{ $emp->philhealth_no ?: '-' }}</strong> <span style="color: #38bdf8; display: block; font-size: 0.75rem;">₱{{ number_format($emp->philhealth_contribution, 2) }}/mo</span></div>
                                                    <div><span style="color: var(--text-muted); display: block;">Pag-IBIG No:</span> <strong>{{ $emp->pagibig_no ?: '-' }}</strong> <span style="color: #fbbf24; display: block; font-size: 0.75rem;">₱{{ number_format($emp->pagibig_contribution, 2) }}/mo</span></div>
                                                    <div><span style="color: var(--text-muted); display: block;">TIN No / DL No:</span> <strong>{{ $emp->tin_no ?: '-' }} / {{ $emp->drivers_license_no ?: '-' }}</strong></div>
                                                </div>
                                            @else
                                                <div style="background: rgba(245, 158, 11, 0.1); border-left: 3px solid #f59e0b; padding: 0.65rem 0.85rem; font-size: 0.78rem; color: #cbd5e1;">
                                                    <strong>Status is {{ ucfirst($emp->employment_type) }}:</strong> Statutory contributions (SSS, PhilHealth, Pag-IBIG) are not deducted on payslip until staff is transitioned to <strong>Casual</strong> or <strong>Regular</strong> status.
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Attached Documents Repository -->
                                        <div style="background: rgba(11, 25, 44, 0.6); border: 1px solid var(--navy-border); border-radius: 8px; padding: 1rem;">
                                            <div style="font-weight: 700; color: var(--gold-light); font-size: 0.85rem; margin-bottom: 0.75rem; text-transform: uppercase;">
                                                📎 Attached Credentials, Certifications & IDs
                                            </div>
                                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem;">
                                                @php
                                                    $docList = [
                                                        ['label' => 'Police Clearance / NBI', 'icon' => '🚓', 'file' => $emp->police_clearance_file],
                                                        ['label' => 'Physical Exam / Med Cert', 'icon' => '🩺', 'file' => $emp->medical_certificate_file],
                                                        ['label' => 'SSS ID / E1 Card', 'icon' => '🪪', 'file' => $emp->sss_id_file],
                                                        ['label' => 'PhilHealth ID / MDR', 'icon' => '🪪', 'file' => $emp->philhealth_id_file],
                                                        ['label' => 'Pag-IBIG ID / MDF', 'icon' => '🪪', 'file' => $emp->pagibig_id_file],
                                                        ['label' => 'Driver\'s License', 'icon' => '🚗', 'file' => $emp->drivers_license_file],
                                                        ['label' => 'Other Attachments / Resume', 'icon' => '📄', 'file' => $emp->other_doc_file],
                                                    ];
                                                @endphp
                                                @foreach($docList as $d)
                                                    <div style="background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.08); border-radius: 6px; padding: 0.75rem; display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;">
                                                        <div>
                                                            <div style="font-size: 0.78rem; font-weight: 700; color: #fff;">{{ $d['icon'] }} {{ $d['label'] }}</div>
                                                            <div style="font-size: 0.68rem; color: var(--text-muted);">
                                                                {{ $d['file'] ? 'File Uploaded' : 'Not Provided' }}
                                                            </div>
                                                        </div>
                                                        @if($d['file'])
                                                            <a href="{{ asset($d['file']) }}" target="_blank" class="btn btn-navy btn-sm" style="padding: 0.25rem 0.6rem; font-size: 0.75rem; text-decoration: none;">
                                                                👁️ View
                                                            </a>
                                                        @else
                                                            <span style="font-size: 0.7rem; color: #64748b;">--</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer" style="padding: 1rem 1.5rem;">
                                        <button type="button" class="btn btn-ghost" data-modal-close>Close</button>
                                        <button type="button" class="btn btn-gold" data-modal-close data-modal-target="modal-edit-emp-{{ $emp->id }}">✏️ Edit Profile</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Edit Modal for this Employee -->
                            <div class="modal-backdrop" id="modal-edit-emp-{{ $emp->id }}">
                                <div class="modal-dialog modal-lg" style="max-width: 960px; width: 95%;">
                                    <div class="modal-header">
                                        <div class="modal-title-group">
                                            <h4 class="modal-title">Edit Employee: {{ $emp->full_name }}</h4>
                                            <span style="font-size: 0.75rem; color: var(--gold-light);">Code: {{ $emp->employee_code }}</span>
                                        </div>
                                        <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
                                    </div>
                                    <form action="{{ route('manager.payroll.employees.update', $emp) }}" method="POST" enctype="multipart/form-data">
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

                                                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                                                    <div class="form-group">
                                                        <label class="form-label">Birth Date</label>
                                                        <input type="date" name="birth_date" class="form-control" value="{{ $emp->birth_date ? $emp->birth_date->format('Y-m-d') : '' }}">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Gender</label>
                                                        <select name="gender" class="form-control">
                                                            <option value="">-- Select --</option>
                                                            <option value="Male" {{ $emp->gender == 'Male' ? 'selected' : '' }}>Male</option>
                                                            <option value="Female" {{ $emp->gender == 'Female' ? 'selected' : '' }}>Female</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Civil Status</label>
                                                        <select name="civil_status" class="form-control">
                                                            <option value="">-- Select --</option>
                                                            <option value="Single" {{ $emp->civil_status == 'Single' ? 'selected' : '' }}>Single</option>
                                                            <option value="Married" {{ $emp->civil_status == 'Married' ? 'selected' : '' }}>Married</option>
                                                            <option value="Widowed" {{ $emp->civil_status == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                                                            <option value="Separated" {{ $emp->civil_status == 'Separated' ? 'selected' : '' }}>Separated</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                                                    <div class="form-group">
                                                        <label class="form-label">Position / Role *</label>
                                                        <select name="position" class="form-control" required>
                                                            @php
                                                                $posOptions = \App\Models\Employee::POSITIONS;
                                                                if ($emp->position && !in_array($emp->position, $posOptions)) {
                                                                    $posOptions[] = $emp->position;
                                                                }
                                                            @endphp
                                                            @foreach ($posOptions as $posOption)
                                                                <option value="{{ $posOption }}" {{ $emp->position == $posOption ? 'selected' : '' }}>
                                                                    {{ $posOption }}
                                                                </option>
                                                            @endforeach
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

                                                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                                                    <div class="form-group">
                                                        <label class="form-label">Residential Address</label>
                                                        <input type="text" name="address" class="form-control" value="{{ $emp->address }}" placeholder="e.g. 123 San Juan St, Poblacion">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Educational Attainment</label>
                                                        <input type="text" name="education" class="form-control" value="{{ $emp->education }}" placeholder="e.g. BS Veterinary Medicine / College">
                                                    </div>
                                                </div>

                                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                                                    <div class="form-group">
                                                        <label class="form-label">Emergency Contact Person</label>
                                                        <input type="text" name="emergency_contact_name" class="form-control" value="{{ $emp->emergency_contact_name }}" placeholder="e.g. Maria Bautista (Spouse)">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Emergency Contact Phone</label>
                                                        <input type="text" name="emergency_contact_phone" class="form-control" value="{{ $emp->emergency_contact_phone }}" placeholder="0917-000-0000">
                                                    </div>
                                                </div>

                                                <!-- Shift Assignment -->
                                                <div style="background: rgba(11, 25, 44, 0.4); border: 1px solid var(--navy-border); border-radius: 6px; padding: 0.75rem 1rem; margin-top: 0.75rem;">
                                                    <div style="font-size: 0.78rem; font-weight: 700; color: var(--gold-light); margin-bottom: 0.5rem;">
                                                        ⏰ Assigned Work Shift Schedule
                                                    </div>
                                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                                        <div class="form-group">
                                                            <label class="form-label">Shift Start (Time In) *</label>
                                                            <select name="shift_start" class="form-control">
                                                                <option value="08:00" {{ ($emp->shift_start ?: '09:00') == '08:00' ? 'selected' : '' }}>08:00 AM</option>
                                                                <option value="08:30" {{ ($emp->shift_start ?: '09:00') == '08:30' ? 'selected' : '' }}>08:30 AM</option>
                                                                <option value="09:00" {{ ($emp->shift_start ?: '09:00') == '09:00' ? 'selected' : '' }}>09:00 AM (Clinic Opening)</option>
                                                                <option value="09:30" {{ ($emp->shift_start ?: '09:00') == '09:30' ? 'selected' : '' }}>09:30 AM</option>
                                                                <option value="10:00" {{ ($emp->shift_start ?: '09:00') == '10:00' ? 'selected' : '' }}>10:00 AM (Late Shift)</option>
                                                                <option value="11:00" {{ ($emp->shift_start ?: '09:00') == '11:00' ? 'selected' : '' }}>11:00 AM</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="form-label">Shift End (Time Out) *</label>
                                                            <select name="shift_end" class="form-control">
                                                                <option value="17:00" {{ ($emp->shift_end ?: '18:00') == '17:00' ? 'selected' : '' }}>05:00 PM</option>
                                                                <option value="17:30" {{ ($emp->shift_end ?: '18:00') == '17:30' ? 'selected' : '' }}>05:30 PM</option>
                                                                <option value="18:00" {{ ($emp->shift_end ?: '18:00') == '18:00' ? 'selected' : '' }}>06:00 PM (Standard Close)</option>
                                                                <option value="18:30" {{ ($emp->shift_end ?: '18:00') == '18:30' ? 'selected' : '' }}>06:30 PM</option>
                                                                <option value="19:00" {{ ($emp->shift_end ?: '18:00') == '19:00' ? 'selected' : '' }}>07:00 PM (Night Close)</option>
                                                                <option value="20:00" {{ ($emp->shift_end ?: '18:00') == '20:00' ? 'selected' : '' }}>08:00 PM</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section 2: Previous Employment History -->
                                            <div style="margin-bottom: 1.25rem; border-top: 1px solid var(--navy-border); padding-top: 1rem;">
                                                <h5 style="font-size: 0.85rem; font-weight: 700; color: var(--gold-light); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 6px;">
                                                    <span>💼 Previous Employment History</span>
                                                </h5>
                                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                                    <div class="form-group">
                                                        <label class="form-label">Previous Company / Employer</label>
                                                        <input type="text" name="previous_employer" class="form-control" value="{{ $emp->previous_employer }}" placeholder="e.g. Pet Care Veterinary Clinic">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Previous Position / Role</label>
                                                        <input type="text" name="previous_position" class="form-control" value="{{ $emp->previous_position }}" placeholder="e.g. Veterinary Assistant">
                                                    </div>
                                                </div>
                                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                                                    <div class="form-group">
                                                        <label class="form-label">Previous Monthly Salary (₱)</label>
                                                        <input type="number" step="0.01" name="previous_salary" class="form-control" value="{{ $emp->previous_salary }}" placeholder="e.g. 16000">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Years of Experience / Duration</label>
                                                        <input type="text" name="years_of_experience" class="form-control" value="{{ $emp->years_of_experience }}" placeholder="e.g. 2 Years, 4 Months">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section 3: Compensation & Employment Type -->
                                            <div style="margin-bottom: 1.25rem; border-top: 1px solid var(--navy-border); padding-top: 1rem;">
                                                <h5 style="font-size: 0.85rem; font-weight: 700; color: var(--gold-light); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 6px;">
                                                    <span>💵 Compensation & Employment Classification</span>
                                                </h5>
                                                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                                                    <div class="form-group">
                                                        <label class="form-label">Employment Classification *</label>
                                                        <select name="employment_type" class="form-control" required>
                                                            <option value="regular" {{ $emp->employment_type == 'regular' ? 'selected' : '' }}>⭐ Regular Employee (With Gov Benefits)</option>
                                                            <option value="casual" {{ $emp->employment_type == 'casual' ? 'selected' : '' }}>🔹 Casual Employee (With Gov Benefits)</option>
                                                            <option value="probationary" {{ in_array($emp->employment_type, ['probationary', 'full_time']) ? 'selected' : '' }}>⏳ Probationary (No Gov Benefits)</option>
                                                            <option value="contractual" {{ in_array($emp->employment_type, ['contractual', 'contract']) ? 'selected' : '' }}>📄 Contractual (No Gov Benefits)</option>
                                                            <option value="part_time" {{ $emp->employment_type == 'part_time' ? 'selected' : '' }}>⏱️ Part Time (No Gov Benefits)</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Basic Salary (₱/Month) *</label>
                                                        <input type="number" step="0.01" name="basic_salary" class="form-control" value="{{ $emp->basic_salary }}" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Status *</label>
                                                        <select name="status" class="form-control" required>
                                                            <option value="active" {{ $emp->status == 'active' ? 'selected' : '' }}>Active</option>
                                                            <option value="inactive" {{ $emp->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                            <option value="on_leave" {{ $emp->status == 'on_leave' ? 'selected' : '' }}>On Leave</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                                                    <div class="form-group">
                                                        <label class="form-label">Divisor Days</label>
                                                        <select name="divisor_days" class="form-control">
                                                            <option value="26" {{ ($emp->divisor_days ?: 26) == 26 ? 'selected' : '' }}>26 Days (1 Restday)</option>
                                                            <option value="22" {{ ($emp->divisor_days ?: 26) == 22 ? 'selected' : '' }}>22 Days (2 Restdays - Vet)</option>
                                                            <option value="21" {{ ($emp->divisor_days ?: 26) == 21 ? 'selected' : '' }}>21.75 Days</option>
                                                            <option value="30" {{ ($emp->divisor_days ?: 26) == 30 ? 'selected' : '' }}>30 Days</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Daily Rate (₱)</label>
                                                        <input type="number" step="0.01" name="daily_rate" class="form-control" value="{{ $emp->daily_rate }}" placeholder="Auto computed">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Hourly Rate (₱)</label>
                                                        <input type="number" step="0.01" name="hourly_rate" class="form-control" value="{{ $emp->hourly_rate }}" placeholder="Auto computed">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Date Hired</label>
                                                        <input type="date" name="date_hired" class="form-control" value="{{ $emp->date_hired ? $emp->date_hired->format('Y-m-d') : '' }}">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section 4: Statutory IDs & Government Benefits -->
                                            <div style="margin-bottom: 1.25rem; border-top: 1px solid var(--navy-border); padding-top: 1rem;">
                                                <h5 style="font-size: 0.85rem; font-weight: 700; color: var(--gold-light); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 6px;">
                                                    <span>🏛️ Government Statutory Accounts</span>
                                                </h5>
                                                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr 1fr; gap: 0.75rem;">
                                                    <div class="form-group">
                                                        <label class="form-label">SSS No.</label>
                                                        <input type="text" name="sss_no" class="form-control" value="{{ $emp->sss_no }}" placeholder="00-0000000-0">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">PhilHealth</label>
                                                        <input type="text" name="philhealth_no" class="form-control" value="{{ $emp->philhealth_no }}" placeholder="00-000000000-0">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Pag-IBIG</label>
                                                        <input type="text" name="pagibig_no" class="form-control" value="{{ $emp->pagibig_no }}" placeholder="0000-0000-0000">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">TIN No.</label>
                                                        <input type="text" name="tin_no" class="form-control" value="{{ $emp->tin_no }}" placeholder="000-000-000">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Driver's License #</label>
                                                        <input type="text" name="drivers_license_no" class="form-control" value="{{ $emp->drivers_license_no }}" placeholder="N01-00-000000">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section 5: Attached Credentials & ID Documents -->
                                            <div style="margin-bottom: 1.25rem; border-top: 1px solid var(--navy-border); padding-top: 1rem;">
                                                <h5 style="font-size: 0.85rem; font-weight: 700; color: var(--gold-light); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 6px;">
                                                    <span>📎 Document & ID Attachments (PDF / Images)</span>
                                                </h5>
                                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                                    <div class="form-group">
                                                        <label class="form-label">🚓 Police Clearance / NBI</label>
                                                        <input type="file" name="police_clearance_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                                        @if($emp->police_clearance_file)
                                                            <div style="font-size: 0.72rem; margin-top: 4px;">Current: <a href="{{ asset($emp->police_clearance_file) }}" target="_blank" style="color: var(--gold-light);">View Attached Clearance ↗</a></div>
                                                        @endif
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">🩺 Physical Exam / Med Cert</label>
                                                        <input type="file" name="medical_certificate_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                                        @if($emp->medical_certificate_file)
                                                            <div style="font-size: 0.72rem; margin-top: 4px;">Current: <a href="{{ asset($emp->medical_certificate_file) }}" target="_blank" style="color: var(--gold-light);">View Medical Cert ↗</a></div>
                                                        @endif
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">🪪 SSS ID / E1 Form</label>
                                                        <input type="file" name="sss_id_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                                        @if($emp->sss_id_file)
                                                            <div style="font-size: 0.72rem; margin-top: 4px;">Current: <a href="{{ asset($emp->sss_id_file) }}" target="_blank" style="color: var(--gold-light);">View SSS Doc ↗</a></div>
                                                        @endif
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">🪪 PhilHealth ID / MDR</label>
                                                        <input type="file" name="philhealth_id_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                                        @if($emp->philhealth_id_file)
                                                            <div style="font-size: 0.72rem; margin-top: 4px;">Current: <a href="{{ asset($emp->philhealth_id_file) }}" target="_blank" style="color: var(--gold-light);">View PhilHealth Doc ↗</a></div>
                                                        @endif
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">🪪 Pag-IBIG ID / MDF Form</label>
                                                        <input type="file" name="pagibig_id_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                                        @if($emp->pagibig_id_file)
                                                            <div style="font-size: 0.72rem; margin-top: 4px;">Current: <a href="{{ asset($emp->pagibig_id_file) }}" target="_blank" style="color: var(--gold-light);">View Pag-IBIG Doc ↗</a></div>
                                                        @endif
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">🚗 Driver's License</label>
                                                        <input type="file" name="drivers_license_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                                        @if($emp->drivers_license_file)
                                                            <div style="font-size: 0.72rem; margin-top: 4px;">Current: <a href="{{ asset($emp->drivers_license_file) }}" target="_blank" style="color: var(--gold-light);">View Driver's License ↗</a></div>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="form-group" style="margin-top: 0.75rem;">
                                                    <label class="form-label">📄 Other Supporting Documents / Resume / Contract</label>
                                                    <input type="file" name="other_doc_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                                    @if($emp->other_doc_file)
                                                        <div style="font-size: 0.72rem; margin-top: 4px;">Current: <a href="{{ asset($emp->other_doc_file) }}" target="_blank" style="color: var(--gold-light);">View Attached Other File ↗</a></div>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Section 6: Link System User Account -->
                                            <div style="border-top: 1px solid var(--navy-border); padding-top: 1rem;">
                                                <div class="form-group">
                                                    <label class="form-label">Link System User Account (Optional)</label>
                                                    <select name="user_id" class="form-control">
                                                        <option value="">-- None (Standalone Staff) --</option>
                                                        @foreach ($users as $user)
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
                                            <button type="submit" class="btn btn-gold" style="padding: 0.6rem 1.5rem;">Update Employee Profile</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="10" style="text-align: center; color: var(--text-muted); padding: 2.5rem;">
                                    No employees found. Click "+ Register New Employee" to register clinic staff.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($employees->hasPages())
                <div style="padding: 1rem;">
                    {{ $employees->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal: Register New Employee -->
    <div class="modal-backdrop" id="modal-add-employee">
        <div class="modal-dialog modal-lg" style="max-width: 960px; width: 95%;">
            <div class="modal-header">
                <div class="modal-title-group">
                    <h4 class="modal-title">Register New Employee</h4>
                    <span style="font-size: 0.75rem; color: var(--gold-light);">Complete profile registration with statutory & credentials repository</span>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form action="{{ route('manager.payroll.employees.store') }}" method="POST" enctype="multipart/form-data">
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

                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                            <div class="form-group">
                                <label class="form-label">Birth Date</label>
                                <input type="date" name="birth_date" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Gender</label>
                                <select name="gender" class="form-control">
                                    <option value="">-- Select --</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Civil Status</label>
                                <select name="civil_status" class="form-control">
                                    <option value="">-- Select --</option>
                                    <option value="Single" selected>Single</option>
                                    <option value="Married">Married</option>
                                    <option value="Widowed">Widowed</option>
                                    <option value="Separated">Separated</option>
                                </select>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                            <div class="form-group">
                                <label class="form-label">Position / Role *</label>
                                <select name="position" class="form-control" required id="add_position">
                                    @foreach (\App\Models\Employee::POSITIONS as $posOption)
                                        <option value="{{ $posOption }}" {{ $posOption === 'Receptionist' ? 'selected' : '' }}>
                                            {{ $posOption }}
                                        </option>
                                    @endforeach
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

                        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                            <div class="form-group">
                                <label class="form-label">Residential Address</label>
                                <input type="text" name="address" class="form-control" placeholder="e.g. 123 San Juan St, Poblacion">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Educational Attainment</label>
                                <input type="text" name="education" class="form-control" placeholder="e.g. BS Veterinary Medicine / College">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                            <div class="form-group">
                                <label class="form-label">Emergency Contact Person</label>
                                <input type="text" name="emergency_contact_name" class="form-control" placeholder="e.g. Maria Bautista (Spouse)">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Emergency Contact Phone</label>
                                <input type="text" name="emergency_contact_phone" class="form-control" placeholder="0917-000-0000">
                            </div>
                        </div>

                        <!-- Shift Assignment -->
                        <div style="background: rgba(11, 25, 44, 0.4); border: 1px solid var(--navy-border); border-radius: 6px; padding: 0.75rem 1rem; margin-top: 0.75rem;">
                            <div style="font-size: 0.78rem; font-weight: 700; color: var(--gold-light); margin-bottom: 0.5rem;">
                                ⏰ Assigned Work Shift Schedule
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="form-group">
                                    <label class="form-label">Shift Start (Time In) *</label>
                                    <select name="shift_start" class="form-control">
                                        <option value="08:00">08:00 AM</option>
                                        <option value="08:30">08:30 AM</option>
                                        <option value="09:00" selected>09:00 AM (Clinic Opening)</option>
                                        <option value="09:30">09:30 AM</option>
                                        <option value="10:00">10:00 AM (Late Shift)</option>
                                        <option value="11:00">11:00 AM</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Shift End (Time Out) *</label>
                                    <select name="shift_end" class="form-control">
                                        <option value="17:00">05:00 PM</option>
                                        <option value="17:30">05:30 PM</option>
                                        <option value="18:00" selected>06:00 PM (Standard Close)</option>
                                        <option value="18:30">06:30 PM</option>
                                        <option value="19:00">07:00 PM (Night Close)</option>
                                        <option value="20:00">08:00 PM</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Previous Employment History -->
                    <div style="margin-bottom: 1.25rem; border-top: 1px solid var(--navy-border); padding-top: 1rem;">
                        <h5 style="font-size: 0.85rem; font-weight: 700; color: var(--gold-light); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 6px;">
                            <span>💼 Previous Employment History</span>
                        </h5>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label class="form-label">Previous Company / Employer</label>
                                <input type="text" name="previous_employer" class="form-control" placeholder="e.g. Pet Care Veterinary Clinic">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Previous Position / Role</label>
                                <input type="text" name="previous_position" class="form-control" placeholder="e.g. Veterinary Assistant">
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                            <div class="form-group">
                                <label class="form-label">Previous Monthly Salary (₱)</label>
                                <input type="number" step="0.01" name="previous_salary" class="form-control" placeholder="e.g. 16000">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Years of Experience / Duration</label>
                                <input type="text" name="years_of_experience" class="form-control" placeholder="e.g. 2 Years, 4 Months">
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Compensation & Employment Classification -->
                    <div style="margin-bottom: 1.25rem; border-top: 1px solid var(--navy-border); padding-top: 1rem;">
                        <h5 style="font-size: 0.85rem; font-weight: 700; color: var(--gold-light); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 6px;">
                            <span>💵 Compensation & Employment Classification</span>
                        </h5>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label class="form-label">Employment Classification *</label>
                                <select name="employment_type" class="form-control" required id="add_emp_type">
                                    <option value="regular">⭐ Regular Employee (With Gov Benefits)</option>
                                    <option value="casual">🔹 Casual Employee (With Gov Benefits)</option>
                                    <option value="probationary" selected>⏳ Probationary (No Gov Benefits)</option>
                                    <option value="contractual">📄 Contractual (No Gov Benefits)</option>
                                    <option value="part_time">⏱️ Part Time (No Gov Benefits)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Monthly Basic Salary (₱) *</label>
                                <input type="number" step="0.01" name="basic_salary" id="add_basic_salary" class="form-control" placeholder="e.g. 18000" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Status *</label>
                                <select name="status" class="form-control" required>
                                    <option value="active" selected>Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="on_leave">On Leave</option>
                                </select>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                            <div class="form-group">
                                <label class="form-label">Divisor (Working Days/Mo)</label>
                                <select name="divisor_days" class="form-control">
                                    <option value="26" selected>26 Days (1 Restday)</option>
                                    <option value="22">22 Days (2 Restdays - Vet)</option>
                                    <option value="21">21.75 Days</option>
                                    <option value="30">30 Days</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Daily Rate (₱)</label>
                                <input type="number" step="0.01" name="daily_rate" class="form-control" placeholder="Auto: Basic / Divisor">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Hourly Rate (₱)</label>
                                <input type="number" step="0.01" name="hourly_rate" class="form-control" placeholder="Auto: Daily / 8">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Date Hired</label>
                                <input type="date" name="date_hired" class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- Section 4: Statutory Government Identifications -->
                    <div style="margin-bottom: 1.25rem; border-top: 1px solid var(--navy-border); padding-top: 1rem;">
                        <h5 style="font-size: 0.85rem; font-weight: 700; color: var(--gold-light); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 6px;">
                            <span>🏛️ Statutory Government Identifications</span>
                        </h5>
                        <div style="font-size: 0.76rem; color: #cbd5e1; background: rgba(0,0,0,0.25); border-left: 3px solid #38bdf8; padding: 0.5rem 0.75rem; border-radius: 4px; margin-bottom: 0.75rem;">
                            📌 <strong>Policy Rule:</strong> SSS, PhilHealth, and Pag-IBIG contributions will only be computed & displayed in payroll for <strong>Casual</strong> or <strong>Regular</strong> employees.
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr 1fr; gap: 0.75rem;">
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
                            <div class="form-group">
                                <label class="form-label">Driver's License #</label>
                                <input type="text" name="drivers_license_no" class="form-control" placeholder="N01-00-000000">
                            </div>
                        </div>
                    </div>

                    <!-- Section 5: Document Uploads -->
                    <div style="margin-bottom: 1.25rem; border-top: 1px solid var(--navy-border); padding-top: 1rem;">
                        <h5 style="font-size: 0.85rem; font-weight: 700; color: var(--gold-light); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 6px;">
                            <span>📎 Document & ID Attachments (PDF / Image)</span>
                        </h5>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label class="form-label">🚓 Police Clearance / NBI</label>
                                <input type="file" name="police_clearance_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                            <div class="form-group">
                                <label class="form-label">🩺 Physical Exam / Med Cert</label>
                                <input type="file" name="medical_certificate_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                            <div class="form-group">
                                <label class="form-label">🪪 SSS ID / E1 Form</label>
                                <input type="file" name="sss_id_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                            <div class="form-group">
                                <label class="form-label">🪪 PhilHealth ID / MDR</label>
                                <input type="file" name="philhealth_id_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                            <div class="form-group">
                                <label class="form-label">🪪 Pag-IBIG ID / MDF Form</label>
                                <input type="file" name="pagibig_id_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                            <div class="form-group">
                                <label class="form-label">🚗 Driver's License</label>
                                <input type="file" name="drivers_license_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                        </div>
                        <div class="form-group" style="margin-top: 0.75rem;">
                            <label class="form-label">📄 Other Supporting Documents / Resume / Contract</label>
                            <input type="file" name="other_doc_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                    </div>

                    <!-- Section 6: Link System User Account -->
                    <div style="border-top: 1px solid var(--navy-border); padding-top: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Link System User Account (Optional)</label>
                            <select name="user_id" class="form-control">
                                <option value="">-- None (Standalone Staff) --</option>
                                @foreach ($users as $user)
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
