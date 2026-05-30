<script>
    $(document).ready(function() {
        const dtConfig = {
            responsive: true,
            lengthChange: true,
            autoWidth: true,
            colReorder: true,
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
        };

        $("#pesajes-historicos").DataTable({
            ...dtConfig,
            order: [
                [0, 'asc']
            ],
        });

        $("#recuentos-historicos").DataTable({
            ...dtConfig,
            order: [
                [0, 'desc']
            ],
        });

        @if ($bovino->genero === 'hembra' && $bovino->entores_como_hembra->count() > 0)
            $("#entores-hembra").DataTable({
                ...dtConfig,
                order: [
                    [2, 'desc']
                ],
            });
        @endif

        @if ($bovino->genero === 'macho' && $bovino->entores_como_macho->count() > 0)
            $("#entores-macho").DataTable({
                ...dtConfig,
                order: [
                    [2, 'desc']
                ],
            });
        @endif

        @if ($bovino->ventas->count() > 0)
            $("#ventas").DataTable({
                ...dtConfig,
                order: [],
            });
        @endif

        // Gráfico de pesajes históricos (línea)
        new Chart(document.getElementById('chart-pesajes-historicos'), {
            type: 'line',
            data: {
                labels: @json(
                    $bovino->pesajes_historicos->map(function ($p) {
                        return date('d/m/Y', strtotime($p->fecha));
                    })),
                datasets: [{
                    label: 'Peso (kg)',
                    data: @json($bovino->pesajes_historicos->pluck('peso')),
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y',
                }, {
                    label: 'Peso (ua)',
                    data: @json(
                        $bovino->pesajes_historicos->map(function ($p) {
                            return round($p->peso / session('unidad_animal'), 2);
                        })),
                    borderColor: 'rgba(255, 159, 64, 1)',
                    backgroundColor: 'rgba(255, 159, 64, 0.1)',
                    fill: false,
                    tension: 0.4,
                    borderDash: [5, 5],
                    yAxisID: 'y1',
                }]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    title: {
                        display: false,
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
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Peso (kg)'
                        },
                        beginAtZero: false
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Peso (ua)'
                        },
                        beginAtZero: false,
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });
    });
</script>
