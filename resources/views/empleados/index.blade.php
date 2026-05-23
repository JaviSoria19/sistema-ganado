@extends('layouts.app')

@section('content')
    <h1 class="text-center text-info fw-bold"><i class="fa-solid fa-duotone fa-user-tag"></i> {{ $headTitle }}</h1>

    <button type="button" class="btn btn-success mb-3 btn-crear" data-bs-toggle="modal" data-bs-target="#modalCreateOrEdit">
        <i class="fa-solid fa-duotone fa-plus"></i> Crear empleado</button>

    <h2 class="text-info fw-bold">Lista de empleados</h2>

    <p>Nota: para que un empleado acceda al manejo del sistema se le debe crear un <b>usuario</b>, si desea hacerlo haga
        clic <a href="{{ route('usuarios.index') }}">aquí.</a></p>

    <div class="card p-3 mb-3">
        <p>Seleccione una opción para <i class="fa-solid fa-duotone fa-file-export"></i> exportar o <i
                class="fa-solid fa-duotone fa-filter"></i> filtrar la tabla:</p>
        <div id="dataTableExportButtonsContainer"></div>
    </div>

    <table class="table table-bordered table-striped" id="dataTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre Empleado</th>
                <th>Estado</th>
                <th>F. Registro</th>
                <th>F. Actualización</th>
                <th>Modificado Por</th>
                <th>Acciones</th>
            </tr>
        </thead>
    </table>

    <div class="mb-3"></div>

    <!-- Modal para crear y editar empleados -->
    <div class="modal fade" id="modalCreateOrEdit" tabindex="-1" aria-labelledby="modalCreateOrEdit_Title"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalCreateOrEdit_Title"><i class="fa-solid fa-duotone fa-plus"></i>
                        CREAR EMPLEADO</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formCreateOrEdit">
                        <!-- input de idEmpleado en caso de editar -->
                        <input type="hidden" name="idEmpleado" value="0">

                        <div class="mb-3">
                            <label for="nombreEmpleado" class="form-label">Nombre de empleado <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombreEmpleado" name="nombreEmpleado" required>
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
                    url: "{{ route('empleados.listar') }}", // Ruta de Laravel
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
                        data: "nombreEmpleado",
                    },
                    {
                        data: "estado",
                        render: function(data, type, row) {
                            if (data == 2) {
                                return '<span class="badge bg-success">Tiene usuario</span>';
                            } else {
                                return '<span class="badge bg-danger">No tiene usuario</span>';
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
                                data-id="${row.idEmpleado}" data-toggle="tooltip" title="Editar">
                            <i class="fa-duotone fa-solid fa-edit"></i>
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
                const id = 0;

                $('#formCreateOrEdit input[name="idEmpleado"]').val(0);
                $('#formCreateOrEdit input[name="nombreEmpleado"]').val('');

                const titleElement = document.getElementById('modalCreateOrEdit_Title');
                titleElement.innerHTML = '<i class="fa-solid fa-duotone fa-plus"></i> CREAR EMPLEADO';
                $('#modalCreateOrEdit').modal('show');
            });



            $(document).on('click', '.btn-editar', function() {
                const id = $(this).data('id');

                $.get("{{ route('empleados.index') . '/' }}" + id, function(empleado) {
                    $('#formCreateOrEdit input[name="idEmpleado"]').val(empleado.data.idEmpleado);
                    $('#formCreateOrEdit input[name="nombreEmpleado"]').val(empleado.data
                        .nombreEmpleado);
                    const titleElement = document.getElementById('modalCreateOrEdit_Title');
                    titleElement.innerHTML =
                        '<i class="fa-solid fa-duotone fa-edit"></i> EDITAR EMPLEADO';
                    $('#modalCreateOrEdit').modal('show');
                });
            });


            $(document).on('click', '#btnGuardar', function() {
                const idEmpleado = $('#formCreateOrEdit input[name="idEmpleado"]').val();
                const url = idEmpleado == 0 ?
                    "{{ route('empleados.create') }}" // POST -> crear
                    :
                    "{{ route('empleados.index') . '/' }}" + idEmpleado; // PUT -> actualizar

                const type = idEmpleado == 0 ? 'POST' : 'PUT';

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
        });
    </script>
@endsection
