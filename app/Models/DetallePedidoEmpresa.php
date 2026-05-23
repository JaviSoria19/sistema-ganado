<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetallePedidoEmpresa extends Model
{
    use HasFactory;

    protected $table = 'detalles_pedidos_empresas';
    protected $primaryKey = 'idDetallePedidoEmpresa';

    const CREATED_AT = null;
    const UPDATED_AT = null;

    /** Relación FK con pedidos_empresas */
    public function pedido_empresa()
    {
        return $this->belongsTo(PedidoEmpresa::class, 'idPedidoEmpresa', 'idPedidoEmpresa');
    }
}
