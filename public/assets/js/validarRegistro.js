document.addEventListener("DOMContentLoaded", function () {
  const form = document.querySelector("form");
  if (!form) return;

  const cedula    = form.querySelector("[name='cedula']");
  const nombres   = form.querySelector("[name='nombres']");
  const apellidos = form.querySelector("[name='apellidos']");
  const telefono  = form.querySelector("[name='telefono']");
  const correo    = form.querySelector("[name='correo']");
  const password  = form.querySelector("[name='password']");

  // ========= REGEX =========
  const regexNombre   = /^[a-zA-ZÀ-ÿ\s]+$/;
  const regexCorreo   = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  const regexTelefono = /^\d{10}$/;
const regexCedula   = /^(\d{8}|\d{10})$/;
 // solo 8 a 10 dígitos

  // ========= UTILIDADES =========
  function createOrGetErrorBox() {
    let box = document.querySelector(".error-msg");
    if (!box) {
      box = document.createElement("div");
      box.className = "error-msg";
      form.prepend(box);
    }
    return box;
  }

  function showErrors(msgs) {
    const box = createOrGetErrorBox();
    box.innerHTML = msgs.map(m => `<div>• ${m}</div>`).join("");
    box.style.display = "block";
  }

  function clearErrors() {
    document.querySelectorAll(".error-msg").forEach(box => {
      box.innerHTML = "";
      box.style.display = "none";
    });
  }

  // ========= RESTRICCIONES EN VIVO =========
  if (cedula) {
    cedula.setAttribute("inputmode", "numeric");
    cedula.addEventListener("input", function () {
      cedula.value = cedula.value.replace(/\D/g, "").slice(0, 10);
    });
  }

  if (telefono) {
    telefono.setAttribute("inputmode", "numeric");
    telefono.setAttribute("maxlength", "10");
    telefono.addEventListener("input", function () {
      telefono.value = telefono.value.replace(/\D/g, "").slice(0, 10);
    });
  }

  // ========= HELPER AJAX check_field =========
  async function checkField(type, value) {
    try {
      const url = `?r=check_field&type=${encodeURIComponent(type)}&value=${encodeURIComponent(value)}`;
      const res = await fetch(url, { headers: { "Accept": "application/json" } });
      if (!res.ok) return { exists: false };
      return await res.json();
    } catch (e) {
      return { exists: false };
    }
  }

  // ========= BLUR CÉDULA =========
  if (cedula) {
    cedula.addEventListener("blur", async function () {
      const valor = cedula.value.trim();
      if (valor === "") return;

      clearErrors();

      if (!regexCedula.test(valor)) {
        showErrors(["⚠️ La cédula debe tener entre 8 y 10 dígitos numéricos."]);
        return;
      }

      const { exists } = await checkField("cedula", valor);
      if (exists) {
        showErrors(["⚠️ La cédula ya está registrada."]);
      }
    });
  }

  // ========= BLUR CORREO =========
  if (correo) {
    correo.addEventListener("blur", async function () {
      const mail = correo.value.trim();
      if (mail === "") return;

      clearErrors();

      if (!regexCorreo.test(mail)) {
        showErrors(["⚠️ El correo no tiene un formato válido."]);
        return;
      }

      const { exists } = await checkField("correo", mail);
      if (exists) {
        showErrors(["⚠️ El correo ya está registrado."]);
      }
    });
  }

  // ========= SUBMIT =========
  form.addEventListener("submit", async function (e) {
    e.preventDefault();
    clearErrors();

    const mensajes = [];

    const c    = cedula    ? cedula.value.trim()    : "";
    const n    = nombres   ? nombres.value.trim()   : "";
    const a    = apellidos ? apellidos.value.trim() : "";
    const t    = telefono  ? telefono.value.trim()  : "";
    const mail = correo    ? correo.value.trim()    : "";
    const pass = password  ? password.value         : "";

    // --- Validaciones de formato ---
    if (!regexCedula.test(c)) {
      mensajes.push("La cédula debe tener entre 8 y 10 dígitos numéricos.");
    }
    if (!regexNombre.test(n)) {
      mensajes.push("El nombre solo debe contener letras y espacios.");
    }
    if (a !== "" && !regexNombre.test(a)) {
      mensajes.push("El apellido solo debe contener letras y espacios.");
    }
    if (!regexTelefono.test(t)) {
      mensajes.push("El teléfono debe tener exactamente 10 dígitos.");
    }
    if (!regexCorreo.test(mail)) {
      mensajes.push("El correo no es válido.");
    }
    if (!pass || pass.length < 8) {
      mensajes.push("La contraseña debe tener mínimo 8 caracteres.");
    }

    // Si formato ok, revisar existencia en BD
    if (mensajes.length === 0) {
      const [cedulaRes, correoRes] = await Promise.all([
        checkField("cedula", c),
        checkField("correo", mail)
      ]);
      if (cedulaRes.exists) mensajes.push("⚠️ La cédula ya está registrada.");
      if (correoRes.exists) mensajes.push("⚠️ El correo ya está registrado.");
    }

    if (mensajes.length > 0) {
      showErrors(mensajes);

      if (!regexCedula.test(c)) { cedula && cedula.focus(); return; }
      if (!regexNombre.test(n)) { nombres && nombres.focus(); return; }
      if (a !== "" && !regexNombre.test(a)) { apellidos && apellidos.focus(); return; }
      if (!regexTelefono.test(t)) { telefono && telefono.focus(); return; }
      if (!regexCorreo.test(mail)) { correo && correo.focus(); return; }
      if (!pass || pass.length < 8) { password && password.focus(); return; }
      return;
    }

    // ========= Envío AJAX REAL =========
    const btn = form.querySelector(".submit-btn");
    if (btn) {
      btn.setAttribute("disabled", "disabled");
      btn.textContent = "Procesando...";
    }

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

      const contentType = res.headers.get("content-type") || "";
      let data = {};

      if (contentType.indexOf("application/json") !== -1) {
        data = await res.json();
      } else {
        // respuesta HTML (error del servidor normal)
        const html = await res.text();
        document.open();
        document.write(html);
        document.close();
        return;
      }

      // 👇 CLAVE: cualquier cosa que NO sea 2xx o ok true es ERROR
      if (!res.ok || !data.ok) {
        const msg = data.msg || "No se pudo completar el registro.";
        showErrors([msg]);
        if (btn) {
          btn.removeAttribute("disabled");
          btn.textContent = "Crear cuenta";
        }
        return;
      }

      // ✅ Solo aquí es éxito REAL
      clearErrors();

      if (window.Swal) {
        await Swal.fire({
          icon: "success",
          title: "¡Registro exitoso!",
          html: '<p style="font-size:15px;margin-top:10px;">Revisa tu correo Gmail para verificar tu cuenta.</p>',
          confirmButtonText: "Ir a iniciar sesión",
          confirmButtonColor: "#4E73DF",
          allowOutsideClick: false,
          backdrop: true
        });
      }

      window.location.href = "?r=login";

    } catch (err) {
      showErrors(["Error de conexión. Intenta de nuevo."]);
      if (btn) {
        btn.removeAttribute("disabled");
        btn.textContent = "Crear cuenta";
      }
    }
  });
});
