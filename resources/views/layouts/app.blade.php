<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>@yield('title', 'الرئيسية') - {{ config('app.name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-800 antialiased">
    <div class="flex min-h-screen">
        <aside class="hidden md:flex md:w-60 md:flex-col bg-emerald-900 text-emerald-50">
            <div class="flex items-center gap-3 px-5 py-5 border-b border-emerald-800">
                <img src="{{ asset('images/logo.png') }}" alt="اليازوري ماركت" class="h-11 w-11 rounded-full shrink-0 object-cover">
                <div class="leading-tight">
                    <div class="font-bold">اليازوري ماركت</div>
                    <div class="text-xs text-emerald-300">Al-Yazori Market</div>
                </div>
            </div>
            <nav class="flex-1 overflow-y-auto py-3 text-sm">
                @php
                    $navItems = [
                        ['route' => 'dashboard', 'label' => 'لوحة التحكم'],
                        ['route' => 'pos.index', 'label' => 'نقطة البيع'],
                        ['route' => 'products.index', 'label' => 'المنتجات'],
                        ['route' => 'categories.index', 'label' => 'التصنيفات'],
                        ['route' => 'purchases.index', 'label' => 'المشتريات'],
                        ['route' => 'inventory.index', 'label' => 'الجرد والمخزون'],
                        ['route' => 'sales.index', 'label' => 'سجل المبيعات'],
                        ['route' => 'settings.index', 'label' => 'الإعدادات'],
                    ];
                @endphp
                @foreach ($navItems as $item)
                    <a href="{{ route($item['route']) }}"
                       class="block px-5 py-3 border-r-4 {{ request()->routeIs($item['route'].'*') ? 'border-emerald-300 bg-emerald-800 font-semibold' : 'border-transparent hover:bg-emerald-800/60' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
            <form method="POST" action="{{ route('logout') }}" class="border-t border-emerald-800 p-3">
                @csrf
                <button type="submit" class="w-full rounded-lg bg-emerald-800 py-3 text-sm font-semibold hover:bg-emerald-700">
                    تسجيل الخروج
                </button>
            </form>
        </aside>

        <div class="flex flex-1 flex-col min-w-0">
            <header class="flex items-center justify-between bg-white px-4 py-3 shadow-sm md:hidden">
                <div class="font-bold text-emerald-900">اليازوري ماركت</div>
                <a href="{{ route('pos.index') }}" class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white">نقطة البيع</a>
            </header>

            <nav class="flex gap-1 overflow-x-auto bg-white px-2 py-2 shadow-sm md:hidden text-xs">
                @foreach ($navItems as $item)
                    <a href="{{ route($item['route']) }}"
                       class="shrink-0 rounded-full px-3 py-2 {{ request()->routeIs($item['route'].'*') ? 'bg-emerald-700 text-white font-semibold' : 'bg-slate-100 text-slate-600' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="shrink-0 rounded-full bg-red-50 px-3 py-2 text-red-600 font-semibold">خروج</button>
                </form>
            </nav>

            <main class="flex-1 p-4 md:p-6">
                @if (session('status'))
                    <div class="mb-4 rounded-lg bg-emerald-100 border border-emerald-300 px-4 py-3 text-emerald-800">
                        {{ session('status') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-4 rounded-lg bg-red-100 border border-red-300 px-4 py-3 text-red-800">
                        {{ session('error') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 rounded-lg bg-red-100 border border-red-300 px-4 py-3 text-red-800">
                        <ul class="list-disc pr-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
