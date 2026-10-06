<?php

namespace App\Services\Acumatica;

class SalesOrderService
{
    public function __construct(
        private readonly AcumaticaService $acumatica
    ) {}

    public function getSalesOrder(
        string $orderType,
        string $orderNbr
    ): array {
        return $this->acumatica
            ->get("SalesOrder/{$orderType}/{$orderNbr}")
            ->json();
    }
}