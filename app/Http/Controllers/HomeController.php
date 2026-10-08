<?php

namespace App\Http\Controllers;

use App\Enums\Ability;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        if (Gate::allows(Ability::SalesOrdersView->value)) {
            return redirect()->route('acumatica.sales-orders.create');
        }

        return view('home');
    }
}
