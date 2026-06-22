// ==========================================
// HOMEPAGE JS - MI HIERIBAL (ARQUITECTURA MVC)
// ==========================================

/**
 * Listener principal que dispara la inicializacion de los modulos
 * una vez que el DOM ha sido cargado completamente.
 */
document.addEventListener("DOMContentLoaded", () => {
    initContadorDinamico(); // Gestiona los numeros animados con reinicio por scroll
    initBlog();             // Gestiona la expansion de texto en la seccion de noticias
    initFaq();              // Gestiona el comportamiento del acordeon de preguntas
    initModal();            // Gestiona la validacion y envio del modal de perfil
});

// ==========================================
// CONTADOR ANIMADO (REINICIO POR INTERSECCION)
// ==========================================
/**
 * Se encarga de animar los numeros cada vez que la seccion entra en el area
 * visible del navegador (viewport).
 */
function initContadorDinamico() {
    const statsSection = document.querySelector('.stats-section');
    const counters = document.querySelectorAll(".stat-number");

    if (!statsSection || !counters.length) return;

    const ejecutarAnimacion = (el) => {
        const target = +el.dataset.target;
        const duration = 2000; // Duracion total de la subida (2 segundos)
        let startTime = null;

        const step = (timestamp) => {
            if (!startTime) startTime = timestamp;
            const elapsed = timestamp - startTime;
            
            // Calculo del progreso lineal
            const progress = Math.min(elapsed / duration, 1);
            
            // Calculo y asignacion del numero actual
            const currentCount = Math.floor(progress * target);
            el.innerText = currentCount;

            // La animacion continua hasta que el progreso llega a 1 (100%)
            if (progress < 1) {
                el.animationFrame = window.requestAnimationFrame(step);
            }
        };

        // Cancelacion de frames previos para evitar aceleraciones innecesarias
        if (el.animationFrame) window.cancelAnimationFrame(el.animationFrame);
        el.animationFrame = window.requestAnimationFrame(step);
    };

    /**
     * Configuracion del Observador para detectar entrada y salida de la seccion
     */
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Inicia la animacion al entrar en la seccion
                counters.forEach(counter => ejecutarAnimacion(counter));
            } else {
                // Resetea los valores al salir de la seccion para el proximo scroll
                counters.forEach(counter => {
                    if (counter.animationFrame) window.cancelAnimationFrame(counter.animationFrame);
                    counter.innerText = "0";
                });
            }
        });
    }, { threshold: 0.2 });

    observer.observe(statsSection);
}

// ==========================================
// FAQ - ACORDEON INTERACTIVO
// ==========================================
function initFaq() {
    const preguntas = document.querySelectorAll(".faq-pregunta");

    preguntas.forEach(btn => {
        btn.addEventListener("click", () => {
            const item = btn.parentElement;
            
            // Cierre de otros elementos abiertos para mantener orden visual
            document.querySelectorAll('.faq-item').forEach(el => {
                if(el !== item) el.classList.remove('active');
            });

            item.classList.toggle("active");
            
            const icon = btn.querySelector('.faq-icon');
            if(icon) {
                icon.textContent = item.classList.contains('active') ? '-' : '+';
            }
        });
    });
}

// ==========================================
// BOTON LEER MAS - BLOG
// ==========================================
function initBlog() {
    // Usamos el selector correcto que tienes en el HTML
    const links = document.querySelectorAll(".btn-leer-mas");

    links.forEach(link => {
        link.addEventListener("click", (e) => {
            e.preventDefault();
            
            // Buscamos el párrafo que está justo antes del botón
            const texto = link.parentElement.querySelector('p');

            if (!texto.dataset.original) {
                texto.dataset.original = texto.innerText;
            }

            if (texto.classList.contains("open")) {
                texto.innerText = texto.dataset.original;
                texto.classList.remove("open");
                link.innerText = "Leer más ➔";
            } else {
                // Texto de ejemplo que se añade al expandir
                texto.innerText += " Descubre cómo integrar estos hábitos en tu rutina diaria para mejorar tu vitalidad y bienestar general con los consejos de Mi Hieribal.";
                texto.classList.add("open");
                link.innerText = "Leer menos ↑";
            }
        });
    });
}

// ==========================================
// MODAL PERFIL - VALIDACION Y ENVIO AJAX
// ==========================================
function initModal() {
    const modalBackdrop = document.getElementById("perfilModalBackdrop");
    if (!modalBackdrop) return;

    const form = document.getElementById("perfilForm");
    const btn = document.getElementById("perfilBtn");
    const msg = document.getElementById("perfilMsg");

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        btn.disabled = true;
        btn.innerText = "Guardando...";

        try {
            const formData = new FormData(form);
            const res = await fetch("?r=completar_perfil", {
                method: "POST",
                body: formData
            });

            const data = await res.json();

            if (data.ok) {
                location.reload(); 
            } else {
                msg.style.display = "block";
                msg.style.background = "#fee2e2";
                msg.style.color = "#991b1b";
                msg.textContent = data.error || "Error al actualizar perfil";
                btn.disabled = false;
                btn.innerText = "Guardar y continuar";
            }

        } catch (error) {
            msg.style.display = "block";
            msg.textContent = "Error de conexión con el servidor";
            btn.disabled = false;
            btn.innerText = "Guardar y continuar";
        }
    });

    initSuscripcion(); // Inicializacion del modulo dependiente
}

// ==========================================
// SISTEMA DE SUSCRIPCION - NEWSLETTER
// ==========================================
function initSuscripcion() {
    const form = document.getElementById('formSuscripcion');
    const btn = document.getElementById('btnSuscribirse');
    const msg = document.getElementById('mensajeSuscripcion');

    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        btn.disabled = true;
        btn.innerText = "Enviando...";
        msg.style.display = "none";

        try {
            const formData = new FormData(form);
            const res = await fetch("?r=suscribir_cliente", { 
                method: "POST",
                body: formData
            });

            const data = await res.json();

            msg.style.display = "block";
            msg.textContent = data.mensaje;
            msg.style.color = data.ok ? "#5aa837" : "#e11d48"; 

            if (data.ok) {
                form.reset();
            }

        } catch (error) {
            msg.style.display = "block";
            msg.textContent = "Hubo un problema al conectar con el servidor.";
            msg.style.color = "#e11d48";
        } finally {
            btn.disabled = false;
            btn.innerText = "Suscribirse";
        }
    });
}