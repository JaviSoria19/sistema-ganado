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
    protected $primaryKey = 'id_venta';

    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = 'fecha_actualizacion';

    /** Relación muchos a muchos con bovinos */
    public function bovinos()
    {
        return $this->belongsToMany(
            Bovino::class,           /* Modelo relacionado */
            'ventas_detalles',       /* Tabla pivote */
            'id_venta',              /* FK en la tabla pivote hacia ventas */
            'id_bovino'              /* FK en la tabla pivote hacia bovinos */
        )->withPivot('kg_peso_vivo', 'kg_peso_gancho', 'subtotal', 'observacion');
    }

    /** Relación uno a muchos con pagos */
    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_venta', 'id_venta');
    }

    /** Relación FK con clientes */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    /** Relación con atributo de auditoría */
    public function creado()
    {
        return $this->belongsTo(Usuario::class, 'creado_por', 'id_usuario');
    }

    public function modificado()
    {
        return $this->belongsTo(Usuario::class, 'modificado_por', 'id_usuario');
    }

    public function eliminado()
    {
        return $this->belongsTo(Usuario::class, 'eliminado_por', 'id_usuario');
    }

    public function get_all_ventas(array $filters = [])
    {
        $query = Venta::with('bovinos', 'pagos', 'cliente', 'creado', 'modificado', 'eliminado')
            ->addSelect([
                '*',
                DB::raw('(
                SELECT COALESCE(SUM(p.monto), 0)
                FROM pagos p
                WHERE p.id_venta = ventas.id_venta
                AND p.estado = "activo"
            ) AS total_pagado'),
                DB::raw('(
                ventas.total - (
                    SELECT COALESCE(SUM(p.monto), 0)
                    FROM pagos p
                    WHERE p.id_venta = ventas.id_venta
                    AND p.estado = "activo"
                )
            ) AS saldo'),
            ])
            ->orderBy('id_venta', 'ASC');

        if ($filters['fecha_inicio'] && $filters['fecha_fin']) {
            $query->whereBetween('fecha_venta', [
                $filters['fecha_inicio'],
                $filters['fecha_fin']
            ]);
        }

        if ($filters['estado']) {
            $query->where('estado', $filters['estado']);
        }

        if ($filters['creado_por']) {
            $query->where('creado_por', $filters['creado_por']);
        }

        if ($filters['id_cliente']) {
            $query->where('id_cliente', $filters['id_cliente']);
        }

        if ($filters['tipo_precio']) {
            $query->where('tipo_precio', $filters['tipo_precio']);
        }

        return $query->get();
    }

    public function get_venta($id_venta)
    {
        return Venta::with('bovinos', 'pagos', 'cliente', 'creado', 'modificado', 'eliminado')->find($id_venta);
    }

    public function dashboard_get_estadisticas_ventas()
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
                ->where('v.estado', 'activo')
                ->whereBetween('v.fecha_venta', [$inicio, $fin]);

            // Cantidad de ventas e ingresos (sin join para evitar duplicados)
            $estadisticasVentas = (clone $ventasBase)
                ->select(
                    DB::raw('COUNT(DISTINCT v.id_venta) as cantidad'),
                    DB::raw('SUM(v.total - (SELECT COALESCE(SUM(p.monto), 0) FROM pagos p WHERE p.id_venta = v.id_venta AND p.estado = "activo")) as ingresos')
                )
                ->first();

            // Bovinos vendidos (con join solo para este cálculo)
            $bovinosVendidos = DB::table('ventas_detalles as vd')
                ->join('ventas as v', 'vd.id_venta', '=', 'v.id_venta')
                ->where('v.estado', 'activo')
                ->whereBetween('v.fecha_venta', [$inicio, $fin])
                ->count('vd.id_bovino');

            $resultados[$periodo] = [
                'cantidadVentas' => $estadisticasVentas->cantidad ?? 0,
                'ingresos' => $estadisticasVentas->ingresos ?? 0,
                'bovinosVendidos' => $bovinosVendidos ?? 0,
            ];
        }

        return $resultados;
    }

    public function dashboard_get_clientes_con_saldo()
    {
        return Venta::select(
            'usuarios.usuario',
            'clientes.id_cliente',
            'clientes.nombre',
            'clientes.celular',
            'clientes.estancia',
            DB::raw('SUM(ventas.total - (SELECT COALESCE(SUM(p.monto), 0) FROM pagos p WHERE p.id_venta = ventas.id_venta AND p.estado = "activo")) as saldoPendiente'),
            DB::raw('MIN(ventas.fecha_venta) as fechaMasAntigua')
        )
            ->join('clientes', 'ventas.id_cliente', '=', 'clientes.id_cliente')
            ->join('usuarios', 'ventas.creado_por', '=', 'usuarios.id_usuario')
            ->where('ventas.estado', 'activo')
            ->having('saldoPendiente', '>', 0)
            ->groupBy('clientes.id_cliente', 'clientes.nombre', 'clientes.celular')
            ->orderByDesc('saldoPendiente')
            ->get();
    }
}
