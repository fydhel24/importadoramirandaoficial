<?php
namespace App\Services;

use Fpdf\Fpdf as FpdfFpdf;

class PdfService
{
    public function generatePdf($data)
    {
        $pdf = new FpdfFpdf();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);

        // Título
        $pdf->Cell(0, 10, 'Pedidos', 0, 1, 'C');

        // Encabezado de la tabla
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(30, 10, 'Nombre', 1);
        $pdf->Cell(30, 10, 'CI', 1);
        $pdf->Cell(30, 10, 'Celular', 1);
        $pdf->Cell(30, 10, 'Destino', 1);
        $pdf->Cell(30, 10, 'Estado', 1);
        $pdf->Cell(60, 10, 'Semana', 1);
        $pdf->Ln();

        // Cuerpo de la tabla
        $pdf->SetFont('Arial', '', 12);
        foreach ($data as $item) {
            $pdf->Cell(30, 10, $item->nombre, 1);
            $pdf->Cell(30, 10, $item->ci, 1);
            $pdf->Cell(30, 10, $item->celular, 1);
            $pdf->Cell(30, 10, $item->destino, 1);
            $pdf->Cell(30, 10, $item->estado, 1);
            $pdf->Cell(60, 10, $item->semana->nombre, 1);
            $pdf->Ln();
        }

        // Salida del PDF
        return $pdf->Output('S');
    }
}
