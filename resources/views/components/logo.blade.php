@props(['size' => 'normal', 'showText' => true])

@php
    $logoPath = 'uploads/logos/logo-white.png';
    $hasCustomLogo = file_exists(public_path($logoPath));
    $dimensions = match ($size) {
        'lg' => 'max-height: 85px; max-width: 360px;',
        'sm' => 'max-height: 38px; max-width: 170px;',
        default => 'max-height: 52px; max-width: 235px;',
    };
@endphp

<div class="sidebar-brand-slot-inner"
    style="display: flex; align-items: center; justify-content: center; width: 100%; text-decoration: none;">
    @if ($hasCustomLogo)
        <div style="display: flex; align-items: center; justify-content: center; width: 100%; padding: 2px 0;">
            <img src="{{ asset($logoPath) }}" alt="San Modestos Veterinary Services"
                style="{{ $dimensions }} width: 100%; object-fit: contain; display: block; filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.8));">
        </div>
    @else
        <!-- Blank Layout for Clinic Logo (Reserved Slot) -->
        <div class="logo-blank-placeholder" style="width: 44px; height: 44px;"
            title="Reserved Logo Slot - Upload logo to public/uploads/logos/logo-white.png">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
        </div>
        @if ($showText)
            <div class="brand-text-group" style="margin-left: 0.75rem;">
                <span class="brand-name">San Modestos</span>
                <span class="brand-subtitle">Veterinary Services</span>
            </div>
        @endif
    @endif
</div>
