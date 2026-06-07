<script>
    $('.select2').select2({
        width: '90%',
        language: "es",
        dropdownCssClass: "{{ session('tema_preferido') == 'dark' ? 'bg-dark' : '' }}",
        selectionCssClass: "{{ session('tema_preferido') == 'dark' ? 'bg-dark' : '' }}",
    });

    /* ═══════════════════════════════════════════════════════
       ESTADO GLOBAL
    ═══════════════════════════════════════════════════════ */
    const STATE = {
        bovinos: [], // filas en la tabla de venta
        pagos: [], // { tipo_pago, monto, fecha_pago }
        clientes: [],
        listaBovinosActivos: [], // todos los bovinos activos cargados una vez
        clienteSeleccionado: null,
    };

    const TIPOS_PAGO = ['Efectivo', 'Depósito bancario', 'Transferencia QR', 'Cheque', 'Otro'];

    /* ═══════════════════════════════════════════════════════
       HELPERS
    ═══════════════════════════════════════════════════════ */
    const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const fmt = n => 'Bs. ' + parseFloat(n || 0).toFixed(2);
    const fmtNum = n => parseFloat(n || 0).toFixed(2);

    function showAlert(msg, type = 'info') {
        const iconMap = {
            danger: 'error',
            success: 'success',
            warning: 'warning',
            info: 'info'
        };
        const icon = iconMap[type] || 'info';
        Swal.fire({
            theme: localStorage.getItem('theme') || 'dark',
            icon,
            html: msg,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 6000,
            timerProgressBar: true,
            customClass: {
                popup: 'shadow-sm'
            }
        });
    }

    /* ═══════════════════════════════════════════════════════
       CARGA DE BOVINOS
    ═══════════════════════════════════════════════════════ */
    function buildBovinoOptions(lista, selectedId = null) {
        let html = '<option value="">— Seleccione —</option>';
        lista.forEach(b => {
            const sel = (b.id_bovino == selectedId) ? 'selected' : '';
            const carimbo = new Date(b.fecha_nacimiento).getFullYear();
            const potrero = b.potrero ? `P: ${b.potrero.nombre}` : 'Sin potrero';
            const peso = b.peso_actual ? `${b.peso_actual} kg` : '';
            const genero = b.genero === 'macho' ? 'Macho' : 'Hembra';
            html +=
                `<option value="${b.id_bovino}" ${sel}>
                    C: ${carimbo} ${b.identificador} - ${genero} - ${peso} (${b.color_actual}) ${potrero}
                </option>`;
        });
        return html;
    }

    async function cargarBovinos() {
        try {
            const r = await fetch("{{ route('bovinos.listar') }}?estado=activo");
            const data = await r.json();
            STATE.listaBovinosActivos = data.data ?? [];
            actualizarSelectBovino();
        } catch (e) {
            console.error('Error cargando bovinos', e);
        }
    }

    function actualizarSelectBovino() {
        const idsEnTabla = STATE.bovinos.map(b => b.id_bovino);
        const disponibles = STATE.listaBovinosActivos.filter(b => !idsEnTabla.includes(b.id_bovino));
        document.getElementById('select-bovino').innerHTML = buildBovinoOptions(disponibles);
    }

    /* ═══════════════════════════════════════════════════════
       CARGA DE CLIENTES
    ═══════════════════════════════════════════════════════ */
    async function cargarClientes(seleccionarId = null) {
        try {
            const r = await fetch("{{ route('clientes.listar') }}");
            const data = await r.json();
            STATE.clientes = data.data ?? [];
            const sel = document.getElementById('select-cliente');
            const valorActual = seleccionarId ?? sel.value;
            sel.innerHTML = '<option value="">— Elige un cliente —</option>';
            STATE.clientes.filter(c => c.estado === 'activo').forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id_cliente;
                opt.textContent = c.nombre;
                sel.appendChild(opt);
            });
            if (valorActual) {
                sel.value = valorActual;
                sel.dispatchEvent(new Event('change'));
            }
        } catch (e) {
            console.error('Error cargando clientes', e);
        }
    }

    $('#select-cliente').on('change', function() {
        const id = parseInt(this.value);

        const cliente = STATE.clientes.find(
            c => c.id_cliente == id
        ) ?? null;

        STATE.clienteSeleccionado = cliente;

        const info = document.getElementById('cliente-info');
        const btnEdit = document.getElementById('btn-editar-cliente');

        if (cliente) {
            document.getElementById('cliente-celular').textContent = cliente.celular;
            document.getElementById('cliente-estancia').textContent = cliente.estancia;

            info.classList.remove('d-none');
            btnEdit.disabled = false;
        } else {
            info.classList.add('d-none');
            btnEdit.disabled = true;
        }
    });

    /* ═══════════════════════════════════════════════════════
       MODAL CLIENTE
    ═══════════════════════════════════════════════════════ */
    const modalCliente = new bootstrap.Modal(document.getElementById('modal-cliente'));

    document.getElementById('btn-nuevo-cliente').addEventListener('click', () => {
        document.getElementById('cliente-edit-id').value = '';
        document.getElementById('cliente-nombre').value = '';
        document.getElementById('cliente-celular-input').value = '';
        document.getElementById('cliente-estancia-input').value = '';
        document.getElementById('modal-cliente-titulo').innerHTML =
            '<i class="fa-solid fa-user-plus me-2"></i>Nuevo cliente';
        document.getElementById('modal-cliente-error').classList.add('d-none');
        modalCliente.show();
    });

    document.getElementById('btn-editar-cliente').addEventListener('click', () => {
        const c = STATE.clienteSeleccionado;
        if (!c) return;
        document.getElementById('cliente-edit-id').value = c.id_cliente;
        document.getElementById('cliente-nombre').value = c.nombre;
        document.getElementById('cliente-celular-input').value = c.celular;
        document.getElementById('cliente-estancia-input').value = c.estancia;
        document.getElementById('modal-cliente-titulo').innerHTML =
            '<i class="fa-solid fa-pen-to-square me-2"></i>Editar cliente';
        document.getElementById('modal-cliente-error').classList.add('d-none');
        modalCliente.show();
    });

    document.getElementById('btn-guardar-cliente').addEventListener('click', async () => {
        const editId = document.getElementById('cliente-edit-id').value;
        const nombre = document.getElementById('cliente-nombre').value.trim();
        const celular = document.getElementById('cliente-celular-input').value.trim();
        const estancia = document.getElementById('cliente-estancia-input').value.trim();
        const errEl = document.getElementById('modal-cliente-error');
        errEl.classList.add('d-none');

        if (!nombre || !celular || !estancia) {
            errEl.textContent = 'Todos los campos son obligatorios.';
            errEl.classList.remove('d-none');
            return;
        }

        const isEdit = editId !== '';
        const url = isEdit ? `{{ route('clientes.update', ':id') }}`.replace(':id', editId) : "{{ route('clientes.create') }}";
        const method = isEdit ? 'PUT' : 'POST';

        try {
            const r = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken()
                },
                body: JSON.stringify({
                    nombre,
                    celular,
                    estancia
                }),
            });
            const data = await r.json();
            if (data.success) {
                const idSeleccionar = isEdit ? parseInt(editId) : (data.cliente?.id_cliente ?? null);
                await cargarClientes(idSeleccionar);
                modalCliente.hide();
                showAlert(data.message ?? 'Cliente guardado correctamente.', 'success');
            } else {
                errEl.innerHTML = data.message ?? 'Error al guardar el cliente.';
                errEl.classList.remove('d-none');
            }
        } catch (e) {
            errEl.textContent = 'Error de conexión.';
            errEl.classList.remove('d-none');
        }
    });

    /* ═══════════════════════════════════════════════════════
       AGREGAR BOVINO DESDE SELECT
    ═══════════════════════════════════════════════════════ */
    document.getElementById('btn-agregar-bovino').addEventListener('click', () => {
        const sel = document.getElementById('select-bovino');
        const idBovino = parseInt(sel.value);
        if (!idBovino) return;

        const b = STATE.listaBovinosActivos.find(x => x.id_bovino === idBovino);
        if (!b) return;

        STATE.bovinos.push({
            id_bovino: b.id_bovino,
            identificador: b.identificador,
            genero: b.genero,
            potrero: b.potrero?.nombre ?? '—',
            carimbo: new Date(b.fecha_nacimiento).getFullYear(),
            peso_actual: parseFloat(b.peso_actual),
            tipo_precio: 'fijo',
            precio_fijo: 0,
            precio_kg: 0,
            destare: 0,
            rendimiento: 0,
            kg_peso_vivo: parseFloat(b.peso_actual),
            kg_peso_gancho: 0,
            subtotal: 0,
            observacion: '',
        });

        actualizarSelectBovino();
        sel.value = '';
        renderBovinos();
        recalcularTotales();
    });

    /* ═══════════════════════════════════════════════════════
       RENDER TABLA BOVINOS
    ═══════════════════════════════════════════════════════ */
    function renderBovinos() {
        const tbody = document.getElementById('bovinos-tbody');
        tbody.innerHTML = '';

        if (!STATE.bovinos.length) {
            tbody.innerHTML = `
            <tr id="bovinos-empty">
                <td colspan="11" class="text-center text-muted py-3 small">
                    <i class="fa-solid fa-cow me-1 opacity-50"></i>Aún no se han agregado bovinos
                </td>
            </tr>`;
            return;
        }

        STATE.bovinos.forEach((bov, idx) => {
            const esFijo = bov.tipo_precio === 'fijo';
            const tr = document.createElement('tr');
            tr.dataset.idx = idx;

            tr.innerHTML = `
            <td class="text-center text-muted small">${idx + 1}</td>
            <td class="small">
                <div class="fw-semibold">${bov.identificador}</div>
                <div class="text-muted" style="font-size:0.73rem">C:${bov.carimbo} &middot; ${bov.genero === 'macho' ? 'Macho' : 'Hembra'}</div>
            </td>
            <td class="small text-muted">${bov.potrero}</td>
            <td class="text-center small fw-semibold">${fmtNum(bov.peso_actual)}</td>
            <td class="text-center">
                <select class="form-select form-select-sm bov-tipo-precio" data-idx="${idx}">
                    <option value="fijo"  ${esFijo  ? 'selected' : ''}>Precio fijo</option>
                    <option value="kg"    ${!esFijo ? 'selected' : ''}>Por kg</option>
                </select>
            </td>
            <td class="text-center">
                ${esFijo
                    ? `<input type="number" class="form-control form-control-sm bov-precio-fijo" data-idx="${idx}" value="${bov.precio_fijo}" min="0" step="0.01" placeholder="Bs." style="width:90px">`
                    : `<input type="number" class="form-control form-control-sm bov-precio-kg"   data-idx="${idx}" value="${bov.precio_kg}"   min="0" step="0.01" placeholder="Bs./kg" style="width:90px">`
                }
            </td>
            <td class="text-center">
                ${esFijo
                    ? `<span class="text-muted">—</span>`
                    : `<input type="number" class="form-control form-control-sm bov-destare" data-idx="${idx}" value="${bov.destare}" min="0" max="100" step="0.1" placeholder="%" style="width:70px">`
                }
            </td>
            <td class="text-center">
                ${esFijo
                    ? `<span class="text-muted">—</span>`
                    : `<input type="number" class="form-control form-control-sm bov-rendimiento" data-idx="${idx}" value="${bov.rendimiento}" min="0" max="100" step="0.1" placeholder="%" style="width:70px">`
                }
            </td>
            <td class="text-center small text-muted bov-kg-gancho">${esFijo ? '—' : fmtNum(bov.kg_peso_gancho)}</td>
            <td class="text-center small fw-bold text-info bov-subtotal">${fmt(bov.subtotal)}</td>
            <td class="small">
                <input type="text" class="form-control form-control-sm bov-observacion" data-idx="${idx}" value="${bov.observacion}" placeholder="Ej. Cicatriz" maxlength="255">
            </td>
            <td class="text-center">
                <button class="btn btn-sm btn-outline-danger bov-btn-quitar" data-idx="${idx}" title="Quitar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </td>
        `;
            tbody.appendChild(tr);
        });

        // Cambio tipo precio
        tbody.querySelectorAll('.bov-tipo-precio').forEach(sel => {
            sel.addEventListener('change', () => {
                const idx = parseInt(sel.dataset.idx);
                Object.assign(STATE.bovinos[idx], {
                    tipo_precio: sel.value,
                    precio_fijo: 0,
                    precio_kg: 0,
                    destare: 0,
                    rendimiento: 0,
                    kg_peso_gancho: 0,
                    subtotal: 0,
                });
                renderBovinos();
                recalcularTotales();
            });
        });

        // Precio fijo
        tbody.querySelectorAll('.bov-precio-fijo').forEach(inp => {
            inp.addEventListener('input', () => {
                const idx = parseInt(inp.dataset.idx);
                STATE.bovinos[idx].precio_fijo = parseFloat(inp.value) || 0;
                STATE.bovinos[idx].subtotal = STATE.bovinos[idx].precio_fijo;
                actualizarFilaSubtotal(tbody, idx);
                recalcularTotales();
            });
        });

        // Precio por kg / destare / rendimiento
        tbody.querySelectorAll('.bov-precio-kg, .bov-destare, .bov-rendimiento').forEach(inp => {
            inp.addEventListener('input', () => {
                const idx = parseInt(inp.dataset.idx);
                const bov = STATE.bovinos[idx];
                if (inp.classList.contains('bov-precio-kg')) bov.precio_kg = parseFloat(inp.value) || 0;
                if (inp.classList.contains('bov-destare')) bov.destare = parseFloat(inp.value) || 0;
                if (inp.classList.contains('bov-rendimiento')) bov.rendimiento = parseFloat(inp
                    .value) || 0;
                bov.kg_peso_gancho = (bov.kg_peso_vivo - bov.kg_peso_vivo * bov.destare / 100) * bov
                    .rendimiento / 100;
                bov.subtotal = bov.precio_kg * bov.kg_peso_gancho;
                actualizarFilaSubtotal(tbody, idx);
                recalcularTotales();
            });
        });

        // Quitar bovino
        tbody.querySelectorAll('.bov-btn-quitar').forEach(btn => {
            btn.addEventListener('click', () => {
                STATE.bovinos.splice(parseInt(btn.dataset.idx), 1);
                actualizarSelectBovino();
                renderBovinos();
                recalcularTotales();
            });
        });

        // Observación
        tbody.querySelectorAll('.bov-observacion').forEach(inp => {
            inp.addEventListener('input', () => {
                const idx = parseInt(inp.dataset.idx);
                STATE.bovinos[idx].observacion = inp.value;
            });
        });
    }

    function actualizarFilaSubtotal(tbody, idx) {
        const tr = tbody.querySelector(`tr[data-idx="${idx}"]`);
        if (!tr) return;
        const bov = STATE.bovinos[idx];
        const tdGancho = tr.querySelector('.bov-kg-gancho');
        const tdSub = tr.querySelector('.bov-subtotal');
        if (tdGancho && bov.tipo_precio === 'kg') tdGancho.textContent = fmtNum(bov.kg_peso_gancho);
        if (tdSub) tdSub.textContent = fmt(bov.subtotal);
    }

    /* ═══════════════════════════════════════════════════════
       PAGOS
    ═══════════════════════════════════════════════════════ */
    document.getElementById('btn-agregar-pago').addEventListener('click', () => {
        STATE.pagos.push({
            tipo_pago: 'Efectivo',
            monto: 0,
            fecha_pago: new Date().toISOString().slice(0, 10),
        });
        renderPagos();
        recalcularTotales();
    });

    function renderPagos() {
        const cont = document.getElementById('pagos-container');
        cont.innerHTML = '';

        if (!STATE.pagos.length) {
            cont.innerHTML = `
            <p id="pagos-empty" class="text-center text-muted small py-2 mb-0">
                <i class="fa-regular fa-credit-card me-1 opacity-50"></i>Sin pagos registrados
            </p>`;
            return;
        }

        STATE.pagos.forEach((pago, idx) => {
            const row = document.createElement('div');
            row.className = 'row g-2 align-items-center mb-2';
            row.innerHTML = `
            <div class="col-sm-4">
                <select class="form-select form-select-sm pago-tipo" data-idx="${idx}">
                    ${TIPOS_PAGO.map(t => `<option value="${t}" ${pago.tipo_pago === t ? 'selected' : ''}>${t}</option>`).join('')}
                </select>
            </div>
            <div class="col-sm-3">
                <input type="number" class="form-control form-control-sm pago-monto" data-idx="${idx}"
                    value="${pago.monto}" min="0" step="0.01" placeholder="Monto">
            </div>
            <div class="col-sm-4">
                <input type="date" class="form-control form-control-sm pago-fecha" data-idx="${idx}"
                    value="${pago.fecha_pago}">
            </div>
            <div class="col-sm-1 text-end">
                <button class="btn btn-sm btn-outline-danger pago-btn-quitar" data-idx="${idx}" title="Quitar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        `;
            cont.appendChild(row);
        });

        cont.querySelectorAll('.pago-tipo').forEach(sel => {
            sel.addEventListener('change', () => {
                STATE.pagos[parseInt(sel.dataset.idx)].tipo_pago = sel.value;
            });
        });
        cont.querySelectorAll('.pago-monto').forEach(inp => {
            inp.addEventListener('input', () => {
                STATE.pagos[parseInt(inp.dataset.idx)].monto = parseFloat(inp.value) || 0;
                recalcularTotales();
            });
        });
        cont.querySelectorAll('.pago-fecha').forEach(inp => {
            inp.addEventListener('change', () => {
                STATE.pagos[parseInt(inp.dataset.idx)].fecha_pago = inp.value;
            });
        });
        cont.querySelectorAll('.pago-btn-quitar').forEach(btn => {
            btn.addEventListener('click', () => {
                STATE.pagos.splice(parseInt(btn.dataset.idx), 1);
                renderPagos();
                recalcularTotales();
            });
        });
    }

    /* ═══════════════════════════════════════════════════════
       RESUMEN
    ═══════════════════════════════════════════════════════ */
    function recalcularTotales() {
        const total = STATE.bovinos.reduce((s, b) => s + b.subtotal, 0);
        const pagado = STATE.pagos.reduce((s, p) => s + p.monto, 0);
        const pendiente = Math.max(0, total - pagado);

        document.getElementById('res-cantidad').textContent = STATE.bovinos.length;
        document.getElementById('res-total').textContent = fmt(total);
        document.getElementById('res-pagado').textContent = fmt(pagado);
        document.getElementById('res-pendiente').textContent = fmt(pendiente);
    }

    /* ═══════════════════════════════════════════════════════
       GUARDAR VENTA
    ═══════════════════════════════════════════════════════ */
    document.getElementById('btn-guardar-venta').addEventListener('click', async () => {
        const idCliente = document.getElementById('select-cliente').value;
        const fechaVenta = document.getElementById('fecha-venta').value;

        if (!idCliente) {
            showAlert('Selecciona un cliente para continuar.');
            return;
        }
        if (!fechaVenta) {
            showAlert('Indica la fecha de venta.');
            return;
        }
        if (!STATE.bovinos.length) {
            showAlert('Agrega al menos un bovino a la venta.');
            return;
        }

        for (const b of STATE.bovinos) {
            if (b.tipo_precio === 'fijo' && b.precio_fijo <= 0) {
                showAlert(
                    `El bovino <b>${b.identificador}</b> tiene precio fijo en 0. Ingresa un valor válido.`
                );
                return;
            }
            if (b.tipo_precio === 'kg' && b.precio_kg <= 0) {
                showAlert(
                    `El bovino <b>${b.identificador}</b> tiene precio/kg en 0. Ingresa un valor válido.`
                );
                return;
            }
        }

        const total = STATE.bovinos.reduce((s, b) => s + b.subtotal, 0);

        const payload = {
            id_cliente: parseInt(idCliente),
            fecha_venta: fechaVenta,
            total,
            bovinos: STATE.bovinos.map(b => ({
                id_bovino: b.id_bovino,
                precio_fijo: b.tipo_precio === 'fijo' ? b.precio_fijo : 0,
                precio_kg: b.tipo_precio === 'kg' ? b.precio_kg : 0,
                destare: b.destare,
                rendimiento: b.rendimiento,
                kg_peso_vivo: b.kg_peso_vivo,
                kg_peso_gancho: b.kg_peso_gancho,
                subtotal: b.subtotal,
                observacion: b.observacion || null,
            })),
            pagos: STATE.pagos.map(p => ({
                tipo_pago: p.tipo_pago,
                monto: p.monto,
                fecha_pago: p.fecha_pago,
            })),
        };

        const btn = document.getElementById('btn-guardar-venta');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando\u2026';

        try {
            const r = await fetch("{{ route('ventas.create') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken()
                },
                body: JSON.stringify(payload),
            });
            const data = await r.json();
            if (data.success) {
                showAlert(data.message ?? 'Venta registrada correctamente.', 'success');
                window.open("{{ route('ventas.imprimir', ':id') }}".replace(':id',
                            data.venta.id_venta), '_blank',
                        'noopener,noreferrer');
                setTimeout(() => 
                window.location.href = "{{ route('ventas.index') }}", 1200
                );
            } else {
                showAlert(data.message ?? 'Error al registrar la venta.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-2"></i>Registrar venta';
            }
        } catch (e) {
            showAlert('Error de conexi\u00f3n. Intenta nuevamente.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-2"></i>Registrar venta';
        }
    });

    /* ═══════════════════════════════════════════════════════
       INIT
    ═══════════════════════════════════════════════════════ */
    (async () => {
        await cargarBovinos();
        await cargarClientes();
        recalcularTotales();
    })();
</script>
