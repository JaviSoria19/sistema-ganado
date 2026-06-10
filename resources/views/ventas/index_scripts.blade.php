<script>
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%',
            language: "es",
            dropdownCssClass: "{{ session('tema_preferido') == 'dark' ? 'bg-dark' : '' }}",
            selectionCssClass: "{{ session('tema_preferido') == 'dark' ? 'bg-dark' : '' }}",
        });

        $("#form-filter-ventas").on("click", function(e) {
            e.preventDefault();
            $("#dataTable").DataTable().ajax.reload();
        });

        $("#dataTable").DataTable({
            processing: true,
            ajax: {
                url: "{{ route('ventas.listar') }}", // Ruta de Laravel
                type: "GET",
                data: function(d) {
                    d.fecha_inicio = $("#busqueda-fecha_inicio").val();
                    d.fecha_fin = $("#busqueda-fecha_fin").val();
                    d.estado = $("#busqueda-estado").val();
                    d.creado_por = $("#busqueda-creado_por").val();
                    d.id_cliente = $("#busqueda-id_cliente").val();
                    d.tipo_precio = $("#busqueda-tipo_precio").val();
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                error: function(xhr, error, thrown) {
                    console.error("Error al cargar los datos:", error);
                }
            },
            columns: [{
                    data: "id_venta",
                },
                {
                    data: "fecha_venta",
                },
                {
                    data: "cliente",
                    render: function(data, type, row) {
                        return `<b class="text-primary">${data.nombre}</b>, ${data.celular}, <b class="text-info">${data.estancia}</b>`;
                    }
                },
                {
                    data: "concepto",
                    render: function(data, type, row) {
                        return data || '-';
                    }
                },
                {
                    data: "tipo_precio",
                    render: function(data, type, row) {
                        if (data === 'precio_fijo') {
                            return '<span class="badge bg-success">Precio fijo</span>';
                        } else if (data === 'precio_kg') {
                            return `<span class="badge bg-primary">Precio por kg</span><br>(${row.precio_kg} Bs./kg)<br>Destare: ${row.destare} % <br>Rendimiento: ${row.rendimiento} %`;
                        } else {
                            return '-';
                        }
                    }
                },
                {
                    data: "bovinos",
                    render: function(data, type, row) {
                        if (!data || data.length === 0) {
                            return "-";
                        }

                        return data.map((bovino, index) => {
                            const carimbo = new Date(bovino.fecha_nacimiento)
                                .getFullYear();
                                // Si el tipo de venta es por kg, mostrar el peso del bovino
                                const peso = bovino.pivot.kg_peso_vivo > 0 ? ` (${bovino.pivot.kg_peso_vivo} kg vivo | ${bovino.pivot.kg_peso_gancho} kg gancho)` : '';
                            return `
                                <b>
                                    <span class="text-primary">${index + 1}.</span> C: ${carimbo} <span class="text-danger">${bovino.identificador}</span> ${peso} a <span class="text-success">${bovino.pivot.subtotal} Bs.</span>
                                </b>
                                `;
                        }).join("<br>");
                    },
                },
                {
                    data: "total",
                    render: function(data, type, row) {
                        return `<b class="text-success">${data} Bs.</b>`;
                    }
                },
                {
                    data: "pagos",
                    render: function(data, type, row) {
                        if (!data || data.length === 0) {
                            return '0.00';
                        }

                        return data.map((pago, index) =>
                            `<b class="text-primary">${index + 1}.</b> <b class="text-success">${pago.monto} Bs.</b> - <b>${pago.tipo_pago}</b> en fecha ${new Date(pago.fecha_pago).toLocaleDateString()}`
                        ).join("<br>");
                    }
                },
                {
                    data: "total_pagado",
                    render: function(data, type, row) {
                        return `<b class="text-success">${data} Bs.</b>`;
                    }
                },
                {
                    data: "saldo",
                    render: function(data, type, row) {
                        return data <= 0 ? `<b class="text-success">${data} Bs.</b>` :
                            `<b class="text-warning">${data} Bs.</b>`;
                    }
                },
                {
                    data: "estado",
                    render: function(data, type, row) {
                        if (data == 'activo') {
                            return '<span class="badge bg-success">Activo</span>';
                        } else {
                            return '<span class="badge bg-danger">Eliminado</span>';
                        }
                    }
                },
                {
                    data: "fecha_registro",
                    render: function(data, type, row) {
                        return data ? new Date(data).toLocaleString() : '-';
                    }
                },
                {
                    data: "fecha_actualizacion",
                    render: function(data, type, row) {
                        return data ? new Date(data).toLocaleString() : '-';
                    }
                },
                {
                    data: "fecha_eliminacion",
                    render: function(data, type, row) {
                        return data ? new Date(data).toLocaleString() : '-';
                    }
                },
                {
                    data: "motivo_eliminacion",
                    render: function(data, type, row) {
                        return data || '-';
                    }
                },
                {
                    data: "creado.usuario",
                    render: function(data, type, row) {
                        return data || '-';
                    }
                },
                {
                    data: "modificado.usuario",
                    render: function(data, type, row) {
                        return data || '-';
                    }
                },
                {
                    data: "eliminado.usuario",
                    render: function(data, type, row) {
                        return data || '-';
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `
                                <div class="btn-group" role="group">
                                    <a href="{{ route('ventas.index') }}/${row.id_venta}/editar" class="btn btn-warning btn-sm btn-editar" data-toggle="tooltip" title="Editar">
                                        <i class="fa-duotone fa-solid fa-edit"></i>
                                    </a>
                                    <a class="btn {{ session('tema_preferido') == 'dark' ? 'btn-light' : 'btn-dark' }} btn-sm"
                                        href="{{ route('ventas.index') }}/${row.id_venta}/imprimir" data-toggle="tooltip" title="Imprimir" target="_blank" rel="noopener noreferrer">
                                        <i class="fa-duotone fa-solid fa-print"></i>
                                    </a>
                                </div>
                                
                            `;
                    }
                }
            ],
            columnDefs: [{
                    targets: [1],
                    width: '120px',
                },
                {
                    targets: [4, 5, 7],
                    width: '300px',
                },
            ],
            responsive: false,
            lengthChange: true,
            autoWidth: false,
            scrollX: true,
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
            /* filtrar visibilidad de las columnas de datatables. 
            columnDefs: [{
                targets: [8, 11, 12, 13], // Target the first and third columns (0-indexed)
                visible: false
            }, ], */
            @include('components.datatables.datatables_language_property')
        }).buttons().container().appendTo('#dataTableExportButtonsContainer');
    });
</script>
