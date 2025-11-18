<?php

namespace App\Http\Controllers;

use App\Models\Sucursale;
use App\Models\Inventario;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\User;
use Illuminate\Http\Request;

class NewVentasController extends Controller
{
    public function index()
    {
        $sucursales = Sucursale::where('estado', 'activo')->get();
        return view('ventas.index', compact('sucursales'));
    }
public function productos(Request $request, $sucursalId)
{
    $sucursal = Sucursale::findOrFail($sucursalId);

    $query = Inventario::with([
            'producto.categoria',
            'producto.marca',
            'producto.fotos' => fn($q) => $q->limit(1)
        ])
        ->join('productos', 'productos.id', '=', 'inventario.id_producto') // ✅ Solo para ordenar/filtrar
        ->where('inventario.id_sucursal', $sucursalId)
        ->orderByDesc('inventario.favorito')
        ->orderByDesc('productos.estado')
        ->orderBy('productos.created_at', 'desc')
        ->select('inventario.*'); // 🔑 Clave: selecciona solo inventario

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where('productos.nombre', 'like', "%{$search}%");
    }

    $productos = $query->paginate(9);

    if ($request->ajax()) {
        $html = view('ventas.partials.productos-list', compact('productos'))->render();
        $pagination = $productos->links()->toHtml();
        return response()->json([
            'html' => $html,
            'pagination' => $pagination
        ]);
    }

    $categorias = Categoria::all();
    $marcas = Marca::all();

    $users = User::where(function ($q) use ($sucursalId) {
        $q->whereHas('sucursales', fn($sq) => $sq->where('sucursal_id', $sucursalId))
          ->whereHas('roles', fn($rq) => $rq->whereIn('name', ['Vendedor', 'Vendedor Antiguo', 'Encargado de pedidos']))
          ->where('status', 'active');
    })->orWhere('email', 'JHOELSURCO2@GMAIL.COM')->get();

    return view('ventas.productos', compact(
        'sucursal',
        'productos',
        'categorias',
        'marcas',
        'users',
        'sucursalId'
    ));
}

    public function sugerencias(Request $request, $sucursalId)
    {
        $q = $request->get('q', '');
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $results = Inventario::with('producto.fotos')
            ->where('id_sucursal', $sucursalId)
            ->join('productos', 'productos.id', '=', 'inventario.id_producto')
            ->where('productos.nombre', 'like', "%{$q}%")
            ->limit(6)
            ->get()
            ->map(fn($inv) => [
                'id' => $inv->id_producto,
                'nombre' => $inv->producto->nombre,
                'foto' => $inv->producto->fotos->first()?->foto
                    ? asset('storage/' . $inv->producto->fotos->first()->foto)
                    : null,
            ]);

        return response()->json($results);
    }
}
