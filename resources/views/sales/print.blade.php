<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>فاتورة {{ $sale->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            width: 80mm;
            margin: 0 auto;
            padding: 4mm;
            color: #000;
            font-size: 12px;
        }
        .center { text-align: center; }
        .logo-badge {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 4px;
        }
        h1 { font-size: 15px; margin: 4px 0 0; }
        .sub { font-size: 11px; margin: 2px 0 8px; }
        hr { border: none; border-top: 1px dashed #000; margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th, td { text-align: right; padding: 2px 0; }
        th:last-child, td:last-child { text-align: left; }
        tfoot td { padding-top: 4px; font-weight: bold; }
        .grand-total { font-size: 14px; }
        .meta { font-size: 11px; margin-bottom: 6px; }
        .meta div { display: flex; justify-content: space-between; }
        .footer { text-align: center; font-size: 11px; margin-top: 10px; }
        .no-print { text-align: center; margin-top: 16px; }
        .no-print button, .no-print a {
            display: inline-block;
            font-family: inherit;
            font-size: 14px;
            padding: 10px 18px;
            margin: 4px;
            border-radius: 8px;
            border: 1px solid #333;
            background: #f3f4f6;
            color: #111;
            text-decoration: none;
            cursor: pointer;
        }
        @media print {
            .no-print { display: none; }
            body { width: 80mm; padding: 0 3mm; }
        }
    </style>
</head>
<body>
    <div class="center">
        <img class="logo-badge" src="{{ asset('images/logo.png') }}" alt="اليازوري ماركت">
        <h1>اليازوري ماركت</h1>
        <div class="sub">Al-Yazori Market</div>
        @if ($sale->refunded_at)
            <div class="sub" style="font-weight:bold;">— فاتورة مسترجعة —</div>
        @endif
    </div>

    <hr>

    <div class="meta">
        <div><span>رقم الفاتورة</span><span>{{ $sale->invoice_number }}</span></div>
        <div><span>التاريخ</span><span>{{ $sale->created_at->format('Y-m-d H:i') }}</span></div>
        @if ($sale->customer_name)
            <div><span>الزبون</span><span>{{ $sale->customer_name }}</span></div>
        @endif
    </div>

    <hr>

    <table>
        <thead>
            <tr>
                <th>الصنف</th>
                <th>كمية</th>
                <th>سعر</th>
                <th>إجمالي</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sale->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->formattedQuantity() }}</td>
                    <td>{{ number_format($item->price, 2) }}</td>
                    <td>{{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">المجموع الفرعي</td>
                <td>{{ number_format($sale->subtotal, 2) }}</td>
            </tr>
            @if ($sale->discount > 0)
                <tr>
                    <td colspan="3">الخصم</td>
                    <td>{{ number_format($sale->discount, 2) }}</td>
                </tr>
            @endif
            <tr class="grand-total">
                <td colspan="3">الإجمالي</td>
                <td>{{ number_format($sale->total, 2) }}</td>
            </tr>
            @if ($sale->paid_amount > $sale->total && \App\Models\Setting::get('show_paid_change', '1') === '1')
                <tr>
                    <td colspan="3">المدفوع</td>
                    <td>{{ number_format($sale->paid_amount, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="3">الباقي</td>
                    <td>{{ number_format($sale->paid_amount - $sale->total, 2) }}</td>
                </tr>
            @endif
        </tfoot>
    </table>

    <hr>

    <div class="footer">
        شكراً لتسوقكم معنا<br>
        نتمنى لكم يوماً سعيداً
    </div>

    <div class="no-print">
        <button id="thermal-print-btn">إعادة طباعة على الطابعة</button>
        <button onclick="window.print()">طباعة عادية (متصفح)</button>
        <a href="{{ route('pos.index') }}">بيع جديد</a>
        <a href="{{ route('sales.index') }}">سجل المبيعات</a>
    </div>
    <p id="thermal-print-result" class="no-print" style="text-align:center; font-size: 14px;"></p>

    <script>
        document.getElementById('thermal-print-btn').addEventListener('click', async (e) => {
            const btn = e.currentTarget;
            const resultEl = document.getElementById('thermal-print-result');
            btn.disabled = true;
            resultEl.textContent = 'جارٍ الطباعة...';

            try {
                const res = await fetch('{{ route('sales.printThermal', $sale) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                const data = await res.json();
                resultEl.textContent = data.message;
            } catch (e) {
                resultEl.textContent = 'حدث خطأ أثناء الطباعة';
            } finally {
                btn.disabled = false;
            }
        });
    </script>
</body>
</html>
