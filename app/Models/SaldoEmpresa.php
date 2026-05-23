<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaldoEmpresa extends Model
{
    use HasFactory;

    protected $table = 'saldos_empresas';
    protected $primaryKey = 'idSaldoEmpresa';

    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = 'fecha_actualizacion';

    /** Relación FK con empresas */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'idEmpresa', 'idEmpresa');
    }

    /** Relación con atributo de auditoría */
    public function editor(){
        return $this->belongsTo(Usuario::class, 'modificado_por', 'id_usuario');
    }

    public function getAllSaldosEmpresas()
    {
        return SaldoEmpresa::with(['empresa','editor'])->orderBy('idSaldoEmpresa','ASC')->get();
    }
    
    public function getSaldoEmpresa($idSaldoEmpresa)
    {
        return SaldoEmpresa::with(['empresa','editor'])->find($idSaldoEmpresa);
    }
}
