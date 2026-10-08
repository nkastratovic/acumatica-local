@extends('layouts.app')

@section('title', 'Get Acumatica Sales Order')

@section('content')
<h1>Get Acumatica Sales Order</h1>

<form id="sales-order-form" class="card">
    <div class="field">
        <label for="orderType">Order Type</label>
        <input type="text" id="orderType" value="TR" required>
    </div>

    <div class="field">
        <label for="orderNbr">Order Number</label>
        <input type="text" id="orderNbr" value="SO005483" required>
    </div>

    <button type="submit">Get Sales Order</button>
</form>

<script>
    document
        .getElementById('sales-order-form')
        .addEventListener('submit', function (event) {
            event.preventDefault();

            const orderType = document.getElementById('orderType').value;
            const orderNbr = document.getElementById('orderNbr').value;

            window.location.href =
                `{{ url('/acumatica/sales-orders') }}/${encodeURIComponent(orderType)}/${encodeURIComponent(orderNbr)}`;
        });
</script>
@endsection
