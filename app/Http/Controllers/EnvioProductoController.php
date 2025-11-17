<?php

namespace App\Http\Controllers;

use App\Models\HistorialEnvio;
use App\Models\Inventario;
use App\Models\InventarioHistorial;
use App\Models\InventarioProducto;
use App\Models\Producto;
use App\Models\Sucursale;
use App\Models\User;
use Carbon\Carbon;
use FPDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class EnvioProductoController extends Controller
{
    public function UsuariosPorSucursal($sucursal_id)
    {
        // Obtener los usuarios relacionados con la sucursal
        $usuarios = User::whereHas('sucursales', function ($query) use ($sucursal_id) {
            $query->where('sucursal_id', $sucursal_id);
        })->get();

        // Retornar los usuarios como un JSON
        return response()->json($usuarios);
    }
    public function storeSolicitudEntreSucursales(Request $request)
    {
        // Validamos los datos enviados
        $request->validate([
            'sucursal_origen' => 'required|exists:sucursales,id',
            'productos' => 'required|array',
            'productos.*.cantidad' => 'required|integer|min:1',
        ]);

        // Obtenemos al usuario autenticado
        $user = auth()->user();

        // Verificamos si el usuario tiene sucursales asignadas
        if ($user->sucursales->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario no tiene sucursales asignadas.',
            ], 400);
        }

        // Seleccionamos la primera sucursal del usuario (ajustar lógica si es necesario)
        $sucursalDestino = $user->sucursales->first()->id;

        // Creamos el registro en el historial de solicitud
        $historial = InventarioHistorial::create([
            'id_user' => $user->id,
            'id_sucursal' => $sucursalDestino,
            'id_user_destino' => $user->id,
            'id_sucursal_origen' => $request->sucursal_origen,
            'estado' => 'pendiente', // Se mantiene pendiente hasta confirmar
            'fecha_envio' => now(),
        ]);

        // Registrar los productos solicitados sin modificar el stock aún
        foreach ($request->productos as $productoId => $productoData) {
            $cantidadSolicitada = $productoData['cantidad'];

            // Consultamos el stock en la sucursal de origen
            $inventarioOrigen = Inventario::where('id_producto', $productoId)
                ->where('id_sucursal', $request->sucursal_origen)
                ->first();

            if (!$inventarioOrigen || $inventarioOrigen->cantidad < $cantidadSolicitada) {
                // Si no hay stock suficiente, eliminamos el historial creado y devolvemos error
                $historial->delete();
                return response()->json([
                    'success' => false,
                    'message' => 'No hay suficiente stock en la sucursal seleccionada para el producto: ' . Producto::find($productoId)->nombre,
                ], 400);
            }

            // 🔹 Obtener la cantidad actual en la sucursal destino (antes de la solicitud) 🔹
            $inventarioDestino = Inventario::where('id_producto', $productoId)
                ->where('id_sucursal', $sucursalDestino)
                ->first();

            $cantidadAnteriorDestino = $inventarioDestino ? $inventarioDestino->cantidad : 0;

            // Guardamos la solicitud sin modificar el stock
            InventarioProducto::create([
                'id_inventariohistorial' => $historial->id,
                'id_producto' => $productoId,
                'cantidad' => $cantidadSolicitada,
                'cantidad_antes' => $cantidadAnteriorDestino, // 🔹 Cantidad en la sucursal destino antes de la solicitud 🔹
                'cantidad_despues' => null, // Se actualizará al confirmar
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Solicitud creada correctamente. El stock se descontará cuando se confirme el envío.',
            'historial_id' => $historial->id,
        ]);
    }



    public function sendSucursal1(Request $request)
    {
        // Validar los datos enviados
        $request->validate([
            'productos' => 'required|array|min:1',
            'productos.*.id_producto' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
        ]);

        // Definir la sucursal de destino (Sucursal 1)
        $sucursalDestino = 1;
        // Obtener el usuario que envía (se asume que estás usando autenticación)
        $idUsuarioOrigen = $request->user()->id;

        // Crear un registro en el historial de inventario (puedes ajustar los datos según tu modelo)
        $historial = InventarioHistorial::create([
            'id_sucursal_origen' => null, // O asigna el ID de tu almacén si lo tienes definido
            'id_sucursal'        => $sucursalDestino,
            'id_user'            => $idUsuarioOrigen,
            'id_user_destino'    => $idUsuarioOrigen, // O define otro usuario destino según tu lógica
            'fecha_envio'        => now(),
            'estado'             => 'enviado',
        ]);

        // Procesar cada producto enviado
        foreach ($request->productos as $productoData) {
            $producto = Producto::findOrFail($productoData['id_producto']);
            $cantidad = $productoData['cantidad'];

            // Verificar que haya stock suficiente en el almacén
            if ($producto->stock < $cantidad) {
                return redirect()->back()->with('error', 'No hay suficiente stock para el producto: ' . $producto->nombre);
            }

            // Actualizar el stock en el almacén
            $producto->stock -= $cantidad;
            $producto->save();

            // Actualizar o crear el inventario en la sucursal 1
            $inventarioSucursal = $producto->inventarios()->where('id_sucursal', $sucursalDestino)->first();
            $cantidadAntes = $inventarioSucursal ? $inventarioSucursal->cantidad : 0;

            if ($inventarioSucursal) {
                $inventarioSucursal->cantidad += $cantidad;
                $inventarioSucursal->save();
            } else {
                Inventario::create([
                    'id_producto' => $producto->id,
                    'id_sucursal' => $sucursalDestino,
                    'cantidad'    => $cantidad,
                ]);
            }

            // Registrar en el historial de productos enviados
            InventarioProducto::create([
                'id_inventariohistorial' => $historial->id,
                'id_producto'            => $producto->id,
                'cantidad'               => $cantidad,
                'cantidad_antes'         => $cantidadAntes,
                'cantidad_despues'       => $cantidadAntes + $cantidad,
            ]);
        }

        return redirect()->route('envios.index')->with('success', 'Productos enviados a Sucursal 1 correctamente.');
    }

    public function edit($id)
    {
        // Obtener el historial de envío
        $historial = InventarioHistorial::with(['productos.producto', 'sucursalOrigen', 'sucursalDestino'])->findOrFail($id);

        // Obtener datos necesarios para la vista
        $productos = Producto::all();
        $sucursales = Sucursale::all();
        $usuarios = User::all();

        $idSucursalOrigen = 1; // Definir la sucursal de origen
        return view('envios.edit', compact('historial', 'productos', 'sucursales', 'usuarios', 'idSucursalOrigen'));
    }

    public function update(Request $request, $id)
    {
        // Validación de los datos del formulario (se elimina la validación para 'id_usuario')
        $request->validate([
            'productos' => 'required|array',
            'productos.*.cantidad' => 'required|integer|min:1',
            'id_sucursal' => 'required|exists:sucursales,id',
        ]);

        $idSucursalOrigen = 1; // Definir la sucursal de origen fija (Sucursal 1)

        // Obtener el historial de envío
        $historial = InventarioHistorial::findOrFail($id);

        // El usuario destino es el mismo que el usuario origen
        $idUsuarioOrigen = $historial->id_user;

        // Actualizar datos de la sucursal destino y asignar el mismo usuario origen
        $historial->update([
            'id_sucursal'     => $request->id_sucursal,
            'id_user_destino' => $idUsuarioOrigen,
        ]);

        // Recorrer los productos enviados en el formulario
        foreach ($request->productos as $productoId => $productoData) {
            $cantidadSolicitada = $productoData['cantidad'];

            // Obtener el stock en la Sucursal Origen (1)
            $inventarioOrigen = Inventario::where('id_producto', $productoId)
                ->where('id_sucursal', $idSucursalOrigen)
                ->first();

            // Verificar que el producto existe en la Sucursal Origen y tiene suficiente stock
            if (!$inventarioOrigen || $inventarioOrigen->cantidad < $cantidadSolicitada) {
                return back()->with('error', 'No hay suficiente stock en la Sucursal 1 para el producto: ' . Producto::find($productoId)->nombre);
            }

            // Obtener la cantidad actual en la Sucursal Destino
            $inventarioDestino = Inventario::where('id_producto', $productoId)
                ->where('id_sucursal', $request->id_sucursal)
                ->first();
            $cantidadAnterior = $inventarioDestino ? $inventarioDestino->cantidad : 0;

            // Buscar si el producto ya existe en el historial
            $productoExistente = InventarioProducto::where('id_inventariohistorial', $historial->id)
                ->where('id_producto', $productoId)
                ->first();

            if ($productoExistente) {
                // Si el producto ya está en el historial, actualizar la cantidad
                $productoExistente->update([
                    'cantidad'        => $cantidadSolicitada,
                    'cantidad_antes'  => $cantidadAnterior,
                ]);
            } else {
                // Si el producto no existe, agregarlo al historial
                InventarioProducto::create([
                    'id_inventariohistorial' => $historial->id,
                    'id_producto'            => $productoId,
                    'cantidad'               => $cantidadSolicitada,
                    'cantidad_antes'         => $cantidadAnterior,
                    'cantidad_despues'       => null, // Se actualizará al confirmar el envío
                ]);
            }
        }

        return redirect()->route('envios.solicitud')->with('success', 'Solicitud actualizada correctamente.');
    }





    public function enviosP()
    {
        $productos = Producto::all();
        $sucursales = Sucursale::all();
        $usuarios = User::all();

        // Establecer ID de la sucursal de origen (almacén)
        $idSucursalOrigen = 1; // Cambia esto al ID real de tu almacén

        return view('envios.solicitar', compact('productos', 'sucursales', 'usuarios', 'idSucursalOrigen'));
    }

    public function storeEnvio(Request $request)
    {
        // Validación de los datos del formulario
        $request->validate([
            'productos' => 'required|array',
            'productos.*.id_producto' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'id_sucursal' => 'required|exists:sucursales,id',
            'id_usuario' => 'required|exists:users,id',
        ]);

        // Configurar ID de sucursal de origen y usuario (ahora se usa la Sucursal 1)
        $idSucursalOrigen = 1;
        $idUsuarioOrigen = $request->user()->id;

        // Crear un registro en el historial de inventario
        $historial = InventarioHistorial::create([
            'id_sucursal_origen' => $idSucursalOrigen,
            'id_sucursal'        => $request->id_sucursal, // Sucursal destino
            'id_user'            => $idUsuarioOrigen,
            'id_user_destino'    => $request->id_usuario,
            'fecha_envio'        => now(),
            'estado'             => 'enviado',
        ]);

        foreach ($request->productos as $productoData) {
            $producto = Producto::findOrFail($productoData['id_producto']);
            $cantidad = $productoData['cantidad'];

            // Obtener el inventario en la Sucursal 1 (origen)
            $inventarioOrigen = $producto->inventarios()->where('id_sucursal', $idSucursalOrigen)->first();
            if (!$inventarioOrigen || $inventarioOrigen->cantidad < $cantidad) {
                return back()->with('error', 'No hay suficiente stock en la Sucursal 1 para el producto: ' . $producto->nombre);
            }

            // Obtener el inventario en la sucursal destino (para cantidad antes)
            $inventarioDestino = $producto->inventarios()->where('id_sucursal', $request->id_sucursal)->first();
            $cantidadAntesDestino = $inventarioDestino ? $inventarioDestino->cantidad : 0;

            // Actualizar (descontar) el stock en la Sucursal 1 (origen)
            $inventarioOrigen->cantidad -= $cantidad;
            $inventarioOrigen->save();

            // Manejar inventario en la sucursal destino
            if ($inventarioDestino) {
                $inventarioDestino->cantidad += $cantidad;
                $inventarioDestino->save();
                $cantidadDespues = $inventarioDestino->cantidad;
            } else {
                $cantidadDespues = $cantidad;
                Inventario::create([
                    'id_producto' => $producto->id,
                    'id_sucursal' => $request->id_sucursal,
                    'cantidad'    => $cantidad,
                ]);
            }

            // Registrar el movimiento en el historial de productos enviados
            InventarioProducto::create([
                'id_inventariohistorial' => $historial->id,
                'id_producto'            => $producto->id,
                'cantidad'               => $cantidad,
                'cantidad_antes'         => $cantidadAntesDestino, // Ahora muestra la cantidad antes en la sucursal destino
                'cantidad_despues'       => $cantidadDespues,
            ]);
        }

        // Generar contenido del PDF (reporte)
        $pdfContent = $this->generarReporte($request);

        // Retornar respuesta con el PDF en base64
        return response()->json([
            'success'     => true,
            'redirectUrl' => route('envios.index'),
            'reportUrl'   => 'data:application/pdf;base64,' . base64_encode($pdfContent),
        ]);
    }


    public function eliminarSolicitud($id)
    {
        try {
            $historial = InventarioHistorial::findOrFail($id);

            // Eliminar los productos relacionados en la tabla InventarioProducto
            $historial->productos()->delete();

            // Eliminar la solicitud
            $historial->delete();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la solicitud'
            ], 500);
        }
    }

    /* public function solicitudes(Request $request)
    {
        if ($request->ajax()) {
            $query = InventarioHistorial::with([
                'sucursalOrigen',
                'sucursalDestino',
                'usuarioOrigen',
                'usuarioDestino',
                'productos.producto'
            ])->whereIn('estado', ['pendiente', 'mal estado']); // Incluir registros con estado "mal estado"

            if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
                $query->whereBetween('fecha_envio', [
                    Carbon::parse($request->fecha_inicio)->startOfDay(),
                    Carbon::parse($request->fecha_fin)->endOfDay(),
                ]);
            }

            if ($request->filled('usuario_destino')) {
                $query->where('id_user_destino', $request->usuario_destino);
            }

            return DataTables::of($query)
                ->addColumn('sucursal_origen_nombre', function ($historial) {
                    return $historial->sucursalOrigen->nombre ?? 'N/A';
                })
                ->addColumn('estado', function ($historial) {
                    return ucfirst($historial->estado); // Capitalizar el estado
                })
                ->addColumn('productos', function ($historial) {
                    $productos = $historial->productos->map(function ($producto) {
                        return $producto->producto->nombre . ' (' . $producto->cantidad . ')';
                    })->join(', ');
                    return $productos;
                })
                ->addColumn('acciones', function ($historial) {
                    return "
                    <button class='btn btn-info btn-sm generar-reporte' data-id='{$historial->id}'>Generar Reporte</button>
                    <button class='btn btn-danger btn-sm eliminar-solicitud' data-id='{$historial->id}'>Eliminar</button>
                ";
                })
                ->rawColumns(['acciones'])
                ->make(true);
        }

        $usuariosDestino = User::all();
        $sucursales = Sucursale::all();

        return view('envios.solicitud', compact('usuariosDestino', 'sucursales'));
    } */
    public function solicitudes(Request $request)
    {
        if ($request->ajax()) {
            $query = InventarioHistorial::with([
                'sucursalOrigen',
                'sucursalDestino',
                'usuarioOrigen',
                'usuarioDestino',
                'productos.producto'
            ])->where('estado', 'pendiente', 'mal estado');

            if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
                $query->whereBetween('fecha_envio', [
                    Carbon::parse($request->fecha_inicio)->startOfDay(),
                    Carbon::parse($request->fecha_fin)->endOfDay(),
                ]);
            }

            if ($request->filled('usuario_destino')) {
                $query->where('id_user_destino', $request->usuario_destino);
            }

            return DataTables::of($query)
                ->addColumn('sucursal_origen_nombre', function ($historial) {
                    return $historial->sucursalOrigen
                        ? $historial->sucursalOrigen->nombre
                        : 'Almacén';
                })
                ->addColumn('estado', function ($historial) {
                    $color = $historial->estado === 'pendiente' ? 'yellow' : 'green';
                    return '<span style="display: inline-block; padding: 5px 10px; background-color: ' . $color . '; color: #000; border-radius: 3px; font-weight: bold;">
                            ' . ucfirst($historial->estado) . '</span>';
                })

                ->addColumn('productos', function ($historial) {
                    // Devuelve los datos formateados con HTML
                    return $historial->productos->map(function ($producto) {
                        return '
                <div style="padding: 5px; margin-bottom: 5px; border: 1px solid #ddd; border-radius: 5px; background: #f9f9f9;">
                    <strong>' . $producto->producto->nombre . '</strong><br>
                    <small style="color: #555;">Cant. Solicitada: ' . $producto->cantidad . '</small>
                </div>
            ';
                    })->implode('');
                })
                ->addColumn('productos_text', function ($historial) {
                    // Devuelve los datos en texto plano para búsqueda
                    return $historial->productos->map(function ($producto) {
                        return $producto->producto->nombre . ' (Cantidad: ' . $producto->cantidad . ')';
                    })->implode(', ');
                })
                ->rawColumns(['productos', 'estado']) // Procesa las columnas con HTML
                ->filterColumn('productos', function ($query, $keyword) {
                    // Filtrar la columna de productos_text en lugar de productos
                    $query->whereHas('productos.producto', function ($q) use ($keyword) {
                        $q->where('nombre', 'like', '%' . $keyword . '%');
                    });
                })
                ->addColumn('acciones', function ($historial) {
                    $botones = '';

                    // Verificamos si el usuario autenticado es administrador
                    if (auth()->user()->hasAnyRole(['Admin', 'Vendedor Antiguo'])) {
                        if ($historial->estado == 'pendiente') {
                            $botones .= '<button class="btn btn-success btn-sm confirmar-envio" data-id="' . $historial->id . '">Confirmar Envío</button>';
                            $botones .= '<a href="' . route('envios.edit', $historial->id) . '" class="btn btn-primary btn-sm ml-2">Editar</a>';
                            $botones .= '<button class="btn btn-danger btn-sm eliminar-solicitud ml-2" data-id="' . $historial->id . '">Eliminar</button>';
                        }
                    }

                    return $botones;
                })
                ->rawColumns(['acciones', 'productos', 'estado'])
                ->make(true);
        }
        $usuariosDestino = User::all();
        $sucursales = Sucursale::all();

        return view('envios.solicitud', compact('usuariosDestino', 'sucursales'));
    }
    /* public function productosMalEstado(Request $request)
    {
        if ($request->ajax()) {
            $query = InventarioHistorial::with([
                'sucursalOrigen',
                'sucursalDestino',
                'usuarioOrigen',
                'usuarioDestino',
                'productos.producto'
            ])->where('estado', 'mal estado'); // Filtrar solo los registros con estado "mal estado"

            if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
                $query->whereBetween('fecha_envio', [
                    Carbon::parse($request->fecha_inicio)->startOfDay(),
                    Carbon::parse($request->fecha_fin)->endOfDay(),
                ]);
            }

            if ($request->filled('usuario_destino')) {
                $query->where('id_user_destino', $request->usuario_destino);
            }

            return DataTables::of($query)
                ->addColumn('sucursal_origen_nombre', function ($historial) {
                    return $historial->sucursalOrigen
                        ? $historial->sucursalOrigen->nombre
                        : 'Almacén';
                })
                ->addColumn('estado', function ($historial) {
                    return '<span style="display: inline-block; padding: 5px 10px; background-color: red; color: #fff; border-radius: 3px; font-weight: bold;">
                        ' . ucfirst($historial->estado) . '</span>';
                })
                ->addColumn('productos', function ($historial) {
                    // Devuelve los datos formateados con HTML
                    return $historial->productos->map(function ($producto) {
                        return '
                <div style="padding: 5px; margin-bottom: 5px; border: 1px solid #ddd; border-radius: 5px; background: #f9f9f9;">
                    <strong>' . $producto->producto->nombre . '</strong><br>
                    <small style="color: #555;">Cant. Reportada: ' . $producto->cantidad . '</small>
                </div>
            ';
                    })->implode('');
                })
                ->addColumn('productos_text', function ($historial) {
                    // Devuelve los datos en texto plano para búsqueda
                    return $historial->productos->map(function ($producto) {
                        return $producto->producto->nombre . ' (Cantidad: ' . $producto->cantidad . ')';
                    })->implode(', ');
                })
                ->rawColumns(['productos', 'estado']) // Procesa las columnas con HTML
                ->filterColumn('productos', function ($query, $keyword) {
                    // Filtrar la columna de productos_text en lugar de productos
                    $query->whereHas('productos.producto', function ($q) use ($keyword) {
                        $q->where('nombre', 'like', '%' . $keyword . '%');
                    });
                })
                ->addColumn('acciones', function ($historial) {
                    $botones = '';

                    // Verificamos si el usuario autenticado es administrador
                    if (auth()->user()->hasRole('Admin')) {
                        $botones .= '<button class="btn btn-danger btn-sm eliminar-solicitud" data-id="' . $historial->id . '">Eliminar</button>';
                    }

                    return $botones;
                })
                ->rawColumns(['acciones', 'productos', 'estado'])
                ->make(true);
        }

        $usuariosDestino = User::all();
        $sucursales = Sucursale::all();

        return view('envios.productosMalEstado', compact('usuariosDestino', 'sucursales'));
    } */



    public function historial(Request $request)
    {
        if ($request->ajax()) {
            $query = InventarioHistorial::with([
                'sucursalOrigen',
                'sucursalDestino',
                'usuarioOrigen',
                'usuarioDestino',
                'productos.producto'
            ])->where('estado', 'enviado');

            if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
                $query->whereBetween('fecha_envio', [
                    Carbon::parse($request->fecha_inicio)->startOfDay(),
                    Carbon::parse($request->fecha_fin)->endOfDay()
                ]);
            }

            if ($request->filled('usuario_destino')) {
                $query->where('id_user_destino', $request->usuario_destino);
            }

            return DataTables::of($query)
                ->addColumn('sucursal_origen_nombre', fn($historial) => $historial->sucursalOrigen->nombre ?? 'Almacén')
                ->addColumn('sucursal_destino_nombre', fn($historial) => $historial->sucursalDestino->nombre ?? 'N/A')
                ->addColumn('usuario_origen_name', fn($historial) => $historial->usuarioOrigen->name ?? 'N/A')
                ->addColumn('usuario_destino_name', fn($historial) => $historial->usuarioDestino->name ?? 'N/A')
                ->addColumn('estado', function ($historial) {
                    $color = $historial->estado === 'enviado' ? 'green' : 'green';
                    return '<span style="display: inline-block; padding: 5px 10px; background-color: ' . $color . '; color: #000; border-radius: 3px; font-weight: bold;">
                            ' . ucfirst($historial->estado) . '</span>';
                })
                ->addColumn('productos', function ($historial) {
                    return $historial->productos->map(function ($producto) {
                        return '
                        <div style="padding: 5px; margin-bottom: 5px; border: 1px solid #ddd; border-radius: 5px; background: #f9f9f9;">
                            <strong>' . $producto->producto->nombre . '</strong><br>
                            <small style="color: #555;">
                                Cant. Enviada: ' . $producto->cantidad . '<br>
                                Cant. Antes: ' . $producto->cantidad_antes . '<br>
                                Cant. Después: ' . $producto->cantidad_despues . '
                            </small>
                        </div>
                    ';
                    })->implode('');
                })
                ->addColumn('productos_text', function ($historial) {
                    return $historial->productos->map(function ($producto) {
                        return $producto->producto->nombre . ' (Cantidad: ' . $producto->cantidad . ')';
                    })->implode(', ');
                })
                ->rawColumns(['productos', 'estado']) // Procesa las columnas con HTML
                ->filterColumn('productos', function ($query, $keyword) {
                    $query->whereHas('productos.producto', function ($q) use ($keyword) {
                        $q->where('nombre', 'like', '%' . $keyword . '%');
                    });
                })
                ->addColumn('acciones', function ($historial) {
                    return "
                <button class='btn btn-warning btn-sm revertir-envio' data-id='{$historial->id}'>Revertir todo el Envío</button>
                <button class='btn btn-info btn-sm generar-reporte' data-id='{$historial->id}'>Generar Reporte</button>
            ";
                })
                ->rawColumns(['acciones', 'productos', 'estado'])
                ->make(true);
        }
        $usuariosDestino = User::all();
        // Obtener los productos disponibles en el almacén
        $productosAlmacen = Producto::all();
        return view('envios.historial', compact('usuariosDestino', 'productosAlmacen'));
    }



    public function confirmarEnvio($id)
    {
        $historial = InventarioHistorial::findOrFail($id);

        // Cambiar estado a 'enviado'
        $historial->estado = 'enviado';
        $historial->save();

        foreach ($historial->productos as $productoHistorial) {
            $producto = Producto::findOrFail($productoHistorial->id_producto);

            // Obtener inventario en sucursal de origen
            $inventarioOrigen = $producto->inventarios()->where('id_sucursal', $historial->id_sucursal_origen)->first();

            // Verificar stock suficiente antes de descontar
            if (!$inventarioOrigen || $inventarioOrigen->cantidad < $productoHistorial->cantidad) {
                return response()->json(['success' => false, 'message' => 'No hay suficiente stock en la sucursal de origen para confirmar el envío del producto: ' . $producto->nombre]);
            }

            // Descontar stock en la sucursal de origen
            $inventarioOrigen->cantidad -= $productoHistorial->cantidad;
            $inventarioOrigen->save();

            // Manejar inventario en la sucursal destino
            $inventarioDestino = $producto->inventarios()->where('id_sucursal', $historial->id_sucursal)->first();

            if ($inventarioDestino) {
                // Actualizar cantidad en sucursal destino
                $inventarioDestino->cantidad += $productoHistorial->cantidad;
                $inventarioDestino->save();

                // Actualizar cantidad_despues en InventarioProducto
                InventarioProducto::where('id_inventariohistorial', $historial->id)
                    ->where('id_producto', $productoHistorial->id_producto)
                    ->update(['cantidad_despues' => $inventarioDestino->cantidad]);
            } else {
                // Si no existe inventario, crearlo
                $nuevoInventario = Inventario::create([
                    'id_producto' => $productoHistorial->id_producto,
                    'id_sucursal' => $historial->id_sucursal,
                    'cantidad' => $productoHistorial->cantidad,
                ]);

                // Actualizar cantidad_despues con la cantidad enviada
                InventarioProducto::where('id_inventariohistorial', $historial->id)
                    ->where('id_producto', $productoHistorial->id_producto)
                    ->update(['cantidad_despues' => $nuevoInventario->cantidad]);
            }
        }

        // Generar reporte en PDF
        $pdfContent = $this->generarReporteSol($historial);

        // Devolver respuesta JSON con URL de redirección y PDF generado
        return response()->json([
            'success' => true,
            'redirectUrl' => route('envios.solicitud'),
            'reportUrl' => 'data:application/pdf;base64,' . base64_encode($pdfContent),
        ]);
    }




    public function revertirEnvio($id)
    {
        $historial = InventarioHistorial::with(['productos.producto', 'sucursalOrigen', 'sucursalDestino'])->find($id);

        try {
            // Iniciar la transacción
            DB::transaction(function () use ($historial) {
                foreach ($historial->productos as $productoHistorial) {
                    $producto = $productoHistorial->producto;
                    $cantidadRevertida = $productoHistorial->cantidad_despues - $productoHistorial->cantidad_antes;

                    // Descontar del stock de la sucursal destino
                    if ($historial->sucursalDestino) {
                        $inventarioDestino = Inventario::where('id_sucursal', $historial->sucursalDestino->id)
                            ->where('id_producto', $producto->id)
                            ->first();

                        if ($inventarioDestino) {
                            // Validar si hay suficiente stock en la sucursal destino para revertir
                            if ($inventarioDestino->cantidad >= $cantidadRevertida) {
                                $inventarioDestino->cantidad -= $cantidadRevertida;
                                $inventarioDestino->save();
                            } else {
                                throw new \Exception('Stock insuficiente en la sucursal destino para revertir el envío.');
                            }
                        } else {
                            throw new \Exception('El producto no existe en la sucursal destino.');
                        }
                    }

                    // Devolver el stock a la sucursal origen o al inventario general
                    if ($historial->sucursalOrigen) {
                        $inventarioOrigen = Inventario::where('id_sucursal', $historial->sucursalOrigen->id)
                            ->where('id_producto', $producto->id)
                            ->first();

                        if ($inventarioOrigen) {
                            $inventarioOrigen->cantidad += $cantidadRevertida;
                            $inventarioOrigen->save();
                        } else {
                            Inventario::create([
                                'id_sucursal' => $historial->sucursalOrigen->id,
                                'id_producto' => $producto->id,
                                'cantidad' => $cantidadRevertida,
                            ]);
                        }
                    } else {
                        // Si no hay sucursal origen, incrementar el stock general
                        $producto->increment('stock', $cantidadRevertida);
                    }
                }

                // Eliminar los registros de productos en el historial
                foreach ($historial->productos as $productoHistorial) {
                    $productoHistorial->delete();
                }

                // Eliminar el historial de envío
                $historial->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'El historial de envío se revirtió correctamente.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Hubo un error al intentar revertir el envío: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function productosAlmacen()
    {
        $productos = Producto::select('id', 'nombre', 'stock')->where('stock', '>', 0)->get();
        return response()->json($productos);
    }

    public function getUsuariosPorSucursal($sucursal_id)
    {
        // Obtener los usuarios relacionados con la sucursal
        $usuarios = User::whereHas('sucursales', function ($query) use ($sucursal_id) {
            $query->where('sucursal_id', $sucursal_id);
        })->get();

        // Retornar los usuarios como un JSON
        return response()->json($usuarios);
    }
    public function obtenerStockSucursalOrigen($productoId, $sucursalId)
    {
        // Obtener el inventario de la sucursal de origen
        $inventario = Inventario::where('id_sucursal', $sucursalId)
            ->where('id_producto', $productoId)
            ->first();

        // Verificar si existe el inventario y devolver el stock disponible
        if ($inventario) {
            return response()->json(['stock' => $inventario->cantidad]);
        } else {
            return response()->json(['stock' => 0]); // Si no hay inventario, retornar 0
        }
    }


    public function getProductosPorSucursal($sucursal_id)
    {
        // Obtener los productos relacionados con la sucursal a través del inventario
        $productos = Inventario::where('id_sucursal', $sucursal_id)
            ->with('producto') // Cargar la relación del producto
            ->get();

        // Retornar los productos como un JSON
        return response()->json($productos);
    }
    public function obtenerStock($idProducto)
    {
        $producto = Producto::findOrFail($idProducto);
        return response()->json(['stock' => $producto->stock]);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Inventario::with(['producto', 'sucursale', 'sucursalOrigen', 'user', 'userDestino']);

            // Aplicar filtros de fecha si están presentes
            if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
                $query->whereBetween('created_at', [$request->fecha_inicio, $request->fecha_fin]);
            }

            return DataTables::of($query)
                ->addColumn('acciones', function ($envio) {
                    return '<button class="btn btn-warning btn-sm" onclick="showRevertirModal(' . $envio->id . ', ' . $envio->cantidad . ')">
                            <i class="fas fa-undo"></i> Revertir Envío
                        </button>';
                })
                ->editColumn('created_at', function ($envio) {
                    return $envio->created_at ? $envio->created_at->format('d-m-Y') : '';
                })
                ->editColumn('producto.nombre', function ($envio) {
                    return optional($envio->producto)->nombre ?? 'No disponible';
                })
                ->editColumn('sucursalOrigen.nombre', function ($envio) {
                    return optional($envio->sucursalOrigen)->nombre ?? 'Almacen';
                })
                ->editColumn('sucursale.nombre', function ($envio) {
                    return optional($envio->sucursale)->nombre ?? 'No disponible';
                })
                ->editColumn('user.name', function ($envio) {
                    return optional($envio->user)->name ?? 'No disponible';
                })
                ->editColumn('userDestino.name', function ($envio) {
                    return optional($envio->userDestino)->name ?? 'No disponible';
                })
                ->rawColumns(['acciones'])
                ->make(true);
        }
        // Obtener los productos disponibles en el almacén
        $productosAlmacen = Producto::all();
        // Renderizar la vista si no es una solicitud AJAX
        return view('envios.index', compact('productosAlmacen'));
    }

    public function create()
    {
        $productos = Producto::all();
        $sucursales = Sucursale::all();
        $usuarios = User::all();

        // Establecer ID de la sucursal de origen (almacén)
        $idSucursalOrigen = 1; // Cambia esto al ID real de tu almacén

        return view('envios.create', compact('productos', 'sucursales', 'usuarios', 'idSucursalOrigen'));
    }

    public function store(Request $request)
    {
        // Validación de los datos del formulario
        $request->validate([
            'productos' => 'required|array',
            'productos.*.id_producto' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
        ]);

        // Obtener al usuario autenticado
        $user = auth()->user();

        // Verificar si el usuario tiene una sucursal asignada
        if ($user->sucursales->isEmpty()) {
            return back()->with('error', 'El usuario no tiene sucursales asignadas.');
        }

        // Determinar la sucursal destino automáticamente
        $idSucursalDestino = $user->sucursales->first()->id;

        // Determinar la sucursal de origen (almacén o la que elijas)
        $idSucursalOrigen = 1; // O la sucursal que desees usar como origen

        // Configuramos el ID del usuario de origen
        $idUsuarioOrigen = $user->id;

        // Crear un registro en el historial de inventario
        $historial = InventarioHistorial::create([
            'id_sucursal_origen' => $idSucursalOrigen,
            'id_sucursal' => $idSucursalDestino,
            'id_user' => $idUsuarioOrigen,
            'id_user_destino' => $idUsuarioOrigen,
            'fecha_envio' => now(),
            'estado' => 'pendiente',
        ]);

        // Guardar los datos de los productos en el historial
        foreach ($request->productos as $productoData) {
            $producto = Producto::findOrFail($productoData['id_producto']);
            $cantidadSolicitada = $productoData['cantidad'];

            // Consultar el inventario en la sucursal de origen
            $inventarioOrigen = $producto->inventarios()->where('id_sucursal', $idSucursalOrigen)->first();

            if (!$inventarioOrigen || $inventarioOrigen->cantidad < $cantidadSolicitada) {
                return back()->with('error', 'No hay suficiente stock en la sucursal de origen para el producto: ' . $producto->nombre);
            }

            // Consultar la cantidad actual en la sucursal destino
            $inventarioDestino = $producto->inventarios()->where('id_sucursal', $idSucursalDestino)->first();
            $cantidadAntes = $inventarioDestino ? $inventarioDestino->cantidad : 0;

            // Registrar en el historial
            InventarioProducto::create([
                'id_inventariohistorial' => $historial->id,
                'id_producto'            => $producto->id,
                'cantidad'               => $cantidadSolicitada,
                'cantidad_antes'         => $cantidadAntes,
                'cantidad_despues'       => null,
            ]);
        }

        // Redireccionar con un mensaje de éxito
        return redirect()->route('envios.solicitud')->with('success', 'Envío registrado exitosamente.');
    }

    public function transfer()
    {
        $sucursales = Sucursale::all();
        $productos = Producto::all();
        $usuarios = User::all();

        return view('envios.transfer', compact('sucursales', 'productos', 'usuarios'));
    }
    public function storeTransfer(Request $request)
    {
        // Validación de los datos del formulario
        $request->validate([
            'sucursal_origen' => 'required|exists:sucursales,id',
            'sucursal_destino' => 'required|exists:sucursales,id',
            'id_usuario' => 'required|exists:users,id',
            'productos' => 'required|array',
            'productos.*.id_producto' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
        ]);

        $idUsuarioOrigen = request()->user()->id;

        // Crear un registro en el historial de inventario
        $historial = InventarioHistorial::create([
            'id_sucursal_origen' => $request->sucursal_origen,
            'id_sucursal' => $request->sucursal_destino,
            'id_user' => $idUsuarioOrigen,
            'id_user_destino' => $request->id_usuario,
            'fecha_envio' => now(),
            'estado' => 'enviado',
        ]);

        foreach ($request->productos as $productoData) {
            $producto = Producto::findOrFail($productoData['id_producto']);
            $cantidad = $productoData['cantidad'];

            // Verificar si hay suficiente stock en la sucursal de origen
            $inventarioOrigen = Inventario::where('id_sucursal', $request->sucursal_origen)
                ->where('id_producto', $producto->id)
                ->first();

            if (!$inventarioOrigen || $inventarioOrigen->cantidad < $cantidad) {
                return back()->with('error', 'No hay suficiente stock disponible para el producto: ' . $producto->nombre);
            }

            // Descontar del inventario de origen
            $inventarioOrigen->cantidad -= $cantidad;
            $inventarioOrigen->save();

            // Verificar si existe un registro de inventario para la sucursal de destino
            $inventarioDestino = Inventario::where('id_sucursal', $request->sucursal_destino)
                ->where('id_producto', $producto->id)
                ->first();

            if ($inventarioDestino) {
                // Incrementar la cantidad si ya existe un inventario para ese producto en la sucursal de destino
                $cantidadAntesDestino = $inventarioDestino->cantidad; // Cantidad antes del movimiento
                $inventarioDestino->cantidad += $cantidad;
                $inventarioDestino->save();
                // Obtener cantidad después del movimiento en la sucursal de destino
                $cantidadDespuesDestino = $inventarioDestino->cantidad;
            } else {
                // Crear un nuevo registro de inventario si no existe
                Inventario::create([
                    'id_producto' => $producto->id,
                    'id_sucursal' => $request->sucursal_destino,
                    'cantidad' => $cantidad,
                ]);
                // Cantidades antes y después para nuevo registro
                $cantidadAntesDestino = 0;
                $cantidadDespuesDestino = $cantidad;
            }

            // Registrar en historial de productos enviados (solo para destino)
            InventarioProducto::create([
                'id_inventariohistorial' => $historial->id,
                'id_producto' => $productoData['id_producto'],
                'cantidad' => $productoData['cantidad'],
                'cantidad_antes' => $cantidadAntesDestino,
                'cantidad_despues' => $cantidadDespuesDestino,
            ]);
        }

        // Generate PDF content
        try {
            $pdfContent = $this->generarReporte2($request);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al generar el reporte: ' . $e->getMessage());
        }

        // Return response with headers to display PDF
        return response()->json([
            'success' => true,
            'redirectUrl' => route('envios.index'),
            'reportUrl' => 'data:application/pdf;base64,' . base64_encode($pdfContent),
        ]);
    }


    public function revertir(Request $request, $id)
    {
        $envio = Inventario::findOrFail($id); // Verifica que el modelo y su clave primaria sean correctos

        $request->validate([
            'cantidad' => 'required|integer|min:1|max:' . $envio->cantidad,
        ]);

        $cantidad = $request->cantidad;
        $producto = $envio->producto;

        $inventarioDestino = $producto->inventarios()->where('id_sucursal', $envio->id_sucursal)->first();

        if ($inventarioDestino && $inventarioDestino->cantidad >= $cantidad) {
            $inventarioDestino->cantidad -= $cantidad;
            $inventarioDestino->save();
        } else {
            return redirect()->back()->with('error', 'No hay suficiente inventario para esta reversión.');
        }

        $producto->stock += $cantidad;
        $producto->save();

        if ($envio->cantidad == $cantidad) {
            $envio->delete();
        } else {
            $envio->cantidad -= $cantidad;
            $envio->save();
        }

        return redirect()->route('envios.index')->with('success', 'Reversión realizada.');
    }

    public function generarReporteH($id)
    {
        // Instanciar FPDF para impresora térmica
        $pdf = new \FPDF('P', 'mm', array(80, 120)); // Ajuste del tamaño para más contenido
        $pdf->AddPage();

        // Agregar logo (opcional)
        $pdf->Image('images/logo.png', 30, 5, 20, 20, 'PNG');

        // Configuración del encabezado
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(0, 4, utf8_decode("IMPORTADORA MIRANDA S.A."), 0, 1, 'C');
        $pdf->Ln(2); // Espacio adicional
        $pdf->Cell(0, 0, '', 'T'); // Línea separadora
        $pdf->Ln(2);

        // Título del reporte
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(0, 4, utf8_decode("REPORTE DE HISTORIAL DE ENVIOS"), 0, 1, 'C');
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea separadora
        $pdf->Ln(2);

        // Obtener datos del historial específico
        $historial = InventarioHistorial::with([
            'sucursalOrigen',
            'sucursalDestino',
            'usuarioOrigen',
            'usuarioDestino',
            'productos.producto'
        ])->find($id);

        if (!$historial) {
            return response()->json(['error' => 'El historial solicitado no existe.'], 404);
        }

        // Mostrar detalles generales del envío
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(0, 5, utf8_decode("Sucursal Origen: " . ($historial->sucursalOrigen->nombre ?? 'Almacen')), 0, 1, 'C');
        $pdf->Cell(0, 5, utf8_decode("Sucursal Destino: " . ($historial->sucursalDestino->nombre ?? 'N/A')), 0, 1, 'C');
        $pdf->Cell(0, 5, utf8_decode("Usuario Origen: " . ($historial->usuarioOrigen->name ?? 'N/A')), 0, 1, 'C');
        $pdf->Cell(0, 5, utf8_decode("Usuario Destino: " . ($historial->usuarioDestino->name ?? 'N/A')), 0, 1, 'C');
        $pdf->Cell(0, 5, utf8_decode("Fecha: " . ($historial->created_at->format('Y-m-d') ?? 'N/A')), 0, 1, 'C');
        $pdf->Cell(0, 5, utf8_decode("Hora: " . ($historial->created_at->format('H:i:s') ?? 'N/A')), 0, 1, 'C');

        $pdf->Ln(2); // Espacio adicional

        // Encabezado de la tabla
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(40, 5, 'Producto', 1);
        $pdf->Cell(12, 5, 'Antes', 1);
        $pdf->Cell(12, 5, 'Despues', 1);
        $pdf->Ln(); // Nueva línea

        // Agregar productos del historial al reporte
        $pdf->SetFont('Arial', '', 6);
        foreach ($historial->productos as $producto) {
            $productoNombre = $producto->producto->nombre ?? 'Desconocido';
            $cantidadAntes = $producto->cantidad_antes ?? 'N/A';
            $cantidadDespues = $producto->cantidad_despues ?? 'N/A';

            $pdf->Cell(40, 5, utf8_decode($productoNombre), 1);
            $pdf->Cell(12, 5, $cantidadAntes, 1, 0, 'C');
            $pdf->Cell(12, 5, $cantidadDespues, 1, 0, 'C');
            $pdf->Ln(); // Nueva línea
        }

        // Salida del PDF
        return response($pdf->Output('S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="reporte_historial_envios.pdf"');
    }

    private function generarReporte($request)
    {
        // Instanciar FPDF
        $pdf = new FPDF('P', 'mm', array(80, 200)); // Ancho 80mm para impresora térmica
        $pdf->AddPage();
        $pdf->Image('images/logo.png', 30, 5, 20, 20, 'PNG');

        // Configuración del título y encabezado
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(0, 4, utf8_decode("IMPORTADORA MIRANDA S.A."), 0, 1, 'C');

        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T');
        $pdf->Ln(2);

        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(0, 4, utf8_decode("REPORTE DE ENVIOS"), 0, 1, 'C');

        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T');
        $pdf->Ln(2);

        // Detalles del envío
        $pdf->SetFont('Arial', '', 6);
        $sucursalOrigen = Sucursale::find($request->sucursal_origen);
        $sucursalDestino = Sucursale::find($request->id_sucursal);
        $usuarioOrigen = User::find($request->user()->id);
        $usuarioDestino = User::find($request->id_usuario);

        // Agregar detalles al PDF
        $pdf->Cell(0, 5, 'Sucursal Origen: ' . ($sucursalOrigen->nombre ?? 'ALMACEN'), 0, 1, 'C');
        $pdf->Cell(0, 5, 'Sucursal Destino: ' . ($sucursalDestino->nombre ?? 'No disponible'), 0, 1, 'C');
        $pdf->Cell(0, 5, 'Usuario Origen: ' . ($usuarioOrigen->name ?? 'No disponible'), 0, 1, 'C');
        $pdf->Cell(0, 5, 'Usuario Destino: ' . ($usuarioDestino->name ?? 'No disponible'), 0, 1, 'C');
        $pdf->Cell(0, 5, utf8_decode("Fecha y Hora: " . now()), 0, 1, 'C');

        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T');
        $pdf->Ln(2);;
        $pdf->Ln(); // Nueva línea

        // Agregar productos al reporte
        foreach ($request->productos as $productoData) {
            $producto = Producto::find($productoData['id_producto']);
            $inventarioSucursal = $producto->inventarios()->where('id_sucursal', $request->id_sucursal)->first();
            $cantidadAntes = $inventarioSucursal ? $inventarioSucursal->cantidad - $productoData['cantidad'] : 0;
            $cantidadDespues = $inventarioSucursal ? $inventarioSucursal->cantidad : $productoData['cantidad'];


            if ($producto) {
                // Encabezado de la tabla
                $pdf->SetFont('Arial', 'B', 7);
                $pdf->Cell(45, 4, utf8_decode('Producto'), 1, 0, 'C');
                $pdf->Cell(15, 4, utf8_decode('Enviado'), 1, 1, 'C');
                $pdf->SetFont('Arial', '', 6);
                $pdf->Cell(45, 4, utf8_decode($producto->nombre ?? 'No encontrado'),  1, 0, 'C');
                $pdf->Cell(15, 4, $productoData['cantidad'], 1, 1, 'C'); // Mostrar la cantidad enviada
                $pdf->SetFont('Arial', 'B', 7);
                $pdf->Cell(30, 4, utf8_decode('Antes'), 1, 0, 'C');
                $pdf->Cell(30, 4, utf8_decode('Despues'), 1, 1, 'C');
                $pdf->SetFont('Arial', '', 6);
                $pdf->Cell(30, 4, $cantidadAntes, 1, 0, 'C');
                $pdf->Cell(30, 4, $cantidadDespues, 1, 1, 'C');
            } else {
                $pdf->Cell(39, 4, 'No encontrado', 1, 0, 'C');
                $pdf->Cell(12, 4, 'N/A', 1, 1, 'C');
                $pdf->Cell(12, 4, 'N/A', 1, 0, 'C');
                $pdf->Cell(12, 4, 'N/A', 1, 1, 'C');
            }
        }

        // Output PDF as string
        return $pdf->Output('S');
    }



    private function generarReporte2($request)
    {
        // Instanciar FPDF
        $pdf = new FPDF('P', 'mm', array(80, 200)); // Ancho 80mm para impresora térmica
        $pdf->AddPage();
        $pdf->Image('images/logo.png', 30, 5, 20, 20, 'PNG');

        // Configuración del título y encabezado
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(0, 4, utf8_decode("IMPORTADORA MIRANDA S.A."), 0, 1, 'C');

        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T');
        $pdf->Ln(2);

        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(0, 4, utf8_decode("REPORTE DE ENVIOS"), 0, 1, 'C');

        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T');
        $pdf->Ln(2);

        // Detalles del envío
        $pdf->SetFont('Arial', '', 6);
        $sucursalOrigen = Sucursale::find($request->sucursal_origen);
        $sucursalDestino = Sucursale::find($request->sucursal_destino);
        $usuarioOrigen = User::find($request->user()->id);
        $usuarioDestino = User::find($request->id_usuario);

        // Agregar detalles al PDF
        $pdf->Cell(0, 5, 'Sucursal Origen: ' . ($sucursalOrigen->nombre ?? 'ALMACEN'), 0, 1, 'C');
        $pdf->Cell(0, 5, 'Sucursal Destino: ' . ($sucursalDestino->nombre ?? 'No disponible'), 0, 1, 'C');
        $pdf->Cell(0, 5, 'Usuario Origen: ' . ($usuarioOrigen->name ?? 'No disponible'), 0, 1, 'C');
        $pdf->Cell(0, 5, 'Usuario Destino: ' . ($usuarioDestino->name ?? 'No disponible'), 0, 1, 'C');
        $pdf->Cell(0, 5, utf8_decode("Fecha y Hora: " . now()), 0, 1, 'C');

        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T');
        $pdf->Ln(2);


        // Agregar productos al reporte
        foreach ($request->productos as $productoData) {
            $producto = Producto::find($productoData['id_producto']);
            $inventarioDestino = $producto->inventarios()->where('id_sucursal', $request->sucursal_destino)->first();

            // Calcular cantidad antes y después para sucursal destino
            $cantidadAntes = $inventarioDestino ? $inventarioDestino->cantidad - $productoData['cantidad'] : 0;
            $cantidadDespues = $inventarioDestino ? $inventarioDestino->cantidad : $productoData['cantidad'];

            if ($producto) {
                // Encabezado de la tabla
                $pdf->SetFont('Arial', 'B', 7);
                $pdf->Cell(45, 4, utf8_decode('Producto'), 1, 0, 'C');
                $pdf->Cell(15, 4, utf8_decode('Enviado'), 1, 1, 'C');
                $pdf->SetFont('Arial', '', 6);
                $pdf->Cell(45, 4, utf8_decode($producto->nombre ?? 'No encontrado'),  1, 0, 'C');
                $pdf->Cell(15, 4, $productoData['cantidad'], 1, 1, 'C'); // Mostrar la cantidad enviada
                $pdf->SetFont('Arial', 'B', 7);
                $pdf->Cell(30, 4, utf8_decode('Antes'), 1, 0, 'C');
                $pdf->Cell(30, 4, utf8_decode('Despues'), 1, 1, 'C');
                $pdf->SetFont('Arial', '', 6);
                $pdf->Cell(30, 4, $cantidadAntes, 1, 0, 'C');
                $pdf->Cell(30, 4, $cantidadDespues, 1, 1, 'C');
            } else {
                $pdf->Cell(39, 4, 'No encontrado', 1, 0, 'C');
                $pdf->Cell(12, 4, 'N/A', 1, 1, 'C');
                $pdf->Cell(12, 4, 'N/A', 1, 0, 'C');
                $pdf->Cell(12, 4, 'N/A', 1, 1, 'C');
            }
        }

        // Output PDF as string
        return $pdf->Output('S');
    }

    public function report(Request $request)
    {
        $query = Inventario::with(['producto', 'sucursale', 'user']);

        // Aplicar filtros de fecha si están presentes
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('created_at', [$request->fecha_inicio, $request->fecha_fin]);
        }

        $envios = $query->get();

        // Crear el PDF en formato horizontal
        $pdf = new FPDF('L', 'mm', 'A4'); // 'L' indica orientación Landscape (horizontal)
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 14);

        // Encabezado con fondo de color
        $pdf->SetFillColor(50, 50, 200); // Color azul
        $pdf->SetTextColor(255, 255, 255); // Texto blanco
        $pdf->Cell(0, 10, 'Reporte de Envíos de Productos', 0, 1, 'C', true);
        $pdf->Ln(5);

        // Columnas con diseño mejorado
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(0, 0, 0); // Texto negro
        $pdf->SetFillColor(220, 220, 220); // Color gris claro
        $pdf->Cell(70, 10, 'Producto', 0, 0, 'C', true);
        $pdf->Cell(50, 10, 'Sucursal Origen', 0, 0, 'C', true);
        $pdf->Cell(70, 10, 'Sucursal Destino', 0, 0, 'C', true);
        $pdf->Cell(30, 10, 'Cantidad', 0, 0, 'C', true);
        $pdf->Cell(30, 10, 'Fecha', 0, 1, 'C', true);

        // Filas de datos
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetFillColor(240, 240, 240); // Alternar colores de fondo
        $fill = false;

        foreach ($envios as $envio) {
            $pdf->Cell(70, 8, $envio->producto->nombre ?? 'N/A', 0, 0, 'C', $fill);
            $pdf->Cell(50, 8, $envio->sucursalOrigen->nombre ?? 'N/A', 0, 0, 'C', $fill);
            $pdf->Cell(70, 8, $envio->sucursale->nombre ?? 'N/A', 0, 0, 'C', $fill);
            $pdf->Cell(30, 8, $envio->cantidad, 0, 0, 'C', $fill);
            $pdf->Cell(30, 8, $envio->created_at->format('d-m-Y'), 0, 1, 'C', $fill);

            // Cambiar el color de relleno en cada fila
            $fill = !$fill;
        }
        // Salida del PDF
        $pdf->Output('I', 'reporte_envios.pdf');
        exit;
    }
    public function generarReporteSol($historial)
    {
        // Instanciar FPDF
        $pdf = new FPDF('P', 'mm', array(80, 200)); // Tamaño para impresora térmica
        $pdf->AddPage();
        $pdf->Image('images/logo.png', 30, 5, 20, 20, 'PNG'); // Logo

        // Encabezado principal
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(0, 4, utf8_decode("IMPORTADORA MIRANDA S.A."), 0, 1, 'C');
        $pdf->Ln(2);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(0, 4, utf8_decode("REPORTE DE ENVÍOS"), 0, 1, 'C');
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea separadora
        $pdf->Ln(2);

        // Detalles del historial
        $pdf->SetFont('Arial', '', 7);

        $pdf->Cell(0, 5, utf8_decode('Sucursal Origen: ' . ($historial->sucursalOrigen->nombre ?? 'ALMACÉN')), 0, 1, 'C');
        $pdf->Cell(0, 5, utf8_decode('Sucursal Destino: ' . ($historial->sucursalDestino->nombre ?? 'No disponible')), 0, 1, 'C');
        $pdf->Cell(0, 5, utf8_decode('Usuario Origen: ' . ($historial->usuarioOrigen->name ?? 'ALMACÉN')), 0, 1, 'C');
        $pdf->Cell(0, 5, utf8_decode('Usuario Destino: ' . ($historial->usuarioDestino->name ?? 'No disponible')), 0, 1, 'C');

        $pdf->Cell(0, 5, utf8_decode("Fecha: " . now()), 0, 1, 'C');
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea separadora
        $pdf->Ln(2);

        $pdf->SetFont('Arial', '', 6);
        foreach ($historial->productos as $productoHistorial) {
            $productoNombre = $productoHistorial->producto->nombre ?? 'Desconocido';
            $productoCantidad = $productoHistorial->cantidad ?? 0; // Cantidad del producto
            $cantidadAntes = $productoHistorial->cantidad_antes ?? 0; // Valor predeterminado 0 si no existe
            $cantidadDespues = $cantidadAntes + $productoCantidad; // Suma calculada
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Cell(45, 4, utf8_decode('Producto'), 1, 0, 'C');
            $pdf->Cell(15, 4, utf8_decode('Enviado'), 1, 1, 'C');
            $pdf->SetFont('Arial', '', 6);
            $pdf->Cell(45, 4, utf8_decode($productoNombre), 1, 0, 'C');
            $pdf->Cell(15, 4, $productoCantidad, 1, 1, 'C');
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Cell(30, 4, utf8_decode('Antes'), 1, 0, 'C');
            $pdf->Cell(30, 4, utf8_decode('Después'), 1, 1, 'C');
            $pdf->SetFont('Arial', '', 6);
            $pdf->Cell(30, 4, $cantidadAntes, 1, 0, 'C');
            $pdf->Cell(30, 4, $cantidadDespues, 1, 1, 'C');
        }
        // Salida como cadena
        return $pdf->Output('S');
    }


    public function getProductosPorSucursales($sucursalId)
    {
        $productos = Inventario::with('producto')
            ->where('id_sucursal', $sucursalId)
            ->get();

        return response()->json($productos);
    }

    public function productosMalEstado(Request $request)
    {
        if ($request->ajax()) {
            $query = InventarioHistorial::with([
                'sucursalOrigen',
                'sucursalDestino',
                'usuarioOrigen',
                'usuarioDestino',
                'productos.producto'
            ])->whereIn('estado', ['mal estado', 'mal estado confirmado'])
                ->orderByDesc('fecha_envio'); // Filtrar solo los registros con estado "mal estado"

            if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
                $query->whereBetween('fecha_envio', [
                    Carbon::parse($request->fecha_inicio),
                    Carbon::parse($request->fecha_fin),
                ]);
            }

            if ($request->filled('usuario_destino')) {
                $query->where('id_user_destino', $request->usuario_destino);
            }

            return DataTables::of($query)
                ->addColumn('sucursal_origen_nombre', function ($historial) {
                    return $historial->sucursalOrigen
                        ? $historial->sucursalOrigen->nombre
                        : 'Almacén';
                })
                ->addColumn('estado', function ($historial) {
                    $color = $historial->estado === 'mal estado' ? 'orange' : 'red';
                    $texto = ucfirst($historial->estado);

                    return '<span style="display: inline-block; padding: 2px 5px; background-color: ' . $color . '; color: #fff; border-radius: 3px; font-size: 12px; font-weight: bold;">
                                ' . $texto . '
                            </span>';
                })
                ->addColumn('productos', function ($historial) {
                    // Devuelve los datos formateados con HTML
                    return $historial->productos->map(function ($producto) {
                        return '
                    <div style="padding: 5px; margin-bottom: 5px; border: 1px solid #ddd; border-radius: 5px; background: #f9f9f9;">
                        <strong>' . $producto->producto->nombre . '</strong><br>
                        <small style="color: #555;">Cantidad Reportada: ' . $producto->cantidad . '</small><br>
                        <small style="color: #555;">Antes en Sucursal: ' . $producto->cantidad_antes . '</small><br>
                        <small style="color: #555;">Después en Sucursal: ' . $producto->cantidad_despues . '</small>
                    </div>
                ';
                    })->implode('');
                })
                ->addColumn('productos_text', function ($historial) {
                    // Devuelve los datos en texto plano para búsqueda
                    return $historial->productos->map(function ($producto) {
                        return $producto->producto->nombre . ' (Cantidad: ' . $producto->cantidad . ')';
                    })->implode(', ');
                })
                ->rawColumns(['productos', 'estado']) // Procesa las columnas con HTML
                ->filterColumn('productos', function ($query, $keyword) {
                    // Filtrar la columna de productos_text en lugar de productos
                    $query->whereHas('productos.producto', function ($q) use ($keyword) {
                        $q->where('nombre', 'like', '%' . $keyword . '%');
                    });
                })
                ->addColumn('acciones', function ($historial) {
                    $botones = '';

                    if (auth()->user()->hasRole('Admin')) {

                        // Texto, color e ícono dinámico
                        $estado = $historial->estado;
                        $textoBoton = $estado === 'mal estado confirmado' ? 'Generar reporte' : 'Confirmar';
                        $colorBoton = $estado === 'mal estado confirmado' ? 'btn-primary' : 'btn-success';
                        $icono = $estado === 'mal estado confirmado' ? 'fas fa-file-pdf' : 'fas fa-check-circle';

                        $botones .= '<button class="btn ' . $colorBoton . ' btn-sm confirmar-mal-estado" 
                                            data-id="' . $historial->id . '" 
                                            data-estado="' . $estado . '">
                                            <i class="' . $icono . '"></i> ' . $textoBoton . '
                                     </button>';

                        // Botón revertir si el estado es "mal estado"
                        if ($estado === 'mal estado') {
                            $botones .= '<button class="btn btn-warning btn-sm revertir-recepcion" data-id="' . $historial->id . '">
                                            <i class="fas fa-undo-alt"></i> Revertir
                                         </button>';
                        }
                    }

                    return $botones;
                })


                ->rawColumns(['acciones', 'productos', 'estado'])
                ->make(true);
        }

        $usuariosorigen = User::all();
        $sucursales = Sucursale::all();

        return view('envios.productosMalEstado', compact('usuariosorigen', 'sucursales'));
    }

    public function recepcionMalEstado(Request $request)
    {
        // Validar los datos enviados
        $request->validate([
            'sucursal_origen' => 'required|exists:sucursales,id',
            'productos' => 'required|array|min:1',
            'productos.*.cantidad' => 'required|integer|min:1',
        ]);

        $sucursalOrigenId = $request->sucursal_origen;

        // Crear un único registro en InventarioHistorial
        $historial = InventarioHistorial::create([
            'id_sucursal_origen' => $sucursalOrigenId,
            'id_sucursal' => 1, // Sucursal fija (Sucursal 1)
            'id_user' => auth()->id(),
            'id_user_destino' => auth()->id(),
            'fecha_envio' => now(),
            'estado' => 'mal estado',
        ]);

        // Iterar sobre los productos y asociarlos al historial
        foreach ($request->productos as $productoId => $productoData) {
            $cantidad = $productoData['cantidad'];

            // Verificar el stock en la sucursal de origen
            $inventario = Inventario::where('id_sucursal', $sucursalOrigenId)
                ->where('id_producto', $productoId)
                ->first();

            if (!$inventario || $inventario->cantidad < $cantidad) {
                return response()->json(['message' => 'Stock insuficiente para el producto: ' . $productoId], 400);
            }

            // Descontar el stock
            $inventario->cantidad -= $cantidad;
            $inventario->save();

            // Crear el registro en InventarioProducto asociado al historial
            InventarioProducto::create([
                'id_inventariohistorial' => $historial->id,
                'id_producto' => $productoId,
                'cantidad' => $cantidad,
                'cantidad_antes' => $inventario->cantidad + $cantidad,
                'cantidad_despues' => $inventario->cantidad,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Recepción registrada correctamente.']);
    }

    public function confirmarMalEstado($id)
    {
        try {
            // Buscar el historial por ID
            $historial = InventarioHistorial::findOrFail($id);

            // Cambiar el estado a "mal estado confirmado"
            $historial->estado = 'mal estado confirmado';
            $historial->save();

            // Generar el reporte en PDF
            $pdfContent = $this->generarReporteMalEstado($historial);

            // Devolver respuesta JSON con URL de redirección y PDF generado
            return response()->json([
                'success' => true,
                'message' => 'El estado del producto ha sido confirmado como "mal estado confirmado".',
                'reportUrl' => 'data:application/pdf;base64,' . base64_encode($pdfContent),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al confirmar el estado: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function generarReporteMalEstadoSolo($id)
    {
        try {
            $historial = InventarioHistorial::findOrFail($id);

            $pdfContent = $this->generarReporteMalEstado($historial);

            return response()->json([
                'success' => true,
                'reportUrl' => 'data:application/pdf;base64,' . base64_encode($pdfContent),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el reporte: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function generarReporteMalEstado($historial)
    {
        // Instanciar FPDF
        $pdf = new FPDF('P', 'mm', array(80, 200)); // Tamaño para impresora térmica
        $pdf->AddPage();
        $pdf->Image('images/logo.png', 30, 5, 20, 20, 'PNG'); // Logo

        // Encabezado principal
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(0, 4, utf8_decode("IMPORTADORA MIRANDA S.A."), 0, 1, 'C');
        $pdf->Ln(2);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(0, 4, utf8_decode("REPORTE DE MAL ESTADO CONFIRMADO"), 0, 1, 'C');
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea separadora
        $pdf->Ln(2);

        // Detalles del historial
        $pdf->SetFont('Arial', '', 7);

        $pdf->Cell(0, 5, utf8_decode('Sucursal Origen: ' . ($historial->sucursalOrigen->nombre ?? 'ALMACÉN')), 0, 1, 'C');
        $pdf->Cell(0, 5, utf8_decode('Usuario Receptor: ' . ($historial->usuarioOrigen->name ?? 'ALMACÉN')), 0, 1, 'C');
        $pdf->Cell(0, 5, utf8_decode("Fecha: " . now()), 0, 1, 'C');
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea separadora
        $pdf->Ln(2);

        // Detalles de productos con mal estado
        $pdf->SetFont('Arial', '', 6);
        foreach ($historial->productos as $productoHistorial) {
            $productoNombre = $productoHistorial->producto->nombre ?? 'Desconocido';
            $productoCantidad = $productoHistorial->cantidad ?? 0;
            $cantidadAntes = $productoHistorial->cantidad_antes ?? 0;
            $cantidadDespues = $cantidadAntes - $productoCantidad;

            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Cell(45, 4, utf8_decode('Producto'), 1, 0, 'C');
            $pdf->Cell(15, 4, utf8_decode('Cantidad'), 1, 1, 'C');
            $pdf->SetFont('Arial', '', 6);
            $pdf->Cell(45, 4, utf8_decode($productoNombre), 1, 0, 'C');
            $pdf->Cell(15, 4, $productoCantidad, 1, 1, 'C');
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Cell(30, 4, utf8_decode('Antes en Sucursal'), 1, 0, 'C');
            $pdf->Cell(30, 4, utf8_decode('Después en Sucursal'), 1, 1, 'C');
            $pdf->SetFont('Arial', '', 6);
            $pdf->Cell(30, 4, $cantidadAntes, 1, 0, 'C');
            $pdf->Cell(30, 4, $cantidadDespues, 1, 1, 'C');
        }

        // Salida como cadena
        return $pdf->Output('S');
    }

    public function revertirRecepcion($id)
    {
        try {
            // Buscar el historial por ID
            $historial = InventarioHistorial::with('productos')->findOrFail($id);

            // Verificar que el estado sea "mal estado"
            if ($historial->estado !== 'mal estado') {
                return response()->json(['message' => 'Solo se pueden revertir recepciones con estado "mal estado".'], 400);
            }

            // Iterar sobre los productos asociados al historial
            foreach ($historial->productos as $productoHistorial) {
                $productoId = $productoHistorial->id_producto;
                $cantidad = $productoHistorial->cantidad;

                // Obtener el inventario de la sucursal de origen
                $inventario = Inventario::where('id_sucursal', $historial->id_sucursal_origen)
                    ->where('id_producto', $productoId)
                    ->first();

                if ($inventario) {
                    // Devolver la cantidad al inventario
                    $inventario->cantidad += $cantidad;
                    $inventario->save();
                } else {
                    // Si no existe el inventario, crearlo
                    Inventario::create([
                        'id_sucursal' => $historial->id_sucursal_origen,
                        'id_producto' => $productoId,
                        'cantidad' => $cantidad,
                    ]);
                }
            }

            // Cambiar el estado del historial a "revertido"
            $historial->estado = 'revertido';
            $historial->save();

            return response()->json(['success' => true, 'message' => 'Recepción revertida correctamente.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al revertir la recepción: ' . $e->getMessage()], 500);
        }
    }

    public function generarPDF(Request $request)
    {
        // Construcción de la consulta
        $query = InventarioHistorial::with([
            'sucursalOrigen',
            'sucursalDestino',
            'usuarioOrigen',
            'usuarioDestino',
            'productos.producto'
        ])->whereIn('estado', ['mal estado', 'mal estado confirmado']);

        // Filtrado por fechas
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('fecha_envio', [
                Carbon::parse($request->fecha_inicio)->startOfDay(),
                Carbon::parse($request->fecha_fin)->endOfDay(),
            ]);
        }

        // Filtrado por usuario destino
        if ($request->filled('usuario_destino')) {
            $query->where('id_user_destino', $request->usuario_destino);
        }

        $resultados = $query->get();

        // Crear instancia de FPDF
        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();

        // Configuración de la fuente para el título
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'Reporte de Productos en Mal Estado', 0, 1, 'C');
        $pdf->Ln(5);

        // Agregar la fecha de generación
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 10, 'Fecha de Generacion: ' . now()->format('d/m/Y H:i:s'), 0, 1, 'C');
        $pdf->Ln(10);

        // Cabecera de la tabla principal con estilo
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(41, 128, 185); // Color de fondo azul
        $pdf->SetTextColor(255, 255, 255); // Color del texto blanco
        $pdf->Cell(70, 8, 'Sucursal Origen', 1, 0, 'C', true);
        $pdf->Cell(40, 8, 'Usuario Receptor', 1, 0, 'C', true);
        $pdf->Cell(40, 8, 'Fecha Envio', 1, 0, 'C', true);
        $pdf->Cell(40, 8, 'Estado', 1, 1, 'C', true);

        // Datos de la tabla principal
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor(0, 0, 0); // Texto negro

        foreach ($resultados as $historial) {
            // Mostrar la fila principal (historial)
            $pdf->Cell(70, 8, $historial->sucursalOrigen->nombre ?? 'ALMACÉN', 1, 0, 'C');
            $pdf->Cell(40, 8, $historial->usuarioDestino->name ?? 'N/A', 1, 0, 'C');
            $pdf->Cell(40, 8, $historial->fecha_envio, 1, 0, 'C');
            $pdf->Cell(40, 8, ucfirst($historial->estado), 1, 1, 'C');

            // Añadir productos con un estilo distinto y más moderno
            foreach ($historial->productos as $producto) {
                $pdf->SetFont('Arial', '', 7);

                // Establecer color de fondo gris claro para las filas de productos
                $pdf->SetFillColor(236, 240, 241);  // Gris claro para las filas de productos
                $pdf->Cell(70, 6, 'Producto: ' . ($producto->producto->nombre ?? 'Desconocido'), 1, 0, 'C', true);
                $pdf->Cell(40, 6, 'Cantidad Recepcionada: ' . $producto->cantidad, 1, 0, 'C', true);
                $pdf->Cell(40, 6, 'Cantidad Antes: ' . $producto->cantidad_antes, 1, 0, 'C', true);
                $pdf->Cell(40, 6, 'Cantidad Despues: ' . $producto->cantidad_despues, 1, 1, 'C', true);
            }

            // Espaciado entre registros
            $pdf->Ln(2);
        }

        // Salto de línea final
        $pdf->Ln(5);

        // Output PDF to browser
        $pdf->Output();
        exit;
    }





    public function productosAlmacene(Request $request)
    {
        if ($request->ajax()) {
            $query = InventarioHistorial::with([
                'sucursalOrigen',
                'sucursalDestino',
                'usuarioOrigen',
                'usuarioDestino',
                'productos.producto'
            ])->where('estado', 'enviado a almacen')
            ->orderByDesc('fecha_envio');; // Filtrar solo los registros con estado "enviado a almacen"

            if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
                $query->whereBetween('fecha_envio', [
                    Carbon::parse($request->fecha_inicio),
                    Carbon::parse($request->fecha_fin),
                ]);
            }


            if ($request->filled('usuario_destino')) {
                $query->where('id_user_destino', $request->usuario_destino);
            }

            return DataTables::of($query)
                ->addColumn('sucursal_origen_nombre', function ($historial) {
                    return $historial->sucursalOrigen
                        ? $historial->sucursalOrigen->nombre
                        : 'Almacén';
                })

                ->addColumn('estado', function ($historial) {
                    return '<span style="display: inline-block; padding: 5px 10px; background-color: green; color: #fff; border-radius: 3px; font-weight: bold;">
                        ' . ucfirst($historial->estado) . '</span>';
                })
                ->addColumn('productos', function ($historial) {
                    // Devuelve los datos formateados con HTML
                    return $historial->productos->map(function ($producto) {
                        return '
                <div style="padding: 5px; margin-bottom: 5px; border: 1px solid #ddd; border-radius: 5px; background: #f9f9f9;">
                    <strong>' . $producto->producto->nombre . '</strong><br>
                    <small style="color: #555;">Cantidad Enviada: ' . $producto->cantidad . '</small><br>
                    <small style="color: #555;">Antes en Sucursal: ' . $producto->cantidad_antes . '</small><br>
                    <small style="color: #555;">Después en Sucursal: ' . $producto->cantidad_despues . '</small>
                </div>
            ';
                    })->implode('');
                })
                ->addColumn('productos_text', function ($historial) {
                    // Devuelve los datos en texto plano para búsqueda
                    return $historial->productos->map(function ($producto) {
                        return $producto->producto->nombre . ' (Cantidad: ' . $producto->cantidad . ')';
                    })->implode(', ');
                })
                ->rawColumns(['productos', 'estado']) // Procesa las columnas con HTML
                ->filterColumn('productos', function ($query, $keyword) {
                    // Filtrar la columna de productos_text en lugar de productos
                    $query->whereHas('productos.producto', function ($q) use ($keyword) {
                        $q->where('nombre', 'like', '%' . $keyword . '%');
                    });
                })
                ->addColumn('acciones', function ($historial) {
                    $botones = '';

                    // Verificamos si el usuario autenticado es administrador
                    if (auth()->user()->hasRole('Admin')) {
                        $botones .= '<button class="btn btn-success btn-sm generar-reporte" data-id="' . $historial->id . '">Generar Reporte</button>';
                    }

                    return $botones;
                })
                ->rawColumns(['acciones', 'productos', 'estado'])
                ->make(true);
        }

        $usuariosDestino = User::all();
        $sucursales = Sucursale::all();

        return view('envios.productosAlmacen', compact('usuariosDestino', 'sucursales'));
    }
    public function recepcionAlmacen(Request $request)
    {
        // Validar los datos enviados
        $request->validate([
            'sucursal_origen' => 'required|exists:sucursales,id',
            'productos' => 'required|array|min:1',
            'productos.*.cantidad' => 'required|integer|min:1',
        ]);

        $sucursalOrigenId = $request->sucursal_origen;

        // Crear un único registro en InventarioHistorial
        $historial = InventarioHistorial::create([
            'id_sucursal_origen' => $sucursalOrigenId,
            'id_sucursal' => 1,
            'id_user' => auth()->id(),
            'id_user_destino' => auth()->id(),
            'fecha_envio' => now(),
            'estado' => 'enviado a almacen',
        ]);

        // Iterar sobre los productos y asociarlos al historial
        foreach ($request->productos as $productoId => $productoData) {
            $cantidad = $productoData['cantidad'];

            // Verificar el stock en la sucursal de origen
            $inventario = Inventario::where('id_sucursal', $sucursalOrigenId)
                ->where('id_producto', $productoId)
                ->first();

            if (!$inventario || $inventario->cantidad < $cantidad) {
                return response()->json(['message' => 'Stock insuficiente para el producto: ' . $productoId], 400);
            }

            // Descontar el stock de la sucursal de origen
            $inventario->cantidad -= $cantidad;
            $inventario->save();

            // Actualizar el stock del producto en la tabla productos (almacén)
            $producto = Producto::find($productoId);
            $producto->stock += $cantidad; // Sumar la cantidad al stock del almacén
            $producto->save();

            // Crear el registro en InventarioProducto asociado al historial
            InventarioProducto::create([
                'id_inventariohistorial' => $historial->id,
                'id_producto' => $productoId,
                'cantidad' => $cantidad,
                'cantidad_antes' => $inventario->cantidad + $cantidad,
                'cantidad_despues' => $inventario->cantidad,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Recepción registrada correctamente en el almacén.']);
    }
    public function generarReporteAlmacen($id)
    {
        try {
            $historial = InventarioHistorial::findOrFail($id);

            $pdfContent = $this->generarReporteEnvioAlmacen($historial);

            return response()->json([
                'success' => true,
                'reportUrl' => 'data:application/pdf;base64,' . base64_encode($pdfContent),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el reporte: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function generarReporteEnvioAlmacen($historial)
    {
        // Instanciar FPDF
        $pdf = new FPDF('P', 'mm', array(80, 200)); // Tamaño para impresora térmica
        $pdf->AddPage();
        $pdf->Image('images/logo.png', 30, 5, 20, 20, 'PNG'); // Logo

        // Encabezado principal
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(0, 4, utf8_decode("IMPORTADORA MIRANDA S.A."), 0, 1, 'C');
        $pdf->Ln(2);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(0, 4, utf8_decode("REPORTE DE ENVIO AL ALMACEN "), 0, 1, 'C');
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea separadora
        $pdf->Ln(2);

        // Detalles del historial
        $pdf->SetFont('Arial', '', 7);

        $pdf->Cell(0, 5, utf8_decode('Sucursal Origen: ' . ($historial->sucursalOrigen->nombre ?? 'ALMACÉN')), 0, 1, 'C');
        $pdf->Cell(0, 5, utf8_decode('Usuario : ' . ($historial->usuarioOrigen->name ?? 'ALMACÉN')), 0, 1, 'C');
        $pdf->Cell(0, 5, utf8_decode("Fecha: " . now()), 0, 1, 'C');
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea separadora
        $pdf->Ln(2);

        // Detalles de productos con mal estado
        $pdf->SetFont('Arial', '', 6);
        foreach ($historial->productos as $productoHistorial) {
            $productoNombre = $productoHistorial->producto->nombre ?? 'Desconocido';
            $productoCantidad = $productoHistorial->cantidad ?? 0;
            $cantidadAntes = $productoHistorial->cantidad_antes ?? 0;
            $cantidadDespues = $cantidadAntes - $productoCantidad;

            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Cell(45, 4, utf8_decode('Producto'), 1, 0, 'C');
            $pdf->Cell(15, 4, utf8_decode('Cantidad'), 1, 1, 'C');
            $pdf->SetFont('Arial', '', 6);
            $pdf->Cell(45, 4, utf8_decode($productoNombre), 1, 0, 'C');
            $pdf->Cell(15, 4, $productoCantidad, 1, 1, 'C');
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Cell(30, 4, utf8_decode('Antes en Sucursal'), 1, 0, 'C');
            $pdf->Cell(30, 4, utf8_decode('Después en Sucursal'), 1, 1, 'C');
            $pdf->SetFont('Arial', '', 6);
            $pdf->Cell(30, 4, $cantidadAntes, 1, 0, 'C');
            $pdf->Cell(30, 4, $cantidadDespues, 1, 1, 'C');
        }

        // Salida como cadena
        return $pdf->Output('S');
    }
    public function generarReporteAlmacenPdf(Request $request)
    {
        // Construcción de la consulta
        $query = InventarioHistorial::with([
            'sucursalOrigen',
            'sucursalDestino',
            'usuarioOrigen',
            'usuarioDestino',
            'productos.producto'
        ])->whereIn('estado', ['Enviado a almacen']);

        // Filtrado por fechas
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('fecha_envio', [
                Carbon::parse($request->fecha_inicio)->startOfDay(),
                Carbon::parse($request->fecha_fin)->endOfDay(),
            ]);
        }

        // Filtrado por usuario destino
        if ($request->filled('usuario_destino')) {
            $query->where('id_user_destino', $request->usuario_destino);
        }

        $resultados = $query->get();

        // Crear instancia de FPDF
        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();

        // Configuración de la fuente para el título
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'Reporte de Productos enviados al Almacen', 0, 1, 'C');
        $pdf->Ln(5);

        // Agregar la fecha de generación
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 10, 'Fecha de Generacion: ' . now()->format('d/m/Y H:i:s'), 0, 1, 'C');
        $pdf->Ln(10);

        // Cabecera de la tabla principal con estilo
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(41, 128, 185); // Color de fondo azul
        $pdf->SetTextColor(255, 255, 255); // Color del texto blanco
        $pdf->Cell(70, 8, 'Sucursal Origen', 1, 0, 'C', true);
        $pdf->Cell(40, 8, 'Usuario', 1, 0, 'C', true);
        $pdf->Cell(40, 8, 'Fecha Envio', 1, 0, 'C', true);
        $pdf->Cell(40, 8, 'Estado', 1, 1, 'C', true);

        // Datos de la tabla principal
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor(0, 0, 0); // Texto negro

        foreach ($resultados as $historial) {
            // Mostrar la fila principal (historial)
            $pdf->Cell(70, 8, $historial->sucursalOrigen->nombre ?? 'ALMACÉN', 1, 0, 'C');
            $pdf->Cell(40, 8, $historial->usuarioDestino->name ?? 'N/A', 1, 0, 'C');
            $pdf->Cell(40, 8, $historial->fecha_envio, 1, 0, 'C');
            $pdf->Cell(40, 8, ucfirst($historial->estado), 1, 1, 'C');

            // Añadir productos con un estilo distinto y más moderno
            foreach ($historial->productos as $producto) {
                $pdf->SetFont('Arial', '', 7);

                // Establecer color de fondo gris claro para las filas de productos
                $pdf->SetFillColor(236, 240, 241);  // Gris claro para las filas de productos
                $pdf->Cell(70, 6, 'Producto: ' . ($producto->producto->nombre ?? 'Desconocido'), 1, 0, 'C', true);
                $pdf->Cell(40, 6, 'Cantidad Enviada: ' . $producto->cantidad, 1, 0, 'C', true);
                $pdf->Cell(40, 6, 'Cantidad Antes: ' . $producto->cantidad_antes, 1, 0, 'C', true);
                $pdf->Cell(40, 6, 'Cantidad Despues: ' . $producto->cantidad_despues, 1, 1, 'C', true);
            }

            // Espaciado entre registros
            $pdf->Ln(2);
        }

        // Salto de línea final
        $pdf->Ln(5);

        // Output PDF to browser
        $pdf->Output();
        exit;
    }
}
