<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AbastecimientoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ParametroController;
use App\Http\Controllers\PedidoEmpresaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\SaldoEmpresaController;
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

/* Tabla con PK FK 'empleados'*/
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

Route::controller(EmpleadoController::class)->group(function () {
    Route::get('empleados', 'view_index')->name('empleados.index');
    Route::get('empleados/listar', 'listarEmpleados')->name('empleados.listar');
    Route::get('empleados/{empleado}', 'mostrarEmpleado')->name('empleados.mostrar');
    Route::post('empleados', 'create')->name('empleados.create');
    Route::put('empleados/{empleado}', 'update')->name('empleados.update');
});

Route::controller(ParametroController::class)->group(function () {
    Route::get('parametros', 'view_index')->name('parametros.index');
    Route::put('parametros/{parametro}', 'update')->name('parametros.update');
});

Route::controller(MarcaController::class)->group(function () {
    Route::get('marcas', 'view_index')->name('marcas.index');
    Route::get('marcas/listar', 'listarMarcas')->name('marcas.listar');
    Route::get('marcas/{marca}', 'mostrarMarca')->name('marcas.mostrar');
    Route::post('marcas', 'create')->name('marcas.create');
    Route::put('marcas/{marca}', 'update')->name('marcas.update');
    Route::patch('marcas/{marca}', 'deleteOrRestore')->name('marcas.deleteOrRestore');
});

Route::controller(ClienteController::class)->group(function () {
    Route::get('clientes', 'view_index')->name('clientes.index');
    Route::get('clientes/listar', 'listarClientes')->name('clientes.listar');
    Route::get('clientes/{cliente}', 'mostrarCliente')->name('clientes.mostrar');
    Route::post('clientes', 'create')->name('clientes.create');
    Route::put('clientes/{cliente}', 'update')->name('clientes.update');
    Route::patch('clientes/{cliente}', 'deleteOrRestore')->name('clientes.deleteOrRestore');
});

Route::controller(EmpresaController::class)->group(function () {
    Route::get('empresas', 'view_index')->name('empresas.index');
    Route::get('empresas/listar', 'listarEmpresas')->name('empresas.listar');
    Route::get('empresas/{empresa}', 'mostrarEmpresa')->name('empresas.mostrar');
    Route::post('empresas', 'create')->name('empresas.create');
    Route::put('empresas/{empresa}', 'update')->name('empresas.update');
    Route::patch('empresas/{empresa}', 'deleteOrRestore')->name('empresas.deleteOrRestore');
});

/* Tabla con PK FK 'empresas'*/
Route::controller(SaldoEmpresaController::class)->group(function () {
    Route::get('saldos-empresas', 'view_index')->name('saldos-empresas.index');
    Route::get('saldos-empresas/listar', 'listarSaldosEmpresas')->name('saldos-empresas.listar');
    Route::get('saldos-empresas/{saldo_empresa}', 'mostrarSaldoEmpresa')->name('saldos-empresas.mostrar');
    Route::post('saldos-empresas', 'create')->name('saldos-empresas.create');
    Route::put('saldos-empresas/{saldo_empresa}', 'update')->name('saldos-empresas.update');
    Route::patch('saldos-empresas/{saldo_empresa}', 'deleteOrRestore')->name('saldos-empresas.deleteOrRestore');
});

/* Tabla con PK FK 'empresas' y relación uno a muchos con detalles_pedidos_empresas*/
Route::controller(PedidoEmpresaController::class)->group(function () {
    Route::get('pedidos-empresas', 'view_index')->name('pedidos-empresas.index');
    Route::get('pedidos-empresas/{pedido_empresa}/imprimir', 'view_imprimir')->name('pedidos-empresas.imprimir');
    Route::get('pedidos-empresas/listar', 'listarPedidosEmpresas')->name('pedidos-empresas.listar');
    Route::get('pedidos-empresas/{pedido_empresa}', 'mostrarPedidoEmpresa')->name('pedidos-empresas.mostrar');
    Route::post('pedidos-empresas', 'create')->name('pedidos-empresas.create');
    Route::put('pedidos-empresas/{pedido_empresa}', 'update')->name('pedidos-empresas.update');
    Route::patch('pedidos-empresas/{pedido_empresa}', 'deleteOrRestore')->name('pedidos-empresas.deleteOrRestore');
});

/* Tabla con relación uno a muchos con pedidos*/
Route::controller(AbastecimientoController::class)->group(function () {
    Route::get('abastecimientos', 'view_index')->name('abastecimientos.index');
    Route::get('abastecimientos/listar', 'listarAbastecimientos')->name('abastecimientos.listar');
    Route::get('abastecimientos/{abastecimiento}', 'mostrarAbastecimiento')->name('abastecimientos.mostrar');
    Route::get('abastecimientos/{abastecimiento}/editar', 'view_update')->name('abastecimientos.editar');
    
    Route::post('abastecimientos', 'create')->name('abastecimientos.create');
    Route::put('abastecimientos/{abastecimiento}', 'update')->name('abastecimientos.update');
});

/* Tabla con PK FK 'empresas','marcas','abastecimientos' y relación muchos a muchos con 'ventas' mediante 'detalles_ventas'*/
Route::controller(ProductoController::class)->group(function () {
    Route::get('productos', 'view_index')->name('productos.index');
    Route::get('productos/listar', 'listarProductos')->name('productos.listar');
    Route::get('productos/{producto}', 'mostrarProducto')->name('productos.mostrar');
    Route::get('productos/{producto}/codigo', 'mostrarProductoPorCodigo')->name('productos.codigo');
    Route::post('productos', 'create')->name('productos.create');
    Route::put('productos/{producto}', 'update')->name('productos.update');
    Route::patch('productos/{producto}', 'delete')->name('productos.delete');
});

/* Tabla con PK FK 'usuarios','clientes','empleados' y relación muchos a muchos con 'productos' mediante 'detalles_ventas'*/
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
