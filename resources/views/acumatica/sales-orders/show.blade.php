<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Acumatica Sales Order</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
        }

        pre {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>

<body>

<a href="{{ route('acumatica.sales-orders.create') }}">
    ← Search another Sales Order
</a>

<h1>Acumatica Sales Order</h1>

<pre>{{ json_encode($salesOrder, JSON_PRETTY_PRINT) }}</pre>

</body>
</html>