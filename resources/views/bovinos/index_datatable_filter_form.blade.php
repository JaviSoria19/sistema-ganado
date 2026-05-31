<form method="GET" class="row g-3 align-items-end border border-primary rounded p-3">
    <h3><i class="fa-solid fa-duotone fa-filter"></i> Filtrar tabla de bovinos por:</h3>
    <br>
    <div class="col-md-4">
        <label for="busqueda-potrero" class="form-label">Potrero</label>
        <select id="busqueda-potrero" name="potrero" class="form-select">
            <option value="">Todos</option>
            @foreach ($potreros as $potrero)
                <option value="{{ $potrero->id_potrero }}">{{ $potrero->nombre }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-2">
        <label for="busqueda-origen" class="form-label">Origen</label>
        <select id="busqueda-origen" name="origen" class="form-select">
            <option value="">Todos</option>
            <option value="criollo">Criollo</option>
            <option value="comprado">Comprado</option>
            <option value="prestado">Prestado</option>
        </select>
    </div>

    <div class="col-md-2">
        <label for="busqueda-genero" class="form-label">Género</label>
        <select id="busqueda-genero" name="genero" class="form-select">
            <option value="">Todos</option>
            <option value="macho">Macho</option>
            <option value="hembra">Hembra</option>
        </select>
    </div>

    <div class="col-md-2">
        <label for="busqueda-estado" class="form-label">Estado</label>
        <select id="busqueda-estado" name="estado" class="form-select">
            <option value="">Todos</option>
            <option value="activo">Activo</option>
            <option value="inactivo">Inactivo</option>
            <option value="vendido">Vendido</option>
        </select>
    </div>

    <div class="col-md-2">
        <button id="form-filter-bovinos" type="submit" formaction="{{ route('bovinos.index') }}"
            class="btn btn-primary w-100">
            <i class="fa-solid fa-search"></i> Buscar
        </button>
    </div>

</form>
