<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>فاتورة {{ $sale->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            width: 576px;
            margin: 0;
            padding: 16px 24px;
            color: #000;
            font-size: 24px;
            background: #fff;
        }
        .center { text-align: center; }
        .logo-badge {
            width: 190px;
            height: 190px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 8px;
        }
        h1 { font-size: 32px; margin: 8px 0 0; }
        .sub { font-size: 20px; margin: 4px 0 12px; color: #333; }
        hr { border: none; border-top: 3px dashed #000; margin: 14px 0; }
        table { width: 100%; border-collapse: collapse; font-size: 24px; }
        th, td { text-align: right; padding: 6px 0; }
        th:last-child, td:last-child { text-align: left; }
        tfoot td { padding-top: 10px; font-weight: bold; }
        .grand-total { font-size: 32px; }
        .meta { font-size: 22px; margin-bottom: 10px; }
        .meta div { display: flex; justify-content: space-between; padding: 2px 0; }
        .footer { text-align: center; font-size: 22px; margin-top: 20px; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="center">
        <img class="logo-badge" src="{{ asset('images/logo.png') }}" alt="اليازوري ماركت">
        <h1>اليازوري ماركت</h1>
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
            @if ($sale->paid_amount > $sale->total)
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
</body>
</html>
