<?php

namespace App\Http\Controllers;

use App\Models\CashMovement;
use App\Models\CashReset;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Services\BackupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        // A cash reset moves the reconciliation baseline forward: after a
        // physical count, "expected cash" should start again from whatever
        // was left in the drawer, not from the whole business day - otherwise
        // an end-of-shift count would permanently throw off every
        // reconciliation that follows it today.
        $lastReset = CashReset::query()
            ->where('created_at', '>=', $businessDayStart)
            ->latest()
            ->first();

        $reconciliationPeriodStart = $lastReset?->created_at ?? $businessDayStart;
        $openingFloat = (float) ($lastReset?->left_in_drawer ?? 0);

        $periodSalesTotal = (float) Sale::query()
            ->where('created_at', '>=', $reconciliationPeriodStart)
            ->whereNull('refunded_at')
            ->sum('total');

        $todayCashMovements = CashMovement::query()
            ->where('created_at', '>=', $businessDayStart)
            ->orderByDesc('created_at')
            ->get();

        $periodCashMovements = $todayCashMovements->where('created_at', '>=', $reconciliationPeriodStart);
        $todayExpenses = (float) $periodCashMovements->where('type', 'expense')->sum('amount');
        $todayValueReturns = (float) $periodCashMovements->where('type', 'return')->sum('amount');

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
            'opening_float' => $openingFloat,
            'period_sales_total' => $periodSalesTotal,
            'expected_cash' => $openingFloat + $periodSalesTotal - $todayExpenses - $todayValueReturns,
            'last_reset' => $lastReset,
            'today_cash_movements' => $todayCashMovements,
            'dashboardRevealed' => (bool) session('dashboard_revealed', false),
        ];

        return view('dashboard', $stats);
    }

    /**
     * The dashboard's figures render blurred by default (see index()) since
     * it's reachable with no PIN redirect at all; this is the eye-icon
     * action that unblurs them for the rest of the session after confirming
     * the admin PIN, without navigating away from the page.
     */
    public function reveal(Request $request): JsonResponse
    {
        $data = $request->validate(['pin' => ['required', 'string']], [], ['pin' => 'الرقم السري']);

        if ($data['pin'] !== Setting::get('admin_pin', '0000')) {
            return response()->json(['success' => false, 'message' => 'الرقم السري غير صحيح'], 422);
        }

        $request->session()->put('dashboard_revealed', true);

        return response()->json(['success' => true]);
    }
}
