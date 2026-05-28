<!-- Modal para crear y editar entores -->
<div class="modal fade" id="modal-formulario" tabindex="-1" aria-labelledby="modal-formulario-titulo" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modal-formulario-titulo">
                    <i class="fa-solid fa-duotone fa-plus"></i> CREAR ENTORE
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-crear-o-editar">
                    <!-- Input oculto para id en caso de editar -->
                    <input type="hidden" name="id_entore" value="0">

                    <!-- Tipo de entore -->
                    <div class="mb-3">
                        <label for="tipo_entore" class="form-label">
                            Tipo de entore <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="tipo_entore" name="tipo_entore" required>
                            <option value="" disabled selected>Seleccione el tipo de entore</option>
                            <option value="unitoro">Unitoro</option>
                            <option value="multitoro">Multitoro</option>
                            <option value="inseminacion">Inseminación</option>
                        </select>
                    </div>

                    <!-- Fechas -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha_inicio" class="form-label">
                                Fecha inicio <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fecha_fin" class="form-label">Fecha fin</label>
                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
                        </div>
                    </div>

                    <!-- Código pajuela (solo inseminación) -->
                    <div class="mb-3" id="campo-codigo-pajuela" style="display:none;">
                        <label for="codigo_pajuela" class="form-label">Código de pajuela</label>
                        <input type="text" class="form-control" id="codigo_pajuela" name="codigo_pajuela"
                            maxlength="50">
                    </div>

                    <!-- Macho principal (unitoro / inseminación) -->
                    <div class="mb-3" id="campo-macho-principal" style="display:none;">
                        <label for="id_macho" class="form-label">Macho principal</label>
                        <select class="form-select" id="id_macho" name="id_macho">
                            <option value="">— Sin asignar —</option>
                        </select>
                    </div>

                    <!-- Machos (multitoro): lista dinámica -->
                    <div class="mb-3" id="campo-machos-multitoro" style="display:none;">
                        <label class="form-label">Machos <span class="text-danger">*</span></label>
                        <div id="lista-machos">
                            <!-- Filas dinámicas -->
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="btn-agregar-macho">
                            <i class="fa-solid fa-plus"></i> Agregar macho
                        </button>
                    </div>

                    <!-- Hembras: lista dinámica -->
                    <div class="mb-3">
                        <label class="form-label">Hembras <span class="text-danger">*</span></label>
                        <div id="lista-hembras">
                            <!-- Filas dinámicas -->
                        </div>
                        <button type="button" class="btn btn-outline-danger btn-sm mt-2" id="btn-agregar-hembra">
                            <i class="fa-solid fa-plus"></i> Agregar hembra
                        </button>
                    </div>

                    <!-- Observaciones -->
                    <div class="mb-3">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="3" maxlength="250"></textarea>
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
