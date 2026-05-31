<?php

namespace App\Http\Controllers;

use App\Models\PesajeHistorico;
use App\Models\Bovino;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PesajeHistoricoController extends Controller
{
    public function view_index()
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }

        return view('pesajes_historicos.index', [
            'head_title' => 'PESAJES HISTÓRICOS',
        ]);
    }

    public function view_crear()
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }

        return view('pesajes_historicos.crear', [
            'head_title' => 'REGISTRAR PESAJES HISTÓRICOS',
        ]);
    }

    public function listar(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso',], 403);
        }

        $id_bovino = $request->query('id_bovino');

        $pesajes_historicos = (new PesajeHistorico())->get_all_pesajes_historicos($id_bovino);

        return response()->json([
            'data' => $pesajes_historicos
        ]);
    }

    public function mostrar(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $pesaje_historico = (new PesajeHistorico())->get_pesaje_historico($request->pesaje_historico);

        return response()->json([
            'data' => $pesaje_historico
        ]);
    }

    public function create(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $request->validate([
            'pesajes_historicos'             => 'required|array|min:1',
            'pesajes_historicos.*.carimbo'   => 'required|years_between:1900,' . date('Y'),
            'pesajes_historicos.*.identificador' => 'required|string|max:40',
            'pesajes_historicos.*.peso'      => 'required|numeric',
            'pesajes_historicos.*.fecha'     => 'required|date',
        ]);

        /* Validar que cada carimbo e identificador correspondan a un bovino existente */
        $errores = [];

        foreach ($request->pesajes_historicos as $index => $ph) {
            $bovino = Bovino::where('identificador', $ph['identificador'])
                ->whereYear('fecha_nacimiento', $ph['carimbo'])
                ->where('estado', 'activo')
                ->first();

            if (!$bovino) {
                $errores[] = "Fila " . ($index + 1) . ": No existe un bovino activo con identificador \"{$ph['identificador']}\" y carimbo {$ph['carimbo']}.";
            } else {
                /* Inyectar el id_bovino resuelto para usarlo en la transacción */
                $request->merge([
                    'pesajes_historicos' => array_replace(
                        $request->pesajes_historicos,
                        [$index => array_merge($ph, ['id_bovino' => $bovino->id_bovino])]
                    )
                ]);
            }
        }

        /* Validar que no exista ya un pesaje para ese bovino en esa fecha */
        foreach ($request->pesajes_historicos as $index => $ph) {
            $existe = PesajeHistorico::where('id_bovino', $ph['id_bovino'])
                ->where('fecha', $ph['fecha'])
                ->where('estado', 'activo')
                ->exists();

            if ($existe) {
                $fechaFormateada = (new \DateTime($ph['fecha']))->format('d/m/Y');

                $errores[] = "Fila " . ($index + 1) . ": Ya existe un pesaje para el bovino C{$ph['carimbo']} \"{$ph['identificador']}\" en la fecha {$fechaFormateada}.";
            }
        }

        if (!empty($errores)) {
            return response()->json([
                'success' => false,
                'message' => 'Algunos bovinos no fueron encontrados o ya tienen un pesaje registrado en la fecha indicada.',
                'errores' => $errores,
            ], 422);
        }

        DB::beginTransaction();

        try {
            foreach ($request->pesajes_historicos as $ph) {
                $pesaje_historico = new PesajeHistorico();
                $pesaje_historico->id_bovino = $ph['id_bovino'];
                $pesaje_historico->peso      = $ph['peso'];
                $pesaje_historico->fecha     = $ph['fecha'];
                $pesaje_historico->creado_por = session('id_usuario');
                $pesaje_historico->save();

                /* Actualizar peso_actual del bovino si la fecha es la más reciente */
                $fechaMasReciente = PesajeHistorico::where('id_bovino', $ph['id_bovino'])
                    ->where('estado', 'activo')
                    ->max('fecha');

                if ($fechaMasReciente === $ph['fecha']) {
                    Bovino::where('id_bovino', $ph['id_bovino'])
                        ->update([
                            'peso_actual'         => $ph['peso'],
                            'fecha_actualizacion' => now(),
                            'modificado_por'      => session('id_usuario'),
                        ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pesajes históricos registrados correctamente',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id_pesaje_historico)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $request->validate([
            'peso' => 'required|numeric',
            'fecha' => 'required|date',
        ]);

        $pesaje_historico = (new PesajeHistorico())->get_pesaje_historico($id_pesaje_historico);
        $pesaje_historico->peso = $request->peso;
        $pesaje_historico->fecha = $request->fecha;
        $pesaje_historico->modificado_por = session('id_usuario');
        $pesaje_historico->save();

        return response()->json([
            'success' => true,
            'message' => 'Pesaje histórico actualizado correctamente',
            'pesaje_historico' => $pesaje_historico
        ]);
    }

    public function delete(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $request->validate([
            'id_pesaje_historico' => ['required', 'numeric', 'integer']
        ]);

        $pesaje_historico = (new PesajeHistorico())->get_pesaje_historico($request->id_pesaje_historico);

        $pesaje_historico->estado = $pesaje_historico->estado == 'activo' ? 'inactivo' : 'activo';
        $pesaje_historico->fecha_eliminacion = $pesaje_historico->estado == 'inactivo' ? now() : null;
        $pesaje_historico->eliminado_por = $pesaje_historico->estado == 'inactivo' ? session('id_usuario') : null;
        $pesaje_historico->save();

        return response()->json([
            'success' => true,
            'message' => $pesaje_historico->estado == 'activo' ? 'El pesaje histórico fue restaurado con éxito.' : 'El pesaje histórico fue archivado con éxito.',
            'pesaje_historico' => $pesaje_historico
        ]);
    }
}
