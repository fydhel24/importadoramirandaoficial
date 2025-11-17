<?php

use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\AnalisisPredictivoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ConsultasModifi;
use App\Http\Controllers\CupoController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\SemanaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PdfController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\OrdenadoController;
use App\Http\Controllers\OrdenPdfController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\EnvioController;
//reportes nuevos
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\SucursaleController;
use App\Http\Controllers\TipoController;
use App\Http\Controllers\EnvioSucursalController;

use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;

use App\Http\Controllers\ControlController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\PdfReportesController;
use App\Http\Controllers\EnvioProductoController;

use App\Http\Controllers\NotasController;

use App\Http\Controllers\PrecioProductoController;
use App\Http\Controllers\PromocionController;

use App\Http\Controllers\NotaController;
use App\Http\Controllers\ReporteProductoController;
use App\Http\Controllers\NotaJefaController;

use App\Http\Controllers\CajaSucursalController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\PagoProveedorController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\InformeController;

use App\Http\Controllers\SalesReportController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\CapturaController;
use App\Http\Controllers\CarpetaController;
use App\Http\Controllers\RecojoController;
use App\Http\Controllers\PagoEmpleadoController;
use App\Http\Controllers\ProdRegistroMalEstadoController;
//use App\Http\Controllers\AuditoriaController;
//SolicitudTrabajoController
use App\Http\Controllers\SolicitudTrabajoController;
//rutas de roles
Route::resource('roles', RoleController::class)
    ->only('index', 'create', 'store', 'edit', 'update', 'destroy')
    ->names('roles');

//rutas de usuarios
Route::resource('users', UserController::class)
    ->only('index', 'create', 'store', 'edit', 'update', 'destroy')
    ->names('users');

Route::resource('productos', ProductoController::class)->except(['show']);

Route::delete('productos/fotos/{foto}', [ProductoController::class, 'destroyiamge'])->name('productos.fotos.destroy');
//productos
Route::get('/productos/generarReporte', [ProductoController::class, 'generarReporte'])->name('productos.generarReporte');
// Nueva ruta para generar el reporte de todos los productos
Route::get('/productos/report', [ProductoController::class, 'generarReporte'])->name('productos.report');

//productos
Route::get('/pedidos/semanas', [ClienteController::class, 'getPedidosPorSemana']);

Route::get('/reporte', [ReporteController::class, 'index'])
    ->middleware('can:reporte.index')
    ->name('reporte.index');
Route::post('/reporte/descargar', [ReporteController::class, 'descargar'])->name('reporte.descargar');
//nuevo

Route::get('/orden/pdf/bcResumen/{idSemana}', [OrdenPdfController::class, 'bcResumen'])->name('orden.pdf.bcResumen');

// Ruta existente
Route::get('/formulario', function () {
    return view('formulario.index');
})->name('formulario.index');

// Nueva ruta para el registro rápido
Route::post('/cliente/store-fast', [ClienteController::class, 'storeFast'])->name('cliente.storeFast');

// Ruta para mostrar el formulario (opcional, si tienes una vista específica para eso)
Route::get('/cliente', function () {
    return view('cliente.index');
})->name('cliente.index');

Route::post('/cliente/storenuevo', [ClienteController::class, 'storenuevo'])->name('cliente.storenuevo');
Route::get('/nuevo', function () {
    return view('nuevo.index');
})->name('nuevo.index');

Route::get('/aa', function () {
    return view('aa.index');
})->name('aa.index');

Route::get('/vistas', function () {
    return view('vista.index');
})->name('vista.index');
Route::get('/aa', function () {
    return view('aa.index');
})->name('aa.index');

