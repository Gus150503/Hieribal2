document.addEventListener("DOMContentLoaded", () => {
  const modalProveedor = new bootstrap.Modal(document.getElementById("modalProveedor"));
  const frmProveedor = document.getElementById("frmProveedor");
  const btnNuevo = document.getElementById("btnNuevoProveedor");
  const tabla = document.getElementById("tablaProveedores");

  // === NUEVO PROVEEDOR ===
  if (btnNuevo) {
    btnNuevo.addEventListener("click", () => {
      frmProveedor.reset();
      document.getElementById("idProveedor").value = "";
      document.querySelector("#modalProveedor .modal-title").textContent = "Nuevo Proveedor";
      modalProveedor.show();
    });
  }

  // === EDITAR PROVEEDOR ===
  tabla.addEventListener("click", async (e) => {
    if (e.target.classList.contains("btn-editar")) {
      const id = e.target.dataset.id;
      try {
        const res = await fetch(`/controllers/AdminProveedor.php?action=get&id=${id}`);
        const data = await res.json();
        if (!data.data) throw new Error("Proveedor no encontrado");

        const p = data.data;
        document.getElementById("idProveedor").value = p.id;
        document.getElementById("empresa").value = p.empresa;
        document.getElementById("nit").value = p.nit;
        document.getElementById("contacto").value = p.contacto;
        document.getElementById("telefono").value = p.telefono;
        document.getElementById("email").value = p.email;
        document.getElementById("direccion").value = p.direccion;
        document.getElementById("ciudad").value = p.ciudad;
        document.getElementById("condiciones_pago").value = p.condiciones_pago;
        document.getElementById("creado").value = p.creado;

        document.querySelector("#modalProveedor .modal-title").textContent = "Editar Proveedor";
        modalProveedor.show();
      } catch (err) {
        alert("Error cargando proveedor: " + err.message);
      }
    }
  });

  // === GUARDAR PROVEEDOR ===
  frmProveedor.addEventListener("submit", async (e) => {
    e.preventDefault();
    const formData = new FormData(frmProveedor);
    const id = formData.get("id");

    const action = id ? "update" : "create";

    try {
      const res = await fetch(`/controllers/AdminProveedor.php?action=${action}`, {
        method: "POST",
        body: formData,
      });
      const data = await res.json();

      if (!data.ok) throw new Error(data.msg || "Error en el servidor");

      modalProveedor.hide();
      frmProveedor.reset();
      cargarProveedores();
    } catch (err) {
      alert("Error: " + err.message);
    }
  });

  // === ELIMINAR PROVEEDOR ===
  tabla.addEventListener("click", async (e) => {
    if (e.target.classList.contains("btn-eliminar")) {
      const id = e.target.dataset.id;
      if (!confirm("¿Eliminar este proveedor?")) return;

      try {
        const formData = new FormData();
        formData.append("id", id);

        const res = await fetch(`/controllers/AdminProveedor.php?action=delete`, {
          method: "POST",
          body: formData,
        });
        const data = await res.json();

        if (!data.ok) throw new Error(data.msg || "Error al eliminar");

        cargarProveedores();
      } catch (err) {
        alert("Error: " + err.message);
      }
    }
  });

  // === LISTAR PROVEEDORES ===
  async function cargarProveedores() {
    try {
      const res = await fetch(`/controllers/AdminProveedor.php?action=list`);
      const data = await res.json();

      if (!data.items) return;

      const tbody = tabla.querySelector("tbody");
      tbody.innerHTML = "";
      data.items.forEach((p) => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
          <td>${p.id}</td>
          <td>${p.empresa}</td>
          <td>${p.nit}</td>
          <td>${p.contacto}</td>
          <td>${p.telefono}</td>
          <td>${p.email}</td>
          <td>${p.direccion}</td>
          <td>${p.ciudad}</td>
          <td>${p.condiciones_pago}</td>
          <td>${p.creado}</td>
          <td>
            <button class="btn btn-sm btn-warning btn-editar" data-id="${p.id}">
              <i class="bi bi-pencil"></i>
            </button>
            <button class="btn btn-sm btn-danger btn-eliminar" data-id="${p.id}">
              <i class="bi bi-trash"></i>
            </button>
          </td>
        `;
        tbody.appendChild(tr);
      });
    } catch (err) {
      console.error("Error cargando proveedores:", err);
    }
  }

  cargarProveedores();
});
