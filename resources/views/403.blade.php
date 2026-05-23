@extends('layouts.app')

@section('content')
    <h1 class="text-center text-danger fw-bold"><i class="fa-solid fa-duotone fa-triangle-exclamation mx-2"></i>403
        - Acceso Denegado</h1>

    <div class="card mb-3">
        <div class="card-body text-center">
            <h2 class="text-danger fw-bold"><i class="fa-solid fa-duotone fa-lock mx-2"></i>No tienes permiso para acceder a
                esta página.</h2>
            <p class="fs-5">Por favor, contacta al administrador si crees que esto es un error.</p>
        </div>
    </div>
@endsection