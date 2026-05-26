@extends('layouts.app')

@section('content')
    <h1 class="text-center text-info fw-bold"><i class="fa-solid fa-duotone fa-cart-flatbed-boxes"></i> {{ $head_title }}
    </h1>

    <button type="button" class="btn btn-success mb-3 btn-crear" data-bs-toggle="modal" data-bs-target="#modalCreate">
        <i class="fa-solid fa-duotone fa-plus"></i> Crear abastecimiento</button>

    <h2 class="text-info fw-bold">Lista de abastecimientos</h2>

    <div class="card p-3 mb-3">
        <p>Seleccione una opción para <i class="fa-solid fa-duotone fa-file-export"></i> exportar o <i
                class="fa-solid fa-duotone fa-filter"></i> filtrar la tabla:</p>
        <div id="dataTableExportButtonsContainer"></div>
    </div>

    <table class="table table-bordered table-striped" id="dataTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Productos</th>
                <th>Detalles</th>
                <th>F. Registro</th>
                <th>F. Actualización</th>
                <th>Modificado Por</th>
                <th>Acciones</th>
            </tr>
        </thead>
    </table>

    <div class="mb-3"></div>

    <!-- Modal para crear y editar abastecimientos -->
    <div class="modal fade" id="modalCreate" tabindex="-1" aria-labelledby="modalCreate_Title" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalCreate_Title"><i class="fa-solid fa-duotone fa-plus"></i>
                        CREAR ABASTECIMIENTO</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formCreate">
                        <div class="mb-3">
                            <label for="empresa" class="form-label">Empresa <span class="text-danger">*</span></label><br>
                            <select style="width: 100%" class="form-select select2" id="empresa" name="idEmpresa" required>
                                <option value="" disabled selected>Seleccione un empresa</option>
                                @foreach ($empresas as $empresa)
                                    @if ($empresa->estado != '0')
                                        <option value="{{ $empresa->idEmpresa }}">{{ $empresa->nombreEmpresa }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="marca" class="form-label">Marca <span class="text-danger">*</span></label><br>
                            <select style="width: 100%" class="form-select select2" id="marca" name="idMarca" required>
                                <option value="" disabled selected>Seleccione una marca</option>
                                @foreach ($marcas as $marca)
                                    @if ($marca->estado != '0')
                                        <option value="{{ $marca->idMarca }}">{{ $marca->nombreMarca }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="nombreProducto" class="form-label">Producto <span
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
                            <label for="identificador" class="form-label">Identificador (IMEI/S.N.) <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="identificador" name="identificador" required>
                        </div>

                        <div class="mb-3">
                            <label for="costoBaseUSD" class="form-label">Costo base (USD) <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="costoBaseUSD" name="costoBaseUSD" required>
                        </div>

                        <div class="mb-3">
                            <label for="traspasoPorcentaje" class="form-label">Costo Traspaso (%) <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="traspasoPorcentaje" name="traspasoPorcentaje"
                                value="{{ $parametro->paramPorcentajeTraspaso }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="transporteUSD" class="form-label">Costo Transporte (USD): <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="transporteUSD" name="transporteUSD"
                                value="{{ $parametro->paramTransporteUSD }}" required>
                        </div>

                        <div class="mb-3">
                            Costo Final: <span class="text-warning fw-bold" id="costoFinalUSD">0.00 USD</span>
                        </div>

                        <div class="mb-3">
                            <label for="precioVentaUSD" class="form-label">Precio de venta (USD): <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="precioVentaUSD" name="precioVentaUSD"
                                value="{{ $parametro->paramTransporteUSD }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="cantidad" class="form-label">Cantidad (Unidades) <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="cantidad" name="cantidad" step="1"
                                pattern="\d*" placeholder="Solo números enteros" required>
                        </div>

                        <div class="mb-3">
                            <button type="button" id="btnAdd" class="btn btn-success"><i
                                    class="fa-solid fa-duotone fa-plus"></i>
                                Añadir a la tabla (Cantidad)</button>

                            <button type="button" id="btnEmptyTable" class="btn btn-danger"><i
                                    class="fa-solid fa-duotone fa-trash-can-list"></i>
                                Vaciar tabla</button>
                        </div>

                        <div class="mb-3">
                            Productos:
                            <table class="table table-bordered table-striped" id="productos">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th class="visually-hidden">Empresa id</th>
                                        <th class="visually-hidden">Marca id</th>
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
                                        <th>Remover</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>

                        <div class="mb-3">
                            Resumen:
                            <table class="table table-bordered table-striped" id="resumen_productos">
                                <thead>
                                    <tr>
                                        <th>Empresa</th>
                                        <th>Marca</th>
                                        <th>Producto</th>
                                        <th>Costo base (USD)</th>
                                        <th>Costo traspaso (%)</th>
                                        <th>Costo traspaso (USD)</th>
                                        <th>Costo transporte (USD)</th>
                                        <th>Costo final (USD)</th>
                                        <th>Precio de venta (USD)</th>
                                        <th>Cantidad</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>

                        <div class="col d-flex justify-content-end">
                            <h5>Cantidad Total:</h5>
                            &nbsp;
                            <h5 class="text-primary fw-bold" id="productosTotalCantidad">0</h5>
                            &nbsp;
                            <h5>Unidades</h5>
                        </div>

                        <div class="col d-flex justify-content-end">
                            <h5>Costo Base Total (USD):</h5>
                            &nbsp;
                            <h5>$</h5>
                            &nbsp;
                            <h5 class="text-success fw-bold" id="productosCostoBaseTotalUSD">0.00</h5>
                        </div>

                        <div class="col d-flex justify-content-end">
                            <h5>Costo Final Total (USD):</h5>
                            &nbsp;
                            <h5>$</h5>
                            &nbsp;
                            <h5 class="text-warning fw-bold" id="productosCostoFinalTotalUSD">0.00</h5>
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
    @include('abastecimientos.index_scripts')
@endsection
