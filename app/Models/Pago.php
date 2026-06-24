<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';
    protected $primaryKey = 'id_pago';

    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = 'fecha_actualizacion';

    /** Relación FK con ventas */
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'id_venta', 'id_venta');
    }

    /** Relación con atributos de auditoría */
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

    public function get_all_pagos_ventas()
    {
        return Pago::with('venta','creado','modificado','eliminado')->orderBy('id_pago','ASC')->get();
    }
    
    public function get_pago_venta($id_pago)
    {
        return Pago::with('venta','creado','modificado','eliminado')->findOrFail($id_pago);
    }
}
