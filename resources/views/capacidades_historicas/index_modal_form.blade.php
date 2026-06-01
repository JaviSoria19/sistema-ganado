<!-- Modal para crear y editar -->
<div class="modal fade" id="modal-formulario" tabindex="-1" aria-labelledby="modal-formulario-titulo" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modal-formulario-titulo">
                    <i class="fa-solid fa-duotone fa-plus"></i> CREAR CAPACIDAD HISTÓRICA
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-crear-o-editar">
                    <!-- Input oculto para id en caso de editar -->
                    <input type="hidden" name="id_capacidad_historica" value="0">

                    <div class="mb-3">
                        <label for="id_potrero" class="form-label">
                            Potrero <span class="text-danger">*</span>
                        </label>
                        <select class="form-select select2" id="id_potrero" name="id_potrero" required>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="capacidad_carga" class="form-label">
                            Capacidad de carga (ua) <span class="text-danger">*</span>
                        </label>
                        <input type="number" step="0.01" class="form-control" id="capacidad_carga" name="capacidad_carga"
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
