<?php

namespace App\Http\Controllers;

use App\Models\CashMovement;
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

        // A refunded sale never really kept that cash in the drawer, so it's
        // excluded here the same way PosController and ReportController
        // already exclude it - otherwise a refund would silently break the
        // cash-reconciliation figures below.
        $todaySales = Sale::query()
            ->where('created_at', '>=', $businessDayStart)
            ->whereNull('refunded_at');

        $todaySaleItems = SaleItem::query()
            ->whereHas('sale', fn ($q) => $q->where('created_at', '>=', $businessDayStart)->whereNull('refunded_at'))
            ->whereNotNull('product_id')
            ->with('product')
            ->get();

        $totalCost = $todaySaleItems->sum(fn (SaleItem $item) => $item->quantity * (float) ($item->product->purchase_price ?? 0));
        $totalRevenue = (float) $todaySaleItems->sum('subtotal');
        $totalProfit = $totalRevenue - $totalCost;

        $todayTotal = (float) (clone $todaySales)->sum('total');

        $todayCashMovements = CashMovement::query()
            ->where('created_at', '>=', $businessDayStart)
            ->orderByDesc('created_at')
            ->get();

        $todayExpenses = (float) $todayCashMovements->where('type', 'expense')->sum('amount');
        $todayValueReturns = (float) $todayCashMovements->where('type', 'return')->sum('amount');

        $stats = [
            'today_total' => $todayTotal,
            'today_count' => (int) (clone $todaySales)->count(),
            'products_count' => Product::query()->count(),
            'low_stock' => Product::query()
                ->whereColumn('quantity', '<=', 'min_stock')
                ->where('min_stock', '>', 0)
                ->orderBy('quantity')
                ->limit(10)
                ->get(),
            'last_backup_at' => $backupService->lastBackupAt(),
            'backup_verified' => $backupService->lastBackupVerified(),
            'business_day_start' => $businessDayStart,
            'profit_cost' => $totalCost,
            'profit_revenue' => $totalRevenue,
            'profit_amount' => $totalProfit,
            'profit_percent' => $totalCost > 0 ? ($totalProfit / $totalCost) * 100 : ($totalRevenue > 0 ? 100 : 0),
            'today_expenses' => $todayExpenses,
            'today_value_returns' => $todayValueReturns,
            'expected_cash' => $todayTotal - $todayExpenses - $todayValueReturns,
            'today_cash_movements' => $todayCashMovements,
        ];

        return view('dashboard', $stats);
    }
}
