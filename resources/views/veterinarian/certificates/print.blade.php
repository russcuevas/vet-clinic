<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veterinary Health Certificate - {{ $certificate->control_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,600;0,700;0,800;1,400;1,700&family=Great+Vibes&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #525659;
            color: #000;
            font-family: 'Times New Roman', Times, serif, 'Plus Jakarta Sans', sans-serif;
            font-size: 13.5pt;
            line-height: 1.5;
            display: flex;
            justify-content: center;
            padding: 20px 0;
        }

        .action-bar {
            position: fixed;
            top: 15px;
            right: 20px;
            display: flex;
            gap: 10px;
            z-index: 9999;
        }

        .btn-action {
            background: #1e293b;
            color: #fff;
            border: 1px solid #475569;
            padding: 8px 18px;
            border-radius: 6px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }

        .btn-print {
            background: #d4af37;
            color: #000;
            border: none;
        }

        .page-container {
            background: #fff;
            width: 210mm;
            min-height: 297mm;
            padding: 18mm 20mm 20mm 20mm;
            box-shadow: 0 0 15px rgba(0,0,0,0.4);
            position: relative;
        }

        /* Clinic Header */
        .clinic-header {
            text-align: center;
            margin-bottom: 8px;
        }

        .clinic-logo-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 4px;
        }

        .clinic-logo {
            max-height: 85px;
            max-width: 320px;
            object-fit: contain;
        }

        .clinic-brand-title {
            font-size: 20pt;
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: -0.5px;
            font-family: Arial, Helvetica, sans-serif;
            margin-bottom: 2px;
        }

        .clinic-brand-subtitle {
            font-size: 11pt;
            font-weight: 700;
            color: #0f172a;
            font-family: Arial, Helvetica, sans-serif;
            margin-bottom: 4px;
        }

        .clinic-contact-info {
            font-size: 8.8pt;
            color: #000;
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.35;
        }

        /* Double border separator */
        .header-divider {
            border-top: 2px solid #000;
            border-bottom: 1px solid #000;
            height: 3px;
            margin: 8px 0 16px 0;
        }

        /* Certificate Title & Control # */
        .cert-title-container {
            position: relative;
            text-align: center;
            margin-bottom: 16px;
        }

        .cert-title {
            font-size: 17pt;
            font-weight: 800;
            text-transform: capitalize;
            color: #000;
            letter-spacing: 0.2px;
            display: inline-block;
        }

        .control-number {
            position: absolute;
            right: 0;
            top: 2px;
            font-size: 10.5pt;
            font-weight: 700;
            color: #000;
            font-family: Arial, Helvetica, sans-serif;
        }

        .control-number span {
            color: #b91c1c;
            font-weight: 800;
        }

        /* Content Paragraphs */
        .cert-date {
            font-size: 11pt;
            margin-bottom: 12px;
            font-family: Arial, Helvetica, sans-serif;
        }

        .salutation {
            font-size: 11pt;
            font-weight: 700;
            margin-bottom: 4px;
            font-family: Arial, Helvetica, sans-serif;
        }

        .cert-text {
            font-size: 11pt;
            text-align: justify;
            line-height: 1.45;
            margin-bottom: 10px;
            font-family: Arial, Helvetica, sans-serif;
        }

        /* Key-Value Tables */
        .info-table {
            width: 100%;
            margin-left: 20px;
            margin-bottom: 12px;
            font-size: 10.8pt;
            font-family: Arial, Helvetica, sans-serif;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 2px 0;
            vertical-align: top;
        }

        .info-table .key-col {
            width: 130px;
            font-weight: 700;
            color: #000;
        }

        .info-table .sep-col {
            width: 18px;
            text-align: center;
            font-weight: 700;
        }

        .info-table .val-col {
            font-weight: 700;
            text-decoration: underline;
            color: #000;
        }

        /* Description Section */
        .section-header {
            font-size: 11pt;
            font-weight: 800;
            margin-top: 10px;
            margin-bottom: 4px;
            font-family: Arial, Helvetica, sans-serif;
        }

        .desc-table {
            width: 100%;
            margin-left: 20px;
            margin-bottom: 14px;
            font-size: 10.8pt;
            font-family: Arial, Helvetica, sans-serif;
            border-collapse: collapse;
        }

        .desc-table td {
            padding: 2px 0;
            vertical-align: top;
        }

        .desc-table .key-col {
            width: 130px;
            color: #000;
        }

        .desc-table .sep-col {
            width: 18px;
            text-align: center;
            font-weight: 700;
        }

        .desc-table .val-col {
            font-weight: 700;
            text-decoration: underline;
            color: #000;
        }

        /* Rabies Clause */
        .rabies-clause {
            font-size: 11pt;
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.5;
            text-align: justify;
            margin-top: 10px;
            margin-bottom: 25px;
        }

        .underline-bold {
            font-weight: 800;
            text-decoration: underline;
        }

        /* Signature Block */
        .signature-section {
            display: flex;
            justify-content: flex-end;
            margin-top: 25px;
            page-break-inside: avoid;
        }

        .signature-box {
            width: 270px;
            text-align: center;
            position: relative;
        }

        .vet-sig-img {
            position: absolute;
            top: -45px;
            left: 20px;
            height: 90px;
            width: auto;
            opacity: 0.85;
            pointer-events: none;
        }

        .vet-sig-line {
            border-top: 1px solid #000;
            padding-top: 4px;
            margin-top: 35px;
        }

        .vet-name {
            font-size: 11.5pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            color: #000;
            font-family: Arial, Helvetica, sans-serif;
        }

        .vet-title {
            font-size: 10pt;
            color: #000;
            font-family: Arial, Helvetica, sans-serif;
            margin-bottom: 2px;
        }

        .vet-details {
            font-size: 9pt;
            text-align: right;
            font-family: Arial, Helvetica, sans-serif;
            color: #000;
            line-height: 1.35;
            margin-top: 2px;
            padding-right: 5px;
        }

        .vet-details strong {
            text-decoration: underline;
        }

        @media print {
            body {
                background: #fff !important;
                padding: 0 !important;
            }

            .action-bar {
                display: none !important;
            }

            .page-container {
                box-shadow: none !important;
                margin: 0 !important;
                width: 100% !important;
                min-height: auto !important;
                padding: 10mm 15mm 15mm 15mm !important;
            }

            @page {
                size: A4 portrait;
                margin: 10mm;
            }
        }
    </style>
