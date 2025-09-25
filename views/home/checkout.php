<?php $base = $this->config['app']['base_url']; ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Pasarela de Pago</title>
<script src="https://www.paypal.com/sdk/js?client-id=TU_CLIENT_ID&currency=USD"></script>
<style>
    body { font-family: Arial, sans-serif; text-align: center; padding: 40px; }
    #paypal-button-container { margin-top: 30px; }
</style>
</head>
<body>

<h1>Finalizar Compra</h1>
<p>Total a pagar: <strong id="monto"></strong></p>

<div id="paypal-button-container"></div>

<script>
    const urlParams = new URLSearchParams(window.location.search);
    const total = urlParams.get('total') || 0;
    document.getElementById("monto").textContent = "$" + Number(total).toLocaleString();

    paypal.Buttons({
    createOrder: function(data, actions) {
        return actions.order.create({
          purchase_units: [{ amount: { value: (total / 4000).toFixed(2) } }] // conv. a USD aprox
        });
    },
    onApprove: function(data, actions) {
        return actions.order.capture().then(function(details) {
        alert('Pago realizado por: ' + details.payer.name.given_name);
        window.location.href = "<?= $base ?>/gracias.php";
        });
    }
    }).render('#paypal-button-container');
</script>

</body>
</html>
