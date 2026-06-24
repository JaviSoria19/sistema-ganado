<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesajeHistorico extends Model
{
    use HasFactory;

    protected $table = 'pesajes_historicos';
    protected $primaryKey = 'id_pesaje_historico';

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

    public function get_all_pesajes_historicos($id_bovino = null)
    {
        $query = PesajeHistorico::with('bovino', 'creado', 'modificado', 'eliminado')->orderBy('id_bovino', 'ASC');

        if ($id_bovino) {
            $query->where('id_bovino', $id_bovino);
        }

        return $query->get();
    }

    public function get_pesaje_historico($id_pesaje_historico)
    {
        return PesajeHistorico::with('bovino', 'creado', 'modificado', 'eliminado')->findOrFail($id_pesaje_historico);
    }
}
