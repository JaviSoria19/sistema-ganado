<script>
    $(document).ready(function() {
        $("#dataTable").DataTable({
            processing: true,
            ajax: {
                url: "{{ route('potreros.listar') }}", // Ruta de Laravel
                type: "GET",
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
                    data: "nombre",
                },
                {
                    data: "ubicacion",
                },
                {
                    data: "superficie",
                },
                {
                    data: "tipo_pasto",
                },
                {
                    data: "estado_potrero",
                },
                {
                    data: "disponibilidad_agua",
                },
                {
                    data: "capacidad_carga_actual",
                },
                {
                    data: "capacidades_historicas_count",
                },
                {
                    data: "bovinos_count",
                },
                {
                    data: "estado",
                    render: function(data, type, row) {
                        if (data == 'activo') {
                            return '<span class="badge bg-success">ACTIVO</span>';
                        } else if (data == 'inactivo') {
                            return '<span class="badge bg-secondary">INACTIVO</span>';
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
                        const url_detalles = "{{ route('potreros.detalles', ':id') }}"
                            .replace(':id', row.id_potrero);

                        return `
                            <div class="btn-group" role="group">
                                <a class="btn btn-info btn-sm" href="${url_detalles}" target="_blank" rel="noopener noreferrer"
                                    data-toggle="tooltip" title="Detalles">
                                    <i class="fa-duotone fa-solid fa-eye"></i>
                                </a>
                                <button type="button" class="btn btn-warning btn-sm btn-editar" 
                                        data-id="${row.id_potrero}" data-toggle="tooltip" title="Editar">
                                    <i class="fa-duotone fa-solid fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-${row.estado == 'activo' ? 'danger' : 'success'} btn-sm btn-cambiar-estado" 
                                        data-id="${row.id_potrero}" data-estado="${row.estado}" data-nombre="${row.nombre}" 
                                        data-toggle="tooltip" title="${row.estado == 'activo' ? 'Deshabilitar' : 'Habilitar'}">
                                    <i class="fa-duotone fa-solid fa-toggle-${row.estado == 'activo' ? 'off' : 'on'}"></i>
                                </button>
                            </div>`;
                    }
                }
            ],
            @include('components.datatables.datatables_global_properties')
            @include('components.datatables.datatables_language_property')
        }).buttons().container().appendTo('#dataTable-export-buttons-container');

        $(document).on('click', '.btn-crear', function() {
            $('#form-crear-o-editar input[name="id_potrero"]').val(0);
            $('#form-crear-o-editar input[name="nombre"]').val('');
            $('#form-crear-o-editar input[name="ubicacion"]').val('');
            $('#form-crear-o-editar input[name="superficie"]').val('');
            $('#form-crear-o-editar select[name="tipo_pasto"]').val('');
            $('#form-crear-o-editar select[name="estado_potrero"]').val('').trigger('change');
            $('#form-crear-o-editar select[name="disponibilidad_agua"]').val('').trigger('change');
            $('#form-crear-o-editar input[name="capacidad_carga_actual"]').val('').trigger('change');

            const titleElement = document.getElementById('modal-formulario-titulo');
            titleElement.innerHTML = '<i class="fa-solid fa-duotone fa-plus"></i> CREAR POTRERO';
            $('#modal-formulario').modal('show');
        });



        $(document).on('click', '.btn-editar', function() {
            const id = $(this).data('id');

            $.get("{{ route('potreros.index') . '/' }}" + id, function(potrero) {
                $('#form-crear-o-editar input[name="id_potrero"]').val(potrero.data.id_potrero);
                $('#form-crear-o-editar input[name="nombre"]').val(potrero.data.nombre);
                $('#form-crear-o-editar input[name="ubicacion"]').val(potrero.data.ubicacion);
                $('#form-crear-o-editar input[name="superficie"]').val(potrero.data.superficie);
                $('#form-crear-o-editar select[name="tipo_pasto"]').val(potrero.data
                .tipo_pasto);
                $('#form-crear-o-editar select[name="estado_potrero"]').val(potrero.data
                    .estado_potrero).trigger(
                    'change');
                $('#form-crear-o-editar select[name="disponibilidad_agua"]').val(potrero.data
                    .disponibilidad_agua).trigger(
                    'change');
                $('#form-crear-o-editar input[name="capacidad_carga_actual"]').val(potrero.data
                    .capacidad_carga_actual).trigger(
                    'change');

                const titleElement = document.getElementById('modal-formulario-titulo');
                titleElement.innerHTML =
                    '<i class="fa-solid fa-duotone fa-edit"></i> EDITAR POTRERO';
                $('#modal-formulario').modal('show');
            });
        });


        $(document).on('click', '#btn-guardar', function() {
            const btn = $(this);
            // Deshabilitar el botón para evitar múltiples clics y cambiar el texto
            btn.prop('disabled', true);
            btn.html('<i class="fa-solid fa-duotone fa-spinner fa-spin"></i> Guardando...');

            const id_potrero = $('#form-crear-o-editar input[name="id_potrero"]').val();
            const url = id_potrero == 0 ?
                "{{ route('potreros.create') }}" // POST -> crear
                :
                "{{ route('potreros.update', ':id') }}"
                .replace(':id', id_potrero); // PUT -> actualizar

            const type = id_potrero == 0 ? 'POST' : 'PUT';

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
                html: `¿Estás seguro de <b>${accion}</b> el potrero <span class="text-primary fw-bold">${nombre}</span>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `Sí, ${accion}`,
                cancelButtonText: 'No, cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('potreros.index') . '/' }}" + id,
                        type: "PATCH",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            id_potrero: id
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
                                text: `No se pudo ${accion} el potrero: ${htmlError}`,
                                icon: 'error'
                            });
                        }
                    });

                }
            });
        });

    });
</script>
