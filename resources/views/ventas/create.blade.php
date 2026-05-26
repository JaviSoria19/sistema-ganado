@extends('layouts.app')

@section('content')
    <h1 class="text-center text-info fw-bold"><i class="fa-solid fa-duotone fa-cart-plus"></i>
        {{ $head_title }}</h1>
    <div class="card mb-3">
        <div class="card-body">
            <h2 class="text-info fw-bold"><i class="fa-solid fa-duotone fa-user-tag"></i> ¿QUÉ EMPLEADO ESTÁ REALIZANDO ESTA
                VENTA?</h2>

            <div class="mb-3 col-4">
                <label for="empleado" class="form-label">Empleado <span class="text-danger">*</span></label><br>
                <select style="width: 100%" id="empleado" name="idEmpleado" required>
                    @foreach ($empleados as $empleado)
                        <option value="{{ $empleado->idEmpleado }}"
                            {{ $empleado->idEmpleado == session('idEmpleado') ? 'selected' : '' }}>
                            {{ $empleado->nombreEmpleado }}
                        </option>
                    @endforeach
                </select>
            </div>

            <h2 class="text-info fw-bold"><i class="fa-solid fa-duotone fa-address-card"></i> SELECCIONA, CREA O EDITA AL
                CLIENTE</h2>

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
                        <th>Precio (USD)</th>
                        <th>Remover</th>
                    </tr>
                </thead>
                <tbody>

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

                </tbody>
            </table>
            
            <h5 class="text-end">Cantidad total de productos: <span id="resumen_productos_cantidad_total">0</span></h5>

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
                        <tr>
                            <th>Pagos (USD)</th>
                            <th>Fecha</th>
                            <th>Remover</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="pagoUSD" contenteditable="true">0</td>
                            <td class="fechaPago">
                                <input type="date" class="form-control fechaPagoInput" 
                                    value="{{ date('Y-m-d') }}">
                            </td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mb-3 col-4">
                <h5>Total: <span class="text-primary fw-bold" id="totalUSD">0.00</span> USD</h5>
                <h5>Pagos: <span class="text-success fw-bold" id="totalPagoUSD">0.00</span> USD</h5>
                <h5>Saldo: <span class="text-warning fw-bold" id="saldoUSD">0.00</span> USD</h5>
            </div>


        </div>
        <div class="card-footer">
            <button type="button" id="btnCrearVenta" class="btn btn-primary"><i class="fa-solid fa-duotone fa-save"></i>
                Guardar venta</button>
        </div>
    </div>

    @include('clientes.modal')
@endsection

@section('scripts')
    @include('ventas.create_scripts')
@endsection
