<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'new_quantity' => ['required', 'numeric', 'min:0'],
            'reason' => ['required', 'string', 'max:150'],
        ], [], [
            'new_quantity' => 'الكمية الجديدة',
            'reason' => 'السبب',
        ]);

        DB::transaction(function () use ($data) {
            $product = Product::query()->lockForUpdate()->findOrFail($data['product_id']);
            $before = $product->quantity;
            $after = $data['new_quantity'];

            $product->update(['quantity' => $after]);

            StockAdjustment::create([
                'product_id' => $product->id,
                'quantity_before' => $before,
                'quantity_after' => $after,
                'change' => $after - $before,
                'reason' => $data['reason'],
                'user_id' => Auth::id(),
            ]);
        });

        return back()->with('status', 'تم تعديل الكمية وتسجيل السبب');
    }
}
