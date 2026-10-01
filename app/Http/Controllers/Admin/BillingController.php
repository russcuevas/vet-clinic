<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Owner;
use App\Models\Pet;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $query = Bill::with(['owner', 'pet', 'items', 'cashier']);

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'LIKE', "%{$search}%")
                  ->orWhere('client_name', 'LIKE', "%{$search}%");
            });
        }

        $bills = $query->latest('transaction_date')->get();
        $totalCollected = Bill::where('payment_status', 'paid')->sum('total_amount');
        $totalPending = Bill::where('payment_status', 'unpaid')->sum('total_amount');

        return view('admin.billing.index', compact('bills', 'totalCollected', 'totalPending'));
    }

    public function processPayment(Request $request, Bill $bill)
    {
        $validated = $request->validate([
            'payment_method' => 'required|string',
            'paid_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $subtotal = floatval($bill->subtotal ?: $bill->total_amount);
        $discount = floatval($bill->discount ?? 0);
        $isCreditCard = ($validated['payment_method'] === 'credit_card');
        $tax = $isCreditCard ? round(max(0, $subtotal - $discount) * 0.03, 2) : floatval($bill->tax ?? 0);
        $totalAmount = max(0, $subtotal - $discount) + $tax;

        if ($validated['paid_amount'] < $totalAmount) {
            return redirect()->back()->withInput()->with('error', "Paid amount (₱" . number_format($validated['paid_amount'], 2) . ") cannot be less than total due (₱" . number_format($totalAmount, 2) . ").");
        }

        $change = $validated['paid_amount'] - $totalAmount;

        $notes = $validated['notes'] ?? $bill->notes;
        if ($isCreditCard && !str_contains($notes ?? '', '3% Card Fee')) {
            $cardNotice = 'Includes 3% Card Fee (+₱' . number_format($tax, 2) . ')';
            $notes = $notes ? "{$notes} | {$cardNotice}" : $cardNotice;
        }

        $bill->update([
            'tax' => $tax,
            'total_amount' => $totalAmount,
            'payment_method' => $validated['payment_method'],
            'paid_amount' => $validated['paid_amount'],
            'change_amount' => $change,
            'payment_status' => 'paid',
            'cashier_id' => auth()->id(),
            'notes' => $notes,
        ]);

        // If linked to medical record, mark medical record as billed
        if ($bill->medicalRecord) {
            $bill->medicalRecord->update(['status' => 'billed']);
        }

        // If linked to grooming record, mark grooming record as billed only if completed
        if ($bill->groomingRecord) {
            if ($bill->groomingRecord->status === 'completed') {
                $bill->groomingRecord->update(['status' => 'billed']);
            }
        }

        return redirect()->back()->with('success', "Payment of ₱" . number_format($bill->total_amount, 2) . " received for {$bill->invoice_no}! Change: ₱" . number_format($change, 2));
    }

    public function show(Bill $bill)
    {
        $bill->load(['owner', 'pet', 'items', 'cashier']);
        return view('admin.billing.invoice', compact('bill'));
    }

    public function destroy(Bill $bill)
    {
        $invoice = $bill->invoice_no;
        $bill->delete();

        return redirect()->back()->with('success', "Invoice {$invoice} has been deleted.");
    }
}
