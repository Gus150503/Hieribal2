/**
 * perfil.js - Mi Hieribal
 * Maneja gráficas (Chart.js), Modo Oscuro y Foto de Perfil.
 */

document.addEventListener('DOMContentLoaded', () => {
    
    // --- 1. CAPTURA DE DATOS DESDE PHP ---
    const dataStatsRaw = document.getElementById('js_statsGrafica')?.value;
    const dataTopRaw = document.getElementById('js_productosTop')?.value;

    const statsGrafica = dataStatsRaw ? JSON.parse(dataStatsRaw) : [];
    const productosTop = dataTopRaw ? JSON.parse(dataTopRaw) : [];

    // --- 2. CONFIGURACIÓN DE EVENTOS ---
    
    // Botón para cambiar el tema
    const btnTheme = document.getElementById('btnThemeToggle');
    if (btnTheme) {
        btnTheme.addEventListener('click', toggleDarkMode);
    }

    // Lógica para la foto de perfil
    const inputFoto = document.getElementById('inputFoto');
    const avaPreview = document.getElementById('avaPreview');
    
    if (avaPreview && inputFoto) {
        avaPreview.addEventListener('click', () => inputFoto.click());
        inputFoto.addEventListener('change', cambiarFotoPerfil);
    }

    // --- 3. GRÁFICA DE COMPRAS POR MES ---
    const ctxPedidos = document.getElementById('chartPedidos');
    if (ctxPedidos) {
        new Chart(ctxPedidos, {
            type: 'bar',
            data: {
                labels: statsGrafica.length ? statsGrafica.map(d => d.mes) : ['Ene', 'Feb', 'Mar'],
                datasets: [{
                    label: 'Pedidos',
                    data: statsGrafica.length ? statsGrafica.map(d => d.total_pedidos) : [0, 0, 0],
                    backgroundColor: 'rgba(29, 158, 117, 0.6)',
                    borderColor: '#1D9E75',
                    borderWidth: 1
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    // --- 4. GRÁFICA DE PRODUCTOS TOP ---
    const ctxTop = document.getElementById('chartTop');
    if (ctxTop) {
        new Chart(ctxTop, {
            type: 'doughnut',
            data: {
                labels: productosTop.length ? productosTop.map(p => p.nombre_producto) : ['Sin datos'],
                datasets: [{
                    data: productosTop.length ? productosTop.map(p => p.total) : [1],
                    backgroundColor: ['#1D9E75', '#9FE1CB', '#BA7517', '#E24B4A', '#007b5e']
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false, 
                plugins: { legend: { position: 'bottom' } } 
            }
        });
    }
});

/**
 * Alterna el Modo Oscuro y guarda la preferencia en Cookie y LocalStorage
 */
function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    const esOscuro = document.body.classList.contains('dark-mode');
    
    localStorage.setItem('theme', esOscuro ? 'dark' : 'light');
    document.cookie = "theme=" + (esOscuro ? "dark" : "light") + "; max-age=31536000; path=/";
}

/**
 * Previsualiza la foto de perfil seleccionada
 */
function cambiarFotoPerfil(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('avaPreview');

    if (file && preview) {
        const reader = new FileReader();
        reader.onload = (e) => {
            preview.innerHTML = `<img src="${e.target.result}" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">`;
        };
        reader.readAsDataURL(file);
    }
}