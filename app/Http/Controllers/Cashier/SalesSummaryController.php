<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\BillItem;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SalesSummaryController extends Controller
{
    public function index(Request $request)
    {
        $preset = $request->query('preset', 'today');
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');
        $paymentMethod = $request->query('payment_method');
        $serviceType = $request->query('service_type');
        $search = $request->query('search');

        $query = Bill::with(['items', 'owner', 'pet', 'cashier'])
            ->where('payment_status', 'paid');

        $today = Carbon::today();

        // Date range filtering
        if ($fromDate && $toDate) {
            $preset = 'custom';
            $query->whereDate('transaction_date', '>=', $fromDate)
                  ->whereDate('transaction_date', '<=', $toDate);
            $filterLabel = "From " . Carbon::parse($fromDate)->format('M d, Y') . " to " . Carbon::parse($toDate)->format('M d, Y');
        } elseif ($preset === 'today') {
            $query->whereDate('transaction_date', $today);
            $filterLabel = "Today (" . $today->format('M d, Y') . ")";
        } elseif ($preset === 'yesterday') {
            $yesterday = Carbon::yesterday();
            $query->whereDate('transaction_date', $yesterday);
            $filterLabel = "Yesterday (" . $yesterday->format('M d, Y') . ")";
        } elseif ($preset === 'this_week') {
            $startOfWeek = Carbon::now()->startOfWeek();
            $endOfWeek = Carbon::now()->endOfWeek();
            $query->whereBetween('transaction_date', [$startOfWeek, $endOfWeek]);
            $filterLabel = "This Week (" . $startOfWeek->format('M d') . " - " . $endOfWeek->format('M d, Y') . ")";
        } elseif ($preset === 'this_month') {
            $query->whereMonth('transaction_date', Carbon::now()->month)
                  ->whereYear('transaction_date', Carbon::now()->year);
            $filterLabel = "This Month (" . Carbon::now()->format('F Y') . ")";
        } else {
            $filterLabel = "All Recorded Transactions";
        }

        if ($paymentMethod) {
            $query->where('payment_method', $paymentMethod);
        }

        if ($serviceType) {
            $query->where('service_type', $serviceType);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                  ->orWhere('client_name', 'like', "%{$search}%")
                  ->orWhereHas('items', function ($iq) use ($search) {
                      $iq->where('item_name', 'like', "%{$search}%");
                  });
            });
        }

        $bills = $query->latest('transaction_date')->get();

        // Flatten into itemized transaction rows for detailed sales breakdown
        $salesRows = [];
        $grandSubtotal = 0;
        $grandDiscount = 0;
        $grandSurcharge = 0;
        $grandTotal = 0;
        $grandCash = 0;
        $grandGCash = 0;
        $grandMaya = 0;
        $grandCreditCard = 0;
        $grandBankTransfer = 0;
        $grandPaidTendered = 0;
        $grandChange = 0;

        foreach ($bills as $bill) {
            $billDiscount = floatval($bill->discount ?? 0);
            $grandDiscount += $billDiscount;
            $grandPaidTendered += floatval($bill->paid_amount ?? 0);
            $grandChange += floatval($bill->change_amount ?? 0);

            $method = strtolower($bill->payment_method ?? 'cash');
            $isCreditCard = in_array($method, ['credit_card', 'debit_card', 'card']);

            // Surcharge calculation for credit card (3%)
            $billSurcharge = 0;
            if ($isCreditCard) {
                // If total amount in DB already includes surcharge or if computed from (subtotal - discount) * 0.03
                $netBeforeCardFee = max(0, floatval($bill->subtotal) - $billDiscount);
                $calculatedFee = round($netBeforeCardFee * 0.03, 2);
                $diff = floatval($bill->total_amount) - $netBeforeCardFee;
                $billSurcharge = ($diff > 0) ? $diff : $calculatedFee;
                $grandSurcharge += $billSurcharge;
                $grandCreditCard += $netBeforeCardFee;
            } elseif ($method === 'gcash') {
                $grandGCash += $bill->total_amount;
            } elseif ($method === 'maya' || $method === 'paymaya') {
                $grandMaya += $bill->total_amount;
            } elseif ($method === 'bank_transfer' || $method === 'bank') {
                $grandBankTransfer += $bill->total_amount;
            } else {
                $grandCash += $bill->total_amount;
            }

            $grandTotal += $bill->total_amount;

            $itemCount = $bill->items->count();
            foreach ($bill->items as $index => $item) {
                $grandSubtotal += $item->total_price;

                // Prorate or assign discount
                if ($itemCount === 1) {
                    $rowDiscount = $billDiscount;
                } elseif ($bill->subtotal > 0) {
                    $rowDiscount = round(($item->total_price / $bill->subtotal) * $billDiscount, 2);
                } else {
                    $rowDiscount = round($billDiscount / $itemCount, 2);
                }

                // Allocate payment column for this specific item row
                $rowNetPrice = max(0, $item->total_price - $rowDiscount);
                $rowSurcharge = 0;
                $rowCredit = 0;

                if ($isCreditCard) {
                    $rowCredit = $rowNetPrice;
                    $rowSurcharge = ($itemCount === 1) ? $billSurcharge : round($rowNetPrice * 0.03, 2);
                }

                $rowGCash = ($method === 'gcash') ? $rowNetPrice : 0;
                $rowBank = ($method === 'bank_transfer' || $method === 'bank') ? $rowNetPrice : 0;
                $rowMaya = ($method === 'maya' || $method === 'paymaya') ? $rowNetPrice : 0;
                $rowCash = (!in_array($method, ['credit_card', 'debit_card', 'card', 'gcash', 'maya', 'paymaya', 'bank_transfer', 'bank'])) ? $rowNetPrice : 0;

                $salesRows[] = [
                    'bill_id' => $bill->id,
                    'invoice_no' => $bill->invoice_no,
                    'transaction_date' => $bill->transaction_date,
                    'client_name' => $bill->client_name,
                    'item_name' => $item->item_name,
                    'description' => ucfirst(str_replace('_', ' ', $bill->service_type)),
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                    'discount' => $rowDiscount,
                    'payment_method' => $bill->payment_method,
                    'credit_card' => $rowCredit,
                    'surcharge' => $rowSurcharge,
                    'gcash' => $rowGCash,
                    'bank_transfer' => $rowBank,
                    'maya' => $rowMaya,
                    'cash' => $rowCash,
                    'is_first_item' => $index === 0,
                    'row_span' => $itemCount,
                    'bill_discount' => $billDiscount,
                    'bill_surcharge' => $billSurcharge,
                    'bill_total' => $bill->total_amount,
                    'bill_paid' => $bill->paid_amount,
                    'bill_change' => $bill->change_amount,
                ];
            }
        }

        return view('cashier.reports.sales_summary', compact(
            'bills',
            'salesRows',
            'filterLabel',
            'preset',
            'fromDate',
            'toDate',
            'paymentMethod',
            'serviceType',
            'search',
            'grandSubtotal',
            'grandDiscount',
            'grandSurcharge',
            'grandTotal',
            'grandCash',
            'grandGCash',
            'grandMaya',
            'grandCreditCard',
            'grandBankTransfer',
            'grandPaidTendered',
            'grandChange'
        ));
    }

    public function print(Request $request)
    {
        // Same logic as index for dedicated clean printable view
        return $this->index($request);
    }
}
