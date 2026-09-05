<?php

namespace App\Http\Controllers;

use App\Models\Instrument;
use App\Models\InstrumentRestockLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstrumentController extends Controller
{
    /**
     * Display instrument inventory list and summary stats.
     */
    public function index(Request $request)
    {
        $query = Instrument::query();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            if ($request->status === 'low_stock') {
                $query->whereColumn('stock_quantity', '<=', 'reorder_level')->where('stock_quantity', '>', 0);
            } elseif ($request->status === 'out_of_stock') {
                $query->where('stock_quantity', '<=', 0);
            } elseif ($request->status === 'in_stock') {
                $query->whereColumn('stock_quantity', '>', 'reorder_level');
            }
        }

        if ($request->boolean('low_stock')) {
            $query->whereColumn('stock_quantity', '<=', 'reorder_level');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('item_code', 'LIKE', "%{$search}%")
                  ->orWhere('name', 'LIKE', "%{$search}%")
                  ->orWhere('category', 'LIKE', "%{$search}%")
                  ->orWhere('storage_location', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $instruments = $query->orderBy('name')->get();
        $generatedCode = Instrument::generateItemCode('INST');

        $categories = [
            'surgical' => 'Surgical Instruments',
            'diagnostic' => 'Diagnostic Tools',
            'dental' => 'Dental Instruments',
            'general_equipment' => 'General Equipment',
            'laboratory' => 'Laboratory Equipment',
            'sterilization' => 'Sterilization & Hygiene',
            'consumable_tools' => 'Consumable Clinic Tools',
        ];

        $stats = [
            'total_items' => Instrument::count(),
            'total_quantity' => Instrument::sum('stock_quantity'),
            'low_stock_count' => Instrument::whereColumn('stock_quantity', '<=', 'reorder_level')->where('stock_quantity', '>', 0)->count(),
            'out_of_stock_count' => Instrument::where('stock_quantity', '<=', 0)->count(),
            'recent_restocks_count' => InstrumentRestockLog::whereDate('created_at', '>=', now()->subDays(7))->count(),
        ];

        // Recent 5 restock logs for quick preview widget
        $recentLogs = InstrumentRestockLog::with(['instrument', 'restockedBy'])
            ->latest()
            ->take(6)
            ->get();

        return view('instruments.index', compact('instruments', 'generatedCode', 'categories', 'stats', 'recentLogs'));
    }

    /**
     * Store newly created instrument in stock and log initial stock entry.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'stock_quantity' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'reorder_level' => 'required|integer|min:1',
            'storage_location' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:500',
        ]);

        $prefix = match ($validated['category']) {
            'surgical' => 'SRG',
            'diagnostic' => 'DXT',
            'dental' => 'DNT',
            'laboratory' => 'LAB',
            'sterilization' => 'STZ',
            default => 'INST',
        };

        $code = Instrument::generateItemCode($prefix);

        $status = 'in_stock';
        if ($validated['stock_quantity'] <= 0) {
            $status = 'out_of_stock';
        } elseif ($validated['stock_quantity'] <= $validated['reorder_level']) {
            $status = 'low_stock';
        }

        $instrument = Instrument::create([
            'item_code' => $code,
            'name' => $validated['name'],
            'category' => $validated['category'],
            'description' => $validated['description'] ?? null,
            'stock_quantity' => $validated['stock_quantity'],
            'unit' => $validated['unit'],
            'reorder_level' => $validated['reorder_level'],
            'storage_location' => $validated['storage_location'] ?? null,
            'status' => $status,
        ]);

        // Automatically log initial inventory entry with the current authenticated user
        if ($validated['stock_quantity'] > 0) {
            InstrumentRestockLog::create([
                'instrument_id' => $instrument->id,
                'user_id' => Auth::id(),
                'quantity_added' => $validated['stock_quantity'],
                'quantity_before' => 0,
                'quantity_after' => $validated['stock_quantity'],
                'action_type' => 'initial_stock',
                'remarks' => $validated['remarks'] ?? 'Initial stock registration into instruments inventory',
            ]);
        }

        return redirect()->back()->with('success', "Instrument '{$instrument->name}' ({$code}) recorded successfully!");
    }

    /**
     * Update instrument information.
     */
    public function update(Request $request, Instrument $instrument)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'unit' => 'required|string|max:50',
            'reorder_level' => 'required|integer|min:1',
            'storage_location' => 'nullable|string|max:255',
        ]);

        $instrument->update([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'description' => $validated['description'] ?? null,
            'unit' => $validated['unit'],
            'reorder_level' => $validated['reorder_level'],
            'storage_location' => $validated['storage_location'] ?? null,
            'status' => $instrument->computeStatus(),
        ]);

        return redirect()->back()->with('success', "Instrument '{$instrument->name}' updated successfully!");
    }

    /**
     * Restock instrument and create an audit log attributing the user.
     */
    public function restock(Request $request, Instrument $instrument)
    {
        $validated = $request->validate([
            'added_quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string|max:500',
        ]);

        $quantityBefore = $instrument->stock_quantity;
        $quantityAfter = $quantityBefore + $validated['added_quantity'];

        $instrument->stock_quantity = $quantityAfter;
        $instrument->status = $instrument->computeStatus();
        $instrument->save();

        // Record Restock Audit Log
        InstrumentRestockLog::create([
            'instrument_id' => $instrument->id,
            'user_id' => Auth::id(),
            'quantity_added' => $validated['added_quantity'],
            'quantity_before' => $quantityBefore,
            'quantity_after' => $quantityAfter,
            'action_type' => 'restock',
            'remarks' => $validated['remarks'] ?? 'Stock replenishment',
        ]);

        $actor = Auth::user()->name;
        return redirect()->back()->with('success', "Restocked {$validated['added_quantity']} {$instrument->unit} to '{$instrument->name}'! Recorded by {$actor}. New stock: {$quantityAfter}");
    }

    /**
     * View complete restock history & audit trail.
     */
    public function history(Request $request)
    {
        $query = InstrumentRestockLog::with(['instrument', 'restockedBy'])->latest();

        if ($request->filled('instrument_id')) {
            $query->where('instrument_id', $request->instrument_id);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action_type')) {
            $query->where('action_type', $request->action_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(25);
        $users = User::whereIn('role', ['admin', 'inventory_officer', 'back_office', 'manager'])->orderBy('name')->get();
        $instruments = Instrument::orderBy('name')->get();

        return view('instruments.history', compact('logs', 'users', 'instruments'));
    }

    /**
     * Delete an instrument.
     */
    public function destroy(Instrument $instrument)
    {
        $name = $instrument->name;
        $instrument->delete();

        return redirect()->back()->with('success', "Instrument '{$name}' deleted from database.");
    }
}
