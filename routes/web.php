<?php

use App\Http\Controllers\Acumatica\SalesOrderController;
use Illuminate\Support\Facades\Route;

Route::get(
    '/acumatica/sales-orders',
    [SalesOrderController::class, 'create']
)->name('acumatica.sales-orders.create');

Route::get(
    '/acumatica/sales-orders/{orderType}/{orderNbr}',
    [SalesOrderController::class, 'show']
)->name('acumatica.sales-orders.show');