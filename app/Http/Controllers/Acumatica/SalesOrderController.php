<?php

namespace App\Http\Controllers\Acumatica;

use App\Http\Controllers\Controller;
use App\Services\Acumatica\SalesOrderService;
use Illuminate\View\View;

class SalesOrderController extends Controller
{
    public function create(): View
    {
        return view('acumatica.sales-orders.create');
    }

    public function show(
        string $orderType,
        string $orderNbr,
        SalesOrderService $salesOrderService
    ): View {
        $salesOrder = $salesOrderService->getSalesOrder(
            $orderType,
            $orderNbr
        );

        return view('acumatica.sales-orders.show', [
            'salesOrder' => $salesOrder,
        ]);
    }
}