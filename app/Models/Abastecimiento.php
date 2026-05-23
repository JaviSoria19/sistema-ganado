<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Abastecimiento extends Model
{
    use HasFactory;

    protected $table = 'abastecimientos';
    protected $primaryKey = 'idAbastecimiento';

    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = 'fecha_actualizacion';

    /** Relación uno a muchos con productos */
    public function productos()
    {
        return $this->hasMany(Producto::class, 'idAbastecimiento', 'idAbastecimiento');
    }

    /** Relación con atributo de auditoría */
    public function editor(){
        return $this->belongsTo(Usuario::class, 'modificado_por', 'id_usuario');
    }

    public function getAllAbastecimientos()
    {
        return Abastecimiento::with(['productos.marca','productos.empresa','productos.editor','editor'])->orderBy('idAbastecimiento','ASC')->get();
    }
    
    public function getAbastecimiento($idAbastecimiento)
    {
        return Abastecimiento::with(['productos.marca','productos.empresa','productos.editor','editor'])->find($idAbastecimiento);
    }
}
