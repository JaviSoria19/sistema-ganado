<script>
    document.addEventListener('DOMContentLoaded', function() {

        const tableBody = document.querySelector('#table-recuentos-historicos tbody');
        const fileInput = document.getElementById('excel-file');
        const btnGuardar = document.getElementById('btn-guardar');

        // ─── Helpers ────────────────────────────────────────────────────────────────

        function crearFila(carimbo = '', identificador = '', estadoRecuento = '', fecha = '') {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="text-center align-middle fila-numero"></td>
                <td><input type="number" class="form-control form-control-sm" placeholder="Carimbo" value="${carimbo}"></td>
                <td><input type="text"   class="form-control form-control-sm" placeholder="Identificador" value="${identificador}"></td>
                <td><input type="number" step="1" class="form-control form-control-sm" placeholder="Estado recuento" value="${estadoRecuento}"></td>
                <td><input type="date"   class="form-control form-control-sm" value="${fecha}"></td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-danger btn-sm btn-eliminar-fila">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </td>
                <td class="text-center align-middle celda-observacion">—</td>
            `;
            tr.querySelector('.btn-eliminar-fila').addEventListener('click', () => {
                tr.remove();
                reindexar();
            });
            return tr;
        }

        function reindexar() {
            document.querySelectorAll('#table-recuentos-historicos tbody tr').forEach((tr, i) => {
                tr.querySelector('.fila-numero').textContent = i + 1;
            });
        }

        function limpiarErrores() {
            document.querySelectorAll('#table-recuentos-historicos tbody tr').forEach(tr => {
                tr.classList.remove('table-danger', 'table-warning');
                tr.querySelector('.celda-observacion').textContent = '—';
            });
        }

        // Convierte número de serie de Excel a fecha YYYY-MM-DD
        function excelSerialToDate(serial) {
            if (!serial) return '';
            if (typeof serial === 'string' && serial.match(/^\d{4}-\d{2}-\d{2}$/)) return serial;
            const date = new Date(Math.round((serial - 25569) * 86400 * 1000));
            return date.toISOString().split('T')[0];
        }

        // ─── Leer Excel ─────────────────────────────────────────────────────────────

        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                const workbook = XLSX.read(e.target.result, {
                    type: 'array'
                });
                const sheet = workbook.Sheets[workbook.SheetNames[0]];
                const rows = XLSX.utils.sheet_to_json(sheet, {
                    header: 1,
                    defval: ''
                });

                tableBody.innerHTML = '';

                // Saltar fila de encabezado si la primera celda no es numérica
                const start = isNaN(rows[0]?.[0]) ? 1 : 0;

                rows.slice(start).forEach(row => {
                    if (row.every(cell => cell === '')) return; // ignorar filas vacías

                    /* Se deja un espacio vacío antes de la primera coma para ignorar el primer elemento (#/Nro/Indice) */
                    const [, carimbo, identificador, estadoRecuento, fechaRaw] = row;

                    const fecha = excelSerialToDate(fechaRaw);
                    tableBody.appendChild(crearFila(carimbo, identificador, estadoRecuento, fecha));
                });

                reindexar();
            };
            reader.readAsArrayBuffer(file);
        });

        // ─── Evento agregar fila ───────────────────────────────────────────────

        document.getElementById('btn-agregar-fila').addEventListener('click', () => {
            tableBody.appendChild(crearFila());
            reindexar();
        });

        // ─── Enviar datos ────────────────────────────────────────────────────────────

        btnGuardar.addEventListener('click', function() {
            limpiarErrores();

            const filas = document.querySelectorAll('#table-recuentos-historicos tbody tr');

            if (filas.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Información',
                    text: 'No hay datos para importar.'
                });
                return;
            }

            const recuentos_historicos = [];
            let hayVacios = false;

            filas.forEach((tr, i) => {
                const inputs = tr.querySelectorAll('input');
                const carimbo = inputs[0].value.trim();
                const identificador = inputs[1].value.trim();
                const estado_recuento = inputs[2].value.trim();
                const fecha = inputs[3].value.trim();

                if (!carimbo || !identificador || !estado_recuento || !fecha) {
                    tr.classList.add('table-warning');
                    tr.querySelector('.celda-observacion').textContent = 'Campos incompletos.';
                    hayVacios = true;
                }

                recuentos_historicos.push({
                    carimbo,
                    identificador,
                    estado_recuento,
                    fecha
                });
            });

            if (hayVacios) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos incompletos',
                    text: 'Hay filas con campos vacíos (marcadas en amarillo). Por favor complétalas o elimínalas.'
                });
                return;
            }

            btnGuardar.disabled = true;
            btnGuardar.innerHTML =
                '<span class="spinner-border spinner-border-sm me-1"></span> Importando...';

            fetch('{{ route('recuentos-historicos.create') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        recuentos_historicos
                    }),
                })
                .then(res => res.json().then(data => ({
                    status: res.status,
                    data
                })))
                .then(({
                    status,
                    data
                }) => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: data.message
                        });
                        tableBody.innerHTML = '';
                        reindexar();
                        fileInput.value = '';
                    } else if (status === 422 && data.errores) {
                        // Resaltar filas con errores devueltos por el servidor
                        // El servidor devuelve "Fila N: ..." siendo N con base 1
                        data.errores.forEach(msg => {
                            const match = msg.match(/^Fila (\d+):/);
                            if (match) {
                                const idx = parseInt(match[1]) - 1;
                                const tr = filas[idx];
                                if (tr) {
                                    tr.classList.add('table-danger');
                                    // Extraer solo el texto después de "Fila N: "
                                    tr.querySelector('.celda-observacion').textContent = msg
                                        .replace(/^Fila \d+:\s*/, '');
                                }
                            }
                        });
                        Swal.fire({
                            icon: 'error',
                            title: 'Error en la importación',
                            text: data.message + ' - Revisa las filas marcadas en rojo.'
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
                    btnGuardar.innerHTML = 'Importar datos';
                });
        });

    });
</script>
