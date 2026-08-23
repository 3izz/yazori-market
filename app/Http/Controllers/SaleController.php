<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Services\ThermalPrintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(Request $request): View
    {
        $query = Sale::query();

        if ($date = $request->date('date')) {
            $query->whereDate('created_at', $date);
        }

        $sales = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('sales.index', compact('sales'));
    }

    public function show(Sale $sale): View
    {
        $sale->load('items');

        return view('sales.show', compact('sale'));
    }

    public function print(Sale $sale): View
    {
        $sale->load('items');

        return view('sales.print', compact('sale'));
    }

    public function printThermal(Sale $sale, ThermalPrintService $printer): JsonResponse
    {
        $result = $printer->printSale($sale);

        return response()->json($result, $result['success'] ? 200 : 422);
    }
}
