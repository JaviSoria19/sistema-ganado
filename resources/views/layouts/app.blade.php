<!DOCTYPE html>
<html lang="en" data-bs-theme="{{ session('tema_preferido') ? session('tema_preferido') : 'dark' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ helper_tituloPagina() }} | {{ $headTitle }}</title>
    <!-- Icono -->
    <link rel="icon" type="image/x-icon" href="{{ URL::to('/') }}/public/favicon.ico">
    <!-- Token para formularios -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('layouts.dependenciesCSS')

</head>

<body class="d-flex flex-column min-vh-100">

    @include('layouts.header')

    <main class="flex-grow-1">
        <div class="container">
            @yield('content')
        </div>
    </main>
    
    @include('layouts.modalSignOut')

    @include('layouts.footer')

    @include('layouts.dependenciesJS')

    @yield('scripts')
</body>

</html>
