<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bovino extends Model
{
    use HasFactory;

    protected $table = 'bovinos';
    protected $primaryKey = 'id_bovino';

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

    public function get_all_bovinos()
    {
        return Bovino::with('potrero', 'creado', 'modificado', 'eliminado')->orderBy('identificador', 'ASC')->get();
    }

    public function get_bovino($id_bovino)
    {
        return Bovino::with('potrero', 'creado', 'modificado', 'eliminado')->find($id_bovino);
    }
}
