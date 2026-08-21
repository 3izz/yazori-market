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
            <p class="text-xs text-slate-400 mb-4">يتم أخذ نسخة احتياطية تلقائياً كل يومين عند استخدام البرنامج.</p>

            <form method="POST" action="{{ route('settings.backup') }}" class="mb-5">
                @csrf
                <button type="submit" class="rounded-lg bg-slate-800 text-white font-bold px-6 py-3 hover:bg-slate-900">
                    إنشاء نسخة احتياطية الآن
                </button>
            </form>

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
    </div>
@endsection
