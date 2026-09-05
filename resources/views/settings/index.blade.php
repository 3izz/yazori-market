@extends('layouts.app')

@section('title', 'الإعدادات')

@section('content')
    <h1 class="text-xl font-bold text-slate-800 mb-5">الإعدادات</h1>

    <div class="grid md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h2 class="font-bold text-slate-700 mb-4">تغيير كلمة السر</h2>
            <form method="POST" action="{{ route('settings.password') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">كلمة السر الحالية</label>
                    <input type="password" name="current_password" required
                           class="w-full rounded-lg border border-slate-300 px-4 py-3">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">كلمة السر الجديدة</label>
                    <input type="password" name="password" required
                           class="w-full rounded-lg border border-slate-300 px-4 py-3">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">تأكيد كلمة السر الجديدة</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full rounded-lg border border-slate-300 px-4 py-3">
                </div>
                <button type="submit" class="rounded-lg bg-emerald-700 text-white font-bold px-6 py-3 hover:bg-emerald-800">
                    حفظ كلمة السر
                </button>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5">
            <h2 class="font-bold text-slate-700 mb-4">النسخ الاحتياطي</h2>
            <p class="text-sm text-slate-500 mb-2">
                آخر نسخة احتياطية:
                <span class="font-semibold text-slate-700">
                    {{ $lastBackupAt ? \Illuminate\Support\Carbon::parse($lastBackupAt)->format('Y-m-d H:i') : 'لم تتم بعد' }}
                </span>
            </p>
            <p class="text-xs text-slate-400 mb-4">يتم أخذ نسخة احتياطية تلقائياً كل 6 ساعات عند استخدام البرنامج، وتُحفظ داخل الجهاز دائماً.</p>

            <form method="POST" action="{{ route('settings.backup') }}" class="mb-5">
                @csrf
                <button type="submit" class="rounded-lg bg-slate-800 text-white font-bold px-6 py-3 hover:bg-slate-900">
                    إنشاء نسخة احتياطية الآن
                </button>
            </form>

            <hr class="my-5">

            <h3 class="text-sm font-bold text-slate-600 mb-2">مكان إضافي للنسخة الاحتياطية (اختياري)</h3>
            <p class="text-xs text-slate-400 mb-3">
                مثلاً فلاشة أو قرص خارجي دائم التوصيل — إذا انعطب الجهاز نفسه، بتضل عندك نسخة بمكان تاني.
                اكتب المسار كامل، مثلاً: <code class="bg-slate-100 px-1 rounded">D:\نسخ-احتياطية</code>
            </p>
            <form method="POST" action="{{ route('settings.backupPath') }}" class="space-y-3">
                @csrf
                <input type="text" name="backup_external_path" value="{{ old('backup_external_path', $backupExternalPath) }}"
                       placeholder="مثال: D:\نسخ-احتياطية"
                       class="w-full rounded-lg border border-slate-300 px-4 py-3" dir="ltr">
                <button type="submit" class="rounded-lg bg-emerald-700 text-white font-bold px-6 py-3 hover:bg-emerald-800">
                    حفظ المكان
                </button>
            </form>

            <hr class="my-5">

            <h3 class="text-sm font-bold text-slate-600 mb-2">آخر النسخ المحفوظة</h3>
            @if ($backups->isEmpty())
                <p class="text-sm text-slate-400">لا توجد نسخ محفوظة بعد</p>
            @else
                <ul class="text-sm divide-y">
                    @foreach ($backups as $backup)
                        <li class="py-2 flex items-center justify-between">
                            <span class="text-slate-600">{{ $backup['name'] }}</span>
                            <span class="text-slate-400 text-xs">{{ $backup['date'] }} — {{ $backup['size'] }} كيلوبايت</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5">
            <h2 class="font-bold text-slate-700 mb-4">طابعة الفواتير الحرارية</h2>
            <form method="POST" action="{{ route('settings.printer') }}" class="space-y-3 mb-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">اسم الطابعة كما يظهر في ويندوز</label>
                    <input type="text" name="printer_name" value="{{ old('printer_name', $printerName) }}" required
                           class="w-full rounded-lg border border-slate-300 px-4 py-3">
                </div>
                <button type="submit" class="rounded-lg bg-emerald-700 text-white font-bold px-6 py-3 hover:bg-emerald-800">
                    حفظ اسم الطابعة
                </button>
            </form>

            <button type="button" id="test-print-btn"
                    class="rounded-lg bg-slate-800 text-white font-bold px-6 py-3 hover:bg-slate-900">
                طباعة تجريبية
            </button>
            <p id="test-print-result" class="text-sm mt-2"></p>

            <hr class="my-5">

            <form method="POST" action="{{ route('settings.invoiceOptions') }}">
                @csrf
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 mb-3">
                    <input type="checkbox" name="show_paid_change" value="1" @checked($showPaidChange) class="h-5 w-5">
                    إظهار "المدفوع" و"الباقي" على الفاتورة المطبوعة
                </label>
                <button type="submit" class="rounded-lg bg-emerald-700 text-white font-bold px-6 py-3 hover:bg-emerald-800">
                    حفظ
                </button>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5">
            <h2 class="font-bold text-slate-700 mb-4">الرقم السري لنقطة البيع</h2>
            <p class="text-xs text-slate-400 mb-4">
                هاد الرقم يسمح لأي حد يستخدم نقطة البيع بس، بدون الدخول الكامل على النظام (المنتجات، التقارير، الإعدادات...).
            </p>
            <form method="POST" action="{{ route('settings.posPin') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">الرقم السري (أرقام فقط، 4 إلى 8 خانات)</label>
                    <input type="text" name="pos_pin" value="{{ old('pos_pin', $posPin) }}" inputmode="numeric" required
                           class="w-full rounded-lg border border-slate-300 px-4 py-3">
                </div>
                <button type="submit" class="rounded-lg bg-emerald-700 text-white font-bold px-6 py-3 hover:bg-emerald-800">
                    حفظ الرقم السري
                </button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('test-print-btn').addEventListener('click', async (e) => {
            const btn = e.currentTarget;
            const resultEl = document.getElementById('test-print-result');
            btn.disabled = true;
            resultEl.textContent = 'جارٍ الطباعة...';
            resultEl.className = 'text-sm mt-2 text-slate-500';

            try {
                const res = await fetch('{{ route('settings.printTest') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                const data = await res.json();
                resultEl.textContent = data.message;
                resultEl.className = 'text-sm mt-2 ' + (res.ok ? 'text-emerald-700' : 'text-red-600');
            } catch (e) {
                resultEl.textContent = 'حدث خطأ أثناء الطباعة';
                resultEl.className = 'text-sm mt-2 text-red-600';
            } finally {
                btn.disabled = false;
            }
        });
    </script>
@endsection
