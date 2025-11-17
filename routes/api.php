<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CupoController;
use App\Http\Controllers\PromocionController;
use App\Http\Controllers\PrecioProductoController;
use App\Http\Controllers\ControlController;
//SolicitudTrabajoController
use App\Http\Controllers\SolicitudTrabajoController;
//lupenuevo
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/pedidos/lupestor', [ProductoController::class, 'lupestor']);
Route::post('/pedidos/lupenuevo', [ProductoController::class, 'lupenuevo']);
Route::post('/pedidos/lupepedido', [ProductoController::class, 'lupepedido']);
//productos
//apis de lupe
Route::get('lupe', [ProductoController::class, 'lupe']);
Route::get('/lupe/categorias', [ProductoController::class,'categoriasConProductos'])->name('lupe.categoriasConProductos');

Route::get('/lupe/filtro_categorias', [ProductoController::class, 'filtro_categorias']);

Route::get('/cupos', [CupoController::class, 'cupos']);


Route::get('/promociones', [PromocionController::class, 'ver']);

Route::get('/promociones/{id}', [PromocionController::class, 'verid']);
Route::get('lupeestado', [ProductoController::class, 'lupeestado']);
    Route::get('/cupon', [CupoController::class, 'cupones']);
Route::post('/cupos/validar', [CupoController::class, 'validarCodigo']);
Route::get('/precio-productos', [PrecioProductoController::class, 'apiPrecio']);


Route::get('producto/{id}', [ProductoController::class, 'obtenerProductoPorId']);

Route::post('/solicitudes', [SolicitudTrabajoController::class, 'storeApi']);




//RUTAS PEDIDOS FORMULARIO
Route::post('/pedidos/formulariosucursal1', [ProductoController::class, 'formulariosucursal1']);
Route::post('/pedidos/formulariosucursal2', [ProductoController::class, 'formulariosucursal2']);
Route::post('/pedidos/formulariosucursal3', [ProductoController::class, 'formulariosucursal3']);
Route::post('/pedidos/formulariosucursal4', [ProductoController::class, 'formulariosucursal4']);

Route::get('/productos-moderna/{id}', [ControlController::class, 'apiProductosModerna']);
Route::post('/ventas/moderno', [ControlController::class, 'apiFinModernoAntiguo']);



























