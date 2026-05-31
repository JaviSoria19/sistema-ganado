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

    /** Entores en los que participó como hembra */
    public function entores_como_hembra()
    {
        return $this->belongsToMany(
            Entore::class,
            'entores_detalles',  // Tabla pivote
            'id_hembra',         // FK en la tabla pivote hacia bovinos
            'id_entore'          // FK en la tabla pivote hacia entores
        );
    }

    /** Entores en los que participó como macho */
    public function entores_como_macho()
    {
        return $this->belongsToMany(
            Entore::class,
            'entores_machos',    // Tabla pivote
            'id_macho',          // FK en la tabla pivote hacia bovinos
            'id_entore'          // FK en la tabla pivote hacia entores
        );
    }

    public function ventas()
    {
        return $this->belongsToMany(
            Venta::class,        // Modelo relacionado
            'ventas_detalles',   // Tabla pivote
            'id_bovino',         // FK en la tabla pivote hacia bovinos
            'id_venta'           // FK en la tabla pivote hacia ventas
        )->withPivot('precio_fijo', 'precio_kg', 'destare', 'rendimiento', 'kg_peso_vivo', 'kg_peso_gancho', 'subtotal', 'observacion');
    }

    public function recuentos_historicos()
    {
        return $this->hasMany(RecuentoHistorico::class, 'id_bovino', 'id_bovino');
    }

    public function pesajes_historicos()
    {
        return $this->hasMany(PesajeHistorico::class, 'id_bovino', 'id_bovino')->orderBy('fecha', 'ASC');
    }

    public function potrero()
    {
        return $this->belongsTo(Potrero::class, 'id_potrero', 'id_potrero');
    }

    public function entore(){
        return $this->belongsTo(Entore::class, 'id_entore', 'id_entore');
    }

    public function padre(){
        return $this->belongsTo(Bovino::class, 'id_padre', 'id_bovino');
    }

    public function madre(){
        return $this->belongsTo(Bovino::class, 'id_madre', 'id_bovino');
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

    public function get_all_bovinos($id_potrero = null, $origen = null, $genero = null, $estado = null)
    {
        $query = Bovino::with('potrero', 'entore', 'padre', 'madre', 'creado', 'modificado', 'eliminado')->orderBy('fecha_nacimiento', 'ASC');

        if ($id_potrero) {
            $query->where('id_potrero', $id_potrero);
        }

        if ($origen) {
            $query->where('origen', $origen);
        }        

        if ($genero) {
            $query->where('genero', $genero);
        }

        if ($estado) {
            $query->where('estado', $estado);
        }

        return $query->get();
    }

    public function get_bovino($id_bovino)
    {
        return Bovino::with('potrero', 'entore', 'padre', 'madre', 'entores_como_hembra', 'entores_como_macho', 'recuentos_historicos', 'pesajes_historicos', 'ventas', 'creado', 'modificado', 'eliminado')->find($id_bovino);
    }
}
