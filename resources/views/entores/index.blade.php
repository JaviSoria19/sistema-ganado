@extends('layouts.app')

@section('content')
    <h1 class="text-center text-info fw-bold"><i class="fa-solid fa-duotone fa-cow"></i> {{ $head_title }}</h1>

    <button type="button" class="btn btn-success mb-3 btn-crear" data-bs-toggle="modal" data-bs-target="#modal-formulario">
        <i class="fa-solid fa-duotone fa-plus"></i> Crear entore</button>

    <h2 class="text-info fw-bold">Lista de entores</h2>

    <div class="card p-3 mb-3">
        <p>Seleccione una opción para <i class="fa-solid fa-duotone fa-file-export"></i> exportar o <i
                class="fa-solid fa-duotone fa-filter"></i> filtrar la tabla:</p>
        <div id="dataTable-export-buttons-container"></div>
    </div>

    @if (isset($entoresInfo) && !empty($entoresInfo))
        <div class="alert alert-info">
            {{ $entoresInfo }}
        </div>
    @endif
    
    <table class="table table-bordered table-striped" id="dataTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Id entore</th>
                <th>Tipo de entore</th>
                <th>Fecha inicio</th>
                <th>Fecha fin</th>
                <th>Código pajuela</th>
                <th>Macho o machos</th>
                <th>Hembras</th>
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

    @include('entores.modal_form')
@endsection

@section('scripts')
    @include('entores.index_scripts')
@endsection