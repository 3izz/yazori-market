<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>رقم سري إداري - {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-emerald-900 via-emerald-800 to-emerald-950 flex items-center justify-center p-4">
    <div class="w-full max-w-xs">
        <div class="flex flex-col items-center mb-6 text-center">
            <img src="{{ asset('images/logo.png') }}" alt="اليازوري ماركت" class="h-20 w-20 rounded-full shadow-lg mb-3 object-cover">
            <h1 class="text-xl font-extrabold text-white">اليازوري ماركت</h1>
            <p class="text-emerald-200 text-sm mt-1">أدخل الرقم السري الإداري للمتابعة</p>
        </div>

        <div class="bg-white rounded-2xl shadow-2xl p-6">
            @if ($errors->any())
                <div class="mb-4 rounded-lg bg-red-100 border border-red-300 px-4 py-3 text-red-800 text-sm text-center">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.pin.verify') }}" id="pin-form">
                @csrf
                <input type="password" name="pin" id="pin-display" readonly inputmode="none"
                       class="w-full rounded-xl border border-slate-300 px-4 py-4 text-3xl text-center tracking-[0.5em] mb-4"
                       placeholder="----">

                <div class="grid grid-cols-3 gap-3">
                    @foreach (['1','2','3','4','5','6','7','8','9'] as $digit)
                        <button type="button" data-digit="{{ $digit }}"
                                class="touch-btn rounded-xl bg-slate-100 hover:bg-slate-200 text-2xl font-bold py-4">
                            {{ $digit }}
                        </button>
                    @endforeach
                    <button type="button" id="pin-clear" class="touch-btn rounded-xl bg-red-100 hover:bg-red-200 text-red-700 text-lg font-bold py-4">مسح</button>
                    <button type="button" data-digit="0" class="touch-btn rounded-xl bg-slate-100 hover:bg-slate-200 text-2xl font-bold py-4">0</button>
                    <button type="button" id="pin-back" class="touch-btn rounded-xl bg-slate-100 hover:bg-slate-200 text-lg font-bold py-4">⌫</button>
                </div>

                <button type="submit" class="touch-btn w-full rounded-xl bg-emerald-700 py-4 text-lg font-bold text-white hover:bg-emerald-800 mt-4">
                    دخول
                </button>
            </form>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="mt-4">
            @csrf
            <button type="submit" class="block w-full text-center text-emerald-200 text-sm hover:text-white">
                تسجيل الخروج
            </button>
        </form>
    </div>

<script>
(function () {
    const display = document.getElementById('pin-display');
    let pin = '';

    function updateDisplay() {
        display.value = pin;
    }

    document.querySelectorAll('[data-digit]').forEach((btn) => {
        btn.addEventListener('click', () => {
            if (pin.length < 8) {
                pin += btn.dataset.digit;
                updateDisplay();
            }
        });
    });

    document.getElementById('pin-back').addEventListener('click', () => {
        pin = pin.slice(0, -1);
        updateDisplay();
    });

    document.getElementById('pin-clear').addEventListener('click', () => {
        pin = '';
        updateDisplay();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key >= '0' && e.key <= '9' && pin.length < 8) {
            pin += e.key;
            updateDisplay();
        } else if (e.key === 'Backspace') {
            pin = pin.slice(0, -1);
            updateDisplay();
        } else if (e.key === 'Enter') {
            document.getElementById('pin-form').submit();
        }
    });
})();
</script>
</body>
</html>
