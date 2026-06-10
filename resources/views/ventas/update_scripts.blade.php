<script>
    // Datos de la venta inyectados desde Laravel
    const VENTA_ACTUAL = @json($venta);

    $('.select2').select2({
        width: '90%',
        language: "es",
        dropdownCssClass: "{{ session('tema_preferido') == 'dark' ? 'bg-dark' : '' }}",
        selectionCssClass: "{{ session('tema_preferido') == 'dark' ? 'bg-dark' : '' }}",
    });

    /* ═══════════════════════════════════════════════════════
       ESTADO GLOBAL  —  inicializado con los datos de la venta
    ═══════════════════════════════════════════════════════ */
    const STATE = {
        // Campos de nivel venta
        tipo_precio: VENTA_ACTUAL.tipo_precio ?? 'precio_fijo',
        destare: parseFloat(VENTA_ACTUAL.destare) || 0,
        rendimiento: parseFloat(VENTA_ACTUAL.rendimiento) || 0,
        precio_kg: parseFloat(VENTA_ACTUAL.precio_kg) || 0,

        // Bovinos: mapeamos desde el pivot (nuevo esquema sin precio_fijo/precio_kg por fila)
        bovinos: VENTA_ACTUAL.bovinos.map(b => ({
            id_bovino: b.id_bovino,
            identificador: b.identificador,
            genero: b.genero,
            potrero: b.potrero ? b.potrero.nombre : '—',
            carimbo: new Date(b.fecha_nacimiento).getFullYear(),
            peso_actual: parseFloat(b.peso_actual),
            kg_peso_vivo: parseFloat(b.pivot.kg_peso_vivo),
            kg_peso_gancho: parseFloat(b.pivot.kg_peso_gancho),
            subtotal: parseFloat(b.pivot.subtotal),
            observacion: b.pivot.observacion || '',
        })),

        // Pagos: id_pago 0 = nuevo, id real = existente
        pagos: VENTA_ACTUAL.pagos.map(p => ({
            id_pago: p.id_pago,
            tipo_pago: p.tipo_pago,
            monto: parseFloat(p.monto),
            fecha_pago: p.fecha_pago ? p.fecha_pago.split(' ')[0] : '',
        })),

        clientes: [],
        listaBovinosActivos: [],
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
        Swal.fire({
            theme: localStorage.getItem('theme') || 'dark',
            icon: iconMap[type] || 'info',
            html: msg,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 6000,
            timerProgressBar: true,
            customClass: {
                popup: 'shadow-sm'
            },
        });
    }

    /* ═══════════════════════════════════════════════════════
       TIPO DE PRECIO GLOBAL
    ═══════════════════════════════════════════════════════ */
    document.getElementById('select-tipo-precio').addEventListener('change', function() {
        STATE.tipo_precio = this.value;
        const esPorKg = STATE.tipo_precio === 'precio_kg';

        document.querySelectorAll('.campo-precio-kg').forEach(el => {
            el.classList.toggle('d-none', !esPorKg);
        });

        // Al cambiar tipo, recalcula con los valores actuales de los inputs globales
        recalcularTodosLosGanchos();
        renderThead();
        renderBovinos();
        recalcularTotales();
    });

    document.getElementById('input-destare').addEventListener('input', function() {
        STATE.destare = parseFloat(this.value) || 0;
        recalcularTodosLosGanchos();
        recalcularTotales();
    });

    document.getElementById('input-rendimiento').addEventListener('input', function() {
        STATE.rendimiento = parseFloat(this.value) || 0;
        recalcularTodosLosGanchos();
        recalcularTotales();
    });

    document.getElementById('input-precio-kg').addEventListener('input', function() {
        STATE.precio_kg = parseFloat(this.value) || 0;
        recalcularTodosLosGanchos();
        recalcularTotales();
    });

    // Recalcula gancho y subtotal de todos los bovinos con los valores globales,
    // y actualiza solo las celdas afectadas sin re-renderizar la tabla entera
    function recalcularTodosLosGanchos() {
        if (STATE.tipo_precio !== 'precio_kg') return;
        const tbody = document.getElementById('bovinos-tbody');
        STATE.bovinos.forEach((bov, idx) => {
            bov.kg_peso_gancho = (bov.kg_peso_vivo - bov.kg_peso_vivo * STATE.destare / 100) * STATE
                .rendimiento / 100;
            bov.subtotal = STATE.precio_kg * bov.kg_peso_gancho;
            const tr = tbody.querySelector(`tr[data-idx="${idx}"]`);
            if (!tr) return;
            const tdGancho = tr.querySelector('.bov-kg-gancho');
            const tdSub = tr.querySelector('.bov-subtotal');
            if (tdGancho) tdGancho.textContent = fmtNum(bov.kg_peso_gancho);
            if (tdSub) tdSub.textContent = fmt(bov.subtotal);
        });
    }

    /* ═══════════════════════════════════════════════════════
       THEAD DINÁMICO
    ═══════════════════════════════════════════════════════ */
    function renderThead() {
        const esPorKg = STATE.tipo_precio === 'precio_kg';
        document.getElementById('bovinos-thead').innerHTML = `
            <tr>
                <th class="text-center" style="width:32px">#</th>
                <th>Identificador</th>
                <th>Potrero</th>
                <th class="text-center">Peso actual kg</th>
                ${esPorKg ? `
                <th class="text-center">Kg vivo (editable)</th>
                <th class="text-center">Kg Gancho</th>
                ` : ''}
                <th class="text-center">${esPorKg ? 'Subtotal' : 'Subtotal (Bs.)'}</th>
                <th>Observaciones</th>
                <th style="width:40px"></th>
            </tr>
        `;
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
                `<option value="${b.id_bovino}" ${sel}>C: ${carimbo} ${b.identificador} - ${genero} - ${peso} (${b.color_actual}) ${potrero}</option>`;
        });
        return html;
    }

    async function cargarBovinos() {
        try {
            const r = await fetch("{{ route('bovinos.listar') }}?estado=activo");
            const data = await r.json();
            STATE.listaBovinosActivos = data.data ?? [];
            // Incluir los bovinos ya en la venta (estado vendido) para poder re-agregarlos si se quitan
            VENTA_ACTUAL.bovinos.forEach(b => {
                if (!STATE.listaBovinosActivos.find(x => x.id_bovino === b.id_bovino)) {
                    STATE.listaBovinosActivos.push(b);
                }
            });
            actualizarSelectBovino();
        } catch (e) {
            console.error('Error cargando bovinos', e);
        }
    }

    function actualizarSelectBovino() {
        const idsEnTabla = STATE.bovinos.map(b => b.id_bovino);
        const disponibles = STATE.listaBovinosActivos.filter(b => !idsEnTabla.includes(b.id_bovino));
        document.getElementById('select-bovino').innerHTML = buildBovinoOptions(disponibles);
        if (typeof $ !== 'undefined') $('#select-bovino').trigger('change.select2');
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
                $('#select-cliente').trigger('change');
            }
        } catch (e) {
            console.error('Error cargando clientes', e);
        }
    }

    $('#select-cliente').on('change', function() {
        const id = parseInt(this.value);
        const cliente = STATE.clientes.find(c => c.id_cliente == id) ?? null;
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
        const url = isEdit ?
            "{{ route('clientes.update', ':id') }}".replace(':id', editId) :
            "{{ route('clientes.create') }}";
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

        const kgPesoVivo = parseFloat(b.peso_actual);
        const kgPesoGancho = STATE.tipo_precio === 'precio_kg' ?
            (kgPesoVivo - kgPesoVivo * STATE.destare / 100) * STATE.rendimiento / 100 :
            0;
        const subtotal = STATE.tipo_precio === 'precio_kg' ?
            STATE.precio_kg * kgPesoGancho :
            0;

        STATE.bovinos.push({
            id_bovino: b.id_bovino,
            identificador: b.identificador,
            genero: b.genero,
            potrero: b.potrero?.nombre ?? '—',
            carimbo: new Date(b.fecha_nacimiento).getFullYear(),
            peso_actual: kgPesoVivo,
            kg_peso_vivo: kgPesoVivo,
            kg_peso_gancho: kgPesoGancho,
            subtotal,
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
        const esPorKg = STATE.tipo_precio === 'precio_kg';
        tbody.innerHTML = '';

        if (!STATE.bovinos.length) {
            const cols = esPorKg ? 9 : 7;
            tbody.innerHTML = `
                <tr id="bovinos-empty">
                    <td colspan="${cols}" class="text-center text-muted py-3 small">
                        <i class="fa-solid fa-cow me-1 opacity-50"></i>Aún no se han agregado bovinos
                    </td>
                </tr>`;
            return;
        }

        STATE.bovinos.forEach((bov, idx) => {
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
                ${esPorKg ? `
                <td class="text-center">
                    <input type="number" class="form-control form-control-sm bov-kg-vivo" data-idx="${idx}"
                        value="${fmtNum(bov.kg_peso_vivo)}" min="0" step="0.01" style="width:90px">
                </td>
                <td class="text-center small text-muted bov-kg-gancho">${fmtNum(bov.kg_peso_gancho)}</td>
                ` : ''}
                <td class="text-center small fw-bold text-info bov-subtotal">
                    ${esPorKg
                        ? fmt(bov.subtotal)
                        : `<input type="number" class="form-control form-control-sm bov-subtotal-input" data-idx="${idx}"
                               value="${fmtNum(bov.subtotal)}" min="0" step="0.01" style="width:110px">`
                    }
                </td>
                <td class="small">
                    <input type="text" class="form-control form-control-sm bov-observacion" data-idx="${idx}"
                        value="${bov.observacion}" placeholder="Ej. Cicatriz" maxlength="150">
                </td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-danger bov-btn-quitar" data-idx="${idx}" title="Quitar">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        // Kg vivo editable → recalcula gancho y subtotal de esa fila
        tbody.querySelectorAll('.bov-kg-vivo').forEach(inp => {
            inp.addEventListener('input', () => {
                const idx = parseInt(inp.dataset.idx);
                const bov = STATE.bovinos[idx];
                bov.kg_peso_vivo = parseFloat(inp.value) || 0;
                bov.kg_peso_gancho = (bov.kg_peso_vivo - bov.kg_peso_vivo * STATE.destare / 100) * STATE
                    .rendimiento / 100;
                bov.subtotal = STATE.precio_kg * bov.kg_peso_gancho;
                const tr = tbody.querySelector(`tr[data-idx="${idx}"]`);
                if (tr) {
                    const tdGancho = tr.querySelector('.bov-kg-gancho');
                    const tdSub = tr.querySelector('.bov-subtotal');
                    if (tdGancho) tdGancho.textContent = fmtNum(bov.kg_peso_gancho);
                    if (tdSub) tdSub.textContent = fmt(bov.subtotal);
                }
                recalcularTotales();
            });
        });

        // Subtotal editable directo (solo precio_fijo)
        tbody.querySelectorAll('.bov-subtotal-input').forEach(inp => {
            inp.addEventListener('input', () => {
                STATE.bovinos[parseInt(inp.dataset.idx)].subtotal = parseFloat(inp.value) || 0;
                recalcularTotales();
            });
        });

        // Observación
        tbody.querySelectorAll('.bov-observacion').forEach(inp => {
            inp.addEventListener('input', () => {
                STATE.bovinos[parseInt(inp.dataset.idx)].observacion = inp.value;
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
    }

    /* ═══════════════════════════════════════════════════════
       PAGOS
       - id_pago = '0'  → nuevo, se creará en el servidor
       - id_pago > 0    → existente, se actualizará
       - No se permite quitar pagos existentes; solo nuevos
    ═══════════════════════════════════════════════════════ */
    document.getElementById('btn-agregar-pago').addEventListener('click', () => {
        STATE.pagos.push({
            id_pago: '0',
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
                <p class="text-center text-muted small py-2 mb-0">
                    <i class="fa-regular fa-credit-card me-1 opacity-50"></i>Sin pagos registrados
                </p>`;
            return;
        }

        STATE.pagos.forEach((pago, idx) => {
            const esNuevo = pago.id_pago == '0';
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
                    ${esNuevo
                        ? `<button class="btn btn-sm btn-outline-danger pago-btn-quitar" data-idx="${idx}" title="Quitar">
                               <i class="fa-solid fa-xmark"></i>
                           </button>`
                        : `<span class="badge bg-secondary" title="Pago guardado">
                               <i class="fa-solid fa-lock"></i>
                           </span>`
                    }
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
       GUARDAR CAMBIOS  (PUT)
    ═══════════════════════════════════════════════════════ */
    document.getElementById('btn-guardar-venta').addEventListener('click', async () => {
        const idCliente = document.getElementById('select-cliente').value;
        const concepto = document.getElementById('input-concepto').value.trim();
        const fechaVenta = document.getElementById('fecha-venta').value;
        const esPorKg = STATE.tipo_precio === 'precio_kg';

        if (!idCliente) {
            showAlert('Selecciona un cliente para continuar.', 'warning');
            return;
        }
        if (!concepto) {
            showAlert('Ingresa el concepto de la venta.', 'warning');
            return;
        }
        if (!fechaVenta) {
            showAlert('Indica la fecha de venta.', 'warning');
            return;
        }
        if (!STATE.bovinos.length) {
            showAlert('Agrega al menos un bovino a la venta.', 'warning');
            return;
        }

        if (esPorKg && STATE.precio_kg <= 0) {
            showAlert('Ingresa el precio por kg antes de guardar.', 'warning');
            return;
        }

        for (const b of STATE.bovinos) {
            if (!esPorKg && b.subtotal <= 0) {
                showAlert(
                    `El bovino <b>${b.identificador}</b> tiene subtotal en 0. Ingresa un valor válido.`,
                    'warning');
                return;
            }
        }

        const total = STATE.bovinos.reduce((s, b) => s + b.subtotal, 0);

        const payload = {
            id_cliente: parseInt(idCliente),
            concepto,
            tipo_precio: STATE.tipo_precio,
            destare: esPorKg ? STATE.destare : 0,
            rendimiento: esPorKg ? STATE.rendimiento : 0,
            precio_kg: esPorKg ? STATE.precio_kg : 0,
            fecha_venta: fechaVenta,
            total,
            bovinos: STATE.bovinos.map(b => ({
                id_bovino: b.id_bovino,
                kg_peso_vivo: esPorKg ? b.kg_peso_vivo : 0,
                kg_peso_gancho: esPorKg ? b.kg_peso_gancho : 0,
                subtotal: b.subtotal,
                observacion: b.observacion || null,
            })),
            pagos: STATE.pagos.map(p => ({
                id_pago: p.id_pago,
                tipo_pago: p.tipo_pago,
                monto: p.monto,
                fecha_pago: p.fecha_pago,
            })),
        };

        const btn = document.getElementById('btn-guardar-venta');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando\u2026';

        try {
            const url = "{{ route('ventas.update', ':id') }}".replace(':id', VENTA_ACTUAL.id_venta);
            const r = await fetch(url, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken()
                },
                body: JSON.stringify(payload),
            });
            const data = await r.json();
            if (data.success) {
                showAlert(data.message ?? 'Venta actualizada correctamente.', 'success');
                window.open(
                    "{{ route('ventas.imprimir', ':id') }}".replace(':id', data.venta.id_venta),
                    '_blank', 'noopener,noreferrer'
                );
                setTimeout(() => window.location.href = "{{ route('ventas.index') }}", 1200);
            } else {
                showAlert(data.message ?? 'Error al actualizar la venta.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-2"></i>Guardar cambios';
            }
        } catch (e) {
            showAlert('Error de conexi\u00f3n. Intenta nuevamente.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-2"></i>Guardar cambios';
        }
    });

    /* ═══════════════════════════════════════════════════════
       ELIMINAR VENTA  (PATCH)
    ═══════════════════════════════════════════════════════ */
    document.getElementById('btn-confirmar-eliminar').addEventListener('click', async () => {
        const motivo = document.getElementById('venta-motivo-eliminacion').value.trim();
        if (!motivo) {
            showAlert('Debe proporcionar un motivo de eliminación.', 'warning');
            return;
        }

        const btn = document.getElementById('btn-confirmar-eliminar');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando\u2026';

        try {
            const url = "{{ route('ventas.delete', ':id') }}".replace(':id', VENTA_ACTUAL.id_venta);
            const r = await fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken()
                },
                body: JSON.stringify({
                    id_venta: VENTA_ACTUAL.id_venta,
                    motivo_eliminacion: motivo
                }),
            });
            const data = await r.json();
            if (data.success) {
                showAlert(data.message ?? 'Venta eliminada correctamente.', 'success');
                setTimeout(() => window.location.href = "{{ route('ventas.index') }}", 1500);
            } else {
                showAlert(data.message ?? 'Error al eliminar la venta.', 'danger');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-trash-can me-1"></i>Confirmar eliminación';
            }
        } catch (e) {
            showAlert('Error de conexi\u00f3n.', 'danger');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-trash-can me-1"></i>Confirmar eliminación';
        }
    });

    /* ═══════════════════════════════════════════════════════
       INIT
    ═══════════════════════════════════════════════════════ */
    (async () => {
        await cargarBovinos();
        await cargarClientes(VENTA_ACTUAL.id_cliente);
        renderThead();
        renderBovinos();
        renderPagos();
        recalcularTotales();
    })();
</script>
