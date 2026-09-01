<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>تسجيل الدخول - {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-emerald-900 via-emerald-800 to-emerald-950 flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
        <div class="flex flex-col items-center mb-8 text-center">
            <img src="{{ asset('images/logo.png') }}" alt="اليازوري ماركت" class="h-28 w-28 rounded-full shadow-lg mb-4 object-cover">
            <h1 class="text-2xl font-extrabold text-white">اليازوري ماركت</h1>
            <p class="text-emerald-200 text-sm mt-1">Al-Yazori Market</p>
        </div>

        <div class="bg-white rounded-2xl shadow-2xl p-6">
            <h2 class="text-lg font-bold text-slate-800 mb-4 text-center">تسجيل الدخول</h2>

            @if ($errors->any())
                <div class="mb-4 rounded-lg bg-red-100 border border-red-300 px-4 py-3 text-red-800 text-sm">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">اسم المستخدم</label>
                    <input type="text" name="username" value="{{ old('username') }}" autofocus required
                           class="w-full rounded-xl border border-slate-300 px-4 py-3 text-lg focus:border-emerald-600 focus:ring-emerald-600">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">كلمة السر</label>
                    <input type="password" name="password" required
                           class="w-full rounded-xl border border-slate-300 px-4 py-3 text-lg focus:border-emerald-600 focus:ring-emerald-600">
                </div>
                <button type="submit"
                        class="w-full rounded-xl bg-emerald-700 py-3 text-lg font-bold text-white hover:bg-emerald-800 active:bg-emerald-900">
                    دخول
                </button>
            </form>
        </div>

        <a href="{{ route('pos.unlock') }}" class="block text-center text-emerald-200 text-sm mt-4 hover:text-white">
            دخول نقطة البيع فقط (رقم سري)
        </a>
    </div>
</body>
</html>
