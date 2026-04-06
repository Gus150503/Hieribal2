/**
 * Gestión de pedidos vía AJAX
 */
function marcarDespacho(id) {
    if (!confirm('¿Confirmar que el pedido ha sido despachado?')) return;

    // Llamada al endpoint del controlador
    fetch(`?r=admin_pedidos_despachar_ajax&id=${id}`)
        .then(response => {
            if (!response.ok) throw new Error('Error en la comunicación con el servidor');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Actualizar celda de estado
                const estadoCell = document.getElementById(`estado-${id}`);
                if (estadoCell) {
                    estadoCell.innerHTML = '<span class="badge rounded-pill bg-success">Enviado</span>';
                }
                // Reemplazar botón por texto de confirmación
                const accionCell = document.getElementById(`btn-contenedor-${id}`);
                if (accionCell) {
                    accionCell.innerHTML = '<span class="text-muted small"><i class="bi bi-check2-all"></i> Listo</span>';
                }
            } else {
                alert('No se pudo actualizar el estado en la base de datos.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Hubo un error al procesar la solicitud.');
        });
}