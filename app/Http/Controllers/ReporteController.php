<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Carbon\Carbon;
use FPDF;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    public function index()
    {
        return view('reporte.index'); // Crear esta vista
    }

    public function descargar(Request $request)
    {
        $request->validate([
            'mes' => 'required|date_format:Y-m',
        ]);
        
        $mes = $request->input('mes');
        $pedidos = Pedido::whereYear('fecha', date('Y', strtotime($mes)))
                         ->whereMonth('fecha', date('m', strtotime($mes)))
                         ->get();
    
        // Crear el PDF en orientación horizontal
        $pdf = new FPDF('L'); // 'L' para orientación horizontal
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, "Reporte de Pedidos para el mes: " . $mes, 0, 1, 'C');
    
        // Encabezados de la tabla
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(90, 10, 'Nombre', 1);
        $pdf->Cell(30, 10, 'Celular', 1);
        $pdf->Cell(65, 10, 'Destino', 1);
        $pdf->Cell(40, 10, 'Estado', 1);
        $pdf->Cell(30, 10, 'Monto', 1);
        $pdf->Cell(30, 10, 'Fecha', 1);
        $pdf->Ln();
    
        // Datos de los pedidos
        $pdf->SetFont('Arial', '', 12);
        foreach ($pedidos as $pedido) {
            $pdf->Cell(90, 10, $pedido->nombre, 1);
            $pdf->Cell(30, 10, $pedido->celular, 1);
            
        $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(65, 10, $pedido->destino, 1);
            
        $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(40, 10, $pedido->estado, 1);
            $pdf->Cell(30, 10, $pedido->monto_deposito, 1);
            
            // Convertir la fecha a Carbon solo si no se ha convertido correctamente
            $fecha = is_string($pedido->fecha) ? Carbon::parse($pedido->fecha) : $pedido->fecha;
            $pdf->Cell(30, 10, $fecha->format('Y-m-d'), 1);
            
            $pdf->Ln(); // Mover a la siguiente línea
        }
    
        $pdf->Output('D', 'reporte_pedidos_' . $mes . '.pdf');
    }
    
    
}
