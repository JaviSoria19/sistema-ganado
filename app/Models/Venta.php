<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';
    protected $primaryKey = 'idVenta';

    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = 'fecha_actualizacion';

    /** Relación muchos a muchos con productos */
    public function productos()
    {
        return $this->belongsToMany(
            Producto::class,           // Modelo relacionado
            'detalles_ventas',         // Tabla pivote
            'idVenta',                 // FK en la tabla pivote hacia ventas
            'idProducto'               // FK en la tabla pivote hacia productos
        )->withPivot('precioUSD');     // Campos extras de la tabla pivote
    }

    /** Relación uno a muchos con detalles_pedidos_empresas */
    public function pagos()
    {
        return $this->hasMany(PagoVenta::class, 'idVenta', 'idVenta');
    }

    /** Relación FK con usuarios */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    /** Relación FK con clientes */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    /** Relación FK con empleados */
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'idEmpleado', 'idEmpleado');
    }

    /** Relación con atributo de auditoría */
    public function editor()
    {
        return $this->belongsTo(Usuario::class, 'modificado_por', 'id_usuario');
    }

    public function getAllVentas($fechaInicio = null, $fechaFin = null)
    {
        $query = Venta::with(['productos.marca', 'pagos', 'usuario', 'cliente', 'empleado', 'editor'])
            ->orderBy('idVenta', 'ASC');

        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('fecha_registro', [
                $fechaInicio . ' 00:00:00',
                $fechaFin . ' 23:59:59'
            ]);
        }

        return $query->get();
    }

    public function getVentasPorEstadoYSaldo($estado, $saldoUSD_operador, $saldoUSD, $orden, $fechaInicio, $fechaFin)
    {
        return Venta::with(['productos.marca', 'pagos', 'usuario', 'cliente', 'empleado', 'editor'])
            ->where('estado', $estado)
            ->where('saldoUSD', $saldoUSD_operador, $saldoUSD)
            ->whereBetween('fecha_registro', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->orderBy('idVenta', $orden)
            ->get();
    }

    public function getVenta($idVenta)
    {
        return Venta::with(['productos.marca', 'pagos', 'usuario', 'cliente', 'empleado', 'editor'])->find($idVenta);
    }

    public function dashboard_getEstadisticasVentas()
    {
        $fechas = [
            'hoy' => [Carbon::today(), Carbon::today()->endOfDay()],
            'semana' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'mes' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
        ];

        $resultados = [];

        foreach ($fechas as $periodo => [$inicio, $fin]) {
            // Query base para ventas (sin join)
            $ventasBase = DB::table('ventas as v')
                ->where('v.estado', 1)
                ->whereBetween('v.fecha_registro', [$inicio, $fin]);

            // Cantidad de ventas e ingresos (sin join para evitar duplicados)
            $estadisticasVentas = (clone $ventasBase)
                ->select(
                    DB::raw('COUNT(DISTINCT v.idVenta) as cantidad'),
                    DB::raw('SUM(v.totalUSD - v.saldoUSD) as ingresos')
                )
                ->first();

            // Productos vendidos (con join solo para este cálculo)
            $productosVendidos = DB::table('detalles_ventas as dv')
                ->join('ventas as v', 'dv.idVenta', '=', 'v.idVenta')
                ->where('v.estado', 1)
                ->whereBetween('v.fecha_registro', [$inicio, $fin])
                ->count('dv.idProducto');

            $resultados[$periodo] = [
                'cantidadVentas' => $estadisticasVentas->cantidad ?? 0,
                'ingresos' => $estadisticasVentas->ingresos ?? 0,
                'productosVendidos' => $productosVendidos ?? 0,
            ];
        }

        return $resultados;
    }

    public function dashboard_getClientesConSaldo()
    {
        return Venta::select(
            'empleados.nombreEmpleado',
            'clientes.id_cliente',
            'clientes.nombreCliente',
            'clientes.celular',
            'clientes.procedencia',
            DB::raw('SUM(ventas.saldoUSD) as saldoPendiente'),
            DB::raw('MIN(ventas.fecha_registro) as fechaMasAntigua')
        )
            ->join('clientes', 'ventas.id_cliente', '=', 'clientes.id_cliente')
            ->join('empleados', 'ventas.idEmpleado', '=', 'empleados.idEmpleado')
            ->where('ventas.estado', 1)
            ->where('ventas.saldoUSD', '>', 0)
            ->groupBy('clientes.id_cliente', 'clientes.nombreCliente', 'clientes.celular')
            ->orderByDesc('saldoPendiente')
            ->get();
    }

    public function dashboard_getVentasConSaldo()
    {
        return Venta::with(['productos.marca', 'pagos', 'usuario', 'cliente', 'empleado', 'editor'])
            ->where('ventas.estado', 1)
            ->where('ventas.saldoUSD', '>', 0)
            ->orderBy('ventas.id_cliente', 'ASC')
            ->orderBy('ventas.fecha_registro', 'ASC')
            ->get();
    }
}
