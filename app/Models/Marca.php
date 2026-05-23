<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    use HasFactory;

    protected $table = 'marcas';
    protected $primaryKey = 'idMarca';

    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = 'fecha_actualizacion';
    
    /** Relación con atributo de auditoría */
    public function editor(){
        return $this->belongsTo(Usuario::class, 'modificado_por', 'id_usuario');
    }

    public function getAllMarcas()
    {
        return Marca::with('editor')->orderBy('idMarca','ASC')->get();
    }
    
    public function getMarca($idMarca)
    {
        return Marca::with('editor')->find($idMarca);
    }
}