@extends('layouts.app')

@section('title', 'Roles')

@section('content')
<div class="row-actions" style="justify-content: space-between;">
    <h1>Roles</h1>
    <a class="button" href="{{ route('admin.roles.create') }}">New role</a>
</div>

<table>
    <thead>
        <tr><th>Role</th><th>Permissions</th><th>Users</th><th></th></tr>
    </thead>
    <tbody>
    @foreach ($roles as $role)
        <tr>
            <td>
                <strong>{{ $role->label }}</strong> <span class="hint">({{ $role->name }})</span>
                <div class="hint">{{ $role->description }}</div>
            </td>
            <td>
                @if ($role->isAdmin())
                    <span class="badge">all permissions</span>
                @else
                    @forelse ($role->permissions as $permission)
                        <span class="badge" title="{{ $permission->description }}">{{ $permission->name }}</span>
                    @empty
                        <span class="hint">none</span>
                    @endforelse
                @endif
            </td>
            <td>{{ $role->users_count }}</td>
            <td class="row-actions">
                <a href="{{ route('admin.roles.edit', $role) }}">Edit</a>
                @unless ($role->isAdmin())
                    <form method="POST" action="{{ route('admin.roles.destroy', $role) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="link">Delete</button>
                    </form>
                @endunless
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
@endsection
