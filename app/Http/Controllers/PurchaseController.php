<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function index(): View
    {
        $purchases = Purchase::query()
            ->withCount('items')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('purchases.index', compact('purchases'));
    }

    public function create(): View
    {
        return view('purchases.create');
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'supplier_name' => ['nullable', 'string', 'max:150'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
        ]);

        $purchase = DB::transaction(function () use ($data) {
            $total = 0;

            $purchase = Purchase::create([
                'reference' => 'TEMP',
                'supplier_name' => $data['supplier_name'] ?? null,
                'total' => 0,
                'user_id' => Auth::id(),
            ]);

            foreach ($data['items'] as $item) {
                $product = Product::query()->lockForUpdate()->findOrFail($item['product_id']);
                $subtotal = $item['price'] * $item['quantity'];
                $total += $subtotal;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $subtotal,
                ]);

                $product->increment('quantity', $item['quantity']);
                $product->update(['purchase_price' => $item['price']]);
            }

            $purchase->update([
                'reference' => 'PUR-'.str_pad((string) $purchase->id, 6, '0', STR_PAD_LEFT),
                'total' => $total,
            ]);

            return $purchase;
        });

        if ($request->wantsJson()) {
            return response()->json(['redirect' => route('purchases.show', $purchase)]);
        }

        return redirect()->route('purchases.show', $purchase)->with('status', 'تم تسجيل فاتورة الشراء بنجاح');
    }

    public function show(Purchase $purchase): View
    {
        $purchase->load('items.product');

        return view('purchases.show', compact('purchase'));
    }
}
