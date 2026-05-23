<script>
    $(document).ready(function() {
        $("#dataTable").DataTable({
            processing: true,
            ajax: {
                url: "{{ route('pedidos-empresas.listar') }}", // Ruta de Laravel
                type: "GET",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                error: function(xhr, error, thrown) {
                    console.error("Error al cargar los datos:", error);
                }
            },
            columns: [{
                    data: null,
                    render: function(data, type, row, meta) {
                        return meta.row + 1; // número de iteración
                    }
                },
                {
                    data: "empresa.nombreEmpresa",
                },
                {
                    data: "detalles",
                    render: function(data, type, row) {
                        if (!data || data.length === 0) {
                            return "-";
                        }

                        return data.map((detalle, index) =>
                            `<span class="text-primary fw-bold">${index + 1}</span>. <span class="text-info fw-bold">${detalle.nombreProducto}</span><br>
                                (${detalle.cantidad} uds. * ${detalle.precioUSD} USD = ${(detalle.cantidad * detalle.precioUSD).toFixed(2)} USD)`
                        ).join("<br>");
                    }
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        if (!row.detalles || row.detalles.length === 0) {
                            return "0.00";
                        }
                        let total = row.detalles.reduce((acc, detalle) => {
                            return acc + (detalle.cantidad * parseFloat(detalle
                                .precioUSD));
                        }, 0);
                        return total.toFixed(2);
                    }
                },
                {
                    data: "estado",
                    render: function(data, type, row) {
                        if (data == 1) {
                            return '<span class="badge bg-success">Activo</span>';
                        } else {
                            return '<span class="badge bg-danger">Archivado</span>';
                        }
                    }
                },
                {
                    data: "fecha_registro",
                    render: function(data, type, row) {
                        return new Date(data).toLocaleString();
                    }
                },
                {
                    data: "fecha_actualizacion",
                    render: function(data, type, row) {
                        return new Date(data).toLocaleString();
                    }
                },
                {
                    data: "modificado.usuario",
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
                        <button type="button" class="btn btn-warning btn-sm btn-editar" 
                                data-id="${row.idPedidoEmpresa}" data-toggle="tooltip" title="Editar">
                            <i class="fa-duotone fa-solid fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-${row.estado == 1 ? 'danger' : 'success'} btn-sm btn-cambiar-estado" 
                                data-id="${row.idPedidoEmpresa}" data-estado="${row.estado}" data-nombre="${row.empresa.nombreEmpresa}" 
                                data-toggle="tooltip" title="${row.estado == 1 ? 'Deshabilitar' : 'Habilitar'}">
                            <i class="fa-duotone fa-solid fa-toggle-${row.estado == 1 ? 'off' : 'on'}"></i>
                        </button>
                        <a class="btn {{ session('tema_preferido') == 'dark' ? 'btn-light' : 'btn-dark' }} btn-sm" href="{{ route('pedidos-empresas.index') }}/${row.idPedidoEmpresa}/imprimir" data-toggle="tooltip" title="Imprimir" target="_blank" rel="noopener noreferrer">
                            <i class="fa-duotone fa-solid fa-print"></i>
                        </a>
                    </div>
                `;
                    }
                }
            ],
            responsive: true,
            lengthChange: true,
            autoWidth: true,
            colReorder: true,
            order: [
                [0, 'desc']
            ],
            pageLength: 10,
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
            @include('datatables.dataTablesLanguageProperty')
        }).buttons().container().appendTo('#dataTableExportButtonsContainer');

        $(document).on('click', '.btn-crear', function() {
            $('#formCreateOrEdit input[name="idPedidoEmpresa"]').val(0);
            $('#formCreateOrEdit select[name="idEmpresa"]').val('')
                .trigger('change');
            $('#formCreateOrEdit input[name="nombreProducto"]').val('');
            $('#formCreateOrEdit input[name="precioUSD"]').val('');
            $('#formCreateOrEdit input[name="cantidad"]').val('');

            // Previniendo que el usuario se salga de la tabla accidentalmente, no se eliminará el contenido de la tabla.

            const titleElement = document.getElementById('modalCreateOrEdit_Title');
            titleElement.innerHTML =
                '<i class="fa-solid fa-duotone fa-plus"></i> CREAR PEDIDO A EMPRESA';
            $('#modalCreateOrEdit').modal('show');
        });



        $(document).on('click', '.btn-editar', function() {
            const id = $(this).data('id');
            $.get("{{ route('pedidos-empresas.index') . '/' }}" + id, function(saldo_empresa) {
                $('#formCreateOrEdit input[name="idPedidoEmpresa"]').val(saldo_empresa.data
                    .idPedidoEmpresa);
                $('#formCreateOrEdit select[name="idEmpresa"]').val(saldo_empresa.data
                    .idEmpresa).trigger('change');
                $('#formCreateOrEdit input[name="nombreProducto"]').val('');
                $('#formCreateOrEdit input[name="precioUSD"]').val('');
                $('#formCreateOrEdit input[name="cantidad"]').val('');

                poblarTablaDetalles(saldo_empresa.data.detalles);

                const titleElement = document.getElementById('modalCreateOrEdit_Title');
                titleElement.innerHTML =
                    '<i class="fa-solid fa-duotone fa-edit"></i> EDITAR PEDIDO A EMPRESA';
                $('#modalCreateOrEdit').modal('show');
            });
        });

        function poblarTablaDetalles(detalles) {
            // Limpiar la tabla primero
            $("#detalles tbody").empty();

            // Iterar sobre cada detalle y agregarlo a la tabla
            detalles.forEach(function(detalle) {
                let precio = parseFloat(detalle.precioUSD);
                let cantidad = parseInt(detalle.cantidad);
                let subtotal = precio * cantidad;

                let fila = `
            <tr data-detalle-id="${detalle.idDetallePedidoEmpresa}">
                <td class="numero"></td>
                <td class="producto" contenteditable="true">${detalle.nombreProducto}</td>
                <td class="precio" contenteditable="true">${precio.toFixed(2)}</td>
                <td class="cantidad" contenteditable="true">${cantidad}</td>
                <td class="subTotalUSD">${subtotal.toFixed(2)}</td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm btn-remover" 
                        data-toggle="tooltip" title="Remover de la tabla">
                        <i class="fa-solid fa-duotone fa-trash-can-list"></i>
                    </button>
                </td>
            </tr>
        `;

                $("#detalles tbody").append(fila);
            });

            // Reenumerar las filas y actualizar totales
            reenumerarFilas();
            actualizarTotales();
        }

        $(document).on('click', '#btnGuardar', function() {
            const idPedidoEmpresa = $('#formCreateOrEdit input[name="idPedidoEmpresa"]').val();
            const idEmpresa = $('#empresa').val();
            const url = idPedidoEmpresa == 0 ?
                "{{ route('pedidos-empresas.create') }}" // POST -> crear
                :
                "{{ route('pedidos-empresas.index') . '/' }}" + idPedidoEmpresa; // PUT -> actualizar

            const type = idPedidoEmpresa == 0 ? 'POST' : 'PUT';

            let detalles = [];

            $("#detalles tbody tr").each(function() {
                let fila = $(this);
                let nombreProducto = fila.find('.producto').text().trim();
                let precioUSD = parseFloat(fila.find('.precio').text());
                let cantidad = parseInt(fila.find('.cantidad').text());
                detalles.push({
                    nombreProducto: nombreProducto,
                    precioUSD: precioUSD,
                    cantidad: cantidad
                });
            });

            /*
            console.log(idPedidoEmpresa);
            console.log(idEmpresa);
            console.log(detalles);
            */

            if (detalles.length === 0) {
                Swal.fire({
                    title: "¡No válido!",
                    text: "Debe agregar al menos un detalle a la tabla.",
                    icon: "warning"
                });
                return;
            }

            $.ajax({
                url: url,
                type: type,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    idPedidoEmpresa: idPedidoEmpresa,
                    idEmpresa: idEmpresa,
                    detalles: detalles
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Éxito', response.message, 'success');
                        $('#modalCreateOrEdit').modal('hide');
                        $('#dataTable').DataTable().ajax.reload();

                        $("#detalles tbody").empty();
                        actualizarTotales();
                        
                        window.open(`{{ route('pedidos-empresas.index') }}/${response.pedido_empresa.idPedidoEmpresa}/imprimir`, '_blank', 'noopener,noreferrer');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    //console.error(xhr.responseText);
                    //console.error(JSON.parse(xhr.responseText));

                    const erroresConcatenados = Object.values(JSON.parse(xhr.responseText)
                            .errors)
                        .flatMap(errores => errores)
                        .join('<br>');

                    Swal.fire('Error', 'Ocurrió un error al intentar la acción: <br>' +
                        erroresConcatenados, 'error');
                }
            });
        });



        $(document).on('click', '.btn-cambiar-estado', function() {
            const id = $(this).data('id');
            const estadoActual = $(this).data('estado');
            const nuevoEstado = estadoActual == 1 ? 0 : 1;
            const nombre = $(this).data('nombre');
            const accion = nuevoEstado == 1 ? 'restaurar' : 'archivar';

            Swal.fire({
                title: `¡ATENCIÓN!`,
                text: `¿Estás seguro de ${accion} el pedido a la empresa ${nombre}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `Sí, ${accion}`,
                cancelButtonText: 'No, cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('pedidos-empresas.index') . '/' }}" + id,
                        type: "PATCH",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            idPedidoEmpresa: id
                        },
                        success: function(response) {
                            Swal.fire('Actualizado', response.message, 'success');
                            $('#dataTable').DataTable().ajax.reload();
                        },
                        error: function() {
                            Swal.fire('Error',
                                `No se pudo ${accion} el pedido a empresa`,
                                'error');
                        }
                    });

                }
            });
        });

        $('#empresa').select2({
            language: "es",
            dropdownCssClass: "{{ session('tema_preferido') == 'dark' ? 'bg-dark' : '' }}",
            selectionCssClass: "{{ session('tema_preferido') == 'dark' ? 'bg-dark' : '' }}",
            dropdownParent: $('#modalCreateOrEdit')
        });

        function agregarProducto() {
            let nombre = $("#nombreProducto").val().trim();
            let precio = parseFloat($("#precioUSD").val());
            let cantidad = parseInt($("#cantidad").val());

            if (nombre !== "" && !isNaN(precio) && !isNaN(cantidad) && precio > 0 && cantidad > 0) {
                let fila = `
                <tr>
                    <td class="numero"></td>
                    <td class="producto" contenteditable="true">${nombre}</td>
                    <td class="precio" contenteditable="true">${precio.toFixed(2)}</td>
                    <td class="cantidad" contenteditable="true">${cantidad}</td>
                    <td class="subTotalUSD">${(precio * cantidad).toFixed(2)}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm btn-remover" 
                            data-toggle="tooltip" title="Remover de la tabla">
                            <i class="fa-solid fa-duotone fa-trash-can-list"></i>
                        </button>
                    </td>
                </tr>
            `;
                $("#detalles tbody").append(fila);

                reenumerarFilas();
                actualizarTotales();

                // limpiar inputs
                //$("#nombreProducto").val("");
                //$("#precioUSD").val("");
                $("#cantidad").val("");
                $("#nombreProducto").focus();
            }
        }

        function actualizarTotales() {
            let totalUnidades = 0;
            let totalUSD = 0;

            $("#detalles tbody tr").each(function() {
                let precio = parseFloat($(this).find(".precio").text());
                let cantidad = parseInt($(this).find(".cantidad").text());

                if (isNaN(precio)) precio = 0;
                if (isNaN(cantidad)) cantidad = 0;

                // actualizar subtotal de la fila
                let subTotal = precio * cantidad;
                $(this).find(".subTotalUSD").text(subTotal.toFixed(2));

                // acumular totales
                totalUnidades += cantidad;
                totalUSD += subTotal;
            });

            $("#detallesTotalCantidad").text(totalUnidades);
            $("#detallesTotalUSD").text(totalUSD.toFixed(2));
        }

        function reenumerarFilas() {
            $("#detalles tbody tr").each(function(index) {
                $(this).find(".numero").text(index + 1);
            });
        }

        // Botón añadir
        $("#btnAdd").on("click", function() {
            agregarProducto();
        });

        // Enter en cualquier input
        $("#nombreProducto, #precioUSD, #cantidad").on("keypress", function(e) {
            if (e.which === 13) { // Enter
                e.preventDefault();
                agregarProducto();
            }
        });

        // Remover fila
        $("#detalles").on("click", ".btn-remover", function() {
            $(this).closest("tr").remove();
            reenumerarFilas();
            actualizarTotales();
        });

        // Detectar cambios en precio o cantidad (cuando usuario edita)
        $("#detalles").on("input", ".precio, .cantidad", function() {
            let valor = $(this).text();

            // Validar que sea numérico
            if (isNaN(valor) || valor.trim() === "") {
                $(this).text("0");
            }

            actualizarTotales();
        });

        // Validación del detalle si está vacío al perder el foco
        $("#detalles").on("blur", ".producto", function() {
            let valor = $(this).text().trim();

            if (valor === "") {
                $(this).text("PRODUCTO SIN NOMBRE");
            }
        });
    });
</script>
