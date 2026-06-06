@extends('layouts.app')

@section('content')
    <div class="container-fluid px-3 pb-5">
        <h1 class="text-center text-info fw-bold mb-4">
            <i class="fa-solid fa-cart-plus me-2"></i>{{ $head_title }}
        </h1>

        <div class="row g-3">

            {{-- ════ COLUMNA PRINCIPAL ════ --}}
            <div class="col-lg-8">

                {{-- ── CLIENTE ── --}}
                <div class="card border-secondary mb-3">
                    <div class="card-header py-2">
                        <span class="text-info fw-semibold small text-uppercase">
                            <i class="fa-solid fa-user me-1"></i>Cliente
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 align-items-end">
                            <div class="col-sm-5">
                                <label class="form-label form-label-sm">Seleccionar cliente</label>
                                <div class="input-group input-group-sm">
                                    <select id="select-cliente" class="form-select form-select-sm select2">
                                        <option value="">— Elige un cliente —</option>
                                    </select>
                                    <button type="button" class="btn btn-outline-info" id="btn-editar-cliente"
                                        title="Editar cliente" disabled>
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <label class="form-label form-label-sm">Fecha de venta</label>
                                <input type="date" id="fecha-venta" class="form-control form-control-sm"
                                    value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-sm-4">
                                <button type="button" class="btn btn-outline-success btn-sm w-100" id="btn-nuevo-cliente">
                                    <i class="fa-solid fa-plus me-1"></i>Nuevo cliente
                                </button>
                            </div>
                        </div>
                        <div id="cliente-info" class="mt-2 d-none small text-muted">
                            <i class="fa-solid fa-phone me-1"></i><span id="cliente-celular"></span>
                            <span class="mx-2">·</span>
                            <i class="fa-solid fa-location-dot me-1"></i><span id="cliente-estancia"></span>
                        </div>
                    </div>
                </div>

                {{-- ── BOVINOS ── --}}
                <div class="card border-secondary mb-3">
                    <div class="card-header py-2">
                        <span class="text-info fw-semibold small text-uppercase">
                            <i class="fa-solid fa-cow me-1"></i>Bovinos
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 align-items-end mb-3">
                            <div class="col">
                                <label class="form-label form-label-sm">Agregar bovino</label>
                                <select id="select-bovino" class="form-select form-select-sm select2">
                                    <option value="">— Seleccione —</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="button" class="btn btn-outline-info btn-sm" id="btn-agregar-bovino">
                                    <i class="fa-solid fa-plus me-1"></i>Agregar
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="text-center" style="width:32px">#</th>
                                        <th>Identificador</th>
                                        <th>Potrero</th>
                                        <th class="text-center">Peso kg</th>
                                        <th class="text-center">Tipo precio</th>
                                        <th class="text-center">Precio</th>
                                        <th class="text-center">Destare %</th>
                                        <th class="text-center">Rend. %</th>
                                        <th class="text-center">Kg Gancho</th>
                                        <th class="text-center">Subtotal</th>
                                        <th style="width:40px"></th>
                                    </tr>
                                </thead>
                                <tbody id="bovinos-tbody">
                                    <tr id="bovinos-empty">
                                        <td colspan="11" class="text-center text-muted py-3 small">
                                            <i class="fa-solid fa-cow me-1 opacity-50"></i>
                                            Aún no se han agregado bovinos
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- ── PAGOS ── --}}
                <div class="card border-secondary mb-3">
                    <div class="card-header py-2 d-flex justify-content-between align-items-center">
                        <span class="text-info fw-semibold small text-uppercase">
                            <i class="fa-solid fa-money-bill-wave me-1"></i>Pagos
                        </span>
                        <button type="button" class="btn btn-outline-success btn-sm" id="btn-agregar-pago">
                            <i class="fa-solid fa-plus me-1"></i>Agregar pago
                        </button>
                    </div>
                    <div class="card-body" id="pagos-container">
                        <p class="text-center text-muted small py-2 mb-0" id="pagos-empty">
                            <i class="fa-regular fa-credit-card me-1 opacity-50"></i>Sin pagos registrados
                        </p>
                    </div>
                </div>

            </div>{{-- /col principal --}}

            {{-- ════ PANEL RESUMEN ════ --}}
            <div class="col-lg-4">
                <div class="card border-secondary" style="position:sticky;top:1rem">
                    <div class="card-header py-2">
                        <span class="text-info fw-semibold small text-uppercase">
                            <i class="fa-solid fa-receipt me-1"></i>Resumen
                        </span>
                    </div>
                    <div class="card-body p-3">
                        <table class="table table-sm table-borderless mb-0 small">
                            <tr>
                                <td class="text-muted">Bovinos</td>
                                <td class="text-end fw-semibold" id="res-cantidad">0</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Total venta</td>
                                <td class="text-end fw-semibold" id="res-total">Bs. 0.00</td>
                            </tr>
                            <tr>
                                <td class="text-success">Total pagado</td>
                                <td class="text-end text-success fw-semibold" id="res-pagado">Bs. 0.00</td>
                            </tr>
                            <tr class="border-top">
                                <td class="text-danger fw-bold">Pendiente</td>
                                <td class="text-end text-danger fw-bold" id="res-pendiente">Bs. 0.00</td>
                            </tr>
                        </table>

                        <hr class="my-3">

                        <div class="d-grid gap-2">
                            <button type="button" id="btn-guardar-venta" class="btn btn-info fw-bold">
                                <i class="fa-solid fa-floppy-disk me-2"></i>Registrar venta
                            </button>
                            <a href="{{ route('ventas.index') }}" class="btn btn-outline-secondary">
                                <i class="fa-solid fa-arrow-left me-1"></i>Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- /row --}}
    </div>

    {{-- ════════════════════════════════════════════
     MODAL CLIENTE (crear / editar)
════════════════════════════════════════════ --}}
    <div class="modal fade" id="modal-cliente" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-info fw-bold" id="modal-cliente-titulo">
                        <i class="fa-solid fa-user-plus me-2"></i>Nuevo cliente
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="cliente-edit-id" value="">
                    <div class="mb-3">
                        <label class="form-label form-label-sm">Nombre completo <span class="text-danger">*</span></label>
                        <input type="text" id="cliente-nombre" class="form-control form-control-sm"
                            placeholder="Ej. Juan Pérez">
                    </div>
                    <div class="mb-3">
                        <label class="form-label form-label-sm">Celular <span class="text-danger">*</span></label>
                        <input type="text" id="cliente-celular-input" class="form-control form-control-sm"
                            placeholder="Ej. 70012345">
                    </div>
                    <div class="mb-3">
                        <label class="form-label form-label-sm">Estancia <span class="text-danger">*</span></label>
                        <input type="text" id="cliente-estancia-input" class="form-control form-control-sm"
                            placeholder="Ej. Estancia El Palmar">
                    </div>
                    <div id="modal-cliente-error" class="alert alert-danger py-2 d-none small"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-info btn-sm" id="btn-guardar-cliente">
                        <i class="fa-solid fa-floppy-disk me-1"></i>Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @include('ventas.create_scripts')
@endsection
