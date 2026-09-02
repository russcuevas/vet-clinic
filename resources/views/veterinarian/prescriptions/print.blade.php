<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>San Modesto Vet Clinic - Prescription {{ $prescription->prescription_code }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Playfair+Display:ital,wght@1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
    <style>
        body {
            background-color: #0c0e14;
            color: #fff;
            font-family: 'Plus Jakarta Sans', sans-serif;
            padding: 2.5rem;
            display: flex;
            justify-content: center;
        }

        .rx-card {
            background: #12151f;
            border: 1.5px solid var(--gold-border);
            border-radius: 12px;
            max-width: 680px;
            width: 100%;
            padding: 2.5rem 3rem;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.7);
            position: relative;
        }

        .rx-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
            border-bottom: 2px solid var(--gold-primary);
            padding-bottom: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .rx-brand-container {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            flex: 1;
        }

        .rx-logo-img {
            height: 75px;
            width: auto;
            max-width: 320px;
            object-fit: contain;
            display: block;
            filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.8));
        }

        .rx-meta-right {
            text-align: right;
            flex-shrink: 0;
        }

        .rx-title {
            color: var(--gold-primary);
            margin: 0;
            font-size: 1.25rem;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .rx-code {
            font-size: 0.95rem;
            font-weight: 800;
            color: #FFFFFF;
            margin-top: 3px;
        }

        .rx-date {
            font-size: 0.78rem;
            color: #94A3B8;
            margin-top: 2px;
        }

        .rx-symbol {
            font-family: 'Playfair Display', serif;
            font-size: 2.75rem;
            font-style: italic;
            color: var(--gold-primary);
            margin-bottom: 0.5rem;
            line-height: 1;
        }

        .rx-content {
            background: rgba(11, 25, 44, 0.4);
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: 8px;
            padding: 1.5rem;
            min-height: 220px;
            white-space: pre-wrap;
            font-size: 0.95rem;
            line-height: 1.7;
            color: #fff;
            margin-bottom: 1.5rem;
        }

        .signature-box {
            text-align: right;
            margin-top: 2.5rem;
            display: inline-block;
            float: right;
        }

        .signature-line {
            border-top: 1.5px solid var(--gold-primary);
            padding-top: 0.5rem;
        }

        @media print {
            body {
                background: #fff !important;
                color: #000 !important;
                padding: 0 !important;
            }
            .rx-card {
                background: #fff !important;
                color: #000 !important;
                border: 2px solid #000 !important;
                box-shadow: none !important;
                max-width: 100% !important;
                padding: 1.5rem 2rem !important;
            }
            .no-print {
                display: none !important;
            }
            .rx-header {
                border-bottom: 2px solid #000 !important;
            }
            .rx-title {
                color: #000 !important;
            }
            .rx-code {
                color: #000 !important;
            }
            .rx-date {
                color: #555 !important;
            }
            .rx-brand-container {
                background: #040609 !important;
                padding: 6px 14px !important;
                border-radius: 6px !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .rx-logo-img {
                height: 60px !important;
                filter: none !important;
            }
            .rx-symbol {
                color: #000 !important;
            }
            .rx-content {
                background: #fafafa !important;
                border: 1px solid #ddd !important;
                color: #000 !important;
            }
            .signature-line {
                border-top: 1.5px solid #000 !important;
            }
        }
    </style>
</head>
<body>
    <div class="rx-card">
        <div class="no-print" style="display: flex; justify-content: space-between; margin-bottom: 1.5rem;">
            <button onclick="window.close()" style="background: transparent; color: #94A3B8; border: 1px solid #232738; padding: 6px 14px; border-radius: 6px; cursor: pointer;">← Close</button>
            <button onclick="window.print()" style="background: var(--gold-gradient); color: #000; border: none; font-weight: 700; padding: 6px 16px; border-radius: 6px; cursor: pointer;">🖨️ Print Prescription</button>
        </div>

        <!-- Header with Clinic Info & Prominent Logo -->
        <div class="rx-header">
            <div class="rx-brand-container">
                <img src="{{ asset('uploads/logos/logo-white.png') }}" 
                     alt="San Modestos Veterinary Services"
                     class="rx-logo-img">
            </div>
            <div class="rx-meta-right">
                <h3 class="rx-title">PRESCRIPTION ORDER</h3>
                <div class="rx-code">#{{ $prescription->prescription_code }}</div>
                <div class="rx-date">Date: {{ $prescription->date_issued->format('F d, Y') }}</div>
            </div>
        </div>

        <!-- Flowchart Specified Patient Header:
             Display: Owner Name, Pet Name, Age, Species, Sex, Breed, Body Weight -->
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.85rem; background: rgba(11, 25, 44, 0.2); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.88rem; border: 1px solid #232738;">
            <div>
                <span style="color: #94A3B8; font-size: 0.75rem; text-transform: uppercase;">Owner's Name:</span>
                <div style="font-weight: 700; color: #fff;">{{ $prescription->owner->full_name ?? 'N/A' }}</div>
                <div style="font-size: 0.75rem; color: #94A3B8;">Key Code: {{ $prescription->owner->client_code ?? 'N/A' }}</div>
            </div>
            <div>
                <span style="color: #94A3B8; font-size: 0.75rem; text-transform: uppercase;">Pet Name:</span>
                <div style="font-weight: 700; color: #fff;">{{ $prescription->pet->name ?? 'N/A' }} ({{ $prescription->pet->pet_code ?? '' }})</div>
                <div style="font-size: 0.75rem; color: var(--gold-light);">{{ $prescription->pet->species ?? '' }} / {{ $prescription->pet->breed ?? '' }}</div>
            </div>
            <div>
                <span style="color: #94A3B8; font-size: 0.75rem; text-transform: uppercase;">Age & Sex:</span>
                <div>{{ $prescription->pet->age ?? 'N/A' }} | {{ $prescription->pet->sex ?? 'N/A' }}</div>
            </div>
            <div>
                <span style="color: #94A3B8; font-size: 0.75rem; text-transform: uppercase;">Body Weight:</span>
                <div style="font-weight: 700; color: #fff;">{{ $prescription->body_weight ?? ($prescription->pet->medicalRecords()->latest()->first()->body_weight ?? 'N/A') }}</div>
            </div>
        </div>

        <!-- Flowchart Specified: Rx: -->
        <div class="rx-symbol">℞</div>
        <div class="rx-content">{{ $prescription->rx_details }}</div>

        @if($prescription->instructions)
            <div style="margin-bottom: 1.5rem; font-size: 0.85rem; color: #94A3B8;">
                <strong style="color: #fff;">Care Instructions:</strong> {{ $prescription->instructions }}
            </div>
        @endif

        <!-- Flowchart Specified: Veterinarian Name on Duty & License No. -->
        <div class="doctor-signature-area">
            <div class="signature-line">
                <div style="font-weight: 700; font-size: 1rem; color: #fff;">{{ $prescription->veterinarian_name }}</div>
                <div style="font-size: 0.78rem; color: var(--gold-light);">Licensed Veterinarian on Duty</div>
                <div style="font-size: 0.78rem; color: #94A3B8;">License No: <strong>{{ $prescription->license_no ?? 'PRC-VET-009821' }}</strong></div>
            </div>
        </div>
    </div>
</body>
</html>
