@extends('layouts.app')

@php
    $title = 'Billing Data Base';
    $headerTitle = 'Central Billing Database';
    $breadcrumb = 'Billing & Transactions';
@endphp

@section('content')
    <!-- Top Summary Stat Cards -->
    <div class="stat-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Total Collected Revenue</span>
                <div class="stat-icon-wrapper">₱</div>
            </div>
            <div class="stat-value">₱{{ number_format($totalCollected, 2) }}</div>
            <div class="stat-desc">Completed & paid invoices</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Unpaid Queue</span>
                <div class="stat-icon-wrapper" style="color: var(--warning); border-color: rgba(245, 158, 11, 0.3);">⏳</div>
            </div>
            <div class="stat-value" style="color: var(--warning);">₱{{ number_format($totalPending, 2) }}</div>
            <div class="stat-desc">Invoices awaiting cashier payment</div>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="table-toolbar">
        <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <div class="search-input-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                <input type="text" class="form-control" placeholder="Search invoice no, client name..." data-table-search="billing-table">
            </div>

            <!-- Status Filter -->
            <div style="display: flex; gap: 0.35rem;">
                <a href="{{ route('admin.billing.index') }}" class="btn btn-sm {{ !request('payment_status') ? 'btn-gold' : 'btn-navy' }}">All</a>
                <a href="{{ route('admin.billing.index', ['payment_status' => 'unpaid']) }}" class="btn btn-sm {{ request('payment_status') === 'unpaid' ? 'btn-gold' : 'btn-warning' }}" style="{{ request('payment_status') === 'unpaid' ? '' : 'background: var(--warning-bg); color: var(--warning);' }}">Unpaid Queue</a>
                <a href="{{ route('admin.billing.index', ['payment_status' => 'paid']) }}" class="btn btn-sm {{ request('payment_status') === 'paid' ? 'btn-gold' : 'btn-navy' }}">Paid Invoices</a>
            </div>
        </div>
    </div>

    <!-- Invoices Table Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-title-group">
                <h3 class="card-title">Billing Database</h3>
                <span class="card-subtitle">Aggregated bills from Veterinary, Grooming, and Pet Supplies</span>
            </div>
        </div>

        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table" id="billing-table">
                    <thead>
                        <tr>
                            <th>Invoice No</th>
                            <th>Date & Time</th>
                            <th>Client Name</th>
                            <th>Branch / Service</th>
                            <th>Line Items Purchased</th>
                            <th>Total Amount</th>
                            <th>Payment Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bills as $bill)
                            <tr>
                                <td>
                                    <strong style="color: var(--gold-primary);">{{ $bill->invoice_no }}</strong>
                                </td>
                                <td>
                                    <div style="font-size: 0.85rem; color: var(--white);">{{ $bill->transaction_date->format('M d, Y') }}</div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $bill->transaction_date->format('h:i A') }}</div>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--white);">{{ $bill->client_name }}</div>
                                    @if($bill->pet)
                                        <div style="font-size: 0.75rem; color: var(--gold-light);">Pet: {{ $bill->pet->name }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-navy" style="background: var(--navy-dark); border: 1px solid var(--navy-border); color: var(--gold-light);">
                                        {{ ucfirst(str_replace('_', ' ', $bill->service_type)) }}
                                    </span>
                                </td>
                                <td style="max-width: 250px;">
                                    <div style="font-size: 0.8rem; color: var(--text-secondary);">
                                        @foreach($bill->items as $item)
                                            <div>• {{ $item->item_name }} (x{{ $item->quantity }})</div>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    <strong style="color: var(--gold-primary); font-size: 1.05rem;">₱{{ number_format($bill->total_amount, 2) }}</strong>
                                    @if($bill->payment_status === 'paid')
                                        <div style="font-size: 0.72rem; color: var(--text-muted);">Via {{ strtoupper($bill->payment_method) }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $bill->payment_status === 'paid' ? 'badge-success' : 'badge-warning' }}">
                                        {{ ucfirst($bill->payment_status) }}
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 0.4rem;">
                                        <!-- If unpaid, show Checkout Trigger Modal -->
                                        @if($bill->payment_status === 'unpaid')
                                            <button type="button" class="btn btn-gold btn-sm"
                                                data-modal-target="modal-process-payment"
                                                data-action-url="{{ route('admin.billing.pay', $bill->id) }}"
                                                data-field-invoice_no="{{ $bill->invoice_no }}"
                                                data-field-client_name="{{ $bill->client_name }}"
                                                data-field-total_amount="{{ number_format($bill->total_amount, 2) }}"
                                                data-field-raw_amount="{{ $bill->total_amount }}">
                                                💵 Collect Payment
                                            </button>
                                        @endif

                                        <!-- View / Print Receipt -->
                                        <a href="{{ route('admin.billing.show', $bill->id) }}" class="btn btn-navy btn-sm" target="_blank">
                                            📄 Receipt
                                        </a>

                                        <!-- Delete Bill Trigger -->
                                        <button type="button" class="btn btn-danger btn-sm"
                                            data-modal-target="modal-delete-bill"
                                            data-action-url="{{ route('admin.billing.destroy', $bill->id) }}"
                                            data-field-target_name="{{ $bill->invoice_no }}">
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

    <!-- ==================== MODAL 1: PROCESS BILLING PAYMENT ==================== -->
    <div class="modal-backdrop" id="modal-process-payment">
        <div class="modal-dialog modal-sm">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">💵</div>
                    <div>
                        <h4 class="modal-title">Collect Payment</h4>
                        <span style="font-size: 0.75rem; color: var(--gold-light);">Invoice: <strong data-bind="invoice_no"></strong></span>
                    </div>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form method="POST" action="">
                @csrf
                <div class="modal-body">
                    <div style="background: var(--navy-dark); padding: 0.85rem; border-radius: var(--radius-sm); border: 1px solid var(--black-border); margin-bottom: 1.25rem;">
                        <div style="font-size: 0.78rem; color: var(--text-muted);">Client: <strong style="color: var(--white);" data-bind="client_name"></strong></div>
                        <div style="font-size: 1.35rem; font-weight: 800; color: var(--gold-primary); margin-top: 0.35rem;">
                            ₱<span data-bind="total_amount"></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Payment Method <span class="req">*</span></label>
                        <select name="payment_method" class="form-select" required>
                            <option value="cash">Cash</option>
                            <option value="gcash">GCash</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="debit_card">Debit Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Amount Tendered (₱) <span class="req">*</span></label>
                        <input type="number" step="0.01" name="paid_amount" class="form-control" placeholder="0.00" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Cashier Notes (Optional)</label>
                        <input type="text" name="notes" class="form-control" placeholder="e.g. GCash Ref # / Cash exact">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold">Confirm Payment</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL 2: DELETE INVOICE CONFIRMATION ==================== -->
    <div class="modal-backdrop" id="modal-delete-bill">
        <div class="modal-dialog modal-danger modal-sm">
            <div class="modal-header">
                <div class="modal-title-group">
                    <div class="modal-icon">🗑️</div>
                    <h4 class="modal-title">Delete Invoice?</h4>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p style="color: var(--text-secondary); font-size: 0.9rem;">
                        Are you sure you want to permanently delete invoice <strong style="color: var(--white);" data-bind="target_name"></strong>?
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
