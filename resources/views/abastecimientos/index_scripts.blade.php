<style>
    .text-dark-aquamarine {
        color: #20c997 !important;
    }
</style>
<!-- DYMO connect framework  -->
<script src="{{ asset('/public/dependencies/dymo-connect-framework/dymo.connect.framework.full.js') }}"></script>
<script>
    $(document).ready(function() {
        const usarEtiquetadoraDYMO = '{{ session('usarEtiquetadoraDYMO') }}';

        if (usarEtiquetadoraDYMO === 'SI') {
            // Verificar si el objeto dymo existe
            if (typeof dymo === 'undefined') {
                console.error("El objeto DYMO no está cargado. Verifica la ruta del script.");
                return;
            }

            // Inicialización con callback
            dymo.label.framework.init(function() {
                console.log("✓ Framework DYMO inicializado correctamente");

                // Verificar ambiente con más detalle
                const dymo_entorno = dymo.label.framework.checkEnvironment();
                console.log("Framework instalado:", dymo_entorno.isFrameworkInstalled);
                console.log("WebService presente:", dymo_entorno.isWebServicePresent);

                // Verificar puerto del servicio
                if (dymo_entorno.webServicePort) {
                    console.log("Puerto del WebService:", dymo_entorno.webServicePort);
                }

                // Validaciones de ambiente
                if (!dymo_entorno.isFrameworkInstalled) {
                    Swal.fire({
                        theme: 'auto',
                        title: "¡Atención!",
                        text: "Por favor instale DYMO Label Software (DLS) o DYMO Connect for Desktop (DCD)",
                        icon: "error"
                    });
                    return;
                }

                if (!dymo_entorno.isWebServicePresent) {
                    Swal.fire({
                        theme: 'auto',
                        title: "¡Atención!",
                        html: `El servicio web DYMO no está corriendo.<br><br>
                       <strong>Solución:</strong><br>
                       1. Abre DYMO Connect o DYMO Label Software<br>
                       2. Asegúrate de que el servicio esté activo<br>
                       3. Recarga esta página`,
                        icon: "error"
                    });
                    return;
                }

                // Intentar obtener impresoras con manejo de errores
                try {
                    const dymo_printers = dymo.label.framework.getPrinters();
                    console.log(dymo_printers);
                    console.log("Número de impresoras encontradas:", dymo_printers.length);

                    if (dymo_printers.length === 0) {
                        console.warn("⚠ No se encontraron impresoras DYMO");

                        // Verificar si hay impresoras del sistema
                        const allPrinters = dymo.label.framework.getPrinters();
                        console.log("Todas las impresoras:", allPrinters);

                        Swal.fire({
                            theme: 'auto',
                            title: "Sin impresoras DYMO",
                            html: `No se detectaron impresoras DYMO conectadas.<br><br>
                           <strong>Verifica:</strong><br>
                           • La impresora está encendida<br>
                           • El cable USB está conectado<br>
                           • Los drivers están instalados<br>
                           • Windows reconoce la impresora`,
                            icon: "warning"
                        });
                    } else {
                        console.log("✓ Impresoras DYMO encontradas:");
                        dymo_printers.forEach(function(printer, index) {
                            console.log(`  ${index + 1}. ${printer.name}`);
                            console.log(`     Modelo: ${printer.modelName}`);
                            console.log(
                                `     Estado: ${printer.isConnected ? '🟢 Conectada' : '🔴 Desconectada'}`
                            );
                            console.log(`     Local: ${printer.isLocal ? 'Sí' : 'No'}`);
                            console.log(
                                `     Twin Turbo: ${printer.isTwinTurbo ? 'Sí' : 'No'}`);
                        });

                        // Guardar referencia a la primera impresora
                        window.dymo_impresora = dymo_printers[0].name;
                        console.log("✓ Impresora seleccionada:", window.dymo_impresora);
                    }

                } catch (error) {
                    console.error("❌ Error al obtener impresoras:", error);
                    console.error("Detalles del error:", error.message);
                    console.error("Stack:", error.stack);

                    Swal.fire({
                        theme: 'auto',
                        title: "Error al detectar impresoras",
                        html: `Ocurrió un error: <code>${error.message}</code><br><br>
                       Revisa la consola del navegador para más detalles.`,
                        icon: "error"
                    });
                }
            });
        }

        // XML de etiqueta
        const dymo_labelXml =
            `<?xml version="1.0" encoding="utf-8"?> <DieCutLabel Version="8.0" Units="twips"> <PaperOrientation>Portrait</PaperOrientation> <Id>Small30332</Id> <PaperName>30332 1 in x 1 in</PaperName> <DrawCommands> <RoundRectangle X="0" Y="0" Width="1440" Height="1440" Rx="180" Ry="180" /> </DrawCommands> <ObjectInfo> <TextObject> <Name>lblCodigoProducto</Name> <ForeColor Alpha="255" Red="0" Green="0" Blue="0" /> <BackColor Alpha="0" Red="255" Green="255" Blue="255" /> <LinkedObjectName></LinkedObjectName> <Rotation>Rotation0</Rotation> <IsMirrored>False</IsMirrored> <IsVariable>False</IsVariable> <HorizontalAlignment>Center</HorizontalAlignment> <VerticalAlignment>Middle</VerticalAlignment> <TextFitMode>AlwaysFit</TextFitMode> <UseFullFontHeight>True</UseFullFontHeight> <Verticalized>False</Verticalized> <StyledText> <Element> <String>PL1-1-1</String> <Attributes> <Font Family="Arial" Size="12" Bold="False" Italic="False" Underline="False" Strikeout="False" /> <ForeColor Alpha="255" Red="0" Green="0" Blue="0" /> </Attributes> </Element> </StyledText> </TextObject> <Bounds X="344.621544893498" Y="252.042954757161" Width="763.056631892695" Height="170.611028315945" /> </ObjectInfo> <ObjectInfo> <BarcodeObject> <Name>lblCodigoQR</Name> <ForeColor Alpha="255" Red="0" Green="0" Blue="0" /> <BackColor Alpha="0" Red="255" Green="255" Blue="255" /> <LinkedObjectName></LinkedObjectName> <Rotation>Rotation0</Rotation> <IsMirrored>False</IsMirrored> <IsVariable>False</IsVariable> <Text>PL1-1-1</Text> <Type>QRCode</Type> <Size>Medium</Size> <TextPosition>None</TextPosition> <TextFont Family="Arial" Size="8" Bold="False" Italic="False" Underline="False" Strikeout="False" /> <CheckSumFont Family="Arial" Size="8" Bold="False" Italic="False" Underline="False" Strikeout="False" /> <TextEmbedding>None</TextEmbedding> <ECLevel>0</ECLevel> <HorizontalAlignment>Center</HorizontalAlignment> <QuietZonesPadding Left="0" Top="0" Right="0" Bottom="0" /> </BarcodeObject> <Bounds X="82" Y="360" Width="1301" Height="720" /> </ObjectInfo> </DieCutLabel>`;

        function DYMO_imprimirCodigoProducto(codigoProducto) {
            try {
                if (!window.dymo_impresora) {
                    Swal.fire({
                        theme: 'auto',
                        title: "Error",
                        text: "No hay impresora DYMO configurada",
                        icon: "error"
                    });
                    return;
                }
                let label = dymo.label.framework.openLabelXml(dymo_labelXml);
                label.setObjectText("lblCodigoProducto", codigoProducto);
                label.setObjectText("lblCodigoQR", codigoProducto);

                // Usar printLabel con parámetros personalizados
                const printParams = dymo.label.framework.createLabelWriterPrintParamsXml({
                    copies: 1,
                    jobTitle: `Etiqueta_${codigoProducto}`,
                    flowDirection: dymo.label.framework.FlowDirection.LeftToRight,
                    printQuality: dymo.label.framework.LabelWriterPrintQuality.BarcodeAndGraphics,
                    twinTurboRoll: dymo.label.framework.TwinTurboRoll.Auto
                });

                label.print(window.dymo_impresora, printParams);
                console.log(`✓ Etiqueta impresa: ${codigoProducto}`);
            } catch (error) {
                console.error("Error al imprimir:", error);
                Swal.fire({
                    theme: 'auto',
                    title: "Error de impresión",
                    text: error.message,
                    icon: "error"
                });
            }
        }

        $("#dataTable").DataTable({
            processing: true,
            ajax: {
                url: "{{ route('abastecimientos.listar') }}", // Ruta de Laravel
                type: "GET",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                error: function(xhr, error, thrown) {
                    console.error("Error al cargar los datos:", error);
                }
            },
            columns: [{
                    data: "idAbastecimiento"
                },
                {
                    data: "productos",
                    render: function(data, type, row) {
                        if (!data || data.length === 0) {
                            return "-";
                        }

                        return data.map((producto, index) => {
                            const costoFinalUSD = (parseFloat(producto.costoBaseUSD) + (
                                producto.costoBaseUSD * producto
                                .traspasoPorcentaje / 100) + parseFloat(producto
                                .transporteUSD)).toFixed(2);
                            const bonoMarcaUSD = (parseFloat(producto.costoBaseUSD) *
                                producto.marca.bonoMarcaPorcentaje / 100).toFixed(2);
                            const bonoEmpresaUSD = (parseFloat(producto.costoBaseUSD) *
                                    producto.empresa.bonoEmpresaPorcentaje / 100)
                                .toFixed(2);
                            return `<span class="text-primary fw-bold">● ${index + 1}.</span> <span class="text-info fw-bold">${producto.empresa.nombreEmpresa}</span> - ${producto.marca.nombreMarca} ${producto.nombreProducto}:
                                <br>
                                <span class="fw-bold">${producto.codigoProducto}</span> | <span class="text-danger fw-bold">${producto.identificador}</span>
                                <br>
                                <span class="text-success fw-bold">Costo base: ${producto.costoBaseUSD} USD</span>,
                                <br> 
                                Traspaso: ${producto.traspasoPorcentaje}% - ${(producto.costoBaseUSD * producto.traspasoPorcentaje / 100).toFixed(2)} USD, 
                                Transporte: ${producto.transporteUSD} USD, 
                                <br>
                                <span class="text-warning fw-bold">Costo Final: ${costoFinalUSD} USD</span>
                                <br>
                                <span class="text-dark-aquamarine fw-bold">Precio de venta: ${producto.precioVentaUSD} USD</span>
                                <br>
                                <span class="text-dark-aquamarine fw-bold">Bono empresa: ${producto.empresa.bonoEmpresaPorcentaje}%, ${bonoEmpresaUSD} USD</span>
                                <br>
                                <span class="text-dark-aquamarine fw-bold">Bono marca: ${producto.marca.bonoMarcaPorcentaje}%, ${bonoMarcaUSD} USD</span>`
                        }).join("<br>");
                    },
                    width: '100%'
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        if (!row.productos || row.productos.length === 0) {
                            return "-";
                        }

                        let cantidadTotal = row.productos.length;
                        let costoBaseTotal = 0;
                        let costoFinalTotal = 0;
                        let bonoEmpresaTotal = 0;
                        let bonoMarcaTotal = 0;
                        let precioVentaTotal = 0;

                        row.productos.forEach((producto) => {
                            // Sumar costo base
                            costoBaseTotal += parseFloat(producto.costoBaseUSD) || 0;

                            // Calcular y sumar costo final
                            const costoFinal = parseFloat(producto.costoBaseUSD) +
                                (producto.costoBaseUSD * producto.traspasoPorcentaje /
                                    100) +
                                parseFloat(producto.transporteUSD);
                            costoFinalTotal += costoFinal;

                            // Calcular y sumar bono empresa
                            const bonoEmpresa = (parseFloat(producto.costoBaseUSD) *
                                producto.empresa.bonoEmpresaPorcentaje / 100);
                            bonoEmpresaTotal += bonoEmpresa;

                            const bonoMarca = (parseFloat(producto.costoBaseUSD) *
                                producto.marca.bonoMarcaPorcentaje / 100);
                            bonoMarcaTotal += bonoMarca;

                            const precioVenta = parseFloat(producto.precioVentaUSD);
                            precioVentaTotal += precioVenta;
                        });

                        return `
                                <span class="text-info fw-bold">Cantidad total: ${cantidadTotal} productos</span><br>
                                <span class="text-success fw-bold">Costo base total: ${costoBaseTotal.toFixed(2)} USD</span><br>
                                <span class="text-warning fw-bold">Costo final total: ${costoFinalTotal.toFixed(2)} USD</span><br>
                                <span class="text-dark-aquamarine fw-bold">Precio de venta total: ${precioVentaTotal.toFixed(2)} USD</span><br>
                                <span class="text-dark-aquamarine fw-bold">Bono empresa total: ${bonoEmpresaTotal.toFixed(2)} USD</span><br>
                                <span class="text-dark-aquamarine fw-bold">Bono marca total: ${bonoMarcaTotal.toFixed(2)} USD</span><br>
                                <span class="text-info fw-bold">Utilidad potencial total: ${(precioVentaTotal - costoFinalTotal).toFixed(2)} USD</span>
                            `;
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
                        <a href="{{ route('abastecimientos.index') }}/${row.idAbastecimiento}/editar" class="btn btn-warning btn-sm btn-editar" data-toggle="tooltip" title="Editar" target="_blank" rel="noopener noreferrer">
                            <i class="fa-duotone fa-solid fa-edit"></i>
                        </a>
                    </div>
                `;
                    }
                }
            ],
            responsive: false,
            lengthChange: true,
            autoWidth: false,
            scrollX: true,
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

        $(document).on('click', '#btnGuardar', function() {
            const btnGuardar = document.getElementById('btnGuardar');
            let productos = [];

            $("#productos tbody tr").each(function() {
                let fila = $(this);

                let idEmpresa = fila.find('.idEmpresa').text();
                let idMarca = fila.find('.idMarca').text();
                let nombreProducto = fila.find('.nombreProducto').text().trim().toUpperCase();
                let color = fila.find('.color').text().trim().toUpperCase() || '';
                let identificador = fila.find('.identificador').text().trim().toUpperCase();
                let codigoProducto = fila.find('.codigoProducto').text();
                let costoBaseUSD = parseFloat(fila.find('.costoBaseUSD').text());
                let traspasoPorcentaje = parseFloat(fila.find('.traspasoPorcentaje').text());
                let transporteUSD = parseFloat(fila.find('.transporteUSD').text());
                let precioVentaUSD = parseFloat(fila.find('.precioVentaUSD').text());

                productos.push({
                    idEmpresa: idEmpresa,
                    idMarca: idMarca,
                    nombreProducto: nombreProducto,
                    color: color,
                    identificador: identificador,
                    codigoProducto: codigoProducto,
                    costoBaseUSD: costoBaseUSD,
                    traspasoPorcentaje: traspasoPorcentaje,
                    transporteUSD: transporteUSD,
                    precioVentaUSD: precioVentaUSD
                });
            });

            /*
            console.log(productos);
            */

            if (productos.length === 0) {
                Swal.fire({
                    theme: 'auto',
                    title: "¡No válido!",
                    text: "Debe agregar al menos un producto a la tabla.",
                    icon: "warning"
                });
                return;
            }

            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<i class="fa-duotone fa-solid fa-loader fa-spin"></i> Guardando...';

            $.ajax({
                url: "{{ route('abastecimientos.create') }}",
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    productos: productos
                },
                success: function(response) {
                    if (response.success) {
                        btnGuardar.disabled = false;
                        btnGuardar.innerHTML =
                            '<i class="fa-solid fa-duotone fa-save"></i> Guardar';

                        Swal.fire('Éxito', response.message, 'success');
                        $('#modalCreate').modal('hide');
                        $('#dataTable').DataTable().ajax.reload();

                        $("#productos tbody").empty();
                        $("#resumen_productos tbody").empty();
                        actualizarTotales();

                        if (usarEtiquetadoraDYMO === 'SI') {
                            response.abastecimiento?.productos?.forEach(function(producto) {
                                DYMO_imprimirCodigoProducto(producto
                                .codigoProducto);
                            });
                        }

                        // vaciar inputs
                        $('#empresa').val(1).trigger('change');
                        $('#marca').val(1).trigger('change');
                        $('#nombreProducto').val('');
                        $('#color').val('');
                        $('#identificador').val('');
                        $('#costoBaseUSD').val('');
                        $('#precioVentaUSD').val('');
                        $('#cantidad').val('');
                        $('#costoFinalUSD').text('0.00 USD');
                    } else {
                        btnGuardar.disabled = false;
                        btnGuardar.innerHTML =
                            '<i class="fa-solid fa-duotone fa-save"></i> Guardar';

                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    btnGuardar.disabled = false;
                    btnGuardar.innerHTML =
                        '<i class="fa-solid fa-duotone fa-save"></i> Guardar';
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

        $('.select2').select2({
            language: "es",
            dropdownCssClass: "{{ session('tema_preferido') == 'dark' ? 'bg-dark' : '' }}",
            selectionCssClass: "{{ session('tema_preferido') == 'dark' ? 'bg-dark' : '' }}",
            dropdownParent: $('#modalCreate')
        });

        // 1. Agregar con ENTER en el campo cantidad
        $("#cantidad").on("keypress", function(e) {
            if (e.which === 13) { // ENTER
                e.preventDefault();
                agregarProducto();
            }
        });

        // También con el botón
        $("#btnAdd").on("click", function() {
            agregarProducto();
        });

        $("#btnEmptyTable").on("click", function() {
            Swal.fire({
                theme: 'auto',
                title: `¡ATENCIÓN!`,
                text: `¿Estás seguro de vaciar la tabla de productos?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `Sí, vaciar`,
                cancelButtonText: 'No, cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#productos tbody").empty();
                    $("#resumen_productos tbody").empty();
                    actualizarTotales();

                    Swal.fire({
                        theme: 'auto',
                        icon: "success",
                        title: "¡Hecho!",
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            });
        });

        function agregarProducto() {
            let empresa = $("#empresa option:selected").text().trim();
            let idEmpresa = $("#empresa option:selected").val();
            let marca = $("#marca option:selected").text().trim();
            let idMarca = $("#marca option:selected").val();
            let nombreProducto = $("#nombreProducto").val().trim();
            let color = $("#color").val().trim();
            let identificador = $("#identificador").val().trim() || 'N/A';
            let costoBase = parseFloat($("#costoBaseUSD").val());
            let traspasoPorcentaje = parseFloat($("#traspasoPorcentaje").val());
            let transporteUSD = parseFloat($("#transporteUSD").val());
            let precioVentaUSD = parseFloat($("#precioVentaUSD").val());
            let cantidad = parseInt($("#cantidad").val());

            if (!empresa || !marca || !nombreProducto || isNaN(costoBase) || isNaN(traspasoPorcentaje) || isNaN(
                    transporteUSD) || isNaN(precioVentaUSD) || isNaN(cantidad)) {
                Swal.fire({
                    theme: 'auto',
                    title: "¡No válido!",
                    text: "Complete todos los campos obligatorios.",
                    icon: "warning"
                });
                return;
            }

            // punto de inicio: cuántas filas hay ya
            let filasExistentes = $("#productos tbody tr").length;

            // 2. Insertar N filas según cantidad
            for (let i = 0; i < cantidad; i++) {
                let numeroFila = filasExistentes + i + 1;

                // 3. Generar código: 2 letras empresa + 2 letras marca + fecha/hora + correlativo
                let ahora = new Date();
                /*let fechaHora = ahora.getFullYear() +
                    String(ahora.getMonth() + 1).padStart(2, "0") +
                    String(ahora.getDate()).padStart(2, "0") + "-" +
                    String(ahora.getHours()).padStart(2, "0") +
                    String(ahora.getMinutes()).padStart(2, "0");*/
                let fechaHora = ahora.getFullYear() +
                    String(ahora.getMonth() + 1).padStart(2, "0") +
                    String(ahora.getDate()).padStart(2, "0");

                /*let codigo = empresa.substring(0, 2).toUpperCase() + "-" +
                    marca.substring(0, 2).toUpperCase() + "-" +
                    fechaHora + "-" +
                    String(numeroFila).padStart(2, "0");*/
                let codigo = fechaHora + "-" + String(numeroFila).padStart(2, "0");

                let costoTraspasoUSD = (costoBase * traspasoPorcentaje / 100).toFixed(2);
                let costoFinalUSD = (costoBase + parseFloat(costoTraspasoUSD) + transporteUSD).toFixed(2);

                let fila = `
                        <tr>
                            <td class="numero">${numeroFila}</td>
                            <td class="visually-hidden idEmpresa">${idEmpresa}</td>
                            <td class="visually-hidden idMarca">${idMarca}</td>
                            <td class="empresa">${empresa}</td>
                            <td class="marca">${marca}</td>
                            <td class="nombreProducto" contenteditable="true">${nombreProducto.toUpperCase()}</td>
                            <td class="color" contenteditable="true">${color.toUpperCase()}</td>
                            <td class="identificador" contenteditable="true">${identificador}</td>
                            <td class="codigoProducto">${codigo}</td>
                            <td class="costoBaseUSD">${costoBase.toFixed(2)}</td>
                            <td class="traspasoPorcentaje">${traspasoPorcentaje.toFixed(2)}</td>
                            <td class="traspasoUSD">${costoTraspasoUSD}</td>
                            <td class="transporteUSD">${transporteUSD.toFixed(2)}</td>
                            <td class="costoFinalUSD">${costoFinalUSD}</td>
                            <td class="precioVentaUSD">${precioVentaUSD.toFixed(2)}</td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm btn-remover" 
                                data-toggle="tooltip" title="Remover de la tabla">
                                <i class="fa-solid fa-duotone fa-trash-can-list"></i>
                                </button>
                            </td>
                        </tr>
                        `;

                $("#productos tbody").append(fila);
            }

            actualizarTotales();
            actualizarResumen();
            limpiarCampos(cantidad);
        }

        function actualizarResumen() {
            let resumen = {}; // objeto agrupador

            // Recorremos todas las filas de la tabla productos
            $("#productos tbody tr").each(function() {
                let empresa = $(this).find(".empresa").text().trim();
                let marca = $(this).find(".marca").text().trim();
                let nombre = $(this).find(".nombreProducto").text().trim().toUpperCase();
                let costoBase = parseFloat($(this).find(".costoBaseUSD").text()) || 0;
                let traspasoPorcentaje = parseFloat($(this).find(".traspasoPorcentaje").text()) || 0;
                let traspasoUSD = parseFloat($(this).find(".traspasoUSD").text()) || 0;
                let transporteUSD = parseFloat($(this).find(".transporteUSD").text()) || 0;
                let costoFinal = parseFloat($(this).find(".costoFinalUSD").text()) || 0;
                let precioVenta = parseFloat($(this).find(".precioVentaUSD").text()) || 0;

                // Si ya existe el producto, sumamos sus valores
                if (!resumen[nombre]) {
                    resumen[nombre] = {
                        empresa,
                        marca,
                        costoBase: 0,
                        traspasoPorcentaje,
                        traspasoUSD: 0,
                        transporteUSD: 0,
                        costoFinalUSD: 0,
                        precioVentaUSD: 0,
                        cantidad: 0
                    };
                }

                resumen[nombre].costoBase += costoBase;
                resumen[nombre].traspasoUSD += traspasoUSD;
                resumen[nombre].transporteUSD += transporteUSD;
                resumen[nombre].costoFinalUSD += costoFinal;
                resumen[nombre].precioVentaUSD += precioVenta;
                resumen[nombre].cantidad += 1;
            });

            // Limpiamos la tabla resumen
            let tbody = $("#resumen_productos tbody");
            tbody.empty();

            // Insertamos filas nuevas con los totales
            for (let nombre in resumen) {
                let r = resumen[nombre];
                tbody.append(`
            <tr class="table-primary" data-producto="${nombre}">
                <td>${r.empresa}</td>
                <td>${r.marca}</td>
                <td>${nombre}</td>
                <td>${r.costoBase.toFixed(2)}</td>
                <td>${r.traspasoPorcentaje.toFixed(2)}</td>
                <td>${r.traspasoUSD.toFixed(2)}</td>
                <td>${r.transporteUSD.toFixed(2)}</td>
                <td>${r.costoFinalUSD.toFixed(2)}</td>
                <td>${r.precioVentaUSD.toFixed(2)}</td>
                <td class="cantidad">${r.cantidad}</td>
            </tr>
        `);
            }
        }


        // 4. Actualizar totales
        function actualizarTotales() {
            let totalCantidad = $("#productos tbody tr").length;
            let totalCostoBase = 0;
            let totalCostoFinal = 0;

            $("#productos tbody tr").each(function() {
                totalCostoBase += parseFloat($(this).find(".costoBaseUSD").text());
                totalCostoFinal += parseFloat($(this).find(".costoFinalUSD").text());
            });

            $("#productosTotalCantidad").text(totalCantidad);
            $("#productosCostoBaseTotalUSD").text(totalCostoBase.toFixed(2));
            $("#productosCostoFinalTotalUSD").text(totalCostoFinal.toFixed(2));
        }

        // Eliminar fila
        $(document).on("click", ".btn-remover", function() {
            // Obtener los datos de la fila que se eliminará
            let fila = $(this).closest("tr");
            let nombreProducto = fila.find(".nombreProducto").text().trim().toUpperCase();
            fila.remove();
            reenumerarFilas();
            actualizarTotales();
            actualizarResumen();
        });

        function reenumerarFilas() {
            $("#productos tbody tr").each(function(index) {
                let numero = index + 1;
                $(this).find(".numero").text(numero);
                let codigoActual = $(this).find(".codigoProducto").text();
                let baseCodigo = codigoActual.slice(0, -2); // quitar correlativo
                $(this).find(".codigoProducto").text(baseCodigo + String(numero).padStart(2, "0"));
            });
        }

        function limpiarCampos(cantidad) {
            $("#cantidad").val("");
            $("#identificador").val("");

            if (cantidad === 1) {
                $("#identificador").focus();
            } else {
                $("#nombreProducto").focus();
            }
        }

        $("#identificador").on("keypress", function(e) {
            if (e.which === 13) { // ENTER
                e.preventDefault();
                agregarProductoPorIdentificador();
            }
        });

        function agregarProductoPorIdentificador() {
            let identificador = $("#identificador").val().trim();
            if (!identificador) {
                Swal.fire({
                    theme: 'auto',
                    title: "¡No válido!",
                    text: "Ingrese un identificador de producto (IMEI/S.N.).",
                    icon: "warning",
                    showConfirmButton: false,
                    timer: 500
                });
                return;
            }

            // Buscar si ya existe en la tabla
            let existe = false;
            $("#productos tbody tr").each(function() {
                let filaIdentificador = $(this).find(".identificador").text().trim();
                if (filaIdentificador === identificador) {
                    existe = true;
                    return false; // salir del each
                }
            });

            if (existe) {
                Swal.fire({
                    theme: 'auto',
                    title: "¡Duplicado!",
                    text: `El producto con el identificador "${identificador}" ya está en la tabla.`,
                    icon: "warning",
                    showConfirmButton: false,
                    timer: 1000
                });
                return;
            }

            // Si no existe, agregar una fila con cantidad 1
            $("#cantidad").val(1);
            agregarProducto();

            Swal.fire({
                position: "top-end",
                title: `Producto con identificador ingresado:<br><b class="text-success">${identificador}</b>`,
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true,
            });
        }

        $(document).on("keydown", ".identificador", function(e) {
            if (e.which === 13) { // ENTER
                e.preventDefault();
                let celdas = $(".identificador");
                let indice = celdas.index(this);
                if (indice >= 0 && indice < celdas.length - 1) {
                    let siguienteCelda = celdas.eq(indice + 1);
                    siguienteCelda.focus();

                    // Seleccionar todo el contenido para contenteditable
                    let range = document.createRange();
                    range.selectNodeContents(siguienteCelda[0]);
                    let sel = window.getSelection();
                    sel.removeAllRanges();
                    sel.addRange(range);
                }
            }
        });

        $(document).on("keydown", ".nombreProducto", function(e) {
            if (e.which === 13) { // ENTER
                e.preventDefault();
                let celdas = $(".nombreProducto");
                let indice = celdas.index(this);
                if (indice >= 0 && indice < celdas.length - 1) {
                    let siguienteCelda = celdas.eq(indice + 1);
                    siguienteCelda.focus();

                    // Seleccionar todo el contenido para contenteditable
                    let range = document.createRange();
                    range.selectNodeContents(siguienteCelda[0]);
                    let sel = window.getSelection();
                    sel.removeAllRanges();
                    sel.addRange(range);
                }
            }
        });

        $(document).on("blur", ".nombreProducto", function() {
            let valor = $(this).text().trim();
            $(this).text(valor === "" ? "PRODUCTO SIN NOMBRE" : valor.toUpperCase());
        });

        $(document).on("blur", ".identificador", function() {
            let valor = $(this).text().trim();
            $(this).text((valor === "" || valor.length < 3) ? "N/A" : valor.toUpperCase());
        });

        // Aplicar hacia abajo cambios en celdas editables
        $(document).on("blur", ".costoBaseUSD, .traspasoPorcentaje, .transporteUSD, .nombreProducto",
            function() {
                const celda = $(this);
                const valor = celda.text().trim();
                const clase = celda.attr("class").split(" ").find(c => ["costoBaseUSD",
                    "traspasoPorcentaje", "transporteUSD", "nombreProducto"
                ].includes(c));

                if (!clase) return;

                const filaActual = celda.closest("tr");
                const filas = filaActual.nextAll("tr");

                filas.each(function() {
                    const fila = $(this);
                    const celdaDestino = fila.find("." + clase);
                    if (celdaDestino.is("[contenteditable=true]")) {
                        celdaDestino.text(valor);
                    }
                });

                actualizarTotales();
                actualizarResumen();
            });

        // Calcular costo final para que el usuario vea el resultado en tiempo real
        const costoBaseInput = document.getElementById('costoBaseUSD');
        const traspasoInput = document.getElementById('traspasoPorcentaje');
        const transporteInput = document.getElementById('transporteUSD');
        const costoFinalSpan = document.getElementById('costoFinalUSD');
        const precioVentaInput = document.getElementById('precioVentaUSD');

        function calcularCostoFinal() {
            const costoBase = parseFloat(costoBaseInput.value) || 0;
            const traspaso = parseFloat(traspasoInput.value) || 0;
            const transporte = parseFloat(transporteInput.value) || 0;

            const costoFinal = costoBase + (costoBase * (traspaso / 100)) + transporte;
            costoFinalSpan.textContent = `${costoFinal.toFixed(2)} USD`;

            precioVentaInput.value = costoFinal.toFixed(2);
        }

        // recalcular cada vez que cambia un input
        [costoBaseInput, traspasoInput, transporteInput].forEach(input => {
            input.addEventListener('input', calcularCostoFinal);
        });
    });
</script>