// Ruta para manejar la creación del pedido
Route::post('/cliente', [ClienteController::class, 'store'])->name('cliente.store');
// Rutas protegidas por autenticación
Route::middleware('auth')->group(function () {
    Route::get('/orden', [OrdenadoController::class, 'index'])
        ->middleware('can:orden.index')
        ->name('orden.index');
    Route::get('/orden/pedidos/{id}', [OrdenadoController::class, 'pedidosPorSemana'])
        ->middleware('can:orden.pedidos')
        ->name('orden.pedidos');
    Route::get('/orden/create/{id}', [OrdenadoController::class, 'createPedido'])
        ->middleware('can:orden.create')
        ->name('orden.create');
    Route::post('/orden/store', [OrdenadoController::class, 'storePedido'])->name('orden.store');
    Route::get('/orden/edit/{id}', [OrdenadoController::class, 'editPedido'])
        ->middleware('can:orden.edit')
        ->name('orden.edit');
    Route::put('/orden/update/{id}', [OrdenadoController::class, 'updatePedido'])->name('orden.update');
    Route::delete('/orden/destroy/{id}', [OrdenadoController::class, 'destroyPedido'])
        ->middleware('can:orden.destroy')
        ->name('orden.destroy');
    Route::post('/ruta-a-tu-metodo/reporteResumen/{id}', [OrdenPdfController::class, 'reporteResumen'])->name('reporte.resumen');

    Route::resource('pedidos', PedidoController::class)
        ->only('index', 'create', 'store', 'edit', 'update', 'destroy', 'show')
        ->names('pedido');

    Route::resource('semanas', SemanaController::class)
        ->only('index', 'create', 'store', 'edit', 'update', 'destroy', 'show')
        ->names('semanas');

    // Ruta para generar PDF de pedidos seleccionados (nuevo)
    Route::get('/orden/pdf/nuevo/{idSemana}', [OrdenPdfController::class, 'generarPdfNuevo'])->name('orden.pdf.nuevo');

    // Ruta para generar reporte de fichas de pedidos seleccionados
    Route::get('/orden/pdf/nuevo/generate/{id}', [OrdenPdfController::class, 'generarReporteFicha'])->name('orden.pdf.nuevo.generate');
    // Rutas para generar el PDF
    Route::get('/orden/pdf/{id}', [OrdenPdfController::class, 'nuevo_pdf_id'])->name('orden.pdf');
    Route::get('/orden/pdf/generate/{id}', [OrdenPdfController::class, 'generatePdfid'])->name('orden.pdf.generate');

    Route::get('/pdf/hello', [PdfController::class, 'nuevo_pdf'])->name('pedidos.hello');
    Route::get('/pdf', [PdfController::class, 'generatePdf'])->name('pedidos.pdf');

    Route::resource('inventarios', InventarioController::class)
        ->only('index', 'create', 'store', 'edit', 'update', 'destroy', 'show')
        ->names('inventarios');
    Route::resource('cupos', CupoController::class)
        ->only('index', 'create', 'store', 'edit', 'update', 'destroy', 'show')
        ->names('cupos');
    //productos
    Route::resource('sucursales', SucursaleController::class)
        ->only('index', 'create', 'store', 'edit', 'update', 'destroy', 'show')
        ->names('sucursales');
    Route::resource('marcas', MarcaController::class)
        ->only('index', 'create', 'store', 'edit', 'update', 'destroy', 'show')
        ->names('marcas');
    Route::resource('categorias', CategoriaController::class)
        ->only('index', 'create', 'store', 'edit', 'update', 'destroy', 'show')
        ->names('categorias');
    Route::resource('tipos', TipoController::class)
        ->only('index', 'create', 'store', 'edit', 'update', 'destroy', 'show')
        ->names('tipos');
    // Rutas
    Route::get('/control', [ControlController::class, 'index'])->name('control.index');
    // Rutas para el controlador de Control
    Route::get('/control/sucursal/{id}', [ControlController::class, 'productos'])->name('control.productos');
    Route::get('/control/sucursal/{id}/filtrar', [ControlController::class, 'productos'])->name('control.productos.filtrar');
    Route::get('/control/sucursal/{id}/inventario', [ControlController::class, 'showInventarioForm'])->name('control.inventario.form');
    Route::post('/control/sucursal/{id}/inventario', [ControlController::class, 'realizarInventario'])->name('control.inventario');
    //ventas
    Route::get('/nota/pdf/', [OrdenPdfController::class, 'nota'])->name('nota.pdf');

    Route::post('/fin', [ControlController::class, 'fin'])->name('control.fin');
    // Ruta para la venta rápida
    Route::get('/ventarapida', [ControlController::class, 'ventaRapida'])->name('ventarapida');

    // Nueva ruta para generar el reporte de productos de una sucursal
    Route::get('sucursal/reporte/productos/{id}', [ControlController::class, 'generarReporte'])->name('sucursal.reporte.productos');
    //Ruta cancelar venta
    Route::get('/cancelarventa', [VentaController::class, 'index'])->name('cancelarventa.index');
    Route::post('/cancelarventa/{id}/revertir', [VentaController::class, 'revertirVenta'])->name('cancelarventa.revertir');

    //modifi
    Route::get('/update-stock', [ConsultasModifi::class, 'updateStockForAllProducts'])->name('updateStockForAllProducts');

    Route::get('/reporte/ventas/pdf', [PdfReportesController::class, 'generatePdf']);
    Route::get('/reporte/inventario/pdf', [PdfReportesController::class, 'generateinventarioPdf']);
    Route::get('/reporte/pedidos/pdf', [PdfReportesController::class, 'generatepedidoPdf']);
    Route::get('/reporte/pedido-productos/pdf', [PdfReportesController::class, 'generatepedidoproductoPdf']);

    Route::get('/imprimirSeleccionados', [NotasController::class, 'imprimirSeleccionados'])->name('nota.imprimirSeleccionados');
    Route::get('/nota-venta/{pedidoId}', [NotasController::class, 'nota'])->name('nota.venta');
    //13-11-2024
    Route::get('/check-code-existence/{codigo}', [CupoController::class, 'checkCodeExistence']);
    //ruta de envios de porductos a sucursales
    Route::get('/envios', [EnvioProductoController::class, 'index'])
        ->middleware('can:envios.index')
        ->name('envios.index');
    Route::get('/envios/create', [EnvioProductoController::class, 'create'])->name('envios.create');
    Route::post('/envios', [EnvioProductoController::class, 'store'])->name('envios.store');
    Route::get('/envios/transfer', [EnvioProductoController::class, 'transfer'])->name('envios.transfer');
    Route::post('/envios/transfer', [EnvioProductoController::class, 'storeTransfer'])->name('envios.storeTransfer');
    Route::put('/envios/revertir/{id}', [EnvioProductoController::class, 'revertir'])->name('envios.revertir');
    Route::patch('/orden/confirm/{pedido}', [OrdenadoController::class, 'confirmPedido1'])->name('orden.confirm');

    //15-11-2024
    Route::get('/reporte-ventas', [PdfReportesController::class, 'showSalesReport'])
        ->middleware('can:report.ventas')
        ->name('report.ventas');
    Route::get('/reporte/ventas/pdf', [PdfReportesController::class, 'generatePdf'])->name('report.ventas.pdf');

    //
    Route::get('/reporte/inventario', [PdfReportesController::class, 'showInventoryReport'])
        ->middleware('can:report.inventario')
        ->name('report.inventario');
    Route::get('/reporte/inventario/pdf', [PdfReportesController::class, 'generateInventarioPdf'])->name('report.inventario.pdf');
    //
    Route::get('/reporte/pedidos', [PdfReportesController::class, 'showPedidosReport'])
        ->middleware('can:reporte.pedidos')
        ->name('reporte.pedidos');
    Route::get('/reporte/pedidos/pdf', [PdfReportesController::class, 'generatepedidoPdf'])->name('reporte.pedidos.pdf');
    // Ruta para generar el PDF del reporte de productos con filtrado por fechas
    Route::get('/reporte/pedidos_producto', [PdfReportesController::class, 'showPedidoProductosReport'])
        ->middleware('can:reporte.pedidos_producto')
        ->name('reporte.pedidos_producto');
    Route::get('/reporte/pedidos_producto/pdf', [PdfReportesController::class, 'generatepedidoproductoPdf'])->name('reporte.pedidos_producto.pdf');
    //pedido
    Route::get('/orden/add-product/{id}', [OrdenadoController::class, 'addProduct'])->name('orden.add-product');
    Route::post('/orden/store-product/{id}', [OrdenadoController::class, 'storeProduct'])->name('orden.store-product');
    Route::post('/pedido-producto/eliminar', [OrdenadoController::class, 'eliminarProducto']);
    //29-11-2024
    Route::post('/cancelarventa', [VentaController::class, 'generarReporte'])->name('cancelarventa.nota');
    Route::post('/cancelarventa/reporte/{id}', [VentaController::class, 'generarReporteIndividual'])->name('cancelarventa.reporte');
    Route::get('/reportestock', [ProductoController::class, 'reporteStock'])->name('report.stock');
    Route::get('/reporte-stock/pdf', [ProductoController::class, 'generatePdf'])->name('reporteStock.pdf');

    //2-12-2024
    // Ruta para generar el reporte
    Route::get('/envios/report', [EnvioProductoController::class, 'report'])->name('envios.report');
    // Ruta para generar el reporte
    Route::get('/envios/reporte', [EnvioProductoController::class, 'generarReporte'])->name('envios.generarReporte');
    Route::get('/change-password', [UserController::class, 'changePasswordView'])->name('change.password.view');
    Route::post('/change-password', [UserController::class, 'changePassword'])->name('change.password');
    //4-12-2024
    Route::get('/reporte-usuario-ventas', [PdfReportesController::class, 'showUserSalesReport'])->name('report.user.ventas');
    Route::get('/reporte/usuario/ventas/pdf', [PdfReportesController::class, 'generateUserPdf'])->name('report.user.ventas.pdf');
    //11-12-2024
    Route::get('/envios/historial', [EnvioProductoController::class, 'historial'])->name('envios.historial');

    Route::get('/productos/{id}/stock', [EnvioProductoController::class, 'obtenerStock']);
    // Ruta para obtener usuarios de una sucursal
    Route::get('/usuarios/sucursal/{id}', [EnvioProductoController::class, 'getUsuariosPorSucursal']);
    Route::get('/productos/sucursal/{id}', [EnvioProductoController::class, 'getProductosPorSucursal']);
    Route::get('/productos/almacen', [ProductoController::class, 'productosAlmacen']);
    //10-12
    Route::post('/envios/reporte/{id}', [EnvioProductoController::class, 'generarReporteH'])->name('envios.generar-reporte');
    Route::post('/envios/revertir/{id}', [EnvioProductoController::class, 'revertirEnvio'])->name('envios.revertir');
    Route::get('/envios/obtener-stock-origen/{productoId}/{sucursalOrigenId}', [EnvioProductoController::class, 'obtenerStockSucursalOrigen']);
    //27-12-2024
    // Rutas GET
    Route::get('/envioscuaderno', [EnvioController::class, 'index'])->name('envioscuaderno.index');
    Route::get('/envioscuaderno/paginate', [EnvioController::class, 'paginate'])->name('envioscuaderno.paginate');
    Route::get('/envios/search', [EnvioController::class, 'search'])->name('envios.search');
    Route::get('/envios/paginate', [EnvioController::class, 'paginate'])->name('envios.paginate');
    Route::get('/envios/search-pedido', [EnvioController::class, 'searchPedido'])->name('envios.searchPedido');
    Route::get('/cuadernopedidos/search', [EnvioController::class, 'search_pedido'])->name('cuadernopedidos.search');

    Route::get('/envios/data', [EnvioController::class, 'dataTable'])->name('envios.data');
    Route::post('/envios/store-product/{id_pedido}/{id_envio}', [EnvioController::class, 'storeProduct'])->name('envio.store-product');
    Route::post('/envioproductos/store-product/{id_pedido}/{id_envio}', [EnvioController::class, 'envioproductos'])->name('envio.envioproductos');
  Route::get('/envios/getPedidoData', [EnvioController::class, 'getPedidoData'])->name('envios.getPedidoData');
   
    // Rutas POST
    Route::post('/storecuaderno', [EnvioController::class, 'store'])->name('envios.storecuaderno');
    Route::post('/envios/{envio}/set-pedido', [EnvioController::class, 'setPedido'])->name('envios.setPedido');
    Route::post('/envios/actualizarSemana', [EnvioController::class, 'actualizarSemana'])->name('envios.actualizarSemana');

    // Rutas PUT
    Route::put('/envios/{id}', [EnvioController::class, 'update'])->name('envios.update');

    // Rutas DELETE
    Route::delete('/envioscuaderno/{id}', [EnvioController::class, 'destroy'])->name('envios.destroy');
    Route::delete('/envios/destroyMultiple', [EnvioController::class, 'destroyMultiple'])->name('envios.destroyMultiple');
    //rutas de revertir ultima semena
    Route::get('/cancelarventa/ultimasemana', [VentaController::class, 'indexUltimaSemana'])->name('cancelarventa.ultimasemana');
    Route::post('/cancelarventa/revertirsemana/{id}', [VentaController::class, 'revertirVentaSemana'])->name('cancelarventa.revertirsemana');

    Route::patch('/orden/cambiar-estado', [OrdenadoController::class, 'cambiarEstado'])->name('orden.cambiarEstado');


    //29-12-2024

    // CRUD completo para promociones
    // Rutas para Promociones CRUD
    Route::get('promociones', [PromocionController::class, 'index'])->name('promociones.index'); // Listar
    Route::get('promociones/create', [PromocionController::class, 'create'])->name('promociones.create'); // Crear
    Route::post('promociones', [PromocionController::class, 'store'])->name('promociones.store'); // Guardar
    Route::get('promociones/{id}', [PromocionController::class, 'show'])->name('promociones.show'); // Mostrar
    Route::get('promociones/{id}/edit', [PromocionController::class, 'edit'])->name('promociones.edit'); // Editar
    Route::put('promociones/{id}', [PromocionController::class, 'update'])->name('promociones.update'); // Actualizar
    Route::delete('promociones/{id}', [PromocionController::class, 'destroy'])->name('promociones.destroy'); // Eliminar

    // Rutas adicionales relacionadas con Promociones
    Route::post('promociones/{id}/vender', [PromocionController::class, 'vender'])->name('promociones.vender'); // Vender promoción
    Route::post('promociones/validate-stock', [PromocionController::class, 'validateStock'])->name('promociones.validateStock'); // Validar stock
    Route::get('sucursales/{id}/productos', [ProductoController::class, 'getBySucursal'])->name('sucursales.productos'); // Obtener productos por sucursal

    //Route::get('/promociones/data', [PromocionController::class, 'sendData'])->name('promociones.data');
    Route::post('/finpromocion', [PromocionController::class, 'finpromocion'])->name('finpromocion');

    Route::get('/sucursales/{id}/productos', function ($id) {
        return \App\Models\Producto::whereHas('inventarios', function ($query) use ($id) {
            $query->where('id_sucursal', $id)->where('cantidad', '>', 0);
        })->get();
    });

    Route::post('/promociones/nota-venta', [PromocionController::class, 'notaPromocion'])->name('notaPromocion');
    //05/01/2025

    Route::post('/cancelarventa/generar-reporte', [VentaController::class, 'generarReporte'])->name('cancelarventa.generarReporte');
    // Ruta para mostrar el historial de ventas (si es necesario)
    Route::get('/cancelarventa/index', [VentaController::class, 'index'])->name('cancelarventa.index');

    Route::post('/envios/confirmar/{id}', [EnvioProductoController::class, 'confirmarEnvio'])->name('envios.confirmar');

    //
    Route::get('/envios/solicitud', [EnvioProductoController::class, 'solicitudes'])->name('envios.solicitud');
    Route::get('/envios/solicitud/solicitar', [EnvioProductoController::class, 'enviosP'])->name('envios.solicitar');
    Route::post('/envios/solicitud/solicitar', [EnvioProductoController::class, 'storeEnvio'])->name('envios.storeEnvio');
    Route::post('/cancelarventa/reportesemana/{id}', [VentaController::class, 'generarReporteIndividualSemana'])->name('cancelarventa.reportesemana');
    //7-1-2025
        Route::get('/envioscuaderno/sinlapaz', [EnvioController::class, 'indexSinLaPaz'])->name('envioscuaderno.indexSinLaPaz');
    Route::get('/envioscuaderno/sinlapazyenviados', [EnvioController::class, 'indexSinLaPazYEnviados'])->name('envioscuaderno.indexSinLaPazYEnviados');
    Route::get('/data-table/envios/sinlapaz', [EnvioController::class, 'dataTableSinLaPaz'])->name('envios.dataTableSinLaPaz');
Route::get('/data-table/envios/sinlapazyenviados', [EnvioController::class, 'dataTableSinLaPazYEnviados'])->name('envios.dataTableSinLaPazYEnviados');
Route::get('/envioscuaderno/sololapaz', [EnvioController::class, 'indexSoloLaPaz'])->name('envioscuaderno.sololapaz');
    Route::get('/data-table/envios/sololapaz', [EnvioController::class, 'dataTableSoloLaPaz'])->name('envios.dataTableSoloLaPaz');

    //12-01-2025

    Route::get('/precioproductos', [PrecioProductoController::class, 'index'])->name('precioproductos.index');
    Route::get('/precioproductos/data', [PrecioProductoController::class, 'getData'])->name('precioproductos.data');

Route::post('/precioproductos/{id}/update', [PrecioProductoController::class, 'updatePrice'])->name('precioproductos.update');
Route::get('/envios/generar-reporte/{id}', [EnvioProductoController::class, 'generarReporteH'])->name('envios.generar-reporte');
Route::get('/notas', [NotaController::class, 'index'])->name('notas.index');
    Route::post('/notas', [NotaController::class, 'store'])->name('notas.store');
    Route::delete('/notas/{id}', [NotaController::class, 'destroy'])->name('notas.destroy');
    
    //17-01-2025
    Route::get('/reportestockedit', [ProductoController::class, 'reporteStockEdit'])->name('report.stock');
    Route::post('report/update-stock', [ProductoController::class, 'updateStock'])->name('report.updateStock');
    //19-01-2025
    Route::get('/ventas', [PdfReportesController::class, 'showSalesReport'])->name('ventas.report');
    Route::get('/ventas/pdf', [PdfReportesController::class, 'generatePdf'])->name('ventas.pdf');
    Route::patch('orden/confirmar-seleccionados', [OrdenadoController::class, 'confirmarSeleccionados'])->name('orden.confirmarSeleccionados');

Route::put('/notas/{id}', [NotaController::class, 'update'])->name('notas.update');

    Route::patch('/orden/{id}', [OrdenadoController::class, 'update'])->name('orden.updatep');

    Route::get('report/ventas/dia', [PdfReportesController::class, 'reportDia'])->name('report.dias');
    Route::get('report/ventas/mes', [PdfReportesController::class, 'reportMes'])->name('report.mess');
    Route::get('report/ventas/pdfdia', [PdfReportesController::class, 'generateDailyPdf'])->name('report.pdfdia');
    Route::get('report/ventas/pdfmes', [PdfReportesController::class, 'generateMonthlyPdf'])->name('report.pdfmes');
    
        Route::get('/caja-sucursal', [CajaSucursalController::class, 'index'])->name('caja_sucursal.index');
    Route::get('/caja_sucursal/create', [CajaSucursalController::class, 'create'])->name('caja_sucursal.create');
    Route::post('/caja_sucursal', [CajaSucursalController::class, 'store'])->name('caja_sucursal.store');
    Route::get('/caja_sucursal/{id}/edit', [CajaSucursalController::class, 'edit'])->name('caja_sucursal.edit');
    Route::put('/caja_sucursal/{id}', [CajaSucursalController::class, 'update'])->name('caja_sucursal.update');
    Route::delete('caja_sucursal/{id}', [CajaSucursalController::class, 'destroy'])->name('caja_sucursal.destroy');
    //09/02/19-01-2025
        Route::get('reportes/productos', [ReporteProductoController::class, 'showForm'])->name('reportes.productos.form');
    Route::post('reportes/productos/generar', [ReporteProductoController::class, 'generateReport'])->name('reportes.productos.generar');
    
    // Rutas para obtener los datos de ventas y pedidos para DataTables
    Route::get('reportes/productos/ventas/data', [ReporteProductoController::class, 'getVentasData'])->name('reportes.productos.ventas.data');
    Route::get('reportes/productos/pedidos/data', [ReporteProductoController::class, 'getPedidosData'])->name('reportes.productos.pedidos.data');
    Route::get('/envios/{id}/edit', [EnvioProductoController::class, 'edit'])->name('envios.edit');

    Route::put('/enviosproducto/{id}', [EnvioProductoController::class, 'update'])->name('envios.producto.update');
   Route::get('/notasjefa', [NotaJefaController::class, 'index'])->name('notasjefa.index');
    Route::post('/notasjefa', [NotaJefaController::class, 'store'])->name('notasjefa.store');
    Route::delete('/notasjefa/{id}', [NotaJefaController::class, 'destroy'])->name('notasjefa.destroy');
    Route::put('/notasjefa/{id}', [NotaJefaController::class, 'update'])->name('notasjefa.update');
        Route::get('/reportestockedit', [ProductoController::class, 'reporteStockEdit'])->name('report.stock');
    Route::post('/update-stock-almacen', [ProductoController::class, 'updateAlmacenStock'])->name('report.updateAlmacenStock');
    Route::get('/reporte/caja/pdf', [CajaSucursalController::class, 'generatePdf'])->name('reporte_caja_pdf');
    Route::delete('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.resetPassword');


//16-02-2025
Route::post('/envios/send-sucursal1', [EnvioProductoController::class, 'sendSucursal1'])->name('envios.sendSucursal1');

 Route::post('/envios/solicitud-entre-sucursales', [EnvioProductoController::class, 'storeSolicitudEntreSucursales'])->name('envios.storeSolicitudEntreSucursales');
 
 //23-02-2025
 Route::post('/control/sucursal/{id}/favoritos', [ControlController::class, 'agregarFavorito'])->name('control.favoritos');
 Route::post('/control/sucursal/{id}/favoritos/quitar', [ControlController::class, 'quitarFavorito'])->name('control.quitarfavoritos');
 Route::get('/control/sucursal/{id}/favoritos', [ControlController::class, 'showFavoritosForm'])->name('control.favoritos.form');
 
 //04-03-2025
 //Route::resource('cajas', CajaController::class)->only('index', 'create', 'store', 'edit', 'update', 'destroy')->names('cajas');
 Route::get('/verificar-caja-abierta', [ControlController::class, 'verificarCajaAbierta']);
 //Route::get('cajas/reporte-pdf', [CajaController::class, 'generatePdf'])->name('cajas.reporte-pdf');
 // Rutas para Pagos a Proveedores
Route::resource('pagos', PagoProveedorController::class);
Route::resource('proveedores', ProveedorController::class)->parameters([ 'proveedores' => 'proveedor']);

Route::post('/pagos', [PagoProveedorController::class, 'store'])->name('pagos.store');
//informes de los pagos a proveedores etc...
Route::get('/informes', 'App\Http\Controllers\InformeController@index')->name('informes.index');
Route::get('/informes/pagos-diarios', 'App\Http\Controllers\InformeController@pagosDiarios')->name('informes.pagos-diarios');
Route::get('/informes/pagos-mensuales', 'App\Http\Controllers\InformeController@pagosMensuales')->name('informes.pagos-mensuales');
Route::get('/informes/proveedores-pagados', 'App\Http\Controllers\InformeController@proveedoresPagados')->name('informes.proveedores-pagados');
Route::get('/informes/proveedores-pendientes', 'App\Http\Controllers\InformeController@proveedoresPendientes')->name('informes.proveedores-pendientes');
 //09-03-2025
 Route::post('/cancelarventa/devolucion-rapida/{venta}', [VentaController::class, 'devolucionRapida'])->name('cancelarventa.devolucionRapida');

 
//Rutas estao
Route::post('/cancelarventa/devolucion-rapida/{venta}', [VentaController::class, 'devolucionRapida'])
    ->name('cancelarventa.devolucionRapida');


// Agrega esta nueva ruta en web.php:
Route::get('cancelarventa/{venta}/edit', [VentaController::class, 'edit'])->name('cancelarventa.edit');
Route::put('cancelarventa/{venta}/update', [VentaController::class, 'update'])->name('cancelarventa.update');
Route::post('cancelarventa/producto/devolver', [VentaController::class, 'devolverProducto'])->name('cancelarventa.devolver-producto');
Route::post('cancelarventa/producto/cancelar', [VentaController::class, 'cancelarProducto'])->name('cancelarventa.cancelar-producto');
Route::post('cancelarventa/producto/agregar', [VentaController::class, 'agregarProducto'])->name('cancelarventa.agregar-producto');
///guadalupe//
Route::post('/cancelarventa/devolucion-rapida/{venta}', [VentaController::class, 'devolucionRapida'])
    ->name('cancelarventa.devolucionRapida');
///NUEVO///
    Route::post('/venta/devolver-producto', [VentaController::class, 'devolverProducto'])->name('venta.devolver-producto');
Route::post('/venta/cancelar-producto', [VentaController::class, 'cancelarProducto'])->name('venta.cancelar-producto');
Route::get('/cancelarventa/devolver/{producto}', [VentaController::class, 'devolverProducto'])->name('cancelarventa.devolver-producto-individual');
Route::post('/cancelarventa/devolver-producto/{producto}', [VentaController::class, 'devolverProductoIndividual'])
    ->name('cancelarventa.devolver-producto-individual');

///
Route::post('/devolver-producto', [VentaController::class, 'devolverProducto'])->name('cancelarventa.devolucion-rapida');
    // En routes/web.php
Route::post('/cancelarventa/finalizar-edicion', [VentaController::class, 'finalizarEdicion'])
->name('cancelarventa.finalizar-edicion');

// Agregar estas rutas a web.php o a donde corresponda
Route::post('/cancelarventa/verificar-stock', [VentaController::class, 'verificarStock'])->name('cancelarventa.verificar-stock');
Route::post('/cancelarventa/ejecutar-devolucion', [VentaController::class, 'ejecutarDevolucion'])->name('cancelarventa.ejecutar-devolucion');
Route::post('/cancelarventa/cancelar-productos', [VentaController::class, 'cancelarProductos'])->name('cancelarventa.cancelar-productos');
Route::post('/cancelarventa/ejecutar-cambios', [VentaController::class, 'ejecutarCambios'])->name('cancelarventa.ejecutar-cambios');

Route::post('/pedidos/devolver/{id}', [OrdenadoController::class, 'devolverPedido'])->name('pedido.devolver');

//Route::get('/cajas/reporte-mensual', [CajaController::class, 'generateMonthlyReport'])->name('cajas.reporte-mensual');
    Route::delete('/envios/eliminar/{id}', [EnvioProductoController::class, 'eliminarSolicitud'])->name('envios.eliminar');
//Route::get('/cajas/reporte/{id}', [CajaController::class, 'generateIndividualPdf'])->name('cajas.reporte-individual');
//16/03/2025
  
    Route::get('/cajas-sucursales', [CajaController::class, 'sucursales'])->name('cajas.sucursales');
    Route::get('/cajas-sucursales/{id}', [CajaController::class, 'index'])->name('cajas.index');
    Route::get('/cajas-sucursales/{id}/create', [CajaController::class, 'create'])->name('cajas.create');
    Route::post('/cajas-sucursales/{id}', [CajaController::class, 'store'])->name('cajas.store');
    Route::get('/cajas/{caja}/edit/{id}', [CajaController::class, 'edit'])->name('cajas.edit');
    Route::put('/cajas/{caja}/{id}', [CajaController::class, 'update'])->name('cajas.update');
    Route::delete('/cajas/{caja}', [CajaController::class, 'destroy'])->name('cajas.destroy');
    Route::get('/cajas/{caja}/editCaja/{id}', [CajaController::class, 'editCaja'])->name('cajas.editCaja');
    Route::put('/cajas/{caja}/updateEdit/{id}', [CajaController::class, 'updateEdit'])->name('cajas.updateEdit');
    // Ruta para abrir todas las cajas
    Route::post('cajas/abrir_todas', [CajaController::class, 'abrirTodas'])->name('cajas.abrir_todas');
    Route::post('/cajas/cerrar_todas', [CajaController::class, 'cerrarTodas'])->name('cajas.cerrar_todas');
    Route::get('cajas/report/{id}', [CajaController::class, 'generateIndividualPdf'])->name('cajas.report');
    Route::get('/verificar-caja-abierta/{sucursalId}', [CajaController::class, 'verificarCajaAbierta']);
//17/03/2025
    Route::get('/envioscuaderno/pendientes', [EnvioController::class, 'indexpendientes'])->name('envioscuaderno.indexpendientes');
    Route::get('/data-table/envios/Pendientes', [EnvioController::class, 'dataTablePendientes'])->name('envios.dataTablePendientes');
    Route::get('/envioscuaderno/confirmados', [EnvioController::class, 'indexconfirmados'])->name('envioscuaderno.indexconfirmados');
    Route::get('/data-table/envios/Confirmados', [EnvioController::class, 'dataTableConfirmados'])->name('envios.dataTableConfirmados');
    // En routes/web.php
    Route::post('/pedidos/{pedido}/confirm', [OrdenadoController::class, 'confirmPedido'])->name('pedidos.confirm');
    Route::post('/pedidos/{id}/devolver', [OrdenadoController::class, 'devolverPedido'])->name('pedidos.devolver');
    
    
    //09/04/2025
      // Ruta para el reporte de ventas por día
    Route::get('/sales-report', [SalesReportController::class, 'index'])->name('sales-report.index');

    // Ruta para obtener los datos del reporte (día, semana, mes)
    Route::get('/sales-report/data', [SalesReportController::class, 'getSalesData'])->name('sales-report.data');

    // Ruta para el reporte de ventas por semana
    Route::get('/sales-report/week', function () {
        $branches = \App\Models\Sucursale::all();
        return view('sales_report.week', compact('branches'));
    })->name('sales-report.week');

    // Ruta para el reporte de ventas por mes
    Route::get('/sales-report/month', function () {
        $branches = \App\Models\Sucursale::all();
        return view('sales_report.month', compact('branches'));
    })->name('sales-report.month');
    
    
    //13042025
    
    Route::post('/envios/recepcion-mal-estado', [EnvioProductoController::class, 'recepcionMalEstado'])->name('envios.recepcionMalEstado');
    Route::get('/productos/sucursal/{sucursalId}', [EnvioProductoController::class, 'getProductosPorSucursales']);
    Route::get('/envios/productos-mal-estado', [EnvioProductoController::class, 'productosMalEstado'])->name('envios.productosMalEstado');
    Route::get('/usuarios/sucursal/{id}', [EnvioProductoController::class, 'usuariosPorSucursal']);
    Route::post('/envios/confirmar-mal-estado/{id}', [EnvioProductoController::class, 'confirmarMalEstado'])->name('envios.confirmarMalEstado');
    Route::post('/envios/revertir-recepcion/{id}', [EnvioProductoController::class, 'revertirRecepcion']);
    Route::post('/envios/generar-reporte-mal-estado/{id}', [EnvioProductoController::class, 'generarReporteMalEstadoSolo']); 
    Route::get('/envios/productos-mal-estado-pdf', [EnvioProductoController::class, 'generarPDF'])
    ->name('envios.productosMalEstadoPDF');


    Route::post('/envios/recepcion-almacen', [EnvioProductoController::class, 'recepcionAlmacen'])->name('envios.recepcionAlmacen');
    Route::get('/envios/productos-almacen', [EnvioProductoController::class, 'productosAlmacene'])->name('envios.productosAlmacen');
    Route::post('/envios/generar-reporte-almacen/{id}', [EnvioProductoController::class, 'generarReporteAlmacen']);
    Route::get('/envios/almacen-pdf', [EnvioProductoController::class, 'generarReporteAlmacenPdf'])
    ->name('envios.productosAlmacenPDF');
    
    //14042025
    
    Route::get('/ventas-canceladas', [SalesController::class, 'canceledSales'])->name('ventas.canceladas');

    Route::get('/ventas-canceladas/export', [SalesController::class, 'exportFilteredSales'])->name('ventas.canceladas.export');


    Route::get('/productos/pdf', [ProductoController::class, 'generarPDF'])->name('productos.precio.pdf');
    
    Route::get('/orden/cuaderno/edit/{id_envio}/{id_pedido}', [OrdenadoController::class, 'editPedidocuaderno'])
    ->middleware('can:orden.edit')
    ->name('orden.cuaderno');
    
    Route::put('/orden/cuaderno/update/{id}', [OrdenadoController::class, 'updatePedidocuaderno'])->name('orden.cuaderno.update');
      Route::get('/capturas/edit/{id}', [CapturaController::class, 'edit'])->name('capturas.edit');

    Route::resource('capturas', CapturaController::class);
    // Ruta para guardar la foto modificada
    Route::put('/capturas/update/{id}', [CapturaController::class, 'update'])->name('capturas.update');
    // Ruta para eliminar una captura
    // Ruta para eliminar una captura
    Route::delete('/capturas/{id}', [CapturaController::class, 'destroy'])->name('capturas.destroy');
    
//Route::resource('carpetas', CarpetaController::class);
Route::get('/carpetas', [CarpetaController::class, 'index'])->name('carpetas.index');
Route::get('/carpetas/create', [CarpetaController::class, 'create'])->name('carpetas.create');
Route::post('/carpetas', [CarpetaController::class, 'store'])->name('carpetas.store');
// Route::get('/carpetas/{carpeta}/edit', [CarpetaController::class, 'edit'])->name('carpetas.edit');
Route::put('/carpetas/{carpeta}', [CarpetaController::class, 'update'])->name('carpetas.update');
Route::delete('/carpetas/{carpeta}', [CarpetaController::class, 'destroy'])->name('carpetas.destroy');
Route::get('/carpetas/search', [CarpetaController::class, 'search'])->name('carpetas.search');

    
Route::resource('carpetas', CarpetaController::class);




Route::get('/envios/extra1', [EnvioController::class, 'extra1View'])->name('envios.extra1.view');
Route::get('/envios/extra1/data', [EnvioController::class, 'extra1Data'])->name('envios.extra1.data');
Route::get('/envios/generar-pdf', [OrdenPdfController::class, 'generarPdf'])->name('envios.generarPdf');
Route::get('/envios/generar-pdf-respaldo', [OrdenPdfController::class, 'generarPdfrespaldo'])->name('envios.generarPdfrespaldo');
// Ruta para validar los pedidos seleccionados
Route::get('/envios/validar-pedidos', [OrdenPdfController::class, 'validarPedidos'])->name('envios.validarPedidos');
Route::get('/envios/generar-fichas', [OrdenPdfController::class, 'generarPdfFichasSeleccionadas'])->name('envios.generarPdfFichasSeleccionadas');
Route::get('/envios/nota-venta', [NotasController::class, 'generarNotaVenta'])->name('envios.generarNotaVenta');

//23-04-2025
Route::get('/orden/cuaderno/extra1/edit/{id_envio}/{id_pedido}', [OrdenadoController::class, 'editPedidocuadernoextra1'])
->middleware('can:orden.edit')
->name('orden.cuaderno.extra1');

Route::put('/orden/cuaderno/extra1/update/{id}', [OrdenadoController::class, 'updatePedidocuadernoextra1'])->name('orden.cuaderno.extra1.update');
Route::get('/orden/cuaderno/enlp/edit/{id_envio}/{id_pedido}', [OrdenadoController::class, 'editPedidocuadernoenlp'])
->middleware('can:orden.edit')
->name('orden.cuaderno.enlp');

Route::put('/orden/cuaderno/enlp/update/{id}', [OrdenadoController::class, 'updatePedidocuadernoenlp'])->name('orden.cuaderno.enlp.update');
Route::get('/orden/cuaderno/lapaz/edit/{id_envio}/{id_pedido}', [OrdenadoController::class, 'editPedidocuadernolapaz'])
->middleware('can:orden.edit')
->name('orden.cuaderno.lapaz');

Route::put('/orden/cuaderno/lapaz/update/{id}', [OrdenadoController::class, 'updatePedidocuadernolapaz'])->name('orden.cuaderno.lapaz.update');

//24-04-2025
Route::get('/orden/cuaderno/lpconfirmados/edit/{id_envio}/{id_pedido}', [OrdenadoController::class, 'editPedidocuadernolapazconfirmados'])
->middleware('can:orden.edit')
->name('orden.cuaderno.lapazconfirmados');

Route::put('/orden/cuaderno/lpconfirmados/update/{id}', [OrdenadoController::class, 'updatePedidocuadernolapazconfirmados'])->name('orden.cuaderno.lapazconfirmados.update');

Route::get('/orden/cuaderno/lppendientes/edit/{id_envio}/{id_pedido}', [OrdenadoController::class, 'editPedidocuadernolapazpendientes'])
->middleware('can:orden.edit')
->name('orden.cuaderno.lppendientes');

Route::put('/orden/cuaderno/lppendientes/update/{id}', [OrdenadoController::class, 'updatePedidocuadernolapazpendientes'])->name('orden.cuaderno.lppendientes.update');

Route::get('/orden/cuaderno/sololapaz/edit/{id_envio}/{id_pedido}', [OrdenadoController::class, 'editPedidocuadernosololapaz'])
->middleware('can:orden.edit')
->name('orden.cuaderno.sololapaz');

Route::put('/orden/cuaderno/sololapaz/update/{id}', [OrdenadoController::class, 'updatePedidocuadernosololapaz'])->name('orden.cuaderno.sololapaz.update');

Route::post('/envios/mark-confirmed-as-sent', [EnvioController::class, 'markConfirmedAsSent'])->name('envios.markConfirmedAsSent');
Route::resource('solicitudes', SolicitudTrabajoController::class);

Route::get('/envios/{id}/productos', [EnvioController::class, 'getEnvioProductos'])->name('envios.productos');
Route::post('/envios/guardar-productos', [EnvioController::class, 'guardarProductos']);
//08052025

Route::get('/envios/faltante', [EnvioController::class, 'faltanteView'])->name('envios.faltante.view');
Route::get('/envios/faltante/data', [EnvioController::class, 'faltanteData'])->name('envios.faltante.data');

Route::get('/orden/cuaderno/faltantes/edit/{id_envio}/{id_pedido}', [OrdenadoController::class, 'editPedidocuadernofaltantes'])
->middleware('can:orden.edit')
->name('orden.cuaderno.faltantes');
Route::put('/orden/cuaderno/faltantes/update/{id}', [OrdenadoController::class, 'updatePedidocuadernofaltantes'])->name('orden.cuaderno.faltantes.update');

//
Route::get('/notas/imprimir/{id}', [NotaController::class, 'imprimirNota'])->name('notas.imprimir');

//1/07/2025
Route::get('/ventarecojo', [RecojoController::class, 'index'])->name('recojo.index');
Route::put('/ventas/{venta}', [RecojoController::class, 'update'])->name('ventas.update');
Route::get('/ventas/nota', [RecojoController::class, 'nota'])->name('ventas.nota');

Route::get('/ventarapidamoderna', [ControlController::class, 'ventarapidamoderna'])->name('ventarapidamoderna');
Route::get('/control/moderno/sucursal/{id}', [ControlController::class, 'productosmoderna'])->name('control.productos.moderna');

//11/07/2025Route::get('/recojo/nueva/{id}/{idventa}', [RecojoController::class, 'productosnuevos'])->name('recojo.productosnuevos');
   
    Route::get('/recojoproducto/{idventa}', function ($idventa) {
        return view('ventarecojo.pro', ['idventa' => $idventa]);
    })->name('recojoproducto.pro');

    Route::get('/nota/productosnuevos/pdf/', [RecojoController::class, 'pdf'])->name('productosnuevos.pdf');
    

//14/07/2025
Route::get('/control/moderno1/sucursal/{id}', [ControlController::class, 'productosmoderna'])->name('control.productos.moderna');
Route::post('/fin/finmodernoActualizar/{idventa}', [ControlController::class, 'finmodernoActualizar'])->name('control.finmoderno.finmodernoActualizar');
Route::get('/recojo/nueva/{id}/{idventa}', [RecojoController::class, 'productosnuevos'])->name('recojo.productosnuevos');

///18/7/2025
//ruta para crear cuaderno de sucursales
    Route::get('/envioscuadernosucursal', [EnvioSucursalController::class, 'index'])->name('envioscuadernosucursal.index');
    Route::get('/envioscuadernosucursal/{id}', [EnvioSucursalController::class, 'indexsucursales'])->name('envioscuaderno.index.sucursal');
    Route::post('/storecuadernosucursal', [EnvioSucursalController::class, 'storesucursal'])->name('envios.storecuadernosucursal');
    Route::get('/envios/datasucursal', [EnvioSucursalController::class, 'dataTablesucursal'])->name('envios.datasucursal');

    //ruta para editar cuaderno de sucursal
    Route::get('/orden/cuaderno/editsucursal/{id_envio}/{id_pedido}', [OrdenadoController::class, 'editPedidocuadernosucursal'])
        ->middleware('can:orden.edit')
        ->name('orden.cuaderno');
    Route::put('/orden/cuaderno/updatesucursal/{id}', [OrdenadoController::class, 'updatePedidocuadernosucursal'])->name('orden.cuadernosucursal.update');
   //rutas de confirmar y devolver sucursal 
    Route::post('/pedidos/{pedido}/confirmsucursal', [OrdenadoController::class, 'confirmPedidosucursal'])->name('pedidos.confirm.sucursal');
    Route::post('/pedidos/{id}/devolversucursal', [OrdenadoController::class, 'devolverPedidosucursal'])->name('pedidos.devolver.sucursal');


// NUEVA RUTA para procesar y guardar OCR de todas las capturas de una carpeta
Route::post('/carpetas/{carpeta}/process-all-ocr', [CapturaController::class, 'processAllOcrAndSave'])->name('carpetas.process_all_ocr');
// NUEVA RUTA para sugerencias de búsqueda en tiempo real
Route::get('/carpetas/{carpeta}/search-suggestions', [CarpetaController::class, 'searchSuggestions'])->name('carpetas.search_suggestions');
// NUEVA RUTA para la búsqueda de imágenes en tiempo real
Route::get('/carpetas/{carpeta}/search-realtime', [CarpetaController::class, 'searchRealtime'])->name('carpetas.search_realtime');

////23/7/2025
Route::get('/envioscuaderno/sinmarcados', [EnvioController::class, 'indexsinmarcados'])->name('envioscuaderno.indexsinmarcados');
      Route::get('/data-table/envios/sinmarcados', [EnvioController::class, 'dataTablesinmarcados'])->name('envios.dataTablesinmarcados');

//08/08/2025

Route::get('/envios/search-pedido/sucursal', [EnvioController::class, 'searchPedidoSucursal'])->name('envios.searchPedidoSucursal');

//rutas pagos empleados
    Route::get('/planilla-pagos', [PagoEmpleadoController::class, 'index'])->name('pagos.index');
    Route::post('/pagos/realizar', [PagoEmpleadoController::class, 'realizarPago'])->name('pagos.realizar');
    Route::get('/pagos/pdf/all', [PagoEmpleadoController::class, 'generateAllPdf'])->name('pagos.generateAllPdf');
    Route::get('/pagos/pdf/single', [PagoEmpleadoController::class, 'generatePdf'])->name('pagos.generatePdf');


//rutas230925lupe
// Route::resource('registros', RegistroMalEstadoController::class);

Route::resource('registros', ProdRegistroMalEstadoController::class)
    ->names([
        'index'   => 'prodregistromalestado.index',
        'create'  => 'prodregistromalestado.create',
        'store'   => 'prodregistromalestado.store',
        'show'    => 'prodregistromalestado.show',
        'edit'    => 'prodregistromalestado.edit',
        'update'  => 'prodregistromalestado.update',
        'destroy' => 'prodregistromalestado.destroy',
    ]);

// Rutas adicionales para actualizar checkboxes y campos en tiempo real
Route::put('prodregistromalestado/{id}/toggle-check', [ProdRegistroMalEstadoController::class, 'toggleCheck'])->name('prodregistromalestado.toggleCheck');
Route::put('prodregistromalestado/{id}/update-descripcion', [ProdRegistroMalEstadoController::class, 'updateDescripcion'])->name('prodregistromalestado.updateDescripcion');
Route::put('prodregistromalestado/{id}/update-estado', [ProdRegistroMalEstadoController::class, 'updateEstado'])->name('prodregistromalestado.updateEstado');

Route::get('/productos/buscar', [ProdRegistroMalEstadoController::class, 'buscarProductos'])
    ->name('productos.buscar');
    
//rutaauditoriastock091025
Route::get('/reportes/auditoria-stock', [AuditoriaController::class, 'auditoriaStock'])->name('report.stocklog');
    Route::get('/reportes/auditoria-stock/data', [AuditoriaController::class, 'auditoriaStockData'])->name('report.stocklog.data');
    Route::post('/auditoria/detalle/store', [AuditoriaController::class, 'guardarDetalle'])->name('auditoria.detalle.store');
    Route::get('/inventario/stock-actual', [AuditoriaController::class, 'getStockActual'])->name('inventario.stock_actual');
    Route::get('/auditorias/inventario', [AuditoriaController::class, 'vistaAuditorias'])->name('auditorias.inventario');
    Route::get('auditorias/data', [AuditoriaController::class, 'getAuditoriasData'])->name('auditorias.data');
    Route::post('/auditorias/{id}/solucionar', [AuditoriaController::class, 'marcarComoSolucionado'])->name('auditorias.solucionar');
    
    //rutas de analisispredictivo
    Route::get('/analisis', [AnalisisPredictivoController::class, 'index'])->name('analisis.index');
    Route::get('/analisis/search-ajax', [AnalisisPredictivoController::class, 'searchProductos'])->name('analisis.searchProductos');

});

