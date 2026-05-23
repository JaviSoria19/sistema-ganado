<style>
    .text-dark-aquamarine {
        color: #008b8b !important;
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

        $('.idEmpresa').select2({
            language: "es",
            dropdownCssClass: "{{ session('tema_preferido') == 'dark' ? 'bg-dark' : '' }}",
            selectionCssClass: "{{ session('tema_preferido') == 'dark' ? 'bg-dark' : '' }}"
        });

        $('.idMarca').select2({
            language: "es",
            dropdownCssClass: "{{ session('tema_preferido') == 'dark' ? 'bg-dark' : '' }}",
            selectionCssClass: "{{ session('tema_preferido') == 'dark' ? 'bg-dark' : '' }}"
        });

        $(document).on('click', '.btn-imprimir-codigo', function() {
            const codigoProducto = $(this).data('codigo');


            Swal.fire({
                theme: 'auto',
                title: `¡ATENCIÓN!`,
                html: `¿Estás seguro de imprimir el código <b class="text-primary">${codigoProducto}</b>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `Sí, imprimir`,
                cancelButtonText: 'No, cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    if (usarEtiquetadoraDYMO === 'SI') {
                        DYMO_imprimirCodigoProducto(codigoProducto);
                    } else {
                        Swal.fire({
                            theme: 'auto',
                            title: "No permitido",
                            text: "Se ha deshabilitado el uso de la etiquetadora DYMO desde los parámetros del sistema.",
                            icon: "warning"
                        });
                    }
                }
            });
        });

        $(document).on('click', '.btn-imprimir-codigos-disponibles', function() {
            Swal.fire({
                theme: 'auto',
                title: `¡ATENCIÓN!`,
                html: `¿Estás seguro de imprimir los códigos de <b class="text-success">todos los productos disponibles</b>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `Sí, imprimir`,
                cancelButtonText: 'No, cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    if (usarEtiquetadoraDYMO === 'SI') {
                        $('#productos tr.table-success').each(function() {
                            const codigoProducto = $(this).find('.codigoProducto')
                                .text()
                                .trim();
                            DYMO_imprimirCodigoProducto(codigoProducto);
                        });
                    } else {
                        Swal.fire({
                            theme: 'auto',
                            title: "No permitido",
                            text: "Se ha deshabilitado el uso de la etiquetadora DYMO desde los parámetros del sistema.",
                            icon: "warning"
                        });
                    }

                }
            });
        });

        $(document).on('click', '.btn-guardar-cambios', function() {
            Swal.fire({
                theme: 'auto',
                title: `¡ATENCIÓN!`,
                html: `¿Estás seguro de guardar los cambios realizados en los productos?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `Sí, guardar`,
                cancelButtonText: 'No, cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    guardarCambios();
                }
            });
        });
    });

    function guardarCambios() {
        const btnGuardar = document.getElementById('btnGuardar');
        const idAbastecimiento = '{{ $abastecimiento->idAbastecimiento }}';
        let productos = [];
        $("#productos tbody tr").each(function() {
            const fila = $(this);
            if (fila.hasClass("table-success")) { // Solo disponibles
                const idProducto = fila.find(".idProducto").text().trim();
                const idEmpresa = fila.find(".idEmpresa").val();
                const idMarca = fila.find(".idMarca").val();
                const nombreProducto = fila.find(".nombreProducto").text().trim();
                const color = fila.find(".color").text().trim();
                const identificador = fila.find(".identificador").text().trim();
                const costoBaseUSD = parseFloat(fila.find(".costoBaseUSD").text().trim()) ||
                    0;
                const traspasoPorcentaje = parseFloat(fila.find(".traspasoPorcentaje")
                    .text().trim()) || 0;
                const transporteUSD = parseFloat(fila.find(".transporteUSD").text()
                    .trim()) || 0;
                const precioVentaUSD = parseFloat(fila.find(".precioVentaUSD").text().trim()) ||
                    0;

                productos.push({
                    idProducto: idProducto,
                    idEmpresa: idEmpresa,
                    idMarca: idMarca,
                    nombreProducto: nombreProducto,
                    color: color,
                    identificador: identificador,
                    costoBaseUSD: costoBaseUSD,
                    traspasoPorcentaje: traspasoPorcentaje,
                    transporteUSD: transporteUSD,
                    precioVentaUSD: precioVentaUSD
                });
            }
        });

        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<i class="fa-duotone fa-solid fa-loader fa-spin"></i> Guardando...';

        $.ajax({
            url: "{{ route('abastecimientos.update', $abastecimiento->idAbastecimiento) }}",
            type: 'PUT',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                idAbastecimiento: idAbastecimiento,
                productos: productos
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire('Éxito', response.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                    btnGuardar.disabled = false;
                    btnGuardar.innerHTML =
                        '<i class="fa-solid fa-duotone fa-save"></i> Guardar cambios';
                }
            },
            error: function(xhr) {
                btnGuardar.disabled = false;
                btnGuardar.innerHTML =
                    '<i class="fa-solid fa-duotone fa-save"></i> Guardar cambios';

                console.error(xhr.responseText);
                console.error(JSON.parse(xhr.responseText));

                Swal.fire('Error', 'Ocurrió un error al guardar los cambios.', 'error');
            }
        });
    }

    $(document).ready(function() {
        const tabla = $("#productos");

        tabla.on("input", ".costoBaseUSD, .traspasoPorcentaje, .transporteUSD, .precioVentaUSD", function() {
            let valor = $(this).text();

            // Validar que sea numérico
            if (isNaN(valor) || valor.trim() === "") {
                $(this).text("0");
            }
        });

        tabla.on("blur", ".nombreProducto", function() {
            let valor = $(this).text().trim();

            if (valor === "") {
                $(this).text("PRODUCTO SIN NOMBRE");
            } else {
                $(this).text(valor.toUpperCase());
            }
        });

        tabla.on("blur", ".identificador", function() {
            let valor = $(this).text().trim();

            if (valor === "" || valor.length < 3) {
                $(this).text("N/A");
            } else {
                $(this).text(valor.toUpperCase());
            }
        });

        tabla.on("keydown", ".identificador", function(e) {
            if (e.which === 13) { // ENTER
                e.preventDefault();
                let celdas = $(".identificador");
                let indice = celdas.index(this);
                if (indice >= 0 && indice < celdas.length - 1 && celdas.eq(indice + 1).is(
                        "[contenteditable=true]")) {
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

        // Aplicar hacia abajo cambios en celdas editables
        tabla.on("blur", ".costoBaseUSD, .traspasoPorcentaje, .transporteUSD, .precioVentaUSD", function() {
            const celda = $(this);
            const valor = celda.text().trim();
            const clase = celda.attr("class").split(" ").find(c => ["costoBaseUSD",
                "traspasoPorcentaje", "transporteUSD", "precioVentaUSD"
            ].includes(c));

            if (!clase) return;

            const filaActual = celda.closest("tr");
            const filas = filaActual.nextAll("tr");

            filas.each(function() {
                const fila = $(this);
                if (fila.hasClass("table-success")) { // Solo disponibles
                    const celdaDestino = fila.find("." + clase);
                    if (celdaDestino.is("[contenteditable=true]")) {
                        celdaDestino.text(valor);
                    }
                }
            });
        });

        // Aplicar hacia abajo cambios en los selects
        tabla.on("change", ".idEmpresa, .idMarca", function() {
            const select = $(this);
            const clase = select.hasClass("idEmpresa") ? "idEmpresa" : "idMarca";
            const valor = select.val();
            const texto = select.find("option:selected").text();

            const filaActual = select.closest("tr");
            const filas = filaActual.nextAll("tr");

            filas.each(function() {
                const fila = $(this);
                if (fila.hasClass("table-success")) { // Solo disponibles
                    const selectDestino = fila.find("." + clase);
                    if (selectDestino.length) {
                        selectDestino.val(valor);
                        // actualiza el texto visible si usas select2 o similar
                        selectDestino.val(valor).trigger('change.select2');
                    }
                }
            });
        });
    });
</script>
