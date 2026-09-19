<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryItem::query();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->boolean('low_stock')) {
            $query->whereColumn('stock_quantity', '<=', 'reorder_level');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('item_code', 'LIKE', "%{$search}%")
                  ->orWhere('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $items = $query->orderBy('name')->get();
        $generatedCode = InventoryItem::generateItemCode('ITM');

        $lowStockCount = InventoryItem::whereColumn('stock_quantity', '<=', 'reorder_level')->count();

        return view('admin.inventory.index', compact('items', 'generatedCode', 'lowStockCount'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'stock_quantity' => 'nullable|integer|min:0',
            'unit' => 'required|string|max:50',
            'unit_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        $prefix = match ($validated['category']) {
            'medical_services' => 'SRV',
            'medicine' => 'MED',
            'vaccine' => 'VAC',
            'grooming_supply' => 'GRM',
            default => 'ITM',
        };

        $code = InventoryItem::generateItemCode($prefix);
        $imagePath = null;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'item_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/supplies'), $filename);
            $imagePath = 'uploads/supplies/' . $filename;
        }

        $stock = $validated['category'] === 'medical_services' ? 999 : ($validated['stock_quantity'] ?? 0);
        $reorder = $validated['category'] === 'medical_services' ? 0 : ($validated['reorder_level'] ?? 5);

        $item = InventoryItem::create([
            'item_code' => $code,
            'name' => $validated['name'],
            'category' => $validated['category'],
            'description' => $validated['description'] ?? null,
            'stock_quantity' => $stock,
            'unit' => $validated['unit'],
            'unit_price' => $validated['unit_price'],
            'cost_price' => $validated['cost_price'] ?? 0.00,
            'reorder_level' => $reorder,
            'image' => $imagePath,
        ]);

        return redirect()->back()->with('success', "Item {$item->name} added to inventory ({$code})!");
    }

    public function update(Request $request, InventoryItem $item)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'stock_quantity' => 'nullable|integer|min:0',
            'unit' => 'required|string|max:50',
            'unit_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        if ($request->hasFile('image')) {
            if ($item->image && file_exists(public_path($item->image))) {
                @unlink(public_path($item->image));
            }
            $file = $request->file('image');
            $filename = 'item_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/supplies'), $filename);
            $validated['image'] = 'uploads/supplies/' . $filename;
        }

        $item->update($validated);

        return redirect()->back()->with('success', "Inventory item {$item->name} updated successfully!");
    }

    public function restock(Request $request, InventoryItem $item)
    {
        $validated = $request->validate([
            'added_quantity' => 'required|integer|min:1',
        ]);

        $item->increment('stock_quantity', $validated['added_quantity']);

        return redirect()->back()->with('success', "Added {$validated['added_quantity']} units to {$item->name}. New stock: {$item->stock_quantity}");
    }

    public function destroy(InventoryItem $item)
    {
        $name = $item->name;
        if ($item->image && file_exists(public_path($item->image))) {
            @unlink(public_path($item->image));
        }
        $item->delete();

        return redirect()->back()->with('success', "Item {$name} deleted from inventory.");
    }
}
