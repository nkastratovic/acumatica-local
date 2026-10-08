@extends('layouts.app')

@section('title', 'Home')

@section('content')
<h1>Welcome, {{ auth()->user()->name }}</h1>
<p>Your account has no access to any area yet. Ask an administrator to assign you a role.</p>
@endsection
