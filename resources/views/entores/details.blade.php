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

    <label for="capacidad_carga_acumulada">Capacidad de carga acumulada (ua):</label>
    <p class="form-control mb-3 fw-bold text-primary" id="capacidad_carga_acumulada">
        {{ round($potrero->bovinos->sum('peso_actual') / session('unidad_animal'), 2) }}
    </p>

    <label for="capacidad_carga_disponible">Capacidad de carga disponible (ua):</label>
    @php
        $capacidad_carga_disponible = round(
            $potrero->capacidad_carga_actual - $potrero->bovinos->sum('peso_actual') / session('unidad_animal'),
            2,
        );
    @endphp
    <p class="form-control mb-3 fw-bold {{ $capacidad_carga_disponible >= 0 ? 'text-success' : 'text-danger' }}"
        id="capacidad_carga_disponible">
        {{ $capacidad_carga_disponible }}
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

    <div class="border border-info rounded mb-3 p-2">
        <div class="d-flex justify-content-center align-items-center" style="height: 400px;">
            <canvas id="chart-capacidades-historicas"></canvas>
        </div>
    </div>

    <table class="table table-bordered table-striped mb-3 dataTable" id="capacidades-historicas">
        <thead>
            <tr>
                <th>#</th>
                <th>Capacidad de Carga (ua)</th>
                <th>Fecha</th>
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
                    <td>{{ $capacidad_historica->fecha }}</td>
                    <td><span class="{{ $tendencia_class }}">{{ $tendencia }}</span></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mb-3"></div>

    <h2 class="text-info fw-bold mt-3">Bovinos</h2>

    <div class="border border-info rounded mb-3 p-2">
        <div class="d-flex justify-content-center align-items-center" style="height: 400px;">
            <canvas id="chart-bovinos"></canvas>
        </div>
    </div>

    <table class="table table-bordered table-striped mb-3 dataTable" id="bovinos">
        <thead>
            <tr>
                <th>#</th>
                <th>Identificador</th>
                <th>Género</th>
                <th>Carimbo</th>
                <th>Color actual</th>
                <th>Peso actual (kg)</th>
                <th>Peso actual (ua)</th>

            </tr>
        </thead>
        <tbody>
            @foreach ($potrero->bovinos as $bovino)
                @php
                    $genero_class = $bovino->genero === 'macho' ? 'bg-primary' : 'bg-danger';
                @endphp
                <tr>
                    <td>{{ $loop->index + 1 }}</td>
                    <td>{{ $bovino->identificador }}</td>
                    <td><span class="badge {{ $genero_class }}">{{ ucfirst($bovino->genero) }}</span></td>
                    <td>{{ date('Y', strtotime($bovino->fecha_nacimiento)) }}</td>
                    <td>{{ $bovino->color_actual }}</td>
                    <td>{{ $bovino->peso_actual }}</td>
                    <td class="text-primary">{{ round($bovino->peso_actual / session('unidad_animal'), 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-end">
                    Total bovinos: {{ $potrero->bovinos->count() }} |
                    <span class="badge bg-primary">Machos:
                        {{ $potrero->bovinos->where('genero', 'macho')->count() }}</span> |
                    <span class="badge bg-danger">Hembras:
                        {{ $potrero->bovinos->where('genero', 'hembra')->count() }}</span>
                </th>
                <th class="fw-bold">{{ round($potrero->bovinos->sum('peso_actual'), 2) }}</th>
                <th class="fw-bold text-primary">
                    {{ round($potrero->bovinos->sum('peso_actual') / session('unidad_animal'), 2) }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="mb-3"></div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $("#capacidades-historicas").DataTable({
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

            $("#bovinos").DataTable({
                responsive: true,
                lengthChange: true,
                autoWidth: true,
                colReorder: true,
                order: [],
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

            new Chart(document.getElementById('chart-capacidades-historicas'), {
                type: 'line',
                data: {
                    labels: @json(
                        $potrero->capacidades_historicas->pluck('fecha')->map(function ($fecha) {
                            return date('d/m/Y', strtotime($fecha));
                        })),
                    datasets: [{
                        label: 'Capacidad de Carga (ua)',
                        data: @json($potrero->capacidades_historicas->pluck('capacidad_carga')),
                        borderColor: 'rgba(54, 162, 235, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        fill: true,
                        tension: 0.4,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                        },
                        title: {
                            display: false,
                            text: 'Tendencia de Capacidad de Carga a lo largo del tiempo'
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Fecha'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Capacidad de Carga (ua)'
                            },
                            beginAtZero: true
                        }
                    }
                }
            });

            // Gráfico de dona de doble anillo para separar bovinos por género (Peso en kg y ua)
            new Chart(document.getElementById('chart-bovinos'), {
                type: 'doughnut',
                data: {
                    labels: ['Machos', 'Hembras'],
                    datasets: [{
                            label: 'Peso Actual (kg)',
                            data: [
                                {{ $potrero->bovinos->where('genero', 'macho')->sum('peso_actual') }},
                                {{ $potrero->bovinos->where('genero', 'hembra')->sum('peso_actual') }}
                            ],
                            backgroundColor: [
                                'rgba(54, 162, 235, 0.7)', // Azul para machos
                                'rgba(255, 99, 132, 0.7)' // Rojo/Rosa para hembras
                            ],
                            borderColor: [
                                'rgba(54, 162, 235, 1)',
                                'rgba(255, 99, 132, 1)'
                            ],
                            borderWidth: 1
                        },
                        {
                            label: 'Peso Actual (ua)',
                            data: [
                                {{ round($potrero->bovinos->where('genero', 'macho')->sum('peso_actual') / session('unidad_animal'), 2) }},
                                {{ round($potrero->bovinos->where('genero', 'hembra')->sum('peso_actual') / session('unidad_animal'), 2) }}
                            ],
                            backgroundColor: [
                                'rgba(54, 162, 235, 0.4)', // Azul más claro para UA machos
                                'rgba(255, 99, 132, 0.4)' // Rojo más claro para UA hembras
                            ],
                            borderColor: [
                                'rgba(54, 162, 235, 1)',
                                'rgba(255, 99, 132, 1)'
                            ],
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed !== null) {
                                        label += context.parsed + (context.datasetIndex === 0 ? ' kg' :
                                            ' ua');
                                    }
                                    return context.label + ' (' + label + ')';
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
@endsection
