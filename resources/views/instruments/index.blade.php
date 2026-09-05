@extends('layouts.app')

@php
    $roleName = ucfirst(str_replace('_', ' ', auth()->user()->role));
    $title = 'Instruments & Equipment Inventory';
    $headerTitle = 'Instruments & Equipment Inventory';
    $breadcrumb = 'Inventory / Instruments Catalog';

    $isRestockRoute = match(auth()->user()->role) {
        'inventory_officer' => 'inventory_officer.instruments.',
        'back_office' => 'back_office.instruments.',
        default => 'admin.instruments.',
    };
@endphp

@section('content')
    <!-- Top Statistics Cards -->
    <div class="stat-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 1.5rem;">
        <div class="stat-card">
            <span class="stat-title">Total Instruments</span>
            <div class="stat-value">{{ $stats['total_items'] }}</div>
            <div class="stat-desc">Distinct clinic tools & equipment</div>
        </div>
        <div class="stat-card">
            <span class="stat-title">Total Stock Units</span>
            <div class="stat-value" style="color: var(--gold-light);">{{ number_format($stats['total_quantity']) }}</div>
            <div class="stat-desc">Physical tools in storage</div>
        </div>
        <div class="stat-card">
            <span class="stat-title">Low Stock Alert</span>
            <div class="stat-value" style="color: #f59e0b;">{{ $stats['low_stock_count'] }}</div>
            <div class="stat-desc">Reorder threshold reached</div>
        </div>
        <div class="stat-card">
            <span class="stat-title">Out of Stock</span>
            <div class="stat-value" style="color: #ef4444;">{{ $stats['out_of_stock_count'] }}</div>
            <div class="stat-desc">Zero stock available</div>
        </div>
        <div class="stat-card">
            <span class="stat-title">Recent Restocks (7d)</span>
            <div class="stat-value" style="color: #10b981;">{{ $stats['recent_restocks_count'] }}</div>
            <div class="stat-desc">Logged replenishment acts</div>
        </div>
    </div>

    <!-- Action Bar & Filter Toolbar -->
    <div class="table-toolbar">
        <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <div class="search-input-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" class="form-control" placeholder="Search instrument name, code, location..." data-table-search="instruments-table">
            </div>

            <!-- Category Filter Pills -->
            <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                <a href="{{ route($isRestockRoute . 'index') }}" class="btn btn-sm {{ !request('category') && !request('low_stock') && !request('status') ? 'btn-gold' : 'btn-navy' }}">All</a>
                <a href="{{ route($isRestockRoute . 'index', ['category' => 'surgical']) }}" class="btn btn-sm {{ request('category') === 'surgical' ? 'btn-gold' : 'btn-navy' }}">Surgical</a>
                <a href="{{ route($isRestockRoute . 'index', ['category' => 'diagnostic']) }}" class="btn btn-sm {{ request('category') === 'diagnostic' ? 'btn-gold' : 'btn-navy' }}">Diagnostic</a>
                <a href="{{ route($isRestockRoute . 'index', ['category' => 'dental']) }}" class="btn btn-sm {{ request('category') === 'dental' ? 'btn-gold' : 'btn-navy' }}">Dental</a>
                <a href="{{ route($isRestockRoute . 'index', ['category' => 'general_equipment']) }}" class="btn btn-sm {{ request('category') === 'general_equipment' ? 'btn-gold' : 'btn-navy' }}">Equipment</a>
                <a href="{{ route($isRestockRoute . 'index', ['category' => 'laboratory']) }}" class="btn btn-sm {{ request('category') === 'laboratory' ? 'btn-gold' : 'btn-navy' }}">Laboratory</a>
                <a href="{{ route($isRestockRoute . 'index', ['low_stock' => '1']) }}" class="btn btn-sm {{ request('low_stock') ? 'btn-gold' : 'btn-danger' }}" style="{{ request('low_stock') ? '' : 'background: rgba(239, 68, 68, 0.2); color: var(--danger);' }}">
                    ⚠️ Low Stock ({{ $stats['low_stock_count'] }})
                </a>
            </div>
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            <a href="{{ route($isRestockRoute . 'history') }}" class="btn btn-navy" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Restock History & Audit</span>
            </a>

            <button type="button" class="btn btn-gold" data-modal-target="modal-new-instrument" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>+ Add New Instrument</span>
            </button>
        </div>
    </div>

    <!-- Main Instruments Table -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="card-title-group">
                <h3 class="card-title">Instruments & Clinic Equipment Catalog</h3>
                <span class="card-subtitle">Non-POS medical tools, surgical gear, laboratory devices, and diagnostics</span>
            </div>
            <div style="font-size: 0.8rem; color: var(--text-muted);">
                Logged in as: <strong style="color: var(--gold-light);">{{ auth()->user()->name }} ({{ $roleName }})</strong>
            </div>
        </div>

        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table" id="instruments-table">
                    <thead>
                        <tr>
                            <th>Item Code</th>
                            <th>Instrument / Tool Name</th>
                            <th>Category</th>
                            <th>Current Quantity</th>
                            <th>Storage Location</th>
                            <th>Stock Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($instruments as $item)
                            <tr>
                                <td>
                                    <strong style="color: var(--gold-primary); font-family: monospace; font-size: 0.9rem;">
                                        {{ $item->item_code }}
                                    </strong>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.65rem;">
                                        <div style="width: 38px; height: 38px; border-radius: var(--radius-sm); background: var(--navy-dark); border: 1px solid var(--black-border); display: flex; align-items: center; justify-content: center; font-size: 1.15rem;">
                                            @switch($item->category)
                                                @case('surgical') ✂️ @break
                                                @case('diagnostic') 🩺 @break
                                                @case('dental') 🦷 @break
                                                @case('laboratory') 🔬 @break
                                                @case('sterilization') 🧼 @break
                                                @case('consumable_tools') 🩹 @break
                                                @default ⚙️
                                            @endswitch
                                        </div>
                                        <div>
                                            <div style="font-weight: 700; color: var(--white); font-size: 0.95rem;">
                                                {{ $item->name }}
                                            </div>
                                            @if($item->description)
                                                <div style="font-size: 0.75rem; color: var(--text-muted);">
                                                    {{ Str::limit($item->description, 55) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-navy" style="background: var(--navy-dark); border: 1px solid var(--navy-border); color: var(--gold-light); font-size: 0.72rem;">
                                        {{ $categories[$item->category] ?? ucfirst(str_replace('_', ' ', $item->category)) }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight: 700; font-size: 1rem; color: {{ $item->stock_quantity <= 0 ? 'var(--danger)' : ($item->isLowStock() ? '#f59e0b' : 'var(--white)') }};">
                                        {{ number_format($item->stock_quantity) }} <span style="font-size: 0.8rem; font-weight: 500; color: var(--text-secondary);">{{ $item->unit }}</span>
                                    </div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">
                                        Reorder alert below: {{ $item->reorder_level }} {{ $item->unit }}
                                    </div>
                                </td>
                                <td>
                                    @if($item->storage_location)
                                        <span style="font-size: 0.82rem; color: var(--text-secondary); display: inline-flex; align-items: center; gap: 4px;">
                                            📍 {{ $item->storage_location }}
                                        </span>
                                    @else
                                        <span style="font-size: 0.78rem; color: var(--text-muted); font-style: italic;">Not specified</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->stock_quantity <= 0)
                                        <span class="badge badge-danger" style="font-size: 0.72rem;">🚫 Out of Stock</span>
                                    @elseif($item->isLowStock())
                                        <span class="badge" style="background: rgba(245, 158, 11, 0.2); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.4); font-size: 0.72rem;">
                                            ⚠️ Low Stock
                                        </span>
                                    @else
                                        <span class="badge badge-success" style="font-size: 0.72rem;">✅ In Stock</span>
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 0.4rem;">
                                        <!-- Quick Restock Button -->
                                        <button type="button" class="btn btn-outline-gold btn-sm"
                                            data-modal-target="modal-restock-instrument"
                                            data-action-url="{{ route($isRestockRoute . 'restock', $item->id) }}"
                                            data-field-item_name="{{ $item->name }}"
                                            data-field-item_code="{{ $item->item_code }}"
                                            data-field-current_stock="{{ $item->stock_quantity }} {{ $item->unit }}"
                                            title="Add stock / restock item">
                                            + Restock
                                        </button>

                                        <!-- Edit Button -->
                                        <button type="button" class="btn btn-navy btn-sm"
                                            data-modal-target="modal-edit-instrument"
                                            data-action-url="{{ route($isRestockRoute . 'update', $item->id) }}"
                                            data-field-name="{{ $item->name }}"
                                            data-field-category="{{ $item->category }}"
                                            data-field-unit="{{ $item->unit }}"
                                            data-field-reorder_level="{{ $item->reorder_level }}"
                                            data-field-storage_location="{{ $item->storage_location }}"
                                            data-field-description="{{ $item->description }}"
                                            title="Edit details">
                                            ✏️ Edit
                                        </button>

                                        @if(auth()->user()->role === 'admin')
                                            <!-- Delete Button (Admin Only) -->
                                            <button type="button" class="btn btn-danger btn-sm"
                                                data-modal-target="modal-delete-instrument"
                                                data-action-url="{{ route('admin.instruments.destroy', $item->id) }}"
                                                data-field-target_name="{{ $item->name }} ({{ $item->item_code }})"
                                                title="Delete instrument">
                                                🗑️
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
                                    <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🩺</div>
                                    <div style="font-weight: 600; color: var(--white); font-size: 1.05rem;">No instruments found</div>
                                    <p style="font-size: 0.85rem; margin-top: 0.25rem;">Start by adding clinic equipment or surgical instruments to the inventory.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Restock Activity Preview -->
    @if($recentLogs->count() > 0)
        <div class="card" style="margin-top: 1.5rem;">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div class="card-title-group">
                    <h3 class="card-title">Recent Stock Replenishment Activity</h3>
                    <span class="card-subtitle">Last recorded restock actions with user accountability</span>
                </div>
                <a href="{{ route($isRestockRoute . 'history') }}" class="btn btn-ghost btn-sm" style="font-size: 0.78rem;">
                    View Complete Audit Trail &rarr;
                </a>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Instrument</th>
                                <th>Restocked By (Account)</th>
                                <th>Quantity Added</th>
                                <th>Stock Change</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentLogs as $log)
                                <tr>
                                    <td>
                                        <div style="font-size: 0.82rem; color: var(--white);">{{ $log->created_at->format('M d, Y') }}</div>
                                        <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $log->created_at->format('h:i A') }} ({{ $log->created_at->diffForHumans() }})</div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--white);">{{ $log->instrument->name ?? 'Deleted Item' }}</div>
                                        <div style="font-size: 0.72rem; color: var(--gold-light);">{{ $log->instrument->item_code ?? '-' }}</div>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div style="width: 28px; height: 28px; border-radius: 50%; background: var(--navy-surface); border: 1px solid var(--gold-border); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; color: var(--gold-light);">
                                                {{ strtoupper(substr($log->restockedBy->name ?? 'U', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div style="font-weight: 600; color: var(--white); font-size: 0.85rem;">
                                                    {{ $log->restockedBy->name ?? 'System' }}
                                                </div>
                                                <div style="font-size: 0.68rem; color: var(--text-muted);">
                                                    {{ ucfirst(str_replace('_', ' ', $log->restockedBy->role ?? 'Staff')) }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-success" style="font-size: 0.82rem; font-weight: 700;">
                                            +{{ $log->quantity_added }} {{ $log->instrument->unit ?? 'pcs' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-family: monospace; font-size: 0.85rem; color: var(--text-secondary);">
                                            {{ $log->quantity_before }} &rarr; <strong style="color: var(--white);">{{ $log->quantity_after }}</strong>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-size: 0.8rem; color: var(--text-secondary);">
                                            {{ $log->remarks ?: 'Stock replenishment' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- ==================== MODAL 1: ADD NEW INSTRUMENT ==================== -->
    <div class="modal-backdrop" id="modal-new-instrument">
        <div class="modal-dialog modal-lg" style="max-width: 720px; width: 95%;">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">🩺</div>
                    <h4 class="modal-title">Register New Clinic Instrument / Equipment</h4>
                    <span style="font-size: 0.72rem; color: var(--gold-light);">Separate non-POS inventory tracking</span>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form action="{{ route($isRestockRoute . 'store') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding: 1.5rem;">
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Instrument / Tool Name <span class="req">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Metzenbaum Dissecting Scissors (Curved)" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Category <span class="req">*</span></label>
                            <select name="category" class="form-control" required>
                                <option value="surgical">Surgical Instruments</option>
                                <option value="diagnostic">Diagnostic Tools</option>
                                <option value="dental">Dental Instruments</option>
                                <option value="general_equipment">General Equipment</option>
                                <option value="laboratory">Laboratory Equipment</option>
                                <option value="sterilization">Sterilization & Hygiene</option>
                                <option value="consumable_tools">Consumable Clinic Tools</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 0.75rem;">
                        <label class="form-label">Description / Specifications</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="e.g. Stainless steel 14cm curved blunt/blunt tip for surgical procedures"></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Initial Stock Quantity <span class="req">*</span></label>
                            <input type="number" name="stock_quantity" class="form-control" min="0" value="1" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Unit of Measure <span class="req">*</span></label>
                            <input type="text" name="unit" class="form-control" placeholder="pcs, sets, units, kits" value="pcs" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Reorder Alert Level <span class="req">*</span></label>
                            <input type="number" name="reorder_level" class="form-control" min="1" value="3" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Storage Location / Cabinet</label>
                            <input type="text" name="storage_location" class="form-control" placeholder="e.g. OR Cabinet 2, Shelf B, Consultation Room 1">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Initial Stock Remarks / Batch Note</label>
                            <input type="text" name="remarks" class="form-control" placeholder="e.g. Initial clinic procurement batch">
                        </div>
                    </div>

                    <div style="background: rgba(212, 175, 55, 0.08); border: 1px solid rgba(212, 175, 55, 0.2); border-radius: var(--radius-sm); padding: 0.75rem 1rem; margin-top: 1rem; font-size: 0.78rem; color: var(--gold-light); display: flex; align-items: center; gap: 0.5rem;">
                        <span>👤 Restock & Registration will be recorded under: <strong>{{ auth()->user()->name }} ({{ $roleName }})</strong></span>
                    </div>
                </div>

                <div class="modal-footer" style="padding: 1rem 1.5rem;">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold">Save Instrument</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL 2: RESTOCK INSTRUMENT ==================== -->
    <div class="modal-backdrop" id="modal-restock-instrument">
        <div class="modal-dialog modal-sm" style="max-width: 480px; width: 95%;">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">➕</div>
                    <h4 class="modal-title">Restock Instrument Stock</h4>
                    <span style="font-size: 0.72rem; color: var(--gold-light);">Account audit trail will be recorded</span>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form method="POST" action="">
                @csrf
                <div class="modal-body" style="padding: 1.5rem;">
                    <div style="background: var(--navy-dark); border: 1px solid var(--black-border); border-radius: var(--radius-sm); padding: 0.85rem 1rem; margin-bottom: 1rem;">
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Restocking Item:</div>
                        <div style="font-weight: 700; color: var(--white); font-size: 1rem;" data-bind="item_name"></div>
                        <div style="display: flex; justify-content: space-between; margin-top: 0.35rem; font-size: 0.8rem;">
                            <span style="color: var(--gold-light);" data-bind="item_code"></span>
                            <span style="color: var(--text-secondary);">Current Stock: <strong style="color: var(--white);" data-bind="current_stock"></strong></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Units to Add <span class="req">*</span></label>
                        <input type="number" name="added_quantity" class="form-control" min="1" value="5" required autofocus>
                    </div>

                    <div class="form-group" style="margin-top: 0.75rem;">
                        <label class="form-label">Restock Remarks / Supplier Notes</label>
                        <input type="text" name="remarks" class="form-control" placeholder="e.g. Replenishment delivery, Supplier Order #104">
                    </div>

                    <div style="background: rgba(56, 189, 248, 0.08); border: 1px solid rgba(56, 189, 248, 0.2); border-radius: var(--radius-sm); padding: 0.65rem 0.85rem; margin-top: 1rem; font-size: 0.75rem; color: #38bdf8;">
                        📝 <strong>Restocked by:</strong> {{ auth()->user()->name }} ({{ $roleName }})
                    </div>
                </div>
                <div class="modal-footer" style="padding: 1rem 1.5rem;">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold">Confirm Restock</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL 3: EDIT INSTRUMENT ==================== -->
    <div class="modal-backdrop" id="modal-edit-instrument">
        <div class="modal-dialog modal-lg" style="max-width: 720px; width: 95%;">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">✏️</div>
                    <h4 class="modal-title">Edit Instrument Details</h4>
                    <span style="font-size: 0.72rem; color: var(--gold-light);">Update tool details, category, or storage location</span>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-body" style="padding: 1.5rem;">
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Instrument / Tool Name <span class="req">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Category <span class="req">*</span></label>
                            <select name="category" class="form-control" required>
                                <option value="surgical">Surgical Instruments</option>
                                <option value="diagnostic">Diagnostic Tools</option>
                                <option value="dental">Dental Instruments</option>
                                <option value="general_equipment">General Equipment</option>
                                <option value="laboratory">Laboratory Equipment</option>
                                <option value="sterilization">Sterilization & Hygiene</option>
                                <option value="consumable_tools">Consumable Clinic Tools</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 0.75rem;">
                        <label class="form-label">Description / Specifications</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Unit of Measure <span class="req">*</span></label>
                            <input type="text" name="unit" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Reorder Alert Level <span class="req">*</span></label>
                            <input type="number" name="reorder_level" class="form-control" min="1" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Storage Location</label>
                            <input type="text" name="storage_location" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 1rem 1.5rem;">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold">Update Instrument</button>
                </div>
            </form>
        </div>
    </div>

    @if(auth()->user()->role === 'admin')
        <!-- ==================== MODAL 4: DELETE CONFIRMATION (ADMIN ONLY) ==================== -->
        <div class="modal-backdrop" id="modal-delete-instrument">
            <div class="modal-dialog modal-danger modal-sm" style="max-width: 480px; width: 95%;">
                <div class="modal-header">
                    <div class="modal-title-group">
                        <div class="modal-icon">🗑️</div>
                        <h4 class="modal-title">Delete Instrument?</h4>
                    </div>
                    <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
                </div>
                <form method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body" style="padding: 1.5rem;">
                        <p style="color: var(--text-secondary); font-size: 0.9rem;">
                            Are you sure you want to delete <strong style="color: var(--white);" data-bind="target_name"></strong> from the instruments database?
                        </p>
                        <p style="color: var(--danger); font-size: 0.78rem; margin-top: 0.5rem;">
                            ⚠️ This will also remove its associated stock restock history records.
                        </p>
                    </div>
                    <div class="modal-footer" style="padding: 1rem 1.5rem;">
                        <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                        <button type="submit" class="btn btn-danger">Confirm Delete</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection
