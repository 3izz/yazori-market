<?php

namespace App\Http\Controllers;

use App\Models\CashMovement;
use App\Models\CashReset;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Services\ThermalPrintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PosController extends Controller
{
    public function index(): View
    {
        $businessDayStart = Sale::currentBusinessDayStart();

        $lastInvoiceNumber = Sale::query()
            ->where('created_at', '>=', $businessDayStart)
            ->whereNull('refunded_at')
            ->orderByDesc('id')
            ->value('invoice_number');

        return view('pos.index', [
            'lastInvoiceNumber' => $lastInvoiceNumber,
            'cashierName' => session('pos_cashier_name'),
        ]);
    }

    public function customerDisplay(): View
    {
        return view('pos.customer');
    }

    public function quickItems(): JsonResponse
    {
        $bestSellerIds = SaleItem::query()
            ->selectRaw('product_id, SUM(quantity) as total_qty')
            ->whereNotNull('product_id')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->pluck('product_id');

        $bestSellers = Product::query()
            ->whereIn('id', $bestSellerIds)
            ->get()
            ->sortBy(fn (Product $p) => array_search($p->id, $bestSellerIds->all()))
            ->values()
            ->map(fn (Product $product) => $this->formatProduct($product));

        $categories = Category::query()
            ->with(['products' => fn ($q) => $q->orderBy('name')->limit(12)])
            ->orderBy('name')
            ->get()
            ->filter(fn (Category $category) => $category->products->isNotEmpty())
            ->map(fn (Category $category) => [
                'name' => $category->name,
                'products' => $category->products->map(fn (Product $product) => $this->formatProduct($product)),
            ])
            ->values();

        return response()->json([
            'bestSellers' => $bestSellers,
            'categories' => $categories,
        ]);
    }

    public function openDrawer(ThermalPrintService $printer): JsonResponse
    {
        return response()->json($printer->openDrawerOnly());
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
            // Weighted products (e.g. 0.250 kg) need fractional quantities; a
            // whole-unit sale just happens to always submit a round number.
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
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
                'cashier_name' => session('pos_cashier_name'),
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
            'invoice_number' => $sale->invoice_number,
            'total' => (float) $sale->total,
        ]);
    }

    public function lookupReturn(Request $request): JsonResponse
    {
        $invoice = $request->string('invoice_number')->trim()->value();

        $sale = Sale::query()->with('items')->where('invoice_number', $invoice)->first();

        if (! $sale) {
            return response()->json(['found' => false, 'message' => 'لا توجد فاتورة بهذا الرقم'], 404);
        }

        if ($sale->refunded_at) {
            return response()->json(['found' => false, 'message' => 'هذه الفاتورة مسترجعة مسبقاً بتاريخ '.$sale->refunded_at->format('Y-m-d H:i')], 422);
        }

        return response()->json([
            'found' => true,
            'sale' => [
                'id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'total' => (float) $sale->total,
                'created_at' => $sale->created_at->format('Y-m-d H:i'),
                'items' => $sale->items->map(fn (SaleItem $item) => [
                    'name' => $item->product_name,
                    'quantity' => (float) $item->quantity,
                    'subtotal' => (float) $item->subtotal,
                ]),
            ],
        ]);
    }

    /**
     * Returns and expenses all require the admin PIN as an explicit
     * authorization step - a cashier alone shouldn't be able to move money
     * out of the drawer (real or claimed) without a supervisor confirming
     * it. Checked server-side so it can never be bypassed from the browser.
     */
    public function processReturn(Request $request): JsonResponse
    {
        $data = $request->validate([
            'sale_id' => ['required', 'exists:sales,id'],
            'reason' => ['nullable', 'string', 'max:150'],
            'admin_pin' => ['required', 'string'],
        ], [], ['admin_pin' => 'الرقم السري']);

        if ($data['admin_pin'] !== Setting::get('admin_pin', '0000')) {
            return response()->json(['success' => false, 'message' => 'الرقم السري غير صحيح'], 422);
        }

        try {
            $sale = DB::transaction(function () use ($data) {
                $sale = Sale::query()->lockForUpdate()->with('items')->findOrFail($data['sale_id']);

                if ($sale->refunded_at) {
                    throw new \RuntimeException('هذه الفاتورة مسترجعة مسبقاً');
                }

                foreach ($sale->items as $item) {
                    if ($item->product_id) {
                        Product::query()->where('id', $item->product_id)->increment('quantity', $item->quantity);
                    }
                }

                $sale->update([
                    'refunded_at' => now(),
                    'refund_reason' => $data['reason'] ?? null,
                ]);

                return $sale;
            });
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'message' => 'تم استرجاع الفاتورة وإعادة الكمية للمخزون', 'sale_id' => $sale->id]);
    }

    public function storeExpense(Request $request): JsonResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string', 'max:150'],
            'admin_pin' => ['required', 'string'],
        ], [], ['amount' => 'المبلغ', 'reason' => 'السبب', 'admin_pin' => 'الرقم السري']);

        if ($data['admin_pin'] !== Setting::get('admin_pin', '0000')) {
            return response()->json(['success' => false, 'message' => 'الرقم السري غير صحيح'], 422);
        }

        CashMovement::create([
            'type' => 'expense',
            'amount' => $data['amount'],
            'reason' => $data['reason'],
            'cashier_name' => session('pos_cashier_name'),
            'user_id' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'message' => 'تم تسجيل المصروف']);
    }

    public function storeCashReturn(Request $request): JsonResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string', 'max:150'],
            'admin_pin' => ['required', 'string'],
        ], [], ['amount' => 'المبلغ', 'reason' => 'السبب', 'admin_pin' => 'الرقم السري']);

        if ($data['admin_pin'] !== Setting::get('admin_pin', '0000')) {
            return response()->json(['success' => false, 'message' => 'الرقم السري غير صحيح'], 422);
        }

        CashMovement::create([
            'type' => 'return',
            'amount' => $data['amount'],
            'reason' => $data['reason'],
            'cashier_name' => session('pos_cashier_name'),
            'user_id' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'message' => 'تم تسجيل الإرجاع']);
    }

    /**
     * Records a physical cash count as a checkpoint: what was actually
     * counted in the drawer, and how much of it stays behind as the float
     * for the next shift. From this moment on, the dashboard's expected-cash
     * reconciliation starts counting sales/expenses/returns fresh from this
     * checkpoint instead of from the start of the business day, using
     * left_in_drawer as the new opening balance - otherwise a shift-end count
     * would permanently throw off every reconciliation after it. Requires
     * the admin PIN for the same reason storeCashReturn() does: it directly
     * moves the reported cash baseline, which a dishonest cashier could
     * otherwise abuse to paper over a shortfall.
     */
    public function storeCashReset(Request $request): JsonResponse
    {
        $data = $request->validate([
            'counted_amount' => ['required', 'numeric', 'min:0'],
            'left_in_drawer' => ['required', 'numeric', 'min:0'],
            'admin_pin' => ['required', 'string'],
        ], [], ['counted_amount' => 'المبلغ المعدود', 'left_in_drawer' => 'المبلغ المتبقي بالدرج', 'admin_pin' => 'الرقم السري']);

        if ($data['admin_pin'] !== Setting::get('admin_pin', '0000')) {
            return response()->json(['success' => false, 'message' => 'الرقم السري غير صحيح'], 422);
        }

        CashReset::create([
            'counted_amount' => $data['counted_amount'],
            'left_in_drawer' => $data['left_in_drawer'],
            'cashier_name' => session('pos_cashier_name'),
            'user_id' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'message' => 'تم تصفير الكاش وتسجيل الرصيد الجديد']);
    }

    /**
     * Idle-timeout lock for PIN-only sessions: a cashier who walks away and
     * forgets to log out shouldn't leave POS usable indefinitely. A logged-in
     * admin is left alone here - they already passed the strongest gate, and
     * force-locking them mid-task would just be an annoyance with no added
     * safety.
     */
    public function lock(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            $request->session()->forget(['pos_unlocked', 'pos_cashier_name']);
        }

        return response()->json(['redirect' => route('pos.unlock')]);
    }

    private function formatProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'barcode' => $product->barcode,
            'price' => (float) $product->sale_price,
            'quantity' => (float) $product->quantity,
            'unit' => $product->unit,
            'is_weighted' => $product->is_weighted,
        ];
    }
}
