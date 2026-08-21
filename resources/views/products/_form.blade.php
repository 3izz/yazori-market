@php
    $product = $product ?? null;
@endphp

<div class="grid md:grid-cols-2 gap-4">
    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-slate-700 mb-1">اسم المنتج</label>
        <input type="text" name="name" value="{{ old('name', $product?->name) }}" required autofocus
               class="w-full rounded-lg border border-slate-300 px-4 py-3 text-lg focus:border-emerald-600 focus:ring-emerald-600">
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">الباركود</label>
        <input type="text" name="barcode" value="{{ old('barcode', $product?->barcode) }}" inputmode="numeric"
               class="w-full rounded-lg border border-slate-300 px-4 py-3 text-lg focus:border-emerald-600 focus:ring-emerald-600"
               placeholder="امسح الباركود أو اكتبه يدوياً">
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">التصنيف</label>
        <select name="category_id" class="w-full rounded-lg border border-slate-300 px-4 py-3 text-lg">
            <option value="">بدون تصنيف</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $product?->category_id) == $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">سعر الشراء</label>
        <input type="number" step="0.01" min="0" name="purchase_price" value="{{ old('purchase_price', $product?->purchase_price ?? 0) }}" required
               class="w-full rounded-lg border border-slate-300 px-4 py-3 text-lg focus:border-emerald-600 focus:ring-emerald-600">
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">سعر البيع</label>
        <input type="number" step="0.01" min="0" name="sale_price" value="{{ old('sale_price', $product?->sale_price ?? 0) }}" required
               class="w-full rounded-lg border border-slate-300 px-4 py-3 text-lg focus:border-emerald-600 focus:ring-emerald-600">
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">الكمية الحالية بالمخزون</label>
        <input type="number" min="0" name="quantity" value="{{ old('quantity', $product?->quantity ?? 0) }}" required
               class="w-full rounded-lg border border-slate-300 px-4 py-3 text-lg focus:border-emerald-600 focus:ring-emerald-600">
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">الوحدة</label>
        <input type="text" name="unit" value="{{ old('unit', $product?->unit ?? 'قطعة') }}"
               class="w-full rounded-lg border border-slate-300 px-4 py-3 text-lg focus:border-emerald-600 focus:ring-emerald-600">
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">تنبيه عند نقص الكمية عن (اختياري)</label>
        <input type="number" min="0" name="min_stock" value="{{ old('min_stock', $product?->min_stock ?? 0) }}"
               class="w-full rounded-lg border border-slate-300 px-4 py-3 text-lg focus:border-emerald-600 focus:ring-emerald-600">
    </div>
</div>

<div class="flex gap-3 mt-6">
    <button type="submit" class="rounded-lg bg-emerald-700 text-white font-bold px-8 py-3 text-lg hover:bg-emerald-800">
        حفظ
    </button>
    <a href="{{ route('products.index') }}" class="rounded-lg bg-slate-100 text-slate-700 font-semibold px-8 py-3 text-lg hover:bg-slate-200">
        إلغاء
    </a>
</div>
