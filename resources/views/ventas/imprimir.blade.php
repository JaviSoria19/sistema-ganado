<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ helper_tituloPagina() }} | VENTA N° {{ $venta->id_venta }}
        {{ $venta->estado == 'eliminado' ? '(ELIMINADA)' : '' }}</title>
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

        * {
            font-size: 10px;
        }
    </style>

    <img src="{{ public_path('img/logo_venta.jpg') }}" class="watermark">

    <div class="table-container border border-info rounded p-2 m-2">

        {{-- ── ENCABEZADO ── --}}
        <table class="table table-bordered">
            <tr class="font-weight-bold">
                <td class="bg-{{ $venta->estado == 'eliminado' ? 'danger' : 'info' }} text-light" style="width:33%">
                    {{ $venta->creado->usuario ?? '—' }}
                </td>
                <td class="bg-{{ $venta->estado == 'eliminado' ? 'danger' : 'info' }} text-light text-center"
                    style="width:33%">
                    VENTA N° {{ $venta->id_venta }}
                </td>
                <td class="bg-{{ $venta->estado == 'eliminado' ? 'danger' : 'info' }} text-light text-right"
                    style="width:33%">
                    Fecha: {{ date('d/m/Y', strtotime($venta->fecha_venta)) }}
                </td>
            </tr>
            @if ($venta->estado == 'eliminado')
                <tr>
                    <td class="text-center" colspan="3">
                        <span class="text-danger font-weight-bold">ELIMINADA</span>
                        — {{ date('d/m/Y H:i:s', strtotime($venta->fecha_eliminacion)) }}
                        @if ($venta->motivo_eliminacion)
                            | Motivo: {{ $venta->motivo_eliminacion }}
                        @endif
                    </td>
                </tr>
            @endif
        </table>

        {{-- ── DATOS DEL CLIENTE + CONCEPTO ── --}}
        <table class="table table-bordered mb-1">
            <tr>
                <td style="width:20%"><span class="text-info font-weight-bold">Cliente:</span>
                    {{ $venta->cliente->nombre }}</td>
                <td style="width:15%"><span class="text-info font-weight-bold">Celular:</span>
                    {{ $venta->cliente->celular }}</td>
                <td style="width:25%"><span class="text-info font-weight-bold">Estancia:</span>
                    {{ $venta->cliente->estancia }}</td>
                <td style="width:40%"><span class="text-info font-weight-bold">Concepto:</span> {{ $venta->concepto }}
                </td>
            </tr>
        </table>

        {{-- ── TIPO DE PRECIO + PARÁMETROS ── --}}
        @php
            $esPorKg = $venta->tipo_precio === 'precio_kg';
        @endphp
        <table class="table table-bordered mb-1">
            <tr>
                <td style="width:25%">
                    <span class="text-info font-weight-bold">Tipo de precio:</span>
                    {{ $esPorKg ? 'Precio por kg' : 'Precio fijo por bovino' }}
                </td>
                @if ($esPorKg)
                    <td style="width:20%">
                        <span class="text-info font-weight-bold">Precio/kg:</span>
                        Bs. {{ number_format($venta->precio_kg, 2) }}
                    </td>
                    <td style="width:20%">
                        <span class="text-info font-weight-bold">Destare:</span>
                        {{ number_format($venta->destare, 2) }}%
                    </td>
                    <td style="width:20%">
                        <span class="text-info font-weight-bold">Rendimiento:</span>
                        {{ number_format($venta->rendimiento, 2) }}%
                    </td>
                    <td style="width:15%"></td>
                @else
                    <td colspan="4"></td>
                @endif
            </tr>
        </table>

        {{-- ── TABLA DE BOVINOS ── --}}
        @php
            $totalCabezas = 0;
            $totalKgVivo = 0;
            $totalKgGancho = 0;
            $totalSubtotal = 0;
        @endphp

        <div class="border border-info">
            <table class="table table-bordered">
                <thead class="text-info text-center">
                    <tr>
                        <th class="align-middle">#</th>
                        <th class="align-middle">Identificador</th>
                        <th class="align-middle">Género</th>
                        <th class="align-middle">F. Nacimiento</th>
                        <th class="align-middle">Color</th>
                        @if ($esPorKg)
                            <th class="align-middle">Kg Vivo</th>
                            <th class="align-middle">Kg Gancho</th>
                        @endif
                        <th class="align-middle">Subtotal (Bs.)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($venta->bovinos as $bovino)
                        @php
                            $det = $bovino->pivot;
                            $totalCabezas++;
                            $totalKgVivo += $det->kg_peso_vivo;
                            $totalKgGancho += $det->kg_peso_gancho;
                            $totalSubtotal += $det->subtotal;
                            $rowBg = $loop->index % 2 == 1 ? '#c7c7c7' : '';
                        @endphp
                        <tr style="background-color: {{ $rowBg }}">
                            <td class="text-center"><b>{{ $loop->iteration }}.</b></td>
                            <td class="font-weight-bold">{{ $bovino->identificador }}</td>
                            <td class="text-center">{{ ucfirst($bovino->genero) }}</td>
                            <td class="text-center">{{ date('d/m/Y', strtotime($bovino->fecha_nacimiento)) }}</td>
                            <td>{{ $bovino->color_actual }}</td>
                            @if ($esPorKg)
                                <td class="text-right">{{ number_format($det->kg_peso_vivo, 2) }}</td>
                                <td class="text-right">{{ number_format($det->kg_peso_gancho, 2) }}</td>
                            @endif
                            <td class="text-right font-weight-bold">{{ number_format($det->subtotal, 2) }}</td>
                        </tr>
                        @if ($det->observacion)
                            <tr style="background-color: {{ $rowBg }}">
                                <td></td>
                                <td colspan="{{ $esPorKg ? 7 : 5 }}" class="text-muted">
                                    <i>Obs: {{ $det->observacion }}</i>
                                </td>
                            </tr>
                        @endif
                    @endforeach

                    {{-- Fila de totales --}}
                    <tr class="font-weight-bold">
                        <td colspan="4" class="text-right">TOTAL CABEZAS:</td>
                        <td class="text-center">{{ $totalCabezas }}</td>
                        @if ($esPorKg)
                            <td class="text-right">{{ number_format($totalKgVivo, 2) }}</td>
                            <td class="text-right">{{ number_format($totalKgGancho, 2) }}</td>
                        @endif
                        <td class="text-right">Bs. {{ number_format($totalSubtotal, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- ── PAGOS ── --}}
        <p class="text-info text-center m-0 font-weight-bold">--- PAGOS ---</p>

        @php
            $totalPagado = $venta->pagos->where('estado', 'activo')->sum('monto');
            $saldo = $venta->total - $totalPagado;
        @endphp

        <div class="border border-info">
            <table class="table table-bordered table-striped">
                <thead class="text-info text-center">
                    <tr>
                        <th class="align-middle">#</th>
                        <th class="align-middle">Fecha</th>
                        <th class="align-middle">Tipo</th>
                        <th class="align-middle">Monto (Bs.)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($venta->pagos->where('estado', 'activo') as $index => $pago)
                        <tr>
                            <td class="text-center"><b>{{ $index + 1 }}.</b></td>
                            <td>{{ date('d/m/Y', strtotime($pago->fecha_pago)) }}</td>
                            <td>{{ ucfirst($pago->tipo_pago) }}</td>
                            <td class="text-right">
                                <span class="text-success font-weight-bold">
                                    {{ number_format($pago->monto, 2) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">Sin pagos registrados</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-right">TOTAL VENTA:</th>
                        <th class="text-right">Bs. {{ number_format($venta->total, 2) }}</th>
                    </tr>
                    <tr>
                        <th colspan="3" class="text-right">TOTAL PAGADO:</th>
                        <th class="text-right">Bs. {{ number_format($totalPagado, 2) }}</th>
                    </tr>
                    <tr>
                        <th colspan="3" class="text-right">SALDO:</th>
                        <th class="text-right {{ $saldo > 0 ? 'text-danger' : 'text-success' }}">
                            Bs. {{ number_format($saldo, 2) }}
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>
</body>

</html>
