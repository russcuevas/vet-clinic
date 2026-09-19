@extends('layouts.app')

@php
    $title = 'Sales Summary Report';
    $headerTitle = 'Cashier Sales Summary & Itemized Report';
    $breadcrumb = 'Cashier / Sales Summary';
@endphp

@section('content')
    <!-- Top Summary Stat Cards -->
    <div class="stat-grid" style="margin-bottom: 1.5rem;">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Total Gross Subtotal</span>
                <div class="stat-icon-wrapper" style="background: rgba(59, 130, 246, 0.15); color: #3b82f6;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <div class="stat-value">₱{{ number_format($grandSubtotal, 2) }}</div>
            <div class="stat-desc">Before discounts</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Discounts Given</span>
                <div class="stat-icon-wrapper" style="background: rgba(239, 68, 68, 0.15); color: #ef4444;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="stat-value" style="color: #f87171;">₱{{ number_format($grandDiscount, 2) }}</div>
            <div class="stat-desc">Promos & customer discounts</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Net Sales Revenue</span>
                <div class="stat-icon-wrapper" style="background: rgba(245, 186, 49, 0.15); color: var(--gold-primary);">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="stat-value" style="color: var(--gold-light);">₱{{ number_format($grandTotal, 2) }}</div>
            <div class="stat-desc">Total collected for {{ $filterLabel }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Cash on Hand (Physical)</span>
                <div class="stat-icon-wrapper" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
            <div class="stat-value" style="color: #34d399;">₱{{ number_format($grandCash, 2) }}</div>
            <div class="stat-desc">Digital e-Wallets: ₱{{ number_format($grandGCash + $grandMaya + $grandCreditCard + $grandBankTransfer, 2) }}</div>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body" style="padding: 1.25rem;">
            <form action="{{ route('cashier.sales.summary') }}" method="GET" style="display: flex; flex-direction: column; gap: 1rem;">
                
                <!-- Quick Date Presets -->
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                    <div style="display: flex; gap: 0.35rem; flex-wrap: wrap; align-items: center;">
                        <span style="font-size: 0.8rem; font-weight: 700; color: var(--gold-light); margin-right: 0.5rem;">Quick Presets:</span>
                        <a href="{{ route('cashier.sales.summary', ['preset' => 'today']) }}" class="btn btn-sm {{ $preset === 'today' ? 'btn-gold' : 'btn-ghost' }}">📅 Today</a>
                        <a href="{{ route('cashier.sales.summary', ['preset' => 'yesterday']) }}" class="btn btn-sm {{ $preset === 'yesterday' ? 'btn-gold' : 'btn-ghost' }}">Yesterday</a>
                        <a href="{{ route('cashier.sales.summary', ['preset' => 'this_week']) }}" class="btn btn-sm {{ $preset === 'this_week' ? 'btn-gold' : 'btn-ghost' }}">This Week</a>
                        <a href="{{ route('cashier.sales.summary', ['preset' => 'this_month']) }}" class="btn btn-sm {{ $preset === 'this_month' ? 'btn-gold' : 'btn-ghost' }}">This Month</a>
                        <a href="{{ route('cashier.sales.summary', ['preset' => 'all']) }}" class="btn btn-sm {{ $preset === 'all' ? 'btn-gold' : 'btn-ghost' }}">All Time</a>
                    </div>

                    <div>
                        <button type="button" onclick="window.print()" class="btn btn-navy btn-sm" style="font-weight: 700;">
                            🖨️ Print Sales Summary
                        </button>
                    </div>
                </div>

                <!-- Custom Date Range & Filters Row -->
                <div style="display: grid; grid-template-columns: 1.2fr 1fr 1fr 1fr 1fr auto; gap: 0.75rem; align-items: flex-end;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-size: 0.78rem;">Search Invoice / Client / Item</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." value="{{ $search ?? '' }}">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-size: 0.78rem;">From Date</label>
                        <input type="date" name="from_date" class="form-control form-control-sm" value="{{ $fromDate ?? '' }}">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-size: 0.78rem;">To Date</label>
                        <input type="date" name="to_date" class="form-control form-control-sm" value="{{ $toDate ?? '' }}">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-size: 0.78rem;">Payment Channel</label>
                        <select name="payment_method" class="form-control form-control-sm">
                            <option value="">-- All Channels --</option>
                            <option value="cash" {{ $paymentMethod === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="gcash" {{ $paymentMethod === 'gcash' ? 'selected' : '' }}>GCash</option>
                            <option value="maya" {{ $paymentMethod === 'maya' ? 'selected' : '' }}>Maya</option>
                            <option value="credit_card" {{ $paymentMethod === 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                            <option value="bank_transfer" {{ $paymentMethod === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-size: 0.78rem;">Service Category</label>
                        <select name="service_type" class="form-control form-control-sm">
                            <option value="">-- All Services --</option>
                            <option value="veterinary" {{ $serviceType === 'veterinary' ? 'selected' : '' }}>Veterinary</option>
                            <option value="grooming" {{ $serviceType === 'grooming' ? 'selected' : '' }}>Grooming</option>
                            <option value="boarding" {{ $serviceType === 'boarding' ? 'selected' : '' }}>Boarding</option>
                            <option value="pet_supplies" {{ $serviceType === 'pet_supplies' ? 'selected' : '' }}>Pet Supplies POS</option>
                        </select>
                    </div>

                    <div style="display: flex; gap: 0.35rem;">
                        <button type="submit" class="btn btn-gold btn-sm" style="font-weight: 700; height: 36px; padding: 0 1rem;">Filter</button>
                        @if($fromDate || $toDate || $paymentMethod || $serviceType || $search || $preset !== 'today')
                            <a href="{{ route('cashier.sales.summary') }}" class="btn btn-ghost btn-sm" style="height: 36px; display: inline-flex; align-items: center;">Clear</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Sales Summary Table Card -->
    <div class="card" id="printable-sales-summary">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="card-title-group">
                <h3 class="card-title">Itemized Sales Breakdown: {{ $filterLabel }}</h3>
                <span class="card-subtitle">Format: Invoice • Item • Description • Quantity • Total • Credit Card • GCash • Bank Transfer • Maya • Cash</span>
            </div>
            <div>
                <span class="badge badge-gold" style="font-size: 0.78rem; padding: 0.35rem 0.75rem;">
                    {{ count($salesRows) }} Line Items Recorded
                </span>
            </div>
        </div>

        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table" style="font-size: 0.82rem;">
                    <thead>
                        <tr>
                            <th style="width: 130px;">Invoice #</th>
                            <th>Item Name</th>
                            <th>Description</th>
                            <th style="text-align: center; width: 60px;">Qty</th>
                            <th style="text-align: right; width: 100px;">Total</th>
                            <th style="text-align: right; color: #60a5fa; width: 100px;">Credit Card</th>
                            <th style="text-align: right; color: #3b82f6; width: 95px;">GCash</th>
                            <th style="text-align: right; color: #a78bfa; width: 110px;">Bank Transfer</th>
                            <th style="text-align: right; color: #10b981; width: 95px;">Maya</th>
                            <th style="text-align: right; color: #fbbf24; width: 95px;">Cash</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($salesRows as $row)
                            <tr>
                                <td>
                                    <strong style="color: var(--gold-light);">{{ $row['invoice_no'] }}</strong>
                                    <div style="font-size: 0.7rem; color: var(--text-muted);">
                                        {{ $row['transaction_date']->format('M d, h:i A') }}
                                    </div>
                                    <div style="font-size: 0.68rem; color: #cbd5e1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 120px;">
                                        {{ $row['client_name'] }}
                                    </div>
                                </td>
                                <td>
                                    <strong style="color: var(--white);">{{ $row['item_name'] }}</strong>
                                    <div style="font-size: 0.7rem; color: var(--text-secondary);">
                                        @ ₱{{ number_format($row['unit_price'], 2) }} each
                                    </div>
                                </td>
                                <td>
                                    <span class="badge" style="background: rgba(255, 255, 255, 0.05); border: 1px solid var(--navy-border); color: #cbd5e1; font-size: 0.7rem;">
                                        {{ $row['description'] }}
                                    </span>
                                </td>
                                <td style="text-align: center; font-weight: 700; color: var(--white);">
                                    {{ $row['quantity'] }}
                                </td>
                                <td style="text-align: right; font-weight: 700; color: var(--gold-primary);">
                                    ₱{{ number_format($row['total_price'], 2) }}
                                </td>
                                <td style="text-align: right; color: #93c5fd;">
                                    {{ $row['credit_card'] > 0 ? '₱' . number_format($row['credit_card'], 2) : '-' }}
                                </td>
                                <td style="text-align: right; color: #60a5fa;">
                                    {{ $row['gcash'] > 0 ? '₱' . number_format($row['gcash'], 2) : '-' }}
                                </td>
                                <td style="text-align: right; color: #c4b5fd;">
                                    {{ $row['bank_transfer'] > 0 ? '₱' . number_format($row['bank_transfer'], 2) : '-' }}
                                </td>
                                <td style="text-align: right; color: #6ee7b7;">
                                    {{ $row['maya'] > 0 ? '₱' . number_format($row['maya'], 2) : '-' }}
                                </td>
                                <td style="text-align: right; font-weight: 600; color: #fde68a;">
                                    {{ $row['cash'] > 0 ? '₱' . number_format($row['cash'], 2) : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" style="text-align: center; color: var(--text-muted); padding: 3rem 1rem;">
                                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">📊</div>
                                    <strong style="color: var(--white);">No sales transactions found for this period.</strong>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($salesRows) > 0)
                        <tfoot>
                            <tr style="background: rgba(0, 0, 0, 0.35); font-weight: 700; border-top: 2px solid var(--gold-border);">
                                <td colspan="4" style="text-align: right; color: var(--gold-light); font-size: 0.88rem; text-transform: uppercase;">
                                    Channel Totals:
                                </td>
                                <td style="text-align: right; color: var(--gold-primary); font-size: 0.95rem;">
                                    ₱{{ number_format($grandSubtotal, 2) }}
                                </td>
                                <td style="text-align: right; color: #93c5fd;">
                                    ₱{{ number_format($grandCreditCard, 2) }}
                                </td>
                                <td style="text-align: right; color: #60a5fa;">
                                    ₱{{ number_format($grandGCash, 2) }}
                                </td>
                                <td style="text-align: right; color: #c4b5fd;">
                                    ₱{{ number_format($grandBankTransfer, 2) }}
                                </td>
                                <td style="text-align: right; color: #6ee7b7;">
                                    ₱{{ number_format($grandMaya, 2) }}
                                </td>
                                <td style="text-align: right; color: #fde68a;">
                                    ₱{{ number_format($grandCash, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            <!-- Grand Financial Summary Footer (Matching Client Requested Layout) -->
            <div style="padding: 1.75rem; background: linear-gradient(180deg, rgba(19, 22, 32, 0.95) 0%, rgba(10, 12, 18, 1) 100%); border-top: 1px solid var(--navy-border);">
                <div style="display: flex; justify-content: flex-end;">
                    <div style="width: 100%; max-width: 380px; background: rgba(0, 0, 0, 0.4); border: 1px solid var(--gold-border); border-radius: 10px; padding: 1.25rem;">
                        <h4 style="color: var(--gold-light); font-size: 0.95rem; font-weight: 700; margin: 0 0 1rem 0; border-bottom: 1px dashed rgba(212, 175, 55, 0.3); padding-bottom: 0.5rem; text-align: center;">
                            Financial Summary ({{ $filterLabel }})
                        </h4>

                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.88rem; color: #cbd5e1;">
                            <span>Subtotal:</span>
                            <strong style="color: var(--white);">₱{{ number_format($grandSubtotal, 2) }}</strong>
                        </div>

                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.88rem; color: #f87171;">
                            <span>Discount:</span>
                            <strong>- ₱{{ number_format($grandDiscount, 2) }}</strong>
                        </div>

                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem; font-size: 1.2rem; color: var(--gold-primary); font-weight: 800; border-top: 2px solid var(--gold-border); padding-top: 0.6rem;">
                            <span>Total Net Sales:</span>
                            <span>₱{{ number_format($grandTotal, 2) }}</span>
                        </div>

                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.4rem; font-size: 0.85rem; color: #34d399;">
                            <span>Cash Tendered:</span>
                            <span>₱{{ number_format($grandPaidTendered, 2) }}</span>
                        </div>

                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: #94a3b8;">
                            <span>Change Given:</span>
                            <span>₱{{ number_format($grandChange, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
@media print {
    body {
        background: #fff !important;
        color: #000 !important;
    }
    .app-sidebar, .app-header, .table-toolbar, .stat-grid, .card:not(#printable-sales-summary), .no-print {
        display: none !important;
    }
    #printable-sales-summary {
        border: none !important;
        background: #fff !important;
        color: #000 !important;
    }
    .data-table th, .data-table td {
        color: #000 !important;
        border-color: #ddd !important;
    }
}
</style>
@endpush
