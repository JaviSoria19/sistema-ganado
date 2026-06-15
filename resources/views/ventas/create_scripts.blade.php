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
        listaBovinosActivos: [], // cargados una vez al inicio
        clienteSeleccionado: null,
        // Campos de nivel venta
        tipo_precio: 'precio_fijo', // 'precio_fijo' | 'precio_kg'
        destare: 0,
        rendimiento: 0,
        precio_kg: 0,
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
            }
        });
    }

    function excelDateToJS(excelDate) {

        if (!excelDate) return '';

        if (typeof excelDate === 'string') {
            return excelDate.substring(0, 10);
        }

        const date = XLSX.SSF.parse_date_code(excelDate);

        return `${date.y}-${String(date.m).padStart(2,'0')}-${String(date.d).padStart(2,'0')}`;
    }

    function normalizarTexto(valor) {
        return String(valor ?? '')
            .trim()
            .toLowerCase();
    }

    /* ═══════════════════════════════════════════════════════
       TIPO DE PRECIO GLOBAL
       Cuando cambia, re-renderiza thead y todos los bovinos
    ═══════════════════════════════════════════════════════ */
    document.getElementById('select-tipo-precio').addEventListener('change', function() {
        STATE.tipo_precio = this.value;
        const esPorKg = STATE.tipo_precio === 'precio_kg';

        document.querySelectorAll('.campo-precio-kg').forEach(el => {
            el.classList.toggle('d-none', !esPorKg);
        });

        // Reinicia campos de precio en todos los bovinos al cambiar tipo
        STATE.bovinos.forEach(bov => {
            bov.kg_peso_gancho = 0;
            bov.subtotal = 0;
        });

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

    // Recalcula gancho y subtotal de todos los bovinos con los valores globales actuales
    function recalcularTodosLosGanchos() {
        if (STATE.tipo_precio !== 'precio_kg') return;
        STATE.bovinos.forEach(bov => {
            bov.kg_peso_gancho = (bov.kg_peso_vivo - bov.kg_peso_vivo * STATE.destare / 100) * STATE
                .rendimiento / 100;
            bov.subtotal = STATE.precio_kg * bov.kg_peso_gancho;
        });
        // Actualiza solo las celdas calculadas sin re-renderizar toda la tabla
        const tbody = document.getElementById('bovinos-tbody');
        STATE.bovinos.forEach((bov, idx) => {
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
        const thead = document.getElementById('bovinos-thead');
        thead.innerHTML = `
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
            actualizarSelectBovino();
        } catch (e) {
            console.error('Error cargando bovinos', e);
        }
    }

    function actualizarSelectBovino() {
        const idsEnTabla = STATE.bovinos.map(b => b.id_bovino);
        const disponibles = STATE.listaBovinosActivos.filter(b => !idsEnTabla.includes(b.id_bovino));
        document.getElementById('select-bovino').innerHTML = buildBovinoOptions(disponibles);
        // Refresh select2 si está inicializado
        if (typeof $ !== 'undefined') {
            $('#select-bovino').trigger('change.select2');
        }
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
            kg_peso_vivo: kgPesoVivo, // editable si precio_kg
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

        /* ── Eventos ── */

        // Kg vivo editable (solo precio_kg): recalcula gancho y subtotal de esa fila
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
                const idx = parseInt(inp.dataset.idx);
                STATE.bovinos[idx].subtotal = parseFloat(inp.value) || 0;
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
        const concepto = document.getElementById('input-concepto').value.trim();
        const fechaVenta = document.getElementById('fecha-venta').value;
        const esPorKg = STATE.tipo_precio === 'precio_kg';

        if (!idCliente) {
            showAlert('Selecciona un cliente para continuar.');
            return;
        }
        if (!concepto) {
            showAlert('Ingresa el concepto de la venta.');
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

        if (esPorKg && STATE.precio_kg <= 0) {
            showAlert('Ingresa el precio por kg antes de registrar la venta.');
            return;
        }

        for (const b of STATE.bovinos) {
            if (!esPorKg && b.subtotal <= 0) {
                showAlert(
                    `El bovino <b>${b.identificador}</b> tiene subtotal en 0. Ingresa un valor válido.`);
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
                window.open(
                    "{{ route('ventas.imprimir', ':id') }}".replace(':id', data.venta.id_venta),
                    '_blank', 'noopener,noreferrer'
                );
                setTimeout(() => window.location.href = "{{ route('ventas.index') }}", 1200);
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


    async function obtenerOCrearCliente(nombre, celular, estancia) {
        if (!nombre) return null;

        // 1. Buscar en el estado local
        let cliente = STATE.clientes.find(c =>
            normalizarTexto(c.nombre) === normalizarTexto(nombre)
        );

        if (cliente) {
            return cliente.id_cliente;
        }

        const url = "{{ route('clientes.create') }}";
        const type = 'POST';

        // 2. Envolver el AJAX en un bloque try/catch con await
        try {
            const response = await $.ajax({
                url: url,
                type: type,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    "nombre": nombre,
                    "celular": celular,
                    "estancia": estancia
                }
            });

            if (response.success) {
                Swal.fire('Éxito', response.message, 'success');
                console.log(response);

                await cargarClientes();

                return response.cliente.id_cliente;
            } else {
                Swal.fire('Error', response.message, 'error');
                return null;
            }

        } catch (xhr) {
            let respuesta = {};
            try {
                respuesta = JSON.parse(xhr.responseText);
            } catch (e) {
                respuesta = {
                    message: "Error desconocido"
                };
            }

            let htmlError = "";

            if (respuesta.errors) {
                htmlError = Object.values(respuesta.errors).flat().join("<br>");
            } else if (respuesta.message) {
                htmlError = respuesta.message;
            } else {
                htmlError = "Ocurrió un error inesperado.";
            }

            Swal.fire({
                theme: localStorage.getItem('theme') || 'dark',
                title: 'Error',
                html: 'Ocurrió un error al intentar la acción: <br>' + htmlError,
                icon: 'error'
            });

            return null;
        }
    }

    async function importarVentaExcel(file) {

        const buffer = await file.arrayBuffer();

        const workbook = XLSX.read(buffer, {
            type: 'array'
        });

        const sheet = workbook.Sheets[workbook.SheetNames[0]];

        const rows = XLSX.utils.sheet_to_json(sheet, {
            defval: null
        });

        if (!rows.length) {
            showAlert('El archivo no contiene datos.', 'warning');
            return;
        }

        const filaBase = rows[0];

        try {

            /* ======================================
               CLIENTE
            ====================================== */

            const idCliente = await obtenerOCrearCliente(
                filaBase['Cliente'],
                filaBase['Celular'],
                filaBase['Estancia']
            );

            $('#select-cliente')
                .val(idCliente)
                .trigger('change');

            /* ======================================
               DATOS VENTA
            ====================================== */

            document.getElementById('input-concepto').value =
                filaBase['Concepto'] ?? '';

            document.getElementById('fecha-venta').value =
                excelDateToJS(filaBase['Fecha de venta']);

            const tipoVenta =
                normalizarTexto(filaBase['Tipo de venta']);

            STATE.tipo_precio =
                tipoVenta.includes('kg') ?
                'precio_kg' :
                'precio_fijo';

            document.getElementById('select-tipo-precio').value =
                STATE.tipo_precio;

            document
                .getElementById('select-tipo-precio')
                .dispatchEvent(new Event('change'));

            STATE.destare =
                parseFloat(filaBase['Destare % (Precio por kg)']) || 0;

            STATE.rendimiento =
                parseFloat(filaBase['Rendimiento % (Precio por kg)']) || 0;

            STATE.precio_kg =
                parseFloat(filaBase['Precio por kg (Bs.)']) || 0;

            document.getElementById('input-destare').value =
                STATE.destare;

            document.getElementById('input-rendimiento').value =
                STATE.rendimiento;

            document.getElementById('input-precio-kg').value =
                STATE.precio_kg;

            /* ======================================
               LIMPIAR DATOS ACTUALES
            ====================================== */

            STATE.bovinos = [];
            STATE.pagos = [];

            /* ======================================
               BOVINOS
            ====================================== */

            rows.forEach(row => {

                const idBovino =
                    parseInt(row['id_bovino']);

                if (!idBovino || isNaN(idBovino)) {
                    return;
                }

                const bovinoSistema =
                    STATE.listaBovinosActivos.find(
                        b => b.id_bovino == idBovino
                    );

                if (!bovinoSistema) {
                    return;
                }

                STATE.bovinos.push({
                    id_bovino: bovinoSistema.id_bovino,
                    identificador: bovinoSistema.identificador,
                    genero: bovinoSistema.genero,
                    potrero: bovinoSistema.potrero?.nombre ?? '—',
                    carimbo: new Date(
                        bovinoSistema.fecha_nacimiento
                    ).getFullYear(),
                    peso_actual: parseFloat(bovinoSistema.peso_actual) || 0,

                    kg_peso_vivo: parseFloat(row['Peso vivo kg']) ||
                        parseFloat(bovinoSistema.peso_actual) ||
                        0,

                    kg_peso_gancho: parseFloat(row['Peso gancho']) || 0,

                    subtotal: parseFloat(row['Subtotal']) || 0,

                    observacion: row['Observación'] ||
                        row['Observacion'] ||
                        '',
                });
            });

            /* ======================================
               PAGOS
            ====================================== */

            rows.forEach(row => {

                if (!row['Tipo de pago']) {
                    return;
                }

                const monto =
                    parseFloat(row['Monto']) || 0;

                if (monto <= 0) {
                    return;
                }

                STATE.pagos.push({
                    tipo_pago: row['Tipo de pago'],
                    monto,
                    fecha_pago: excelDateToJS(row['Fecha pago'])
                });
            });

            actualizarSelectBovino();
            renderThead();
            renderBovinos();
            renderPagos();
            recalcularTotales();

            showAlert(
                `Importación completada. Se cargaron ${STATE.bovinos.length} bovinos.`,
                'success'
            );

        } catch (error) {

            console.error(error);

            showAlert(
                error.message ||
                'Error al importar el archivo.',
                'danger'
            );
        }
    }

    /* ═══════════════════════════════════════════════════════
       INIT
    ═══════════════════════════════════════════════════════ */

    document
        .getElementById('excel-file')
        .addEventListener('change', async function(e) {

            const file = e.target.files[0];

            if (!file) return;

            if (!file.name.match(/\.(xlsx|xls)$/i)) {

                showAlert(
                    'Seleccione un archivo Excel válido.',
                    'warning'
                );

                return;
            }

            await importarVentaExcel(file);
        });

    (async () => {
        await cargarBovinos();
        await cargarClientes();
        renderThead();
        recalcularTotales();
    })();
</script>
