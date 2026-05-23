@extends('layouts.app')

@section('content')
    <h1 class="text-center text-info fw-bold"><i class="fa-solid fa-duotone fa-boxes-stacked"></i>
        {{ $headTitle }}</h1>

    <div class="mb-3">
        <a class="btn btn-success" href="{{ route('abastecimientos.create') }}"><i class="fa-solid fa-duotone fa-plus"></i>
            Crear abastecimiento</a>
        <button type="button" class="btn btn-success btn-crear" data-bs-toggle="modal" data-bs-target="#modalCreateOrEdit">
            <i class="fa-solid fa-duotone fa-plus"></i> Crear producto</button>
    </div>

    <h2 class="text-info fw-bold">Lista de productos</h2>
    <p>Nota: N° A. = Número de abastecimiento.</p>
    <div class="card p-3 mb-3">
        <p>Seleccione una opción para <i class="fa-solid fa-duotone fa-file-export"></i> exportar o <i
                class="fa-solid fa-duotone fa-filter"></i> filtrar la tabla:</p>
        <div id="dataTableExportButtonsContainer"></div>
    </div>

    <table class="table table-bordered table-striped" id="dataTable">
        <thead>
            <tr>
                <th>#</th>
                <th>N° A.</th>
                <th>Empresa</th>
                <th>Marca</th>
                <th>Producto</th>
                <th>Color</th>
                <th>Identificador</th>
                <th>Código</th>
                <th>Costo base (USD)</th>
                <th>Costo traspaso (%)</th>
                <th>Costo traspaso (USD)</th>
                <th>Costo transporte (USD)</th>
                <th>Costo final (USD)</th>
                <th>Precio de venta (USD)</th>
                <th>Utilidad potencial (USD)</th>
                <th>Bono empresa (USD)</th>
                <th>Bono marca (USD)</th>
                <th>Estado</th>
                <th>F. Registro</th>
                <th>F. Actualización</th>
                <th>F. Eliminación</th>
                <th>F. Venta</th>
                <th>Modificado Por</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <th colspan="8" class="text-end fw-bold">TOTALES:</th>
                <th id="foot-costoBase"></th>
                <th></th>
                <th id="foot-costoTraspaso"></th>
                <th id="foot-transporte"></th>
                <th id="foot-costoFinal"></th>
                <th id="foot-precioVenta"></th>
                <th id="foot-utilidad"></th>
                <th id="foot-bonoEmpresa"></th>
                <th id="foot-bonoMarca"></th>
                <th colspan="7"></th>
            </tr>
        </tfoot>
    </table>

    <h2 class="text-info fw-bold mt-3">Lista de productos disponibles por color</h2>

    <div class="card p-3 mb-3">
        <p>Seleccione una opción para <i class="fa-solid fa-duotone fa-file-export"></i> exportar o <i
                class="fa-solid fa-duotone fa-filter"></i> filtrar la tabla:</p>
        <div id="dataTableExportButtonsContainer2"></div>
    </div>

    <table class="table table-bordered table-striped" id="dataTable2">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Costo base (USD)</th>
                <th>Costo final (USD)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($productos_cantidades_disponibles_color as $producto)
                <tr>
                    <td>{{ $producto->nombreMarca . ' ' . $producto->nombreProducto . ' ' . $producto->color }}</td>
                    <td>{{ $producto->cantidad }}</td>
                    <td class="text-success fw-bold">{{ number_format($producto->costoBaseUSD, 2, '.', '') }}</td>
                    <td class="text-warning fw-bold">{{ number_format($producto->costoFinalUSD, 2, '.', '') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th class="text-end">Totales:</th>
                <th>{{ $productos_cantidades_disponibles->sum('cantidad') }}</th>
                <th class="text-success">
                    {{ number_format($productos_cantidades_disponibles->sum('costoBaseUSD'), 2, '.', '') }}</th>
                <th class="text-warning">
                    {{ number_format($productos_cantidades_disponibles->sum('costoFinalUSD'), 2, '.', '') }}</th>
            </tr>
            @php
                $productos_cantidades_disponibles_por_marcas = collect($productos_cantidades_disponibles)
                    ->groupBy('nombreMarca')
                    ->map(function ($items, $marca) {
                        return [
                            'nombreMarca' => $marca,
                            'cantidadTotal' => $items->sum('cantidad'),
                            'costoBaseTotalUSD' => $items->sum(function ($item) {
                                return (float) $item['costoBaseUSD'];
                            }),
                            'costoFinalTotalUSD' => $items->sum(function ($item) {
                                return (float) $item['costoFinalUSD'];
                            }),
                            'productos' => $items->values(), // opcional: mantiene el detalle por producto
                        ];
                    })
                    ->values();
            @endphp
            @foreach ($productos_cantidades_disponibles_por_marcas as $grupo)
                <tr class="table-info">
                    <th class="text-end">Totales {{ $grupo['nombreMarca'] }}:</th>
                    <th>{{ $grupo['cantidadTotal'] }}</th>
                    <th class="text-success">
                        {{ number_format($grupo['costoBaseTotalUSD'], 2, '.', '') }}</th>
                    <th class="text-warning">
                        {{ number_format($grupo['costoFinalTotalUSD'], 2, '.', '') }}</th>
                </tr>
            @endforeach
        </tfoot>
    </table>

    <div class="mb-3"></div>

    <h2 class="text-info fw-bold mt-3">Productos agrupados (sin color)</h2>

    <div class="card p-3 mb-3">
        <p>Seleccione una opción para <i class="fa-solid fa-duotone fa-file-export"></i> exportar o <i
                class="fa-solid fa-duotone fa-filter"></i> filtrar la tabla:</p>
        <div id="dataTableExportButtonsContainer3"></div>
    </div>

    <table class="table table-bordered table-striped" id="dataTable3">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Costo base (USD)</th>
                <th>Costo final (USD)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($productos_cantidades_disponibles as $producto)
                <tr>
                    <td>{{ $producto->nombreMarca . ' ' . $producto->nombreProducto }}</td>
                    <td>{{ $producto->cantidad }}</td>
                    <td class="text-success fw-bold">{{ number_format($producto->costoBaseUSD, 2, '.', '') }}</td>
                    <td class="text-warning fw-bold">{{ number_format($producto->costoFinalUSD, 2, '.', '') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th class="text-end">Totales:</th>
                <th>{{ $productos_cantidades_disponibles->sum('cantidad') }}</th>
                <th class="text-success">
                    {{ number_format($productos_cantidades_disponibles->sum('costoBaseUSD'), 2, '.', '') }}</th>
                <th class="text-warning">
                    {{ number_format($productos_cantidades_disponibles->sum('costoFinalUSD'), 2, '.', '') }}</th>
            </tr>
            @foreach ($productos_cantidades_disponibles_por_marcas as $grupo)
                <tr class="table-info">
                    <th class="text-end">Totales {{ $grupo['nombreMarca'] }}:</th>
                    <th>{{ $grupo['cantidadTotal'] }}</th>
                    <th class="text-success">
                        {{ number_format($grupo['costoBaseTotalUSD'], 2, '.', '') }}</th>
                    <th class="text-warning">
                        {{ number_format($grupo['costoFinalTotalUSD'], 2, '.', '') }}</th>
                </tr>
            @endforeach
        </tfoot>
    </table>

    <div class="mb-3"></div>

    <!-- Modal para crear y editar productos -->
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
                        <!-- input de idProducto en caso de editar -->
                        <input type="hidden" name="idProducto" value="0">

                        <div class="mb-3">
                            <label for="abastecimiento" class="form-label">Abastecimiento <span
                                    class="text-danger">*</span></label><br>
                            <select style="width: 100%" class="form-select" id="abastecimiento" name="idAbastecimiento"
                                required>
                                <option value="" disabled selected>Seleccione el número de abastecimiento</option>
                                @foreach ($abastecimientos as $abastecimiento)
                                    <option>{{ $abastecimiento->idAbastecimiento }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="empresa" class="form-label">Empresa <span class="text-danger">*</span></label><br>
                            <select style="width: 100%" class="form-select" id="empresa" name="idEmpresa" required>
                                <option value="" disabled selected>Seleccione una empresa</option>
                                @foreach ($empresas as $empresa)
                                    @if ($empresa->estado != '0')
                                        <option value="{{ $empresa->idEmpresa }}">{{ $empresa->nombreEmpresa }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="marca" class="form-label">Marca <span class="text-danger">*</span></label><br>
                            <select style="width: 100%" class="form-select" id="marca" name="idMarca" required>
                                <option value="" disabled selected>Seleccione una marca</option>
                                @foreach ($marcas as $marca)
                                    @if ($marca->estado != '0')
                                        <option value="{{ $marca->idMarca }}">{{ $marca->nombreMarca }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="nombreProducto" class="form-label">Nombre de producto <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombreProducto" name="nombreProducto"
                                list="productos" required>
                        </div>

                        <datalist id="productos">
                            @foreach ($productos as $producto)
                                <option>{{ $producto->nombreProducto }}</option>
                            @endforeach
                        </datalist>

                        <div class="mb-3">
                            <label for="color" class="form-label">Color</label>
                            <input type="text" class="form-control" id="color" name="color" required>
                        </div>

                        <div class="mb-3">
                            <label for="identificador" class="form-label">Identificador <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="identificador" name="identificador" required>
                        </div>

                        <div class="mb-3">
                            <label for="codigoProducto" class="form-label">Código de producto <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="codigoProducto" name="codigoProducto"
                                placeholder="GENERADO AUTOMÁTICAMENTE AL CREAR" required readonly>
                        </div>

                        <div class="mb-3">
                            <label for="costoBaseUSD" class="form-label">Costo base (USD) <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="costoBaseUSD" name="costoBaseUSD" required>
                        </div>

                        <div class="mb-3">
                            <label for="traspasoPorcentaje" class="form-label">Costo traspaso (%) <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="traspasoPorcentaje" name="traspasoPorcentaje"
                                value="{{ $parametro->paramPorcentajeTraspaso }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="transporteUSD" class="form-label">Costo transporte (USD) <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="transporteUSD" name="transporteUSD"
                                value="{{ $parametro->paramTransporteUSD }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="precioVentaUSD" class="form-label">Precio de venta (USD) <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="precioVentaUSD" name="precioVentaUSD"
                                value="{{ $parametro->paramTransporteUSD }}" required>
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
    @include('productos.index_scripts')
@endsection
