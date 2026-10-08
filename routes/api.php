<?php

use App\Http\Controllers\Api\SalesOrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
 * All routes here are prefixed with /api and require a Sanctum bearer token:
 *   Authorization: Bearer <token>
 *   Accept: application/json
 *
 * Authorization uses the same Gates as the web app; a request succeeds only
 * if the user's roles grant the permission AND the token was issued with it.
 */
Route::middleware(['auth:sanctum', 'active', 'password.changed', 'throttle:api'])->group(function () {
    Route::get('/user', function (Request $request) {
        $user = $request->user();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->roles->pluck('name'),
            'permissions' => $user->permissionNames(),
            'token_abilities' => $user->currentAccessToken()?->abilities,
        ];
    })->name('api.user');

    Route::get('/acumatica/sales-orders/{orderType}/{orderNbr}', SalesOrderController::class)
        ->middleware('can:sales-orders.view')
        ->name('api.acumatica.sales-orders.show');
});
