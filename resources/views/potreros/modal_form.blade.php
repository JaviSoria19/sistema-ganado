<!-- Modal para crear y editar -->
<div class="modal fade" id="modal-formulario" tabindex="-1" aria-labelledby="modal-formulario-titulo" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modal-formulario-titulo"><i class="fa-solid fa-duotone fa-plus"></i>
                    CREAR ÁREA</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-crear-o-editar">
                    <!-- input de id_potrero en caso de editar -->
                    <input type="hidden" name="id_potrero" value="0">

                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre" name="nombre" max="100" required>
                    </div>

                    <div class="mb-3">
                        <label for="ubicacion" class="form-label">Ubicación <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ubicacion" name="ubicacion" max="255" required>
                    </div>

                    <div class="mb-3">
                        <label for="superficie" class="form-label">Superficie (ha) <span
                                class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" id="superficie" name="superficie"
                            required>
                    </div>

                    <div class="mb-3">
                        <label for="tipo_pasto" class="form-label">Tipo de pasto <span
                                class="text-danger">*</span></label>
                        <select class="form-select" id="tipo_pasto" name="tipo_pasto" required>
                            <option value="" disabled selected>Seleccione un tipo de pasto</option>
                            <option value="Pasto natural">Pasto natural</option>
                            <option value="Pasto cultivado">Pasto cultivado</option>
                            <option value="Mezcla de pastos">Mezcla de pastos</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="estado_potrero" class="form-label">Estado del potrero<span
                                class="text-danger">*</span></label>
                        <select class="form-select" id="estado_potrero" name="estado_potrero" required>
                            <option value="" disabled selected>Seleccione el estado del potrero</option>
                            <option value="Excelente">Excelente</option>
                            <option value="Bueno">Bueno</option>
                            <option value="Regular">Regular</option>
                            <option value="Malo">Malo</option>
                            <option value="Pésimo">Pésimo</option>
                            <option value="Solo hierba">Solo hierba</option>
                            <option value="Solo agua">Solo agua</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="disponibilidad_agua" class="form-label">Disponibilidad de agua<span
                                class="text-danger">*</span></label>
                        <select class="form-select" id="disponibilidad_agua" name="disponibilidad_agua" required>
                            <option value="" disabled selected>Seleccione la disponibilidad de agua</option>
                            <option value="Excelente">Excelente</option>
                            <option value="Bueno">Bueno</option>
                            <option value="Regular">Regular</option>
                            <option value="Malo">Malo</option>
                            <option value="Pésimo">Pésimo</option>
                            <option value="Solo en época de lluvia">Solo en época de lluvia</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="capacidad_carga_actual" class="form-label">Capacidad de carga actual (ua) <span
                                class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" id="capacidad_carga_actual" name="capacidad_carga_actual" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i
                        class="fa-solid fa-duotone fa-close"></i>Cerrar</button>
                <button type="submit" id="btn-guardar" form="form-crear-o-editar" class="btn btn-primary">
                    Guardar
                </button>
            </div>
        </div>
    </div>
</div>
