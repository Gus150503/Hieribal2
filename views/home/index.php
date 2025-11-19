<?php
// home/index.php (o la ruta que uses)
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$base = rtrim($this->config['app']['base_url'] ?? '', '/');
$logeado = !empty($_SESSION['cliente']);
?>
<!-- HERO / BIENVENIDA -->
<section id="top" class="bienvenida">
    <div class="texto-bienvenida">
        <h1>¡Hola <span>somos Hieribal</span>!</h1>
        <p>Cuidarte naturalmente es la mejor forma de quererte. Hierbal lo hace posible.</p>

        <?php if (!$logeado): ?>
        <!-- ✅ Usuario NO logueado -->
        <a href="<?= $base ?>/?r=login" class="btn-ver-todo">Iniciar sesión (Cliente)</a>
        <a href="<?= $base ?>/?r=admin_login" class="btn-ver-todo">Modo Administrador</a>
        <?php else: ?>
        <!-- ✅ Usuario logueado -->
        <a href="<?= $base ?>/?r=dashboard" class="btn-ver-todo">Ir a mi panel</a>
        <a href="<?= $base ?>/?r=logout" class="btn-ver-todo" style="background:#444;">Cerrar sesión</a>
        <?php endif; ?>
    </div>

    <div class="imagenes-bienvenida">
        <div class="img-card grande">
            <img src="<?= $base ?>/assets/img/IA 1.jpg" alt="Persona 1">
        </div>
        <div class="img-card">
            <img src="<?= $base ?>/assets/img/IA 2.jpg" alt="Persona 2">
        </div>
        <div class="img-card">
            <img src="<?= $base ?>/assets/img/IA 3.jpg" alt="Persona 3">
        </div>
    </div>
</section>

<!-- QUIÉNES SOMOS -->
<main id="quienes-somos" class="main-content">
    <div class="image-section">
        <ul class='slider'>
    <li class='item' style="background-image: url('https://cdn.mos.cms.futurecdn.net/dP3N4qnEZ4tCTCLq59iysd.jpg')">
    <div class='content'>
        <h2 class='title'>"Lossless Youths"</h2>
        <p class='description'> Lorem ipsum, dolor sit amet consectetur
        adipisicing elit. Tempore fuga voluptatum, iure corporis inventore
        praesentium nisi. Id laboriosam ipsam enim.  </p>
        <button>Read More</button>
    </div>
    </li>
    <li class='item' style="background-image: url('https://i.redd.it/tc0aqpv92pn21.jpg')">
    <div class='content'>
        <h2 class='title'>"Estrange Bond"</h2>
        <p class='description'> Lorem ipsum, dolor sit amet consectetur
        adipisicing elit. Tempore fuga voluptatum, iure corporis inventore
        praesentium nisi. Id laboriosam ipsam enim.  </p>
        <button>Read More</button>
    </div>
    </li>
    <li class='item' style="background-image: url('https://wharferj.files.wordpress.com/2015/11/bio_north.jpg')">
    <div class='content'>
        <h2 class='title'>"The Gate Keeper"</h2>
        <p class='description'> Lorem ipsum, dolor sit amet consectetur
        adipisicing elit. Tempore fuga voluptatum, iure corporis inventore
        praesentium nisi. Id laboriosam ipsam enim.  </p>
        <button>Read More</button>
    </div>
    </li>
    <li class='item' style="background-image: url('https://images7.alphacoders.com/878/878663.jpg')">
    <div class='content'>
        <h2 class='title'>"Last Trace Of Us"</h2>
        <p class='description'>
        Lorem ipsum, dolor sit amet consectetur adipisicing elit. Tempore fuga voluptatum, iure corporis inventore praesentium nisi. Id laboriosam ipsam enim.
        </p>
        <button>Read More</button>
    </div>
    </li>
    <li class='item' style="background-image: url('https://theawesomer.com/photos/2017/07/simon_stalenhag_the_electric_state_6.jpg')">
    <div class='content'>
        <h2 class='title'>"Urban Decay"</h2>
        <p class='description'>
        Lorem ipsum, dolor sit amet consectetur adipisicing elit. Tempore fuga voluptatum, iure corporis inventore praesentium nisi. Id laboriosam ipsam enim.
        </p>
        <button>Read More</button>
    </div>
    </li>
    <li class='item' style="background-image: url('https://da.se/app/uploads/2015/09/simon-december1994.jpg')">
    <div class='content'>
    <h2 class='title'>"The Migration"</h2>
        <p class='description'> Lorem ipsum, dolor sit amet consectetur
        adipisicing elit. Tempore fuga voluptatum, iure corporis inventore
        praesentium nisi. Id laboriosam ipsam enim.  </p>
        <button>Read More</button>
    </div>
    </li>
</ul>
<nav class='nav'>
    <ion-icon class='btn prev' name="arrow-back-outline"></ion-icon>
    <ion-icon class='btn next' name="arrow-forward-outline"></ion-icon>
</nav>
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

<section id="nosotros" class="nosotros-section">
    <div class="nosotros-about-section">
        <div>
            <div class="nosotros-text-block">
                <h2>Misión</h2>
                <p>Nuestra misión es mejorar la calidad de vida de nuestros clientes...</p>
            </div>
            <div class="nosotros-text-block">
                <h2>Visión</h2>
                <p>Ser la droguería naturista líder en la zona...</p>
            </div>
        </div>
        <div>
            <div class="commitment-card">
                <h3>Compromiso</h3>
                <p>Nuestro lema es ser comprometidos de una manera eficiente con nuestros clientes.</p>
            </div>
        </div>
    </div>
</section>

<section class="call-to-action-section">
    <h2>Únete a Nuestra Comunidad Saludable</h2>
    <p>Recibe las últimas noticias, ofertas exclusivas y consejos de bienestar directamente en tu bandeja de entrada.
    </p>
    <div class="form-container">
        <input type="text" placeholder="Tu Nombre" />
        <input type="email" placeholder="Tu Correo Electrónico" />
        <button type="submit">Suscribirse</button>
    </div>
</section>

<script>

</script>