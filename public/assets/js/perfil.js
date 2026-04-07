document.addEventListener('DOMContentLoaded', () => {
    // 1. Cargar Tema
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-mode');
    }

    // 2. Gráfica de Barras (Compras por mes)
    const ctxPedidos = document.getElementById('chartPedidos');
    if (ctxPedidos) {
        new Chart(ctxPedidos, {
            type: 'bar',
            data: {
                labels: window.statsGrafica.length ? window.statsGrafica.map(d => d.mes) : ['Ene', 'Feb', 'Mar'],
                datasets: [{
                    label: 'Pedidos',
                    data: window.statsGrafica.length ? window.statsGrafica.map(d => d.total_pedidos) : [5, 8, 12],
                    backgroundColor: 'rgba(29, 158, 117, 0.6)',
                    borderColor: '#1D9E75',
                    borderWidth: 1
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }

    // 3. Gráfica de Dona (Productos)
    const ctxTop = document.getElementById('chartTop');
    if (ctxTop) {
        new Chart(ctxTop, {
            type: 'doughnut',
            data: {
                labels: window.productosTop.length ? window.productosTop.map(p => p.nombre_producto) : ['Producto A', 'Producto B'],
                datasets: [{
                    data: window.productosTop.length ? window.productosTop.map(p => p.total) : [60, 40],
                    backgroundColor: ['#1D9E75', '#9FE1CB', '#BA7517', '#E24B4A']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }
});

function previewImage(event) {
    const reader = new FileReader();
    reader.onload = () => {
        const output = document.getElementById('preview');
        output.innerHTML = `<img src="${reader.result}" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">`;
    };
    reader.readAsDataURL(event.target.files[0]);
}

function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    localStorage.setItem('theme', document.body.classList.contains('dark-mode') ? 'dark' : 'light');
}