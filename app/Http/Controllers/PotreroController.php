<?php

namespace App\Http\Controllers;

use App\Http\Requests\PotreroValidation;
use App\Models\CapacidadHistorica;
use App\Models\Potrero;
use Illuminate\Http\Request;

class PotreroController extends Controller
{
    public function view_index()
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }

        return view('potreros.index', [
            'head_title' => 'GESTIÓN DE POTREROS',
        ]);
    }

    public function view_details($potrero)
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }

        $potrero = (new Potrero())->get_potrero($potrero);

        return view('potreros.details', [
            'head_title' => 'POTRERO: ' . $potrero->nombre,
            'potrero' => $potrero,
        ]);
    }

    public function listar()
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso',], 403);
        }

        $potreros = (new Potrero())->get_all_potreros();
        return response()->json([
            'data' => $potreros
        ]);
    }

    public function mostrar(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $potrero = (new Potrero())->get_potrero($request->potrero);
        return response()->json([
            'data' => $potrero
        ]);
    }

    public function create(PotreroValidation $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        try {
            $potrero = new Potrero();
            $potrero->nombre = $request->nombre;
            $potrero->ubicacion = $request->ubicacion;
            $potrero->superficie = $request->superficie;
            $potrero->tipo_pasto = $request->tipo_pasto;
            $potrero->estado_potrero = $request->estado_potrero;
            $potrero->disponibilidad_agua = $request->disponibilidad_agua;
            $potrero->capacidad_carga_actual = $request->capacidad_carga_actual;
            $potrero->creado_por = session('id_usuario');
            $potrero->save();

            $capacidad_historica = new CapacidadHistorica();
            $capacidad_historica->id_potrero = $potrero->id_potrero;
            $capacidad_historica->capacidad_carga = $request->capacidad_carga_actual;
            $capacidad_historica->fecha = date('Y-m-d');
            $capacidad_historica->creado_por = session('id_usuario');
            $capacidad_historica->save();

            return response()->json([
                'success' => true,
                'message' => 'Potrero creado correctamente',
                'potrero' => $potrero
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el potrero: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(PotreroValidation $request, $id_potrero)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $potrero = (new Potrero())->get_potrero($id_potrero);

        // Si la capacidad de carga actual ha cambiado, se registra un nuevo registro en la tabla de capacidad histórica.
        if($potrero->capacidad_carga_actual != $request->capacidad_carga_actual) {
            $capacidad_historica = new CapacidadHistorica();
            $capacidad_historica->id_potrero = $potrero->id_potrero;
            $capacidad_historica->capacidad_carga = $request->capacidad_carga_actual;
            $capacidad_historica->fecha = date('Y-m-d');
            $capacidad_historica->creado_por = session('id_usuario');
            $capacidad_historica->save();
        }

        $potrero->nombre = $request->nombre;
        $potrero->ubicacion = $request->ubicacion;
        $potrero->superficie = $request->superficie;
        $potrero->tipo_pasto = $request->tipo_pasto;
        $potrero->estado_potrero = $request->estado_potrero;
        $potrero->disponibilidad_agua = $request->disponibilidad_agua;
        $potrero->capacidad_carga_actual = $request->capacidad_carga_actual;
        $potrero->modificado_por = session('id_usuario');
        $potrero->save();

        return response()->json([
            'success' => true,
            'message' => 'Potrero actualizado correctamente',
            'potrero' => $potrero
        ]);
    }

    public function delete(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $request->validate([
            'id_potrero' => ['required', 'numeric', 'integer']
        ]);

        $potrero = (new Potrero())->get_potrero($request->id_potrero);
        $potrero->estado = $potrero->estado == 'activo' ? 'inactivo' : 'activo';
        $potrero->fecha_eliminacion = $potrero->estado == 'inactivo' ? now() : null;
        $potrero->eliminado_por = $potrero->estado == 'inactivo' ? session('id_usuario') : null;
        $potrero->save();

        return response()->json([
            'success' => true,
            'message' => $potrero->estado == 'activo' ? 'El potrero fue restaurado con éxito.' : 'El potrero fue archivado con éxito.',
            'potrero' => $potrero
        ]);
    }
}
