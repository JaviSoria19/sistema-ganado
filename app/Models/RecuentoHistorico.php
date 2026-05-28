<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecuentoHistorico extends Model
{
    use HasFactory;

    protected $table = 'recuentos_historicos';
    protected $primaryKey = 'id_recuento_historico';

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

    public function get_all_recuentos_historicos()
    {
        return RecuentoHistorico::with('bovino', 'creado', 'modificado', 'eliminado')->orderBy('id_bovino', 'ASC')->get();
    }

    public function get_recuentos_historicos_por_bovino($id_bovino)
    {
        return RecuentoHistorico::with('bovino', 'creado', 'modificado', 'eliminado')->where('id_bovino', $id_bovino)->orderBy('fecha', 'ASC')->get();
    }

    public function get_recuento_historico($id_recuento_historico)
    {
        return RecuentoHistorico::with('bovino', 'creado', 'modificado', 'eliminado')->find($id_recuento_historico);
    }
}
