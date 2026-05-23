<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    use HasFactory;

    protected $table = 'empleados';
    protected $primaryKey = 'idEmpleado';

    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = 'fecha_actualizacion';
    
    /** Relación con atributo de auditoría */
    public function editor(){
        return $this->belongsTo(Usuario::class, 'modificado_por', 'id_usuario');
    }
    
    public function getAllEmpleados()
    {
        return Empleado::with('editor')->orderBy('idEmpleado','ASC')->get();
    }
    
    public function getEmpleado($idEmpleado)
    {
        return Empleado::with('editor')->find($idEmpleado);
    }
}