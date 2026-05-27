@extends('layouts.app')

@section('content')
    <h1 class="text-center text-info fw-bold"><i class="fa-solid fa-duotone fa-address-card"></i> {{ $head_title }}</h1>

    <button type="button" class="btn btn-success mb-3 btn-crear" data-bs-toggle="modal" data-bs-target="#modalCreateOrEdit">
        <i class="fa-solid fa-duotone fa-plus"></i> Crear cliente</button>

    <h2 class="text-info fw-bold">Lista de clientes</h2>

    <div class="card p-3 mb-3">
        <p>Seleccione una opción para <i class="fa-solid fa-duotone fa-file-export"></i> exportar o <i
                class="fa-solid fa-duotone fa-filter"></i> filtrar la tabla:</p>
        <div id="dataTableExportButtonsContainer"></div>
    </div>

    <table class="table table-bordered table-striped" id="dataTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Celular</th>
                <th>Estancia</th>

                <th>Estado</th>
                <th>F. Registro</th>
                <th>F. Actualización</th>
                <th>F. Eliminación</th>
                <th>Creado por</th>
                <th>Modificado por</th>
                <th>Eliminado por</th>
                <th>Acciones</th>
            </tr>
        </thead>
    </table>

    <div class="mb-3"></div>

    @include('clientes.modal')
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $("#dataTable").DataTable({
                processing: true,
                ajax: {
                    url: "{{ route('clientes.listar') }}", // Ruta de Laravel
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
                        data: "celular",
                    },
                    {
                        data: "estancia",
                    },
                    {
                        data: "estado",
                        render: function(data, type, row) {
                            if (data == 'activo') {
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
                            return new Date(data).toLocaleString();
                        }
                    },
                    {
                        data: "fecha_eliminacion",
                        render: function(data, type, row) {
                            return new Date(data).toLocaleString();
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
                                data-id="${row.id_cliente}" data-toggle="tooltip" title="Editar">
                            <i class="fa-duotone fa-solid fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-${row.estado == 'activo' ? 'danger' : 'success'} btn-sm btn-cambiar-estado" 
                                data-id="${row.id_cliente}" data-estado="${row.estado}" data-nombre="${row.nombre}" 
                                data-toggle="tooltip" title="${row.estado == 'activo' ? 'Deshabilitar' : 'Habilitar'}">
                            <i class="fa-duotone fa-solid fa-toggle-${row.estado == 'activo' ? 'off' : 'on'}"></i>
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
                $('#formCreateOrEdit input[name="id_cliente"]').val(0);
                $('#formCreateOrEdit input[name="nombre"]').val('');
                $('#formCreateOrEdit input[name="celular"]').val('');
                $('#formCreateOrEdit input[name="estancia"]').val('');

                const titleElement = document.getElementById('modalCreateOrEdit_Title');
                titleElement.innerHTML = '<i class="fa-solid fa-duotone fa-plus"></i> CREAR CLIENTE';
                $('#modalCreateOrEdit').modal('show');
            });



            $(document).on('click', '.btn-editar', function() {
                const id = $(this).data('id');

                $.get("{{ route('clientes.index') . '/' }}" + id, function(cliente) {
                    $('#formCreateOrEdit input[name="id_cliente"]').val(cliente.data.id_cliente);
                    $('#formCreateOrEdit input[name="nombre"]').val(cliente.data.nombre);
                    $('#formCreateOrEdit input[name="celular"]').val(cliente.data.celular);
                    $('#formCreateOrEdit input[name="estancia"]').val(cliente.data.estancia);

                    const titleElement = document.getElementById('modalCreateOrEdit_Title');
                    titleElement.innerHTML =
                        '<i class="fa-solid fa-duotone fa-edit"></i> EDITAR CLIENTE';
                    $('#modalCreateOrEdit').modal('show');
                });
            });


            $(document).on('click', '#btnGuardar', function() {
                const id_cliente = $('#formCreateOrEdit input[name="id_cliente"]').val();
                const url = id_cliente == 0 ?
                    "{{ route('clientes.create') }}" // POST -> crear
                    :
                    "{{ route('clientes.index') . '/' }}" + id_cliente; // PUT -> actualizar

                const type = id_cliente == 0 ? 'POST' : 'PUT';

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
                    html: `¿Estás seguro de <b>${accion}</b> el/la cliente <b class="text-primary">${nombre}</b>?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: `Sí, ${accion}`,
                    cancelButtonText: 'No, cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('clientes.index') . '/' }}" + id,
                            type: "PATCH",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                id_cliente: id
                            },
                            success: function(response) {
                                Swal.fire('Actualizado', response.message, 'success');
                                $('#dataTable').DataTable().ajax.reload();
                            },
                            error: function(xhr) {
                                console.error(xhr.responseText);
                                Swal.fire('Error', `No se pudo ${accion} el/la cliente`,
                                    'error');
                            }
                        });

                    }
                });
            });
        });
    </script>
@endsection
