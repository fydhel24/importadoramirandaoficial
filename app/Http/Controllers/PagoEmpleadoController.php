<?php

namespace App\Http\Controllers;

use App\Models\PagoEmpleado;
use App\Models\PagoUser;
use App\Models\User;
use Carbon\Carbon;
use Codedge\Fpdf\Fpdf\Fpdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagoEmpleadoController extends Controller
{
    //
    public function index()
    {
        // Nombres a excluir
        $nombresExcluidos = [
            'VENDEDOR SUCURSAL 1',
            'VENDEDOR SUCURSAL 2',
            'VENDEDOR SUCURSAL 3',
            'VENDEDOR SUCURSAL 4',
            'yesenew@gmail.com',
        ];

        // Usuarios activos, ordenados por creación descendente, excluyendo ciertos nombres
        $usuarios = User::where('status', 'active')
            ->whereNotIn('name', $nombresExcluidos)
            ->orderBy('created_at', 'desc')
            ->get();

        $pagos = PagoUser::with(['pagoEmpleado'])
            ->get()
            ->groupBy(function ($item) {
                return $item->user_id . '-' . Carbon::parse($item->pagoEmpleado->fecha_inicio)->format('m');
            });

        return view('pagos.indexempleado', compact('usuarios', 'pagos'));
    }


    public function realizarPago(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'mes' => 'required|integer|min:1|max:12',
            'fecha_pago' => 'required|date',
            'monto' => 'required|numeric|min:0',
            'bono_extra' => 'nullable|numeric',
            'descuento' => 'nullable|numeric',
            'descripcion' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            // Paso 1: Buscar o crear pago_empleado por mes (solo uno por mes)
            $mes = str_pad($request->mes, 2, '0', STR_PAD_LEFT);
            $año = now()->year;

            $fechaInicio = Carbon::createFromDate($año, $mes, 1);
            $fechaFin = $fechaInicio->copy()->endOfMonth();
            $total = $request->monto + ($request->bono_extra ?? 0) - ($request->descuento ?? 0);
            $año = now()->year;

            $pagoEmpleado = PagoEmpleado::firstOrCreate(
                ['mes' => $mes, 'año' => $año, 'fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin],
                [
                    'monto' => $request->monto,
                    'bono_extra' => $request->bono_extra ?? 0,
                    'descuento' => $request->descuento ?? 0,
                    'descripcion' => $request->descripcion,
                    'total' => $total,
                ]
            );

            // Paso 2: Registrar o actualizar el pago del usuario
            $pagoUser = PagoUser::updateOrCreate(
                [
                    'user_id' => $request->user_id,
                    'pago_id' => $pagoEmpleado->id
                ],
                [
                    'estado' => 'pagado',
                    'fecha_pago' => $request->fecha_pago
                ]
            );

            DB::commit();

            return redirect()->route('pagos.index')->with('success', 'Pago registrado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocurrió un error al registrar el pago: ' . $e->getMessage());
        }
    }

    public function generateAllPdf()
    {
        // Excluir los mismos nombres de usuario que en el índice
        $nombresExcluidos = [
            'VENDEDOR SUCURSAL 1',
            'VENDEDOR SUCURSAL 2',
            'VENDEDOR SUCURSAL 3',
            'VENDEDOR SUCURSAL 4',
            'yesenew@gmail.com',
        ];

        // Obtener usuarios y agrupar sus pagos por mes
        $usuarios = User::where('status', 'active')
            ->whereNotIn('name', $nombresExcluidos)
            ->with(['pagosUsers.pagoEmpleado'])
            ->orderBy('name')
            ->get();

        // Configuración del PDF
        $pdf = new FPDF('L', 'mm', 'A4'); // 'L' para formato horizontal
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, utf8_decode('PLANILLA DE PAGOS - AÑO ' . date('Y')), 0, 1, 'C');
        $pdf->Ln(5);
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetFillColor(230, 230, 230); // Color de fondo para el encabezado

        // Anchos de las columnas
        $colWidths = [
            'nombre' => 40,
            'mes' => 19, // Ajustado para que quepa la información adicional
        ];
        $totalWidth = $colWidths['nombre'] + (12 * $colWidths['mes']);
        $cellHeight = 10; // Altura de las filas para acomodar dos líneas de texto

        // Encabezados de la tabla
        $pdf->SetFont('Arial', 'B', 8); // Fuente más pequeña para los encabezados
        $pdf->Cell($colWidths['nombre'], $cellHeight, utf8_decode('Usuario'), 1, 0, 'C', true);
        for ($i = 1; $i <= 12; $i++) {
            $monthName = strtoupper(Carbon::create()->month($i)->locale('es')->shortMonthName);
            $pdf->Cell($colWidths['mes'], $cellHeight, utf8_decode($monthName), 1, 0, 'C', true);
        }
        $pdf->Ln();

        // Rellenar la tabla con los datos de los usuarios
        $pdf->SetFont('Arial', '', 7); // Fuente más pequeña para los datos
        $fill = false;
        foreach ($usuarios as $usuario) {
            $pdf->Cell($colWidths['nombre'], $cellHeight, utf8_decode($usuario->name), 1, 0, 'L', $fill);

            // Guardar posición inicial X e Y para el inicio de la fila
            $startX = $pdf->GetX();
            $startY = $pdf->GetY();

            // Obtener los pagos del usuario agrupados por mes
            $pagosPorMes = $usuario->pagosUsers->keyBy(function ($item) {
                return Carbon::parse($item->pagoEmpleado->fecha_inicio)->format('n');
            });

            for ($mes = 1; $mes <= 12; $mes++) {
                $x = $startX + ($mes - 1) * $colWidths['mes'];
                $y = $startY;

                $pdf->SetXY($x, $y);

                if ($pagosPorMes->has($mes)) {
                    $pago = $pagosPorMes->get($mes);
                    $monto = number_format($pago->pagoEmpleado->total, 2) . ' Bs';
                    $estado = strtoupper($pago->estado);
                    $fecha = Carbon::parse($pago->fecha_pago)->format('d/m');

                    // Combinar el monto, estado y fecha en una sola cadena
                    $text = "$monto\n$estado\n($fecha)";
                    $pdf->MultiCell($colWidths['mes'], 3.3, utf8_decode($text), 1, 'C', $fill);
                } else {
                    $pdf->MultiCell($colWidths['mes'], $cellHeight, '', 1, 'C', $fill);
                }
            }

            // Mover a la siguiente fila después de procesar todos los meses
            $pdf->SetXY(10, $startY + $cellHeight);
            $fill = !$fill; // Alternar el color de las filas
        }

        // Devolver el PDF
        return response($pdf->Output('S', 'planilla_pagos_anual.pdf'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="planilla_pagos_anual.pdf"');
    }

    // Nueva función para generar un PDF de un solo pago
    public function generatePdf(Request $request)
    {
        $request->validate([
            'user' => 'required|exists:users,id',
            'mes' => 'required|integer|min:1|max:12',
        ]);

        $userId = $request->input('user');
        $mes = $request->input('mes');
        $año = now()->year;

        // Buscar el pago específico del usuario para el mes y año
        $pago = PagoUser::where('user_id', $userId)
            ->whereHas('pagoEmpleado', function ($query) use ($mes, $año) {
                $query->where('mes', str_pad($mes, 2, '0', STR_PAD_LEFT))
                    ->where('año', $año);
            })
            ->with(['user', 'pagoEmpleado'])
            ->first();

        // Si no se encuentra el pago, redirige con un mensaje de error
        if (!$pago) {
            return back()->with('error', 'No se encontró un pago para este usuario en el mes seleccionado.');
        }

        // Crear instancia de FPDF
        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();

        // Posicionar el recibo al centro horizontal y parte superior de la página
        $reciboWidth = 100; // Ancho del recibo
        $reciboHeight = 100; // Altura del recibo (puedes ajustar si se necesita más espacio)
        $x = ($pdf->GetPageWidth() - $reciboWidth) / 2;
        $y = 30; // Más arriba (parte superior)

        // Dibuja el marco del recibo (opcional)
        $pdf->Rect($x, $y, $reciboWidth, $reciboHeight);

        // Título del Recibo
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetXY($x, $y + 5);
        $pdf->Cell($reciboWidth, 6, utf8_decode('RECIBO DE PAGO'), 0, 1, 'C');

        // Detalles del Recibo
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetXY($x + 5, $y + 15);
        $pdf->Cell(0, 5, utf8_decode('Nombre: ' . $pago->user->name), 0, 1);
        $pdf->SetX($x + 5);
        $pdf->Cell(0, 5, utf8_decode('Fecha de Pago: ' . Carbon::parse($pago->fecha_pago)->format('d/m/Y')), 0, 1);
        $pdf->SetX($x + 5);
        $pdf->Cell(0, 5, utf8_decode('Mes: ' . Carbon::parse($pago->pagoEmpleado->fecha_inicio)->locale('es')->monthName), 0, 1);

        // Tabla de detalles del pago
        $pdf->Ln(2);
        $pdf->SetX($x + 5);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(50, 6, utf8_decode('Concepto'), 1, 0, 'C');
        $pdf->Cell(40, 6, utf8_decode('Monto (Bs)'), 1, 1, 'C');

        $pdf->SetFont('Arial', '', 10);
        $pdf->SetX($x + 5);
        $pdf->Cell(50, 6, utf8_decode('Monto Base'), 1, 0);
        $pdf->Cell(40, 6, number_format($pago->pagoEmpleado->monto, 2), 1, 1, 'R');

        $pdf->SetX($x + 5);
        $pdf->Cell(50, 6, utf8_decode('Bono Extra'), 1, 0);
        $pdf->Cell(40, 6, number_format($pago->pagoEmpleado->bono_extra, 2), 1, 1, 'R');

        $pdf->SetX($x + 5);
        $pdf->Cell(50, 6, utf8_decode('Descuento'), 1, 0);
        $pdf->Cell(40, 6, number_format($pago->pagoEmpleado->descuento, 2), 1, 1, 'R');

        // Total
        $pdf->SetX($x + 5);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(50, 6, utf8_decode('Total Pagado'), 1, 0, 'C');
        $pdf->Cell(40, 6, number_format($pago->pagoEmpleado->total, 2), 1, 1, 'R');

        // Descripción
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->SetX($x + 5);
        $pdf->MultiCell(0, 4, utf8_decode('Descripción: ' . ($pago->pagoEmpleado->descripcion ?? 'N/A')));

        // Devolver el PDF
        return response($pdf->Output('S', 'recibo_pago.pdf'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="recibo_pago.pdf"');
    }
}
