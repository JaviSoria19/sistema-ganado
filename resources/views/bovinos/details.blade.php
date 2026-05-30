@extends('layouts.app')

@section('content')
    <h1 class="text-center text-info fw-bold">
        <i class="fa-solid fa-duotone fa-cow"></i> {{ $head_title }}
    </h1>

    <a class="btn btn-secondary mb-3" href="{{ route('bovinos.index') }}">
        <i class="fa-solid fa-duotone fa-arrow-left"></i> Volver
    </a>

    <div class="mb-3"></div>

    {{-- ======================== INFORMACIÓN GENERAL ======================== --}}
    <h2 class="text-info fw-bold mt-3">Información General</h2>

    <div class="row">
        <div class="col-md-6">
            <label for="identificador">Identificador:</label>
            <p class="form-control mb-3" id="identificador">{{ $bovino->identificador }}</p>
        </div>
        <div class="col-md-6">
            <label for="origen">Origen:</label>
            <p class="form-control mb-3" id="origen">{{ ucfirst($bovino->origen) }}</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <label for="genero">Género:</label>
            @php
                $genero_class = $bovino->genero === 'macho' ? 'bg-primary' : 'bg-danger';
            @endphp
            <p class="form-control mb-3" id="genero">
                <span class="badge {{ $genero_class }}">{{ ucfirst($bovino->genero) }}</span>
            </p>
        </div>
        <div class="col-md-6">
            <label for="fecha_nacimiento">Fecha de nacimiento:</label>
            <p class="form-control mb-3" id="fecha_nacimiento">
                {{ date('d/m/Y', strtotime($bovino->fecha_nacimiento)) }}
                <small class="text-muted">(Carimbo: {{ date('Y', strtotime($bovino->fecha_nacimiento)) }})</small>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <label for="color_nacimiento">Color al nacimiento:</label>
            <p class="form-control mb-3" id="color_nacimiento">{{ $bovino->color_nacimiento }}</p>
        </div>
        <div class="col-md-6">
            <label for="color_actual">Color actual:</label>
            <p class="form-control mb-3" id="color_actual">{{ $bovino->color_actual }}</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <label for="tiene_identificador_oreja">Identificador de oreja:</label>
            <p class="form-control mb-3" id="tiene_identificador_oreja">
                @if ($bovino->tiene_identificador_oreja)
                    <span class="badge bg-success">Sí</span>
                @else
                    <span class="badge bg-secondary">No</span>
                @endif
            </p>
        </div>
        <div class="col-md-4">
            <label for="tiene_identificador_lomo">Identificador de lomo:</label>
            <p class="form-control mb-3" id="tiene_identificador_lomo">
                @if ($bovino->tiene_identificador_lomo)
                    <span class="badge bg-success">Sí</span>
                @else
                    <span class="badge bg-secondary">No</span>
                @endif
            </p>
        </div>
        <div class="col-md-4">
            <label for="potrero">Potrero actual:</label>
            <p class="form-control mb-3" id="potrero">
                @if ($bovino->potrero)
                    <a href="{{ route('potreros.detalles', $bovino->potrero->id_potrero) }}" class="text-info">
                        {{ $bovino->potrero->nombre }}
                    </a>
                @else
                    <span class="text-muted">Sin potrero</span>
                @endif
            </p>
        </div>
    </div>

    @if ($bovino->fecha_salida)
        <div class="row">
            <div class="col-md-6">
                <label for="fecha_salida">Fecha de salida:</label>
                <p class="form-control mb-3 text-warning fw-bold" id="fecha_salida">
                    {{ date('d/m/Y', strtotime($bovino->fecha_salida)) }}
                </p>
            </div>
        </div>
    @endif

    @if ($bovino->observaciones)
        <label for="observaciones">Observaciones:</label>
        <p class="form-control mb-3" id="observaciones">{{ $bovino->observaciones }}</p>
    @endif

    {{-- ======================== PESOS ======================== --}}
    <h2 class="text-info fw-bold mt-3">Pesos</h2>

    <div class="row">
        <div class="col-md-4">
            <label for="peso_nacimiento">Peso al nacimiento (kg):</label>
            <p class="form-control mb-3" id="peso_nacimiento">{{ $bovino->peso_nacimiento }}</p>
        </div>
        <div class="col-md-4">
            <label for="peso_destete">Peso al destete (kg):</label>
            <p class="form-control mb-3" id="peso_destete">
                {{ $bovino->peso_destete }}
                <small class="text-muted">({{ round($bovino->peso_destete / session('unidad_animal'), 2) }} ua)</small>
            </p>
        </div>
        <div class="col-md-4">
            <label for="peso_actual">Peso actual (kg):</label>
            <p class="form-control mb-3 fw-bold text-primary" id="peso_actual">
                {{ $bovino->peso_actual }}
                <small class="text-muted">({{ round($bovino->peso_actual / session('unidad_animal'), 2) }} ua)</small>
            </p>
        </div>
    </div>

    {{-- ======================== GENEALOGÍA ======================== --}}
    <h2 class="text-info fw-bold mt-3">Genealogía</h2>

    <div class="row">
        <div class="col-md-6">
            <label for="padre">Padre:</label>
            <p class="form-control mb-3" id="padre">
                @if ($bovino->padre)
                    <a href="{{ route('bovinos.detalles', $bovino->padre->id_bovino) }}" class="text-primary">
                        <i class="fa-solid fa-mars"></i> {{ $bovino->padre->identificador }}
                    </a>
                @else
                    <span class="text-muted">Desconocido</span>
                @endif
            </p>
        </div>
        <div class="col-md-6">
            <label for="madre">Madre:</label>
            <p class="form-control mb-3" id="madre">
                @if ($bovino->madre)
                    <a href="{{ route('bovinos.detalles', $bovino->madre->id_bovino) }}" class="text-danger">
                        <i class="fa-solid fa-venus"></i> {{ $bovino->madre->identificador }}
                    </a>
                @else
                    <span class="text-muted">Desconocida</span>
                @endif
            </p>
        </div>
    </div>

    {{-- ======================== ESTADO ======================== --}}
    @php
        $estado = match ($bovino->estado) {
            'inactivo' => 'ARCHIVADO',
            'activo' => 'ACTIVO',
            default => 'DESCONOCIDO',
        };
        $class = match ($bovino->estado) {
            'inactivo' => 'alert alert-secondary',
            'activo' => 'alert alert-success',
            default => 'alert alert-secondary',
        };
    @endphp

    <div class="{{ $class }} fw-bold mb-3">
        Estado: {{ $estado }}
    </div>

    {{-- ======================== PESAJES HISTÓRICOS ======================== --}}
    <h2 class="text-info fw-bold mt-3">Pesajes Históricos</h2>

    <div class="border border-info rounded mb-3 p-2">
        <div class="d-flex justify-content-center align-items-center" style="height: 400px;">
            <canvas id="chart-pesajes-historicos"></canvas>
        </div>
    </div>

    <table class="table table-bordered table-striped mb-3 dataTable" id="pesajes-historicos">
        <thead>
            <tr>
                <th>#</th>
                <th>Peso (kg)</th>
                <th>Peso (ua)</th>
                <th>Fecha</th>
                <th>Ganancia/Pérdida (kg)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($bovino->pesajes_historicos as $pesaje)
                @php
                    $diff = null;
                    $diff_class = 'text-secondary';
                    $diff_label = '—';
                    if (isset($bovino->pesajes_historicos[$loop->index - 1])) {
                        $peso_anterior = $bovino->pesajes_historicos[$loop->index - 1]->peso;
                        $diff = round($pesaje->peso - $peso_anterior, 2);
                        if ($diff > 0) {
                            $diff_class = 'text-success fw-bold';
                            $diff_label = '+' . $diff . ' kg ↑';
                        } elseif ($diff < 0) {
                            $diff_class = 'text-danger fw-bold';
                            $diff_label = $diff . ' kg ↓';
                        } else {
                            $diff_label = '0 kg =';
                        }
                    }
                @endphp
                <tr>
                    <td>{{ $loop->index + 1 }}</td>
                    <td>{{ $pesaje->peso }}</td>
                    <td class="text-primary">{{ round($pesaje->peso / session('unidad_animal'), 2) }}</td>
                    <td>{{ date('d/m/Y', strtotime($pesaje->fecha)) }}</td>
                    <td><span class="{{ $diff_class }}">{{ $diff_label }}</span></td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="text-end">Total pesajes registrados:</th>
                <th>{{ $bovino->pesajes_historicos->count() }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="mb-3"></div>

    {{-- ======================== RECUENTOS HISTÓRICOS ======================== --}}
    <h2 class="text-info fw-bold mt-3">Recuentos Históricos</h2>

    <table class="table table-bordered table-striped mb-3 dataTable" id="recuentos-historicos">
        <thead>
            <tr>
                <th>#</th>
                <th>Estado</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($bovino->recuentos_historicos as $recuento)
                <tr>
                    <td>{{ $loop->index + 1 }}</td>
                    <td>
                        @if ($recuento->estado_recuento)
                            <span class="badge bg-success">Presente</span>
                        @else
                            <span class="badge bg-danger">Ausente</span>
                        @endif
                    </td>
                    <td>{{ date('d/m/Y', strtotime($recuento->fecha)) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2" class="text-end">Total recuentos:</th>
                <th>{{ $bovino->recuentos_historicos->count() }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="mb-3"></div>

    {{-- ======================== ENTORES ======================== --}}
    @if ($bovino->genero === 'hembra' && $bovino->entores_como_hembra->count() > 0)
        <h2 class="text-info fw-bold mt-3">Entores (como hembra)</h2>
        <table class="table table-bordered table-striped mb-3 dataTable" id="entores-hembra">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tipo</th>
                    <th>Fecha inicio</th>
                    <th>Fecha fin</th>
                    <th>Código pajuela</th>
                    <th>Observaciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bovino->entores_como_hembra as $entore)
                    <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td><span class="badge bg-info text-dark">{{ ucfirst($entore->tipo_entore) }}</span></td>
                        <td>{{ date('d/m/Y', strtotime($entore->fecha_inicio)) }}</td>
                        <td>{{ $entore->fecha_fin ? date('d/m/Y', strtotime($entore->fecha_fin)) : '—' }}</td>
                        <td>{{ $entore->codigo_pajuela ?? '—' }}</td>
                        <td>{{ $entore->observaciones ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mb-3"></div>
    @endif

    @if ($bovino->genero === 'macho' && $bovino->entores_como_macho->count() > 0)
        <h2 class="text-info fw-bold mt-3">Entores (como macho)</h2>
        <table class="table table-bordered table-striped mb-3 dataTable" id="entores-macho">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tipo</th>
                    <th>Fecha inicio</th>
                    <th>Fecha fin</th>
                    <th>Código pajuela</th>
                    <th>Observaciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bovino->entores_como_macho as $entore)
                    <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td><span class="badge bg-info text-dark">{{ ucfirst($entore->tipo_entore) }}</span></td>
                        <td>{{ date('d/m/Y', strtotime($entore->fecha_inicio)) }}</td>
                        <td>{{ $entore->fecha_fin ? date('d/m/Y', strtotime($entore->fecha_fin)) : '—' }}</td>
                        <td>{{ $entore->codigo_pajuela ?? '—' }}</td>
                        <td>{{ $entore->observaciones ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mb-3"></div>
    @endif

    {{-- ======================== VENTAS ======================== --}}
    @if ($bovino->ventas->count() > 0)
        <h2 class="text-info fw-bold mt-3">Ventas</h2>
        <table class="table table-bordered table-striped mb-3 dataTable" id="ventas">
            <thead>
                <tr>
                    <th>#</th>
                    <th>ID Venta</th>
                    <th>Precio fijo ($)</th>
                    <th>Precio/kg ($)</th>
                    <th>Destare</th>
                    <th>Rendimiento (%)</th>
                    <th>Kg peso vivo</th>
                    <th>Kg peso gancho</th>
                    <th>Subtotal ($)</th>
                    <th>Observación</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bovino->ventas as $venta)
                    <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td>{{ $venta->id_venta }}</td>
                        <td>{{ $venta->pivot->precio_fijo }}</td>
                        <td>{{ $venta->pivot->precio_kg }}</td>
                        <td>{{ $venta->pivot->destare }}</td>
                        <td>{{ $venta->pivot->rendimiento }}</td>
                        <td>{{ $venta->pivot->kg_peso_vivo }}</td>
                        <td>{{ $venta->pivot->kg_peso_gancho }}</td>
                        <td class="fw-bold text-success">{{ $venta->pivot->subtotal }}</td>
                        <td>{{ $venta->pivot->observacion }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="8" class="text-end">Total vendido:</th>
                    <th class="fw-bold text-success">
                        {{ $bovino->ventas->sum('pivot.subtotal') }}
                    </th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
        <div class="mb-3"></div>
    @endif

    {{-- ======================== AUDITORÍA ======================== --}}
    <h2 class="text-info fw-bold mt-3">Auditoría</h2>

    <div class="row">
        <div class="col-md-4">
            <label>Creado por:</label>
            <p class="form-control mb-3">
                {{ $bovino->creado?->usuario ?? '—' }}
                <small
                    class="text-muted d-block">{{ $bovino->fecha_registro ? date('d/m/Y H:i', strtotime($bovino->fecha_registro)) : '' }}</small>
            </p>
        </div>
        @if ($bovino->modificado_por)
            <div class="col-md-4">
                <label>Modificado por:</label>
                <p class="form-control mb-3">
                    {{ $bovino->modificado?->usuario ?? '—' }}
                    <small
                        class="text-muted d-block">{{ $bovino->fecha_actualizacion ? date('d/m/Y H:i', strtotime($bovino->fecha_actualizacion)) : '' }}</small>
                </p>
            </div>
        @endif
        @if ($bovino->eliminado_por)
            <div class="col-md-4">
                <label>Eliminado por:</label>
                <p class="form-control mb-3 text-danger">
                    {{ $bovino->eliminado?->usuario ?? '—' }}
                    <small
                        class="text-muted d-block">{{ $bovino->fecha_eliminacion ? date('d/m/Y H:i', strtotime($bovino->fecha_eliminacion)) : '' }}</small>
                </p>
            </div>
        @endif
    </div>

    <div class="mb-5"></div>
@endsection

@section('scripts')
    @include('bovinos.details_scripts')
@endsection
