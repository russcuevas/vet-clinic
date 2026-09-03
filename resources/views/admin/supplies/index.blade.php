@extends('layouts.app')

@php
    $title = 'Pet Supplies POS';
    $headerTitle = 'Pet Supplies Counter & POS';
    $breadcrumb = 'Pet Supplies POS';
@endphp

@section('content')
    <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 1.5rem; align-items: start;">
        <!-- Left: Product Catalog Grid -->
        <div class="card">
            <div class="card-header" style="flex-direction: column; align-items: stretch; gap: 0.85rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                    <div class="card-title-group">
                        <h3 class="card-title">Products & Supplies Catalog</h3>
                        <span class="card-subtitle">Click items to add to customer cart</span>
                    </div>
                    <div class="search-input-wrapper" style="min-width: 220px;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        <input type="text" class="form-control" placeholder="Search supplies, vaccines, medicine..." id="supply-search-input">
                    </div>
                </div>

                <!-- Category Filter Pills -->
                <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;" id="category-filter-pills">
                    <button type="button" class="btn btn-sm btn-gold cat-filter-btn" data-category="all">All Items</button>
                    <button type="button" class="btn btn-sm btn-navy cat-filter-btn" data-category="pet_supplies">Supplies</button>
                    <button type="button" class="btn btn-sm btn-navy cat-filter-btn" data-category="medicine">Medicines</button>
                    <button type="button" class="btn btn-sm btn-navy cat-filter-btn" data-category="vaccine">Vaccines</button>
                    <button type="button" class="btn btn-sm btn-navy cat-filter-btn" data-category="grooming_supply">Grooming</button>
                    <button type="button" class="btn btn-sm btn-navy cat-filter-btn" data-category="accessories">Accessories</button>
                </div>
            </div>

            <div class="card-body" id="pos-catalog-scroll-container" style="max-height: 600px; overflow-y: auto; padding: 1.25rem;">
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem;" id="supply-catalog-grid">
                    @forelse($supplies as $item)
                        <div class="supply-item-card" 
                            style="background: var(--navy-dark); border: 1px solid var(--black-border); border-radius: var(--radius-sm); padding: 1rem; cursor: pointer; transition: all var(--transition-fast); display: flex; flex-direction: column; justify-content: space-between;"
                            onclick="addToCart({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $item->unit_price }}, {{ $item->stock_quantity }})"
                            data-name="{{ strtolower($item->name) }}"
                            data-code="{{ strtolower($item->item_code) }}"
                            data-category="{{ $item->category }}">
                            <div>
                                @if($item->image && file_exists(public_path($item->image)))
                                    <div style="width: 100%; height: 120px; border-radius: var(--radius-sm); overflow: hidden; margin-bottom: 0.6rem; background: var(--black-bg); border: 1px solid rgba(255,255,255,0.06);">
                                        <img src="{{ asset($item->image) }}" alt="{{ $item->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                @else
                                    <div style="width: 100%; height: 75px; border-radius: var(--radius-sm); margin-bottom: 0.6rem; background: rgba(11, 25, 44, 0.6); border: 1px dashed rgba(212, 175, 55, 0.25); display: flex; align-items: center; justify-content: center; font-size: 1.6rem;">
                                        @switch($item->category)
                                            @case('vaccine')
                                                💉
                                                @break
                                            @case('medicine')
                                                💊
                                                @break
                                            @case('grooming_supply')
                                                🛁
                                                @break
                                            @case('accessories')
                                                🎀
                                                @break
                                            @default
                                                🦴
                                        @endswitch
                                    </div>
                                @endif

                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.4rem; gap: 0.35rem;">
                                    <span class="badge badge-gold" style="font-size: 0.65rem;">{{ $item->item_code }}</span>
                                    <span class="badge badge-navy" style="font-size: 0.62rem; background: rgba(14, 165, 233, 0.12); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.25);">
                                        {{ ucfirst(str_replace('_', ' ', $item->category)) }}
                                    </span>
                                </div>
                                <h4 style="font-size: 0.9rem; font-weight: 700; color: var(--white); margin-bottom: 0.35rem; line-height: 1.3;">{{ $item->name }}</h4>
                                @if($item->description)
                                    <p style="font-size: 0.74rem; color: var(--text-muted); margin-bottom: 0.6rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">{{ $item->description }}</p>
                                @endif
                            </div>
                            <div style="margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px solid rgba(255,255,255,0.05);">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                                    <strong style="color: var(--gold-primary); font-size: 1.05rem;">₱{{ number_format($item->unit_price, 2) }}</strong>
                                    <span style="font-size: 0.72rem; color: {{ $item->stock_quantity <= ($item->reorder_level ?? 5) ? 'var(--danger)' : 'var(--text-muted)' }}; font-weight: 600;">
                                        {{ $item->stock_quantity }} {{ $item->unit }}
                                    </span>
                                </div>
                                <button type="button" class="btn {{ $item->stock_quantity > 0 ? 'btn-gold' : 'btn-navy' }} btn-sm" style="width: 100%; padding: 4px 8px; font-size: 0.78rem;" {{ $item->stock_quantity <= 0 ? 'disabled' : '' }}>
                                    {{ $item->stock_quantity > 0 ? '+ Add to Cart' : 'Out of Stock' }}
                                </button>
                            </div>
                        </div>
                    @empty
                        <div style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: var(--text-muted);">
                            No products or supplies found in inventory.
                        </div>
                    @endforelse
                </div>

                <div id="no-filter-match" style="display: none; text-align: center; padding: 2.5rem 1rem; color: var(--text-muted); font-size: 0.88rem;">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">🔍</div>
                    No items match the selected category or search keyword.
                </div>

                <!-- Scroll Trigger & Infinite Scroll Status -->
                <div id="catalog-scroll-status" style="margin-top: 1.25rem; text-align: center; padding: 0.75rem; border-top: 1px dashed rgba(255,255,255,0.08);">
                    <div id="catalog-spinner" style="display: none; align-items: center; justify-content: center; gap: 0.6rem; color: var(--gold-light); font-size: 0.82rem; padding: 0.5rem;">
                        <span style="display: inline-block; width: 1.2rem; height: 1.2rem; border: 2px solid rgba(212, 175, 55, 0.3); border-top-color: var(--gold-primary); border-radius: 50%; animation: spin 0.6s linear infinite;"></span>
                        <span>Loading more products...</span>
                    </div>

                    <div id="catalog-scroll-hint" style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; font-size: 0.78rem; color: var(--text-muted);">
                        <span>↓ Scroll down to load more</span>
                        <span class="badge badge-navy" id="catalog-count-badge" style="font-size: 0.7rem; color: var(--gold-light);">Showing 10 of {{ count($supplies) }}</span>
                    </div>

                    <div id="catalog-all-loaded" style="display: none; font-size: 0.78rem; color: var(--text-muted);">
                        ✓ All <span id="total-loaded-count">{{ count($supplies) }}</span> products loaded
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Checkout Cart & Client Selection (Flowchart: Owner Information + Billing Data Base) -->
        <div class="card" style="position: sticky; top: 90px;">
            <div class="card-header">
                <div class="card-title-group">
                    <h3 class="card-title">Checkout Counter</h3>
                    <span class="card-subtitle">Flowchart: Client Info + Billing Queue</span>
                </div>
            </div>

            <form action="{{ route('admin.supplies.store') }}" method="POST" id="checkout-form">
                @csrf
                <div class="card-body">
                    <!-- Client Selection -->
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

                    <!-- Existing Client Dropdown -->
                    <div class="form-group" id="field-existing-client">
                        <label class="form-label">Select Client Key Code</label>
                        <select name="owner_id" class="form-select">
                            <option value="">-- Choose Existing Client --</option>
                            @foreach($owners as $owner)
                                <option value="{{ $owner->id }}">{{ $owner->client_code }} - {{ $owner->full_name }} ({{ $owner->contact_number }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- New Client Form (Flowchart: Generate key code, enter name, contact #, address) -->
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

                    <!-- Cart Item Lines -->
                    <label class="form-label">Purchased Items</label>
                    <div id="cart-items-container" style="max-height: 220px; overflow-y: auto; margin-bottom: 1rem;">
                        <p id="empty-cart-msg" style="color: var(--text-muted); font-size: 0.82rem; text-align: center; padding: 1rem;">No items in cart yet. Select items from the catalog.</p>
                    </div>

                    <!-- Summary & Payment -->
                    <div style="background: var(--navy-dark); padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--black-border); margin-bottom: 1rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.88rem; color: var(--text-secondary);">
                            <span>Total Amount:</span>
                            <strong style="color: var(--gold-primary); font-size: 1.25rem;" id="cart-total-display">₱0.00</strong>
                        </div>
                    </div>

                    <div class="form-grid" style="margin-bottom: 1rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="cash">Cash</option>
                                <option value="gcash">GCash</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="debit_card">Debit Card</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Amount Paid (₱) <span class="req">*</span></label>
                            <input type="number" step="0.01" name="paid_amount" id="paid_amount_input" class="form-control" placeholder="0.00" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-gold" style="width: 100%; padding: 0.75rem;" id="btn-complete-sale">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        <span>Complete Sale & Push to Billing</span>
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
                window.Toast.show('error', 'Out of Stock', `${name} is currently out of stock.`);
                return;
            }
            cart.push({ id, name, price, qty: 1, maxStock });
        }
        renderCart();
        window.Toast.show('success', 'Item Added', `Added ${name} to checkout cart.`);
    }

    function updateQty(id, delta) {
        const item = cart.find(i => i.id === id);
        if (!item) return;

        item.qty += delta;
        if (item.qty <= 0) {
            cart = cart.filter(i => i.id !== id);
        } else if (item.qty > item.maxStock) {
            item.qty = item.maxStock;
            window.Toast.show('warning', 'Stock Limit', `Cannot exceed stock of ${item.maxStock}`);
        }
        renderCart();
    }

    function removeItem(id) {
        cart = cart.filter(i => i.id !== id);
        renderCart();
    }

    function renderCart() {
        const container = document.getElementById('cart-items-container');
        const emptyMsg = document.getElementById('empty-cart-msg');
        const totalDisplay = document.getElementById('cart-total-display');
        const paidInput = document.getElementById('paid_amount_input');

        container.innerHTML = '';

        if (cart.length === 0) {
            container.innerHTML = '<p id="empty-cart-msg" style="color: var(--text-muted); font-size: 0.82rem; text-align: center; padding: 1rem;">No items in cart yet. Select items from the catalog.</p>';
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
                    <div style="font-size: 0.82rem; font-weight: 600; color: var(--white); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${item.name}</div>
                    <div style="font-size: 0.72rem; color: var(--gold-light);">₱${item.price.toFixed(2)} x ${item.qty} = ₱${lineTotal.toFixed(2)}</div>
                    <input type="hidden" name="items[${index}][item_id]" value="${item.id}">
                    <input type="hidden" name="items[${index}][quantity]" value="${item.qty}">
                </div>
                <div style="display: flex; align-items: center; gap: 0.35rem;">
                    <button type="button" class="btn btn-navy btn-sm" style="padding: 2px 6px;" onclick="updateQty(${item.id}, -1)">-</button>
                    <span style="font-size: 0.82rem; font-weight: 700; color: var(--white); min-width: 16px; text-align: center;">${item.qty}</span>
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

    // Infinite Scroll / Lazy Load & Search Logic
    const BATCH_SIZE = 10;
    let currentLimit = 10;
    let activeCategory = 'all';
    let isFetchingMore = false;

    function getMatchingCards() {
        const query = (document.getElementById('supply-search-input')?.value || '').toLowerCase().trim();
        const cards = Array.from(document.querySelectorAll('.supply-item-card'));

        return cards.filter(card => {
            const name = card.getAttribute('data-name') || '';
            const code = card.getAttribute('data-code') || '';
            const category = card.getAttribute('data-category') || '';

            const matchesCategory = (activeCategory === 'all' || category === activeCategory);
            const matchesQuery = !query || name.includes(query) || code.includes(query) || category.toLowerCase().includes(query);

            return matchesCategory && matchesQuery;
        });
    }

    function renderCatalog() {
        const query = (document.getElementById('supply-search-input')?.value || '').toLowerCase().trim();
        const allCards = document.querySelectorAll('.supply-item-card');
        const matchingCards = getMatchingCards();
        const totalMatching = matchingCards.length;

        // Hide all cards first
        allCards.forEach(card => card.style.display = 'none');

        const noMatchEl = document.getElementById('no-filter-match');
        const scrollStatusEl = document.getElementById('catalog-scroll-status');
        const spinnerEl = document.getElementById('catalog-spinner');
        const hintEl = document.getElementById('catalog-scroll-hint');
        const allLoadedEl = document.getElementById('catalog-all-loaded');
        const countBadge = document.getElementById('catalog-count-badge');
        const totalLoadedCount = document.getElementById('total-loaded-count');

        if (totalMatching === 0) {
            if (noMatchEl) noMatchEl.style.display = 'block';
            if (scrollStatusEl) scrollStatusEl.style.display = 'none';
            return;
        } else {
            if (noMatchEl) noMatchEl.style.display = 'none';
            if (scrollStatusEl) scrollStatusEl.style.display = 'block';
        }

        // When searching, show ALL matching items immediately so user finds it instantly
        const isSearchActive = query.length > 0;
        const effectiveLimit = isSearchActive ? totalMatching : currentLimit;

        matchingCards.forEach((card, index) => {
            if (index < effectiveLimit) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });

        const displayedCount = Math.min(effectiveLimit, totalMatching);

        if (countBadge) {
            countBadge.textContent = `Showing ${displayedCount} of ${totalMatching}`;
        }
        if (totalLoadedCount) {
            totalLoadedCount.textContent = totalMatching;
        }

        if (isSearchActive) {
            if (spinnerEl) spinnerEl.style.display = 'none';
            if (hintEl) hintEl.style.display = 'none';
            if (allLoadedEl) {
                allLoadedEl.style.display = 'block';
                allLoadedEl.textContent = `✓ Found ${totalMatching} matching ${totalMatching === 1 ? 'item' : 'items'}`;
            }
        } else if (displayedCount >= totalMatching) {
            if (spinnerEl) spinnerEl.style.display = 'none';
            if (hintEl) hintEl.style.display = 'none';
            if (allLoadedEl) {
                allLoadedEl.style.display = 'block';
                allLoadedEl.textContent = `✓ All ${totalMatching} products loaded`;
            }
        } else {
            if (spinnerEl) spinnerEl.style.display = 'none';
            if (hintEl) hintEl.style.display = 'flex';
            if (allLoadedEl) allLoadedEl.style.display = 'none';
        }
    }

    function loadNextBatch() {
        const query = (document.getElementById('supply-search-input')?.value || '').toLowerCase().trim();
        if (query.length > 0) return; // Search shows all directly

        const matchingCards = getMatchingCards();
        if (currentLimit >= matchingCards.length || isFetchingMore) return;

        isFetchingMore = true;
        const spinnerEl = document.getElementById('catalog-spinner');
        const hintEl = document.getElementById('catalog-scroll-hint');

        if (spinnerEl) spinnerEl.style.display = 'flex';
        if (hintEl) hintEl.style.display = 'none';

        setTimeout(() => {
            currentLimit += BATCH_SIZE;
            renderCatalog();
            isFetchingMore = false;
        }, 280);
    }

    // Scroll listener on catalog scroll container
    const scrollContainer = document.getElementById('pos-catalog-scroll-container');
    if (scrollContainer) {
        scrollContainer.addEventListener('scroll', function() {
            const threshold = 60;
            if (this.scrollTop + this.clientHeight >= this.scrollHeight - threshold) {
                loadNextBatch();
            }
        });
    }

    // Category button click
    document.querySelectorAll('.cat-filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.cat-filter-btn').forEach(b => {
                b.classList.remove('btn-gold');
                b.classList.add('btn-navy');
            });
            this.classList.remove('btn-navy');
            this.classList.add('btn-gold');
            activeCategory = this.getAttribute('data-category');
            currentLimit = BATCH_SIZE;
            if (scrollContainer) scrollContainer.scrollTop = 0;
            renderCatalog();
        });
    });

    // Search input
    document.getElementById('supply-search-input')?.addEventListener('input', function() {
        currentLimit = BATCH_SIZE;
        renderCatalog();
    });

    // Initial render
    renderCatalog();
</script>
@endpush
