@extends('layouts.app')

@section('content')
    <h1 class="text-center text-info fw-bold"><i class="fa-solid fa-duotone fa-cart-flatbed-boxes"></i> {{ $head_title }} N°
        {{ $abastecimiento->idAbastecimiento }}</h1>

    <div class="mb-3">
        <a href="{{ route('abastecimientos.index') }}" class="btn btn-info">
            <i class="fa-solid fa-arrow-left"></i> Volver a la lista de abastecimientos
        </a>
        <button type="button"
            class="btn {{ session('tema_preferido') == 'dark' ? 'btn-light' : 'btn-dark' }} btn-imprimir-codigos-disponibles">
            <i class="fa-duotone fa-solid fa-qrcode"></i> Imprimir todos los códigos de los productos disponibles
        </button>
        <button type="button" class="btn btn-primary btn-guardar-cambios" id="btnGuardar">
            <i class="fa-solid fa-floppy-disk"></i> Guardar cambios
        </button>
    </div>

    <h2 class="text-info fw-bold">Lista de productos</h2>

    <h5>Abastecimiento creado el {{ $abastecimiento->fecha_registro->format('d/m/Y H:i') }}</h5>

    <h5 class="mb-3">Última modificación por <span class="text-info">{{ $abastecimiento->editor?->usuario }}</span>
        el
        {{ $abastecimiento->fecha_actualizacion?->format('d/m/Y H:i') }}</h5>

    <p>Nota: aquí puede actualizar la información de los productos <span class="badge bg-success">disponibles</span>.<br>
        Los productos <span class="badge bg-secondary">eliminados</span> o <span class="badge bg-warning">vendidos</span> no
        se pueden modificar.<br>
        Haga clic en el nombre del producto, costo base, porcentaje de traspaso o costo de transporte para editar esos
        campos, también puede editar la empresa o la marca.<br>
        Los productos se modificarán a partir de la fila que haya seleccionado hacia abajo ↓, por lo que si desea modificar
        varios productos, asegúrese de seleccionar la fila correcta antes de hacer clic en <span
            class="text-primary fw-bold">"Guardar cambios".</span>
    </p>

    <table class="table table-bordered table-striped" id="productos">
        <thead>
            <tr class="text-center align-middle">
                <th>#</th>
                <th class="visually-hidden">Id</th>
                <th>Empresa</th>
                <th>Marca</th>
                <th>Producto</th>
                <th>Color</th>
                <th>Identificador</th>
                <th>Código</th>
                <th>Costo base (USD)</th>
                <th>Costo traspaso (%)</th>
                <th>Costo transporte (USD)</th>
                <th>Precio de venta (USD)</th>
                <th>Estado</th>
                <th>F. Actualización</th>
                <th>Imprimir código</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($abastecimiento->productos as $producto)
                @php
                    $backgroundColor = match ($producto->estado) {
                        0 => 'table-secondary border-secondary', // Eliminado
                        1 => 'table-success border-success', // Disponible
                        2 => 'table-warning border-warning', // Vendido
                        default => '', // Sin estado definido
                    };

                    $estado = match ($producto->estado) {
                        0 => 'Eliminado',
                        1 => 'Disponible',
                        2 => 'Vendido',
                        default => 'Desconocido',
                    };

                    $badgeColor = match ($producto->estado) {
                        0 => 'bg-secondary', // Eliminado
                        1 => 'bg-success', // Disponible
                        2 => 'bg-warning', // Vendido
                        default => 'bg-dark', // Sin estado definido
                    };
                @endphp
                <tr class="{{ $backgroundColor }}">
                    <td class="fw-bold">{{ $loop->iteration }}.</td>
                    <td class="visually-hidden idProducto">{{ $producto->idProducto }}</td>
                    <td>
                        <select style="width: 100%" class="form-select idEmpresa" name="idEmpresa">
                            <option value="{{ $producto->idEmpresa }}" selected>{{ $producto->empresa->nombreEmpresa }}
                            </option>
                            @foreach ($empresas as $empresa)
                                <option value="{{ $empresa->idEmpresa }}">{{ $empresa->nombreEmpresa }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select style="width: 100%" class="form-select idMarca" name="idMarca">
                            <option value="{{ $producto->idMarca }}" selected>{{ $producto->marca->nombreMarca }}</option>
                            @foreach ($marcas as $marca)
                                <option value="{{ $marca->idMarca }}">{{ $marca->nombreMarca }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="fw-bold nombreProducto" {{ $producto->estado == 1 ? 'contenteditable=true' : '' }}>
                        {{ $producto->nombreProducto }}</td>
                    <td class="fw-bold color" {{ $producto->estado == 1 ? 'contenteditable=true' : '' }}>
                        {{ $producto->color }}</td>
                    <td class="fw-bold identificador" {{ $producto->estado == 1 ? 'contenteditable=true' : '' }}>
                        {{ $producto->identificador }}</td>
                    <td class="text-primary fw-bold codigoProducto">{{ $producto->codigoProducto }}</td>
                    <td class="text-success fw-bold costoBaseUSD"
                        {{ $producto->estado == 1 ? 'contenteditable=true' : '' }}>
                        {{ $producto->costoBaseUSD }}</td>
                    <td class="text-success fw-bold traspasoPorcentaje"
                        {{ $producto->estado == 1 ? 'contenteditable=true' : '' }}>
                        {{ $producto->traspasoPorcentaje }}</td>
                    <td class="text-success fw-bold transporteUSD"
                        {{ $producto->estado == 1 ? 'contenteditable=true' : '' }}>
                        {{ $producto->transporteUSD }}</td>
                    <td class="text-dark-aquamarine fw-bold precioVentaUSD"
                        {{ $producto->estado == 1 ? 'contenteditable=true' : '' }}>
                        {{ $producto->precioVentaUSD }}</td>
                    <td><span class="badge {{ $badgeColor }}">{{ $estado }}</span></td>
                    <td>
                        @if ($producto->editor?->usuario)
                            {{ $producto->fecha_actualizacion?->format('d/m/Y H:i') }} <br> por <span
                                class="text-info fw-bold">{{ $producto->editor?->usuario }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if ($producto->estado == 1)
                            <div class="btn-group" role="group">
                                <button type="button"
                                    class="btn {{ session('tema_preferido') == 'dark' ? 'btn-light' : 'btn-dark' }} btn-sm btn-imprimir-codigo"
                                    data-codigo="{{ $producto->codigoProducto }}" data-toggle="tooltip"
                                    title="Imprimir código">
                                    <i class="fa-duotone fa-solid fa-qrcode"></i>
                                </button>
                            </div>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

@section('scripts')
    @include('abastecimientos.update_scripts')
@endsection
