@extends('layouts.app')

@section('content')
    <h1 class="text-center text-info fw-bold">
        <i class="fa-solid fa-duotone fa-edit"></i> {{ $headTitle }}<br>
        <span class="text-danger">{{ $venta->estado == '0' ? '(VENTA ELIMINADA)' : '' }}</span>
    </h1>
    <div class="card mb-3">
        <div class="card-body">
            <a class="btn {{ session('tema_preferido') == 'dark' ? 'btn-light' : 'btn-dark' }} mb-3"
                href="{{ route('ventas.imprimir', $venta->idVenta) }}" data-toggle="tooltip" title="Imprimir" target="_blank"
                rel="noopener noreferrer">
                <i class="fa-duotone fa-solid fa-print"></i> Imprimir
            </a>
            <h2 class="text-info fw-bold"><i class="fa-solid fa-duotone fa-user-tag"></i> EMPLEADO</h2>

            <div class="mb-3 col-4">
                <label for="empleado" class="form-label">Empleado <span class="text-danger">*</span></label><br>
                <select style="width: 100%" id="empleado" name="idEmpleado" required>
                    @foreach ($empleados as $empleado)
                        <option value="{{ $empleado->idEmpleado }}"
                            {{ $empleado->idEmpleado == $venta->idEmpleado ? 'selected' : '' }}>
                            {{ $empleado->nombreEmpleado }}
                        </option>
                    @endforeach
                </select>
            </div>

            <h2 class="text-info fw-bold"><i class="fa-solid fa-duotone fa-address-card"></i> CLIENTE</h2>

            <div class="mb-1">
                <button type="button" class="btn btn-success btn-crear" data-bs-toggle="modal"
                    data-bs-target="#modalCreateOrEdit">
                    <i class="fa-solid fa-duotone fa-plus"></i> Crear cliente</button>

                <button type="button" class="btn btn-warning btn-editar">
                    <i class="fa-solid fa-duotone fa-edit"></i> Editar cliente</button>
            </div>

            <div class="mb-3 col-4">
                <label for="cliente" class="form-label">Cliente <span class="text-danger">*</span></label><br>
                <select style="width: 100%" class="form-select" id="cliente" name="idCliente" required>
                </select>
            </div>

            <h2 class="text-info fw-bold"><i class="fa-solid fa-duotone fa-boxes-stacked"></i> PRODUCTOS</h2>

            <div class="mb-3 col-4">
                <label for="codigoProducto" class="form-label">Código de producto <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="text" class="form-control" id="codigoProducto" name="codigoProducto"
                        placeholder="Ingresa el código y presiona ENTER" required autofocus>
                    <button class="btn btn-primary btn-buscar" type="button">
                        <i class="fa-solid fa-duotone fa-search"></i>
                    </button>
                </div>
            </div>

            <table class="table table-bordered table-striped" id="productos">
                <thead class="text-center">
                    <tr>
                        <th class="visually-hidden">Id</th>
                        <th>Código</th>
                        <th>Identificador</th>
                        <th>Producto</th>
                        <th>C.F. (USD)</th>
                        <th>Precio anterior (USD)</th>
                        <th>Precio (USD)</th>
                        <th>Remover</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($venta->productos as $producto)
                        @php
                            $costoFinalUSD =
                                $producto->costoBaseUSD +
                                ($producto->costoBaseUSD * $producto->traspasoPorcentaje) / 100 +
                                $producto->transporteUSD;
                            $costoFinalUSD = number_format($costoFinalUSD, 2, '.', '');
                        @endphp
                        <tr>
                            <td class="visually-hidden idProducto">{{ $producto->idProducto }}</td>
                            <td class="codigoProducto">{{ $producto->codigoProducto }}</td>
                            <td class="identificador">{{ $producto->identificador }}</td>
                            <td class="nombreProducto">{{ $producto->marca->nombreMarca }} {{ $producto->nombreProducto }} {{ $producto->color }}
                            </td>
                            <td class="costoFinalUSD">{{ $costoFinalUSD }}</td>
                            <td class="precioAnterior">{{ $producto->pivot->precioUSD }}</td>
                            <td class="precioUSD" contenteditable="true">{{ $producto->pivot->precioUSD }}</td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm btn-remover" data-toggle="tooltip"
                                    title="Remover de la tabla"
                                    data-producto="{{ $producto->codigoProducto }} {{ $producto->marca->nombreMarca }} {{ $producto->nombreProducto }}">
                                    <i class="fa-solid fa-duotone fa-trash-can-list"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <h2 class="text-info fw-bold"><i class="fa-solid fa-duotone fa-list-check"></i> RESUMEN</h2>

            <table class="table table-bordered table-striped" id="resumen_productos">
                <thead class="text-center">
                    <tr>
                        <th>Producto</th>
                        <th>Precio (USD)</th>
                        <th>Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $productos_agrupados = $venta->productos->groupBy(function ($producto) {
                            return $producto->marca->nombreMarca . ' ' . $producto->nombreProducto;
                        });
                    @endphp
                    @foreach ($productos_agrupados as $nombre => $productos)
                        @php
                            $precio_total = $productos->sum(function ($producto) {
                                return $producto->pivot->precioUSD;
                            });
                            $cantidad = $productos->count();
                        @endphp
                        <tr class="table-primary" data-producto="{{ $nombre }}">
                            <td>{{ $nombre }}</td>
                            <td>{{ number_format($precio_total, 2, '.', '') }}</td>
                            <td class="cantidad">{{ $cantidad }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <h5 class="text-end">Cantidad total de productos: <span
                    id="resumen_productos_cantidad_total">{{ $venta->productos->count() }}</span></h5>

            <h2 class="text-info fw-bold"><i class="fa-solid fa-duotone fa-credit-card"></i> PAGOS</h2>

            <div class="mb-3 col-4">
                <label for="pagoUSD" class="form-label">Agregar pago (opcional)</label>
                <div class="input-group">
                    <input type="number" class="form-control" id="pagoUSD" name="pagoUSD" required>
                    <button class="btn btn-success btn-agregar-pago" type="button">
                        <i class="fa-solid fa-duotone fa-credit-card"></i>
                    </button>
                </div>
            </div>

            <div class="mb-3 col-4">
                <table class="table table-bordered table-striped" id="pagos">
                    <thead class="text-center">
                        <tr class="border border-primary">
                            <th>#</th>
                            <th class="visually-hidden">Id Pago</th>
                            <th>Fecha</th>
                            <th>Pagos (USD)</th>
                            <th>Remover</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($venta->pagos as $pago)
                            <tr class="border border-primary">
                                <td class="text-center text-primary fw-bold">{{ $loop->index + 1 }}.</td>
                                <td class="visually-hidden idPagoVenta">{{ $pago->idPagoVenta }}</td>
                                <td class="fechaPago">
                                    <input type="date" class="form-control fechaPagoInput"
                                        value="{{ date('Y-m-d', strtotime($pago->fechaPago)) }}">
                                </td>
                                <td class="text-success fw-bold pagoUSD"
                                    {{ $pago->pagoUSD <= '0' ? 'contenteditable=true' : '' }}>{{ $pago->pagoUSD == '0.00' ? 0 : $pago->pagoUSD }}</td>
                                <td class="bg-secondary">
                                    {{-- <button type="button" class="btn btn-danger btn-sm btn-remover" data-toggle="tooltip"
                                        title="Remover de la tabla">
                                        <i class="fa-solid fa-duotone fa-trash-can-list"></i>
                                    </button> --}}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mb-3 col-4">
                <h5>Total: <span class="text-primary fw-bold"
                        id="totalUSD">{{ number_format($venta->totalUSD, 2, '.', '') }}</span> USD</h5>
                <h5>Pagos: <span class="text-success fw-bold"
                        id="totalPagoUSD">{{ number_format($venta->pagos->sum('pagoUSD'), 2, '.', '') }}</span> USD</h5>
                <h5>Saldo: <span class="text-warning fw-bold"
                        id="saldoUSD">{{ number_format($venta->saldoUSD, 2, '.', '') }}</span> USD</h5>
            </div>

            <h2 class="text-danger fw-bold"><i class="fa-solid fa-duotone fa-credit-card"></i> ¿ELIMINARÁ LA VENTA?</h2>
            <div class="mb-3 col-4">
                <label for="motivoEliminacion" class="form-label">Motivo de eliminación <span
                        class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="text" class="form-control" id="motivoEliminacion" name="motivoEliminacion"
                        placeholder="Ingrese el motivo" value="{{ $venta->motivoEliminacion }}" required
                        {{ $venta->estado == '0' ? 'disabled' : '' }}>
                    <button class="btn btn-danger btn-eliminar-venta" type="button" id="btnEliminarVenta"
                        {{ $venta->estado == '0' ? 'disabled' : '' }}>
                        <i class="fa-solid fa-duotone fa-cart-xmark"></i>
                    </button>
                </div>
            </div>

        </div>
        <div class="card-footer">
            <button type="button" id="btnGuardarVenta" class="btn btn-primary"
                {{ $venta->estado == '0' ? 'disabled' : '' }}><i class="fa-solid fa-duotone fa-save"></i>
                Guardar cambios</button>
        </div>
    </div>

    @include('clientes.modal')
@endsection

@section('scripts')
    @include('ventas.update_scripts')
@endsection
