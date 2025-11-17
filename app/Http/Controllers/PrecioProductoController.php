<?php

namespace App\Http\Controllers;

use App\Models\PrecioProducto;
use App\Models\Producto;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use App\Models\Inventario;
class PrecioProductoController extends Controller
{
    /**
     * Mostrar la vista de productos.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('precioproducto.index');
    }

    /**
     * Obtener los datos de los productos y precios para DataTables.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getData(Request $request)
    {
        // Obtener productos con sus precios asociados
        $productos = Producto::with('precioProductos')->get();

        return DataTables::of($productos)
            ->addColumn('nombre', function ($producto) {
                return $producto->nombre;
            })
            ->addColumn('precio_jefa', function ($producto) {
                return optional($producto->precioProductos->first())->precio_jefa ?? '0';
            })
            ->addColumn('precio_unitario', function ($producto) {
                return optional($producto->precioProductos->first())->precio_unitario ?? '0';
            })
            ->addColumn('cantidad', function ($producto) {
                return optional($producto->precioProductos->first())->cantidad ?? '0';
            })
            ->addColumn('precio_general', function ($producto) {
                return optional($producto->precioProductos->first())->precio_general ?? '0';
            })
            ->addColumn('precio_extra', function ($producto) {
                return optional($producto->precioProductos->first())->precio_extra ?? '0';
            })
            ->addColumn('precio_preventa', function ($producto) {
                return optional($producto->precioProductos->first())->precio_preventa ?? '0';
            })
            ->addColumn('fecha_creada', function ($producto) {
                return optional($producto->precioProductos->first())->fecha_creada
                    ? optional($producto->precioProductos->first())->fecha_creada->format('Y-m-d H:i:s')
                    : '0';
            })
            ->rawColumns(['nombre', 'precio_jefa', 'precio_unitario','cantidad', 'precio_general', 'precio_extra', 'fecha_creada'])
            ->make(true);
    }

    /**
     * Actualizar los precios de un producto en tiempo real.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePrice(Request $request, $id)
    {
        // Encontrar el producto por su ID
        $producto = Producto::find($id);
    
        if (!$producto) {
            return response()->json(['success' => false, 'message' => 'Producto no encontrado']);
        }
    
        // Obtener el primer precio asociado al producto
        $precioProducto = $producto->precioProductos()->first();
    
        // Si no existe un precio asociado, creamos uno nuevo
        if (!$precioProducto) {
            $precioProducto = new PrecioProducto();
            $precioProducto->id_producto = $id;
        }
    
        // Obtener el campo y el valor enviados en la solicitud
        $campo = $request->input('campo');
        $valor = $request->input('valor');
    
        // Validar que el campo a actualizar está en el arreglo fillable del modelo PrecioProducto
        if (in_array($campo, $precioProducto->getFillable())) {
            // Actualizar el campo de precio dinámicamente
            $precioProducto->$campo = $valor;
            $precioProducto->save();
    
            // Devolver la respuesta con el precio actualizado
            return response()->json([
                'success' => true,
                'message' => 'Precio actualizado correctamente',
                'data' => $precioProducto // Retorna los datos del precio actualizado
            ]);
        }
    
        // Si el campo no es válido, retornamos un mensaje de error
        return response()->json(['success' => false, 'message' => 'Campo no válido']);
    }
   public function apiPrecio()
    {
        // Obtener todos los productos con las relaciones necesarias
        $productos = Producto::with([
            'precioProductos',
            'fotos:id,foto',
            'categoria',
            'marca'
        ])  // Cargar las relaciones de precios, fotos, categoría y marca
            ->select('id', 'nombre', 'descripcion', 'id_categoria', 'id_marca', 'created_at')  // Seleccionar los campos del producto
            ->orderBy('created_at', 'desc')  // Ordenar por created_at de forma descendente
            ->get();

        // Iterar sobre los productos para modificar el stock con el de la sucursal 1
        $productos->each(function ($producto) {
            // Obtener el stock del producto en la sucursal 1 usando el modelo Inventario
            $inventarioSucursal1 = Inventario::where('id_producto', $producto->id)
                ->where('id_sucursal', 1)  // Filtrar por la sucursal 1
                ->sum('cantidad');  // Obtener la suma de las cantidades

            // Asignar el stock de la sucursal 1 al producto (si es null asignar 0)
            $producto->stock = $inventarioSucursal1 ?? 0;  // Si $inventarioSucursal1 es null o 0, asigna 0

            // Verificar si hay precios asociados a este producto
            if ($producto->precioProductos->isNotEmpty()) {
                $producto->precioProductos->each(function ($precioProducto) {
                    // Asegurarse de que todos los campos sean válidos, o asignar 0 si son null
                    $precioProducto->precio_unitario = $precioProducto->precio_unitario ?? 0;
                    $precioProducto->cantidad = $precioProducto->cantidad ?? 0;
                    $precioProducto->precio_general = $precioProducto->precio_general ?? 0;
                    $precioProducto->precio_extra = $precioProducto->precio_extra ?? 0;
                    $precioProducto->precio_preventa = $precioProducto->precio_preventa ?? 0;
                    $precioProducto->fecha_creada = $precioProducto->fecha_creada ?? null;  // Si fecha_creada es null, mantener null
                });
            } else {
                // Si no hay precios, asignar todos los campos de precio a 0
                $producto->precioProductos = [
                    [
                        'id' => 0,
                        'id_producto' => $producto->id,
                        'precio_unitario' => 0,
                        'cantidad' => 0,
                        'precio_general' => 0,
                        'precio_extra' => 0,
                        'precio_preventa' => 0,
                        'fecha_creada' => null
                    ]
                ];
            }

            // Reemplazar el campo 'categoria' por el nombre de la categoría (si es necesario)
            if ($producto->categoria) {
                $producto->categoria = $producto->categoria->categoria;  // Reemplazar por el nombre de la categoría
            } else {
                $producto->categoria = 'Sin categoría';  // Si no tiene categoría, asignar 'Sin categoría'
            }

            // Reemplazar el id_marca por el nombre de la marca
            if ($producto->marca) {
                $producto->marca = $producto->marca->marca;  // Reemplazar el objeto Marca por el nombre
            } else {
                $producto->marca = 0;  // Si no tiene marca, asignar 0
            }
        });

        // Agrupar los productos por el nombre de la categoría
        $productosAgrupados = $productos->groupBy(function ($producto) {
            return $producto->categoria;  // Usar el nombre de la categoría para la agrupación
        });

        // Retornar los productos agrupados por categoría en formato JSON
        return response()->json($productosAgrupados);
    }
    
}
