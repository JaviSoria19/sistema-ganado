<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parametro extends Model
{
    use HasFactory;

    protected $table = 'parametros';
    protected $primaryKey = 'id_parametro';

    const CREATED_AT = null;
    const UPDATED_AT = null;

    /** Relación con atributo de auditoría */
    public function modificado()
    {
        return $this->belongsTo(Usuario::class, 'modificado_por', 'id_usuario');
    }
    
    public function get_parametro()
    {
        return Parametro::with('modificado')->find(1);
    }
}
