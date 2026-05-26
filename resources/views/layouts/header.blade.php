<header class="p-3 mb-3 border-bottom">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start fw-bold">
            <i class="fa-duotone fa-solid fa-user-alien fa-lg"></i> {{ session('usuario') }}
            &nbsp;
            <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
                <ul class="nav nav-pills">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('panel') ? 'active' : '' }}" aria-current="page"
                            href="{{ route('dashboard') }}"><i class="fa-solid fa-duotone fa-dashboard"></i>
                            Panel</a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('ventas/crear') ? 'active' : '' }}" aria-current="page"
                            href="{{ route('ventas.crear') }}"><i class="fa-solid fa-duotone fa-cart-plus"></i>
                            Añadir venta</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('ventas') ? 'active' : '' }}" aria-current="page"
                            href="{{ route('ventas.index') }}"><i class="fa-solid fa-duotone fa-cart-shopping"></i>
                            Ventas</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('abastecimientos') ? 'active' : '' }}" aria-current="page"
                            href="{{ route('abastecimientos.index') }}"><i class="fa-solid fa-duotone fa-cow"></i>
                            Bovinos</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('abastecimientos') ? 'active' : '' }}" aria-current="page"
                            href="{{ route('abastecimientos.index') }}"><i class="fa-solid fa-duotone fa-cow"></i>
                            Entores</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('potreros') ? 'active' : '' }}" aria-current="page"
                            href="{{ route('potreros.index') }}"><i class="fa-solid fa-duotone fa-frame"></i>
                            Potreros</a>
                    </li>
                </ul>
            </ul>
            <div class="dropdown text-end"> <a href="#"
                    class="d-block link-body-emphasis text-decoration-none dropdown-toggle" data-bs-toggle="dropdown"
                    aria-expanded="false"> <img src="{{ URL::to('/') }}/public/img/user.jpeg" alt="mdo" width="32"
                        height="32" class="rounded-circle"> </a>
                <ul class="dropdown-menu text-small" style="">
                    <li><a class="dropdown-item" href="#"><i class="fa-solid fa-duotone fa-alien"></i>
                            {{ session('usuario') }}</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item {{ request()->is('parametros') ? 'active' : '' }}"
                            href="{{ route('parametros.index') }}"><i class="fa-solid fa-duotone fa-sliders"></i>
                            Parámetros</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    </li>
                    <li><button type="button" class="dropdown-item" data-bs-toggle="modal"
                            data-bs-target="#modalSignOut">
                            <i class="fa-solid fa-duotone  fa-sign-out"></i> Cerrar sesión</button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>
