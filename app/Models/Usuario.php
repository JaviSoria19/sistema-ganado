<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';

    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = 'fecha_actualizacion';

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

    public function get_all_usuarios()
    {
        return Usuario::with('creado', 'modificado', 'eliminado')->get();
    }

    public function get_usuario(int $id_usuario)
    {
        return Usuario::with('creado', 'modificado', 'eliminado')->find($id_usuario);
    }

    /**Función utilizada para verificar y crear la sesión del Usuario.*/
    public function login(string $usuario)
    {
        return Usuario::where('usuario', $usuario)->first();
    }

    /**Función para destruir y cerrar la sesión.*/
    public function logout()
    {
        session()->flush();
    }
}
