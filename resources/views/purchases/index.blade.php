@extends('layouts.app')

@section('title', 'المشتريات')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <h1 class="text-xl font-bold text-slate-800">فواتير الشراء</h1>
        <a href="{{ route('purchases.create') }}" class="rounded-lg bg-emerald-700 text-white font-bold px-5 py-3 hover:bg-emerald-800">
            + فاتورة شراء جديدة
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
        <table class="w-full text-sm min-w-[600px]">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="text-right px-4 py-3">المرجع</th>
                    <th class="text-right px-4 py-3">المورد</th>
                    <th class="text-right px-4 py-3">عدد الأصناف</th>
                    <th class="text-right px-4 py-3">الإجمالي</th>
                    <th class="text-right px-4 py-3">التاريخ</th>
                    <th class="text-right px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($purchases as $purchase)
                    <tr class="border-t">
                        <td class="px-4 py-3 font-medium">{{ $purchase->reference }}</td>
                        <td class="px-4 py-3">{{ $purchase->supplier_name ?: '—' }}</td>
                        <td class="px-4 py-3">{{ $purchase->items_count }}</td>
                        <td class="px-4 py-3">{{ number_format($purchase->total, 2) }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $purchase->created_at->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('purchases.show', $purchase) }}" class="text-emerald-700 font-semibold">عرض</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-500">لا توجد فواتير شراء بعد</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $purchases->links() }}
    </div>
@endsection
