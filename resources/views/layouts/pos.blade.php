<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>نقطة البيع - {{ config('app.name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-800 antialiased h-screen overflow-hidden">
    <div class="flex h-screen flex-col">
        <header class="flex items-center justify-between bg-emerald-900 text-white px-4 py-3 shrink-0">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo.png') }}" alt="اليازوري ماركت" class="h-9 w-9 rounded-full shrink-0 object-cover">
                <div class="font-bold">اليازوري ماركت — نقطة البيع</div>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" id="open-customer-display-btn"
                        class="touch-btn rounded-lg bg-emerald-700 px-4 py-3 text-sm font-semibold hover:bg-emerald-600">
                    فتح شاشة الزبون
                </button>
                @auth
                    <a href="{{ route('dashboard') }}" class="touch-btn rounded-lg bg-emerald-800 px-4 py-3 text-sm font-semibold hover:bg-emerald-700">
                        رجوع للوحة التحكم
                    </a>
                @endauth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="touch-btn rounded-lg bg-red-900/60 px-4 py-3 text-sm font-semibold hover:bg-red-900">
                        خروج
                    </button>
                </form>
            </div>
        </header>

        @yield('content')
    </div>
</body>
</html>
