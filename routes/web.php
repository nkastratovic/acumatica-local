<?php

use App\Http\Controllers\Account\ApiTokenController;
use App\Http\Controllers\Account\PasswordController;
use App\Http\Controllers\Acumatica\SalesOrderController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

// Guests
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

// Signed-in users
Route::middleware(['auth', 'auth.session', 'active'])->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/account/password', [PasswordController::class, 'edit'])->name('account.password.edit');
    Route::put('/account/password', [PasswordController::class, 'update'])->name('account.password.update');

    Route::middleware('password.changed')->group(function () {
        Route::get('/', HomeController::class)->name('home');

        // Acumatica
        Route::middleware('can:sales-orders.view')->group(function () {
            Route::get(
                '/acumatica/sales-orders',
                [SalesOrderController::class, 'create']
            )->name('acumatica.sales-orders.create');

            Route::get(
                '/acumatica/sales-orders/{orderType}/{orderNbr}',
                [SalesOrderController::class, 'show']
            )->name('acumatica.sales-orders.show');
        });

        // Personal API tokens
        Route::middleware('can:api-tokens.manage')->group(function () {
            Route::get('/account/tokens', [ApiTokenController::class, 'index'])->name('account.tokens.index');
            Route::post('/account/tokens', [ApiTokenController::class, 'store'])->name('account.tokens.store');
            Route::delete('/account/tokens/{tokenId}', [ApiTokenController::class, 'destroy'])->name('account.tokens.destroy');
        });

        // Administration
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::middleware('can:users.manage')->group(function () {
                Route::resource('users', UserController::class)->except(['show', 'destroy']);
                Route::delete('users/{user}/tokens', [UserController::class, 'revokeTokens'])->name('users.tokens.destroy');
            });

            Route::middleware('can:roles.manage')->group(function () {
                Route::resource('roles', RoleController::class)->except(['show']);
            });
        });
    });
});
