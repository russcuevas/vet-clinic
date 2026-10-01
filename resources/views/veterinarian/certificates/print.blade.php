<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veterinary Health Certificate - {{ $certificate->control_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,600;0,700;0,800;1,400;1,700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #525659;
            color: #000;
            font-family: Arial, Helvetica, 'Plus Jakarta Sans', sans-serif;
            font-size: 10.8pt;
            line-height: 1.42;
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
            padding: 14mm 20mm 15mm 20mm;
            box-shadow: 0 0 15px rgba(0,0,0,0.4);
            position: relative;
        }

        /* Clinic Header */
        .clinic-header {
            text-align: center;
            margin-bottom: 6px;
        }

        .clinic-logo-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 4px;
        }

        .clinic-logo {
            max-height: 75px;
            max-width: 320px;
            object-fit: contain;
        }

        .clinic-contact-info {
            font-size: 8.8pt;
            color: #000;
            line-height: 1.35;
            text-align: center;
        }

        /* Double border separator matching original certificate */
        .header-divider {
            border-top: 2px solid #000;
            border-bottom: 1px solid #000;
            height: 3px;
            margin: 6px 0 12px 0;
        }

        /* Certificate Title & Control # */
        .cert-title-block {
            text-align: center;
            margin-bottom: 12px;
        }

        .cert-title {
            font-size: 16pt;
            font-weight: 800;
            text-transform: capitalize;
            color: #000;
            letter-spacing: 0.2px;
            margin-bottom: 2px;
        }

        .control-number {
            text-align: right;
            font-size: 10.5pt;
            font-weight: 700;
            color: #000;
            margin-top: 2px;
        }

        .control-number span {
            color: #b91c1c;
            font-weight: 800;
        }

        /* Content Paragraphs */
        .cert-date {
            font-size: 10.8pt;
            margin-bottom: 12px;
        }

        .salutation {
            font-size: 10.8pt;
            font-weight: 700;
            margin-bottom: 3px;
        }

        .cert-text {
            font-size: 10.8pt;
            text-align: justify;
            line-height: 1.42;
            margin-bottom: 10px;
        }

        /* Key-Value Tables */
        .info-table {
            width: 100%;
            margin-left: 24px;
            margin-bottom: 12px;
            font-size: 10.8pt;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 2px 0;
            vertical-align: top;
        }

        .info-table .key-col {
            width: 125px;
            font-weight: 700;
            color: #000;
        }

        .info-table .sep-col {
            width: 16px;
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
            font-size: 10.8pt;
            font-weight: 800;
            margin-top: 8px;
            margin-bottom: 3px;
        }

        .desc-table {
            width: 100%;
            margin-left: 24px;
            margin-bottom: 12px;
            font-size: 10.8pt;
            border-collapse: collapse;
        }

        .desc-table td {
            padding: 2px 0;
            vertical-align: top;
        }

        .desc-table .key-col {
            width: 125px;
            color: #000;
        }

        .desc-table .sep-col {
            width: 16px;
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
            font-size: 10.8pt;
            line-height: 1.42;
            text-align: justify;
            margin-top: 8px;
            margin-bottom: 16px;
        }

        .underline-bold {
            font-weight: 800;
            text-decoration: underline;
        }

        /* Signature Block */
        .signature-section {
            display: flex;
            justify-content: flex-end;
            margin-top: 18px;
            page-break-inside: avoid;
        }

        .signature-box {
            width: 250px;
            text-align: center;
            position: relative;
        }

        .vet-sig-line {
            border-top: 1px solid #000;
            padding-top: 4px;
            margin-top: 40px;
        }

        .vet-name {
            font-size: 9.5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.15px;
            color: #000;
            white-space: nowrap;
        }

        .vet-title {
            font-size: 9pt;
            color: #000;
            margin-bottom: 2px;
        }

        .vet-details {
            font-size: 8.8pt;
            text-align: right;
            color: #000;
            line-height: 1.3;
            margin-top: 2px;
            padding-right: 4px;
        }

        .vet-details strong {
            text-decoration: underline;
        }

        @page {
            size: A4 portrait;
            margin: 10mm 15mm 10mm 15mm;
        }

        @media print {
            html, body {
                background: #fff !important;
                padding: 0 !important;
                margin: 0 !important;
                font-size: 10.4pt !important;
                line-height: 1.38 !important;
            }

            .action-bar {
                display: none !important;
            }

            .page-container {
                box-shadow: none !important;
                margin: 0 !important;
                width: 100% !important;
                min-height: auto !important;
                height: auto !important;
                padding: 0 !important;
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
                border: none !important;
            }

            .clinic-logo {
                max-height: 75px !important;
            }

            * {
                page-break-inside: avoid !important;
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
        <div class="cert-title-block">
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
