@extends('layouts.app')

@section('content')
    <h1 class="text-center text-info fw-bold"><i class="fa-solid fa-duotone fa-industry-windows"></i> {{ $head_title }}</h1>

    <button type="button" class="btn btn-success mb-3 btn-crear" data-bs-toggle="modal" data-bs-target="#modalCreateOrEdit">
        <i class="fa-solid fa-duotone fa-plus"></i> Crear marca</button>

    <h2 class="text-info fw-bold">Lista de marcas</h2>

    <div class="card p-3 mb-3">
        <p>Seleccione una opción para <i class="fa-solid fa-duotone fa-file-export"></i> exportar o <i
                class="fa-solid fa-duotone fa-filter"></i> filtrar la tabla:</p>
        <div id="dataTableExportButtonsContainer"></div>
    </div>

    <table class="table table-bordered table-striped" id="dataTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Marca</th>
                <th>Bono (%)</th>
                <th>Estado</th>
                <th>F. Registro</th>
                <th>F. Actualización</th>
                <th>Modificado Por</th>
                <th>Acciones</th>
            </tr>
        </thead>
    </table>

    <div class="mb-3"></div>

    <!-- Modal para crear y editar marcas -->
    <div class="modal fade" id="modalCreateOrEdit" tabindex="-1" aria-labelledby="modalCreateOrEdit_Title"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalCreateOrEdit_Title"><i class="fa-solid fa-duotone fa-plus"></i>
                        CREAR MARCA</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formCreateOrEdit">
                        <!-- input de idMarca en caso de editar -->
                        <input type="hidden" name="idMarca" value="0">

                        <div class="mb-3">
                            <label for="nombreMarca" class="form-label">Nombre de marca <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombreMarca" name="nombreMarca" required>
                        </div>

                        <div class="mb-3">
                            <label for="bonoMarcaPorcentaje" class="form-label">Bono (%) <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="bonoMarcaPorcentaje" name="bonoMarcaPorcentaje"
                                required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i
                            class="fa-solid fa-duotone fa-close"></i>Cerrar</button>
                    <button type="button" id="btnGuardar" class="btn btn-primary"><i
                            class="fa-solid fa-duotone fa-save"></i>
                        Guardar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $("#dataTable").DataTable({
                processing: true,
                ajax: {
                    url: "{{ route('marcas.listar') }}", // Ruta de Laravel
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
                        data: "nombreMarca",
                    },
                    {
                        data: "bonoMarcaPorcentaje",
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
                                data-id="${row.idMarca}" data-toggle="tooltip" title="Editar">
                            <i class="fa-duotone fa-solid fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-${row.estado == 1 ? 'danger' : 'success'} btn-sm btn-cambiar-estado" 
                                data-id="${row.idMarca}" data-estado="${row.estado}" data-nombre="${row.nombreMarca}" 
                                data-toggle="tooltip" title="${row.estado == 1 ? 'Deshabilitar' : 'Habilitar'}">
                            <i class="fa-duotone fa-solid fa-toggle-${row.estado == 1 ? 'off' : 'on'}"></i>
                        </button>
                    </div>
                `;
                        }
                    }
                ],
                @include('datatables.datatables_global_properties')
                @include('datatables.datatables_language_property')
            }).buttons().container().appendTo('#dataTableExportButtonsContainer');



            $(document).on('click', '.btn-crear', function() {
                $('#formCreateOrEdit input[name="idMarca"]').val(0);
                $('#formCreateOrEdit input[name="nombreMarca"]').val('');
                $('#formCreateOrEdit input[name="bonoMarcaPorcentaje"]').val('0');

                const titleElement = document.getElementById('modalCreateOrEdit_Title');
                titleElement.innerHTML = '<i class="fa-solid fa-duotone fa-plus"></i> CREAR MARCA';
                $('#modalCreateOrEdit').modal('show');
            });



            $(document).on('click', '.btn-editar', function() {
                const id = $(this).data('id');

                $.get("{{ route('marcas.index') . '/' }}" + id, function(marca) {
                    $('#formCreateOrEdit input[name="idMarca"]').val(marca.data.idMarca);
                    $('#formCreateOrEdit input[name="nombreMarca"]').val(marca.data.nombreMarca);
                    $('#formCreateOrEdit input[name="bonoMarcaPorcentaje"]').val(marca.data
                        .bonoMarcaPorcentaje);
                    const titleElement = document.getElementById('modalCreateOrEdit_Title');
                    titleElement.innerHTML =
                        '<i class="fa-solid fa-duotone fa-edit"></i> EDITAR MARCA';
                    $('#modalCreateOrEdit').modal('show');
                });
            });


            $(document).on('click', '#btnGuardar', function() {
                const idMarca = $('#formCreateOrEdit input[name="idMarca"]').val();
                const url = idMarca == 0 ?
                    "{{ route('marcas.create') }}" // POST -> crear
                    :
                    "{{ route('marcas.index') . '/' }}" + idMarca; // PUT -> actualizar

                const type = idMarca == 0 ? 'POST' : 'PUT';

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
                    text: `¿Estás seguro de ${accion} la marca ${nombre}?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: `Sí, ${accion}`,
                    cancelButtonText: 'No, cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('marcas.index') . '/' }}" + id,
                            type: "PATCH",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                idMarca: id
                            },
                            success: function(response) {
                                Swal.fire('Actualizado', response.message, 'success');
                                $('#dataTable').DataTable().ajax.reload();
                            },
                            error: function(xhr) {
                                console.error(xhr.responseText);
                                Swal.fire('Error', `No se pudo ${accion} la marca`,
                                    'error');
                            }
                        });

                    }
                });
            });
        });
    </script>
@endsection
