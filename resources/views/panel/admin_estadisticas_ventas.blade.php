<div class="row mb-3">
    <div class="col-12">
        <h4 class="text-dark-aquamarine fw-bold">VENTAS</h4>
    </div>

    <div class="col-md-6 col-lg-4 mb-3">
        <div class="card info-card shadow-sm border-success">
            <div class="card-body d-flex align-items-center bg-success bg-opacity-10">
                <div class="icon-box bg-success bg-opacity-10 me-3">
                    <i class="text-success fa-solid fa-duotone fa-cart-shopping fa-xl"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 small">Ventas de hoy</h6>
                    <h3 class="fw-bold">{{ $estadisticas['hoy']['cantidadVentas'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-4 mb-3">
        <div class="card info-card shadow-sm border-success">
            <div class="card-body d-flex align-items-center bg-success bg-opacity-10">
                <div class="icon-box bg-success bg-opacity-10 me-3">
                    <i class="text-success fa-solid fa-duotone fa-cart-shopping fa-xl"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 small">Ventas de la semana</h6>
                    <h3 class="fw-bold">{{ $estadisticas['semana']['cantidadVentas'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-4 mb-3">
        <div class="card info-card shadow-sm border-success">
            <div class="card-body d-flex align-items-center bg-success bg-opacity-10">
                <div class="icon-box bg-success bg-opacity-10 me-3">
                    <i class="text-success fa-solid fa-duotone fa-cart-shopping fa-xl"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 small">Ventas del mes</h6>
                    <h3 class="fw-bold">{{ $estadisticas['mes']['cantidadVentas'] }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12">
        <h4 class="text-dark-aquamarine fw-bold">INGRESOS</h4>
    </div>
    <div class="col-md-6 col-lg-4 mb-3">
        <div class="card info-card shadow-sm border-success">
            <div class="card-body d-flex align-items-center bg-success bg-opacity-10">
                <div class="icon-box bg-success bg-opacity-10 me-3">
                    <i class="text-success fa-solid fa-duotone fa-sack-dollar fa-xl"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 small">Ingresos de hoy</h6>
                    <h3 class="fw-bold">$ {{ $estadisticas['hoy']['ingresos'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-4 mb-3">
        <div class="card info-card shadow-sm border-success">
            <div class="card-body d-flex align-items-center bg-success bg-opacity-10">
                <div class="icon-box bg-success bg-opacity-10 me-3">
                    <i class="text-success fa-solid fa-duotone fa-sack-dollar fa-xl"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 small">Ingresos de la semana</h6>
                    <h3 class="fw-bold">$ {{ $estadisticas['semana']['ingresos'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-4 mb-3">
        <div class="card info-card shadow-sm border-success">
            <div class="card-body d-flex align-items-center bg-success bg-opacity-10">
                <div class="icon-box bg-success bg-opacity-10 me-3">
                    <i class="text-success fa-solid fa-duotone fa-sack-dollar fa-xl"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 small">Ingresos del mes</h6>
                    <h3 class="fw-bold">$ {{ $estadisticas['mes']['ingresos'] }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <h4 class="text-dark-aquamarine fw-bold">PRODUCTOS</h4>
    </div>
    <div class="col-md-6 col-lg-4 mb-3">
        <div class="card info-card shadow-sm border-primary">
            <div class="card-body d-flex align-items-center bg-primary bg-opacity-10">
                <div class="icon-box bg-primary bg-opacity-10 me-3">
                    <i class="text-primary fa-solid fa-duotone fa-boxes-stacked fa-xl"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 small">Productos vendidos de hoy</h6>
                    <h3 class="fw-bold">{{ $estadisticas['hoy']['productosVendidos'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-4 mb-3">
        <div class="card info-card shadow-sm border-primary">
            <div class="card-body d-flex align-items-center bg-primary bg-opacity-10">
                <div class="icon-box bg-primary bg-opacity-10 me-3">
                    <i class="text-primary fa-solid fa-duotone fa-boxes-stacked fa-xl"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 small">Productos vendidos de la semana</h6>
                    <h3 class="fw-bold">{{ $estadisticas['semana']['productosVendidos'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-4 mb-3">
        <div class="card info-card shadow-sm border-primary">
            <div class="card-body d-flex align-items-center bg-primary bg-opacity-10">
                <div class="icon-box bg-primary bg-opacity-10 me-3">
                    <i class="text-primary fa-solid fa-duotone fa-boxes-stacked fa-xl"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 small">Productos vendidos del mes</h6>
                    <h3 class="fw-bold">{{ $estadisticas['mes']['productosVendidos'] }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>
