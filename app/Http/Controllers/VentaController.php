<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Venta;
use App\Models\Empleado;
use App\Models\PagoVenta;
use App\Models\Producto;
use App\Models\Parametro;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class VentaController extends Controller
{
    public function view_index(Request $request)
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }

        $fechaInicio = $request->fechaInicio ? $request->fechaInicio : date('Y-m-d', strtotime('-1 months'));
        $fechaFin = $request->fechaFin ? $request->fechaFin : date('Y-m-d');

        if ($fechaInicio > $fechaFin) {
            return redirect()->route('ventas.index')->withErrors(['error' => 'La fecha de inicio ingresada (' . date('d/m/Y', strtotime($fechaInicio)) . ') no puede ser mayor a la fecha de fin (' . date('d/m/Y', strtotime($fechaFin)) . ').']);
        }

        return view('ventas.index', [
            'headTitle' => 'GESTIÓN DE VENTAS',
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
        ]);
    }

    public function view_create()
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }

        $empleados = (new Empleado())->getAllEmpleados();
        $parametro = (new Parametro())->get_parametro();

        return view('ventas.create', [
            'headTitle' => 'CREAR VENTA',
            'empleados' => $empleados,
            'parametro' => $parametro,
        ]);
    }

    public function view_update($venta)
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }

        $venta = (new Venta())->getVenta($venta);
        $empleados = (new Empleado())->getAllEmpleados();
        $parametro = (new Parametro())->get_parametro();

        return view('ventas.update', [
            'headTitle' => 'EDITAR VENTA N°' . $venta->idVenta,
            'empleados' => $empleados,
            'parametro' => $parametro,
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

        $venta = (new Venta())->getVenta($venta);
        $fecha = date('Y-m-d H_i_s');

        $pdf = Pdf::loadView('ventas.imprimir', compact('venta'));
        $pdf->setPaper('letter');
        return $pdf->stream('VENTA N° ' . $venta->idVenta . ' - ' . $fecha . '.pdf');
    }

    public function view_reporte_utilidades(Request $request)
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }

        if (session('id_usuario') != 1){
            return view('403')->with('headTitle', 'ACCESO NO AUTORIZADO');
        }

        $fechaInicio = $request->fechaInicio ? $request->fechaInicio : date('Y-m-d', strtotime('-1 months'));
        $fechaFin = $request->fechaFin ? $request->fechaFin : date('Y-m-d');

        if ($fechaInicio > $fechaFin) {
            return redirect()->route('ventas.utilidades')->withErrors(['error' => 'La fecha de inicio ingresada (' . date('d/m/Y', strtotime($fechaInicio)) . ') no puede ser mayor a la fecha de fin (' . date('d/m/Y', strtotime($fechaFin)) . ').']);
        }

        $ventasSinSaldo = (new Venta())->getVentasPorEstadoYSaldo('1', '<=', '0', 'DESC', $fechaInicio, $fechaFin);
        $ventasConSaldo = (new Venta())->getVentasPorEstadoYSaldo('1', '>', '0', 'DESC', $fechaInicio, $fechaFin);

        $utilidadVentasSinSaldo = 0;
        foreach ($ventasSinSaldo as $venta) {
            foreach ($venta->productos as $producto) {
                $costoFinalUSD = $producto->costoBaseUSD +
                    ($producto->costoBaseUSD * $producto->traspasoPorcentaje) / 100 +
                    $producto->transporteUSD;

                $utilidadVentasSinSaldo += $producto->pivot->precioUSD - $costoFinalUSD;
            }
        }

        $utilidadVentasConSaldo = 0;
        foreach ($ventasConSaldo as $venta) {
            foreach ($venta->productos as $producto) {
                $costoFinalUSD = $producto->costoBaseUSD +
                    ($producto->costoBaseUSD * $producto->traspasoPorcentaje) / 100 +
                    $producto->transporteUSD;

                $utilidadVentasConSaldo += $producto->pivot->precioUSD - $costoFinalUSD;
            }
        }

        $utilidadTotal = $utilidadVentasSinSaldo + $utilidadVentasConSaldo;

        $cantidadVentas = count($ventasSinSaldo) + count($ventasConSaldo);

        $productosRegistrados = DB::table('productos')
            ->whereBetween('fecha_registro', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->count();

        $productosVendidos = DB::table('detalles_ventas as dv')
            ->join('ventas as v', 'dv.idVenta', '=', 'v.idVenta')
            ->where('v.estado', 1)
            ->whereBetween('v.fecha_registro', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->count('dv.idProducto');

        return view('ventas.reporte_utilidades', [
            'headTitle' => 'REPORTE UTILIDADES',
            'ventasSinSaldo' => $ventasSinSaldo,
            'ventasConSaldo' => $ventasConSaldo,
            'utilidadVentasSinSaldo' => $utilidadVentasSinSaldo,
            'utilidadVentasConSaldo' => $utilidadVentasConSaldo,
            'utilidadTotal' => $utilidadTotal,
            'cantidadVentas' => $cantidadVentas,
            'productosRegistrados' => $productosRegistrados,
            'productosVendidos' => $productosVendidos,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
        ]);
    }

    public function view_reporte_perdidas(Request $request)
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }

        if (session('id_usuario') != 1){
            return view('403')->with('headTitle', 'ACCESO NO AUTORIZADO');
        }

        $fechaInicio = $request->fechaInicio ? $request->fechaInicio : date('Y-m-d', strtotime('-1 months'));
        $fechaFin = $request->fechaFin ? $request->fechaFin : date('Y-m-d');

        if ($fechaInicio > $fechaFin) {
            return redirect()->route('ventas.utilidades')->withErrors(['error' => 'La fecha de inicio ingresada (' . date('d/m/Y', strtotime($fechaInicio)) . ') no puede ser mayor a la fecha de fin (' . date('d/m/Y', strtotime($fechaFin)) . ').']);
        }

        $ventas = (new Venta())->getVentasPorEstadoYSaldo('1', '<=', '0', 'DESC', $fechaInicio, $fechaFin);

        return view('ventas.reporte_perdidas', [
            'headTitle' => 'REPORTE PERDIDAS',
            'ventas' => $ventas,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
        ]);
    }

    public function listarVentas(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $ventas = (new Venta())->getAllVentas($request->fechaInicio, $request->fechaFin);

        return response()->json([
            'data' => $ventas
        ]);
    }

    public function mostrarVenta(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $venta = (new Venta())->getVenta($request->venta);

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
            'idCliente'   => 'required|integer|exists:clientes,idCliente',
            'idEmpleado'  => 'nullable|integer|exists:empleados,idEmpleado',
            'productos'   => 'required|array|min:1',
            'productos.*.idProducto' => 'required|integer|exists:productos,idProducto',
            'productos.*.precioUSD'  => 'required|numeric|min:0',
            'pagos'       => 'nullable|array'
        ]);

        // Validar productos antes de iniciar la transacción
        foreach ($request->productos as $detalle) {
            $producto = Producto::find($detalle['idProducto']);
            if (!$producto) {
                return response()->json([
                    'success' => false,
                    'message' => 'El producto con ID ' . $detalle['idProducto'] . ' no existe.'
                ], 400);
            }

            if ($producto->estado != 1) {
                // Estado 2 = vendido, Estado 0 = eliminado
                $estadoTexto = $producto->estado == 2 ? '<b class="text-primary">vendido</b>' : '<b class="text-secondary">eliminado</b>';
                return response()->json([
                    'success' => false,
                    'message' => 'El producto con el código <b class="text-primary">' . $producto->codigoProducto . '</b> no está disponible para la venta (actualmente ' . $estadoTexto . '), remuévalo de la lista e intente nuevamente.',
                ], 400);
            }
        }

        DB::beginTransaction();
        try {
            $venta = new Venta();
            $venta->id_usuario = session('id_usuario');
            $venta->idCliente = $request->idCliente;
            $venta->idEmpleado = $request->idEmpleado;
            $venta->modificado_por = session('id_usuario');
            $venta->totalUSD = $request->totalUSD;
            $venta->saldoUSD = $request->saldoUSD;
            $venta->save();

            foreach ($request->productos as $detalle) {
                $venta->productos()->attach($detalle['idProducto'], [
                    'precioUSD' => $detalle['precioUSD']
                ]);

                $producto = Producto::find($detalle['idProducto']);
                $producto->estado = 2;
                $producto->fechaVenta = Carbon::now();
                $producto->save();
            }

            if (!empty($request->pagos)) {
                foreach ($request->pagos as $pago) {
                    $p = new PagoVenta();
                    $p->idVenta = $venta->idVenta;
                    $p->pagoUSD = $pago['pagoUSD'];
                    $p->fechaPago = $pago['fechaPago'];
                    $p->modificado_por = session('id_usuario');
                    $p->save();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta registrada correctamente',
                'venta'   => $venta->load(['productos', 'cliente'])
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $idVenta)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $request->validate([
            'idCliente'   => 'required|integer|exists:clientes,idCliente',
            'idEmpleado'  => 'nullable|integer|exists:empleados,idEmpleado',
            'productos'   => 'required|array|min:1',
            'productos.*.idProducto' => 'required|integer|exists:productos,idProducto',
            'productos.*.precioUSD'  => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $venta = (new Venta())->getVenta($idVenta);
            $venta->idCliente = $request->idCliente;
            $venta->idEmpleado = $request->idEmpleado;
            $venta->modificado_por = session('id_usuario');
            $venta->totalUSD = $request->totalUSD;
            $venta->saldoUSD = $request->saldoUSD;
            $venta->save();

            // Obtener productos antes de la actualización
            $productosAnteriores = $venta->productos->pluck('idProducto')->toArray();
            $productosNuevos = collect($request->productos)->pluck('idProducto')->toArray();

            // Identificar productos eliminados
            $productosEliminados = array_diff($productosAnteriores, $productosNuevos);

            // Revertir estado de los productos eliminados
            foreach ($productosEliminados as $idProd) {
                $producto = Producto::find($idProd);
                if ($producto) {
                    $producto->estado = 1; // Disponible nuevamente
                    $producto->fechaVenta = null;
                    $producto->save();
                }
            }

            // Elimina todos los 'detalles_ventas'
            $venta->productos()->detach();

            foreach ($request->productos as $detalle) {
                $venta->productos()->attach($detalle['idProducto'], [
                    'precioUSD' => $detalle['precioUSD']
                ]);

                $producto = (new Producto())->getProducto($detalle['idProducto']);
                $producto->estado = 2;
                $producto->fechaVenta = Carbon::now();
                $producto->save();
            }

            // Borrar pagos anteriores
            /*PagoVenta::where('idVenta', $venta->idVenta)->delete();*/

            // Insertar nuevos pagos
            foreach ($request->pagos as $pago) {
                if ($pago['idPagoVenta'] == '0') {
                    $p = new PagoVenta();
                    $p->idVenta = $venta->idVenta;
                    $p->pagoUSD = $pago['pagoUSD'];
                    $p->fechaPago = $pago['fechaPago'];
                    $p->modificado_por = session('id_usuario');
                    $p->save();
                } else {
                    $p = (new PagoVenta())->getPagoVenta($pago['idPagoVenta']);
                    // Actualizar solo si el pago es menor o igual a 0.00 (editable)
                    if ($p->pagoUSD <= '0.00') {
                        $p->pagoUSD = $pago['pagoUSD'];
                        $p->fechaPago = $pago['fechaPago'];
                        $p->fecha_registro = Carbon::now();
                        $p->modificado_por = session('id_usuario');
                        $p->save();
                    }
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Venta actualizada correctamente',
                'venta'   => $venta->load(['productos', 'cliente', 'pagos'])
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

        $request->validate([
            'motivoEliminacion' => 'required|string|min:3|max:255',
        ]);

        $venta = (new Venta())->getVenta($request->idVenta);
        $venta->estado = 0;
        $venta->fechaEliminacion = now();
        $venta->motivoEliminacion = $request->motivoEliminacion;
        $venta->modificado_por = session('id_usuario');
        $venta->save();

        foreach ($venta->productos as $producto) {
            $p = (new Producto())->getProducto($producto->idProducto);
            $p->estado = 1;
            $p->fechaVenta = null;
            $p->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Venta eliminada correctamente, todos los productos involucrados retornaron al inventario',
            'venta'   => $venta
        ]);
    }
}
