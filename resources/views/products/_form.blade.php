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
        <div class="flex gap-2">
            <input type="text" name="barcode" id="barcode-field" value="{{ old('barcode', $product?->barcode) }}" inputmode="numeric"
                   class="w-full rounded-lg border border-slate-300 px-4 py-3 text-lg focus:border-emerald-600 focus:ring-emerald-600"
                   placeholder="امسح الباركود أو اكتبه يدوياً">
            <button type="button" id="generate-barcode-btn"
                    class="shrink-0 rounded-lg bg-slate-700 text-white font-semibold px-4 py-3 text-sm hover:bg-slate-800 whitespace-nowrap">
                توليد باركود
            </button>
        </div>
        <p class="text-xs text-slate-400 mt-1">لو المنتج ما إلو باركود من المصنع، اضغط "توليد باركود" ثم احفظ، وبعدين اطبع ملصق الباركود من صفحة التعديل والصقه على المنتج.</p>
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

@if ($product)
    <div class="mt-4">
        <button type="button" id="print-barcode-btn"
                class="rounded-lg bg-slate-800 text-white font-semibold px-6 py-3 text-sm hover:bg-slate-900">
            🖨️ طباعة ملصق الباركود
        </button>
        <p id="print-barcode-result" class="text-sm mt-2"></p>
    </div>
@endif

<div class="flex gap-3 mt-6">
    <button type="submit" class="rounded-lg bg-emerald-700 text-white font-bold px-8 py-3 text-lg hover:bg-emerald-800">
        حفظ
    </button>
    <a href="{{ route('products.index') }}" class="rounded-lg bg-slate-100 text-slate-700 font-semibold px-8 py-3 text-lg hover:bg-slate-200">
        إلغاء
    </a>
</div>

<script>
(function () {
    document.getElementById('generate-barcode-btn').addEventListener('click', () => {
        const digits = '2' + Array.from({ length: 11 }, () => Math.floor(Math.random() * 10)).join('');
        let sum = 0;
        for (let i = 0; i < 12; i++) {
            sum += parseInt(digits[i], 10) * (i % 2 === 0 ? 1 : 3);
        }
        const checkDigit = (10 - (sum % 10)) % 10;
        document.getElementById('barcode-field').value = digits + checkDigit;
    });

    @if ($product)
    const printBtn = document.getElementById('print-barcode-btn');
    const resultEl = document.getElementById('print-barcode-result');

    printBtn.addEventListener('click', async () => {
        printBtn.disabled = true;
        resultEl.textContent = 'جارٍ الطباعة...';
        resultEl.className = 'text-sm mt-2 text-slate-500';

        try {
            const res = await fetch('{{ route('products.printBarcode', $product) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            });
            const data = await res.json();
            resultEl.textContent = data.message;
            resultEl.className = 'text-sm mt-2 ' + (res.ok ? 'text-emerald-700' : 'text-red-600');
        } catch (e) {
            resultEl.textContent = 'حدث خطأ أثناء الطباعة';
            resultEl.className = 'text-sm mt-2 text-red-600';
        } finally {
            printBtn.disabled = false;
        }
    });
    @endif
})();
</script>
