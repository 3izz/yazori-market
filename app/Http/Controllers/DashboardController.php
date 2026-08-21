<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Services\BackupService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(BackupService $backupService): View
    {
        $todaySales = Sale::query()->whereDate('created_at', today());

        $stats = [
            'today_total' => (float) $todaySales->sum('total'),
            'today_count' => (int) (clone $todaySales)->count(),
            'products_count' => Product::query()->count(),
            'low_stock' => Product::query()
                ->whereColumn('quantity', '<=', 'min_stock')
                ->where('min_stock', '>', 0)
                ->orderBy('quantity')
                ->limit(10)
                ->get(),
            'last_backup_at' => $backupService->lastBackupAt(),
        ];

        return view('dashboard', $stats);
    }
}
