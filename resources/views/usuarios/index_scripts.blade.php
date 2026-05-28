<script>
    $(document).ready(function() {
        $("#dataTable").DataTable({
            processing: true,
            ajax: {
                url: "{{ route('usuarios.listar') }}", // Ruta de Laravel
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
                    data: "usuario",
                },
                {
                    data: "tema_preferido",
                    render: function(data, type, row) {
                        switch (data) {
                            case 'dark':
                                return 'Oscuro';
                            case 'light':
                                return 'Claro';
                            default:
                                return data;
                        }
                    }
                },
                {
                    data: "estado",
                    render: function(data, type, row) {
                        if (data == "activo") {
                            return '<span class="badge bg-success">Activo</span>';
                        } else {
                            return '<span class="badge bg-danger">Inactivo</span>';
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
                                data-id="${row.id_usuario}" data-toggle="tooltip" title="Editar">
                            <i class="fa-duotone fa-solid fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-${row.estado == "activo" ? 'danger' : 'success'} btn-sm btn-cambiar-estado" 
                                data-id="${row.id_usuario}" data-estado="${row.estado}" data-nombre="${row.usuario}" 
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
        }).buttons().container().appendTo('#dataTableExportButtonsContainer');



        $(document).on('click', '.btn-crear', function() {
            const id = 0;
            $('#formCreateOrEdit input[name="id_usuario"]').val(0);
            $('#formCreateOrEdit input[name="usuario"]').val('');
            $('#formCreateOrEdit select[name="tema_preferido"]').val('light')
                .trigger('change');
            $('#formCreateOrEdit input[name="contrasenha"]').val('');
            $('#formCreateOrEdit input[name="recontrasenha"]').val('');

            const titleElement = document.getElementById('modalCreateOrEdit_Title');
            titleElement.innerHTML = '<i class="fa-solid fa-duotone fa-plus"></i> CREAR USUARIO';
            $('#modalCreateOrEdit').modal('show');
        });



        $(document).on('click', '.btn-editar', function() {
            const id = $(this).data('id');

            $.get("{{ route('usuarios.index') . '/' }}" + id, function(usuario) {
                $('#formCreateOrEdit input[name="id_usuario"]').val(usuario.data.id_usuario);
                $('#formCreateOrEdit input[name="usuario"]').val(usuario.data
                    .usuario);
                $('#formCreateOrEdit select[name="tema_preferido"]').val(usuario.data
                        .tema_preferido)
                    .trigger('change');
                $('#formCreateOrEdit input[name="contrasenha"]').val(''); // opcional, vacío
                $('#formCreateOrEdit input[name="recontrasenha"]').val('');

                const titleElement = document.getElementById('modalCreateOrEdit_Title');
                titleElement.innerHTML =
                    '<i class="fa-solid fa-duotone fa-edit"></i> EDITAR USUARIO';
                $('#modalCreateOrEdit').modal('show');
            });
        });


        $(document).on('click', '#btnGuardar', function() {
            const id_usuario = $('#formCreateOrEdit input[name="id_usuario"]').val();
            const url = id_usuario == 0 ?
                "{{ route('usuarios.create') }}" // POST -> crear
                :
                "{{ route('usuarios.index') . '/' }}" + id_usuario; // PUT -> actualizar

            const type = id_usuario == 0 ? 'POST' : 'PUT';

            $.ajax({
                url: url,
                type: type,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: $('#formCreateOrEdit').serialize(),
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Éxito', response.message, 'success');
                        $('#modalCreateOrEdit').modal('hide');
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
                html: `¿Estás seguro de <b>${accion}</b> el usuario <b class="text-primary">${nombre}</b>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `Sí, ${accion}`,
                cancelButtonText: 'No, cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('usuarios.index') . '/' }}" + id,
                        type: "PATCH",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            id_usuario: id
                        },
                        success: function(response) {
                            Swal.fire('Actualizado', response.message, 'success');
                            $('#dataTable').DataTable().ajax.reload();
                        },
                        error: function() {
                            Swal.fire('Error', `No se pudo ${accion} el usuario`,
                                'error');
                        }
                    });

                }
            });
        });
    });

    $(document).ready(function() {
        document.querySelectorAll('.toggle-password').forEach(btn => {
            btn.addEventListener('click', function() {
                const input = document.getElementById(this.dataset.target);
                const type = input.type === 'password' ? 'text' : 'password';
                input.type = type;
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        });
    });
</script>
