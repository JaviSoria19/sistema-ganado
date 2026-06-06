@extends('layouts.app')

@section('content')
    <h1 class="text-center text-info fw-bold"><i class="fa-solid fa-duotone fa-cart-shopping"></i>
        {{ $head_title }}</h1>

    <a href="{{ route('ventas.crear') }}" class="btn btn-success mb-3 btn-crear" target="_blank" rel="noopener noreferrer"><i
            class="fa-solid fa-duotone fa-cart-plus"></i> Crear venta</a>

    <h2 class="text-info fw-bold">Lista de ventas</h2>

    <div class="card p-3 mb-3">
        <div class="card-body">
            <p>Seleccione una opción para <i class="fa-solid fa-duotone fa-file-export"></i> exportar o <i
                    class="fa-solid fa-duotone fa-filter"></i> filtrar la tabla:</p>
            <div id="dataTableExportButtonsContainer"></div>

            <br>
            
            @include('ventas.index_datatable_filter_form')
        </div>
    </div>

    <table class="table table-bordered table-striped" id="dataTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha de venta</th>
                <th>Cliente</th>
                <th>Bovinos</th>
                <th>Total</th>
                <th>Pagos</th>
                <th>Total pagado</th>
                <th>Saldo</th>

                <th>Estado</th>
                <th>F. Registro</th>
                <th>F. Actualización</th>
                <th>F. Eliminación</th>
                <th>Motivo de eliminación</th>
                <th>Creado por</th>
                <th>Modificado por</th>
                <th>Eliminado por</th>
                <th>Acciones</th>
            </tr>
        </thead>
    </table>

    <div class="mb-3"></div>
@endsection

@section('scripts')
    @include('ventas.index_scripts')
@endsection
