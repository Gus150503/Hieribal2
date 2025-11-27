    // admin_devoluciones.js
    (function () {
    if (window.__DEVOL_JS_BOUND__) return;
    window.__DEVOL_JS_BOUND__ = true;

    "use strict";

    // ===== BASE Y ENDPOINT =====
    const base = location.pathname.replace(/\/public\/?$/, "") + "/public";
    const api  = (params = "") => `${base}/?r=admin_devoluciones_api&${params}`;

    // ===== ESTADO =====
    const state = { page: 1, per: 10, total: 0, q: "" };
    let __LIST_SEQ__ = 0;

    // ===== HELPERS =====
    const $  = (s) => document.querySelector(s);
    const $$ = (s) => Array.from(document.querySelectorAll(s));
    const tbl = $("#tblDevol tbody");

    function escapeHtml(s) {
        return (s ?? "").toString().replace(/[&<>"']/g, (m) => ({
        "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;",
        }[m]));
    }
    const formData = (obj) =>
        Object.entries(obj).reduce((fd, [k, v]) => (fd.append(k, v), fd), new FormData());
    const resToJsonSafe = async (res) => {
        try { return await res.json(); }
        catch { return { ok: false, msg: "Respuesta inválida" }; }
    };

    // =========================
    // TOAST BÁSICO
    // =========================
    function uiToast(msg, variant = "info") {
        alert(msg); // reemplaza si quieres tu sistema de toasts
    }

    // =========================
    // CONFIRM
    // =========================
    async function uiConfirmSimple(msg) {
        return confirm(msg);
    }

    // =========================
    // LOADING
    // =========================
    function setLoading(on) {
        if (!tbl) return;
        tbl.innerHTML = on
        ? `<tr><td colspan="12" class="py-4 text-center">
            <div class="spinner-border spinner-border-sm me-2"></div> Cargando…
            </td></tr>`
        : "";
    }

    // =========================
    // RENDER TABLA
    // =========================
    function renderTabla(items) {
        tbl.innerHTML = "";

        if (!items.length) {
        tbl.innerHTML = `
            <tr><td colspan="12" class="text-center text-muted py-3">Sin resultados</td></tr>`;
        return;
        }

        for (const d of items) {
        const tr = document.createElement("tr");
        tr.dataset.id = d.id;

        tr.innerHTML = `
            <td>${d.id}</td>
            <td>${escapeHtml(d.nombre_cliente)}</td>
            <td>${escapeHtml(d.correo)}</td>
            <td>${escapeHtml(d.numero_orden)}</td>
            <td>${escapeHtml(d.telefono)}</td>
            <td>${escapeHtml(d.producto)}</td>
            <td>${escapeHtml(d.motivo_devolucion)}</td>
            <td>${escapeHtml(d.fecha_compra)}</td>
            <td>${escapeHtml(d.fecha_devolucion)}</td>
            <td>${escapeHtml(d.estado)}</td>
            <td class="text-end">
            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-primary" data-editar="${d.id}">
                <i class="bi bi-pencil-square"></i>
                </button>
                <button class="btn btn-outline-danger" data-eliminar="${d.id}">
                <i class="bi bi-trash"></i>
                </button>
            </div>
            </td>
        `;
        tbl.appendChild(tr);
        }
    }

    // =========================
    // PAGINADOR
    // =========================
    function renderPager() {
        const ul = $("#paginadorDevol");
        if (!ul) return;

        const pages = Math.max(1, Math.ceil(state.total / state.per));
        let html = "";

        const prevDis = state.page <= 1 ? "disabled" : "";
        html += `<li class="page-item ${prevDis}">
                <button class="page-link" data-page="${state.page - 1}">&laquo;</button>
                </li>`;

        for (let p = 1; p <= pages; p++) {
        html += `<li class="page-item ${p === state.page ? "active" : ""}">
                    <button class="page-link" data-page="${p}">${p}</button>
                </li>`;
        }

        const nextDis = state.page >= pages ? "disabled" : "";
        html += `<li class="page-item ${nextDis}">
                <button class="page-link" data-page="${state.page + 1}">&raquo;</button>
                </li>`;

        ul.innerHTML = html;
    }

    $("#paginadorDevol")?.addEventListener("click", (e) => {
        const btn = e.target.closest("[data-page]");
        if (!btn) return;
        listar(+btn.dataset.page);
    });

    // =========================
    // BUSCAR
    // =========================
    $("#btnBuscarDevol")?.addEventListener("click", () => {
        state.q = $("#qDevol")?.value.trim() || "";
        listar(1);
    });

    $("#qDevol")?.addEventListener("keydown", (e) => {
        if (e.key === "Enter") {
        state.q = e.target.value.trim();
        listar(1);
        }
    });

    // =========================
    // LISTAR
    // =========================
    async function listar(page = 1) {
        state.page = page;

        const q = encodeURIComponent(state.q || "");
        setLoading(true);

        const seq = ++__LIST_SEQ__;

        try {
        const res = await fetch(
            api(`action=list&page=${state.page}&per=${state.per}&q=${q}`)
        );
        const j = await resToJsonSafe(res);

        if (seq !== __LIST_SEQ__) return;

        const items = j.items || [];
        state.total = +j.total || items.length;

        renderTabla(items);
        renderPager();

        } catch (err) {
        if (seq !== __LIST_SEQ__) return;
        uiToast("No se pudo cargar la lista", "danger");
        }
    }

    // =========================
    // FORMULARIO
    // =========================
    const frm = $("#frmDevol");
    const modal = $("#modalDevol");

    function fillForm(d = {}) {
        const def = {
        id: 0,
        nombre_cliente: "",
        correo: "",
        numero_orden: "",
        telefono: "",
        producto: "",
        motivo_devolucion: "",
        fecha_compra: "",
        fecha_devolucion: "",
        observaciones: "",
        estado: "",
        ...d,
        };

        for (const k in def) {
        const el = frm.querySelector(`#${k}`);
        if (el) el.value = def[k] ?? "";
        }
    }

    function openModal(data, title) {
        fillForm(data);
        $("#modalDevolTitle").textContent = title;

        if (window.bootstrap) {
        new bootstrap.Modal(modal).show();
        } else modal.style.display = "block";
    }

    // =========================
    // CLICK EN TABLA (EDITAR / ELIMINAR)
    // =========================
    tbl?.addEventListener("click", async (e) => {
        const btn = e.target.closest("button");
        if (!btn) return;

        const id = +btn.dataset.editar || +btn.dataset.eliminar;

        // === EDITAR ===
        if (btn.dataset.editar) {
        try {
            const res = await fetch(api(`action=get&id=${id}`));
            const j = await resToJsonSafe(res);
            openModal(j.data, "Editar devolución");
        } catch {
            uiToast("Error al cargar la devolución", "danger");
        }
        return;
        }

        // === ELIMINAR ===
        if (btn.dataset.eliminar) {
        if (!await uiConfirmSimple("¿Eliminar esta devolución?")) return;

        try {
            const res = await fetch(api("action=delete"), {
            method: "POST",
            body: formData({ id }),
            });
            const j = await resToJsonSafe(res);

            if (!j.ok) throw new Error(j.msg);

            uiToast("Devolución eliminada", "success");
            listar(state.page);
        } catch (err) {
            uiToast(err.message, "danger");
        }
        }
    });

    // =========================
    // GUARDAR (CREATE / UPDATE)
    // =========================
    frm?.addEventListener("submit", async (e) => {
        e.preventDefault();

        const fd = new FormData(frm);
        const id = +fd.get("id");
        const isUpdate = id > 0;

        const ok = await uiConfirmSimple(
        isUpdate ? "¿Guardar cambios?" : "¿Registrar devolución?"
        );
        if (!ok) return;

        try {
        const action = isUpdate ? "update" : "create";
        const res = await fetch(api(`action=${action}`), {
            method: "POST",
            body: fd,
        });

        const j = await resToJsonSafe(res);
        if (!j.ok) throw new Error(j.msg);

        uiToast(isUpdate ? "Actualizado" : "Registrado", "success");
        listar(state.page);

        if (window.bootstrap) bootstrap.Modal.getInstance(modal)?.hide();
        else modal.style.display = "none";

        } catch (err) {
        uiToast(err.message, "danger");
        }
    });

    // =========================
    // INIT
    // =========================
    document.addEventListener("DOMContentLoaded", () => listar(1));
    })();
