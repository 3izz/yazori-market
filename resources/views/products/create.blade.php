@extends('layouts.app')

@section('title', 'إضافة منتج')

@section('content')
    <h1 class="text-xl font-bold text-slate-800 mb-5">إضافة منتج جديد</h1>

    <div class="bg-white rounded-xl shadow-sm p-5 max-w-3xl">
        <form method="POST" action="{{ route('products.store') }}">
            @csrf
            @include('products._form', ['product' => null])
        </form>
    </div>
@endsection
