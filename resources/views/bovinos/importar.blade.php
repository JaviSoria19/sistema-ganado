@extends('layouts.app')

@section('content')
    <h1 class="text-center text-info fw-bold">
        <i class="fa-solid fa-cow"></i> {{ $head_title }}
    </h1>

    <a class="btn btn-secondary mb-3" href="{{ route('bovinos.index') }}">
        <i class="fa-solid fa-arrow-left"></i> Volver
    </a>

    <br>

    <div class="mb-3">
        <label for="excel-file" class="form-label">Seleccionar archivo Excel</label>
        <input type="file" class="form-control" id="excel-file" accept=".xlsx, .xls">
    </div>

    <p>
        Importante: subir un archivo Excel con las siguientes columnas en orden:
        <strong>Índice</strong>, <strong>Id. Potrero</strong>, <strong>Id. Entore</strong>,
        <strong>Id. Padre</strong>, <strong>Id. Madre</strong>, <strong>Origen</strong>,
        <strong>Identificador</strong>, <strong>Género</strong>, <strong>Id. Oreja (0/1)</strong>,
        <strong>Id. Lomo (0/1)</strong>, <strong>Fecha Nacimiento</strong>, <strong>Peso Nacimiento</strong>,
        <strong>Fecha Destete</strong>, <strong>Peso Destete</strong>, <strong>Peso Actual</strong>,
        <strong>Color Nacimiento</strong>, <strong>Color Actual</strong>, <strong>Estado Corporal</strong>,
        <strong>Selección</strong>, <strong>Observaciones</strong>.
        Los campos <strong>Id. Potrero</strong>, <strong>Id. Entore</strong>, <strong>Id. Padre</strong> e
        <strong>Id. Madre</strong> se ingresan como números (puedes verlos en los listados de potreros,
        entores y bovinos). <strong>Id. Potrero</strong> es obligatorio; los demás pueden dejarse vacíos
        si no corresponden.
    </p>

    <p>Se recomienda subir primero el archivo antes de editar filas manualmente.</p>

    <p>
        Los bovinos se registrarán en el sistema. Si ya existe un bovino con el mismo
        <strong>identificador</strong> y <strong>fecha de nacimiento</strong>, será rechazado.
    </p>

    <p>Lista de potreros existentes:</p>

    <ul class="list-group">
        @foreach ($potreros as $potrero)
            <li class="list-group-item">{{ $potrero->nombre }} | Id: <b>{{ $potrero->id_potrero }}</b></li>
        @endforeach
    </ul>

    <br>
    
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-sm" id="table-bovinos-importar">
            <thead>
                <tr>
                    <th class="text-center">#</th>
                    <th>Id. Potrero</th>
                    <th>Id. Entore</th>
                    <th>Id. Padre</th>
                    <th>Id. Madre</th>
                    <th>Origen</th>
                    <th>Identificador</th>
                    <th>Género</th>
                    <th class="text-center">Id. Oreja</th>
                    <th class="text-center">Id. Lomo</th>
                    <th>F. Nacimiento</th>
                    <th>Peso Nac.</th>
                    <th>F. Destete</th>
                    <th>Peso Destete</th>
                    <th>Peso Actual</th>
                    <th>Color Nac.</th>
                    <th>Color Actual</th>
                    <th>E. Corporal</th>
                    <th>Selección</th>
                    <th>Observaciones</th>
                    <th class="text-center">Acciones</th>
                    <th>Observación</th>
                </tr>
            </thead>
            <tbody>
                {{-- Las filas se llenarán dinámicamente con JavaScript al cargar el archivo Excel --}}
            </tbody>
        </table>
    </div>

    <br>

    <button type="button" class="btn btn-success me-2" id="btn-agregar-fila">
        <i class="fa-solid fa-plus"></i> Agregar fila
    </button>

    <button type="button" class="btn btn-primary" id="btn-guardar">
        <i class="fa-solid fa-upload"></i> Importar datos
    </button>

    <div class="mb-3"></div>
@endsection

@section('scripts')
    @include('bovinos.importar_scripts')
    <style>
        th {
            min-width: 150px;
        }
    </style>
@endsection
