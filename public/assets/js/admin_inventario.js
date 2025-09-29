document.addEventListener("DOMContentLoaded", () => {
  const API = window.INVENTARIO_API || '/controllers/AdminInventario.php';
  const modalEl = document.getElementById("modalInventario");
  if (!modalEl) { console.warn("Modal no encontrado (id=modalInventario)"); return; }
  const modal = new bootstrap.Modal(modalEl);
  const form = document.getElementById("frmInventario");
  const btnNuevo = document.getElementById("btnNuevo");
  const tblBody = document.querySelector("#tblInventario tbody");
  const qInput = document.getElementById("q");
  const perPageSelect = document.getElementById("perPage");
  const totalLabel = document.getElementById("totalInventario");
  const paginador = document.getElementById("paginador");

  if (!form || !btnNuevo || !tblBody) {
    console.warn("Faltan elementos del DOM necesarios (form/btnNuevo/tblBody).");
    return;
  }

  let currentPage = 1;
  let currentQuery = "";

  // Nuevo
  btnNuevo.addEventListener("click", () => {
    form.reset();
    document.getElementById("id").value = "";
    document.getElementById("modalTitle").textContent = "Nuevo Inventario";
    form.classList.remove("was-validated");
    modal.show();
  });

  // Cargar lista
  async function cargarLista() {
    const per = perPageSelect.value || 10;
    try {
      const resp = await fetch(`${API}?action=list&q=${encodeURIComponent(currentQuery)}&page=${currentPage}&per=${per}`);
      if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
      const data = await resp.json();
      renderTabla(data.data || [], data.total || 0);
    } catch (err) {
      console.error("Error al cargar lista:", err);
      tblBody.innerHTML = `<tr><td colspan="10" class="text-center text-danger">Error al cargar datos</td></tr>`;
      totalLabel.textContent = "";
      paginador.innerHTML = "";
    }
  }

  function renderTabla(rows, total) {
    tblBody.innerHTML = "";
    if (!rows || rows.length === 0) {
      tblBody.innerHTML = `<tr><td colspan="10" class="text-center">No hay registros</td></tr>`;
    } else {
      const frag = document.createDocumentFragment();
      for (const row of rows) {
        const tr = document.createElement("tr");
        tr.innerHTML = `
          <td>${escapeHtml(row.id)}</td>
          <td>${escapeHtml(row.producto_id)}</td>
          <td>${escapeHtml(row.codigo_interno)}</td>
          <td>${escapeHtml(row.stock)}</td>
          <td>${escapeHtml(row.stock_minimo)}</td>
          <td>${escapeHtml(row.stock_maximo)}</td>
          <td>${escapeHtml(row.punto_reorden)}</td>
          <td>${escapeHtml(row.ubicacion)}</td>
          <td>${escapeHtml(row.estado)}</td>
          <td class="text-end">
            <button class="btn btn-sm btn-primary btnEditar" data-id="${row.id}" title="Editar"><i class="bi bi-pencil"></i></button>
            <button class="btn btn-sm btn-danger btnEliminar" data-id="${row.id}" title="Eliminar"><i class="bi bi-trash"></i></button>
          </td>
        `;
        frag.appendChild(tr);
      }
      tblBody.appendChild(frag);
    }
    totalLabel.textContent = `Total: ${total}`;
    renderPaginador(total, parseInt(perPageSelect.value, 10) || 10);
  }

  function renderPaginador(total, per) {
    const pages = Math.max(1, Math.ceil((total || 0) / per));
    paginador.innerHTML = "";
    for (let i = 1; i <= pages; i++) {
      const li = document.createElement("li");
      li.className = `page-item ${i === currentPage ? "active" : ""}`;
      li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
      li.addEventListener("click", (e) => {
        e.preventDefault();
        if (currentPage === i) return;
        currentPage = i;
        cargarLista();
      });
      paginador.appendChild(li);
    }
  }

  // Buscar / perPage
  document.getElementById("btnBuscar").addEventListener("click", () => {
    currentQuery = qInput.value.trim();
    currentPage = 1;
    cargarLista();
  });
  perPageSelect.addEventListener("change", () => { currentPage = 1; cargarLista(); });

  // Guardar/Actualizar
  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    if (!form.checkValidity()) {
      form.classList.add("was-validated");
      return;
    }
    const id = document.getElementById("id").value;
    const action = id ? "update" : "create";
    const formData = new FormData(form);

    try {
      const resp = await fetch(`${API}?action=${action}`, {
        method: "POST",
        body: formData
      });
      const res = await resp.json();
      if (res.ok) {
        modal.hide();
        cargarLista();
      } else {
        alert("Error: " + (res.msg || "Respuesta inválida"));
        console.error("Respuesta error:", res);
      }
    } catch (err) {
      console.error("Error en guardar:", err);
      alert("Error de conexión con el servidor.");
    }
  });

  // Delegación: Editar / Eliminar
  tblBody.addEventListener("click", async (e) => {
    const btnEd = e.target.closest(".btnEditar");
    const btnDel = e.target.closest(".btnEliminar");

    if (btnEd) {
      const id = btnEd.dataset.id;
      try {
        const resp = await fetch(`${API}?action=get&id=${encodeURIComponent(id)}`);
        const data = await resp.json();
        if (data.data) {
          const row = data.data;
          document.getElementById("id").value = row.id;
          document.getElementById("producto_id").value = row.producto_id;
          document.getElementById("codigo_interno").value = row.codigo_interno;
          document.getElementById("stock").value = row.stock;
          document.getElementById("stock_minimo").value = row.stock_minimo;
          document.getElementById("stock_maximo").value = row.stock_maximo;
          document.getElementById("punto_reorden").value = row.punto_reorden;
          document.getElementById("ubicacion").value = row.ubicacion;
          document.getElementById("estado").value = row.estado;
          document.getElementById("modalTitle").textContent = "Editar Inventario";
          form.classList.remove("was-validated");
          modal.show();
        } else {
          alert("Registro no encontrado.");
        }
      } catch (err) {
        console.error("Error al obtener registro:", err);
        alert("Error al obtener datos del servidor.");
      }
    }

    if (btnDel) {
      const id = btnDel.dataset.id;
      if (!confirm("¿Seguro que deseas eliminar este registro?")) return;
      try {
        const resp = await fetch(`${API}?action=delete`, {
          method: "POST",
          body: new URLSearchParams({ id })
        });
        const res = await resp.json();
        if (res.ok) cargarLista();
        else alert("Error: " + (res.msg || "No se pudo eliminar"));
      } catch (err) {
        console.error("Error al eliminar:", err);
        alert("Error de conexión al eliminar.");
      }
    }
  });

  // Helper: escapar simple
  function escapeHtml(s) {
    if (s === null || s === undefined) return '';
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/"/g, '&quot;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
  }

  // Inicial
  cargarLista();
});
