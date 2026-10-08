<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Acumatica Integration') · {{ config('app.name') }}</title>
    <style>
        :root { --border: #d9dde3; --muted: #5f6b7a; --accent: #1f5fbf; --danger: #b42318; --ok: #067647; }
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 0; color: #1d2733; background: #f7f8fa; }
        header { background: #fff; border-bottom: 1px solid var(--border); padding: 12px 40px; display: flex; gap: 24px; align-items: center; flex-wrap: wrap; }
        header .brand { font-weight: bold; margin-right: 16px; }
        header nav { display: flex; gap: 16px; flex: 1; flex-wrap: wrap; }
        header a { color: var(--accent); text-decoration: none; }
        header .user { color: var(--muted); font-size: 14px; display: flex; gap: 12px; align-items: center; }
        main { padding: 32px 40px; max-width: 1100px; }
        h1 { margin-top: 0; }
        pre { background: #f0f2f5; padding: 20px; border-radius: 5px; overflow-x: auto; }
        table { border-collapse: collapse; width: 100%; background: #fff; }
        th, td { border-bottom: 1px solid var(--border); padding: 8px 10px; text-align: left; vertical-align: top; }
        th { background: #f0f2f5; font-size: 13px; }
        label { display: block; font-weight: bold; margin-bottom: 4px; }
        input[type=text], input[type=email], input[type=password], select, textarea { width: 100%; max-width: 420px; padding: 8px; border: 1px solid var(--border); border-radius: 4px; font: inherit; }
        .field { margin-bottom: 16px; }
        .check { font-weight: normal; display: flex; gap: 8px; align-items: flex-start; margin-bottom: 6px; }
        .hint { color: var(--muted); font-size: 13px; }
        button, .button { background: var(--accent); color: #fff; border: 0; border-radius: 4px; padding: 8px 14px; cursor: pointer; font: inherit; text-decoration: none; display: inline-block; }
        button.link { background: none; color: var(--accent); padding: 0; }
        button.danger { background: var(--danger); }
        .flash { padding: 10px 14px; border-radius: 4px; margin-bottom: 20px; background: #ecfdf3; color: var(--ok); border: 1px solid #abefc6; }
        .errors { padding: 10px 14px; border-radius: 4px; margin-bottom: 20px; background: #fef3f2; color: var(--danger); border: 1px solid #fecdca; }
        .badge { display: inline-block; font-size: 12px; padding: 2px 8px; border-radius: 10px; background: #eef2f6; margin: 0 4px 4px 0; }
        .badge.off { background: #fef3f2; color: var(--danger); }
        .token { font-family: monospace; word-break: break-all; background: #fff; border: 1px dashed var(--accent); padding: 10px; }
        .card { background: #fff; border: 1px solid var(--border); border-radius: 6px; padding: 20px; margin-bottom: 24px; }
        .row-actions { display: flex; gap: 10px; align-items: center; }
        @media (max-width: 640px) { header, main { padding-left: 16px; padding-right: 16px; } }
    </style>
</head>
<body>
@auth
<header>
    <span class="brand">{{ config('app.name') }}</span>
    <nav>
        @can('sales-orders.view')
            <a href="{{ route('acumatica.sales-orders.create') }}">Sales orders</a>
        @endcan
        @can('api-tokens.manage')
            <a href="{{ route('account.tokens.index') }}">API tokens</a>
        @endcan
        @can('users.manage')
            <a href="{{ route('admin.users.index') }}">Users</a>
        @endcan
        @can('roles.manage')
            <a href="{{ route('admin.roles.index') }}">Roles</a>
        @endcan
    </nav>
    <div class="user">
        <span>{{ auth()->user()->name }}</span>
        <a href="{{ route('account.password.edit') }}">Password</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="link">Sign out</button>
        </form>
    </div>
</header>
@endauth

<main>
    @if (session('status'))
        <div class="flash">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="errors">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    @yield('content')
</main>
</body>
</html>
