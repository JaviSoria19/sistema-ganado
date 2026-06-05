<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PalpacionHistorica extends Model
{
    use HasFactory;

    protected $table = 'palpaciones_historicas';
    protected $primaryKey = 'id_palpacion_historica';

    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = 'fecha_actualizacion';
    
    public function bovino()
    {
        return $this->belongsTo(Bovino::class, 'id_bovino', 'id_bovino');
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

    public function get_all_palpaciones_historicas($id_bovino = null)
    {
        $query = PalpacionHistorica::with('bovino', 'creado', 'modificado', 'eliminado')->orderBy('id_bovino', 'ASC');

        if ($id_bovino) {
            $query->where('id_bovino', $id_bovino);
        }

        return $query->get();
    }

    public function get_palpacion_historica($id_palpacion_historica)
    {
        return PalpacionHistorica::with('bovino', 'creado', 'modificado', 'eliminado')->find($id_palpacion_historica);
    }
}
