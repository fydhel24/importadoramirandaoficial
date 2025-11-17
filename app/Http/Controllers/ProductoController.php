<?php

namespace App\Http\Controllers;

use App\Models\AuditoriaDetalle;
use App\Models\Producto;
use App\Models\Foto;
use App\Models\Categoria;

use App\Models\PedidoProducto;
use App\Models\Marca;
use App\Models\Tipo;
use App\Models\Cupo;
use App\Models\Inventario;
use App\Models\Pedido;
use App\Models\Semana;
use App\Models\StockLog;
use App\Models\Venta;
use Carbon\Carbon;
use Codedge\Fpdf\Fpdf\Fpdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


use App\Models\Sucursale;


use Yajra\DataTables\Facades\DataTables;
use JsonException;

use Illuminate\Support\Facades\Log;


class ProductoController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:productos.index')->only('index');
        $this->middleware('can:productos.create')->only('create', 'store');
        $this->middleware('can:productos.edit')->only('edit', 'update');
        $this->middleware('can:productos.destroy')->only('destroy');
    }
    public function generarPDF()
    {
        // Obtener todos los productos con sus precios
        $productos = Producto::with('precioProductos')->get();

        // Crear una instancia de FPDF
        $pdf = new Fpdf();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 14);

        // Título del documento
        $pdf->Cell(0, 10, 'Listado de Productos y Precios', 0, 1, 'C');
        $pdf->Ln(10);

        // Títulos de las columnas (encabezado)
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(10, 10, 'id', 1, 0, 'C');
        $pdf->Cell(85, 10, 'Producto', 1, 0, 'C');
        $pdf->Cell(20, 10, ' Jefa', 1, 0, 'C');
        $pdf->Cell(20, 10, ' caja', 1, 0, 'C');
        $pdf->Cell(20, 10, ' cantidad', 1, 0, 'C');
        $pdf->Cell(20, 10, ' Docena', 1, 0, 'C');
        $pdf->Cell(20, 10, ' unidad', 1, 0, 'C');

        $pdf->SetFont('Arial', '', 10);

        $pdf->Ln(9); // Espacio entre productos
        // Iterar sobre los productos
        foreach ($productos as $producto) {
            // Mostrar el nombre del producto y sus precios
            foreach ($producto->precioProductos as $precio) {
                $pdf->Cell(10, 10, $producto->id, 1, 0, 'C');

                $pdf->SetFont('Arial', '', 9);
                $productoss = utf8_decode(strtoupper($producto->nombre));

                $pdf->Cell(85, 10, $productoss, 1, 0, 'C');

                $pdf->SetFont('Arial', '', 12);
                $pdf->Cell(20, 10, number_format($precio->precio_jefa, 2, ',', '.'), 1, 0, 'C');
                $pdf->Cell(20, 10, number_format($precio->precio_unitario, 2, ',', '.'), 1, 0, 'C');

                $pdf->Cell(20, 10, number_format($precio->cantidad, 2, ',', '.'), 1, 0, 'C');
                $pdf->Cell(20, 10, number_format($precio->precio_general, 2, ',', '.'), 1, 0, 'C');
                $pdf->Cell(20, 10, number_format($precio->precio_extra, 2, ',', '.'), 1, 1, 'C');
            }

            $pdf->Ln(1); // Espacio entre productos
        }

        // Salida del PDF
        $pdf->Output('I', 'productos_precios.pdf');
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Producto::with(['categoria', 'marca', 'tipo', 'cupo', 'fotos'])
                ->where('estado_producto', '!=', 'inactivo')
                ->orWhereNull('estado_producto')
                ->select('id', 'nombre', 'descripcion', 'precio', 'stock', 'estado')
                ->when($request->search, function ($query) use ($request) {
                    $query->where('nombre', 'like', '%' . $request->search . '%')
                        /* ->orWhere('descripcion', 'like', '%' . $request->search . '%') */;
                });
            return DataTables::of($data)
                ->addColumn('semana', function ($producto) {
                    // Ejemplo de columna adicional, ajusta según tus necesidades
                    return $producto->categoria ? $producto->categoria->categoria : 'N/A';
                })
                ->addColumn('action', function ($producto) {
                    // Ejemplo de columna de acciones, ajusta según tus necesidades
                    $editUrl = route('productos.edit', $producto->id);
                    $deleteUrl = route('productos.destroy', $producto->id);
                    $deleteForm = "<form action='$deleteUrl' method='POST' style='display:inline;'>" .
                        csrf_field() .
                        method_field('DELETE') .
                        "<button type='submit' class='btn btn-light btn-sm' style='color: #dc3545; border-color: #dc3545; background-color: #f8d7da;'>" .
                        "<i class='fas fa-trash-alt'></i></button></form>";

                    return '<a href="' . $editUrl . '" class="btn btn-light btn-sm" style="color: #007bff; border-color: #007bff; background-color: #f0f8ff;">' .
                        '<i class="fas fa-edit"></i></a>' .
                        $deleteForm;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        // Para la vista sin AJAX
        $productos = Producto::with(['categoria', 'marca', 'tipo', 'cupo', 'fotos'])->get();
        $categorias = Categoria::all();
        $marcas = Marca::all();

        return view('productos.index', compact('productos', 'categorias', 'marcas'));
    }

    public function create()
    {
        $categorias = Categoria::all();
        $marcas = Marca::all();
        $tipos = Tipo::all();
        $cupos = Cupo::all();
        return view('productos.create', compact('categorias', 'marcas', 'tipos', 'cupos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'precio' => 'required|numeric',
            'precio_descuento' => 'nullable|numeric',
            'stock' => 'required|integer', // Solo para usarlo al crear inventario
            'estado' => 'required|boolean',
            'fecha' => 'required|date',
            'id_cupo' => 'nullable|exists:cupos,id',
            'id_tipo' => 'required|exists:tipos,id',
            'id_categoria' => 'required|exists:categorias,id',
            'id_marca' => 'required|exists:marcas,id',
            'fotos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Crear el producto sin 'stock'
        $producto = Producto::create(array_merge(
            $request->except(['id', 'stock']),
            ['stock' => 0]
        ));


        // Crear entrada de inventario en la sucursal 1
        Inventario::create([
            'id_producto' => $producto->id,
            'id_sucursal' => 1, // Sucursal 1 fija
            'cantidad' => $request->stock,
            'id_user' => auth()->id(), // Usuario autenticado
            'id_sucursal_origen' => null, // O puedes poner algún valor si aplica
            'id_user_destino' => null,
            'transfer_date' => now(),
        ]);

        // Guardar fotos
        if ($request->hasFile('fotos')) {
            foreach ($request->file('fotos') as $foto) {
                $path = $foto->store('fotos', 'public');
                $nuevaFoto = Foto::create(['foto' => $path]);
                $producto->fotos()->attach($nuevaFoto->id);
            }
        }

        return redirect()->route('productos.index')->with('success', 'Producto creado con éxito.');
    }



    public function edit(Producto $producto)
    {
        $categorias = Categoria::all();
        $marcas = Marca::all();
        $tipos = Tipo::all();
        $cupos = Cupo::all();
        $fotos = $producto->fotos;
        return view('productos.edit', compact('producto', 'categorias', 'marcas', 'tipos', 'cupos', 'fotos'));
    }

    public function update(Request $request, Producto $producto)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'precio' => 'required|numeric',
            'precio_descuento' => 'nullable|numeric',
            'stock' => 'required|integer',
            'estado' => 'required|boolean',
            'fecha' => 'required|date',
            'id_cupo' => 'nullable|exists:cupos,id',
            'id_tipo' => 'required|exists:tipos,id',
            'id_categoria' => 'required|exists:categorias,id',
            'id_marca' => 'required|exists:marcas,id',
            'fotos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $producto->update($request->except('fotos'));

        if ($request->hasFile('fotos')) {
            foreach ($request->file('fotos') as $foto) {
                $path = $foto->store('fotos', 'public');
                $fotoModel = Foto::create(['foto' => $path]);
                $producto->fotos()->attach($fotoModel->id);
            }
        }

        return redirect()->route('productos.index')->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy(Producto $producto)
    {
        $producto->estado_producto = 'inactivo';
        $producto->save();

        return redirect()->route('productos.index')->with('success', 'Producto desactivado exitosamente.');
    }

    public function destroyiamge(Foto $foto)
    {
        // Eliminar la relación de la foto con el producto
        $foto->productos()->detach(); // Esto elimina la relación en la tabla catalogos

        // Eliminar el archivo de la carpeta de almacenamiento
        if (Storage::disk('public')->exists($foto->foto)) {
            Storage::disk('public')->delete($foto->foto);
        }

        // Eliminar la foto de la base de datos
        $foto->delete();

        return redirect()->back()->with('success', 'Foto eliminada con éxito.');
    }
    public function lupe()
    {
        $productos = Producto::with(['categoria', 'marca', 'tipo', 'cupo', 'fotos'])->get();
        return response()->json($productos);
    }
    public function categoriasConProductos()
    {
        $categorias = Categoria::with([
            'productos.categoria',
            'productos.marca',
            'productos.tipo',
            'productos.cupo',
            'productos.fotos',
            'productos.precioProductos'  // Cargar la relación de precios de productos
        ])
            ->get()
            ->each(function ($categoria) {

                // Ordenar los productos dentro de cada categoría por el id (más grandes primero)
                $categoria->productos = $categoria->productos->sortBy('id'); // Ordenar productos por el id (más grandes primero)


                // Ordenar los productos dentro de cada categoría
                //  $categoria->productos = $categoria->productos->sortByDesc('created_at'); // Ordenar productos por la fecha de creación (más recientes primero)

                // Procesar los productos para agregar el precio_extra y stock de la sucursal con id 1
                $categoria->productos->each(function ($producto) {
                    // Si el producto tiene precios, agrega el precio_unitario o 0 si no tiene
                    $producto->precio_extra = $producto->precioProductos->first()->precio_extra ?? 0;

                    // Obtener el stock actual en la sucursal con id = 1
                    $stockSucursal1 = $producto->inventarios()->where('id_sucursal', 1)->first();
                    $producto->stock_sucursal_1 = $stockSucursal1 ? $stockSucursal1->cantidad : 0; // Asignar 0 si no tiene inventario
                });
            });

        return response()->json($categorias);
    }


    public function filtro_categorias(Request $request)
    {
        $idCategoria = $request->input('id_categoria');  // Obtener el id de la categoría desde la solicitud

        // Si no se proporciona id_categoria, se puede devolver todos los productos de todas las categorías
        $query = Categoria::with([
            'productos.categoria',
            'productos.marca',
            'productos.tipo',
            'productos.cupo',
            'productos.fotos',
            'productos.precioProductos',  // Cargar la relación de precios de productos
        ]);

        // Si se proporciona un id_categoria, filtrar solo esa categoría
        if ($idCategoria) {
            $query->where('id', $idCategoria);
        }

        // Obtener las categorías (si se proporciona un id_categoria, solo traerá esa categoría)
        $categorias = $query->get()->each(function ($categoria) {
            // Ordenar los productos dentro de cada categoría por id de manera descendente y tomar los últimos 10
            $categoria->productos = $categoria->productos->sortByDesc('id')->take(10);

            // Procesar los productos para agregar el precio_extra
            $categoria->productos->each(function ($producto) {
                // Si el producto tiene precios, agrega el precio_extra o 0 si no tiene
                $producto->precio_extra = $producto->precioProductos->first()->precio_extra ?? 0;
            });
        });

        return response()->json($categorias);
    }
    public function lupenuevo(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ci' => 'required|string|max:255',
            'celular' => 'required|string|max:255',
            'destino' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
            'estado' => 'required|string|max:255',
            'cantidad_productos' => 'required|integer',
            'detalle' => 'nullable|string',
            'productos' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!is_string($value)) {
                        $fail('The ' . $attribute . ' must be a string.');
                        return;
                    }
                    try {
                        json_decode($value, true, 512, JSON_THROW_ON_ERROR);
                    } catch (JsonException $e) {
                        $fail('The ' . $attribute . ' must be a valid JSON string.');
                    }
                },
            ],
            'monto_deposito' => 'required|numeric',
            'codigo' => 'nullable|string|max:50', // Agregamos el nuevo campo
            'monto_enviado_pagado' => 'required|numeric',
            'foto_comprobante' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'id_usuario' => 'nullable|integer',
        ]);

        // Handle 'null' string for id_usuario
        if ($request->input('id_usuario') === 'null') {
            $request->merge(['id_usuario' => null]);
        }

        // Decode the JSON string to an array
        $productos = json_decode($request->input('productos'), true);

        if (!is_array($productos)) {
            throw new \InvalidArgumentException('Invalid JSON format for productos');
        }

        // Inicializamos el array de nombres de productos
        $productosNombres = [];

        // Recorremos los productos para obtener sus nombres
        foreach ($productos as $producto) {
            // Verificar si 'id_producto' está presente y es numérico
            if (isset($producto['id_producto']) && is_numeric($producto['id_producto'])) {
                // Si 'id_producto' es numérico, obtenemos el nombre del producto
                $productoNombre = Producto::find($producto['id_producto'])->nombre ?? 'Producto no encontrado';
                $productosNombres[] = $productoNombre;
            } else {
                // Si no es un id_producto válido (como una cadena como '167W' o '2 POWERB MAGNETIC'),
                // usamos directamente el valor de producto (que se espera sea un nombre o código)
                $productosNombres[] = strtoupper($producto['id_producto']);
            }
        }

        // Unimos los nombres de los productos con comas
        $productosString = implode(', ', $productosNombres);

        // Store the photo if exists
        $fotoPath = $request->file('foto_comprobante')
            ? $request->file('foto_comprobante')->store('fotos', 'public')
            : null;

        // Get the last ID of the week
        /* $ultimaSemana = Semana::latest('id')->first();
        $id_semana = $ultimaSemana ? $ultimaSemana->id : null; */
        // Usar la semana 
        $semanaFija = Semana::find(102);
        if (!$semanaFija) {
            return response()->json([
                'error' => 'No se encontró la semana.'
            ], 404);
        }
        $id_semana = $semanaFija->id;


        // Create the pedido
        $dataToStore = array_merge($request->all(), [
            'foto_comprobante' => $fotoPath,
            'fecha' => now(),
            'id_semana' => $id_semana,
            'productos' => $productosString, // Agregamos el string de nombres de productos
            'codigo' => $request->input('codigo')
        ]);

        $pedido = Pedido::create($dataToStore);

        // Verify that each product exists before inserting into pedido_producto
        foreach ($productos as $producto) {
            $product = Producto::find($producto['id_producto']);
            if (!$product) {
                throw new \InvalidArgumentException('Product does not exist.');
            }

            PedidoProducto::create([
                'id_pedido' => $pedido->id,
                'id_producto' => $producto['id_producto'],
                'cantidad' => $producto['cantidad'],
                'precio' => $producto['precio'],
                'id_usuario' => $request->input('id_usuario'), // Can be null
                'fecha' => now(),
            ]);
        }

        // Return JSON response
        return response()->json([
            'success' => true,
            'message' => $pedido->id,
            'pedido' => $pedido,
            'codigo' => $pedido->codigo, // Agregamos el campo en la respuesta

            'productos' => $pedido->productos, // Assuming you have defined the relationship in the Pedido model
        ], 201);
    }
    public function lupepedido(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ci' => 'required|string|max:255',
            'celular' => 'required|string|max:255',
            'destino' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
            'estado' => 'required|string|max:255',
            'cantidad_productos' => 'required|integer',
            'detalle' => 'nullable|string',
            'productos' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!is_string($value)) {
                        $fail('The ' . $attribute . ' must be a string.');
                        return;
                    }
                    try {
                        json_decode($value, true, 512, JSON_THROW_ON_ERROR);
                    } catch (JsonException $e) {
                        $fail('The ' . $attribute . ' must be a valid JSON string.');
                    }
                },
            ],
            'monto_deposito' => 'required|numeric',
            'codigo' => 'nullable|string|max:50', // Agregamos el nuevo campo
            'monto_enviado_pagado' => 'required|numeric',
            'foto_comprobante' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'id_usuario' => 'nullable|integer',
        ]);

        // Handle 'null' string for id_usuario
        if ($request->input('id_usuario') === 'null') {
            $request->merge(['id_usuario' => null]);
        }

        // Decode the JSON string to an array
        $productos = json_decode($request->input('productos'), true);

        if (!is_array($productos)) {
            throw new \InvalidArgumentException('Invalid JSON format for productos');
        }

        // Inicializamos el array de nombres de productos
        $productosNombres = [];

        // Recorremos los productos para obtener sus nombres
        foreach ($productos as $producto) {
            // Verificar si 'id_producto' está presente y es numérico
            if (isset($producto['id_producto']) && is_numeric($producto['id_producto'])) {
                // Si 'id_producto' es numérico, obtenemos el nombre del producto
                $productoNombre = Producto::find($producto['id_producto'])->nombre ?? 'Producto no encontrado';
                $productosNombres[] = $productoNombre;
            } else {
                // Si no es un id_producto válido (como una cadena como '167W' o '2 POWERB MAGNETIC'),
                // usamos directamente el valor de producto (que se espera sea un nombre o código)
                $productosNombres[] = strtoupper($producto['id_producto']);
            }
        }

        // Unimos los nombres de los productos con comas
        $productosString = implode(', ', $productosNombres);

        // Store the photo if exists
        $fotoPath = $request->file('foto_comprobante')
            ? $request->file('foto_comprobante')->store('fotos', 'public')
            : null;

        // Get the last ID of the week
        /* $ultimaSemana = Semana::latest('id')->first();
        $id_semana = $ultimaSemana ? $ultimaSemana->id : null; */
        // Usar la semana 
        $semanaFija = Semana::find(103);
        if (!$semanaFija) {
            return response()->json([
                'error' => 'No se encontró la semana.'
            ], 404);
        }
        $id_semana = $semanaFija->id;


        // Create the pedido
        $dataToStore = array_merge($request->all(), [
            'foto_comprobante' => $fotoPath,
            'fecha' => now(),
            'id_semana' => $id_semana,
            'productos' => $productosString, // Agregamos el string de nombres de productos
            'codigo' => $request->input('codigo')
        ]);

        $pedido = Pedido::create($dataToStore);

        // Verify that each product exists before inserting into pedido_producto
        foreach ($productos as $producto) {
            $product = Producto::find($producto['id_producto']);
            if (!$product) {
                throw new \InvalidArgumentException('Product does not exist.');
            }

            PedidoProducto::create([
                'id_pedido' => $pedido->id,
                'id_producto' => $producto['id_producto'],
                'cantidad' => $producto['cantidad'],
                'precio' => $producto['precio'],
                'id_usuario' => $request->input('id_usuario'), // Can be null
                'fecha' => now(),
            ]);
        }

        // Return JSON response
        return response()->json([
            'success' => true,
            'message' => $pedido->id,
            'pedido' => $pedido,
            'codigo' => $pedido->codigo, // Agregamos el campo en la respuesta

            'productos' => $pedido->productos, // Assuming you have defined the relationship in the Pedido model
        ], 201);
    }
    public function lupestor(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ci' => 'required|string|max:255',
            'celular' => 'required|string|max:255',
            'destino' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
            'estado' => 'required|string|max:255',
            'cantidad_productos' => 'required|integer',
            'detalle' => 'nullable|string',
            'productos' => 'nullable|string',
            'monto_deposito' => 'required|numeric',
            'monto_enviado_pagado' => 'required|numeric',
            'foto_comprobante' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        $fotoPath = $request->file('foto_comprobante')
            ? $request->file('foto_comprobante')->store('fotos', 'public')
            : null;

        /* // Obtener el último ID de semana
        $ultimaSemana = Semana::latest('id')->first();
        $validatedData['id_semana'] = $ultimaSemana ? $ultimaSemana->id : null;
        $validatedData['fecha'] = Carbon::now(); */
        // Obtener la semana con ID 89
        $semanaFija = Semana::find(102);
        if (!$semanaFija) {
            return response()->json([
                'error' => 'No se encontró la semana.'
            ], 404);
        }

        $validatedData['id_semana'] = $semanaFija->id;
        $validatedData['fecha'] = Carbon::now();


        // Crear el pedido
        $dataToStore = array_merge($request->all(), [
            'foto_comprobante' => $fotoPath,
            'fecha' => $validatedData['fecha'],
            'id_semana' => $validatedData['id_semana'],
        ]);

        $pedido = Pedido::create($dataToStore);

        // Devolver respuesta JSON
        return response()->json([
            'success' => true,
            'message' => 'El pedido ha sido creado exitosamente.',
            'pedido' => $pedido,
        ], 201);
    }


    public function generarReporte(Request $request)
    {
        $query = Producto::query();

        // Filtros
        if ($request->filled('id_categoria')) {
            $query->where('id_categoria', $request->id_categoria);
        }
        if ($request->filled('id_marca')) {
            $query->where('id_marca', $request->id_marca);
        }

        // Obtiene productos filtrados
        $productos = $query->with(['categoria', 'marca', 'inventarios'])->get();

        // Crear una instancia de FPDF con orientación horizontal
        $pdf = new Fpdf('L'); // 'L' para horizontal
        $pdf->AddPage();

        // Configurar encabezado del PDF
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetTextColor(0, 51, 102); // Color azul oscuro para el encabezado
        $pdf->Cell(0, 10, 'REPORTE DE PRODUCTOS - IMPORTADORA MIRANDA', 0, 1, 'C');
        $pdf->Ln(10); // Salto de línea
        $pdf->Line(10, $pdf->GetY(), 280, $pdf->GetY()); // Línea horizontal
        $pdf->Ln(5); // Salto de línea para separación

        // Colores y fuente para el encabezado de la tabla
        $pdf->SetFillColor(200, 200, 255); // Fondo azul claro
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', 'B', 11);

        // Encabezado de la tabla (añadiendo nuevas columnas)
        $pdf->Cell(10, 10, 'id', 0, 0, 'C', true);
        $pdf->Cell(80, 10, 'Nombre', 0, 0, 'C', true);
        $pdf->Cell(30, 10, 'Precio', 0, 0, 'C', true);
        $pdf->Cell(15, 10, 'Stock', 0, 0, 'C', true);
        $pdf->Cell(20, 10, 'Stock S1', 0, 0, 'C', true);
        $pdf->Cell(20, 10, 'Stock S2', 0, 0, 'C', true);
        $pdf->Cell(20, 10, 'Stock S3', 0, 0, 'C', true);
        $pdf->Cell(20, 10, 'Stock S4', 0, 0, 'C', true);
        $pdf->Cell(35, 10, 'Categoria', 0, 0, 'C', true);
        $pdf->Cell(30, 10, 'Marca', 0, 1, 'C', true);

        // Cuerpo de la tabla
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetFillColor(245, 245, 245); // Fondo gris claro para las filas alternadas
        $pdf->SetTextColor(0, 0, 0);

        foreach ($productos as $index => $producto) {
            $fill = $index % 2 == 0; // Alternar colores de fila

            // Ajustar la posición vertical para cada nueva fila
            $pdf->SetFillColor($fill ? 245 : 255, $fill ? 245 : 255, 245); // Alternar colores

            // Nombre

            $id = utf8_decode($producto->id);
            $nombre = utf8_decode($producto->nombre);
            $pdf->SetFont('Arial', '', $pdf->GetStringWidth($id) > 75 ? 8 : 10); // Cambia el tamaño de la fuente si es necesario
            $pdf->SetFont('Arial', '', $pdf->GetStringWidth($nombre) > 75 ? 8 : 10); // Cambia el tamaño de la fuente si es necesario
            $pdf->Cell(10, 10, $id, 0, 0, 'C', $fill);
            $pdf->Cell(80, 10, $nombre, 0, 0, 'C', $fill);
            // Precio
            $pdf->SetFont('Arial', '', 10); // Restablecer tamaño de fuente
            $pdf->Cell(30, 10, number_format($producto->precio, 2), 0, 0, 'C', $fill);
            // Stock
            $pdf->Cell(15, 10, $producto->stock, 0, 0, 'C', $fill);

            // Obtener stock por sucursal
            $stockSucursal1 = $producto->inventarios()->where('id_sucursal', 1)->sum('cantidad');
            $stockSucursal2 = $producto->inventarios()->where('id_sucursal', 2)->sum('cantidad');
            $stockSucursal3 = $producto->inventarios()->where('id_sucursal', 3)->sum('cantidad');
            $stockSucursal4 = $producto->inventarios()->where('id_sucursal', 4)->sum('cantidad');

            $pdf->Cell(20, 10, $stockSucursal1, 0, 0, 'C', $fill);
            $pdf->Cell(20, 10, $stockSucursal2, 0, 0, 'C', $fill);
            $pdf->Cell(20, 10, $stockSucursal3, 0, 0, 'C', $fill);
            $pdf->Cell(20, 10, $stockSucursal4, 0, 0, 'C', $fill);

            // Categoria
            $categoria = isset($producto->categoria) ? utf8_decode($producto->categoria->categoria) : 'N/A';
            $pdf->SetFont('Arial', '', $pdf->GetStringWidth($categoria) > 35 ? 8 : 10); // Cambia el tamaño de la fuente si es necesario
            $pdf->Cell(35, 10, $categoria, 0, 0, 'C', $fill);

            // Marca
            $marca = isset($producto->marca) ? utf8_decode($producto->marca->marca) : 'N/A';
            $pdf->SetFont('Arial', '', 10); // Restablecer tamaño de fuente
            $pdf->Cell(30, 10, $marca, 0, 1, 'C', $fill);

            // Línea horizontal después de cada fila
            $pdf->Line(10, $pdf->GetY(), 280, $pdf->GetY()); // Línea horizontal
        }

        // Salida del PDF al navegador
        $pdf->Output();
        exit;
    }




    public function reporteStock(Request $request)
    {
        // Establecer las fechas predeterminadas si no se pasa ningún parámetro
        $startDate = null;
        $endDate = null;

        // Consulta de productos con las relaciones necesarias
        $query = Producto::with(['inventarios', 'ventaProductos']);

        // Si se pasan fechas, filtrar por ellas (opcional, si sigues usando filtros por fechas)
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();

            // Filtrar productos con ventas dentro del rango de fechas
            $query->whereHas('ventaProductos', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            });
        }

        // Si la solicitud es AJAX, filtrar solo por el término de búsqueda en el nombre
        if ($request->ajax()) {
            // Filtrar solo por nombre del producto
            if ($request->has('search') && $request->search['value']) {
                $search = $request->search['value'];
                $query->where('nombre', 'like', "%{$search}%");
            }

            // Registrar la consulta final antes de ejecutarla
            Log::info('Consulta de productos final:', ['query' => $query->toSql()]);

            // Devolver los datos con DataTables
            return DataTables::of($query)
                ->addColumn('total_vendido', function ($producto) use ($startDate, $endDate) {
                    // Calcular el total vendido dentro del rango de fechas
                    return $producto->ventaProductos->filter(function ($ventaProducto) use ($startDate, $endDate) {
                        $ventaFecha = $ventaProducto->created_at;
                        return (!$startDate || !$endDate || ($ventaFecha >= $startDate && $ventaFecha <= $endDate));
                    })->sum('cantidad');
                })
                ->addColumn('stocks', function ($producto) {
                    // Obtener el stock por sucursal para cada producto (procesar aquí)
                    $stocks = [];
                    foreach ($producto->inventarios as $inventario) {
                        $stocks[$inventario->id_sucursal] = $inventario->cantidad;
                    }
                    return $stocks;
                })
                ->rawColumns(['stocks'])
                ->make(true);
        }

        // Obtener las sucursales
         $sucursales = Producto::with('inventarios')->get()->pluck('inventarios')->flatten()->unique('id_sucursal')->sortBy('id_sucursal')->pluck('id_sucursal');
        $sucurnombre = Sucursale::all();
        // Obtener solo la sucursal con id 1
        /* $sucursales = Producto::with('inventarios')->get()
            ->pluck('inventarios')
            ->flatten()
            ->where('id_sucursal', 1) // Solo sucursal 1
            ->unique('id_sucursal')
            ->pluck('id_sucursal');

        $sucurnombre = Sucursale::where('id', 1)->get(); */

        // Registrar las sucursales obtenidas
        Log::info('Sucursales obtenidas:', $sucursales->toArray());

        return view('productos.stock', compact('startDate', 'endDate', 'sucursales', 'sucurnombre'));
    }
    public function reporteStockEdit(Request $request)
    {
        // Establecer las fechas predeterminadas si no se pasa ningún parámetro
        $startDate = null;
        $endDate = null;
        $categoriaId = null;

        // Consulta de productos con las relaciones necesarias
        $query = Producto::with(['inventarios', 'ventaProductos', 'categoria']);

        // Si se pasan fechas, filtrar por ellas (opcional, si sigues usando filtros por fechas)
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();

            // Filtrar productos con ventas dentro del rango de fechas
            $query->whereHas('ventaProductos', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            });
        }
        // Filtrar por categoría (si se pasa un id de categoría)
        if ($request->filled('categoria_id')) {
            $categoriaId = $request->categoria_id;
            $query->where('id_categoria', $categoriaId);
        }
        // Si la solicitud es AJAX, filtrar solo por el término de búsqueda en el nombre
        if ($request->ajax()) {
            // Filtrar solo por nombre del producto
            if ($request->has('search') && $request->search['value']) {
                $search = $request->search['value'];
                // Filtrar por id o nombre con LIKE
                $query->where(function ($query) use ($search) {
                    $query->where('id', 'like', "%{$search}%")
                        ->orWhere('nombre', 'like', "%{$search}%");
                });
            }

            // Registrar la consulta final antes de ejecutarla
            Log::info('Consulta de productos final:', ['query' => $query->toSql()]);

            // Devolver los datos con DataTables
            return DataTables::of($query)
                ->addColumn('total_vendido', function ($producto) use ($startDate, $endDate) {
                    // Calcular el total vendido dentro del rango de fechas
                    return $producto->ventaProductos->filter(function ($ventaProducto) use ($startDate, $endDate) {
                        $ventaFecha = $ventaProducto->created_at;
                        return (!$startDate || !$endDate || ($ventaFecha >= $startDate && $ventaFecha <= $endDate));
                    })->sum('cantidad');
                })
                ->addColumn('stocks', function ($producto) {
                    // Obtener el stock por sucursal para cada producto (procesar aquí)
                    $stocks = [];
                    foreach ($producto->inventarios as $inventario) {
                        $stocks[$inventario->id_sucursal] = $inventario->cantidad;
                    }
                    return $stocks;
                })
                ->rawColumns(['stocks'])
                ->make(true);
        }

          // Obtener todas las sucursales
        $sucursales = Producto::with('inventarios')->get()->pluck('inventarios')->flatten()->unique('id_sucursal')->sortBy('id_sucursal')->pluck('id_sucursal');
        $sucurnombre = Sucursale::all();
        // Obtener solo la sucursal con id 1
        /* $sucursales = Producto::with('inventarios')->get()
            ->pluck('inventarios')
            ->flatten()
            ->where('id_sucursal', 1) // Solo sucursal 1
            ->unique('id_sucursal')
            ->pluck('id_sucursal');

        $sucurnombre = Sucursale::where('id', 1)->get(); */

        // Obtener todas las categorías
        $categorias = Categoria::all();
        // Registrar las sucursales obtenidas
        Log::info('Sucursales obtenidas:', $sucursales->toArray());

        return view('productos.stockedit', compact('startDate', 'endDate', 'sucursales', 'sucurnombre', 'categorias'));
    }

    public function updateStock(Request $request)
    {
        // Validar los datos recibidos
        $validated = $request->validate([
            'product_id' => 'required|exists:productos,id',
            'sucursal_id' => 'required|exists:sucursales,id',
            'new_value' => 'required|numeric|min:0',
        ]);

        $producto = Producto::find($request->product_id);

        // Intentar encontrar el inventario de este producto en la sucursal especificada
        $inventario = $producto->inventarios()->where('id_sucursal', $request->sucursal_id)->first();

        $user = auth()->user();

        if ($inventario) {
            $valorAnterior = $inventario->cantidad;

            // Solo registrar si hubo un cambio
            if ($valorAnterior != $request->new_value) {
                // Actualizar el stock
                $inventario->cantidad = $request->new_value;
                $inventario->id_user = $user->id;
                $inventario->updated_at = now();
                $inventario->save();

                // Registrar log de stock
                StockLog::create([
                    'producto_id' => $producto->id,
                    'sucursal_id' => $request->sucursal_id,
                    'valor_anterior' => $valorAnterior,
                    'valor_nuevo' => $request->new_value,
                    'usuario_id' => $user->id,
                ]);
            }

            return response()->json(['success' => true]);
        } else {
            // Si el inventario no existe, crear uno nuevo
            $producto->inventarios()->create([
                'id_sucursal' => $request->sucursal_id,
                'cantidad' => $request->new_value,
                'id_user' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Registrar log también para nuevo inventario (valor anterior = 0)
            StockLog::create([
                'producto_id' => $producto->id,
                'sucursal_id' => $request->sucursal_id,
                'valor_anterior' => 0,
                'valor_nuevo' => $request->new_value,
                'usuario_id' => $user->id,
            ]);

            return response()->json(['success' => true]);
        }
    }
    public function updateAlmacenStock(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:productos,id',
            'new_value' => 'required|numeric|min:0',
        ]);

        $producto = Producto::find($request->product_id);

        if ($producto) {
            $valorAnterior = $producto->stock;
            $nuevoValor = $request->new_value;

            // Solo si cambia el valor
            if ($valorAnterior != $nuevoValor) {
                $producto->stock = $nuevoValor;
                $producto->save();

                // Registrar log para stock en almacén
                StockLog::create([
                    'producto_id' => $producto->id,
                    'sucursal_id' => null, // Almacén
                    'valor_anterior' => $valorAnterior,
                    'valor_nuevo' => $nuevoValor,
                    'usuario_id' => auth()->id(),
                ]);
            }

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Producto no encontrado']);
    }

    public function generatePdf(Request $request)
    {
        // Obtén las fechas de los filtros
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        // Asegúrate de que las fechas estén en el formato correcto
        if ($startDate && $endDate) {
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();
        }

        // Consulta de productos con las relaciones necesarias
        $query = Producto::with(['inventarios', 'ventaProductos']); // Cargar inventarios y ventas relacionadas

        // Filtro por rango de fechas (solo se aplicará a los productos vendidos)
        if ($startDate && $endDate) {
            $query->whereHas('ventaProductos', function ($query) use ($startDate, $endDate) {
                // Filtrar usando 'created_at' de la tabla 'venta_producto'
                $query->whereBetween('created_at', [$startDate, $endDate]);
            });
        }

        // Obtener productos filtrados
        $productos = $query->get();

        // Calcular el total vendido de cada producto dentro del rango de fechas
        foreach ($productos as $producto) {
            $producto->total_vendido = 0;

            // Verificar las ventas asociadas a este producto
            foreach ($producto->ventaProductos as $ventaProducto) {
                $ventaCreatedAt = $ventaProducto->created_at;

                // Si hay un filtro por fechas, solo contar las ventas dentro de ese rango
                if ($startDate && $endDate) {
                    if ($ventaCreatedAt >= $startDate && $ventaCreatedAt <= $endDate) {
                        // Sumar la cantidad vendida del producto
                        $producto->total_vendido += $ventaProducto->cantidad;
                    }
                } else {
                    // Si no hay filtro de fecha, sumar todas las ventas
                    $producto->total_vendido += $ventaProducto->cantidad;
                }
            }
        }

        // Obtener las sucursales únicas
        $sucursales = $productos->pluck('inventarios')->flatten()->unique('id_sucursal')->sortBy('id_sucursal')->pluck('id_sucursal');

        // Crear PDF
        $pdf = new Fpdf('L', 'mm', 'A4');  // Modo 'L' para orientación horizontal
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'Reporte de Productos', 0, 1, 'C');

        // Información de las fechas
        if ($startDate && $endDate) {
            $pdf->SetFont('Arial', 'I', 12);
            $pdf->Cell(0, 10, 'Desde: ' . $startDate->toDateString() . ' Hasta: ' . $endDate->toDateString(), 0, 1, 'C');
        }

        // Espacio entre el título y la tabla
        $pdf->Ln(10);

        // Encabezados de la tabla
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetFillColor(51, 122, 183);  // Color de fondo azul
        $pdf->Cell(100, 10, 'Nombre del Producto', 1, 0, 'C', true);
        $pdf->Cell(30, 10, 'StockAlmacen', 1, 0, 'C', true);
        $pdf->Cell(30, 10, 'Total Vendido', 1, 0, 'C', true);

        foreach ($sucursales as $sucursalId) {
            $pdf->Cell(30, 10, 'Stock. Suc.' . $sucursalId, 1, 0, 'C', true);
        }

        $pdf->Ln();  // Salto de línea para los datos

        // Datos de los productos
        foreach ($productos as $producto) {
            // Mostrar la fila de cada producto
            $pdf->SetFont('Arial', '', 10);
            $pdf->MultiCell(100, 10, $producto->nombre, 1, 'L');
            $pdf->SetXY($pdf->GetX() + 100, $pdf->GetY() - 10); // Restablecer la posición del cursor

            // Mostrar stock en almacén
            $pdf->Cell(30, 10, $producto->stock, 1, 0, 'C');
            $pdf->Cell(30, 10, $producto->total_vendido, 1, 0, 'C');

            // Mostrar el stock en cada sucursal
            foreach ($sucursales as $sucursalId) {
                $inventario = $producto->inventarios->firstWhere('id_sucursal', $sucursalId);
                $pdf->Cell(30, 10, $inventario ? $inventario->cantidad : 0, 1, 0, 'C');
            }

            $pdf->Ln();  // Salto de línea para la siguiente fila
        }

        // Retornar el PDF
        return response($pdf->Output('S', 'reporte_productos.pdf'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="reporte_productos.pdf"');
    }
    public function lupeestado()
    {
        // Obtener productos con estado 1 (destacados) y agregar precio_extra y cantidad de sucursal 1
        $productosDestacados = Producto::with(['categoria', 'marca', 'tipo', 'cupo', 'fotos', 'precioProductos', 'inventarios'])
            ->where('estado', 1)
            ->get()
            ->map(function ($producto) {
                // Obtener el primer precio (si existe) o asignar 0 si no hay precio
                $producto->precio_extra = $producto->precioProductos->first()->precio_extra ?? 0; // Si no hay precio, asignar 0

                // Obtener la cantidad del producto en la sucursal con id = 1
                $producto->cantidad_sucursal_1 = $producto->inventarios()->where('id_sucursal', 1)->sum('cantidad') ?? 0;

                return $producto;
            });

        // Obtener productos con estado 0 (no destacados) y agregar precio_extra y cantidad de sucursal 1
        $productosNoDestacados = Producto::with(['categoria', 'marca', 'tipo', 'cupo', 'fotos', 'precioProductos', 'inventarios'])
            ->where('estado', 0)
            ->get()
            ->map(function ($producto) {
                // Obtener el primer precio (si existe) o asignar 0 si no hay precio
                $producto->precio_extra = $producto->precioProductos->first()->precio_extra ?? 0; // Si no hay precio, asignar 0

                // Obtener la cantidad del producto en la sucursal con id = 1
                $producto->cantidad_sucursal_1 = $producto->inventarios()->where('id_sucursal', 1)->sum('cantidad') ?? 0;

                return $producto;
            });

        // Retornar las dos listas separadas en la respuesta
        return response()->json([
            'destacados' => $productosDestacados,
            'no_destacados' => $productosNoDestacados
        ]);
    }
    public function obtenerProductoPorId($id)
    {
        $producto = Producto::with(['categoria', 'marca', 'tipo', 'cupo', 'fotos', 'precioProductos', 'inventarios'])->find($id);
        if (!$producto) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }
        $producto->precio_extra = $producto->precioProductos->first()->precio_extra ?? 0;
        $producto->stock_sucursal_1 = $producto->inventarios()->where('id_sucursal', 1)->sum('cantidad') ?? 0;
        return response()->json($producto);
    }



    public function formulariosucursal1(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ci' => 'required|string|max:255',
            'celular' => 'required|string|max:255',
            'destino' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
            'estado' => 'required|string|max:255',
            'cantidad_productos' => 'required|integer',
            'detalle' => 'nullable|string',
            'productos' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!is_string($value)) {
                        $fail('The ' . $attribute . ' must be a string.');
                        return;
                    }
                    try {
                        json_decode($value, true, 512, JSON_THROW_ON_ERROR);
                    } catch (JsonException $e) {
                        $fail('The ' . $attribute . ' must be a valid JSON string.');
                    }
                },
            ],
            'monto_deposito' => 'required|numeric',
            'codigo' => 'nullable|string|max:50', // Agregamos el nuevo campo
            'monto_enviado_pagado' => 'required|numeric',
            'foto_comprobante' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'id_usuario' => 'nullable|integer',
        ]);

        // Handle 'null' string for id_usuario
        if ($request->input('id_usuario') === 'null') {
            $request->merge(['id_usuario' => null]);
        }

        // Decode the JSON string to an array
        $productos = json_decode($request->input('productos'), true);

        if (!is_array($productos)) {
            throw new \InvalidArgumentException('Invalid JSON format for productos');
        }

        // Inicializamos el array de nombres de productos
        $productosNombres = [];

        // Recorremos los productos para obtener sus nombres
        foreach ($productos as $producto) {
            // Verificar si 'id_producto' está presente y es numérico
            if (isset($producto['id_producto']) && is_numeric($producto['id_producto'])) {
                // Si 'id_producto' es numérico, obtenemos el nombre del producto
                $productoNombre = Producto::find($producto['id_producto'])->nombre ?? 'Producto no encontrado';
                $productosNombres[] = $productoNombre;
            } else {
                // Si no es un id_producto válido (como una cadena como '167W' o '2 POWERB MAGNETIC'),
                // usamos directamente el valor de producto (que se espera sea un nombre o código)
                $productosNombres[] = strtoupper($producto['id_producto']);
            }
        }

        // Unimos los nombres de los productos con comas
        $productosString = implode(', ', $productosNombres);

        // Store the photo if exists
        $fotoPath = $request->file('foto_comprobante')
            ? $request->file('foto_comprobante')->store('fotos', 'public')
            : null;

        // Get the last ID of the week
        /* $ultimaSemana = Semana::latest('id')->first();
        $id_semana = $ultimaSemana ? $ultimaSemana->id : null; */
        // Usar la semana 
        $semanaFija = Semana::find(157);
        if (!$semanaFija) {
            return response()->json([
                'error' => 'No se encontró la semana.'
            ], 404);
        }
        $id_semana = $semanaFija->id;


        // Create the pedido
        $dataToStore = array_merge($request->all(), [
            'foto_comprobante' => $fotoPath,
            'fecha' => now(),
            'id_semana' => $id_semana,
            'productos' => $productosString, // Agregamos el string de nombres de productos
            'codigo' => $request->input('codigo')
        ]);

        $pedido = Pedido::create($dataToStore);

        // Verify that each product exists before inserting into pedido_producto
        foreach ($productos as $producto) {
            $product = Producto::find($producto['id_producto']);
            if (!$product) {
                throw new \InvalidArgumentException('Product does not exist.');
            }

            PedidoProducto::create([
                'id_pedido' => $pedido->id,
                'id_producto' => $producto['id_producto'],
                'cantidad' => $producto['cantidad'],
                'precio' => $producto['precio'],
                'id_usuario' => $request->input('id_usuario'), // Can be null
                'fecha' => now(),
            ]);
        }

        // Return JSON response
        return response()->json([
            'success' => true,
            'message' => $pedido->id,
            'pedido' => $pedido,
            'codigo' => $pedido->codigo, // Agregamos el campo en la respuesta

            'productos' => $pedido->productos, // Assuming you have defined the relationship in the Pedido model
        ], 201);
    }
    public function formulariosucursal2(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ci' => 'required|string|max:255',
            'celular' => 'required|string|max:255',
            'destino' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
            'estado' => 'required|string|max:255',
            'cantidad_productos' => 'required|integer',
            'detalle' => 'nullable|string',
            'productos' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!is_string($value)) {
                        $fail('The ' . $attribute . ' must be a string.');
                        return;
                    }
                    try {
                        json_decode($value, true, 512, JSON_THROW_ON_ERROR);
                    } catch (JsonException $e) {
                        $fail('The ' . $attribute . ' must be a valid JSON string.');
                    }
                },
            ],
            'monto_deposito' => 'required|numeric',
            'codigo' => 'nullable|string|max:50', // Agregamos el nuevo campo
            'monto_enviado_pagado' => 'required|numeric',
            'foto_comprobante' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'id_usuario' => 'nullable|integer',
        ]);

        // Handle 'null' string for id_usuario
        if ($request->input('id_usuario') === 'null') {
            $request->merge(['id_usuario' => null]);
        }

        // Decode the JSON string to an array
        $productos = json_decode($request->input('productos'), true);

        if (!is_array($productos)) {
            throw new \InvalidArgumentException('Invalid JSON format for productos');
        }

        // Inicializamos el array de nombres de productos
        $productosNombres = [];

        // Recorremos los productos para obtener sus nombres
        foreach ($productos as $producto) {
            // Verificar si 'id_producto' está presente y es numérico
            if (isset($producto['id_producto']) && is_numeric($producto['id_producto'])) {
                // Si 'id_producto' es numérico, obtenemos el nombre del producto
                $productoNombre = Producto::find($producto['id_producto'])->nombre ?? 'Producto no encontrado';
                $productosNombres[] = $productoNombre;
            } else {
                // Si no es un id_producto válido (como una cadena como '167W' o '2 POWERB MAGNETIC'),
                // usamos directamente el valor de producto (que se espera sea un nombre o código)
                $productosNombres[] = strtoupper($producto['id_producto']);
            }
        }

        // Unimos los nombres de los productos con comas
        $productosString = implode(', ', $productosNombres);

        // Store the photo if exists
        $fotoPath = $request->file('foto_comprobante')
            ? $request->file('foto_comprobante')->store('fotos', 'public')
            : null;

        // Get the last ID of the week
        /* $ultimaSemana = Semana::latest('id')->first();
        $id_semana = $ultimaSemana ? $ultimaSemana->id : null; */
        // Usar la semana 
        $semanaFija = Semana::find(158);
        if (!$semanaFija) {
            return response()->json([
                'error' => 'No se encontró la semana.'
            ], 404);
        }
        $id_semana = $semanaFija->id;


        // Create the pedido
        $dataToStore = array_merge($request->all(), [
            'foto_comprobante' => $fotoPath,
            'fecha' => now(),
            'id_semana' => $id_semana,
            'productos' => $productosString, // Agregamos el string de nombres de productos
            'codigo' => $request->input('codigo')
        ]);

        $pedido = Pedido::create($dataToStore);

        // Verify that each product exists before inserting into pedido_producto
        foreach ($productos as $producto) {
            $product = Producto::find($producto['id_producto']);
            if (!$product) {
                throw new \InvalidArgumentException('Product does not exist.');
            }

            PedidoProducto::create([
                'id_pedido' => $pedido->id,
                'id_producto' => $producto['id_producto'],
                'cantidad' => $producto['cantidad'],
                'precio' => $producto['precio'],
                'id_usuario' => $request->input('id_usuario'), // Can be null
                'fecha' => now(),
            ]);
        }

        // Return JSON response
        return response()->json([
            'success' => true,
            'message' => $pedido->id,
            'pedido' => $pedido,
            'codigo' => $pedido->codigo, // Agregamos el campo en la respuesta

            'productos' => $pedido->productos, // Assuming you have defined the relationship in the Pedido model
        ], 201);
    }
    public function formulariosucursal3(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ci' => 'required|string|max:255',
            'celular' => 'required|string|max:255',
            'destino' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
            'estado' => 'required|string|max:255',
            'cantidad_productos' => 'required|integer',
            'detalle' => 'nullable|string',
            'productos' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!is_string($value)) {
                        $fail('The ' . $attribute . ' must be a string.');
                        return;
                    }
                    try {
                        json_decode($value, true, 512, JSON_THROW_ON_ERROR);
                    } catch (JsonException $e) {
                        $fail('The ' . $attribute . ' must be a valid JSON string.');
                    }
                },
            ],
            'monto_deposito' => 'required|numeric',
            'codigo' => 'nullable|string|max:50', // Agregamos el nuevo campo
            'monto_enviado_pagado' => 'required|numeric',
            'foto_comprobante' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'id_usuario' => 'nullable|integer',
        ]);

        // Handle 'null' string for id_usuario
        if ($request->input('id_usuario') === 'null') {
            $request->merge(['id_usuario' => null]);
        }

        // Decode the JSON string to an array
        $productos = json_decode($request->input('productos'), true);

        if (!is_array($productos)) {
            throw new \InvalidArgumentException('Invalid JSON format for productos');
        }

        // Inicializamos el array de nombres de productos
        $productosNombres = [];

        // Recorremos los productos para obtener sus nombres
        foreach ($productos as $producto) {
            // Verificar si 'id_producto' está presente y es numérico
            if (isset($producto['id_producto']) && is_numeric($producto['id_producto'])) {
                // Si 'id_producto' es numérico, obtenemos el nombre del producto
                $productoNombre = Producto::find($producto['id_producto'])->nombre ?? 'Producto no encontrado';
                $productosNombres[] = $productoNombre;
            } else {
                // Si no es un id_producto válido (como una cadena como '167W' o '2 POWERB MAGNETIC'),
                // usamos directamente el valor de producto (que se espera sea un nombre o código)
                $productosNombres[] = strtoupper($producto['id_producto']);
            }
        }

        // Unimos los nombres de los productos con comas
        $productosString = implode(', ', $productosNombres);

        // Store the photo if exists
        $fotoPath = $request->file('foto_comprobante')
            ? $request->file('foto_comprobante')->store('fotos', 'public')
            : null;

        // Get the last ID of the week
        /* $ultimaSemana = Semana::latest('id')->first();
        $id_semana = $ultimaSemana ? $ultimaSemana->id : null; */
        // Usar la semana 
        $semanaFija = Semana::find(159);
        if (!$semanaFija) {
            return response()->json([
                'error' => 'No se encontró la semana.'
            ], 404);
        }
        $id_semana = $semanaFija->id;


        // Create the pedido
        $dataToStore = array_merge($request->all(), [
            'foto_comprobante' => $fotoPath,
            'fecha' => now(),
            'id_semana' => $id_semana,
            'productos' => $productosString, // Agregamos el string de nombres de productos
            'codigo' => $request->input('codigo')
        ]);

        $pedido = Pedido::create($dataToStore);

        // Verify that each product exists before inserting into pedido_producto
        foreach ($productos as $producto) {
            $product = Producto::find($producto['id_producto']);
            if (!$product) {
                throw new \InvalidArgumentException('Product does not exist.');
            }

            PedidoProducto::create([
                'id_pedido' => $pedido->id,
                'id_producto' => $producto['id_producto'],
                'cantidad' => $producto['cantidad'],
                'precio' => $producto['precio'],
                'id_usuario' => $request->input('id_usuario'), // Can be null
                'fecha' => now(),
            ]);
        }

        // Return JSON response
        return response()->json([
            'success' => true,
            'message' => $pedido->id,
            'pedido' => $pedido,
            'codigo' => $pedido->codigo, // Agregamos el campo en la respuesta

            'productos' => $pedido->productos, // Assuming you have defined the relationship in the Pedido model
        ], 201);
    }
    public function formulariosucursal4(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ci' => 'required|string|max:255',
            'celular' => 'required|string|max:255',
            'destino' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
            'estado' => 'required|string|max:255',
            'cantidad_productos' => 'required|integer',
            'detalle' => 'nullable|string',
            'productos' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!is_string($value)) {
                        $fail('The ' . $attribute . ' must be a string.');
                        return;
                    }
                    try {
                        json_decode($value, true, 512, JSON_THROW_ON_ERROR);
                    } catch (JsonException $e) {
                        $fail('The ' . $attribute . ' must be a valid JSON string.');
                    }
                },
            ],
            'monto_deposito' => 'required|numeric',
            'codigo' => 'nullable|string|max:50', // Agregamos el nuevo campo
            'monto_enviado_pagado' => 'required|numeric',
            'foto_comprobante' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'id_usuario' => 'nullable|integer',
        ]);

        // Handle 'null' string for id_usuario
        if ($request->input('id_usuario') === 'null') {
            $request->merge(['id_usuario' => null]);
        }

        // Decode the JSON string to an array
        $productos = json_decode($request->input('productos'), true);

        if (!is_array($productos)) {
            throw new \InvalidArgumentException('Invalid JSON format for productos');
        }

        // Inicializamos el array de nombres de productos
        $productosNombres = [];

        // Recorremos los productos para obtener sus nombres
        foreach ($productos as $producto) {
            // Verificar si 'id_producto' está presente y es numérico
            if (isset($producto['id_producto']) && is_numeric($producto['id_producto'])) {
                // Si 'id_producto' es numérico, obtenemos el nombre del producto
                $productoNombre = Producto::find($producto['id_producto'])->nombre ?? 'Producto no encontrado';
                $productosNombres[] = $productoNombre;
            } else {
                // Si no es un id_producto válido (como una cadena como '167W' o '2 POWERB MAGNETIC'),
                // usamos directamente el valor de producto (que se espera sea un nombre o código)
                $productosNombres[] = strtoupper($producto['id_producto']);
            }
        }

        // Unimos los nombres de los productos con comas
        $productosString = implode(', ', $productosNombres);

        // Store the photo if exists
        $fotoPath = $request->file('foto_comprobante')
            ? $request->file('foto_comprobante')->store('fotos', 'public')
            : null;

        // Get the last ID of the week
        /* $ultimaSemana = Semana::latest('id')->first();
        $id_semana = $ultimaSemana ? $ultimaSemana->id : null; */
        // Usar la semana 
        $semanaFija = Semana::find(160);
        if (!$semanaFija) {
            return response()->json([
                'error' => 'No se encontró la semana.'
            ], 404);
        }
        $id_semana = $semanaFija->id;


        // Create the pedido
        $dataToStore = array_merge($request->all(), [
            'foto_comprobante' => $fotoPath,
            'fecha' => now(),
            'id_semana' => $id_semana,
            'productos' => $productosString, // Agregamos el string de nombres de productos
            'codigo' => $request->input('codigo')
        ]);

        $pedido = Pedido::create($dataToStore);

        // Verify that each product exists before inserting into pedido_producto
        foreach ($productos as $producto) {
            $product = Producto::find($producto['id_producto']);
            if (!$product) {
                throw new \InvalidArgumentException('Product does not exist.');
            }

            PedidoProducto::create([
                'id_pedido' => $pedido->id,
                'id_producto' => $producto['id_producto'],
                'cantidad' => $producto['cantidad'],
                'precio' => $producto['precio'],
                'id_usuario' => $request->input('id_usuario'), // Can be null
                'fecha' => now(),
            ]);
        }

        // Return JSON response
        return response()->json([
            'success' => true,
            'message' => $pedido->id,
            'pedido' => $pedido,
            'codigo' => $pedido->codigo, // Agregamos el campo en la respuesta

            'productos' => $pedido->productos, // Assuming you have defined the relationship in the Pedido model
        ], 201);
    }
}
