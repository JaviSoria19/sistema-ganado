@extends('layouts.app')

@section('content')
    <h1 class="text-center text-info fw-bold"><i class="fa-solid fa-duotone fa-frame"></i> {{ $head_title }}</h1>

    <button type="button" class="btn btn-success mb-3 btn-crear" data-bs-toggle="modal" data-bs-target="#modal-formulario">
        <i class="fa-solid fa-duotone fa-plus"></i> Crear bovino</button>

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
                <th>Identificador</th>
                <th>Potrero</th>
                <th>Entore</th>
                <th>Padre</th>
                <th>Madre</th>
                <th>Origen</th>
                <th>Carimbo</th> <!-- Generado con datatables según el año de nacimiento ✅ -->
                <th>Género</th>
                <th>Categoría</th> <!-- Generado con datatables según género y edad -->
                <th>Tiene identificador (oreja)</th>
                <th>Tiene identificador (lomo)</th>
                <th>Peso de nacimiento (kg)</th>
                <th>Peso al destete (kg)</th>
                <th>Peso actual (kg)</th>
                <th>Peso de nacimiento (ua)</th> <!-- Generado con datatables en base a su contraparte en kg ✅-->
                <th>Peso al destete (ua)</th> <!-- Generado con datatables en base a su contraparte en kg ✅ -->
                <th>Peso actual (ua)</th> <!-- Generado con datatables en base a su contraparte en kg ✅ -->
                <th>Color de nacimiento</th>
                <th>Color actual</th>
                <th>Fecha de nacimiento</th>
                <th>Factible para venta</th> <!-- Generado con datatables según género y edad -->
                <th>Fecha de salida</th>
                <th>Observaciones</th>

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
