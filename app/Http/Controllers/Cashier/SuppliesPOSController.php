<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\Owner;
use App\Models\Bill;
use App\Models\BillItem;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SuppliesPOSController extends Controller
{
    public function index()
    {
        $supplies = InventoryItem::where('category', 'pet_supplies')
            ->orWhere('category', 'accessories')
            ->where('stock_quantity', '>', 0)
            ->get();
        $owners = Owner::where('status', 'active')->latest()->get();

        return view('cashier.pos.index', compact('supplies', 'owners'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_type' => 'required|in:existing,new,walkin',
            'owner_id' => 'nullable|required_if:client_type,existing|exists:owners,id',
            'full_name' => 'nullable|required_if:client_type,new|string|max:255',
            'contact_number' => 'nullable|required_if:client_type,new|string|max:50',
            'address' => 'nullable|required_if:client_type,new|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|string',
            'paid_amount' => 'required|numeric|min:0',
        ]);

        $owner = null;
        $clientName = 'Walk-in Customer';

        if ($validated['client_type'] === 'existing') {
            $owner = Owner::find($validated['owner_id']);
            $clientName = $owner->full_name;
        } elseif ($validated['client_type'] === 'new') {
            $clientCode = Owner::generateClientCode();
            $owner = Owner::create([
                'client_code' => $clientCode,
                'full_name' => $validated['full_name'],
                'contact_number' => $validated['contact_number'],
                'address' => $validated['address'],
                'status' => 'active',
            ]);
            $clientName = $owner->full_name;
        }

        $subtotal = 0;
        $itemsToInsert = [];

        foreach ($validated['items'] as $cartItem) {
            $product = InventoryItem::findOrFail($cartItem['item_id']);
            $qty = $cartItem['quantity'];

            if ($product->stock_quantity < $qty) {
                return redirect()->back()->with('error', "Insufficient stock for {$product->name} (available: {$product->stock_quantity})");
            }

            $product->decrement('stock_quantity', $qty);
            $totalLine = $product->unit_price * $qty;
            $subtotal += $totalLine;

            $itemsToInsert[] = [
                'inventory_item_id' => $product->id,
                'item_name' => $product->name,
                'item_type' => 'product',
                'quantity' => $qty,
                'unit_price' => $product->unit_price,
                'total_price' => $totalLine,
            ];
        }

        $paid = $validated['paid_amount'];
        $change = max(0, $paid - $subtotal);
        $status = $paid >= $subtotal ? 'paid' : 'unpaid';

        $invoiceNo = Bill::generateInvoiceNo();
        $bill = Bill::create([
            'invoice_no' => $invoiceNo,
            'owner_id' => $owner ? $owner->id : null,
            'cashier_id' => auth()->id(),
            'client_name' => $clientName,
            'service_type' => 'pet_supplies',
            'subtotal' => $subtotal,
            'total_amount' => $subtotal,
            'paid_amount' => $paid,
            'change_amount' => $change,
            'payment_method' => $validated['payment_method'],
            'payment_status' => $status,
            'transaction_date' => Carbon::now(),
            'notes' => 'Pet supplies purchase processed by cashier',
        ]);

        foreach ($itemsToInsert as $item) {
            $item['bill_id'] = $bill->id;
            BillItem::create($item);
        }

        return redirect()->route('cashier.billing.invoice', $bill->id)
            ->with('success', "Sale completed ({$invoiceNo})! Change: ₱" . number_format($change, 2));
    }
}
