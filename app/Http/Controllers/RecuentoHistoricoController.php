<?php

namespace App\Http\Controllers;

use App\Models\RecuentoHistorico;
use App\Models\Bovino;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecuentoHistoricoController extends Controller
{
    public function view_index()
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }

        return view('recuentos_historicos.index', [
            'head_title' => 'RECUENTOS HISTÓRICOS',
        ]);
    }

    public function view_crear()
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }

        return view('recuentos_historicos.crear', [
            'head_title' => 'REGISTRAR RECUENTOS HISTÓRICOS',
        ]);
    }

    public function listar(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso',], 403);
        }

        $id_bovino = $request->query('id_bovino');

        $recuentos_historicos = (new RecuentoHistorico())->get_all_recuentos_historicos($id_bovino);

        return response()->json([
            'data' => $recuentos_historicos
        ]);
    }

    public function mostrar(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $recuento_historico = (new RecuentoHistorico())->get_recuento_historico($request->recuento_historico);

        return response()->json([
            'data' => $recuento_historico
        ]);
    }

    public function create(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $request->validate([
            'recuentos_historicos'             => 'required|array|min:1',
            'recuentos_historicos.*.carimbo'   => 'required|years_between:1900,' . date('Y'),
            'recuentos_historicos.*.identificador' => 'required|string|max:40',
            'recuentos_historicos.*.estado_recuento'      => 'required|numeric',
            'recuentos_historicos.*.fecha'     => 'required|date',
        ]);

        /* Validar que cada carimbo e identificador correspondan a un bovino existente */
        $errores = [];

        foreach ($request->recuentos_historicos as $index => $rh) {
            $bovino = Bovino::where('identificador', $rh['identificador'])
                ->whereYear('fecha_nacimiento', $rh['carimbo'])
                ->where('estado', 'activo')
                ->first();

            if (!$bovino) {
                $errores[] = "Fila " . ($index + 1) . ": No existe un bovino activo con identificador \"{$rh['identificador']}\" y carimbo {$rh['carimbo']}.";
            } else {
                /* Inyectar el id_bovino resuelto para usarlo en la transacción */
                $request->merge([
                    'recuentos_historicos' => array_replace(
                        $request->recuentos_historicos,
                        [$index => array_merge($rh, ['id_bovino' => $bovino->id_bovino])]
                    )
                ]);
            }

            /* Validar que el valor de estado_recuento sea válido */
            if (!in_array($rh['estado_recuento'], [0, 1])) {
                $errores[] = "Fila " . ($index + 1) . ": El valor de 'Estado recuento' debe ser 0 o 1.";
            }
        }

        /* Validar que no exista ya un recuento para ese bovino en esa fecha */
        foreach ($request->recuentos_historicos as $index => $rh) {
            $existe = RecuentoHistorico::where('id_bovino', $rh['id_bovino'])
                ->where('fecha', $rh['fecha'])
                ->where('estado', 'activo')
                ->exists();

            if ($existe) {
                $fechaFormateada = (new \DateTime($rh['fecha']))->format('d/m/Y');

                $errores[] = "Fila " . ($index + 1) . ": Ya existe un recuento para el bovino C{$rh['carimbo']} \"{$rh['identificador']}\" en la fecha {$fechaFormateada}.";
            }
        }

        if (!empty($errores)) {
            return response()->json([
                'success' => false,
                'message' => 'Algunos bovinos no fueron encontrados o ya tienen un recuento registrado en la fecha indicada.',
                'errores' => $errores,
            ], 422);
        }

        DB::beginTransaction();

        try {
            foreach ($request->recuentos_historicos as $rh) {
                $recuento_historico = new RecuentoHistorico();
                $recuento_historico->id_bovino = $rh['id_bovino'];
                $recuento_historico->estado_recuento = $rh['estado_recuento'];
                $recuento_historico->fecha     = $rh['fecha'];
                $recuento_historico->creado_por = session('id_usuario');
                $recuento_historico->save();
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

    public function update(Request $request, $id_recuento_historico)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $request->validate([
            'estado_recuento' => 'required|numeric',
            'fecha' => 'required|date',
        ]);

        $recuento_historico = (new RecuentoHistorico())->get_recuento_historico($id_recuento_historico);
        $recuento_historico->estado_recuento = $request->estado_recuento;
        $recuento_historico->fecha = $request->fecha;
        $recuento_historico->modificado_por = session('id_usuario');
        $recuento_historico->save();

        return response()->json([
            'success' => true,
            'message' => 'Recuento histórico actualizado correctamente',
            'recuento_historico' => $recuento_historico
        ]);
    }

    public function delete(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $request->validate([
            'id_recuento_historico' => ['required', 'numeric', 'integer']
        ]);

        $recuento_historico = (new RecuentoHistorico())->get_pesaje_historico($request->id_recuento_historico);

        $recuento_historico->estado = $recuento_historico->estado == 'activo' ? 'inactivo' : 'activo';
        $recuento_historico->fecha_eliminacion = $recuento_historico->estado == 'inactivo' ? now() : null;
        $recuento_historico->eliminado_por = $recuento_historico->estado == 'inactivo' ? session('id_usuario') : null;
        $recuento_historico->save();

        return response()->json([
            'success' => true,
            'message' => $recuento_historico->estado == 'activo' ? 'El pesaje histórico fue restaurado con éxito.' : 'El pesaje histórico fue archivado con éxito.',
            'recuento_historico' => $recuento_historico
        ]);
    }
}
