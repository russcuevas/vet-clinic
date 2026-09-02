<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>San Modesto Vet Clinic - Invoice {{ $bill->invoice_no }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
    <style>
        body {
            background-color: #0d0f15;
            color: #fff;
            font-family: 'Plus Jakarta Sans', sans-serif;
            padding: 2rem;
            display: flex;
            justify-content: center;
        }

        .receipt-card {
            background: #131620;
            border: 1px solid var(--gold-border);
            border-radius: 12px;
            max-width: 650px;
            width: 100%;
            padding: 2.5rem;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.7);
        }

        .receipt-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--gold-primary);
            padding-bottom: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .receipt-brand {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .receipt-table {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5rem 0;
        }

        .receipt-table th {
            text-align: left;
            padding: 0.65rem 0.5rem;
            border-bottom: 1px solid var(--gold-border);
            color: var(--gold-primary);
            font-size: 0.8rem;
            text-transform: uppercase;
        }

        .receipt-table td {
            padding: 0.75rem 0.5rem;
            border-bottom: 1px solid #232738;
            font-size: 0.88rem;
        }

        @media print {
            body {
                background: #fff !important;
                color: #000 !important;
                padding: 0;
            }
            .receipt-card {
                background: #fff !important;
                color: #000 !important;
                border: 1px solid #ccc !important;
                box-shadow: none !important;
            }
            .no-print {
                display: none !important;
            }
            .receipt-table th {
                color: #000 !important;
                border-bottom: 2px solid #000 !important;
            }
            .receipt-table td {
                color: #000 !important;
                border-bottom: 1px solid #ddd !important;
            }
            .receipt-brand {
                background: #040609 !important;
                padding: 6px 14px !important;
                border-radius: 6px !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .receipt-brand img {
                height: 55px !important;
                filter: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-card">
        <div class="no-print" style="display: flex; justify-content: space-between; margin-bottom: 1.5rem;">
            <button onclick="window.close()" style="background: transparent; color: #94A3B8; border: 1px solid #232738; padding: 6px 14px; border-radius: 6px; cursor: pointer;">← Back</button>
            <button onclick="window.print()" style="background: var(--gold-gradient); color: #000; border: none; font-weight: 700; padding: 6px 16px; border-radius: 6px; cursor: pointer;">🖨️ Print Invoice</button>
        </div>

        <div class="receipt-header">
            <div class="receipt-brand">
                <img src="{{ asset('uploads/logos/logo-white.png') }}" 
                     alt="San Modestos Veterinary Services"
                     style="height: 70px; width: auto; max-width: 290px; object-fit: contain; display: block; filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.7));">
            </div>
            <div style="text-align: right;">
                <h3 style="color: var(--gold-primary); margin: 0; font-size: 1.2rem;">OFFICIAL INVOICE</h3>
                <div style="font-size: 0.85rem; font-weight: 700;">#{{ $bill->invoice_no }}</div>
                <div style="font-size: 0.75rem; color: #94A3B8;">{{ $bill->transaction_date->format('M d, Y h:i A') }}</div>
            </div>
        </div>

        <!-- Client & Pet Info -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; font-size: 0.88rem;">
            <div>
                <span style="color: #94A3B8; font-size: 0.75rem; text-transform: uppercase;">Billed To:</span>
                <div style="font-weight: 700; font-size: 1rem;">{{ $bill->client_name }}</div>
                @if($bill->owner)
                    <div style="color: #94A3B8; font-size: 0.78rem;">Key Code: {{ $bill->owner->client_code }}</div>
                    <div style="color: #94A3B8; font-size: 0.78rem;">{{ $bill->owner->contact_number }}</div>
                    <div style="color: #94A3B8; font-size: 0.78rem;">{{ $bill->owner->address }}</div>
                @endif
            </div>
            <div style="text-align: right;">
                <span style="color: #94A3B8; font-size: 0.75rem; text-transform: uppercase;">Service Details:</span>
                <div style="font-weight: 700; color: var(--gold-light);">{{ ucfirst(str_replace('_', ' ', $bill->service_type)) }}</div>
                @if($bill->pet)
                    <div style="color: #fff; font-size: 0.85rem;">Pet: <strong>{{ $bill->pet->name }}</strong> ({{ $bill->pet->pet_code }})</div>
                    <div style="color: #94A3B8; font-size: 0.78rem;">{{ $bill->pet->species }} - {{ $bill->pet->breed }}</div>
                @endif
                <div style="color: #94A3B8; font-size: 0.78rem; margin-top: 4px;">Cashier: {{ $bill->cashier->name ?? 'Frontdesk' }}</div>
            </div>
        </div>

        <!-- Line Items -->
        <table class="receipt-table">
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th style="text-align: center;">Qty</th>
                    <th style="text-align: right;">Unit Price</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bill->items as $item)
                    <tr>
                        <td>{{ $item->item_name }}</td>
                        <td style="text-align: center;">{{ $item->quantity }}</td>
                        <td style="text-align: right;">₱{{ number_format($item->unit_price, 2) }}</td>
                        <td style="text-align: right; font-weight: 700;">₱{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div style="margin-left: auto; max-width: 250px; font-size: 0.9rem;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.35rem;">
                <span>Subtotal:</span>
                <strong>₱{{ number_format($bill->subtotal, 2) }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 1.15rem; color: var(--gold-primary); border-top: 1px solid var(--gold-border); padding-top: 0.5rem;">
                <span>Total Amount:</span>
                <strong>₱{{ number_format($bill->total_amount, 2) }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 0.82rem; color: #94A3B8;">
                <span>Paid via {{ strtoupper($bill->payment_method) }}:</span>
                <span>₱{{ number_format($bill->paid_amount, 2) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 0.82rem; color: #94A3B8;">
                <span>Change:</span>
                <span>₱{{ number_format($bill->change_amount, 2) }}</span>
            </div>
        </div>

        <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #232738; text-align: center; font-size: 0.78rem; color: #94A3B8;">
            Thank you for trusting <strong>San Modesto Vet Clinic</strong> with your pet's healthcare and grooming!
        </div>
    </div>
</body>
</html>
