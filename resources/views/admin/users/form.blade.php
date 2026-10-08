@extends('layouts.app')

@section('title', $user->exists ? 'Edit user' : 'New user')

@section('content')
<h1>{{ $user->exists ? 'Edit user' : 'New user' }}</h1>

<form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="card">
    @csrf
    @if ($user->exists) @method('PUT') @endif

    <div class="field">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
    </div>

    <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
    </div>

    <div class="field">
        <label for="password">{{ $user->exists ? 'Reset password' : 'Temporary password' }}</label>
        <input type="password" id="password" name="password" autocomplete="new-password" @required(! $user->exists)>
        <div class="hint">
            {{ $user->exists ? 'Leave blank to keep the current password. ' : '' }}The user must change it at next sign-in.
        </div>
    </div>

    <div class="field">
        <label for="password_confirmation">Confirm password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" @required(! $user->exists)>
    </div>

    <div class="field">
        <label>Roles</label>
        @php($selected = old('roles', $user->exists ? $user->roles->pluck('id')->all() : []))
        @foreach ($roles as $role)
            <label class="check">
                <input type="checkbox" name="roles[]" value="{{ $role->id }}" @checked(in_array($role->id, $selected))>
                <span>{{ $role->label }} <span class="hint">{{ $role->description }}</span></span>
            </label>
        @endforeach
    </div>

    @if ($user->exists)
        <div class="field">
            <input type="hidden" name="is_active" value="0">
            <label class="check">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active))>
                <span>Active <span class="hint">Disabling signs the user out and revokes all their API tokens.</span></span>
            </label>
        </div>
    @else
        <input type="hidden" name="is_active" value="1">
    @endif

    <button type="submit">Save</button>
    <a href="{{ route('admin.users.index') }}">Cancel</a>
</form>
@endsection
