<?php

namespace App\Http\Controllers;

use App\Models\Envio;
use App\Models\Pedido;
use App\Models\PedidoProducto;
use App\Models\Producto;
use App\Models\Semana;
use App\Models\Sucursale;
use Illuminate\Http\Request;

use Yajra\DataTables\Facades\DataTables;

class EnvioSucursalController extends Controller

{
    public function index()
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
        return redirect()->route('envioscuaderno.index.sucursal', ['id' => $sucursal->id]);
    }
    public function indexsucursales(Request $request, $id)
    {
        $semanas = Semana::latest('created_at')->take(5)->get();

        $sucursal = Sucursale::find($id);

        $productos = Producto::with(['inventarios' => function ($query) use ($sucursal) {
            $query->where('id_sucursal', $sucursal->id);
        }])->get()->map(function ($producto) use ($sucursal) {
            // Filtrar inventario de la sucursal específica
            $inventarioSucursal = $producto->inventarios->firstWhere('id_sucursal', $sucursal->id);

            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'cantidad_sucursal' => $inventarioSucursal ? $inventarioSucursal->cantidad : 0,
            ];
        });

        return view('envioscuaderno.indexsucursal', compact('semanas', 'productos'));
    }

    public function dataTablesucursal(Request $request)
    {
        // Obtener usuario logueado y su primera sucursal asignada
        $user = auth()->user();
        $sucursal = $user->sucursales->first();

        if (!$sucursal) {
            // Si no tiene una sucursal asignada, se aborta con un error 403
            abort(403, 'Este usuario no tiene una sucursal asignada.');
        }

        // Filtrar envíos por la sucursal del usuario
        $data = Envio::with('pedido')
            ->where('sucursal_id', $sucursal->id)
            ->latest();

        if ($request->ajax()) {
            // Filtros opcionales (checkboxes u otros filtros)
            if ($request->filled('lapaz')) {
                $data->where('lapaz', $request->lapaz);
            }
            if ($request->filled('enviado')) {
                $data->where('enviado', $request->enviado);
            }
            if ($request->filled('extra')) {
                $data->where('extra', $request->extra);
            }
            if ($request->filled('extra1')) {
                $data->where('extra1', $request->extra1);
            }

            // Filtros de fecha
            if ($request->filled('start_date')) {
                $data->where('fecha_hora_creada', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $data->where('fecha_hora_creada', '<=', $request->end_date);
            }

            // Búsqueda por celular o id
            if ($request->filled('celular')) {
                $search = $request->celular;
                $data->where(function ($query) use ($search) {
                    $query->where('celular', 'like', "%$search%")
                        ->orWhere('id', 'like', "%$search%")
                        ->orWhere('id_pedido', 'like', "%$search%");
                });
            }

            // Retornar DataTable
            return DataTables::of($data)
                ->addColumn('productos', function ($row) {
                    if ($row->pedido) {
                        return $row->pedido->productos;
                    }

                    $pedidoProductos = \App\Models\PedidoProducto::where('id_envio', $row->id)->get();
                    $nombres = $pedidoProductos->map(function ($item) {
                        return $item->producto->nombre ?? 'N/A';
                    });

                    return $nombres->implode(', ');
                })
                ->addColumn('cantidad_productos', function ($row) {
                    if ($row->pedido) {
                        return $row->pedido->cantidad_productos;
                    }

                    return \App\Models\PedidoProducto::where('id_envio', $row->id)->sum('cantidad');
                })
                ->addColumn('monto_deposito', function ($row) {
                    if ($row->pedido) {
                        return number_format($row->pedido->monto_deposito, 2);
                    }

                    $pedidoProductos = \App\Models\PedidoProducto::where('id_envio', $row->id)->get();

                    $monto = $pedidoProductos->sum(function ($pp) {
                        return $pp->cantidad * $pp->precio;
                    });

                    return number_format($monto, 2);
                })
                ->addColumn('action', function ($row) {
                    return '<button class="btn btn-sm btn-danger delete-envio" data-id="' . $row->id . '">Eliminar</button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return response()->json(['error' => 'No es una petición AJAX'], 400);
    }
    public function storesucursal(Request $request)
    {
        // Obtener el usuario logueado
        $user = auth()->user();

        // Obtener la primera sucursal asociada
        $sucursal = $user->sucursales->first();

        // Verificar si el usuario tiene una sucursal
        if (!$sucursal) {
            return redirect()->route('envioscuaderno.index.sucursal', ['id' => $sucursal->id])
                ->with('success', 'Envío creado correctamente.');
        }

        // Crear el envío con la sucursal asignada
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
        $envio->extra3 = false;
        $envio->fecha_hora_enviado = null;
        $envio->fecha_hora_creada = now();
        $envio->id_pedido = null;
        $envio->detalle = null;
        $envio->estado = null;

        // Aquí asignamos la sucursal del usuario
        $envio->sucursal_id = $sucursal->id;

        $envio->save();

        return redirect()->route('envioscuaderno.index.sucursal', ['id' => $sucursal->id])
            ->with('success', 'Envío creado correctamente.');
    }
}
