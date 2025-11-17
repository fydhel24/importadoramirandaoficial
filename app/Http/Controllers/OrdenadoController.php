<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\Semana;
use App\Models\Pedido;
use App\Models\PedidoProducto;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use JsonException;
use App\Models\Envio;

class OrdenadoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }







    public function editPedidocuadernofaltantes($id_envio, $id_pedido)
    {
        $pedido = Pedido::find($id_pedido);
        $productos = Producto::all(); // Obtén la lista de productos desde la base de datos


        $semanas = Semana::all();
        if (!$pedido) {
            return redirect()->route('orden.index')
                ->with('error', 'Pedido no encontrado.');
        }

        // Preparar los productos seleccionados para la vista
        $pedido->productos = $pedido->pedidoProductos;

        return view('orden.editPedidocuadernofaltantes', compact('id_envio', 'id_pedido', 'pedido', 'productos', 'semanas'));
    }
    public function updatePedidocuadernofaltantes(Request $request, $id)
    {

        // Validación de los campos del formulario
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ci' => 'required|string|max:20',
            'celular' => 'required|string|max:20',
            'destino' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'estado' => 'required|string|max:50',
            'cantidad_productos' => 'required|integer',
            'detalle' => 'nullable|string',
            'productos' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (!empty($value)) {
                        if (!is_string($value)) {
                            $fail("El campo $attribute debe ser una cadena JSON válida.");
                        }
                        try {
                            json_decode($value, true, 512, JSON_THROW_ON_ERROR);
                        } catch (JsonException $e) {
                            $fail("El campo $attribute debe contener JSON válido.");
                        }
                    }
                },
            ],
            'monto_deposito' => 'required|numeric',
            'monto_enviado_pagado' => 'nullable|numeric',
            'fecha' => 'nullable|date',
            'id_semana' => 'nullable|exists:semanas,id',
            'foto_comprobante' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'codigo' => 'nullable|string|max:100',

            // Nuevos campos
            'efectivo' => 'nullable|numeric',
            'transferencia_qr' => 'nullable|numeric',
            'garantia'  => 'nullable|string|max:20',
        ]);

        // Obtener el pedido
        $pedido = Pedido::findOrFail($id);

        // Si el usuario ha marcado para eliminar la imagen
        if ($request->input('foto_comprobante_eliminada') == 'true') {
            // Eliminar la imagen existente (si hay)
            if ($pedido->foto_comprobante) {
                Storage::disk('public')->delete('fotos/' . $pedido->foto_comprobante);
            }
            // No asignar ninguna nueva imagen
            $fotoPath = null;
        } else {
            // Si se sube una nueva imagen, guardarla
            $fotoPath = $request->file('foto_comprobante')
                ? $request->file('foto_comprobante')->store('fotos', 'public')
                : $pedido->foto_comprobante; // Mantener la imagen actual si no se sube una nueva
        }

        // Decodificar el JSON de productos
        $productos = json_decode($request->input('productos'), true);

        // Si el array de productos está vacío, recuperar los productos del pedido existente

        // Verificar que los productos sean válidos
        foreach ($productos as $producto) {
            if (!isset($producto['id_producto'], $producto['cantidad'], $producto['precio'])) {
                return back()->withErrors(['productos' => 'El JSON de productos tiene datos faltantes o incorrectos.']);
            }
        }
        // Extraer los nombres de los productos
        $productosNombres = [];

        // Recorrer los productos para obtener sus nombres
        foreach ($productos as $producto) {
            if (isset($producto['id_producto']) && is_numeric($producto['id_producto'])) {
                // Si el 'id_producto' es válido, obtener el nombre del producto
                $productoNombre = Producto::find($producto['id_producto'])->nombre ?? 'Producto no encontrado';
                $productosNombres[] = $productoNombre;
            } else {
                // Si el 'id_producto' no es válido, usamos el valor tal cual (por si es un código o nombre)
                $productosNombres[] = strtoupper($producto['id_producto']);
            }
        }

        // Unir los nombres de los productos con comas
        $productosString = implode(', ', $productosNombres);


        // Actualizar el pedido con los nuevos datos
        $pedido->update(array_merge($request->all(), [
            'foto_comprobante' => $fotoPath,
            'productos' => $productosString,  // Guardar los productos como una cadena
        ]));

        // Eliminar los registros antiguos de la tabla pedido_producto solo si se proporcionaron nuevos productos
        if (!empty($productos)) {
            PedidoProducto::where('id_pedido', $id)->delete();

            // Guardar los productos en la tabla pedido_producto
            foreach ($productos as $producto) {
                $productoModelo = Producto::find($producto['id_producto']);
                if (!$productoModelo) {
                    throw new \InvalidArgumentException('Producto no existe.');
                }

                // Obtener el ID del usuario autenticado
                $usuarioId = auth()->user()->id;

                // Crear el registro en la tabla pedido_producto
                PedidoProducto::create([
                    'id_pedido' => $pedido->id,
                    'id_producto' => $producto['id_producto'],
                    'cantidad' => $producto['cantidad'],
                    'precio' => $producto['precio'],
                    'fecha' => now(),
                    'id_usuario' => $usuarioId, // Asignar el ID del usuario
                ]);
            }
        }

        $this->actualizarpedidoenvio($request->id_pedido, $request->id_envio);

        // Redirigir con mensaje de éxito
        return redirect()->route('envios.faltante.view', $request->input('id_semana'))
            ->with('success', 'Pedido actualizado exitosamente.');
    }





    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

























    public function editPedidocuaderno($id_envio, $id_pedido)
    {
        $pedido = Pedido::find($id_pedido);
        $productos = Producto::all(); // Lista de productos
        $semanas = Semana::all();     // Lista de semanas

        if (!$pedido) {
            return redirect()->route('orden.index')
                ->with('error', 'Pedido no encontrado.');
        }

        // Cargar productos del pedido
        $pedido->productos = $pedido->pedidoProductos;

        // Pasar ambos IDs a la vista
        return view('orden.editcuaderno', compact('id_envio', 'id_pedido', 'pedido', 'productos', 'semanas'));
    }

    public function updatePedidocuaderno(Request $request, $id)
    {
        // dd($request->all());
        // Validación de los campos del formulario
        // Validación de los campos del formulario
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ci' => 'required|string|max:20',
            'celular' => 'required|string|max:20',
            'destino' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'estado' => 'required|string|max:50',
            'cantidad_productos' => 'required|integer',
            'detalle' => 'nullable|string',
            'productos' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (!empty($value)) {
                        if (!is_string($value)) {
                            $fail("El campo $attribute debe ser una cadena JSON válida.");
                        }
                        try {
                            json_decode($value, true, 512, JSON_THROW_ON_ERROR);
                        } catch (JsonException $e) {
                            $fail("El campo $attribute debe contener JSON válido.");
                        }
                    }
                },
            ],
            'monto_deposito' => 'required|numeric',
            'monto_enviado_pagado' => 'nullable|numeric',
            'fecha' => 'nullable|date',
            'id_semana' => 'nullable|exists:semanas,id',
            'foto_comprobante' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'codigo' => 'nullable|string|max:100',

            // Nuevos campos
            'efectivo' => 'nullable|numeric',
            'transferencia_qr' => 'nullable|numeric',
            'garantia'  => 'nullable|string|max:20',
        ]);


        // Obtener el pedido
        $pedido = Pedido::findOrFail($id);

        // Si el usuario ha marcado para eliminar la imagen
        if ($request->input('foto_comprobante_eliminada') == 'true') {
            // Eliminar la imagen existente (si hay)
            if ($pedido->foto_comprobante) {
                Storage::disk('public')->delete('fotos/' . $pedido->foto_comprobante);
            }
            // No asignar ninguna nueva imagen
            $fotoPath = null;
        } else {
            // Si se sube una nueva imagen, guardarla
            $fotoPath = $request->file('foto_comprobante')
                ? $request->file('foto_comprobante')->store('fotos', 'public')
                : $pedido->foto_comprobante; // Mantener la imagen actual si no se sube una nueva
        }

        // Decodificar el JSON de productos
        $productos = json_decode($request->input('productos'), true);

        // Si el array de productos está vacío, recuperar los productos del pedido existente

        // Verificar que los productos sean válidos
        foreach ($productos as $producto) {
            if (!isset($producto['id_producto'], $producto['cantidad'], $producto['precio'])) {
                return back()->withErrors(['productos' => 'El JSON de productos tiene datos faltantes o incorrectos.']);
            }
        }
        // Extraer los nombres de los productos
        $productosNombres = [];

        // Recorrer los productos para obtener sus nombres
        foreach ($productos as $producto) {
            if (isset($producto['id_producto']) && is_numeric($producto['id_producto'])) {
                // Si el 'id_producto' es válido, obtener el nombre del producto
                $productoNombre = Producto::find($producto['id_producto'])->nombre ?? 'Producto no encontrado';
                $productosNombres[] = $productoNombre;
            } else {
                // Si el 'id_producto' no es válido, usamos el valor tal cual (por si es un código o nombre)
                $productosNombres[] = strtoupper($producto['id_producto']);
            }
        }

        // Unir los nombres de los productos con comas
        $productosString = implode(', ', $productosNombres);


        // Actualizar el pedido con los nuevos datos
        $pedido->update(array_merge($request->all(), [
            'foto_comprobante' => $fotoPath,
            'productos' => $productosString,  // Guardar los productos como una cadena
        ]));

        // Eliminar los registros antiguos de la tabla pedido_producto solo si se proporcionaron nuevos productos
        if (!empty($productos)) {
            PedidoProducto::where('id_pedido', $id)->delete();

            // Guardar los productos en la tabla pedido_producto
            foreach ($productos as $producto) {
                $productoModelo = Producto::find($producto['id_producto']);
                if (!$productoModelo) {
                    throw new \InvalidArgumentException('Producto no existe.');
                }

                // Obtener el ID del usuario autenticado
                $usuarioId = auth()->user()->id;

                // Crear el registro en la tabla pedido_producto
                PedidoProducto::create([
                    'id_pedido' => $pedido->id,
                    'id_producto' => $producto['id_producto'],
                    'cantidad' => $producto['cantidad'],
                    'precio' => $producto['precio'],
                    'fecha' => now(),
                    'id_usuario' => $usuarioId, // Asignar el ID del usuario
                ]);
            }
        }
        //quiero que el id_evio y id_pedido se envie a esta funcion 
        //  public function actualizarpedidoenvio(Request $request, $id_pedido, $id_envio) con this
        // Redirigir con mensaje de éxito
        $this->actualizarpedidoenvio($request->id_pedido, $request->id_envio);






        return redirect()->route('envioscuaderno.index', $request->input('id_semana'))
            ->with('success', 'Pedido actualizado exitosamente.');
    }



    public function actualizarpedidoenvio($id_pedido, $id_envio)
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
        }

        // Si no se reciben id_pedido e id_envio correctamente
        return response()->json([
            'success' => false,
            'message' => 'Se deben proporcionar tanto un ID de pedido como un ID de envío.',
        ]);
    }


    public function editPedidocuadernoextra1($id_envio, $id_pedido)
    {
        $pedido = Pedido::find($id_pedido);
        $productos = Producto::all(); // Obtén la lista de productos desde la base de datos


        $semanas = Semana::all();
        if (!$pedido) {
            return redirect()->route('orden.index')
                ->with('error', 'Pedido no encontrado.');
        }

        // Preparar los productos seleccionados para la vista
        $pedido->productos = $pedido->pedidoProductos;

        return view('orden.editcuadernoextra1', compact('pedido', 'id_envio', 'id_pedido', 'productos', 'semanas'));
    }
    public function updatePedidocuadernoextra1(Request $request, $id)
    {
        // Validación de los campos del formulario


        // Validación de los campos del formulario

        // Validación de los campos del formulario
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ci' => 'required|string|max:20',
            'celular' => 'required|string|max:20',
            'destino' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'estado' => 'required|string|max:50',
            'cantidad_productos' => 'required|integer',
            'detalle' => 'nullable|string',
            'productos' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (!empty($value)) {
                        if (!is_string($value)) {
                            $fail("El campo $attribute debe ser una cadena JSON válida.");
                        }
                        try {
                            json_decode($value, true, 512, JSON_THROW_ON_ERROR);
                        } catch (JsonException $e) {
                            $fail("El campo $attribute debe contener JSON válido.");
                        }
                    }
                },
            ],
            'monto_deposito' => 'required|numeric',
            'monto_enviado_pagado' => 'nullable|numeric',
            'fecha' => 'nullable|date',
            'id_semana' => 'nullable|exists:semanas,id',
            'foto_comprobante' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'codigo' => 'nullable|string|max:100',

            // Nuevos campos
            'efectivo' => 'nullable|numeric',
            'transferencia_qr' => 'nullable|numeric',
            'garantia'  => 'nullable|string|max:20',
        ]);


        // Obtener el pedido
        $pedido = Pedido::findOrFail($id);

        // Si el usuario ha marcado para eliminar la imagen
        if ($request->input('foto_comprobante_eliminada') == 'true') {
            // Eliminar la imagen existente (si hay)
            if ($pedido->foto_comprobante) {
                Storage::disk('public')->delete('fotos/' . $pedido->foto_comprobante);
            }
            // No asignar ninguna nueva imagen
            $fotoPath = null;
        } else {
            // Si se sube una nueva imagen, guardarla
            $fotoPath = $request->file('foto_comprobante')
                ? $request->file('foto_comprobante')->store('fotos', 'public')
                : $pedido->foto_comprobante; // Mantener la imagen actual si no se sube una nueva
        }

        // Decodificar el JSON de productos
        $productos = json_decode($request->input('productos'), true);

        // Si el array de productos está vacío, recuperar los productos del pedido existente

        // Verificar que los productos sean válidos
        foreach ($productos as $producto) {
            if (!isset($producto['id_producto'], $producto['cantidad'], $producto['precio'])) {
                return back()->withErrors(['productos' => 'El JSON de productos tiene datos faltantes o incorrectos.']);
            }
        }
        // Extraer los nombres de los productos
        $productosNombres = [];

        // Recorrer los productos para obtener sus nombres
        foreach ($productos as $producto) {
            if (isset($producto['id_producto']) && is_numeric($producto['id_producto'])) {
                // Si el 'id_producto' es válido, obtener el nombre del producto
                $productoNombre = Producto::find($producto['id_producto'])->nombre ?? 'Producto no encontrado';
                $productosNombres[] = $productoNombre;
            } else {
                // Si el 'id_producto' no es válido, usamos el valor tal cual (por si es un código o nombre)
                $productosNombres[] = strtoupper($producto['id_producto']);
            }
        }

        // Unir los nombres de los productos con comas
        $productosString = implode(', ', $productosNombres);


        // Actualizar el pedido con los nuevos datos
        $pedido->update(array_merge($request->all(), [
            'foto_comprobante' => $fotoPath,
            'productos' => $productosString,  // Guardar los productos como una cadena
        ]));

        // Eliminar los registros antiguos de la tabla pedido_producto solo si se proporcionaron nuevos productos
        if (!empty($productos)) {
            PedidoProducto::where('id_pedido', $id)->delete();

            // Guardar los productos en la tabla pedido_producto
            foreach ($productos as $producto) {
                $productoModelo = Producto::find($producto['id_producto']);
                if (!$productoModelo) {
                    throw new \InvalidArgumentException('Producto no existe.');
                }

                // Obtener el ID del usuario autenticado
                $usuarioId = auth()->user()->id;

                // Crear el registro en la tabla pedido_producto
                PedidoProducto::create([
                    'id_pedido' => $pedido->id,
                    'id_producto' => $producto['id_producto'],
                    'cantidad' => $producto['cantidad'],
                    'precio' => $producto['precio'],
                    'fecha' => now(),
                    'id_usuario' => $usuarioId, // Asignar el ID del usuario
                ]);
            }
        }

        $this->actualizarpedidoenvio($request->id_pedido, $request->id_envio);

        // Redirigir con mensaje de éxito
        return redirect()->route('envios.extra1.view', $request->input('id_semana'))
            ->with('success', 'Pedido actualizado exitosamente.');
    }











    ////////////////////////////////////////////////////////////////////////////////////////////////////















    public function editPedidocuadernoenlp($id_envio, $id_pedido)
    {
        $pedido = Pedido::find($id_pedido);
        $productos = Producto::all(); // Obtén la lista de productos desde la base de datos


        $semanas = Semana::all();
        if (!$pedido) {
            return redirect()->route('orden.index')
                ->with('error', 'Pedido no encontrado.');
        }

        // Preparar los productos seleccionados para la vista
        $pedido->productos = $pedido->pedidoProductos;

        return view('orden.editcuadernolp', compact('id_envio', 'id_pedido', 'pedido', 'productos', 'semanas'));
    }
    public function updatePedidocuadernoenlp(Request $request, $id)
    {
        // Validación de los campos del formulario
        // Validación de los campos del formulario
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ci' => 'required|string|max:20',
            'celular' => 'required|string|max:20',
            'destino' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'estado' => 'required|string|max:50',
            'cantidad_productos' => 'required|integer',
            'detalle' => 'nullable|string',
            'productos' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (!empty($value)) {
                        if (!is_string($value)) {
                            $fail("El campo $attribute debe ser una cadena JSON válida.");
                        }
                        try {
                            json_decode($value, true, 512, JSON_THROW_ON_ERROR);
                        } catch (JsonException $e) {
                            $fail("El campo $attribute debe contener JSON válido.");
                        }
                    }
                },
            ],
            'monto_deposito' => 'required|numeric',
            'monto_enviado_pagado' => 'nullable|numeric',
            'fecha' => 'nullable|date',
            'id_semana' => 'nullable|exists:semanas,id',
            'foto_comprobante' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'codigo' => 'nullable|string|max:100',

            // Nuevos campos
            'efectivo' => 'nullable|numeric',
            'transferencia_qr' => 'nullable|numeric',
            'garantia'  => 'nullable|string|max:20',
        ]);


        // Obtener el pedido
        $pedido = Pedido::findOrFail($id);

        // Si el usuario ha marcado para eliminar la imagen
        if ($request->input('foto_comprobante_eliminada') == 'true') {
            // Eliminar la imagen existente (si hay)
            if ($pedido->foto_comprobante) {
                Storage::disk('public')->delete('fotos/' . $pedido->foto_comprobante);
            }
            // No asignar ninguna nueva imagen
            $fotoPath = null;
        } else {
            // Si se sube una nueva imagen, guardarla
            $fotoPath = $request->file('foto_comprobante')
                ? $request->file('foto_comprobante')->store('fotos', 'public')
                : $pedido->foto_comprobante; // Mantener la imagen actual si no se sube una nueva
        }

        // Decodificar el JSON de productos
        $productos = json_decode($request->input('productos'), true);

        // Si el array de productos está vacío, recuperar los productos del pedido existente

        // Verificar que los productos sean válidos
        foreach ($productos as $producto) {
            if (!isset($producto['id_producto'], $producto['cantidad'], $producto['precio'])) {
                return back()->withErrors(['productos' => 'El JSON de productos tiene datos faltantes o incorrectos.']);
            }
        }
        // Extraer los nombres de los productos
        $productosNombres = [];

        // Recorrer los productos para obtener sus nombres
        foreach ($productos as $producto) {
            if (isset($producto['id_producto']) && is_numeric($producto['id_producto'])) {
                // Si el 'id_producto' es válido, obtener el nombre del producto
                $productoNombre = Producto::find($producto['id_producto'])->nombre ?? 'Producto no encontrado';
                $productosNombres[] = $productoNombre;
            } else {
                // Si el 'id_producto' no es válido, usamos el valor tal cual (por si es un código o nombre)
                $productosNombres[] = strtoupper($producto['id_producto']);
            }
        }

        // Unir los nombres de los productos con comas
        $productosString = implode(', ', $productosNombres);


        // Actualizar el pedido con los nuevos datos
        $pedido->update(array_merge($request->all(), [
            'foto_comprobante' => $fotoPath,
            'productos' => $productosString,  // Guardar los productos como una cadena
        ]));

        // Eliminar los registros antiguos de la tabla pedido_producto solo si se proporcionaron nuevos productos
        if (!empty($productos)) {
            PedidoProducto::where('id_pedido', $id)->delete();

            // Guardar los productos en la tabla pedido_producto
            foreach ($productos as $producto) {
                $productoModelo = Producto::find($producto['id_producto']);
                if (!$productoModelo) {
                    throw new \InvalidArgumentException('Producto no existe.');
                }

                // Obtener el ID del usuario autenticado
                $usuarioId = auth()->user()->id;

                // Crear el registro en la tabla pedido_producto
                PedidoProducto::create([
                    'id_pedido' => $pedido->id,
                    'id_producto' => $producto['id_producto'],
                    'cantidad' => $producto['cantidad'],
                    'precio' => $producto['precio'],
                    'fecha' => now(),
                    'id_usuario' => $usuarioId, // Asignar el ID del usuario
                ]);
            }
        }

        $this->actualizarpedidoenvio($request->id_pedido, $request->id_envio);

        // Redirigir con mensaje de éxito
        return redirect()->route('envioscuaderno.indexSinLaPazYEnviados', $request->input('id_semana'))
            ->with('success', 'Pedido actualizado exitosamente.');
    }









    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////














    public function editPedidocuadernolapaz($id_envio, $id_pedido)
    {
        $pedido = Pedido::find($id_pedido);
        $productos = Producto::all(); // Obtén la lista de productos desde la base de datos


        $semanas = Semana::all();
        if (!$pedido) {
            return redirect()->route('orden.index')
                ->with('error', 'Pedido no encontrado.');
        }

        // Preparar los productos seleccionados para la vista
        $pedido->productos = $pedido->pedidoProductos;

        return view('orden.editcuadernolapaz', compact('id_envio', 'id_pedido', 'pedido', 'productos', 'semanas'));
    }

    public function updatePedidocuadernolapaz(Request $request, $id)
    {
        // Validación de los campos del formulario
        // Validación de los campos del formulario
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ci' => 'required|string|max:20',
            'celular' => 'required|string|max:20',
            'destino' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'estado' => 'required|string|max:50',
            'cantidad_productos' => 'required|integer',
            'detalle' => 'nullable|string',
            'productos' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (!empty($value)) {
                        if (!is_string($value)) {
                            $fail("El campo $attribute debe ser una cadena JSON válida.");
                        }
                        try {
                            json_decode($value, true, 512, JSON_THROW_ON_ERROR);
                        } catch (JsonException $e) {
                            $fail("El campo $attribute debe contener JSON válido.");
                        }
                    }
                },
            ],
            'monto_deposito' => 'required|numeric',
            'monto_enviado_pagado' => 'nullable|numeric',
            'fecha' => 'nullable|date',
            'id_semana' => 'nullable|exists:semanas,id',
            'foto_comprobante' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'codigo' => 'nullable|string|max:100',

            // Nuevos campos
            'efectivo' => 'nullable|numeric',
            'transferencia_qr' => 'nullable|numeric',
            'garantia'  => 'nullable|string|max:20',
        ]);


        // Obtener el pedido
        $pedido = Pedido::findOrFail($id);

        // Si el usuario ha marcado para eliminar la imagen
        if ($request->input('foto_comprobante_eliminada') == 'true') {
            // Eliminar la imagen existente (si hay)
            if ($pedido->foto_comprobante) {
                Storage::disk('public')->delete('fotos/' . $pedido->foto_comprobante);
            }
            // No asignar ninguna nueva imagen
            $fotoPath = null;
        } else {
            // Si se sube una nueva imagen, guardarla
            $fotoPath = $request->file('foto_comprobante')
                ? $request->file('foto_comprobante')->store('fotos', 'public')
                : $pedido->foto_comprobante; // Mantener la imagen actual si no se sube una nueva
        }

        // Decodificar el JSON de productos
        $productos = json_decode($request->input('productos'), true);

        // Si el array de productos está vacío, recuperar los productos del pedido existente

        // Verificar que los productos sean válidos
        foreach ($productos as $producto) {
            if (!isset($producto['id_producto'], $producto['cantidad'], $producto['precio'])) {
                return back()->withErrors(['productos' => 'El JSON de productos tiene datos faltantes o incorrectos.']);
            }
        }
        // Extraer los nombres de los productos
        $productosNombres = [];

        // Recorrer los productos para obtener sus nombres
        foreach ($productos as $producto) {
            if (isset($producto['id_producto']) && is_numeric($producto['id_producto'])) {
                // Si el 'id_producto' es válido, obtener el nombre del producto
                $productoNombre = Producto::find($producto['id_producto'])->nombre ?? 'Producto no encontrado';
                $productosNombres[] = $productoNombre;
            } else {
                // Si el 'id_producto' no es válido, usamos el valor tal cual (por si es un código o nombre)
                $productosNombres[] = strtoupper($producto['id_producto']);
            }
        }
        // Unir los nombres de los productos con comas
        $productosString = implode(', ', $productosNombres);


        // Actualizar el pedido con los nuevos datos
        $pedido->update(array_merge($request->all(), [
            'foto_comprobante' => $fotoPath,
            'productos' => $productosString,  // Guardar los productos como una cadena
        ]));

        // Eliminar los registros antiguos de la tabla pedido_producto solo si se proporcionaron nuevos productos
        if (!empty($productos)) {
            PedidoProducto::where('id_pedido', $id)->delete();

            // Guardar los productos en la tabla pedido_producto
            foreach ($productos as $producto) {
                $productoModelo = Producto::find($producto['id_producto']);
                if (!$productoModelo) {
                    throw new \InvalidArgumentException('Producto no existe.');
                }

                // Obtener el ID del usuario autenticado
                $usuarioId = auth()->user()->id;

                // Crear el registro en la tabla pedido_producto
                PedidoProducto::create([
                    'id_pedido' => $pedido->id,
                    'id_producto' => $producto['id_producto'],
                    'cantidad' => $producto['cantidad'],
                    'precio' => $producto['precio'],
                    'fecha' => now(),
                    'id_usuario' => $usuarioId, // Asignar el ID del usuario
                ]);
            }
        }
        $this->actualizarpedidoenvio($request->id_pedido, $request->id_envio);


        // Redirigir con mensaje de éxito
        return redirect()->route('envioscuaderno.indexSinLaPaz', $request->input('id_semana'))
            ->with('success', 'Pedido actualizado exitosamente.');
    }














    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////















    public function editPedidocuadernolapazconfirmados($id_envio, $id_pedido)
    {
        $pedido = Pedido::find($id_pedido);
        $productos = Producto::all(); // Obtén la lista de productos desde la base de datos


        $semanas = Semana::all();
        if (!$pedido) {
            return redirect()->route('orden.index')
                ->with('error', 'Pedido no encontrado.');
        }

        // Preparar los productos seleccionados para la vista
        $pedido->productos = $pedido->pedidoProductos;

        return view('orden.editPedidocuadernolapazconfirmados', compact('pedido', 'id_envio', 'id_pedido', 'productos', 'semanas'));
    }
    public function updatePedidocuadernolapazconfirmados(Request $request, $id)
    {
        // Validación de los campos del formulario


        // Validación de los campos del formulario
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ci' => 'required|string|max:20',
            'celular' => 'required|string|max:20',
            'destino' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'estado' => 'required|string|max:50',
            'cantidad_productos' => 'required|integer',
            'detalle' => 'nullable|string',
            'productos' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (!empty($value)) {
                        if (!is_string($value)) {
                            $fail("El campo $attribute debe ser una cadena JSON válida.");
                        }
                        try {
                            json_decode($value, true, 512, JSON_THROW_ON_ERROR);
                        } catch (JsonException $e) {
                            $fail("El campo $attribute debe contener JSON válido.");
                        }
                    }
                },
            ],
            'monto_deposito' => 'required|numeric',
            'monto_enviado_pagado' => 'nullable|numeric',
            'fecha' => 'nullable|date',
            'id_semana' => 'nullable|exists:semanas,id',
            'foto_comprobante' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'codigo' => 'nullable|string|max:100',

            // Nuevos campos
            'efectivo' => 'nullable|numeric',
            'transferencia_qr' => 'nullable|numeric',
            'garantia'  => 'nullable|string|max:20',
        ]);

        // Obtener el pedido
        $pedido = Pedido::findOrFail($id);

        // Si el usuario ha marcado para eliminar la imagen
        if ($request->input('foto_comprobante_eliminada') == 'true') {
            // Eliminar la imagen existente (si hay)
            if ($pedido->foto_comprobante) {
                Storage::disk('public')->delete('fotos/' . $pedido->foto_comprobante);
            }
            // No asignar ninguna nueva imagen
            $fotoPath = null;
        } else {
            // Si se sube una nueva imagen, guardarla
            $fotoPath = $request->file('foto_comprobante')
                ? $request->file('foto_comprobante')->store('fotos', 'public')
                : $pedido->foto_comprobante; // Mantener la imagen actual si no se sube una nueva
        }

        // Decodificar el JSON de productos
        $productos = json_decode($request->input('productos'), true);

        // Si el array de productos está vacío, recuperar los productos del pedido existente

        // Verificar que los productos sean válidos
        foreach ($productos as $producto) {
            if (!isset($producto['id_producto'], $producto['cantidad'], $producto['precio'])) {
                return back()->withErrors(['productos' => 'El JSON de productos tiene datos faltantes o incorrectos.']);
            }
        }
        // Extraer los nombres de los productos
        $productosNombres = [];

        // Recorrer los productos para obtener sus nombres
        foreach ($productos as $producto) {
            if (isset($producto['id_producto']) && is_numeric($producto['id_producto'])) {
                // Si el 'id_producto' es válido, obtener el nombre del producto
                $productoNombre = Producto::find($producto['id_producto'])->nombre ?? 'Producto no encontrado';
                $productosNombres[] = $productoNombre;
            } else {
                // Si el 'id_producto' no es válido, usamos el valor tal cual (por si es un código o nombre)
                $productosNombres[] = strtoupper($producto['id_producto']);
            }
        }

        // Unir los nombres de los productos con comas
        $productosString = implode(', ', $productosNombres);


        // Actualizar el pedido con los nuevos datos
        $pedido->update(array_merge($request->all(), [
            'foto_comprobante' => $fotoPath,
            'productos' => $productosString,  // Guardar los productos como una cadena
        ]));

        // Eliminar los registros antiguos de la tabla pedido_producto solo si se proporcionaron nuevos productos
        if (!empty($productos)) {
            PedidoProducto::where('id_pedido', $id)->delete();

            // Guardar los productos en la tabla pedido_producto
            foreach ($productos as $producto) {
                $productoModelo = Producto::find($producto['id_producto']);
                if (!$productoModelo) {
                    throw new \InvalidArgumentException('Producto no existe.');
                }

                // Obtener el ID del usuario autenticado
                $usuarioId = auth()->user()->id;

                // Crear el registro en la tabla pedido_producto
                PedidoProducto::create([
                    'id_pedido' => $pedido->id,
                    'id_producto' => $producto['id_producto'],
                    'cantidad' => $producto['cantidad'],
                    'precio' => $producto['precio'],
                    'fecha' => now(),
                    'id_usuario' => $usuarioId, // Asignar el ID del usuario
                ]);
            }
        }
        $this->actualizarpedidoenvio($request->id_pedido, $request->id_envio);

        // Redirigir con mensaje de éxito
        return redirect()->route('envioscuaderno.indexconfirmados', $request->input('id_semana'))
            ->with('success', 'Pedido actualizado exitosamente.');
    }










    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////7


















    public function editPedidocuadernolapazpendientes($id_envio, $id_pedido)
    {
        $pedido = Pedido::find($id_pedido);
        $productos = Producto::all(); // Obtén la lista de productos desde la base de datos


        $semanas = Semana::all();
        if (!$pedido) {
            return redirect()->route('orden.index')
                ->with('error', 'Pedido no encontrado.');
        }

        // Preparar los productos seleccionados para la vista
        $pedido->productos = $pedido->pedidoProductos;

        return view('orden.editPedidocuadernolapazpendientes', compact('id_envio', 'id_pedido', 'pedido', 'productos', 'semanas'));
    }
    public function updatePedidocuadernolapazpendientes(Request $request, $id)
    {

        // Validación de los campos del formulario
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ci' => 'required|string|max:20',
            'celular' => 'required|string|max:20',
            'destino' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'estado' => 'required|string|max:50',
            'cantidad_productos' => 'required|integer',
            'detalle' => 'nullable|string',
            'productos' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (!empty($value)) {
                        if (!is_string($value)) {
                            $fail("El campo $attribute debe ser una cadena JSON válida.");
                        }
                        try {
                            json_decode($value, true, 512, JSON_THROW_ON_ERROR);
                        } catch (JsonException $e) {
                            $fail("El campo $attribute debe contener JSON válido.");
                        }
                    }
                },
            ],
            'monto_deposito' => 'required|numeric',
            'monto_enviado_pagado' => 'nullable|numeric',
            'fecha' => 'nullable|date',
            'id_semana' => 'nullable|exists:semanas,id',
            'foto_comprobante' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'codigo' => 'nullable|string|max:100',

            // Nuevos campos
            'efectivo' => 'nullable|numeric',
            'transferencia_qr' => 'nullable|numeric',
            'garantia'  => 'nullable|string|max:20',
        ]);

        // Obtener el pedido
        $pedido = Pedido::findOrFail($id);

        // Si el usuario ha marcado para eliminar la imagen
        if ($request->input('foto_comprobante_eliminada') == 'true') {
            // Eliminar la imagen existente (si hay)
            if ($pedido->foto_comprobante) {
                Storage::disk('public')->delete('fotos/' . $pedido->foto_comprobante);
            }
            // No asignar ninguna nueva imagen
            $fotoPath = null;
        } else {
            // Si se sube una nueva imagen, guardarla
            $fotoPath = $request->file('foto_comprobante')
                ? $request->file('foto_comprobante')->store('fotos', 'public')
                : $pedido->foto_comprobante; // Mantener la imagen actual si no se sube una nueva
        }

        // Decodificar el JSON de productos
        $productos = json_decode($request->input('productos'), true);

        // Si el array de productos está vacío, recuperar los productos del pedido existente

        // Verificar que los productos sean válidos
        foreach ($productos as $producto) {
            if (!isset($producto['id_producto'], $producto['cantidad'], $producto['precio'])) {
                return back()->withErrors(['productos' => 'El JSON de productos tiene datos faltantes o incorrectos.']);
            }
        }
        // Extraer los nombres de los productos
        $productosNombres = [];

        // Recorrer los productos para obtener sus nombres
        foreach ($productos as $producto) {
            if (isset($producto['id_producto']) && is_numeric($producto['id_producto'])) {
                // Si el 'id_producto' es válido, obtener el nombre del producto
                $productoNombre = Producto::find($producto['id_producto'])->nombre ?? 'Producto no encontrado';
                $productosNombres[] = $productoNombre;
            } else {
                // Si el 'id_producto' no es válido, usamos el valor tal cual (por si es un código o nombre)
                $productosNombres[] = strtoupper($producto['id_producto']);
            }
        }

        // Unir los nombres de los productos con comas
        $productosString = implode(', ', $productosNombres);


        // Actualizar el pedido con los nuevos datos
        $pedido->update(array_merge($request->all(), [
            'foto_comprobante' => $fotoPath,
            'productos' => $productosString,  // Guardar los productos como una cadena
        ]));

        // Eliminar los registros antiguos de la tabla pedido_producto solo si se proporcionaron nuevos productos
        if (!empty($productos)) {
            PedidoProducto::where('id_pedido', $id)->delete();

            // Guardar los productos en la tabla pedido_producto
            foreach ($productos as $producto) {
                $productoModelo = Producto::find($producto['id_producto']);
                if (!$productoModelo) {
                    throw new \InvalidArgumentException('Producto no existe.');
                }

                // Obtener el ID del usuario autenticado
                $usuarioId = auth()->user()->id;

                // Crear el registro en la tabla pedido_producto
                PedidoProducto::create([
                    'id_pedido' => $pedido->id,
                    'id_producto' => $producto['id_producto'],
                    'cantidad' => $producto['cantidad'],
                    'precio' => $producto['precio'],
                    'fecha' => now(),
                    'id_usuario' => $usuarioId, // Asignar el ID del usuario
                ]);
            }
        }

        $this->actualizarpedidoenvio($request->id_pedido, $request->id_envio);

        // Redirigir con mensaje de éxito
        return redirect()->route('envioscuaderno.indexpendientes', $request->input('id_semana'))
            ->with('success', 'Pedido actualizado exitosamente.');
    }








    /////////////////////////////////////////////////////////////////////////////////////////










    public function editPedidocuadernosololapaz($id_envio, $id_pedido)
    {
        $pedido = Pedido::find($id_pedido);
        $productos = Producto::all(); // Obtén la lista de productos desde la base de datos


        $semanas = Semana::all();
        if (!$pedido) {
            return redirect()->route('orden.index')
                ->with('error', 'Pedido no encontrado.');
        }

        // Preparar los productos seleccionados para la vista
        $pedido->productos = $pedido->pedidoProductos;

        return view('orden.editPedidocuadernosololapaz', compact('pedido', 'id_envio', 'id_pedido', 'productos', 'semanas'));
    }
    public function updatePedidocuadernosololapaz(Request $request, $id)
    {
        // Validación de los campos del formulario
        // Validación de los campos del formulario
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ci' => 'required|string|max:20',
            'celular' => 'required|string|max:20',
            'destino' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'estado' => 'required|string|max:50',
            'cantidad_productos' => 'required|integer',
            'detalle' => 'nullable|string',
            'productos' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (!empty($value)) {
                        if (!is_string($value)) {
                            $fail("El campo $attribute debe ser una cadena JSON válida.");
                        }
                        try {
                            json_decode($value, true, 512, JSON_THROW_ON_ERROR);
                        } catch (JsonException $e) {
                            $fail("El campo $attribute debe contener JSON válido.");
                        }
                    }
                },
            ],
            'monto_deposito' => 'required|numeric',
            'monto_enviado_pagado' => 'nullable|numeric',
            'fecha' => 'nullable|date',
            'id_semana' => 'nullable|exists:semanas,id',
            'foto_comprobante' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'codigo' => 'nullable|string|max:100',

            // Nuevos campos
            'efectivo' => 'nullable|numeric',
            'transferencia_qr' => 'nullable|numeric',
            'garantia'  => 'nullable|string|max:20',
        ]);


        // Obtener el pedido
        $pedido = Pedido::findOrFail($id);

        // Si el usuario ha marcado para eliminar la imagen
        if ($request->input('foto_comprobante_eliminada') == 'true') {
            // Eliminar la imagen existente (si hay)
            if ($pedido->foto_comprobante) {
                Storage::disk('public')->delete('fotos/' . $pedido->foto_comprobante);
            }
            // No asignar ninguna nueva imagen
            $fotoPath = null;
        } else {
            // Si se sube una nueva imagen, guardarla
            $fotoPath = $request->file('foto_comprobante')
                ? $request->file('foto_comprobante')->store('fotos', 'public')
                : $pedido->foto_comprobante; // Mantener la imagen actual si no se sube una nueva
        }

        // Decodificar el JSON de productos
        $productos = json_decode($request->input('productos'), true);

        // Si el array de productos está vacío, recuperar los productos del pedido existente

        // Verificar que los productos sean válidos
        foreach ($productos as $producto) {
            if (!isset($producto['id_producto'], $producto['cantidad'], $producto['precio'])) {
                return back()->withErrors(['productos' => 'El JSON de productos tiene datos faltantes o incorrectos.']);
            }
        }
        // Extraer los nombres de los productos
        $productosNombres = [];

        // Recorrer los productos para obtener sus nombres
        foreach ($productos as $producto) {
            if (isset($producto['id_producto']) && is_numeric($producto['id_producto'])) {
                // Si el 'id_producto' es válido, obtener el nombre del producto
                $productoNombre = Producto::find($producto['id_producto'])->nombre ?? 'Producto no encontrado';
                $productosNombres[] = $productoNombre;
            } else {
                // Si el 'id_producto' no es válido, usamos el valor tal cual (por si es un código o nombre)
                $productosNombres[] = strtoupper($producto['id_producto']);
            }
        }

        // Unir los nombres de los productos con comas
        $productosString = implode(', ', $productosNombres);


        // Actualizar el pedido con los nuevos datos
        $pedido->update(array_merge($request->all(), [
            'foto_comprobante' => $fotoPath,
            'productos' => $productosString,  // Guardar los productos como una cadena
        ]));

        // Eliminar los registros antiguos de la tabla pedido_producto solo si se proporcionaron nuevos productos
        if (!empty($productos)) {
            PedidoProducto::where('id_pedido', $id)->delete();

            // Guardar los productos en la tabla pedido_producto
            foreach ($productos as $producto) {
                $productoModelo = Producto::find($producto['id_producto']);
                if (!$productoModelo) {
                    throw new \InvalidArgumentException('Producto no existe.');
                }

                // Obtener el ID del usuario autenticado
                $usuarioId = auth()->user()->id;

                // Crear el registro en la tabla pedido_producto
                PedidoProducto::create([
                    'id_pedido' => $pedido->id,
                    'id_producto' => $producto['id_producto'],
                    'cantidad' => $producto['cantidad'],
                    'precio' => $producto['precio'],
                    'fecha' => now(),
                    'id_usuario' => $usuarioId, // Asignar el ID del usuario
                ]);
            }
        }
        $this->actualizarpedidoenvio($request->id_pedido, $request->id_envio);
        // Redirigir con mensaje de éxito
        return redirect()->route('envioscuaderno.sololapaz', $request->input('id_semana'))
            ->with('success', 'Pedido actualizado exitosamente.');
    }























    ////////////////////////////////////////////////////////////////////////////////////
















    public function update(Request $request, $id)
    {
        $pedido = Pedido::find($id);
        if ($pedido) {
            $pedido->update($request->all());
            return response()->json(['success' => true, 'message' => 'Campo actualizado con éxito']);
        } else {
            return response()->json(['success' => false, 'message' => 'Pedido no encontrado']);
        }
    }
    public function cambiarEstado(Request $request)
    {
        $pedidos = $request->input('pedidos');
        $estados = $request->input('estado'); // Este es el objeto con los cambios

        foreach ($pedidos as $pedidoId) {
            $pedido = Pedido::find($pedidoId);
            if ($pedido) {
                $estadoActual = $pedido->estado;

                // Si el estado actual está en el objeto de cambios, actualizarlo
                if (isset($estados[$estadoActual])) {
                    $pedido->estado = $estados[$estadoActual]; // Cambiar al estado opuesto
                    $pedido->save();
                }
            }
        }

        return response()->json(['success' => true, 'message' => 'Los pedidos han sido actualizados con éxito.']);
    }


    public function index()
    {
        $semanas = Semana::all();
        return view('orden.index', compact('semanas'));
    }

    public function pedidosPorSemana($id)
    {
        // Obtener los pedidos para la semana específica y ordenarlos por ID
        $pedidos = Pedido::where('id_semana', $id)
            ->orderBy('id', 'desc')
            ->get();

        // Obtener la semana específica
        $semana = Semana::find($id);
        $productos = Producto::all();


        // Calcular el monto total de depósitos para la semana
        $totalMontoDeposito = Pedido::where('id_semana', $id)
            ->sum('monto_deposito');
        $totalMontoEnviado = $pedidos->sum('monto_enviado_pagado');
        $totalDiferencia = $totalMontoDeposito - $totalMontoEnviado;

        // Pasar los datos a la vista
        return view('orden.pedidos', [
            'pedidos' => $pedidos,
            'id' => $id,
            'semana' => $semana,
            'totalMontoDeposito' => $totalMontoDeposito,
            'totalMontoEnviado' => $totalMontoEnviado,
            'totalDiferencia' => $totalDiferencia,
            'productos' => $productos
        ]);
    }




    public function createPedido($id)
    {
        $semana = Semana::find($id);
        $productos = Producto::all(); // Obtén la lista de productos desde la base de datos

        if (!$semana) {
            return redirect()->route('orden.index')
                ->with('error', 'Semana no encontrada.');
        }

        return view('orden.create', compact('id', 'productos'));
    }
    public function storePedido(Request $request)
    {
        // Validación de los campos del formulario
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ci' => 'required|string|max:20',
            'celular' => 'required|string|max:20',
            'destino' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
            'estado' => 'required|string|max:50',
            'cantidad_productos' => 'required|integer',
            'detalle' => 'nullable|string',
            'productos' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (!is_string($value)) {
                        $fail("El campo $attribute debe ser una cadena JSON válida.");
                    }
                    try {
                        json_decode($value, true, 512, JSON_THROW_ON_ERROR);
                    } catch (JsonException $e) {
                        $fail("El campo $attribute debe contener JSON válido.");
                    }
                },
            ],
            'monto_deposito' => 'required|numeric',
            'monto_enviado_pagado' => 'required|numeric',
            'fecha' => 'required|date',
            'id_semana' => 'required|exists:semanas,id',
            'foto_comprobante' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'codigo' => 'nullable|string|max:100',
        ]);

        // Manejo de la foto
        $fotoPath = $request->file('foto_comprobante')
            ? $request->file('foto_comprobante')->store('fotos', 'public')
            : null;

        // Decodificar el JSON de productos
        $productos = json_decode($request->input('productos'), true);

        // Verificar que los productos sean válidos
        foreach ($productos as $producto) {
            if (!isset($producto['id_producto'], $producto['cantidad'], $producto['precio'])) {
                return back()->withErrors(['productos' => 'El JSON de productos tiene datos faltantes o incorrectos.']);
            }
        }

        // Crear el pedido
        // Inicializar el array para los nombres de los productos
        $productosNombres = [];

        // Recorrer los productos para obtener sus nombres
        foreach ($productos as $producto) {
            if (isset($producto['id_producto']) && is_numeric($producto['id_producto'])) {
                // Si el 'id_producto' es válido, obtener el nombre del producto
                $productoNombre = Producto::find($producto['id_producto'])->nombre ?? 'Producto no encontrado';
                $productosNombres[] = $productoNombre;
            } else {
                // Si el 'id_producto' no es válido, usamos el valor tal cual (por si es un código o nombre)
                $productosNombres[] = strtoupper($producto['id_producto']);
            }
        }

        // Unir los nombres de los productos con comas
        $productosString = implode(', ', $productosNombres);

        // Crear el pedido, agregando el string de los nombres de los productos
        $pedido = Pedido::create(array_merge($request->all(), [
            'foto_comprobante' => $fotoPath,
            'productos' => $productosString,  // Agregar los nombres de productos al pedido
        ]));

        // Guardar los productos en la tabla `pedido_producto`
        foreach ($productos as $producto) {
            $productoModelo = Producto::find($producto['id_producto']);
            if (!$productoModelo) {
                throw new \InvalidArgumentException('Producto no existe.');
            }

            // Obtener el ID del usuario autenticado
            $usuarioId = auth()->user()->id;

            // Crear el registro en la tabla `pedido_producto`
            PedidoProducto::create([
                'id_pedido' => $pedido->id,
                'id_producto' => $producto['id_producto'],
                'cantidad' => $producto['cantidad'],
                'precio' => $producto['precio'],
                'fecha' => now(),
                'id_usuario' => $usuarioId, // Asignar el ID del usuario
            ]);
        }

        // Redirigir con mensaje de éxito
        return redirect()->route('orden.pedidos', $request->input('id_semana'))
            ->with('success', 'Pedido creado exitosamente.');
    }









    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


















    public function editPedido($id)
    {
        $pedido = Pedido::find($id);
        $productos = Producto::all();

        $semanas = Semana::all();
        if (!$pedido) {
            return redirect()->route('orden.index')
                ->with('error', 'Pedido no encontrado.');
        }

        // Preparar los productos seleccionados para la vista
        $pedido->productos = $pedido->pedidoProductos;

        return view('orden.edit', compact('id', 'pedido', 'productos', 'semanas'));
    }


    public function updatePedido(Request $request, $id)
    {
        // Validación de los campos del formulario
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ci' => 'required|string|max:20',
            'celular' => 'required|string|max:20',
            'destino' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'estado' => 'required|string|max:50',
            'cantidad_productos' => 'required|integer',
            'detalle' => 'nullable|string',
            'productos' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (!empty($value)) {
                        if (!is_string($value)) {
                            $fail("El campo $attribute debe ser una cadena JSON válida.");
                        }
                        try {
                            json_decode($value, true, 512, JSON_THROW_ON_ERROR);
                        } catch (JsonException $e) {
                            $fail("El campo $attribute debe contener JSON válido.");
                        }
                    }
                },
            ],
            'monto_deposito' => 'required|numeric',
            'monto_enviado_pagado' => 'nullable|numeric',
            'fecha' => 'nullable|date',
            'id_semana' => 'nullable|exists:semanas,id',
            'foto_comprobante' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'codigo' => 'nullable|string|max:100',

            // Nuevos campos
            'efectivo' => 'nullable|numeric',
            'transferencia_qr' => 'nullable|numeric',
            'garantia'  => 'nullable|string|max:20',
        ]);

        $pedido = Pedido::findOrFail($id);

        if ($request->input('foto_comprobante_eliminada') == 'true') {
            if ($pedido->foto_comprobante) {
                Storage::disk('public')->delete('fotos/' . $pedido->foto_comprobante);
            }
            $fotoPath = null;
        } else {
            $fotoPath = $request->file('foto_comprobante')
                ? $request->file('foto_comprobante')->store('fotos', 'public')
                : $pedido->foto_comprobante;
        }

        $productos = json_decode($request->input('productos'), true);

        foreach ($productos as $producto) {
            if (!isset($producto['id_producto'], $producto['cantidad'], $producto['precio'])) {
                return back()->withErrors(['productos' => 'El JSON de productos tiene datos faltantes o incorrectos.']);
            }
        }

        $productosNombres = [];
        foreach ($productos as $producto) {
            if (isset($producto['id_producto']) && is_numeric($producto['id_producto'])) {
                $productoNombre = Producto::find($producto['id_producto'])->nombre ?? 'Producto no encontrado';
                $productosNombres[] = $productoNombre;
            } else {
                $productosNombres[] = strtoupper($producto['id_producto']);
            }
        }

        $productosString = implode(', ', $productosNombres);

        // Actualizar el pedido con los nuevos datos, incluyendo los nuevos campos
        $pedido->update(array_merge($request->all(), [
            'foto_comprobante' => $fotoPath,
            'productos' => $productosString,
        ]));

        if (!empty($productos)) {
            PedidoProducto::where('id_pedido', $id)->delete();

            foreach ($productos as $producto) {
                $productoModelo = Producto::find($producto['id_producto']);
                if (!$productoModelo) {
                    throw new \InvalidArgumentException('Producto no existe.');
                }

                $usuarioId = auth()->user()->id;

                PedidoProducto::create([
                    'id_pedido' => $pedido->id,
                    'id_producto' => $producto['id_producto'],
                    'cantidad' => $producto['cantidad'],
                    'precio' => $producto['precio'],
                    'fecha' => now(),
                    'id_usuario' => $usuarioId,
                ]);
            }
        }

        return redirect()->route('orden.pedidos', $request->input('id_semana'))
            ->with('success', 'Pedido actualizado exitosamente.');
    }









    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////















    /* public function confirmPedido(Pedido $pedido)
    {
        $pedidoProductos = $pedido->pedidoProductos;

        foreach ($pedidoProductos as $pedidoProducto) {
            $producto = $pedidoProducto->producto;

            // Verificar si el stock es 0
            if ($producto->stock <= 0) {
                return response()->json([
                    'warning' => 'El producto ' . $producto->nombre . ' tiene un stock de 0 y no está disponible para confirmar.'
                ]);
            }

            // Verificar si el stock es insuficiente
            if ($producto->stock < $pedidoProducto->cantidad) {
                return response()->json([
                    'warning' => 'El producto ' . $producto->nombre . ' tiene solo ' . $producto->stock . ' unidades disponibles, pero se han solicitado ' . $pedidoProducto->cantidad . '. No está disponible para confirmar.'
                ]);
            }
        }

        // Si todos los productos tienen stock suficiente, actualizar el stock
        foreach ($pedidoProductos as $pedidoProducto) {
            $producto = $pedidoProducto->producto;
            $producto->stock -= $pedidoProducto->cantidad;
            $producto->save();
        }

        // Marcar el pedido como confirmado
        $pedido->estado_pedido = 'confirmado';
        $pedido->save();

        return response()->json(['success' => 'El pedido ha sido confirmado con éxito.']);
    } */

    public function confirmarSeleccionados(Request $request)
    {
        $pedidosIds = $request->input('pedidos');
        $pedidos = Pedido::whereIn('id', $pedidosIds)->get();

        $warningMessages = [];  // Para acumular advertencias de stock
        $todosLosPedidosConStockSuficiente = true;  // Variable para verificar si todos los productos tienen stock suficiente
        $pedidosYaConfirmados = [];  // Para almacenar los pedidos que ya están confirmados

        // Verificar los pedidos y sus productos
        foreach ($pedidos as $pedido) {
            // Verificar si el pedido ya está confirmado
            if ($pedido->estado_pedido === 'confirmado') {
                $pedidosYaConfirmados[] = $pedido->id; // Acumulamos los IDs de los pedidos ya confirmados
                continue;  // Si ya está confirmado, pasamos al siguiente pedido sin hacer más operaciones
            }

            $pedidoProductos = $pedido->pedidoProductos;

            // Verificar stock de cada producto en el pedido
            foreach ($pedidoProductos as $pedidoProducto) {
                $producto = $pedidoProducto->producto;

                // Verificar si el stock es 0 en la sucursal con id 1
                $inventarioSucursal1 = Inventario::where('id_sucursal', 1)
                    ->where('id_producto', $producto->id)
                    ->first();

                if (!$inventarioSucursal1 || $inventarioSucursal1->cantidad <= 0) {
                    $warningMessages[] = 'El producto ' . $producto->nombre . ' en el pedido ' . $pedido->id . ' tiene un stock de 0 en la sucursal 1 y no está disponible para confirmar.';
                    $todosLosPedidosConStockSuficiente = false;
                }

                // Verificar si el stock es insuficiente en la sucursal con id 1
                if ($inventarioSucursal1 && $inventarioSucursal1->cantidad < $pedidoProducto->cantidad) {
                    $warningMessages[] = 'El producto ' . $producto->nombre . ' en el pedido ' . $pedido->id . ' tiene solo ' . $inventarioSucursal1->cantidad . ' unidades disponibles en la sucursal 1, pero se han solicitado ' . $pedidoProducto->cantidad . '. No está disponible para confirmar.';
                    $todosLosPedidosConStockSuficiente = false;
                }
            }
        }

        // Si hay productos con stock insuficiente en cualquier pedido o pedidos ya confirmados, no realizamos ninguna operación
        if (!$todosLosPedidosConStockSuficiente || count($pedidosYaConfirmados) > 0) {
            // Si hay pedidos ya confirmados, agregamos un mensaje a las advertencias
            if (count($pedidosYaConfirmados) > 0) {
                $warningMessages[] = 'Los siguientes pedidos ya están confirmados y no pueden ser modificados: ' . implode(', ', $pedidosYaConfirmados);
            }

            return response()->json([
                'warning' => implode('<br>', $warningMessages)  // Unimos las advertencias con un salto de línea
            ]);
        }

        // Si todos los productos tienen stock suficiente, procedemos a descontar el stock y confirmar los pedidos
        foreach ($pedidos as $pedido) {
            // Si el pedido ya está confirmado, no realizamos ninguna operación
            if ($pedido->estado_pedido === 'confirmado') {
                continue;
            }

            $pedidoProductos = $pedido->pedidoProductos;

            // Descontamos el stock de los productos y confirmamos el pedido
            foreach ($pedidoProductos as $pedidoProducto) {
                $producto = $pedidoProducto->producto;

                // Buscamos el inventario de la sucursal con ID 1
                $inventarioSucursal1 = Inventario::where('id_sucursal', 1)
                    ->where('id_producto', $producto->id)
                    ->first();

                if ($inventarioSucursal1) {
                    // Descontamos la cantidad del inventario de la sucursal 1
                    $inventarioSucursal1->cantidad -= $pedidoProducto->cantidad;
                    $inventarioSucursal1->save();
                }
            }

            // Marcar el pedido como confirmado
            $pedido->estado_pedido = 'confirmado';
            $pedido->save();
        }

        // Retornamos la respuesta de éxito si todo se procesó correctamente
        return response()->json(['success' => 'Los pedidos seleccionados han sido confirmados con éxito.']);
    }
    public function destroyPedido($id)
    {
        $pedido = Pedido::find($id);
        if ($pedido) {
            $pedido->delete();
        }

        return redirect()->route('orden.pedidos', $pedido->id_semana)
            ->with('success', 'Pedido eliminado exitosamente.');
    }
    public function addProduct($id)
    {
        $pedido = Pedido::find($id);
        $productos = Producto::all();

        if (!$pedido) {
            return redirect()->route('orden.index')
                ->with('error', 'Pedido no encontrado.');
        }

        return view('orden.add-product-modal', compact('id', 'pedido', 'productos'));
    }

    public function storeProduct(Request $request, $id)
    {
        // Obtener el pedido
        $pedido = Pedido::findOrFail($id);

        if (!$pedido) {
            return back()->withErrors(['pedido' => 'Pedido no encontrado.']);
        }

        // Validación de los campos del formulario
        $request->validate([
            'id_producto' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
            'precio' => 'required|numeric',
        ]);

        // Obtener el ID del usuario autenticado
        $usuarioId = auth()->user()->id;

        // Crear el registro en la tabla `pedido_producto`
        PedidoProducto::create([
            'id_pedido' => $pedido->id,
            'id_producto' => $request->input('id_producto'),
            'cantidad' => $request->input('cantidad'),
            'precio' => $request->input('precio'),
            'fecha' => now(),
            'id_usuario' => $usuarioId,
        ]);

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

        // Devolver una respuesta JSON para indicar éxito
        return response()->json([
            'success' => true,
            'totalCantidad' => $totalCantidad,
            'totalMonto' => $totalMonto
        ]);
    }
    public function eliminarProducto(Request $request)
    {
        $pedidoId = $request->input('id_pedido');
        $productoId = $request->input('id_producto');
        $cantidad = $request->input('cantidad');

        // Buscar el registro del producto en el pedido
        $pedidoProducto = PedidoProducto::where('id_pedido', $pedidoId)
            ->where('id_producto', $productoId)
            ->first();

        if ($pedidoProducto) {
            // Eliminar el registro
            $pedidoProducto->delete();

            // Actualizar el stock del producto
            $producto = $pedidoProducto->producto;
            $producto->descontarStockSucursal(-$cantidad, $pedidoProducto->id_usuario); // Ajustar según tu lógica de stock
        }

        return response()->json(['message' => 'Producto eliminado con éxito']);
    }

    public function confirmPedido(Pedido $pedido)
    {
        // Verificar si el pedido ya está confirmado
        if ($pedido->estado_pedido === 'confirmado') {
            return response()->json([
                'warning' => 'El pedido ya ha sido confirmado anteriormente y no puede ser modificado.'
            ]);
        }

        $pedidoProductos = $pedido->pedidoProductos;
        $warningMessages = [];
        $stockSuficiente = true;

        // Verificar stock en la sucursal con ID 1
        foreach ($pedidoProductos as $pedidoProducto) {
            $producto = $pedidoProducto->producto;

            // Buscar inventario de la sucursal con ID 1
            $inventarioSucursal1 = Inventario::where('id_sucursal', 1)
                ->where('id_producto', $producto->id)
                ->first();

            // Verificar si el producto está disponible en la sucursal 1
            if (!$inventarioSucursal1 || $inventarioSucursal1->cantidad <= 0) {
                $warningMessages[] = 'El producto ' . $producto->nombre . ' tiene un stock de 0 en la sucursal 1 y no está disponible para confirmar.';
                $stockSuficiente = false;
            }

            // Verificar si el stock es insuficiente
            if ($inventarioSucursal1 && $inventarioSucursal1->cantidad < $pedidoProducto->cantidad) {
                $warningMessages[] = 'El producto ' . $producto->nombre . ' tiene solo ' . $inventarioSucursal1->cantidad . ' unidades disponibles en la sucursal 1, pero se han solicitado ' . $pedidoProducto->cantidad . '. No está disponible para confirmar.';
                $stockSuficiente = false;
            }
        }

        // Si no hay suficiente stock, devolvemos advertencias
        if (!$stockSuficiente) {
            return response()->json([
                'warning' => implode('<br>', $warningMessages)
            ]);
        }

        // Si todos los productos tienen stock suficiente, proceder a confirmar el pedido
        foreach ($pedidoProductos as $pedidoProducto) {
            $producto = $pedidoProducto->producto;

            // Buscar inventario de la sucursal con ID 1
            $inventarioSucursal1 = Inventario::where('id_sucursal', 1)
                ->where('id_producto', $producto->id)
                ->first();

            if ($inventarioSucursal1) {
                // Descontar la cantidad correspondiente en el inventario de la sucursal 1
                $inventarioSucursal1->cantidad -= $pedidoProducto->cantidad;
                $inventarioSucursal1->save();
            }
        }

        // Marcar el pedido como confirmado
        $pedido->estado_pedido = 'confirmado';
        $pedido->save();

        return response()->json(['success' => 'El pedido ha sido confirmado con éxito.']);
    }
    public function devolverPedido(Request $request, $id)
    {
        try {
            // Buscar el pedido por ID
            $pedido = Pedido::findOrFail($id);

            // Verificar si el pedido está confirmado
            if ($pedido->estado_pedido !== 'confirmado') {
                return response()->json([
                    'error' => 'Solo se pueden devolver pedidos confirmados.'
                ], 400);
            }

            // Iniciar una transacción de base de datos para garantizar la integridad
            DB::beginTransaction();

            // Obtener los productos del pedido
            $pedidoProductos = $pedido->pedidoProductos;

            // Si no hay productos, lanzar una excepción
            if ($pedidoProductos->isEmpty()) {
                throw new \Exception('El pedido no tiene productos asociados.');
            }

            // Restaurar el stock de cada producto
            foreach ($pedidoProductos as $pedidoProducto) {
                $producto = $pedidoProducto->producto;

                if (!$producto) {
                    throw new \Exception('No se encontró un producto asociado al pedido.');
                }

                // Buscar el inventario de la sucursal 1
                $inventarioSucursal1 = Inventario::where('id_sucursal', 1)
                    ->where('id_producto', $producto->id)
                    ->first();

                // Si existe el inventario, aumentar la cantidad
                if ($inventarioSucursal1) {
                    $inventarioSucursal1->cantidad += $pedidoProducto->cantidad;
                    $inventarioSucursal1->save();
                } else {
                    // Si no existe, crear un nuevo registro de inventario
                    $nuevoInventario = new Inventario();
                    $nuevoInventario->id_sucursal = 1;
                    $nuevoInventario->id_producto = $producto->id;
                    $nuevoInventario->cantidad = $pedidoProducto->cantidad;
                    $nuevoInventario->save();
                }
            }

            // Cambiar el estado del pedido a 'pendiente'
            $pedido->estado_pedido = 'pendiente';
            $pedido->save();

            // Confirmar la transacción
            DB::commit();

            return response()->json([
                'success' => 'El pedido ha sido devuelto y el stock ha sido restaurado correctamente.'
            ]);
        } catch (\Exception $e) {
            // Si algo sale mal, revertir todos los cambios
            DB::rollBack();

            // Registrar el error
            Log::error('Error al devolver pedido: ' . $e->getMessage());

            return response()->json([
                'error' => 'Ha ocurrido un error al procesar la devolución: ' . $e->getMessage()
            ], 500);
        }
    }
    
    
    
    public function editPedidocuadernosucursal($id_envio, $id_pedido)
    {
        $pedido = Pedido::find($id_pedido);
        $productos = Producto::all(); // Lista de productos
        $semanas = Semana::all();     // Lista de semanas

        if (!$pedido) {
            return redirect()->route('orden.index')
                ->with('error', 'Pedido no encontrado.');
        }

        // Cargar productos del pedido
        $pedido->productos = $pedido->pedidoProductos;

        // Pasar ambos IDs a la vista
        return view('orden.editcuadernosucursal', compact('id_envio', 'id_pedido', 'pedido', 'productos', 'semanas'));
    }
    public function updatePedidocuadernosucursal(Request $request, $id)
    {
        // dd($request->all());
        // Validación de los campos del formulario
        // Validación de los campos del formulario
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ci' => 'required|string|max:20',
            'celular' => 'required|string|max:20',
            'destino' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'estado' => 'required|string|max:50',
            'cantidad_productos' => 'required|integer',
            'detalle' => 'nullable|string',
            'productos' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (!empty($value)) {
                        if (!is_string($value)) {
                            $fail("El campo $attribute debe ser una cadena JSON válida.");
                        }
                        try {
                            json_decode($value, true, 512, JSON_THROW_ON_ERROR);
                        } catch (JsonException $e) {
                            $fail("El campo $attribute debe contener JSON válido.");
                        }
                    }
                },
            ],
            'monto_deposito' => 'required|numeric',
            'monto_enviado_pagado' => 'nullable|numeric',
            'fecha' => 'nullable|date',
            'id_semana' => 'nullable|exists:semanas,id',
            'foto_comprobante' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'codigo' => 'nullable|string|max:100',

            // Nuevos campos
            'efectivo' => 'nullable|numeric',
            'transferencia_qr' => 'nullable|numeric',
            'garantia'  => 'nullable|string|max:20',
        ]);


        // Obtener el pedido
        $pedido = Pedido::findOrFail($id);

        // Si el usuario ha marcado para eliminar la imagen
        if ($request->input('foto_comprobante_eliminada') == 'true') {
            // Eliminar la imagen existente (si hay)
            if ($pedido->foto_comprobante) {
                Storage::disk('public')->delete('fotos/' . $pedido->foto_comprobante);
            }
            // No asignar ninguna nueva imagen
            $fotoPath = null;
        } else {
            // Si se sube una nueva imagen, guardarla
            $fotoPath = $request->file('foto_comprobante')
                ? $request->file('foto_comprobante')->store('fotos', 'public')
                : $pedido->foto_comprobante; // Mantener la imagen actual si no se sube una nueva
        }

        // Decodificar el JSON de productos
        $productos = json_decode($request->input('productos'), true);

        // Si el array de productos está vacío, recuperar los productos del pedido existente

        // Verificar que los productos sean válidos
        foreach ($productos as $producto) {
            if (!isset($producto['id_producto'], $producto['cantidad'], $producto['precio'])) {
                return back()->withErrors(['productos' => 'El JSON de productos tiene datos faltantes o incorrectos.']);
            }
        }
        // Extraer los nombres de los productos
        $productosNombres = [];

        // Recorrer los productos para obtener sus nombres
        foreach ($productos as $producto) {
            if (isset($producto['id_producto']) && is_numeric($producto['id_producto'])) {
                // Si el 'id_producto' es válido, obtener el nombre del producto
                $productoNombre = Producto::find($producto['id_producto'])->nombre ?? 'Producto no encontrado';
                $productosNombres[] = $productoNombre;
            } else {
                // Si el 'id_producto' no es válido, usamos el valor tal cual (por si es un código o nombre)
                $productosNombres[] = strtoupper($producto['id_producto']);
            }
        }

        // Unir los nombres de los productos con comas
        $productosString = implode(', ', $productosNombres);


        // Actualizar el pedido con los nuevos datos
        $pedido->update(array_merge($request->all(), [
            'foto_comprobante' => $fotoPath,
            'productos' => $productosString,  // Guardar los productos como una cadena
        ]));

        // Eliminar los registros antiguos de la tabla pedido_producto solo si se proporcionaron nuevos productos
        if (!empty($productos)) {
            PedidoProducto::where('id_pedido', $id)->delete();

            // Guardar los productos en la tabla pedido_producto
            foreach ($productos as $producto) {
                $productoModelo = Producto::find($producto['id_producto']);
                if (!$productoModelo) {
                    throw new \InvalidArgumentException('Producto no existe.');
                }

                // Obtener el ID del usuario autenticado
                $usuarioId = auth()->user()->id;

                // Crear el registro en la tabla pedido_producto
                PedidoProducto::create([
                    'id_pedido' => $pedido->id,
                    'id_producto' => $producto['id_producto'],
                    'cantidad' => $producto['cantidad'],
                    'precio' => $producto['precio'],
                    'fecha' => now(),
                    'id_usuario' => $usuarioId, // Asignar el ID del usuario
                ]);
            }
        }
        //quiero que el id_evio y id_pedido se envie a esta funcion 
        //  public function actualizarpedidoenvio(Request $request, $id_pedido, $id_envio) con this
        // Redirigir con mensaje de éxito
        $this->actualizarpedidoenvio($request->id_pedido, $request->id_envio);

        $sucursal = auth()->user()->sucursales->first();

        if (!$sucursal) {
            return redirect()->route('home')->with('error', 'No tienes una sucursal asignada.');
        }

        return redirect()->route('envioscuaderno.index.sucursal', ['id' => $sucursal->id])
            ->with('success', 'Pedido actualizado exitosamente.');
    }


