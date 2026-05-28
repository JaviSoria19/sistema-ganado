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
            Bovino::class,           // Modelo relacionado
            'ventas_detalles',       // Tabla pivote
            'id_venta',              // FK en la tabla pivote hacia ventas
            'id_bovino'              // FK en la tabla pivote hacia bovinos
        )->withPivot('precio_fijo', 'precio_kg', 'destare', 'rendimiento', 'kg_peso_vivo', 'kg_peso_gancho', 'subtotal', 'observacion');
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

    public function get_all_ventas($fecha_inicio = null, $fecha_fin = null, $creado_por = null, $estado = null)
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

        if ($fecha_inicio && $fecha_fin) {
            $query->whereBetween('fecha_registro', [
                $fecha_inicio . ' 00:00:00',
                $fecha_fin . ' 23:59:59'
            ]);
        }

        if ($creado_por) {
            $query->where('creado_por', $creado_por);
        }

        if (!is_null($estado)) {
            $query->where('estado', $estado);
        }

        return $query->get();
    }
    
    public function get_venta($id_venta)
    {
        return Venta::with('bovinos', 'pagos', 'cliente', 'creado', 'modificado', 'eliminado')->find($id_venta);
    }
}
