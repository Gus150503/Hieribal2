document.addEventListener("DOMContentLoaded", () => {
  const modalInventario = new bootstrap.Modal(document.getElementById("modalInventario"));
  const frmInventario = document.getElementById("frmInventario");
  const btnNuevo = document.getElementById("btnNuevoInventario");
  const tabla = document.getElementById("tablaInventario");

  // === NUEVO INVENTARIO ===
  if (btnNuevo) {
    btnNuevo.addEventListener("click", () => {
      frmInventario.reset();
      document.getElementById("idInventario").value = "";
      document.querySelector("#modalInventario .modal-title").textContent = "Nuevo Inventario";
      modalInventario.show();
    });
  }

  // === EDITAR INVENTARIO ===
  tabla.addEventListener("click", async (e) => {
    if (e.target.classList.contains("btn-editar")) {
      const id = e.target.dataset.id;
      try {
        const res = await fetch(`/controllers/AdminInventario.php?action=get&id=${id}`);
        const data = await res.json();
        if (!data.data) throw new Error("Inventario no encontrado");

        const i = data.data;
        document.getElementById("idInventario").value = i.id;
        document.getElementById("producto").value = i.producto;
        document.getElementById("codigo_interno").value = i.codigo_interno;
        document.getElementById("stock").value = i.stock;
        document.getElementById("stock_minimo").value = i.stock_minimo;
        document.getElementById("stock_maximo").value = i.stock_maximo;
        document.getElementById("punto_reorden").value = i.punto_reorden;
        document.getElementById("ubicacion").value = i.ubicacion;
        document.getElementById("estado").value = i.estado;

        document.querySelector("#modalInventario .modal-title").textContent = "Editar Inventario";
        modalInventario.show();
      } catch (err) {
        alert("Error cargando inventario: " + err.message);
      }
    }
  });

  // === GUARDAR INVENTARIO ===
  frmInventario.addEventListener("submit", async (e) => {
    e.preventDefault();
    const formData = new FormData(frmInventario);
    const id = formData.get("id");

    const action = id ? "update" : "create";

    try {
      const res = await fetch(`/controllers/AdminInventario.php?action=${action}`, {
        method: "POST",
        body: formData,
      });
      const data = await res.json();

      if (!data.ok) throw new Error(data.msg || "Error en el servidor");

      modalInventario.hide();
      frmInventario.reset();
      cargarInventario();
    } catch (err) {
      alert("Error: " + err.message);
    }
  });

  // === ELIMINAR INVENTARIO ===
  tabla.addEventListener("click", async (e) => {
    if (e.target.classList.contains("btn-eliminar")) {
      const id = e.target.dataset.id;
      if (!confirm("¿Eliminar este inventario?")) return;

      try {
        const formData = new FormData();
        formData.append("id", id);

        const res = await fetch(`/controllers/AdminInventario.php?action=delete`, {
          method: "POST",
          body: formData,
        });
        const data = await res.json();

        if (!data.ok) throw new Error(data.msg || "Error al eliminar");

        cargarInventario();
      } catch (err) {
        alert("Error: " + err.message);
      }
    }
  });

  // === LISTAR INVENTARIO ===
  async function cargarInventario() {
    try {
      const res = await fetch(`/controllers/AdminInventario.php?action=list`);
      const data = await res.json();

      if (!data.items) return;

      const tbody = tabla.querySelector("tbody");
      tbody.innerHTML = "";
      data.items.forEach((i) => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
          <td>${i.id}</td>
          <td>${i.producto}</td>
          <td>${i.codigo_interno}</td>
          <td>${i.stock}</td>
          <td>${i.stock_minimo}</td>
          <td>${i.stock_maximo}</td>
          <td>${i.punto_reorden}</td>
          <td>${i.ubicacion}</td>
          <td>${i.estado}</td>
          <td>
            <button class="btn btn-sm btn-warning btn-editar" data-id="${i.id}">
              <i class="bi bi-pencil"></i>
            </button>
            <button class="btn btn-sm btn-danger btn-eliminar" data-id="${i.id}">
              <i class="bi bi-trash"></i>
            </button>
          </td>
        `;
        tbody.appendChild(tr);
      });
    } catch (err) {
      console.error("Error cargando inventario:", err);
    }
  }

  cargarInventario();
});
