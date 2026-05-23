<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PedidoEmpresa extends Model
{
    use HasFactory;

    protected $table = 'pedidos_empresas';
    protected $primaryKey = 'idPedidoEmpresa';
    public $timestamps = true;

    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = 'fecha_actualizacion';

    protected $fillable = [
        'idEmpresa',
    ];

    /** Relación uno a muchos con detalles_pedidos_empresas */
    public function detalles()
    {
        return $this->hasMany(DetallePedidoEmpresa::class, 'idPedidoEmpresa', 'idPedidoEmpresa');
    }

    /** Relación FK con empresas */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'idEmpresa', 'idEmpresa');
    }

    /** Relación con atributo de auditoría */
    public function editor(){
        return $this->belongsTo(Usuario::class, 'modificado_por', 'id_usuario');
    }

    public function getAllPedidosEmpresas()
    {
        return PedidoEmpresa::with(['detalles','empresa','editor'])->orderBy('idPedidoEmpresa','ASC')->get();
    }
    
    public function getPedidoEmpresa($idPedidoEmpresa)
    {
        return PedidoEmpresa::with(['detalles','empresa','editor'])->find($idPedidoEmpresa);
    }
}
