<?php

namespace App\Http\Controllers;

use App\Models\Envio;
use App\Models\Inventario;
use Illuminate\Http\Request;
use FPDF;
use App\Models\Pedido;
use App\Models\PedidoProducto;
use App\Models\Producto;
use App\Models\Semana;
use App\Models\Sucursale;
use Carbon\Carbon;
use PDF;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class OrdenPdfController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function nuevo_pdf_id($id, Request $request)
    {
        $destinosSeleccionados = explode(',', $request->input('destinos', ''));

        // Obtener los pedidos para la semana con ID $id y los destinos seleccionados
        $pedidos = Pedido::where('id_semana', $id)
            ->where(function ($query) use ($destinosSeleccionados) {
                foreach ($destinosSeleccionados as $destino) {
                    $query->orWhere('destino', 'LIKE', '%' . $destino . '%');
                }
            })
            ->get();
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
        $cellHeight = 6;

        $pdf->SetXY($x, $y);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(85, $cellHeight, utf8_decode("IMPORTADORA MIRANDA  #" . strtoupper($row['id'])), 1, 0, 'C');
        $pdf->Cell(15, $cellHeight, utf8_decode(strtoupper($row['codigo'])), 1, 1, 'C');
        $pdf->SetX($x);
        $nameFontSize = $this->getFontSize($row['nombre'], 20); // Ajusta el tamaño del nombre
        $pdf->SetFont('Arial', '', $nameFontSize);
        $pdf->Cell(75, $cellHeight, utf8_decode("Nombre: " . strtoupper($row['nombre'])), 1, 0, 'L');


        $ciFontSize = $this->getFontSize($row['ci'], 20); // Ajusta el tamaño del nombre
        $pdf->SetFont('Arial', '', $ciFontSize);
        $pdf->Cell(25, $cellHeight, utf8_decode("CI: " . strtoupper($row['ci'])), 1, 1, 'L');

        $pdf->SetX($x);
        $celularFontSize = $this->getFontSize($row['celular'], 20);
        $pdf->SetFont('Arial', '', $celularFontSize);
        $pdf->Cell(30, $cellHeight, utf8_decode("Celular: " . strtoupper($row['celular'])), 1, 0, 'L');

        $destinoFontSize = $this->getFontSize($row['destino'], 20);
        $pdf->SetFont('Arial', '', $destinoFontSize);
        $pdf->Cell(70, $cellHeight, utf8_decode("Destino: " . strtoupper($row['destino'])), 1, 1, 'L');

        $pdf->SetX($x);
        $direccionFontSize = $this->getFontSize($row['direccion'], 30); // Ajusta el tamaño según la longitud
        $pdf->SetFont('Arial', '', $direccionFontSize);
        $direccion = utf8_decode("Direccion: " . strtoupper($row['direccion']));
        $pdf->MultiCell(100, 3.5, $direccion, 1, 'L');

        $pdf->SetX($x);
        $detalleFontSize = $this->getFontSize($row['detalle'], 30);
        $pdf->SetFont('Arial', '', $detalleFontSize);
        $detalleProducto = utf8_decode("Detalle del Producto: " . strtoupper($row['detalle']));
        $pdf->MultiCell(100, 3.5, $detalleProducto, 1, 'L');

        // Decodificar y mostrar los productos
        // Decodificar el JSON de productos
        // Decodificar el JSON de productos
        $productos = json_decode($row->productos, true); // Decodificamos el JSON de los productos

        // Verificamos si la decodificación fue exitosa y si es un array
        if (is_array($productos)) {
            // Si es un array, procesamos los productos
            $productosNombres = [];
            foreach ($productos as $producto) {
                // Verificar si 'id_producto' está presente y es un valor numérico
                if (isset($producto['id_producto']) && is_numeric($producto['id_producto'])) {
                    // Si 'id_producto' es numérico, obtenemos el nombre del producto
                    $productoNombre = Producto::find($producto['id_producto'])->nombre ?? 'Producto no encontrado';
                    $productosNombres[] = $productoNombre;
                } else {
                    // Si no es un id_producto válido (como una cadena como '167W' o '2 POWERB MAGNETIC'),
                    // usamos directamente el valor de producto (que se espera sea un nombre o código)
                    $productosNombres[] = strtoupper($producto['id_producto']);
                }
            }
            $productosString = implode(', ', $productosNombres); // Unimos los nombres de los productos con comas
        } else {
            // Si el JSON no es válido o no es un array, simplemente usamos el valor original de $row->productos
            $productosString = strtoupper($row->productos);
        }

        $pdf->SetX($x);
        $productoFontSize = $this->getFontSize($productosString, 30); // Ajustar tamaño del texto de productos
        $pdf->SetFont('Arial', '', $productoFontSize);
        $producto = utf8_decode("Producto: " . strtoupper($productosString));
        $pdf->MultiCell(100, 3, $producto, 1, 'L');

        $pdf->SetX($x);
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(33.3, $cellHeight, utf8_decode("Monto de Deposito: " . strtoupper($row['monto_deposito'])), 1, 0, 'L');
        $pdf->Cell(33.3, $cellHeight, utf8_decode("" . strtoupper($row['estado'])), 1, 0, 'C');
        $pdf->Cell(33.4, $cellHeight, utf8_decode("Fecha: " . strtoupper($row['fecha'])), 1, 1, 'L');

        $pdf->SetX($x);
        $pdf->Cell(25, $cellHeight, utf8_decode("Firma Recepcion"), 1, 0, 'L');
        $pdf->Cell(25, $cellHeight, " ", 1, 0, 'L');
        $pdf->Cell(25, $cellHeight, utf8_decode("Firma Importadora"), 1, 0, 'L');
        $pdf->Cell(25, $cellHeight, " ", 1, 1, 'L');

        $pdf->SetX($x);
        $pdf->Cell(25, $cellHeight, utf8_decode("Firma Mensajero"), 1, 0, 'L');
        $pdf->Cell(25, $cellHeight, " ", 1, 0, 'L');
        $pdf->Cell(25, $cellHeight, utf8_decode("Firma Cliente"), 1, 0, 'L');
        $pdf->Cell(25, $cellHeight, " ", 1, 1, 'L');
    }

    private function getFontSize($text, $maxLength)
    {
        $baseSize = 7.5; // Tamaño base
        $length = strlen($text);
        if ($length > $maxLength) {
            // Calcular el tamaño de la fuente en función de la longitud del texto
            return max(5, $baseSize - floor(($length - $maxLength) / 10)); // Decrementa el tamaño con un mínimo de 6
        }
        return $baseSize; // Tamaño base si no excede la longitud máxima
    }

    public function generatePdfid($id, Request $request)
    {
        $destinosSeleccionados = explode(',', $request->input('destinos', ''));

        // Obtener los pedidos para la semana con ID $id y los destinos seleccionados
        $pedidos = Pedido::where('id_semana', $id)
            ->where(function ($query) use ($destinosSeleccionados) {
                foreach ($destinosSeleccionados as $destino) {
                    $query->orWhere('destino', 'LIKE', '%' . $destino . '%');
                }
            })
            ->get();
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
        $i = 1;
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
            $codigo = strtoupper($pedido->codigo);

            // Agregar los datos del pedido dentro del cuadro
            $pdf->SetXY($x + 5, $y + 5);
            $pdf->SetFont('Arial', 'B', 16); // Fuente para el texto

            // Ajustar el texto largo dentro de la celda usando MultiCell
            $pdf->MultiCell($cellWidth - 10, $lineHeight, utf8_decode('N#: ' . $id), 0, 'M');
            $pdf->MultiCell($cellWidth - 10, $lineHeight, utf8_decode('  Codigo: ' . $codigo), 0, 'M');
            $pdf->SetFont('Arial', 'B', 11); // Fuente para el texto
            $pdf->SetXY($x + 4, $pdf->GetY()); // Ajustar la posición Y para la siguiente línea
            $pdf->MultiCell($cellWidth - 1, $lineHeight, utf8_decode('NOMBRE: ' . $nombre), 0, 'L');

            $pdf->SetFont('Arial', 'B', 16); // Fuente para el texto
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
    public function reporteResumesn($id, Request $request)
    {
        // Obtener los destinos seleccionados
        $destinosSeleccionados = $request->input('destinos');

        // Obtener todos los pedidos para la semana con ID $id y los destinos seleccionados
        $pedidos = Pedido::where('id_semana', $id)
            ->whereIn('destino', $destinosSeleccionados) // Filtrar por destinos
            ->get();

        // Crear instancia de FPDF
        $pdf = new FPDF('L'); // 'L' para orientación horizontal
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);

        // Encabezados de las columnas
        $pdf->Cell(90, 10, 'Nombre', 1);
        $pdf->Cell(25, 10, 'Celular', 1);
        $pdf->Cell(70, 10, 'Destino', 1);
        $pdf->Cell(30, 10, 'Estado', 1);
        $pdf->Cell(40, 10, 'Monto Restante', 1);
        $pdf->Cell(30, 10, 'Fecha', 1);
        $pdf->Ln();

        // Datos de los pedidos
        foreach ($pedidos as $pedido) {
            $montoRestante = $pedido->monto_deposito - $pedido->monto_enviado_pagado;

            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(90, 10, utf8_decode($pedido->nombre), 1);
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(25, 10, utf8_decode($pedido->celular), 1);
            $pdf->Cell(70, 10, utf8_decode($pedido->destino), 1);
            $pdf->Cell(30, 10, utf8_decode($pedido->estado), 1);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(40, 10, number_format($montoRestante, 2) . ' BS', 1);

            // Asegúrate de que la fecha esté formateada correctamente
            $pdf->Cell(30, 10, Carbon::parse($pedido->fecha)->format('d/m/Y'), 1);
            $pdf->Ln();
        }

        // Output el PDF al navegador
        return response($pdf->Output('S', 'reporte_resumen.pdf'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="reporte_resumen.pdf"');
    }

    public function reporteResumen($id, Request $request)
    {
        $destinosSeleccionados = $request->input('destinos', []);

        // Iniciar la consulta
        $query = Pedido::where('id_semana', $id);

        // Añadir condiciones con LIKE
        if (!empty($destinosSeleccionados)) {
            $query->where(function ($q) use ($destinosSeleccionados) {
                foreach ($destinosSeleccionados as $destino) {
                    $q->orWhere('destino', 'LIKE', '%' . $destino . '%');
                }
            });
        }

        // Obtener los pedidos que coinciden con los destinos seleccionados
        $pedidos = $query->get();
        // Crear instancia de FPDF
        $pdf = new FPDF('L'); // 'L' para orientación horizontal
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);

        // Encabezados de las columnas
        $pdf->Cell(90, 10, 'Nombre', 1);
        $pdf->Cell(25, 10, 'Celular', 1);
        $pdf->Cell(70, 10, 'Destino', 1);
        $pdf->Cell(30, 10, 'Estado', 1);
        $pdf->Cell(40, 10, 'Monto Restante', 1);
        $pdf->Cell(30, 10, 'Fecha', 1);
        $pdf->Ln();

        // Datos de los pedidos
        foreach ($pedidos as $pedido) {
            $montoRestante = $pedido->monto_deposito - $pedido->monto_enviado_pagado;
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(90, 10, utf8_decode($pedido->nombre), 1);
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(25, 10, utf8_decode($pedido->celular), 1);
            $pdf->Cell(70, 10, utf8_decode($pedido->destino), 1);
            $pdf->Cell(30, 10, utf8_decode($pedido->estado), 1);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(40, 10, number_format($montoRestante, 2) . ' BS', 1);

            // Asegúrate de que la fecha esté formateada correctamente
            $pdf->Cell(30, 10, Carbon::parse($pedido->fecha)->format('d/m/Y'), 1);
            $pdf->Ln();
        }

        // Output el PDF al navegador
        return response($pdf->Output('S', 'reporte_resumen.pdf'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="reporte_resumen.pdf"');
    }



    // Función para generar PDF de pedidos seleccionados (nuevo)
    public function generarPdfNuevo(Request $request, $idSemana)
    {
        // Obtener los IDs de los pedidos seleccionados
        $pedidoIds = explode(',', $request->input('pedidos', ''));

        // Obtener los pedidos con los IDs seleccionados
        $pedidos = Pedido::whereIn('id', $pedidoIds)->get();

        // Crear el PDF
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

    // Función para generar reporte de fichas de pedidos seleccionados
    public function generarReporteFicha($id, Request $request)
    {
        // Obtener los IDs de los pedidos seleccionados
        $pedidoIds = explode(',', $request->input('pedidos', ''));

        // Obtener los pedidos con los IDs seleccionados
        $pedidos = Pedido::whereIn('id', $pedidoIds)->get();

        // Crear instancia de FPDF
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
        $i = 1;
        // Recorrer los pedidos y crear celdas con información
        foreach ($pedidos as $pedido) {

            // Si se alcanzó el número máximo de celdas por página, agregar una nueva página
            if ($cellsInPage >= $cellPerPage) {
                $pdf->AddPage(); // Agregar nueva página
                $pdf->SetFont('Arial', 'B', 9); // Establecer la fuente en la nueva página
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
            $codigo = strtoupper($pedido->codigo);

            // Agregar los datos del pedido dentro del cuadro
            $pdf->SetXY($x + 5, $y + 5);
            $pdf->SetFont('Arial', 'B', 16); // Fuente para el texto

            // Ajustar el texto largo dentro de la celda usando MultiCell
            $pdf->MultiCell($cellWidth - 10, $lineHeight, utf8_decode('N#: ' . $id), 0, 'M');

            $pdf->MultiCell($cellWidth - 10, $lineHeight, utf8_decode('  Codigo: ' . $codigo), 0, 'M');
            $pdf->SetFont('Arial', 'B', 9); // Fuente para el texto
            $pdf->SetXY($x + 4, $pdf->GetY()); // Ajustar la posición Y para la siguiente línea
            $pdf->MultiCell($cellWidth - 1, $lineHeight, utf8_decode('NOMBRE: ' . $nombre), 0, 'L');

            $pdf->SetFont('Arial', 'B', 16); // Fuente para el texto
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

    public function bcResumen(Request $request, $idSemana)
    {
        // Obtener los IDs de los pedidos seleccionados
        $pedidoIds = explode(',', $request->input('pedidos', ''));

        // Obtener los pedidos con los IDs seleccionados
        $pedidos = Pedido::whereIn('id', $pedidoIds)->get();

        // Crear el PDF
        $pdf = new FPDF('P', 'mm', 'Legal');
        $pdf->AliasNbPages();
        $pdf->AddPage();

        // Agregar imagen de fondo centrada
        $imagePath = 'images/logo_gris-3.png';
        $imageWidth = 216; // Ancho de la imagen
        $imageHeight = 216; // Alto de la imagen
        $pdf->Image($imagePath, (216 - $imageWidth) / 2, (356 - $imageHeight) / 2, $imageWidth, $imageHeight); // Centrado

        // Configuración del documento
        $marginLeft = 10;  // Margen izquierdo
        $marginTop = 10;   // Margen superior

        // Logo en la esquina izquierda
        $pdf->Image('images/logo_gris-3.png', $marginLeft, $marginTop, 40);

        // Información en la esquina derecha
        $pdf->SetX(150);
        $pdf->SetFont('Arial', 'I', 8); // Reducido a tamaño 8
        $pdf->SetTextColor(0, 102, 204); // Color azul
        $pdf->Cell(0, 5, utf8_decode("Pagina: importadoramiranda.com/vistas"), 0, 1, 'R');
        $pdf->Cell(0, 5, utf8_decode("Catalogo: importadoramiranda777.sumerlabs.com"), 0, 1, 'R');
        $pdf->Cell(0, 5, utf8_decode("Contactos: 70621016"), 0, 1, 'R');
        $pdf->Cell(0, 5, utf8_decode("Fecha: " . date('Y-m-d')), 0, 1, 'R');

        $pdf->Ln(25);
        // Título
        $pdf->SetFont('Helvetica', 'B', 30); // Cambiar a Helvetica y aumentar tamaño a 30
        $pdf->SetTextColor(0, 51, 102); // Color azul oscuro
        $pdf->Cell(0, 10, utf8_decode("IMPORTADORA MIRANDA"), 0, 1, 'C');
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->Cell(0, 10, utf8_decode("A un Click del Producto que necesitas"), 0, 1, 'C');

        $pdf->Ln(10);

        // Cabecera de la tabla
        $cellHeight = 10;
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(0, 102, 204); // Azul para la cabecera
        $pdf->SetTextColor(255, 255, 255); // Texto blanco
        $pdf->Cell(10, $cellHeight, utf8_decode("#"), 1, 0, 'C', true); // Columna para el contador
        $pdf->Cell(20, $cellHeight, utf8_decode("Código"), 1, 0, 'C', true);
        $pdf->Cell(50, $cellHeight, utf8_decode("Nombre"), 1, 0, 'C', true);
        $pdf->Cell(25, $cellHeight, utf8_decode("CI"), 1, 0, 'C', true);
        $pdf->Cell(25, $cellHeight, utf8_decode("Celular"), 1, 0, 'C', true);
        $pdf->Cell(60, $cellHeight, utf8_decode("Destino"), 1, 0, 'C', true);
        $pdf->Cell(10, $cellHeight, '', 1, 1, 'C', true);

        // Datos de los pedidos
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetTextColor(0, 0, 0);

        $counter = 1; // Inicializar el contador
        foreach ($pedidos as $pedido) {
            $pdf->SetFillColor(255, 255, 255); // Color de fondo blanco
            $pdf->Cell(10, $cellHeight, utf8_decode(strval($counter++)), 0, 0, 'C', true); // Mostrar el contador
            $pdf->Cell(20, $cellHeight, utf8_decode(strtoupper($pedido['id'])), 0, 0, 'C', true);
            $pdf->Cell(50, $cellHeight, utf8_decode(strtoupper($pedido['nombre'])), 0, 0, 'C', true);
            $pdf->Cell(25, $cellHeight, utf8_decode(strtoupper($pedido['ci'])), 0, 0, 'C', true);
            $pdf->Cell(25, $cellHeight, utf8_decode(strtoupper($pedido['celular'])), 0, 0, 'C', true);
            $pdf->Cell(60, $cellHeight, utf8_decode(strtoupper($pedido['destino'])), 0, 0, 'C', true);

            $pdf->Cell(10, $cellHeight, '', 1, 1, 'C', true); // Checkbox

            // Dibujar línea horizontal "translúcida"
            $pdf->SetDrawColor(200, 200, 200); // Color gris claro
            $pdf->Line(10, $pdf->GetY(), 205, $pdf->GetY()); // Línea horizontal

            // Resaltar la línea después de cada pedido
            $pdf->SetDrawColor(0, 102, 204); // Color azul para resaltar
            $pdf->SetLineWidth(0.5); // Ancho de línea
            $pdf->Line(10, $pdf->GetY(), 205, $pdf->GetY()); // Línea horizontal resaltada

            // Mover hacia abajo para la siguiente fila
            $pdf->Ln(0);
        }

        // Check if there is enough space for the signatures
        if ($pdf->GetY() + 50 > 340) { // Assuming 50 is the total height needed for signatures and titles
            $pdf->AddPage(); // Add a new page if there is not enough space
        }
        // Mover hacia abajo para la siguiente fila
        $pdf->Ln(0);  // Mover hacia abajo para la siguiente fila
        $pdf->Ln(0);
        // Firmas
        $cellHeight = 30;
        $pdf->Cell(90, $cellHeight, "__________________________", 0, 0, 'C');
        $pdf->Cell(50, $cellHeight, "", 0, 0, 'C');
        $pdf->Cell(20, $cellHeight, "__________________________", 0, 1, 'C');

        // Títulos de las firmas
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(90, $cellHeight - 50, utf8_decode("Firma BCPLUS"), 0, 0, 'C');
        $pdf->Cell(50, $cellHeight, "", 0, 0, 'C');
        $pdf->Cell(20, $cellHeight - 50, utf8_decode("Firma Importadora"), 0, 0, 'C');

        // Añadir un borde decorativo alrededor de la página
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Rect(5, 5, 205, 340);

        // Salida
        return response($pdf->Output('S', 'pedidos.pdf'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="pedidos.pdf"');
    }

    public function validarPedidos(Request $request)
    {
        // Obtener los IDs de los envíos seleccionados (realmente son id_envio)
        $envioIds = $request->input('pedidos', []);

        // Validar si hay envíos seleccionados
        if (empty($envioIds)) {
            return response()->json(['error' => 'No se seleccionaron envíos.'], 400);
        }

        // Inicializar colección para productos de los pedidos relacionados
        $pedidosProductos = collect();

        // Iterar sobre cada envío
        foreach ($envioIds as $envioId) {
            $envio = \App\Models\Envio::with('pedido')->find($envioId);

            if ($envio && $envio->pedido) {
                $productos = \App\Models\PedidoProducto::with(['pedido', 'producto'])
                    ->where('id_pedido', $envio->pedido->id)
                    ->get();

                $pedidosProductos = $pedidosProductos->merge($productos);
            }
        }

        if ($pedidosProductos->isEmpty()) {
            return response()->json(['error' => 'No se encontraron productos para los envíos seleccionados.'], 404);
        }

        // Agrupar por id_pedido
        $pedidosUnicos = $pedidosProductos->groupBy('id_pedido');

        // Validar si alguno de los pedidos ya está confirmado
        foreach ($pedidosUnicos as $pedidoId => $productosDelPedido) {
            $pedido = $productosDelPedido->first()->pedido;

            if (strtolower($pedido->estado_pedido ?? '') === 'confirmado') {
                return response()->json([
                    'error' => "El pedido con ID #{$pedido->id} ya está confirmado."
                ], 400);
            }
        }

        // Validar disponibilidad de inventario
        foreach ($pedidosProductos as $pedidoProducto) {
            $producto = $pedidoProducto->producto;
            $cantidadSolicitada = $pedidoProducto->cantidad;

            if (!$producto) {
                return response()->json([
                    'error' => 'Producto no encontrado para uno de los pedidos.'
                ], 400);
            }

            $inventario = \App\Models\Inventario::where('id_producto', $producto->id)
                ->where('id_sucursal', 1)
                ->first();

            if (!$inventario || $inventario->cantidad < $cantidadSolicitada) {
                $cantidadDisponible = $inventario ? $inventario->cantidad : 0;
                return response()->json([
                    'error' => "Stock insuficiente para el producto: " . strtoupper($producto->nombre ?? 'SIN NOMBRE') .
                        ". Solicitado: {$cantidadSolicitada}, Disponible: {$cantidadDisponible}"
                ], 400);
            }
        }

        // Si la validación fue exitosa, devolver la URL del PDF
        $params = http_build_query(['pedidos' => $envioIds]);
        Log::info('Parametros de la solicitud:', ['params' => $params]);
        Log::info('Parametros de la solicitud:', ['envios' => $envioIds]);

        // Generar la URL correctamente
        $pdfUrl = route('envios.generarPdf', ['pedidos' => $envioIds]);

        return response()->json(['pdfUrl' => $pdfUrl]);
    }

  public function generarPdf(Request $request)
    {
        $envioIds = $request->input('pedidos', []);

        if (empty($envioIds)) {
            return response()->json([
                'error' => 'No se seleccionaron pedidos.',
                'contenido_recibido' => $envioIds
            ], 400);
        }

        $pedidosProductos = collect();

        foreach ($envioIds as $envioId) {
            $envio = Envio::with('pedido')->find($envioId);

            if ($envio && $envio->pedido) {
                $productos = PedidoProducto::with(['pedido', 'producto'])
                    ->where('id_pedido', $envio->pedido->id)
                    ->get();

                $pedidosProductos = $pedidosProductos->merge($productos);
            }
        }

        if ($pedidosProductos->isEmpty()) {
            return response()->json(['error' => 'No se encontraron productos para los pedidos.'], 404);
        }

        $pedidosProductos = $pedidosProductos->sortBy(function ($producto) {
            return $producto->pedido->id;
        });

        $pedidosUnicos = $pedidosProductos->groupBy('id_pedido');

        foreach ($pedidosProductos as $pedidoProducto) {
            $producto = $pedidoProducto->producto;
            $cantidadSolicitada = $pedidoProducto->cantidad;

            if (!$producto) {
                return response()->json([
                    'error' => 'Producto no encontrado para uno de los pedidos.'
                ], 400);
            }

            $inventario = Inventario::where('id_producto', $producto->id)
                ->where('id_sucursal', 1)
                ->first();

            // if (!$inventario || $inventario->cantidad < $cantidadSolicitada) {
            //     $cantidadDisponible = $inventario ? $inventario->cantidad : 0;
            //     return response()->json([
            //         'error' => "Stock insuficiente para el producto: " . strtoupper($producto->nombre ?? 'SIN NOMBRE') .
            //             ". Solicitado: {$cantidadSolicitada}, Disponible: {$cantidadDisponible}"
            //     ], 400);
            // }

            $inventario->cantidad -= $cantidadSolicitada;
            $inventario->save();
        }

        //  Buscar o crear semana para hoy
        $hoy = Carbon::today()->format('Y-m-d');
        $semana = Semana::firstOrCreate(
            ['fecha' => $hoy],
            ['nombre' => 'Pedidos Enviados del ' . $hoy]
        );

        // Crear PDF
        $pdf = new FPDF('P', 'mm', 'Legal');
        $pdf->AliasNbPages();
        $pdf->AddPage();

        $imagePath = public_path('images/logo_gris-3.png');
        $pdf->Image($imagePath, (216 - 216) / 2, (356 - 216) / 2, 216, 216);

        // Fecha centrada (formato: día-mes-año)
        $pdf->SetFont('Arial', 'B', 25);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetY(15); // Altura desde el borde superior
        $pdf->Cell(0, 10, utf8_decode("Fecha: " . date('d-m-Y')), 0, 1, 'C');

        $pdf->Image(public_path('images/logo_gris-3.png'), 10, 10, 40);
        $pdf->SetX(150);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->SetTextColor(0, 102, 204);
        $pdf->Cell(0, 5, utf8_decode("Pagina: importadoramiranda.com"), 0, 1, 'R');
        $pdf->Cell(0, 5, utf8_decode("Catalogo: shop.importadoramiranda.com"), 0, 1, 'R');
        $pdf->Cell(0, 5, utf8_decode("Contactos: 70621016"), 0, 1, 'R');
        $pdf->Cell(0, 5, utf8_decode("Fecha: " . $hoy), 0, 1, 'R');

        $pdf->Ln(25);
        $pdf->SetFont('Helvetica', 'B', 30);
        $pdf->SetTextColor(0, 51, 102);
        $pdf->Cell(0, 10, utf8_decode("IMPORTADORA MIRANDA"), 0, 1, 'C');
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->Cell(0, 10, utf8_decode("A un Click del Producto que necesitas"), 0, 1, 'C');

        $pdf->Ln(10);

        // Tabla encabezado
        $cellHeight = 10;
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(0, 102, 204);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(8, $cellHeight, "#", 1, 0, 'C', true);
        $pdf->Cell(12, $cellHeight, "Codigo", 1, 0, 'C', true);
        $pdf->Cell(50, $cellHeight, "Nombre", 1, 0, 'C', true);
        $pdf->Cell(25, $cellHeight, "Estado", 1, 0, 'C', true);
        $pdf->Cell(25, $cellHeight, "Celular", 1, 0, 'C', true);
        $pdf->Cell(70, $cellHeight, "Destino", 1, 0, 'C', true);
        $pdf->Cell(10, $cellHeight, "", 1, 1, 'C', true);

        $pdf->SetFont('Arial', '', 6);
        $pdf->SetTextColor(0, 0, 0);
        $counter = 1;

        foreach ($pedidosUnicos as $pedidoId => $productosDelPedido) {
            $pedido = $productosDelPedido->first()->pedido;

            $pdf->SetFont('Arial', 'B', 12);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(8, $cellHeight, strval($counter++), 1, 0, 'C', true);
            $pdf->Cell(12, $cellHeight, utf8_decode(strtoupper($pedido->id)), 1, 0, 'C', true);

            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Cell(50, $cellHeight, utf8_decode(strtoupper($pedido->nombre ?? 'N/A')), 1, 0, 'C', true);

            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(25, $cellHeight, utf8_decode(strtoupper($pedido->estado ?? 'N/A')), 1, 0, 'C', true);
             $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(25, $cellHeight, utf8_decode(strtoupper($pedido->celular ?? 'N/A')), 1, 0, 'C', true);
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(70, $cellHeight, utf8_decode(strtoupper($pedido->destino ?? 'N/A')), 1, 0, 'C', true);

            $pdf->Cell(10, $cellHeight, '', 1, 1, 'C', true);
        }

        // Confirmar pedidos y asignar semana
        foreach ($pedidosUnicos as $pedidoId => $productosDelPedido) {
            $pedido = $productosDelPedido->first()->pedido;
            $pedido->estado_pedido = 'confirmado';
            $pedido->id_semana = $semana->id;
            $pedido->save();
        }

        if ($pdf->GetY() + 50 > 340) {
            $pdf->AddPage();
        }

        $pdf->Ln(0);
        $pdf->Ln(0);

        $cellHeight = 30;
        $pdf->Cell(90, $cellHeight, "", 0, 0, 'C');
        $pdf->Cell(50, $cellHeight, "", 0, 0, 'C');
        $pdf->Cell(20, $cellHeight, "", 0, 1, 'C');

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(90, $cellHeight - 50, utf8_decode("Firma BCPLUS"), 0, 0, 'C');
        $pdf->Cell(50, $cellHeight, "", 0, 0, 'C');
        $pdf->Cell(20, $cellHeight - 50, utf8_decode("Firma Importadora"), 0, 0, 'C');

        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Rect(5, 5, 205, 340);

        return response($pdf->Output('S', 'pedidos.pdf'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="pedidos.pdf"');
    }
    
   public function generarPdfrespaldo(Request $request)
    {
        $envioIds = $request->input('pedidos', []); // Este contiene ids de envíos

        // Validar si hay envíos seleccionados
        if (empty($envioIds)) {
            return response()->json(['error' => 'No se seleccionaron envíos.'], 400);
        }

        // Crear un array para almacenar todos los productos
        $pedidosProductos = collect(); // colección vacía

        // Recorremos cada id_envio recibido
        foreach ($envioIds as $envioId) {
            // Buscar el envío y cargar el pedido relacionado
            $envio = \App\Models\Envio::with('pedido')->find($envioId);

            // Validar si el envío o el pedido existen
            if ($envio && $envio->pedido) {
                // Obtener los productos del pedido
                $productos = \App\Models\PedidoProducto::with('pedido')
                    ->where('id_pedido', $envio->pedido->id)
                    ->get();

                // Agregarlos a la colección principal
                $pedidosProductos = $pedidosProductos->merge($productos);
            }
        }

        // Verificar si se encontraron productos
        if ($pedidosProductos->isEmpty()) {
            return response()->json(['error' => 'No se encontraron productos para los envíos seleccionados.'], 404);
        }
        // Ordenar por id_pedido
        $pedidosProductos = $pedidosProductos->sortBy(function ($producto) {
            return $producto->pedido->id;
        });
        // Agrupar por id_pedido
        $pedidosUnicos = $pedidosProductos->groupBy('id_pedido');
        // Agrupar por destino y ordenar alfabéticamente
        $pedidosAgrupadosPorDestino = $pedidosProductos->groupBy(function ($producto) {
            $destino = strtoupper(trim($producto->pedido->destino ?? 'SIN DESTINO'));
            return $destino;
        })->sortKeys();


        // Crear el PDF
        $pdf = new FPDF('P', 'mm', 'Legal');
        $pdf->AliasNbPages();
        $pdf->AddPage();

        // Agregar imagen de fondo centrada
        $imagePath = public_path('images/logo_gris-3.png');
        $imageWidth = 216; // Ancho de la imagen
        $imageHeight = 216; // Alto de la imagen
        $pdf->Image($imagePath, (216 - $imageWidth) / 2, (356 - $imageHeight) / 2, $imageWidth, $imageHeight); // Centrado

        // Configuración del documento
        $marginLeft = 10;  // Margen izquierdo
        $marginTop = 10;   // Margen superior

        // Logo en la esquina izquierda
        $pdf->Image(public_path('images/logo_gris-3.png'), $marginLeft, $marginTop, 40);

        // Fecha centrada (formato: día-mes-año)
        $pdf->SetFont('Arial', 'B', 25);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetY(15); // Altura desde el borde superior
        $pdf->Cell(0, 10, utf8_decode("Fecha: " . date('d-m-Y')), 0, 1, 'C');
        
        // Información en la esquina derecha
        $pdf->SetX(150);
        $pdf->SetFont('Arial', 'I', 8); // Reducido a tamaño 8
        $pdf->SetTextColor(0, 102, 204); // Color azul
        $pdf->Cell(0, 5, utf8_decode("Pagina: importadoramiranda.com"), 0, 1, 'R');
        $pdf->Cell(0, 5, utf8_decode("Catalogo: shop.importadoramiranda.com"), 0, 1, 'R');
        $pdf->Cell(0, 5, utf8_decode("Contactos: 70621016"), 0, 1, 'R');
        $pdf->Cell(0, 5, utf8_decode("Fecha: " . date('Y-m-d')), 0, 1, 'R');

        $pdf->Ln(25);
        // Título
        $pdf->SetFont('Helvetica', 'B', 30); // Cambiar a Helvetica y aumentar tamaño a 30
        $pdf->SetTextColor(0, 51, 102); // Color azul oscuro
        $pdf->Cell(0, 10, utf8_decode("IMPORTADORA MIRANDA"), 0, 1, 'C');
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->Cell(0, 10, utf8_decode("A un Click del Producto que necesitas"), 0, 1, 'C');

        $pdf->Ln(10);

        // Cabecera de la tabla
        $cellHeight = 10;
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(0, 102, 204); // Azul para la cabecera
        $pdf->SetTextColor(255, 255, 255); // Texto blanco
        $pdf->Cell(8, $cellHeight, utf8_decode("#"), 1, 0, 'C', true); // Columna para el contador
        $pdf->Cell(12, $cellHeight, utf8_decode("Codigo"), 1, 0, 'C', true);
        $pdf->Cell(50, $cellHeight, utf8_decode("Nombre"), 1, 0, 'C', true);
        $pdf->Cell(25, $cellHeight, utf8_decode("Estado"), 1, 0, 'C', true);
        $pdf->Cell(25, $cellHeight, utf8_decode("Celular"), 1, 0, 'C', true);
        $pdf->Cell(70, $cellHeight, utf8_decode("Destino"), 1, 0, 'C', true);
        $pdf->Cell(10, $cellHeight, '', 1, 1, 'C', true);

        // Datos de los pedidos
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetTextColor(0, 0, 0);

        $counter = 1; // Inicializar el contador
        foreach ($pedidosAgrupadosPorDestino as $destino => $productosDelDestino) {

            // Agrupar por id_pedido dentro del destino
            $pedidosDentroDelDestino = $productosDelDestino->groupBy('id_pedido');

            foreach ($pedidosDentroDelDestino as $pedidoId => $productosDelPedido) {
                $pedido = $productosDelPedido->first()->pedido;

                $pdf->SetFont('Arial', 'B', 12);
                $pdf->SetFillColor(255, 255, 255);
                $pdf->Cell(8, $cellHeight, strval($counter++), 1, 0, 'C', true);
                $pdf->Cell(12, $cellHeight, utf8_decode(strtoupper($pedido->id)), 1, 0, 'C', true);

                $pdf->SetFont('Arial', 'B', 7);
                $pdf->Cell(50, $cellHeight, utf8_decode(strtoupper($pedido->nombre ?? 'N/A')), 1, 0, 'C', true);

                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell(25, $cellHeight, utf8_decode(strtoupper($pedido->estado ?? 'N/A')), 1, 0, 'C', true);

                $pdf->SetFont('Arial', 'B', 12);
                $pdf->Cell(25, $cellHeight, utf8_decode(strtoupper($pedido->celular ?? 'N/A')), 1, 0, 'C', true);

                $pdf->SetFont('Arial', 'B', 8);
                $pdf->Cell(70, $cellHeight, utf8_decode(strtoupper($pedido->destino ?? 'N/A')), 1, 0, 'C', true);

                $pdf->Cell(10, $cellHeight, '', 1, 1, 'C', true);
            }
        }


        $pdf->SetFont('Arial', 'B', 9);
        // Check if there is enough space for the signatures
        if ($pdf->GetY() + 50 > 340) { // Assuming 50 is the total height needed for signatures and titles
            $pdf->AddPage(); // Add a new page if there is not enough space
        }
        // Mover hacia abajo para la siguiente fila
        $pdf->Ln(0);  // Mover hacia abajo para la siguiente fila
        $pdf->Ln(0);
        // Firmas
        $cellHeight = 30;
        $pdf->Cell(90, $cellHeight, "", 0, 0, 'C');
        $pdf->Cell(50, $cellHeight, "", 0, 0, 'C');
        $pdf->Cell(20, $cellHeight, "", 0, 1, 'C');

        // Títulos de las firmas
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(90, $cellHeight - 50, utf8_decode("Firma BCPLUS"), 0, 0, 'C');
        $pdf->Cell(50, $cellHeight, "", 0, 0, 'C');
        $pdf->Cell(20, $cellHeight - 50, utf8_decode("Firma Importadora"), 0, 0, 'C');

        // Añadir un borde decorativo alrededor de la página
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Rect(5, 5, 205, 340);

        // Salida

        return response($pdf->Output('S', 'pedidos.pdf'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="pedidos.pdf"');
    }
    /*  public function generarPdfFichasSeleccionadas(Request $request)
    {
        $envioIds = $request->input('pedidos', []); // Este contiene ids de envíos

        // Validar si hay envíos seleccionados
        if (empty($envioIds)) {
            return response()->json(['error' => 'No se seleccionaron envíos.'], 400);
        }

        // Crear un array para almacenar todos los productos
        $pedidosProductos = collect(); // colección vacía

        // Recorremos cada id_envio recibido
        foreach ($envioIds as $envioId) {
            // Buscar el envío y cargar el pedido relacionado
            $envio = \App\Models\Envio::with('pedido')->find($envioId);

            // Validar si el envío o el pedido existen
            if ($envio && $envio->pedido) {
                // Obtener los productos del pedido
                $productos = \App\Models\PedidoProducto::with('pedido')
                    ->where('id_pedido', $envio->pedido->id)
                    ->get();

                // Agregarlos a la colección principal
                $pedidosProductos = $pedidosProductos->merge($productos);
            }
        }

        // Verificar si se encontraron productos
        if ($pedidosProductos->isEmpty()) {
            return response()->json(['error' => 'No se encontraron productos para los envíos seleccionados.'], 404);
        }
        // Ordenar por id_pedido
        $pedidosProductos = $pedidosProductos->sortBy(function ($producto) {
            return $producto->pedido->id;
        });
        // Agrupar por id_pedido
        $pedidosUnicos = $pedidosProductos->groupBy('id_pedido');

        // Crear el PDF
        $pdf = new FPDF('P', 'mm', [216, 330]); // Tamaño oficio
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);

        $margin = 10;
        $pageWidth = 216 - 2 * $margin;
        $pageHeight = 330 - 2 * $margin;

        $numColumns = 2;
        $numRows = 3;
        $cellWidth = $pageWidth / $numColumns;
        $cellHeight = $pageHeight / $numRows;

        $logoPath = public_path('images/logo_gris-3.png');

        $cellCount = 0;
        $cellsInPage = 0;
        $cellPerPage = $numColumns * $numRows;

        $counter = 1; // Inicializar el contador
        foreach ($pedidosUnicos as $pedidoId => $productosDelPedido) {
            $pedido = $productosDelPedido->first()->pedido;

            if (!$pedido) continue;

            if ($cellsInPage >= $cellPerPage) {
                $pdf->AddPage();
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->SetXY($margin, $margin);
                $cellsInPage = 0;
                $cellCount = 0;
            }

            $x = $margin + ($cellCount % $numColumns) * $cellWidth;
            $y = $margin + floor($cellCount / $numColumns) * $cellHeight;

            // Marco exterior
            $pdf->Rect($x, $y, $cellWidth, $cellHeight);

            // Marca de agua
            $pdf->Image($logoPath, $x, $y, $cellWidth, $cellHeight, 'PNG');

            $pdf->SetXY($x + 5, $y + 5);
            $pdf->SetFont('Arial', 'B', 16);

            $lineHeight = 10;
            //$pdf->Cell(10, $cellHeight, strval($counter++), 1, 0, 'C', true);

            $pdf->MultiCell($cellWidth - 10, $lineHeight, utf8_decode('N°: ' . strtoupper($counter++)), 0, 'M');
            //$pdf->MultiCell($cellWidth - 10, $lineHeight, utf8_decode('N° PEDIDO: ' . strtoupper($pedido->id)), 0, 'M');


            $pdf->SetFont('Arial', 'B', 14);
            $pdf->SetXY($x + 5, $pdf->GetY());
            $pdf->MultiCell($cellWidth - 10, $lineHeight, utf8_decode('N° DE PEDIDO: ' . strtoupper($pedido->id)), 0, 'L');

            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetXY($x + 5, $pdf->GetY());
            $pdf->MultiCell($cellWidth - 10, $lineHeight, utf8_decode('NOMBRE: ' . strtoupper($pedido->nombre ?? 'N/A')), 0, 'L');

            $pdf->SetFont('Arial', 'B', 12);
            $pdf->SetXY($x + 5, $pdf->GetY());
            $pdf->MultiCell($cellWidth - 10, $lineHeight, utf8_decode('CI: ' . strtoupper($pedido->ci ?? 'N/A')), 0, 'L');

            $pdf->SetXY($x + 5, $pdf->GetY());
            $pdf->MultiCell($cellWidth - 10, $lineHeight, utf8_decode('CEL: ' . strtoupper($pedido->celular ?? 'N/A')), 0, 'L');

            $pdf->SetXY($x + 5, $pdf->GetY());
            $pdf->MultiCell($cellWidth - 10, $lineHeight, utf8_decode('DESTINO: ' . strtoupper($pedido->destino ?? 'N/A')), 0, 'L');

            $pdf->SetXY($x + 5, $pdf->GetY());
            $pdf->MultiCell($cellWidth - 10, $lineHeight, utf8_decode('ESTADO: ' . strtoupper($pedido->estado ?? 'N/A')), 0, 'L');

            $cellCount++;
            $cellsInPage++;
        }

        return response($pdf->Output('S', 'fichas_pedidos.pdf'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="fichas_pedidos.pdf"');
    } */

    public function generarPdfFichasSeleccionadas(Request $request)
    {
        $envioIds = $request->input('pedidos', []); // Este contiene ids de envíos

        // Validar si hay envíos seleccionados
        if (empty($envioIds)) {
            return response()->json(['error' => 'No se seleccionaron envíos.'], 400);
        }

        // Crear un array para almacenar todos los productos
        $pedidosProductos = collect(); // colección vacía

        // Recorremos cada id_envio recibido
        foreach ($envioIds as $envioId) {
            // Buscar el envío y cargar el pedido relacionado
            $envio = \App\Models\Envio::with('pedido')->find($envioId);

            // Validar si el envío o el pedido existen
            if ($envio && $envio->pedido) {
                // Obtener los productos del pedido
                $productos = \App\Models\PedidoProducto::with('pedido')
                    ->where('id_pedido', $envio->pedido->id)
                    ->get();

                // Agregarlos a la colección principal
                $pedidosProductos = $pedidosProductos->merge($productos);

                // Si no hay productos, agregamos un objeto "falso" solo con el pedido
                if ($productos->isEmpty()) {
                    // Creamos un objeto stdClass con solo el pedido
                    $fake = new \stdClass();
                    $fake->pedido = $envio->pedido;
                    $fake->id_pedido = $envio->pedido->id;
                    $pedidosProductos->push($fake);
                }
            }
        }

        // Ya no validamos si $pedidosProductos->isEmpty()

        // Ordenar por id_pedido
        $pedidosProductos = $pedidosProductos->sortBy(function ($producto) {
            return $producto->pedido->id;
        });
        // Agrupar por id_pedido
        $pedidosUnicos = $pedidosProductos->groupBy('id_pedido');

        // Crear el PDF
        $pdf = new FPDF('P', 'mm', [216, 330]); // Tamaño oficio
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);

        $margin = 10;
        $pageWidth = 216 - 2 * $margin;
        $pageHeight = 330 - 2 * $margin;

        $numColumns = 2;
        $numRows = 3;
        $cellWidth = $pageWidth / $numColumns;
        $cellHeight = $pageHeight / $numRows;

        $logoPath = public_path('images/logo_gris-3.png');

        $cellCount = 0;
        $cellsInPage = 0;
        $cellPerPage = $numColumns * $numRows;

        $counter = 1; // Inicializar el contador
        foreach ($pedidosUnicos as $pedidoId => $productosDelPedido) {
            $pedido = $productosDelPedido->first()->pedido;

            if (!$pedido) continue;

            if ($cellsInPage >= $cellPerPage) {
                $pdf->AddPage();
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->SetXY($margin, $margin);
                $cellsInPage = 0;
                $cellCount = 0;
            }

            $x = $margin + ($cellCount % $numColumns) * $cellWidth;
            $y = $margin + floor($cellCount / $numColumns) * $cellHeight;

            // Marco exterior
            $pdf->Rect($x, $y, $cellWidth, $cellHeight);

            // Marca de agua
            $pdf->Image($logoPath, $x, $y, $cellWidth, $cellHeight, 'PNG');

            $pdf->SetXY($x + 5, $y + 5);
            $pdf->SetFont('Arial', 'B', 16);

            $lineHeight = 10;

            $pdf->MultiCell($cellWidth - 10, $lineHeight, utf8_decode('N°: ' . strtoupper($counter++)), 0, 'M');

            $pdf->SetFont('Arial', 'B', 14);
            $pdf->SetXY($x + 5, $pdf->GetY());
            $pdf->MultiCell($cellWidth - 10, $lineHeight, utf8_decode('N° DE PEDIDO: ' . strtoupper($pedido->id)), 0, 'L');

            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetXY($x + 5, $pdf->GetY());
            $pdf->MultiCell($cellWidth - 10, $lineHeight, utf8_decode('NOMBRE: ' . strtoupper($pedido->nombre ?? 'N/A')), 0, 'L');

            $pdf->SetFont('Arial', 'B', 12);
            $pdf->SetXY($x + 5, $pdf->GetY());
            $pdf->MultiCell($cellWidth - 10, $lineHeight, utf8_decode('CI: ' . strtoupper($pedido->ci ?? 'N/A')), 0, 'L');

            $pdf->SetXY($x + 5, $pdf->GetY());
            $pdf->MultiCell($cellWidth - 10, $lineHeight, utf8_decode('CEL: ' . strtoupper($pedido->celular ?? 'N/A')), 0, 'L');

            $pdf->SetXY($x + 5, $pdf->GetY());
            $pdf->MultiCell($cellWidth - 10, $lineHeight, utf8_decode('DESTINO: ' . strtoupper($pedido->destino ?? 'N/A')), 0, 'L');

            $pdf->SetXY($x + 5, $pdf->GetY());
            $pdf->MultiCell($cellWidth - 10, $lineHeight, utf8_decode('ESTADO: ' . strtoupper($pedido->estado ?? 'N/A')), 0, 'L');

            $cellCount++;
            $cellsInPage++;
        }

        return response($pdf->Output('S', 'fichas_pedidos.pdf'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="fichas_pedidos.pdf"');
    }





    //dd($request);
    public function nota(Request $request)
    {
        // Obtener los parámetros de la consulta
        $nombreCliente = $request->query('nombre_cliente');
        $costoTotal = $request->query('costo_total');
        $productosJson = $request->query('productos');
        $ci = $request->query('ci'); // Obtener el CI
        $descuento = $request->query('descuento', 0); // Obtener el descuento
        $pagado = $request->query('pagado', 0); // Obtener el monto pagado
        $pagadoqr = $request->query('pagadoqr', 0); // Obtener el monto pagado
        $cambio = $request->query('cambio', 0); // Obtener el cambio
        $tipopago = $request->query('tipo_pago');
        $garantia = $request->query('garantia');
        $id_sucursal = $request->query('id_sucursal'); // Capturar garantía
        $id_user = $request->query('id_user'); // Cambiar query por input


        // Verificar si la sucursal existe
        $sucursal = Sucursale::find($id_sucursal);
        if (!$sucursal) {
            return response()->json(['error' => 'Sucursal no encontrada'], 404);
        }

        // Obtener el vendedor
        $user = User::find($id_user);


        // Verificar si el vendedor existe
        if (!$user) {
            return response()->json(['error' => 'Vendedor no encontrado'], 404);
        }
        // Decodificar el JSON de productos
        $pedidos = json_decode($productosJson, true);

        // Inicializar un array para almacenar los productos detallados
        $productosDetalles = [];

        // Buscar cada producto en la base de datos y agregar los detalles
        foreach ($pedidos as $pedido) {
            $producto = Producto::find($pedido['id']); // Busca el producto por ID

            if ($producto) {
                $productosDetalles[] = [
                    'cantidad' => $pedido['cantidad'],
                    'nombre' => $producto->nombre, // Obtener el nombre del producto
                    'precio' => $pedido['precio'], // Usar el precio editado
                    'total' => $pedido['total'] // Total editable
                ];
            }
        }

        // Crear el PDF
        $pdf = new FPDF('P', 'mm', [80, 200]);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->AddPage();
        $marginTop = 5;

        // Preparar los datos del pedido
        $pedido = [
            'nombre_cliente' => $nombreCliente,
            'nit' => $ci, // Asigna el CI al campo NIT

            'fecha' => date('Y/m/d'),
            'productos' => $productosDetalles, // Usar los detalles de los productos
            'subtotal' => $costoTotal,
            'descuento' => $descuento, // Agregar descuento
            'total' => $costoTotal - $descuento, // Calcular total menos descuento
            'pagado' => $pagado, // Agregar monto pagado
            'pagadoqr' => $pagadoqr, // Agregar monto pagado
            'cambio' => $cambio, // Agregar cambio
            'monto_a_pagar' => $costoTotal, // Mantener monto a pagar como total
            'forma_pago' => $tipopago,
            'garantia' => $garantia,
            'id_sucursal' => $id_sucursal,
            'nombre_sucursal' => $sucursal->nombre, // Agregar nombre de la sucursal
            'id_user' => $id_user,
            'nombre_vendedor' => $user->name
        ];
        if ($tipopago == "Efectivo y QR") {
            $this->datosqye($pdf, $pedido, $marginTop);
        } else {
            $this->datos($pdf, $pedido, $marginTop);
        }
        // Llamar a la función para agregar los datos al PDF

        $pdf->Output('I', 'pedidos.pdf'); // 'I' para visualizar en el navegador
    }


    public function datosqye($pdf, $pedido, $marginTop)
    {
        $pdf->SetY($marginTop);

        // Logo centrado
        $pdf->Image('images/logo.png', 30, 5, 20, 20, 'PNG'); // Ajusta la ruta y tamaño del logo
        $pdf->Ln(17); // Espacio debajo del logo

        // Cabecera
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(0, 4, utf8_decode("IMPORTADORA MIRANDA S.A."), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 4, utf8_decode("A un Click del Producto que Necesita!!"), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 4, utf8_decode("Telefono: 70621016"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("Direccion: Caparazon Mall Center, Planta Baja, Local Nro29"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode($pedido['nombre_sucursal']), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("Fecha: " . date('Y/m/d H:i:s')), 0, 1, 'C');


        $pdf->Cell(0, 4, utf8_decode("Codigo de Venta:IMP" . date('Y/m/d')), 0, 1, 'C');

        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea horizontal
        $pdf->Ln(2);


        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 4, utf8_decode("Forma de Pago: " . $pedido['forma_pago']), 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 8);
        // Información de la factura
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(0, 4, utf8_decode("COMPRA DE PRODUCTO"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode(strtoupper($pedido['garantia'])), 0, 1, 'C');

        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea horizontal
        $pdf->Ln(2);

        $pdf->Cell(0, 4, utf8_decode("Cliente: " . $pedido['nombre_cliente']), 0, 1, 'L');
        $pdf->Cell(0, 4, utf8_decode("CI / NIT: " . $pedido['nit']), 0, 1, 'L'); // Mostrar el CI aquí
        $pdf->Cell(0, 4, utf8_decode("Fecha: " . $pedido['fecha']), 0, 1, 'L');
        $pdf->Cell(0, 4, utf8_decode("Vendedor: " . $pedido['nombre_vendedor']), 0, 1, 'L');

        /*    vendedor quiero que se vea aqui */


        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea horizontal
        $pdf->Ln(2);

        // Detalle de productos
        $pdf->SetFont('Arial', 'B', 8);
        // Cabecera
        $pdf->Cell(10, 6, utf8_decode("Cant."), 1, 0, 'C');
        $pdf->Cell(30, 6, utf8_decode("Desc."), 1, 0, 'C');
        $pdf->Cell(10, 6, utf8_decode("P.Unit"), 1, 0, 'C');
        $pdf->Cell(15, 6, utf8_decode("Subtotal"), 1, 1, 'C');

        // Productos
        $pdf->SetFont('Arial', '', 6);

        if (is_array($pedido['productos'])) {
            foreach ($pedido['productos'] as $producto) {
                $pdf->Cell(10, 4, utf8_decode($producto['cantidad']), 1, 0, 'C');

                // Ajustar el tamaño de la fuente según la longitud del nombre del producto
                $nombre = utf8_decode($producto['nombre'] ?? 'Sin descripción');
                $maxCaracteres = 20; // Número de caracteres antes de reducir el tamaño

                if (strlen($nombre) > $maxCaracteres) {
                    $pdf->SetFont('Arial', '', 5); // Disminuye el tamaño de fuente si el texto es muy largo
                } else {
                    $pdf->SetFont('Arial', '', 7); // Tamaño normal
                }

                $pdf->Cell(30, 4, $nombre, 1, 0, 'L');

                $pdf->SetFont('Arial', '', 6);
                $pdf->Cell(10, 4, utf8_decode($producto['precio']), 1, 0, 'R'); // Precio unitario
                $pdf->Cell(15, 4, utf8_decode($producto['total']), 1, 1, 'R'); // Total del producto
            }
        } else {
            $pdf->Cell(0, 10, 'No hay productos disponibles.', 0, 1, 'C');
        }

        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea horizontal
        $pdf->Ln(2);

        // Subtotal y total
        $pdf->SetFont('Arial', 'B', 8);

        // Calculate the subtotal
        $subtotal = array_sum(array_column($pedido['productos'], 'total')) + $pedido['descuento'];

        $pdf->Cell(0, 4, utf8_decode("PRECIO ORIGINAL: " . number_format($subtotal, 2)), 0, 1, 'R');
        $pdf->Cell(0, 4, utf8_decode("DESCUENTO: " . number_format($pedido['descuento'], 2)), 0, 1, 'R');
        $pdf->Cell(0, 4, utf8_decode("TOTAL: " . number_format($pedido['subtotal'], 2)), 0, 1, 'R');
        $pdf->Cell(0, 4, utf8_decode("PAGADO EFECTIVO: " . number_format($pedido['pagado'], 2)), 0, 1, 'R');
        $pdf->Cell(0, 4, utf8_decode("PAGADO QR: " . number_format($pedido['pagadoqr'], 2)), 0, 1, 'R');
        $pdf->Cell(0, 4, utf8_decode("CAMBIO: " . number_format($pedido['cambio'], 2)), 0, 1, 'R');
        $pdf->Cell(0, 4, utf8_decode("MONTO A PAGAR: " . number_format($pedido['monto_a_pagar'], 2)), 0, 1, 'R');

        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea horizontal
        $pdf->Ln(2);

        // Subtotal y total
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(0, 4, utf8_decode("NOTA IMPORTANTE"), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(0, 4, utf8_decode("Los productos en PROMOCION NO CUENTAN CON NINGUN"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("TIPO DE GARANTIA, ya que se encuentran en precio de REMATE."), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("Si su producto llegara a contar con algun defecto de FABRICA"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("si quiere cambiarlo debe cancelar el producto al precio normal."), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("y debe traerlo como maximo al dia siguente por la tarde con su"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("NOTA DE VENTA de lo contrario"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("pierde derecho a cualquier RECLAMO"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("GRACIAS POR SU COMPRA :D !!!"), 0, 1, 'C');
    }
    public function datos($pdf, $pedido, $marginTop)
    {
        // Reducir margen superior al mínimo
        $pdf->SetY($marginTop - 2);

        // Logo centrado más arriba y pequeño
        $pdf->Image('images/logo.png', 30, 2, 18, 18, 'PNG');
        $pdf->Ln(15);

        // Cabecera compacta
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(0, 4, utf8_decode("IMPORTADORA MIRANDA S.A."), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 3, utf8_decode("A un Click del Producto que Necesita!!"), 0, 1, 'C');
        $pdf->Cell(0, 3, utf8_decode("Fecha: " . date('Y/m/d H:i:s')), 0, 1, 'C');

        // Línea separadora
        $pdf->Ln(1);
        $pdf->Cell(0, 0, '', 'T');
        $pdf->Ln(1);

        // Forma de pago
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 3, utf8_decode("Forma de Pago: " . $pedido['forma_pago']), 0, 1, 'C');

        // Título
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(0, 4, utf8_decode("COMPRA DE PRODUCTO"), 0, 1, 'C');

        // Línea separadora
        $pdf->Ln(1);
        $pdf->Cell(0, 0, '', 'T');
        $pdf->Ln(1);

        // Información de cliente y vendedor
        $pdf->SetFont('Arial', '', 7);
        $halfWidth = 35;

        $pdf->Cell($halfWidth, 4, utf8_decode("Cliente: " . $pedido['nombre_cliente']), 0, 0, 'L');
        $pdf->Cell($halfWidth, 4, utf8_decode("CI / NIT: " . $pedido['nit']), 0, 1, 'L');

        $pdf->Cell($halfWidth, 4, utf8_decode("Fecha: " . $pedido['fecha']), 0, 0, 'L');
        $pdf->Cell($halfWidth, 4, utf8_decode("Vendedor: " . $pedido['nombre_vendedor']), 0, 1, 'L');

        // Línea separadora
        $pdf->Ln(1);
        $pdf->Cell(0, 0, '', 'T');
        $pdf->Ln(1);

        // Detalle de productos
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(10, 5, utf8_decode("Cant."), 1, 0, 'C');
        $pdf->Cell(30, 5, utf8_decode("Desc."), 1, 0, 'C');
        $pdf->Cell(10, 5, utf8_decode("P.Unit"), 1, 0, 'C');
        $pdf->Cell(15, 5, utf8_decode("Subtotal"), 1, 1, 'C');

        // Productos
        $pdf->SetFont('Arial', '', 6);
        if (is_array($pedido['productos'])) {
            foreach ($pedido['productos'] as $producto) {
                $pdf->Cell(10, 4, utf8_decode($producto['cantidad']), 1, 0, 'C');

                $nombre = utf8_decode($producto['nombre'] ?? 'Sin descripción');
                $maxCaracteres = 20;
                if (strlen($nombre) > $maxCaracteres) {
                    $pdf->SetFont('Arial', '', 5);
                } else {
                    $pdf->SetFont('Arial', '', 6);
                }

                $pdf->Cell(30, 4, $nombre, 1, 0, 'L');
                $pdf->SetFont('Arial', '', 6);
                $pdf->Cell(10, 4, utf8_decode($producto['precio']), 1, 0, 'R');
                $pdf->Cell(15, 4, utf8_decode($producto['total']), 1, 1, 'R');
            }
        } else {
            $pdf->Cell(0, 6, 'No hay productos disponibles.', 0, 1, 'C');
        }

        // Línea separadora
        $pdf->Ln(1);
        $pdf->Cell(0, 0, '', 'T');
        $pdf->Ln(1);

        // Totales compactos
        $pdf->SetFont('Arial', 'B', 7);
        $subtotal = array_sum(array_column($pedido['productos'] ?? [], 'total'));
        $precio_original = $subtotal + ($pedido['descuento'] ?? 0);

        $pdf->Cell(0, 3, utf8_decode("PRECIO ORIGINAL: " . number_format($precio_original, 2)), 0, 1, 'R');
        $pdf->Cell(0, 3, utf8_decode("DESCUENTO: " . number_format($pedido['descuento'], 2)), 0, 1, 'R');
        $pdf->Cell(0, 3, utf8_decode("TOTAL: " . number_format($pedido['subtotal'], 2)), 0, 1, 'R');
        $pdf->Cell(0, 3, utf8_decode("PAGADO: " . number_format($pedido['pagado'], 2)), 0, 1, 'R');
        $pdf->Cell(0, 3, utf8_decode("CAMBIO: " . number_format($pedido['cambio'], 2)), 0, 1, 'R');
        $pdf->Cell(0, 3, utf8_decode("MONTO A PAGAR: " . number_format($pedido['monto_a_pagar'], 2)), 0, 1, 'R');

        // Línea separadora
        $pdf->Ln(1);
        $pdf->Cell(0, 0, '', 'T');
        $pdf->Ln(1);

        // Mensaje final compacto
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(0, 4, utf8_decode("¡GRACIAS POR SU COMPRA!"), 0, 1, 'C');
        $pdf->Ln(1);
    }
}
