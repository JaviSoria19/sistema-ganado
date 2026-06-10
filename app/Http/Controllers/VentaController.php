<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Venta;
use App\Models\Pago;
use App\Models\Bovino;
use App\Models\Cliente;
use App\Models\Usuario;
use Barryvdh\DomPDF\Facade\Pdf;

class VentaController extends Controller
{
    public function view_index()
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }

        $usuarios = (new Usuario())->get_all_usuarios();
        $clientes = (new Cliente())->get_all_clientes();

        return view('ventas.index', [
            'head_title' => 'GESTIÓN DE VENTAS',
            'usuarios' => $usuarios,
            'clientes' => $clientes,
        ]);
    }

    public function view_create()
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }

        return view('ventas.create', [
            'head_title' => 'CREAR VENTA',
        ]);
    }

    public function view_update($venta)
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }

        $venta = (new Venta())->get_venta($venta);

        return view('ventas.update', [
            'head_title' => 'EDITAR VENTA N°' . $venta->id_venta,
            'venta' => $venta,
        ]);
    }

    public function view_imprimir($venta)
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }

        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $venta = (new Venta())->get_venta($venta);
        $fecha = date('Y-m-d H_i_s');

        $pdf = Pdf::loadView('ventas.imprimir', compact('venta'));
        $pdf->setPaper('letter');
        return $pdf->stream('VENTA N° ' . $venta->id_venta . ' - ' . $fecha . '.pdf');
    }

    public function listar(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $filters = [
            'fecha_inicio' => $request->fecha_inicio ?? null,
            'fecha_fin' => $request->fecha_fin ?? null,
            'estado' => $request->estado ?? null,
            'creado_por' => $request->creado_por ?? null,
            'id_cliente' => $request->id_cliente ?? null,
            'tipo_precio' => $request->tipo_precio ?? null,
        ];

        $ventas = (new Venta())->get_all_ventas($filters);

        return response()->json([
            'data' => $ventas
        ]);
    }

    public function mostrar(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $venta = (new Venta())->get_venta($request->venta);

        return response()->json([
            'data' => $venta
        ]);
    }

    public function create(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $request->validate([
            'id_cliente'   => 'required|integer|exists:clientes,id_cliente',
            'fecha_venta'   => 'required|date',
            'concepto' => 'nullable|string|max:150',
            'bovinos'   => 'required|array|min:1',
            'bovinos.*.id_bovino' => 'required|integer|exists:bovinos,id_bovino',
            'pagos'       => 'nullable|array'
        ]);

        // Validar bovinos antes de iniciar la transacción
        foreach ($request->bovinos as $detalle) {
            $bovino = Bovino::find($detalle['id_bovino']);
            if (!$bovino) {
                return response()->json([
                    'success' => false,
                    'message' => 'El bovino con ID ' . $detalle['id_bovino'] . ' no existe.'
                ], 400);
            }

            if ($bovino->estado != 'activo') {
                $estadoTexto = $bovino->estado == 'vendido' ? '<b class="text-primary">vendido</b>' : '<b class="text-secondary">eliminado</b>';
                return response()->json([
                    'success' => false,
                    'message' => 'El bovino con el código <b class="text-primary">' . $bovino->identificador . '</b> no está disponible para la venta (actualmente ' . $estadoTexto . '), remuévalo de la lista e intente nuevamente.',
                ], 400);
            }
        }

        // Validar pagos antes de iniciar la transacción
        if (!empty($request->pagos)) {
            foreach ($request->pagos as $pago) {
                if (!isset($pago['monto']) || !is_numeric($pago['monto']) || $pago['monto'] <= 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cada pago debe tener un monto válido mayor a 0.'
                    ], 400);
                }

                if (!isset($pago['fecha_pago']) || !strtotime($pago['fecha_pago'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cada pago debe tener una fecha de pago válida.'
                    ], 400);
                }
            }
        }

        // Validar que la suma total de los pagos no exceda el total de la venta
        $totalPagos = array_reduce($request->pagos ?? [], function ($sum, $pago) {
            return $sum + (is_numeric($pago['monto']) ? $pago['monto'] : 0);
        }, 0);

        $totalPagos = round($totalPagos, 2);
        $totalVenta = round($request->total, 2);

        if ($totalPagos > $totalVenta) {
            return response()->json([
                'success' => false,
                'message' => "La suma total de los pagos ({$totalPagos}) excede el total de la venta ({$totalVenta})."
            ], 400);
        }

        DB::beginTransaction();

        try {
            $venta = new Venta();
            $venta->id_cliente = $request->id_cliente;
            $venta->concepto = $request->concepto ?? 'Sin concepto';
            $venta->tipo_precio = $request->tipo_precio;
            $venta->precio_kg = $request->tipo_precio == 'precio_kg' ? $request->precio_kg : 0;
            $venta->destare = $request->destare ?? 0;
            $venta->rendimiento = $request->rendimiento ?? 0;
            $venta->total = round($request->total, 2);
            $venta->fecha_venta = $request->fecha_venta;

            $venta->creado_por = session('id_usuario');
            $venta->save();

            foreach ($request->bovinos as $detalle) {
                $venta->bovinos()->attach($detalle['id_bovino'], [
                    'kg_peso_vivo' => round($detalle['kg_peso_vivo'], 2),
                    'kg_peso_gancho' => round($detalle['kg_peso_gancho'], 2),
                    'subtotal' => round($detalle['subtotal'], 2),
                    'observacion' => $detalle['observacion'] ?? null,
                ]);

                $bovino = Bovino::find($detalle['id_bovino']);
                $bovino->estado = 'vendido';
                $bovino->fecha_salida = now();
                $bovino->save();
            }

            if (!empty($request->pagos)) {
                foreach ($request->pagos as $pago) {
                    $p = new Pago();
                    $p->id_venta = $venta->id_venta;
                    $p->monto = round($pago['monto'], 2);
                    $p->tipo_pago = $pago['tipo_pago'];
                    $p->fecha_pago = $pago['fecha_pago'];
                    $p->save();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta registrada correctamente',
                'venta'   => $venta->load(['bovinos', 'cliente'])
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $id_venta)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $request->validate([
            'id_cliente'   => 'required|integer|exists:clientes,id_cliente',
            'fecha_venta'   => 'required|date',
            'concepto' => 'string|max:150|nullable',
            'bovinos'   => 'required|array|min:1',
            'bovinos.*.id_bovino' => 'required|integer|exists:bovinos,id_bovino',
            'pagos'       => 'nullable|array'
        ]);

        // Validar que la suma total de los pagos no exceda el total de la venta
        $totalPagos = array_reduce($request->pagos ?? [], function ($sum, $pago) {
            return $sum + (is_numeric($pago['monto']) ? $pago['monto'] : 0);
        }, 0);

        $totalVenta = round($request->total, 2);
        $totalPagos = round($totalPagos, 2);

        if ($totalPagos > $totalVenta) {
            return response()->json([
                'success' => false,
                'message' => "La suma total de los pagos ({$totalPagos}) excede el total de la venta ({$totalVenta})."
            ], 400);
        }

        DB::beginTransaction();
        try {
            $venta = (new Venta())->get_venta($id_venta);
            $venta->id_cliente = $request->id_cliente;
            $venta->concepto = $request->concepto ?? 'Sin concepto';
            $venta->tipo_precio = $request->tipo_precio;
            $venta->destare = $request->destare ?? 0;
            $venta->rendimiento = $request->rendimiento ?? 0;
            $venta->precio_kg = $request->tipo_precio == 'precio_kg' ? $request->precio_kg : 0;
            $venta->total = round($request->total, 2);
            $venta->fecha_venta = $request->fecha_venta;

            $venta->modificado_por = session('id_usuario');
            $venta->save();

            // Obtener bovinos antes de la actualización
            $bovinosAnteriores = $venta->bovinos->pluck('id_bovino')->toArray();
            $bovinosNuevos = collect($request->bovinos)->pluck('id_bovino')->toArray();

            // Identificar bovinos eliminados
            $bovinosEliminados = array_diff($bovinosAnteriores, $bovinosNuevos);

            // Revertir estado de los bovinos eliminados
            foreach ($bovinosEliminados as $idBovino) {
                $bovino = Bovino::find($idBovino);
                if ($bovino) {
                    $bovino->estado = 'activo';
                    $bovino->fecha_salida = null;
                    $bovino->save();
                }
            }

            // Elimina todos los 'ventas_detalles'
            $venta->bovinos()->detach();

            foreach ($request->bovinos as $detalle) {
                $venta->bovinos()->attach($detalle['id_bovino'], [
                    'kg_peso_vivo' => round($detalle['kg_peso_vivo'], 2),
                    'kg_peso_gancho' => round($detalle['kg_peso_gancho'], 2),
                    'subtotal' => round($detalle['subtotal'], 2),
                    'observacion' => $detalle['observacion'] ?? null,
                ]);

                $bovino = Bovino::find($detalle['id_bovino']);
                $bovino->estado = 'vendido';
                $bovino->fecha_salida = now();
                $bovino->save();
            }

            // Insertar nuevos pagos si existen
            if (!empty($request->pagos)) {
                foreach ($request->pagos as $pago) {
                    if ($pago['id_pago'] == '0') {
                        $p = new Pago();
                        $p->id_venta = $venta->id_venta;
                        $p->monto = round($pago['monto'], 2);
                        $p->tipo_pago = $pago['tipo_pago'];
                        $p->fecha_pago = $pago['fecha_pago'];
                        $p->save();
                    } else {
                        $p = Pago::find($pago['id_pago']);
                        $p->monto = round($pago['monto'], 2);
                        $p->tipo_pago = $pago['tipo_pago'];
                        $p->fecha_pago = $pago['fecha_pago'];
                        $p->fecha_registro = now();
                        $p->modificado_por = session('id_usuario');
                        $p->save();
                    }
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Venta actualizada correctamente',
                'venta'   => $venta->load(['bovinos', 'cliente', 'pagos'])
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function delete(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        if ($request->motivo_eliminacion == null || $request->motivo_eliminacion == '') {
            return response()->json([
                'success' => false,
                'message' => 'Debe proporcionar un motivo de eliminación para continuar'
            ], 400);
        }

        $venta = (new Venta())->get_venta($request->id_venta);

        if ($venta->estado == 'eliminado') {
            return response()->json([
                'success' => false,
                'message' => 'La venta ya se encuentra eliminada'
            ], 400);
        }

        $venta->motivo_eliminacion = $request->motivo_eliminacion;
        $venta->estado = 'eliminado';
        $venta->fecha_eliminacion = now();
        $venta->eliminado_por = session('id_usuario');
        $venta->save();

        foreach ($venta->bovinos as $bovino) {
            $p = Bovino::find($bovino->id_bovino);
            $p->estado = 'activo';
            $p->fecha_salida = null;
            $p->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Venta eliminada correctamente, todos los bovinos involucrados retornaron al sistema como activos',
            'venta'   => $venta
        ]);
    }
}
