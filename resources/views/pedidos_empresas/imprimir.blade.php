<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ helper_tituloPagina() }} | PEDIDO A EMPRESA N° {{ $pedido_empresa->idPedidoEmpresa }}</title>
    <!-- Bootstrap CSS -->
    <link href="{{ asset('/public/dependencies/bootstrapdompdf.css') }}" rel="stylesheet">
</head>

<body>
    <style>
        html {
            margin: 25px;
        }

        .titulo {
            font-size: 30px;
            font-weight: bold;
        }

        .subtitulo {
            font-size: 20px;
            font-weight: bold;
        }

        .watermark {
            position: fixed;
            top: 34.5%;
            left: 28%;
            width: 300px;
            opacity: 0.15;
            z-index: -1000;
        }

    </style>
    <img src="{{public_path('img/logo_sistema_ganado.jpg')}}" class="watermark">
    <div class="border border-info rounded p-2">
        <p class="titulo text-info text-center">SOLICITUD DE PEDIDO</p>
        <p class="font-weight-bold"><strong class="text-info">CLIENTE:</strong> {{ session('nombreEmpleado') }}</p>
        <p class="font-weight-bold"><strong class="text-info">EMPRESA:</strong> {{ $pedido_empresa->empresa->nombreEmpresa }}</p>
        <p class="font-weight-bold"><strong class="text-info">FECHA DE REGISTRO:</strong> {{ date('d/m/Y H:i:s', strtotime($pedido_empresa->fecha_registro)) }}</p>
        <p class="subtitulo text-info text-center">DETALLES</p>
        <table class="table table-bordered table-striped">
            <thead class="bg-secondary text-light text-center">
                <tr>
                    <th>#</th>
                    <th>PRODUCTO</th>
                    <th>CANTIDAD</th>
                    <th>PRECIO (USD)</th>
                    <th>SUBTOTAL (USD)</th>
                </tr>
            </thead>
            <tbody>
                @php $total = 0; @endphp
                @foreach ($pedido_empresa->detalles as $index => $detalle)
                    @php
                        $subtotal = $detalle->cantidad * $detalle->precioUSD;
                        $total += $subtotal;
                    @endphp
                    <tr>
                        <td class="text-center"><b>{{ $index + 1 }}.</b></td>
                        <td>{{ $detalle->nombreProducto }}</td>
                        <td class="text-center">{{ $detalle->cantidad }}</td>
                        <td class="text-right">{{ number_format($detalle->precioUSD, 2) }}</td>
                        <td class="text-right">{{ number_format($subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-right">TOTAL (USD)</th>
                    <th class="text-right">{{ number_format($total, 2) }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
</body>

</html>
