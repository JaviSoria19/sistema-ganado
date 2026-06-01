@extends('layouts.app')

@section('content')
    <h1 class="text-center text-info fw-bold"><i class="fa-solid fa-duotone fa-check"></i> {{ $head_title }}</h1>

    <a class="btn btn-primary mb-3" href="{{ route('recuentos-historicos.crear') }}">
        <i class="fa-solid fa-duotone fa-plus"></i> Registrar recuentos históricos</a>

    <button type="button" class="btn btn-success mb-3 btn-crear" data-bs-toggle="modal" data-bs-target="#modal-formulario">
        <i class="fa-solid fa-duotone fa-plus"></i> Crear recuento histórico</button>

    <h2 class="text-info fw-bold">Lista de recuentos históricos</h2>

    <div class="card p-3 mb-3">
        <p>Seleccione una opción para <i class="fa-solid fa-duotone fa-file-export"></i> exportar o <i
                class="fa-solid fa-duotone fa-filter"></i> filtrar la tabla:</p>
        <div id="dataTable-export-buttons-container"></div>
    </div>

    <table class="table table-bordered table-striped" id="dataTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Carimbo</th>
                <th>Identificador</th>
                <th>Estado recuento</th>
                <th>Fecha</th>

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

    @include('recuentos_historicos.index_modal_form')
@endsection

@section('scripts')
    @include('recuentos_historicos.index_scripts')
@endsection
