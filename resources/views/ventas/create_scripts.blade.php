<script>
    $(document).ready(function() {
        const paramPorcentajeLimiteDescuento = "{{ $parametro?->paramPorcentajeLimiteDescuento }}";

        $('#empleado').select2({
            language: "es",
            dropdownCssClass: "{{ session('tema_preferido') == 'dark' ? 'bg-dark' : '' }}",
            selectionCssClass: "{{ session('tema_preferido') == 'dark' ? 'bg-dark' : '' }}",
        });

        $('#cliente').select2({
            language: "es",
            dropdownCssClass: "{{ session('tema_preferido') == 'dark' ? 'bg-dark' : '' }}",
            selectionCssClass: "{{ session('tema_preferido') == 'dark' ? 'bg-dark' : '' }}",
        });

        function recargarClientesSelect(idSeleccionado = null) {
            $.ajax({
                url: "{{ route('clientes.listar') }}",
                type: "GET",
                dataType: "json",
                success: function(response) {
                    let $select = $("#cliente");
                    $select.empty();
                    $select.append('<option value="">-- Seleccione un cliente --</option>');

                    $.each(response.data, function(i, cliente) {
                        if (cliente.estado == '0') return; // Omitir clientes inactivos

                        $select.append(
                            `<option value="${cliente.idCliente}">
                        ${cliente.nombreCliente} - CI: ${cliente.cedulaIdentidad} - Cel: ${cliente.celular} - Procedencia: ${cliente.procedencia}
                    </option>`
                        );
                    });

                    // Si se pasó un cliente recién creado, seleccionarlo
                    if (idSeleccionado) {
                        $select.val(idSeleccionado).trigger('change');
                    } else {
                        $select.trigger('change.select2');
                    }
                }
            });
        }

        recargarClientesSelect();

        $(document).on('click', '.btn-crear', function() {
            $('#formCreateOrEdit input[name="idCliente"]').val(0);
            $('#formCreateOrEdit input[name="nombreCliente"]').val('');
            $('#formCreateOrEdit input[name="celular"]').val('');
            $('#formCreateOrEdit input[name="cedulaIdentidad"]').val('');
            $('#formCreateOrEdit input[name="procedencia"]').val('');

            const titleElement = document.getElementById('modalCreateOrEdit_Title');
            titleElement.innerHTML = '<i class="fa-solid fa-duotone fa-plus"></i> CREAR CLIENTE';
            $('#modalCreateOrEdit').modal('show');
        });



        $(document).on('click', '.btn-editar', function() {
            const id = $('#cliente').val();
            if (!id) {
                Swal.fire({
                    theme: 'auto',
                    title: "¡Espera!",
                    text: "Selecciona un cliente para editar su información.",
                    icon: "warning"
                });
                return;
            }

            $.get("{{ route('clientes.index') . '/' }}" + id, function(cliente) {
                $('#formCreateOrEdit input[name="idCliente"]').val(cliente.data.idCliente);
                $('#formCreateOrEdit input[name="nombreCliente"]').val(cliente.data
                    .nombreCliente);
                $('#formCreateOrEdit input[name="celular"]').val(cliente.data.celular);
                $('#formCreateOrEdit input[name="cedulaIdentidad"]').val(cliente.data
                    .cedulaIdentidad);
                $('#formCreateOrEdit input[name="procedencia"]').val(cliente.data.procedencia);

                const titleElement = document.getElementById('modalCreateOrEdit_Title');
                titleElement.innerHTML =
                    '<i class="fa-solid fa-duotone fa-edit"></i> EDITAR CLIENTE';
                $('#modalCreateOrEdit').modal('show');
            });
        });

        $(document).on('click', '#btnGuardar', function() {
            const idCliente = $('#formCreateOrEdit input[name="idCliente"]').val();
            const url = idCliente == 0 ?
                "{{ route('clientes.create') }}" // POST -> crear
                :
                "{{ route('clientes.index') . '/' }}" + idCliente; // PUT -> actualizar

            const type = idCliente == 0 ? 'POST' : 'PUT';

            $.ajax({
                url: url,
                type: type,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: $('#formCreateOrEdit').serialize(),
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Éxito', response.message, 'success');
                        $('#modalCreateOrEdit').modal('hide');
                        recargarClientesSelect(response.cliente.idCliente);
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

        function actualizarTotalPagosSaldo() {
            let totalUSD = 0;
            let totalPagoUSD = 0;
            let saldoUSD = 0;

            $("#productos tbody tr").each(function() {
                let precioUSD = parseFloat($(this).find('.precioUSD').text());
                if (isNaN(precioUSD)) precioUSD = 0;
                totalUSD += precioUSD;
            });

            $("#pagos tbody tr").each(function() {
                let pagoUSD = parseFloat($(this).find(".pagoUSD").text());
                if (isNaN(pagoUSD)) pagoUSD = 0;
                totalPagoUSD += pagoUSD;
            });

            saldoUSD = totalUSD - totalPagoUSD;

            $("#totalUSD").text(totalUSD.toFixed(2));
            $("#totalPagoUSD").text(totalPagoUSD.toFixed(2));
            $("#saldoUSD").text(saldoUSD.toFixed(2));
        }

        // Inicio de métodos para los productos
        function buscarProducto() {
            const codigoProducto = ($('#codigoProducto').val()).trim();
            let existe = false;

            if (codigoProducto.length < 4 || codigoProducto === "") {
                Swal.fire({
                    theme: 'auto',
                    icon: "warning",
                    title: "",
                    text: `¡Ingresa un código o identificador de producto!`,
                    showConfirmButton: false,
                    timer: 1000
                });
                $('#codigoProducto').val('');
                return;
            }

            $("#productos tbody tr").each(function() {
                let codigoFila = $(this).find('.codigoProducto').text();
                let identificadorFila = $(this).find('.identificador').text();
                if (codigoFila == codigoProducto || identificadorFila == codigoProducto) {
                    existe = true;
                }
            });

            if (existe) {
                Swal.fire({
                    theme: 'auto',
                    icon: "info",
                    title: "",
                    html: `¡El producto con el código o identificador <b class="text-primary">${codigoProducto}</b> ya se encuentra en la lista!`,
                    showConfirmButton: false,
                    timer: 1500
                });

                $('#codigoProducto').val('');

                return;
            }
            $.get("{{ route('productos.index') . '/' }}" + codigoProducto + "/codigo", function(producto) {
                if (producto.data == null) {
                    Swal.fire({
                        theme: 'auto',
                        icon: "error",
                        title: "",
                        text: `¡No se encontró el producto con el código ${codigoProducto}!`,
                        showConfirmButton: false,
                        timer: 1500
                    });
                    return;
                } else {
                    if (producto.data.estado == 2 || producto.data.estado == 0) {
                        let estado = producto.data.estado == 2 ? 'vendido' : 'eliminado';
                        Swal.fire({
                            theme: 'auto',
                            icon: "error",
                            title: "",
                            text: `¡El producto con el código ${codigoProducto} fue ${estado} y no está disponible para su venta!`,
                            showConfirmButton: false,
                            timer: 1500
                        });
                        return;
                    }

                    let costoFinalUSD = parseFloat(producto.data.costoBaseUSD) + parseFloat(producto
                        .data.costoBaseUSD * producto.data.traspasoPorcentaje / 100) + parseFloat(
                        producto.data.transporteUSD);
                    let fila = `
                        <tr>
                            <td class="visually-hidden idProducto">${producto.data.idProducto}</td>
                            <td class="codigoProducto">${producto.data.codigoProducto}</td>
                            <td class="identificador">${producto.data.identificador}</td>
                            <td class="nombreProducto">${producto.data.marca.nombreMarca} ${producto.data.nombreProducto} ${producto.data.color}</td>
                            <td class="costoFinalUSD">${costoFinalUSD.toFixed(2)}</td>
                            <td class="precioUSD" contenteditable="true">${producto.data.precioVentaUSD}</td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm btn-remover" 
                                    data-toggle="tooltip" title="Remover de la tabla" data-producto="${producto.data.codigoProducto} ${producto.data.marca.nombreMarca} ${producto.data.nombreProducto}">
                                    <i class="fa-solid fa-duotone fa-trash-can-list"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    $("#productos tbody").append(fila);
                    actualizarTotalPagosSaldo();
                    actualizarResumen();

                    Swal.fire({
                        position: "top-end",
                        title: `Producto con identificador ingresado:<br><b class="text-success">${producto.data.identificador}</b>`,
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true,
                    });
                }
            });

            $('#codigoProducto').val('');
        }

        $(".btn-buscar").on("click", function() {
            buscarProducto();
        });

        $("#codigoProducto").on("keypress", function(e) {
            if (e.which === 13) { // Enter
                e.preventDefault();
                buscarProducto();
            }
        });

        $("#productos").on("click", ".btn-remover", function() {
            Swal.fire({
                theme: "auto",
                title: "Confirmación",
                text: "¿Estás seguro de remover este producto?",
                icon: "info",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Si, remover producto",
                cancelButtonText: "No, cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    $(this).closest("tr").remove();
                    actualizarTotalPagosSaldo();
                    actualizarResumen();
                    Swal.fire({
                        theme: 'auto',
                        icon: "success",
                        title: "",
                        text: `¡Hecho!`,
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            });
        });

        // Validación del detalle si está vacío al perder el foco
        $("#productos").on("blur", ".precioUSD", function() {
            let valor = $(this).text().trim();

            // Obtener el costoFinalUSD de la FILA ACTUAL
            let costoFinal = $(this).closest('tr').find('.costoFinalUSD').text();
            let precioMinimo = Math.round(parseFloat(costoFinal) / 100 * (100 -
                paramPorcentajeLimiteDescuento), 2);

            // Validar que sea numérico, no esté vació o no sea menor al precio minimo
            if (isNaN(valor) || valor.trim() === "" || valor < precioMinimo) {
                Swal.fire({
                    theme: 'auto',
                    icon: "error",
                    title: "",
                    text: `¡El valor ingresado no es un número, está vacío o es inferior al precio mínimo!`,
                    showConfirmButton: false,
                    timer: 3000
                });
                $(this).text(Math.round(precioMinimo));
            }

            actualizarTotalPagosSaldo();
        });
        // Fin de métodos para los pagos

        // Inicio de métodos para los pagos
        function agregarPago() {
            let pagoUSD = parseFloat($("#pagoUSD").val());

            if (pagoUSD > 0) {
                let fila = `
                <tr>
                    <td class="pagoUSD" contenteditable="true">${pagoUSD.toFixed(2)}</td>
                    <td class="fechaPago">
                        <input type="date" class="form-control fechaPagoInput" 
                            value="${new Date().toISOString().split('T')[0]}">
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm btn-remover" 
                            data-toggle="tooltip" title="Remover de la tabla">
                            <i class="fa-solid fa-duotone fa-trash-can-list"></i>
                        </button>
                    </td>
                </tr>
            `;
                $("#pagos tbody").append(fila);
                actualizarTotalPagosSaldo();

                $("#pagoUSD").val("");
                $("#pagoUSD").focus();
            }
        }

        $(".btn-agregar-pago").on("click", function() {
            agregarPago();
        });

        $("#pagoUSD").on("keypress", function(e) {
            if (e.which === 13) { // Enter
                e.preventDefault();
                agregarPago();
            }
        });

        $("#pagos").on("click", ".btn-remover", function() {
            Swal.fire({
                theme: "auto",
                title: "Confirmación",
                text: "¿Estás seguro de remover este pago?",
                icon: "info",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Si, remover pago",
                cancelButtonText: "No, cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    $(this).closest("tr").remove();
                    actualizarTotalPagosSaldo();
                    Swal.fire({
                        theme: 'auto',
                        icon: "success",
                        title: "",
                        text: `¡Hecho!`,
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            });
        });

        // Detectar cambios en precio o cantidad (cuando usuario edita)
        $("#pagos").on("input", ".pagoUSD", function() {
            let valor = $(this).text();

            // Validar que sea numérico
            if (isNaN(valor) || valor.trim() === "") {
                $(this).text("0");
            }

            actualizarTotalPagosSaldo();
        });

        // Fin de métodos para los pagos

        $("#btnCrearVenta").on("click", function() {

            const idEmpleado = $('#empleado').val();
            const idCliente = $('#cliente').val();
            let productos = [];
            let pagos = [];

            $("#productos tbody tr").each(function() {
                let fila = $(this);
                let idProducto = fila.find('.idProducto').text().trim();
                let precioUSD = parseFloat(fila.find('.precioUSD').text());
                productos.push({
                    idProducto: idProducto,
                    precioUSD: precioUSD,
                });
            });

            $("#pagos tbody tr").each(function() {
                let pagoUSD = parseFloat($(this).find('.pagoUSD').text());
                let fechaPago = $(this).find('.fechaPagoInput').val();
                pagos.push({
                    pagoUSD: pagoUSD,
                    fechaPago: fechaPago,
                });
            });

            if (!idEmpleado) {
                Swal.fire({
                    theme: "auto",
                    title: "¡No válido!",
                    html: "Selecciona el <b>empleado</b> que está realizando la venta",
                    icon: "info"
                });
                return;
            }

            if (!idCliente) {
                Swal.fire({
                    theme: "auto",
                    title: "¡No válido!",
                    html: "Selecciona un <b>cliente</b> para registrar la venta",
                    icon: "info"
                });
                return;
            }

            if (productos.length === 0) {
                Swal.fire({
                    theme: "auto",
                    title: "¡No válido!",
                    html: "¡Ingresa al menos un producto a la lista!",
                    icon: "warning"
                });
                return;
            }

            if (pagos.length === 0 || (pagos.length === 1 && pagos[0].pagoUSD === 0)) {
                Swal.fire({
                    theme: "auto",
                    title: "¿Estás seguro?",
                    text: "No agregaste ningún pago, ¿deseas continuar?",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Si, registrar la venta",
                    cancelButtonText: "No, cancelar"
                }).then((result) => {
                    if (result.isConfirmed) {
                        registrarVentaAJAX(idEmpleado, idCliente, productos, pagos);
                    }
                });
            } else {
                Swal.fire({
                    theme: "auto",
                    title: "Confirmación",
                    text: "¿Estás seguro de haber llenado la información correctamente?",
                    icon: "info",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Si, registrar la venta",
                    cancelButtonText: "No, cancelar"
                }).then((result) => {
                    if (result.isConfirmed) {
                        registrarVentaAJAX(idEmpleado, idCliente, productos, pagos);
                    }
                });
            }
        });

        function registrarVentaAJAX(idEmpleado, idCliente, productos, pagos) {
            const btnCrearVenta = document.getElementById('btnCrearVenta');
            const _totalUSD = document.getElementById('totalUSD');
            const _value_totalUSD = parseFloat(_totalUSD.textContent);
            const _saldoUSD = document.getElementById('saldoUSD');
            const _value_saldoUSD = parseFloat(_saldoUSD.textContent);
            btnCrearVenta.disabled = true;
            btnCrearVenta.innerHTML = '<i class="fa-duotone fa-solid fa-loader fa-spin"></i> Guardando...';


            /*console.log('idEmpleado');
            console.log(idEmpleado);
            console.log('idCliente');
            console.log(idCliente);
            console.log('_value_totalUSD');
            console.log(_value_totalUSD);
            console.log('_value_saldoUSD');
            console.log(_value_saldoUSD);
            console.log('productos');
            console.log(productos);
            console.log('pagos');
            console.log(pagos);
            return;*/

            $.ajax({
                url: "{{ route('ventas.create') }}",
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    id_usuario: '{{ session('id_usuario') }}',
                    idCliente: idCliente,
                    idEmpleado: idEmpleado,
                    totalUSD: _value_totalUSD,
                    saldoUSD: _value_saldoUSD,
                    productos: productos,
                    pagos: pagos
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Éxito', response.message, 'success');
                        btnCrearVenta.innerHTML =
                            '<i class="fa-solid fa-duotone fa-cart-circle-check"></i> ¡Éxito!';
                        window.open(
                            `{{ route('ventas.index') }}/${response.venta.idVenta}/imprimir`,
                            '_blank', 'noopener,noreferrer');
                    } else {
                        btnCrearVenta.disabled = false;
                        btnCrearVenta.innerHTML =
                            '<i class="fa-solid fa-duotone fa-save"></i> Guardar venta';
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    btnCrearVenta.disabled = false;
                    btnCrearVenta.innerHTML =
                        '<i class="fa-solid fa-duotone fa-save"></i> Guardar venta';

                    //console.error(xhr.responseText);
                    //console.error(JSON.parse(xhr.responseText));

                    let message = JSON.parse(xhr.responseText).message;

                    Swal.fire({
                        theme: 'auto',
                        title: 'Error',
                        html: 'Ocurrió un error al intentar la acción:<br>' + message,
                        icon: 'error',
                    });
                }
            });
        }

        // Navegación con flechas en celdas editables
        document.getElementById('productos').addEventListener('keydown', function(e) {
            if (!e.target.hasAttribute('contenteditable')) return;

            const tecla = e.key;
            if (!['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'Enter'].includes(tecla)) return;

            const celda = e.target;
            const fila = celda.parentElement;
            const filas = Array.from(this.querySelectorAll('tbody tr'));
            const celdas = Array.from(fila.querySelectorAll('[contenteditable="true"]'));

            const indiceFila = filas.indexOf(fila);
            const indiceCelda = celdas.indexOf(celda);

            let nuevaCelda = null;

            switch (tecla) {
                case 'ArrowUp':
                    e.preventDefault();
                    if (indiceFila > 0) {
                        nuevaCelda = filas[indiceFila - 1].querySelectorAll('[contenteditable="true"]')[
                            indiceCelda];
                    }
                    break;

                case 'ArrowDown':
                case 'Enter':
                    e.preventDefault();
                    if (indiceFila < filas.length - 1) {
                        nuevaCelda = filas[indiceFila + 1].querySelectorAll('[contenteditable="true"]')[
                            indiceCelda];
                    }
                    break;

                case 'ArrowLeft':
                    if (window.getSelection().anchorOffset === 0) {
                        e.preventDefault();
                        if (indiceCelda > 0) {
                            nuevaCelda = celdas[indiceCelda - 1];
                        }
                    }
                    break;

                case 'ArrowRight':
                    const texto = celda.textContent;
                    if (window.getSelection().anchorOffset === texto.length) {
                        e.preventDefault();
                        if (indiceCelda < celdas.length - 1) {
                            nuevaCelda = celdas[indiceCelda + 1];
                        }
                    }
                    break;
            }

            if (nuevaCelda) {
                nuevaCelda.focus();
                // Seleccionar todo el contenido al navegar
                const rango = document.createRange();
                rango.selectNodeContents(nuevaCelda);
                const seleccion = window.getSelection();
                seleccion.removeAllRanges();
                seleccion.addRange(rango);
            }
        });

        // Aplicar hacia abajo cambios en celdas editables
        /*
        $("#productos").on("blur", ".precioUSD", function() {
            const celda = $(this);
            const valor = celda.text().trim();
            const clase = celda.attr("class").split(" ").find(c => ["precioUSD"].includes(c));

            if (!clase) return;

            // Aplicar hacia abajo
            const tabla = $("#productos");
            let aplicarHaciaAbajo = false;

            tabla.find("tbody tr").each(function() {
                const fila = $(this);
                const celdaActual = fila.find(`.${clase}`);

                if (celdaActual.is(celda)) {
                    aplicarHaciaAbajo = true; // Empezar a aplicar hacia abajo desde esta fila
                } else if (aplicarHaciaAbajo) {
                    celdaActual.text(valor);
                }
            });

            actualizarTotalPagosSaldo();
        });
        */

        function actualizarResumen() {
            let resumen = {};
            let cantidad_total = 0;
            $("#productos tbody tr").each(function() {
                let nombre = $(this).find(".nombreProducto").text().trim().toUpperCase();
                let precioUSD = parseFloat($(this).find(".precioUSD").text()) || 0;

                // Si ya existe el producto, sumamos sus valores
                if (!resumen[nombre]) {
                    resumen[nombre] = {
                        precioUSD: 0,
                        cantidad: 0
                    };
                }

                resumen[nombre].precioUSD += precioUSD;
                resumen[nombre].cantidad += 1;
                cantidad_total += 1;
            });

            // Limpiamos la tabla resumen
            let tbody = $("#resumen_productos tbody");
            tbody.empty();

            // Insertamos filas nuevas con los totales
            for (let nombre in resumen) {
                let r = resumen[nombre];
                tbody.append(`
                    <tr class="table-primary" data-producto="${nombre}">
                        <td>${nombre}</td>
                        <td>${r.precioUSD.toFixed(2)}</td>
                        <td class="cantidad">${r.cantidad}</td>
                    </tr>
                `);
            }

            $("#resumen_productos_cantidad_total").text(cantidad_total);
        }
    });
</script>
