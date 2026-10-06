<div>
    <!-- An unexamined life is not worth living. - Socrates -->
</div>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Get Sales Order</title>
</head>

<body>

<h1>Get Acumatica Sales Order</h1>

<form id="sales-order-form">
    <div>
        <label for="orderType">Order Type</label>
        <input
            type="text"
            id="orderType"
            value="TR"
            required
        >
    </div>

    <br>

    <div>
        <label for="orderNbr">Order Number</label>
        <input
            type="text"
            id="orderNbr"
            value="SO005483"
            required
        >
    </div>

    <br>

    <button type="submit">Get Sales Order</button>
</form>

<script>
    document
        .getElementById('sales-order-form')
        .addEventListener('submit', function (event) {
            event.preventDefault();

            const orderType =
                document.getElementById('orderType').value;

            const orderNbr =
                document.getElementById('orderNbr').value;

            window.location.href =
                `/acumatica/sales-orders/${encodeURIComponent(orderType)}/${encodeURIComponent(orderNbr)}`;
        });
</script>

</body>
</html>