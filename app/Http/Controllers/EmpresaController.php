<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmpresaValidation;
use App\Models\Empresa;
use Illuminate\Http\Request;

class EmpresaController extends Controller
{
    public function view_index()
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }

        return view('empresas.index', [
            'head_title' => 'GESTIÓN DE EMPRESAS'
        ]);
    }

    public function listarEmpresas()
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $empresas = (new Empresa())->getAllEmpresas();
        return response()->json([
            'data' => $empresas
        ]);
    }

    public function mostrarEmpresa(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $empresa = (new Empresa())->getEmpresa($request->empresa);
        return response()->json([
            'data' => $empresa
        ]);
    }

    public function create(EmpresaValidation $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $empresa = new Empresa();
        $empresa->nombreEmpresa = strtoupper($request->nombreEmpresa);
        $empresa->bonoEmpresaPorcentaje = $request->bonoEmpresaPorcentaje;
        $empresa->save();
        return response()->json([
            'success' => true,
            'message' => 'Empresa creada correctamente',
            'empresa' => $empresa
        ]);
    }

    public function update(EmpresaValidation $request, $idEmpresa)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $empresa = (new Empresa())->getEmpresa($idEmpresa);
        $empresa->nombreEmpresa = strtoupper($request->nombreEmpresa);
        $empresa->bonoEmpresaPorcentaje = strtoupper($request->bonoEmpresaPorcentaje);
        $empresa->modificado_por = session('id_usuario');
        $empresa->save();

        return response()->json([
            'success' => true,
            'message' => 'Empresa actualizada correctamente',
            'empresa' => $empresa
        ]);
    }

    public function deleteOrRestore(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $request->validate([
            'idEmpresa' => ['required', 'numeric', 'integer']
        ]);

        $empresa = (new Empresa())->getEmpresa($request->idEmpresa);
        $empresa->estado = $empresa->estado == '1' ? '0' : '1';
        $empresa->modificado_por = session('id_usuario');
        $empresa->save();
        return response()->json([
            'success' => true,
            'message' => $empresa->estado == '1' ? 'La empresa fue habilitada con éxito' : 'La empresa fue deshabilitada con éxito' ,
            'empresa' => $empresa
        ]);
    }
}
