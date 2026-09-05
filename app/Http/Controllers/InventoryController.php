<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::query()->with('category');

        if ($categoryId = $request->integer('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($request->boolean('low_only')) {
            $query->whereColumn('quantity', '<=', 'min_stock')->where('min_stock', '>', 0);
        }

        $products = $query->orderBy('name')->get();

        $totals = [
            'items' => $products->count(),
            'stock_value' => $products->sum(fn (Product $product) => $product->quantity * $product->purchase_price),
        ];

        $categories = Category::query()->orderBy('name')->get();

        $adjustments = StockAdjustment::query()
            ->with('product')
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        return view('inventory.index', compact('products', 'categories', 'totals', 'adjustments'));
    }
}
