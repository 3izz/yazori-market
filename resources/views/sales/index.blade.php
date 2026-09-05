@extends('layouts.app')

@section('title', 'سجل المبيعات')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <h1 class="text-xl font-bold text-slate-800">سجل المبيعات</h1>
        <a href="{{ route('pos.index') }}" class="rounded-lg bg-emerald-700 text-white font-bold px-5 py-3 hover:bg-emerald-800">
            فتح نقطة البيع
        </a>
    </div>

    <form method="GET" class="bg-white rounded-xl shadow-sm p-4 mb-5 flex flex-wrap items-center gap-3">
        <input type="date" name="date" value="{{ request('date') }}" class="rounded-lg border border-slate-300 px-4 py-3">
        <button type="submit" class="rounded-lg bg-slate-800 text-white font-semibold px-5 py-3">تصفية</button>
        @if (request('date'))
            <a href="{{ route('sales.index') }}" class="text-slate-500 text-sm">إلغاء التصفية</a>
        @endif
    </form>

    <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
        <table class="w-full text-sm min-w-[600px]">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="text-right px-4 py-3">رقم الفاتورة</th>
                    <th class="text-right px-4 py-3">الزبون</th>
                    <th class="text-right px-4 py-3">الكاشير</th>
                    <th class="text-right px-4 py-3">الإجمالي</th>
                    <th class="text-right px-4 py-3">التاريخ</th>
                    <th class="text-right px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sales as $sale)
                    <tr class="border-t {{ $sale->refunded_at ? 'bg-red-50' : '' }}">
                        <td class="px-4 py-3 font-medium">
                            {{ $sale->invoice_number }}
                            @if ($sale->refunded_at)
                                <span class="text-xs text-red-600 font-bold">(مسترجعة)</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $sale->customer_name ?: '—' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $sale->cashier_name ?: '—' }}</td>
                        <td class="px-4 py-3 font-semibold">{{ number_format($sale->total, 2) }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('sales.show', $sale) }}" class="text-emerald-700 font-semibold">عرض</a>
                                <button type="button" class="reprint-btn text-slate-600 font-semibold" data-sale-id="{{ $sale->id }}">
                                    إعادة طباعة
                                </button>
                                <span class="reprint-result text-xs"></span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-500">لا توجد مبيعات بعد</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $sales->links() }}
    </div>

    <script>
        document.querySelectorAll('.reprint-btn').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const resultEl = btn.nextElementSibling;
                btn.disabled = true;
                resultEl.textContent = 'جارٍ الطباعة...';
                resultEl.className = 'reprint-result text-xs text-slate-500';

                try {
                    const res = await fetch(`/sales/${btn.dataset.saleId}/print-thermal`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                    });
                    const data = await res.json();
                    resultEl.textContent = data.message;
                    resultEl.className = 'reprint-result text-xs ' + (res.ok ? 'text-emerald-700' : 'text-red-600');
                } catch (e) {
                    resultEl.textContent = 'حدث خطأ';
                    resultEl.className = 'reprint-result text-xs text-red-600';
                } finally {
                    btn.disabled = false;
                }
            });
        });
    </script>
@endsection
