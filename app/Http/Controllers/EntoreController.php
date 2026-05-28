<?php

namespace App\Http\Controllers;

use App\Models\Entore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EntoreController extends Controller
{
    public function view_index()
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }

        return view('entores.index', [
            'head_title' => 'GESTIÓN DE ENTORES',
        ]);
    }

    public function view_details($entore)
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }

        $entore = (new Entore())->get_entore($entore);

        return view('entores.details', [
            'head_title' => 'ENTORE NÚMERO ' . $entore->id_entore,
            'entore' => $entore,
        ]);
    }

    public function listar()
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso',], 403);
        }

        $entores = (new Entore())->get_all_entores();
        return response()->json([
            'data' => $entores
        ]);
    }

    public function mostrar(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $entore = (new Entore())->get_entore($request->entore);
        return response()->json([
            'data' => $entore
        ]);
    }

    public function create(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $request->validate([
            'tipo_entore'  => ['required', 'in:unitoro,multitoro,inseminacion'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin'    => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'id_macho'     => ['nullable', 'integer'],
            'codigo_pajuela' => ['nullable', 'string', 'max:50'],
            'observaciones'  => ['nullable', 'string', 'max:250'],
            'hembras'        => ['required', 'array', 'min:1'],
            'hembras.*.id_hembra' => ['required', 'integer', 'exists:bovinos,id_bovino'],
            'machos'         => ['nullable', 'array'],
            'machos.*.id_macho'   => ['nullable', 'integer', 'exists:bovinos,id_bovino'],
        ]);

        if ($request->tipo_entore == 'unitoro' && empty($request->id_macho)) {
            return response()->json(['success' => false, 'message' => 'El campo id_macho es obligatorio para entores unitoro.'], 422);
        }

        if ($request->tipo_entore == 'multitoro' && empty($request->machos)) {
            return response()->json(['success' => false, 'message' => 'Debe seleccionar al menos un macho para entores multitoro.'], 422);
        }

        if ($request->tipo_entore == 'inseminacion' && empty($request->codigo_pajuela)) {
            return response()->json(['success' => false, 'message' => 'El campo código de pajuela es obligatorio para entores por inseminación.'], 422);
        }

        DB::beginTransaction();
        try {
            $entore = new Entore();
            $entore->tipo_entore    = $request->tipo_entore;

            if ($request->tipo_entore == 'unitoro') {
                $entore->id_macho = $request->id_macho;
                $entore->codigo_pajuela = null; // No hay código de pajuela en unitoro
            } else if ($request->tipo_entore == 'multitoro') {
                $entore->id_macho = null; // Para entores multitoro, el campo id_macho se deja nulo y se gestionan las relaciones con los machos seleccionados
                $entore->codigo_pajuela = null; // No hay código de pajuela en multitoro
            } else if ($request->tipo_entore == 'inseminacion') {
                $entore->codigo_pajuela = $request->codigo_pajuela;
                $entore->id_macho = null; // No hay macho directo en inseminación
            }

            $entore->fecha_inicio   = $request->fecha_inicio;
            $entore->fecha_fin      = $request->fecha_fin ?? null;
            $entore->observaciones  = $request->observaciones ?? null;
            $entore->creado_por     = session('id_usuario');
            $entore->save();

            // Adjuntar machos si hay (multitoro)
            if (!empty($request->machos) && $request->tipo_entore == 'multitoro') {
                $idsMachos = array_unique(array_column($request->machos, 'id_macho'));
                $entore->machos()->attach($idsMachos);
            }

            // Adjuntar hembras
            $idsHembras = array_unique(array_column($request->hembras, 'id_hembra'));
            $entore->hembras()->attach($idsHembras);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Entore creado correctamente',
                'entore'  => $entore
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id_entore)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $request->validate([
            'tipo_entore'  => ['required', 'in:unitoro,multitoro,inseminacion'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin'    => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'id_macho'     => ['nullable', 'integer'],
            'codigo_pajuela' => ['nullable', 'string', 'max:50'],
            'observaciones'  => ['nullable', 'string', 'max:250'],
            'hembras'        => ['required', 'array', 'min:1'],
            'hembras.*.id_hembra' => ['required', 'integer', 'exists:bovinos,id_bovino'],
            'machos'         => ['nullable', 'array'],
            'machos.*.id_macho'   => ['nullable', 'integer', 'exists:bovinos,id_bovino'],
        ]);

        if ($request->tipo_entore == 'unitoro' && empty($request->id_macho)) {
            return response()->json(['success' => false, 'message' => 'El campo id_macho es obligatorio para entores unitoro.'], 422);
        }

        if ($request->tipo_entore == 'multitoro' && empty($request->machos)) {
            return response()->json(['success' => false, 'message' => 'Debe seleccionar al menos un macho para entores multitoro.'], 422);
        }

        if ($request->tipo_entore == 'inseminacion' && empty($request->codigo_pajuela)) {
            return response()->json(['success' => false, 'message' => 'El campo código de pajuela es obligatorio para entores por inseminación.'], 422);
        }

        $entore = (new Entore())->get_entore($id_entore);

        DB::beginTransaction();
        try {
            $entore->tipo_entore      = $request->tipo_entore;

            if ($request->tipo_entore == 'unitoro') {
                $entore->id_macho = $request->id_macho;
                $entore->codigo_pajuela = null; // No hay código de pajuela en unitoro
            } else if ($request->tipo_entore == 'multitoro') {
                $entore->id_macho = null; // Para entores multitoro, el campo id_macho se deja nulo y se gestionan las relaciones con los machos seleccionados
                $entore->codigo_pajuela = null; // No hay código de pajuela en multitoro
            } else if ($request->tipo_entore == 'inseminacion') {
                $entore->codigo_pajuela = $request->codigo_pajuela;
                $entore->id_macho = null; // No hay macho directo en inseminación
            }

            $entore->fecha_inicio     = $request->fecha_inicio;
            $entore->fecha_fin        = $request->fecha_fin ?? null;
            $entore->observaciones    = $request->observaciones ?? null;
            $entore->modificado_por   = session('id_usuario');
            $entore->save();

            // Sincronizar machos (reemplaza los anteriores con los nuevos)
            if ($request->tipo_entore == 'multitoro') {
                $idsMachos = array_unique(array_column($request->machos, 'id_macho'));
                $entore->machos()->sync($idsMachos);
            } else {
                // Si no es multitoro, aseguramos que no queden machos relacionados
                $entore->machos()->sync([]);
            }

            // Sincronizar hembras
            $idsHembras = array_unique(array_column($request->hembras, 'id_hembra'));
            $entore->hembras()->sync($idsHembras);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Entore actualizado correctamente',
                'entore'  => $entore
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
            'id_entore' => ['required', 'integer']
        ]);

        $entore = (new Entore())->get_entore($request->id_entore);
        $entore->estado = $entore->estado == 'activo' ? 'inactivo' : 'activo';
        $entore->fecha_eliminacion = $entore->estado == 'inactivo' ? now() : null;
        $entore->eliminado_por = $entore->estado == 'inactivo' ? session('id_usuario') : null;
        $entore->save();

        return response()->json([
            'success' => true,
            'message' => $entore->estado == 'activo' ? 'El entore fue restaurado con éxito.' : 'El entore fue archivado con éxito.',
            'entore' => $entore
        ]);
    }
}
