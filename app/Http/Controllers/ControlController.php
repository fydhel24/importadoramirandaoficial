<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\Categoria;
use App\Models\Cupo;
use App\Models\Inventario;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\Sucursale;
use App\Models\User;
use App\Models\Venta;
use App\Models\VentaProducto;
use Codedge\Fpdf\Fpdf\Fpdf;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use JsonException;

class ControlController extends Controller
{
    public function index()
    {
        // Obtener todas las sucursales
        $sucursales = Sucursale::all();
        return view('control.index', compact('sucursales'));
    }

    public function productos(Request $request, $id)
    {
        $sucur = Sucursale::find($id);

        // Inicializamos la consulta base para los productos
        $productosQuery = Inventario::where('id_sucursal', $id)
            ->join('productos', 'productos.id', '=', 'inventario.id_producto') // Hacemos el join con productos
            ->with(['producto.categoria', 'producto.marca', 'producto.fotos'])
            ->orderByDesc('inventario.favorito') // Primero por los productos favoritos
            ->orderByDesc('productos.estado')   // Luego por estado de los productos
            ->orderBy('productos.created_at', 'desc'); // Y finalmente por fecha de creación

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;

            // Si hay una búsqueda, aplicamos el filtro de búsqueda
            $productosQuery->whereHas('producto', function ($query) use ($search) {
                $query->where('nombre', 'like', "%$search%");
            });
        }

        // Ejecutamos la consulta y paginamos los productos
        $productos = $productosQuery->paginate(9);

        // Se cargan las categorías y marcas
        $categorias = Categoria::all();
        $marcas = Marca::all();

