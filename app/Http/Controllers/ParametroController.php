<?php

namespace App\Http\Controllers;

use App\Models\Parametro;
use Illuminate\Http\Request;

class ParametroController extends Controller
{
    public function view_index()
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }

        $parametro = (new Parametro())->get_parametro();

        return view('parametros.index', [
            'head_title' => 'PARÁMETROS',
            'parametro' => $parametro,
        ]);
    }
    public function update(Request $request, $id_parametro)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }
        $request->validate([
            'unidad_animal' => ['required', 'numeric', 'min:0.00', 'max:999.99','regex:/^\d+(\.\d{1,2})?$/'],
        ]);

        $parametro = (new Parametro())->get_parametro();
        $parametro->unidad_animal = $request->unidad_animal;
        $parametro->fecha_actualizacion = now();
        $parametro->modificado_por = session('id_usuario');
        $parametro->save();

        session(['unidad_animal' => $parametro->unidad_animal]);
        
        return response()->json([
            'success' => true,
            'message' => 'Parametro actualizado correctamente',
            'parametro' => $parametro->load(['modificado']),
        ]);
    }
}
