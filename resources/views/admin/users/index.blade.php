@extends('layouts.app')

@section('title', 'Users')

@section('content')
<div class="row-actions" style="justify-content: space-between;">
    <h1>Users</h1>
    <a class="button" href="{{ route('admin.users.create') }}">New user</a>
</div>

<table>
    <thead>
        <tr><th>Name</th><th>Email</th><th>Roles</th><th>Status</th><th>Last sign-in</th><th>API tokens</th><th></th></tr>
    </thead>
    <tbody>
    @foreach ($users as $user)
        <tr>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>
                @forelse ($user->roles as $role)
                    <span class="badge">{{ $role->label }}</span>
                @empty
                    <span class="hint">none</span>
                @endforelse
            </td>
            <td>
                @if ($user->is_active)
                    Active
                @else
                    <span class="badge off">Disabled</span>
                @endif
                @if ($user->must_change_password)
                    <div class="hint">temporary password</div>
                @endif
            </td>
            <td>{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</td>
            <td>
                {{ $user->tokens_count }}
                @if ($user->tokens_count > 0)
                    <form method="POST" action="{{ route('admin.users.tokens.destroy', $user) }}" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="link">revoke all</button>
                    </form>
                @endif
            </td>
            <td><a href="{{ route('admin.users.edit', $user) }}">Edit</a></td>
        </tr>
    @endforeach
    </tbody>
</table>

{{ $users->links() }}
@endsection
