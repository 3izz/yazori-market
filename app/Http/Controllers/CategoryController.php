<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->withCount('products')
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
        ], [], ['name' => 'اسم التصنيف']);

        Category::create($data);

        return back()->with('status', 'تمت إضافة التصنيف بنجاح');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name,'.$category->id],
        ], [], ['name' => 'اسم التصنيف']);

        $category->update($data);

        return back()->with('status', 'تم تعديل التصنيف بنجاح');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->with('error', 'لا يمكن حذف تصنيف يحتوي على منتجات، انقل المنتجات أولاً.');
        }

        $category->delete();

        return back()->with('status', 'تم حذف التصنيف');
    }
}
