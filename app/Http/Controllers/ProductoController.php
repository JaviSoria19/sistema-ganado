<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductoValidation;
use App\Models\Abastecimiento;
use App\Models\Producto;
use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\Marca;
use App\Models\Parametro;
use Carbon\Carbon;

class ProductoController extends Controller
{
    public function view_index()
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }

        $productos = (new Producto())->getAllProductosGroupByNombreProducto();
        $parametro = (new Parametro())->get_parametro();

        $productos_cantidades_disponibles = (new Producto())->getProductosDisponiblesAgrupados();
        $productos_cantidades_disponibles_color = (new Producto())->getProductosDisponiblesAgrupadosPorColor();
        $abastecimientos = (new Abastecimiento())->getAllAbastecimientos();
        $empresas = (new Empresa())->getAllEmpresas();
        $marcas = (new Marca())->getAllMarcas();

        return view('productos.index', [
            'head_title' => 'GESTIÓN DE PRODUCTOS',
            'productos' => $productos,
            'productos_cantidades_disponibles' => $productos_cantidades_disponibles,
            'productos_cantidades_disponibles_color' => $productos_cantidades_disponibles_color,
            'parametro' => $parametro,
            'empresas' => $empresas,
            'marcas' => $marcas,
            'abastecimientos' => $abastecimientos,
        ]);
    }

    public function listarProductos()
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $productos = (new Producto())->getAllProductos();
        return response()->json([
            'data' => $productos
        ]);
    }

    public function mostrarProducto(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $producto = (new Producto())->getProducto($request->producto);
        return response()->json([
            'data' => $producto
        ]);
    }

    public function mostrarProductoPorCodigo(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $producto = (new Producto())->getProductoByCodigo($request->producto);
        return response()->json([
            'data' => $producto
        ]);
    }

    public function create(ProductoValidation $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $producto = new Producto();
        $producto->idEmpresa = $request->idEmpresa;
        $producto->idMarca = $request->idMarca;
        $producto->idAbastecimiento = $request->idAbastecimiento;
        $producto->nombreProducto = strtoupper($request->nombreProducto);
        $producto->color = strtoupper($request->color);
        $producto->identificador = trim($request->identificador);
        $producto->codigoProducto = $request->idAbastecimiento . '-' . $request->codigoProducto;
        $producto->costoBaseUSD = $request->costoBaseUSD;
        $producto->traspasoPorcentaje = $request->traspasoPorcentaje;
        $producto->transporteUSD = $request->transporteUSD;
        $producto->precioVentaUSD = $request->precioVentaUSD;
        $producto->modificado_por = session('id_usuario');
        $producto->save();

        return response()->json([
            'success' => true,
            'message' => 'Producto registrado correctamente',
            'producto' => $producto
        ]);
    }

    public function update(ProductoValidation $request, $idProducto)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $producto = (new Producto())->getProducto($idProducto);
        $producto->nombreProducto = strtoupper($request->nombreProducto);
        $producto->color = strtoupper($request->color);
        $producto->identificador = trim($request->identificador);
        $producto->costoBaseUSD = $request->costoBaseUSD;
        $producto->traspasoPorcentaje = $request->traspasoPorcentaje;
        $producto->transporteUSD = $request->transporteUSD;
        $producto->precioVentaUSD = $request->precioVentaUSD;
        $producto->modificado_por = session('id_usuario');
        $producto->save();

        return response()->json([
            'success' => true,
            'message' => 'Producto actualizado correctamente',
            'producto' => $producto
        ]);
    }

    public function delete(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $request->validate([
            'idProducto' => ['required', 'numeric', 'integer']
        ]);

        $producto = (new Producto())->getProducto($request->idProducto);
        if ($producto->estado != '2') {
            $producto->estado = $producto->estado == '1' ? '0' : '1';
            $producto->fechaEliminacion = $producto->estado == '0' ? Carbon::now() : null ;
            $producto->modificado_por = session('id_usuario');
            $producto->save();
        } else {
            return response()->json([
                'success' => true,
                'message' => 'El producto ya fue vendido, no se puede eliminar o restaurar',
                'producto' => $producto
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $producto->estado == '1' ? 'El producto fue restaurado con éxito y ahora está disponible para su venta.' : 'El producto fue eliminado con éxito y ya no está disponible para su venta.',
            'producto' => $producto
        ]);
    }
}
