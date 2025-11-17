<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\Pedido;
use App\Models\PedidoProducto;
use App\Models\Venta;
use Illuminate\Http\Request;
use App\Models\Sucursale;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use FPDF;
use Illuminate\Support\Carbon;
use Yajra\DataTables\Facades\DataTables;

class PdfReportesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function showUserSalesReport(Request $request)
    {
        // Obtener el usuario autenticado
        $userId = Auth::id();

        // Consulta para obtener las ventas del usuario autenticado
        $query = Venta::with('ventaProductos.producto', 'sucursal')
            ->where('id_user', $userId); // Filtrar por el ID del usuario

        // Filtro por rango de fechas
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('fecha', [$request->start_date, $request->end_date]);
        }

        // Filtro por sucursal (opcional)
        if ($request->filled('id_sucursal')) {
            $query->where('id_sucursal', $request->id_sucursal);
        }

        // Filtro por tipo de pago (opcional)
        if ($request->filled('tipo_pago')) {
            $query->where('tipo_pago', $request->tipo_pago);
        }

        $ventas = $query->get();

        // Calcular totales
        $totalCosto = $ventas->sum('costo_total');
        $totalUtilidadBruta = $ventas->sum('utilidad_bruta');
        $totalVentas = $ventas->count();
        $totalProductosVendidos = $ventas->pluck('ventaProductos')->flatten()->count();
        $totalVentasBs = $totalCosto + $totalUtilidadBruta;

        return view('report.user_ventas', [
            'ventas' => $ventas,
            'totalCosto' => $totalCosto,
            'totalUtilidadBruta' => $totalUtilidadBruta,
            'totalVentas' => $totalVentas,
            'totalProductosVendidos' => $totalProductosVendidos,
            'totalVentasBs' => $totalVentasBs,
            'sucursales' => Sucursale::all(), // Puedes filtrar esto si solo quieres las sucursales del usuario
            'tiposPago' => Venta::select('tipo_pago')->distinct()->pluck('tipo_pago'), // Obtener tipos de pago únicos
        ]);
    }


    public function generateUserPdf(Request $request)
    {
        // Obtener el usuario autenticado
        $userId = Auth::id();

        // Consulta para obtener las ventas del usuario autenticado
        $query = Venta::with('ventaProductos.producto', 'sucursal')
            ->where('id_user', $userId); // Filtrar por el ID del usuario

        // Filtro por rango de fechas
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('fecha', [$request->start_date, $request->end_date]);
        }

        // Filtro por sucursal
        if ($request->filled('id_sucursal')) {
            $query->where('id_sucursal', $request->id_sucursal);
        }


        if ($request->filled('tipo_pago')) {
            $query->where('tipo_pago', $request->tipo_pago);
        }


        $ventas = $query->get();
        // Calcular totales
        $totalCosto = $ventas->sum('costo_total');
        $totalUtilidadBruta = $ventas->sum('utilidad_bruta');
        $totalVentas = $ventas->count();
        $totalProductosVendidos = $ventas->pluck('ventaProductos')->flatten()->count();
        $totalVentasBs = $totalCosto + $totalUtilidadBruta;

        // Crear PDF (modo horizontal)
        $pdf = new Fpdf('L', 'mm', 'A4');  // Modo 'L' para orientación horizontal
        $pdf->AliasNbPages();
        $pdf->AddPage();

        // Establecer fuentes y tamaños
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, "Reporte de Ventas - Importadora Miranda", 0, 1, 'C');
        $pdf->Ln(5);

        // Resumen de ventas
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, "Resumen de Ventas", 0, 1, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 8, "Costo Total: " . number_format($totalCosto, 2), 0, 1, 'L');
        $pdf->Cell(0, 8, "Utilidad Bruta: " . number_format($totalUtilidadBruta, 2), 0, 1, 'L');
        $pdf->Cell(0, 8, "Numero de Ventas en Total: " . $totalVentas, 0, 1, 'L');
        $pdf->Cell(0, 8, "Numero de Productos Vendidos en Total: " . $totalProductosVendidos, 0, 1, 'L');
        $pdf->Cell(0, 8, "Total de Ventas (Bs.): " . number_format($totalVentasBs, 2), 0, 1, 'L');
        $pdf->Ln(10);

        // Crear tabla con borde solo horizontal y sin líneas verticales
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(178, 216, 255);  // Celeste bebé (RGB: 178, 216, 255)

        // Encabezado de la tabla (sin líneas verticales)
        $pdf->Cell(25, 8, "Fecha", 1, 0, 'C', true);
        $pdf->Cell(60, 8, "Nombre del Cliente", 1, 0, 'C', true);
        $pdf->Cell(30, 8, "Costo Total", 1, 0, 'C', true);
        $pdf->Cell(35, 8, "Usuario", 1, 0, 'C', true);
        $pdf->Cell(30, 8, "Tipo de Pago", 1, 0, 'C', true);
        $pdf->Cell(20, 8, "Cantidad", 1, 0, 'C', true); // Nueva columna para la cantidad
        $pdf->Cell(90, 8, "Productos", 1, 1, 'C', true);

        $pdf->SetFont('Arial', '', 9);
        $fill = false; // Alternar el fondo de las filas

        foreach ($ventas as $venta) {
            // Datos de cada venta (sin líneas verticales)
            $pdf->Cell(25, 8, $venta->fecha, 0, 0, 'C', $fill);
            $pdf->Cell(60, 8, $venta->nombre_cliente ?? 'N/A', 0, 0, 'C', $fill);
            $pdf->Cell(30, 8, number_format($venta->costo_total, 2), 0, 0, 'C', $fill);
            $pdf->Cell(35, 8, $venta->user->name ?? 'N/A', 0, 0, 'C', $fill);
            $pdf->Cell(30, 8, $venta->tipo_pago ?? 'N/A', 0, 0, 'C', $fill);

            // Concatenar la cantidad de productos
            $cantidadProductos = 0;
            $productos = '';
            foreach ($venta->ventaProductos as $ventaProducto) {
                $producto = $ventaProducto->producto;  // Accede al producto relacionado
                $cantidadProductos += $ventaProducto->cantidad;  // Sumar la cantidad de productos
                $productos .= isset($producto->nombre) ? $producto->nombre . ' (' . $ventaProducto->cantidad . '), ' : '';
            }
            $productos = rtrim($productos, ', ');  // Eliminar la coma final

            // Mostrar cantidad en nueva columna
            $pdf->Cell(20, 8, $cantidadProductos, 0, 0, 'C', $fill);

            // Comprobar si el texto es demasiado largo y reducir el tamaño de la fuente si es necesario
            $pdf->SetFont('Arial', '', 9);
            if (strlen($productos) > 40) {
                // Si el texto es largo, reducir el tamaño de la fuente
                $pdf->SetFont('Arial', '', 7.5);
                $pdf->MultiCell(90, 8, $productos ?: 'N/A', 0, 'C', $fill);  // Ajustar el texto para que quepa
            } else {
                $pdf->MultiCell(90, 8, $productos ?: 'N/A', 0, 'C', $fill);
            }

            $fill = !$fill;  // Alternar el fondo de las filas
        }

        // Pie de página con numeración
        $pdf->SetY(-15);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 10, 'Página ' . $pdf->PageNo(), 0, 0, 'C');

        // Retornar el PDF como respuesta
        return response($pdf->Output('S', 'ventas.pdf'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="ventas.pdf"');
    }


    public function showSalesReport(Request $request)
    {
        if ($request->ajax()) {
            $query = Venta::query()
                ->with(['ventaProductos.producto:id,nombre', 'user:id,name', 'sucursal:id,nombre'])
                ->select('id', 'fecha', 'estado', 'id_user', 'id_sucursal', 'tipo_pago', 'costo_total', 'nombre_cliente');

            // Filtro por rango de fechas con hora
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $startDate = Carbon::parse($request->start_date);
                $endDate = Carbon::parse($request->end_date);
                $query->whereBetween('fecha', [$startDate, $endDate]);
            }

            // Filtro por sucursal
            if ($request->filled('id_sucursal')) {
                $query->where('id_sucursal', $request->id_sucursal);
            }

            // Filtro por usuario
            if ($request->filled('id_user')) {
                $query->where('id_user', $request->id_user);
            }

            // Filtro por tipo de pago
            if ($request->filled('tipo_pago')) {
                $query->where('tipo_pago', $request->tipo_pago);
            }
            // Filtro por estado
            if ($request->filled('estado')) {
                $query->where('estado', $request->estado);
            }


            // Implementar búsqueda global para columnas específicas
            return DataTables::of($query)
                ->filter(function ($query) use ($request) {
                    if ($request->filled('search.value')) {
                        $search = $request->input('search.value');
                        $query->where('nombre_cliente', 'like', "%{$search}%")
                            ->orWhere('tipo_pago', 'like', "%{$search}%")
                            ->orWhere('estado', 'like', "%{$search}%")
                            ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"))
                            ->orWhereHas('sucursal', fn($q) => $q->where('nombre', 'like', "%{$search}%"))
                            ->orWhereHas('ventaProductos.producto', fn($q) => $q->where('nombre', 'like', "%{$search}%"));
                    }
                })
                ->addColumn('cliente', fn($venta) => $venta->nombre_cliente ?? 'N/A')
                ->addColumn('usuario', fn($venta) => $venta->user->name ?? 'N/A')
                ->addColumn('productos', function ($venta) {
                    return $venta->ventaProductos->map(fn($p) => $p->producto->nombre . ' (' . $p->cantidad . ')')->implode(', ');
                })
                ->addColumn('estado', fn($venta) => $venta->estado ?? 'N/A')

                ->addColumn('sucursal', fn($venta) => $venta->sucursal->nombre ?? 'N/A')
                ->with([
                    'totalCosto' => number_format($query->sum('costo_total'), 2),
                    'totalUtilidadBruta' => number_format(
                        $query->get()->sum(fn($venta) => $venta->ventaProductos->sum(fn($p) => ($p->precio_unitario - $p->costo_unitario) * $p->cantidad)),
                        2
                    ),
                    'totalVentas' => $query->count(),
                    'totalProductosVendidos' => $query->withCount('ventaProductos as productos_vendidos_count')->get()->sum('productos_vendidos_count'),
                    'totalVentasBs' => number_format($query->sum('costo_total'), 2),
                ])
                ->make(true);
        }
        // Calcular ventas del día
        $ventasDia = Venta::whereDate('fecha', today())
            ->selectRaw('SUM(costo_total) as total_ventas_dia, COUNT(*) as num_ventas_dia')
            ->first();

        // Calcular ventas del mes
        $ventasMes = Venta::whereYear('fecha', now()->year)
            ->whereMonth('fecha', now()->month)
            ->selectRaw('SUM(costo_total) as total_ventas_mes, COUNT(*) as num_ventas_mes')
            ->first();

        // Cargar datos iniciales para la vista
        return view('report.ventas', [
            'sucursales' => Sucursale::select('id', 'nombre')->get(),
            'usuarios' => User::select('id', 'name')->get(),
            'tiposPago' => Venta::distinct()->pluck('tipo_pago'),
            'ventasDia' => $ventasDia,
            'ventasMes' => $ventasMes,
        ]);
    }

    public function generateDailyPdf(Request $request)
    {
        // Obtener el día desde la solicitud (o usar el día actual si no se proporciona)
        $day = $request->input('day', today()->toDateString()); // Si no se proporciona 'day', se usa el día actual

        // Ventas del día
        $query = Venta::query()
            ->with(['ventaProductos.producto', 'user', 'sucursal'])
            ->whereDate('fecha', $day);

        return $this->generatePdfFromQuery($query, "Reporte de Ventas del Día");
    }


    public function generateMonthlyPdf(Request $request)
    {
        // Obtener el mes y año desde la solicitud (o usar el mes y año actuales si no se proporcionan)
        $month = $request->input('month', now()->format('Y-m')); // Si no se proporciona 'month', se usa el mes y año actuales

        // Extraer el año y mes de la fecha
        $year = substr($month, 0, 4);
        $monthNumber = substr($month, 5, 2);

        // Ventas del mes
        $query = Venta::query()
            ->with(['ventaProductos.producto', 'user', 'sucursal'])
            ->whereYear('fecha', $year)
            ->whereMonth('fecha', $monthNumber);

        return $this->generatePdfFromQuery($query, "Reporte de Ventas del Mes");
    }

    public function reportDia(Request $request)
    {
        // Obtener la fecha desde el request
        $day = $request->input('day');

        // Establecer el rango del día
        $dayStart = $day . ' 00:00:00';
        $dayEnd = $day . ' 23:59:59';

        // Filtrar las ventas para ese día específico
        $ventasDia = Venta::whereBetween('fecha', [$dayStart, $dayEnd])
            ->selectRaw('SUM(costo_total) as total_ventas_dia, COUNT(id) as num_ventas_dia')
            ->first();

        // Formatear el total de ventas
        $ventasDia->total_ventas_dia = number_format($ventasDia->total_ventas_dia, 2, '.', ',');

        return response()->json($ventasDia);
    }

    public function reportMes(Request $request)
    {
        // Obtener el mes desde el request
        $month = $request->input('month');

        // Filtrar las ventas para ese mes específico
        $ventasMes = Venta::whereMonth('fecha', substr($month, 5, 2)) // Extraer mes
            ->whereYear('fecha', substr($month, 0, 4)) // Extraer año
            ->selectRaw('SUM(costo_total) as total_ventas_mes, COUNT(id) as num_ventas_mes')
            ->first();

        // Formatear el total de ventas
        $ventasMes->total_ventas_mes = number_format($ventasMes->total_ventas_mes, 2, '.', ',');

        return response()->json($ventasMes);
    }



    private function generatePdfFromQuery($query, $title)
    {
        $ventas = $query->get();
        $totalCosto = $ventas->sum('costo_total');
        $totalUtilidadBruta = $ventas->sum('utilidad_bruta');
        $totalVentas = $ventas->count();
        $totalProductosVendidos = $ventas->pluck('ventaProductos')->flatten()->count();
        $totalVentasBs = $totalCosto + $totalUtilidadBruta;

        $pdf = new Fpdf('L', 'mm', 'A4');
        $pdf->AliasNbPages();
        $pdf->AddPage();

        // Agregar los logos
        $pdf->Image(public_path('images/logo_old_2.png'), 10, 10, 30); // Logo izquierdo
        $pdf->Image(public_path('images/logo_old_2.png'), 250, 10, 30); // Logo derecho

        // Encabezado
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, utf8_decode($title . " - Importadora Miranda"), 0, 1, 'C');
        $pdf->Ln(5);

        // Resumen de ventas
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, utf8_decode("Resumen de Ventas"), 0, 1, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 8, utf8_decode("Costo Total: " . number_format($totalCosto, 2)), 0, 1, 'L');
        $pdf->Cell(0, 8, utf8_decode("Utilidad Bruta: " . number_format($totalUtilidadBruta, 2)), 0, 1, 'L');
        $pdf->Cell(0, 8, utf8_decode("Número de Ventas: " . $totalVentas), 0, 1, 'L');
        $pdf->Cell(0, 8, utf8_decode("Productos Vendidos: " . $totalProductosVendidos), 0, 1, 'L');
        $pdf->Cell(0, 8, utf8_decode("Total de Ventas (Bs.): " . number_format($totalVentasBs, 2)), 0, 1, 'L');
        $pdf->Ln(10);

        // Definir anchos de columnas
        $anchoFecha = 35;
        $anchoCliente = 35;
        $anchoCosto = 30;
        $anchoVendedor = 40;
        $anchoPago = 25;
        $anchoProductos = 90;
        $anchoEstado = 25;

        // Altura uniforme para todas las filas
        $alturaFila = 8;

        // Tabla - Encabezado
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(178, 216, 255); // Color de fondo para el encabezado

        // Encabezado de la tabla
        $pdf->Cell($anchoFecha, $alturaFila, utf8_decode("Fecha"), 0, 0, 'C', true);
        $pdf->Cell($anchoCliente, $alturaFila, utf8_decode("Cliente"), 0, 0, 'C', true);
        $pdf->Cell($anchoCosto, $alturaFila, utf8_decode("Costo Total"), 0, 0, 'C', true);
        $pdf->Cell($anchoVendedor, $alturaFila, utf8_decode("Vendedor"), 0, 0, 'C', true);
        $pdf->Cell($anchoPago, $alturaFila, utf8_decode("Pago"), 0, 0, 'C', true);
        $pdf->Cell($anchoProductos, $alturaFila, utf8_decode("Productos"), 0, 0, 'C', true);
        $pdf->Cell($anchoEstado, $alturaFila, utf8_decode("Estado"), 0, 1, 'C', true);

        // Línea de separación entre encabezado y datos
        $pdf->SetDrawColor(200, 200, 200);
        $pdf->Line(10, $pdf->GetY(), 287, $pdf->GetY());

        // Configuración para filas de datos
        $pdf->SetFont('Arial', '', 9);

        // Color de sombreado para filas alternadas (gris claro uniforme)
        $colorFilaAlternada = [240, 240, 240]; // Gris muy claro
        $pdf->SetFillColor($colorFilaAlternada[0], $colorFilaAlternada[1], $colorFilaAlternada[2]);

        $fill = false; // Alternar sombreado

        foreach ($ventas as $venta) {
            // Preparar lista de productos formateada
            $productos = [];
            foreach ($venta->ventaProductos as $p) {
                $productos[] = utf8_decode($p->producto->nombre . ' (' . $p->cantidad . ')');
            }
            $productosTexto = implode(", ", $productos);

            // Calcular la altura necesaria para la celda de productos
            $pdf->SetFont('Arial', '', 9);
            $stringWidth = $pdf->GetStringWidth($productosTexto);
            $neededRows = ceil($stringWidth / $anchoProductos);
            $rowHeight = max($alturaFila, $neededRows * 4); // Altura mínima o la necesaria para productos

            // Guardar posición inicial
            $startY = $pdf->GetY();

            //formatear la fecha y la hora en dos lineas separadas
            $fechaCompleta = $venta->fecha;
            $fechaPartes = explode(' ', $fechaCompleta);
            $soloFecha = isset($fechaPartes[0]) ? $fechaPartes[0] : '';
            $soloHora = isset($fechaPartes[1]) ? $fechaPartes[1] : '';

            // Dibujar fondo de toda la fila primero si es necesario
            if ($fill) {
                $pdf->Rect(10, $startY, $anchoFecha + $anchoCliente + $anchoCosto + $anchoVendedor +
                    $anchoPago + $anchoProductos + $anchoEstado, $rowHeight, 'F');
            }

            // Dibujar celdas (sin borde)
            $pdf->SetXY(10, $startY);
            $pdf->Cell($anchoFecha, $rowHeight, utf8_decode($venta->fecha), 0, 0, 'R', false);


            $pdf->Cell($anchoCliente, $rowHeight, utf8_decode($venta->nombre_cliente ?? 'N/A'), 0, 0, 'L', false);
            $pdf->Cell($anchoCosto, $rowHeight, number_format($venta->costo_total, 2), 0, 0, 'L', false);
            $pdf->Cell($anchoVendedor, $rowHeight, utf8_decode($venta->user->name ?? 'N/A'), 0, 0, 'L', false);
            $pdf->Cell($anchoPago, $rowHeight, utf8_decode($venta->tipo_pago ?? 'N/A'), 0, 0, 'C', false);

            // Posición para productos
            $xProductos = $pdf->GetX();
            $yProductos = $pdf->GetY();

            // Si el texto es muy largo, usar MultiCell
            if ($neededRows > 1) {
                $pdf->SetXY($xProductos, $yProductos);
                $pdf->MultiCell($anchoProductos, 4, $productosTexto, 0, 'L', false);
                $pdf->SetXY($xProductos + $anchoProductos, $yProductos);
            } else {
                // Si cabe en una línea, usar Cell normal
                $pdf->Cell($anchoProductos, $rowHeight, $productosTexto, 0, 0, 'L', false);
            }

            $pdf->Cell($anchoEstado, $rowHeight, utf8_decode($venta->estado ?? 'N/A'), 0, 1, 'C', false);

            // Línea divisoria entre filas (muy sutil)
            $pdf->SetDrawColor(230, 230, 230);
            $pdf->Line(10, $pdf->GetY(), 287, $pdf->GetY());

            // Alternar sombreado
            $fill = !$fill;
        }

        return response($pdf->Output(), 200)->header('Content-Type', 'application/pdf');
    }





    public function generatePdf(Request $request)
    {
        $query = Venta::query()
            ->with(['ventaProductos.producto', 'user', 'sucursal'])
            ->select('id', 'fecha', 'estado', 'id_user', 'id_sucursal', 'tipo_pago', 'costo_total', 'nombre_cliente');

        // Filtro por rango de fechas con hora
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $query->whereBetween('fecha', [$startDate, $endDate]);
        }

        // Filtro por sucursal
        if ($request->filled('id_sucursal')) {
            $query->where('id_sucursal', $request->id_sucursal);
        }

        // Filtro por usuario
        if ($request->filled('id_user')) {
            $query->where('id_user', $request->id_user);
        }
        if ($request->filled('tipo_pago')) {
            $query->where('tipo_pago', $request->tipo_pago);
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }


        $ventas = $query->get();
        // Calcular totales
        $totalCosto = $ventas->sum('costo_total');
        $totalUtilidadBruta = $ventas->sum('utilidad_bruta');
        $totalVentas = $ventas->count();
        $totalProductosVendidos = $ventas->pluck('ventaProductos')->flatten()->count();
        $totalVentasBs = $totalCosto + $totalUtilidadBruta;

        // Crear PDF (modo horizontal)
        $pdf = new Fpdf('L', 'mm', 'A4');  // Modo 'L' para orientación horizontal
        $pdf->AliasNbPages();
        $pdf->AddPage();
        // Agregar los logos
        $pdf->Image(public_path('images/logo_old_2.png'), 10, 10, 30); // Logo izquierdo
        $pdf->Image(public_path('images/logo_old_2.png'), 250, 10, 30); // Logo derecho
        // Establecer fuentes y tamaños
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, "Reporte de Ventas - Importadora Miranda", 0, 1, 'C');
        $pdf->Ln(5);

        // Resumen de ventas
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, "Resumen de Ventas", 0, 1, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 8, "Costo Total: " . number_format($totalCosto, 2), 0, 1, 'L');
        $pdf->Cell(0, 8, "Utilidad Bruta: " . number_format($totalUtilidadBruta, 2), 0, 1, 'L');
        $pdf->Cell(0, 8, "Numero de Ventas en Total: " . $totalVentas, 0, 1, 'L');
        $pdf->Cell(0, 8, "Numero de Productos Vendidos en Total: " . $totalProductosVendidos, 0, 1, 'L');
        $pdf->Cell(0, 8, "Total de Ventas (Bs.): " . number_format($totalVentasBs, 2), 0, 1, 'L');
        $pdf->Ln(10);

        // Crear tabla con borde solo horizontal y sin líneas verticales
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(178, 216, 255);  // Celeste bebé (RGB: 178, 216, 255)

        // Encabezado de la tabla (sin líneas verticales)
        $pdf->Cell(25, 8, "Fecha", 1, 0, 'C', true);
        $pdf->Cell(60, 8, "Nombre del Cliente", 1, 0, 'C', true);
        $pdf->Cell(30, 8, "Costo Total", 1, 0, 'C', true);
        $pdf->Cell(35, 8, "Usuario", 1, 0, 'C', true);
        $pdf->Cell(30, 8, "Tipo de Pago", 1, 0, 'C', true);
        $pdf->Cell(30, 8, "Estado", 1, 0, 'C', true);
        // Nueva columna para la cantidad
        $pdf->Cell(90, 8, "Productos", 1, 1, 'C', true);

        $pdf->SetFont('Arial', '', 9);
        $fill = false; // Alternar el fondo de las filas

        foreach ($ventas as $venta) {
            // Datos de cada venta (sin líneas verticales)
            $pdf->Cell(25, 8, utf8_decode($venta->fecha), 0, 0, 'C', $fill);
            $pdf->Cell(60, 8, utf8_decode($venta->nombre_cliente ?? 'N/A'), 0, 0, 'C', $fill);
            $pdf->Cell(30, 8, utf8_decode(number_format($venta->costo_total, 2)), 0, 0, 'C', $fill);
            $pdf->Cell(35, 8, utf8_decode($venta->user->name ?? 'N/A'), 0, 0, 'C', $fill);
            $pdf->Cell(30, 8, utf8_decode($venta->tipo_pago ?? 'N/A'), 0, 0, 'C', $fill);
            $pdf->Cell(30, 8, utf8_decode($venta->estado ?? 'N/A'), 0, 0, 'C', $fill);

            // Concatenar la cantidad de productos
            $cantidadProductos = 0;
            $productos = '';
            foreach ($venta->ventaProductos as $ventaProducto) {
                $producto = $ventaProducto->producto;  // Accede al producto relacionado
                $cantidadProductos += $ventaProducto->cantidad;  // Sumar la cantidad de productos
                $productos .= isset($producto->nombre) ? $producto->nombre . ' (' . $ventaProducto->cantidad . '), ' : '';
            }
            $productos = rtrim($productos, ', ');  // Eliminar la coma final



            // Comprobar si el texto es demasiado largo y reducir el tamaño de la fuente si es necesario
            $pdf->SetFont('Arial', '', 9);
            if (strlen($productos) > 40) {
                // Si el texto es largo, reducir el tamaño de la fuente
                $pdf->SetFont('Arial', '', 7.5);
                $pdf->MultiCell(90, 8, $productos ?: 'N/A', 0, 'C', $fill);  // Ajustar el texto para que quepa
            } else {
                $pdf->MultiCell(90, 8, $productos ?: 'N/A', 0, 'C', $fill);
            }

            $fill = !$fill;  // Alternar el fondo de las filas
        }

        // Pie de página con numeración
        $pdf->SetY(-15);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 10, 'Página ' . $pdf->PageNo(), 0, 0, 'C');

        // Retornar el PDF como respuesta
        return response($pdf->Output(), 200)
            ->header('Content-Type', 'application/pdf');
    }


    public function showInventoryReport(Request $request)
    {
        $query = Inventario::with('producto', 'sucursale', 'user');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        $inventarios = $query->get();

        return view('report.inventario', compact('inventarios'));
    }

    public function generateinventarioPdf(Request $request)
    {
        $query = Inventario::with('producto', 'sucursale', 'user');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        $inventarios = $query->get();
        $totalProductos = $inventarios->count();
        $totalCantidad = $inventarios->sum('cantidad');

        $pdf = new Fpdf('L', 'mm', 'A4'); // Orientación horizontal
        $pdf->AliasNbPages();
        $pdf->AddPage();

        // Título del reporte
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, "Reporte de Inventario - Importadora Miranda", 0, 1, 'C');
        $pdf->Ln(5);

        // Resumen de inventario
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, "Resumen de Inventario", 0, 1, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 8, "Total de Productos: " . $totalProductos, 0, 1, 'L');
        $pdf->Cell(0, 8, "Total de Cantidad: " . $totalCantidad, 0, 1, 'L');
        $pdf->Ln(10);

        // Ancho total de la página en formato horizontal (210 mm)
        $anchoPagina = 210;
        $anchoID = 20;
        $anchoProducto = 80;
        $anchoSucursal = 80;
        $anchoCantidad = 30;
        $anchoUsuario = 40;

        // Cabecera de la tabla (distribuir los anchos proporcionalmente)
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(178, 216, 255);
        $pdf->Cell($anchoID, 8, "ID", 1, 0, 'C', true);
        $pdf->Cell($anchoProducto, 8, "Nombre del Producto", 1, 0, 'C', true);
        $pdf->Cell($anchoSucursal, 8, "Sucursal", 1, 0, 'C', true);
        $pdf->Cell($anchoCantidad, 8, "Cantidad", 1, 0, 'C', true);
        $pdf->Cell($anchoUsuario, 8, "Usuario", 1, 1, 'C', true);

        $pdf->SetFont('Arial', '', 9);
        $fill = false;
        foreach ($inventarios as $inventario) {
            $pdf->Cell($anchoID, 8, $inventario->id, 0, 0, 'C', $fill);
            $productoNombre = $inventario->producto ? $inventario->producto->nombre : 'No disponible';
            $pdf->Cell($anchoProducto, 8, $productoNombre, 0, 0, 'C', $fill);
            $sucursalNombre = $inventario->sucursale ? $inventario->sucursale->nombre : 'No disponible';
            $pdf->Cell($anchoSucursal, 8, $sucursalNombre, 0, 0, 'C', $fill);
            $pdf->Cell($anchoCantidad, 8, $inventario->cantidad, 0, 0, 'C', $fill);
            $usuarioNombre = $inventario->user ? $inventario->user->name : 'No disponible';
            $pdf->Cell($anchoUsuario, 8, $usuarioNombre, 0, 1, 'C', $fill);

            $fill = !$fill;
        }

        // Pie de página
        $pdf->SetY(-15);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 10, 'Página ' . $pdf->PageNo(), 0, 0, 'C');

        return response($pdf->Output('S', 'inventario.pdf'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="inventario.pdf"');
    }


    public function showPedidosReport(Request $request)
    {
        // Obtener las fechas del request
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Crear una consulta base para los pedidos
        $query = Pedido::query();

        // Aplicar los filtros de fecha si existen
        if ($startDate) {
            $query->whereDate('fecha', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('fecha', '<=', $endDate);
        }

        // Obtener los pedidos filtrados
        $pedidos = $query->get();

        // Calcular los totales
        $totalPedidos = $pedidos->count();
        $totalMontoDepositado = $pedidos->sum('monto_deposito');
        $totalMontoEnviado = $pedidos->sum('monto_enviado_pagado');

        // Retornar la vista con los datos filtrados
        return view('report.pedidos', compact('pedidos', 'totalPedidos', 'totalMontoDepositado', 'totalMontoEnviado'));
    }

    public function generatepedidoPdf(Request $request)
    {
        // Obtener las fechas del request
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Crear una consulta base para los pedidos
        $query = Pedido::query();

        // Aplicar los filtros de fecha si existen
        if ($startDate) {
            $query->whereDate('fecha', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('fecha', '<=', $endDate);
        }

        // Obtener los pedidos filtrados
        $pedidos = $query->get();
        $totalPedidos = $pedidos->count();
        $totalMontoDepositado = $pedidos->sum('monto_deposito');
        $totalMontoEnviado = $pedidos->sum('monto_enviado_pagado');

        $pdf = new Fpdf('L', 'mm', 'A4'); // Orientación horizontal
        $pdf->AliasNbPages();
        $pdf->AddPage();

        // Título del reporte
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, "Reporte de Pedidos - Importadora Miranda", 0, 1, 'C');
        $pdf->Ln(5);

        // Resumen de pedidos
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, "Resumen de Pedidos", 0, 1, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 8, "Total de Pedidos: " . $totalPedidos, 0, 1, 'L');
        $pdf->Cell(0, 8, "Total de Monto Depositado: " . number_format($totalMontoDepositado, 2), 0, 1, 'L');
        $pdf->Cell(0, 8, "Total de Monto Enviado: " . number_format($totalMontoEnviado, 2), 0, 1, 'L');
        $pdf->Ln(10);

        // Ancho total de la página en formato horizontal (210 mm)
        $anchoPagina = 210;
        $anchoNombre = 80;
        $anchoCI = 25;
        $anchoCelular = 25;
        $anchoDestino = 50;
        $anchoEstado = 30;
        $anchoMontoDep = 20;
        $anchoMontoEnv = 20;
        $anchoFecha = 25;

        // Cabecera de la tabla (distribuir los anchos proporcionalmente)
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(178, 216, 255);
        $pdf->Cell($anchoNombre, 8, "Nombre", 1, 0, 'C', true);
        $pdf->Cell($anchoCI, 8, "CI", 1, 0, 'C', true);
        $pdf->Cell($anchoCelular, 8, "Celular", 1, 0, 'C', true);
        $pdf->Cell($anchoDestino, 8, "Destino", 1, 0, 'C', true);
        $pdf->Cell($anchoEstado, 8, "Estado", 1, 0, 'C', true);
        $pdf->Cell($anchoMontoDep, 8, "Monto Dep.", 1, 0, 'C', true);
        $pdf->Cell($anchoMontoEnv, 8, "Monto Env.", 1, 0, 'C', true);
        $pdf->Cell($anchoFecha, 8, "Fecha", 1, 1, 'C', true);

        $pdf->SetFont('Arial', '', 9);
        $fill = false;
        foreach ($pedidos as $pedido) {
            $pdf->Cell($anchoNombre, 8, $pedido->nombre, 0, 0, 'C', $fill);
            $pdf->Cell($anchoCI, 8, $pedido->ci, 0, 0, 'C', $fill);
            $pdf->Cell($anchoCelular, 8, $pedido->celular, 0, 0, 'C', $fill);
            $pdf->Cell($anchoDestino, 8, $pedido->destino, 0, 0, 'C', $fill);
            $pdf->Cell($anchoEstado, 8, $pedido->estado, 0, 0, 'C', $fill);
            $pdf->Cell($anchoMontoDep, 8, number_format($pedido->monto_deposito, 2), 0, 0, 'C', $fill);
            $pdf->Cell($anchoMontoEnv, 8, number_format($pedido->monto_enviado_pagado, 2), 0, 0, 'C', $fill);
            $pdf->Cell($anchoFecha, 8, $pedido->fecha, 0, 1, 'C', $fill);
            $fill = !$fill;
        }

        // Pie de página
        $pdf->SetY(-15);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 10, 'Página ' . $pdf->PageNo(), 0, 0, 'C');

        return response($pdf->Output('S', 'pedidos.pdf'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="pedidos.pdf"');
    }

    public function showPedidoProductosReport(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        $pedidoProductos = PedidoProducto::with('pedido', 'producto', 'usuario');

        // Filtrar por rango de fechas si se especifica
        if ($fechaInicio && $fechaFin) {
            $pedidoProductos = $pedidoProductos->whereHas('pedido', function ($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
            });
        }

        // Paginación de resultados
        $pedidoProductos = $pedidoProductos->paginate(10);

        // Calcular los totales
        $totalPedidos = $pedidoProductos->total();
        $totalCantidad = $pedidoProductos->sum('cantidad');
        $totalPrecio = $pedidoProductos->sum('precio');

        return view('report.pedidos_producto', compact('pedidoProductos', 'totalPedidos', 'totalCantidad', 'totalPrecio'));
    }

    public function generatepedidoproductoPdf(Request $request)
    {
        // Obtener las fechas de inicio y fin desde el formulario
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        // Comprobar si las fechas están presentes
        if ($fechaInicio && $fechaFin) {
            // Asegurarse de que las fechas están en el formato correcto (Y-m-d)
            $fechaInicio = Carbon::parse($fechaInicio)->startOfDay(); // Empieza a las 00:00
            $fechaFin = Carbon::parse($fechaFin)->endOfDay(); // Termina a las 23:59
        }

        // Obtener los pedidos de productos con las relaciones necesarias
        $pedidoProductos = PedidoProducto::with('pedido', 'producto', 'usuario');

        // Filtrar por fechas si se proporcionan
        if (isset($fechaInicio) && isset($fechaFin)) {
            $pedidoProductos = $pedidoProductos->whereHas('pedido', function ($query) use ($fechaInicio, $fechaFin) {
                // Filtrar por rango de fechas
                $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
            });
        }

        // Obtener todos los resultados sin paginación
        $pedidoProductos = $pedidoProductos->get();

        // Calcular los totales
        $totalPedidos = $pedidoProductos->count();
        $totalCantidad = $pedidoProductos->sum('cantidad');
        $totalPrecio = $pedidoProductos->sum('precio');

        // Crear el PDF
        $pdf = new Fpdf('P', 'mm', 'A4');
        $pdf->AliasNbPages();
        $pdf->AddPage();

        // Título del reporte
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, "Reporte de Pedidos de Productos - Importadora Miranda", 0, 1, 'C');
        $pdf->Ln(5);

        // Resumen de pedidos de productos
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, "Resumen de Pedidos de Productos", 0, 1, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 8, "Total de Pedidos: " . $totalPedidos, 0, 1, 'L');
        $pdf->Cell(0, 8, "Total de Cantidad: " . $totalCantidad, 0, 1, 'L');
        $pdf->Cell(0, 8, "Total de Precio: " . number_format($totalPrecio, 2), 0, 1, 'L');
        $pdf->Ln(10);

        // Cabecera de la tabla
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(178, 216, 255);
        $pdf->Cell(65, 8, "Nombre", 1, 0, 'C', true);
        $pdf->Cell(50, 8, "Producto", 1, 0, 'C', true);
        $pdf->Cell(15, 8, "Cantidad", 1, 0, 'C', true);
        $pdf->Cell(25, 8, "Precio", 1, 0, 'C', true);
        $pdf->Cell(40, 8, "Usuario", 1, 1, 'C', true);

        // Contenido de la tabla
        $pdf->SetFont('Arial', '', 9);
        $fill = false;
        foreach ($pedidoProductos as $pedidoProducto) {
            $pdf->Cell(65, 8, $pedidoProducto->pedido->nombre, 0, 0, 'C', $fill);
            $pdf->Cell(50, 8, $pedidoProducto->producto->nombre, 0, 0, 'C', $fill);
            $pdf->Cell(15, 8, $pedidoProducto->cantidad, 0, 0, 'C', $fill);
            $pdf->Cell(25, 8, number_format($pedidoProducto->precio, 2), 0, 0, 'C', $fill);
            $pdf->Cell(40, 8, $pedidoProducto->usuario->name, 0, 1, 'C', $fill);

            $fill = !$fill;
        }

        // Pie de página
        $pdf->SetY(-15);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 10, 'Página ' . $pdf->PageNo(), 0, 0, 'C');

        // Generar el PDF y devolverlo al navegador
        return response($pdf->Output('S', 'pedidos_productos.pdf'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="pedidos_productos.pdf"');
    }
}
