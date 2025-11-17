<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Cupo;
use App\Models\Inventario;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\Sucursale;
use App\Models\Venta;
use App\Models\VentaProducto;
use Codedge\Fpdf\Fpdf\Fpdf;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
        $query = Producto::query();

        // Filtros de categoría y marca
        if ($request->has('categoria') && $request->categoria != '') {
            $query->where('id_categoria', $request->categoria);
        }

        if ($request->has('marca') && $request->marca != '') {
            $query->where('id_marca', $request->marca);
        }

        // Obtenemos los productos disponibles en la sucursal $id
        $productos = Inventario::where('id_sucursal', $id)
            ->with(['producto.categoria', 'producto.marca', 'producto.fotos'])
            ->get();

        // Obtener todas las categorías y marcas disponibles para los filtros
        $categorias = Categoria::all();
        $marcas = Marca::all();

        // Agregamos el stock de cada producto
        foreach ($productos as $inventario) {
            // Agregar stock actual (total en todas las sucursales) y stock en esta sucursal
            $inventario->producto->stock_actual = $inventario->producto->getStockActual();
            $inventario->producto->stock_sucursal = $inventario->cantidad;  // Stock específico en esta sucursal
        }

        // Verificamos si hay productos disponibles
        if ($productos->isEmpty()) {
            return view('control.pro', compact('productos', 'id', 'categorias', 'marcas'));
        }

        // Generar un código de venta único
        $codigoVenta = 'V' . Str::random(6); // Cambia esto según tu lógica para el código

        // Obtener el primer cupo (según tu lógica)
        $primerCupo = Cupo::first();

        // Pasamos los datos a la vista
        return view('control.pro', compact('productos', 'id', 'categorias', 'marcas', 'primerCupo', 'codigoVenta'));
    }

    // Método para realizar el inventario
    public function realizarInventario(Request $request, $id)
    {
        // Validar la solicitud
        $request->validate([
            'id_producto' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
        ]);

        $producto = Producto::find($request->id_producto);
        $totalStock = Inventario::where('id_producto', $producto->id)->sum('cantidad') + $producto->stock;

        // Validar si la cantidad solicitada excede el stock total
        if ($request->cantidad > $totalStock) {
            return back()->withErrors(['cantidad' => 'La cantidad solicitada excede el stock disponible del producto.']);
        }

        // Buscar el inventario existente para el producto en la sucursal
        $inventarioExistente = Inventario::where('id_producto', $producto->id)
            ->where('id_sucursal', $id)
            ->first();

        if ($inventarioExistente) {
            // Si existe, sumar la cantidad
            $inventarioExistente->cantidad += $request->cantidad;
            $inventarioExistente->save();
        } else {
            // Si no existe, crear un nuevo registro
            $inventario = new Inventario();
            $inventario->id_producto = $producto->id;
            $inventario->id_sucursal = $id; // ID de la sucursal
            $inventario->cantidad = $request->cantidad; // La cantidad del pedido
            $inventario->id_user = auth()->id(); // ID del usuario logueado
            $inventario->save();
        }

        return redirect()->route('control.productos', $id)->with('success', 'Inventario actualizado correctamente.');
    }



    public function showInventarioForm($id)
    {
        // Obtener todos los productos con sus inventarios
        $productos = Producto::with('inventarios')->get();

        $productosDisponibles = [];

        foreach ($productos as $producto) {
            // Sumar la cantidad de stock en todas las sucursales
            $totalStockSucursales = $producto->inventarios->sum('cantidad');

            // Calcular el stock restante después de restar el stock total de las sucursales
            $stockRestante = $producto->stock - $totalStockSucursales;

            // Almacenar la información del producto
            $productosDisponibles[] = [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'stock_global' => $producto->stock, // El stock global del producto
                'stock_sucursales' => $totalStockSucursales, // Total stock asignado a las sucursales
                'stock_restante' => $stockRestante, // El stock restante después de descontar el inventario de las sucursales
            ];
        }

        return view('control.inventario', compact('id', 'productosDisponibles'));
    }

    //dd($request->all(), $ventaData, $ventaProductos);
    public function fin(Request $request)
    {
        // Validación de la entrada
        $request->validate([
            'nombre_cliente' => 'required|string',
            'costo_total' => 'required|numeric',
            'productos' => 'required|json',
            'id_sucursal' => 'required|numeric', // Asegúrate de que se esté enviando el id de sucursal
            'tipo_pago' => 'required|string', // Validar que sea una cadena
        ]);

        // Decodificar los productos de la venta desde el JSON
        $productos = json_decode($request->productos, true);

        // Crear la venta
        $venta = Venta::create([
            'fecha' => now(),
            'nombre_cliente' => $request->nombre_cliente,
            'costo_total' => $request->costo_total,
            'id_user' => auth()->id(),
            'ci' => $request->ci,
            'tipo_pago' => $request->tipo_pago, // Guardar el método de pago seleccionado
        ]);

        // Guardar los productos en venta_producto y realizar el descuento de stock
        foreach ($productos as $producto) {
            // Verificar si el producto existe
            $productoExistente = Producto::find($producto['id']);
            if (!$productoExistente) {
                return redirect()->back()->withErrors(['error' => 'El producto con ID ' . $producto['id'] . ' no existe.']);
            }

            // Verificar si hay suficiente stock en la sucursal para el producto
            $inventario = Inventario::where('id_producto', $producto['id'])
                ->where('id_sucursal', $request->id_sucursal) // Asegúrate de tener el ID de sucursal
                ->first();

            if (!$inventario) {
                return redirect()->back()->withErrors(['error' => 'No hay inventario disponible para el producto: ' . $productoExistente->nombre]);
            }

            // Verificar que haya suficiente stock disponible en la sucursal
            if ($inventario->cantidad < $producto['cantidad']) {
                return redirect()->back()->withErrors(['error' => 'No hay suficiente stock en la sucursal para el producto: ' . $productoExistente->nombre]);
            }

            // Crear el registro en venta_producto con el precio editado
            VentaProducto::create([
                'id_venta' => $venta->id,
                'id_producto' => $producto['id'],
                'cantidad' => $producto['cantidad'],
                'precio_unitario' => $producto['precio'], // Usar el precio editado
                'total' => $producto['total'], // Total editable
                'descuento' => 0, // Aquí puedes agregar el descuento si es necesario
            ]);

            // **Descontar el stock global del producto**
            $productoExistente->stock -= $producto['cantidad'];
            $productoExistente->save(); // Guardamos los cambios en el stock global del producto

            // **Descontar la cantidad en el inventario de la sucursal**
            $inventario->cantidad -= $producto['cantidad'];
            $inventario->save(); // Guardamos los cambios en el inventario de la sucursal
        }

        // Retornar respuesta
        return response()->json(['success' => true]);
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
}
