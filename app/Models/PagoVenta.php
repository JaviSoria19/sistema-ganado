<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoVenta extends Model
{
    use HasFactory;

    protected $table = 'pagos_ventas';
    protected $primaryKey = 'idPagoVenta';

    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = 'fecha_actualizacion';

    /** Relación FK con ventas */
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'idVenta', 'idVenta');
    }

    /** Relación con atributo de auditoría */
    public function editor(){
        return $this->belongsTo(Usuario::class, 'modificado_por', 'id_usuario');
    }

    public function getAllPagosVentas()
    {
        return PagoVenta::with(['venta','editor'])->orderBy('idPagoVenta','ASC')->get();
    }
    
    public function getPagoVenta($idPagoVenta)
    {
        return PagoVenta::with(['venta','editor'])->find($idPagoVenta);
    }
}
