@extends('layouts.app')

@section('content')
    <h1 class="text-center text-info fw-bold"><i class="fa-solid fa-duotone fa-frame"></i> {{ $head_title }}
    </h1>

    <a class="btn btn-secondary mb-3" href="{{ route('potreros.index') }}">
        <i class="fa-solid fa-duotone fa-arrow-left"></i> Volver</a>
    
    <br>

    <label for="potrero">Nombre:</label>
    <p class="form-control mb-3" id="nombre">
        {{ $potrero->nombre }}
    </p>

    <label for="ubicacion">Ubicación:</label>
    <p class="form-control mb-3" id="ubicacion">
        {{ $potrero->ubicacion }}
    </p>

    <label for="superficie">Superficie (ha):</label>
    <p class="form-control mb-3" id="superficie">
        {{ $potrero->superficie }}
    </p>

    <label for="tipo_pasto">Tipo de pasto:</label>
    <p class="form-control mb-3" id="tipo_pasto">
        {{ $potrero->tipo_pasto }}
    </p>

    <label for="estado_potrero">Estado del potrero:</label>
    <p class="form-control mb-3" id="estado_potrero">
        {{ $potrero->estado_potrero }}
    </p>

    <label for="disponibilidad_agua">Disponibilidad de agua:</label>
    <p class="form-control mb-3" id="disponibilidad_agua">
        {{ $potrero->disponibilidad_agua }}
    </p>

    <label for="capacidad_carga">Capacidad de carga actual (ua):</label>
    <p class="form-control mb-3" id="capacidad_carga">
        {{ $potrero->capacidad_carga_actual }}
    </p>
    


    @php
        $estado = match ($potrero->estado) {
            'inactivo' => 'ARCHIVADO',
            'activo' => 'ACTIVO',
            default => 'DESCONOCIDO',
        };
        $class = match ($potrero->estado) {
            'inactivo' => 'alert alert-secondary',
            'activo' => 'alert alert-success',
            default => 'alert alert-secondary',
        };
    @endphp

    <div class="{{ $class }} fw-bold mb-3">
        Estado: {{ $estado }}
    </div>

    <h2 class="text-info fw-bold mt-3">Capacidades Históricas</h2>

    <table class="table table-bordered table-striped mb-3 dataTable" id="detalles">
        <thead>
            <tr>
                <th>#</th>
                <th>Capacidad de Carga (ua)</th>
                <th>Fecha de registro</th>
                <th>Tendencia</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($potrero->capacidades_historicas as $capacidad_historica)
                @php
                    $tendencia = 'Estable =';
                    $tendencia_class = 'text-secondary';
                    if (isset($potrero->capacidades_historicas[$loop->index - 1])) {
                        $capacidad_anterior = $potrero->capacidades_historicas[$loop->index - 1]->capacidad_carga;
                        if ($capacidad_historica->capacidad_carga > $capacidad_anterior) {
                            $tendencia = 'Aumentando ↑';
                            $tendencia_class = 'text-success';
                        } elseif ($capacidad_historica->capacidad_carga < $capacidad_anterior) {
                            $tendencia = 'Disminuyendo ↓';
                            $tendencia_class = 'text-danger';
                        }
                    }
                @endphp
                <tr>
                    <td>{{ $loop->index + 1 }}</td>
                    <td>{{ $capacidad_historica->capacidad_carga }}</td>
                    <td>{{ date('Y-m-d', strtotime($capacidad_historica->fecha_registro)) }}</td>
                    <td><span class="{{ $tendencia_class }}">{{ $tendencia }}</span></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mb-3"></div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $(".dataTable").DataTable({
                responsive: true,
                lengthChange: true,
                autoWidth: true,
                colReorder: true,
                order: [
                    [0, 'desc']
                ],
                pageLength: 100,
                dom: 'Blfrtip',
                buttons: [{
                        extend: 'copy',
                        className: 'btn btn-secondary'
                    },
                    {
                        extend: 'csv',
                        className: 'btn btn-success'
                    },
                    {
                        extend: 'excel',
                        className: 'btn btn-success'
                    },
                    {
                        extend: 'pdf',
                        className: 'btn btn-danger'
                    },
                    {
                        extend: 'colvis',
                        className: 'btn btn-info'
                    },
                    {
                        extend: 'searchBuilder',
                        className: 'btn btn-warning'
                    },
                ],
                @include('components.datatables.datatables_language_property')
            });
        });
    </script>
@endsection
