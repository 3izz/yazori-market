@extends('layouts.app')

@section('title', 'لوحة التحكم')

@section('content')
    <h1 class="text-xl font-bold text-slate-800 mb-5">لوحة التحكم</h1>

    @if ($low_stock->isNotEmpty())
        <a href="{{ route('inventory.index', ['low_only' => 1]) }}"
           class="block rounded-xl bg-red-600 text-white p-4 mb-5 shadow-sm hover:bg-red-700">
            <div class="flex items-center gap-2 font-bold">
                <span class="text-xl">⚠</span>
                <span>تنبيه: {{ $low_stock->count() }} {{ $low_stock->count() == 1 ? 'منتج' : 'منتجات' }} قاربت على النفاد أو خلصت — اضغط لعرضها</span>
            </div>
        </a>
    @endif

    @if (! $backup_verified)
        <div class="rounded-xl bg-red-100 border border-red-300 text-red-800 p-4 mb-5 text-sm font-semibold">
            ⚠ آخر نسخة احتياطية غير موثوقة! راجع صفحة الإعدادات فوراً.
        </div>
    @endif

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

    <div class="bg-white rounded-xl shadow-sm p-5 mb-6">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-bold text-slate-700">نسبة الربح لهذا اليوم</h2>
            <span class="text-xs text-slate-400">
                من {{ $business_day_start->format('Y-m-d h:i A') }} — يبدأ يوم البيع الساعة 6 صباحاً
            </span>
        </div>

        @if ($profit_revenue <= 0)
            <p class="text-sm text-slate-400">لا يوجد مبيعات بعد اليوم.</p>
        @else
            <div class="flex items-end gap-4 mb-3">
                <div class="text-3xl font-extrabold text-emerald-700">{{ number_format($profit_percent, 1) }}%</div>
                <div class="text-sm text-slate-500 pb-1">نسبة الربح من سعر التكلفة</div>
            </div>

            <div class="flex h-3 rounded-full overflow-hidden bg-slate-100" role="img"
                 aria-label="التكلفة {{ number_format($profit_cost, 2) }}، الربح {{ number_format($profit_amount, 2) }}">
                @php
                    $costShare = $profit_revenue > 0 ? min(max($profit_cost / $profit_revenue, 0), 1) * 100 : 0;
                @endphp
                <div class="bg-slate-400" style="width: {{ $costShare }}%"></div>
                <div class="w-0.5 bg-white"></div>
                <div class="bg-emerald-500 flex-1"></div>
            </div>

            <div class="flex items-center gap-5 mt-3 text-xs text-slate-500">
                <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-slate-400"></span> التكلفة: {{ number_format($profit_cost, 2) }}</span>
                <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span> الربح: {{ number_format($profit_amount, 2) }}</span>
                <span>المبيعات: {{ number_format($profit_revenue, 2) }}</span>
            </div>
        @endif
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
        <a href="{{ route('reports.daily') }}" target="_blank" class="rounded-xl bg-white border border-slate-300 font-semibold px-6 py-4 text-slate-700 hover:bg-slate-50">
            🖨️ طباعة تقرير اليوم
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
                            <td class="px-4 py-2 text-red-600 font-bold">{{ $product->formattedQuantity() }}</td>
                            <td class="px-4 py-2">{{ $product->min_stock }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
