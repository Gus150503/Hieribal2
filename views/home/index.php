<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$logueado = isset($_SESSION['cliente']) && !empty($_SESSION['cliente']['nombres']);
$nombreUsuario = $logueado ? htmlspecialchars($_SESSION['cliente']['nombres']) : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hieribal - Droguería Naturista</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

    <section id="top" class="bienvenida">
        <div class="texto-bienvenida">
            <h1>
                <?php if ($logueado): ?>
                    ¡Hola <span><?= $nombreUsuario ?></span> 👋!
                <?php else: ?>
                    ¡Hola <span>somos Hieribal</span>!
                <?php endif; ?>
            </h1>
            <p>Cuidarte naturalmente es la mejor forma de quererte. Hierbal lo hace posible.</p>
            <div class="botones-hero">
                <?php if ($logueado): ?>
                    <a href="?r=perfil" class="btn-ver-todo">Ir a mi Perfil</a>
                    <a href="?r=logout" class="btn-ver-todo" style="background:#444;">Cerrar sesión</a>
                <?php else: ?>
                    <a href="?r=login" class="btn-ver-todo">Iniciar sesión (Cliente)</a>
                    <a href="?r=admin_login" class="btn-ver-todo">Modo Administrador</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="imagenes-bienvenida">
            <div class="img-card grande"><img src="assets/img/IA 1.jpg" alt="Salud Natural"></div>
            <div class="img-card"><img src="assets/img/IA 2.jpg" alt="Cuidado Personal"></div>
            <div class="img-card"><img src="assets/img/IA 3.jpg" alt="Bienestar"></div>
        </div>
    </section>

    <section class="stats-section">
        <div class="stats-container">
            <div class="stat-box"><div class="stat-icon">🌿</div><h3 class="stat-number" data-target="500">0</h3><p class="stat-label">Productos Naturales</p></div>
            <div class="stat-box"><div class="stat-icon">😊</div><h3 class="stat-number" data-target="1000">0</h3><p class="stat-label">Clientes Satisfechos</p></div>
            <div class="stat-box"><div class="stat-icon">⭐</div><h3 class="stat-number" data-target="15">0</h3><p class="stat-label">Años de Experiencia</p></div>
            <div class="stat-box"><div class="stat-icon">✅</div><h3 class="stat-number" data-target="100">0</h3><p class="stat-label">% Calidad Garantizada</p></div>
        </div>
    </section>

    <section class="beneficios-section">
        <div class="beneficios-header">
            <span class="section-badge">¿Por qué elegirnos?</span>
            <h2>Ventajas de comprar con nosotros</h2>
        </div>
        <div class="beneficios-grid">
            <div class="beneficio-card"><div class="beneficio-icon">🍃</div><h3>100% Natural</h3><p>Origen natural, sin químicos artificiales.</p></div>
            <div class="beneficio-card"><div class="beneficio-icon">🔬</div><h3>Certificados</h3><p>Avalados por autoridades sanitarias.</p></div>
            <div class="beneficio-card"><div class="beneficio-icon">🚚</div><h3>Envío Seguro</h3><p>Entrega rápida a toda Colombia.</p></div>
            <div class="beneficio-card"><div class="beneficio-icon">💚</div><h3>Asesoría</h3><p>Expertos listos para guiarte.</p></div>
        </div>
    </section>

    <main id="quienes-somos" class="main-content">
        <div class="image-section"><img src="assets/img/atencion.png" alt="Asesoría"></div>
        <section class="text-section">
            <h1>¿Quiénes somos?</h1>
            <p>Somos MI HIERIBAL, un oasis de bienestar natural. Creemos que la salud es un
            viaje, no un destino. Y en cada paso de ese camino, queremos acompañarte con
            productos naturales de la más alta calidad. En nuestra tienda, encontrarás más que
            simples productos; encontrarás un compromiso con tu bienestar integral.</p>
            <div class="text-section-buttons">
                <a href="https://api.whatsapp.com/send?phone=573212322978&text=Hola,%20necesito%20información" target="_blank" class="btn btn-contactanos">Contáctanos</a>
                <button class="btn btn-mas-nosotros" onclick="document.getElementById('mision-vision').scrollIntoView({ behavior: 'smooth' })">Más sobre nosotros</button>
            </div>
        </section>
    </main>

    <section class="categorias-section">
        <div class="categorias-header"><h2>Nuestras Categorías</h2></div>
        <div class="categorias-grid">
            <div class="categoria-card"><div class="categoria-imagen"><img src="assets/img/gym3.png"><div class="categoria-overlay"><h3>Suplementos</h3><a href="?r=login" class="btn-categoria">Ver</a></div></div></div>
            <div class="categoria-card"><div class="categoria-imagen"><img src="assets/img/img1.png"><div class="categoria-overlay"><h3>Plantas</h3><a href="?r=login" class="btn-categoria">Ver</a></div></div></div>
            <div class="categoria-card"><div class="categoria-imagen"><img src="assets/img/Crema de lavanda.png"><div class="categoria-overlay"><h3>Cosmética</h3><a href="?r=login" class="btn-categoria">Ver</a></div></div></div>
            <div class="categoria-card"><div class="categoria-imagen"><img src="assets/img/Miel.png"><div class="categoria-overlay"><h3>Mieles</h3><a href="?r=login" class="btn-categoria">Ver</a></div></div></div>
        </div>
    </section>

    <section class="resenas-section">
        <div class="redes-container">
            <span class="section-badge" style="background:#e8f5e9; color:#2e7d32;">TESTIMONIOS</span>
            <h2 style="margin-top:10px;">Lo que dicen nuestros clientes</h2>
        </div>
        <div class="resenas-grid">
            <div class="resena-card">
                <div class="stars">★★★★★</div>
                <p>"Los productos para la ansiedad me han cambiado la vida. La atención por WhatsApp es muy rápida y amable."</p>
                <img src="https://i.pravatar.cc/150?u=1" alt="Cliente" class="resena-img">
                <h4>Ana María Rojas</h4>
            </div>
            <div class="resena-card">
                <div class="stars">★★★★★</div>
                <p>"Compré colágeno y miel de abejas pura. Se nota la calidad natural desde el primer uso. ¡Recomendados!"</p>
                <img src="https://i.pravatar.cc/150?u=2" alt="Cliente" class="resena-img">
                <h4>Carlos Alberto</h4>
            </div>
            <div class="resena-card">
                <div class="stars">★★★★★</div>
                <p>"Excelente ubicación en Bosa. Fui por una asesoría y me explicaron todo detalladamente sobre las plantas."</p>
                <img src="https://i.pravatar.cc/150?u=3" alt="Cliente" class="resena-img">
                <h4>Marta Lucía</h4>
            </div>
        </div>
    </section>

    <section class="blog-section">
        <div class="redes-container" style="text-align: center;">
            <span class="section-badge">BIENESTAR</span>
            <h2 style="margin-top:10px;">Aprende a cuidar tu salud</h2>
        </div>
        <div class="blog-grid">
            <article class="blog-card">
                <img src="assets/img/IA 2.jpg" alt="Blog" class="blog-img">
                <div class="blog-content">
                    <h3>Beneficios de la Miel Pura</h3>
                    <p>Descubre por qué la miel orgánica es el mejor aliado para tus defensas este invierno...</p>
                    <a href="#" class="btn-leer-mas">Leer más ➔</a>
                </div>
            </article>
            <article class="blog-card">
                <img src="assets/img/img1.png" alt="Blog" class="blog-img">
                <div class="blog-content">
                    <h3>Plantas que curan</h3>
                    <p>Guía básica sobre el uso de la caléndula y el árnica en el cuidado diario de la piel.</p>
                    <a href="#" class="btn-leer-mas">Leer más ➔</a>
                </div>
            </article>
            <article class="blog-card">
                <img src="assets/img/gym3.png" alt="Blog" class="blog-img">
                <div class="blog-content">
                    <h3>Suplementación Deportiva</h3>
                    <p>¿Cómo elegir el suplemento natural ideal según tu tipo de entrenamiento físico?</p>
                    <a href="#" class="btn-leer-mas">Leer más ➔</a>
                </div>
            </article>
        </div>
    </section>
