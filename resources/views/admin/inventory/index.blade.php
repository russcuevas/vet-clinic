@extends('layouts.app')

@php
    $title = 'Inventory Management';
    $headerTitle = 'Clinic Inventory & Supplies';
    $breadcrumb = 'Inventory Stock';
@endphp

@section('content')
    <!-- Action Bar & Filter Toolbar -->
    <div class="table-toolbar">
        <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <div class="search-input-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                <input type="text" class="form-control" placeholder="Search item name, code, sku..." data-table-search="inventory-table">
            </div>

            <!-- Category Pills -->
            <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                <a href="{{ route('admin.inventory.index') }}" class="btn btn-sm {{ !request('category') && !request('low_stock') ? 'btn-gold' : 'btn-navy' }}">All</a>
                <a href="{{ route('admin.inventory.index', ['category' => 'pet_supplies']) }}" class="btn btn-sm {{ request('category') === 'pet_supplies' ? 'btn-gold' : 'btn-navy' }}">Supplies</a>
                <a href="{{ route('admin.inventory.index', ['category' => 'medicine']) }}" class="btn btn-sm {{ request('category') === 'medicine' ? 'btn-gold' : 'btn-navy' }}">Medicines</a>
                <a href="{{ route('admin.inventory.index', ['category' => 'vaccine']) }}" class="btn btn-sm {{ request('category') === 'vaccine' ? 'btn-gold' : 'btn-navy' }}">Vaccines</a>
                <a href="{{ route('admin.inventory.index', ['category' => 'grooming_supply']) }}" class="btn btn-sm {{ request('category') === 'grooming_supply' ? 'btn-gold' : 'btn-navy' }}">Grooming</a>
                <a href="{{ route('admin.inventory.index', ['low_stock' => '1']) }}" class="btn btn-sm {{ request('low_stock') ? 'btn-gold' : 'btn-danger' }}" style="{{ request('low_stock') ? '' : 'background: rgba(239, 68, 68, 0.2); color: var(--danger);' }}">
                    ⚠️ Low Stock ({{ $lowStockCount }})
                </a>
            </div>
        </div>

        <button type="button" class="btn btn-gold" data-modal-target="modal-new-item">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            <span>Add Inventory Item</span>
        </button>
    </div>

    <!-- Inventory Table Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-title-group">
                <h3 class="card-title">Inventory Catalog</h3>
                <span class="card-subtitle">Items, medicines, vaccines, and grooming supplies</span>
            </div>
        </div>

        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table" id="inventory-table">
                    <thead>
                        <tr>
                            <th>Item Code</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Stock Quantity</th>
                            <th>Unit Price (Selling)</th>
                            <th>Cost Price</th>
                            <th>Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td>
                                    <strong style="color: var(--gold-primary);">{{ $item->item_code }}</strong>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--white);">{{ $item->name }}</div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ Str::limit($item->description, 50) }}</div>
                                </td>
                                <td>
                                    <span class="badge badge-navy" style="background: var(--navy-dark); border: 1px solid var(--navy-border); color: var(--gold-light);">
                                        {{ ucfirst(str_replace('_', ' ', $item->category)) }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight: 700; font-size: 0.95rem; color: {{ $item->isLowStock() ? 'var(--danger)' : 'var(--white)' }};">
                                        {{ $item->stock_quantity }} {{ $item->unit }}
                                    </div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">Alert below: {{ $item->reorder_level }}</div>
                                </td>
                                <td>
                                    <strong style="color: var(--gold-primary); font-size: 0.95rem;">₱{{ number_format($item->unit_price, 2) }}</strong>
                                </td>
                                <td>
                                    <span style="color: var(--text-secondary);">₱{{ number_format($item->cost_price, 2) }}</span>
                                </td>
                                <td>
                                    @if($item->isLowStock())
                                        <span class="badge badge-danger">Low Stock</span>
                                    @else
                                        <span class="badge badge-success">In Stock</span>
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 0.4rem;">
                                        <!-- Restock Modal Trigger -->
                                        <button type="button" class="btn btn-outline-gold btn-sm"
                                            data-modal-target="modal-restock-item"
                                            data-action-url="{{ route('admin.inventory.restock', $item->id) }}"
                                            data-field-item_name="{{ $item->name }}"
                                            data-field-current_stock="{{ $item->stock_quantity }} {{ $item->unit }}">
                                            + Restock
                                        </button>

                                        <!-- Edit Modal Trigger -->
                                        <button type="button" class="btn btn-navy btn-sm"
                                            data-modal-target="modal-edit-item"
                                            data-action-url="{{ route('admin.inventory.update', $item->id) }}"
                                            data-field-name="{{ $item->name }}"
                                            data-field-category="{{ $item->category }}"
                                            data-field-description="{{ $item->description }}"
                                            data-field-stock_quantity="{{ $item->stock_quantity }}"
                                            data-field-unit="{{ $item->unit }}"
                                            data-field-unit_price="{{ $item->unit_price }}"
                                            data-field-cost_price="{{ $item->cost_price }}"
                                            data-field-reorder_level="{{ $item->reorder_level }}">
                                            Edit
                                        </button>

                                        <!-- Delete Trigger -->
                                        <button type="button" class="btn btn-danger btn-sm"
                                            data-modal-target="modal-delete-item"
                                            data-action-url="{{ route('admin.inventory.destroy', $item->id) }}"
                                            data-field-target_name="{{ $item->name }} ({{ $item->item_code }})">
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

    <!-- ==================== MODAL 1: ADD NEW INVENTORY ITEM ==================== -->
    <div class="modal-backdrop" id="modal-new-item">
        <div class="modal-dialog modal-lg">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">📦</div>
                    <div>
                        <h4 class="modal-title">Add Inventory Item</h4>
                        <span style="font-size: 0.75rem; color: var(--gold-light);">Code will be generated automatically</span>
                    </div>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form action="{{ route('admin.inventory.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Item / Product Name <span class="req">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Royal Canin Mini Adult (1kg)" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Category <span class="req">*</span></label>
                            <select name="category" class="form-select" required>
                                <option value="pet_supplies">Pet Supplies</option>
                                <option value="medicine">Medicine</option>
                                <option value="vaccine">Vaccine</option>
                                <option value="grooming_supply">Grooming Supply</option>
                                <option value="accessories">Accessories</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Specifications, indications, dosage, ingredients..."></textarea>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Initial Quantity <span class="req">*</span></label>
                            <input type="number" name="stock_quantity" class="form-control" value="10" min="0" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Unit of Measure <span class="req">*</span></label>
                            <input type="text" name="unit" class="form-control" placeholder="pcs, bag, bottle, tablet, vial" value="pcs" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Low Stock Reorder Alert <span class="req">*</span></label>
                            <input type="number" name="reorder_level" class="form-control" value="5" min="1" required>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Selling Price (₱) <span class="req">*</span></label>
                            <input type="number" step="0.01" name="unit_price" class="form-control" placeholder="0.00" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Cost Price (₱)</label>
                            <input type="number" step="0.01" name="cost_price" class="form-control" placeholder="0.00" value="0.00">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Item Picture (Stored in public/)</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold">Save Inventory Item</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL 2: EDIT INVENTORY ITEM ==================== -->
    <div class="modal-backdrop" id="modal-edit-item">
        <div class="modal-dialog modal-lg">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">✏️</div>
                    <h4 class="modal-title">Edit Inventory Item</h4>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form method="POST" action="" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Item Name <span class="req">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Category <span class="req">*</span></label>
                            <select name="category" class="form-select" required>
                                <option value="pet_supplies">Pet Supplies</option>
                                <option value="medicine">Medicine</option>
                                <option value="vaccine">Vaccine</option>
                                <option value="grooming_supply">Grooming Supply</option>
                                <option value="accessories">Accessories</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Stock Quantity <span class="req">*</span></label>
                            <input type="number" name="stock_quantity" class="form-control" min="0" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Unit <span class="req">*</span></label>
                            <input type="text" name="unit" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Reorder Level <span class="req">*</span></label>
                            <input type="number" name="reorder_level" class="form-control" min="1" required>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Selling Price (₱) <span class="req">*</span></label>
                            <input type="number" step="0.01" name="unit_price" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Cost Price (₱)</label>
                            <input type="number" step="0.01" name="cost_price" class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Replace Picture (saved in public/)</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold">Update Item Details</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL 3: RESTOCK ITEM ==================== -->
    <div class="modal-backdrop" id="modal-restock-item">
        <div class="modal-dialog modal-sm">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">➕</div>
                    <h4 class="modal-title">Restock Inventory</h4>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form method="POST" action="">
                @csrf
                <div class="modal-body">
                    <p style="font-size: 0.88rem; color: var(--text-secondary); margin-bottom: 1rem;">
                        Restocking: <strong style="color: var(--white);" data-bind="item_name"></strong>
                        <br><span style="font-size: 0.78rem; color: var(--gold-light);">Current Stock: <span data-bind="current_stock"></span></span>
                    </p>
                    <div class="form-group">
                        <label class="form-label">Units to Add <span class="req">*</span></label>
                        <input type="number" name="added_quantity" class="form-control" min="1" value="10" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold">Confirm Restock</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL 4: DELETE ITEM CONFIRMATION ==================== -->
    <div class="modal-backdrop" id="modal-delete-item">
        <div class="modal-dialog modal-danger modal-sm">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">🗑️</div>
                    <h4 class="modal-title">Delete Inventory Item?</h4>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p style="color: var(--text-secondary); font-size: 0.9rem;">
                        Are you sure you want to permanently delete <strong style="color: var(--white);" data-bind="target_name"></strong>?
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
