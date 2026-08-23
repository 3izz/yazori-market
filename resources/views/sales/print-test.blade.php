<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>طباعة تجريبية</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            width: 576px;
            margin: 0;
            padding: 16px 24px;
            color: #000;
            background: #fff;
            text-align: center;
        }
        .logo-badge { width: 110px; height: 110px; border-radius: 50%; object-fit: cover; margin-bottom: 8px; }
        h1 { font-size: 32px; margin: 8px 0 0; }
        p { font-size: 24px; margin: 10px 0; }
        hr { border: none; border-top: 3px dashed #000; margin: 14px 0; }
    </style>
</head>
<body>
    <img class="logo-badge" src="{{ asset('images/logo.png') }}" alt="اليازوري ماركت">
    <h1>اليازوري ماركت</h1>
    <hr>
    <p>طباعة تجريبية ناجحة</p>
    <p>{{ now()->format('Y-m-d H:i') }}</p>
</body>
</html>
