<script>
    function cargarBovinosSelect() {
        $.ajax({
            url: "{{ route('bovinos.listar') }}?estado=activo", // Solo cargar bovinos activos
            type: "GET",
            dataType: "json",
            success: function(response) {
                let $select = $("#id_bovino");
                $select.empty();
                $select.append('<option value="">Seleccione el bovino</option>');

                $.each(response.data, function(i, bovino) {
                    if (bovino.estado == 'inactivo' || bovino.estado == 'vendido')
                        return; // Omitir bovinos inactivos o vendidos
                    const carimbo = bovino.fecha_nacimiento ?
                        `C${new Date(bovino.fecha_nacimiento).getFullYear()}` : '';
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
        cargarBovinosSelect();

        $('.select2').select2({
            width: '100%',
            language: "es",
            dropdownCssClass: "{{ session('tema_preferido') == 'dark' ? 'bg-dark' : '' }}",
            selectionCssClass: "{{ session('tema_preferido') == 'dark' ? 'bg-dark' : '' }}",
            dropdownParent: $('#modal-formulario'),
        });

        $("#dataTable").DataTable({
            processing: true,
            ajax: {
                url: "{{ route('pesajes-historicos.listar') }}", // Ruta de Laravel
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
                    data: "bovino.fecha_nacimiento",
                    render: function(data, type, row) {
                        return data ? `${new Date(data).getFullYear()}` : '-';
                    }
                },
                {
                    data: "bovino.identificador",
                },
                {
                    data: "peso",
                },
                {
                    data: "fecha",
                },
                {
                    data: "estado",
                    render: function(data, type, row) {
                        if (data == "activo") {
                            return '<span class="badge bg-success">Activo</span>';
                        } else {
                            return '<span class="badge bg-secondary">Inactivo</span>';
                        }
                    }
                },
                {
                    data: "fecha_registro",
                    render: function(data, type, row) {
                        return new Date(data).toLocaleString();
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
                        return `
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-warning btn-sm btn-editar" 
                                data-id="${row.id_pesaje_historico}" data-toggle="tooltip" title="Editar">
                            <i class="fa-duotone fa-solid fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-${row.estado == "activo" ? 'danger' : 'success'} btn-sm btn-cambiar-estado" 
                                data-id="${row.id_pesaje_historico}" data-estado="${row.estado}" data-nombre="${row.bovino.identificador}" 
                                data-toggle="tooltip" title="${row.estado == "activo" ? 'Deshabilitar' : 'Habilitar'}">
                            <i class="fa-duotone fa-solid fa-toggle-${row.estado == "activo" ? 'off' : 'on'}"></i>
                        </button>
                    </div>
                `;
                    }
                }
            ],
            @include('components.datatables.datatables_global_properties')
            @include('components.datatables.datatables_language_property')
        }).buttons().container().appendTo('#dataTable-export-buttons-container');

        $(document).on('click', '.btn-editar', function() {
            const id = $(this).data('id');

            $.get("{{ route('pesajes-historicos.index') . '/' }}" + id, function(pesaje_historico) {
                $('#form-crear-o-editar input[name="id_pesaje_historico"]').val(pesaje_historico.data.id_pesaje_historico);
                $('#form-crear-o-editar select[name="id_bovino"]').val(pesaje_historico.data.id_bovino)
                    .trigger('change');
                $('#form-crear-o-editar input[name="peso"]').val(pesaje_historico.data.peso);
                $('#form-crear-o-editar input[name="fecha"]').val(pesaje_historico.data.fecha);

                const titleElement = document.getElementById('modal-formulario-titulo');
                titleElement.innerHTML =
                    `<i class="fa-solid fa-edit"></i> EDITAR PESAJE HISTÓRICO`;
                $('#modal-formulario').modal('show');
            });
        });


        $(document).on('click', '#btn-guardar', function() {
            const id_pesaje_historico = $('#form-crear-o-editar input[name="id_pesaje_historico"]').val();

            if (id_pesaje_historico === 0) {
                swal.fire('Error', 'No se ha cargado un pesaje histórico para editar.', 'error');
                return; // Evitar enviar el formulario si no se ha cargado un pesaje histórico para editar
            }

            const url = id_pesaje_historico == 0 ?
                "{{ route('pesajes-historicos.create') }}" // POST -> crear
                :
                "{{ route('pesajes-historicos.index') . '/' }}" +
                id_pesaje_historico; // PUT -> actualizar

            const type = id_pesaje_historico == 0 ? 'POST' : 'PUT';

            $.ajax({
                url: url,
                type: type,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: $('#form-crear-o-editar').serialize(),
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Éxito', response.message, 'success');
                        $('#modal-formulario').modal('hide');
                        $('#dataTable').DataTable().ajax.reload();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    const erroresConcatenados = Object.values(JSON.parse(xhr.responseText)
                            .errors)
                        .flatMap(errores => errores)
                        .join('<br>');

                    Swal.fire('Error', 'Ocurrió un error al intentar la acción: <br>' +
                        erroresConcatenados, 'error');
                }
            });
        });



        $(document).on('click', '.btn-cambiar-estado', function() {
            const id = $(this).data('id');
            const estadoActual = $(this).data('estado');
            const nuevoEstado = estadoActual == 'activo' ? 'inactivo' : 'activo';
            const nombre = $(this).data('nombre');
            const accion = nuevoEstado == 'activo' ? 'habilitar' : 'deshabilitar';

            Swal.fire({
                title: `¡ATENCIÓN!`,
                html: `¿Estás seguro de <b>${accion}</b> el pesaje histórico <b class="text-primary">${nombre}</b>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `Sí, ${accion}`,
                cancelButtonText: 'No, cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('pesajes-historicos.index') . '/' }}" + id,
                        type: "PATCH",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            id_pesaje_historico: id
                        },
                        success: function(response) {
                            Swal.fire('Actualizado', response.message, 'success');
                            $('#dataTable').DataTable().ajax.reload();
                        },
                        error: function() {
                            Swal.fire('Error',
                                `No se pudo ${accion} el pesaje histórico`,
                                'error');
                        }
                    });

                }
            });
        });
    });
</script>
