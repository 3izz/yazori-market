<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::query()->with('category');

        if ($search = $request->string('q')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->integer('category_id')) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->orderBy('name')->paginate(20)->withQueryString();
        $categories = Category::query()->orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::query()->orderBy('name')->get();

        return view('products.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        Product::create($data);

        return redirect()->route('products.index')->with('status', 'تمت إضافة المنتج بنجاح');
    }

    public function edit(Product $product): View
    {
        $categories = Category::query()->orderBy('name')->get();

        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validateData($request, $product);

        $product->update($data);

        return redirect()->route('products.index')->with('status', 'تم تعديل المنتج بنجاح');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return back()->with('status', 'تم حذف المنتج');
    }

    private function validateData(Request $request, ?Product $product = null): array
    {
        $barcodeRule = 'unique:products,barcode';
        if ($product) {
            $barcodeRule .= ','.$product->id;
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'barcode' => ['nullable', 'string', 'max:64', $barcodeRule],
            'unit' => ['nullable', 'string', 'max:30'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:0'],
            'min_stock' => ['nullable', 'integer', 'min:0'],
        ], [], [
            'name' => 'اسم المنتج',
            'category_id' => 'التصنيف',
            'barcode' => 'الباركود',
            'unit' => 'الوحدة',
            'purchase_price' => 'سعر الشراء',
            'sale_price' => 'سعر البيع',
            'quantity' => 'الكمية',
            'min_stock' => 'حد التنبيه',
        ]);
    }
}
