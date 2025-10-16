document.addEventListener("DOMContentLoaded", () => {
  const API = window.PRODUCTO_API || "/controllers/AdminProducto.php";
  const tbl = document.querySelector("#tblProducto tbody");
  const modalEl = document.getElementById("modalProducto");
  const frm = document.getElementById("frmProducto");
  const btnNuevo = document.getElementById("btnNuevoProducto");
  const modal = new bootstrap.Modal(modalEl);
  const perPageSel = document.getElementById("perPage");
  const qInput = document.getElementById("qProducto");
  const btnBuscar = document.getElementById("btnBuscarProducto");
  const paginador = document.getElementById("paginador");
  const totalEl = document.getElementById("totalProducto");

  let currentPage = 1;
  let perPage = parseInt(perPageSel.value, 10);
  let q = "";

  /* -------------------- Helpers -------------------- */
  const fetchJSON = async (url, opts = {}) => {
    const res = await fetch(url, opts);
    if (!res.ok) throw new Error("Error HTTP " + res.status);
    return await res.json();
  };

  const loadProductos = async () => {
    try {
      const url = `${API}?action=list&q=${encodeURIComponent(q)}&page=${currentPage}&per=${perPage}`;
      const data = await fetchJSON(url);

      tbl.innerHTML = "";
      if (data.items && data.items.length > 0) {
        data.items.forEach((p) => {
          const tr = document.createElement("tr");
          tr.innerHTML = `
            <td>${p.id}</td>
            <td>${p.nombre}</td>
            <td>${p.categoria || ""}</td>
            <td>${p.marca || ""}</td>
            <td>${p.presentacion || ""}</td>
            <td>${p.unidad || ""}</td>
            <td>${p.descripcion || ""}</td>
            <td>${p.lote || ""}</td>
            <td>${p.f_vencimiento || ""}</td>
            <td>${p.precio_compra ?? 0}</td>
            <td>${p.precio_venta ?? 0}</td>
            <td>${p.iva ?? 0}</td>
            <td>${p.codigo_sku || ""}</td>
            <td>${p.ubicacion || ""}</td>
            <td>
              <span class="badge ${p.estado === "activo" ? "bg-success" : "bg-secondary"}">
                ${p.estado}
              </span>
            </td>
            <td class="text-end">
              <button class="btn btn-sm btn-primary me-1 btn-edit" data-id="${p.id}">
                <i class="bi bi-pencil"></i>
              </button>
              <button class="btn btn-sm btn-danger me-1 btn-delete" data-id="${p.id}">
                <i class="bi bi-trash"></i>
              </button>
              <button class="btn btn-sm btn-warning btn-toggle" data-id="${p.id}">
                <i class="bi bi-power"></i>
              </button>
            </td>
          `;
          tbl.appendChild(tr);
        });
      } else {
        tbl.innerHTML = `<tr><td colspan="16" class="text-center text-muted">No se encontraron productos</td></tr>`;
      }

      totalEl.textContent = `Total: ${data.total || 0}`;
      renderPaginator(data.totalPages || 1);
    } catch (err) {
      console.error(err);
      tbl.innerHTML = `<tr><td colspan="16" class="text-danger text-center">Error cargando productos</td></tr>`;
    }
  };

  const renderPaginator = (totalPages) => {
    paginador.innerHTML = "";
    for (let i = 1; i <= totalPages; i++) {
      const li = document.createElement("li");
      li.className = "page-item " + (i === currentPage ? "active" : "");
      li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
      li.addEventListener("click", (e) => {
        e.preventDefault();
        currentPage = i;
        loadProductos();
      });
      paginador.appendChild(li);
    }
  };

  const resetForm = () => {
    frm.reset();
    frm.classList.remove("was-validated");
    document.getElementById("productoAction").value = "create";
    document.getElementById("idProducto").value = "";
    document.getElementById("modalTitleProducto").textContent = "Nuevo Producto";
  };

  /* -------------------- Eventos -------------------- */
  btnNuevo.addEventListener("click", () => {
    resetForm();
    modal.show();
  });

  btnBuscar.addEventListener("click", () => {
    q = qInput.value.trim();
    currentPage = 1;
    loadProductos();
  });

  perPageSel.addEventListener("change", () => {
    perPage = parseInt(perPageSel.value, 10);
    currentPage = 1;
    loadProductos();
  });

  // Guardar producto
  frm.addEventListener("submit", async (e) => {
    e.preventDefault();
    e.stopPropagation();
    frm.classList.add("was-validated");
    if (!frm.checkValidity()) return;

    const formData = new FormData(frm);
    const action = formData.get("action");

    try {
      const res = await fetch(API, {
        method: "POST",
        body: formData,
      });
      const json = await res.json();
      if (json.ok) {
        modal.hide();
        loadProductos();
      } else {
        alert("Error: " + (json.msg || "No se pudo guardar"));
      }
    } catch (err) {
      console.error(err);
      alert("Error de red");
    }
  });

  // Editar producto
  tbl.addEventListener("click", async (e) => {
    if (e.target.closest(".btn-edit")) {
      const id = e.target.closest(".btn-edit").dataset.id;
      try {
        const res = await fetch(`${API}?action=get&id=${id}`);
        const json = await res.json();
        if (json.data) {
          resetForm();
          Object.entries(json.data).forEach(([k, v]) => {
            const el = frm.querySelector(`[name="${k}"]`);
            if (el) el.value = v ?? "";
          });
          document.getElementById("productoAction").value = "update";
          document.getElementById("idProducto").value = id;
          document.getElementById("modalTitleProducto").textContent = "Editar Producto";
          modal.show();
        }
      } catch (err) {
        console.error(err);
        alert("No se pudo cargar el producto");
      }
    }
  });

  // Eliminar producto
  tbl.addEventListener("click", async (e) => {
    if (e.target.closest(".btn-delete")) {
      const id = e.target.closest(".btn-delete").dataset.id;
      if (!confirm("¿Eliminar este producto?")) return;
      const fd = new FormData();
      fd.append("action", "delete");
      fd.append("id", id);
      try {
        const res = await fetch(API, { method: "POST", body: fd });
        const json = await res.json();
        if (json.ok) loadProductos();
        else alert("Error: " + (json.msg || "No se pudo eliminar"));
      } catch (err) {
        console.error(err);
        alert("Error de red");
      }
    }
  });

  // Toggle estado
  tbl.addEventListener("click", async (e) => {
    if (e.target.closest(".btn-toggle")) {
      const id = e.target.closest(".btn-toggle").dataset.id;
      const fd = new FormData();
      fd.append("action", "toggle");
      fd.append("id", id);
      try {
        const res = await fetch(API, { method: "POST", body: fd });
        const json = await res.json();
        if (json.ok) loadProductos();
      } catch (err) {
        console.error(err);
        alert("Error de red");
      }
    }
  });

  /* -------------------- Inicial -------------------- */
  loadProductos();
});
