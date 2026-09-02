<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use Illuminate\Http\Request;

class InventoryAuditController extends Controller
{
    public function index(Request $request)
    {
        $items = InventoryItem::orderBy('category')->get();

        $totalItemsCount = $items->count();
        $totalStockUnits = $items->sum('stock_quantity');
        $inventoryCost = $items->sum(fn($i) => $i->stock_quantity * $i->cost_price);
        $inventoryRetailValue = $items->sum(fn($i) => $i->stock_quantity * $i->unit_price);
        $potentialProfit = $inventoryRetailValue - $inventoryCost;
        $lowStockItems = $items->filter(fn($i) => $i->stock_quantity <= $i->reorder_level);

        return view('manager.inventory.audit', compact(
            'items',
            'totalItemsCount',
            'totalStockUnits',
            'inventoryCost',
            'inventoryRetailValue',
            'potentialProfit',
            'lowStockItems'
        ));
    }
}
