<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entore extends Model
{
    use HasFactory;

    protected $table = 'entores';
    protected $primaryKey = 'id_entore';

    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = 'fecha_actualizacion';

    /** Relación muchos a muchos con bovinos hembra (tabla pivote: entores_detalles) */
    public function hembras()
    {
        return $this->belongsToMany(
            Bovino::class,
            'entores_detalles',  // Tabla pivote
            'id_entore',         // FK en la tabla pivote hacia entores
            'id_hembra'          // FK en la tabla pivote hacia bovinos
        )->orderBy('fecha_nacimiento', 'ASC');
    }

    /** Relación muchos a muchos con bovinos macho (tabla pivote: entores_machos) */
    public function machos()
    {
        return $this->belongsToMany(
            Bovino::class,
            'entores_machos',    // Tabla pivote
            'id_entore',         // FK en la tabla pivote hacia entores
            'id_macho'           // FK en la tabla pivote hacia bovinos
        )->orderBy('fecha_nacimiento', 'ASC');
    }

    public function macho()
    {
        return $this->belongsTo(Bovino::class, 'id_macho', 'id_bovino');
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

    public function get_all_entores()
    {
        return Entore::with('macho.potrero', 'machos.potrero', 'hembras.potrero', 'creado', 'modificado', 'eliminado')->orderBy('fecha_inicio', 'DESC')->get();
    }

    public function get_entore($id_entore)
    {
        return Entore::with('macho.potrero', 'machos.potrero', 'hembras.potrero', 'creado', 'modificado', 'eliminado')->findOrFail($id_entore);
    }
}
