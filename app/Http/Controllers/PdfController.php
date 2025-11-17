<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use FPDF;
use App\Models\Pedido;

class PdfController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function nuevo_pdf()
    {
        // Obtener todos los pedidos
        $pedidos = Pedido::all()->toArray(); // Convierte a array para facilidad de uso

        // Crear instancia de FPDF
        $pdf = new FPDF('P', 'mm', 'Legal');
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $marginLeft = 10;  // Margen izquierdo
        $marginTop = 10;   // Margen superior
        $boxWidth = 90;    // Ancho de cada tabla
        $boxHeight = 60;   // Altura de cada tabla
        $spaceBetweenBoxes = 10; // Espacio entre tablas

        $x = $marginLeft;
        $y = $marginTop;

        foreach ($pedidos as $pedido) {
            // Verificar si hay suficiente espacio en la página actual
            if ($y + $boxHeight > $pdf->GetPageHeight() - $marginTop) {
                $pdf->AddPage(); // Nueva página si no hay suficiente espacio
                $x = $marginLeft; // Reiniciar posición X
                $y = $marginTop; // Reiniciar posición Y
            }

            // Crear factura en la posición calculada
            $this->createInvoice($pdf, $pedido, $x, $y);

            // Actualizar la posición Y para la siguiente factura
            $y += $boxHeight + $spaceBetweenBoxes;

            // Si el próximo box no cabe en la misma columna, mover a la siguiente columna
            if ($y + $boxHeight > $pdf->GetPageHeight() - $marginTop) {
                $y = $marginTop; // Reiniciar posición Y
                $x += $boxWidth + $spaceBetweenBoxes; // Mover a la siguiente columna
                // Si el próximo box no cabe en la página actual, añadir nueva página
                if ($x + $boxWidth > $pdf->GetPageWidth() - $marginLeft) {
                    $pdf->AddPage(); // Nueva página si no hay suficiente espacio horizontal
                    $x = $marginLeft; // Reiniciar posición X
                    $y = $marginTop; // Reiniciar posición Y
                }
            }
        }

        // Output el PDF al navegador
        return response($pdf->Output('S', 'pedidos.pdf'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="pedidos.pdf"');
    }

    public function createInvoice($pdf, $row, $x, $y)
    {
        $cellHeight = 6; // Ajustado para cada fila

        // Establecer la posición inicial de la tabla
        $pdf->SetXY($x, $y);

        // Usa una fuente que soporte caracteres especiales
        $pdf->SetFont('Arial', 'B', 10); // Tamaño de fuente reducido
        $pdf->Cell(100, $cellHeight, "IMPORTADORA MIRANDA", 1, 1, 'C');  // Ancho ajustado

        // Ajustar posición después de escribir la primera celda
        $pdf->SetX($x);
        $pdf->SetFont('Arial', '', 8);
        $nameCellWidth = 65; // Ancho de la celda del nombre
        $ciCellWidth = 35; // Ancho de la celda del CI
        
        // Añadir celdas en la misma fila
        $pdf->Cell($nameCellWidth, $cellHeight, "Nombre: " . strtoupper($row['nombre']), 1, 0, 'L');
        $pdf->Cell($ciCellWidth, $cellHeight, "CI: " . strtoupper($row['ci']), 1, 1, 'L');

        // Continuar con el resto de los datos

        // Ajustar la posición X nuevamente
        $pdf->SetX($x);
        $pdf->Cell(50, $cellHeight, "Celular: " . strtoupper($row['celular']), 1, 0, 'L');
        $pdf->Cell(50, $cellHeight, "Destino: " . strtoupper($row['destino']), 1, 1, 'L');

        // Ajustar posición X para la siguiente línea
        $pdf->SetX($x);
        $pdf->SetFont('Arial', '', 8);
        $direccion = "Direccion: " . strtoupper($row['direccion']);
        $pdf->MultiCell(100, 5, $direccion, 1, 'L');

        // Ajustar posición X después de MultiCell
        $pdf->SetX($x);
        $detalleProducto = "Detalle del Producto: " . strtoupper($row['detalle']);
        $pdf->MultiCell(100, 5, $detalleProducto, 1, 'L');

        // Ajustar posición X después de MultiCell
        $pdf->SetX($x);
        $producto = "Producto: " . strtoupper($row['productos']);
        $pdf->MultiCell(100, 5, $producto, 1, 'L');

        // Volver al tamaño de fuente original y ajustar la posición para el siguiente contenido
        $pdf->SetX($x);
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(33.3, $cellHeight, "Monto de Deposito: " . strtoupper($row['monto_deposito']), 1, 0, 'L');
        $pdf->Cell(33.3, $cellHeight, "Monto de Envio: " . strtoupper($row['monto_enviado_pagado']), 1, 0, 'L');
        $pdf->Cell(33.3, $cellHeight, "Fecha: " . strtoupper($row['fecha']), 1, 1, 'L');

        // Ajustar la posición para firmas
        $pdf->SetX($x);
        $pdf->Cell(25, $cellHeight, "Firma Recepcion", 1, 0, 'L');
        $pdf->Cell(25, $cellHeight, " ", 1, 0, 'L');
        $pdf->Cell(25, $cellHeight, "Firma Importadora", 1, 0, 'L');
        $pdf->Cell(25, $cellHeight, " ", 1, 1, 'L');

        $pdf->SetX($x);
        $pdf->Cell(25, $cellHeight, "Firma Mensajero", 1, 0, 'L');
        $pdf->Cell(25, $cellHeight, " ", 1, 0, 'L');
        $pdf->Cell(25, $cellHeight, "Firma Cliente", 1, 0, 'L');
        $pdf->Cell(25, $cellHeight, " ", 1, 1, 'L');
    }


    public function generatePdf()
    {
        // Obtener todos los pedidos
        $pedidos = Pedido::all();
    
        // Crear instancia de FPDF
        $pdf = new FPDF('P', 'mm', array(216, 330)); // Orientación Portrait (P), unidades en milímetros, tamaño de página en mm
    
        // Agregar la primera página
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12); // Fuente de tamaño 12 para el texto
    
        // Definir márgenes
        $margin = 10; // Margen de 10 mm alrededor de la página
    
        // Definir el tamaño de las celdas en función de la página oficio (216x330 mm) menos márgenes
        $pageWidth = 216 - 2 * $margin; // Ancho de la página menos márgenes
        $pageHeight = 330 - 2 * $margin; // Altura de la página menos márgenes
    
        $numColumns = 2; // Número de columnas
        $numRows = 3; // Número de filas
    
        $cellWidth = $pageWidth / $numColumns; // Ancho de cada celda (2 columnas)
        $cellHeight = $pageHeight / $numRows; // Altura de cada celda (3 filas)
    
        // Ruta del logo
        $logoPath = public_path('images/logo_gris-3.png'); // Ruta al logo en el directorio 'public'
    
        // Contadores
        $cellCount = 0;
        $cellsInPage = 0;
        $cellPerPage = $numColumns * $numRows; // Número máximo de celdas por página
        $i=1;
        // Recorrer los pedidos y crear celdas con información
        foreach ($pedidos as $pedido) {
            
            // Si se alcanzó el número máximo de celdas por página, agregar una nueva página
            if ($cellsInPage >= $cellPerPage) {
                $pdf->AddPage(); // Agregar nueva página
                $pdf->SetFont('Arial', 'B', 12); // Establecer la fuente en la nueva página
                $pdf->SetXY($margin, $margin); // Volver a los márgenes
                $cellsInPage = 0; // Restablecer el contador de celdas por página
                $cellCount = 0; // Reiniciar el contador de celdas por fila
            }
    
            // Calcular la posición de la celda actual
            $x = $margin + ($cellCount % $numColumns) * $cellWidth;
            $y = $margin + floor($cellCount / $numColumns) * $cellHeight;
    
            // Crear un marco alrededor de la celda
            $pdf->Rect($x, $y, $cellWidth, $cellHeight);
    
            // Agregar el logo como marca de agua dentro de la celda
            $pdf->Image($logoPath, $x, $y, $cellWidth, $cellHeight, 'PNG'); // Ajusta tamaño y posición según sea necesario
    
            // Reducir el espaciado entre líneas de texto
            $lineHeight = 10; // Ajusta este valor para cambiar el espaciado entre líneas
    
            // Convertir datos a mayúsculas
            $id = strtoupper($pedido->id);
            $nombre = strtoupper($pedido->nombre);
            $ci = strtoupper($pedido->ci);
            $celular = strtoupper($pedido->celular);
            $destino = strtoupper($pedido->destino);
            $estado = strtoupper($pedido->estado);
    
            // Agregar los datos del pedido dentro del cuadro
            $pdf->SetXY($x + 5, $y + 5);
            $pdf->SetFont('Arial', 'B', 16); // Fuente para el texto
    
            // Ajustar el texto largo dentro de la celda usando MultiCell
            $pdf->MultiCell($cellWidth - 10, $lineHeight, utf8_decode('N#: ' . $i), 0, 'M');
    
            $pdf->SetXY($x + 5, $pdf->GetY()); // Ajustar la posición Y para la siguiente línea
            $pdf->MultiCell($cellWidth - 10, $lineHeight, utf8_decode('NOMBRE: ' . $nombre), 0, 'L');
    
            $pdf->SetXY($x + 5, $pdf->GetY()); // Ajustar la posición Y para la siguiente línea
            $pdf->MultiCell($cellWidth - 10, $lineHeight, utf8_decode('CI: ' . $ci), 0, 'L');
    
            $pdf->SetXY($x + 5, $pdf->GetY()); // Ajustar la posición Y para la siguiente línea
            $pdf->MultiCell($cellWidth - 10, $lineHeight, utf8_decode('CEL: ' . $celular), 0, 'L');
    
            $pdf->SetXY($x + 5, $pdf->GetY()); // Ajustar la posición Y para la siguiente línea
            $pdf->MultiCell($cellWidth - 10, $lineHeight, utf8_decode('DESTINO: ' . $destino), 0, 'L');
    
            $pdf->SetXY($x + 5, $pdf->GetY()); // Ajustar la posición Y para la siguiente línea
            $pdf->MultiCell($cellWidth - 10, $lineHeight, utf8_decode('ESTADO: ' . $estado), 0, 'L');
    
            // Mover el cursor a la siguiente celda
            $cellCount++;
            $cellsInPage++; // Incrementar el contador de celdas por página
            $i++;
        }
    
        // Output el PDF al navegador
        return response($pdf->Output('S', 'pedidos.pdf'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="pedidos.pdf"');
    }
    

    
}
