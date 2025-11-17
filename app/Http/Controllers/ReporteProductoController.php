<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\VentaProducto;
use App\Models\PedidoProducto;
use Carbon\Carbon;
use DataTables;
use Yajra\DataTables\DataTables as DataTablesDataTables;
use Yajra\DataTables\Facades\DataTables as FacadesDataTables;

class ReporteProductoController extends Controller
{
    // Mostrar el formulario de reporte
    public function showForm()
    {
        $productos = Producto::all();
        return view('reporte.productos', compact('productos'));
    }

    public function generateReport(Request $request)
    {
        // Validar los datos del formulario
        $request->validate([
            'id_producto' => 'required|exists:productos,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        // Obtener el producto seleccionado
        $producto = Producto::find($request->id_producto);

        // Convertir fechas
        $fecha_inicio = Carbon::parse($request->fecha_inicio);
        $fecha_fin = Carbon::parse($request->fecha_fin);

        // Obtener todas las ventas de ese producto dentro del rango de fechas
        $ventaProductos = VentaProducto::where('id_producto', $producto->id)
            ->whereHas('venta', function ($query) use ($fecha_inicio, $fecha_fin) {
                $query->whereBetween('fecha', [$fecha_inicio, $fecha_fin]);
            })
            ->get();

        // Calcular el total vendido
        $totalVentas = $ventaProductos->sum(function ($ventaProducto) {
            return $ventaProducto->cantidad * $ventaProducto->precio_unitario;
        });

        // Obtener todos los pedidos de ese producto dentro del rango de fechas
        $pedidoProductos = PedidoProducto::where('id_producto', $producto->id)
            ->whereBetween('fecha', [$fecha_inicio, $fecha_fin])
            ->get();

        // Calcular el total de pedidos (en cantidad total)
        $totalPedidos = $pedidoProductos->sum('cantidad');
        $totalPedidosPrecio = $pedidoProductos->sum('precio');
        // Obtener todos los productos para el formulario
        $productos = Producto::all();
        // Inicializar variables para el total general
        $totalGeneral = [
            'total' => 0, // Total vendido en todas las sucursales
            'cantidad' => 0, // Total de unidades vendidas en todas las sucursales
            'qr' => 0, // Total vendido por QR
            'efectivo' => 0 // Total vendido por Efectivo
        ];
        // Obtener totales por sucursal
        $ventasPorSucursal = [];
        foreach ($ventaProductos as $ventaProducto) {
            $sucursalId = $ventaProducto->venta->id_sucursal;
            if (!isset($ventasPorSucursal[$sucursalId])) {
                $ventasPorSucursal[$sucursalId] = ['total' => 0, 'cantidad' => 0, 'qr' => 0, 'efectivo' => 0];
            }

            // Lógica para calcular los pagos por sucursal y tipo de pago
            $totalVenta = $ventaProducto->cantidad * $ventaProducto->precio_unitario;
            $ventasPorSucursal[$sucursalId]['total'] += $totalVenta;
            $ventasPorSucursal[$sucursalId]['cantidad'] += $ventaProducto->cantidad; // Sumar la cantidad

            // Clasificar por tipo de pago
            if ($ventaProducto->venta->tipo_pago == 'QR') {
                $ventasPorSucursal[$sucursalId]['qr'] += $totalVenta;
                $totalGeneral['qr'] += $totalVenta; // Sumar QR al total general
            } else if ($ventaProducto->venta->tipo_pago == 'Efectivo') {
                $ventasPorSucursal[$sucursalId]['efectivo'] += $totalVenta;
                $totalGeneral['efectivo'] += $totalVenta; // Sumar Efectivo al total general
            }

            // Sumar a los totales generales
            $totalGeneral['total'] += $totalVenta; // Sumar el total vendido a todas las sucursales
            $totalGeneral['cantidad'] += $ventaProducto->cantidad; // Sumar las cantidades de todas las sucursales

        }

        // Pasar las variables a la vista
        return view('reporte.reporte', compact('totalPedidosPrecio','totalPedidos', 'totalGeneral', 'producto', 'ventaProductos', 'pedidoProductos', 'fecha_inicio', 'fecha_fin', 'productos', 'totalVentas', 'ventasPorSucursal'));
    }

    // Obtener los datos de las ventas para DataTables
    public function getVentasData(Request $request)
    {
        $productoId = $request->get('id_producto');
        $fechaInicio = Carbon::parse($request->get('fecha_inicio'));  // Convertir a Carbon
        $fechaFin = Carbon::parse($request->get('fecha_fin'));        // Convertir a Carbon

        $ventaProductos = VentaProducto::with('venta')
            ->where('id_producto', $productoId)
            ->whereHas('venta', function ($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
            })
            ->get();

        return DataTablesDataTables::of($ventaProductos)
            ->addColumn('fecha', function ($ventaProducto) {
                // Asegurarse de que sea un objeto Carbon antes de formatear
                return Carbon::parse($ventaProducto->venta->fecha)->format('d/m/Y H:i');
            })
            ->addColumn('cliente', function ($ventaProducto) {
                return $ventaProducto->venta ? $ventaProducto->venta->nombre_cliente : 'N/A';
            })


            ->addColumn('total', function ($ventaProducto) {
                return $ventaProducto->cantidad * $ventaProducto->precio_unitario;
            })
            ->addColumn('sucursal', function ($ventaProducto) {
                return $ventaProducto->venta && $ventaProducto->venta->sucursal ? $ventaProducto->venta->sucursal->nombre : 'N/A';
            })
            ->addColumn('tipo_pago', function ($ventaProducto) {
                return $ventaProducto->venta->tipo_pago;
            })
            ->make(true);
    }

    // Obtener los datos de los pedidos para DataTables
    public function getPedidosData(Request $request)
    {
        $productoId = $request->get('id_producto');
        $fechaInicio = Carbon::parse($request->get('fecha_inicio'));  // Convertir a Carbon
        $fechaFin = Carbon::parse($request->get('fecha_fin'));        // Convertir a Carbon

        $pedidoProductos = PedidoProducto::with('pedido')
            ->where('id_producto', $productoId)
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->get();

        return DataTablesDataTables::of($pedidoProductos)
            ->addColumn('fecha', function ($pedidoProducto) {
                // Asegurarse de que sea un objeto Carbon antes de formatear
                return Carbon::parse($pedidoProducto->pedido->fecha)->format('d/m/Y');
            })
            ->addColumn('id_pedido', function ($pedidoProducto) {
                return $pedidoProducto->pedido->id;
            })
            ->addColumn('nombre', function ($pedidoProducto) {
                return $pedidoProducto->pedido->nombre;
            })
            ->addColumn('total', function ($pedidoProducto) {
                return $pedidoProducto->cantidad * $pedidoProducto->precio;
            })
            ->make(true);
    }
}
