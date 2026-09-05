<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function daily(): View
    {
        $businessDayStart = Sale::currentBusinessDayStart();

        $sales = Sale::query()
            ->where('created_at', '>=', $businessDayStart)
            ->whereNull('refunded_at')
            ->orderBy('created_at')
            ->get();

        $items = SaleItem::query()
            ->whereIn('sale_id', $sales->pluck('id'))
            ->get();

        $topProducts = $items->groupBy('product_name')
            ->map(fn ($group) => [
                'name' => $group->first()->product_name,
                'quantity' => $group->sum('quantity'),
                'subtotal' => $group->sum('subtotal'),
            ])
            ->sortByDesc('subtotal')
            ->take(10)
            ->values();

        $totalCost = $items->filter(fn (SaleItem $item) => $item->product_id)
            ->loadMissing('product')
            ->sum(fn (SaleItem $item) => $item->quantity * (float) ($item->product->purchase_price ?? 0));

        $stats = [
            'business_day_start' => $businessDayStart,
            'sales' => $sales,
            'sales_count' => $sales->count(),
            'total_amount' => $sales->sum('total'),
            'total_discount' => $sales->sum('discount'),
            'top_products' => $topProducts,
            'total_cost' => $totalCost,
            'total_profit' => $sales->sum('total') - $totalCost,
        ];

        return view('reports.daily', $stats);
    }
}
