@extends('layouts.app')

@section('content')
    <h1 class="text-center text-success fw-bold"><i class="fa-solid fa-duotone fa-chart-mixed-up-circle-dollar"></i>
        {{ $head_title }}</h1>

    <p>Nota: las ventas que se muestran solo serán aquellas que no se deba saldo.</p>

    <div class="card mb-3">

        <div class="card-body">
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

    <div class="mt-3 mb-3 p-2 border rounded">

        {{-- Encabezado de fechas --}}
        <div class="d-flex align-items-center gap-2 mb-3 pb-3 border-bottom">
            <span class="text-secondary small">Entre</span>
            <span class="badge rounded-pill bg-info-subtle text-info fw-semibold px-3 py-2">
                <i class="fa-regular fa-calendar me-1"></i>{{ date('d/m/Y', strtotime($fechaInicio)) }}
            </span>
            <span class="text-secondary small">y</span>
            <span class="badge rounded-pill bg-info-subtle text-info fw-semibold px-3 py-2">
                <i class="fa-regular fa-calendar me-1"></i>{{ date('d/m/Y', strtotime($fechaFin)) }}
            </span>
        </div>

        {{-- Sección utilidades --}}
        <p class="text-uppercase text-secondary fw-semibold small mb-2" style="letter-spacing: .06em; font-size: .7rem;">
            Utilidades
        </p>
        <div class="row g-2 mb-3">
            <div class="col-12 col-md-4">
                <div class="bg-body-secondary rounded p-3">
                    <p class="text-secondary small mb-1">
                        <i class="fa-regular fa-clock-rotate-left me-1"></i>Ventas con saldo pendiente
                    </p>
                    <p class="fs-5 fw-semibold text-success mb-0">
                        {{ number_format($utilidadVentasConSaldo, 2, '.', '') }}
                        <small class="fw-normal fs-6">USD</small>
                    </p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="bg-body-secondary rounded p-3">
                    <p class="text-secondary small mb-1">
                        <i class="fa-regular fa-circle-check me-1"></i>Ventas sin saldo
                    </p>
                    <p class="fs-5 fw-semibold text-success mb-0">
                        {{ number_format($utilidadVentasSinSaldo, 2, '.', '') }}
                        <small class="fw-normal fs-6">USD</small>
                    </p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="bg-body-secondary rounded p-3 border border-success-subtle">
                    <p class="text-secondary small mb-1">
                        <i class="fa-regular fa-chart-line me-1"></i>Total (con y sin saldo)
                    </p>
                    <p class="fs-5 fw-semibold text-success mb-0">
                        {{ number_format($utilidadTotal, 2, '.', '') }}
                        <small class="fw-normal fs-6">USD</small>
                    </p>
                </div>
            </div>
        </div>

        {{-- Sección ventas y productos --}}
        <p class="text-uppercase text-secondary fw-semibold small mb-2" style="letter-spacing: .06em; font-size: .7rem;">
            Ventas y productos
        </p>
        <div class="row g-2">
            <div class="col-12 col-md-4">
                <div class="bg-body-secondary rounded p-3">
                    <p class="text-secondary small mb-1">
                        <i class="fa-regular fa-receipt me-1"></i>Ventas realizadas
                    </p>
                    <p class="fs-5 fw-semibold mb-0">{{ $cantidadVentas }}</p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="bg-body-secondary rounded p-3">
                    <p class="text-secondary small mb-1">
                        <i class="fa-regular fa-box me-1"></i>Productos registrados
                    </p>
                    <p class="fs-5 fw-semibold mb-0">{{ $productosRegistrados }}</p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="bg-body-secondary rounded p-3">
                    <p class="text-secondary small mb-1">
                        <i class="fa-regular fa-bag-shopping me-1"></i>Productos vendidos
                    </p>
                    <p class="fs-5 fw-semibold mb-0">{{ $productosVendidos }}</p>
                </div>
            </div>
        </div>

    </div>

    <h2 class="text-info fw-bold">Ventas sin saldo pendiente</h2>

    <table class="table table-bordered table-striped dataTable">
        <thead>
            <th>Venta</th>
            <th>Saldo (USD)</th>
            <th>Fecha de registro</th>
            <th>Producto</th>
            <th>Costo final (USD)</th>
            <th>Precio de venta (USD)</th>
            <th>Utilidad (USD)</th>
        </thead>
        <tbody>
            @foreach ($ventasSinSaldo as $ventaSinSaldo)
                @foreach ($ventaSinSaldo->productos as $producto)
                    @php
                        $costoFinalUSD =
                            $producto->costoBaseUSD +
                            ($producto->costoBaseUSD * $producto->traspasoPorcentaje) / 100 +
                            $producto->transporteUSD;
                        $utilidad = $producto->pivot->precioUSD - $costoFinalUSD;
                    @endphp
                    <tr>
                        <td class="text-primary fw-bold">{{ $ventaSinSaldo->idVenta }}</td>
                        <td class="fw-bold"><span
                                class="text-{{ $ventaSinSaldo->saldoUSD > 0 ? 'warning' : 'success' }}">{{ $ventaSinSaldo->saldoUSD }}</span>
                        </td>
                        <td>{{ date('d/m/Y H:i:s', strtotime($ventaSinSaldo->fecha_registro)) }}</td>
                        <td class="fw-bold">{{ $producto->codigoProducto }}
                            <span class="text-danger">{{ $producto->identificador }}</span>
                            <span
                                class="text-info">{{ $producto->marca->nombreMarca . ' ' . $producto->nombreProducto }}</span>
                        </td>
                        <td>{{ number_format($costoFinalUSD, 2, '.', '') }}</td>
                        <td>{{ $producto->pivot->precioUSD }}</td>
                        <td class="fw-bold"><span
                                class="text-{{ $utilidad > 0 ? 'success' : 'danger' }}">{{ number_format($utilidad, 2, '.', '') }}</span>
                        </td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    <h2 class="text-info fw-bold">Ventas con saldo pendiente</h2>

    <table class="table table-bordered table-striped dataTable">
        <thead>
            <th>Venta</th>
            <th>Saldo (USD)</th>
            <th>Fecha de registro</th>
            <th>Producto</th>
            <th>Costo final (USD)</th>
            <th>Precio de venta (USD)</th>
            <th>Utilidad (USD)</th>
        </thead>
        <tbody>
            @foreach ($ventasConSaldo as $ventaConSaldo)
                @foreach ($ventaConSaldo->productos as $producto)
                    @php
                        $costoFinalUSD =
                            $producto->costoBaseUSD +
                            ($producto->costoBaseUSD * $producto->traspasoPorcentaje) / 100 +
                            $producto->transporteUSD;
                        $utilidad = $producto->pivot->precioUSD - $costoFinalUSD;
                    @endphp
                    <tr>
                        <td class="text-primary fw-bold">{{ $ventaConSaldo->idVenta }}</td>
                        <td class="fw-bold"><span
                                class="text-{{ $ventaConSaldo->saldoUSD > 0 ? 'warning' : 'success' }}">{{ $ventaConSaldo->saldoUSD }}</span>
                        </td>
                        <td>{{ date('d/m/Y H:i:s', strtotime($ventaConSaldo->fecha_registro)) }}</td>
                        <td class="fw-bold">{{ $producto->codigoProducto }}
                            <span class="text-danger">{{ $producto->identificador }}</span>
                            <span
                                class="text-info">{{ $producto->marca->nombreMarca . ' ' . $producto->nombreProducto }}</span>
                        </td>
                        <td>{{ number_format($costoFinalUSD, 2, '.', '') }}</td>
                        <td>{{ $producto->pivot->precioUSD }}</td>
                        <td class="fw-bold"><span
                                class="text-{{ $utilidad > 0 ? 'success' : 'danger' }}">{{ number_format($utilidad, 2, '.', '') }}</span>
                        </td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
@endsection

@section('scripts')
    <script>
        var dateError = "{{ $errors }}";

        $(document).ready(function() {
            $(".dataTable").DataTable({
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
                @include('datatables.datatables_language_property')
            }).buttons();
        });
    </script>
@endsection
