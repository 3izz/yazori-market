@extends('layouts.app')

@section('title', 'لوحة التحكم')

@section('content')
    <h1 class="text-xl font-bold text-slate-800 mb-5">لوحة التحكم</h1>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <div class="text-xs text-slate-500 mb-1">مبيعات اليوم</div>
            <div class="text-2xl font-extrabold text-emerald-700">{{ number_format($today_total, 2) }}</div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <div class="text-xs text-slate-500 mb-1">عدد فواتير اليوم</div>
            <div class="text-2xl font-extrabold text-slate-800">{{ $today_count }}</div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <div class="text-xs text-slate-500 mb-1">عدد المنتجات</div>
            <div class="text-2xl font-extrabold text-slate-800">{{ $products_count }}</div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <div class="text-xs text-slate-500 mb-1">آخر نسخة احتياطية</div>
            <div class="text-sm font-semibold text-slate-700 mt-2">
                {{ $last_backup_at ? \Illuminate\Support\Carbon::parse($last_backup_at)->format('Y-m-d H:i') : 'لم تتم بعد' }}
            </div>
        </div>
    </div>

    <div class="flex flex-wrap gap-3 mb-6">
        <a href="{{ route('pos.index') }}" class="rounded-xl bg-emerald-700 text-white font-bold px-6 py-4 text-lg shadow hover:bg-emerald-800">
            فتح نقطة البيع
        </a>
        <a href="{{ route('products.create') }}" class="rounded-xl bg-white border border-slate-300 font-semibold px-6 py-4 text-slate-700 hover:bg-slate-50">
            إضافة منتج جديد
        </a>
        <a href="{{ route('purchases.create') }}" class="rounded-xl bg-white border border-slate-300 font-semibold px-6 py-4 text-slate-700 hover:bg-slate-50">
            تسجيل فاتورة شراء
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b font-bold text-slate-700">منتجات قاربت على النفاد</div>
        @if ($low_stock->isEmpty())
            <div class="p-4 text-slate-500 text-sm">لا يوجد نقص حالياً بالمخزون.</div>
        @else
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="text-right px-4 py-2">المنتج</th>
                        <th class="text-right px-4 py-2">الكمية المتبقية</th>
                        <th class="text-right px-4 py-2">حد التنبيه</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($low_stock as $product)
                        <tr class="border-t">
                            <td class="px-4 py-2">{{ $product->name }}</td>
                            <td class="px-4 py-2 text-red-600 font-bold">{{ $product->quantity }}</td>
                            <td class="px-4 py-2">{{ $product->min_stock }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
