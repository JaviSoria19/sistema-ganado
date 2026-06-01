<?php

use App\Http\Controllers\BovinoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\EntoreController;
use App\Http\Controllers\ParametroController;
use App\Http\Controllers\PesajeHistoricoController;
use App\Http\Controllers\PotreroController;
use App\Http\Controllers\RecuentoHistoricoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VentaController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Ruta por defecto
Route::get('/', function () {
    return redirect()->route('dashboard');
});

/*Estructura de Laravel => Route::get(URL web, método de controlador)->name(nombre para referenciar ruta)*/

Route::controller(UsuarioController::class)->group(function () {
    /* Rutas para gestionar la sesión del usuario y el panel de administración */
    Route::get('panel', 'view_dashboard')->name('dashboard');
    Route::get('iniciar-sesion', 'view_iniciar_sesion')->name('login');
    Route::get('cerrar-sesion', 'cerrar_sesion')->name('logout');
    Route::post('verificar', 'verificar')->name('login.verificar');

    /* Rutas para gestionar los registros de la tabla 'Usuarios' */
    Route::get('usuarios', 'view_index')->name('usuarios.index');
    Route::get('usuarios/listar', 'listarUsuarios')->name('usuarios.listar');
    Route::get('usuarios/{usuario}', 'mostrarUsuario')->name('usuarios.mostrar');
    Route::post('usuarios', 'create')->name('usuarios.create');
    Route::put('usuarios/{usuario}', 'update')->name('usuarios.update');
    Route::patch('usuarios/{usuario}', 'deleteOrRestore')->name('usuarios.deleteOrRestore');
});

Route::controller(PotreroController::class)->group(function () {
    Route::get('potreros', 'view_index')->name('potreros.index');
    Route::get('potreros/listar', 'listar')->name('potreros.listar');
    Route::get('potreros/{potrero}', 'mostrar')->name('potreros.mostrar');
    Route::post('potreros', 'create')->name('potreros.create');
    Route::put('potreros/{potrero}', 'update')->name('potreros.update');
    Route::patch('potreros/{potrero}', 'delete')->name('potreros.delete');

    Route::get('potreros/{potrero}/detalles', 'view_details')->name('potreros.detalles');
});

Route::controller(ClienteController::class)->group(function () {
    Route::get('clientes', 'view_index')->name('clientes.index');
    Route::get('clientes/listar', 'listar')->name('clientes.listar');
    Route::get('clientes/{cliente}', 'mostrar')->name('clientes.mostrar');
    Route::post('clientes', 'create')->name('clientes.create');
    Route::put('clientes/{cliente}', 'update')->name('clientes.update');
    Route::patch('clientes/{cliente}', 'delete')->name('clientes.delete');
});

Route::controller(EntoreController::class)->group(function () {
    Route::get('entores', 'view_index')->name('entores.index');
    Route::get('entores/listar', 'listar')->name('entores.listar');
    Route::get('entores/{entore}', 'mostrar')->name('entores.mostrar');
    Route::post('entores', 'create')->name('entores.create');
    Route::put('entores/{entore}', 'update')->name('entores.update');
    Route::patch('entores/{entore}', 'delete')->name('entores.delete');

    //Route::get('entores/{entore}/detalles', 'view_details')->name('entores.detalles');
});

Route::controller(BovinoController::class)->group(function () {
    Route::get('bovinos', 'view_index')->name('bovinos.index');
    Route::get('bovinos/listar', 'listar')->name('bovinos.listar');
    Route::get('bovinos/{bovino}', 'mostrar')->name('bovinos.mostrar');
    Route::post('bovinos', 'create')->name('bovinos.create');
    Route::put('bovinos/{bovino}', 'update')->name('bovinos.update');
    Route::patch('bovinos/{bovino}', 'delete')->name('bovinos.delete');

    Route::get('bovinos/{bovino}/detalles', 'view_details')->name('bovinos.detalles');
});

Route::controller(PesajeHistoricoController::class)->group(function () {
    Route::get('pesajes-historicos', 'view_index')->name('pesajes-historicos.index');
    Route::get('pesajes-historicos/crear', 'view_crear')->name('pesajes-historicos.crear');
    Route::get('pesajes-historicos/listar', 'listar')->name('pesajes-historicos.listar');
    Route::get('pesajes-historicos/{pesaje_historico}', 'mostrar')->name('pesajes-historicos.mostrar');
    Route::post('pesajes-historicos', 'create')->name('pesajes-historicos.create');
    Route::put('pesajes-historicos/{pesaje_historico}', 'update')->name('pesajes-historicos.update');
    Route::patch('pesajes-historicos/{pesaje_historico}', 'delete')->name('pesajes-historicos.delete');
});

Route::controller(RecuentoHistoricoController::class)->group(function () {
    Route::get('recuentos-historicos', 'view_index')->name('recuentos-historicos.index');
    Route::get('recuentos-historicos/crear', 'view_crear')->name('recuentos-historicos.crear');
    Route::get('recuentos-historicos/listar', 'listar')->name('recuentos-historicos.listar');
    Route::get('recuentos-historicos/{recuento_historico}', 'mostrar')->name('recuentos-historicos.mostrar');
    Route::post('recuentos-historicos', 'create')->name('recuentos-historicos.create');
    Route::put('recuentos-historicos/{recuento_historico}', 'update')->name('recuentos-historicos.update');
    Route::patch('recuentos-historicos/{recuento_historico}', 'delete')->name('recuentos-historicos.delete');
});

Route::controller(ParametroController::class)->group(function () {
    Route::get('parametros', 'view_index')->name('parametros.index');
    Route::put('parametros/{parametro}', 'update')->name('parametros.update');
});

Route::controller(VentaController::class)->group(function () {
    // Vistas web
    Route::get('ventas', 'view_index')->name('ventas.index');
    Route::get('ventas/crear', 'view_create')->name('ventas.crear');
    Route::get('ventas/{venta}/editar', 'view_update')->name('ventas.editar');
    Route::get('ventas/{venta}/imprimir', 'view_imprimir')->name('ventas.imprimir');
    Route::get('ventas/reporte_utilidades', 'view_reporte_utilidades')->name('ventas.utilidades');
    Route::get('ventas/reporte_perdidas', 'view_reporte_perdidas')->name('ventas.perdidas');

    // Operaciones CRUD
    Route::get('ventas/listar', 'listarVentas')->name('ventas.listar');
    Route::get('ventas/{venta}', 'mostrarVenta')->name('ventas.mostrar');
    Route::post('ventas', 'create')->name('ventas.create');
    Route::put('ventas/{venta}', 'update')->name('ventas.update');
    Route::patch('ventas/{venta}', 'delete')->name('ventas.delete');
});
