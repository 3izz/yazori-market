@extends('layouts.app')

@section('title', 'المنتجات')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <h1 class="text-xl font-bold text-slate-800">المنتجات</h1>
        <a href="{{ route('products.create') }}" class="rounded-lg bg-emerald-700 text-white font-bold px-5 py-3 hover:bg-emerald-800">
            + إضافة منتج
        </a>
    </div>

    <form method="GET" class="bg-white rounded-xl shadow-sm p-4 mb-5 flex flex-wrap gap-3">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="ابحث بالاسم أو الباركود"
               class="flex-1 min-w-[200px] rounded-lg border border-slate-300 px-4 py-3">
        <select name="category_id" class="rounded-lg border border-slate-300 px-4 py-3">
            <option value="">كل التصنيفات</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-lg bg-slate-800 text-white font-semibold px-5 py-3">بحث</button>
    </form>

    <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
        <table class="w-full text-sm min-w-[700px]">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="text-right px-4 py-3">الاسم</th>
                    <th class="text-right px-4 py-3">الباركود</th>
                    <th class="text-right px-4 py-3">التصنيف</th>
                    <th class="text-right px-4 py-3">سعر البيع</th>
                    <th class="text-right px-4 py-3">الكمية</th>
                    <th class="text-right px-4 py-3">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr class="border-t {{ $product->isLowStock() ? 'bg-red-50' : '' }}">
                        <td class="px-4 py-3 font-medium">{{ $product->name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $product->barcode ?: '—' }}</td>
                        <td class="px-4 py-3">{{ $product->category?->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ number_format($product->sale_price, 2) }}</td>
                        <td class="px-4 py-3 {{ $product->isLowStock() ? 'text-red-600 font-bold' : '' }}">{{ $product->quantity }} {{ $product->unit }}</td>
                        <td class="px-4 py-3">
                            <div class="flex gap-3">
                                <a href="{{ route('products.edit', $product) }}" class="text-emerald-700 font-semibold">تعديل</a>
                                <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('حذف هذا المنتج؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 font-semibold">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-500">لا توجد منتجات</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $products->links() }}
    </div>
@endsection
