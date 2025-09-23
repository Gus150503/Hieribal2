document.addEventListener("DOMContentLoaded", () => {
  const modal = new bootstrap.Modal(document.getElementById("modalUsuario"));
  const form = document.getElementById("frmUsuario");
  const btnNuevo = document.getElementById("btnNuevo");
  const tblBody = document.querySelector("#tblInventario tbody");
  const qInput = document.getElementById("q");
  const perPageSelect = document.getElementById("perPage");
  const totalLabel = document.getElementById("totalInventario");
  const paginador = document.getElementById("paginador");

  let currentPage = 1;
  let currentQuery = "";

  // Mostrar modal para nuevo registro
  btnNuevo.addEventListener("click", () => {
    form.reset();
    document.getElementById("id_usuario").value = "";
    document.getElementById("modalTitle").textContent = "Nuevo Inventario";
    modal.show();
  });

  // Cargar lista de inventario
  async function cargarLista() {
    const per = perPageSelect.value;
    const resp = await fetch(`/controllers/AdminInventario.php?action=list&q=${encodeURIComponent(currentQuery)}&page=${currentPage}&per=${per}`);
    const data = await resp.json();
    tblBody.innerHTML = "";
    if (data.data.length === 0) {
      tblBody.innerHTML = `<tr><td colspan="10" class="text-center">No hay registros</td></tr>`;
    } else {
      for (const row of data.data) {
        tblBody.innerHTML += `
          <tr>
            <td>${row.id}</td>
            <td>${row.producto_id}</td>
            <td>${row.codigo_interno}</td>
            <td>${row.stock}</td>
            <td>${row.stock_minimo}</td>
            <td>${row.stock_maximo}</td>
            <td>${row.punto_reorden}</td>
            <td>${row.ubicacion}</td>
            <td>${row.estado}</td>
            <td class="text-end">
              <button class="btn btn-sm btn-primary btnEditar" data-id="${row.id}"><i class="bi bi-pencil"></i></button>
              <button class="btn btn-sm btn-danger btnEliminar" data-id="${row.id}"><i class="bi bi-trash"></i></button>
            </td>
          </tr>
        `;
      }
    }
    totalLabel.textContent = `Total: ${data.total}`;
    renderPaginador(data.total, per);
  }

  // Paginador
  function renderPaginador(total, per) {
    const pages = Math.ceil(total / per);
    paginador.innerHTML = "";
    for (let i = 1; i <= pages; i++) {
      const li = document.createElement("li");
      li.className = `page-item ${i === currentPage ? "active" : ""}`;
      li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
      li.addEventListener("click", (e) => {
        e.preventDefault();
        currentPage = i;
        cargarLista();
      });
      paginador.appendChild(li);
    }
  }

  // Buscar
  document.getElementById("btnBuscar").addEventListener("click", () => {
    currentQuery = qInput.value.trim();
    currentPage = 1;
    cargarLista();
  });

  perPageSelect.addEventListener("change", () => {
    currentPage = 1;
    cargarLista();
  });

  // Guardar/Actualizar
  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    if (!form.checkValidity()) {
      form.classList.add("was-validated");
      return;
    }
    const id = document.getElementById("id_usuario").value;
    const action = id ? "update" : "create";

    const resp = await fetch(`/controllers/AdminInventario.php?action=${action}`, {
      method: "POST",
      body: new FormData(form)
    });
    const res = await resp.json();
    if (res.ok) {
      modal.hide();
      cargarLista();
      form.classList.remove("was-validated");
    } else {
      alert("Error: " + res.msg);
    }
  });

  // Delegación de botones Editar/Eliminar
  tblBody.addEventListener("click", async (e) => {
    if (e.target.closest(".btnEditar")) {
      const id = e.target.closest(".btnEditar").dataset.id;
      const resp = await fetch(`/controllers/AdminInventario.php?action=get&id=${id}`);
      const data = await resp.json();
      if (data.data) {
        const row = data.data;
        document.getElementById("id_usuario").value = row.id;
        document.getElementById("producto_id").value = row.producto_id;
        document.getElementById("codigo_interno").value = row.codigo_interno;
        document.getElementById("stock").value = row.stock;
        document.getElementById("stock_minimo").value = row.stock_minimo;
        document.getElementById("stock_maximo").value = row.stock_maximo;
        document.getElementById("punto_reorden").value = row.punto_reorden;
        document.getElementById("ubicacion").value = row.ubicacion;
        document.getElementById("estado").value = row.estado;
        document.getElementById("modalTitle").textContent = "Editar Inventario";
        modal.show();
      }
    }

    if (e.target.closest(".btnEliminar")) {
      const id = e.target.closest(".btnEliminar").dataset.id;
      if (confirm("¿Seguro que deseas eliminar este registro?")) {
        const resp = await fetch(`/controllers/AdminInventario.php?action=delete`, {
          method: "POST",
          body: new URLSearchParams({ id })
        });
        const res = await resp.json();
        if (res.ok) cargarLista();
        else alert("Error: " + res.msg);
      }
    }
  });

  // Inicial
  cargarLista();
});
