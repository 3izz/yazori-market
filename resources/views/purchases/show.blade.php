@extends('layouts.app')

@section('title', 'فاتورة شراء')

@section('content')
    <div class="flex items-center justify-between mb-5">
        <h1 class="text-xl font-bold text-slate-800">فاتورة شراء {{ $purchase->reference }}</h1>
        <a href="{{ route('purchases.index') }}" class="text-emerald-700 font-semibold">رجوع لكل الفواتير</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5 mb-5 grid sm:grid-cols-3 gap-4 text-sm">
        <div>
            <div class="text-slate-500">المورد</div>
            <div class="font-semibold">{{ $purchase->supplier_name ?: '—' }}</div>
        </div>
        <div>
            <div class="text-slate-500">التاريخ</div>
            <div class="font-semibold">{{ $purchase->created_at->format('Y-m-d H:i') }}</div>
        </div>
        <div>
            <div class="text-slate-500">الإجمالي</div>
            <div class="font-bold text-emerald-700 text-lg">{{ number_format($purchase->total, 2) }}</div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
        <table class="w-full text-sm min-w-[500px]">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="text-right px-4 py-3">المنتج</th>
                    <th class="text-right px-4 py-3">سعر الشراء</th>
                    <th class="text-right px-4 py-3">الكمية</th>
                    <th class="text-right px-4 py-3">الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchase->items as $item)
                    <tr class="border-t">
                        <td class="px-4 py-3">{{ $item->product->name ?? 'منتج محذوف' }}</td>
                        <td class="px-4 py-3">{{ number_format($item->price, 2) }}</td>
                        <td class="px-4 py-3">{{ $item->quantity }}</td>
                        <td class="px-4 py-3 font-semibold">{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
