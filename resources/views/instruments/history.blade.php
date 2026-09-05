@extends('layouts.app')

@php
    $roleName = ucfirst(str_replace('_', ' ', auth()->user()->role));
    $title = 'Restock Audit Trail & History';
    $headerTitle = 'Restock History & Audit Logs';
    $breadcrumb = 'Inventory / Instruments / Restock History';

    $isRestockRoute = match(auth()->user()->role) {
        'inventory_officer' => 'inventory_officer.instruments.',
        'back_office' => 'back_office.instruments.',
        default => 'admin.instruments.',
    };
@endphp

@section('content')
    <!-- Action Toolbar & Filters -->
    <div class="table-toolbar">
        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            <a href="{{ route($isRestockRoute . 'index') }}" class="btn btn-navy" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>&larr; Back to Instruments Inventory</span>
            </a>
        </div>

        <div style="font-size: 0.82rem; color: var(--text-muted);">
            Tracking all stock additions with user accountability
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body" style="padding: 1.25rem 1.5rem;">
            <form method="GET" action="{{ route($isRestockRoute . 'history') }}">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: flex-end;">
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.78rem;">Filter by Instrument</label>
                        <select name="instrument_id" class="form-control">
                            <option value="">All Instruments</option>
                            @foreach($instruments as $ins)
                                <option value="{{ $ins->id }}" {{ request('instrument_id') == $ins->id ? 'selected' : '' }}>
                                    {{ $ins->name }} ({{ $ins->item_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.78rem;">Restocked By (Staff User)</label>
                        <select name="user_id" class="form-control">
                            <option value="">All Staff / Users</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ ucfirst(str_replace('_', ' ', $u->role)) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.78rem;">Action Type</label>
                        <select name="action_type" class="form-control">
                            <option value="">All Actions</option>
                            <option value="restock" {{ request('action_type') === 'restock' ? 'selected' : '' }}>Restock Replenishment</option>
                            <option value="initial_stock" {{ request('action_type') === 'initial_stock' ? 'selected' : '' }}>Initial Stock Registration</option>
                            <option value="adjustment" {{ request('action_type') === 'adjustment' ? 'selected' : '' }}>Manual Adjustment</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.78rem;">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.78rem;">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>

                    <div style="display: flex; gap: 0.5rem;">
                        <button type="submit" class="btn btn-gold" style="flex: 1;">Filter</button>
                        <a href="{{ route($isRestockRoute . 'history') }}" class="btn btn-ghost" title="Clear filter">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Restock Audit Log Table Card -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="card-title-group">
                <h3 class="card-title">Restock Audit Log & Activity Trail</h3>
                <span class="card-subtitle">Detailed record showing who restocked each instrument and resulting stock levels</span>
            </div>
            <span class="badge badge-gold">{{ $logs->total() }} Log Entries</span>
        </div>

        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Instrument Item</th>
                            <th>Restocked By (User Account)</th>
                            <th>Action Type</th>
                            <th>Quantity Added</th>
                            <th>Stock Count (Before &rarr; After)</th>
                            <th>Remarks / Batch Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>
                                    <div style="font-weight: 600; color: var(--white); font-size: 0.85rem;">
                                        {{ $log->created_at->format('M d, Y') }}
                                    </div>
                                    <div style="font-size: 0.72rem; color: var(--gold-light);">
                                        {{ $log->created_at->format('h:i:s A') }}
                                    </div>
                                    <div style="font-size: 0.68rem; color: var(--text-muted);">
                                        {{ $log->created_at->diffForHumans() }}
                                    </div>
                                </td>
                                <td>
                                    @if($log->instrument)
                                        <div style="font-weight: 700; color: var(--white); font-size: 0.92rem;">
                                            {{ $log->instrument->name }}
                                        </div>
                                        <div style="display: flex; gap: 0.4rem; align-items: center; margin-top: 2px;">
                                            <span style="font-family: monospace; font-size: 0.75rem; color: var(--gold-light);">
                                                {{ $log->instrument->item_code }}
                                            </span>
                                            <span class="badge badge-navy" style="font-size: 0.65rem; padding: 2px 6px;">
                                                {{ ucfirst(str_replace('_', ' ', $log->instrument->category)) }}
                                            </span>
                                        </div>
                                    @else
                                        <span style="color: var(--danger); font-style: italic;">Deleted Instrument</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->restockedBy)
                                        <div style="display: flex; align-items: center; gap: 0.65rem;">
                                            <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--navy-surface); border: 1px solid var(--gold-border); display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 700; color: var(--gold-light);">
                                                {{ strtoupper(substr($log->restockedBy->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div style="font-weight: 700; color: var(--white); font-size: 0.88rem;">
                                                    {{ $log->restockedBy->name }}
                                                </div>
                                                <div>
                                                    @if($log->restockedBy->role === 'inventory_officer')
                                                        <span class="badge badge-gold" style="font-size: 0.65rem;">📦 Inventory Officer</span>
                                                    @elseif($log->restockedBy->role === 'back_office')
                                                        <span class="badge badge-purple" style="font-size: 0.65rem;">🏢 Back Office</span>
                                                    @elseif($log->restockedBy->role === 'admin')
                                                        <span class="badge badge-blue" style="font-size: 0.65rem;">👑 Admin</span>
                                                    @else
                                                        <span class="badge badge-navy" style="font-size: 0.65rem;">{{ ucfirst(str_replace('_', ' ', $log->restockedBy->role)) }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span style="color: var(--text-muted); font-size: 0.82rem;">System Automated</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->action_type === 'initial_stock')
                                        <span class="badge badge-navy" style="font-size: 0.72rem; color: var(--gold-light);">
                                            Initial Stock
                                        </span>
                                    @elseif($log->action_type === 'restock')
                                        <span class="badge badge-success" style="font-size: 0.72rem;">
                                            Restock
                                        </span>
                                    @else
                                        <span class="badge" style="background: rgba(148, 163, 184, 0.2); color: #cbd5e1; font-size: 0.72rem;">
                                            {{ ucfirst($log->action_type) }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <strong style="color: #10b981; font-size: 1rem;">
                                        +{{ number_format($log->quantity_added) }}
                                    </strong>
                                    <span style="font-size: 0.78rem; color: var(--text-secondary);">
                                        {{ $log->instrument->unit ?? 'units' }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-family: monospace; font-size: 0.88rem;">
                                        <span style="color: var(--text-muted);">{{ $log->quantity_before }}</span>
                                        <span style="color: var(--gold-light); margin: 0 4px;">&rarr;</span>
                                        <strong style="color: var(--white);">{{ $log->quantity_after }}</strong>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size: 0.82rem; color: var(--text-secondary); max-width: 250px;">
                                        {{ $log->remarks ?: '-' }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
                                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">📜</div>
                                    <div style="font-weight: 600; color: var(--white);">No restock history logs found</div>
                                    <p style="font-size: 0.82rem; margin-top: 0.25rem;">Logs will be automatically recorded when staff add new instruments or restock supplies.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div style="padding: 1.25rem 1.5rem; border-top: 1px solid var(--black-border);">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