// Ruta pública
// Ruta pública
Route::middleware(['auth'])->group(function () {
    Route::get('/', [VentaController::class, 'estadisticasHome']);
});

Route::get('/home', function () {
    return redirect('/');
})->name('home');


/////GUADALUPE
Route::get('/dashboard/home', [VentaController::class, 'estadisticasHome'])->name('dashboard.home');

///06/07/2025
    Route::get('/ventarecojomoderno', [RecojoController::class, 'indexmoderno'])->name('recojo.index');
    Route::get('/ventarecojomodernocola', [RecojoController::class, 'indexmodernocola'])->name('recojo.index');
    
    Route::get('/ventas/detalles/{id}', [RecojoController::class, 'verid']);

    Route::get('/ventas/detalles', [RecojoController::class, 'ver']);
    Route::post('/fin/moderno', [ControlController::class, 'finmodernoantiguo'])->name('control.finmoderno');
    Route::get('/ventas/modernocola', [RecojoController::class, 'getVentasModernas']);
    Route::get('/recojoproductocola/{idventa}', function ($idventa) {
        return view('ventarecojo.pro1', ['idventa' => $idventa]);
    })->name('recojoproducto.pro');

// Rutas de autenticación
Auth::routes();
use App\Http\Controllers\VerificacionController;

Route::get('/verificacion', [VerificacionController::class, 'index'])->name('verificacion.index');
Route::get('/verificacion/validar', [VerificacionController::class, 'validar'])->name('verificacion.validar');

