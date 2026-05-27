<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClienteValidation;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function view_index()
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }

        return view('clientes.index', [
            'head_title' => 'GESTIÓN DE CLIENTES'
        ]);
    }

    public function listar()
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $clientes = (new Cliente())->get_all_clientes();
        return response()->json([
            'data' => $clientes
        ]);
    }

    public function mostrar(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $cliente = (new Cliente())->get_cliente($request->cliente);
        return response()->json([
            'data' => $cliente
        ]);
    }

    public function create(ClienteValidation $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $cliente = new Cliente();
        $cliente->nombre = strtoupper($request->nombre);
        $cliente->celular = $request->celular;
        $cliente->estancia = strtoupper($request->estancia);
        $cliente->creado_por = session('id_usuario');
        $cliente->save();
        return response()->json([
            'success' => true,
            'message' => 'Cliente creado/a correctamente',
            'cliente' => $cliente
        ]);
    }

    public function update(ClienteValidation $request, $id_cliente)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $cliente = (new Cliente())->get_cliente($id_cliente);
        $cliente->nombre = strtoupper($request->nombre);
        $cliente->celular = $request->celular;
        $cliente->estancia = strtoupper($request->estancia);
        $cliente->modificado_por = session('id_usuario');
        $cliente->save();

        return response()->json([
            'success' => true,
            'message' => 'Cliente actualizado/a correctamente',
            'cliente' => $cliente
        ]);
    }

    public function delete(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $request->validate([
            'id_cliente' => ['required', 'numeric', 'integer']
        ]);

        $cliente = (new Cliente())->get_cliente($request->id_cliente);
        $cliente->estado = $cliente->estado == 'activo' ? 'inactivo' : 'activo';
        $cliente->fecha_eliminacion = $cliente->estado == 'inactivo' ? now() : null;
        $cliente->eliminado_por = $cliente->estado == 'inactivo' ? session('id_usuario') : null;
        $cliente->save();
        return response()->json([
            'success' => true,
            'message' => $cliente->estado == 'activo' ? 'El/la cliente fue habilitado con éxito' : 'El/la cliente fue deshabilitado con éxito' ,
            'cliente' => $cliente
        ]);
    }
}
