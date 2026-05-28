<script>
    const fechaHoy = new Date().toISOString().split('T')[0];

    // ─── Datos globales ───────────────────────────────────────────────────────
    let listaMachos = []; // bovinos macho cargados por AJAX
    let listaHembras = []; // bovinos hembra cargados por AJAX

    // ─── Helpers para construir filas dinámicas ───────────────────────────────
    function buildSelectOptions(lista, selectedId = null) {
        let html = '<option value="">— Seleccione —</option>';
        lista.forEach(b => {
            const sel = (b.id_bovino == selectedId) ? 'selected' : '';
            const fecha_nacimiento = new Date(b.fecha_nacimiento);
            const carimbo = fecha_nacimiento.getFullYear();
            const potrero = b.potrero ? `P: ${b.potrero.nombre}` : 'Sin potrero';
            html +=
                `<option value="${b.id_bovino}" ${sel}>C${carimbo} ${b.identificador} (${b.color_actual}) ${potrero}</option>`;
        });
        return html;
    }

    function agregarFilaMacho(id_macho = null) {
        const idx = $('#lista-machos .fila-macho').length;
        const options = buildSelectOptions(listaMachos, id_macho);
        const fila = `
            <div class="input-group mb-2 fila-macho">
                <select class="form-select" name="machos[${idx}][id_macho]" required>
                    ${options}
                </select>
                <button type="button" class="btn btn-outline-danger btn-quitar-macho">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>`;
        $('#lista-machos').append(fila);
    }

    function agregarFilaHembra(id_hembra = null) {
        const idx = $('#lista-hembras .fila-hembra').length;
        const options = buildSelectOptions(listaHembras, id_hembra);
        const fila = `
            <div class="input-group mb-2 fila-hembra">
                <select class="form-select" name="hembras[${idx}][id_hembra]" required>
                    ${options}
                </select>
                <button type="button" class="btn btn-outline-danger btn-quitar-hembra">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>`;
        $('#lista-hembras').append(fila);
    }

    // Renumera los name[] después de quitar una fila
    function renumerarFilas(contenedor, campo) {
        $(`${contenedor} select`).each(function(i) {
            $(this).attr('name', `${campo}[${i}][${campo === 'machos' ? 'id_macho' : 'id_hembra'}]`);
        });
    }

    // ─── Control de campos según tipo_entore ─────────────────────────────────
    function actualizarCamposPorTipo(tipo) {
        $('#campo-codigo-pajuela').hide();
        $('#campo-macho-principal').hide();
        $('#campo-machos-multitoro').hide();

        if (tipo === 'inseminacion') {
            $('#campo-codigo-pajuela').show();
        } else if (tipo === 'unitoro') {
            $('#campo-macho-principal').show();
        } else if (tipo === 'multitoro') {
            $('#campo-machos-multitoro').show();
        }
    }

    // ─── Carga de bovinos por AJAX ────────────────────────────────────────────
    function cargarBovinos() {
        const urlMachos = "{{ route('bovinos.listar') }}?genero=macho&estado=activo";
        const urlHembras = "{{ route('bovinos.listar') }}?genero=hembra&estado=activo";

        return Promise.all([
            $.get(urlMachos),
            $.get(urlHembras)
        ]).then(([resMachos, resHembras]) => {
            listaMachos = resMachos.data || [];
            listaHembras = resHembras.data || [];

            // Actualizar select de macho principal
            $('#id_macho').html(
                '<option value="">— Sin asignar —</option>' +
                buildSelectOptions(listaMachos)
            );
        });
    }

    // ─── Limpiar / resetear el formulario ────────────────────────────────────
    function resetearFormulario() {
        $('#form-crear-o-editar input[name="id_entore"]').val(0);
        $('#tipo_entore').val('').trigger('change');
        $('#fecha_inicio').val(fechaHoy);
        $('#fecha_fin').val('');
        $('#codigo_pajuela').val('');
        $('#id_macho').val('');
        $('#observaciones').val('');
        $('#lista-machos').empty();
        $('#lista-hembras').empty();
        // Agregar una fila de hembra vacía por defecto
        agregarFilaHembra();
    }

    // ─── Recolectar payload del formulario ───────────────────────────────────
    function recolectarPayload() {
        const tipo = $('#tipo_entore').val();

        const payload = {
            tipo_entore: tipo,
            fecha_inicio: $('#fecha_inicio').val(),
            fecha_fin: $('#fecha_fin').val() || null,
            codigo_pajuela: $('#codigo_pajuela').val() || null,
            id_macho: $('#id_macho').val() || null,
            observaciones: $('#observaciones').val() || null,
            hembras: [],
            machos: [],
        };

        // Hembras
        $('#lista-hembras .fila-hembra select').each(function(i) {
            const val = $(this).val();
            if (val) payload.hembras.push({
                id_hembra: val
            });
        });

        // Machos (multitoro)
        if (tipo === 'multitoro') {
            $('#lista-machos .fila-macho select').each(function(i) {
                const val = $(this).val();
                if (val) payload.machos.push({
                    id_macho: val
                });
            });
        }

        return payload;
    }

    // ─── DataTable ────────────────────────────────────────────────────────────
    $(document).ready(function() {

        cargarBovinos(); // pre-carga al abrir la página

        const table = $("#dataTable").DataTable({
                processing: true,
                ajax: {
                    url: "{{ route('entores.listar') }}",
                    type: "GET",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    error: function(xhr, error) {
                        console.error("Error al cargar datos:", error);
                    }
                },
                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    {
                        data: "tipo_entore",
                        render: function(data) {
                            const map = {
                                unitoro: '<span class="badge bg-primary">Unitoro</span>',
                                multitoro: '<span class="badge bg-warning text-dark">Multitoro</span>',
                                inseminacion: '<span class="badge bg-info text-dark">Inseminación</span>',
                            };
                            return map[data] || data;
                        }
                    },
                    {
                        data: "fecha_inicio",
                        render: function(data) {
                            return data || '-';
                        }
                    },
                    {
                        data: "fecha_fin",
                        render: function(data) {
                            return data || '-';
                        }
                    },
                    {
                        data: "codigo_pajuela",
                        render: function(data) {
                            return data ? `<span class="badge bg-info text-dark">${data}</span>` :
                                '-';
                        }
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            switch (row.tipo_entore) {
                                case 'unitoro':
                                    const fecha_nacimiento = new Date(row.macho.fecha_nacimiento);
                                    const carimbo = fecha_nacimiento.getFullYear();
                                    const potrero = row.macho.potrero ?
                                        `P: ${row.macho.potrero.nombre}` : 'Sin potrero';
                                    return row.macho ?
                                        `<span class="badge bg-primary">C${carimbo} ${row.macho.identificador} (${row.macho.color_actual}) ${potrero}</span>` :
                                        '-';
                                case 'multitoro':
                                    if (!row.machos || row.machos.length === 0) return '-';

                                    return row.machos.map((macho, index) => {
                                        const fecha_nacimiento = new Date(macho.fecha_nacimiento);
                                        const carimbo = fecha_nacimiento.getFullYear();
                                        const potrero = macho.potrero ? `P: ${macho.potrero.nombre}` :
                                            'Sin potrero';
                                        return `<span class="badge bg-warning text-dark">M${index + 1}: C${carimbo} ${macho.identificador} (${macho.color_actual}) ${potrero}</span>`;
                                    }
                                ).join("<br>");
                            default:
                            return '-';
                        }
                    }
                },
                {
                    data: "hembras",
                    render: function(data, type, row) {
                        if (!data || data.length === 0) {
                            return "-";
                        }

                        return data.map((hembra, index) => {
                            const fecha_nacimiento = new Date(hembra.fecha_nacimiento);
                            const carimbo = fecha_nacimiento.getFullYear();
                            const potrero = hembra.potrero ?
                                `P: ${hembra.potrero.nombre}` : 'Sin potrero';
                            return `<span class="badge bg-secondary">H${index + 1}: C${carimbo} ${hembra.identificador} (${hembra.color_actual}) ${potrero}</span>`;
                        }).join("<br>");
                    }
                },
                {
                    data: "observaciones",
                    render: function(data) {
                        return data || '-';
                    }
                },
                {
                    data: "estado",
                    render: function(data) {
                        if (data === 'activo')
                            return '<span class="badge bg-success">ACTIVO</span>';
                        if (data === 'inactivo')
                            return '<span class="badge bg-secondary">INACTIVO</span>';
                        return '<span class="badge bg-warning">DESCONOCIDO</span>';
                    }
                },
                {
                    data: "fecha_registro",
                    render: function(data) {
                        return data ? new Date(data).toLocaleString() : '-';
                    }
                },
                {
                    data: "fecha_actualizacion",
                    render: function(data) {
                        return data ? new Date(data).toLocaleString() : '-';
                    }
                },
                {
                    data: "fecha_eliminacion",
                    render: function(data) {
                        return data ? new Date(data).toLocaleString() : '-';
                    }
                },
                {
                    data: "creado.usuario",
                    render: function(data) {
                        return data || '-';
                    }
                },
                {
                    data: "modificado.usuario",
                    render: function(data) {
                        return data || '-';
                    }
                },
                {
                    data: "eliminado.usuario",
                    render: function(data) {
                        return data || '-';
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-warning btn-sm btn-editar"
                                        data-id="${row.id_entore}" data-toggle="tooltip" title="Editar">
                                    <i class="fa-duotone fa-solid fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-${row.estado === 'activo' ? 'danger' : 'success'} btn-sm btn-cambiar-estado"
                                        data-id="${row.id_entore}" data-estado="${row.estado}"
                                        data-toggle="tooltip" title="${row.estado === 'activo' ? 'Archivar' : 'Restaurar'}">
                                    <i class="fa-duotone fa-solid fa-toggle-${row.estado === 'activo' ? 'off' : 'on'}"></i>
                                </button>
                            </div>`;
                    }
                }
            ],
            columnDefs: [{
                    targets: [2, 3],
                    width: '125px',
                },
                {
                    targets: [4],
                    width: '200px',
                },
                {
                    targets: [5, 6, 7],
                    width: '350px',
                },
            ],
            responsive: false,
            lengthChange: true,
            autoWidth: false,
            scrollX: true,
            colReorder: true,
            order: [],
            pageLength: 10,
            dom: 'Blfrtip',
            buttons: [{
                    extend: 'copy',
                    className: 'btn btn-secondary'
                },
                {
                    extend: 'csv',
                    className: 'btn btn-success'
                },
                {
                    extend: 'excel',
                    className: 'btn btn-success'
                },
                {
                    extend: 'pdf',
                    className: 'btn btn-danger'
                },
                {
                    extend: 'colvis',
                    className: 'btn btn-info'
                },
                {
                    extend: 'searchBuilder',
                    className: 'btn btn-warning'
                },
            ],
            @include('components.datatables.datatables_language_property')
        }).buttons().container().appendTo('#dataTable-export-buttons-container');

    // ─── Tipo entore: mostrar/ocultar campos ─────────────────────────────
    $(document).on('change', '#tipo_entore', function() {
        actualizarCamposPorTipo($(this).val());
    });

    // ─── Agregar / quitar machos ──────────────────────────────────────────
    $(document).on('click', '#btn-agregar-macho', function() {
        agregarFilaMacho();
    }); $(document).on('click', '.btn-quitar-macho', function() {
        $(this).closest('.fila-macho').remove();
        renumerarFilas('#lista-machos', 'machos');
    });

    // ─── Agregar / quitar hembras ─────────────────────────────────────────
    $(document).on('click', '#btn-agregar-hembra', function() {
        agregarFilaHembra();
    }); $(document).on('click', '.btn-quitar-hembra', function() {
        if ($('#lista-hembras .fila-hembra').length === 1) {
            Swal.fire({
                theme: localStorage.getItem('theme') || 'dark',
                title: 'Atención',
                text: 'Debe haber al menos una hembra en el entore.',
                icon: 'warning'
            });
            return;
        }
        $(this).closest('.fila-hembra').remove();
        renumerarFilas('#lista-hembras', 'hembras');
    });

    // ─── Botón Crear ──────────────────────────────────────────────────────
    $(document).on('click', '.btn-crear', function() {
        cargarBovinos().then(() => {
            resetearFormulario();
            const titleElement = document.getElementById('modal-formulario-titulo');
            titleElement.innerHTML =
                '<i class="fa-solid fa-duotone fa-plus"></i> CREAR ENTORE';
            $('#modal-formulario').modal('show');
        });
    });

    // ─── Botón Editar ─────────────────────────────────────────────────────
    $(document).on('click', '.btn-editar', function() {
        const id = $(this).data('id');
        cargarBovinos().then(() => {
            $.get("{{ route('entores.mostrar', ':id') }}".replace(':id', id), function(
                res) {
                const e = res.data;

                $('#form-crear-o-editar input[name="id_entore"]').val(e.id_entore);
                $('#tipo_entore').val(e.tipo_entore).trigger('change');
                $('#fecha_inicio').val(e.fecha_inicio);
                $('#fecha_fin').val(e.fecha_fin ?? '');
                $('#codigo_pajuela').val(e.codigo_pajuela ?? '');
                $('#id_macho').val(e.id_macho ?? '');
                $('#observaciones').val(e.observaciones ?? '');

                // Limpiar listas
                $('#lista-machos').empty();
                $('#lista-hembras').empty();

                // Cargar machos (multitoro)
                if (e.machos && e.machos.length > 0) {
                    e.machos.forEach(m => agregarFilaMacho(m.id_bovino));
                }

                // Cargar hembras
                if (e.hembras && e.hembras.length > 0) {
                    e.hembras.forEach(h => agregarFilaHembra(h.id_bovino));
                } else {
                    agregarFilaHembra();
                }

                const titleElement = document.getElementById(
                    'modal-formulario-titulo');
                titleElement.innerHTML =
                    '<i class="fa-solid fa-duotone fa-edit"></i> EDITAR ENTORE';
                $('#modal-formulario').modal('show');
            });
        });
    });

    // ─── Botón Guardar ────────────────────────────────────────────────────
    $(document).on('click', '#btn-guardar', function() {
        const btn = $(this);
        const id_entore = $('#form-crear-o-editar input[name="id_entore"]').val();
        const url = id_entore == 0 ?
            "{{ route('entores.create') }}" :
            "{{ route('entores.update', ':id') }}".replace(':id', id_entore);
        const type = id_entore == 0 ? 'POST' : 'PUT';

        const payload = recolectarPayload();

        // Validación mínima en cliente
        if (!payload.tipo_entore) {
            Swal.fire({
                theme: localStorage.getItem('theme') || 'dark',
                title: 'Atención',
                text: 'Seleccione el tipo de entore.',
                icon: 'warning'
            });
            return;
        }
        if (payload.hembras.length === 0) {
            Swal.fire({
                theme: localStorage.getItem('theme') || 'dark',
                title: 'Atención',
                text: 'Debe agregar al menos una hembra.',
                icon: 'warning'
            });
            return;
        }
        if (payload.tipo_entore === 'multitoro' && payload.machos.length === 0) {
            Swal.fire({
                theme: localStorage.getItem('theme') || 'dark',
                title: 'Atención',
                text: 'Debe agregar al menos un macho para el tipo multitoro.',
                icon: 'warning'
            });
            return;
        }

        btn.prop('disabled', true).html(
            '<i class="fa-solid fa-duotone fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: url,
            type: type,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            contentType: 'application/json',
            data: JSON.stringify(payload),
            success: function(response) {
                Swal.fire({
                    theme: localStorage.getItem('theme') || 'dark',
                    title: 'Éxito',
                    text: response.message,
                    icon: 'success'
                });
                $('#modal-formulario').modal('hide');
                $('#dataTable').DataTable().ajax.reload();
                btn.prop('disabled', false).html(
                    '<i class="fa-solid fa-duotone fa-save"></i> Guardar');
            },
            error: function(xhr) {
                let respuesta = {};
                try {
                    respuesta = JSON.parse(xhr.responseText);
                } catch (e) {
                    respuesta = {
                        message: 'Error desconocido'
                    };
                }

                let htmlError = '';
                if (respuesta.errors) {
                    htmlError = Object.values(respuesta.errors).flat().join('<br>');
                } else if (respuesta.message) {
                    htmlError = respuesta.message;
                } else {
                    htmlError = 'Ocurrió un error inesperado.';
                }

                Swal.fire({
                    theme: localStorage.getItem('theme') || 'dark',
                    title: 'Error',
                    html: 'Ocurrió un error al intentar la acción:<br>' +
                        htmlError,
                    icon: 'error'
                });
                btn.prop('disabled', false).html(
                    '<i class="fa-solid fa-duotone fa-save"></i> Guardar');
            }
        });
    });

    // ─── Cambiar estado (archivar / restaurar) ────────────────────────────
    $(document).on('click', '.btn-cambiar-estado', function() {
        const id = $(this).data('id');
        const estadoActual = $(this).data('estado');
        const accion = estadoActual === 'activo' ? 'archivar' : 'restaurar';

        Swal.fire({
            theme: localStorage.getItem('theme') || 'dark',
            title: '¡ATENCIÓN!',
            html: `¿Estás seguro de <b>${accion}</b> este entore?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: `Sí, ${accion}`,
            cancelButtonText: 'No, cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('entores.delete', ':id') }}".replace(':id', id),
                    type: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        id_entore: id
                    },
                    success: function(response) {
                        Swal.fire({
                            theme: localStorage.getItem('theme') ||
                                'dark',
                            title: 'Actualizado',
                            text: response.message,
                            icon: 'success'
                        });
                        $('#dataTable').DataTable().ajax.reload();
                    },
                    error: function(xhr) {
                        let respuesta = {};
                        try {
                            respuesta = JSON.parse(xhr.responseText);
                        } catch (e) {
                            respuesta = {
                                message: 'Error desconocido'
                            };
                        }
                        Swal.fire({
                            theme: localStorage.getItem('theme') ||
                                'dark',
                            title: 'Error',
                            text: `No se pudo ${accion} el entore: ` + (
                                respuesta.message || ''),
                            icon: 'error'
                        });
                    }
                });
            }
        });
    });

    });
</script>
