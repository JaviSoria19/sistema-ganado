@extends('layouts.app')

@section('content')
    <h1 class="text-center text-info fw-bold"><i class="fa-solid fa-duotone fa-address-card"></i> {{ $headTitle }}</h1>

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
                <th>Cliente</th>
                <th>Celular</th>
                <th>C.I.</th>
                <th>Procedencia</th>
                <th>Estado</th>
                <th>F. Registro</th>
                <th>F. Actualización</th>
                <th>Modificado Por</th>
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
                        data: "nombreCliente",
                    },
                    {
                        data: "celular",
                    },
                    {
                        data: "cedulaIdentidad",
                    },
                    {
                        data: "procedencia",
                    },
                    {
                        data: "estado",
                        render: function(data, type, row) {
                            if (data == 1) {
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
                        data: "modificado.usuario",
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
                                data-id="${row.idCliente}" data-toggle="tooltip" title="Editar">
                            <i class="fa-duotone fa-solid fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-${row.estado == 1 ? 'danger' : 'success'} btn-sm btn-cambiar-estado" 
                                data-id="${row.idCliente}" data-estado="${row.estado}" data-nombre="${row.nombreCliente}" 
                                data-toggle="tooltip" title="${row.estado == 1 ? 'Deshabilitar' : 'Habilitar'}">
                            <i class="fa-duotone fa-solid fa-toggle-${row.estado == 1 ? 'off' : 'on'}"></i>
                        </button>
                    </div>
                `;
                        }
                    }
                ],
                @include('datatables.dataTablesGlobalProperties')
                @include('datatables.dataTablesLanguageProperty')
            }).buttons().container().appendTo('#dataTableExportButtonsContainer');



            $(document).on('click', '.btn-crear', function() {
                $('#formCreateOrEdit input[name="idCliente"]').val(0);
                $('#formCreateOrEdit input[name="nombreCliente"]').val('');
                $('#formCreateOrEdit input[name="celular"]').val('');
                $('#formCreateOrEdit input[name="cedulaIdentidad"]').val('');
                $('#formCreateOrEdit input[name="procedencia"]').val('');
                
                const titleElement = document.getElementById('modalCreateOrEdit_Title');
                titleElement.innerHTML = '<i class="fa-solid fa-duotone fa-plus"></i> CREAR CLIENTE';
                $('#modalCreateOrEdit').modal('show');
            });



            $(document).on('click', '.btn-editar', function() {
                const id = $(this).data('id');

                $.get("{{ route('clientes.index') . '/' }}" + id, function(cliente) {
                    $('#formCreateOrEdit input[name="idCliente"]').val(cliente.data.idCliente);
                    $('#formCreateOrEdit input[name="nombreCliente"]').val(cliente.data.nombreCliente);
                    $('#formCreateOrEdit input[name="celular"]').val(cliente.data.celular);
                    $('#formCreateOrEdit input[name="cedulaIdentidad"]').val(cliente.data.cedulaIdentidad);
                    $('#formCreateOrEdit input[name="procedencia"]').val(cliente.data.procedencia);

                    const titleElement = document.getElementById('modalCreateOrEdit_Title');
                    titleElement.innerHTML =
                        '<i class="fa-solid fa-duotone fa-edit"></i> EDITAR CLIENTE';
                    $('#modalCreateOrEdit').modal('show');
                });
            });


            $(document).on('click', '#btnGuardar', function() {
                const idCliente = $('#formCreateOrEdit input[name="idCliente"]').val();
                const url = idCliente == 0 ?
                    "{{ route('clientes.create') }}" // POST -> crear
                    :
                    "{{ route('clientes.index') . '/' }}" + idCliente; // PUT -> actualizar

                const type = idCliente == 0 ? 'POST' : 'PUT';

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
                        //console.error(xhr.responseText);
                        //console.error(JSON.parse(xhr.responseText));

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
                const nuevoEstado = estadoActual == 1 ? 0 : 1;
                const nombre = $(this).data('nombre');
                const accion = nuevoEstado == 1 ? 'habilitar' : 'deshabilitar';

                Swal.fire({
                    title: `¡ATENCIÓN!`,
                    text: `¿Estás seguro de ${accion} el/la cliente ${nombre}?`,
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
                                idCliente: id
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
