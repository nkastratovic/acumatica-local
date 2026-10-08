<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Acumatica\SalesOrderService;
use Illuminate\Http\JsonResponse;

class SalesOrderController extends Controller
{
    public function __invoke(
        string $orderType,
        string $orderNbr,
        SalesOrderService $salesOrderService
    ): JsonResponse {
        return response()->json(
            $salesOrderService->getSalesOrder($orderType, $orderNbr)
        );
    }
}
