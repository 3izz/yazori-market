<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>تقرير المبيعات اليومي</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 24px;
            color: #111;
        }
        .center { text-align: center; }
        .logo-badge { width: 70px; height: 70px; border-radius: 50%; object-fit: cover; margin-bottom: 6px; }
        h1 { font-size: 20px; margin: 6px 0 0; }
        .sub { font-size: 13px; margin: 2px 0 14px; color: #444; }
        hr { border: none; border-top: 2px dashed #999; margin: 16px 0; }
        .stats { display: flex; gap: 16px; margin-bottom: 20px; flex-wrap: wrap; }
        .stat-box { flex: 1; min-width: 140px; border: 1px solid #ddd; border-radius: 10px; padding: 12px 14px; }
        .stat-box .label { font-size: 12px; color: #666; margin-bottom: 4px; }
        .stat-box .value { font-size: 22px; font-weight: 800; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 20px; }
        th, td { text-align: right; padding: 6px 8px; border-bottom: 1px solid #eee; }
        th:last-child, td:last-child { text-align: left; }
        thead th { background: #f5f5f5; font-weight: 700; }
        h2 { font-size: 15px; margin: 0 0 8px; }
        .no-print { text-align: center; margin-top: 20px; }
        .no-print button, .no-print a {
            display: inline-block; font-family: inherit; font-size: 14px;
            padding: 10px 18px; margin: 4px; border-radius: 8px; border: 1px solid #333;
            background: #f3f4f6; color: #111; text-decoration: none; cursor: pointer;
        }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="center">
        <img class="logo-badge" src="{{ asset('images/logo.png') }}" alt="اليازوري ماركت">
        <h1>تقرير مبيعات اليوم — اليازوري ماركت</h1>
        <div class="sub">من {{ $business_day_start->format('Y-m-d h:i A') }} إلى الآن — تم الإصدار: {{ now()->format('Y-m-d h:i A') }}</div>
    </div>

    <hr>

    <div class="stats">
        <div class="stat-box">
            <div class="label">عدد الفواتير</div>
            <div class="value">{{ $sales_count }}</div>
        </div>
        <div class="stat-box">
            <div class="label">إجمالي المبيعات</div>
            <div class="value">{{ number_format($total_amount, 2) }}</div>
        </div>
        <div class="stat-box">
            <div class="label">إجمالي الخصومات</div>
            <div class="value">{{ number_format($total_discount, 2) }}</div>
        </div>
        <div class="stat-box">
            <div class="label">الربح التقديري</div>
            <div class="value">{{ number_format($total_profit, 2) }}</div>
        </div>
    </div>

    <h2>الأصناف الأكثر مبيعاً اليوم</h2>
    @if ($top_products->isEmpty())
        <p>لا توجد مبيعات بعد اليوم.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>الصنف</th>
                    <th>الكمية المباعة</th>
                    <th>الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($top_products as $product)
                    <tr>
                        <td>{{ $product['name'] }}</td>
                        <td>{{ rtrim(rtrim(number_format($product['quantity'], 3), '0'), '.') ?: '0' }}</td>
                        <td>{{ number_format($product['subtotal'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>كل الفواتير اليوم</h2>
    @if ($sales->isEmpty())
        <p>لا توجد فواتير بعد اليوم.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>رقم الفاتورة</th>
                    <th>الوقت</th>
                    <th>الكاشير</th>
                    <th>الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sales as $sale)
                    <tr>
                        <td>{{ $sale->invoice_number }}</td>
                        <td>{{ $sale->created_at->format('h:i A') }}</td>
                        <td>{{ $sale->cashier_name ?: '—' }}</td>
                        <td>{{ number_format($sale->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="no-print">
        <button onclick="window.print()">طباعة</button>
        <a href="{{ route('dashboard') }}">رجوع للوحة التحكم</a>
    </div>
</body>
</html>
