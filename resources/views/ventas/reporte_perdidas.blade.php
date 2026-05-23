@extends('layouts.app')

@section('content')
    <h1 class="text-center text-danger fw-bold"><i class="fa-solid fa-duotone fa-chart-line-down"></i>
        {{ $headTitle }}</h1>

    <p>Nota: las ventas que se muestran solo serán aquellas que no se deba saldo.</p>

    <div class="card mb-3">

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

                <button type="submit" formaction="{{ route('ventas.utilidades') }}"
                    class="btn btn-primary btn-flat rounded"><i class="fa-solid fa-duotone fa-search"></i> Buscar</button>
            </form>
        </div>
    </div>

    <table class="table table-bordered table-striped" id="dataTable">
        <thead>
            <th>Venta</th>
            <th>Saldo (USD)</th>
            <th>Fecha de registro</th>
            <th>Producto</th>
            <th>Costo final (USD)</th>
            <th>Precio de venta (USD)</th>
            <th>Pérdida (USD)</th>
        </thead>
        <tbody>
            @php
                $perdidaTotal = 0;
            @endphp
            @foreach ($ventas as $venta)
                @foreach ($venta->productos as $producto)
                    @php
                        $costoFinalUSD =
                            $producto->costoBaseUSD +
                            ($producto->costoBaseUSD * $producto->traspasoPorcentaje) / 100 +
                            $producto->transporteUSD;
                        $perdida = $producto->pivot->precioUSD - $costoFinalUSD;
                    @endphp
                    @if ($perdida < 0)
                        @php $perdidaTotal += $perdida; @endphp
                        <tr>
                            <td class="text-primary fw-bold">{{ $venta->idVenta }}</td>
                            <td class="fw-bold"><span
                                    class="text-{{ $venta->saldoUSD > 0 ? 'warning' : 'success' }}">{{ $venta->saldoUSD }}</span>
                            </td>
                            <td>{{ date('d/m/Y H:i:s', strtotime($venta->fecha_registro)) }}</td>
                            <td class="fw-bold">{{ $producto->codigoProducto }} <span
                                    class="text-info">{{ $producto->marca->nombreMarca . ' ' . $producto->nombreProducto }}</span>
                            </td>
                            <td>{{ $costoFinalUSD }}</td>
                            <td>{{ $producto->pivot->precioUSD }}</td>
                            <td class="fw-bold"><span
                                    class="text-{{ $perdida > 0 ? 'success' : 'danger' }}">{{ number_format($perdida, 2) }}</span>
                            </td>
                        </tr>
                    @endif
                @endforeach
            @endforeach
        </tbody>
    </table>

    <div class="mt-3 mb-3">
        <p class="fw-bold">Pérdida total entre las fechas <span
                class="text-info">{{ date('d/m/Y', strtotime($fechaInicio)) }}</span> y <span
                class="text-info">{{ date('d/m/Y', strtotime($fechaFin)) }}</span>: <span
                class="text-danger">{{ number_format($perdidaTotal, 2) }} USD</span></p>
    </div>
@endsection

@section('scripts')
    <script>
        var dateError = "{{ $errors }}";

        $(document).ready(function() {
            $("#dataTable").DataTable({
                responsive: true,
                lengthChange: true,
                autoWidth: true,
                colReorder: true,
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
                @include('datatables.dataTablesLanguageProperty')
            }).buttons().container().appendTo('#dataTableExportButtonsContainer');
        });
    </script>
@endsection
