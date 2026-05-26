@extends('layouts.app')

@section('content')
    <h1 class="text-center text-info fw-bold"><i class="fa-solid fa-duotone fa-money-check-dollar-pen"></i>
        {{ $head_title }}</h1>

    <button type="button" class="btn btn-success mb-3 btn-crear" data-bs-toggle="modal" data-bs-target="#modalCreateOrEdit">
        <i class="fa-solid fa-duotone fa-plus"></i> Crear saldo de empresa</button>

    <h2 class="text-info fw-bold">Lista de saldos de empresas</h2>

    <div class="card p-3 mb-3">
        <p>Seleccione una opción para <i class="fa-solid fa-duotone fa-file-export"></i> exportar o <i
                class="fa-solid fa-duotone fa-filter"></i> filtrar la tabla:</p>
        <div id="dataTableExportButtonsContainer"></div>
    </div>

    <table class="table table-bordered table-striped" id="dataTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Empresa</th>
                <th>Monto (USD)</th>
                <th>Pago (USD)</th>
                <th>Saldo (USD)</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>F. Registro</th>
                <th>F. Actualización</th>
                <th>Modificado Por</th>
                <th>Acciones</th>
            </tr>
        </thead>
    </table>

    <div class="mb-3"></div>

    <!-- Modal para crear y editar saldos-empresas -->
    <div class="modal fade" id="modalCreateOrEdit" tabindex="-1" aria-labelledby="modalCreateOrEdit_Title"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalCreateOrEdit_Title"><i class="fa-solid fa-duotone fa-plus"></i>
                        CREAR USUARIO</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formCreateOrEdit">
                        <!-- input de idSaldoEmpresa en caso de editar -->
                        <input type="hidden" name="idSaldoEmpresa" value="0">

                        <div class="mb-3">
                            <label for="empresa" class="form-label">Empresa <span class="text-danger">*</span></label><br>
                            <select style="width: 100%" class="form-select" id="empresa" name="idEmpresa" required>
                                <option value="" disabled selected>Seleccione un empresa</option>
                                @foreach ($empresas as $empresa)
                                    @if ($empresa->estado != '0')
                                        <option value="{{ $empresa->idEmpresa }}">{{ $empresa->nombreEmpresa }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="montoUSD" class="form-label">Monto (USD) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="montoUSD" name="montoUSD" required>
                        </div>

                        <div class="mb-3">
                            <label for="pagoUSD" class="form-label">Pago (USD) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="pagoUSD" name="pagoUSD" required>
                        </div>

                        <div class="mb-3"> 
                            <label for="fecha" class="form-label">Fecha <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="fecha" name="fecha" value="{{ date('Y-m-d') }}" required>
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
                    url: "{{ route('saldos-empresas.listar') }}", // Ruta de Laravel
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
                        data: "empresa.nombreEmpresa",
                    },
                    {
                        data: "montoUSD",
                    },
                    {
                        data: "pagoUSD",
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            let saldoUSD = (row.montoUSD - row.pagoUSD).toFixed(2);
                            return saldoUSD;
                        }
                    },
                    {
                        data: "fecha",
                        render: function(data, type, row) {
                            let fecha = new Date(data);
                            const options = { year: 'numeric', month: '2-digit', day: '2-digit' };
                            return fecha.toLocaleDateString('es-ES', options);
                        }
                    },
                    {
                        data: "estado",
                        render: function(data, type, row) {
                            if (data == 1) {
                                return '<span class="badge bg-success">Activo</span>';
                            } else {
                                return '<span class="badge bg-danger">Archivado</span>';
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
                                data-id="${row.idSaldoEmpresa}" data-toggle="tooltip" title="Editar">
                            <i class="fa-duotone fa-solid fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-${row.estado == 1 ? 'danger' : 'success'} btn-sm btn-cambiar-estado" 
                                data-id="${row.idSaldoEmpresa}" data-estado="${row.estado}" data-nombre="${row.empresa.nombreEmpresa}" 
                                data-toggle="tooltip" title="${row.estado == 1 ? 'Deshabilitar' : 'Habilitar'}">
                            <i class="fa-duotone fa-solid fa-toggle-${row.estado == 1 ? 'off' : 'on'}"></i>
                        </button>
                    </div>
                `;
                        }
                    }
                ],
                responsive: true,
                lengthChange: true,
                autoWidth: true,
                colReorder: true,
                order: [
                    [0, 'desc']
                ],
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
                @include('datatables.datatables_language_property')
            }).buttons().container().appendTo('#dataTableExportButtonsContainer');

            $(document).on('click', '.btn-crear', function() {
                const id = 0;
                $('#formCreateOrEdit input[name="idSaldoEmpresa"]').val(0);
                $('#formCreateOrEdit select[name="idEmpresa"]').val('')
                    .trigger('change');
                $('#formCreateOrEdit input[name="montoUSD"]').val(0);
                $('#formCreateOrEdit input[name="pagoUSD"]').val(0);

                const titleElement = document.getElementById('modalCreateOrEdit_Title');
                titleElement.innerHTML =
                    '<i class="fa-solid fa-duotone fa-plus"></i> CREAR SALDO DE EMPRESA';
                $('#modalCreateOrEdit').modal('show');
            });



            $(document).on('click', '.btn-editar', function() {
                const id = $(this).data('id');

                $.get("{{ route('saldos-empresas.index') . '/' }}" + id, function(saldo_empresa) {
                    fecha = new Date(saldo_empresa.data.fecha);
                    options = { year: 'numeric', month: '2-digit', day: '2-digit' };
                    $('#formCreateOrEdit input[name="idSaldoEmpresa"]').val(saldo_empresa.data.idSaldoEmpresa);
                    $('#formCreateOrEdit select[name="idEmpresa"]').val(saldo_empresa.data.idEmpresa)
                        .trigger('change');
                    $('#formCreateOrEdit input[name="montoUSD"]').val(saldo_empresa.data.montoUSD);
                    $('#formCreateOrEdit input[name="pagoUSD"]').val(saldo_empresa.data.pagoUSD);
                    $('#formCreateOrEdit input[name="fecha"]').val(fecha.toISOString().split('T')[0]);
                    const titleElement = document.getElementById('modalCreateOrEdit_Title');
                    titleElement.innerHTML =
                        '<i class="fa-solid fa-duotone fa-edit"></i> EDITAR SALDO DE EMPRESA';
                    $('#modalCreateOrEdit').modal('show');
                });
            });


            $(document).on('click', '#btnGuardar', function() {
                const idSaldoEmpresa = $('#formCreateOrEdit input[name="idSaldoEmpresa"]').val();
                const url = idSaldoEmpresa == 0 ?
                    "{{ route('saldos-empresas.create') }}" // POST -> crear
                    :
                    "{{ route('saldos-empresas.index') . '/' }}" + idSaldoEmpresa; // PUT -> actualizar

                const type = idSaldoEmpresa == 0 ? 'POST' : 'PUT';

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
                const accion = nuevoEstado == 1 ? 'restaurar' : 'archivar';

                Swal.fire({
                    title: `¡ATENCIÓN!`,
                    text: `¿Estás seguro de ${accion} el saldo de la empresa ${nombre}?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: `Sí, ${accion}`,
                    cancelButtonText: 'No, cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('saldos-empresas.index') . '/' }}" + id,
                            type: "PATCH",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                idSaldoEmpresa: id
                            },
                            success: function(response) {
                                Swal.fire('Actualizado', response.message, 'success');
                                $('#dataTable').DataTable().ajax.reload();
                            },
                            error: function() {
                                Swal.fire('Error', `No se pudo ${accion} el saldo de empresa`,
                                    'error');
                            }
                        });

                    }
                });
            });
        });

        $(document).ready(function() {
            $('#empresa').select2({
                language: "es",
                dropdownCssClass: "{{ session('tema_preferido') == 'dark' ? 'bg-dark' : '' }}",
                selectionCssClass: "{{ session('tema_preferido') == 'dark' ? 'bg-dark' : '' }}",
                dropdownParent: $('#modalCreateOrEdit')
            });
        });
    </script>
@endsection
