<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaldoEmpresaValidation;
use App\Models\SaldoEmpresa;
use Illuminate\Http\Request;
use App\Models\Empresa;

class SaldoEmpresaController extends Controller
{
    public function view_index()
    {
        if (!session('tiene_acceso')) {
            return redirect()->route('login');
        }

        $empresas = (new Empresa())->getAllEmpresas();

        return view('saldos_empresas.index', [
            'headTitle' => 'GESTIÓN DE SALDOS DE EMPRESAS',
            'empresas' => $empresas,
        ]);
    }

    public function listarSaldosEmpresas()
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $saldos_empresas = (new SaldoEmpresa())->getAllSaldosEmpresas();
        return response()->json([
            'data' => $saldos_empresas
        ]);
    }

    public function mostrarSaldoEmpresa(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $saldo_empresa = (new SaldoEmpresa())->getSaldoEmpresa($request->saldo_empresa);
        return response()->json([
            'data' => $saldo_empresa
        ]);
    }

    public function create(SaldoEmpresaValidation $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $saldo_empresa = new SaldoEmpresa();
        $saldo_empresa->idEmpresa = $request->idEmpresa;
        $saldo_empresa->montoUSD = $request->montoUSD;
        $saldo_empresa->pagoUSD = $request->pagoUSD;
        $saldo_empresa->fecha = $request->fecha;
        $saldo_empresa->save();

        return response()->json([
            'success' => true,
            'message' => 'Saldo de empresa registrado correctamente',
            'saldo_empresa' => $saldo_empresa
        ]);
    }

    public function update(SaldoEmpresaValidation $request, $idSaldoEmpresa)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $saldo_empresa = (new SaldoEmpresa())->getSaldoEmpresa($idSaldoEmpresa);
        $saldo_empresa->idEmpresa = $request->idEmpresa;
        $saldo_empresa->montoUSD = $request->montoUSD;
        $saldo_empresa->pagoUSD = $request->pagoUSD;
        $saldo_empresa->fecha = $request->fecha;
        $saldo_empresa->modificado_por = session('id_usuario');
        $saldo_empresa->save();

        return response()->json([
            'success' => true,
            'message' => 'Saldo de empresa actualizado correctamente',
            'saldo_empresa' => $saldo_empresa
        ]);
    }

    public function deleteOrRestore(Request $request)
    {
        if (!session('tiene_acceso')) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso'], 403);
        }

        $request->validate([
            'idSaldoEmpresa' => ['required', 'numeric', 'integer']
        ]);

        $saldo_empresa = (new SaldoEmpresa())->getSaldoEmpresa($request->idSaldoEmpresa);
        $saldo_empresa->estado = $saldo_empresa->estado == '1' ? '0' : '1';
        $saldo_empresa->modificado_por = session('id_usuario');
        $saldo_empresa->save();
        return response()->json([
            'success' => true,
            'message' => $saldo_empresa->estado == '1' ? 'El saldo de empresa fue restaurado con éxito' : 'El saldo de empresa fue archivado con éxito' ,
            'saldo_empresa' => $saldo_empresa
        ]);
    }
}
