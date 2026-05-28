<?php

namespace App\Http\Controllers;

use App\Models\Bovino;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BovinoController extends Controller
{
    public function view_index()
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }

        return view('bovinos.index', [
            'head_title' => 'GESTIÓN DE BOVINOS',
        ]);
    }

    public function view_details($bovino)
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }

        $bovino = (new Bovino())->get_bovino($bovino);

        return view('bovinos.details', [
            'head_title' => 'BOVINO: ' . $bovino->identificador,
            'bovino' => $bovino,
        ]);
    }

    public function listar(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso',], 403);
        }

        $id_potrero = $request->query('id_potrero');
        
        $origen = $request->query('origen');

        $genero = $request->query('genero');

        $bovinos = (new Bovino())->get_all_bovinos($id_potrero, $origen, $genero);

        return response()->json([
            'data' => $bovinos
        ]);
    }

    public function mostrar(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $bovino = (new Bovino())->get_bovino($request->bovino);
        return response()->json([
            'data' => $bovino
        ]);
    }

    public function create(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $request->validate([
            'tipo_bovino'  => ['required', 'in:unitoro,multitoro,inseminacion'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin'    => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'id_macho'     => ['nullable', 'integer', 'numeric'],
            'codigo_pajuela' => ['nullable', 'string', 'max:50'],
            'observaciones'  => ['nullable', 'string', 'max:250'],
            'hembras'        => ['required', 'array', 'min:1'],
            'hembras.*.id_hembra' => ['required', 'integer', 'exists:bovinos,id_bovino'],
            'machos'         => ['nullable', 'array'],
            'machos.*.id_macho'   => ['nullable', 'integer', 'exists:bovinos,id_bovino'],
        ]);

        DB::beginTransaction();
        try {
            $bovino = new Bovino();
            $bovino->id_macho       = $request->id_macho ?? null;
            $bovino->tipo_bovino    = $request->tipo_bovino;
            $bovino->fecha_inicio   = $request->fecha_inicio;
            $bovino->fecha_fin      = $request->fecha_fin ?? null;
            $bovino->codigo_pajuela = $request->codigo_pajuela ?? null;
            $bovino->observaciones  = $request->observaciones ?? null;
            $bovino->creado_por     = session('id_usuario');
            $bovino->save();

            // Adjuntar machos si hay (multitoro)
            if (!empty($request->machos)) {
                $idsMachos = array_column($request->machos, 'id_macho');
                $bovino->machos()->attach($idsMachos);
            }

            // Adjuntar hembras
            $idsHembras = array_column($request->hembras, 'id_hembra');
            $bovino->hembras()->attach($idsHembras);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bovino creado correctamente',
                'bovino'  => $bovino
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id_bovino)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $request->validate([
            'tipo_bovino'  => ['required', 'in:unitoro,multitoro,inseminacion'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin'    => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'id_macho'     => ['nullable', 'integer', 'numeric'],
            'codigo_pajuela' => ['nullable', 'string', 'max:50'],
            'observaciones'  => ['nullable', 'string', 'max:250'],
            'hembras'        => ['required', 'array', 'min:1'],
            'hembras.*.id_hembra' => ['required', 'integer', 'exists:bovinos,id_bovino'],
            'machos'         => ['nullable', 'array'],
            'machos.*.id_macho'   => ['nullable', 'integer', 'exists:bovinos,id_bovino'],
        ]);

        $bovino = (new Bovino())->get_bovino($id_bovino);

        DB::beginTransaction();
        try {
            $bovino->id_macho         = $request->id_macho ?? null;
            $bovino->tipo_bovino      = $request->tipo_bovino;
            $bovino->fecha_inicio     = $request->fecha_inicio;
            $bovino->fecha_fin        = $request->fecha_fin ?? null;
            $bovino->codigo_pajuela   = $request->codigo_pajuela ?? null;
            $bovino->observaciones    = $request->observaciones ?? null;
            $bovino->modificado_por   = session('id_usuario');
            $bovino->save();

            // Sincronizar machos (reemplaza los anteriores con los nuevos)
            $idsMachos = !empty($request->machos)
                ? array_column($request->machos, 'id_macho')
                : [];
            $bovino->machos()->sync($idsMachos);

            // Sincronizar hembras
            $idsHembras = array_column($request->hembras, 'id_hembra');
            $bovino->hembras()->sync($idsHembras);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bovino actualizado correctamente',
                'bovino'  => $bovino
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function delete(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $request->validate([
            'id_bovino' => ['required', 'numeric', 'integer']
        ]);

        $bovino = (new Bovino())->get_bovino($request->id_bovino);
        $bovino->estado = $bovino->estado == 'activo' ? 'inactivo' : 'activo';
        $bovino->fecha_eliminacion = $bovino->estado == 'inactivo' ? now() : null;
        $bovino->eliminado_por = $bovino->estado == 'inactivo' ? session('id_usuario') : null;
        $bovino->save();

        return response()->json([
            'success' => true,
            'message' => $bovino->estado == 'activo' ? 'El bovino fue restaurado con éxito.' : 'El bovino fue archivado con éxito.',
            'bovino' => $bovino
        ]);
    }
}
