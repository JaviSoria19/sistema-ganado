@extends('layouts.app')

@section('content')
    <h1 class="text-center text-info fw-bold"><i class="fa-solid fa-duotone fa-cow"></i> {{ $head_title }}</h1>

    <button type="button" class="btn btn-success mb-3 btn-crear" data-bs-toggle="modal" data-bs-target="#modal-formulario">
        <i class="fa-solid fa-duotone fa-plus"></i> Crear bovino</button>

    <a class="btn btn-primary mb-3" href="{{ route('pesajes-historicos.index') }}">
        <i class="fa-solid fa-duotone fa-weight-scale"></i> Pesajes históricos</a>

    <a class="btn btn-primary mb-3" href="{{ route('recuentos-historicos.index') }}">
        <i class="fa-solid fa-duotone fa-check"></i> Recuentos históricos</a>

    <h2 class="text-info fw-bold">Lista de bovinos</h2>

    <div class="card p-3 mb-3">
        <p>Seleccione una opción para <i class="fa-solid fa-duotone fa-file-export"></i> exportar o <i
                class="fa-solid fa-duotone fa-filter"></i> filtrar la tabla:</p>
        <div id="dataTable-export-buttons-container"></div>

        <br>

        <div class="card-body">
            @include('bovinos.index_datatable_filter_form')
        </div>
    </div>

    <table class="table table-bordered table-striped" id="dataTable">
        <thead>
            <tr>
                <!-- Los campos generados que están comentados abajo son aquellos que se calculan dinámicamente en el frontend en base a los datos de entrada -->
                <th>#</th>
                <th>Carimbo</th> <!-- Generado con datatables según el año de nacimiento ✅ -->
                <th>Identificador</th>
                <th>Potrero</th>
                <th>Entore</th>
                <th>Padre</th>
                <th>Madre</th>
                <th>Origen</th>
                <th>Género</th>
                <th>Categoría</th> <!-- Generado con datatables según género y edad ✅ -->
                <th>Tiene identificador (oreja)</th>
                <th>Tiene identificador (lomo)</th>
                <th>Fecha de nacimiento</th>
                <th>Peso de nacimiento (kg)</th>
                <th>Fecha de destete</th>
                <th>Peso al destete (kg)</th>
                <th>Peso actual (kg)</th>
                <th>Peso de nacimiento (ua)</th> <!-- Generado con datatables en base a su contraparte en kg ✅-->
                <th>Peso al destete (ua)</th> <!-- Generado con datatables en base a su contraparte en kg ✅ -->
                <th>Peso actual (ua)</th> <!-- Generado con datatables en base a su contraparte en kg ✅ -->
                <th>Peso ajustado a los 205 días</th>
                <th>Ganancia diaria de peso</th>
                <th>Color de nacimiento</th>
                <th>Color actual</th>
                <th>Estado corporal</th>
                <th>Selección</th>
                <th>Observaciones</th>
                <th>Fecha de salida</th>

                <th>Estado</th>
                <th>F. Registro</th>
                <th>F. Actualización</th>
                <th>F. Eliminación</th>
                <th>Creado por</th>
                <th>Modificado por</th>
                <th>Eliminado por</th>
                <th>Acciones</th>
            </tr>
        </thead>
    </table>

    <div class="mb-3"></div>

    @include('bovinos.index_modal_form')
@endsection

@section('scripts')
    @include('bovinos.index_scripts')
@endsection