public function confirmPedidosucursal(Pedido $pedido)
    {
        // Obtener la sucursal del usuario logueado
        $sucursalId = auth()->user()->sucursales->first()->id ?? null;

        if (!$sucursalId) {
            return response()->json([
                'error' => 'El usuario no tiene una sucursal asignada.'
            ], 400);
        }

        if ($pedido->estado_pedido === 'confirmado') {
            return response()->json([
                'warning' => 'El pedido ya ha sido confirmado anteriormente y no puede ser modificado.'
            ]);
        }

        $pedidoProductos = $pedido->pedidoProductos;
        $warningMessages = [];
        $stockSuficiente = true;

        // Verificar stock en la sucursal del usuario
        foreach ($pedidoProductos as $pedidoProducto) {
            $producto = $pedidoProducto->producto;

            $inventario = Inventario::where('id_sucursal', $sucursalId)
                ->where('id_producto', $producto->id)
                ->first();

            if (!$inventario || $inventario->cantidad <= 0) {
                $warningMessages[] = "El producto {$producto->nombre} tiene un stock de 0 en la sucursal y no está disponible.";
                $stockSuficiente = false;
            }

            if ($inventario && $inventario->cantidad < $pedidoProducto->cantidad) {
                $warningMessages[] = "El producto {$producto->nombre} tiene solo {$inventario->cantidad} unidades disponibles, pero se solicitaron {$pedidoProducto->cantidad}.";
                $stockSuficiente = false;
            }
        }

        if (!$stockSuficiente) {
            return response()->json([
                'warning' => implode('<br>', $warningMessages)
            ]);
        }

        // Descontar stock
        foreach ($pedidoProductos as $pedidoProducto) {
            $inventario = Inventario::where('id_sucursal', $sucursalId)
                ->where('id_producto', $pedidoProducto->id_producto)
                ->first();

            if ($inventario) {
                $inventario->cantidad -= $pedidoProducto->cantidad;
                $inventario->save();
            }
        }

        $pedido->estado_pedido = 'confirmado';
        $pedido->save();

        return response()->json(['success' => 'El pedido ha sido confirmado con éxito.']);
    }

    public function devolverPedidosucursal(Request $request, $id)
    {
        try {
            $pedido = Pedido::findOrFail($id);

            if ($pedido->estado_pedido !== 'confirmado') {
                return response()->json([
                    'error' => 'Solo se pueden devolver pedidos confirmados.'
                ], 400);
            }

            // Obtener la sucursal del usuario logueado
            $sucursalId = auth()->user()->sucursales->first()->id ?? null;

            if (!$sucursalId) {
                return response()->json([
                    'error' => 'El usuario no tiene una sucursal asignada.'
                ], 400);
            }

            DB::beginTransaction();

            $pedidoProductos = $pedido->pedidoProductos;

            if ($pedidoProductos->isEmpty()) {
                throw new \Exception('El pedido no tiene productos asociados.');
            }

            foreach ($pedidoProductos as $pedidoProducto) {
                $producto = $pedidoProducto->producto;

                if (!$producto) {
                    throw new \Exception('No se encontró un producto asociado al pedido.');
                }

                $inventario = Inventario::firstOrNew([
                    'id_sucursal' => $sucursalId,
                    'id_producto' => $producto->id
                ]);

                $inventario->cantidad += $pedidoProducto->cantidad;
                $inventario->save();
            }

            $pedido->estado_pedido = 'pendiente';
            $pedido->save();

            DB::commit();

            return response()->json([
                'success' => 'El pedido ha sido devuelto y el stock ha sido restaurado correctamente.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al devolver pedido: ' . $e->getMessage());

            return response()->json([
                'error' => 'Ha ocurrido un error al procesar la devolución: ' . $e->getMessage()
            ], 500);
        }
    }
}
