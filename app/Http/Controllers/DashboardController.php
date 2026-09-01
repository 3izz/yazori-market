<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\BackupService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(BackupService $backupService): View
    {
        $businessDayStart = Sale::currentBusinessDayStart();

        $todaySales = Sale::query()->where('created_at', '>=', $businessDayStart);

        $todaySaleItems = SaleItem::query()
            ->whereHas('sale', fn ($q) => $q->where('created_at', '>=', $businessDayStart))
            ->whereNotNull('product_id')
            ->with('product')
            ->get();

        $totalCost = $todaySaleItems->sum(fn (SaleItem $item) => $item->quantity * (float) ($item->product->purchase_price ?? 0));
        $totalRevenue = (float) $todaySaleItems->sum('subtotal');
        $totalProfit = $totalRevenue - $totalCost;

        $stats = [
            'today_total' => (float) (clone $todaySales)->sum('total'),
            'today_count' => (int) (clone $todaySales)->count(),
            'products_count' => Product::query()->count(),
            'low_stock' => Product::query()
                ->whereColumn('quantity', '<=', 'min_stock')
                ->where('min_stock', '>', 0)
                ->orderBy('quantity')
                ->limit(10)
                ->get(),
            'last_backup_at' => $backupService->lastBackupAt(),
            'business_day_start' => $businessDayStart,
            'profit_cost' => $totalCost,
            'profit_revenue' => $totalRevenue,
            'profit_amount' => $totalProfit,
            'profit_percent' => $totalCost > 0 ? ($totalProfit / $totalCost) * 100 : ($totalRevenue > 0 ? 100 : 0),
        ];

        return view('dashboard', $stats);
    }
}
