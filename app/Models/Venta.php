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
        return Venta::with('bovinos', 'pagos', 'cliente', 'creado', 'modificado', 'eliminado')->findOrFail($id_venta);
    }

    public function dashboard_get_estadisticas_ventas()
    {
        $hoy = Carbon::today();

        $periodos = [
            'hoy'    => [$hoy->copy(),                    $hoy->copy()->endOfDay()],
            'semana' => [$hoy->copy()->startOfWeek(),     $hoy->copy()->endOfWeek()],
            'mes'    => [$hoy->copy()->startOfMonth(),    $hoy->copy()->endOfMonth()],
        ];

        $resultados = [];

        foreach ($periodos as $periodo => [$inicio, $fin]) {

            // Ventas activas en el rango
            $ventas = DB::table('ventas as v')
                ->where('v.estado', 'activo')
                ->whereBetween('v.fecha_venta', [
                    $inicio->toDateString(),
                    $fin->toDateString(),
                ]);

            // Cantidad de ventas y suma de totales brutos
            $stats = (clone $ventas)
                ->selectRaw('COUNT(*) as cantidad, COALESCE(SUM(total), 0) as total_bruto')
                ->first();

            // Pagos recibidos solo para esas ventas
            $totalPagado = DB::table('pagos as p')
                ->join('ventas as v', 'p.id_venta', '=', 'v.id_venta')
                ->where('v.estado', 'activo')
                ->where('p.estado', 'activo')
                ->whereBetween('v.fecha_venta', [
                    $inicio->toDateString(),
                    $fin->toDateString(),
                ])
                ->sum('p.monto');

            // Bovinos vendidos en esas ventas
            $bovinosVendidos = DB::table('ventas_detalles as vd')
                ->join('ventas as v', 'vd.id_venta', '=', 'v.id_venta')
                ->where('v.estado', 'activo')
                ->whereBetween('v.fecha_venta', [
                    $inicio->toDateString(),
                    $fin->toDateString(),
                ])
                ->count();

            $resultados[$periodo] = [
                'cantidadVentas'  => (int) $stats->cantidad,
                'ingresos'        => (float) $totalPagado,
                'bovinosVendidos' => (int) $bovinosVendidos,
            ];
        }

        return $resultados;
    }

    public function dashboard_get_clientes_con_saldo()
    {
        $pagosSubquery = DB::table('pagos as p')
            ->selectRaw('p.id_venta, COALESCE(SUM(p.monto), 0) as total_pagado')
            ->where('p.estado', 'activo')
            ->groupBy('p.id_venta');

        return DB::table('ventas as v')
            ->select(
                'u.usuario',
                'c.id_cliente',
                'c.nombre',
                'c.celular',
                'c.estancia',
                DB::raw('SUM(v.total - COALESCE(pag.total_pagado, 0)) as saldoPendiente'),
                DB::raw('MIN(v.fecha_venta) as fechaMasAntigua')
            )
            ->join('clientes as c', 'v.id_cliente', '=', 'c.id_cliente')
            ->join('usuarios as u', 'v.creado_por', '=', 'u.id_usuario')
            ->leftJoinSub($pagosSubquery, 'pag', 'pag.id_venta', '=', 'v.id_venta')
            ->where('v.estado', 'activo')
            ->groupBy('c.id_cliente', 'c.nombre', 'c.celular', 'c.estancia', 'u.usuario')
            ->havingRaw('SUM(v.total - COALESCE(pag.total_pagado, 0)) > 0')
            ->orderByDesc('saldoPendiente')
            ->get();
    }
}
