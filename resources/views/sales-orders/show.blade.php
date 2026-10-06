<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Order</title>
</head>

<body>

<a href="{{ route('acumatica.sales-orders.create') }}">
    ← Search another Sales Order
</a>

<h1>Acumatica Sales Order</h1>

<pre>{{ json_encode($salesOrder, JSON_PRETTY_PRINT) }}</pre>

</body>
</html>