@extends('layouts.app')

@section('content')
    <h1 class="text-center text-info fw-bold"><i class="fa-solid fa-duotone fa-check"></i> {{ $head_title }}</h1>

    <a class="btn btn-secondary mb-3" href="{{ route('palpaciones-historicas.index') }}">
        <i class="fa-solid fa-duotone fa-arrow-left"></i> Volver</a>

    <br>

    <div class="mb-3">
        <label for="excel-file" class="form-label">Seleccionar archivo Excel</label>
        <input type="file" class="form-control" id="excel-file" accept=".xlsx, .xls">
    </div>
    
    <p>Importante: subir un archivo excel con las siguientes columnas: <strong>Carimbo</strong>, <strong>Identificador</strong>, <strong>Resultado</strong> y <strong>Fecha</strong>. El sistema validará que el formato sea correcto y que los datos correspondan a bovinos existentes en el sistema. En caso de encontrar errores, se indicará la fila y el motivo del error para su corrección.</p>

    <p>Se recomienda subir primero el archivo antes de asignar filas manualmente</p>

    <p>Si se registra el recuento más reciente de un bovino, su estado se actualizará automáticamente.</p>

    <table class="table table-bordered table-striped" id="table-palpaciones-historicas">
        <thead>
            <th>#</th>
            <th>Carimbo</th>
            <th>Identificador</th>
            <th>Resultado</th>
            <th>Fecha</th>
            <th>Acciones</th>
            <th>Observación</th>
        </thead>
        <tbody>
            <!-- Las filas se llenarán dinámicamente con JavaScript al cargar el archivo Excel usando SheetJS -->
        </tbody>
    </table>

    <button type="button" class="btn btn-success me-2" id="btn-agregar-fila">
        <i class="fa-solid fa-plus"></i> Agregar fila
    </button>

    <button type="button" class="btn btn-primary" id="btn-guardar">
        <i class="fa-solid fa-upload"></i> Importar datos
    </button>

    <div class="mb-3"></div>
@endsection

@section('scripts')
    @include('palpaciones_historicas.crear_scripts')
@endsection
