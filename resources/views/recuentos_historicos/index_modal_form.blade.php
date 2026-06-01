<!-- Modal para crear y editar -->
<div class="modal fade" id="modal-formulario" tabindex="-1" aria-labelledby="modal-formulario-titulo" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modal-formulario-titulo">
                    <i class="fa-solid fa-duotone fa-plus"></i> CREAR RECUENTO HISTÓRICO
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-crear-o-editar">
                    <!-- Input oculto para id en caso de editar -->
                    <input type="hidden" name="id_recuento_historico" value="0">

                    <div class="mb-3">
                        <label for="id_bovino" class="form-label">
                            Bovino <span class="text-danger">*</span>
                        </label>
                        <select class="form-select select2" id="id_bovino" name="id_bovino" required>
                            <option value="" disabled selected>Seleccione el bovino</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="estado_recuento" class="form-label">
                            Estado del recuento <span class="text-danger">*</span>
                        </label>
                        <input type="number" step="1" class="form-control" id="estado_recuento" name="estado_recuento"
                            required>
                    </div>

                    <div class="mb-3">
                        <label for="fecha" class="form-label">
                            Fecha <span class="text-danger">*</span>
                        </label>
                        <input type="date" class="form-control" id="fecha" name="fecha" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fa-solid fa-duotone fa-close"></i> Cerrar
                </button>
                <button type="button" id="btn-guardar" class="btn btn-primary">
                    <i class="fa-solid fa-duotone fa-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>
