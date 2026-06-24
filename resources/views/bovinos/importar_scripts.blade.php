<script>
    document.addEventListener('DOMContentLoaded', function () {

        const tableBody = document.querySelector('#table-bovinos-importar tbody');
        const fileInput = document.getElementById('excel-file');
        const btnGuardar = document.getElementById('btn-guardar');

        // ─── Constantes de dominio ───────────────────────────────────────────────────

        const ORIGENES = ['criollo', 'comprado', 'prestado'];
        const GENEROS  = ['macho', 'hembra'];

        // ─── Helpers generales ───────────────────────────────────────────────────────

        function excelSerialToDate(serial) {
            if (!serial) return '';
            if (typeof serial === 'string' && serial.match(/^\d{4}-\d{2}-\d{2}$/)) return serial;
            if (typeof serial === 'number') {
                const date = new Date(Math.round((serial - 25569) * 86400 * 1000));
                return date.toISOString().split('T')[0];
            }
            return String(serial);
        }

        function buildSelect(opciones, valorInicial) {
            const select = document.createElement('select');
            select.className = 'form-select form-select-sm';
            const optVacia = document.createElement('option');
            optVacia.value = '';
            optVacia.textContent = '— Seleccionar —';
            select.appendChild(optVacia);
            opciones.forEach(op => {
                const opt = document.createElement('option');
                opt.value = op;
                opt.textContent = op.charAt(0).toUpperCase() + op.slice(1);
                if (op === valorInicial) opt.selected = true;
                select.appendChild(opt);
            });
            return select;
        }

        function buildCheckbox(valor) {
            const div = document.createElement('div');
            div.className = 'd-flex justify-content-center';
            const cb = document.createElement('input');
            cb.type = 'checkbox';
            cb.className = 'form-check-input';
            cb.checked = valor === 1 || valor === '1' || valor === true;
            div.appendChild(cb);
            return div;
        }

        function crearTdInput(tipo, valor, placeholder, campo, opciones = {}) {
            const td = document.createElement('td');
            const input = document.createElement('input');
            input.type = tipo;
            input.className = 'form-control form-control-sm';
            input.value = valor ?? '';
            if (placeholder) input.placeholder = placeholder;
            if (campo) input.dataset.campo = campo;
            if (opciones.maxlength) input.maxLength = opciones.maxlength;
            if (opciones.step !== undefined) input.step = opciones.step;
            if (opciones.min !== undefined) input.min = opciones.min;
            if (opciones.max !== undefined) input.max = opciones.max;
            td.appendChild(input);
            return td;
        }

        function reindexar() {
            tableBody.querySelectorAll('tr').forEach((tr, i) => {
                tr.querySelector('.fila-numero').textContent = i + 1;
            });
        }

        function limpiarErrores() {
            tableBody.querySelectorAll('tr').forEach(tr => {
                tr.classList.remove('table-danger', 'table-warning');
                tr.querySelector('.celda-observacion').textContent = '—';
            });
        }

        // ─── Crear fila ──────────────────────────────────────────────────────────────

        function crearFila(datos = {}) {
            const tr = document.createElement('tr');

            // Número de fila
            const tdNum = document.createElement('td');
            tdNum.className = 'text-center align-middle fila-numero';
            tr.appendChild(tdNum);

            // Id. Potrero (obligatorio)
            tr.appendChild(crearTdInput('number', datos.id_potrero, 'Id. Potrero', 'id_potrero', { step: '1', min: '1' }));

            // Id. Entore (opcional)
            tr.appendChild(crearTdInput('number', datos.id_entore, 'Opcional', 'id_entore', { step: '1', min: '1' }));

            // Id. Padre (opcional)
            tr.appendChild(crearTdInput('number', datos.id_padre, 'Opcional', 'id_padre', { step: '1', min: '1' }));

            // Id. Madre (opcional)
            tr.appendChild(crearTdInput('number', datos.id_madre, 'Opcional', 'id_madre', { step: '1', min: '1' }));

            // Origen (select)
            const tdOrigen = document.createElement('td');
            const selectOrigen = buildSelect(ORIGENES, datos.origen || '');
            selectOrigen.dataset.campo = 'origen';
            tdOrigen.appendChild(selectOrigen);
            tr.appendChild(tdOrigen);

            // Identificador
            tr.appendChild(crearTdInput('text', datos.identificador || '', 'Identificador', 'identificador', { maxlength: 25 }));

            // Género (select)
            const tdGenero = document.createElement('td');
            const selectGenero = buildSelect(GENEROS, datos.genero || '');
            selectGenero.dataset.campo = 'genero';
            tdGenero.appendChild(selectGenero);
            tr.appendChild(tdGenero);

            // Id. Oreja (checkbox)
            const tdOreja = document.createElement('td');
            tdOreja.className = 'text-center align-middle';
            const cbOreja = buildCheckbox(datos.tiene_identificador_oreja ?? 0);
            cbOreja.querySelector('input').dataset.campo = 'tiene_identificador_oreja';
            tdOreja.appendChild(cbOreja);
            tr.appendChild(tdOreja);

            // Id. Lomo (checkbox)
            const tdLomo = document.createElement('td');
            tdLomo.className = 'text-center align-middle';
            const cbLomo = buildCheckbox(datos.tiene_identificador_lomo ?? 0);
            cbLomo.querySelector('input').dataset.campo = 'tiene_identificador_lomo';
            tdLomo.appendChild(cbLomo);
            tr.appendChild(tdLomo);

            // Fecha nacimiento
            tr.appendChild(crearTdInput('date', datos.fecha_nacimiento || '', '', 'fecha_nacimiento'));

            // Peso nacimiento
            tr.appendChild(crearTdInput('number', datos.peso_nacimiento, '0.00', 'peso_nacimiento', { step: '0.01', min: '0', max: '99.99' }));

            // Fecha destete
            tr.appendChild(crearTdInput('date', datos.fecha_destete || '', '', 'fecha_destete'));

            // Peso destete
            tr.appendChild(crearTdInput('number', datos.peso_destete, '0.00', 'peso_destete', { step: '0.01', min: '0', max: '999.99' }));

            // Peso actual
            tr.appendChild(crearTdInput('number', datos.peso_actual, '0.00', 'peso_actual', { step: '0.01', min: '0', max: '9999.99' }));

            // Color nacimiento
            tr.appendChild(crearTdInput('text', datos.color_nacimiento || '', 'Color nac.', 'color_nacimiento', { maxlength: 45 }));

            // Color actual
            tr.appendChild(crearTdInput('text', datos.color_actual || '', 'Color actual', 'color_actual', { maxlength: 45 }));

            // Estado corporal
            tr.appendChild(crearTdInput('number', datos.estado_corporal, '0-15', 'estado_corporal', { step: '1', min: '0', max: '15' }));

            // Selección
            tr.appendChild(crearTdInput('text', datos.seleccion || '', 'Selección', 'seleccion', { maxlength: 100 }));

            // Observaciones
            tr.appendChild(crearTdInput('text', datos.observaciones || '', 'Observaciones', 'observaciones', { maxlength: 250 }));

            // Botón eliminar
            const tdAcciones = document.createElement('td');
            tdAcciones.className = 'text-center align-middle';
            const btnEliminar = document.createElement('button');
            btnEliminar.type = 'button';
            btnEliminar.className = 'btn btn-danger btn-sm';
            btnEliminar.innerHTML = '<i class="fa-solid fa-trash"></i>';
            btnEliminar.addEventListener('click', () => {
                tr.remove();
                reindexar();
            });
            tdAcciones.appendChild(btnEliminar);
            tr.appendChild(tdAcciones);

            // Celda observación
            const tdObs = document.createElement('td');
            tdObs.className = 'align-middle celda-observacion';
            tdObs.style.minWidth = '140px';
            tdObs.textContent = '—';
            tr.appendChild(tdObs);

            return tr;
        }

        // ─── Leer Excel ──────────────────────────────────────────────────────────────

        // Columnas esperadas en el Excel (índice 0-based):
        // 0: Índice (ignorar), 1: Id. Potrero, 2: Id. Entore, 3: Id. Padre, 4: Id. Madre,
        // 5: Origen, 6: Identificador, 7: Género, 8: Id.Oreja, 9: Id.Lomo,
        // 10: F.Nacimiento, 11: Peso Nac., 12: F.Destete, 13: Peso Destete, 14: Peso Actual,
        // 15: Color Nac., 16: Color Actual, 17: E.Corporal, 18: Selección, 19: Observaciones

        fileInput.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function (e) {
                const workbook = XLSX.read(e.target.result, { type: 'array' });
                const sheet = workbook.Sheets[workbook.SheetNames[0]];
                const rows = XLSX.utils.sheet_to_json(sheet, { header: 1, defval: '' });

                tableBody.innerHTML = '';

                // Saltar fila de encabezado si la primera celda no es numérica
                const start = isNaN(rows[0]?.[0]) ? 1 : 0;

                rows.slice(start).forEach(row => {
                    if (row.every(cell => cell === '')) return; // ignorar filas vacías

                    const datos = {
                        id_potrero:                row[1] !== '' ? row[1] : '',
                        id_entore:                 row[2] !== '' ? row[2] : '',
                        id_padre:                  row[3] !== '' ? row[3] : '',
                        id_madre:                  row[4] !== '' ? row[4] : '',
                        origen:                    String(row[5] || '').toLowerCase(),
                        identificador:             String(row[6] || ''),
                        genero:                    String(row[7] || '').toLowerCase(),
                        tiene_identificador_oreja: row[8],
                        tiene_identificador_lomo:  row[9],
                        fecha_nacimiento:          excelSerialToDate(row[10]),
                        peso_nacimiento:           row[11] !== '' ? row[11] : '',
                        fecha_destete:             excelSerialToDate(row[12]),
                        peso_destete:              row[13] !== '' ? row[13] : '',
                        peso_actual:               row[14] !== '' ? row[14] : '',
                        color_nacimiento:          String(row[15] || ''),
                        color_actual:              String(row[16] || ''),
                        estado_corporal:           row[17] !== '' ? row[17] : '',
                        seleccion:                 String(row[18] || ''),
                        observaciones:             String(row[19] || ''),
                    };

                    tableBody.appendChild(crearFila(datos));
                });

                reindexar();
            };
            reader.readAsArrayBuffer(file);
        });

        // ─── Agregar fila vacía ──────────────────────────────────────────────────────

        document.getElementById('btn-agregar-fila').addEventListener('click', () => {
            tableBody.appendChild(crearFila());
            reindexar();
        });

        // ─── Enviar datos ────────────────────────────────────────────────────────────

        btnGuardar.addEventListener('click', function () {
            limpiarErrores();

            const filas = tableBody.querySelectorAll('tr');

            if (filas.length === 0) {
                Swal.fire({ icon: 'info', title: 'Información', text: 'No hay datos para importar.' });
                return;
            }

            const bovinos = [];
            let hayVacios = false;

            filas.forEach((tr) => {
                const getVal = campo => {
                    const el = tr.querySelector(`[data-campo="${campo}"]`);
                    return el ? el.value.trim() : '';
                };

                const id_potrero       = getVal('id_potrero');
                const origen           = getVal('origen');
                const identificador    = getVal('identificador');
                const genero           = getVal('genero');
                const fecha_nacimiento = getVal('fecha_nacimiento');
                const peso_nacimiento  = getVal('peso_nacimiento');
                const peso_actual      = getVal('peso_actual');
                const color_nacimiento = getVal('color_nacimiento');
                const color_actual     = getVal('color_actual');

                // Campos obligatorios
                const camposObligatorios = [id_potrero, origen, identificador, genero,
                    fecha_nacimiento, peso_nacimiento, peso_actual, color_nacimiento, color_actual];

                if (camposObligatorios.some(v => !v)) {
                    tr.classList.add('table-warning');
                    tr.querySelector('.celda-observacion').textContent = 'Campos obligatorios incompletos.';
                    hayVacios = true;
                }

                // Opcionales
                const id_entore        = getVal('id_entore')       || null;
                const id_padre         = getVal('id_padre')        || null;
                const id_madre         = getVal('id_madre')        || null;
                const fecha_destete    = getVal('fecha_destete')   || null;
                const peso_destete     = getVal('peso_destete')    || null;
                const estado_corporal  = getVal('estado_corporal') || null;
                const seleccion        = getVal('seleccion')       || null;
                const observaciones    = getVal('observaciones')   || null;

                const cbOreja = tr.querySelector('input[type="checkbox"][data-campo="tiene_identificador_oreja"]');
                const cbLomo  = tr.querySelector('input[type="checkbox"][data-campo="tiene_identificador_lomo"]');

                bovinos.push({
                    id_potrero: id_potrero ? parseInt(id_potrero) : null,
                    id_entore:  id_entore  ? parseInt(id_entore)  : null,
                    id_padre:   id_padre   ? parseInt(id_padre)   : null,
                    id_madre:   id_madre   ? parseInt(id_madre)   : null,
                    origen,
                    identificador,
                    genero,
                    tiene_identificador_oreja: cbOreja?.checked ? 1 : 0,
                    tiene_identificador_lomo:  cbLomo?.checked  ? 1 : 0,
                    fecha_nacimiento,
                    peso_nacimiento:  peso_nacimiento  !== '' ? parseFloat(peso_nacimiento)  : null,
                    fecha_destete,
                    peso_destete:     peso_destete     !== null ? parseFloat(peso_destete)   : null,
                    peso_actual:      peso_actual      !== '' ? parseFloat(peso_actual)       : null,
                    color_nacimiento,
                    color_actual,
                    estado_corporal:  estado_corporal  !== null ? parseInt(estado_corporal)   : null,
                    seleccion,
                    observaciones,
                });
            });

            if (hayVacios) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos incompletos',
                    text: 'Hay filas con campos obligatorios vacíos (marcadas en amarillo). Por favor complétalas o elimínalas.'
                });
                return;
            }

            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Importando...';

            fetch('{{ route('bovinos.import') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ bovinos }),
            })
            .then(res => res.json().then(data => ({ status: res.status, data })))
            .then(({ status, data }) => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Éxito', text: data.message });
                    tableBody.innerHTML = '';
                    reindexar();
                    fileInput.value = '';
                } else if (status === 422 && data.errors) {
                    // El controlador devuelve data.errors con claves "bovinos.N.campo"
                    // Agrupar mensajes por índice de fila
                    const mensajesPorFila = {};
                    Object.entries(data.errors).forEach(([clave, msgs]) => {
                        const match = clave.match(/^bovinos\.(\d+)\./);
                        if (match) {
                            const idx = parseInt(match[1]);
                            if (!mensajesPorFila[idx]) mensajesPorFila[idx] = [];
                            mensajesPorFila[idx].push(...msgs);
                        }
                    });

                    const filasArr = Array.from(filas);
                    Object.entries(mensajesPorFila).forEach(([idx, msgs]) => {
                        const tr = filasArr[parseInt(idx)];
                        if (tr) {
                            tr.classList.add('table-danger');
                            tr.querySelector('.celda-observacion').textContent = msgs.join(' | ');
                        }
                    });

                    Swal.fire({
                        icon: 'error',
                        title: 'Error en la importación',
                        text: (data.message || 'Hay errores en los datos.') + ' Revisa las filas marcadas en rojo.'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message ?? 'Error desconocido'
                    });
                }
            })
            .catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'Error de red al intentar importar.'
                });
            })
            .finally(() => {
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = '<i class="fa-solid fa-upload"></i> Importar datos';
            });
        });

    });
</script>