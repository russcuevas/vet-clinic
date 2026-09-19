@extends('layouts.app')

@php
    $title = 'Cashier Billing';
    $headerTitle = 'Cashier Checkout Desk';
    $breadcrumb = 'Checkout Queue';
@endphp

@section('content')
    <div class="table-toolbar" style="margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <div class="search-input-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                <input type="text" class="form-control" placeholder="Search invoice, client name..." data-table-search="cashier-billing-table">
            </div>

            <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                <a href="{{ route('cashier.billing.index') }}" class="btn btn-sm {{ !request('payment_status') && !request('service_type') ? 'btn-gold' : 'btn-navy' }}">All</a>
                <a href="{{ route('cashier.billing.index', ['payment_status' => 'unpaid']) }}" class="btn btn-sm {{ request('payment_status') === 'unpaid' ? 'btn-gold' : 'btn-warning' }}">Unpaid Queue ({{ $totalPending > 0 ? '₱' . number_format($totalPending, 2) : '0' }})</a>
                <a href="{{ route('cashier.billing.index', ['payment_status' => 'paid']) }}" class="btn btn-sm {{ request('payment_status') === 'paid' ? 'btn-gold' : 'btn-navy' }}">Paid Invoices</a>
                <a href="{{ route('cashier.billing.index', ['service_type' => 'boarding']) }}" class="btn btn-sm {{ request('service_type') === 'boarding' ? 'btn-gold' : 'btn-navy' }}">🏨 Boarding</a>
                <a href="{{ route('cashier.billing.index', ['service_type' => 'grooming']) }}" class="btn btn-sm {{ request('service_type') === 'grooming' ? 'btn-gold' : 'btn-navy' }}">✂️ Grooming</a>
            </div>
        </div>

        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <a href="{{ route('cashier.sales.summary') }}" class="btn btn-navy">
                📊 Sales Summary
            </a>
            <button type="button" class="btn btn-gold" data-modal-target="modal-create-bill">
                + Create Bill / Checkout
            </button>
        </div>
    </div>

    <!-- Billing Records Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-title-group">
                <h3 class="card-title">Invoices & Patient Accounts</h3>
                <span class="card-subtitle">Itemized billing, payments, discounts, and receipts</span>
            </div>
        </div>

        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table" id="cashier-billing-table">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Date & Time</th>
                            <th>Client / Owner</th>
                            <th>Service Category</th>
                            <th>Itemized Items Breakdown</th>
                            <th>Total Due</th>
                            <th>Status</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bills as $bill)
                            <tr>
                                <td>
                                    <strong style="color: var(--gold-primary);">{{ $bill->invoice_no }}</strong>
                                    @if($bill->payment_method && $bill->payment_status === 'paid')
                                        <div style="font-size: 0.68rem; color: #60a5fa; text-transform: uppercase; margin-top: 2px;">
                                            💳 {{ strtoupper(str_replace('_', ' ', $bill->payment_method)) }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-size: 0.85rem; font-weight: 600; color: var(--white);">{{ $bill->transaction_date->format('M d, Y') }}</div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $bill->transaction_date->format('h:i A') }}</div>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--white);">{{ $bill->client_name }}</div>
                                    @if($bill->pet)
                                        <div style="font-size: 0.75rem; color: var(--gold-light);">🐾 Pet: {{ $bill->pet->name }}</div>
                                    @endif
                                    @if($bill->owner)
                                        <div style="font-size: 0.7rem; color: var(--text-muted);">{{ $bill->owner->contact_number }}</div>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $catColor = match($bill->service_type) {
                                            'grooming' => '#fbbf24',
                                            'boarding' => '#c4b5fd',
                                            'pet_supplies' => '#34d399',
                                            default => '#60a5fa',
                                        };
                                    @endphp
                                    <span class="badge" style="background: rgba(255, 255, 255, 0.05); border: 1px solid var(--navy-border); color: {{ $catColor }};">
                                        {{ ucfirst(str_replace('_', ' ', $bill->service_type)) }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-size: 0.8rem; color: var(--text-secondary); max-width: 280px;">
                                        @foreach($bill->items as $item)
                                            <div style="line-height: 1.35; margin-bottom: 2px;">
                                                • <strong style="color: #cbd5e1;">{{ $item->item_name }}</strong> 
                                                <span style="color: var(--text-muted); font-size: 0.75rem;">(x{{ $item->quantity }} @ ₱{{ number_format($item->unit_price, 2) }})</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    <strong style="color: var(--gold-primary); font-size: 1.05rem;">₱{{ number_format($bill->total_amount, 2) }}</strong>
                                    @if($bill->discount > 0)
                                        <div style="font-size: 0.7rem; color: #ef4444;">
                                            Disc: -₱{{ number_format($bill->discount, 2) }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $bill->payment_status === 'paid' ? 'badge-success' : 'badge-warning' }}">
                                        {{ ucfirst($bill->payment_status) }}
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 0.35rem; align-items: center;">
                                        @if($bill->payment_status === 'unpaid')
                                            <button type="button" class="btn btn-gold btn-sm btn-open-pay-modal"
                                                data-bill-id="{{ $bill->id }}"
                                                data-invoice-no="{{ $bill->invoice_no }}"
                                                data-client-name="{{ $bill->client_name }}"
                                                data-subtotal="{{ $bill->subtotal ?: $bill->total_amount }}"
                                                data-discount="{{ $bill->discount ?? 0 }}"
                                                data-total-amount="{{ $bill->total_amount }}"
                                                data-action-url="{{ route('cashier.billing.pay', $bill->id) }}"
                                                data-items='@json($bill->items)'>
                                                💵 Pay
                                            </button>
                                        @endif
                                        <a href="{{ route('cashier.billing.invoice', $bill->id) }}" class="btn btn-navy btn-sm" target="_blank">
                                            Receipt
                                        </a>
                                        <form action="{{ route('cashier.billing.destroy', $bill) }}" method="POST" style="display: inline;"
                                            onsubmit="return confirm('Delete invoice {{ $bill->invoice_no }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-ghost btn-sm" style="color: #ef4444; padding: 0.25rem 0.45rem;">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 3rem 1rem;">
                                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">🧾</div>
                                    <strong style="color: var(--white);">No billing records found.</strong>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($bills->hasPages())
                <div style="padding: 1rem;">
                    {{ $bills->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- ========================================================== -->
    <!-- PAY / CHECKOUT MODAL (Itemized Table + Add/Delete Item) -->
    <!-- ========================================================== -->
    <div class="modal-backdrop" id="modal-pay-bill">
        <div class="modal-dialog" style="max-width: 800px; width: 95vw;">
            <div class="modal-header" style="padding: 1.25rem 1.5rem;">
                <div class="modal-title-group">
                    <h4 class="modal-title" style="font-size: 1.15rem;">Checkout & Collect Payment</h4>
                    <span style="font-size: 0.75rem; color: var(--gold-light);">
                        Invoice: <strong id="pay_modal_invoice_no"></strong> • Client: <strong id="pay_modal_client_name"></strong>
                    </span>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form method="POST" id="form-pay-bill" action="">
                @csrf
                <div class="modal-body" style="padding: 1.5rem; max-height: 75vh; overflow-y: auto;">
                    
                    <!-- Itemized Table (Item | Qty | Price | Total) -->
                    <div style="margin-bottom: 1.25rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.6rem;">
                            <label class="form-label" style="font-size: 0.9rem; font-weight: 700; color: var(--gold-light); margin: 0;">
                                Itemized Breakdown
                            </label>
                            <button type="button" class="btn btn-navy btn-sm" id="btn-add-pay-item" style="font-size: 0.78rem; padding: 0.3rem 0.75rem;">
                                + Add Item
                            </button>
                        </div>

                        <div class="table-responsive" style="background: rgba(0, 0, 0, 0.25); border: 1px solid var(--navy-border); border-radius: 8px;">
                            <table class="data-table" id="table-pay-items" style="margin: 0; font-size: 0.85rem;">
                                <thead>
                                    <tr>
                                        <th>Item Description</th>
                                        <th style="width: 85px; text-align: center;">Qty</th>
                                        <th style="width: 120px; text-align: right;">Price (₱)</th>
                                        <th style="width: 120px; text-align: right;">Total (₱)</th>
                                        <th style="width: 45px; text-align: center;"></th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-pay-items">
                                    <!-- Populated dynamically via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Financial Summary Box (Subtotal, Discount, Total, Cash, Change) -->
                    <div style="background: rgba(0, 0, 0, 0.4); border: 1px solid var(--gold-border); border-radius: 10px; padding: 1.25rem; margin-top: 1.25rem;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                            <!-- Left: Payment Method & Tendered Input -->
                            <div>
                                <div class="form-group">
                                    <label class="form-label" style="font-weight: 700; font-size: 0.85rem;">Payment Channel *</label>
                                    <select name="payment_method" id="pay_select_method" class="form-control" required>
                                        <option value="cash">💵 Cash</option>
                                        <option value="gcash">📱 GCash</option>
                                        <option value="maya">💳 Maya</option>
                                        <option value="credit_card">💳 Credit Card</option>
                                        <option value="debit_card">💳 Debit Card</option>
                                        <option value="bank_transfer">🏦 Bank Transfer</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" style="font-weight: 700; font-size: 0.85rem;">Cash / Amount Tendered (₱) *</label>
                                    <input type="number" step="0.01" name="paid_amount" id="pay_input_cash" class="form-control" placeholder="0.00" required style="font-size: 1.1rem; font-weight: 700; color: #34d399;">
                                </div>

                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label" style="font-size: 0.8rem;">Cashier Reference / Notes</label>
                                    <input type="text" name="notes" id="pay_input_notes" class="form-control form-control-sm" placeholder="e.g. Reference No., Card Auth...">
                                </div>
                            </div>

                            <!-- Right: Calculations Summary Box -->
                            <div style="display: flex; flex-direction: column; justify-content: space-between;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; font-size: 0.9rem;">
                                    <span style="color: var(--text-muted);">Subtotal:</span>
                                    <strong style="color: var(--white); font-size: 1rem;" id="pay_display_subtotal">₱0.00</strong>
                                </div>

                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; font-size: 0.9rem;">
                                    <span style="color: #f87171;">Discount (₱):</span>
                                    <div style="width: 110px;">
                                        <input type="number" step="0.01" min="0" name="discount" id="pay_input_discount" class="form-control form-control-sm" value="0.00" style="text-align: right; font-weight: 700; color: #f87171;">
                                    </div>
                                </div>

                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.6rem; padding-top: 0.5rem; border-top: 1px solid var(--gold-border);">
                                    <span style="font-weight: 800; font-size: 1rem; color: var(--gold-light);">Total Due:</span>
                                    <strong style="font-size: 1.35rem; font-weight: 800; color: var(--gold-primary);" id="pay_display_total">₱0.00</strong>
                                </div>

                                <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.5rem; border-top: 1px dashed rgba(255, 255, 255, 0.1);">
                                    <span style="color: var(--text-muted); font-size: 0.85rem;">Change:</span>
                                    <strong style="font-size: 1.15rem; font-weight: 800; color: #60a5fa;" id="pay_display_change">₱0.00</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer" style="padding: 1.25rem 1.5rem;">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold" style="font-weight: 700; padding: 0.6rem 2rem; font-size: 0.95rem;">
                        Confirm & Settle Payment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================== -->
    <!-- CREATE CUSTOM BILL MODAL (Without POS / Itemized Form) -->
    <!-- ========================================================== -->
    <div class="modal-backdrop" id="modal-create-bill">
        <div class="modal-dialog" style="max-width: 840px; width: 95vw;">
            <div class="modal-header" style="padding: 1.25rem 1.5rem;">
                <div class="modal-title-group">
                    <h4 class="modal-title" style="font-size: 1.15rem;">Create & Process Itemized Bill</h4>
                    <span style="font-size: 0.75rem; color: var(--gold-light);">Direct itemized billing & instant checkout</span>
                </div>
                <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
            </div>
            <form action="{{ route('cashier.billing.store') }}" method="POST" id="form-create-bill">
                @csrf
                <div class="modal-body" style="padding: 1.5rem; max-height: 75vh; overflow-y: auto;">
                    
                    <!-- Client & Service Information -->
                    <div style="display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Client / Customer Name *</label>
                            <input type="text" name="client_name" id="new_bill_client_name" class="form-control" placeholder="e.g. Maria Santos" required>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Service Category *</label>
                            <select name="service_type" class="form-control" required>
                                <option value="veterinary">🏥 Veterinary</option>
                                <option value="grooming">✂️ Grooming</option>
                                <option value="boarding">🏨 Pet Boarding</option>
                                <option value="pet_supplies">🛍️ Pet Supplies</option>
                                <option value="combined" selected>📦 Combined Services</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Link Existing Owner (Optional)</label>
                            <select name="owner_id" id="select_new_bill_owner" class="form-control select2-searchable">
                                <option value="">-- Walk-in Client --</option>
                                @foreach($owners as $own)
                                    <option value="{{ $own->id }}" data-name="{{ $own->full_name }}">{{ $own->full_name }} ({{ $own->client_code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Itemized Breakdown Table -->
                    <div style="margin-bottom: 1.25rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.6rem;">
                            <label class="form-label" style="font-size: 0.9rem; font-weight: 700; color: var(--gold-light); margin: 0;">
                                Line Items
                            </label>
                            <button type="button" class="btn btn-navy btn-sm" id="btn-add-create-item" style="font-size: 0.78rem; padding: 0.3rem 0.75rem;">
                                + Add Item
                            </button>
                        </div>

                        <div class="table-responsive" style="background: rgba(0, 0, 0, 0.25); border: 1px solid var(--navy-border); border-radius: 8px;">
                            <table class="data-table" id="table-create-items" style="margin: 0; font-size: 0.85rem;">
                                <thead>
                                    <tr>
                                        <th>Item Description</th>
                                        <th style="width: 85px; text-align: center;">Qty</th>
                                        <th style="width: 120px; text-align: right;">Price (₱)</th>
                                        <th style="width: 120px; text-align: right;">Total (₱)</th>
                                        <th style="width: 45px; text-align: center;"></th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-create-items">
                                    <tr>
                                        <td><input type="text" name="items[0][item_name]" class="form-control form-control-sm item-name-input" placeholder="e.g. Consultation Fee" required></td>
                                        <td><input type="number" name="items[0][quantity]" class="form-control form-control-sm item-qty-input" min="1" value="1" required style="text-align: center;"></td>
                                        <td><input type="number" step="0.01" name="items[0][unit_price]" class="form-control form-control-sm item-price-input" min="0" value="0.00" required style="text-align: right;"></td>
                                        <td><input type="number" step="0.01" name="items[0][total_price]" class="form-control form-control-sm item-total-input" value="0.00" readonly style="text-align: right; font-weight: 700; background: rgba(0,0,0,0.3);"></td>
                                        <td style="text-align: center;"><button type="button" class="btn btn-ghost btn-sm btn-delete-row" style="color: #ef4444; padding: 0.2rem 0.4rem;">🗑️</button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Summary & Payment Box -->
                    <div style="background: rgba(0, 0, 0, 0.4); border: 1px solid var(--gold-border); border-radius: 10px; padding: 1.25rem;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                            <div>
                                <div class="form-group">
                                    <label class="form-label" style="font-weight: 700; font-size: 0.85rem;">Payment Channel *</label>
                                    <select name="payment_method" class="form-control" required>
                                        <option value="cash">💵 Cash</option>
                                        <option value="gcash">📱 GCash</option>
                                        <option value="maya">💳 Maya</option>
                                        <option value="credit_card">💳 Credit Card</option>
                                        <option value="debit_card">💳 Debit Card</option>
                                        <option value="bank_transfer">🏦 Bank Transfer</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" style="font-weight: 700; font-size: 0.85rem;">Cash / Paid Amount (₱) *</label>
                                    <input type="number" step="0.01" name="paid_amount" id="create_input_cash" class="form-control" placeholder="0.00" required style="font-size: 1.1rem; font-weight: 700; color: #34d399;">
                                </div>

                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label" style="font-size: 0.8rem;">Notes</label>
                                    <input type="text" name="notes" class="form-control form-control-sm" placeholder="Optional notes">
                                </div>
                            </div>

                            <div style="display: flex; flex-direction: column; justify-content: space-between;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; font-size: 0.9rem;">
                                    <span style="color: var(--text-muted);">Subtotal:</span>
                                    <strong style="color: var(--white); font-size: 1rem;" id="create_display_subtotal">₱0.00</strong>
                                    <input type="hidden" name="subtotal" id="create_hidden_subtotal" value="0.00">
                                </div>

                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; font-size: 0.9rem;">
                                    <span style="color: #f87171;">Discount (₱):</span>
                                    <div style="width: 110px;">
                                        <input type="number" step="0.01" min="0" name="discount" id="create_input_discount" class="form-control form-control-sm" value="0.00" style="text-align: right; font-weight: 700; color: #f87171;">
                                    </div>
                                </div>

                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.6rem; padding-top: 0.5rem; border-top: 1px solid var(--gold-border);">
                                    <span style="font-weight: 800; font-size: 1rem; color: var(--gold-light);">Total Due:</span>
                                    <strong style="font-size: 1.35rem; font-weight: 800; color: var(--gold-primary);" id="create_display_total">₱0.00</strong>
                                    <input type="hidden" name="total_amount" id="create_hidden_total" value="0.00">
                                </div>

                                <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.5rem; border-top: 1px dashed rgba(255, 255, 255, 0.1);">
                                    <span style="color: var(--text-muted); font-size: 0.85rem;">Change:</span>
                                    <strong style="font-size: 1.15rem; font-weight: 800; color: #60a5fa;" id="create_display_change">₱0.00</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer" style="padding: 1.25rem 1.5rem;">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-gold" style="font-weight: 700; padding: 0.6rem 2rem; font-size: 0.95rem;">
                        Save & Print Receipt
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/select2.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // -------------------------------------------------------------
    // Helper function to format PHP currency
    // -------------------------------------------------------------
    function fmtCurrency(val) {
        return '₱' + Number(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // -------------------------------------------------------------
    // 1. PAY MODAL DYNAMICS
    // -------------------------------------------------------------
    const payModal = document.getElementById('modal-pay-bill');
    const payForm = document.getElementById('form-pay-bill');
    const tbodyPay = document.getElementById('tbody-pay-items');
    const btnAddPayItem = document.getElementById('btn-add-pay-item');
    const inputPayDiscount = document.getElementById('pay_input_discount');
    const inputPayCash = document.getElementById('pay_input_cash');
    const dispPaySubtotal = document.getElementById('pay_display_subtotal');
    const dispPayTotal = document.getElementById('pay_display_total');
    const dispPayChange = document.getElementById('pay_display_change');

    let payItemIndex = 0;

    function recalcPayModal() {
        let subtotal = 0;
        const rows = tbodyPay.querySelectorAll('tr');
        rows.forEach(row => {
            const qtyInput = row.querySelector('.item-qty-input');
            const priceInput = row.querySelector('.item-price-input');
            const totalInput = row.querySelector('.item-total-input');

            const qty = Math.max(1, parseInt(qtyInput.value) || 1);
            const price = Math.max(0, parseFloat(priceInput.value) || 0);
            const lineTotal = qty * price;

            totalInput.value = lineTotal.toFixed(2);
            subtotal += lineTotal;
        });

        const discount = Math.max(0, parseFloat(inputPayDiscount.value) || 0);
        const total = Math.max(0, subtotal - discount);
        const cash = Math.max(0, parseFloat(inputPayCash.value) || 0);
        const change = Math.max(0, cash - total);

        dispPaySubtotal.textContent = fmtCurrency(subtotal);
        dispPayTotal.textContent = fmtCurrency(total);
        dispPayChange.textContent = fmtCurrency(change);
    }

    function addPayItemRow(name = '', qty = 1, price = 0) {
        const tr = document.createElement('tr');
        const lineTotal = (qty * price).toFixed(2);
        tr.innerHTML = `
            <td><input type="text" name="items[${payItemIndex}][item_name]" class="form-control form-control-sm item-name-input" value="${name}" placeholder="Item description" required></td>
            <td><input type="number" name="items[${payItemIndex}][quantity]" class="form-control form-control-sm item-qty-input" min="1" value="${qty}" required style="text-align: center;"></td>
            <td><input type="number" step="0.01" name="items[${payItemIndex}][unit_price]" class="form-control form-control-sm item-price-input" min="0" value="${Number(price).toFixed(2)}" required style="text-align: right;"></td>
            <td><input type="number" step="0.01" name="items[${payItemIndex}][total_price]" class="form-control form-control-sm item-total-input" value="${lineTotal}" readonly style="text-align: right; font-weight: 700; background: rgba(0,0,0,0.3);"></td>
            <td style="text-align: center;"><button type="button" class="btn btn-ghost btn-sm btn-delete-row" style="color: #ef4444; padding: 0.2rem 0.4rem;">🗑️</button></td>
        `;
        tbodyPay.appendChild(tr);
        payItemIndex++;
        recalcPayModal();
    }

    if (btnAddPayItem) {
        btnAddPayItem.addEventListener('click', function() {
            addPayItemRow('', 1, 0);
        });
    }

    tbodyPay.addEventListener('input', recalcPayModal);
    tbodyPay.addEventListener('click', function(e) {
        if (e.target.closest('.btn-delete-row')) {
            const row = e.target.closest('tr');
            if (tbodyPay.querySelectorAll('tr').length > 1) {
                row.remove();
                recalcPayModal();
            } else {
                alert('At least one item is required.');
            }
        }
    });

    if (inputPayDiscount) inputPayDiscount.addEventListener('input', recalcPayModal);
    if (inputPayCash) inputPayCash.addEventListener('input', recalcPayModal);

    // Open Pay Modal button handler
    document.querySelectorAll('.btn-open-pay-modal').forEach(btn => {
        btn.addEventListener('click', function() {
            const actionUrl = this.dataset.actionUrl;
            const invoiceNo = this.dataset.invoiceNo;
            const clientName = this.dataset.clientName;
            const discount = parseFloat(this.dataset.discount) || 0;
            const items = JSON.parse(this.dataset.items || '[]');

            payForm.action = actionUrl;
            document.getElementById('pay_modal_invoice_no').textContent = invoiceNo;
            document.getElementById('pay_modal_client_name').textContent = clientName;
            inputPayDiscount.value = discount.toFixed(2);

            tbodyPay.innerHTML = '';
            payItemIndex = 0;

            if (items && items.length > 0) {
                items.forEach(it => {
                    addPayItemRow(it.item_name, it.quantity, it.unit_price);
                });
            } else {
                addPayItemRow('Service Charge', 1, parseFloat(this.dataset.totalAmount) || 0);
            }

            // Default cash tendered to total
            const subtotal = parseFloat(this.dataset.subtotal) || parseFloat(this.dataset.totalAmount) || 0;
            const total = Math.max(0, subtotal - discount);
            inputPayCash.value = total.toFixed(2);

            recalcPayModal();
            payModal.classList.add('active');
        });
    });

    // -------------------------------------------------------------
    // 2. CREATE NEW BILL MODAL DYNAMICS
    // -------------------------------------------------------------
    const tbodyCreate = document.getElementById('tbody-create-items');
    const btnAddCreateItem = document.getElementById('btn-add-create-item');
    const inputCreateDiscount = document.getElementById('create_input_discount');
    const inputCreateCash = document.getElementById('create_input_cash');
    const dispCreateSubtotal = document.getElementById('create_display_subtotal');
    const dispCreateTotal = document.getElementById('create_display_total');
    const dispCreateChange = document.getElementById('create_display_change');
    const hiddenCreateSubtotal = document.getElementById('create_hidden_subtotal');
    const hiddenCreateTotal = document.getElementById('create_hidden_total');

    let createItemIndex = 1;

    function recalcCreateModal() {
        let subtotal = 0;
        const rows = tbodyCreate.querySelectorAll('tr');
        rows.forEach(row => {
            const qtyInput = row.querySelector('.item-qty-input');
            const priceInput = row.querySelector('.item-price-input');
            const totalInput = row.querySelector('.item-total-input');

            const qty = Math.max(1, parseInt(qtyInput.value) || 1);
            const price = Math.max(0, parseFloat(priceInput.value) || 0);
            const lineTotal = qty * price;

            totalInput.value = lineTotal.toFixed(2);
            subtotal += lineTotal;
        });

        const discount = Math.max(0, parseFloat(inputCreateDiscount.value) || 0);
        const total = Math.max(0, subtotal - discount);
        const cash = Math.max(0, parseFloat(inputCreateCash.value) || 0);
        const change = Math.max(0, cash - total);

        dispCreateSubtotal.textContent = fmtCurrency(subtotal);
        dispCreateTotal.textContent = fmtCurrency(total);
        dispCreateChange.textContent = fmtCurrency(change);
        hiddenCreateSubtotal.value = subtotal.toFixed(2);
        hiddenCreateTotal.value = total.toFixed(2);
    }

    function addCreateItemRow(name = '', qty = 1, price = 0) {
        const tr = document.createElement('tr');
        const lineTotal = (qty * price).toFixed(2);
        tr.innerHTML = `
            <td><input type="text" name="items[${createItemIndex}][item_name]" class="form-control form-control-sm item-name-input" value="${name}" placeholder="Item description" required></td>
            <td><input type="number" name="items[${createItemIndex}][quantity]" class="form-control form-control-sm item-qty-input" min="1" value="${qty}" required style="text-align: center;"></td>
            <td><input type="number" step="0.01" name="items[${createItemIndex}][unit_price]" class="form-control form-control-sm item-price-input" min="0" value="${Number(price).toFixed(2)}" required style="text-align: right;"></td>
            <td><input type="number" step="0.01" name="items[${createItemIndex}][total_price]" class="form-control form-control-sm item-total-input" value="${lineTotal}" readonly style="text-align: right; font-weight: 700; background: rgba(0,0,0,0.3);"></td>
            <td style="text-align: center;"><button type="button" class="btn btn-ghost btn-sm btn-delete-row" style="color: #ef4444; padding: 0.2rem 0.4rem;">🗑️</button></td>
        `;
        tbodyCreate.appendChild(tr);
        createItemIndex++;
        recalcCreateModal();
    }

    if (btnAddCreateItem) {
        btnAddCreateItem.addEventListener('click', function() {
            addCreateItemRow('', 1, 0);
        });
    }

    tbodyCreate.addEventListener('input', recalcCreateModal);
    tbodyCreate.addEventListener('click', function(e) {
        if (e.target.closest('.btn-delete-row')) {
            const row = e.target.closest('tr');
            if (tbodyCreate.querySelectorAll('tr').length > 1) {
                row.remove();
                recalcCreateModal();
            } else {
                alert('At least one item is required.');
            }
        }
    });

    if (inputCreateDiscount) inputCreateDiscount.addEventListener('input', recalcCreateModal);
    if (inputCreateCash) inputCreateCash.addEventListener('input', recalcCreateModal);

    // Sync client name from select2
    if (typeof jQuery !== 'undefined') {
        jQuery('#select_new_bill_owner').on('change', function() {
            const selectedOption = jQuery(this).find('option:selected');
            const ownerName = selectedOption.data('name');
            if (ownerName) {
                document.getElementById('new_bill_client_name').value = ownerName;
            }
        });
    }
});
</script>
@endpush
