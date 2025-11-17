<?php

namespace App\Http\Controllers;

use App\Models\VentaProducto;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\Sucursale;
use App\Models\User;
use App\Models\Venta;

use Illuminate\Http\Request;
use Exception;
use FPDF;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{


    public function devolucionRapida(Venta $venta)
    {
        try {
            DB::beginTransaction();

            // Primero verificamos el stock de todos los productos
            foreach ($venta->ventaProductos as $ventaProducto) {
                $inventario = Inventario::where('id_producto', $ventaProducto->id_producto)
                    ->where('id_sucursal', $venta->id_sucursal)
                    ->first();

                if (!$inventario || $inventario->cantidad < $ventaProducto->cantidad) {
                    DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => 'No hay suficiente stock para el producto: ' . $ventaProducto->producto->nombre
                    ], 400);
                }
            }

            // Si hay suficiente stock, creamos la nueva venta
            $ventaDevolucion = Venta::create([
                'fecha' => now(),
                'nombre_cliente' => $venta->nombre_cliente,
                'costo_total' => $venta->costo_total,
                'id_user' => auth()->id(),
                'ci' => $venta->ci,
                'tipo_pago' => $venta->tipo_pago,
                'id_sucursal' => $venta->id_sucursal,
                'garantia' => $venta->garantia,
                'estado' => 'DEVUELTO',
            ]);

            // Procesamos cada producto
            foreach ($venta->ventaProductos as $ventaProducto) {
                // Crear el registro de venta_producto
                VentaProducto::create([
                    'id_venta' => $ventaDevolucion->id,
                    'id_producto' => $ventaProducto->id_producto,
                    'cantidad' => $ventaProducto->cantidad,
                    'precio_unitario' => $ventaProducto->precio_unitario,
                    'descuento' => $ventaProducto->descuento,
                ]);

                // Restar del inventario
                $inventario = Inventario::where('id_producto', $ventaProducto->id_producto)
                    ->where('id_sucursal', $venta->id_sucursal)
                    ->first();

                $inventario->cantidad -= $ventaProducto->cantidad;
                $inventario->save();
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la devolución: ' . $e->getMessage()
            ], 500);
        }
    }
    public function edit($id)
    {
        $venta = Venta::with(['ventaProductos.producto', 'sucursal', 'user'])->findOrFail($id);
        $productos = Producto::where('estado', 'activo')->get();

        return view('cancelarventa.edit', compact('venta', 'productos'));
    }


    public function ejecutarDevolucion(Request $request)
    {
        $request->validate([
            'venta_id' => 'required|exists:ventas,id',
            'productos' => 'required|array',
            'productos.*.ventaProductoId' => 'required|exists:venta_producto,id',
            'productos.*.cantidad' => 'required|integer|min:1'
        ]);

        return DB::transaction(function () use ($request) {
            $ventaOriginal = Venta::findOrFail($request->venta_id);
            $productosDevueltos = [];
            $montoTotalDevolucion = 0;

            // Verificar stock para todos los productos primero
            foreach ($request->productos as $producto) {
                $ventaProducto = VentaProducto::findOrFail($producto['ventaProductoId']);

                // Verificar que la cantidad a devolver no sea mayor a la vendida
                if ($producto['cantidad'] > $ventaProducto->cantidad) {
                    return response()->json([
                        'error' => 'Cantidad a devolver mayor a la vendida para el producto: ' .
                            Producto::find($ventaProducto->id_producto)->nombre
                    ], 400);
                }

                // Verificar stock
                $inventario = Inventario::where('id_producto', $ventaProducto->id_producto)
                    ->where('id_sucursal', $ventaOriginal->id_sucursal)
                    ->first();

                if (!$inventario || $inventario->cantidad < $producto['cantidad']) {
                    return response()->json([
                        'error' => 'No hay suficiente stock para devolver el producto: ' .
                            Producto::find($ventaProducto->id_producto)->nombre
                    ], 400);
                }

                $subtotal = $producto['cantidad'] * $ventaProducto->precio_unitario;
                $montoTotalDevolucion += $subtotal;

                $productosDevueltos[] = [
                    'ventaProducto' => $ventaProducto,
                    'cantidad' => $producto['cantidad'],
                    'subtotal' => $subtotal
                ];
            }

            // Crear una nueva venta con estado DEVUELTO
            $ventaDevolucion = Venta::create([
                'fecha' => now(),
                'nombre_cliente' => $ventaOriginal->nombre_cliente,
                'costo_total' => $montoTotalDevolucion,
                'id_user' => auth()->id(),
                'ci' => $ventaOriginal->ci,
                'tipo_pago' => $ventaOriginal->tipo_pago,
                'id_sucursal' => $ventaOriginal->id_sucursal,
                'garantia' => $ventaOriginal->garantia,
                'estado' => 'DEVUELTO',
                'venta_original_id' => $ventaOriginal->id
            ]);

            // Procesar cada producto
            foreach ($productosDevueltos as $item) {
                $ventaProducto = $item['ventaProducto'];
                $cantidad = $item['cantidad'];

                // Crear el registro de producto devuelto
                VentaProducto::create([
                    'id_venta' => $ventaDevolucion->id,
                    'id_producto' => $ventaProducto->id_producto,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $ventaProducto->precio_unitario,
                    'descuento' => $ventaProducto->descuento ?? 0
                ]);

                // Actualizar inventario
                $inventario = Inventario::where('id_producto', $ventaProducto->id_producto)
                    ->where('id_sucursal', $ventaOriginal->id_sucursal)
                    ->first();

                $inventario->cantidad -= $cantidad;
                $inventario->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Devolución registrada correctamente',
                'venta_devolucion_id' => $ventaDevolucion->id
            ], 200);
        });
    }




    public function cancelarProductos(Request $request)
    {
        $request->validate([
            'venta_id' => 'required|exists:ventas,id',
            'productos_cancelados' => 'required|array',
            'productos_cancelados.*.ventaProductoId' => 'required|exists:venta_producto,id',
            'productos_cancelados.*.cantidad' => 'required|integer|min:1'
        ]);

        try {
            DB::beginTransaction();

            $venta = Venta::findOrFail($request->venta_id);
            $totalCancelado = 0;

            // Procesar cada producto a cancelar
            foreach ($request->productos_cancelados as $producto) {
                $ventaProducto = VentaProducto::findOrFail($producto['ventaProductoId']);

                // Verificar que la cantidad a cancelar no sea mayor a la vendida
                if ($producto['cantidad'] > $ventaProducto->cantidad) {
                    DB::rollback();
                    return response()->json([
                        'error' => 'La cantidad a cancelar no puede ser mayor a la cantidad vendida.'
                    ], 400);
                }

                $subtotal = $producto['cantidad'] * $ventaProducto->precio_unitario;
                $totalCancelado += $subtotal;

                // Devolver stock al inventario
                $inventario = Inventario::where('id_producto', $ventaProducto->id_producto)
                    ->where('id_sucursal', $venta->id_sucursal)
                    ->first();

                if (!$inventario) {
                    // Si no existe el inventario, crearlo
                    $inventario = new Inventario();
                    $inventario->id_producto = $ventaProducto->id_producto;
                    $inventario->id_sucursal = $venta->id_sucursal;
                    $inventario->cantidad = $producto['cantidad'];
                } else {
                    // Si existe, aumentar el stock
                    $inventario->cantidad += $producto['cantidad'];
                }
                $inventario->save();

                // // Actualizar o eliminar el producto de la venta
                // if ($producto['cantidad'] == $ventaProducto->cantidad) {
                //     // Si se cancela toda la cantidad, eliminar el registro
                //     $ventaProducto->delete();
                // } else {
                //     // Si se cancela parcialmente, actualizar la cantidad
                //     $ventaProducto->cantidad -= $producto['cantidad'];
                //     $ventaProducto->save();
                // }
            }

            // Actualizar el total de la venta
            $venta->costo_total -= $totalCancelado;
            if ($venta->costo_total <= 0) {
                // Si el total queda en 0 o negativo, marcar la venta como cancelada
                $venta->estado = 'CANCELADO';
            }
            $venta->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Productos cancelados correctamente'
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => 'Error al procesar la cancelación: ' . $e->getMessage()
            ], 500);
        }
    }

    public function ejecutarCambios(Request $request)
    {
        $request->validate([
            'venta_id' => 'required|exists:ventas,id',
        ]);

        try {
            DB::beginTransaction();

            $venta = Venta::findOrFail($request->venta_id);
            $totalAfectado = 0;

            // Procesar devoluciones si existen
            if (!empty($request->productos_devolucion)) {
                foreach ($request->productos_devolucion as $producto) {
                    $ventaProducto = VentaProducto::findOrFail($producto['ventaProductoId']);

                    // Verificar que la cantidad a devolver no sea mayor a la vendida
                    if ($producto['cantidad'] > $ventaProducto->cantidad) {
                        DB::rollback();
                        return response()->json([
                            'error' => 'La cantidad a devolver no puede ser mayor a la cantidad vendida.'
                        ], 400);
                    }

                    // Verificar stock
                    $inventario = Inventario::where('id_producto', $ventaProducto->id_producto)
                        ->where('id_sucursal', $venta->id_sucursal)
                        ->first();

                    if (!$inventario || $inventario->cantidad < $producto['cantidad']) {
                        DB::rollback();
                        return response()->json([
                            'error' => 'No hay suficiente stock para devolver el producto: ' .
                                Producto::find($ventaProducto->id_producto)->nombre
                        ], 400);
                    }

                    $subtotal = $producto['cantidad'] * $ventaProducto->precio_unitario;
                    $totalAfectado += $subtotal;

                    // Restar del inventario
                    $inventario->cantidad -= $producto['cantidad'];
                    $inventario->save();

                    // Si se devuelve parcialmente, actualizar la cantidad
                    if ($producto['cantidad'] < $ventaProducto->cantidad) {
                        //$ventaProducto->cantidad -= $producto['cantidad'];
                        $ventaProducto->save();
                    } else {
                        // Si se devuelve todo, eliminar el registro
                        $ventaProducto->delete();
                    }
                }

                // Crear venta de devolución
                $ventaDevolucion = Venta::create([
                    'fecha' => now(),
                    'nombre_cliente' => $venta->nombre_cliente,
                    'costo_total' => $totalAfectado,
                    'id_user' => auth()->id(),
                    'ci' => $venta->ci,
                    'tipo_pago' => $venta->tipo_pago,
                    'id_sucursal' => $venta->id_sucursal,
                    'garantia' => $venta->garantia,
                    'estado' => 'DEVUELTO',
                    'venta_original_id' => $venta->id
                ]);

                // Registrar productos devueltos
                foreach ($request->productos_devolucion as $producto) {
                    $ventaProductoOriginal = VentaProducto::findOrFail($producto['ventaProductoId']);

                    VentaProducto::create([
                        'id_venta' => $ventaDevolucion->id,
                        'id_producto' => $ventaProductoOriginal->id_producto,
                        'cantidad' => $producto['cantidad'],
                        'precio_unitario' => $ventaProductoOriginal->precio_unitario,
                        'descuento' => $ventaProductoOriginal->descuento ?? 0
                    ]);
                }
            }

            // Procesar cancelaciones si existen
            if (!empty($request->productos_cancelados)) {
                foreach ($request->productos_cancelados as $producto) {
                    $ventaProducto = VentaProducto::findOrFail($producto['ventaProductoId']);

                    // Verificar que la cantidad a cancelar no sea mayor a la vendida
                    if ($producto['cantidad'] > $ventaProducto->cantidad) {
                        DB::rollback();
                        return response()->json([
                            'error' => 'La cantidad a cancelar no puede ser mayor a la cantidad vendida.'
                        ], 400);
                    }

                    $subtotal = $producto['cantidad'] * $ventaProducto->precio_unitario;

                    // Devolver stock al inventario
                    $inventario = Inventario::where('id_producto', $ventaProducto->id_producto)
                        ->where('id_sucursal', $venta->id_sucursal)
                        ->first();

                    if (!$inventario) {
                        $inventario = new Inventario();
                        $inventario->id_producto = $ventaProducto->id_producto;
                        $inventario->id_sucursal = $venta->id_sucursal;
                        $inventario->cantidad = $producto['cantidad'];
                    } else {
                        $inventario->cantidad += $producto['cantidad'];
                    }
                    $inventario->save();

                    // Actualizar o eliminar el producto de la venta
                    if ($producto['cantidad'] == $ventaProducto->cantidad) {
                        $ventaProducto->delete();
                    } else {
                        $ventaProducto->cantidad -= $producto['cantidad'];
                        $ventaProducto->save();
                    }
                }

                // Recalcular el total de la venta
                $nuevoTotal = 0;
                foreach ($venta->ventaProductos as $ventaProducto) {
                    $nuevoTotal += ($ventaProducto->cantidad * $ventaProducto->precio_unitario);
                }

                $venta->costo_total = $nuevoTotal;
                if ($nuevoTotal <= 0) {
                    $venta->estado = 'CANCELADO';
                }
                $venta->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cambios ejecutados correctamente'
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => 'Error al ejecutar los cambios: ' . $e->getMessage()
            ], 500);
        }
    }


    public function indexUltimaSemana(Request $request)
    {
        $from = now()->subWeek();
        $query = VentaProducto::with(['venta', 'producto', 'venta.sucursal', 'venta.user'])
            ->whereHas('venta', function ($query) use ($from) {
                $query->where('fecha', '>=', $from);
            });

        // Si hay un parámetro de búsqueda, lo aplicamos
        if ($request->search) {
            $query->whereHas('venta', function ($query) use ($request) {
                $query->where('nombre_cliente', 'like', '%' . $request->search . '%')
                    ->orWhere('ci', 'like', '%' . $request->search . '%')
                    ->orWhere('id', 'like', '%' . $request->search . '%');
            });
        }

        $ventasProductos = $query->orderBy('created_at', 'desc')->paginate(10);

        // Obtener sucursales y usuarios para los filtros
        $sucursales = Sucursale::all();
        $usuarios = User::all();

        if ($request->ajax()) {
            return response()->json($ventasProductos);
        }

        return view('cancelarventa.ultimasemana', compact('ventasProductos', 'sucursales', 'usuarios'));
    }
    //revertir ventas por semana
    public function revertirVentaSemana($id)
    {
        try {
            $ventaProducto = VentaProducto::findOrFail($id);

            // Obtener la venta y la sucursal asociada
            $venta = $ventaProducto->venta;
            $sucursal = $venta->sucursal;

            // Obtener el producto
            $producto = $ventaProducto->producto;

            // Actualizar el stock del producto en la sucursal
            $producto->agregarStockSucursal($ventaProducto->cantidad, $sucursal->id);

            // Eliminar el registro de ventaProducto
            $ventaProducto->delete();

            return redirect()->route('cancelarventa.ultimasemana')->with('success', 'La venta ha sido revertida exitosamente y el stock ha sido actualizado.');
        } catch (Exception $e) {
            return redirect()->route('cancelarventa.ultimasemana')->with('error', 'Error al intentar revertir la venta.');
        }
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Venta::with(['ventaProductos.producto', 'sucursal', 'user'])
                ->orderBy('created_at', 'desc');

            if ($request->filled('fecha_inicio')) {
                $query->where('fecha', '>=', $request->input('fecha_inicio'));
            }

            if ($request->filled('fecha_fin')) {
                $query->where('fecha', '<=', $request->input('fecha_fin'));
            }

            if ($request->filled('sucursal_id')) {
                $query->where('id_sucursal', $request->input('sucursal_id'));
            }

            if ($request->filled('user_id')) {
                $query->where('id_user', $request->input('user_id'));
            }

            return DataTables::of($query)
                ->addColumn('productos', function ($venta) {
                    return $venta->ventaProductos->pluck('producto.nombre')->implode(', ');
                })
                ->addColumn('total_cantidad', function ($venta) {
                    return $venta->ventaProductos->sum('cantidad');
                })
                ->addColumn('total_precio', function ($venta) {
                    return $venta->ventaProductos->sum(function ($vp) {
                        return $vp->precio_unitario * $vp->cantidad;
                    });
                })
                ->addColumn('acciones', function ($venta) {
                    return '
                        <form action="' . route('cancelarventa.reporte', $venta->id) . '" method="POST" style="display:inline;">
                            ' . csrf_field() . '
                            <button type="submit" class="btn btn-info btn-sm" title="Ver Reporte">
                                <i class="fas fa-file-alt"></i> 
                            </button>
                        </form>
                        <form action="' . route('cancelarventa.revertir', $venta->id) . '" method="POST" style="display:inline;">
                            ' . csrf_field() . '
                            <button type="submit" class="btn btn-danger btn-sm" title="Cancelar Venta">
                                <i class="fas fa-undo-alt"></i>
                            </button>
                        </form>
                        <button onclick="devolucionRapida(' . $venta->id . ')" class="btn btn-warning btn-sm" title="Devolución Rápida">
                            <i class="fas fa-exchange-alt"></i> 
                        </button>
                        <a href="' . route('cancelarventa.edit', $venta->id) . '" class="btn btn-primary btn-sm" title="Editar Venta">
                            <i class="fas fa-edit"></i> 
                        </a>';
                })

                ->rawColumns(['acciones'])
                ->make(true);
        }

        // Cargar sucursales y usuarios para los filtros
        $sucursales = Sucursale::all();
        $usuarios = User::all();

        return view('cancelarventa.index', compact('sucursales', 'usuarios'));
    }


    public function revertirVenta($id)
    {
        try {
            $venta = Venta::findOrFail($id);

            // Revertir cada producto vendido aumentando el stock
            foreach ($venta->ventaProductos as $ventaProducto) {
                // Buscar el inventario en la sucursal correspondiente
                $inventario = Inventario::where('id_producto', $ventaProducto->id_producto)
                    ->where('id_sucursal', $venta->id_sucursal)
                    ->first();

                if ($inventario) {
                    // Aumentar el stock existente
                    $inventario->cantidad += $ventaProducto->cantidad;
                    $inventario->save();
                } else {
                    // Crear nuevo registro de inventario si no existe
                    Inventario::create([
                        'id_producto' => $ventaProducto->id_producto,
                        'id_sucursal' => $venta->id_sucursal,
                        'cantidad' => $ventaProducto->cantidad
                    ]);
                }
            }

            // Marcar la venta como cancelada
            $venta->estado = 'CANCELADA';
            $venta->save();

            return redirect()->route('cancelarventa.index')
                ->with('success', 'Venta cancelada y stock aumentado correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('cancelarventa.index')
                ->with('error', 'Error al cancelar la venta.');
        }
    }




    public function generarReporte(Request $request)
    {
        // Obtener los IDs de las ventas seleccionadas
        $ventaIds = $request->input('venta_ids');

        // Verificar si se seleccionaron ventas
        if (!$ventaIds || count($ventaIds) === 0) {
            return redirect()->route('cancelarventa.index')->with('error', 'No se seleccionó ninguna venta.');
        }

        // Obtener las ventas con sus productos y usuario
        $ventas = Venta::with('ventaProductos.producto', 'user')->whereIn('id', $ventaIds)->get();

        // Generar PDF con FPDF
        $pdf = new FPDF('P', 'mm', [80, 200]);

        foreach ($ventas as $venta) {
            $pdf->AddPage(); // Añadir una nueva página para cada venta

            // Datos generales
            $pdf->Image('images/logo.png', 30, 5, 20, 20, 'PNG');
            $pdf->Ln(15);
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(0, 5, 'IMPORTADORA MIRANDA S.A.', 0, 1, 'C');
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(0, 5, 'A un Click del Producto que Necesita!!', 0, 1, 'C');
            $pdf->Cell(0, 5, 'Telefono: 70621016', 0, 1, 'C');
            $pdf->Cell(0, 5, 'Direccion: Caparazon Mall Center, Planta Baja, Local Nro29', 0, 1, 'C');
            $pdf->Cell(0, 5, 'Sucursal: ' . $venta->sucursal->nombre, 0, 1, 'C');
            $pdf->Cell(0, 5, 'Vendedor: ' . $venta->user->name, 0, 1, 'C');
            $pdf->Cell(0, 5, 'Codigo de Venta: IMP' . $venta->id, 0, 1, 'C');

            // Línea separadora
            $pdf->Ln(2);
            $pdf->Cell(0, 0, '', 'T');
            $pdf->Ln(2);

            // Nota de Venta
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(0, 5, 'NOTA DE VENTA', 0, 1, 'C');
            $pdf->Ln(2);
            $pdf->Cell(0, 0, '', 'T');
            $pdf->Ln(2);

            // Información del cliente y la venta
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(0, 5, utf8_decode('Cliente: ' . $venta->nombre_cliente), 0, 1, 'L');
            $pdf->Cell(0, 5, 'CI / NIT: ' . $venta->ci, 0, 1, 'L');
            $pdf->Cell(0, 5, 'Fecha: ' . $venta->fecha, 0, 1, 'L');
            $pdf->Cell(0, 5, 'Forma de Pago: ' . ($venta->tipo_pago), 0, 1, 'L');

            // Línea separadora
            $pdf->Ln(5);

            // Encabezado de la tabla de productos
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(10, 6, 'Cant.', 1, 0, 'C');
            $pdf->Cell(25, 6, 'Descripcion', 1, 0, 'C');
            $pdf->Cell(15, 6, 'P. Unit.', 1, 0, 'C');
            $pdf->Cell(15, 6, 'Subtotal', 1, 1, 'C');

            // Detalle de productos
            $subtotal = 0;
            foreach ($venta->ventaProductos as $ventaProducto) {
                $cantidad = $ventaProducto->cantidad;
                $descripcion = $ventaProducto->producto->nombre;
                $precioUnitario = $ventaProducto->precio_unitario;
                $subtotalProducto = $cantidad * $precioUnitario;
                $subtotal += $subtotalProducto;

                $pdf->SetFont('Arial', '', 6);
                $pdf->Cell(10, 6, $cantidad, 1, 0, 'C');
                $pdf->Cell(25, 6, utf8_decode($descripcion), 1, 0, 'L');
                $pdf->Cell(15, 6, number_format($precioUnitario, 2), 1, 0, 'R');
                $pdf->Cell(15, 6, number_format($subtotalProducto, 2), 1, 1, 'R');
            }

            // Calcular totales
            $descuento = $venta->descuento ?? 0;
            $total = $subtotal - $descuento;

            // Mostrar los totales
            $pdf->Ln(3);
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(0, 5, 'SUBTOTAL: ' . number_format($subtotal, 2), 0, 1, 'R');
            $pdf->Cell(0, 5, 'DESCUENTO: ' . number_format($descuento, 2), 0, 1, 'R');
            $pdf->Cell(0, 5, 'TOTAL: ' . number_format($total, 2), 0, 1, 'R');

            // Línea separadora
            $pdf->Ln(5);
            $pdf->Cell(0, 0, '', 'T');
            $pdf->Ln(5);

            // Mensaje de advertencia
            $pdf->SetFont('Arial', '', 6);
            $pdf->Cell(0, 4, utf8_decode("La empresa no se hace responsable de daños ocasionados "), 0, 1, 'C');
            $pdf->Cell(0, 4, utf8_decode("por un mal uso de los productos adquiridos."), 0, 1, 'C');
            $pdf->Cell(0, 4, utf8_decode("Por favor, revise sus productos antes de salir."), 0, 1, 'C');
            $pdf->Cell(0, 4, utf8_decode("No se permiten cambios ni devoluciones después de la compra."), 0, 1, 'C');
            $pdf->Cell(0, 4, utf8_decode("Agradecemos su confianza. Si tiene alguna inquietud, estamos aquí para ayudarle."), 0, 1, 'C');
            $pdf->Cell(0, 4, utf8_decode("GRACIAS POR SU COMPRA!!!"), 0, 1, 'C');
        }

        // Salida del PDF
        $pdf->Output('I', 'Reporte_Venta_' . $venta->id . '.pdf');
        exit;
    }


     public function generarReporteIndividual($id)
    {
        // Buscar la venta con relaciones
        $venta = Venta::with('ventaProductos.producto', 'user')->findOrFail($id);
        $pagado = $venta->pagado;

        // Crear el PDF (tamaño ticket)
        $pdf = new FPDF('P', 'mm', [80, 120]);
        $pdf->AddPage();
        $marginTop = 10;
        $pdf->SetY($marginTop - 2);

        // === CABECERA ===
        $pdf->Image('images/logo.png', 30, 2, 18, 18, 'PNG');
        $pdf->Ln(15);

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(0, 4, utf8_decode("IMPORTADORA MIRANDA S.A."), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 3, utf8_decode("A un Click del Producto que Necesita!!"), 0, 1, 'C');
        $pdf->Cell(0, 3, utf8_decode("Fecha: " . date('Y/m/d H:i:s')), 0, 1, 'C');

        // Línea separadora
        $pdf->Ln(1);
        $pdf->Cell(0, 0, '', 'T');
        $pdf->Ln(1);

        // Forma de pago
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 3, utf8_decode("Forma de Pago: " . $venta->tipo_pago), 0, 1, 'C');

        // Título del documento
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(0, 4, utf8_decode("NOTA DE VENTA"), 0, 1, 'C');

        // Línea separadora
        $pdf->Ln(1);
        $pdf->Cell(0, 0, '', 'T');
        $pdf->Ln(1);

        // === INFORMACIÓN DE CLIENTE Y VENDEDOR ===
        $pdf->SetFont('Arial', '', 7);
        $halfWidth = 35;

        $pdf->Cell($halfWidth, 4, utf8_decode("Cliente: " . $venta->nombre_cliente), 0, 0, 'L');
        $pdf->Cell($halfWidth, 4, utf8_decode("CI / NIT: " . $venta->ci), 0, 1, 'L');

        $pdf->Cell($halfWidth, 4, utf8_decode("Fecha: " . $venta->fecha), 0, 0, 'L');
        $pdf->Cell($halfWidth, 4, utf8_decode("Vendedor: " . $venta->user->name), 0, 1, 'L');

        // Línea separadora
        $pdf->Ln(1);
        $pdf->Cell(0, 0, '', 'T');
        $pdf->Ln(1);

        // === DETALLE DE PRODUCTOS ===
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(10, 5, utf8_decode("Cant."), 1, 0, 'C');
        $pdf->Cell(30, 5, utf8_decode("Desc."), 1, 0, 'C');
        $pdf->Cell(10, 5, utf8_decode("P.Unit"), 1, 0, 'C');
        $pdf->Cell(15, 5, utf8_decode("Subtotal"), 1, 1, 'C');

        $pdf->SetFont('Arial', '', 6);
        $subtotal = 0;

        foreach ($venta->ventaProductos as $vp) {
            $cantidad = $vp->cantidad;
            $nombre = utf8_decode($vp->producto->nombre ?? 'Sin descripción');
            $precio = $vp->precio_unitario;
            $subtotalProducto = $cantidad * $precio;
            $subtotal += $subtotalProducto;

            // Ajuste de tamaño de texto si el nombre es muy largo
            $maxCaracteres = 20;
            if (strlen($nombre) > $maxCaracteres) {
                $pdf->SetFont('Arial', '', 5);
            } else {
                $pdf->SetFont('Arial', '', 6);
            }

            $pdf->Cell(10, 4, $cantidad, 1, 0, 'C');
            $pdf->Cell(30, 4, $nombre, 1, 0, 'L');
            $pdf->SetFont('Arial', '', 6);
            $pdf->Cell(10, 4, number_format($precio, 2), 1, 0, 'R');
            $pdf->Cell(15, 4, number_format($subtotalProducto, 2), 1, 1, 'R');
        }

        // Línea separadora
        $pdf->Ln(1);
        $pdf->Cell(0, 0, '', 'T');
        $pdf->Ln(1);

        // === TOTALES ===
        $pdf->SetFont('Arial', 'B', 7);
        $descuento = $venta->descuento ?? 0;
        $precio_original = $subtotal + $descuento;
        $cambio = $pagado - $subtotal;

        $pdf->Cell(0, 3, utf8_decode("PRECIO ORIGINAL: " . number_format($precio_original, 2)), 0, 1, 'R');
        $pdf->Cell(0, 3, utf8_decode("DESCUENTO: " . number_format($descuento, 2)), 0, 1, 'R');
        $pdf->Cell(0, 3, utf8_decode("TOTAL: " . number_format($subtotal, 2)), 0, 1, 'R');
        $pdf->Cell(0, 3, utf8_decode("PAGADO: " . number_format($pagado, 2)), 0, 1, 'R');
        $pdf->Cell(0, 3, utf8_decode("CAMBIO: " . number_format($cambio, 2)), 0, 1, 'R');

        // Línea separadora
        $pdf->Ln(1);
        $pdf->Cell(0, 0, '', 'T');
        $pdf->Ln(1);

        // === MENSAJE FINAL ===
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(0, 4, utf8_decode("¡GRACIAS POR SU COMPRA!"), 0, 1, 'C');
        $pdf->Ln(1);

        // Salida del PDF
        $pdf->Output('I', 'Nota_Venta_' . $venta->id . '.pdf');
        exit;
    }

    public function generarReporteIndividualSemana($id)
    {
        // Buscar el VentaProducto por su ID
        $ventaProducto = VentaProducto::with('venta', 'producto')->findOrFail($id);

        // Generar PDF con FPDF
        $pdf = new FPDF('P', 'mm', [80, 200]);
        $pdf->AddPage();

        // Datos del producto y venta
        $venta = $ventaProducto->venta;
        $producto = $ventaProducto->producto;

        // Logo
        $pdf->Image('images/logo.png', 30, 5, 20, 20, 'PNG');
        $pdf->Ln(15);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 5, 'IMPORTADORA MIRANDA S.A.', 0, 1, 'C');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 5, 'A un Click del Producto que Necesita!!', 0, 1, 'C');
        $pdf->Cell(0, 5, 'Telefono: 70621016', 0, 1, 'C');
        $pdf->Cell(0, 5, 'Direccion: Caparazon Mall Center, Planta Baja, Local Nro29', 0, 1, 'C');
        $pdf->Cell(0, 5, 'Sucursal: Sucursal 1 - CAPARAZON MALL CENTER', 0, 1, 'C');
        $pdf->Cell(0, 5, 'Fecha: ' . now()->format('Y/m/d'), 0, 1, 'C');
        $pdf->Cell(0, 5, 'Codigo de Venta: IMP' . now()->format('Ymd/H:i'), 0, 1, 'C');
        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea horizontal
        $pdf->Ln(5);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 5, 'NOTA DE VENTA', 0, 1, 'C');

        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea horizontal
        $pdf->Ln(5);

        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 5, 'Cliente: ' . $venta->nombre_cliente, 0, 1, 'L');
        $pdf->Cell(0, 5, 'CI / NIT: ' . $venta->ci, 0, 1, 'L');

        $pdf->Cell(0, 5, 'Fecha: ' . $venta->fecha, 0, 1, 'L');
        $pdf->Cell(0, 5, 'Forma de Pago: ' . ($venta->tipo_pago), 0, 1, 'L');

        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(8, 6, 'Cant.', 1, 0, 'C');
        $pdf->Cell(30, 6, 'Descripcion', 1, 0, 'C');
        $pdf->Cell(13, 6, 'P. Unit.', 1, 0, 'C');
        $pdf->Cell(15, 6, 'Subtotal', 1, 1, 'C');
        $pdf->SetFont('Arial', '', 6);

        // Detalle del producto
        $subtotal = $ventaProducto->cantidad * $ventaProducto->precio_unitario;
        $pdf->Cell(8, 6, $ventaProducto->cantidad, 1, 0, 'C');
        $pdf->Cell(30, 6, $producto->nombre, 1, 0, 'L');
        $pdf->Cell(13, 6, number_format($ventaProducto->precio_unitario, 2), 1, 0, 'R');
        $pdf->Cell(15, 6, number_format($subtotal, 2), 1, 1, 'R');

        // Totales
        $descuento = $venta->descuento ?? 0;
        $total = $subtotal - $descuento;

        $pdf->Ln(3);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(0, 5, 'SUBTOTAL: ' . number_format($subtotal, 2), 0, 1, 'R');
        $pdf->Cell(0, 5, 'DESCUENTO: ' . number_format($descuento, 2), 0, 1, 'R');
        $pdf->Cell(0, 5, 'TOTAL: ' . number_format($total, 2), 0, 1, 'R');
        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea horizontal
        $pdf->Ln(5);

        // Subtotal y total
        $pdf->SetFont('Arial', '', 6);
        $pdf->Cell(0, 4, utf8_decode("La empresa no se hace responsable de daños ocasionados "), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("por un mal uso de los productos adquiridos."), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("Por favor, revise sus productos antes de salir."), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("No se permiten cambios ni devoluciones después de la compra."), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("Agradecemos su confianza. Si tiene alguna inquietud, estamos aquí para ayudarle."), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("Guarde este documento como comprobante para cualquier gestión futura."), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("Agradecemos su confianza."), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("GRACIAS POR SU COMPRA!!!"), 0, 1, 'C');

        // Salida del PDF
        $pdf->Output('I', 'Reporte_Venta_' . $ventaProducto->id . '.pdf');
        exit;
    }
    public function estadisticasHome(Request $request)
{
    $currentMonth = $request->mes ?? now()->month;
    $currentYear = $request->anio ?? now()->year;
    $today = now()->toDateString();

    // ================================
    // Ventas por usuario en el mes
    // ================================
    $ventasPorUsuario = Venta::select(
        'ventas.id_user',
        DB::raw('COUNT(*) as total_ventas')
    )
        ->whereMonth('fecha', $currentMonth)
        ->whereYear('fecha', $currentYear)
        ->groupBy('ventas.id_user')
        ->with('user')
        ->get();

    $labels = $ventasPorUsuario->pluck('user.name');
    $data = $ventasPorUsuario->pluck('total_ventas');

    // ================================
    // Ventas por sucursal en el mes
    // ================================
    $ventasPorSucursal = DB::table('ventas')
        ->join('sucursales', 'ventas.id_sucursal', '=', 'sucursales.id')
        ->select('sucursales.id', 'sucursales.nombre', DB::raw('COUNT(*) as total_ventas'))
        ->whereMonth('ventas.fecha', $currentMonth)
        ->whereYear('ventas.fecha', $currentYear)
        ->groupBy('sucursales.id', 'sucursales.nombre')
        ->get();

    $labelsSucursal = $ventasPorSucursal->pluck('nombre');
    $dataSucursal = $ventasPorSucursal->pluck('total_ventas');

    // ================================
    // Vendedor con más ventas hoy
    // ================================
    $vendedorTopHoy = Venta::select('id_user', DB::raw('COUNT(*) as total_ventas'))
        ->whereDate('fecha', $today)
        ->groupBy('id_user')
        ->orderByDesc('total_ventas')
        ->with('user')
        ->first();

    // ================================
    // Total ventas del mes
    // ================================
    $ventasMes = Venta::whereMonth('fecha', $currentMonth)
        ->whereYear('fecha', $currentYear)
        ->count();

    // ================================
    // Total ventas del año
    // ================================
    $ventasAnio = Venta::whereYear('fecha', $currentYear)->count();

    // ================================
    // TOP 5 productos más vendidos
    // ================================
    $topProductos = VentaProducto::select(
        'id_producto',
        DB::raw('SUM(cantidad) as total_vendidos')
    )
        ->whereHas('venta', function ($query) use ($currentMonth, $currentYear) {
            $query->whereMonth('fecha', $currentMonth)
                  ->whereYear('fecha', $currentYear);
        })
        ->groupBy('id_producto')
        ->orderByDesc('total_vendidos')
        ->with('producto')
        ->limit(5)
        ->get();

    return view('home', compact(
        'ventasPorUsuario',
        'labels',
        'data',
        'ventasPorSucursal',
        'labelsSucursal',
        'dataSucursal',
        'vendedorTopHoy',
        'ventasMes',
        'ventasAnio',
        'topProductos',
        'currentMonth',
        'currentYear'
    ));
}

}
