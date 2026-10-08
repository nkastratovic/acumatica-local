@extends('layouts.app')

@section('title', 'Acumatica Sales Order')

@section('content')
<a href="{{ route('acumatica.sales-orders.create') }}">← Search another Sales Order</a>

<h1>Acumatica Sales Order</h1>

<pre>{{ json_encode($salesOrder, JSON_PRETTY_PRINT) }}</pre>
@endsection
