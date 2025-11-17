<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\Promocione;
use App\Models\Producto;
use App\Models\PromocionProducto;
use App\Models\Sucursale;
use App\Models\User;
use App\Models\Venta;
use App\Models\VentaProducto;
use Dotenv\Validator;
use FPDF;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class PromocionController extends Controller
{
     public function index()
    {
        // Obtener usuarios con roles específicos y estado activo, o el email de JHOEL
        $users = User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['Vendedor', 'Vendedor Antiguo', 'Encargado de pedidos']);
            })
            ->where('status', 'active')
            ->orWhereIn('email', ['JHOELSURCO2@GMAIL.COM'])
            ->get();

        // Obtener todas las promociones con sus relaciones
        $promociones = Promocione::with('productos', 'productos.inventarios', 'sucursal', 'usuario')->get();

        // Pasar promociones y usuarios a la vista
        return view('promociones.index', compact('promociones', 'users'))->with('showMenu', false);
    }
    public function create()
    {
        $productos = Producto::all();
        $sucursales = Sucursale::all();
        return view('promociones.create', compact('productos', 'sucursales'));
    }
    public function edit($id)
    {
        $promocion = Promocione::with('productos')->findOrFail($id);
        $productos = Producto::all();
        $sucursales = Sucursale::all();

        return view('promociones.edit', compact('promocion', 'productos', 'sucursales'));
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'precio_promocion' => 'required|numeric',
            'id_sucursal' => 'required|exists:sucursales,id',
            'productos' => 'required|array',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $promocion = Promocione::findOrFail($id);
        $promocion->update([
            'nombre' => $request->nombre,
            'precio_promocion' => $request->precio_promocion,
            'id_sucursal' => $request->id_sucursal,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'estado' => $request->has('estado'),
        ]);

        // Sincronizar los productos
        $promocion->productos()->detach();
        foreach ($request->productos as $producto) {
            PromocionProducto::create([
                'id_promocion' => $promocion->id,
                'id_producto' => $producto['id'],
                'cantidad' => $producto['cantidad'],
                'precio_unitario' => $producto['precio'],
            ]);
        }

        return redirect()->route('promociones.index')->with('success', 'Promoción actualizada correctamente.');
    }
    public function destroy($id)
    {
        $promocion = Promocione::findOrFail($id);
        $promocion->delete();

        return redirect()->route('promociones.index')->with('success', 'Promoción eliminada correctamente.');
    }

    public function store(Request $request)
    {
        // Decodificar el campo productos si es necesario
        $productos = json_decode($request->input('productos'), true);

        if (!is_array($productos)) {
            return redirect()->back()->withErrors(['productos' => 'El formato de productos es inválido.'])->withInput();
        }

        // Reemplazar productos en la solicitud con el array decodificado
        $request->merge(['productos' => $productos]);

        // Validar los datos
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'precio_promocion' => 'required|numeric',
            'id_sucursal' => 'required|exists:sucursales,id',
            'productos' => 'required|array',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0.01',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        try {
            // Crear la promoción
            $promocion = Promocione::create([
                'nombre' => $validatedData['nombre'],
                'precio_promocion' => $validatedData['precio_promocion'],
                'id_sucursal' => $validatedData['id_sucursal'],
                'id_usuario' => auth()->id(),
                'fecha_inicio' => $validatedData['fecha_inicio'],
                'fecha_fin' => $validatedData['fecha_fin'],
                'estado' => now()->between($validatedData['fecha_inicio'], $validatedData['fecha_fin']),
            ]);

            // Asociar productos seleccionados con la promoción
            foreach ($validatedData['productos'] as $producto) {
                PromocionProducto::create([
                    'id_promocion' => $promocion->id,
                    'id_producto' => $producto['id'],
                    'cantidad' => $producto['cantidad'],
                    'precio_unitario' => $producto['precio'],
                ]);
            }

            // Redirigir a la pantalla de promociones con un mensaje de éxito
            return redirect()->route('promociones.index')->with('success', 'Promoción creada exitosamente.');
        } catch (\Exception $e) {
            // Redirigir de vuelta con los errores
            return redirect()->back()->withErrors(['error' => 'Ocurrió un error al crear la promoción: ' . $e->getMessage()])->withInput();
        }
    }



    public function finpromocion(Request $request)
    {
        // 1. Validar los datos de entrada
        $request->validate([
            'nombre_cliente' => 'required|string|max:255',
            'ci' => 'nullable|string|max:255',
            'costo_total' => 'required|numeric|min:0',
            'id_user' => 'required|exists:users,id', // Validar que el ID de usuario exista
            'id_promocion' => 'required|exists:promociones,id',
            'id_sucursal' => 'required|exists:sucursales,id',
            'productos' => 'required|json', // Los productos de la promoción en formato JSON
        ]);

        // Debugging: Puedes descomentar esto para ver todos los datos recibidos
        \Log::info('Datos recibidos en finpromocion:', $request->all());

        // Decodificar los productos JSON
        $productos = json_decode($request->productos, true);

        try {
            // 2. Crear el registro de Venta
            $venta = Venta::create([
                'fecha' => now(),
                'nombre_cliente' => $request->nombre_cliente,
                'costo_total' => $request->costo_total,
                'pagado' => $request->costo_total, // Asignar 'pagado' igual a 'costo_total'
                'monto_cambio' => 0, // El cambio es 0 si se paga el total exacto
                'id_user' => $request->id_user, // Usar el ID de usuario seleccionado del modal
                'ci' => $request->ci,
                'tipo_pago' => 'Efectivo', // Valor por defecto si no se pide en el modal
                'id_sucursal' => $request->id_sucursal,
                'estado' => 'recojo', // Estado por defecto 'recogido'
                // 'descuento' => 0, // Si no se maneja en el modal, puedes poner un default aquí
                // 'garantia' => null, // Si no se maneja en el modal, puedes poner un default aquí
                // 'efectivo' => $request->costo_total, // Si tipo_pago es Efectivo, puedes asignar aquí
                // 'qr' => 0, // Si tipo_pago es QR, puedes asignar aquí
            ]);

            // 3. Procesar los productos de la venta y actualizar el inventario
            foreach ($productos as $producto) {
                $productoExistente = Producto::find($producto['id']);
                if (!$productoExistente) {
                    throw new \Exception('El producto con ID ' . $producto['id'] . ' no existe.');
                }

                $inventario = Inventario::where('id_producto', $producto['id'])
                    ->where('id_sucursal', $request->id_sucursal)
                    ->first();

                if (!$inventario) {
                    throw new \Exception('No hay inventario disponible para el producto: ' . $productoExistente->nombre . ' en la sucursal seleccionada.');
                }

                if ($inventario->cantidad < $producto['cantidad']) {
                    throw new \Exception('No hay suficiente stock (' . $inventario->cantidad . ') en la sucursal para el producto: ' . $productoExistente->nombre . '. Se solicitan: ' . $producto['cantidad']);
                }

                VentaProducto::create([
                    'id_venta' => $venta->id,
                    'id_producto' => $producto['id'],
                    'cantidad' => $producto['cantidad'],
                    'precio_unitario' => $producto['precio_unitario'],
                    'descuento' => 0,
                ]);

                $inventario->cantidad -= $producto['cantidad'];
                $inventario->save();
            }

            // 4. Retornar una respuesta JSON de éxito
            return response()->json(['message' => 'Venta registrada y promoción finalizada con éxito.', 'venta_id' => $venta->id]);
        } catch (\Exception $e) {
            \Log::error('Error al procesar finpromocion:', [
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['message' => 'Error interno del servidor al procesar la venta.', 'error' => $e->getMessage()], 500);
        }
    }






    public function notaPromocion(Request $request)
    {
        // Obtener los parámetros de la consulta
        $nombreCliente = $request->input('nombre_cliente');
        $costoTotal = $request->input('costo_total');
        $productosJson = $request->input('productos');
        $ci = $request->input('ci');
        $tipopago = $request->input('tipo_pago');
        $id_sucursal = $request->input('id_sucursal');
        $pagado = $request->input('monto_pagado', 0);
        $cambio = $request->input('monto_cambio', 0);
        $sucursal = Sucursale::find($id_sucursal);

        // Suponiendo que 'semana' es una columna en la tabla 'Sucursales' que contiene el nombre o identificador de la semana
        $nombresu = $sucursal->nombre;

        // Decodificar el JSON de productos
        $pedidos = json_decode($productosJson, true);

        // Inicializar un array para almacenar los productos detallados
        $productosDetalles = [];

        // Buscar cada producto en la base de datos y agregar los detalles
        foreach ($pedidos as $pedido) {
            $producto = Producto::find($pedido['id']); // Busca el producto por ID

            if ($producto) {
                $productosDetalles[] = [
                    'cantidad' => $pedido['cantidad'],
                    'nombre' => $producto->nombre, // Obtener el nombre del producto
                    'precio' => $pedido['precio'], // Usar el precio editado
                    'total' => $pedido['precio'] * $pedido['cantidad'] // Total editable
                ];
            }
        }

        // Crear el PDF
        $pdf = new FPDF('P', 'mm', [80, 180]);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->AddPage();
        $marginTop = 5;

        // Preparar los datos del pedido
        $pedido = [
            'nombre_cliente' => $nombreCliente,
            'nit' => $ci,
            'fecha' => date('Y/m/d H:i:s'),
            'productos' => $productosDetalles, // Usar los detalles de los productos
            'subtotal' => $costoTotal,
            'total' => $costoTotal,
            'pagado' => $pagado, // Agregar monto pagado
            'cambio' => $cambio, // Agregar cambio
            'monto_a_pagar' => $costoTotal,
            'forma_pago' => $tipopago,
            'id_sucursal' => $nombresu,
        ];

        // Llamar a la función para agregar los datos al PDF
        $this->datos($pdf, $pedido, $marginTop);
        $pdf->Output('I', 'promocion.pdf'); // 'I' para visualizar en el navegador
    }



    public function datos($pdf, $pedido, $marginTop)
    {
        // Logo centrado
        $pdf->Image('images/logo.png', 30, 5, 20, 20, 'PNG'); // Ajusta la ruta y tamaño del logo
        $pdf->Ln(15); // Espacio debajo del logo

        // Cabecera
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(0, 4, utf8_decode("IMPORTADORA MIRANDA S.A."), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 4, utf8_decode("A un Click del Producto que Necesita!!"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("Telefono: 70621016"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("Direccion: Caparazon Mall Center, Planta Baja, Local Nro29"), 0, 1, 'C');

        $pdf->SetFont('Arial', '', 6);
        $pdf->Cell(0, 4, utf8_decode("Sucursal: Sucursal " . $pedido['id_sucursal']), 0, 1, 'C');

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(0, 4, utf8_decode("Fecha: " . $pedido['fecha']), 0, 1, 'C');

        $pdf->Cell(0, 4, utf8_decode("Codigo de Venta:IMP" . date('Y/m/d')), 0, 1, 'C');
        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea horizontal
        $pdf->Ln(2);

        // Información de la factura
        // Información de la factura
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(0, 4, utf8_decode("NOTA DE VENTA " . $pedido['forma_pago']), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 8);

        $pdf->Cell(0, 4, utf8_decode("Forma de Pago: " . $pedido['forma_pago']), 0, 1, 'C');
        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea horizontal
        $pdf->Ln(2);
        $pdf->Cell(0, 4, utf8_decode("Cliente: " . $pedido['nombre_cliente']), 0, 1, 'L');
        $pdf->Cell(0, 4, utf8_decode("CI / NIT: " . $pedido['nit']), 0, 1, 'L'); // Mostrar el CI aquí
        $pdf->Cell(0, 4, utf8_decode("Fecha: " . $pedido['fecha']), 0, 1, 'L');

        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea horizontal
        $pdf->Ln(2);

        // Detalle de productos
        $pdf->SetFont('Arial', 'B', 8);
        // Cabecera
        $pdf->Cell(10, 6, utf8_decode("Cant."), 1, 0, 'C');
        $pdf->Cell(25, 6, utf8_decode("Desc."), 1, 0, 'C');
        $pdf->Cell(15, 6, utf8_decode("P. Unit"), 1, 0, 'C');
        $pdf->Cell(15, 6, utf8_decode("Subtotal"), 1, 1, 'C');

        $pdf->SetFont('Arial', '', 8);
        foreach ($pedido['productos'] as $producto) {
            $pdf->Cell(10, 4, utf8_decode($producto['cantidad']), 1, 0, 'C');

            $pdf->SetFont('Arial', 'B', 4);
            $pdf->Cell(25, 4, utf8_decode($producto['nombre'] ?? 'Sin descripción'), 1, 0, 'L'); // Usa 'nombre' del producto

            $pdf->SetFont('Arial', 'B', 6);
            $pdf->Cell(15, 4, utf8_decode($producto['precio']), 1, 0, 'R'); // Precio unitario
            $pdf->Cell(15, 4, utf8_decode($producto['total']), 1, 1, 'R'); // Total del producto
        }

        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea horizontal
        $pdf->Ln(2);

        // Totales

        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(0, 4, utf8_decode("SUBTOTAL: " . number_format($pedido['subtotal'], 2)), 0, 1, 'R');
        $pdf->Cell(0, 4, utf8_decode("DESCUENTO: 0"), 0, 1, 'R');
        $pdf->Cell(0, 4, utf8_decode("TOTAL: " . number_format($pedido['subtotal'], 2)), 0, 1, 'R');
        $pdf->Cell(0, 4, utf8_decode("PAGADO: " . number_format($pedido['pagado'], 2)), 0, 1, 'R');
        $pdf->Cell(0, 4, utf8_decode("CAMBIO: " . number_format($pedido['cambio'], 2)), 0, 1, 'R');
        $pdf->Cell(0, 4, utf8_decode("MONTO A PAGAR: " . number_format($pedido['total'], 2)), 0, 1, 'R');

        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea horizontal
        $pdf->Ln(5);

        // Subtotal y total
        $pdf->SetFont('Arial', '', 5);
        $pdf->Cell(0, 4, utf8_decode("NOTA IMPORTANTE"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("Los productos en PROMOCION NO CUENTAN CON NINGUN"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("TIPO DE GARANTIA, ya que se encuentran en precio"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("de REMATE. Si su producto llegara a contar con algun defecto"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("de FABRICA si quiere cambiarlo debe cancelar el producto al precio normal."), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("y debe traerlo como maximo al dia siguente por la tarde con su nota de venta"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("de lo contrario pierde derecho a cualquier RECLAMO"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("GRACIAS POR SU COMPRA!!!"), 0, 1, 'C');
    }
    //api
    public function ver()
    {
        // Obtener todas las promociones con sus relaciones de productos, fotos, sucursal y usuario
        $promociones = Promocione::with([
            'productos',    // Relación con productos
            'productos.fotos',   // Relación con fotos de productos
            'sucursal',     // Relación con sucursal
            'usuario'       // Relación con usuario
        ])->get();  // Usamos `get()` para obtener todas las promociones

        // Formatear la respuesta sin el ID de cada promoción
        $promociones_data = $promociones->map(function ($promocion) {
            return [
                'id' => $promocion->id,
                'nombre' => $promocion->nombre,
                'precio_promocion' => $promocion->precio_promocion,
                'fecha_inicio' => $promocion->fecha_inicio,
                'fecha_fin' => $promocion->fecha_fin,
                'estado' => $promocion->estado,
                'productos' => $promocion->productos->map(function ($producto) {
                    return [
                        'id' => $producto->id,
                        'nombre' => $producto->nombre,
                        'descripcion' => $producto->descripcion,
                        'precio' => $producto->precio,
                        'precio_descuento' => $producto->precio_descuento,
                        'stock' => $producto->stock,
                        'estado' => $producto->estado,
                        'fotos' => $producto->fotos->map(function ($foto) {
                            return $foto->foto;
                        })
                    ];
                }),
                'sucursal' => [
                    'nombre' => $promocion->sucursal->nombre,
                    'direccion' => $promocion->sucursal->direccion
                ],
                'usuario' => [
                    'nombre' => $promocion->usuario->nombre,
                    'email' => $promocion->usuario->email
                ]
            ];
        });
        $users = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['Vendedor', 'Vendedor Antiguo', 'Encargado de pedidos']); // Filtramos por los roles
        })
            ->where('status', 'active')
            ->orWhereIn('email', ['JHOELSURCO2@GMAIL.COM'])
            ->get(); // Obtener todos los usuarios con los roles y estado especificados
        return response()->json([
            'promociones' => $promociones_data,
            'usuarios' => $users
        ]);
    }
    public function verid($id)
    {
        // Obtener la promoción por su id con sus relaciones
        $promocion = Promocione::with([
            'productos',    // Relación con productos
            'productos.fotos',   // Relación con fotos de productos
            'sucursal',     // Relación con sucursal
            'usuario'       // Relación con usuario
        ])->find($id);  // Usamos `find($id)` para obtener la promoción por ID

        // Verificar si la promoción existe
        if (!$promocion) {
            return response()->json([
                'error' => 'Promoción no encontrada'
            ], 404);
        }

        // Formatear la respuesta sin el ID de la promoción
        $promocion_data = [
            'id' => $promocion->id,
            'nombre' => $promocion->nombre,
            'precio_promocion' => $promocion->precio_promocion,
            'fecha_inicio' => $promocion->fecha_inicio,
            'fecha_fin' => $promocion->fecha_fin,
            'estado' => $promocion->estado,
            'productos' => $promocion->productos->map(function ($producto) {
                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'descripcion' => $producto->descripcion,
                    'precio' => $producto->precio,
                    'precio_descuento' => $producto->precio_descuento,
                    'stock' => $producto->stock,
                    'estado' => $producto->estado,
                    'fotos' => $producto->fotos->map(function ($foto) {
                        return $foto->foto;
                    })
                ];
            }),
            'sucursal' => [
                'nombre' => $promocion->sucursal->nombre,
                'direccion' => $promocion->sucursal->direccion
            ],
            'usuario' => [
                'nombre' => $promocion->usuario->nombre,
                'email' => $promocion->usuario->email
            ]
        ];

        // Retornar la respuesta con la promoción específica
        return response()->json([
            'promocion' => $promocion_data
        ]);
    }
}
