<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmpleadoValidation;
use App\Models\Empleado;
use Illuminate\Http\Request;

class EmpleadoController extends Controller
{
    public function view_index()
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }

        return view('empleados.index', [
            'headTitle' => 'GESTIÓN DE EMPLEADOS'
        ]);
    }

    public function listarEmpleados()
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $empleados = (new Empleado())->getAllEmpleados();
        return response()->json([
            'data' => $empleados
        ]);
    }

    public function mostrarEmpleado(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $empleado = (new Empleado())->getEmpleado($request->empleado);
        return response()->json([
            'data' => $empleado
        ]);
    }

    public function create(EmpleadoValidation $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $empleado = new Empleado();
        $empleado->nombreEmpleado = strtoupper($request->nombreEmpleado);
        $empleado->save();
        return response()->json([
            'success' => true,
            'message' => 'Usuario creado correctamente',
            'empleado' => $empleado
        ]);
    }

    public function update(EmpleadoValidation $request, $idEmpleado)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $empleado = (new Empleado())->getEmpleado($idEmpleado);
        $empleado->nombreEmpleado = strtoupper($request->nombreEmpleado);
        $empleado->modificado_por = session('id_usuario');
        $empleado->save();

        return response()->json([
            'success' => true,
            'message' => 'Usuario actualizado correctamente',
            'empleado' => $empleado
        ]);
    }
}