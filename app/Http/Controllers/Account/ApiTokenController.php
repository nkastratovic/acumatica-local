<?php

namespace App\Http\Controllers\Account;

use App\Enums\Ability;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ApiTokenController extends Controller
{
    /** Allowed lifetimes in minutes. */
    public const EXPIRY_OPTIONS = ['10' => '10 minutes', '20' => '20 minutes', '30' => '30 minutes', '60' => '1 hour', '120' => '2 hours', '360' => '6 hours'];

    public function index(Request $request): View
    {
        $user = $request->user();

        return view('account.tokens', [
            'tokens' => $user->tokens()->latest()->get(),
            'abilities' => $this->grantableAbilities($request),
            'expiryOptions' => self::EXPIRY_OPTIONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $grantable = array_map(fn (Ability $a) => $a->value, $this->grantableAbilities($request));

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'abilities' => ['required', 'array', 'min:1'],
            'abilities.*' => ['string', Rule::in($grantable)],
            'expires' => ['required', Rule::in(array_keys(self::EXPIRY_OPTIONS))],
        ]);

        $expiresAt = now()->addMinutes((int) $validated['expires']);

        $token = $request->user()->createToken(
            $validated['name'],
            array_values(array_unique($validated['abilities'])),
            $expiresAt,
        );

        Log::info('API token created', [
            'user_id' => $request->user()->id,
            'token_id' => $token->accessToken->id,
            'abilities' => $token->accessToken->abilities,
        ]);

        // The plain-text token is shown exactly once.
        return redirect()->route('account.tokens.index')->with('plainTextToken', $token->plainTextToken);
    }

    public function destroy(Request $request, int $tokenId): RedirectResponse
    {
        $request->user()->tokens()->whereKey($tokenId)->firstOrFail()->delete();

        return redirect()->route('account.tokens.index')->with('status', 'Token revoked.');
    }

    /**
     * A user can only put abilities on a token that their roles grant.
     *
     * @return list<Ability>
     */
    private function grantableAbilities(Request $request): array
    {
        return array_values(array_filter(
            Ability::tokenAbilities(),
            fn (Ability $a) => $request->user()->hasPermission($a),
        ));
    }
}