        // Filtrar usuarios que están asignados a la sucursal con el id correspondiente y que tienen los roles específicos
        $users = User::whereHas('sucursales', function ($query) use ($id) {
            $query->where('sucursal_id', $id);
        })
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['Vendedor', 'Vendedor Antiguo', 'Encargado de pedidos']); // Filtramos por los roles
            })
            ->where('status', 'active')
            ->get(); // Obtener solo los usuarios con los roles específicos de la sucursal

        // Recorremos los productos para obtener los datos adicionales
        foreach ($productos as $inventario) {
            $inventario->producto->stock_actual = $inventario->producto->getStockActual();
            $inventario->producto->stock_sucursal = $inventario->cantidad;
        }

        // Si es una solicitud AJAX, retornamos los productos y los enlaces de paginación
        if ($request->ajax()) {
            return response()->json([
                'productos' => $productos,
            ]);
        }
        session(['venta_token' => Str::random(40)]);
        // Obtener id del vendedor por defecto
        $defaultVendedor = User::where('email', 'sucursal1@gmail.com')->first();
        $defaultVendedorId = $defaultVendedor ? $defaultVendedor->id : null;

        // Retornamos la vista con los productos y los usuarios filtrados
        return view('control.pro', compact('sucur', 'productos', 'id', 'categorias', 'marcas', 'users', 'defaultVendedorId'));
    }
    public function productosmoderna(Request $request, $id)
    {
        $sucur = Sucursale::find($id);

        // Inicializamos la consulta base para los productos
        $productosQuery = Inventario::where('id_sucursal', $id)
            ->join('productos', 'productos.id', '=', 'inventario.id_producto') // Hacemos el join con productos
            ->with(['producto.categoria', 'producto.marca', 'producto.fotos'])
            ->orderByDesc('inventario.favorito') // Primero por los productos favoritos
            ->orderByDesc('productos.estado')   // Luego por estado de los productos
            ->orderBy('productos.created_at', 'desc'); // Y finalmente por fecha de creación

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;

            // Si hay una búsqueda, aplicamos el filtro de búsqueda
            $productosQuery->whereHas('producto', function ($query) use ($search) {
                $query->where('nombre', 'like', "%$search%");
            });
        }

        // Ejecutamos la consulta y paginamos los productos
        $productos = $productosQuery->paginate(9);

        // Se cargan las categorías y marcas
        $categorias = Categoria::all();
        $marcas = Marca::all();

        // Filtrar usuarios que están asignados a la sucursal con el id correspondiente y que tienen los roles específicos
        $users = User::whereHas('sucursales', function ($query) use ($id) {
            $query->where('sucursal_id', $id);
        })
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['Vendedor', 'Vendedor Antiguo', 'Encargado de pedidos']); // Filtramos por los roles
            })
            ->where('status', 'active')
            ->orWhereIn('email', ['JHOELSURCO2@GMAIL.COM'])
            ->get(); // Obtener solo los usuarios con los roles específicos de la sucursal

        // Incluir al usuario autenticado
        $loggedUser = auth()->user();
        if ($loggedUser) {
            // Verificamos si el usuario autenticado no está en la lista de usuarios
            if (!$users->contains('id', $loggedUser->id)) {
                $users->prepend($loggedUser); // Lo añadimos al inicio de la colección
            }
        }

        // Recorremos los productos para obtener los datos adicionales
        foreach ($productos as $inventario) {
            $inventario->producto->stock_actual = $inventario->producto->getStockActual();
            $inventario->producto->stock_sucursal = $inventario->cantidad;
        }

        // Si es una solicitud AJAX, retornamos los productos y los enlaces de paginación
        if ($request->ajax()) {
            return response()->json([
                'productos' => $productos,
            ]);
        }

        // Retornamos la vista con los productos y los usuarios filtrados
        return view('control.prov2', compact('sucur', 'productos', 'id', 'categorias', 'marcas', 'users'));
    }
    public function apiProductosModerna(Request $request, $id)
    {
        $sucursal = Sucursale::find($id);

        if (!$sucursal) {
            return response()->json([
                'success' => false,
                'message' => 'Sucursal no encontrada'
            ], 404);
        }

        // Consulta base sin autenticación
        $productosQuery = Inventario::where('id_sucursal', $id)
            ->join('productos', 'productos.id', '=', 'inventario.id_producto')
            ->with(['producto.categoria', 'producto.marca', 'producto.fotos'])
            ->orderByDesc('inventario.favorito')
            ->orderByDesc('productos.estado')
            ->orderBy('productos.created_at', 'desc');

        // Filtro de búsqueda (si viene en la URL ?search=)
        if ($request->filled('search')) {
            $search = $request->search;
            $productosQuery->whereHas('producto', function ($query) use ($search) {
                $query->where('nombre', 'like', "%$search%")
                    ->orWhere('id', 'like', "%$search%");
            });
        }

        // Paginación (10 por página)
        $productos = $productosQuery->paginate(10);

        // Añadir stock adicional
        foreach ($productos as $inventario) {
            if ($inventario->producto) {
                $inventario->producto->stock_actual = $inventario->producto->getStockActual();
                $inventario->producto->stock_sucursal = $inventario->cantidad;
            }
        }

        // Categorías y marcas
        $categorias = Categoria::all();
        $marcas = Marca::all();

        // Ya no se filtran usuarios ni se requiere login
        // Eliminamos completamente auth()->user() y los roles
        $users = [];

        // Respuesta JSON pública
        return response()->json([
            'success' => true,
            'message' => 'Productos obtenidos correctamente (pública)',
            'sucursal' => $sucursal,
            'categorias' => $categorias,
            'marcas' => $marcas,
            'usuarios' => $users,
            'productos' => $productos,
        ]);
    }
    public function apiFinModernoAntiguo(Request $request)
    {
        try {
            // ✅ Validación de los datos recibidos
            $validated = $request->validate([
                'nombre_cliente' => 'required|string',
                'costo_total' => 'required|numeric',
                'productos' => 'required|json',
                'id_sucursal' => 'required|numeric',
                'ci' => 'nullable|string',
                'tipo_pago' => 'required|string',
                'garantia' => 'nullable|in:sin garantia,con garantia',
                'descuento' => 'nullable|numeric',
                'id_user' => 'required|numeric',
                'pagado' => 'required|numeric',
                'pagado_qr' => 'nullable|numeric'
            ]);

            $productos = json_decode($request->productos, true);
            $descuentoTotal = $request->descuento ?? 0;

            // ✅ Determinar los montos de pago
            $efectivo = null;
            $qr = null;

            if ($request->tipo_pago === 'Efectivo') {
                $efectivo = $request->costo_total;
            } elseif ($request->tipo_pago === 'QR') {
                $qr = $request->costo_total;
            } elseif ($request->tipo_pago === 'Efectivo y QR') {
                $efectivo = $request->pagado ?? 0;
                $qr = $request->pagado_qr ?? 0;
            }

            // ✅ Crear la venta
            $venta = Venta::create([
                'fecha'        => now(),
                'nombre_cliente' => $request->nombre_cliente,
                'costo_total'  => $request->costo_total,
                'id_user'      => $request->id_user,
                'ci'           => $request->ci,
                'descuento'    => $descuentoTotal,
                'tipo_pago'    => $request->tipo_pago,
                'id_sucursal'  => $request->id_sucursal,
                'garantia'     => $request->garantia,
                'efectivo'     => $efectivo,
                'qr'           => $qr,
                'pagado'       => $request->pagado,
                'estado'       => 'RESERVA',
            ]);

            // ✅ Registrar los productos de la venta
            foreach ($productos as $producto) {
                $productoExistente = Producto::find($producto['id']);

                if (!$productoExistente) {
                    return response()->json([
                        'success' => false,
                        'message' => "El producto con ID {$producto['id']} no existe."
                    ], 404);
                }

                $inventario = Inventario::where('id_producto', $producto['id'])
                    ->where('id_sucursal', $request->id_sucursal)
                    ->first();

                if (!$inventario) {
                    return response()->json([
                        'success' => false,
                        'message' => "No hay inventario para el producto {$productoExistente->nombre}."
                    ], 400);
                }

                if ($inventario->cantidad < $producto['cantidad']) {
                    return response()->json([
                        'success' => false,
                        'message' => "Stock insuficiente para el producto {$productoExistente->nombre}."
                    ], 400);
                }

                // Registrar en tabla venta_producto
                VentaProducto::create([
                    'id_venta' => $venta->id,
                    'id_producto' => $producto['id'],
                    'cantidad' => $producto['cantidad'],
                    'precio_unitario' => $producto['precio'],
                    'descuento' => 0,
                ]);

                // Actualizar stock del inventario
                $inventario->cantidad -= $producto['cantidad'];
                $inventario->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Venta registrada correctamente',
                'venta' => $venta,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Errores de validación
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Errores generales
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al registrar la venta',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    public function showFavoritosForm($id)
    {
        // Obtener todos los productos con su stock y si están en favoritos, ordenados por fecha de creación
        $productosDisponibles = Producto::orderBy('created_at', 'desc')->get()->map(function ($producto) use ($id) {
            // Verificar si el producto ya está marcado como favorito para la sucursal y el usuario
            $favorito = Inventario::where('id_producto', $producto->id)
                ->where('id_sucursal', $id)
                ->where('favorito', true)
                ->exists();

            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'favorito' => $favorito // Agregar si el producto está como favorito
            ];
        });

        // Devolver la vista con los productos y la información de favoritos
        return view('control.favoritos', compact('id', 'productosDisponibles'));
    }
    public function agregarFavorito(Request $request, $id)
    {
        // Validar los datos de la solicitud
        $request->validate([
            'producto_id' => 'required|exists:productos,id', // Validar que el producto exista
        ]);

        $productoId = $request->producto_id;
        $userId = $request->user_id;

        // Buscar si el producto ya está en inventario para el usuario y la sucursal
        $producto = Producto::find($productoId);
        $inventarioExistente = Inventario::where('id_producto', $productoId)
            ->where('id_user', $userId)
            ->where('id_sucursal', $id)
            ->first();

        // Si el producto ya está en el inventario y es favorito, retornar un mensaje de alerta
        if ($inventarioExistente && $inventarioExistente->favorito) {
            return response()->json(['message' => 'Este producto ya está marcado como favorito en esta sucursal.'], 400);
        }

        // Si el producto ya está en inventario pero no es favorito, marcarlo como favorito
        if ($inventarioExistente) {
            $inventarioExistente->favorito = true;
            $inventarioExistente->save();
            return response()->json(['message' => 'Producto agregado a favoritos.'], 200);
        }

        // Si no existe en inventario, creamos un nuevo registro
        $inventario = new Inventario();
        $inventario->id_producto = $productoId;
        $inventario->id_sucursal = $id;
        $inventario->cantidad = 0;
        $inventario->id_user = $userId;
        $inventario->favorito = true;
        $inventario->save();

        return response()->json(['message' => 'Producto agregado a favoritos.'], 200);
    }
    public function quitarFavorito(Request $request, $id)
    {
        // Validar los datos de la solicitud
        $request->validate([
            'producto_id' => 'required|exists:productos,id', // Validar que el producto exista
        ]);

        $productoId = $request->producto_id;
        $userId = $request->user_id;

        // Buscar si el producto está marcado como favorito para el usuario y la sucursal
        $producto = Producto::find($productoId);
        $inventarioExistente = Inventario::where('id_producto', $productoId)
            ->where('id_user', $userId)
            ->where('id_sucursal', $id)
            ->first();

        // Si el producto está en el inventario y es favorito, quitarlo de favoritos
        if ($inventarioExistente && $inventarioExistente->favorito) {
            $inventarioExistente->favorito = false; // Quitar el favorito
            $inventarioExistente->save();
            return response()->json(['message' => 'Producto quitado de favoritos.'], 200);
        }

        // Si no está en inventario o no es favorito
        return response()->json(['message' => 'Este producto no está marcado como favorito.'], 400);
    }

    public function realizarInventario(Request $request, $id)
    {
        // Validate the request
        $request->validate([
            'id_producto' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
        ]);

        $producto = Producto::find($request->id_producto);

        // Check if the requested quantity exceeds the total available stock
        $totalStock = Inventario::where('id_producto', $producto->id)->sum('cantidad') + $producto->stock;
        if ($request->cantidad > $totalStock) {
            return back()->withErrors(['cantidad' => 'La cantidad solicitada excede el stock disponible del producto.']);
        }

        // Update the product stock
        $producto->stock -= $request->cantidad;
        $producto->save();

        // Find or create the inventory record for the product in the branch
        $inventarioExistente = Inventario::where('id_producto', $producto->id)
            ->where('id_sucursal', $id)
            ->first();

        if ($inventarioExistente) {
            // If it exists, add the quantity
            $inventarioExistente->cantidad += $request->cantidad;
            $inventarioExistente->save();
        } else {
            // If it does not exist, create a new record
            $inventario = new Inventario();
            $inventario->id_producto = $producto->id;
            $inventario->id_sucursal = $id; // ID of the branch
            $inventario->cantidad = $request->cantidad; // The quantity of the order
            $inventario->id_user = auth()->id(); // ID of the logged-in user
            $inventario->save();
        }

        return redirect()->route('control.productos', $id)->with('success', 'Inventario actualizado correctamente.');
    }

    public function showInventarioForm($id)
    {
        // Obtain all products with their stock
        $productosDisponibles = Producto::all()->map(function ($producto) {
            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'Stock' => $producto->stock,
            ];
        });

        // Return the view with the necessary data
        return view('control.inventario', compact('id', 'productosDisponibles'));
    }

    public function fin(Request $request)
    {
        // Validar token único
        $token = $request->input('venta_token');
        $sessionToken = session('venta_token');

        if ($token !== $sessionToken) {
            return response()->json([
                'success' => false,
                'message' => 'Venta ya procesada o token inválido'
            ]);
        }
        // Eliminar el token para evitar reuso
        session()->forget('venta_token');
        // Validation of the input
        $request->validate([
            'nombre_cliente' => 'required|string',
            'costo_total' => 'required|numeric',
            'productos' => 'required|json',
            'id_sucursal' => 'required|numeric', // Ensure this is validated as numeric
            'ci' => 'string', // Optional, adjust according to your needs
            'tipo_pago' => 'required|string', // Validate the payment method
            'garantia' => 'nullable|in:sin garantia,con garantia', // Validar garantía
            'descuento' => 'required', // Agregado para el campo descuento
            'id_user' => 'required',
            'pagado' => 'required',

        ]);

        // Decode the products from the JSON
        $productos = json_decode($request->productos, true);
        $descuentoTotal = $request->descuento ?? 0; // Valor del descuento, por defecto es 0
        // Inicializar las variables para el pago
        $efectivo = null;
        $qr = null;

        // Determinar qué valor guardar dependiendo del tipo de pago
        if ($request->tipo_pago == 'Efectivo') {
            $efectivo = $request->costo_total; // Guardar costo_total como pago en efectivo
        }

        if ($request->tipo_pago == 'QR') {
            $qr = $request->costo_total; // Guardar costo_total como pago por QR
        }

        if ($request->tipo_pago == 'Efectivo y QR') {
            $efectivo = $request->pagado ?? 0; // Guardar monto pagado en efectivo
            $qr = $request->pagado_qr ?? 0;   // Guardar monto pagado por QR
        }

        // Create the sale
        $venta = Venta::create([
            'fecha' => now(),
            'nombre_cliente' => $request->nombre_cliente,
            'costo_total' => $request->costo_total,
            'id_user' => $request->id_user,
            'ci' => $request->ci, // Optional, adjust according to your needs
            'descuento' => $descuentoTotal, // Guardar el descuento en la venta
            'tipo_pago' => $request->tipo_pago, // Save the selected payment method
            'id_sucursal' => $request->id_sucursal, // Save the ID of the sucursal
            'garantia' => $request->garantia ?? null, // Agregar el campo garantía
            'efectivo' => $efectivo, // Almacenar el valor de pago en efectivo
            'qr' => $qr,
            'pagado'  => $request->pagado,
        ]);

        // Save the products in venta_producto and update the stock
        foreach ($productos as $producto) {
            // Check if the product exists
            $productoExistente = Producto::find($producto['id']);
            if (!$productoExistente) {
                return redirect()->back()->withErrors(['error' => 'The product with ID ' . $producto['id'] . ' does not exist.']);
            }

            // Check if there is enough stock in the sucursal for the product
            $inventario = Inventario::where('id_producto', $producto['id'])
                ->where('id_sucursal', $request->id_sucursal)
                ->first();

            if (!$inventario) {
                return redirect()->back()->withErrors(['error' => 'No inventory available for the product: ' . $productoExistente->nombre]);
            }

            // Check if there is enough stock available in the sucursal
            if ($inventario->cantidad < $producto['cantidad']) {
                return redirect()->back()->withErrors(['error' => 'Not enough stock in the sucursal for the product: ' . $productoExistente->nombre]);
            }

            // Create the record in venta_producto
            VentaProducto::create([
                'id_venta' => $venta->id,
                'id_producto' => $producto['id'],
                'cantidad' => $producto['cantidad'],
                'precio_unitario' => $producto['precio'],
                'descuento' => 0, // You can add the discount here if necessary
                //'descuento' => $descuentoTotal,
            ]);

            // Deduct the quantity from the inventory of the sucursal
            $inventario->cantidad -= $producto['cantidad'];
            $inventario->save(); // Save the changes in the sucursal inventory
        }

        // Return response
        return response()->json(['success' => true]);
    }

    public function finmoderno(Request $request, $idventa)
    {
        Log::info('Datos recibidos en /fin/moderno:', $request->all());

        try {
            // Validation of the input
            $validated = $request->validate([
                'nombre_cliente' => 'required|string|max:255',
                'costo_total' => 'required|numeric|min:0',
                'productos' => 'required|json',
                'id_sucursal' => 'required|numeric',
                'ci' => 'nullable|string|max:20',
                'tipo_pago' => 'required|in:Efectivo,QR,Efectivo y QR',
                'garantia' => 'nullable|in:sin garantia,con garantia',
                'descuento' => 'nullable|numeric|min:0',
                'id_user' => 'required|numeric|exists:users,id',
                'pagado' => 'nullable|numeric|min:0',
                'pagado_qr' => 'nullable|numeric|min:0',
            ]);

            // Decode the products from the JSON
            $productos = json_decode($request->productos, true);
            if (empty($productos)) {
                Log::error('Lista de productos vacía');
                return response()->json(['success' => false, 'message' => 'La lista de productos está vacía'], 400);
            }

            // Validate product structure
            foreach ($productos as $producto) {
                if (!isset($producto['id'], $producto['cantidad'], $producto['precio'], $producto['nombre'])) {
                    Log::error('Formato de producto inválido:', $producto);
                    return response()->json(['success' => false, 'message' => 'Formato de productos inválido'], 400);
                }
            }

            $descuentoTotal = $request->descuento ?? 0;
            $efectivo = null;
            $qr = null;

            // Determine payment values based on tipo_pago
            if ($request->tipo_pago == 'Efectivo') {
                $efectivo = $request->pagado;
                if ($efectivo < $request->costo_total) {
                    Log::error('Monto pagado en efectivo insuficiente:', ['pagado' => $efectivo, 'costo_total' => $request->costo_total]);
                    return response()->json(['success' => false, 'message' => 'El monto pagado en efectivo es insuficiente'], 400);
                }
            } elseif ($request->tipo_pago == 'QR') {
                $qr = $request->pagado_qr;
                if ($qr < $request->costo_total) {
                    Log::error('Monto pagado por QR insuficiente:', ['pagado_qr' => $qr, 'costo_total' => $request->costo_total]);
                    return response()->json(['success' => false, 'message' => 'El monto pagado por QR es insuficiente'], 400);
                }
            } elseif ($request->tipo_pago == 'Efectivo y QR') {
                $efectivo = $request->pagado ?? 0;
                $qr = $request->pagado_qr ?? 0;
                if (($efectivo + $qr) < $request->costo_total) {
                    Log::error('Suma de montos pagados insuficiente:', ['efectivo' => $efectivo, 'qr' => $qr, 'costo_total' => $request->costo_total]);
                    return response()->json(['success' => false, 'message' => 'La suma de los montos pagados es insuficiente'], 400);
                }
            }

            // Create the sale
            $venta = Venta::create([
                'fecha' => now(),
                'nombre_cliente' => $request->nombre_cliente,
                'costo_total' => $request->costo_total,
                'id_user' => $request->id_user,
                'ci' => $request->ci,
                'descuento' => $descuentoTotal,
                'tipo_pago' => $request->tipo_pago,
                'id_sucursal' => $request->id_sucursal,
                'garantia' => $request->garantia,
                'efectivo' => $efectivo,
                'qr' => $qr,
                'pagado' => $request->pagado,
                'estado' => 'RECOJO'
            ]);

            // Save the products in venta_producto and update the stock
            foreach ($productos as $producto) {
                // Check if the product exists
                $productoExistente = Producto::find($producto['id']);
                if (!$productoExistente) {
                    Log::error('Producto no encontrado:', ['id' => $producto['id']]);
                    return response()->json(['success' => false, 'message' => 'El producto con ID ' . $producto['id'] . ' no existe'], 400);
                }

                // Check if there is enough stock in the sucursal
                $inventario = Inventario::where('id_producto', $producto['id'])
                    ->where('id_sucursal', $request->id_sucursal)
                    ->first();

                if (!$inventario) {
                    Log::error('No hay inventario disponible para el producto:', ['id' => $producto['id'], 'sucursal' => $request->id_sucursal]);
                    return response()->json(['success' => false, 'message' => 'No hay inventario disponible para el producto: ' . $productoExistente->nombre], 400);
                }

                if ($inventario->cantidad < $producto['cantidad']) {
                    Log::error('Stock insuficiente para el producto:', ['id' => $producto['id'], 'cantidad_solicitada' => $producto['cantidad'], 'cantidad_disponible' => $inventario->cantidad]);
                    return response()->json(['success' => false, 'message' => 'No hay suficiente stock para el producto: ' . $productoExistente->nombre], 400);
                }

                // Create the record in venta_producto
                VentaProducto::create([
                    'id_venta' => $venta->id,
                    'id_producto' => $producto['id'],
                    'cantidad' => $producto['cantidad'],
                    'precio_unitario' => $producto['precio'],
                    'descuento' => 0 // Adjust if you want to distribute descuentoTotal
                ]);

                // Deduct the quantity from the inventory
                $inventario->cantidad -= $producto['cantidad'];
                $inventario->save();
            }

            Log::info('Venta procesada correctamente:', ['venta_id' => $venta->id]);
            return response()->json(['success' => true, 'message' => 'Venta procesada correctamente']);
        } catch (\Exception $e) {
            Log::error('Error al procesar venta:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function ventaRapida()
    {
        // Obtener el usuario logueado
        $user = auth()->user();

        // Obtener la primera sucursal asociada al usuario (puedes cambiar esto si prefieres otra lógica)
        $sucursal = $user->sucursales->first();  // Obtener la primera sucursal asociada al usuario

        // Verificar si el usuario tiene sucursales asociadas
        if (!$sucursal) {
            // Si el usuario no tiene sucursal asociada, redirigir con un mensaje de error
            return redirect()->route('home')->with('success', 'NO TIENES UNA SUCURSAL ASIGNADA . Informale al admininistrador para que te asigne una sucursal ');
        }

        // Redirigir al usuario a los productos de su sucursal
        return redirect()->route('control.productos', ['id' => $sucursal->id]);
    }
    public function ventarapidamoderna()
    {
        // Obtener el usuario logueado
        $user = auth()->user();

        // Obtener la primera sucursal asociada al usuario (puedes cambiar esto si prefieres otra lógica)
        $sucursal = $user->sucursales->first();  // Obtener la primera sucursal asociada al usuario

        // Verificar si el usuario tiene sucursales asociadas
        if (!$sucursal) {
            // Si el usuario no tiene sucursal asociada, redirigir con un mensaje de error
            return redirect()->route('home')->with('success', 'NO TIENES UNA SUCURSAL ASIGNADA . Informale al admininistrador para que te asigne una sucursal ');
        }

        // Redirigir al usuario a los productos de su sucursal
        return redirect()->route('control.productos.moderna', ['id' => $sucursal->id]);
    }
    public function generarReporte($id)
    {
        // Obtener todos los inventarios de la sucursal específica
        $sucursal = Sucursale::find($id);

        // Verifica si la sucursal existe
        if (!$sucursal) {
            return response()->json(['error' => 'Sucursal no encontrada'], 404);
        }

        // Recupera los inventarios de la sucursal
        $inventarios = Inventario::where('id_sucursal', $id)->with('producto')->get();

        // Crear un nuevo PDF
        $pdf = new Fpdf();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 14);

        // Título del reporte
        $pdf->Cell(0, 10, 'Reporte de Productos en Sucursal: ' . $sucursal->nombre, 0, 1, 'C'); // Aquí usas el nombre de la sucursal
        $pdf->Ln(10);

        // Agregar encabezados de la tabla
        $pdf->SetFillColor(135, 206, 250); // Color de fondo celeste para el encabezado
        $pdf->SetTextColor(0, 0, 0); // Color del texto
        $pdf->SetFont('Arial', 'B', 12);

        // Encabezados de la tabla
        $pdf->Cell(150, 10, 'Producto', 0, 0, 'C', true); // Sin borde
        $pdf->Cell(40, 10, 'Cantidad', 0, 1, 'C', true); // Sin borde

        // Restablecer colores de texto y fuente
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 12);

        // Alternar colores de las filas
        $fill = false; // Inicializa el estado de relleno
        foreach ($inventarios as $inventario) {
            // Usar utf8_decode para asegurar que los nombres se muestren correctamente
            $nombreProducto = utf8_decode($inventario->producto->nombre);

            // Cambiar el color de fondo de la fila
            if ($fill) {
                $pdf->SetFillColor(230, 230, 250); // Color de fondo alternativo (lavanda)
            } else {
                $pdf->SetFillColor(255, 255, 255); // Color de fondo blanco
            }
            $fill = !$fill; // Alternar el estado de relleno

            // Celdas de la tabla (sin líneas verticales)
            $pdf->Cell(150, 10, $nombreProducto, 0, 0, 'L', true); // Sin borde
            $pdf->Cell(40, 10, $inventario->cantidad, 0, 1, 'C', true); // Sin borde
        }

        // Salida del PDF
        $pdf->Output();
        exit;
    }
    public function finmodernoantiguo(Request $request)
    {
        // Validation of the input
        $request->validate([
            'nombre_cliente' => 'required|string',
            'costo_total' => 'required|numeric',
            'productos' => 'required|json',
            'id_sucursal' => 'required|numeric', // Ensure this is validated as numeric
            'ci' => 'string', // Optional, adjust according to your needs
            'tipo_pago' => 'required|string', // Validate the payment method
            'garantia' => 'nullable|in:sin garantia,con garantia', // Validar garantía
            'descuento' => 'required', // Agregado para el campo descuento
            'id_user' => 'required',
            'pagado' => 'required',

        ]);

        // Decode the products from the JSON
        $productos = json_decode($request->productos, true);
        $descuentoTotal = $request->descuento ?? 0; // Valor del descuento, por defecto es 0
        // Inicializar las variables para el pago
        $efectivo = null;
        $qr = null;

        // Determinar qué valor guardar dependiendo del tipo de pago
        if ($request->tipo_pago == 'Efectivo') {
            $efectivo = $request->costo_total; // Guardar costo_total como pago en efectivo
        }

        if ($request->tipo_pago == 'QR') {
            $qr = $request->costo_total; // Guardar costo_total como pago por QR
        }

        if ($request->tipo_pago == 'Efectivo y QR') {
            $efectivo = $request->pagado ?? 0; // Guardar monto pagado en efectivo
            $qr = $request->pagado_qr ?? 0;   // Guardar monto pagado por QR
        }

        // Create the sale
        $venta = Venta::create([
            'fecha' => now(),
            'nombre_cliente' => $request->nombre_cliente,
            'costo_total' => $request->costo_total,
            'id_user' => $request->id_user,
            'ci' => $request->ci, // Optional, adjust according to your needs
            'descuento' => $descuentoTotal, // Guardar el descuento en la venta
            'tipo_pago' => $request->tipo_pago, // Save the selected payment method
            'id_sucursal' => $request->id_sucursal, // Save the ID of the sucursal
            'garantia' => $request->garantia ?? null, // Agregar el campo garantía
            'efectivo' => $efectivo, // Almacenar el valor de pago en efectivo
            'qr' => $qr,
            'pagado'  => $request->pagado,
            'estado'         => 'RECOJO'
        ]);

        // Save the products in venta_producto and update the stock
        foreach ($productos as $producto) {
            // Check if the product exists
            $productoExistente = Producto::find($producto['id']);
            if (!$productoExistente) {
                return redirect()->back()->withErrors(['error' => 'The product with ID ' . $producto['id'] . ' does not exist.']);
            }

            // Check if there is enough stock in the sucursal for the product
            $inventario = Inventario::where('id_producto', $producto['id'])
                ->where('id_sucursal', $request->id_sucursal)
                ->first();

            if (!$inventario) {
                return redirect()->back()->withErrors(['error' => 'No inventory available for the product: ' . $productoExistente->nombre]);
            }

            // Check if there is enough stock available in the sucursal
            if ($inventario->cantidad < $producto['cantidad']) {
                return redirect()->back()->withErrors(['error' => 'Not enough stock in the sucursal for the product: ' . $productoExistente->nombre]);
            }

            // Create the record in venta_producto
            VentaProducto::create([
                'id_venta' => $venta->id,
                'id_producto' => $producto['id'],
                'cantidad' => $producto['cantidad'],
                'precio_unitario' => $producto['precio'],
                'descuento' => 0, // You can add the discount here if necessary
                //'descuento' => $descuentoTotal,
            ]);

            // Deduct the quantity from the inventory of the sucursal
            //  $inventario->cantidad -= $producto['cantidad'];
            $inventario->save(); // Save the changes in the sucursal inventory
        }

        // Return response
        return response()->json(['success' => true]);
    }
    public function finmodernoActualizar(Request $request, $idventa)
    {
        Log::info('Datos recibidos en /fin/moderno actualizar:', $request->all());

        try {
            // Validación de los datos de entrada
            $validated = $request->validate([
                'nombre_cliente' => 'required|string|max:255',
                'costo_total' => 'required|numeric|min:0',
                'productos' => 'required|json',
                'id_sucursal' => 'required|numeric',
                'ci' => 'nullable|string|max:20',
                'tipo_pago' => 'required|in:Efectivo,QR,Efectivo y QR',
                'garantia' => 'nullable|in:sin garantia,con garantia',
                'descuento' => 'nullable|numeric|min:0',
                'id_user' => 'required|numeric|exists:users,id',
                'pagado' => 'nullable|numeric|min:0',
                'pagado_qr' => 'nullable|numeric|min:0',
            ]);

            // Decodificar los productos del JSON
            $productos = json_decode($request->productos, true);
            if (empty($productos)) {
                Log::error('Lista de productos vacía');
                return response()->json(['success' => false, 'message' => 'La lista de productos está vacía'], 400);
            }

            // Validar la estructura de los productos
            foreach ($productos as $producto) {
                if (!isset($producto['id'], $producto['cantidad'], $producto['precio'], $producto['nombre'])) {
                    Log::error('Formato de producto inválido:', $producto);
                    return response()->json(['success' => false, 'message' => 'Formato de productos inválido'], 400);
                }
            }

            // Buscar la venta existente por idventa
            $venta = Venta::find($idventa);
            if (!$venta) {
                Log::error('Venta no encontrada:', ['idventa' => $idventa]);
                return response()->json(['success' => false, 'message' => 'La venta no existe'], 404);
            }

            // Calcular el total del descuento (si lo hay)
            $descuentoTotal = $request->descuento ?? 0;

            // Inicializar variables para el pago
            $efectivo = null;
            $qr = null;

            // Calcular los pagos según el tipo de pago
            if ($request->tipo_pago == 'Efectivo') {
                $efectivo = $request->pagado;
                if ($efectivo < $request->costo_total) {
                    Log::error('Monto pagado en efectivo insuficiente:', ['pagado' => $efectivo, 'costo_total' => $request->costo_total]);
                    return response()->json(['success' => false, 'message' => 'El monto pagado en efectivo es insuficiente'], 400);
                }
            } elseif ($request->tipo_pago == 'QR') {
                $qr = $request->pagado_qr;
                if ($qr < $request->costo_total) {
                    Log::error('Monto pagado por QR insuficiente:', ['pagado_qr' => $qr, 'costo_total' => $request->costo_total]);
                    return response()->json(['success' => false, 'message' => 'El monto pagado por QR es insuficiente'], 400);
                }
            } elseif ($request->tipo_pago == 'Efectivo y QR') {
                $efectivo = $request->pagado ?? 0;
                $qr = $request->pagado_qr ?? 0;
                if (($efectivo + $qr) < $request->costo_total) {
                    Log::error('Suma de montos pagados insuficiente:', ['efectivo' => $efectivo, 'qr' => $qr, 'costo_total' => $request->costo_total]);
                    return response()->json(['success' => false, 'message' => 'La suma de los montos pagados es insuficiente'], 400);
                }
            }

            // Actualizar la venta con los nuevos datos
            $venta->update([
                'nombre_cliente' => $request->nombre_cliente,
                'costo_total' => $request->costo_total,
                'id_user' => $request->id_user,
                'ci' => $request->ci,
                'descuento' => $descuentoTotal,
                'tipo_pago' => $request->tipo_pago,
                'id_sucursal' => $request->id_sucursal,
                'garantia' => $request->garantia,
                'efectivo' => $efectivo,
                'qr' => $qr,
                'pagado' => $request->pagado,
                'estado' => 'NORMAL', // Puedes cambiar esto si es necesario
            ]);

            // Obtener los productos antiguos de la venta
            $productosAntiguos = VentaProducto::where('id_venta', $venta->id)->get();


            // Eliminar los productos antiguos de venta_producto
            VentaProducto::where('id_venta', $venta->id)->delete();

            // Agregar los nuevos productos y actualizar el inventario
            foreach ($productos as $producto) {
                // Verificar si el producto existe
                $productoExistente = Producto::find($producto['id']);
                if (!$productoExistente) {
                    Log::error('Producto no encontrado:', ['id' => $producto['id']]);
                    return response()->json(['success' => false, 'message' => 'El producto con ID ' . $producto['id'] . ' no existe'], 400);
                }

                // Verificar si hay suficiente stock en la sucursal
                $inventario = Inventario::where('id_producto', $producto['id'])
                    ->where('id_sucursal', $request->id_sucursal)
                    ->first();

                if (!$inventario) {
                    Log::error('No hay inventario disponible para el producto:', ['id' => $producto['id'], 'sucursal' => $request->id_sucursal]);
                    return response()->json(['success' => false, 'message' => 'No hay inventario disponible para el producto: ' . $productoExistente->nombre], 400);
                }

                if ($inventario->cantidad < $producto['cantidad']) {
                    Log::error('Stock insuficiente para el producto:', ['id' => $producto['id'], 'cantidad_solicitada' => $producto['cantidad'], 'cantidad_disponible' => $inventario->cantidad]);
                    return response()->json(['success' => false, 'message' => 'No hay suficiente stock para el producto: ' . $productoExistente->nombre], 400);
                }

                // Crear el registro en venta_producto con los nuevos productos
                VentaProducto::create([
                    'id_venta' => $venta->id,
                    'id_producto' => $producto['id'],
                    'cantidad' => $producto['cantidad'],
                    'precio_unitario' => $producto['precio'],
                    'descuento' => 0, // Puedes agregar el descuento aquí si es necesario
                ]);

                // Descontar la cantidad del inventario
                $inventario->cantidad -= $producto['cantidad'];
                $inventario->save();
            }

            Log::info('Venta actualizada correctamente:', ['venta_id' => $venta->id]);
            return response()->json(['success' => true, 'message' => 'Venta actualizada correctamente']);
        } catch (\Exception $e) {
            Log::error('Error al actualizar venta:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
