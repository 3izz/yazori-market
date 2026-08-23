@extends('layouts.app')

@section('title', 'فاتورة بيع')

@section('content')
    <div class="flex items-center justify-between mb-5">
        <h1 class="text-xl font-bold text-slate-800">فاتورة {{ $sale->invoice_number }}</h1>
        <div class="flex gap-3">
            <a href="{{ route('sales.print', $sale) }}" target="_blank" class="rounded-lg bg-slate-800 text-white font-semibold px-4 py-2">طباعة</a>
            <a href="{{ route('sales.index') }}" class="text-emerald-700 font-semibold self-center">رجوع للسجل</a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5 mb-5 grid sm:grid-cols-3 gap-4 text-sm">
        <div>
            <div class="text-slate-500">الزبون</div>
            <div class="font-semibold">{{ $sale->customer_name ?: '—' }}</div>
        </div>
        <div>
            <div class="text-slate-500">التاريخ</div>
            <div class="font-semibold">{{ $sale->created_at->format('Y-m-d H:i') }}</div>
        </div>
        <div>
            <div class="text-slate-500">الإجمالي</div>
            <div class="font-bold text-emerald-700 text-lg">{{ number_format($sale->total, 2) }}</div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
        <table class="w-full text-sm min-w-[500px]">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="text-right px-4 py-3">المنتج</th>
                    <th class="text-right px-4 py-3">السعر</th>
                    <th class="text-right px-4 py-3">الكمية</th>
                    <th class="text-right px-4 py-3">الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->items as $item)
                    <tr class="border-t">
                        <td class="px-4 py-3">{{ $item->product_name }}</td>
                        <td class="px-4 py-3">{{ number_format($item->price, 2) }}</td>
                        <td class="px-4 py-3">{{ $item->quantity }}</td>
                        <td class="px-4 py-3 font-semibold">{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t bg-slate-50">
                    <td colspan="3" class="px-4 py-2 text-left font-semibold">المجموع الفرعي</td>
                    <td class="px-4 py-2 font-semibold">{{ number_format($sale->subtotal, 2) }}</td>
                </tr>
                <tr class="bg-slate-50">
                    <td colspan="3" class="px-4 py-2 text-left font-semibold">الخصم</td>
                    <td class="px-4 py-2 font-semibold">{{ number_format($sale->discount, 2) }}</td>
                </tr>
                <tr class="bg-slate-50">
                    <td colspan="3" class="px-4 py-2 text-left font-bold text-emerald-700">الإجمالي</td>
                    <td class="px-4 py-2 font-bold text-emerald-700">{{ number_format($sale->total, 2) }}</td>
                </tr>
                @if ($sale->paid_amount > $sale->total)
                    <tr class="bg-slate-50">
                        <td colspan="3" class="px-4 py-2 text-left font-semibold">المدفوع</td>
                        <td class="px-4 py-2 font-semibold">{{ number_format($sale->paid_amount, 2) }}</td>
                    </tr>
                    <tr class="bg-slate-50">
                        <td colspan="3" class="px-4 py-2 text-left font-semibold">الباقي</td>
                        <td class="px-4 py-2 font-semibold">{{ number_format($sale->paid_amount - $sale->total, 2) }}</td>
                    </tr>
                @endif
            </tfoot>
        </table>
    </div>
@endsection
