<?php

namespace App\Http\Controllers;

use App\Models\AuditoriaDetalle;
use App\Models\Caja;
use App\Models\StockLog;
use App\Models\Sucursale;
use App\Models\User;
use App\Models\Venta;
use Fpdf\Fpdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;


class AuditoriaController extends Controller
{
    public function auditoriaStock()
    {
        return view('productos.stocklog');
    }

    public function auditoriaStockData(Request $request)
    {
        $logs = StockLog::with(['producto', 'usuario', 'sucursal'])->latest();

        return DataTables::of($logs)
            ->addColumn('producto', fn($log) => $log->producto->nombre ?? 'N/A')
            ->addColumn('usuario', fn($log) => $log->usuario->name ?? 'Sistema')
            ->addColumn('sucursal', fn($log) => $log->sucursal->nombre ?? 'Almacén')
            ->editColumn('created_at', fn($log) => $log->created_at->format('d/m/Y H:i'))
            ->make(true);
    }
    public function guardarDetalle(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'sucursal_id' => 'required|exists:sucursales,id',
            'stock_sistema' => 'required|numeric',
            'stock_real' => 'required|numeric',
            'comentario' => 'nullable|string'
        ]);

        $auditoria = \App\Models\AuditoriaInventario::firstOrCreate([
            'sucursal_id' => $request->sucursal_id,
            'fecha' => now()->toDateString(),
            'usuario_id' => auth()->id()
        ]);

        \App\Models\AuditoriaDetalle::create([
            'auditoria_id' => $auditoria->id,
            'producto_id' => $request->producto_id,
            'stock_sistema' => $request->stock_sistema,
            'stock_real' => $request->stock_real,
            'diferencia' => $request->stock_real - $request->stock_sistema,
            'estado' => ($request->stock_real == $request->stock_sistema) ? 'correcto' : 'faltante',
            'comentario' => $request->comentario,
        ]);

        return response()->json(['success' => true]);
    }
    public function getStockActual(Request $request)
    {
        $productoId = $request->producto_id;
        $sucursalId = $request->sucursal_id;

        $stock = \App\Models\Inventario::where('id_producto', $productoId)
            ->where('id_sucursal', $sucursalId)
            ->value('cantidad');

        return response()->json([
            'stock' => $stock ?? 0  // Si no existe, devolver 0
        ]);
    }
    public function vistaAuditorias()
    {
        $sucursales = Sucursale::all(); // Si quieres filtrar por sucursal más adelante
        return view('productos.inventario', compact('sucursales'));
    }
     public function getAuditoriasData(Request $request)
    {
        $query = AuditoriaDetalle::with(['producto', 'auditoria.sucursal', 'auditoria.usuario']);

        // Filtro por sucursal
        if ($request->filled('sucursal_id')) {
            $query->whereHas('auditoria', function ($q) use ($request) {
                $q->where('sucursal_id', $request->sucursal_id);
            });
        }

        // Filtro por producto
        if ($request->filled('producto_nombre')) {
            $query->whereHas('producto', function ($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->producto_nombre . '%');
            });
        }
        return DataTables::of($query)
            ->addColumn('fecha', fn($row) => optional($row->auditoria)->fecha)
            ->addColumn('producto', fn($row) => optional($row->producto)->nombre ?? 'Producto eliminado')
            ->addColumn('sucursal', fn($row) => optional(optional($row->auditoria)->sucursal)->nombre)
            ->addColumn('usuario', fn($row) => optional(optional($row->auditoria)->usuario)->name)
            ->addColumn('stock_sistema', fn($row) => $row->stock_sistema)
            ->addColumn('stock_real', fn($row) => $row->stock_real)
            ->addColumn('diferencia', fn($row) => $row->stock_real - $row->stock_sistema)
            ->addColumn('comentario', fn($row) => $row->comentario)
            // Nuevas columnas añadidas
            ->addColumn('fecha_solucion', fn($row) => $row->fecha_solucion ? \Carbon\Carbon::parse($row->fecha_solucion)->format('Y-m-d') : '-')
            ->addColumn('observacion_solucion', fn($row) => $row->observacion_solucion ?? '-')
            // Fin nuevas columnas
            ->addColumn('estado', function ($row) {
                return match ($row->estado) {
                    'correcto' => '<span class="badge badge-success">Correcto</span>',
                    'faltante' => '<span class="badge badge-danger">Faltante</span>',
                    'solucionado' => '<span class="badge badge-primary">Solucionado</span>',
                    default => $row->estado,
                };
            })
            ->addColumn('action', function ($row) {
                if ($row->estado === 'faltante') {
                    return '<button class="btn btn-sm btn-warning btn-solucionar" data-id="' . $row->id . '">Solucionar</button>';
                }
                return '-';
            })
            ->rawColumns(['estado', 'action'])
            ->make(true);
    }
    /* public function getAuditoriasData(Request $request)
    {
        $query = AuditoriaDetalle::with(['producto', 'auditoria.sucursal', 'auditoria.usuario']);

        // Filtro por sucursal
        if ($request->filled('sucursal_id')) {
            $query->whereHas('auditoria', function ($q) use ($request) {
                $q->where('sucursal_id', $request->sucursal_id);
            });
        }

        // Filtro por producto
        if ($request->filled('producto_nombre')) {
            $query->whereHas('producto', function ($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->producto_nombre . '%');
            });
        }

        return DataTables::of($query)
            ->addColumn('fecha', fn($row) => optional($row->auditoria)->fecha)
            ->addColumn('producto', fn($row) => optional($row->producto)->nombre ?? 'Producto eliminado')
            ->addColumn('sucursal', fn($row) => optional(optional($row->auditoria)->sucursal)->nombre)
            ->addColumn('usuario', fn($row) => optional(optional($row->auditoria)->usuario)->name)
            ->addColumn('stock_sistema', fn($row) => $row->stock_sistema)
            ->addColumn('stock_real', fn($row) => $row->stock_real)
            ->addColumn('diferencia', fn($row) => $row->stock_real - $row->stock_sistema)
            ->addColumn('comentario', fn($row) => $row->comentario)
            ->addColumn('fecha_solucion', fn($row) => $row->fecha_solucion ? Carbon::parse($row->fecha_solucion)->format('Y-m-d') : '-')
            ->addColumn('observacion_solucion', fn($row) => $row->observacion_solucion ?? '-')
            ->addColumn('estado', function ($row) {
                return match ($row->estado) {
                    'correcto' => '<span class="badge badge-success">Correcto</span>',
                    'faltante' => '<span class="badge badge-danger">Faltante</span>',
                    'solucionado' => '<span class="badge badge-primary">Solucionado</span>',
                    default => $row->estado,
                };
            })
            ->addColumn('action', function ($row) {
                $buttons = '';
                // Botón Editar (disponible para todos)
                $buttons .= '<button class="btn btn-sm btn-info btn-editar mr-1" data-id="' . $row->id . '"><i class="fas fa-edit"></i> Editar</button>';

                // Botón Solucionar (solo si el estado es 'faltante')
                if ($row->estado === 'faltante') {
                    $buttons .= '<button class="btn btn-sm btn-warning btn-solucionar" data-id="' . $row->id . '"><i class="fas fa-check"></i> Solucionar</button>';
                }

                return $buttons ?: '-';
            })
            ->rawColumns(['estado', 'action'])
            ->make(true);
    } */
    // 2. Obtiene los datos de un registro específico para el modal de edición
    public function getDetalleAuditoria($id)
    {
        $detalle = AuditoriaDetalle::findOrFail($id);

        return response()->json([
            'id' => $detalle->id,
            'stock_sistema' => $detalle->stock_sistema,
            'stock_real' => $detalle->stock_real,
            'comentario' => $detalle->comentario,
            'estado' => $detalle->estado,
            'observacion_solucion' => $detalle->observacion_solucion,
            // Formatea la fecha, o null si no existe
            'fecha_solucion' => $detalle->fecha_solucion ? Carbon::parse($detalle->fecha_solucion)->format('Y-m-d') : null,
        ]);
    }

    // 3. Guarda la edición de un registro
    public function actualizarDetalle(Request $request, $id)
    {
        $request->validate([
            'stock_sistema' => 'required|integer|min:0',
            'stock_real' => 'required|integer|min:0',
            'comentario' => 'nullable|string',
            'estado' => 'required|string|in:correcto,faltante,solucionado',
            // Solo se requieren si el estado es 'solucionado'
            'observacion_solucion' => 'nullable|string',
            'fecha_solucion' => 'nullable|date',
        ]);

        $detalle = AuditoriaDetalle::findOrFail($id);

        $detalle->stock_sistema = $request->stock_sistema;
        $detalle->stock_real = $request->stock_real;
        $detalle->comentario = $request->comentario;
        $detalle->estado = $request->estado;

        // Manejo de la lógica de solución
        if ($request->estado === 'solucionado') {
            $detalle->observacion_solucion = $request->observacion_solucion;
            $detalle->fecha_solucion = $request->fecha_solucion ?? now();
        } else {
            // Si el estado cambia a otro, limpia los campos de solución
            $detalle->observacion_solucion = null;
            $detalle->fecha_solucion = null;
        }

        // Si el estado es "correcto" o "faltante", recalcular automáticamente basado en la diferencia
        if ($detalle->estado !== 'solucionado') {
            $detalle->estado = ($detalle->stock_sistema == $detalle->stock_real) ? 'correcto' : 'faltante';
        }

        $detalle->save();

        return response()->json(['success' => true]);
    }
    public function marcarComoSolucionado(Request $request, $id)
    {
        $request->validate([
            'observacion_solucion' => 'required|string',
            'fecha_solucion' => 'required|date', // Nueva validación para la fecha
        ]);

        $detalle = AuditoriaDetalle::findOrFail($id);
        $detalle->estado = 'solucionado';
        $detalle->observacion_solucion = $request->observacion_solucion;
        $detalle->fecha_solucion = $request->fecha_solucion; // Guardar la fecha proporcionada
        $detalle->save();

        return response()->json(['success' => true]);
    }
}
