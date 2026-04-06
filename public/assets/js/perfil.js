// Mantener tu lógica de la gráfica intacta
const ctx = document.getElementById('chartPedidos');

if(ctx){
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Sep','Oct','Nov','Dic','Ene','Feb','Mar'],
            datasets: [
                {
                    label: 'Pedidos',
                    data: [18, 22, 15, 31, 20, 28, 14],
                    backgroundColor: 'rgba(45,158,95,0.2)',
                    borderColor: '#2d9e5f',
                    borderWidth: 2
                },
                {
                    label: 'Total',
                    data: [320, 410, 280, 580, 370, 520, 270],
                    type: 'line',
                    borderColor: '#c8960c',
                    backgroundColor: 'rgba(200, 150, 12, 0.1)',
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            }
        }
    });
}

/* ==========================================
   NUEVAS FUNCIONALIDADES (FOTO Y TEMA)
   ========================================== */

// 1. Previsualización de Foto de Perfil
function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function() {
        const output = document.getElementById('preview');
        // Si el elemento es un IMG cambia el src, si es un DIV cambia el contenido
        if (output.tagName === 'IMG') {
            output.src = reader.result;
        } else {
            output.innerHTML = `<img src="${reader.result}" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">`;
        }
    };
    if(event.target.files[0]) {
        reader.readAsDataURL(event.target.files[0]);
    }
}

// 2. Lógica de Modo Oscuro
function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    const isDark = document.body.classList.contains('dark-mode');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
}

// 3. Aplicar tema guardado al cargar la página
document.addEventListener('DOMContentLoaded', () => {
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-mode');
    }
});