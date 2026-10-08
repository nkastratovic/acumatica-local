@extends('layouts.app')

@section('title', $role->exists ? 'Edit role' : 'New role')

@section('content')
<h1>{{ $role->exists ? 'Edit role' : 'New role' }}</h1>

<form method="POST" action="{{ $role->exists ? route('admin.roles.update', $role) : route('admin.roles.store') }}" class="card">
    @csrf
    @if ($role->exists) @method('PUT') @endif

    <div class="field">
        <label for="name">Key</label>
        @if ($role->isAdmin())
            <input type="text" id="name" value="{{ $role->name }}" disabled>
        @else
            <input type="text" id="name" name="name" value="{{ old('name', $role->name) }}" required pattern="[a-z0-9-]+">
            <div class="hint">Lowercase letters, numbers and dashes, e.g. <code>warehouse</code>.</div>
        @endif
    </div>

    <div class="field">
        <label for="label">Label</label>
        <input type="text" id="label" name="label" value="{{ old('label', $role->label) }}" required>
    </div>

    <div class="field">
        <label for="description">Description</label>
        <input type="text" id="description" name="description" value="{{ old('description', $role->description) }}">
    </div>

    <div class="field">
        <label>Permissions</label>
        @if ($role->isAdmin())
            <p class="hint">Administrators always have every permission.</p>
        @else
            @php($selected = old('permissions', $role->exists ? $role->permissions->pluck('id')->all() : []))
            @foreach ($permissions as $permission)
                <label class="check">
                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" @checked(in_array($permission->id, $selected))>
                    <span>{{ $permission->label }} <span class="hint">({{ $permission->name }}) {{ $permission->description }}</span></span>
                </label>
            @endforeach
        @endif
    </div>

    <button type="submit">Save</button>
    <a href="{{ route('admin.roles.index') }}">Cancel</a>
</form>
@endsection
