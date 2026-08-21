@extends('layouts.app')

@section('title', 'التصنيفات')

@section('content')
    <div class="flex items-center justify-between mb-5">
        <h1 class="text-xl font-bold text-slate-800">التصنيفات الرئيسية</h1>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
        <div class="md:col-span-1 bg-white rounded-xl shadow-sm p-4 h-fit">
            <h2 class="font-bold text-slate-700 mb-3">إضافة تصنيف جديد</h2>
            <form method="POST" action="{{ route('categories.store') }}" class="space-y-3">
                @csrf
                <input type="text" name="name" placeholder="اسم التصنيف" required
                       class="w-full rounded-lg border border-slate-300 px-4 py-3 text-lg focus:border-emerald-600 focus:ring-emerald-600">
                <button type="submit" class="w-full rounded-lg bg-emerald-700 text-white font-bold py-3 hover:bg-emerald-800">
                    إضافة
                </button>
            </form>
        </div>

        <div class="md:col-span-2 bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="text-right px-4 py-3">الاسم</th>
                        <th class="text-right px-4 py-3">عدد المنتجات</th>
                        <th class="text-right px-4 py-3">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr class="border-t">
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('categories.update', $category) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="name" value="{{ $category->name }}"
                                           class="rounded-lg border border-slate-300 px-3 py-2 w-full max-w-xs">
                                    <button type="submit" class="text-emerald-700 font-semibold text-xs shrink-0">حفظ</button>
                                </form>
                            </td>
                            <td class="px-4 py-3">{{ $category->products_count }}</td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('categories.destroy', $category) }}"
                                      onsubmit="return confirm('حذف هذا التصنيف؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 font-semibold text-xs">حذف</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-6 text-center text-slate-500">لا توجد تصنيفات بعد</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
