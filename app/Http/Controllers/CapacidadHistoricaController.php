<?php

namespace App\Http\Controllers;

use App\Models\CapacidadHistorica;
use App\Models\Potrero;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CapacidadHistoricaController extends Controller
{
    public function view_index()
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }

        return view('capacidades_historicas.index', [
            'head_title' => 'CAPACIDADES HISTÓRICAS',
        ]);
    }

    public function view_crear()
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }

        return view('capacidades_historicas.crear', [
            'head_title' => 'REGISTRAR CAPACIDADES HISTÓRICAS',
        ]);
    }

    public function listar(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso',], 403);
        }

        $id_potrero = $request->query('id_potrero');

        $capacidades_historicas = (new CapacidadHistorica())->get_all_capacidades_historicas($id_potrero);

        return response()->json([
            'data' => $capacidades_historicas
        ]);
    }

    public function mostrar(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $capacidad_historica = (new CapacidadHistorica())->get_capacidad_historica($request->capacidad_historica);

        return response()->json([
            'data' => $capacidad_historica
        ]);
    }

    public function create(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $request->validate([
            'capacidades_historicas'             => 'required|array|min:1',
            /* Nombre del potrero */
            'capacidades_historicas.*.nombre'   => 'required|string|max:100',
            'capacidades_historicas.*.capacidad_carga'      => 'required|numeric',
            'capacidades_historicas.*.fecha'     => 'required|date',
        ]);

        /* Validar que cada nombre corresponda a un potrero existente */
        $errores = [];

        foreach ($request->capacidades_historicas as $index => $ch) {
            $potrero = Potrero::where('nombre', $ch['nombre'])
                ->where('estado', 'activo')
                ->first();

            if (!$potrero) {
                $errores[] = "Fila " . ($index + 1) . ": No existe un potrero activo con nombre \"{$ch['nombre']}\".";
            } else {
                /* Inyectar el id_potrero resuelto para usarlo en la transacción */
                $request->merge([
                    'capacidades_historicas' => array_replace(
                        $request->capacidades_historicas,
                        [$index => array_merge($ch, ['id_potrero' => $potrero->id_potrero])]
                    )
                ]);
            }
        }

        // Validar que no exista ya una capacidad histórica para ese potrero en esa fecha
        foreach ($request->capacidades_historicas as $index => $ch) {

            // Si esta fila ya tiene error (no se resolvió el potrero), saltarla
            if (!isset($ch['id_potrero'])) {
                continue;
            }

            $existe = CapacidadHistorica::where('id_potrero', $ch['id_potrero'])
                ->where('fecha', $ch['fecha'])
                ->where('estado', 'activo')
                ->exists();

            if ($existe) {
                $fechaFormateada = (new \DateTime($ch['fecha']))->format('d/m/Y');
                $errores[] = "Fila " . ($index + 1) . ": Ya existe una capacidad histórica para el potrero \"{$ch['nombre']}\" en la fecha {$fechaFormateada}.";
            }
        }

        if (!empty($errores)) {
            return response()->json([
                'success' => false,
                'message' => 'Algunos potreros no fueron encontrados o ya tienen una capacidad histórica registrada en la fecha indicada.',
                'errores' => $errores,
            ], 422);
        }

        DB::beginTransaction();

        try {
            foreach ($request->capacidades_historicas as $ch) {
                $capacidad_historica = new CapacidadHistorica();
                $capacidad_historica->id_potrero = $ch['id_potrero'];
                $capacidad_historica->capacidad_carga      = $ch['capacidad_carga'];
                $capacidad_historica->fecha     = $ch['fecha'];
                $capacidad_historica->creado_por = session('id_usuario');
                $capacidad_historica->save();

                /* Actualizar capacidad_carga_actual del potrero si la fecha es la más reciente */
                $fechaMasReciente = CapacidadHistorica::where('id_potrero', $ch['id_potrero'])
                    ->where('estado', 'activo')
                    ->max('fecha');

                if ($fechaMasReciente === $ch['fecha']) {
                    Potrero::where('id_potrero', $ch['id_potrero'])
                        ->update([
                            'capacidad_carga_actual'         => $ch['capacidad_carga'],
                            'fecha_actualizacion' => now(),
                            'modificado_por'      => session('id_usuario'),
                        ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Capacidades históricas registradas correctamente',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id_capacidad_historica)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $request->validate([
            'capacidad_carga' => 'required|numeric',
            'fecha' => 'required|date',
        ]);

        $capacidad_historica = (new CapacidadHistorica())->get_capacidad_historica($id_capacidad_historica);
        $capacidad_historica->capacidad_carga = $request->capacidad_carga;
        $capacidad_historica->fecha = $request->fecha;
        $capacidad_historica->modificado_por = session('id_usuario');
        $capacidad_historica->save();

        return response()->json([
            'success' => true,
            'message' => 'Capacidad histórica actualizada correctamente',
            'capacidad_historica' => $capacidad_historica
        ]);
    }

    public function delete(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $request->validate([
            'id_capacidad_historica' => ['required', 'numeric', 'integer']
        ]);

        $capacidad_historica = (new CapacidadHistorica())->get_capacidad_historica($request->id_capacidad_historica);

        $capacidad_historica->estado = $capacidad_historica->estado == 'activo' ? 'inactivo' : 'activo';
        $capacidad_historica->fecha_eliminacion = $capacidad_historica->estado == 'inactivo' ? now() : null;
        $capacidad_historica->eliminado_por = $capacidad_historica->estado == 'inactivo' ? session('id_usuario') : null;
        $capacidad_historica->save();

        return response()->json([
            'success' => true,
            'message' => $capacidad_historica->estado == 'activo' ? 'La capacidad histórica fue restaurada con éxito.' : 'La capacidad histórica fue archivada con éxito.',
            'capacidad_historica' => $capacidad_historica
        ]);
    }
}
