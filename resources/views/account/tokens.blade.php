@extends('layouts.app')

@section('title', 'API tokens')

@section('content')
<h1>API tokens</h1>

@if (session('plainTextToken'))
    <div class="card">
        <strong>Copy your new token now. It won't be shown again.</strong>
        <p class="token">{{ session('plainTextToken') }}</p>
        <p class="hint">Send it as <code>Authorization: Bearer &lt;token&gt;</code> with <code>Accept: application/json</code>.</p>
    </div>
@endif

<div class="card">
    <h2>Create token</h2>
    @if (empty($abilities))
        <p>Your roles don't grant any permission that can be used through the API.</p>
    @else
        <form method="POST" action="{{ route('account.tokens.store') }}">
            @csrf
            <div class="field">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="e.g. Warehouse sync" required maxlength="100">
            </div>

            <div class="field">
                <label>Abilities</label>
                @foreach ($abilities as $ability)
                    <label class="check">
                        <input type="checkbox" name="abilities[]" value="{{ $ability->value }}" @checked(in_array($ability->value, old('abilities', [$ability->value])))>
                        <span>{{ $ability->label() }} <span class="hint">({{ $ability->value }})</span></span>
                    </label>
                @endforeach
            </div>

            <div class="field">
                <label for="expires">Expires</label>
                <select id="expires" name="expires">
                    @foreach ($expiryOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('expires', '60') == $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit">Create token</button>
        </form>
    @endif
</div>

<h2>Your tokens</h2>
<table>
    <thead>
        <tr><th>Name</th><th>Abilities</th><th>Last used</th><th>Expires</th><th></th></tr>
    </thead>
    <tbody>
    @forelse ($tokens as $token)
        <tr>
            <td>{{ $token->name }}</td>
            <td>@foreach ($token->abilities as $a)<span class="badge">{{ $a }}</span>@endforeach</td>
            <td>{{ $token->last_used_at?->diffForHumans() ?? 'Never' }}</td>
            <td>
                @if ($token->expires_at === null) Never
                @elseif ($token->expires_at->isPast()) <span class="badge off">Expired</span>
                @else {{ $token->expires_at->format('Y-m-d H:i') }}
                @endif
            </td>
            <td>
                <form method="POST" action="{{ route('account.tokens.destroy', $token->id) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="danger">Revoke</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="5">No tokens yet.</td></tr>
    @endforelse
    </tbody>
</table>
@endsection
