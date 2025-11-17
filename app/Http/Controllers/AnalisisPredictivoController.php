<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use Illuminate\Http\Request;
use App\Models\Prediccion;
use App\Models\Producto;
use App\Models\VentaProducto;
use App\Models\Venta;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalisisPredictivoController extends Controller
{
    public function index(Request $request)
    {
        $productoId = $request->get('producto_id');

        // Lista de productos para el dropdown
        $listaProductos = Producto::all();

        // Inicializa variables
        $predicciones = [];
        $ventasUltimoMes = [];
        $totalVendido = null;
        $productos = $productoId ? Producto::where('id', $productoId)->get() : Producto::all();
        $alertas = [];
        $ventasHistoricas = collect();
        $ventasHistoricasJS = collect();
        $prediccionesJS = collect();

        // Inicializa la variable de demanda para las Cards
        $productoMayorDemanda = (object)['nombre' => 'Sin datos'];

        if ($productoId) {
            // Lógica para un producto seleccionado  -

            $productoSeleccionado = Producto::findOrFail($productoId);

            // Último mes
            $fechaInicioUltimoMes = Carbon::now()->subMonth()->startOfDay();

            // Ventas históricas agrupadas por día
            $ventasHistoricas = DB::table('venta_producto as vp')
                ->join('ventas as v', 'v.id', '=', 'vp.id_venta')
                ->where('vp.id_producto', $productoId)
                ->select(DB::raw('DATE(v.fecha) as fecha'), DB::raw('SUM(vp.cantidad) as cantidad'))
                ->groupBy(DB::raw('DATE(v.fecha)'))
                ->orderBy('fecha')
                ->get();


            // Ventas solo del último mes
            $ventasUltimoMes = $ventasHistoricas->filter(function ($venta) use ($fechaInicioUltimoMes) {
                return Carbon::parse($venta->fecha)->greaterThanOrEqualTo($fechaInicioUltimoMes);
            })->values();


            // Total vendido
            $totalVendido = $ventasHistoricas->sum('cantidad');

            // Completar días faltantes con 0
            $fechaInicio = $ventasHistoricas->isNotEmpty()
                ? Carbon::parse($ventasHistoricas->first()->fecha)
                : Carbon::now()->subMonth();

            $fechas = collect(Carbon::parse($fechaInicio)->toPeriod(Carbon::now()));
            $ventasCompletas = $fechas->map(function ($date) use ($ventasHistoricas) {
                $fechaStr = $date->format('Y-m-d');
                $venta = $ventasHistoricas->firstWhere('fecha', $fechaStr);
                return [
                    "fecha" => $fechaStr,
                    "cantidad" => $venta ? $venta->cantidad : 0
                ];
            })->values()->toArray();
            // Predicción con Flask solo si hay ventas en el último mes
            if ($ventasUltimoMes->count() > 0 && $ventasHistoricas->isNotEmpty()) {
                // Completar días faltantes en TODA la serie histórica
                $fechaInicio = Carbon::parse($ventasHistoricas->first()->fecha);
                $fechasHistoricas = collect(Carbon::parse($fechaInicio)->toPeriod(Carbon::now()));
                $ventasHistoricasCompletas = $fechasHistoricas->map(function ($date) use ($ventasHistoricas) {
                    $fechaStr = $date->format('Y-m-d');
                    $venta = $ventasHistoricas->firstWhere('fecha', $fechaStr);
                    return [
                        "fecha" => $fechaStr,
                        "cantidad" => $venta ? $venta->cantidad : 0
                    ];
                })->values()->toArray();

                // Llamada al modelo Flask (usa todas las ventas históricas)
                $response = Http::post('http://31.97.175.29:5010/predict', [
                    "ventas" => $ventasHistoricasCompletas,
                    "dias" => 7
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $predicciones = $data["predicciones"];
                    $errorPromedio = $data["error_promedio"] ?? null;
                    

                    //Guardar predicciones en BD
                    $fechasPred = array_map(fn($item) => Carbon::parse($item['ds'])->format('Y-m-d'), $predicciones);
                    Prediccion::where('producto_id', $productoId)
                        ->whereIn('fecha', $fechasPred)
                        ->delete();

                    foreach ($predicciones as $prediccion) {
                        Prediccion::create([
                            'producto_id' => $productoId,
                            'fecha' => Carbon::parse($prediccion['ds'])->format('Y-m-d'),
                            'ventas_predichas' => $prediccion['yhat'],
                            'error_promedio' => $errorPromedio,
                            
                        ]);
                    }
                }
            } else {
                //no se genera prediccion
                $predicciones = [];
                //$alertas[] = "Este producto no tiene datos actualizados de ventas recientes. No se generó predicción.";
            }

            // Datos para JS (agrupados por fecha)
            $ventasHistoricasJS = collect($ventasHistoricas)->map(fn($item) => [
                'fecha' => $item->fecha,
                'cantidad' => (float) $item->cantidad,
            ]);

            $prediccionesJS = collect($predicciones)->map(fn($item) => [
                'fecha' => $item['ds'],
                'yhat' => (float) $item['yhat'],
                'yhat_lower' => isset($item['yhat_lower']) ? (float) $item['yhat_lower'] : null,
                'yhat_upper' => isset($item['yhat_upper']) ? (float) $item['yhat_upper'] : null,
            ]);
            $productoMayorDemanda = $productoSeleccionado;
        } else {

            // Top 3 productos con más ventas históricas (sin límite de fecha)
            $topProductosIdsData = DB::table('venta_producto as vp')
                ->join('ventas as v', 'v.id', '=', 'vp.id_venta')
                ->groupBy('vp.id_producto')
                ->select('vp.id_producto', DB::raw('SUM(vp.cantidad) as total_cantidad_vendida'))
                ->orderBy('total_cantidad_vendida', 'desc')
                ->get();

            // Buscar productos con ventas el último mes
            $productosConVentasRecientes = DB::table('venta_producto as vp')
                ->join('ventas as v', 'v.id', '=', 'vp.id_venta')
                ->whereDate('v.fecha', '>=', Carbon::now()->subMonth())
                ->groupBy('vp.id_producto')
                ->select('vp.id_producto', DB::raw('SUM(vp.cantidad) as ventas_recientes'))
                ->orderBy('ventas_recientes', 'desc')
                ->pluck('id_producto')
                ->toArray();

            // Combinar ambos criterios: prioridad a los que tienen ventas recientes
            $topProductosIds = collect($topProductosIdsData)
                ->pluck('id_producto')
                ->filter(fn($id) => in_array($id, $productosConVentasRecientes)) // solo los que tienen ventas recientes
                ->take(3)
                ->values()
                ->toArray();

            // Si hay menos de 3, completamos con los siguientes de alto histórico
            if (count($topProductosIds) < 3) {
                $faltan = 3 - count($topProductosIds);
                $complemento = collect($topProductosIdsData)
                    ->pluck('id_producto')
                    ->diff($topProductosIds)
                    ->take($faltan)
                    ->values()
                    ->toArray();
                $topProductosIds = array_merge($topProductosIds, $complemento);
            }

            $topProductos = Producto::whereIn('id', $topProductosIds)->get();

            // Producto con mayor demanda
            $productoMayorDemandaId = $topProductosIds[0] ?? null;
            $productoMayorDemanda = $productoMayorDemandaId ? Producto::find($productoMayorDemandaId) : (object)['nombre' => 'Sin datos'];

            $ventasHistoricasJS = [];
            $prediccionesJS = [];

            foreach ($topProductos as $producto) {
                // Ventas históricas completas (TODAS las fechas) para la predicción
                $ventasHistCompleta = DB::table('venta_producto as vp')
                    ->join('ventas as v', 'v.id', '=', 'vp.id_venta')
                    ->where('vp.id_producto', $producto->id)
                    ->select(DB::raw('DATE(v.fecha) as fecha'), DB::raw('SUM(vp.cantidad) as cantidad'))
                    ->groupBy('fecha')
                    ->orderBy('fecha')
                    ->get();

                // Completar días faltantes
                $fechaInicio = Carbon::parse($ventasHistCompleta->first()->fecha);
                $fechas = collect(Carbon::parse($fechaInicio)->toPeriod(Carbon::now()));
                $ventasCompletas = $fechas->map(function ($date) use ($ventasHistCompleta) {
                    $fechaStr = $date->format('Y-m-d');
                    $venta = $ventasHistCompleta->firstWhere('fecha', $fechaStr);
                    return [
                        "fecha" => $fechaStr,
                        "cantidad" => $venta ? $venta->cantidad : 0
                    ];
                })->values()->toArray();

                // Enviar TODA la serie a Flask
                $response = Http::post('http://31.97.175.29:5010/predict', [
                    "ventas" => $ventasCompletas,
                    "dias" => 7
                ]);

                if ($response->successful()) {
                    $preds = $response->json()["predicciones"];

                    // Para el gráfico solo mostramos el último mes
                    $ventasUltimoMes = collect($ventasCompletas)->filter(function ($v) {
                        return Carbon::parse($v['fecha'])->greaterThanOrEqualTo(Carbon::now()->subMonth());
                    })->values();

                    $ventasHistoricasJS[$producto->nombre] = $ventasUltimoMes;
                    $prediccionesJS[$producto->nombre] = collect($preds)->map(fn($item) => [
                        'fecha' => $item['ds'],
                        'yhat' => (float)$item['yhat'],
                    ]);
                }
            }
        }

        if (!$productoId) {
            $totalVendido = DB::table('venta_producto as vp')
                ->join('ventas as v', 'v.id', '=', 'vp.id_venta')
                ->where('vp.id_producto', $productoMayorDemanda->id ?? 0)
                ->sum('vp.cantidad');
        }

        // Si sigue siendo null (sin ventas), poner 0
        $totalVendido = $totalVendido ?? 0;
        // Cards de resumen ---
        $cards = [
            'Producto con Mayor Demanda' => [
                'color' => 'info',
                'titulo' => $productoMayorDemanda->nombre ?? 'Sin datos',
                'descripcion' => $productoId
                    ? 'Comportamiento histórico del producto seleccionado.'
                    : 'Según ventas historicas.'
            ],
            'Resumen de Ventas' => [
                'color' => 'success',
                'titulo' => number_format($totalVendido) . ' unidades',
                'descripcion' => $productoId
                    ? 'Total vendido históricamente del producto seleccionado.'
                    : 'Total vendido del producto con mayor demanda.',
            ],
        ];

        return view('analisis_predictivo.index', compact(
            'cards',
            'alertas',
            'predicciones',
            'listaProductos',
            'productoId',
            'ventasUltimoMes',
            'productos',
            'totalVendido',
            'ventasHistoricasJS',
            'prediccionesJS'
        ));
    }

    public function searchProductos(Request $request)
    {
        $term = $request->input('term');

        if (empty($term)) {
            return response()->json([]);
        }

        // Buscar productos .
        $productos = Producto::where('nombre', 'like', '%' . $term . '%')
            ->select('id', 'nombre')
            ->limit(10)
            ->get();

        return response()->json($productos);
    }
}
