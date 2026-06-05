<div class="row mb-3">
    <h4 class="text-warning fw-bold"><i class="fa-solid fa-duotone fa-cart-shopping"></i> SALDOS PENDIENTES (RESUMEN)
    </h4>

    <div class="border p-3 mb-3 rounded">
        <p>Seleccione una opción para <i class="fa-solid fa-duotone fa-file-export"></i> exportar o <i
                class="fa-solid fa-duotone fa-filter"></i> filtrar la tabla:</p>
        <div id="dataTableExportButtonsContainer"></div>
    </div>

    <table class="table table-bordered table-striped" id="dataTable">
        <thead class="text-center">
            <tr>
                <th>#</th>
                <th>Empleado</th>
                <th>Cliente</th>
                <th>Celular</th>
                <th>Procedencia</th>
                <th>Saldo (USD)</th>
                <th>Fecha desde</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($saldos_pendientes as $saldo_pendiente)
                <tr>
                    <th class="text-center">{{ $loop->index + 1 }}.</th>
                    <th>{{ $saldo_pendiente->nombreEmpleado }}</th>
                    <th>{{ $saldo_pendiente->nombreCliente }}</th>
                    <th>{{ $saldo_pendiente->celular }}</th>
                    <th>{{ $saldo_pendiente->procedencia }}</th>
                    <th class="text-warning">{{ $saldo_pendiente->saldoPendiente }}</th>
                    <th>{{ date('d/m/Y H:i:s', strtotime($saldo_pendiente->fechaMasAntigua)) }}</th>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-end">Total Saldos Pendientes (USD):</th>
                <th class="{{ $saldos_pendientes->sum('saldoPendiente') > 0 ? 'text-warning' : 'text-success' }}">
                    {{ number_format($saldos_pendientes->sum('saldoPendiente'), 2, '.', '') }}
                </th>
                <th></th>
            </tr>
            <!-- Totales por empleado -->
            @foreach ($saldos_pendientes->groupBy('nombreEmpleado') as $empleado => $saldos)
                <tr>
                    <th colspan="5" class="text-end text-muted">{{ $empleado }}:</th>
                    <th class="{{ $saldos->sum('saldoPendiente') > 0 ? 'text-warning' : 'text-success' }}">
                        {{ number_format($saldos->sum('saldoPendiente'), 2, '.', '') }}
                    </th>
                    <th></th>
                </tr>
            @endforeach
        </tfoot>
    </table>
</div>
<div class="row">
    <h4 class="text-warning fw-bold"><i class="fa-solid fa-duotone fa-cart-shopping"></i> SALDOS PENDIENTES (DETALLES)
    </h4>
    <div class="border p-3 mb-3 rounded">
        <p>Seleccione una opción para <i class="fa-solid fa-duotone fa-file-export"></i> exportar o <i
                class="fa-solid fa-duotone fa-filter"></i> filtrar la tabla:</p>
        <div id="dataTableExportButtonsContainer2"></div>
    </div>
    <table class="table table-bordered table-striped" id="dataTable2">
        <thead>
            <tr>
                <th>Empleado</th>
                <th>Cliente</th>
                <th>Venta</th>
                <th>Productos</th>
                <th>Total (USD)</th>
                <th>Pagos (USD)</th>
                <th>Saldo (USD)</th>
                <th>F. Registro</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($saldos_pendientes_detalles as $saldo_pendiente_detalle)
                <tr>
                    <td>{{ $saldo_pendiente_detalle->empleado->nombreEmpleado }}</td>
                    <td>{{ $saldo_pendiente_detalle->cliente->nombreCliente }}</td>
                    <td>{{ $saldo_pendiente_detalle->id_venta }}</td>
                    <td class="fw-bold">
                        @foreach ($saldo_pendiente_detalle->productos as $producto)
                            <span class="text-primary">{{ $loop->index + 1 }}.</span>
                            {{ $producto->codigoProducto }}
                            <span class="text-danger">{{ $producto->identificador }}</span>
                            <span class="text-info">{{ $producto->nombreProducto }}</span> a
                            <span class="text-success">{{ $producto->pivot->precioUSD }} USD</span>
                            <br>
                        @endforeach
                    </td>
                    <td class="text-success fw-bold">
                        {{ number_format($saldo_pendiente_detalle->totalUSD, 2, '.', '') }}</td>
                    <td class="text-success">
                        {{ number_format($saldo_pendiente_detalle->pagos->sum('montoUSD'), 2, '.', '') }}
                    </td>
                    <td class="text-warning fw-bold">
                        {{ number_format($saldo_pendiente_detalle->saldoUSD, 2, '.', '') }}</td>
                    <td>{{ date('d/m/Y H:i:s', strtotime($saldo_pendiente_detalle->fecha_registro)) }}</td>
                    <td class="text-center">
                        <div class="btn-group" role="group">
                            <a href="{{ route('ventas.editar', $saldo_pendiente_detalle->id_venta) }}"
                                class="btn btn-warning btn-sm" title="Editar" target="_blank"
                                rel="noopener noreferrer"><i class="fa-solid fa-duotone fa-edit"></i></a>
                            <a href="{{ route('ventas.imprimir', $saldo_pendiente_detalle->id_venta) }}"
                                class="btn {{ session('tema_preferido') == 'dark' ? 'btn-light' : 'btn-dark' }} btn-sm"
                                title="Imprimir venta" target="_blank" rel="noopener noreferrer"><i
                                    class="fa-solid fa-duotone fa-print"></i></a>
                        </div>
                    </td>
            @endforeach
        </tbody>
    </table>
</div>
