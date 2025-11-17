<?php

namespace App\Http\Controllers;

use App\Models\Envio;
use App\Models\Pedido;
use App\Models\PedidoProducto;
use App\Models\Producto;
use App\Models\Semana;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Yajra\DataTables\Facades\DataTables;

class EnvioController extends Controller
{
    public function faltanteView()
    {
        $semanas = Semana::latest('created_at')->take(5)->get();
        $idSucursal = 1;

        $productos = Producto::with(['inventarios' => function ($query) use ($idSucursal) {
            $query->where('id_sucursal', $idSucursal);
        }])->get()->map(function ($producto) use ($idSucursal) {
            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'cantidad_sucursal' => optional($producto->inventarios->first())->cantidad ?? 0,
            ];
        });

        // Filtros aplicados a envíos
        $enviosFiltrados = Envio::with('pedido') // importante: eager load del pedido
            ->where('extra2', 1) // ✅ solo los que tienen extra2 confirmado
            ->where('enviado', '!=', 1)
            ->where('lapaz', '!=', 1)
            ->where('extra1', '!=', 1);

        // Identificar pedidos duplicados
        $duplicados = (clone $enviosFiltrados)
            ->select('id_pedido')
            ->whereNotNull('id_pedido')
            ->groupBy('id_pedido')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('id_pedido');

        $pedidosDuplicados = (clone $enviosFiltrados)
            ->whereIn('id_pedido', $duplicados)
            ->orderBy('id_pedido')
            ->get()
            ->groupBy('id_pedido');

        // Obtener pedidos confirmados dentro de los envíos filtrados
        $pedidosConfirmados = $enviosFiltrados
            ->get()
            ->filter(function ($envio) {
                return $envio->pedido && $envio->pedido->estado_pedido === 'confirmado';
            })
            ->groupBy('id_pedido');

