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
            'headTitle' => 'GESTIÓN DE CLIENTES'
        ]);
    }

    public function listarClientes()
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $clientes = (new Cliente())->getAllClientes();
        return response()->json([
            'data' => $clientes
        ]);
    }

    public function mostrarCliente(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $cliente = (new Cliente())->getCliente($request->cliente);
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
        $cliente->nombreCliente = strtoupper($request->nombreCliente);
        $cliente->celular = $request->celular;
        $cliente->cedulaIdentidad = $request->cedulaIdentidad;
        $cliente->procedencia = $request->procedencia;
        $cliente->save();
        return response()->json([
            'success' => true,
            'message' => 'Cliente creado/a correctamente',
            'cliente' => $cliente
        ]);
    }

    public function update(ClienteValidation $request, $idCliente)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $cliente = (new Cliente())->getCliente($idCliente);
        $cliente->nombreCliente = strtoupper($request->nombreCliente);
        $cliente->celular = $request->celular;
        $cliente->cedulaIdentidad = $request->cedulaIdentidad;
        $cliente->procedencia = $request->procedencia;
        $cliente->modificado_por = session('id_usuario');
        $cliente->save();

        return response()->json([
            'success' => true,
            'message' => 'Cliente actualizado/a correctamente',
            'cliente' => $cliente
        ]);
    }

    public function deleteOrRestore(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $request->validate([
            'idCliente' => ['required', 'numeric', 'integer']
        ]);

        $cliente = (new Cliente())->getCliente($request->idCliente);
        $cliente->estado = $cliente->estado == '1' ? '0' : '1';
        $cliente->modificado_por = session('id_usuario');
        $cliente->save();
        return response()->json([
            'success' => true,
            'message' => $cliente->estado == '1' ? 'El/la cliente fue habilitado con éxito' : 'El/la cliente fue deshabilitado con éxito' ,
            'cliente' => $cliente
        ]);
    }
}
