<?php

namespace App\Http\Controllers;

use App\Http\Requests\MarcaValidation;
use App\Models\Marca;
use Illuminate\Http\Request;

class MarcaController extends Controller
{
    public function view_index()
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }

        return view('marcas.index', [
            'headTitle' => 'GESTIÓN DE MARCAS'
        ]);
    }

    public function listarMarcas()
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $marcas = (new Marca())->getAllMarcas();
        return response()->json([
            'data' => $marcas
        ]);
    }

    public function mostrarMarca(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $marca = (new Marca())->getMarca($request->marca);
        return response()->json([
            'data' => $marca
        ]);
    }

    public function create(MarcaValidation $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $marca = new Marca();
        $marca->nombreMarca = strtoupper($request->nombreMarca);
        $marca->bonoMarcaPorcentaje = $request->bonoMarcaPorcentaje;
        $marca->save();
        return response()->json([
            'success' => true,
            'message' => 'Marca creada correctamente',
            'marca' => $marca
        ]);
    }

    public function update(MarcaValidation $request, $idMarca)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $marca = (new Marca())->getMarca($idMarca);
        $marca->nombreMarca = strtoupper($request->nombreMarca);
        $marca->bonoMarcaPorcentaje = strtoupper($request->bonoMarcaPorcentaje);
        $marca->modificado_por = session('id_usuario');
        $marca->save();

        return response()->json([
            'success' => true,
            'message' => 'Marca actualizada correctamente',
            'marca' => $marca
        ]);
    }

    public function deleteOrRestore(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $request->validate([
            'idMarca' => ['required', 'numeric', 'integer']
        ]);

        $marca = (new Marca())->getMarca($request->idMarca);
        $marca->estado = $marca->estado == '1' ? '0' : '1';
        $marca->modificado_por = session('id_usuario');
        $marca->save();
        return response()->json([
            'success' => true,
            'message' => $marca->estado == '1' ? 'La marca fue habilitada con éxito' : 'La marca fue deshabilitada con éxito' ,
            'marca' => $marca
        ]);
    }
}
