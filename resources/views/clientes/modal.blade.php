<!-- Modal para crear y editar clientes -->
<div class="modal fade" id="modalCreateOrEdit" tabindex="-1" aria-labelledby="modalCreateOrEdit_Title" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modalCreateOrEdit_Title"><i class="fa-solid fa-duotone fa-plus"></i>
                    CREAR CLIENTE</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formCreateOrEdit">
                    <!-- input de idCliente en caso de editar -->
                    <input type="hidden" name="idCliente" value="0">

                    <div class="mb-3">
                        <label for="nombreCliente" class="form-label">Nombre de cliente <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombreCliente" name="nombreCliente" required>
                    </div>

                    <div class="mb-3">
                        <label for="celular" class="form-label">Celular <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="celular" name="celular" required>
                    </div>

                    <div class="mb-3">
                        <label for="cedulaIdentidad" class="form-label">Cédula de Identidad <span
                                class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="cedulaIdentidad" name="cedulaIdentidad" required>
                    </div>

                    <div class="mb-3">
                        <label for="procedencia" class="form-label">Procedencia <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="procedencia" name="procedencia" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i
                        class="fa-solid fa-duotone fa-close"></i>Cerrar</button>
                <button type="button" id="btnGuardar" class="btn btn-primary"><i
                        class="fa-solid fa-duotone fa-save"></i>
                    Guardar</button>
            </div>
        </div>
    </div>
</div>
