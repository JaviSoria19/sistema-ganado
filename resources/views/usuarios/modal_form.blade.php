<!-- Modal para crear y editar usuarios -->
<div class="modal fade" id="modalCreateOrEdit" tabindex="-1" aria-labelledby="modalCreateOrEdit_Title" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modalCreateOrEdit_Title"><i class="fa-solid fa-duotone fa-plus"></i>
                    CREAR USUARIO</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-crear-o-editar">
                    <!-- input de id_usuario en caso de editar -->
                    <input type="hidden" name="id_usuario" value="0">

                    <div class="mb-3">
                        <label for="usuario" class="form-label">Nombre de usuario <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="usuario" name="usuario" required>
                    </div>

                    <div class="mb-3">
                        <label for="tema_preferido" class="form-label">Tema preferido <span
                                class="text-danger">*</span></label><br>
                        <select style="width: 100%" class="form-select" id="tema_preferido" name="tema_preferido"
                            required>
                            <option value="light" selected>Claro</option>
                            <option value="dark">Oscuro</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="contrasenha" class="form-label">Contraseña <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="contrasenha" name="contrasenha" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button"
                                data-target="contrasenha">
                                <i class="fa-solid fa-duotone fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="recontrasenha" class="form-label">Repita contraseña <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="recontrasenha" name="recontrasenha"
                                required>
                            <button class="btn btn-outline-secondary toggle-password" type="button"
                                data-target="recontrasenha">
                                <i class="fa-solid fa-duotone fa-eye"></i>
                            </button>
                        </div>
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
