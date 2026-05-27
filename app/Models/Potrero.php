<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Potrero extends Model
{
    use HasFactory;

    protected $table = 'potreros';
    protected $primaryKey = 'id_potrero';

    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = 'fecha_actualizacion';

    public function capacidades_historicas()
    {
        return $this->hasMany(CapacidadHistorica::class, 'id_potrero', 'id_potrero')->orderBy('fecha', 'ASC');
    }

    public function bovinos()
    {
        return $this->hasMany(Bovino::class, 'id_potrero', 'id_potrero')->orderBy('identificador', 'ASC');
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

    public function get_all_potreros()
    {
        return Potrero::with('capacidades_historicas', 'bovinos', 'creado', 'modificado', 'eliminado')->orderBy('nombre', 'ASC')->get();
    }

    public function get_potrero($id_potrero)
    {
        return Potrero::with('capacidades_historicas', 'bovinos', 'creado', 'modificado', 'eliminado')->find($id_potrero);
    }
}
