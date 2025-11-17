<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;

class SalesReportController extends Controller
{
    public function index()
    {
        $branches = \App\Models\Sucursale::all(); // Obtener todas las sucursales
        return view('sales_report.index', compact('branches'));
    }

    public function getSalesData(Request $request)
    {
        // Obtener el tipo de filtro (día, semana o mes)
        $filterType = $request->input('filter_type', 'day'); // Por defecto, filtrar por día
        $selectedDate = $request->input('date') ?? now()->format('Y-m-d');  // Asegura que la fecha sea del formato correcto
        $selectedWeek = $request->input('week'); // Semana seleccionada
        $selectedMonth = $request->input('month'); // Mes seleccionado
    
        $search = $request->input('search_term', null);  // Cambiado a search_term
        Log::info('Search term:', ['search' => $search]);
    
        // Filtrar por día, semana o mes
        $query = Venta::with(['user', 'ventaProductos.producto'])
            ->where('estado', 'NORMAL') // Filtrar solo ventas con estado NORMAL
            ->when($search, function ($q) use ($search) {
                return $q->where(function ($query) use ($search) {
                    // Buscar por nombre del cliente, costo total, tipo de pago y fecha
                    $query->where('nombre_cliente', 'like', "%{$search}%")
                        ->orWhere('costo_total', 'like', "%{$search}%")  // Cambiar si `costo_total` no debería usar LIKE
                        ->orWhere('fecha', 'like', "%{$search}%")
                        ->orWhere('tipo_pago', 'like', "%{$search}%")
                        // Buscar por nombre del producto a través de la relación
                        ->orWhereHas('ventaProductos.producto', function ($q) use ($search) {
                            $q->where('nombre', 'like', "%{$search}%");
                        })
                        // Buscar por nombre del vendedor (usuario)
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filterType === 'day', function ($q) use ($selectedDate) {
                return $q->whereDate('fecha', $selectedDate);
            })
            ->when($filterType === 'week', function ($q) use ($selectedWeek) {
                if ($selectedWeek) {
                    $startOfWeek = now()->parse($selectedWeek)->startOfWeek()->format('Y-m-d');
                    $endOfWeek = now()->parse($selectedWeek)->endOfWeek()->format('Y-m-d');
                    return $q->whereBetween('fecha', [$startOfWeek, $endOfWeek]);
                }
            })
            ->when($filterType === 'month', function ($q) use ($selectedMonth) {
                if ($selectedMonth) {
                    $month = now()->parse($selectedMonth)->month;
                    $year = now()->parse($selectedMonth)->year;
                    return $q->whereMonth('fecha', $month)->whereYear('fecha', $year);
                }
            })
            ->when($request->branch_id, function ($q) use ($request) {
                return $q->where('id_sucursal', $request->branch_id);
            });
    
        // Ejecutar la consulta
        $ventas = $query->get();
        Log::info($query->toSql());
        Log::info($query->getBindings());
    
        // Calcular totales por usuario
        $ventasPorUsuario = $ventas->groupBy('id_user')->map(function ($ventas, $userId) {
            return [
                'usuario' => $ventas->first()->user->name ?? 'Desconocido',
                'total_ventas' => $ventas->count(),
                'productos_vendidos' => $ventas->sum(function ($venta) {
                    return $venta->ventaProductos->sum('cantidad');
                }),
                'total_efectivo' => $ventas->sum('efectivo'),
                'total_qr' => $ventas->sum('qr'),
                'total_general' => $ventas->sum('costo_total'),
            ];
        })->values();
    
        Log::info($query->toSql());
        Log::info($query->getBindings());
    
        return DataTables::of($query)
            ->addColumn('productos', function ($venta) {
                return $venta->ventaProductos->map(function ($vp) {
                    return $vp->producto->nombre . ' (' . $vp->cantidad . ')';
                })->join(', ');
            })
            ->addColumn('vendedor', function ($venta) {
                return $venta->user ? $venta->user->name : 'N/A';
            })
            ->addColumn('cliente', function ($venta) {
                return $venta->nombre_cliente ?? 'N/A';
            })
            ->addColumn('costo', function ($venta) {
                return $venta->ventaProductos->map(function ($vp) {
                    return $vp->precio_unitario;
                })->join(', ');
            })
            ->with([
                'totalProductosVendidos' => $ventas->sum(function ($venta) {
                    return $venta->ventaProductos->sum('cantidad');
                }),
                'totalGanancia' => $ventas->sum('costo_total'),
                'totalEfectivo' => $ventas->sum('efectivo'),
                'totalQr' => $ventas->sum('qr'),
                'ventasPorUsuario' => $ventasPorUsuario,
            ])
            ->make(true);
    }
    
}
