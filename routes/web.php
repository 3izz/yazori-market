<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'create'])->name('login');
Route::post('/login', [AuthController::class, 'store'])->name('login.store');
Route::get('/pos/unlock', [AuthController::class, 'showPosUnlock'])->name('pos.unlock');
Route::post('/pos/unlock', [AuthController::class, 'posUnlock'])->name('pos.unlock.attempt');

// Reachable with either a full admin login or just the POS PIN.
Route::middleware('pos.access')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::get('/pos/customer-display', [PosController::class, 'customerDisplay'])->name('pos.customer');
    Route::get('/pos/search', [PosController::class, 'search'])->name('pos.search');
    Route::get('/pos/quick-items', [PosController::class, 'quickItems'])->name('pos.quickItems');
    Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    Route::post('/pos/open-drawer', [PosController::class, 'openDrawer'])->name('pos.openDrawer');
    Route::post('/sales/{sale}/print-thermal', [SaleController::class, 'printThermal'])->name('sales.printThermal');
});

// Full admin login required for everything else.
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('products', ProductController::class)->except(['show']);
    Route::post('/products/{product}/print-barcode', [ProductController::class, 'printBarcode'])->name('products.printBarcode');

    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
    Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');
    Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');

    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');

    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    Route::get('/sales/{sale}/print', [SaleController::class, 'print'])->name('sales.print');

    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings/password', [SettingController::class, 'updatePassword'])->name('settings.password');
    Route::post('/settings/backup', [SettingController::class, 'backupNow'])->name('settings.backup');
    Route::post('/settings/backup-path', [SettingController::class, 'updateBackupPath'])->name('settings.backupPath');
    Route::post('/settings/printer', [SettingController::class, 'updatePrinter'])->name('settings.printer');
    Route::post('/settings/print-test', [SettingController::class, 'printTest'])->name('settings.printTest');
    Route::post('/settings/pos-pin', [SettingController::class, 'updatePosPin'])->name('settings.posPin');
    Route::post('/settings/invoice-options', [SettingController::class, 'updateInvoiceOptions'])->name('settings.invoiceOptions');
});
