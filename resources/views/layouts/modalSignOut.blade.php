<!-- Modal para cerrar sesión -->
<div class="modal fade" id="modalSignOut" tabindex="-1" aria-labelledby="modalSignOut_Title" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 text-info fw-bold" id="modalSignOut_Title"><i class="fa fa-sign-out"></i> CERRAR SESIÓN
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body fw-bold">
                <span class="text-info">{{ session('usuario') }}</span>, ¿Desea cerrar sesión?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fa fa-close"></i>No,
                    cancelar</button>
                <a href="{{ route('logout') }}" class="btn btn-info"><i class="fa fa-sign-out"></i> Si, cerrar
                    sesión</a>
            </div>
        </div>
    </div>
</div>