</head>
<body>

    <!-- Action Toolbar (Hidden during print) -->
    <div class="action-bar">
        <a href="{{ route('vet.certificates.index') }}" class="btn-action">← Back to List</a>
        <button onclick="window.print()" class="btn-action btn-print">🖨️ Print Certificate</button>
    </div>

    <div class="page-container">
        <!-- Clinic Letterhead Header -->
        <div class="clinic-header">
            <div class="clinic-logo-wrapper">
                <img src="{{ asset('uploads/logos/logo.png') }}" alt="Pets Best Logo" class="clinic-logo" onerror="this.style.display='none'">
            </div>
            <div class="clinic-contact-info">
                Address: 2261 V. Rama Avenue, Guadalupe, Cebu City | Contact no. 0928 5953 780 or 0966 910 2846<br>
                Facebook Page: PetsBest Pet Supplies and Veterinary Services
            </div>
        </div>

        <!-- Double Divider Line -->
        <div class="header-divider"></div>

        <!-- Certificate Title & Sequential Control Number -->
        <div class="cert-title-container">
            <h1 class="cert-title">Veterinary Health Certificate</h1>
            <div class="control-number">
                Control Number: <span>{{ $certificate->control_number }}</span>
            </div>
        </div>

        <!-- Active Date -->
        <div class="cert-date">
            Date: <strong>{{ $certificate->certificate_date->format('F d, Y') }}</strong>
        </div>

        <!-- Statement Intro -->
        <div class="salutation">TO WHOM IT MAY CONCERN:</div>
        <div class="cert-text">
            This is to certify that I have examined on this date, the dog/cat described below:
        </div>

        <!-- Owner Information Table -->
        <table class="info-table">
            <tr>
                <td class="key-col">Owned by</td>
                <td class="sep-col">:</td>
                <td class="val-col">{{ $certificate->owner_name }}</td>
            </tr>
            <tr>
                <td class="key-col">Residing At</td>
                <td class="sep-col">:</td>
                <td class="val-col">{{ $certificate->residing_at }}</td>
            </tr>
            <tr>
                <td class="key-col">Contact #</td>
                <td class="sep-col">:</td>
                <td class="val-col">{{ $certificate->contact_number ?: 'N/A' }}</td>
            </tr>
            <tr>
                <td class="key-col">Destination</td>
                <td class="sep-col">:</td>
                <td class="val-col">{{ $certificate->destination }}</td>
            </tr>
        </table>

        <!-- Examination Certification Statement -->
        <div class="cert-text">
            And to the best of my knowledge and ability to determine with the procedures used, find the dog/cat at the time of the examination to be apparently free from evidence of dangerous communicable animal disease.
        </div>

        <!-- Pet Description Section -->
        <div class="section-header">DESCRIPTION:</div>
        <table class="desc-table">
            <tr>
                <td class="key-col">Name of pet</td>
                <td class="sep-col">:</td>
                <td class="val-col">{{ $certificate->pet_name }}</td>
            </tr>
            <tr>
                <td class="key-col">Species</td>
                <td class="sep-col">:</td>
                <td class="val-col">{{ $certificate->species }}</td>
            </tr>
            <tr>
                <td class="key-col">Breed</td>
                <td class="sep-col">:</td>
                <td class="val-col">{{ $certificate->breed ?: 'N/A' }}</td>
            </tr>
            <tr>
                <td class="key-col">Color</td>
                <td class="sep-col">:</td>
                <td class="val-col">{{ $certificate->color ?: 'N/A' }}</td>
            </tr>
            <tr>
                <td class="key-col">Sex</td>
                <td class="sep-col">:</td>
                <td class="val-col">{{ $certificate->sex ?: 'N/A' }}</td>
            </tr>
            <tr>
                <td class="key-col">Date of birth</td>
                <td class="sep-col">:</td>
                <td class="val-col">{{ $certificate->birth_date ? $certificate->birth_date->format('F d, Y') : ($certificate->pet && $certificate->pet->birth_date ? $certificate->pet->birth_date->format('F d, Y') : 'N/A') }}</td>
            </tr>
            <tr>
                <td class="key-col">Age</td>
                <td class="sep-col">:</td>
                <td class="val-col">{{ $certificate->age ?: 'N/A' }}</td>
            </tr>
            <tr>
                <td class="key-col">Weight</td>
                <td class="sep-col">:</td>
                <td class="val-col">{{ $certificate->weight ?: 'N/A' }}</td>
            </tr>
            <tr>
                <td class="key-col">Microchip</td>
                <td class="sep-col">:</td>
                <td class="val-col">{{ $certificate->microchip ?: 'None' }}</td>
            </tr>
        </table>

        <!-- Rabies Vaccination Certification Clause -->
        <div class="rabies-clause">
            This further certifies that the above-described dog/cat was vaccinated against Rabies on 
            <span class="underline-bold">{{ $certificate->rabies_vaccination_date ? $certificate->rabies_vaccination_date->format('d F Y') : 'N/A' }}</span> 
            using <span class="underline-bold">{{ $certificate->rabies_vaccine_name ?: 'Rabisin' }}</span> 
            with Serial / Lot <span class="underline-bold">{{ $certificate->rabies_lot_number ?: 'N/A' }}</span>.
        </div>

        <!-- Signature Block -->
        <div class="signature-section">
            <div class="signature-box">
                <!-- Decorative signature line -->
                <svg class="vet-sig-img" viewBox="0 0 200 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10 40 Q 30 10, 60 30 T 110 20 T 150 40 T 190 25" stroke="#1e293b" stroke-width="2" fill="none" stroke-linecap="round"/>
                    <path d="M40 50 Q 70 15, 120 45 T 180 30" stroke="#1e293b" stroke-width="1.5" fill="none"/>
                </svg>

                <div class="vet-sig-line"></div>
                <div class="vet-name">{{ $certificate->veterinarian_name }}</div>
                <div class="vet-title">Veterinarian</div>
                <div class="vet-details">
                    TIN <strong>{{ $certificate->tin_no ?: '331-645-364' }}</strong><br>
                    PTR <strong>{{ $certificate->ptr_no ?: '1601213' }}</strong><br>
                    PRC <strong>{{ $certificate->prc_no ?: '0009324' }}</strong><br>
                    Expiry Date <strong>{{ $certificate->license_expiry_date ? $certificate->license_expiry_date->format('m/d/Y') : '12/24/2026' }}</strong>
                </div>
            </div>
        </div>
    </div>

    @if(session('auto_print'))
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => window.print(), 500);
            });
        </script>
    @endif
</body>
</html>
