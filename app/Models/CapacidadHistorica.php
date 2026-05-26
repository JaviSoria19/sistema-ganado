<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CapacidadHistorica extends Model
{
    use HasFactory;

    protected $table = 'capacidades_historicas';
    protected $primaryKey = 'id_capacidad_historica';

    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = 'fecha_actualizacion';
    
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

    public function get_all_capacidades_historicas()
    {
        return CapacidadHistorica::with('creado', 'modificado', 'eliminado')->orderBy('id_potrero', 'ASC')->get();
    }
    
    public function get_capacidad_historica($id_capacidad_historica)
    {
        return CapacidadHistorica::with('creado', 'modificado', 'eliminado')->find($id_capacidad_historica);
    }
}
