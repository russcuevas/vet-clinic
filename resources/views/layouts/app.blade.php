<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>San Modesto Vet Clinic - {{ $title ?? 'Management System' }}</title>

    <!-- Google Fonts (Readable Modern Typography) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,400&display=swap"
        rel="stylesheet">

    <!-- Modular CSS Files in public/css/ (Clean separation) -->
    <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/datatables.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/modal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/toast.css') }}">
    <link rel="stylesheet" href="{{ asset('css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/select2-custom.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/footer-animation.css') }}?v={{ time() }}">

    @stack('styles')

    <!-- Flash Messages via Meta Tags for Top-Right Toast Notifications -->
    @if (session('success'))
        <meta name="flash-success" content="{{ session('success') }}">
    @endif
    @if (session('error'))
        <meta name="flash-error" content="{{ session('error') }}">
    @endif
    @if (session('warning'))
        <meta name="flash-warning" content="{{ session('warning') }}">
    @endif
</head>

<body>
    <div class="app-wrapper">
        <!-- Responsive Sidebar Overlay for Mobile/Tablet -->
        <div class="sidebar-overlay"></div>

        <!-- Sidebar Navigation -->
        <aside class="app-sidebar">
            <!-- Blank Logo Layout Slot -->
            <div class="sidebar-brand-slot">
                <x-logo size="normal" />
            </div>

            <!-- Role Badge -->
            <!-- Role Badge & Admin Portal Switcher -->
            @auth
                <div class="sidebar-role-badge">
                    <span class="role-badge-title">Portal Role</span>
                    <span class="role-badge-val">{{ auth()->user()->role }}</span>
                </div>

                @if (auth()->user()->role === 'admin')
                    @php
                        $isPayrollPortal = request()->routeIs('admin.payroll.*');
                    @endphp
                    <!-- Admin Mode Switcher -->
                    <div style="padding: 0.5rem 1rem; margin-bottom: 0.5rem;">
                        <div
                            style="background: rgba(0, 0, 0, 0.25); border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 9999px; padding: 3px; display: flex; gap: 3px;">
                            <a href="{{ route('admin.dashboard') }}"
                                style="flex: 1; text-align: center; font-size: 0.72rem; font-weight: 700; padding: 6px 6px; border-radius: 9999px; text-decoration: none; transition: all 0.2s; {{ !$isPayrollPortal ? 'background: #d4af37; color: #111; box-shadow: 0 2px 6px rgba(212, 175, 55, 0.4);' : 'color: rgba(255, 255, 255, 0.7);' }}">
                                🐾 Clinic
                            </a>
                            <a href="{{ route('admin.payroll.dashboard') }}"
                                style="flex: 1; text-align: center; font-size: 0.72rem; font-weight: 700; padding: 6px 6px; border-radius: 9999px; text-decoration: none; transition: all 0.2s; {{ $isPayrollPortal ? 'background: #2563eb; color: #fff; box-shadow: 0 2px 8px rgba(37, 99, 235, 0.4);' : 'color: rgba(255, 255, 255, 0.7);' }}">
                                💼 Payroll
                            </a>
                        </div>
                    </div>
                @endif
            @endauth

            <!-- Navigation Links Based on Role -->
            <nav class="sidebar-nav">
                @auth
                    @if (auth()->user()->role === 'admin')
                        @if (request()->routeIs('admin.payroll.*'))
                            <!-- Payroll Portal Navigation (Admin Only) -->
                            <div class="nav-section-title">Payroll Command</div>
                            <a href="{{ route('admin.payroll.dashboard') }}"
                                class="nav-link-item {{ request()->routeIs('admin.payroll.dashboard') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                <span>Payroll Dashboard</span>
                            </a>
                            <a href="{{ route('admin.payroll.employees.index') }}"
                                class="nav-link-item {{ request()->routeIs('admin.payroll.employees.*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span>Employee Database</span>
                            </a>
                            <a href="{{ route('admin.payroll.dtr.index') }}"
                                class="nav-link-item {{ request()->routeIs('admin.payroll.dtr.*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Timekeeping (DTR)</span>
                            </a>

                            <div class="nav-section-title">Leaves & Deductions</div>
                            <a href="{{ route('admin.payroll.leaves.index') }}"
                                class="nav-link-item {{ request()->routeIs('admin.payroll.leaves.*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Leave Applications</span>
                            </a>
                            <a href="{{ route('admin.payroll.deductions.index') }}"
                                class="nav-link-item {{ request()->routeIs('admin.payroll.deductions.*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Financial & Deductions</span>
                            </a>
                            <a href="{{ route('admin.payroll.incentives.index') }}"
                                class="nav-link-item {{ request()->routeIs('admin.payroll.incentives.*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                                <span>Role Incentives</span>
                            </a>

                            <div class="nav-section-title">Payroll Processing</div>
                            <a href="{{ route('admin.payroll.periods.index') }}"
                                class="nav-link-item {{ request()->routeIs('admin.payroll.periods.*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <span>15 & 30-Day Payroll</span>
                            </a>
                            <a href="{{ route('admin.payroll.annual') }}"
                                class="nav-link-item {{ request()->routeIs('admin.payroll.annual') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Annual Records</span>
                            </a>
                        @else
                            <!-- Admin Navigation (Full Clinic Access) -->
                            <div class="nav-section-title">Core Management</div>
                            <a href="{{ route('admin.dashboard') }}"
                                class="nav-link-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                <span>Dashboard</span>
                            </a>
                            <a href="{{ route('admin.clients.index') }}"
                                class="nav-link-item {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span>Clients & Pets</span>
                            </a>
                            <a href="{{ route('receptionist.appointments.index') }}"
                                class="nav-link-item {{ request()->routeIs('receptionist.appointments.*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Appointments & Bookings</span>
                            </a>

                            <div class="nav-section-title">Clinic Services</div>
                            <a href="{{ route('admin.veterinary.index') }}"
                                class="nav-link-item {{ request()->routeIs('admin.veterinary.index') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>Veterinary Services</span>
                            </a>
                            <a href="{{ route('admin.veterinary.followups') }}"
                                class="nav-link-item {{ request()->routeIs('admin.veterinary.followups') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Incoming Follow-ups</span>
                            </a>
                            <a href="{{ route('admin.grooming.index') }}"
                                class="nav-link-item {{ request()->routeIs('admin.grooming.*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879a3 3 0 11-4.242-4.242L10.758 7.758a3 3 0 014.242 4.242z" />
                                </svg>
                                <span>Grooming</span>
                            </a>
                            <a href="{{ route('admin.supplies.index') }}"
                                class="nav-link-item {{ request()->routeIs('admin.supplies.*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <span>Pet Supplies POS</span>
                            </a>

                            <div class="nav-section-title">Finance & Stock</div>
                            <a href="{{ route('admin.inventory.index') }}"
                                class="nav-link-item {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                <span>POS Supplies Stock</span>
                            </a>
                            <a href="{{ route('admin.instruments.index') }}"
                                class="nav-link-item {{ request()->routeIs('admin.instruments.index') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                </svg>
                                <span>Instruments Inventory</span>
                            </a>
                            <a href="{{ route('admin.instruments.history') }}"
                                class="nav-link-item {{ request()->routeIs('admin.instruments.history') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Restock Audit Trail</span>
                            </a>
                            <a href="{{ route('admin.billing.index') }}"
                                class="nav-link-item {{ request()->routeIs('admin.billing.*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <span>Billing Database</span>
                            </a>
                            <a href="{{ route('admin.reports.sales') }}"
                                class="nav-link-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                <span>Sales Reports</span>
                            </a>

                            <div class="nav-section-title">Administration</div>
                            <a href="{{ route('admin.users.index') }}"
                                class="nav-link-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <span>Account Management</span>
                            </a>
                        @endif
                    @elseif(auth()->user()->role === 'cashier')
                        <!-- Cashier Navigation -->
                        <div class="nav-section-title">Cashier Desk</div>
                        <a href="{{ route('cashier.dashboard') }}"
                            class="nav-link-item {{ request()->routeIs('cashier.dashboard') ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <span>Dashboard</span>
                        </a>
                        <a href="{{ route('cashier.billing.index') }}"
                            class="nav-link-item {{ request()->routeIs('cashier.billing.*') ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span>Checkout & Billing</span>
                        </a>
                        <a href="{{ route('cashier.pos.index') }}"
                            class="nav-link-item {{ request()->routeIs('cashier.pos.*') ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            <span>Pet Supplies POS</span>
                        </a>
                        <a href="{{ route('cashier.grooming.index') }}"
                            class="nav-link-item {{ request()->routeIs('cashier.grooming.*') ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879a3 3 0 11-4.242-4.242L10.758 7.758a3 3 0 014.242 4.242z" />
                            </svg>
                            <span>Grooming Services</span>
                        </a>
                    @elseif(auth()->user()->role === 'veterinarian')
                        <!-- Veterinarian Navigation -->
                        <div class="nav-section-title">Veterinary Clinic</div>
                        <a href="{{ route('vet.dashboard') }}"
                            class="nav-link-item {{ request()->routeIs('vet.dashboard') ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <span>Dashboard</span>
                        </a>
                        <a href="{{ route('vet.medical.index') }}"
                            class="nav-link-item {{ request()->routeIs('vet.medical.index') ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Medical Records</span>
                        </a>
                        <a href="{{ route('vet.followups.index') }}"
                            class="nav-link-item {{ request()->routeIs('vet.followups.*') ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>Incoming Follow-ups</span>
                        </a>
                        <a href="{{ route('vet.clients.index') }}"
                            class="nav-link-item {{ request()->routeIs('vet.clients.*') ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span>Clients & Pet History</span>
                        </a>
                        <a href="{{ route('vet.grooming.index') }}"
                            class="nav-link-item {{ request()->routeIs('vet.grooming.*') ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879a3 3 0 11-4.242-4.242L10.758 7.758a3 3 0 014.242 4.242z" />
                            </svg>
                            <span>Grooming</span>
                        </a>
                    @elseif(auth()->user()->role === 'manager')
                        <!-- Manager Navigation -->
                        <div class="nav-section-title">Manager Center</div>
                        <a href="{{ route('manager.dashboard') }}"
                            class="nav-link-item {{ request()->routeIs('manager.dashboard') ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <span>Dashboard</span>
                        </a>
                        <a href="{{ route('manager.reports.sales') }}"
                            class="nav-link-item {{ request()->routeIs('manager.reports.*') ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <span>Sales Reports</span>
                        </a>
                        <a href="{{ route('manager.inventory.audit') }}"
                            class="nav-link-item {{ request()->routeIs('manager.inventory.*') ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Inventory Valuation</span>
                        </a>

                        <div class="nav-section-title">Staff Management</div>
                        <a href="{{ route('manager.leaves.index') }}"
                            class="nav-link-item {{ request()->routeIs('manager.leaves.*') ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>Leave Applications</span>
                        </a>
                    @elseif(auth()->user()->role === 'inventory_officer')
                        <!-- Inventory Officer Navigation -->
                        <div class="nav-section-title">Stock Command</div>
                        <a href="{{ route('inventory_officer.instruments.index') }}"
                            class="nav-link-item {{ request()->routeIs('inventory_officer.instruments.index') ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                            <span>Instruments Catalog</span>
                        </a>
                        <a href="{{ route('inventory_officer.instruments.history') }}"
                            class="nav-link-item {{ request()->routeIs('inventory_officer.instruments.history') ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Restock Audit History</span>
                        </a>
                    @elseif(auth()->user()->role === 'back_office')
                        <!-- Back Office Navigation -->
                        <div class="nav-section-title">Back Office Control</div>
                        <a href="{{ route('back_office.instruments.index') }}"
                            class="nav-link-item {{ request()->routeIs('back_office.instruments.index') ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                            <span>Instruments Catalog</span>
                        </a>
                        <a href="{{ route('back_office.instruments.history') }}"
                            class="nav-link-item {{ request()->routeIs('back_office.instruments.history') ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Restock Audit History</span>
                        </a>
                    @elseif(auth()->user()->role === 'receptionist')
                        <!-- Receptionist Navigation -->
                        <div class="nav-section-title">Front Desk & Reception</div>
                        <a href="{{ route('receptionist.appointments.index') }}"
                            class="nav-link-item {{ request()->routeIs('receptionist.appointments.*') ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>Appointments Hub</span>
                        </a>

                        <div class="nav-section-title">Attendance & Timekeeping</div>
                        <a href="{{ route('receptionist.dtr.index') }}"
                            class="nav-link-item {{ request()->routeIs('receptionist.dtr.*') ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Time In / Time Out (DTR)</span>
                        </a>
                        <a href="{{ route('receptionist.leaves.index') }}"
                            class="nav-link-item {{ request()->routeIs('receptionist.leaves.*') ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>Leave Applications</span>
                        </a>
                    @endif
                @endauth
            </nav>

            <!-- Sidebar User Profile & Logout -->
            @auth
                <div class="sidebar-footer">
                    <div class="sidebar-user-info" data-modal-target="modal-update-profile"
                        title="Click to update your profile">
                        <div class="user-avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
                        <div class="user-details">
                            <span class="user-name"
                                title="{{ auth()->user()->name }}">{{ auth()->user()->name }}</span>
                            <span class="user-role-label">{{ ucfirst(auth()->user()->role) }} • Edit ✏️</span>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-logout" title="Sign Out">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            @endauth
        </aside>

        <!-- Main Workspace Area -->
        <main class="app-main">
            <!-- Topbar sticky header -->
            <header class="app-topbar">
                <div class="topbar-left">
                    <button type="button" class="sidebar-toggle-btn" id="sidebarToggleBtn"
                        aria-label="Toggle Sidebar Menu" title="Toggle Sidebar">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div class="page-headline">
                        <h1 class="page-title">{{ $headerTitle ?? 'San Modesto Vet Clinic' }}</h1>
                        <span class="page-breadcrumb">San Modesto Vet Clinic <span>/</span>
                            {{ $breadcrumb ?? 'Workspace' }}</span>
                    </div>
                </div>

                <div class="topbar-right" style="display: flex; align-items: center; gap: 0.75rem;">
                    @auth
                        @if (auth()->user()->role === 'admin')
                            @if (request()->routeIs('admin.payroll.*'))
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-gold"
                                    style="border-radius: 9999px; text-decoration: none; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; padding: 0.35rem 0.85rem;">
                                    <span>🐾 Switch to Vet Clinic</span>
                                </a>
                            @else
                                <a href="{{ route('admin.payroll.dashboard') }}" class="btn btn-sm"
                                    style="background: #1d4ed8; color: #fff; border: 1px solid #3b82f6; border-radius: 9999px; text-decoration: none; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; padding: 0.35rem 0.85rem; box-shadow: 0 2px 6px rgba(29, 78, 216, 0.3);">
                                    <span>💼 Switch to Payroll</span>
                                </a>
                            @endif
                        @endif
                    @endauth

                    <!-- Quick action buttons or date badge -->
                    <div class="badge badge-gold" style="font-weight: 600;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>{{ now()->format('M d, Y') }}</span>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <section class="page-container">
                {{ $slot ?? '' }}
                @yield('content')
            </section>

            <!-- Animated Walking Pet Footer -->
            <footer class="app-animated-footer">
                <div class="footer-pet-track">
                    <div class="walking-pet-wrapper" title="🐾 San Modestos Companion">
                        <img src="{{ asset('uploads/logos/footer.gif') }}" alt="San Modestos Companion"
                            class="walking-pet-gif">
                        <span class="pet-speech-bubble">🐾 Happy Pet, Happy Life!</span>
                    </div>
                    <div class="footer-walking-line"></div>
                </div>
                <div class="footer-info-bar">
                    <div>
                        <span class="footer-brand-title">San Modestos Veterinary Services</span>
                        <span style="color: var(--text-muted); margin: 0 0.5rem;">|</span>
                    </div>
                    <div>
                        <span>&copy; {{ date('Y') }} SMVC. All Rights Reserved.</span>
                    </div>
                </div>
            </footer>
        </main>
    </div>

    @auth
        <!-- ==================== SCROLLABLE MODAL: UPDATE PROFILE ==================== -->
        <div class="modal-backdrop" id="modal-update-profile">
            <div class="modal-dialog">
                <div class="modal-header">
                    <div class="modal-title-group">
                        <div class="modal-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="modal-title">My Profile Settings</h4>
                            <span style="font-size: 0.72rem; color: var(--gold-light);">Update your account details and
                                password</span>
                        </div>
                    </div>
                    <button type="button" class="modal-close-btn" data-modal-close>&times;</button>
                </div>
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <!-- Current User Role & Avatar Preview -->
                        <div
                            style="display: flex; align-items: center; gap: 1rem; background: var(--navy-dark); border: 1px solid var(--black-border); padding: 1rem; border-radius: var(--radius-sm); margin-bottom: 1.25rem;">
                            <div class="user-avatar" style="width: 48px; height: 48px; font-size: 1.2rem;">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <div>
                                <div style="font-size: 1rem; font-weight: 700; color: var(--white);">
                                    {{ auth()->user()->name }}</div>
                                <span class="badge badge-gold"
                                    style="font-size: 0.68rem; margin-top: 2px;">{{ strtoupper(auth()->user()->role) }}</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="profile_name">Full Name <span class="req">*</span></label>
                            <input type="text" name="name" id="profile_name" class="form-control"
                                value="{{ auth()->user()->name }}" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="profile_email">Email Address <span
                                    class="req">*</span></label>
                            <input type="email" name="email" id="profile_email" class="form-control"
                                value="{{ auth()->user()->email }}" required>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label" for="profile_contact">Contact Number</label>
                                <input type="text" name="contact_number" id="profile_contact" class="form-control"
                                    value="{{ auth()->user()->contact_number }}" placeholder="09XX-XXX-XXXX">
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="profile_license">License No. (PRC)</label>
                                <input type="text" name="license_no" id="profile_license" class="form-control"
                                    value="{{ auth()->user()->license_no }}" placeholder="PRC-VET-XXXXXX">
                            </div>
                        </div>

                        <div
                            style="background: rgba(11, 25, 44, 0.35); border: 1px dashed var(--gold-border); border-radius: var(--radius-sm); padding: 1rem; margin-top: 0.75rem;">
                            <h5
                                style="font-size: 0.78rem; text-transform: uppercase; color: var(--gold-primary); letter-spacing: 0.05em; margin-bottom: 0.75rem;">
                                🔒 Change Password (Optional)
                            </h5>
                            <div class="form-group">
                                <label class="form-label" for="profile_password">New Password</label>
                                <input type="password" name="password" id="profile_password" class="form-control"
                                    placeholder="Leave blank to keep current password" autocomplete="new-password">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" for="profile_password_confirmation">Confirm New Password</label>
                                <input type="password" name="password_confirmation" id="profile_password_confirmation"
                                    class="form-control" placeholder="Re-type new password" autocomplete="new-password">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                        <button type="submit" class="btn btn-gold">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    @endauth

    <!-- Container for Top-Right Alerts Toast Notifications -->
    <div class="toast-container"></div>

    <!-- Modular JS Files in public/js/ -->
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/select2.min.js') }}"></script>
    <script src="{{ asset('js/datatables.min.js') }}"></script>
    <script src="{{ asset('js/modal.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/toast.js') }}"></script>
    <script src="{{ asset('js/main.js') }}?v={{ time() }}"></script>

    @stack('scripts')
</body>

</html>