<section class="redes-section">
    <div class="redes-container">
        <span class="section-badge">¡CONÉCTATE!</span>
        <h2 style="margin-top:10px;">Canales de Atención</h2>
        
        <div class="redes-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); max-width: 800px; margin: 40px auto 0;">
            
            <a href="https://www.google.com/maps/search/?api=1&query=Cra.+87c+%2362-15,+Bogotá" target="_blank" class="red-card" style="border-bottom: 5px solid #ff9800;">
                <span class="red-icon">📍</span>
                <h3>Visítanos en Bosa</h3>
                <p>Cra. 87c #62-15, Bogotá</p>
            </a>

            <a href="https://mail.google.com/mail/?view=cm&fs=1&to=mihieribal@gmail.com" target="_blank" class="red-card" style="border-bottom: 5px solid #5aa837;">
            <span class="red-icon">✉️</span>
            <h3>Correo Gmail</h3>
            <p>Escríbenos vía Google Mail</p>
        </a>

        </div>
    </div>
</section>

    <?php 
    $forceProfile = !empty($_SESSION['force_profile']);
    $faltaCedula = !empty($_SESSION['cliente']['falta_cedula']);
    $faltaApe = !empty($_SESSION['cliente']['falta_apellidos']);
    $faltaTel = !empty($_SESSION['cliente']['falta_telefono']);
    $mostrarModal = $logueado && $forceProfile && ($faltaCedula || $faltaApe || $faltaTel);
    ?>

    <?php if ($mostrarModal): ?>
        <div id="perfilModalBackdrop" class="modal-backdrop">
            <div id="perfilModal" class="modal-content">
                <h2>Completa tu perfil</h2>
                <p>Necesitamos unos datos finales para tu registro.</p>
                <form id="perfilForm" autocomplete="off">
                    <?php if ($faltaCedula): ?>
                        <label style="display:block; font-size:13px; margin-bottom:6px;">Cédula</label>
                        <input type="text" name="cedula" required class="modal-input">
                    <?php endif; ?>
                    <?php if ($faltaApe): ?>
                        <label style="display:block; font-size:13px; margin-bottom:6px;">Apellidos</label>
                        <input type="text" name="apellidos" required class="modal-input">
                    <?php endif; ?>
                    <?php if ($faltaTel): ?>
                        <label style="display:block; font-size:13px; margin-bottom:6px;">Teléfono</label>
                        <input type="text" name="telefono" required class="modal-input">
                    <?php endif; ?>
                    <div style="display:flex; justify-content:flex-end;">
                        <button type="submit" id="perfilBtn" class="btn-guardar">Guardar y continuar</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <script type="module" src="assets/js/homepage.js"></script>

</body>
</html>