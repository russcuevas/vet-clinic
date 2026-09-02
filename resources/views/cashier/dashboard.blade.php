@extends('layouts.app')

@php
    $title = 'Cashier Portal';
    $headerTitle = 'Cashier Station & Frontdesk';
    $breadcrumb = 'Cashier Station';
@endphp

@section('content')
    <div class="stat-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Today's Total Collection</span>
                <div class="stat-icon-wrapper">₱</div>
            </div>
            <div class="stat-value">₱{{ number_format($todayCollected, 2) }}</div>
            <div class="stat-desc">Cash & Digital payments today</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Awaiting Payment</span>
                <div class="stat-icon-wrapper" style="color: var(--warning); border-color: rgba(245, 158, 11, 0.3);">⏳</div>
            </div>
            <div class="stat-value" style="color: var(--warning);">{{ $pendingBillsCount }} Bills</div>
            <div class="stat-desc">Veterinary, Grooming & Supplies waiting</div>
        </div>
    </div>

    <!-- Quick Buttons -->
    <div style="display: flex; gap: 0.75rem; margin-bottom: 1.75rem;">
        <a href="{{ route('cashier.billing.index') }}" class="btn btn-gold">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
            <span>Open Checkout Queue</span>
        </a>
        <a href="{{ route('cashier.pos.index') }}" class="btn btn-navy">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
            <span>Pet Supplies POS</span>
        </a>
    </div>

    <!-- Unpaid Queue Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-title-group">
                <h3 class="card-title">Pending Payment Queue</h3>
                <span class="card-subtitle">Patients and clients ready for checkout</span>
            </div>
            <a href="{{ route('cashier.billing.index') }}" class="btn btn-ghost btn-sm">Full Billing List →</a>
        </div>

        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Client Name</th>
                            <th>Branch / Service</th>
                            <th>Description</th>
                            <th>Amount Due</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($unpaidQueue as $bill)
                            <tr>
                                <td><strong style="color: var(--gold-primary);">{{ $bill->invoice_no }}</strong></td>
                                <td>
                                    <div style="font-weight: 700; color: var(--white);">{{ $bill->client_name }}</div>
                                    @if($bill->pet)
                                        <div style="font-size: 0.75rem; color: var(--gold-light);">Pet: {{ $bill->pet->name }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-gold">{{ ucfirst(str_replace('_', ' ', $bill->service_type)) }}</span>
                                </td>
                                <td>
                                    @foreach($bill->items as $item)
                                        <div style="font-size: 0.8rem; color: var(--text-secondary);">• {{ $item->item_name }} (x{{ $item->quantity }})</div>
                                    @endforeach
                                </td>
                                <td>
                                    <strong style="color: var(--gold-primary); font-size: 1.1rem;">₱{{ number_format($bill->total_amount, 2) }}</strong>
                                </td>
                                <td style="text-align: right;">
                                    <button type="button" class="btn btn-gold btn-sm"
                                        data-modal-target="modal-cashier-pay"
                                        data-action-url="{{ route('cashier.billing.pay', $bill->id) }}"
                                        data-field-invoice_no="{{ $bill->invoice_no }}"
                                        data-field-client_name="{{ $bill->client_name }}"
                                        data-field-total_amount="{{ number_format($bill->total_amount, 2) }}">
                                        💵 Collect
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal for Payment Collection -->
    <div class="modal-backdrop" id="modal-cashier-pay">
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
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Amount Received (₱) <span class="req">*</span></label>
                        <input type="number" step="0.01" name="paid_amount" class="form-control" placeholder="0.00" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Notes (Optional)</label>
                        <input type="text" name="notes" class="form-control" placeholder="GCash ref # or receipt notes">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold">Complete Transaction</button>
                </div>
            </form>
        </div>
    </div>
@endsection
