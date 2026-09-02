@extends('layouts.app')

@php
    $title = 'Inventory Audit';
    $headerTitle = 'Inventory Valuation & Audit';
    $breadcrumb = 'Inventory Valuation';
@endphp

@section('content')
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Total Retail Valuation</span>
                <div class="stat-icon-wrapper">₱</div>
            </div>
            <div class="stat-value">₱{{ number_format($inventoryRetailValue, 2) }}</div>
            <div class="stat-desc">Projected sales value</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Total Cost Basis</span>
                <div class="stat-icon-wrapper" style="color: var(--text-secondary);">🧾</div>
            </div>
            <div class="stat-value">₱{{ number_format($inventoryCost, 2) }}</div>
            <div class="stat-desc">Stock capital investment</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Projected Gross Margin</span>
                <div class="stat-icon-wrapper" style="color: var(--success);">📈</div>
            </div>
            <div class="stat-value" style="color: var(--success);">₱{{ number_format($potentialProfit, 2) }}</div>
            <div class="stat-desc">Estimated retail markup</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Units & Low Stock</span>
                <div class="stat-icon-wrapper" style="color: var(--warning);">⚠️</div>
            </div>
            <div class="stat-value">{{ $totalStockUnits }} <span
                    style="font-size: 0.85rem; color: var(--text-muted);">Units</span></div>
            <div class="stat-desc">{{ $lowStockItems->count() }} items under reorder threshold</div>
        </div>
    </div>

    <!-- Low Stock Priority Alert Box if any -->
    @if ($lowStockItems->isNotEmpty())
        <div
            style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: var(--radius-md); padding: 1.25rem; margin-bottom: 1.5rem;">
            <h4
                style="color: var(--warning); font-size: 0.95rem; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <span>⚠️ Stock Reorder Priority Notice</span>
            </h4>
            <div style="display: flex; gap: 0.6rem; flex-wrap: wrap;">
                @foreach ($lowStockItems as $lItem)
                    <span class="badge badge-warning" style="padding: 0.35rem 0.65rem;">
                        {{ $lItem->name }}: {{ $lItem->stock_quantity }} {{ $lItem->unit }} remaining (Threshold:
                        {{ $lItem->reorder_level }})
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Full Valuation Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-title-group">
                <h3 class="card-title">Inventory Valuation Breakdown</h3>
                <span class="card-subtitle">Item-by-item stock quantity, unit cost, selling price, and total asset
                    value</span>
            </div>
        </div>

        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Item Code</th>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>On-Hand Stock</th>
                            <th>Cost Price</th>
                            <th>Retail Price</th>
                            <th>Total Asset Value</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            @php
                                $itemValue = $item->stock_quantity * $item->unit_price;
                            @endphp
                            <tr>
                                <td><strong style="color: var(--gold-primary);">{{ $item->item_code }}</strong></td>
                                <td>
                                    <div style="font-weight: 700; color: var(--white);">{{ $item->name }}</div>
                                </td>
                                <td>
                                    <span class="badge badge-navy"
                                        style="background: var(--navy-dark); border: 1px solid var(--navy-border); color: var(--gold-light);">
                                        {{ ucfirst(str_replace('_', ' ', $item->category)) }}
                                    </span>
                                </td>
                                <td>
                                    <strong style="color: {{ $item->isLowStock() ? 'var(--danger)' : 'var(--white)' }};">
                                        {{ $item->stock_quantity }} {{ $item->unit }}
                                    </strong>
                                </td>
                                <td>₱{{ number_format($item->cost_price, 2) }}</td>
                                <td><strong>₱{{ number_format($item->unit_price, 2) }}</strong></td>
                                <td>
                                    <strong
                                        style="color: var(--gold-primary);">₱{{ number_format($itemValue, 2) }}</strong>
                                </td>
                                <td>
                                    @if ($item->isLowStock())
                                        <span class="badge badge-danger">Reorder Needed</span>
                                    @else
                                        <span class="badge badge-success">Sufficient</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
