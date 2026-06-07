<div class="row mb-3">
    <h4 class="text-warning fw-bold"><i class="fa-solid fa-duotone fa-cart-shopping"></i> SALDOS PENDIENTES (RESUMEN)
    </h4>

    <table class="table table-bordered table-striped dataTable" id="dataTable">
        <thead class="text-center">
            <tr>
                <th>#</th>
                <th>Creado por</th>
                <th>Cliente</th>
                <th>Celular</th>
                <th>Estancia</th>
                <th>Saldo (Bs.)</th>
                <th>Fecha desde</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($saldos_pendientes as $saldo_pendiente)
                <tr>
                    <th class="text-center">{{ $loop->index + 1 }}.</th>
                    <th>{{ $saldo_pendiente->usuario }}</th>
                    <th>{{ $saldo_pendiente->nombre }}</th>
                    <th>{{ $saldo_pendiente->celular }}</th>
                    <th>{{ $saldo_pendiente->estancia }}</th>
                    <th class="text-warning">{{ $saldo_pendiente->saldoPendiente }}</th>
                    <th>{{ date('d/m/Y', strtotime($saldo_pendiente->fechaMasAntigua)) }}</th>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-end">Total Saldos Pendientes:</th>
                <th class="{{ $saldos_pendientes->sum('saldoPendiente') > 0 ? 'text-warning' : 'text-success' }}">
                    {{ number_format($saldos_pendientes->sum('saldoPendiente'), 2, '.', '') }}
                </th>
                <th></th>
            </tr>
        </tfoot>
    </table>
</div>