        return view('envioscuaderno.faltante', compact(
            'semanas',
            'productos',
            'pedidosDuplicados',
            'pedidosConfirmados'
        ));
    }

    public function faltanteData(Request $request)
    {
        $query = Envio::with('pedido')
            // Solo P. PENDIENTE:
            ->where('extra1', 1)
            // Y ninguno de los otros:
            ->where('lapaz',   0)
            ->where('enviado', 0)
            ->where('extra',    0)   // si ese es el campo de “P. LISTO”
            ->where('extra2',  0)   // si tienes más flags (EXTRA 3, EXTRA 4, …)
            ->where('extra3',  0)
            ->latest();

        if ($request->ajax() && $request->filled('celular')) {
            $search = $request->celular;
            $query->where(function ($q) use ($search) {
                $q->where('celular', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%")
                    ->orWhere('id_pedido', 'like', "%{$search}%");
            });
        }
        return DataTables::of($query)
            ->addColumn('productos', function ($row) {
                if ($row->pedido) {
                    return $row->pedido->productos;
                } else {
                    $pedidoProductos = PedidoProducto::where('id_envio', $row->id)->get();
                    $productos = $pedidoProductos->map(fn($p) => $p->producto->nombre)->toArray();
                    return implode(', ', $productos);
                }
            })
            ->addColumn('cantidad_productos', function ($row) {
                if ($row->pedido) {
                    return $row->pedido->cantidad_productos;
                } else {
                    return PedidoProducto::where('id_envio', $row->id)->sum('cantidad');
                }
            })
            ->addColumn('monto_deposito', function ($row) {
                if ($row->pedido) {
                    return number_format($row->pedido->monto_deposito, 2);
                } else {
                    $pedidoProductos = PedidoProducto::where('id_envio', $row->id)->get();
                    $totalMonto = $pedidoProductos->sum(fn($p) => $p->cantidad * $p->precio);
                    return number_format($totalMonto, 2);
                }
            })
            ->addColumn('action', function ($row) {
                return '<button class="btn btn-sm btn-danger delete-envio" data-id="' . $row->id . '">Eliminar</button>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function markConfirmedAsSent(Request $request)
    {
        try {
            // Filtrar envíos que cumplan TODAS las condiciones
            $envios = Envio::with('pedido')
                ->where('extra', 1)
                ->where('enviado', '!=', 1)
                ->where('lapaz', '!=', 1)
                ->where('extra1', '!=', 1)
                ->whereHas('pedido', function ($query) {
                    $query->where('estado_pedido', 'confirmado');
                });

            // Contar cuántos se van a actualizar
            $count = $envios->count();

            // Actualizar a 'enviado = 1'
            $envios->update(['enviado' => 1]);

            // Opcional: log
            Log::info("Se marcaron como enviados {$count} pedidos confirmados con filtros adicionales.");

            return response()->json([
                'success' => true,
                'message' => "Se marcaron como enviados {$count} pedidos.",
                'count' => $count
            ]);
        } catch (\Exception $e) {
            Log::error('Error al marcar pedidos como enviados: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la solicitud.'
            ]);
        }
    }
    public function index()
    {
        $semanas = Semana::latest('created_at')->take(5)->get();

        $idSucursal = 1;

        $productos = Producto::with(['inventarios' => function ($query) use ($idSucursal) {
            $query->where('id_sucursal', $idSucursal);
        }])->get()->map(function ($producto) use ($idSucursal) {
            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'cantidad_sucursal' => optional($producto->inventarios->first())->cantidad ?? 0,
            ];
        });

        return view('envioscuaderno.index', compact('semanas', 'productos'));
    }
    public function getPedidoData(Request $request)
    {
        // Procesa los datos recibidos
        $pedidoData = $request->all();

        // Llama a storeProduct pasando los datos procesados
        $this->storeProduct(new Request($pedidoData), $request->id_pedido, $request->id_envio);
        // Verificar si se recibe id_pedido
        if ($request->id_pedido) {
            // Si el id_pedido está presente, buscar el pedido directamente
            $pedido = Pedido::find($request->id_pedido);

            if ($pedido) {
                return response()->json([
                    'productos' => $pedido->productos,
                    'cantidad_productos' => $pedido->cantidad_productos,
                    'monto_deposito' => number_format($pedido->monto_deposito, 2),
                    'detalle' => $pedido->detalle // Aquí estamos devolviendo el detalle directamente del modelo
                ]);
            } else {
                return response()->json([], 404);
            }
        } else if ($request->id_envio) {
            // Si no se recibe un id_pedido, buscar los productos relacionados con el id_envio
            $pedidoProductos = PedidoProducto::where('id_envio', $request->id_envio)->get();

            $productos = [];
            $cantidad_productos = 0;
            $monto_deposito = 0;
            $detalle = ''; // Inicializamos la variable para el detalle

            // Concatenamos los productos, cantidades y precios para crear el detalle
            foreach ($pedidoProductos as $pedidoProducto) {
                $productos[] = $pedidoProducto->producto->nombre; // Nombre del producto
                $cantidad_productos += $pedidoProducto->cantidad; // Sumar cantidad de productos
                $monto_deposito += $pedidoProducto->cantidad * $pedidoProducto->precio; // Sumar monto total
                // Concatenamos los detalles de los productos
                $detalle .= $pedidoProducto->producto->nombre . ' (' . $pedidoProducto->cantidad . ' x ' . number_format($pedidoProducto->precio, 2) . '), ';
            }

            // Retornamos la respuesta con los detalles concatenados
            return response()->json([
                'productos' => implode(', ', $productos), // Productos concatenados por coma
                'cantidad_productos' => $cantidad_productos,
                'monto_deposito' => number_format($monto_deposito, 2),
                'detalle' => rtrim($detalle, ', ') // Eliminamos la última coma y espacio del detalle
            ]);
        }

        return response()->json([], 404);
    }
    public function storeProduct(Request $request, $id_pedido, $id_envio)
    {
        // Verificar que se recibieron tanto id_pedido como id_envio
        if ($id_pedido && $id_envio) {

            // Obtener los productos que están en el id_envio pero no tienen id_pedido
            $productosSinIdPedido = PedidoProducto::where('id_envio', $id_envio)
                ->whereNull('id_pedido')  // Solo los productos que no tienen id_pedido
                ->get();

            $detalleEnvio = ''; // Variable para concatenar detalles del envío
            $detallePedido = ''; // Variable para concatenar detalles del pedido
            $detallePedidonombre = '';
            // Si hay productos sin id_pedido, actualizamos su id_pedido
            foreach ($productosSinIdPedido as $producto) {
                // Concatenar el detalle de los productos del envío en formato: 'Nombre (cantidad x precio)'
                $productoNombre = Producto::find($producto->id_producto)->nombre ?? 'Producto no encontrado';
                // Actualizamos su id_pedido
                $producto->id_pedido = $id_pedido;  // Asociar el id_pedido al producto
                $producto->save();  // Guardar el cambio
            }

            // Obtener los productos existentes del pedido
            $productosExistentes = PedidoProducto::where('id_pedido', $id_pedido)->get();

            // Concatenar el detalle del pedido
            foreach ($productosExistentes as $producto) {
                $productoNombre = Producto::find($producto->id_producto)->nombre ?? 'Producto no encontrado';
                $detallePedido .= $productoNombre . ' (' . $producto->cantidad . ' x ' . number_format($producto->precio, 2) . '), ';
                $detallePedidonombre .= $productoNombre;
            }

            // Calcular la nueva cantidad total y el monto total del pedido
            $totalCantidad = $productosExistentes->sum('cantidad');
            $totalMonto = $productosExistentes->sum(function ($item) {
                return $item->cantidad * $item->precio;
            });

            // Actualizar los campos cantidad_productos y monto_deposito del pedido
            $pedido = Pedido::findOrFail($id_pedido);
            $pedido->cantidad_productos = $totalCantidad;
            $pedido->monto_deposito = $totalMonto;

            // Concatenar los detalles (productos asociados al pedido y los del envío)
            $pedido->productos = rtrim($detallePedidonombre); // Concatenamos y eliminamos la última coma
            $pedido->detalle = rtrim($detallePedido . $detalleEnvio, ', '); // Concatenamos y eliminamos la última coma

            // Guardar el pedido actualizado
            $pedido->save();

            // Actualizar el detalle en la tabla de envíos
            $envio = Envio::findOrFail($id_envio);
            $envio->detalle = rtrim($detallePedido . $detalleEnvio, ', '); // Actualizamos el detalle en el envío
            $envio->save();

            return response()->json([
                'success' => true,
                'message' => 'Productos del envío asociados al pedido correctamente.',
                'totalCantidad' => $totalCantidad,
                'totalMonto' => $totalMonto
            ]);
        } else if ($id_envio) {
            // Obtener los productos que están en el id_envio pero no tienen id_pedido
            $productosSinIdPedido = PedidoProducto::where('id_envio', $id_envio)
                ->get();

            $detalleEnvio = ''; // Variable para concatenar detalles del envío
            $detallePedido = ''; // Variable para concatenar detalles del pedido
            $detallePedidonombre = '';
            // Si hay productos sin id_pedido, actualizamos su id_pedido
            foreach ($productosSinIdPedido as $producto) {
                // Concatenar el detalle de los productos del envío en formato: 'Nombre (cantidad x precio)'
                $productoNombre = Producto::find($producto->id_producto)->nombre ?? 'Producto no encontrado';
                $producto->save();  // Guardar el cambio
            }

            // Concatenar el detalle del pedido
            foreach ($productosSinIdPedido as $producto) {
                $productoNombre = Producto::find($producto->id_producto)->nombre ?? 'Producto no encontrado';
                $detallePedido .= $productoNombre . ' (' . $producto->cantidad . ' x ' . number_format($producto->precio, 2) . '), ';
                $detallePedidonombre .= $productoNombre;
            }

            // Calcular la nueva cantidad total y el monto total del pedido
            $totalCantidad = $productosSinIdPedido->sum('cantidad');
            $totalMonto = $productosSinIdPedido->sum(function ($item) {
                return $item->cantidad * $item->precio;
            });


            // Actualizar el detalle en la tabla de envíos
            $envio = Envio::findOrFail($id_envio);
            $envio->detalle = rtrim($detallePedido . $detalleEnvio, ', '); // Actualizamos el detalle en el envío
            $envio->save();
            return response()->json([
                'success' => false,
                'message' => 'No se puede asociar el envío sin un ID de pedido.',
            ]);
        }

        // Si no se reciben id_pedido e id_envio correctamente
        return response()->json([
            'success' => false,
            'message' => 'Se deben proporcionar tanto un ID de pedido como un ID de envío.',
        ]);
    }

    public function getEnvioProductos($envioId)
    {

        $productos = PedidoProducto::where('id_envio', $envioId)
            ->with('producto') // Asegúrate de cargar la relación con el modelo Producto
            ->get();

        if ($productos->isNotEmpty()) {
            return response()->json([
                'productos' => $productos->map(function ($pedidoProducto) {
                    return [
                        'id' => $pedidoProducto->id,
                        'nombre' => $pedidoProducto->producto->nombre,
                        'cantidad' => $pedidoProducto->cantidad,
                        'precio' => $pedidoProducto->precio,
                    ];
                }),
            ]);
        }

        return response()->json(['productos' => []], 200);
    }
    public function guardarProductos(Request $request)
    {

        $productos = $request->input('productos');
        $eliminados = $request->input('eliminados');

        // Actualizar los productos editados
        foreach ($productos as $producto) {
            $pedidoProducto = PedidoProducto::find($producto['id']);
            if ($pedidoProducto) {
                $pedidoProducto->cantidad = $producto['cantidad'];
                $pedidoProducto->precio = $producto['precio'];
                $pedidoProducto->save();
            }
        }

        // Eliminar los productos marcados como eliminados
        if (!empty($eliminados)) {
            PedidoProducto::whereIn('id', $eliminados)->delete();
        }
        // Procesa los datos recibidos
        $pedidoData = $request->all();
        $this->storeProduct(new Request($pedidoData), $request->id_pedido, $request->id_envio);
        return response()->json(['success' => true]);
    }


    public function envioproductos(Request $request, $id_pedido, $id_envio)
    {
        // Obtener los valores de pedido y envio del formulario
        $id_pedido = $request->input('pedidoId'); // id_pedido puede ser null
        $id_envio = $request->input('envioId'); // id_envio no debería ser null

        // Validación de los campos del formulario
        $request->validate([
            'id_producto' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
            'precio' => 'required|numeric',
        ]);

        // Obtener el ID del usuario autenticado
        $usuarioId = auth()->user()->id;

        // Si existe el id_pedido
        if ($id_pedido) {
            // Obtener el pedido
            $pedido = Pedido::findOrFail($id_pedido);

            if (!$pedido) {
                return back()->withErrors(['pedido' => 'Pedido no encontrado.']);
            }

            // Crear el nuevo registro en PedidoProducto
            PedidoProducto::create([
                'id_pedido' => $pedido->id, // Guardar id_pedido
                'id_envio' => $id_envio ?? null, // Guardar id_envio si existe, si no, lo guarda como null
                'id_producto' => $request->input('id_producto'),
                'cantidad' => $request->input('cantidad'),
                'precio' => $request->input('precio'),
                'fecha' => now(),
                'id_usuario' => $usuarioId,
            ]);

            // Obtener los productos relacionados con id_envio y cuyo id_pedido sea null
            $productosConNullIdPedido = PedidoProducto::where('id_envio', $id_envio)
                ->whereNull('id_pedido')  // Buscar productos cuyo id_pedido es null
                ->get();

            // Actualizar los productos con id_pedido = null a ese id_pedido
            foreach ($productosConNullIdPedido as $producto) {
                $producto->id_pedido = $id_pedido; // Asociar el producto con el nuevo id_pedido
                $producto->save(); // Guardar el cambio
            }

            // Obtener los productos existentes del pedido
            $productosExistentes = PedidoProducto::where('id_pedido', $pedido->id)->get();

            // Calcular la nueva cantidad total y el monto total
            $totalCantidad = $productosExistentes->sum('cantidad');
            $totalMonto = $productosExistentes->sum(function ($item) {
                return $item->cantidad * $item->precio;
            });

            // Actualizar los campos cantidad_productos y monto_deposito del pedido
            $pedido->cantidad_productos = $totalCantidad;
            $pedido->monto_deposito = $totalMonto;
            $pedido->save();

            // Obtener los nombres de los productos para el campo productos
            $productosString = $productosExistentes->map(function ($pedidoProducto) {
                return Producto::find($pedidoProducto->id_producto)->nombre ?? 'Producto no encontrado';
            })->implode(', ');

            // Actualizar el campo productos del pedido
            $pedido->productos = $productosString;
            $pedido->save();

            return response()->json([
                'success' => true,
                'totalCantidad' => $totalCantidad,
                'totalMonto' => $totalMonto
            ]);
        } elseif ($id_envio) {
            // Si no existe id_pedido pero existe id_envio, guardar id_pedido como null y id_envio
            PedidoProducto::create([
                'id_pedido' => null, // id_pedido como null
                'id_envio' => $id_envio, // Guardar id_envio normalmente
                'id_producto' => $request->input('id_producto'),
                'cantidad' => $request->input('cantidad'),
                'precio' => $request->input('precio'),
                'fecha' => now(),
                'id_usuario' => $usuarioId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Producto agregado solo al envío.',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No se proporcionó un ID de pedido ni un ID de envío.',
            ]);
        }
    }


    public function dataTable(Request $request)
    {
        $data = Envio::with('pedido') // Asegúrate de cargar la relación pedido
            ->latest();

        if ($request->ajax()) {
            // Filtrar por los campos booleanos
            if ($request->has('lapaz') && $request->lapaz !== null) {
                $data->where('lapaz', $request->lapaz);
            }
            if ($request->has('enviado') && $request->enviado !== null) {
                $data->where('enviado', $request->enviado);
            }
            if ($request->has('extra') && $request->extra !== null) {
                $data->where('extra', $request->extra);
            }
            if ($request->has('extra1') && $request->extra1 !== null) {
                $data->where('extra1', $request->extra1);
            }


            // Filtrar por fecha de creación
            if ($request->has('start_date') && !empty($request->start_date)) {
                $data->where('fecha_hora_creada', '>=', $request->start_date);
            }

            if ($request->has('end_date') && !empty($request->end_date)) {
                $data->where('fecha_hora_creada', '<=', $request->end_date);
            }
            if ($request->has('celular') && !empty($request->celular)) {
                $search = $request->celular;

                $data->where(function ($query) use ($search) {
                    $query->where('celular', 'like', '%' . $search . '%')
                        ->orWhere('id', 'like', '%' . $search . '%')
                        ->orWhere('id_pedido', 'like', '%' . $search . '%');
                });
            }


            return DataTables::of($data)
                ->addColumn('productos', function ($row) {
                    if ($row->pedido) {
                        return $row->pedido->productos;
                    } else {
                        // Si no existe un id_pedido, obtener los productos desde el id_envio
                        $pedidoProductos = PedidoProducto::where('id_envio', $row->id)->get();

                        $productos = [];
                        foreach ($pedidoProductos as $pedidoProducto) {
                            $productos[] = $pedidoProducto->producto->nombre; // Nombre de los productos
                        }

                        return implode(', ', $productos); // Concatena y muestra los productos
                    }
                })
                ->addColumn('cantidad_productos', function ($row) {
                    if ($row->pedido) {
                        return $row->pedido->cantidad_productos;
                    } else {
                        // Si no existe un id_pedido, sumar las cantidades de los productos relacionados
                        $pedidoProductos = PedidoProducto::where('id_envio', $row->id)->get();
                        return $pedidoProductos->sum('cantidad'); // Sumar las cantidades
                    }
                })
                ->addColumn('monto_deposito', function ($row) {
                    if ($row->pedido) {
                        return number_format($row->pedido->monto_deposito, 2);
                    } else {
                        // Si no existe un id_pedido, sumar el monto total de los productos relacionados
                        $pedidoProductos = PedidoProducto::where('id_envio', $row->id)->get();
                        $totalMonto = 0;
                        foreach ($pedidoProductos as $pedidoProducto) {
                            $totalMonto += $pedidoProducto->cantidad * $pedidoProducto->precio;
                        }
                        return number_format($totalMonto, 2); // Mostrar el total formateado
                    }
                })
                ->addColumn('action', function ($row) {
                    return '<button class="btn btn-sm btn-danger delete-envio" data-id="' . $row->id . '">Eliminar</button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function indexSinLaPaz()
    {
        // Método 2: Mostrar todos los envíos donde 'lapaz' no esté seleacionado (es falso)
        $envios = Envio::latest()->where('lapaz', true)->paginate(10);
        $semanas = Semana::latest('created_at')->take(5)->get();
        $idSucursal = 1;
        $productos = Producto::with(['inventarios' => function ($query) use ($idSucursal) {
            $query->where('id_sucursal', $idSucursal);
        }])->get()->map(function ($producto) use ($idSucursal) {
            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'cantidad_sucursal' => optional($producto->inventarios->first())->cantidad ?? 0,
            ];
        }); // Obtiene tanto el id como el nombre

        return view('envioscuaderno.indexlapaz', compact('envios', 'semanas', 'productos'));
    }

    public function dataTableSinLaPaz(Request $request)
    {
        if ($request->ajax()) {
            // Filtra los envíos donde 'lapaz' está marcado como false
            $data = Envio::with('pedido') // Asegúrate de cargar la relación pedido
                ->latest()->where('lapaz', false);

            if ($request->ajax()) {
                // Filtrar por los campos booleanos
                if ($request->has('lapaz') && $request->lapaz !== null) {
                    $data->where('lapaz', $request->lapaz);
                }
                if ($request->has('enviado') && $request->enviado !== null) {
                    $data->where('enviado', $request->enviado);
                }
                if ($request->has('extra') && $request->extra !== null) {
                    $data->where('extra', $request->extra);
                }
                if ($request->has('extra1') && $request->extra1 !== null) {
                    $data->where('extra1', $request->extra1);
                }

                // Filtrar por fecha de creación
                if ($request->has('start_date') && !empty($request->start_date)) {
                    $data->where('fecha_hora_creada', '>=', $request->start_date);
                }

                if ($request->has('end_date') && !empty($request->end_date)) {
                    $data->where('fecha_hora_creada', '<=', $request->end_date);
                }
                if ($request->has('celular') && !empty($request->celular)) {
                    $search = $request->celular;

                    $data->where(function ($query) use ($search) {
                        $query->where('celular', 'like', '%' . $search . '%')
                            ->orWhere('id', 'like', '%' . $search . '%')
                            ->orWhere('id_pedido', 'like', '%' . $search . '%');
                    });
                }

                return DataTables::of($data)
                    ->addColumn('productos', function ($row) {
                        if ($row->pedido) {
                            return $row->pedido->productos;
                        } else {
                            // Si no existe un id_pedido, obtener los productos desde el id_envio
                            $pedidoProductos = PedidoProducto::where('id_envio', $row->id)->get();

                            $productos = [];
                            foreach ($pedidoProductos as $pedidoProducto) {
                                $productos[] = $pedidoProducto->producto->nombre; // Nombre de los productos
                            }

                            return implode(', ', $productos); // Concatena y muestra los productos
                        }
                    })
                    ->addColumn('cantidad_productos', function ($row) {
                        if ($row->pedido) {
                            return $row->pedido->cantidad_productos;
                        } else {
                            // Si no existe un id_pedido, sumar las cantidades de los productos relacionados
                            $pedidoProductos = PedidoProducto::where('id_envio', $row->id)->get();
                            return $pedidoProductos->sum('cantidad'); // Sumar las cantidades
                        }
                    })
                    ->addColumn('monto_deposito', function ($row) {
                        if ($row->pedido) {
                            return number_format($row->pedido->monto_deposito, 2);
                        } else {
                            // Si no existe un id_pedido, sumar el monto total de los productos relacionados
                            $pedidoProductos = PedidoProducto::where('id_envio', $row->id)->get();
                            $totalMonto = 0;
                            foreach ($pedidoProductos as $pedidoProducto) {
                                $totalMonto += $pedidoProducto->cantidad * $pedidoProducto->precio;
                            }
                            return number_format($totalMonto, 2); // Mostrar el total formateado
                        }
                    })
                    ->addColumn('action', function ($row) {
                        return '<button class="btn btn-sm btn-danger delete-envio" data-id="' . $row->id . '">Eliminar</button>';
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }
        }
    }

    public function indexSinLaPazYEnviados()
    {
        // Método 3: Mostrar todos los envíos donde 'lapaz' no esté seleccionado (es falso) y 'enviado' no esté marcado (es falso)
        $envios = Envio::latest()->where('lapaz', false)->where('enviado', false)->paginate(10);
        $semanas = Semana::latest('created_at')->take(5)->get();
        $idSucursal = 1;

        $productos = Producto::with(['inventarios' => function ($query) use ($idSucursal) {
            $query->where('id_sucursal', $idSucursal);
        }])->get()->map(function ($producto) use ($idSucursal) {
            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'cantidad_sucursal' => optional($producto->inventarios->first())->cantidad ?? 0,
            ];
        });
        return view('envioscuaderno.indexenlp', compact('envios', 'semanas', 'productos'));
    }
    public function dataTableSinLaPazYEnviados(Request $request)
    {
        if ($request->ajax()) {
            // Filtra los envíos donde 'lapaz' es false y 'enviado' es false
            $data = Envio::with('pedido')->latest()->where('lapaz', false)->where('enviado', false);

            if ($request->ajax()) {
                // Filtrar por los campos booleanos
                if ($request->has('lapaz') && $request->lapaz !== null) {
                    $data->where('lapaz', $request->lapaz);
                }
                if ($request->has('enviado') && $request->enviado !== null) {
                    $data->where('enviado', $request->enviado);
                }
                if ($request->has('extra') && $request->extra !== null) {
                    $data->where('extra', $request->extra);
                }
                if ($request->has('extra1') && $request->extra1 !== null) {
                    $data->where('extra1', $request->extra1);
                }

                // Filtrar por fecha de creación
                if ($request->has('start_date') && !empty($request->start_date)) {
                    $data->where('fecha_hora_creada', '>=', $request->start_date);
                }

                if ($request->has('end_date') && !empty($request->end_date)) {
                    $data->where('fecha_hora_creada', '<=', $request->end_date);
                }
                if ($request->has('celular') && !empty($request->celular)) {
                    $search = $request->celular;

                    $data->where(function ($query) use ($search) {
                        $query->where('celular', 'like', '%' . $search . '%')
                            ->orWhere('id', 'like', '%' . $search . '%')
                            ->orWhere('id_pedido', 'like', '%' . $search . '%');
                    });
                }

                return DataTables::of($data)
                    ->addColumn('productos', function ($row) {
                        if ($row->pedido) {
                            return $row->pedido->productos;
                        } else {
                            // Si no existe un id_pedido, obtener los productos desde el id_envio
                            $pedidoProductos = PedidoProducto::where('id_envio', $row->id)->get();

                            $productos = [];
                            foreach ($pedidoProductos as $pedidoProducto) {
                                $productos[] = $pedidoProducto->producto->nombre; // Nombre de los productos
                            }

                            return implode(', ', $productos); // Concatena y muestra los productos
                        }
                    })
                    ->addColumn('cantidad_productos', function ($row) {
                        if ($row->pedido) {
                            return $row->pedido->cantidad_productos;
                        } else {
                            // Si no existe un id_pedido, sumar las cantidades de los productos relacionados
                            $pedidoProductos = PedidoProducto::where('id_envio', $row->id)->get();
                            return $pedidoProductos->sum('cantidad'); // Sumar las cantidades
                        }
                    })
                    ->addColumn('monto_deposito', function ($row) {
                        if ($row->pedido) {
                            return number_format($row->pedido->monto_deposito, 2);
                        } else {
                            // Si no existe un id_pedido, sumar el monto total de los productos relacionados
                            $pedidoProductos = PedidoProducto::where('id_envio', $row->id)->get();
                            $totalMonto = 0;
                            foreach ($pedidoProductos as $pedidoProducto) {
                                $totalMonto += $pedidoProducto->cantidad * $pedidoProducto->precio;
                            }
                            return number_format($totalMonto, 2); // Mostrar el total formateado
                        }
                    })
                    ->addColumn('action', function ($row) {
                        return '<button class="btn btn-sm btn-danger delete-envio" data-id="' . $row->id . '">Eliminar</button>';
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }
        }
    }


    public function indexSoloLaPaz()
    {
        // Método 2: Mostrar todos los envíos donde 'lapaz' no esté seleacionado (es falso)
        $envios = Envio::latest()->where('lapaz', true)->paginate(10);
        $semanas = Semana::latest('created_at')->take(5)->get();
        $idSucursal = 1;

        $productos = Producto::with(['inventarios' => function ($query) use ($idSucursal) {
            $query->where('id_sucursal', $idSucursal);
        }])->get()->map(function ($producto) use ($idSucursal) {
            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'cantidad_sucursal' => optional($producto->inventarios->first())->cantidad ?? 0,
            ];
        });
        return view('envioscuaderno.indexlsoloapaz', compact('envios', 'semanas', 'productos'));
    }
    public function dataTableSoloLaPaz(Request $request)
    {
        if ($request->ajax()) {
            // Filtra los envíos donde 'lapaz' está marcado como false
            $data = Envio::with('pedido')->latest()->where('lapaz', true);

            if ($request->ajax()) {
                // Filtrar por los campos booleanos
                if ($request->has('lapaz') && $request->lapaz !== null) {
                    $data->where('lapaz', $request->lapaz);
                }
                if ($request->has('enviado') && $request->enviado !== null) {
                    $data->where('enviado', $request->enviado);
                }
                if ($request->has('extra') && $request->extra !== null) {
                    $data->where('extra', $request->extra);
                }
                if ($request->has('extra1') && $request->extra1 !== null) {
                    $data->where('extra1', $request->extra1);
                }

                // Filtrar por fecha de creación
                if ($request->has('start_date') && !empty($request->start_date)) {
                    $data->where('fecha_hora_creada', '>=', $request->start_date);
                }

                if ($request->has('end_date') && !empty($request->end_date)) {
                    $data->where('fecha_hora_creada', '<=', $request->end_date);
                }
                if ($request->has('celular') && !empty($request->celular)) {
                    $search = $request->celular;

                    $data->where(function ($query) use ($search) {
                        $query->where('celular', 'like', '%' . $search . '%')
                            ->orWhere('id', 'like', '%' . $search . '%')
                            ->orWhere('id_pedido', 'like', '%' . $search . '%');
                    });
                }

                return DataTables::of($data)
                    ->addColumn('productos', function ($row) {
                        if ($row->pedido) {
                            return $row->pedido->productos;
                        } else {
                            // Si no existe un id_pedido, obtener los productos desde el id_envio
                            $pedidoProductos = PedidoProducto::where('id_envio', $row->id)->get();

                            $productos = [];
                            foreach ($pedidoProductos as $pedidoProducto) {
                                $productos[] = $pedidoProducto->producto->nombre; // Nombre de los productos
                            }

                            return implode(', ', $productos); // Concatena y muestra los productos
                        }
                    })
                    ->addColumn('cantidad_productos', function ($row) {
                        if ($row->pedido) {
                            return $row->pedido->cantidad_productos;
                        } else {
                            // Si no existe un id_pedido, sumar las cantidades de los productos relacionados
                            $pedidoProductos = PedidoProducto::where('id_envio', $row->id)->get();
                            return $pedidoProductos->sum('cantidad'); // Sumar las cantidades
                        }
                    })
                    ->addColumn('monto_deposito', function ($row) {
                        if ($row->pedido) {
                            return number_format($row->pedido->monto_deposito, 2);
                        } else {
                            // Si no existe un id_pedido, sumar el monto total de los productos relacionados
                            $pedidoProductos = PedidoProducto::where('id_envio', $row->id)->get();
                            $totalMonto = 0;
                            foreach ($pedidoProductos as $pedidoProducto) {
                                $totalMonto += $pedidoProducto->cantidad * $pedidoProducto->precio;
                            }
                            return number_format($totalMonto, 2); // Mostrar el total formateado
                        }
                    })
                    ->addColumn('action', function ($row) {
                        return '<button class="btn btn-sm btn-danger delete-envio" data-id="' . $row->id . '">Eliminar</button>';
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }
        }
    }


    // Método para manejar la búsqueda y paginación con AJAX
    public function search(Request $request)
    {
        // Obtener el término de búsqueda (si existe)
        $searchTerm = $request->input('search', '');

        // Filtramos los envíos según el término de búsqueda
        $envios = Envio::where('celular', 'LIKE', "%$searchTerm%")
            ->orWhere('departamento', 'LIKE', "%$searchTerm%")
            ->orWhere('descripcion', 'LIKE', "%$searchTerm%")
            ->orWhere('monto_de_pago', 'LIKE', "%$searchTerm%")
            ->latest()
            ->paginate(10);

        // Retornamos la vista con la tabla actualizada (y la paginación)
        return view('envioscuaderno.data', compact('envios'))->render();
    }
    // Método para manejar las solicitudes AJAX de DataTables

    public function destroyMultiple(Request $request)
    {
        $ids = $request->input('ids');
        Envio::whereIn('id', $ids)->delete();
        return response()->json(['success' => 'Filas eliminadas con éxito']);
    }

    public function extra1View()
    {
        $semanas = Semana::latest('created_at')->take(5)->get();
        $idSucursal = 1;

        $productos = Producto::with(['inventarios' => function ($query) use ($idSucursal) {
            $query->where('id_sucursal', $idSucursal);
        }])->get()->map(function ($producto) use ($idSucursal) {
            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'cantidad_sucursal' => optional($producto->inventarios->first())->cantidad ?? 0,
            ];
        });

        // Filtros aplicados a envíos
        $enviosFiltrados = Envio::with('pedido') // importante: eager load del pedido
            ->where('extra', 1)
            ->where('enviado', '!=', 1)
            ->where('lapaz', '!=', 1)
            ->where('extra1', '!=', 1);

        // Identificar pedidos duplicados
        $duplicados = (clone $enviosFiltrados)
            ->select('id_pedido')
            ->whereNotNull('id_pedido')
            ->groupBy('id_pedido')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('id_pedido');

        $pedidosDuplicados = (clone $enviosFiltrados)
            ->whereIn('id_pedido', $duplicados)
            ->orderBy('id_pedido')
            ->get()
            ->groupBy('id_pedido');

        // Obtener pedidos confirmados dentro de los envíos filtrados
        $pedidosConfirmados = $enviosFiltrados
            ->get()
            ->filter(function ($envio) {
                return $envio->pedido && $envio->pedido->estado_pedido === 'confirmado';
            })
            ->groupBy('id_pedido');

        return view('envioscuaderno.extra1', compact(
            'semanas',
            'productos',
            'pedidosDuplicados',
            'pedidosConfirmados'
        ));
    }

    public function extra1Data(Request $request)
    {
        $data = Envio::with('pedido')
            ->where('extra', 1)
            ->where('enviado', '!=', 1)
            ->where('lapaz', '!=', 1)
            ->where('extra1', '!=', 1)
            ->latest();

        // Aplicar filtros de búsqueda si es una solicitud AJAX
        if ($request->ajax()) {
            if ($request->has('celular') && !empty($request->celular)) {
                $search = $request->celular;

                $data->where(function ($query) use ($search) {
                    $query->where('celular', 'like', '%' . $search . '%')
                        ->orWhere('id', 'like', '%' . $search . '%')
                        ->orWhere('id_pedido', 'like', '%' . $search . '%');
                });
            }
        }

        return DataTables::of($data)
            ->addColumn('productos', function ($row) {
                if ($row->pedido) {
                    return $row->pedido->productos;
                } else {
                    $pedidoProductos = PedidoProducto::where('id_envio', $row->id)->get();
                    $productos = $pedidoProductos->map(fn($p) => $p->producto->nombre)->toArray();
                    return implode(', ', $productos);
                }
            })
            ->addColumn('cantidad_productos', function ($row) {
                if ($row->pedido) {
                    return $row->pedido->cantidad_productos;
                } else {
                    return PedidoProducto::where('id_envio', $row->id)->sum('cantidad');
                }
            })
            ->addColumn('monto_deposito', function ($row) {
                if ($row->pedido) {
                    return number_format($row->pedido->monto_deposito, 2);
                } else {
                    $pedidoProductos = PedidoProducto::where('id_envio', $row->id)->get();
                    $totalMonto = $pedidoProductos->sum(fn($p) => $p->cantidad * $p->precio);
                    return number_format($totalMonto, 2);
                }
            })
            ->addColumn('action', function ($row) {
                return '<button class="btn btn-sm btn-danger delete-envio" data-id="' . $row->id . '">Eliminar</button>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }




    public function paginate(Request $request)
    {
        $page = $request->input('page', 1);
        $envios = Envio::latest()->paginate(10, ['*'], 'page', $page);
        return view('envioscuaderno.data', compact('envios'))->render();
    }
    public function store(Request $request)
    {
        // dd($request->all());
        $envio = new Envio();
        $envio->celular = $request->input('celular');
        $envio->departamento = null;
        $envio->monto_de_pago = null;
        $envio->descripcion = null;
        $envio->lapaz = false;
        $envio->enviado = false;
        $envio->extra = false;
        $envio->extra1 = false;
        $envio->extra2 = false;
        $envio->fecha_hora_enviado = null;
        $envio->fecha_hora_creada = now();
        $envio->id_pedido = null;
        $envio->save();

        // Redirigir a la función index
        return redirect()->route('envioscuaderno.index');
    }


    public function update(Request $request, $id)
    {
        $envio = Envio::find($id);

        // Verificamos si el envío existe
        if ($envio) {
            // Obtenemos el nombre del campo y su valor desde la solicitud
            $campo = $request->input('campo');
            $valor = $request->input('valor');

            // Verificamos si el campo está en el array $fillable
            if (in_array($campo, $envio->getFillable())) {
                // Actualizamos el campo dinámicamente
                $envio->$campo = $valor;
                $envio->save();
                return response()->json(['success' => true]);
            }
        }

        return response()->json(['success' => false, 'message' => 'Campo no válido']);
    }

    public function destroy($id)
    {
        $envio = Envio::find($id);
        $envio->delete();

        return response()->json(['success' => 'Envio eliminado con éxito']);
    }

    // Método para buscar los pedidos por id
    // Método para buscar pedidos por ID o nombre
    public function searchPedido(Request $request)
    {
        $search = $request->input('search');
        $limit = $request->input('limit', 3); // Limitar resultados a 3 por defecto

        // Limpiar el término de búsqueda para evitar problemas con espacios extra
        $search = trim($search);

        if (empty($search)) {
            return response()->json(['message' => 'Por favor ingresa un término de búsqueda.'], 400);
        }

        // Buscar pedidos que coincidan con el término de búsqueda
        $pedidos = Pedido::where('id', 'like', '%' . $search . '%')
            ->orWhere('nombre', 'like', '%' . $search . '%')
            ->limit($limit)
            ->get();

        // Si no hay resultados, devolver mensaje de error
        if ($pedidos->isEmpty()) {
            return response()->json([]);
        }

        return response()->json($pedidos);
    }

    // Método para asociar el pedido al envío
    public function setPedido(Request $request, $envioId)
    {
        $pedidoId = $request->input('id_pedido');
        $envio = Envio::findOrFail($envioId);

        // Asignar el pedido al envío
        $envio->pedido_id = $pedidoId;
        $envio->save();

        return response()->json(['message' => 'Pedido asignado correctamente']);
    }

    public function actualizarSemana(Request $request)
    {

        $enviosSeleccionados = $request->envios;  // Array de IDs de envíos seleccionados
        $idSemana = $request->id_semana;  // ID de la semana seleccionada

        // Actualizar los pedidos relacionados con los envíos seleccionados
        foreach ($enviosSeleccionados as $envioId) {
            $envio = Envio::findOrFail($envioId);
            $pedido = $envio->pedido;  // Accedemos al pedido relacionado

            // Actualizamos el id_semana del pedido
            $pedido->id_semana = $idSemana;
            $pedido->save();

            $pedido->detalle = $envio->detalle;
            $pedido->save();
        }

        return response()->json(['success' => true, 'message' => 'Semana actualizada correctamente']);
    }

    public function indexpendientes()
    {
        $semanas = Semana::latest('created_at')->take(5)->get();
        $idSucursal = 1;

        $productos = Producto::with(['inventarios' => function ($query) use ($idSucursal) {
            $query->where('id_sucursal', $idSucursal);
        }])->get()->map(function ($producto) use ($idSucursal) {
            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'cantidad_sucursal' => optional($producto->inventarios->first())->cantidad ?? 0,
            ];
        });


        return view('envioscuaderno.indexlapazpendiente', compact('semanas', 'productos'));
    }
    public function dataTablePendientes(Request $request)
    {
        $data = Envio::with('pedido') // Asegúrate de cargar la relación 'pedido'
            ->whereHas('pedido', function ($query) {
                $query->where('estado_pedido', 'pendiente');
            })
            ->latest()
            ->where('lapaz', true);

        if ($request->ajax()) {
            // Filtrar por los campos booleanos
            if ($request->has('lapaz') && $request->lapaz !== null) {
                $data->where('lapaz', $request->lapaz);
            }
            if ($request->has('enviado') && $request->enviado !== null) {
                $data->where('enviado', $request->enviado);
            }
            if ($request->has('extra') && $request->extra !== null) {
                $data->where('extra', $request->extra);
            }
            if ($request->has('extra1') && $request->extra1 !== null) {
                $data->where('extra1', $request->extra1);
            }

            // Filtrar por fecha de creación
            if ($request->has('start_date') && !empty($request->start_date)) {
                $data->where('fecha_hora_creada', '>=', $request->start_date);
            }

            if ($request->has('end_date') && !empty($request->end_date)) {
                $data->where('fecha_hora_creada', '<=', $request->end_date);
            }
            if ($request->has('celular') && !empty($request->celular)) {
                $search = $request->celular;

                $data->where(function ($query) use ($search) {
                    $query->where('celular', 'like', '%' . $search . '%')
                        ->orWhere('id', 'like', '%' . $search . '%')
                        ->orWhere('id_pedido', 'like', '%' . $search . '%');
                });
            }


            return DataTables::of($data)
                ->addColumn('productos', function ($row) {
                    if ($row->pedido) {
                        return $row->pedido->productos;
                    } else {
                        // Si no existe un id_pedido, obtener los productos desde el id_envio
                        $pedidoProductos = PedidoProducto::where('id_envio', $row->id)->get();

                        $productos = [];
                        foreach ($pedidoProductos as $pedidoProducto) {
                            $productos[] = $pedidoProducto->producto->nombre; // Nombre de los productos
                        }

                        return implode(', ', $productos); // Concatena y muestra los productos
                    }
                })
                ->addColumn('cantidad_productos', function ($row) {
                    if ($row->pedido) {
                        return $row->pedido->cantidad_productos;
                    } else {
                        // Si no existe un id_pedido, sumar las cantidades de los productos relacionados
                        $pedidoProductos = PedidoProducto::where('id_envio', $row->id)->get();
                        return $pedidoProductos->sum('cantidad'); // Sumar las cantidades
                    }
                })
                ->addColumn('monto_deposito', function ($row) {
                    if ($row->pedido) {
                        return number_format($row->pedido->monto_deposito, 2);
                    } else {
                        // Si no existe un id_pedido, sumar el monto total de los productos relacionados
                        $pedidoProductos = PedidoProducto::where('id_envio', $row->id)->get();
                        $totalMonto = 0;
                        foreach ($pedidoProductos as $pedidoProducto) {
                            $totalMonto += $pedidoProducto->cantidad * $pedidoProducto->precio;
                        }
                        return number_format($totalMonto, 2); // Mostrar el total formateado
                    }
                })
                ->addColumn('action', function ($row) {
                    return '<button class="btn btn-sm btn-danger delete-envio" data-id="' . $row->id . '">Eliminar</button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function indexconfirmados()
    {
        $semanas = Semana::latest('created_at')->take(5)->get();
        $idSucursal = 1;

        $productos = Producto::with(['inventarios' => function ($query) use ($idSucursal) {
            $query->where('id_sucursal', $idSucursal);
        }])->get()->map(function ($producto) use ($idSucursal) {
            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'cantidad_sucursal' => optional($producto->inventarios->first())->cantidad ?? 0,
            ];
        });


        return view('envioscuaderno.indexlapazconfirmado', compact('semanas', 'productos'));
    }
    public function dataTableConfirmados(Request $request)
    {


        $data = Envio::with('pedido')->latest()->where('lapaz', true)->whereHas('pedido', function ($query) {
            $query->where('estado_pedido', 'confirmado');
        });
        if ($request->ajax()) {
            // Filtrar por los campos booleanos
            if ($request->has('lapaz') && $request->lapaz !== null) {
                $data->where('lapaz', $request->lapaz);
            }
            if ($request->has('enviado') && $request->enviado !== null) {
                $data->where('enviado', $request->enviado);
            }
            if ($request->has('extra') && $request->extra !== null) {
                $data->where('extra', $request->extra);
            }
            if ($request->has('extra1') && $request->extra1 !== null) {
                $data->where('extra1', $request->extra1);
            }

            // Filtrar por fecha de creación
            if ($request->has('start_date') && !empty($request->start_date)) {
                $data->where('fecha_hora_creada', '>=', $request->start_date);
            }

            if ($request->has('end_date') && !empty($request->end_date)) {
                $data->where('fecha_hora_creada', '<=', $request->end_date);
            }
            if ($request->has('celular') && !empty($request->celular)) {
                $search = $request->celular;

                $data->where(function ($query) use ($search) {
                    $query->where('celular', 'like', '%' . $search . '%')
                        ->orWhere('id', 'like', '%' . $search . '%')
                        ->orWhere('id_pedido', 'like', '%' . $search . '%');
                });
            }


            return DataTables::of($data)
                ->addColumn('productos', function ($row) {
                    if ($row->pedido) {
                        return $row->pedido->productos;
                    } else {
                        // Si no existe un id_pedido, obtener los productos desde el id_envio
                        $pedidoProductos = PedidoProducto::where('id_envio', $row->id)->get();

                        $productos = [];
                        foreach ($pedidoProductos as $pedidoProducto) {
                            $productos[] = $pedidoProducto->producto->nombre; // Nombre de los productos
                        }

                        return implode(', ', $productos); // Concatena y muestra los productos
                    }
                })
                ->addColumn('cantidad_productos', function ($row) {
                    if ($row->pedido) {
                        return $row->pedido->cantidad_productos;
                    } else {
                        // Si no existe un id_pedido, sumar las cantidades de los productos relacionados
                        $pedidoProductos = PedidoProducto::where('id_envio', $row->id)->get();
                        return $pedidoProductos->sum('cantidad'); // Sumar las cantidades
                    }
                })
                ->addColumn('monto_deposito', function ($row) {
                    if ($row->pedido) {
                        return number_format($row->pedido->monto_deposito, 2);
                    } else {
                        // Si no existe un id_pedido, sumar el monto total de los productos relacionados
                        $pedidoProductos = PedidoProducto::where('id_envio', $row->id)->get();
                        $totalMonto = 0;
                        foreach ($pedidoProductos as $pedidoProducto) {
                            $totalMonto += $pedidoProducto->cantidad * $pedidoProducto->precio;
                        }
                        return number_format($totalMonto, 2); // Mostrar el total formateado
                    }
                })
                ->addColumn('action', function ($row) {
                    return '<button class="btn btn-sm btn-danger delete-envio" data-id="' . $row->id . '">Eliminar</button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }
    // Función para mostrar la vista de edición
    public function edit($id)
    {
        // Obtener el Envio con sus relaciones
        $envio = Envio::with(['pedido', 'pedido.pedidoProductos.producto'])->findOrFail($id);

        // Obtener todos los productos para poder mostrarlos en un dropdown
        $productos = Producto::all();

        // Pasar a la vista
        return view('envioscuaderno.edit', compact('envio', 'productos'));
    }

    // Función para actualizar los datos
    public function updatea(Request $request, $id)
    {
        // Validación de datos
        $validated = $request->validate([
            'celular' => 'required|string',
            'departamento' => 'required|string',
            'monto_de_pago' => 'required|numeric',
            'descripcion' => 'required|string',
            'estado' => 'required|string|in:pendiente,confirmado',
            'producto_id' => 'required|exists:productos,id',  // Validar el producto seleccionado
        ]);

        // Obtener el Envio
        $envio = Envio::findOrFail($id);

        // Actualizar el Envio
        $envio->update($validated);

        // Si el producto está siendo actualizado, actualizamos también el Producto
        if ($request->has('producto_id')) {
            $producto = Producto::findOrFail($request->producto_id);
            // Realizar acciones si se necesita cambiar algún campo relacionado
            // Ejemplo: Actualizar cantidad de stock, etc.
        }

        // Redirigir con un mensaje de éxito
        return redirect()->route('envioscuaderno.index')->with('success', 'Envio y producto actualizado correctamente.');
    }
    
    public function indexsinmarcados()
    {
        // Método 2: Mostrar todos los envíos donde 'lapaz' no esté seleacionado (es falso)
        $envios = Envio::latest()
            ->where('lapaz', false)
            ->where('enviado', false)
            ->where('extra', false)
            ->where('extra1', false)
            ->where('extra2', false)
            ->where('extra3', false)
            ->paginate(10);

        $semanas = Semana::latest('created_at')->take(5)->get();
        $idSucursal = 1;

        $productos = Producto::with(['inventarios' => function ($query) use ($idSucursal) {
            $query->where('id_sucursal', $idSucursal);
        }])->get()->map(function ($producto) use ($idSucursal) {
            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'cantidad_sucursal' => optional($producto->inventarios->first())->cantidad ?? 0,
            ];
        });
        return view('envioscuaderno.indexsinmarcados', compact('envios', 'semanas', 'productos'));
    }
    public function dataTablesinmarcados(Request $request)
    {
        if ($request->ajax()) {
            // Filtra los envíos donde 'lapaz' está marcado como false
            $data = Envio::with('pedido')->latest()
                ->where('lapaz', false)
                ->where('enviado', false)
                ->where('extra', false)
                ->where('extra1', false)
                ->where('extra2', false)
                ->where('extra3', false);


            if ($request->ajax()) {
                // Filtrar por los campos booleanos
                if ($request->has('lapaz') && $request->lapaz !== null) {
                    $data->where('lapaz', $request->lapaz);
                }
                if ($request->has('enviado') && $request->enviado !== null) {
                    $data->where('enviado', $request->enviado);
                }
                if ($request->has('extra') && $request->extra !== null) {
                    $data->where('extra', $request->extra);
                }
                if ($request->has('extra1') && $request->extra1 !== null) {
                    $data->where('extra1', $request->extra1);
                }

                // Filtrar por fecha de creación
                if ($request->has('start_date') && !empty($request->start_date)) {
                    $data->where('fecha_hora_creada', '>=', $request->start_date);
                }

                if ($request->has('end_date') && !empty($request->end_date)) {
                    $data->where('fecha_hora_creada', '<=', $request->end_date);
                }
                if ($request->has('celular') && !empty($request->celular)) {
                    $search = $request->celular;

                    $data->where(function ($query) use ($search) {
                        $query->where('celular', 'like', '%' . $search . '%')
                            ->orWhere('id', 'like', '%' . $search . '%')
                            ->orWhere('id_pedido', 'like', '%' . $search . '%');
                    });
                }

                return DataTables::of($data)
                    ->addColumn('productos', function ($row) {
                        if ($row->pedido) {
                            return $row->pedido->productos;
                        } else {
                            // Si no existe un id_pedido, obtener los productos desde el id_envio
                            $pedidoProductos = PedidoProducto::where('id_envio', $row->id)->get();

                            $productos = [];
                            foreach ($pedidoProductos as $pedidoProducto) {
                                $productos[] = $pedidoProducto->producto->nombre; // Nombre de los productos
                            }

                            return implode(', ', $productos); // Concatena y muestra los productos
                        }
                    })
                    ->addColumn('cantidad_productos', function ($row) {
                        if ($row->pedido) {
                            return $row->pedido->cantidad_productos;
                        } else {
                            // Si no existe un id_pedido, sumar las cantidades de los productos relacionados
                            $pedidoProductos = PedidoProducto::where('id_envio', $row->id)->get();
                            return $pedidoProductos->sum('cantidad'); // Sumar las cantidades
                        }
                    })
                    ->addColumn('monto_deposito', function ($row) {
                        if ($row->pedido) {
                            return number_format($row->pedido->monto_deposito, 2);
                        } else {
                            // Si no existe un id_pedido, sumar el monto total de los productos relacionados
                            $pedidoProductos = PedidoProducto::where('id_envio', $row->id)->get();
                            $totalMonto = 0;
                            foreach ($pedidoProductos as $pedidoProducto) {
                                $totalMonto += $pedidoProducto->cantidad * $pedidoProducto->precio;
                            }
                            return number_format($totalMonto, 2); // Mostrar el total formateado
                        }
                    })
                    ->addColumn('action', function ($row) {
                        return '<button class="btn btn-sm btn-danger delete-envio" data-id="' . $row->id . '">Eliminar</button>';
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }
        }
    }
    
     public function searchPedidoSucursal(Request $request)
    {
        $search = trim($request->input('search'));
        $limit = $request->input('limit', 3);

        // Validar búsqueda
        if (empty($search)) {
            return response()->json(['message' => 'Por favor ingresa un término de búsqueda.'], 400);
        }

        // Obtener la sucursal del usuario logueado
        $user = auth()->user();
        $sucursal = $user->sucursales->first();

        if (!$sucursal) {
            return response()->json(['message' => 'El usuario no tiene una sucursal asignada.'], 403);
        }

        // Construir nombre de la semana
        $nombreSemana = 'PEDIDOS SUCURSAL ' . $sucursal->id;

        // Buscar la semana correspondiente
        $semana = Semana::whereRaw('LOWER(nombre) = ?', [strtolower($nombreSemana)])->first();

        if (!$semana) {
            return response()->json([]); // No se encontró semana, retornar vacío
        }

        // Buscar pedidos asociados a esa semana
        $pedidos = Pedido::where('id_semana', $semana->id)
            ->where(function ($query) use ($search) {
                $query->where('id', 'like', '%' . $search . '%')
                    ->orWhere('nombre', 'like', '%' . $search . '%');
            })
            ->limit($limit)
            ->get();

        return response()->json($pedidos);
    }

    
    
}
