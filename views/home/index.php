<?php
// No rompe nada si la sesión ya está iniciada en tu layout
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

    <!-- HERO / BIENVENIDA -->
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

            <?php if ($logueado): ?>
                <a href="?r=dashboard" class="btn-ver-todo">Ir a mi panel</a>
                <a href="?r=logout" class="btn-ver-todo" style="background:#444;">Cerrar sesión</a>
            <?php else: ?>
                <a href="?r=login" class="btn-ver-todo">Iniciar sesión (Cliente)</a>
                <a href="?r=admin_login" class="btn-ver-todo">Modo Administrador</a>
            <?php endif; ?>

        </div>

        <div class="imagenes-bienvenida">
            <div class="img-card grande">
                <img src="assets/img/IA 1.jpg" alt="Persona 1">
            </div>
            <div class="img-card">
                <img src="assets/img/IA 2.jpg" alt="Persona 2">
            </div>
            <div class="img-card">
                <img src="assets/img/IA 3.jpg" alt="Persona 3">
            </div>
        </div>
    </section>

    <!-- ✨ SECCIÓN DE ESTADÍSTICAS -->
    <section class="stats-section">
        <div class="stats-container">
            <div class="stat-box">
                <div class="stat-icon">🌿</div>
                <h3 class="stat-number" data-target="500">0</h3>
                <p class="stat-label">Productos Naturales</p>
            </div>
            <div class="stat-box">
                <div class="stat-icon">😊</div>
                <h3 class="stat-number" data-target="1000">0</h3>
                <p class="stat-label">Clientes Satisfechos</p>
            </div>
            <div class="stat-box">
                <div class="stat-icon">⭐</div>
                <h3 class="stat-number" data-target="15">0</h3>
                <p class="stat-label">Años de Experiencia</p>
            </div>
            <div class="stat-box">
                <div class="stat-icon">✅</div>
                <h3 class="stat-number" data-target="100">0</h3>
                <p class="stat-label">% Calidad Garantizada</p>
            </div>
        </div>
    </section>

    <!-- ✨ BENEFICIOS / CARACTERÍSTICAS -->
    <section class="beneficios-section">
        <div class="beneficios-header">
            <span class="section-badge">¿Por qué elegirnos?</span>
            <h2>Ventajas de comprar con nosotros</h2>
        </div>
        <div class="beneficios-grid">
            <div class="beneficio-card">
                <div class="beneficio-icon">🍃</div>
                <h3>100% Natural</h3>
                <p>Todos nuestros productos son de origen natural, sin químicos ni conservantes artificiales.</p>
            </div>
            <div class="beneficio-card">
                <div class="beneficio-icon">🔬</div>
                <h3>Certificados de Calidad</h3>
                <p>Productos certificados y avalados por las autoridades sanitarias correspondientes.</p>
            </div>
            <div class="beneficio-card">
                <div class="beneficio-icon">🚚</div>
                <h3>Envío Seguro</h3>
                <p>Entregamos tus productos de forma segura y rápida a cualquier parte del país.</p>
            </div>
            <div class="beneficio-card">
                <div class="beneficio-icon">💚</div>
                <h3>Asesoría Personalizada</h3>
                <p>Contamos con expertos que te guiarán para encontrar el producto ideal para ti.</p>
            </div>
        </div>
    </section>

    <!-- QUIÉNES SOMOS -->
    <main id="quienes-somos" class="main-content">
        <div class="image-section">
            <img src="assets/img/atencion.png" alt="Equipo MI HIERBAL asesorando clientes">
        </div>

        <section class="text-section">
            <h1>¿Quiénes somos?</h1>
            <p>
                Somos MI HIERBAL, un oasis de bienestar natural. Creemos que la salud es un viaje, no un destino.
                Y en cada paso de ese camino, queremos acompañarte con productos naturales de la más alta calidad...
            </p>
            <div class="text-section-buttons">
                <a href="https://api.whatsapp.com/send?phone=573212322978&text=Hola,%20me%20gustaría%20más%20información%20sobre%20sus%20productos"
                    target="_blank" class="btn btn-contactanos">Contáctanos</a>
                <button class="btn btn-mas-nosotros"
                    onclick="document.getElementById('nosotros').scrollIntoView({ behavior: 'smooth' })">
                    Más sobre nosotros
                </button>
            </div>
        </section>
    </main>

    <!-- ✨ CATEGORÍAS DE PRODUCTOS -->
    <section class="categorias-section">
        <div class="categorias-header">
            <h2>Explora Nuestras Categorías</h2>
            <p>Encuentra el producto perfecto para tus necesidades</p>
        </div>
        <div class="categorias-grid">
            <div class="categoria-card">
                <div class="categoria-imagen">
                    <img src="assets/img/gym3.png" alt="Suplementos">
                    <div class="categoria-overlay">
                        <h3>Suplementos</h3>
                        <p>Proteinas y Creatinas</p>
                        <a href="http://localhost/Hieribal2/public/?r=login" class="btn-categoria">Ver productos</a>
                    </div>
                </div>
            </div>
            <div class="categoria-card">
                <div class="categoria-imagen">
                    <img src="assets/img/img1.png" alt="Plantas Medicinales">
                    <div class="categoria-overlay">
                        <h3>Plantas Medicinales</h3>
                        <p>Hierbas y extractos</p>
                        <a href="http://localhost/Hieribal2/public/?r=login" class="btn-categoria">Ver productos</a>
                    </div>
                </div>
            </div>
            <div class="categoria-card">
                <div class="categoria-imagen">
                    <img src="assets/img/Crema de lavanda.png" alt="Cosméticos Naturales">
                    <div class="categoria-overlay">
                        <h3>Cosmética Natural</h3>
                        <p>Cuidado personal</p>
                        <a href="http://localhost/Hieribal2/public/?r=login" class="btn-categoria">Ver productos</a>
                    </div>
                </div>
            </div>
            <div class="categoria-card">
                <div class="categoria-imagen">
                    <img src="assets/img/Miel.png" alt="Tés e Infusiones">
                    <div class="categoria-overlay">
                        <h3>Cosas Naturales</h3>
                        <p>Mieles</p>
                        <a href="http://localhost/Hieribal2/public/?r=login" class="btn-categoria">Ver productos</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- MISIÓN, VISIÓN Y COMPROMISO -->

    <section class="mision-vision-section">
        <div class="mision-vision-container">

            <div class="mv-card">
                <h2>Misión</h2>
                <p>
                    Nuestra misión es mejorar la calidad de vida de nuestros clientes,
                    promoviendo la salud natural y el equilibrio entre cuerpo y mente,
                    ofreciendo productos orgánicos de alta calidad y asesoramiento
                    para un estilo de vida saludable.
                </p>
            </div>

            <div class="mv-card">
                <h2>Visión</h2>
                <p>
                    Ser la droguería naturista líder en la región,
                    reconocida por nuestra amplia gama de productos naturales,
                    compromiso con la salud y soluciones personalizadas
                    para cada cliente.
                </p>
            </div>

        </div>
    </section>

    <!-- ✨ TESTIMONIOS -->
    <section class="testimonios-section">
        <div class="testimonios-header">
            <span class="section-badge">Testimonios</span>
            <h2>Lo que dicen nuestros clientes</h2>
        </div>
        <div class="testimonios-slider">
            <div class="testimonio-card">
                <div class="testimonio-rating">⭐⭐⭐⭐⭐</div>
                <p class="testimonio-texto">
                    "Excelente atención y productos de calidad. He notado una gran mejora en mi salud desde que consumo sus suplementos naturales."
                </p>
                <div class="testimonio-autor">
                    <div class="autor-avatar">M</div>
                    <div class="autor-info">
                        <h4>María González</h4>
                        <p>Cliente frecuente</p>
                    </div>
                </div>
            </div>
            <div class="testimonio-card">
                <div class="testimonio-rating">⭐⭐⭐⭐⭐</div>
                <p class="testimonio-texto">
                    "La mejor droguería naturista de la zona. Siempre encuentro lo que necesito y me asesoran muy bien sobre cada producto."
                </p>
                <div class="testimonio-autor">
                    <div class="autor-avatar">J</div>
                    <div class="autor-info">
                        <h4>Juan Pérez</h4>
                        <p>Cliente desde 2020</p>
                    </div>
                </div>
            </div>
            <div class="testimonio-card">
                <div class="testimonio-rating">⭐⭐⭐⭐⭐</div>
                <p class="testimonio-texto">
                    "Productos naturales de excelente calidad. El envío fue rápido y todo llegó en perfecto estado. Muy recomendado."
                </p>
                <div class="testimonio-autor">
                    <div class="autor-avatar">A</div>
                    <div class="autor-info">
                        <h4>Ana Martínez</h4>
                        <p>Compra online</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ✨ BLOG / CONSEJOS -->
    <section class="blog-section">
        <div class="blog-header">
            <h2>Consejos de Salud Natural</h2>
            <p>Aprende más sobre bienestar y vida saludable</p>
        </div>
        <div class="blog-grid">
            <article class="blog-card">
                <div class="blog-imagen">
                    <img src="assets/img/blog-1.jpg" alt="Artículo 1" onerror="this.src='https://images.unsplash.com/photo-1490645935967-10de6ba17061?w=400'">
                    <span class="blog-categoria">Nutrición</span>
                </div>
                <div class="blog-contenido">
                    <h3>Beneficios de los Superalimentos</h3>
                    <p>Descubre cómo los superalimentos pueden transformar tu salud y bienestar diario...</p>
                    <a href="#" class="blog-link">Leer más →</a>
                </div>
            </article>
            <article class="blog-card">
                <div class="blog-imagen">
                    <img src="assets/img/blog-2.jpg" alt="Artículo 2" onerror="this.src='https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400'">
                    <span class="blog-categoria">Bienestar</span>
                </div>
                <div class="blog-contenido">
                    <h3>Plantas para Mejorar el Sueño</h3>
                    <p>Conoce las mejores plantas medicinales que te ayudarán a descansar mejor cada noche...</p>
                    <a href="#" class="blog-link">Leer más →</a>
                </div>
            </article>
            <article class="blog-card">
                <div class="blog-imagen">
                    <img src="assets/img/blog-3.jpg" alt="Artículo 3" onerror="this.src='https://images.unsplash.com/photo-1505576399279-565b52d4ac71?w=400'">
                    <span class="blog-categoria">Salud</span>
                </div>
                <div class="blog-contenido">
                    <h3>Refuerza tu Sistema Inmune</h3>
                    <p>Tips naturales y efectivos para fortalecer tus defensas de manera natural...</p>
                    <a href="#" class="blog-link">Leer más →</a>
                </div>
            </article>
        </div>
    </section>

    <!-- ✨ PREGUNTAS FRECUENTES -->
    <section class="faq-section">
        <div class="faq-header">
            <h2>Preguntas Frecuentes</h2>
            <p>Resolvemos tus dudas más comunes</p>
        </div>
        <div class="faq-container">
            <div class="faq-item">
                <button class="faq-pregunta">
                    ¿Los productos son 100% naturales?
                    <span class="faq-icon">+</span>
                </button>
                <div class="faq-respuesta">
                    <p>Sí, todos nuestros productos son de origen natural y están certificados. No utilizamos químicos artificiales ni conservantes dañinos.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-pregunta">
                    ¿Realizan envíos a todo el país?
                    <span class="faq-icon">+</span>
                </button>
                <div class="faq-respuesta">
                    <p>Sí, realizamos envíos seguros a toda Colombia. El tiempo de entrega varía según la ciudad de destino.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-pregunta">
                    ¿Ofrecen asesoría para elegir productos?
                    <span class="faq-icon">+</span>
                </button>
                <div class="faq-respuesta">
                    <p>Por supuesto. Contamos con personal capacitado que te orientará para elegir el producto más adecuado según tus necesidades.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-pregunta">
                    ¿Cuáles son los métodos de pago?
                    <span class="faq-icon">+</span>
                </button>
                <div class="faq-respuesta">
                    <p>Aceptamos transferencias bancarias, pagos con tarjeta de crédito/débito y pago contra entrega en algunas zonas.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- NEWSLETTER / SUSCRIPCIÓN -->
    <section class="call-to-action-section">
        <h2>Únete a Nuestra Comunidad Saludable</h2>
        <p>Recibe las últimas noticias, ofertas exclusivas y consejos de bienestar directamente en tu bandeja de entrada.</p>
        <div class="form-container">
            <input type="text" placeholder="Tu Nombre" />
            <input type="email" placeholder="Tu Correo Electrónico" />
            <button type="submit">Suscribirse</button>
        </div>
    </section>



    <?php
    // ...tu código de arriba...

    $forceProfile = !empty($_SESSION['force_profile']); // true/false
    $faltaCedula  = !empty($_SESSION['cliente']['falta_cedula']);
    $faltaApe     = !empty($_SESSION['cliente']['falta_apellidos']);
    $faltaTel     = !empty($_SESSION['cliente']['falta_telefono']);

    $mostrarModal = $logueado && $forceProfile && ($faltaCedula || $faltaApe || $faltaTel);
    ?>

    <?php if ($mostrarModal): ?>
        <!-- =========================
     MODAL PERFIL OBLIGATORIO
