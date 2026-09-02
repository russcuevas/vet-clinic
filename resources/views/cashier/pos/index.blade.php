@extends('layouts.app')

@php
    $title = 'Cashier POS';
    $headerTitle = 'Pet Supplies POS Station';
    $breadcrumb = 'Point of Sale';
@endphp

@section('content')
    <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 1.5rem; align-items: start;">
        <!-- Left: Supplies Catalog -->
        <div class="card">
            <div class="card-header">
                <div class="card-title-group">
                    <h3 class="card-title">Product Catalog</h3>
                    <span class="card-subtitle">Click items to add to cart</span>
                </div>
                <div class="search-input-wrapper" style="min-width: 200px;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    <input type="text" class="form-control" placeholder="Search supplies..." id="supply-search-input">
                </div>
            </div>

            <div class="card-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1rem;" id="supply-catalog-grid">
                    @foreach($supplies as $item)
                        <div class="supply-item-card" 
                            style="background: var(--navy-dark); border: 1px solid var(--black-border); border-radius: var(--radius-sm); padding: 1rem; cursor: pointer; transition: all var(--transition-fast);"
                            onclick="addToCart({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $item->unit_price }}, {{ $item->stock_quantity }})"
                            data-name="{{ strtolower($item->name) }}">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                <span class="badge badge-gold" style="font-size: 0.68rem;">{{ $item->item_code }}</span>
                                <span style="font-size: 0.72rem; color: var(--text-muted);">Stock: {{ $item->stock_quantity }}</span>
                            </div>
                            <h4 style="font-size: 0.88rem; font-weight: 700; color: var(--white); margin-bottom: 0.5rem;">{{ $item->name }}</h4>
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <strong style="color: var(--gold-primary); font-size: 1rem;">₱{{ number_format($item->unit_price, 2) }}</strong>
                                <button type="button" class="btn btn-gold btn-sm" style="padding: 2px 7px;">+ Add</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right: Checkout Cart -->
        <div class="card" style="position: sticky; top: 90px;">
            <div class="card-header">
                <div class="card-title-group">
                    <h3 class="card-title">Cashier Register</h3>
                    <span class="card-subtitle">Client & checkout calculation</span>
                </div>
            </div>

            <form action="{{ route('cashier.pos.store') }}" method="POST" id="pos-checkout-form">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Client Type</label>
                        <div style="display: flex; gap: 0.5rem;">
                            <label style="display: flex; align-items: center; gap: 0.3rem; font-size: 0.8rem; color: var(--white);">
                                <input type="radio" name="client_type" value="existing" checked onchange="toggleClientFields(this.value)"> Existing Client
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.3rem; font-size: 0.8rem; color: var(--white);">
                                <input type="radio" name="client_type" value="new" onchange="toggleClientFields(this.value)"> New Client
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.3rem; font-size: 0.8rem; color: var(--white);">
                                <input type="radio" name="client_type" value="walkin" onchange="toggleClientFields(this.value)"> Walk-in
                            </label>
                        </div>
                    </div>

                    <div class="form-group" id="field-existing-client">
                        <label class="form-label">Select Client Key Code</label>
                        <select name="owner_id" class="form-select">
                            <option value="">-- Choose Existing Client --</option>
                            @foreach($owners as $owner)
                                <option value="{{ $owner->id }}">{{ $owner->client_code }} - {{ $owner->full_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="field-new-client" style="display: none; background: rgba(11, 25, 44, 0.4); padding: 0.85rem; border-radius: var(--radius-sm); border: 1px dashed var(--gold-border); margin-bottom: 1rem;">
                        <span style="font-size: 0.75rem; color: var(--gold-light); font-weight: 600; display: block; margin-bottom: 0.5rem;">New Client Registration</span>
                        <div class="form-group" style="margin-bottom: 0.5rem;">
                            <input type="text" name="full_name" class="form-control" placeholder="Owner Full Name">
                        </div>
                        <div class="form-group" style="margin-bottom: 0.5rem;">
                            <input type="text" name="contact_number" class="form-control" placeholder="Contact Number">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <input type="text" name="address" class="form-control" placeholder="Address">
                        </div>
                    </div>

                    <hr style="border: 0; border-top: 1px solid var(--black-border); margin: 1rem 0;">

                    <label class="form-label">Cart Items</label>
                    <div id="cart-items-container" style="max-height: 200px; overflow-y: auto; margin-bottom: 1rem;">
                        <p id="empty-cart-msg" style="color: var(--text-muted); font-size: 0.82rem; text-align: center; padding: 1rem;">Cart is empty.</p>
                    </div>

                    <div style="background: var(--navy-dark); padding: 0.85rem; border-radius: var(--radius-sm); border: 1px solid var(--black-border); margin-bottom: 1rem;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.88rem; color: var(--text-secondary);">
                            <span>Total Due:</span>
                            <strong style="color: var(--gold-primary); font-size: 1.25rem;" id="cart-total-display">₱0.00</strong>
                        </div>
                    </div>

                    <div class="form-grid" style="margin-bottom: 1rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="cash">Cash</option>
                                <option value="gcash">GCash</option>
                                <option value="credit_card">Card</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Cash Received (₱) <span class="req">*</span></label>
                            <input type="number" step="0.01" name="paid_amount" id="paid_amount_input" class="form-control" placeholder="0.00" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-gold" style="width: 100%; padding: 0.75rem;">
                        <span>Complete Checkout & Print Receipt</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    let cart = [];

    function addToCart(id, name, price, maxStock) {
        const existing = cart.find(item => item.id === id);
        if (existing) {
            if (existing.qty < maxStock) {
                existing.qty++;
            } else {
                window.Toast.show('warning', 'Stock Limit', `Maximum available stock (${maxStock}) reached for ${name}`);
                return;
            }
        } else {
            if (maxStock <= 0) {
                window.Toast.show('error', 'Out of Stock', `${name} is out of stock.`);
                return;
            }
            cart.push({ id, name, price, qty: 1, maxStock });
        }
        renderCart();
        window.Toast.show('success', 'Item Added', `Added ${name} to cart.`);
    }

    function updateQty(id, delta) {
        const item = cart.find(i => i.id === id);
        if (!item) return;

        item.qty += delta;
        if (item.qty <= 0) {
            cart = cart.filter(i => i.id !== id);
        } else if (item.qty > item.maxStock) {
            item.qty = item.maxStock;
        }
        renderCart();
    }

    function removeItem(id) {
        cart = cart.filter(i => i.id !== id);
        renderCart();
    }

    function renderCart() {
        const container = document.getElementById('cart-items-container');
        const totalDisplay = document.getElementById('cart-total-display');
        const paidInput = document.getElementById('paid_amount_input');

        container.innerHTML = '';

        if (cart.length === 0) {
            container.innerHTML = '<p id="empty-cart-msg" style="color: var(--text-muted); font-size: 0.82rem; text-align: center; padding: 1rem;">Cart is empty.</p>';
            totalDisplay.textContent = '₱0.00';
            paidInput.value = '';
            return;
        }

        let total = 0;

        cart.forEach((item, index) => {
            const lineTotal = item.price * item.qty;
            total += lineTotal;

            const row = document.createElement('div');
            row.style = 'display: flex; align-items: center; justify-content: space-between; background: var(--black-bg); padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); margin-bottom: 0.4rem; border: 1px solid var(--black-border);';
            row.innerHTML = `
                <div style="flex: 1; overflow: hidden; margin-right: 0.5rem;">
                    <div style="font-size: 0.82rem; font-weight: 600; color: var(--white);">${item.name}</div>
                    <div style="font-size: 0.72rem; color: var(--gold-light);">₱${item.price.toFixed(2)} x ${item.qty} = ₱${lineTotal.toFixed(2)}</div>
                    <input type="hidden" name="items[${index}][item_id]" value="${item.id}">
                    <input type="hidden" name="items[${index}][quantity]" value="${item.qty}">
                </div>
                <div style="display: flex; align-items: center; gap: 0.35rem;">
                    <button type="button" class="btn btn-navy btn-sm" style="padding: 2px 6px;" onclick="updateQty(${item.id}, -1)">-</button>
                    <span style="font-size: 0.82rem; font-weight: 700; color: var(--white);">${item.qty}</span>
                    <button type="button" class="btn btn-navy btn-sm" style="padding: 2px 6px;" onclick="updateQty(${item.id}, 1)">+</button>
                    <button type="button" class="btn btn-ghost btn-sm" style="padding: 2px 6px; color: var(--danger);" onclick="removeItem(${item.id})">✕</button>
                </div>
            `;
            container.appendChild(row);
        });

        totalDisplay.textContent = '₱' + total.toFixed(2);
        paidInput.value = total.toFixed(2);
    }

    function toggleClientFields(type) {
        const existingField = document.getElementById('field-existing-client');
        const newField = document.getElementById('field-new-client');

        if (type === 'existing') {
            existingField.style.display = 'block';
            newField.style.display = 'none';
        } else if (type === 'new') {
            existingField.style.display = 'none';
            newField.style.display = 'block';
        } else {
            existingField.style.display = 'none';
            newField.style.display = 'none';
        }
    }

    document.getElementById('supply-search-input').addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        document.querySelectorAll('.supply-item-card').forEach(card => {
            const name = card.getAttribute('data-name');
            card.style.display = name.includes(query) ? '' : 'none';
        });
    });
</script>
@endpush
