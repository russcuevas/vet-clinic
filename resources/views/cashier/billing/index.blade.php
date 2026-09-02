@extends('layouts.app')

@php
    $title = 'Cashier Billing';
    $headerTitle = 'Cashier Checkout Desk';
    $breadcrumb = 'Checkout Queue';
@endphp

@section('content')
    <div class="table-toolbar">
        <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <div class="search-input-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                <input type="text" class="form-control" placeholder="Search invoice, client name..." data-table-search="cashier-billing-table">
            </div>

            <div style="display: flex; gap: 0.35rem;">
                <a href="{{ route('cashier.billing.index') }}" class="btn btn-sm {{ !request('payment_status') ? 'btn-gold' : 'btn-navy' }}">All</a>
                <a href="{{ route('cashier.billing.index', ['payment_status' => 'unpaid']) }}" class="btn btn-sm {{ request('payment_status') === 'unpaid' ? 'btn-gold' : 'btn-warning' }}">Unpaid ({{ $totalPending > 0 ? 'Queue' : '0' }})</a>
                <a href="{{ route('cashier.billing.index', ['payment_status' => 'paid']) }}" class="btn btn-sm {{ request('payment_status') === 'paid' ? 'btn-gold' : 'btn-navy' }}">Paid Invoices</a>
            </div>
        </div>

        <a href="{{ route('cashier.pos.index') }}" class="btn btn-navy">
            🛍️ Pet Supplies POS
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title-group">
                <h3 class="card-title">Invoices & Patient Accounts</h3>
                <span class="card-subtitle">Process payments and print receipts</span>
            </div>
        </div>

        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table" id="cashier-billing-table">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Time</th>
                            <th>Client / Owner</th>
                            <th>Service</th>
                            <th>Items Breakdown</th>
                            <th>Total Due</th>
                            <th>Status</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bills as $bill)
                            <tr>
                                <td><strong style="color: var(--gold-primary);">{{ $bill->invoice_no }}</strong></td>
                                <td>
                                    <div style="font-size: 0.85rem;">{{ $bill->transaction_date->format('M d, Y') }}</div>
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
                                <td>
                                    <div style="font-size: 0.8rem; color: var(--text-secondary);">
                                        @foreach($bill->items as $item)
                                            <div>• {{ $item->item_name }} (x{{ $item->quantity }})</div>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    <strong style="color: var(--gold-primary); font-size: 1.05rem;">₱{{ number_format($bill->total_amount, 2) }}</strong>
                                </td>
                                <td>
                                    <span class="badge {{ $bill->payment_status === 'paid' ? 'badge-success' : 'badge-warning' }}">
                                        {{ ucfirst($bill->payment_status) }}
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 0.4rem;">
                                        @if($bill->payment_status === 'unpaid')
                                            <button type="button" class="btn btn-gold btn-sm"
                                                data-modal-target="modal-checkout-bill"
                                                data-action-url="{{ route('cashier.billing.pay', $bill->id) }}"
                                                data-field-invoice_no="{{ $bill->invoice_no }}"
                                                data-field-client_name="{{ $bill->client_name }}"
                                                data-field-total_amount="{{ number_format($bill->total_amount, 2) }}">
                                                💵 Pay
                                            </button>
                                        @endif
                                        <a href="{{ route('cashier.billing.invoice', $bill->id) }}" class="btn btn-navy btn-sm" target="_blank">
                                            Receipt
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal for Checkout -->
    <div class="modal-backdrop" id="modal-checkout-bill">
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
                        <label class="form-label">Amount Tendered (₱) <span class="req">*</span></label>
                        <input type="number" step="0.01" name="paid_amount" class="form-control" placeholder="0.00" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Cashier Notes</label>
                        <input type="text" name="notes" class="form-control" placeholder="Optional reference notes">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold">Confirm Payment</button>
                </div>
            </form>
        </div>
    </div>
@endsection
