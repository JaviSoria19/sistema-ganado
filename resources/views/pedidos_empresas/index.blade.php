@extends('layouts.app')

@section('content')
    <h1 class="text-center text-info fw-bold"><i class="fa-solid fa-duotone fa-file-pen"></i>
        {{ $headTitle }}</h1>

    <button type="button" class="btn btn-success mb-3 btn-crear" data-bs-toggle="modal" data-bs-target="#modalCreateOrEdit">
        <i class="fa-solid fa-duotone fa-plus"></i> Crear pedido a empresa</button>

    <h2 class="text-info fw-bold">Lista de pedidos a empresas</h2>

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
                <th>Detalles</th>
                <th>Total (USD)</th>
                <th>Estado</th>
                <th>F. Registro</th>
                <th>F. Actualización</th>
                <th>Modificado Por</th>
                <th>Acciones</th>
            </tr>
        </thead>
    </table>

    <div class="mb-3"></div>

    <!-- Modal para crear y editar pedidos-empresas -->
    <div class="modal fade" id="modalCreateOrEdit" tabindex="-1" aria-labelledby="modalCreateOrEdit_Title"
        aria-hidden="true">
        <div class="modal-dialog modal-xl"> <!--modal-lg | modal-xl | modal-fullscreen-->
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalCreateOrEdit_Title"><i class="fa-solid fa-duotone fa-plus"></i>
                        CREAR USUARIO</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formCreateOrEdit">
                        <!-- input de idPedidoEmpresa en caso de editar -->
                        <input type="hidden" name="idPedidoEmpresa" value="0">

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
                            <label for="nombreProducto" class="form-label">Producto <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombreProducto" name="nombreProducto"
                                list="productos" required>
                        </div>

                        <datalist id="productos">
                            @foreach ($productos as $producto)
                                <option>{{$producto->marca->nombreMarca . ' ' . $producto->nombreProducto }}</option>
                            @endforeach
                        </datalist>

                        <div class="mb-3">
                            <label for="precioUSD" class="form-label">Precio (USD) <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="precioUSD" name="precioUSD" required>
                        </div>

                        <div class="mb-3">
                            <label for="cantidad" class="form-label">Cantidad (Unidades) <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="cantidad" name="cantidad" step="1"
                                pattern="\d*" placeholder="Solo números enteros" required>
                        </div>

                        <button type="button" id="btnAdd" class="btn btn-success mb-3"><i
                                class="fa-solid fa-duotone fa-plus"></i>
                            Añadir a la lista</button>

                        <div class="mb-3">
                            Detalles:
                            <table class="table table-bordered table-striped" id="detalles">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Producto</th>
                                        <th>Precio (USD)</th>
                                        <th>Cantidad</th>
                                        <th>Subtotal (USD)</th>
                                        <th>Remover</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>

                        <div class="col d-flex justify-content-end">
                            <h5>Cantidad Total:</h5>
                            &nbsp;
                            <h5 class="text-primary fw-bold" id="detallesTotalCantidad">0</h5>
                            &nbsp;
                            <h5>Unidades</h5>
                        </div>

                        <div class="col d-flex justify-content-end">
                            <h5>Monto Total (USD):</h5>
                            &nbsp;
                            <h5>$</h5>
                            &nbsp;
                            <h5 class="text-primary fw-bold" id="detallesTotalUSD">0.00</h5>
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
    @include('pedidos_empresas.index_scripts')
@endsection
