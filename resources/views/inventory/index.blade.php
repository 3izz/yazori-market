@extends('layouts.app')

@section('title', 'الجرد والمخزون')

@section('content')
    <h1 class="text-xl font-bold text-slate-800 mb-5">الجرد والمخزون</h1>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <div class="text-xs text-slate-500 mb-1">عدد الأصناف</div>
            <div class="text-2xl font-extrabold text-slate-800">{{ $totals['items'] }}</div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <div class="text-xs text-slate-500 mb-1">قيمة المخزون (تقريبية)</div>
            <div class="text-2xl font-extrabold text-emerald-700">{{ number_format($totals['stock_value'], 2) }}</div>
        </div>
    </div>

    <form method="GET" class="bg-white rounded-xl shadow-sm p-4 mb-5 flex flex-wrap items-center gap-3">
        <select name="category_id" class="rounded-lg border border-slate-300 px-4 py-3">
            <option value="">كل التصنيفات</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="low_only" value="1" @checked(request('low_only')) class="h-5 w-5">
            المنتجات الناقصة فقط
        </label>
        <button type="submit" class="rounded-lg bg-slate-800 text-white font-semibold px-5 py-3">تصفية</button>
    </form>

    <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
        <table class="w-full text-sm min-w-[700px]">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="text-right px-4 py-3">المنتج</th>
                    <th class="text-right px-4 py-3">التصنيف</th>
                    <th class="text-right px-4 py-3">الكمية</th>
                    <th class="text-right px-4 py-3">حد التنبيه</th>
                    <th class="text-right px-4 py-3">سعر الشراء</th>
                    <th class="text-right px-4 py-3">قيمة المخزون</th>
                    <th class="text-right px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr class="border-t {{ $product->isLowStock() ? 'bg-red-50' : '' }}">
                        <td class="px-4 py-3 font-medium">{{ $product->name }}</td>
                        <td class="px-4 py-3">{{ $product->category?->name ?? '—' }}</td>
                        <td class="px-4 py-3 {{ $product->isLowStock() ? 'text-red-600 font-bold' : '' }}">{{ $product->formattedQuantity() }} {{ $product->unit }}</td>
                        <td class="px-4 py-3">{{ $product->min_stock }}</td>
                        <td class="px-4 py-3">{{ number_format($product->purchase_price, 2) }}</td>
                        <td class="px-4 py-3">{{ number_format($product->quantity * $product->purchase_price, 2) }}</td>
                        <td class="px-4 py-3">
                            <button type="button" class="adjust-btn text-emerald-700 font-semibold"
                                    data-id="{{ $product->id }}" data-name="{{ $product->name }}" data-quantity="{{ $product->formattedQuantity() }}">
                                تعديل الكمية
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-500">لا توجد منتجات</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-x-auto mt-5">
        <div class="px-4 py-3 border-b font-bold text-slate-700">آخر تعديلات الكمية اليدوية</div>
        @if ($adjustments->isEmpty())
            <div class="p-4 text-slate-500 text-sm">لا توجد تعديلات مسجلة بعد.</div>
        @else
            <table class="w-full text-sm min-w-[600px]">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="text-right px-4 py-2">المنتج</th>
                        <th class="text-right px-4 py-2">قبل</th>
                        <th class="text-right px-4 py-2">بعد</th>
                        <th class="text-right px-4 py-2">السبب</th>
                        <th class="text-right px-4 py-2">التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($adjustments as $adjustment)
                        <tr class="border-t">
                            <td class="px-4 py-2">{{ $adjustment->product?->name ?? '—' }}</td>
                            <td class="px-4 py-2">{{ rtrim(rtrim($adjustment->quantity_before, '0'), '.') ?: '0' }}</td>
                            <td class="px-4 py-2">{{ rtrim(rtrim($adjustment->quantity_after, '0'), '.') ?: '0' }}</td>
                            <td class="px-4 py-2">{{ $adjustment->reason }}</td>
                            <td class="px-4 py-2 text-slate-500">{{ $adjustment->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div id="adjust-modal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl p-6 w-full max-w-sm space-y-4">
            <h3 class="text-lg font-bold text-slate-800">تعديل الكمية — <span id="adjust-product-name"></span></h3>
            <form method="POST" action="{{ route('inventory.adjust') }}" class="space-y-3">
                @csrf
                <input type="hidden" name="product_id" id="adjust-product-id">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">الكمية الجديدة</label>
                    <input type="number" step="0.001" min="0" name="new_quantity" id="adjust-new-quantity" required
                           class="w-full rounded-lg border border-slate-300 px-4 py-3 text-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">السبب</label>
                    <select name="reason" required class="w-full rounded-lg border border-slate-300 px-4 py-3">
                        <option value="جرد">تصحيح بعد جرد</option>
                        <option value="تلف">تلف</option>
                        <option value="سرقة">سرقة/فقدان</option>
                        <option value="أخرى">سبب آخر</option>
                    </select>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="flex-1 rounded-xl bg-emerald-700 text-white font-bold py-3 hover:bg-emerald-800">
                        حفظ التعديل
                    </button>
                    <button type="button" id="adjust-cancel-btn" class="flex-1 rounded-xl bg-slate-200 text-slate-700 font-bold py-3 hover:bg-slate-300">
                        إلغاء
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.querySelectorAll('.adjust-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                document.getElementById('adjust-product-name').textContent = btn.dataset.name;
                document.getElementById('adjust-product-id').value = btn.dataset.id;
                document.getElementById('adjust-new-quantity').value = btn.dataset.quantity;
                const modal = document.getElementById('adjust-modal');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            });
        });

        document.getElementById('adjust-cancel-btn').addEventListener('click', () => {
            const modal = document.getElementById('adjust-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        });
    </script>
@endsection
