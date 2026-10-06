<?php

namespace App\Jobs\Acumatica;

use App\Services\Acumatica\SalesOrderService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GetSalesOrder implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $orderType,
        public string $orderNbr
    ) {}

    public function handle(SalesOrderService $salesOrderService): void
    {
        $salesOrder = $salesOrderService->getSalesOrder(
            $this->orderType,
            $this->orderNbr
        );

        Log::info('Acumatica Sales Order retrieved', [
            'order_type' => $this->orderType,
            'order_nbr' => $this->orderNbr,
            'sales_order' => $salesOrder,
        ]);
    }
}