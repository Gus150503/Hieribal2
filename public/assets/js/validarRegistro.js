document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("form");
  if (!form) return;

  const cedula    = form.querySelector("[name='cedula']");
  const nombres   = form.querySelector("[name='nombres']");
  const apellidos = form.querySelector("[name='apellidos']");
  const telefono  = form.querySelector("[name='telefono']");
  const correo    = form.querySelector("[name='correo']");
  const password  = form.querySelector("[name='password']");

  // RegEx
  const regexNombre   = /^[a-zA-ZÀ-ÿ\s]+$/;
  const regexNumero   = /^[0-9]+$/;
  const regexCorreo   = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  const regexTelefono = /^\d{10}$/;

  // --- utilidades ---
  const createOrGetErrorBox = () => {
    let box = form.querySelector(".error-msg");
    if (!box) {
      box = document.createElement("div");
      box.className = "error-msg";
      form.prepend(box);
    }
    return box;
  };
  const showErrors = (msgs) => {
    const box = createOrGetErrorBox();
    box.innerHTML = msgs.map(m => `<div>• ${m}</div>`).join("");
    box.style.display = "block";
  };
  const clearErrors = () => {
    const box = form.querySelector(".error-msg");
    if (box) { box.innerHTML = ""; box.style.display = "none"; }
  };

  // --- restricciones en vivo ---
  if (cedula) {
    cedula.setAttribute("inputmode", "numeric");
    cedula.addEventListener("input", () => {
      cedula.value = cedula.value.replace(/\D/g, "").slice(0, 10);
    });
  }
  if (telefono) {
    telefono.setAttribute("inputmode", "numeric");
    telefono.setAttribute("maxlength", "10");
    telefono.addEventListener("input", () => {
      telefono.value = telefono.value.replace(/\D/g, "").slice(0, 10);
    });
  }

  // --- helper de servidor (existencia de campos)
  async function checkField(type, value) {
    try {
      const url = `?r=check_field&type=${encodeURIComponent(type)}&value=${encodeURIComponent(value)}`;
      const res = await fetch(url, { headers: { "Accept": "application/json" } });
      if (!res.ok) return { exists: false };
      return await res.json();
    } catch {
      return { exists: false };
    }
  }

  // blur checks
  cedula?.addEventListener("blur", async () => {
    if (cedula.value.length === 10) {
      const { exists } = await checkField("cedula", cedula.value);
      if (exists) showErrors(["⚠️ La cédula ya está registrada."]);
    }
  });

  correo?.addEventListener("blur", async () => {
    if (regexCorreo.test(correo.value.trim())) {
      const { exists } = await checkField("correo", correo.value.trim());
      if (exists) showErrors(["⚠️ El correo ya está registrado."]);
    }
  });

  // --- SUBMIT ---
  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    clearErrors();

    const mensajes = [];

    // Validaciones
    const c = (cedula?.value || "").trim();
    if (!regexNumero.test(c)) mensajes.push("La cédula debe contener solo números.");
    if (c.length !== 10) mensajes.push("La cédula debe tener exactamente 10 dígitos.");

    const n = (nombres?.value || "").trim();
    if (!regexNombre.test(n)) mensajes.push("El nombre solo debe contener letras y espacios.");

    const a = (apellidos?.value || "").trim();
    if (a !== "" && !regexNombre.test(a)) mensajes.push("El apellido solo debe contener letras y espacios.");

    const t = (telefono?.value || "").trim();
    if (!regexTelefono.test(t)) mensajes.push("El teléfono debe tener exactamente 10 dígitos.");

    const mail = (correo?.value || "").trim();
    if (!regexCorreo.test(mail)) mensajes.push("El correo no es válido.");

    if (!password || password.value.length < 8) mensajes.push("La contraseña debe tener mínimo 8 caracteres.");

    // Si no hay errores de formato, verificamos existencia
    if (mensajes.length === 0) {
      const [cedulaRes, correoRes] = await Promise.all([
        checkField("cedula", c),
        checkField("correo", mail)
      ]);
      if (cedulaRes.exists) mensajes.push("⚠️ La cédula ya está registrada.");
      if (correoRes.exists) mensajes.push("⚠️ El correo ya está registrado.");
    }

    // Mostrar errores
    if (mensajes.length > 0) {
      showErrors(mensajes);
      if (!regexNumero.test(c) || c.length !== 10) return cedula?.focus();
      if (!regexNombre.test(n)) return nombres?.focus();
      if (a !== "" && !regexNombre.test(a)) return apellidos?.focus();
      if (!regexTelefono.test(t)) return telefono?.focus();
      if (!regexCorreo.test(mail)) return correo?.focus();
      if (!password || password.value.length < 8) return password?.focus();
      return;
    }

    // --- Envío AJAX real ---
    const btn = form.querySelector(".submit-btn");
    btn?.setAttribute("disabled", "");
    if (btn) btn.textContent = "Procesando...";

    try {
      const fd = new FormData(form);
      const res = await fetch(form.getAttribute("action") || "?r=do_register", {
        method: "POST",
        body: fd,
        headers: {
          "Accept": "application/json",
          "X-Requested-With": "XMLHttpRequest"
        }
      });

      let data = null;
      const contentType = res.headers.get("content-type") || "";

      if (contentType.includes("application/json")) {
        data = await res.json();
      } else {
        // Si el backend devolvió HTML o redirect, lo tomamos como éxito
        data = { ok: true };
      }

      if (res.ok && data.ok) {
        if (window.Swal) {
          await Swal.fire({
            icon: "success",
            title: "¡Registro exitoso!",
            html: `<p style="font-size:15px;margin-top:10px;">Revisa tu correo Gmail para verificar tu cuenta.</p>`,
            confirmButtonText: "Ir a iniciar sesión",
            confirmButtonColor: "#4E73DF",
            allowOutsideClick: false,
            backdrop: true
          });
        }
        window.location.href = "?r=login";
        return;
      }

      // Error del backend
      showErrors([data.msg || "No se pudo completar el registro."]);
      btn?.removeAttribute("disabled");
      if (btn) btn.textContent = "Crear cuenta";

    } catch (err) {
      showErrors(["Error de conexión. Intenta de nuevo."]);
      btn?.removeAttribute("disabled");
      if (btn) btn.textContent = "Crear cuenta";
    }
  });
});
