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
    
    public function potrero()
    {
        return $this->belongsTo(Potrero::class, 'id_potrero', 'id_potrero');
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

    public function get_all_capacidades_historicas($id_potrero = null)
    {
        $query = CapacidadHistorica::with('potrero', 'creado', 'modificado', 'eliminado')->orderBy('id_potrero', 'ASC');

        if ($id_potrero !== null) {
            $query->where('id_potrero', $id_potrero);
        }

        return $query->get();
    }
    
    public function get_capacidad_historica($id_capacidad_historica)
    {
        return CapacidadHistorica::with('potrero', 'creado', 'modificado', 'eliminado')->findOrFail($id_capacidad_historica);
    }
}
