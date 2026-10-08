@extends('layouts.app')

@section('title', 'Sign in')

@section('content')
<div class="card" style="max-width: 460px; margin: 60px auto;">
    <h1>Sign in</h1>

    <form method="POST" action="{{ route('login.store') }}">
        @csrf

        <div class="field">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">
        </div>

        <label class="check">
            <input type="checkbox" name="remember" value="1"> Keep me signed in
        </label>

        <br>
        <button type="submit">Sign in</button>
    </form>

    <p class="hint">Accounts are created by an administrator.</p>
</div>
@endsection
