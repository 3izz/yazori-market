@extends('layouts.app')

@section('title', 'تعديل منتج')

@section('content')
    <h1 class="text-xl font-bold text-slate-800 mb-5">تعديل منتج: {{ $product->name }}</h1>

    <div class="bg-white rounded-xl shadow-sm p-5 max-w-3xl">
        <form method="POST" action="{{ route('products.update', $product) }}">
            @csrf
            @method('PUT')
            @include('products._form')
        </form>
    </div>
@endsection
