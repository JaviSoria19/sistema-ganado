<script>
    const unidadAnimal = {{ session('unidad_animal') }};

    function cargarEntoresSelect() {
        $.ajax({
            url: "{{ route('entores.listar') }}",
            type: "GET",
            dataType: "json",
            success: function(response) {
                let $select = $("#id_entore");
                $select.empty();
                $select.append('<option value="">Sin entore</option>');

                $.each(response.data, function(i, entore) {
                    if (entore.estado == 'inactivo')
                        return; // Omitir entores inactivos

                    $select.append(
                        `<option value="${entore.id_entore}">
                            ${entore.tipo_entore.charAt(0).toUpperCase() + entore.tipo_entore.slice(1)} - ${new Date(entore.fecha_inicio).toLocaleDateString()}
                        </option>`
                    );
                });
            }
        });
    }

    function cargarPadresSelect() {
        $.ajax({
            url: "{{ route('bovinos.listar') }}?genero=macho&estado=activo",
            type: "GET",
            dataType: "json",
            success: function(response) {
                let $select = $("#id_padre");
                $select.empty();
                $select.append('<option value="">Sin padre</option>');

                $.each(response.data, function(i, bovino) {
                    if (bovino.estado == 'inactivo' || bovino.estado == 'vendido')
                        return; // Omitir bovinos inactivos o vendidos
                    const carimbo = bovino.fecha_nacimiento ? `C${new Date(bovino.fecha_nacimiento).getFullYear()}` : '';
                    const potrero = bovino.potrero ? `${bovino.potrero.nombre}` : '';
                    $select.append(
                        `<option value="${bovino.id_bovino}">
                            ${carimbo} ${bovino.identificador} (${bovino.color_actual}) P:${potrero}
                        </option>`
                    );
                });
            }
        });
    }

    function cargarMadresSelect() {
        $.ajax({
            url: "{{ route('bovinos.listar') }}?genero=hembra&estado=activo",
            type: "GET",
            dataType: "json",
            success: function(response) {
                let $select = $("#id_madre");
                $select.empty();
                $select.append('<option value="">Sin madre</option>');

                $.each(response.data, function(i, bovino) {
                    if (bovino.estado == 'inactivo' || bovino.estado == 'vendido')
                        return; // Omitir bovinos inactivos o vendidos
                    const carimbo = bovino.fecha_nacimiento ? `C${new Date(bovino.fecha_nacimiento).getFullYear()}` : '';
                    const potrero = bovino.potrero ? `${bovino.potrero.nombre}` : '';
                    $select.append(
                        `<option value="${bovino.id_bovino}">
                            ${carimbo} ${bovino.identificador} (${bovino.color_actual}) P:${potrero}
                        </option>`
                    );
                });
            }
        });
    }

    $(document).ready(function() {
        cargarEntoresSelect();
        cargarPadresSelect();
        cargarMadresSelect();

        $('.select2').select2({
            width: '100%',
            language: "es",
            dropdownCssClass: "{{ session('tema_preferido') == 'dark' ? 'bg-dark' : '' }}",
            selectionCssClass: "{{ session('tema_preferido') == 'dark' ? 'bg-dark' : '' }}",
            dropdownParent: $('#modal-formulario'),
        });

        $("#form-filter-bovinos").on("click", function(e) {
            e.preventDefault();
            $("#dataTable").DataTable().ajax.reload();
        });

        $("#dataTable").DataTable({
            processing: true,
            ajax: {
                url: "{{ route('bovinos.listar') }}", // Ruta de Laravel
                type: "GET",
                data: function(d) {
                    d.id_potrero = $('#busqueda-potrero').val();
                    d.origen = $('#busqueda-origen').val();
                    d.genero = $('#busqueda-genero').val();
                    d.estado = $('#busqueda-estado').val();
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                error: function(xhr, error, thrown) {
                    console.error("Error al cargar los datos:", error);
                }
            },
            columns: [{
                    data: null,
                    render: function(data, type, row, meta) {
                        return meta.row + 1; // número de iteración
                    }
                },
                {
                    data: "identificador",
                },
                {
                    data: "potrero.nombre",
                },
                {
                    data: "entore",
                    render: function(data, type, row) {
                        return data ? `${data.fecha_inicio} ${data.tipo_entore}` : null;
                    }
                },
                {
                    data: "padre.identificador",
                },
                {
                    data: "madre.identificador",
                },
                {
                    data: "origen",
                    render: function(data, type, row) {
                        return data.charAt(0).toUpperCase() + data.slice(1);
                    }
                },
                {
                    // Carimbo generado con datatables según el año de nacimiento
                    data: "fecha_nacimiento",
                    render: function(data, type, row) {
                        return data ? new Date(data).getFullYear() : '-';
                    }
                },
                {
                    data: "genero",
                    render: function(data, type, row) {
                        if (data == 'macho') {
                            return '<span class="badge bg-primary">MACHO</span>';
                        } else if (data == 'hembra') {
                            return '<span class="badge bg-danger">HEMBRA</span>';
                        } else {
                            return '<span class="badge bg-secondary">DESCONOCIDO</span>';
                        }
                    }
                },
                {
                    // Categoría (se trabajará después)
                    data: null,
                    render: function(data, type, row) {
                        return '-';
                    }
                },
                {
                    data: "tiene_identificador_oreja",
                    render: function(data, type, row) {
                        return data ? '<span class="badge bg-success">SÍ</span>' :
                            '<span class="badge bg-danger">NO</span>';
                    }
                },
                {
                    data: "tiene_identificador_lomo",
                    render: function(data, type, row) {
                        return data ? '<span class="badge bg-success">SÍ</span>' :
                            '<span class="badge bg-danger">NO</span>';
                    }
                },
                {
                    data: "peso_nacimiento",
                },
                {
                    data: "peso_destete",
                },
                {
                    data: "peso_actual",
                },
                {
                    data: "peso_nacimiento",
                    render: function(data, type, row) {
                        return unidadAnimal ? (data / unidadAnimal).toFixed(2) : '-';
                    }
                },
                {
                    data: "peso_destete",
                    render: function(data, type, row) {
                        return unidadAnimal ? (data / unidadAnimal).toFixed(2) : '-';
                    }
                },
                {
                    data: "peso_actual",
                    render: function(data, type, row) {
                        return unidadAnimal ? (data / unidadAnimal).toFixed(2) : '-';
                    }
                },
                {
                    data: "color_nacimiento",
                },
                {
                    data: "color_actual",
                },
                {
                    data: "fecha_nacimiento",
                },
                {
                    // Factible para la venta (se trabajará después)
                    data: null,
                    render: function(data, type, row) {
                        return '-';
                    }
                },
                {
                    data: "fecha_salida",
                    render: function(data, type, row) {
                        return data ? new Date(data).toLocaleDateString() : '-';
                    }
                },
                {
                    data: "observaciones",
                    render: function(data, type, row) {
                        return data || '-';
                    }
                },
                {
                    data: "estado",
                    render: function(data, type, row) {
                        if (data == 'activo') {
                            return '<span class="badge bg-primary">ACTIVO</span>';
                        } else if (data == 'inactivo') {
                            return '<span class="badge bg-secondary">INACTIVO</span>';
                        } else if (data == 'vendido') {
                            return '<span class="badge bg-success">VENDIDO</span>';
                        } else {
                            return '<span class="badge bg-warning">DESCONOCIDO</span>';
                        }
                    }
                },
                {
                    data: "fecha_registro",
                    render: function(data, type, row) {
                        return data ? new Date(data).toLocaleString() : '-';
                    }
                },
                {
                    data: "fecha_actualizacion",
                    render: function(data, type, row) {
                        return data ? new Date(data).toLocaleString() : '-';
                    }
                },
                {
                    data: "fecha_eliminacion",
                    render: function(data, type, row) {
                        return data ? new Date(data).toLocaleString() : '-';
                    }
                },
                {
                    data: "creado.usuario",
                    render: function(data, type, row) {
                        return data || '-';
                    }
                },
                {
                    data: "modificado.usuario",
                    render: function(data, type, row) {
                        return data || '-';
                    }
                },
                {
                    data: "eliminado.usuario",
                    render: function(data, type, row) {
                        return data || '-';
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        const url_detalles = "{{ route('bovinos.detalles', ':id') }}"
                            .replace(':id', row.id_bovino);

                        return `
                            <div class="btn-group" role="group">
                                <a class="btn btn-info btn-sm" href="${url_detalles}" target="_blank" rel="noopener noreferrer"
                                    data-toggle="tooltip" title="Detalles">
                                    <i class="fa-duotone fa-solid fa-eye"></i>
                                </a>
                                <button type="button" class="btn btn-warning btn-sm btn-editar" 
                                        data-id="${row.id_bovino}" data-toggle="tooltip" title="Editar">
                                    <i class="fa-duotone fa-solid fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-${row.estado == 'activo' ? 'danger' : 'success'} btn-sm btn-cambiar-estado" 
                                        data-id="${row.id_bovino}" data-estado="${row.estado}" data-nombre="${row.identificador}" 
                                        data-toggle="tooltip" title="${row.estado == 'activo' ? 'Deshabilitar' : 'Habilitar'}">
                                    <i class="fa-duotone fa-solid fa-toggle-${row.estado == 'activo' ? 'off' : 'on'}"></i>
                                </button>
                            </div>`;
                    }
                }
            ],
            columnDefs: [{
                targets: [2, 3],
                width: '200px',
            }, ],
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

        $(document).on('click', '.btn-crear', function() {
            $('#form-crear-o-editar input[name="id_bovino"]').val(0);
            $('#form-crear-o-editar select[name="id_potrero"]').val('').trigger('change');
            $('#form-crear-o-editar select[name="origen"]').val('').trigger('change');
            $('#form-crear-o-editar input[name="identificador"]').val('');
            $('#form-crear-o-editar input[name="fecha_nacimiento"]').val('{{ date('Y-m-d') }}');
            $('#form-crear-o-editar select[name="genero"]').val('').trigger('change');
            $('#form-crear-o-editar select[name="id_entore"]').val('').trigger('change');
            $('#form-crear-o-editar select[name="id_padre"]').val('').trigger('change');
            $('#form-crear-o-editar select[name="id_madre"]').val('').trigger('change');
            $('#form-crear-o-editar input[name="peso_nacimiento"]').val('');
            $('#form-crear-o-editar input[name="peso_destete"]').val('');
            $('#form-crear-o-editar input[name="peso_actual"]').val('');
            $('#form-crear-o-editar input[name="color_nacimiento"]').val('');
            $('#form-crear-o-editar input[name="color_actual"]').val('');
            $('#form-crear-o-editar input[name="tiene_identificador_oreja"]').prop('checked', false);
            $('#form-crear-o-editar input[name="tiene_identificador_lomo"]').prop('checked', false);
            $('#form-crear-o-editar textarea[name="observaciones"]').val('');

            const titleElement = document.getElementById('modal-formulario-titulo');
            titleElement.innerHTML = '<i class="fa-solid fa-duotone fa-plus"></i> CREAR BOVINO';
            $('#modal-formulario').modal('show');
        });

        $(document).on('click', '.btn-editar', function() {
    const id = $(this).data('id');

    $.get("{{ route('bovinos.index') . '/' }}" + id, function(bovino) {
        const data = bovino.data;
        const carimbo = data.fecha_nacimiento ? `C${new Date(data.fecha_nacimiento).getFullYear()}` : '';
        const potrero = data.potrero ? `${data.potrero.nombre}` : '';
        $('#form-crear-o-editar input[name="id_bovino"]').val(data.id_bovino);
        $('#form-crear-o-editar select[name="id_potrero"]').val(data.id_potrero).trigger('change');
        $('#form-crear-o-editar select[name="origen"]').val(data.origen).trigger('change');
        $('#form-crear-o-editar input[name="identificador"]').val(data.identificador);
        $('#form-crear-o-editar input[name="fecha_nacimiento"]').val(data.fecha_nacimiento);
        $('#form-crear-o-editar select[name="genero"]').val(data.genero).trigger('change');
        $('#form-crear-o-editar select[name="id_entore"]').val(data.id_entore).trigger('change');
        $('#form-crear-o-editar select[name="id_padre"]').val(data.id_padre).trigger('change');
        $('#form-crear-o-editar select[name="id_madre"]').val(data.id_madre).trigger('change');
        $('#form-crear-o-editar input[name="peso_nacimiento"]').val(data.peso_nacimiento);
        $('#form-crear-o-editar input[name="peso_destete"]').val(data.peso_destete);
        $('#form-crear-o-editar input[name="peso_actual"]').val(data.peso_actual);
        $('#form-crear-o-editar input[name="color_nacimiento"]').val(data.color_nacimiento);
        $('#form-crear-o-editar input[name="color_actual"]').val(data.color_actual);
        $('#form-crear-o-editar input[name="tiene_identificador_oreja"]').prop('checked', !!data.tiene_identificador_oreja);
        $('#form-crear-o-editar input[name="tiene_identificador_lomo"]').prop('checked', !!data.tiene_identificador_lomo);
        $('#form-crear-o-editar textarea[name="observaciones"]').val(data.observaciones);

        const titleElement = document.getElementById('modal-formulario-titulo');
        titleElement.innerHTML = `<i class="fa-solid fa-edit"></i> EDITAR BOVINO: ${carimbo} ${data.identificador} (${data.color_actual}) P:${potrero}`;
        $('#modal-formulario').modal('show');
    });
});

        $(document).on('click', '#btn-guardar', function() {
            const btn = $(this);
            // Deshabilitar el botón para evitar múltiples clics y cambiar el texto
            btn.prop('disabled', true);
            btn.html('<i class="fa-solid fa-duotone fa-spinner fa-spin"></i> Guardando...');

            const id_bovino = $('#form-crear-o-editar input[name="id_bovino"]').val();
            const url = id_bovino == 0 ?
                "{{ route('bovinos.create') }}" // POST -> crear
                :
                "{{ route('bovinos.update', ':id') }}"
                .replace(':id', id_bovino); // PUT -> actualizar

            const type = id_bovino == 0 ? 'POST' : 'PUT';

            $.ajax({
                url: url,
                type: type,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: $('#form-crear-o-editar').serialize(),
                success: function(response) {
                    Swal.fire({
                        theme: localStorage.getItem('theme') || 'dark',
                        title: 'Éxito',
                        text: response.message,
                        icon: 'success'
                    });
                    $('#modal-formulario').modal('hide');
                    $('#dataTable').DataTable().ajax.reload();
                    btn.prop('disabled', false);
                    btn.html('<i class="fa-solid fa-duotone fa-save"></i> Guardar');
                },
                error: function(xhr) {
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
                        // Errores de validación (422)
                        htmlError = Object.values(respuesta.errors)
                            .flat()
                            .join("<br>");
                    } else if (respuesta.message) {
                        // Errores manuales (400, 403, 500...)
                        htmlError = respuesta.message;
                    } else {
                        htmlError = "Ocurrió un error inesperado.";
                    }
                    Swal.fire({
                        theme: localStorage.getItem('theme') || 'dark',
                        title: 'Error',
                        html: 'Ocurrió un error al intentar la acción: <br>' +
                            htmlError,
                        icon: 'error'
                    });
                    btn.prop('disabled', false);
                    btn.html('<i class="fa-solid fa-duotone fa-save"></i> Guardar');
                }
            });
        });

        $(document).on('click', '.btn-cambiar-estado', function() {
            const id = $(this).data('id');
            const estadoActual = $(this).data('estado');
            const estadoNuevo = estadoActual == 'activo' ? 'inactivo' : 'activo';
            const nombre = $(this).data('nombre');
            const accion = estadoNuevo == 'activo' ? 'desarchivar' : 'archivar';

            Swal.fire({
                theme: localStorage.getItem('theme') || 'dark',
                title: `¡ATENCIÓN!`,
                html: `¿Estás seguro de <b>${accion}</b> el bovino <span class="text-primary fw-bold">${nombre}</span>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `Sí, ${accion}`,
                cancelButtonText: 'No, cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('bovinos.index') . '/' }}" + id,
                        type: "PATCH",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            id_bovino: id
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
                                    message: "Error desconocido"
                                };
                            }

                            let htmlError = "";

                            if (respuesta.errors) {
                                // Errores de validación (422)
                                htmlError = Object.values(respuesta.errors)
                                    .flat()
                                    .join("<br>");
                            } else if (respuesta.message) {
                                // Errores manuales (400, 403, 500...)
                                htmlError = respuesta.message;
                            } else {
                                htmlError = "Ocurrió un error inesperado.";
                            }
                            Swal.fire({
                                theme: localStorage.getItem('theme') ||
                                    'dark',
                                title: 'Error',
                                text: `No se pudo ${accion} el bovino: ${htmlError}`,
                                icon: 'error'
                            });
                        }
                    });

                }
            });
        });

    });
</script>
