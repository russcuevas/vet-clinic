<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Owner;
use App\Models\Pet;
use App\Models\InventoryItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                  ->orWhere('client_name', 'LIKE', "%{$search}%")
                  ->orWhere('notes', 'LIKE', "%{$search}%");
            });
        }

        $bills = $query->latest('transaction_date')->paginate(15)->withQueryString();
        $totalCollected = Bill::where('payment_status', 'paid')->sum('total_amount');
        $totalPending = Bill::where('payment_status', 'unpaid')->sum('total_amount');

        $owners = Owner::with('pets')->orderBy('full_name')->get();
        $inventoryItems = InventoryItem::where('stock_quantity', '>', 0)->orderBy('name')->get();

        return view('cashier.billing.index', compact('bills', 'totalCollected', 'totalPending', 'owners', 'inventoryItems'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_name' => 'required|string|max:150',
            'owner_id' => 'nullable|exists:owners,id',
            'pet_id' => 'nullable|exists:pets,id',
            'service_type' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.total_price' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'paid_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $discount = floatval($validated['discount'] ?? 0);
            $subtotal = floatval($validated['subtotal']);
            $totalAmount = floatval($validated['total_amount']);
            $paidAmount = floatval($validated['paid_amount']);
            $changeAmount = max(0, $paidAmount - $totalAmount);

            $bill = Bill::create([
                'invoice_no' => Bill::generateInvoiceNo(),
                'owner_id' => $validated['owner_id'] ?? null,
                'pet_id' => $validated['pet_id'] ?? null,
                'cashier_id' => auth()->id(),
                'client_name' => $validated['client_name'],
                'service_type' => in_array($validated['service_type'], ['veterinary', 'grooming', 'pet_supplies', 'combined', 'boarding']) ? $validated['service_type'] : 'combined',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'paid',
                'transaction_date' => Carbon::now(),
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                BillItem::create([
                    'bill_id' => $bill->id,
                    'item_name' => $item['item_name'],
                    'item_type' => 'service',
                    'quantity' => intval($item['quantity']),
                    'unit_price' => floatval($item['unit_price']),
                    'total_price' => floatval($item['total_price']),
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', "Invoice {$bill->invoice_no} created and paid successfully! Change: ₱" . number_format($changeAmount, 2));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Failed to process checkout: ' . $e->getMessage());
        }
    }

    public function processPayment(Request $request, Bill $bill)
    {
        $validated = $request->validate([
            'payment_method' => 'required|string',
            'paid_amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.item_name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.total_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $discount = floatval($validated['discount'] ?? $bill->discount ?? 0);

            // If items were edited/added/deleted in the Pay modal
            if (!empty($validated['items'])) {
                // Delete old items and recreate
                $bill->items()->delete();
                $calculatedSubtotal = 0;

                foreach ($validated['items'] as $item) {
                    $itemTotal = intval($item['quantity']) * floatval($item['unit_price']);
                    $calculatedSubtotal += $itemTotal;

                    BillItem::create([
                        'bill_id' => $bill->id,
                        'item_name' => $item['item_name'],
                        'item_type' => 'service',
                        'quantity' => intval($item['quantity']),
                        'unit_price' => floatval($item['unit_price']),
                        'total_price' => $itemTotal,
                    ]);
                }

                $subtotal = $calculatedSubtotal;
            } else {
                $subtotal = floatval($bill->subtotal ?: $bill->total_amount);
            }

            $totalAmount = max(0, $subtotal - $discount);
            $paidAmount = floatval($validated['paid_amount']);

            if ($paidAmount < $totalAmount) {
                return redirect()->back()->withInput()->with('error', "Paid amount (₱" . number_format($paidAmount, 2) . ") cannot be less than total due (₱" . number_format($totalAmount, 2) . ").");
            }

            $change = max(0, $paidAmount - $totalAmount);

            $bill->update([
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total_amount' => $totalAmount,
                'payment_method' => $validated['payment_method'],
                'paid_amount' => $paidAmount,
                'change_amount' => $change,
                'payment_status' => 'paid',
                'cashier_id' => auth()->id(),
                'transaction_date' => Carbon::now(),
                'notes' => $validated['notes'] ?? $bill->notes,
            ]);

            if ($bill->medicalRecord) {
                $bill->medicalRecord->update(['status' => 'billed']);
            }

            if ($bill->groomingRecord) {
                $bill->groomingRecord->update(['status' => 'billed']);
            }

            DB::commit();
            return redirect()->back()->with('success', "Payment completed for {$bill->invoice_no}! Change: ₱" . number_format($change, 2));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Failed to process payment: ' . $e->getMessage());
        }
    }

    public function destroy(Bill $bill)
    {
        $invoice = $bill->invoice_no;
        $bill->delete();
        return redirect()->back()->with('success', "Invoice {$invoice} deleted successfully.");
    }

    public function invoice(Bill $bill)
    {
        $bill->load(['owner', 'pet', 'items', 'cashier']);
        return view('cashier.billing.receipt', compact('bill'));
    }
}
