<?php

namespace App\Http\Controllers;

use App\Models\PalpacionHistorica;
use App\Models\Bovino;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PalpacionHistoricaController extends Controller
{
    public function view_index()
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }

        return view('palpaciones_historicas.index', [
            'head_title' => 'PALPACIONES HISTÓRICAS',
        ]);
    }

    public function view_crear()
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }

        return view('palpaciones_historicas.crear', [
            'head_title' => 'REGISTRAR PALPACIONES HISTÓRICAS',
        ]);
    }

    public function listar(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso',], 403);
        }

        $id_bovino = $request->query('id_bovino');

        $palpaciones_historicas = (new PalpacionHistorica())->get_all_palpaciones_historicas($id_bovino);

        return response()->json([
            'data' => $palpaciones_historicas
        ]);
    }

    public function mostrar(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $palpacion_historica = (new PalpacionHistorica())->get_palpacion_historica($request->palpacion_historica);

        return response()->json([
            'data' => $palpacion_historica
        ]);
    }

    public function create(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $request->validate([
            'palpaciones_historicas'             => 'required|array|min:1',
            'palpaciones_historicas.*.carimbo'   => 'required|years_between:1900,' . date('Y'),
            'palpaciones_historicas.*.identificador' => 'required|string|max:40',
            'palpaciones_historicas.*.resultado'      => 'required|string', // p de preñada y v de vacía
            'palpaciones_historicas.*.fecha'     => 'required|date',
        ]);

        /* Validar que cada carimbo e identificador correspondan a un bovino existente y que el bovino sea hembra */
        $errores = [];

        foreach ($request->palpaciones_historicas as $index => $ph) {
            $bovino = Bovino::where('identificador', $ph['identificador'])
                ->whereYear('fecha_nacimiento', $ph['carimbo'])
                ->where('estado', 'activo')
                ->where('genero', 'hembra')
                ->first();

            if (!$bovino) {
                $errores[] = "Fila " . ($index + 1) . ": No existe una hembra activa con identificador \"{$ph['identificador']}\" y carimbo {$ph['carimbo']}.";
            } else {
                /* Inyectar el id_bovino resuelto para usarlo en la transacción */
                $request->merge([
                    'palpaciones_historicas' => array_replace(
                        $request->palpaciones_historicas,
                        [$index => array_merge($ph, ['id_bovino' => $bovino->id_bovino])]
                    )
                ]);
            }

            /* Validar que el valor de resultado sea válido */
            if (!in_array($ph['resultado'], ['p', 'v'])) {
                $errores[] = "Fila " . ($index + 1) . ": El valor de 'Resultado' debe ser 'p' de preñada o 'v' de vacía.";
            }
        }

        /* Validar que no exista ya una palpación histórica para ese bovino en esa fecha */
        foreach ($request->palpaciones_historicas as $index => $ph) {

            // Si esta fila ya tiene error (no se resolvió el bovino), saltarla
            if (!isset($ph['id_bovino'])) {
                continue;
            }

            $existe = PalpacionHistorica::where('id_bovino', $ph['id_bovino'])
                ->where('fecha', $ph['fecha'])
                ->where('estado', 'activo')
                ->exists();

            if ($existe) {
                $fechaFormateada = (new \DateTime($ph['fecha']))->format('d/m/Y');

                $errores[] = "Fila " . ($index + 1) . ": Ya existe un recuento para el bovino C{$ph['carimbo']} \"{$ph['identificador']}\" en la fecha {$fechaFormateada}.";
            }
        }

        if (!empty($errores)) {
            return response()->json([
                'success' => false,
                'message' => 'Algunos bovinos no fueron encontrados o ya tienen una palpación histórica en la fecha indicada.',
                'errores' => $errores,
            ], 422);
        }

        DB::beginTransaction();

        try {
            foreach ($request->palpaciones_historicas as $ph) {
                $palpacion_historica = new PalpacionHistorica();
                $palpacion_historica->id_bovino = $ph['id_bovino'];
                $palpacion_historica->resultado = $ph['resultado'];
                $palpacion_historica->fecha     = $ph['fecha'];
                $palpacion_historica->creado_por = session('id_usuario');
                $palpacion_historica->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Recuentos históricos registrados correctamente',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id_palpacion_historica)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $request->validate([
            'resultado' => 'required|in:p,v',
            'fecha' => 'required|date',
        ]);

        $palpacion_historica = (new PalpacionHistorica())->get_palpacion_historica($id_palpacion_historica);
        $palpacion_historica->resultado = $request->resultado;
        $palpacion_historica->fecha = $request->fecha;
        $palpacion_historica->modificado_por = session('id_usuario');
        $palpacion_historica->save();

        return response()->json([
            'success' => true,
            'message' => 'Recuento histórico actualizado correctamente',
            'palpacion_historica' => $palpacion_historica
        ]);
    }

    public function delete(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $request->validate([
            'id_palpacion_historica' => ['required', 'numeric', 'integer']
        ]);

        $palpacion_historica = (new PalpacionHistorica())->get_palpacion_historica($request->id_palpacion_historica);

        $palpacion_historica->estado = $palpacion_historica->estado == 'activo' ? 'inactivo' : 'activo';
        $palpacion_historica->fecha_eliminacion = $palpacion_historica->estado == 'inactivo' ? now() : null;
        $palpacion_historica->eliminado_por = $palpacion_historica->estado == 'inactivo' ? session('id_usuario') : null;
        $palpacion_historica->save();

        return response()->json([
            'success' => true,
            'message' => $palpacion_historica->estado == 'activo' ? 'La palpación histórica fue restaurada con éxito.' : 'La palpación histórica fue archivada con éxito.',
            'palpacion_historica' => $palpacion_historica
        ]);
    }
}
