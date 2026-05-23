<!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>{{ helper_tituloPagina() }} | VENTA N° {{ $venta->idVenta }}
            {{ $venta->estado == '0' ? '(ELIMINADA)' : '' }}</title>
        <!-- Bootstrap CSS -->
        <link href="{{ asset('/public/dependencies/bootstrapdompdf.css') }}" rel="stylesheet">
    </head>

    <body>
        <style>
            html {
                margin: 15px;
            }

            .watermark {
                position: fixed;
                top: 0%;
                left: 23%;
                width: 400px;
                opacity: 0.10;
                z-index: -1000;
            }

            .table-container {
                display: flex;
                flex-direction: column;
            }

            .table-container table {
                margin: 0;
            }

            .table-container table td {
                padding: 0;
                padding-left: 2px;
                padding-right: 2px;
            }

            .table-container table th {
                padding: 0;
                padding-left: 2px;
                padding-right: 2px;
            }

            .inicio {
                margin: 0;
                padding: 0;
            }

            * {
                font-size: 10px;
            }
        </style>
        <img src="{{ public_path('img/logo_venta.jpg') }}" class="watermark">

        {{-- <p
            class="font-weight-bold bg-{{ $venta->estado == '0' ? 'danger' : 'info' }} text-white m-2 text-center rounded align-middle">
            VENTA N° {{ $venta?->idVenta }}
            {{ $venta->estado == '0' ? '(ELIMINADA EL ' . date('d/m/Y H:i:s', strtotime($venta?->fechaEliminacion)) . ')' : '' }}
        </p> --}}

        <div class="table-container border border-info rounded p-2 m-2">
            <table class="table table-bordered">
                <tr class="font-weight-bold bg-secondary text-light">
                    <td class="bg-{{ $venta->estado == '0' ? 'danger' : 'info' }}" style="width: 33%">
                        {{ $venta?->empleado->nombreEmpleado }}
                        {{-- <span class="text-info">Usuario:</span> {{ $venta?->usuario->usuario }} --}}
                    </td>
                    <td class="bg-{{ $venta->estado == '0' ? 'danger' : 'info' }} text-center" style="width: 33%">
                        VENTA N° {{ $venta?->idVenta }}
                    </td>
                    <td class="bg-{{ $venta->estado == '0' ? 'danger' : 'info' }} text-right" style="width: 33%">
                        F. Registro: {{ date('d/m/Y H:i:s', strtotime($venta?->fecha_registro)) }}
                        {{-- <span class="text-info">F. Actualización:</span>
                        {{ date('d/m/Y H:i:s', strtotime($venta?->fecha_actualizacion)) }} --}}
                    </td>
                </tr>
                @if ($venta->fechaEliminacion)
                    <tr>
                        <td class="text-center" colspan="3">
                            Fecha de Eliminación: {{ date('d/m/Y H:i:s', strtotime($venta?->fechaEliminacion)) }}
                        </td>
                    </tr>
                @endif
            </table>

            <div class="text-center font-weight-bold"><span class="text-info">Cliente:</span>
                {{ $venta?->cliente->nombreCliente }} | <span class="text-info">Celular:</span>
                {{ $venta?->cliente->celular }} | <span class="text-info">Procedencia:</span>
                {{ $venta?->cliente->procedencia }}</div>

            @php
                $productosAgrupados = collect($venta?->productos)->groupBy(function ($producto) {
                    // Clave de agrupación (marca + nombre + precio)
                    return $producto->marca->nombreMarca .
                        '|' .
                        $producto->nombreProducto .
                        '|' .
                        $producto->pivot->precioUSD;
                });

                $totalUSD = 0;
                $totalCantidad = 0;
            @endphp

            <div class="border border-info">
                <table class="table table-bordered">
                    <thead class="text-info text-center">
                        <tr>
                            <th class="align-middle">#</th>
                            <th class="align-middle">Producto</th>
                            <th class="align-middle">Cantidad</th>
                            <th class="align-middle">Precio unit. (USD)</th>
                            <th class="align-middle">Subtotal (USD)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($productosAgrupados as $index => $grupo)
                            @php
                                $cantidad = $grupo->count();
                                $precioUnitario = $grupo->first()->pivot->precioUSD;
                                $subtotal = $cantidad * $precioUnitario;
                                $totalUSD += $subtotal;
                                $totalCantidad += $cantidad;
                                $producto = $grupo->first();

                                //Si el número de la fila es par se le asigna una clase como 'table-striped' pero más oscuro
                                $rowBackgroundColor = '';
                                if ($loop->index % 2 == '1') {
                                    $rowBackgroundColor = '#c7c7c7';
                                }

                                $colorDeMarca = '#000000';
                                $colorDeMarca = match (true) {
                                    str_contains($producto->marca->nombreMarca, 'APPLE') => '#555555',
                                    str_contains($producto->marca->nombreMarca, 'ASUS') => '#00539B',
                                    str_contains($producto->marca->nombreMarca, 'FREEYOND') => '#FF6600',
                                    str_contains($producto->marca->nombreMarca, 'HONOR') => '#007BFF',
                                    str_contains($producto->marca->nombreMarca, 'HUAWEI') => '#D81E06',
                                    str_contains($producto->marca->nombreMarca, 'INFINIX') => '#00A651',
                                    str_contains($producto->marca->nombreMarca, 'MEIZU') => '#00A4E4',
                                    str_contains($producto->marca->nombreMarca, 'MOTOROLA') => '#5C2D91',
                                    str_contains($producto->marca->nombreMarca, 'REALME') => '#FFC300',
                                    str_contains($producto->marca->nombreMarca, 'SAMSUNG') => '#1428A0',
                                    str_contains($producto->marca->nombreMarca, 'TECNO') => '#0088CC',
                                    str_contains($producto->marca->nombreMarca, 'XIAOMI') => '#FF6900',
                                    str_contains($producto->marca->nombreMarca, 'ZTE') => '#005BAC',
                                    default => '#000000',
                                };
                            @endphp
                            <tr style="background-color: {{ $rowBackgroundColor }}">
                                <td class="text-center"><b>{{ $loop->iteration }}.</b></td>
                                <td class="font-weight-bold {{str_contains($producto->marca->nombreMarca, 'REALME') ? 'bg-secondary' : '' }}" style="color: {{ $colorDeMarca }}">
                                    {{ $producto->marca->nombreMarca }}
                                    {{ $producto->nombreProducto }}
                                </td>
                                <td class="text-center">{{ $cantidad }}</td>
                                <td class="text-right">{{ number_format($precioUnitario, 2) }}</td>
                                <td class="text-right">{{ number_format($subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                        <tr class="font-weight-bold">
                            <td colspan="2" class="text-right">TOTAL (UNIDADES):</td>
                            <td class="text-center">{{ $totalCantidad }}</td>
                            <td class="text-right">TOTAL (USD):</td>
                            <td class="text-right">{{ number_format($totalUSD, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>


            <p class="text-info text-center m-0 font-weight-bold">--- PAGOS ---</p>

            <div class="border border-info">
                <table class="table table-bordered table-striped">
                    <thead class="text-info text-center">
                        <tr>
                            <th class="align-middle">#</th>
                            <th class="align-middle">Fecha</th>
                            <th class="align-middle">Pago (USD)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($venta?->pagos as $index => $pago)
                            <tr>
                                <td class="text-center"><b>{{ $index + 1 }}.</b></td>
                                <td>{{ date('d/m/Y', strtotime($pago->fechaPago)) }}</td>
                                <td class="text-right"><span
                                        class="text-success font-weight-bold">{{ $pago->pagoUSD }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2" class="text-right">SALDO (USD):</th>
                            <th class="text-right">{{ number_format($venta->saldoUSD, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            {{-- <div class="text-center">Muchas gracias por tu compra!</div> --}}
        </div>
    </body>

    </html>
