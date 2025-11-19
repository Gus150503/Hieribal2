// Desvanecer y eliminar el mensaje después de unos segundos
window.addEventListener('DOMContentLoaded', () => {
    const alerta = document.getElementById('alertaFlash');
    if (alerta) {
        setTimeout(() => {
            alerta.style.opacity = '0';
            setTimeout(() => alerta.remove(), 300);
        }, 4000);
    }
});

