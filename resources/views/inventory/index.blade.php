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
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr class="border-t {{ $product->isLowStock() ? 'bg-red-50' : '' }}">
                        <td class="px-4 py-3 font-medium">{{ $product->name }}</td>
                        <td class="px-4 py-3">{{ $product->category?->name ?? '—' }}</td>
                        <td class="px-4 py-3 {{ $product->isLowStock() ? 'text-red-600 font-bold' : '' }}">{{ $product->quantity }} {{ $product->unit }}</td>
                        <td class="px-4 py-3">{{ $product->min_stock }}</td>
                        <td class="px-4 py-3">{{ number_format($product->purchase_price, 2) }}</td>
                        <td class="px-4 py-3">{{ number_format($product->quantity * $product->purchase_price, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-500">لا توجد منتجات</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
