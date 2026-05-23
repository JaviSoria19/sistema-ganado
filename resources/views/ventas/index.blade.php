@extends('layouts.app')

@section('content')
    <h1 class="text-center text-info fw-bold"><i class="fa-solid fa-duotone fa-cart-shopping"></i>
        {{ $headTitle }}</h1>

    <a href="{{ route('ventas.crear') }}" class="btn btn-success mb-3 btn-crear" target="_blank" rel="noopener noreferrer"><i
            class="fa-solid fa-duotone fa-cart-plus"></i> Crear venta</a>

    <h2 class="text-info fw-bold">Lista de ventas</h2>

    <div class="card p-3 mb-3">

        <div class="card-body">
            <p>Seleccione una opción para <i class="fa-solid fa-duotone fa-file-export"></i> exportar o <i
                    class="fa-solid fa-duotone fa-filter"></i> filtrar la tabla:</p>
            <div id="dataTableExportButtonsContainer"></div>
            <br>
            <form class="col-3" method="GET">
                <div class="mb-3">
                    <label for="fechaInicio" class="form-label">Fecha Inicio:</label>
                    <input type="date" id="fechaInicio" name="fechaInicio" class="form-control"
                        value="{{ $fechaInicio }}">
                </div>

                <div class="mb-3">
                    <label for="fechaFin" class="form-label">Fecha Fin:</label>
                    <input type="date" id="fechaFin" name="fechaFin" class="form-control" value="{{ $fechaFin }}">
                </div>

                <button id="form-ventas" type="submit" formaction="{{ route('ventas.index') }}"
                    class="btn btn-primary btn-flat rounded"><i class="fa-solid fa-duotone fa-search"></i> Buscar</button>
            </form>
        </div>
    </div>

    <table class="table table-bordered table-striped" id="dataTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Usuario</th>
                <th>Empleado</th>
                <th>Cliente</th>
                <th>Productos</th>
                <th>Total (USD)</th>
                <th>Pagos (USD)</th>
                <th>Saldo (USD)</th>
                <th>Estado</th>
                <th>F. Registro</th>
                <th>F. Actualización</th>
                <th>F. Eliminación</th>
                <th>Motivo de Eliminación</th>
                <th>Modificado Por</th>
                <th>Acciones</th>
            </tr>
        </thead>
    </table>

    <div class="mb-3"></div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $("#form-ventas").on("click", function(e) {
                e.preventDefault();
                $("#dataTable").DataTable().ajax.reload();
            });

            $("#dataTable").DataTable({
                processing: true,
                ajax: {
                    url: "{{ route('ventas.listar') }}", // Ruta de Laravel
                    type: "GET",
                    data: function(d) {
                        d.fechaInicio = $("#fechaInicio").val();
                        d.fechaFin = $("#fechaFin").val();
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    error: function(xhr, error, thrown) {
                        console.error("Error al cargar los datos:", error);
                    }
                },
                columns: [{
                        data: "idVenta",
                    },
                    {
                        data: "usuario.usuario",
                    },
                    {
                        data: "empleado.nombreEmpleado",
                    },
                    {
                        data: "cliente",
                        render: function(data, type, row) {
                            return `<b class="text-primary">${data.nombreCliente}</b>, ${data.celular}, <b class="text-info">${data.procedencia}</b>`;
                        }
                    },
                    {
                        data: "productos",
                        render: function(data, type, row) {
                            if (!data || data.length === 0) {
                                return "-";
                            }

                            return data.map((producto, index) =>
                                `
                                <b><span class="text-primary">${index + 1}.</span> ${producto.codigoProducto} <span class="text-danger">${producto.identificador}</span> <span class="text-info">${producto.marca.nombreMarca} ${producto.nombreProducto}</span> a <span class="text-success">${producto.pivot.precioUSD} USD</span></b>
                                `
                            ).join("<br>");
                        },
                    },
                    {
                        data: "totalUSD",
                        render: function(data, type, row) {
                            return `<b class="text-success">${data}</b>`
                        }
                    },
                    {
                        data: "pagos",
                        render: function(data, type, row) {
                            if (!data || data.length === 0) {
                                return '0.00';
                            }

                            return data.map((pago, index) =>
                                `<b class="text-primary">${index + 1}.</b> <b class="text-success">${pago.pagoUSD}</b> el ${new Date(pago.fecha_registro).toLocaleString()}`
                            ).join("<br>");
                        }
                    },
                    {
                        data: "saldoUSD",
                        render: function(data, type, row) {
                            return data <= 0 ? `<b class="text-success">${data}</b>` :
                                `<b class="text-warning">${data}</b>`
                        }
                    },
                    {
                        data: "estado",
                        render: function(data, type, row) {
                            if (data == 1) {
                                return '<span class="badge bg-success">Activo</span>';
                            } else {
                                return '<span class="badge bg-danger">Eliminado</span>';
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
                        data: "fechaEliminacion",
                        render: function(data, type, row) {
                            return data ? new Date(data).toLocaleString() : '-';
                        }
                    },
                    {
                        data: "motivoEliminacion",
                        render: function(data, type, row) {
                            return data || '';
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
                                    <a href="{{ route('ventas.index') }}/${row.idVenta}/editar" class="btn btn-warning btn-sm btn-editar" data-toggle="tooltip" title="Editar" target="_blank" rel="noopener noreferrer">
                                        <i class="fa-duotone fa-solid fa-edit"></i>
                                    </a>
                                    <a class="btn {{ session('tema_preferido') == 'dark' ? 'btn-light' : 'btn-dark' }} btn-sm"
                                        href="{{ route('ventas.index') }}/${row.idVenta}/imprimir" data-toggle="tooltip" title="Imprimir" target="_blank" rel="noopener noreferrer">
                                        <i class="fa-duotone fa-solid fa-print"></i>
                                    </a>
                                </div>
                                
                            `;
                        }
                    }
                ],
                columnDefs: [{
                    targets: [4],
                    width: '800px',
                }, ],
                responsive: false,
                lengthChange: true,
                autoWidth: false,
                scrollX: true,
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
                /* filtrar visibilidad de las columnas de datatables. 
                columnDefs: [{
                    targets: [8, 11, 12, 13], // Target the first and third columns (0-indexed)
                    visible: false
                }, ], */
                @include('datatables.dataTablesLanguageProperty')
            }).buttons().container().appendTo('#dataTableExportButtonsContainer');
        });
    </script>
@endsection
