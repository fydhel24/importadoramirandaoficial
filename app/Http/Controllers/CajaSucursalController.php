<?php

namespace App\Http\Controllers;

use App\Models\CajaSucursal;
use App\Models\FechaSucursal;
use App\Models\Pedido;
use App\Models\Sucursale;
use App\Models\Venta;
use Carbon\Carbon;
use Fpdf\Fpdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;


class CajaSucursalController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // Verificar que el usuario sea Admin y tenga el correo correcto
            if (auth()->user()->hasRole('Admin') && auth()->user()->email === 'yesenew@gmail.com') {
                return $next($request);
            }

            // Si no tiene acceso, redirigir o lanzar un error
            return redirect()->route('home')->with('success', 'No tienes permisos suficientes para acceder a esta página.');
        });
    } 
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Obtener los registros con las relaciones necesarias y ordenarlos por la fecha de la sucursal
            $cajaSucursales = CajaSucursal::with('fechaSucursal', 'sucursal')
                ->orderByDesc('fecha_sucursal_id') // Aseguramos que los más recientes estén primero
                ->orderByDesc('created_at'); // También puedes ordenar por la fecha de creación si lo prefieres

            // Filtrar por fecha de inicio y fecha de fin solo si están presentes
            if ($request->has('fecha_inicio') && $request->has('fecha_fin') && $request->fecha_inicio && $request->fecha_fin) {
                $fechaInicio = Carbon::parse($request->fecha_inicio)->startOfDay();
                $fechaFin = Carbon::parse($request->fecha_fin)->endOfDay();

                // Aplicamos el filtro de fechas
                $cajaSucursales->whereHas('fechaSucursal', function ($query) use ($fechaInicio, $fechaFin) {
                    $query->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin]);
                });
            }

            // Si existe un término de búsqueda, agregarlo a la consulta
            if ($request->has('search') && $request->search['value'] != '') {
                $search = $request->search['value'];
                $cajaSucursales->whereHas('fechaSucursal', function ($query) use ($search) {
                    $query->where('detalle', 'like', "%$search%")
                        // Agregamos la condición para buscar en 'fecha_inicio' también
                        ->orWhereDate('fecha_inicio', 'like', "%$search%");
                })
                    ->orWhereHas('sucursal', function ($query) use ($search) {
                        $query->where('nombre', 'like', "%$search%");
                    });
            }

            // Paginación
            $recordsTotal = $cajaSucursales->count(); // Total de registros sin filtros
            $cajaSucursales = $cajaSucursales->skip($request->start)
                ->take($request->length)
                ->get();

            // Agrupar los registros por 'fecha_sucursal_id'
            $groupedData = $cajaSucursales->groupBy('fecha_sucursal_id');

            $data = [];
            foreach ($groupedData as $fechaSucursalId => $cajaSucursalGroup) {
                // Fecha y detalle de la primera sucursal del grupo
                $fechas = Carbon::parse($cajaSucursalGroup->first()->fechaSucursal->fecha_inicio)->format('d/m/Y H:i') . ' - ' .
                    Carbon::parse($cajaSucursalGroup->first()->fechaSucursal->fecha_fin)->format('d/m/Y H:i') . '<br>' .
                    $cajaSucursalGroup->first()->fechaSucursal->detalle;

                // Inicializamos las variables de totales para este grupo
                $totalVendido = 0;
                $totalQr = 0;
                $totalEfectivo = 0;
                $totalQrOficial = 0;
                $totalEfectivoOficial = 0;

                // Ahora recorremos cada sucursal dentro del grupo
                foreach ($cajaSucursalGroup as $index => $cajaSucursal) {
                    // Si la sucursal es null, lo reemplazamos por 'PEDIDOS'
                    $sucursalNombre = $cajaSucursal->sucursal->nombre ?? 'PEDIDOS';

                    // Para la primera sucursal del grupo, mostramos las fechas y detalle
                    if ($index == 0) {
                        $data[] = [
                            'fechas' => $fechas,
                            'sucursal' => $sucursalNombre,
                            'total_vendido' => number_format($cajaSucursal->total_vendido, 2),
                            'qr' => number_format($cajaSucursal->qr, 2),
                            'efectivo' => number_format($cajaSucursal->efectivo, 2),
                            'qr_oficial' => number_format($cajaSucursal->qr_oficial, 2),
                            'efectivo_oficial' => number_format($cajaSucursal->efectivo_oficial, 2),
                            'action' => '
                            <a href="' . route('caja_sucursal.edit', $cajaSucursal->fechaSucursal->id) . '" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <form action="' . route('caja_sucursal.destroy', $cajaSucursal->fechaSucursal->id) . '" method="POST" style="display:inline;">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'¿Estás seguro de que deseas eliminar este reporte?\')">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </form>
                        ',
                        ];
                    } else {
                        // Para las demás sucursales, solo mostramos la sucursal y los valores
                        $data[] = [
                            'fechas' => '',
                            'sucursal' => $sucursalNombre,
                            'total_vendido' => number_format($cajaSucursal->total_vendido, 2),
                            'qr' => number_format($cajaSucursal->qr, 2),
                            'efectivo' => number_format($cajaSucursal->efectivo, 2),
                            'qr_oficial' => number_format($cajaSucursal->qr_oficial, 2),
                            'efectivo_oficial' => number_format($cajaSucursal->efectivo_oficial, 2),
                            'action' => '',
                        ];
                    }

                    // Acumular los totales para este grupo
                    $totalVendido += $cajaSucursal->total_vendido;
                    $totalQr += $cajaSucursal->qr;
                    $totalEfectivo += $cajaSucursal->efectivo;
                    $totalQrOficial += $cajaSucursal->qr_oficial;
                    $totalEfectivoOficial += $cajaSucursal->efectivo_oficial;
                }

                // Agregar la fila de totales al final de cada grupo de sucursales
                $data[] = [
                    'fechas' => '',
                    'sucursal' => '<strong>TOTALES</strong>',
                    'total_vendido' => number_format($totalVendido, 2),
                    'qr' => number_format($totalQr, 2),
                    'efectivo' => number_format($totalEfectivo, 2),
                    'qr_oficial' => number_format($totalQrOficial, 2),
                    'efectivo_oficial' => number_format($totalEfectivoOficial, 2),
                    'action' => '',
                ];
            }

            // Retornar los datos en el formato necesario para DataTables
            return response()->json([
                'draw' => $request->draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => count($data),
                'data' => $data,
            ]);
        }

        return view('caja_sucursal.index');
    }
    /* public function index(Request $request)
    {
        if ($request->ajax()) {
            // Obtener los registros con las relaciones necesarias y ordenarlos por la fecha de la sucursal
            $cajaSucursales = CajaSucursal::with('fechaSucursal', 'sucursal')
                ->orderByDesc('fecha_sucursal_id') // Aseguramos que los más recientes estén primero
                ->orderByDesc('created_at'); // También puedes ordenar por la fecha de creación si lo prefieres

            // Filtrar por fecha de inicio y fecha de fin solo si están presentes
            if ($request->has('fecha_inicio') && $request->has('fecha_fin') && $request->fecha_inicio && $request->fecha_fin) {
                $fechaInicio = Carbon::parse($request->fecha_inicio)->startOfDay();
                $fechaFin = Carbon::parse($request->fecha_fin)->endOfDay();

                // Aplicamos el filtro de fechas
                $cajaSucursales->whereHas('fechaSucursal', function ($query) use ($fechaInicio, $fechaFin) {
                    $query->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin]);
                });
            }

            // Si existe un término de búsqueda, agregarlo a la consulta
            if ($request->has('search') && $request->search['value'] != '') {
                $search = $request->search['value'];
                $cajaSucursales->whereHas('fechaSucursal', function ($query) use ($search) {
                    $query->where('detalle', 'like', "%$search%")
                        // Agregamos la condición para buscar en 'fecha_inicio' también
                        ->orWhereDate('fecha_inicio', 'like', "%$search%");
                })
                    ->orWhereHas('sucursal', function ($query) use ($search) {
                        $query->where('nombre', 'like', "%$search%");
                    });
            }

            // Paginación
            $recordsTotal = $cajaSucursales->count(); // Total de registros sin filtros
            $cajaSucursales = $cajaSucursales->skip($request->start)
                ->take($request->length)
                ->get();

            // Agrupar los registros por 'fecha_sucursal_id'
            $groupedData = $cajaSucursales->groupBy('fecha_sucursal_id');

            $data = [];
            foreach ($groupedData as $fechaSucursalId => $cajaSucursalGroup) {
                // Fecha y detalle de la primera sucursal del grupo
                $fechas = Carbon::parse($cajaSucursalGroup->first()->fechaSucursal->fecha_inicio)->format('d/m/Y H:i') . ' - ' .
                    Carbon::parse($cajaSucursalGroup->first()->fechaSucursal->fecha_fin)->format('d/m/Y H:i') . '<br>' .
                    $cajaSucursalGroup->first()->fechaSucursal->detalle;

                // Inicializamos las variables de totales para este grupo
                $totalVendido = 0;
                $totalQr = 0;
                $totalEfectivo = 0;
                $totalQrOficial = 0;
                $totalEfectivoOficial = 0;

                // Ahora recorremos cada sucursal dentro del grupo
                foreach ($cajaSucursalGroup as $index => $cajaSucursal) {
                    // Si la sucursal es null, lo reemplazamos por 'PEDIDOS'
                    $sucursalNombre = $cajaSucursal->sucursal->nombre ?? 'PEDIDOS';

                    // Para la primera sucursal del grupo, mostramos las fechas y detalle
                    if ($index == 0) {
                        $data[] = [
                            'fechas' => $fechas,
                            'sucursal' => $sucursalNombre,
                            'total_vendido' => number_format($cajaSucursal->total_vendido, 2),
                            'qr' => number_format($cajaSucursal->qr, 2),
                            'efectivo' => number_format($cajaSucursal->efectivo, 2),
                            'qr_oficial' => number_format($cajaSucursal->qr_oficial, 2),
                            'efectivo_oficial' => number_format($cajaSucursal->efectivo_oficial, 2),
                            'action' => '
                            <a href="' . route('caja_sucursal.edit', $cajaSucursal->fechaSucursal->id) . '" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <form action="' . route('caja_sucursal.destroy', $cajaSucursal->fechaSucursal->id) . '" method="POST" style="display:inline;">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'¿Estás seguro de que deseas eliminar este reporte?\')">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </form>
                        ',
                        ];
                    } else {
                        // Para las demás sucursales, solo mostramos la sucursal y los valores
                        $data[] = [
                            'fechas' => '',
                            'sucursal' => $sucursalNombre,
                            'total_vendido' => number_format($cajaSucursal->total_vendido, 2),
                            'qr' => number_format($cajaSucursal->qr, 2),
                            'efectivo' => number_format($cajaSucursal->efectivo, 2),
                            'qr_oficial' => number_format($cajaSucursal->qr_oficial, 2),
                            'efectivo_oficial' => number_format($cajaSucursal->efectivo_oficial, 2),
                            'action' => '',
                        ];
                    }

                    // Acumular los totales para este grupo
                    $totalVendido += $cajaSucursal->total_vendido;
                    $totalQr += $cajaSucursal->qr;
                    $totalEfectivo += $cajaSucursal->efectivo;
                    $totalQrOficial += $cajaSucursal->qr_oficial;
                    $totalEfectivoOficial += $cajaSucursal->efectivo_oficial;
                }

                // Agregar la fila de totales al final de cada grupo de sucursales
                $data[] = [
                    'fechas' => '',
                    'sucursal' => '<strong>TOTALES</strong>',
                    'total_vendido' => number_format($totalVendido, 2),
                    'qr' => number_format($totalQr, 2),
                    'efectivo' => number_format($totalEfectivo, 2),
                    'qr_oficial' => number_format($totalQrOficial, 2),
                    'efectivo_oficial' => number_format($totalEfectivoOficial, 2),
                    'action' => '',
                ];
            }

            // Retornar los datos en el formato necesario para DataTables
            return response()->json([
                'draw' => $request->draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => count($data),
                'data' => $data,
            ]);
        }

        return view('caja_sucursal.index');
    } */


    /* public function generatePdf(Request $request)
    {
        // Inicializar la consulta para obtener los registros de CajaSucursal con las relaciones necesarias
        $cajaSucursales = CajaSucursal::with('fechaSucursal', 'sucursal');

        // Verificar si se han pasado las fechas para filtrarlas
        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            // Parsear las fechas de inicio y fin
            $fechaInicio = Carbon::parse($request->fecha_inicio)->startOfDay();  // Para incluir todo el día
            $fechaFin = Carbon::parse($request->fecha_fin)->endOfDay();  // Incluir hasta el final del día

            // Aplicar los filtros a la consulta
            $cajaSucursales->whereHas('fechaSucursal', function ($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin])
                    ->orWhereBetween('fecha_fin', [$fechaInicio, $fechaFin]);
            });
        }

        // Si no se filtran las fechas, se obtienen todos los registros
        // Obtener los registros ya filtrados
        $cajaSucursales = $cajaSucursales->get();

        // Crear el PDF (en modo horizontal)
        $pdf = new Fpdf('L', 'mm', 'A4');  // Modo 'L' para orientación horizontal
        $pdf->AliasNbPages();
        $pdf->AddPage();

        // Establecer las fuentes y tamaños
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, "Reporte de Caja - Importadora Miranda", 0, 1, 'C');
        $pdf->Ln(5);

        // Crear la tabla (sin líneas verticales)
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(178, 216, 255);  // Celeste bebé (RGB: 178, 216, 255)

        // Encabezado de la tabla (sin líneas verticales)
        $pdf->Cell(50, 8, "Fechas y Detalle", 1, 0, 'C', true);
        $pdf->Cell(50, 8, "Sucursal", 1, 0, 'C', true);
        $pdf->Cell(25, 8, "Total Vendido", 1, 0, 'C', true);
        $pdf->Cell(25, 8, "Total QR", 1, 0, 'C', true);
        $pdf->Cell(25, 8, "Total Efectivo", 1, 0, 'C', true);
        $pdf->Cell(25, 8, "QR Oficial", 1, 0, 'C', true);
        $pdf->Cell(25, 8, "Efectivo Oficial", 1, 1, 'C', true);

        // Establecer la fuente para el contenido
        $pdf->SetFont('Arial', '', 9);
        $fill = false; // Alternar el fondo de las filas

        // Recorrer los registros de las cajas
        foreach ($cajaSucursales as $cajaSucursal) {
            // Formatear las fechas
            $fechas = Carbon::parse($cajaSucursal->fechaSucursal->fecha_inicio)->format('d/m/Y H:i') . ' - ' .
                Carbon::parse($cajaSucursal->fechaSucursal->fecha_fin)->format('d/m/Y H:i');

            // Obtener el nombre de la sucursal
            $sucursalNombre = $cajaSucursal->sucursal->nombre ?? 'PEDIDOS';

            // Escribir los datos en la tabla
            $pdf->Cell(50, 8, $fechas, 1, 0, 'C', $fill);
            $pdf->Cell(50, 8, $sucursalNombre, 1, 0, 'C', $fill);
            $pdf->Cell(25, 8, number_format($cajaSucursal->total_vendido, 2), 1, 0, 'C', $fill);
            $pdf->Cell(25, 8, number_format($cajaSucursal->qr, 2), 1, 0, 'C', $fill);
            $pdf->Cell(25, 8, number_format($cajaSucursal->efectivo, 2), 1, 0, 'C', $fill);
            $pdf->Cell(25, 8, number_format($cajaSucursal->qr_oficial, 2), 1, 0, 'C', $fill);
            $pdf->Cell(25, 8, number_format($cajaSucursal->efectivo_oficial, 2), 1, 1, 'C', $fill);

            $fill = !$fill;  // Alternar el fondo de las filas
        }

        // Pie de página con numeración
        $pdf->SetY(-15);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 10, 'Página ' . $pdf->PageNo(), 0, 0, 'C');

        // Retornar el PDF como respuesta
        return response($pdf->Output('S', 'reporte_caja.pdf'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="reporte_caja.pdf"');
    } */
    public function generatePdf(Request $request)
    {
        // Inicializar la consulta para obtener los registros de CajaSucursal con las relaciones necesarias
        $cajaSucursales = CajaSucursal::with('fechaSucursal', 'sucursal');

        // Verificar si se han pasado las fechas para filtrarlas
        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            // Parsear las fechas de inicio y fin
            $fechaInicio = Carbon::parse($request->fecha_inicio)->startOfDay();  // Para incluir todo el día
            $fechaFin = Carbon::parse($request->fecha_fin)->endOfDay();  // Incluir hasta el final del día

            // Aplicar los filtros a la consulta
            $cajaSucursales->whereHas('fechaSucursal', function ($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin])
                    ->orWhereBetween('fecha_fin', [$fechaInicio, $fechaFin]);
            });
        }

        // Si no se filtran las fechas, se obtienen todos los registros
        // Obtener los registros ya filtrados
        $cajaSucursales = $cajaSucursales->get();

        // Crear el PDF (en modo horizontal)
        $pdf = new Fpdf('L', 'mm', 'A4');  // Modo 'L' para orientación horizontal
        $pdf->AliasNbPages();
        $pdf->AddPage();

        // Establecer las fuentes y tamaños
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, "Reporte de Caja - Importadora Miranda", 0, 1, 'C');
        $pdf->Ln(5);

        // Crear la tabla (sin líneas verticales)
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(178, 216, 255);  // Celeste bebé (RGB: 178, 216, 255)

        // Encabezado de la tabla (sin líneas verticales)
        $pdf->Cell(50, 8, "Fechas y Detalle", 1, 0, 'C', true);
        $pdf->Cell(70, 8, "Sucursal", 1, 0, 'C', true); // Ancho aumentado a 70
        $pdf->Cell(25, 8, "Total Vendido", 1, 0, 'C', true);
        $pdf->Cell(25, 8, "Total QR", 1, 0, 'C', true);
        $pdf->Cell(25, 8, "Total Efectivo", 1, 0, 'C', true);
        $pdf->Cell(25, 8, "QR Oficial", 1, 0, 'C', true);
        $pdf->Cell(25, 8, "Efectivo Oficial", 1, 1, 'C', true);

        // Establecer la fuente para el contenido
        $pdf->SetFont('Arial', '', 9);
        $fill = false; // Alternar el fondo de las filas

        // Recorrer los registros de las cajas
        foreach ($cajaSucursales as $cajaSucursal) {
            // Formatear las fechas
            $fechas = Carbon::parse($cajaSucursal->fechaSucursal->fecha_inicio)->format('d/m/Y H:i') . ' - ' .
                Carbon::parse($cajaSucursal->fechaSucursal->fecha_fin)->format('d/m/Y H:i');

            // Obtener el nombre de la sucursal
            $sucursalNombre = $cajaSucursal->sucursal->nombre ?? 'PEDIDOS';

            // Cambiar el tamaño de la fuente en las celdas de fechas y sucursales
            $pdf->SetFont('Arial', '', 8);  // Fuente más pequeña para "Fechas y Detalle" y "Sucursal"
            $pdf->Cell(50, 8, $fechas, 1, 0, 'C', $fill);
            $pdf->Cell(70, 8, $sucursalNombre, 1, 0, 'C', $fill); // Ancho de la celda de "Sucursal" más grande

            // Establecer la fuente normal para los otros campos
            $pdf->SetFont('Arial', '', 9); // Tamaño de fuente normal para otros datos
            $pdf->Cell(25, 8, number_format($cajaSucursal->total_vendido, 2), 1, 0, 'C', $fill);
            $pdf->Cell(25, 8, number_format($cajaSucursal->qr, 2), 1, 0, 'C', $fill);
            $pdf->Cell(25, 8, number_format($cajaSucursal->efectivo, 2), 1, 0, 'C', $fill);
            $pdf->Cell(25, 8, number_format($cajaSucursal->qr_oficial, 2), 1, 0, 'C', $fill);
            $pdf->Cell(25, 8, number_format($cajaSucursal->efectivo_oficial, 2), 1, 1, 'C', $fill);

            $fill = !$fill;  // Alternar el fondo de las filas
        }

        // Pie de página con numeración
        $pdf->SetY(-15);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 10, 'Página ' . $pdf->PageNo(), 0, 0, 'C');

        // Retornar el PDF como respuesta
        return response($pdf->Output('S', 'reporte_caja.pdf'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="reporte_caja.pdf"');
    }


    public function create(Request $request)
    {
        // Establecer las fechas por defecto si no están presentes en la solicitud
        $fechaInicio = $request->input('fecha_inicio', now()->startOfDay()->toDateTimeString());
        $fechaFin = $request->input('fecha_fin', now()->endOfDay()->toDateTimeString());

        // Obtener las ventas filtradas por fecha
        $ventas = Venta::with('sucursal')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin]) // Filtrar ventas entre las fechas
            ->get();

        // Agrupar las ventas por sucursal
        $totalesPorSucursal = $ventas->groupBy('id_sucursal')->map(function ($items) {
            return [
                'total_vendido' => $items->sum('costo_total'), // Sumar el costo_total de todas las ventas de esa sucursal
                'total_efectivo' => $items->where('tipo_pago', 'Efectivo')->sum('costo_total'), // Sumar ventas en efectivo
                'total_qr' => $items->where('tipo_pago', 'QR')->sum('costo_total'), // Sumar ventas en QR
            ];
        });

        // Obtener todas las sucursales disponibles
        $sucursales = Sucursale::all();

        // Obtener los pedidos filtrados por fecha
        $pedidos = Pedido::whereBetween('fecha', [$fechaInicio, $fechaFin])->get();

        // Calcular el monto total de depósito de los pedidos
        $totalDepositoPedidos = $pedidos->sum('monto_deposito');

        // Si el formulario fue enviado con los detalles para guardar
        if ($request->isMethod('post')) {
            // Crear la nueva entrada en la tabla 'fecha_sucursal'
            $fechaSucursal = FechaSucursal::create([
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'detalle' => $request->input('detalle')
            ]);

            // Guardar los datos de caja por sucursal
            foreach ($sucursales as $sucursal) {
                CajaSucursal::create([
                    'total_vendido' => $totalesPorSucursal[$sucursal->id]['total_vendido'] ?? 0,
                    'qr' => $totalesPorSucursal[$sucursal->id]['total_qr'] ?? 0,
                    'efectivo' => $totalesPorSucursal[$sucursal->id]['total_efectivo'] ?? 0,
                    'qr_oficial' => $request->input('sucursales.' . $sucursal->id . '.qr_oficial', 0),
                    'efectivo_oficial' => $request->input('sucursales.' . $sucursal->id . '.efectivo_oficial', 0),
                    'sucursal_id' => $sucursal->id,
                    'fecha_sucursal_id' => $fechaSucursal->id,
                ]);
            }

            // Guardar los datos de la sucursal "PEDIDOS"
            CajaSucursal::create([
                'total_vendido' => 0,
                'qr' => $totalDepositoPedidos,
                'efectivo' => 0,
                'qr_oficial' => $request->input('sucursales.PEDIDOS.qr_oficial', 0),
                'efectivo_oficial' => $request->input('sucursales.PEDIDOS.efectivo_oficial', 0),
                'sucursal_id' => null, // Cambiar a null para "PEDIDOS"
                'fecha_sucursal_id' => $fechaSucursal->id,
            ]);



            // Redirigir a la página de índice (o donde prefieras) después de guardar
            return redirect()->route('caja_sucursal.index')->with('success', 'Reporte guardado exitosamente');
        }

        // Retornar a la vista con los datos si el formulario no fue enviado
        return view('caja_sucursal.create', compact('totalesPorSucursal', 'fechaInicio', 'fechaFin', 'sucursales', 'totalDepositoPedidos'));
    }

    public function store(Request $request)
    {
        // Validar los datos del formulario
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'detalle' => 'nullable|string|max:500', // Detalle es opcional
        ]);

        // Obtener las fechas y detalle del formulario
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $detalle = $request->input('detalle');

        // Crear la entrada en la tabla 'fecha_sucursal' con los detalles y fechas
        $fechaSucursal = FechaSucursal::create([
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'detalle' => $detalle, // Almacenar el detalle
        ]);

        // Guardar los datos de caja por sucursal
        foreach ($request->input('sucursales') as $sucursalId => $totales) {
            // Si la sucursal es "PEDIDOS", asignar null
            if ($sucursalId == 'PEDIDOS') {
                $sucursalId = null;
            }

            CajaSucursal::create([
                'total_vendido' => $totales['total_vendido'],
                'qr' => $totales['total_qr'],
                'efectivo' => $totales['total_efectivo'],
                'qr_oficial' => $totales['qr_oficial'],
                'efectivo_oficial' => $totales['efectivo_oficial'],
                'sucursal_id' => $sucursalId,
                'fecha_sucursal_id' => $fechaSucursal->id,
            ]);
        }


        // Redirigir con mensaje de éxito
        return redirect()->route('caja_sucursal.index')->with('success', 'Reporte de Caja Sucursal guardado exitosamente.');
    }
    public function edit($id)
    {
        // Obtener el reporte de caja para la fecha específica
        $fechaSucursal = FechaSucursal::findOrFail($id);

        // Obtener las ventas filtradas por fecha
        $ventas = Venta::with('sucursal')
            ->whereBetween('fecha', [$fechaSucursal->fecha_inicio, $fechaSucursal->fecha_fin]) // Filtrar ventas entre las fechas
            ->get();

        // Agrupar las ventas por sucursal
        $totalesPorSucursal = $ventas->groupBy('id_sucursal')->map(function ($items) {
            return [
                'total_vendido' => $items->sum('costo_total'),
                'total_efectivo' => $items->where('tipo_pago', 'Efectivo')->sum('costo_total'),
                'total_qr' => $items->where('tipo_pago', 'QR')->sum('costo_total'),
            ];
        });

        // Obtener todas las sucursales disponibles
        $sucursales = Sucursale::all();

        // Obtener los pedidos filtrados por fecha
        $pedidos = Pedido::whereBetween('fecha', [$fechaSucursal->fecha_inicio, $fechaSucursal->fecha_fin])->get();

        // Calcular el monto total de depósito de los pedidos
        $totalDepositoPedidos = $pedidos->sum('monto_deposito');

        // Obtener los valores de "PEDIDOS" desde la base de datos
        $cajaPedidos = CajaSucursal::where('fecha_sucursal_id', $fechaSucursal->id)
            ->whereNull('sucursal_id') // Filtrar por sucursal null (representa "PEDIDOS")
            ->first();

        // Si no se encuentran registros, inicializar como cero
        $pedidoQrOficial = $cajaPedidos ? $cajaPedidos->qr_oficial : 0;
        $pedidoEfectivoOficial = $cajaPedidos ? $cajaPedidos->efectivo_oficial : 0;

        // Recuperar los datos de todas las sucursales para la edición
        $cajasSucursales = CajaSucursal::where('fecha_sucursal_id', $fechaSucursal->id)
            ->get()
            ->keyBy('sucursal_id');

        return view('caja_sucursal.edit', compact('fechaSucursal', 'totalesPorSucursal', 'sucursales', 'totalDepositoPedidos', 'pedidoQrOficial', 'pedidoEfectivoOficial', 'cajasSucursales'));
    }


    public function update(Request $request, $id)
    {
        // Validar los datos del formulario
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'detalle' => 'nullable|string|max:500', // Detalle es opcional
        ]);

        // Obtener la entrada del reporte de caja
        $fechaSucursal = FechaSucursal::findOrFail($id);

        // Actualizar la entrada en la tabla 'fecha_sucursal' con los nuevos detalles
        $fechaSucursal->update([
            'fecha_inicio' => $request->input('fecha_inicio'),
            'fecha_fin' => $request->input('fecha_fin'),
            'detalle' => $request->input('detalle'), // Almacenar el detalle
        ]);

        // Actualizar los datos de caja por sucursal
        foreach ($request->input('sucursales') as $sucursalId => $totales) {
            // Si la sucursal es "PEDIDOS", asignar null
            if ($sucursalId == 'PEDIDOS') {
                $sucursalId = null;
            }

            // Buscar la cajaSucursal correspondiente
            $cajaSucursal = CajaSucursal::where('fecha_sucursal_id', $fechaSucursal->id)
                ->where('sucursal_id', $sucursalId)
                ->first();

            if ($cajaSucursal) {
                // Si existe, actualizar los datos
                $cajaSucursal->update([
                    'total_vendido' => $totales['total_vendido'],
                    'qr' => $totales['total_qr'],
                    'efectivo' => $totales['total_efectivo'],
                    'qr_oficial' => $totales['qr_oficial'],
                    'efectivo_oficial' => $totales['efectivo_oficial'],
                ]);
            } else {
                // Si no existe, crear un nuevo registro
                CajaSucursal::create([
                    'total_vendido' => $totales['total_vendido'],
                    'qr' => $totales['total_qr'],
                    'efectivo' => $totales['total_efectivo'],
                    'qr_oficial' => $totales['qr_oficial'],
                    'efectivo_oficial' => $totales['efectivo_oficial'],
                    'sucursal_id' => $sucursalId,
                    'fecha_sucursal_id' => $fechaSucursal->id,
                ]);
            }
        }

        // Redirigir con mensaje de éxito
        return redirect()->route('caja_sucursal.index')->with('success', 'Reporte de Caja Sucursal actualizado exitosamente.');
    }

    public function destroy($id)
    {
        // Buscar el objeto FechaSucursal
        $fechaSucursal = FechaSucursal::findOrFail($id);

        // Eliminar los registros asociados de CajaSucursal
        $fechaSucursal->cajaSucursales()->delete();

        // Eliminar el registro de FechaSucursal
        $fechaSucursal->delete();

        // Redirigir al índice con un mensaje de éxito
        return redirect()->route('caja_sucursal.index')->with('success', 'Reporte eliminado exitosamente');
    }
}
