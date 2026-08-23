<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PosController extends Controller
{
    public function index(): View
    {
        return view('pos.index');
    }

    public function customerDisplay(): View
    {
        return view('pos.customer');
    }

    public function search(Request $request): JsonResponse
    {
        if ($barcode = $request->string('barcode')->trim()->value()) {
            $product = Product::query()->where('barcode', $barcode)->first();

            if (! $product) {
                return response()->json(['found' => false, 'message' => 'لا يوجد منتج بهذا الباركود'], 404);
            }

            return response()->json(['found' => true, 'product' => $this->formatProduct($product)]);
        }

        $term = $request->string('q')->trim()->value();

        if (! $term) {
            return response()->json(['products' => []]);
        }

        $products = Product::query()
            ->where('name', 'like', "%{$term}%")
            ->orWhere('barcode', 'like', "%{$term}%")
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(fn (Product $product) => $this->formatProduct($product));

        return response()->json(['products' => $products]);
    }

    public function checkout(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_name' => ['nullable', 'string', 'max:150'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'items.*.name' => ['required_without:items.*.product_id', 'nullable', 'string', 'max:150'],
            'items.*.price' => ['required_without:items.*.product_id', 'nullable', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $sale = DB::transaction(function () use ($data) {
            $subtotal = 0;
            $lines = [];

            foreach ($data['items'] as $item) {
                if (! empty($item['product_id'])) {
                    $product = Product::query()->lockForUpdate()->findOrFail($item['product_id']);
                    $price = $product->sale_price;
                    $name = $product->name;
                    $barcode = $product->barcode;
                } else {
                    $product = null;
                    $price = $item['price'];
                    $name = $item['name'] ?: 'صنف بدون اسم';
                    $barcode = null;
                }

                $lineSubtotal = $price * $item['quantity'];
                $subtotal += $lineSubtotal;

                $lines[] = [
                    'product' => $product,
                    'name' => $name,
                    'barcode' => $barcode,
                    'quantity' => $item['quantity'],
                    'price' => $price,
                    'subtotal' => $lineSubtotal,
                ];
            }

            $discount = $data['discount'] ?? 0;
            $total = max($subtotal - $discount, 0);

            $sale = Sale::create([
                'invoice_number' => 'TEMP',
                'customer_name' => $data['customer_name'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'paid_amount' => $data['paid_amount'] ?? $total,
                'user_id' => Auth::id(),
            ]);

            $sale->update(['invoice_number' => 'INV-'.str_pad((string) $sale->id, 6, '0', STR_PAD_LEFT)]);

            foreach ($lines as $line) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $line['product']?->id,
                    'product_name' => $line['name'],
                    'barcode' => $line['barcode'],
                    'price' => $line['price'],
                    'quantity' => $line['quantity'],
                    'subtotal' => $line['subtotal'],
                ]);

                $line['product']?->decrement('quantity', $line['quantity']);
            }

            return $sale;
        });

        return response()->json([
            'redirect' => route('sales.print', $sale),
            'sale_id' => $sale->id,
        ]);
    }

    private function formatProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'barcode' => $product->barcode,
            'price' => (float) $product->sale_price,
            'quantity' => $product->quantity,
            'unit' => $product->unit,
        ];
    }
}
