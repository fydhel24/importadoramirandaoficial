<?php

namespace App\Http\Controllers;

use App\Libraries\CustomFpdf;
use App\Models\Sucursale;
use App\Models\User;
use App\Models\Venta;
use App\Models\VentaProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SalesController extends Controller
{
    public function canceledSales(Request $request)
    {
        $users = User::all(); // Carga todos los usuarios
        $sucursales = Sucursale::all(); // Carga todas las sucursales
        // Calcular totales
        $totalProductosPerdidos = Venta::where('estado', 'CANCELADA')
            ->with('ventaProductos')
            ->get()
            ->flatMap(function ($venta) {
                return $venta->ventaProductos;
            })
            ->sum('cantidad');

        $costoProductosPerdidos = Venta::where('estado', 'CANCELADA')
            ->with('ventaProductos')
            ->get()
            ->flatMap(function ($venta) {
                return $venta->ventaProductos->map(function ($producto) {
                    return $producto->cantidad * $producto->precio_unitario;
                });
            })
            ->sum();

        $productoMasCancelado = VentaProducto::whereHas('venta', function ($query) {
            $query->where('estado', 'CANCELADA');
        })
            ->select('id_producto', DB::raw('SUM(cantidad) as total_cantidad'))
            ->groupBy('id_producto')
            ->orderByDesc('total_cantidad')
            ->with('producto')
            ->first();

        if ($request->ajax()) {
            $query = Venta::where('estado', 'CANCELADA')->with(['ventaProductos.producto', 'user', 'sucursal']);

            if ($request->start_date && $request->end_date) {
                $query->whereBetween('fecha', [$request->start_date, $request->end_date]);
            }

            if ($request->user_id) {
                $query->where('id_user', $request->user_id);
            }

            if ($request->sucursal_id) {
                $query->where('id_sucursal', $request->sucursal_id);
            }

            return DataTables::of($query)
                ->addColumn('productos', function ($venta) {
                    return $venta->ventaProductos->map(function ($vp) {
                        return $vp->producto->nombre . ' (Cantidad: ' . $vp->cantidad . ')';
                    })->implode('<br>');
                })
                ->addColumn('efectivo_perdido', function ($venta) {
                    return $venta->efectivo;
                })
                ->addColumn('cantidad_productos', function ($venta) {
                    return $venta->ventaProductos->sum('cantidad');
                })
                ->filter(function ($query) use ($request) {
                    if ($request->search['value']) {
                        $search = $request->search['value'];
                        $query->where(function ($q) use ($search) {
                            $q->where('id', 'like', "%{$search}%")
                                ->orWhere('nombre_cliente', 'like', "%{$search}%")
                                ->orWhere('fecha', 'like', "%{$search}%")
                                ->orWhereHas('ventaProductos.producto', function ($q) use ($search) {
                                    $q->where('nombre', 'like', "%{$search}%");
                                })
                                ->orWhere('efectivo', 'like', "%{$search}%")
                                ->orWhereHas('user', function ($q) use ($search) {
                                    $q->where('name', 'like', "%{$search}%");
                                })
                                ->orWhereHas('sucursal', function ($q) use ($search) {
                                    $q->where('nombre', 'like', "%{$search}%");
                                });
                        });
                    }
                })
                ->rawColumns(['productos'])
                ->make(true);
        }

        return view('sales.canceled_sales', compact(
            'users',
            'sucursales',
            'totalProductosPerdidos',
            'costoProductosPerdidos',
            'productoMasCancelado'
        ));
    }

    public function exportFilteredSales(Request $request)
    {
        $query = Venta::where('estado', 'CANCELADA')->with(['ventaProductos.producto', 'user', 'sucursal']);

        // Aplicar filtros
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('fecha', [$request->start_date, $request->end_date]);
        }

        if ($request->user_id) {
            $query->where('id_user', $request->user_id);
        }

        if ($request->sucursal_id) {
            $query->where('id_sucursal', $request->sucursal_id);
        }

        $ventas = $query->get();

        // Crear el PDF
        $pdf = new CustomFpdf();
        $pdf->AddPage();

        // Logo
        $pdf->Image(public_path('images/logo.png'), 10, 10, 30);

        // Título
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetTextColor(0, 51, 102); // Azul oscuro
        $pdf->Cell(0, 10, utf8_decode('Reporte de Ventas Canceladas'), 0, 1, 'C');
        $pdf->Ln(5);

        // Subtítulo
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetTextColor(0, 0, 0); // Negro
        $pdf->Cell(0, 10, utf8_decode('Fecha de generación: ') . date('d/m/Y H:i'), 0, 1, 'C');
        $pdf->Ln(10);

        // Dibujar el cuadro para los filtros
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(0, 51, 102); // Azul oscuro
        $pdf->Cell(0, 10, utf8_decode('Filtros Aplicados'), 0, 1, 'L');

        // Coordenadas iniciales del cuadro
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $width = 190; // Ancho total del cuadro
        $height = 40; // Altura total del cuadro

        // Dibujar el rectángulo del cuadro
        $pdf->SetDrawColor(0, 60, 120); // Azul oscuro
        $pdf->Rect($x, $y, $width, $height);

        // Configurar la fuente para el contenido
        $pdf->SetFont('Arial', '', 20);
        $pdf->SetTextColor(0, 0, 0); // Negro

        // Colores para el texto (por ejemplo, negro para títulos y azul para valores)
        $pdf->SetTextColor(0, 0, 0); // Texto negro para las etiquetas
        $pdf->SetFillColor(235, 235, 235); // Color de fondo gris claro para las celdas con etiquetas
        $pdf->SetXY($x + 5, $y + 6); // Ajustar posición dentro del cuadro
        $pdf->Cell(90, 6, utf8_decode('Fecha y Hora Inicio: '), 0, 1, 'L', true); // 'L' para alinear a la izquierda, 'true' para color de fondo

        $pdf->SetXY($x + 12, $y + 14); // Ajustar posición dentro del cuadro
        $pdf->SetTextColor(0, 0, 255); // Texto azul para los valores
        $pdf->Cell(90, 6, ($request->start_date ? date('d/m/Y H:i', strtotime($request->start_date)) : 'N/A'), 0, 1);

        $pdf->SetXY($x + 5, $y + 20); // Ajustar posición dentro del cuadro
        $pdf->SetTextColor(0, 0, 0); // Texto negro para las etiquetas
        $pdf->Cell(90, 6, utf8_decode('Usuario: '), 0, 1, 'L', true);

        $pdf->SetXY($x + 12, $y + 28); // Ajustar posición dentro del cuadro
        $pdf->SetTextColor(0, 0, 255); // Texto azul para los valores
        $pdf->Cell(90, 6, ($request->user_id ? User::find($request->user_id)->name : 'Todos'), 0, 1);

        // Segunda columna
        $pdf->SetXY($x + 90, $y + 6); // Ajustar posición dentro del cuadro
        $pdf->SetTextColor(0, 0, 0); // Texto negro para las etiquetas
        $pdf->Cell(90, 6, utf8_decode('Fecha y Hora Fin: '), 0, 1, 'L', true);

        $pdf->SetXY($x + 95, $y + 14); // Ajustar posición dentro del cuadro
        $pdf->SetTextColor(0, 0, 255); // Texto azul para los valores
        $pdf->Cell(90, 6, ($request->end_date ? date('d/m/Y H:i', strtotime($request->end_date)) : 'N/A'), 0, 1);

        $pdf->SetXY($x + 90, $y + 20);
        $pdf->SetTextColor(0, 0, 0); // Texto negro para las etiquetas
        $pdf->Cell(90, 6, utf8_decode('Sucursal: '), 0, 1, 'L', true);

        $pdf->SetXY($x + 95, $y + 28);
        $pdf->SetFont('Arial', '', 11);
        $pdf->SetTextColor(0, 0, 255); // Texto azul para los valores
        $pdf->Cell(90, 6, ($request->sucursal_id ? Sucursale::find($request->sucursal_id)->nombre : 'Todas'), 0, 1);

        $pdf->SetFont('Arial', '', 20);
        // Espacio después del cuadro
        $pdf->Ln(15);

        // Gráfico de barras
        $totalProductos = $ventas->sum(function ($venta) {
            return $venta->ventaProductos->sum('cantidad');
        });
        $totalEfectivo = $ventas->sum('efectivo');

        $this->addBarChart($pdf, [
            'Productos Perdidos' => $totalProductos,
            'Efectivo Perdido' => $totalEfectivo,
        ]);

        // Espacio antes de la tabla
        $pdf->Ln(10);

        // Encabezados de la tabla
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(173, 216, 230); // Celeste
        $pdf->SetTextColor(0, 51, 102); // Azul oscuro
        $pdf->Cell(20, 10, 'ID', 1, 0, 'C', true);
        $pdf->Cell(50, 10, utf8_decode('Cliente'), 1, 0, 'C', true);
        $pdf->Cell(30, 10, utf8_decode('Fecha'), 1, 0, 'C', true);
        $pdf->Cell(50, 10, utf8_decode('Productos'), 1, 0, 'C', true);
        $pdf->Cell(30, 10, utf8_decode('Efectivo'), 1, 1, 'C', true);

        // Datos de la tabla
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(0, 0, 0); // Negro

        foreach ($ventas as $venta) {
            $productos = $venta->ventaProductos->map(function ($vp) {
                return $vp->producto->nombre . ' (' . $vp->cantidad . ')';
            })->implode(', ');

            // Configurar el tamaño de la fuente
            $pdf->SetFont('Arial', '', 9);

            // ID
            $pdf->Cell(20, 10, $venta->id, 1, 0, 'C');

            // Cliente
            $x = $pdf->GetX(); // Guardar la posición actual en X
            $y = $pdf->GetY(); // Guardar la posición actual en Y
            $pdf->MultiCell(50, 5, utf8_decode($venta->nombre_cliente), 1, 'C');
            $pdf->SetXY($x + 50, $y); // Restaurar la posición para la siguiente celda

            // Fecha
            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->MultiCell(30, 5, $venta->fecha, 1, 'C');
            $pdf->SetXY($x + 30, $y);

            // Productos
            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->MultiCell(50, 5, utf8_decode($productos), 1, 'C');
            $pdf->SetXY($x + 50, $y);

            // Efectivo
            $pdf->Cell(30, 10, number_format($venta->efectivo, 2) . ' Bs', 1, 1, 'C'); // Salto de línea después de la fila
        }

        // Salida del PDF
        $pdf->Output('I', 'Reporte_Ventas_Canceladas.pdf');
    }

    /**
     * Agregar un gráfico de barras al PDF.
     */
    protected function addBarChart($pdf, $data)
    {
        $pdf->Ln(10);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, utf8_decode('Gráfico de Barras'), 0, 1, 'C');
        $pdf->Ln(5);

        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $width = 100;
        $height = 50;
        $barWidth = 30;
        $colors = [[100, 149, 237], [255, 99, 71], [60, 179, 113], [255, 215, 0]]; // Azul, rojo, verde, dorado

        $pdf->SetDrawColor(0, 0, 0);
        $maxValue = max($data);

        // Verificar si $maxValue es 0 para evitar división por cero
        if ($maxValue == 0) {
            $pdf->SetFont('Arial', 'I', 10);
            $pdf->Cell(0, 10, utf8_decode('No hay datos suficientes para generar el gráfico.'), 0, 1, 'C');
            return;
        }

        foreach ($data as $label => $value) {
            $barHeight = ($value / $maxValue) * $height;
            $color = array_shift($colors);
            $pdf->SetFillColor($color[0], $color[1], $color[2]);
            $pdf->Rect($x, $y + ($height - $barHeight), $barWidth, $barHeight, 'DF');
            $pdf->SetXY($x, $y + $height + 5);
            $pdf->Cell($barWidth, 5, utf8_decode($label), 0, 0, 'C');
            $x += $barWidth + 10;
        }

        // Agregar leyenda debajo del gráfico
        $pdf->Ln(15);
        $pdf->SetFont('Arial', '', 10);
        foreach ($data as $label => $value) {
            $pdf->Cell(0, 6, utf8_decode("$label: $value"), 0, 1);
        }
    }

    /**
     * Agregar un gráfico circular al PDF.
     */
    protected function addPieChart($pdf, $data)
    {
        $pdf->Ln(10);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, utf8_decode('Gráfico Circular'), 0, 1, 'C');
        $pdf->Ln(5);

        $xCenter = 105; // Centro del círculo en X
        $yCenter = 100; // Centro del círculo en Y
        $radius = 40; // Radio del círculo

        $total = array_sum($data);
        $startAngle = 0;

        foreach ($data as $label => $value) {
            $angle = ($value / $total) * 360;
            $endAngle = $startAngle + $angle;

            // Colores
            $color = [rand(50, 200), rand(50, 200), rand(50, 200)];
            $pdf->SetFillColor($color[0], $color[1], $color[2]);

            // Dibujar el sector
            $pdf->Sector($xCenter, $yCenter, $radius, $startAngle, $endAngle, 'F');
            $startAngle = $endAngle;
        }

        // Leyenda
        $pdf->Ln(10);
        foreach ($data as $label => $value) {
            $pdf->Cell(0, 10, utf8_decode("$label: $value"), 0, 1);
        }
    }
}
