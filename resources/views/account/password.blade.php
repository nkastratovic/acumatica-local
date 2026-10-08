@extends('layouts.app')

@section('title', 'Change password')

@section('content')
<h1>Change password</h1>

<form method="POST" action="{{ route('account.password.update') }}" class="card" style="max-width: 520px;">
    @csrf
    @method('PUT')

    <div class="field">
        <label for="current_password">Current password</label>
        <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
    </div>

    <div class="field">
        <label for="password">New password</label>
        <input type="password" id="password" name="password" required autocomplete="new-password">
        <div class="hint">At least 12 characters, with upper and lower case letters and a number.</div>
    </div>

    <div class="field">
        <label for="password_confirmation">Confirm new password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
    </div>

    <button type="submit">Update password</button>
    <p class="hint">Changing your password signs you out of other browsers.</p>
</form>
@endsection
