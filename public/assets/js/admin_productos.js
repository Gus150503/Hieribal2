document.addEventListener("DOMContentLoaded", () => {
  const modalProducto = new bootstrap.Modal(document.getElementById("modalProducto"));
  const frmProducto = document.getElementById("frmProducto");
  const btnNuevo = document.getElementById("btnNuevoProducto"); // botón "Nuevo Producto"
  const tabla = document.getElementById("tablaProductos"); // tu tabla de productos

  // === NUEVO PRODUCTO ===
  if (btnNuevo) {
    btnNuevo.addEventListener("click", () => {
      frmProducto.reset();
      document.getElementById("idProducto").value = "";
      document.querySelector("#modalProducto .modal-title").textContent = "Nuevo Producto";
      modalProducto.show();
    });
  }

  // === EDITAR PRODUCTO ===
  tabla.addEventListener("click", async (e) => {
    if (e.target.classList.contains("btn-editar")) {
      const id = e.target.dataset.id;
      try {
        const res = await fetch(`/controllers/AdminProducto.php?action=get&id=${id}`);
        const data = await res.json();
        if (!data.data) throw new Error("Producto no encontrado");

        const p = data.data;
        document.getElementById("idProducto").value = p.id;
        document.getElementById("nombre").value = p.nombre;
        document.getElementById("categoria").value = p.categoria;
        document.getElementById("marca").value = p.marca;
        document.getElementById("presentacion").value = p.presentacion;
        document.getElementById("unidad").value = p.unidad;
        document.getElementById("descripcion").value = p.descripcion;
        document.getElementById("lote").value = p.lote;
        document.getElementById("fvencimiento").value = p.f_vencimiento || "";
        document.getElementById("precio_compra").value = p.precio_compra;
        document.getElementById("precio_venta").value = p.precio_venta;
        document.getElementById("iva").value = p.iva;
        document.getElementById("codigo_sku").value = p.codigo_sku;
        document.getElementById("ubicacion").value = p.ubicacion;
        document.getElementById("estado").value = p.estado;

        document.querySelector("#modalProducto .modal-title").textContent = "Editar Producto";
        modalProducto.show();
      } catch (err) {
        alert("Error cargando producto: " + err.message);
      }
    }
  });

  // === GUARDAR PRODUCTO ===
  frmProducto.addEventListener("submit", async (e) => {
    e.preventDefault();
    const formData = new FormData(frmProducto);
    const id = formData.get("id");

    const action = id ? "update" : "create";

    try {
      const res = await fetch(`/controllers/AdminProducto.php?action=${action}`, {
        method: "POST",
        body: formData,
      });
      const data = await res.json();

      if (!data.ok) throw new Error(data.msg || "Error en el servidor");

      modalProducto.hide();
      frmProducto.reset();
      cargarProductos(); // recarga la tabla
    } catch (err) {
      alert("Error: " + err.message);
    }
  });

  // === ELIMINAR PRODUCTO ===
  tabla.addEventListener("click", async (e) => {
    if (e.target.classList.contains("btn-eliminar")) {
      const id = e.target.dataset.id;
      if (!confirm("¿Eliminar este producto?")) return;

      try {
        const formData = new FormData();
        formData.append("id", id);

        const res = await fetch(`/controllers/AdminProducto.php?action=delete`, {
          method: "POST",
          body: formData,
        });
        const data = await res.json();

        if (!data.ok) throw new Error(data.msg || "Error al eliminar");

        cargarProductos();
      } catch (err) {
        alert("Error: " + err.message);
      }
    }
  });

  // === LISTAR PRODUCTOS ===
  async function cargarProductos() {
    try {
      const res = await fetch(`/controllers/AdminProducto.php?action=list`);
      const data = await res.json();

      if (!data.items) return;

      const tbody = tabla.querySelector("tbody");
      tbody.innerHTML = "";
      data.items.forEach((p) => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
          <td>${p.id}</td>
          <td>${p.nombre}</td>
          <td>${p.categoria}</td>
          <td>${p.marca}</td>
          <td>${p.precio_venta}</td>
          <td>${p.estado}</td>
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
      console.error("Error cargando productos:", err);
    }
  }

  // Carga inicial
  cargarProductos();
});
