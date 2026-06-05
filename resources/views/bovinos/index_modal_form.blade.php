<!-- Modal para crear y editar -->
<div class="modal fade modal-xl" id="modal-formulario" tabindex="-1" aria-labelledby="modal-formulario-titulo"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modal-formulario-titulo"><i class="fa-solid fa-duotone fa-plus"></i>
                    CREAR ÁREA</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-crear-o-editar">
                    <input type="hidden" name="id_bovino" value="0">

                    {{-- Fila 1: Potrero y Origen --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="id_potrero" class="form-label">Potrero <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="id_potrero" name="id_potrero" required>
                                <option value="" disabled selected>Seleccione un potrero</option>
                                @foreach ($potreros as $potrero)
                                    <option value="{{ $potrero->id_potrero }}">{{ $potrero->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="origen" class="form-label">Origen <span class="text-danger">*</span></label>
                            <select class="form-select" id="origen" name="origen" required>
                                <option value="" disabled selected>Seleccione un origen</option>
                                <option value="criollo">Criollo</option>
                                <option value="comprado">Comprado</option>
                                <option value="prestado">Prestado</option>
                            </select>
                        </div>
                    </div>

                    {{-- Fila 2: Identificador y Fecha de nacimiento --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="identificador" class="form-label">Identificador <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="identificador" name="identificador"
                                maxlength="25" placeholder="Ej: 5001" required>
                            <div class="form-text">El carimbo (año) se genera automáticamente desde la fecha de
                                nacimiento.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fecha_nacimiento" class="form-label">Fecha de nacimiento <span
                                    class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento"
                                required>
                        </div>
                    </div>

                    {{-- Fila 3: Género y Entore --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="genero" class="form-label">Género <span class="text-danger">*</span></label>
                            <select class="form-select" id="genero" name="genero" required>
                                <option value="" disabled selected>Seleccione un género</option>
                                <option value="macho">Macho</option>
                                <option value="hembra">Hembra</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="id_entore" class="form-label">Entore</label>
                            <select class="form-select select2" id="id_entore" name="id_entore">
                                <option value="">Sin entore</option>
                            </select>
                            <div class="form-text">Solo si es multitoro o inseminación.</div>
                        </div>
                    </div>

                    {{-- Fila 4: Padre y Madre --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="id_padre" class="form-label">Padre</label>
                            <select class="form-select select2" id="id_padre" name="id_padre">
                            </select>
                            <div class="form-text">Solo si es unitoro.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="id_madre" class="form-label">Madre</label>
                            <select class="form-select select2" id="id_madre" name="id_madre">
                                <option value="">Sin registro</option>
                            </select>
                        </div>
                    </div>

                    {{-- Fila 5: Pesos y Fecha de destete --}}
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="peso_nacimiento" class="form-label">Peso nacimiento (kg) <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="peso_nacimiento" name="peso_nacimiento"
                                min="0" max="99.99" step="0.01" placeholder="0.00" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="peso_destete" class="form-label">Peso destete (kg) <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="peso_destete" name="peso_destete"
                                min="0" max="999.99" step="0.01" placeholder="0.00" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="fecha_destete" class="form-label">Fecha de destete</label>
                            <input type="date" class="form-control" id="fecha_destete" name="fecha_destete">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="peso_actual" class="form-label">Peso actual (kg) <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="peso_actual" name="peso_actual"
                                min="0" max="9999.99" step="0.01" placeholder="0.00" required>
                        </div>
                    </div>

                    {{-- Fila 6: Colores --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="color_nacimiento" class="form-label">Color al nacimiento <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="color_nacimiento" name="color_nacimiento"
                                maxlength="45" placeholder="Ej: negro" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="color_actual" class="form-label">Color actual <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="color_actual" name="color_actual"
                                maxlength="45" placeholder="Ej: negro con blanco" required>
                        </div>
                    </div>

                    {{-- Fila 7: Identificadores físicos --}}
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" role="switch"
                                    id="tiene_identificador_oreja" name="tiene_identificador_oreja" value="1">
                                <label class="form-check-label" for="tiene_identificador_oreja">
                                    Tiene identificador de oreja
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" role="switch"
                                    id="tiene_identificador_lomo" name="tiene_identificador_lomo" value="1">
                                <label class="form-check-label" for="tiene_identificador_lomo">
                                    Tiene identificador de lomo
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="estado_corporal" class="form-label">Estado corporal</label>
                            <input type="number" class="form-control" id="estado_corporal" name="estado_corporal"
                                min="0" max="15" step="1" placeholder="0 - 15" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="seleccion" class="form-label">Selección</label>
                            <input type="text" class="form-control" id="seleccion" name="seleccion"
                                maxlength="100" required>
                        </div>
                    </div>

                    {{-- Fila 8: Fecha de salida (solo en edición) --}}
                    <div class="row" id="row-fecha-salida" style="display: none !important;">
                        <div class="col-md-6 mb-3">
                            <label for="fecha_salida" class="form-label">Fecha de salida</label>
                            <input type="date" class="form-control" id="fecha_salida" name="fecha_salida">
                        </div>
                    </div>

                    {{-- Observaciones --}}
                    <div class="mb-3">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="2" maxlength="250"
                            placeholder="Observaciones adicionales..."></textarea>
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
