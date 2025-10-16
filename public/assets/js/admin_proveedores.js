document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("frmProveedor");

  if (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      e.stopPropagation();

      if (!form.checkValidity()) {
        form.classList.add("was-validated");
      } else {
        guardarProveedor();
      }
    });
  }

  function guardarProveedor() {
    const data = new FormData(form);

    fetch(window.PROVEEDOR_API, {
      method: "POST",
      body: data
    })
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          alert("Proveedor guardado con éxito");
          bootstrap.Modal.getInstance(document.getElementById("modalProveedor")).hide();
          form.reset();
          form.classList.remove("was-validated");
        } else {
          alert("Error: " + (res.message || "No se pudo guardar"));
        }
      })
      .catch(err => {
        console.error(err);
        alert("Error en el servidor");
      });
  }
});
document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("frmProveedor");
  const modalEl = document.getElementById("modalProveedor");
  const modal = new bootstrap.Modal(modalEl);
  const table = document.getElementById("tblProveedor").querySelector("tbody");

  document.getElementById("btnNuevoProveedor").addEventListener("click", () => {
    form.reset();
    form.classList.remove("was-validated");
    modalEl.querySelector(".modal-title").textContent = "Nuevo Proveedor";
    modal.show();
  });

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    if (!form.checkValidity()) {
      form.classList.add("was-validated");
      return;
    }

    const formData = new FormData(form);
    try {
      const res = await fetch(window.PROVEEDOR_API, {
        method: "POST",
        body: formData,
      });
      const data = await res.json();
      if (data.success) {
        alert("✅ Proveedor guardado correctamente");
        modal.hide();
        location.reload();
      } else {
        alert("⚠️ Error: " + (data.message || "No se pudo guardar"));
      }
    } catch (err) {
      console.error(err);
      alert("Error al guardar el proveedor");
    }
  });

  table.addEventListener("click", async (e) => {
    const btn = e.target.closest("button");
    if (!btn) return;

    const id = btn.dataset.id;

    if (btn.classList.contains("btn-edit")) {
      const res = await fetch(`${window.PROVEEDOR_API}?action=get&id=${id}`);
      const data = await res.json();
      if (data) {
        Object.keys(data).forEach((key) => {
          if (form[key]) form[key].value = data[key];
        });
        modalEl.querySelector(".modal-title").textContent = "Editar Proveedor";
        modal.show();
      }
    }

    if (btn.classList.contains("btn-delete")) {
      if (confirm("¿Seguro de eliminar este proveedor?")) {
        const res = await fetch(window.PROVEEDOR_API, {
          method: "POST",
          body: new URLSearchParams({ action: "delete", id }),
        });
        const data = await res.json();
        if (data.success) {
          alert("✅ Proveedor eliminado");
          location.reload();
        }
      }
    }
  });
});
