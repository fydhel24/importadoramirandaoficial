<?php

namespace App\Http\Controllers;

use App\Models\PrecioProducto;
use App\Models\Producto;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;

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
    
}
