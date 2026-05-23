<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';
    protected $primaryKey = 'idProducto';

    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = 'fecha_actualizacion';

    /** Relación muchos a muchos con ventas */
    public function ventas()
    {
        return $this->belongsToMany(
            Venta::class,
            'detalles_ventas',
            'idProducto',   // FK en la tabla pivote hacia productos
            'idVenta'       // FK en la tabla pivote hacia ventas
        )->withPivot('precioUSD');
    }

    /** Relación FK con empresas */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'idEmpresa', 'idEmpresa');
    }

    /** Relación FK con marcas */
    public function marca()
    {
        return $this->belongsTo(Marca::class, 'idMarca', 'idMarca');
    }

    /** Relación con atributo de auditoría */
    public function editor()
    {
        return $this->belongsTo(Usuario::class, 'modificado_por', 'id_usuario');
    }

    public function getAllProductos()
    {
        return Producto::with(['empresa', 'marca', 'editor'])->orderBy('idProducto', 'ASC')
        ->where('estado', '1')
        ->get();
    }

    public function getProducto($idProducto)
    {
        return Producto::with(['empresa', 'marca', 'editor'])->find($idProducto);
    }

    public function getProductoByCodigo($codigoProducto)
    {
        return Producto::with(['empresa', 'marca', 'editor'])->where(function ($query) use ($codigoProducto) {
            $query->where('codigoProducto', $codigoProducto)
                ->orWhere('identificador', $codigoProducto);
        })->first();
    }

    public function getAllProductosGroupByNombreProducto()
    {
        return Producto::groupBy('nombreProducto')->orderBy('nombreProducto')->get();
    }

    public function getProductosDisponiblesAgrupados()
    {
        return Producto::select(
            'productos.nombreProducto',
            'marcas.nombreMarca',
            DB::raw('COUNT(productos.idProducto) as cantidad'),
            DB::raw('SUM(productos.costoBaseUSD) as costoBaseUSD'),
            DB::raw('ROUND(SUM(productos.costoBaseUSD + (productos.costoBaseUSD * productos.traspasoPorcentaje) / 100 + productos.transporteUSD), 2) as costoFinalUSD')
        )
            ->join('marcas', 'productos.idMarca', '=', 'marcas.idMarca')
            ->where('productos.estado', 1)
            ->groupBy('productos.nombreProducto', 'marcas.nombreMarca')
            ->orderBy('productos.nombreProducto', 'ASC')
            ->get();
    }

    public function getProductosDisponiblesAgrupadosPorColor()
    {
        return Producto::select(
            'productos.nombreProducto',
            'productos.color',
            'marcas.nombreMarca',
            DB::raw('COUNT(productos.idProducto) as cantidad'),
            DB::raw('SUM(productos.costoBaseUSD) as costoBaseUSD'),
            DB::raw('ROUND(SUM(productos.costoBaseUSD + (productos.costoBaseUSD * productos.traspasoPorcentaje) / 100 + productos.transporteUSD), 2) as costoFinalUSD')
        )
            ->join('marcas', 'productos.idMarca', '=', 'marcas.idMarca')
            ->where('productos.estado', 1)
            ->groupBy('productos.nombreProducto', 'productos.color', 'marcas.nombreMarca')
            ->orderBy('productos.nombreProducto', 'ASC')
            ->orderBy('productos.color', 'ASC')
            ->get();
    }

    
}
