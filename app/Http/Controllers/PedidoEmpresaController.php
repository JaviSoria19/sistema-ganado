<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Empresa;
use App\Models\PedidoEmpresa;
use App\Models\DetallePedidoEmpresa;
use App\Models\Producto;
use Barryvdh\DomPDF\Facade\Pdf;

class PedidoEmpresaController extends Controller
{
    public function view_index()
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }

        $empresas = (new Empresa())->getAllEmpresas();
        $productos = (new Producto())->getAllProductosGroupByNombreProducto();

        return view('pedidos_empresas.index', [
            'head_title' => 'GESTIÓN DE PEDIDOS A EMPRESAS',
            'empresas' => $empresas,
            'productos' => $productos,
        ]);
    }

    public function view_imprimir($pedido_empresa)
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }
        
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $pedido_empresa = (new PedidoEmpresa())->getPedidoEmpresa($pedido_empresa);
        $fecha = date('Y-m-d H_i_s');

        $pdf = Pdf::loadView('pedidos_empresas.imprimir', compact('pedido_empresa'));
        return $pdf->stream('SOLICITUD DE PEDIDO A ' . $pedido_empresa->empresa->nombreEmpresa . ' - ' . $fecha . '.pdf');
    }

    public function listarPedidosEmpresas()
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $pedidos_empresas = (new PedidoEmpresa())->getAllPedidosEmpresas();
        return response()->json([
            'data' => $pedidos_empresas
        ]);
    }

    public function mostrarPedidoEmpresa(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $pedido_empresa = (new PedidoEmpresa())->getPedidoEmpresa($request->pedido_empresa);
        return response()->json([
            'data' => $pedido_empresa
        ]);
    }

    public function create(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $request->validate([
            'idEmpresa' => 'required|numeric|integer',
            'detalles' => 'required|array|min:1',
            'detalles.*.nombreProducto' => 'required|string',
            'detalles.*.precioUSD' => 'required|numeric|min:0',
            'detalles.*.cantidad' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $pedido_empresa = new PedidoEmpresa();
            $pedido_empresa->idEmpresa = $request->idEmpresa;
            $pedido_empresa->modificado_por = session('id_usuario');
            $pedido_empresa->save();

            foreach ($request->detalles as $detalle) {
                $d = new DetallePedidoEmpresa();
                $d->idPedidoEmpresa = $pedido_empresa->idPedidoEmpresa;
                $d->nombreProducto = $detalle['nombreProducto'];
                $d->precioUSD = $detalle['precioUSD'];
                $d->cantidad = $detalle['cantidad'];
                $d->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pedido registrado correctamente',
                'pedido_empresa' => $pedido_empresa->load('detalles')
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $idPedidoEmpresa)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $request->validate([
            'idEmpresa' => 'required|numeric|integer',
            'detalles' => 'required|array|min:1',
            'detalles.*.nombreProducto' => 'required|string',
            'detalles.*.precioUSD' => 'required|numeric|min:0',
            'detalles.*.cantidad' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $pedido_empresa = PedidoEmpresa::findOrFail($idPedidoEmpresa);
            $pedido_empresa->idEmpresa = $request->idEmpresa;
            $pedido_empresa->modificado_por = session('id_usuario');
            $pedido_empresa->save();

            // Elimino los detalles anteriores y re-inserto (más simple)
            DetallePedidoEmpresa::where('idPedidoEmpresa', $pedido_empresa->idPedidoEmpresa)->delete();

            foreach ($request->detalles as $detalle) {
                $d = new DetallePedidoEmpresa();
                $d->idPedidoEmpresa = $pedido_empresa->idPedidoEmpresa;
                $d->nombreProducto = $detalle['nombreProducto'];
                $d->precioUSD = $detalle['precioUSD'];
                $d->cantidad = $detalle['cantidad'];
                $d->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pedido actualizado correctamente',
                'pedido_empresa' => $pedido_empresa->load('detalles')
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteOrRestore(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $request->validate([
            'idPedidoEmpresa' => ['required', 'numeric', 'integer']
        ]);

        $pedido_empresa = PedidoEmpresa::findOrFail($request->idPedidoEmpresa);
        $pedido_empresa->estado = $pedido_empresa->estado == '1' ? '0' : '1';
        $pedido_empresa->modificado_por = session('id_usuario');
        $pedido_empresa->save();

        return response()->json([
            'success' => true,
            'message' => $pedido_empresa->estado == '1' ? 'El pedido de empresa fue restaurado con éxito' : 'El pedido de empresa fue archivado con éxito',
            'pedido_empresa' => $pedido_empresa->load('detalles')
        ]);
    }
}
