@extends('layouts.app')

@section('content')
    <h1 class="text-center text-info fw-bold"><i class="fa-solid fa-duotone fa-dashboard mx-2"></i>{{ $head_title }}</h1>

    <h2 class="text-center"><i class="fa-solid fa-duotone fa-door-open mx-2"></i>Bienvenido, <span
            class="text-info fw-bold">{{ session('usuario') }}</span></h2>

    <div class="card mb-3">
        <div class="card-header">
            <span class="h2 text-info fw-bold align-middle"><i class="fa-solid fa-duotone fa-bars"></i> MENÚ</span>
        </div>

        <div class="card-body">
            @if (session('id_usuario') == 1)
                @include('panel.admin_botones_super')
            @else
                @include('panel.admin_botones')
            @endif
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <span class="h2 text-info fw-bold align-middle"><i class="fa-solid fa-duotone fa-chart-simple"></i>
                ESTADÍSTICAS</span>
        </div>
        <div class="card-body">
            <nav>
                <div class="nav nav-tabs mb-3" id="nav-tab" role="tablist">
                    <button class="nav-link active" id="nav-ventas-tab" data-bs-toggle="tab" data-bs-target="#nav-ventas"
                        type="button" role="tab" aria-controls="nav-ventas" aria-selected="true">Ventas</button>
                    <button class="nav-link" id="nav-saldos-tab" data-bs-toggle="tab" data-bs-target="#nav-saldos"
                        type="button" role="tab" aria-controls="nav-saldos" aria-selected="false">Saldos</button>
                    {{-- <button class="nav-link" id="nav-contact-tab" data-bs-toggle="tab" data-bs-target="#nav-contact"
                        type="button" role="tab" aria-controls="nav-contact" aria-selected="false">...</button> --}}
                </div>
            </nav>
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active" id="nav-ventas" role="tabpanel" aria-labelledby="nav-ventas-tab">
                    {{-- @include('panel.admin_estadisticas_ventas') --}}
                </div>

                <div class="tab-pane fade" id="nav-saldos" role="tabpanel" aria-labelledby="nav-saldos-tab">
                    {{-- @include('panel.admin_estadisticas_saldos') --}}
                </div>

                {{-- <div class="tab-pane fade" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab">

                </div> --}}
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @include('panel.admin_scripts')
@endsection
