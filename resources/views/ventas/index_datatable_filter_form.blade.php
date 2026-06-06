<form method="GET" class="row g-3 align-items-end border border-primary rounded p-3">
    <h3><i class="fa-solid fa-duotone fa-filter"></i> Filtrar tabla de ventas por:</h3>
    <br>
    <div class="col-md-2">
        <label for="fecha_inicio" class="form-label">Fecha de inicio</label>
        <input type="date" id="busqueda-fecha_inicio" name="fecha_inicio" class="form-control"
            value="{{ date('Y-m-d', strtotime('-1 month')) }}">
    </div>

    <div class="col-md-2">
        <label for="fecha_fin" class="form-label">Fecha de fin</label>
        <input type="date" id="busqueda-fecha_fin" name="fecha_fin" class="form-control"
            value="{{ date('Y-m-d') }}">
    </div>

    <div class="col-md-2">
        <label for="estado" class="form-label">Estado</label>
        <select id="busqueda-estado" name="estado" class="form-select">
            <option value="">Todos</option>
            <option value="activo">Activo</option>
            <option value="eliminado">Eliminado</option>
        </select>
    </div>

    <div class="col-md-2">
        <label for="busqueda-creado_por" class="form-label">Creado por</label>
        <select id="busqueda-creado_por" name="creado_por" class="form-select">
            <option value="">Todos</option>
            @foreach ($usuarios as $usuario)
                <option value="{{ $usuario->id_usuario }}">{{ $usuario->usuario }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-2">
        <label for="busqueda-id_cliente" class="form-label">Cliente</label>
        <select id="busqueda-id_cliente" name="id_cliente" class="form-select">
            <option value="">Todos</option>
            @foreach ($clientes as $cliente)
                <option value="{{ $cliente->id_cliente }}">{{ $cliente->nombre }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-2">
        <button id="form-filter-ventas" type="submit" formaction="{{ route('ventas.index') }}"
            class="btn btn-primary w-100">
            <i class="fa-solid fa-search"></i> Buscar
        </button>
    </div>

</form>
