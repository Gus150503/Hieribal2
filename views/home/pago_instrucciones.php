<?php
$metodo = strtolower(trim($metodo ?? ''));
$total  = $total ?? 0;

function formato_pesos(float $monto): string
{
    return '$' . number_format($monto, 0, ',', '.');
}
?>

<div class="pago-wrapper">
    <div class="pago-card">
        <h1>✅ Pedido registrado</h1>

        <p>Gracias por tu compra. A continuación encontrarás las instrucciones para completar el pago.</p>

        <div class="pago-total">
            <span>Total a pagar:</span>
            <strong><?= formato_pesos($total) ?></strong>
        </div>

        <?php if ($metodo === 'nequi'): ?>
            <h2>Pago por Nequi</h2>
            <p>Envía el valor total a nuestro número Nequi:</p>
            <div class="pago-dato-destacado">
                <strong>310299147</strong>
            </div>
            <p>En la referencia escribe tu nombre completo o cédula.</p>
            <p>Si deseas, puedes enviar el comprobante al correo:</p>
            <div class="pago-dato-destacado">
                <strong>mihieribal@gmail.com</strong>
            </div>

        <?php elseif ($metodo === 'transferencia bancaria' || $metodo === 'Transferencia bancaria'): ?>
            <h2>Transferencia bancaria</h2>
            <p>Realiza una transferencia por el valor total a la siguiente cuenta:</p>
            <ul class="pago-lista">
                <li><strong>Banco:</strong> Bancolombia</li>
                <li><strong>Tipo de cuenta:</strong> Ahorros</li>
                <li><strong>Número:</strong> 1234 5678 9012</li> <!-- cambia por el real -->
                <li><strong>Titular:</strong> Mi Hieribal SAS</li>
            </ul>
            <p>En el concepto indica tu nombre o número de documento.</p>
            <p>Puedes enviar el comprobante al correo <strong>mihieribal@gmail.com</strong>.</p>

        <?php elseif ($metodo === 'contra entrega' || $metodo === 'Contra entrega'): ?>
            <h2>Pago contra entrega</h2>
            <p>Has elegido pagar en efectivo al recibir tu pedido.</p>
            <p>Por favor asegúrate de tener el valor exacto en efectivo:</p>
            <div class="pago-dato-destacado">
                <strong><?= formato_pesos($total) ?></strong>
            </div>
            <p>Uno de nuestros asesores se comunicará contigo para coordinar la entrega.</p>

        <?php else: ?>
            <h2>Método de pago registrado</h2>
            <p>Tu pedido fue registrado con método de pago: <strong><?= htmlspecialchars($metodo) ?></strong>.</p>
            <p>En breve nos comunicaremos contigo para indicarte cómo completar el pago.</p>
        <?php endif; ?>

        <a href="?r=dashboard" class="pago-btn-volver">Volver a la tienda</a>
    </div>
</div>