========================= -->
        <div id="perfilModalBackdrop" style="
  position:fixed; inset:0; background:rgba(0,0,0,.55);
  display:flex; align-items:center; justify-content:center;
  z-index:9999;
">
            <div id="perfilModal" style="
    width:min(560px, 92vw);
    background:#fff; border-radius:18px;
    padding:22px 20px;
    box-shadow:0 20px 60px rgba(0,0,0,.25);
    font-family:Poppins, system-ui, Arial;
  ">
                <h2 style="margin:0 0 8px; font-size:20px;">Completa tu perfil</h2>
                <p style="margin:0 0 16px; color:#555; font-size:14px; line-height:1.4;">
                    Para continuar, necesitamos completar unos datos. (Solo te lo pedimos una vez)
                </p>

                <div id="perfilMsg" style="display:none; margin:0 0 12px; padding:10px 12px; border-radius:12px; font-size:14px;"></div>

                <form id="perfilForm" autocomplete="off">
                    <?php if ($faltaCedula): ?>
                        <label style="display:block; font-size:13px; color:#333; margin-bottom:6px;">Cédula (8 o 10 dígitos)</label>
                        <input type="text" name="cedula" inputmode="numeric" placeholder="Ej: 1000123456" style="
        width:100%; padding:12px 14px; border:1px solid #ddd; border-radius:12px;
        outline:none; margin-bottom:12px; font-size:14px;
      ">
                    <?php endif; ?>

                    <?php if ($faltaApe): ?>
                        <label style="display:block; font-size:13px; color:#333; margin-bottom:6px;">Apellidos</label>
                        <input type="text" name="apellidos" placeholder="Ej: Cuevas Pérez" style="
        width:100%; padding:12px 14px; border:1px solid #ddd; border-radius:12px;
        outline:none; margin-bottom:12px; font-size:14px;
      ">
                    <?php endif; ?>

                    <?php if ($faltaTel): ?>
                        <label style="display:block; font-size:13px; color:#333; margin-bottom:6px;">Teléfono</label>
                        <input type="text" name="telefono" inputmode="tel" placeholder="Ej: 3001234567" style="
        width:100%; padding:12px 14px; border:1px solid #ddd; border-radius:12px;
        outline:none; margin-bottom:12px; font-size:14px;
      ">
                    <?php endif; ?>

                    <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:8px;">
                        <button type="submit" id="perfilBtn" style="
          border:none; background:#5aa837; color:#fff;
          padding:12px 16px; border-radius:12px; font-weight:600; cursor:pointer;
        ">
                            Guardar y continuar
                        </button>
                    </div>
                </form>

                <p style="margin:14px 0 0; font-size:12px; color:#777;">
                    * Este formulario es obligatorio para finalizar tu registro con Google.
                </p>
            </div>
        </div>

        <script>
            (() => {
                const form = document.getElementById('perfilForm');
                const btn = document.getElementById('perfilBtn');
                const msg = document.getElementById('perfilMsg');

                function showMsg(text, ok = false) {
                    msg.style.display = 'block';
                    msg.textContent = text;
                    msg.style.background = ok ? '#e9fbef' : '#ffeaea';
                    msg.style.color = ok ? '#157a2b' : '#a40000';
                    msg.style.border = ok ? '1px solid #bde8c7' : '1px solid #ffc2c2';
                }

                form?.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    btn.disabled = true;
                    btn.textContent = 'Guardando...';

                    const fd = new FormData(form);

                    try {
                        const res = await fetch('?r=completar_perfil', {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: fd
                        });

                        const data = await res.json();

                        if (!res.ok || !data.ok) {
                            showMsg(data.msg || 'No se pudo guardar. Intenta de nuevo.');
                            btn.disabled = false;
                            btn.textContent = 'Guardar y continuar';
                            return;
                        }

                        showMsg('✅ Listo, perfil completado.', true);

                        // Recargar para que ya no aparezca el modal
                        setTimeout(() => window.location.reload(), 600);

                    } catch (err) {
                        showMsg('Error de red. Intenta de nuevo.');
                        btn.disabled = false;
                        btn.textContent = 'Guardar y continuar';
                    }
                });
            })();
        </script>
    <?php endif; ?>

</body>

</html>