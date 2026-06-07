<?php

namespace App\Http\Controllers;

use App\Http\Requests\UsuarioValidation;
use App\Models\Usuario;
use Illuminate\Http\Request;
use App\Models\Empleado;
use App\Models\Parametro;
use App\Models\Venta;

class UsuarioController extends Controller
{
    public function view_iniciar_sesion()
    {
        return view('login');
    }

    public function view_dashboard()
    {
        /*Si no tiene acceso, se redirige a la ventana de inicio de sesión.*/
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }
        /*Al ingresar a la vista del panel de administración, se verifica si el usuario aún tiene acceso al sistema.*/
        $usuario = (new Usuario())->get_usuario(session('id_usuario'));
        if ($usuario->estado != 'activo') {
            session(['tiene_acceso' => false]);
        }

        $estadisticas = (new Venta())->dashboard_get_estadisticas_ventas();
        $saldos_pendientes = (new Venta())->dashboard_get_clientes_con_saldo();

        return view('panel.admin', [
            'head_title' => 'PANEL DE ADMINISTRACIÓN',
            'estadisticas' => $estadisticas,
            'saldos_pendientes' => $saldos_pendientes
        ]);
    }

    public function view_index()
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }
        return view('usuarios.index', [
            'head_title' => 'GESTIÓN DE USUARIOS',
        ]);
    }

    public function listarUsuarios()
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $usuarios = (new Usuario())->get_all_usuarios();
        return response()->json([
            'data' => $usuarios
        ]);
    }

    public function mostrarUsuario(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $usuario = (new Usuario())->get_usuario($request->usuario);
        return response()->json([
            'data' => $usuario
        ]);
    }

    public function create(UsuarioValidation $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $request->validate([
            'contrasenha' => ['required', 'string', 'min:8', 'max:100'],
            'recontrasenha' => ['required', 'string', 'min:8', 'max:100', 'same:contrasenha'],
        ]);

        $usuario = new Usuario();
        $usuario->usuario = strtoupper($request->usuario);
        $usuario->contrasenha = helper_encrypt($request->contrasenha);
        $usuario->tema_preferido = $request->tema_preferido;
        $usuario->save();

        return response()->json([
            'success' => true,
            'message' => 'Usuario registrado correctamente',
            'usuario' => $usuario
        ]);
    }

    public function update(UsuarioValidation $request, $id_usuario)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $usuario = (new Usuario())->get_usuario($id_usuario);
        $usuario->usuario = strtoupper($request->usuario);
        if ($request->contrasenha) {
            $usuario->contrasenha = helper_encrypt($request->contrasenha);
        }
        $usuario->tema_preferido = $request->tema_preferido;
        $usuario->modificado_por = session('id_usuario');
        $usuario->save();

        return response()->json([
            'success' => true,
            'message' => 'Usuario actualizado correctamente',
            'usuario' => $usuario
        ]);
    }

    public function deleteOrRestore(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $request->validate([
            'id_usuario' => ['required', 'numeric', 'integer']
        ]);

        $usuario = (new Usuario())->get_usuario($request->id_usuario);
        $usuario->estado = $usuario->estado == 'activo' ? 'inactivo' : 'activo';
        $usuario->fecha_eliminacion = $usuario->estado == 'activo' ? null : now();
        $usuario->eliminado_por = $usuario->estado == 'activo' ? null : session('id_usuario');
        $usuario->save();
        return response()->json([
            'success' => true,
            'message' => $usuario->estado == 'activo' ? 'El usuario fue habilitado con éxito' : 'El usuario fue deshabilitado con éxito',
            'usuario' => $usuario
        ]);
    }


    public function verificar(Request $request)
    {
        $usuario = (new Usuario())->login(
            trim(strtolower($request->usuario))
        );

        if (!$usuario) {
            return redirect()->route('login')->with([
                'mensaje' => 'EL USUARIO NO EXISTE.',
                'loginUsuario' => $request->usuario,
                'loginContrasenha' => $request->contrasenha,
            ]);
        }

        if ($usuario->estado != 'activo') {
            return redirect()->route('login')->with([
                'mensaje' => 'EL USUARIO NO TIENE ACCESO AL SISTEMA.',
                'loginUsuario' => $request->usuario,
                'loginContrasenha' => $request->contrasenha,
            ]);
        }

        if ($request->contrasenha != helper_decrypt($usuario->contrasenha)) {
            return redirect()->route('login')->with([
                'mensaje' => 'LA CONTRASEÑA ES INCORRECTA.',
                'loginUsuario' => $request->usuario,
                'loginContrasenha' => $request->contrasenha,
            ]);
        }
        //Si el usuario y la contraseña son correctos, se crea la sesión y se redirige al panel de administración.

        $parametro = (new Parametro())->get_parametro();
        
        session([
            'tiene_acceso' => true,
            'id_usuario' => $usuario->id_usuario,
            'usuario' => $usuario->usuario,
            'tema_preferido' => $usuario->tema_preferido,
            'unidad_animal' => $parametro->unidad_animal,
        ]);
        return redirect()->route('dashboard');
    }

    public function cerrar_sesion()
    {
        (new Usuario())->logout();
        return redirect()->route('login');
    }
}
