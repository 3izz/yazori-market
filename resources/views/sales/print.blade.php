<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
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
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 2px solid #000;
            font-weight: bold;
            font-size: 16px;
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
        <div class="logo-badge">يز</div>
        <h1>اليزوري ماركت</h1>
        <div class="sub">Al-Yazori Market</div>
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
                    <td>{{ $item->quantity }}</td>
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
        </tfoot>
    </table>

    <hr>

    <div class="footer">
        شكراً لتسوقكم معنا<br>
        نتمنى لكم يوماً سعيداً
    </div>

    <div class="no-print">
        <button onclick="window.print()">طباعة الفاتورة</button>
        <a href="{{ route('pos.index') }}">بيع جديد</a>
        <a href="{{ route('sales.index') }}">سجل المبيعات</a>
    </div>

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
