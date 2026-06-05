<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function get_all_ventas(array $filters = [])
    {
        $query = Venta::with('bovinos', 'pagos', 'cliente', 'creado', 'modificado', 'eliminado')->orderBy('id_venta', 'ASC');

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

        return $query->get();
    }

    public function get_venta($id_venta)
    {
        return Venta::with('bovinos', 'pagos', 'cliente', 'creado', 'modificado', 'eliminado')->find($id_venta);
    }
}
