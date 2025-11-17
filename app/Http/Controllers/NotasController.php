<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\PedidoProducto;
use App\Models\Producto;
use FPDF;
use Illuminate\Http\Request;

class NotasController extends Controller
{
    public function nota(Request $request, $pedidoId)
    {
        // Obtener el pedido desde la base de datos
        $pedido = Pedido::findOrFail($pedidoId);

        // Decodificar los productos almacenados en JSON en el campo 'productos'
        //$productosDetalles = json_decode($pedido->productos, true);

        // Preparar los datos del pedido para la vista PDF
        $pedidoData = [
            'nombre_cliente' => $pedido->nombre,
            'ci' => $pedido->ci,
            'celular' => $pedido->celular,
            'fecha' => $pedido->fecha,
            'cantidad' => $pedido->cantidad_productos,
            'productos' => $pedido->productos,
            'detalle' => $pedido->detalle,
            'subtotal' => $pedido->monto_deposito, // Suponiendo que 'monto_deposito' es el subtotal
            'descuento' => 0, // Asignar un valor adecuado si tienes un campo de descuento
            'total' => $pedido->monto_enviado_pagado, // Total pagado
            'pagado' => $pedido->monto_enviado_pagado,
            'qr' => $pedido->transferencia_qr, // Total pagado
            'efectivo' => $pedido->efectivo,
            'cambio' => 0, // Agregar lógica si es necesario
            'forma_pago' => ($pedido->efectivo > 0 && $pedido->transferencia_qr > 0) ? 'Efectivo y QR' : ($pedido->efectivo > 0 ? 'Efectivo' : ($pedido->transferencia_qr > 0 ? 'QR' : 'QR')),
            'garantia' => $pedido->garantia, // Obtiene el valor de garantia
        ];

        // Crear el PDF
        $pdf = new FPDF('P', 'mm', [80, 180]);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->AddPage();
        $marginTop = 5;

        // Llamar a la función para agregar los datos al PDF
        $this->datos($pdf, $pedidoData, $marginTop);
        $pdf->Output('I', 'nota_de_venta.pdf'); // Mostrar el PDF en el navegador
    }

    public function datos($pdf, $pedido, $marginTop)
    {
        $pdf->SetY($marginTop);

        // Logo centrado
        $pdf->Image('images/logo.png', 30, 5, 20, 20, 'PNG');
        $pdf->Ln(15);

        // Cabecera
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(0, 4, utf8_decode("IMPORTADORA MIRANDA S.A."), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 4, utf8_decode("A un Click del Producto que Necesita!!"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("Telefono: 70621016"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("Direccion: Caparazon Mall Center, Planta Baja, Local Nro29"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("Sucursal: Sucursal 1 - CAPARAZON MALL CENTER"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("Fecha: " . date('Y-m-d H:i:s')), 0, 1, 'C');

        $pdf->Cell(0, 4, utf8_decode("Codigo de Venta:IMP" . date('Y/m/d')), 0, 1, 'C');
        // Agregar mensaje "CON GARANTÍA" o "SIN GARANTÍA"
        $garantia = $pedido['garantia'] == 'con garantia' ? 'CON GARANTIA' : 'SIN GARANTIA';
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(0, 4, utf8_decode($garantia), 0, 1, 'C');
        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T');
        $pdf->Ln(2);

        // Información de la factura
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(0, 4, utf8_decode("NOTA DE ENTREGA"), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 8);

        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T');
        $pdf->Ln(2);

        $pdf->Cell(0, 4, utf8_decode("Cliente: " . $pedido['nombre_cliente']), 0, 1, 'L');
        $pdf->Cell(0, 4, utf8_decode("CI / NIT: " . $pedido['ci']), 0, 1, 'L');
        $pdf->Cell(0, 4, utf8_decode("Fecha: " . date('Y-m-d H:i:s')), 0, 1, 'L');
        $pdf->Cell(0, 4, utf8_decode("Forma de pago: " . $pedido['forma_pago']), 0, 1, 'L');


        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T');
        $pdf->Ln(2);

        // Detalle de productos
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(32.5, 4, utf8_decode("Cantidad"), 1, 0, 'C');
        $pdf->Cell(32.5, 4, utf8_decode("SubTotal"), 1, 1, 'C');
        $pdf->SetFont('Arial', '', 7);

        $pdf->Cell(32.5, 4, utf8_decode($pedido['cantidad']), 1, 0, 'C');
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(32.5, 4, utf8_decode($pedido['subtotal']), 1, 1, 'C');
        $pdf->SetFont('Arial', 'B', 8);

        $pdf->Cell(65, 4, utf8_decode("Productos"), 1, 1, 'C');
        $pdf->SetFont('Arial', '', 4);
        $pdf->MultiCell(65, 3, utf8_decode($pedido['productos']), 1, 'C');
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(65, 4, utf8_decode("Descripcion"), 1, 1, 'C');
        $pdf->SetFont('Arial', '', 4);
        $pdf->MultiCell(65, 3, utf8_decode($pedido['detalle']), 1, 'C');
        // Subtotal y total
        if($pedido['forma_pago']=='Efectivo y QR'){

            $pdf->Ln(2);
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->MultiCell(0, 4, utf8_decode("Total Efectivo: " . $pedido['efectivo']), 0, 'R');
            $pdf->MultiCell(0, 4, utf8_decode("Total Qr: " . $pedido['qr']), 0, 'R');
            $pdf->MultiCell(0, 4, utf8_decode("Sub. Total: " . $pedido['subtotal']), 0, 'R');
    
        }else{
            if($pedido['forma_pago']=='Efectivo'){

                $pdf->Ln(2);
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->MultiCell(0, 4, utf8_decode("Total Efectivo: " . $pedido['subtotal']), 0, 'R');
                $pdf->MultiCell(0, 4, utf8_decode("Sub. Total: " . $pedido['subtotal']), 0, 'R');
        
            }else{
                $pdf->Ln(2);
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->MultiCell(0, 4, utf8_decode("Total Qr: " . $pedido['subtotal']), 0, 'R');
                $pdf->MultiCell(0, 4, utf8_decode("Sub. Total: " . $pedido['subtotal']), 0, 'R');
            }
        }
        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T');
        $pdf->Ln(5);

        // Mensaje de agradecimiento
        $pdf->SetFont('Arial', '', 5);
        $pdf->MultiCell(0, 4, utf8_decode("La empresa no se hace responsable de daños ocasionados "), 0, 'C');
        $pdf->MultiCell(0, 4, utf8_decode("por un mal uso de los productos adquiridos."), 0, 'C');
        $pdf->MultiCell(0, 4, utf8_decode("Por favor, revise sus productos antes de salir."), 0, 'C');
        $pdf->MultiCell(0, 4, utf8_decode("No se permiten cambios ni devoluciones después de la compra."), 0, 'C');
        $pdf->MultiCell(0, 4, utf8_decode("Agradecemos su confianza. Si tiene alguna inquietud, estamos aquí para ayudarle."), 0, 'C');
        $pdf->MultiCell(0, 4, utf8_decode("Guarde este documento como comprobante para cualquier gestión futura."), 0, 'C');
        $pdf->MultiCell(0, 4, utf8_decode("Agradecemos su confianza."), 0, 'C');
        $pdf->MultiCell(0, 4, utf8_decode("GRACIAS POR SU COMPRA!!!"), 0, 'C');
    }


    public function imprimirSeleccionados(Request $request)
    {
        // Obtener los IDs de los pedidos seleccionados
        $pedidoIds = explode(',', $request->input('pedidos')); // Obtener los IDs de pedidos desde la URL
        $idSemana = $request->input('semana'); // Obtener el ID de la semana

        // Verificar si se proporcionaron IDs de pedidos
        if (empty($pedidoIds)) {
            return response()->json(['message' => 'No se seleccionaron pedidos.'], 400);
        }

        // Obtener todos los pedidos seleccionados desde la base de datos
        $pedidos = Pedido::whereIn('id', $pedidoIds)->get();

        // Crear el PDF
        $pdf = new FPDF('P', 'mm', [80, 180]);
        $pdf->SetAutoPageBreak(true, 10);

        foreach ($pedidos as $pedido) {
            $pdf->AddPage();
            $marginTop = 5;
            // Crear un array de productos con nombres en lugar de IDs
            // Intentar decodificar los productos
            $productos = json_decode($pedido->productos, true);

            // Verificar si la decodificación fue exitosa y si es un array
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($productos)) {
                // Si la decodificación falla o no es un array válido, usamos el valor original
                $productosString = strtoupper($pedido->productos);
            } else {
                // Si es un array, procesamos los productos
                $productosNombres = [];
                foreach ($productos as $producto) {
                    // Verificar si 'id_producto' está presente y es numérico
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
            }
            // Preparar los datos del pedido para la vista PDF
            $pedidoData = [
                'nombre_cliente' => $pedido->nombre,
                'ci' => $pedido->ci,
                'celular' => $pedido->celular,
                'fecha' => $pedido->fecha,
                'cantidad' => $pedido->cantidad_productos,
               'productos' => $productosString, // Mostrar los nombres de los productos
             'detalle' => $pedido->detalle,
                'subtotal' => $pedido->monto_deposito,
                'descuento' => 0,
                'total' => $pedido->monto_enviado_pagado,
                'pagado' => $pedido->monto_enviado_pagado,
                'cambio' => 0,
                'forma_pago' => 'Pago con tarjeta',
            ];

            // Llamar a la función para agregar los datos del pedido al PDF
            $this->datos($pdf, $pedidoData, $marginTop);
        }

        // Mostrar el PDF en el navegador con un nombre de archivo personalizado
        $pdf->Output('I', 'nota_de_venta_seleccionados.pdf');
    }
    
    public function generarNotaVenta(Request $request)
    {
        // Obtener los IDs de los pedidos seleccionados
        $pedidoIds = $request->input('pedidos', []);
    
        // Validar si se seleccionaron pedidos
        if (empty($pedidoIds)) {
            return response()->json(['error' => 'No se seleccionaron pedidos.'], 400);
        }
    
        // Obtener los registros de PedidoProducto con las relaciones necesarias
        $pedidosProductos = PedidoProducto::with(['pedido', 'producto'])
            ->whereIn('id_envio', $pedidoIds)
            ->get()
            ->groupBy('id_envio'); // Agrupar por pedido/envío
    
        // Crear el PDF (formato tipo ticket)
        $pdf = new FPDF('P', 'mm', [80, 180]);
        $pdf->SetAutoPageBreak(true, 10);
    
        foreach ($pedidosProductos as $pedidoId => $items) {
            $pedido = $items->first()->pedido;
    
            if (!$pedido) continue;
    
            $pdf->AddPage();
            $marginTop = 5;
    
            // Armar el string de productos igual que en imprimirSeleccionados()
            $productosNombres = [];
    
            foreach ($items as $item) {
                if ($item->producto) {
                    $productosNombres[] = $item->producto->nombre;
                } else {
                    $productosNombres[] = strtoupper($item->producto_id ?? 'N/A');
                }
            }
    
            $productosString = implode(', ', $productosNombres);
    
            // Calcular subtotal y total basado en los productos
            $subtotal = $items->sum(function ($item) {
                return $item->cantidad * $item->precio;
            });
    
            // Preparar los datos como en imprimirSeleccionados()
            $pedidoData = [
                'nombre_cliente' => $pedido->nombre,
                'ci'             => $pedido->ci,
                'celular'        => $pedido->celular,
                'fecha'          => $pedido->fecha,
                'cantidad'       => $items->sum('cantidad'),
                'productos'      => $productosString,
                'detalle'        => $pedido->detalle,
                'subtotal'       => $subtotal,
                'descuento'      => 0,
                'total'          => $subtotal,
                'pagado'         => $subtotal,
                'cambio'         => 0,
                'forma_pago'     => 'Pago con tarjeta',
            ];
    
            // Llamar a la misma función para imprimir el estilo tipo ticket
            $this->datos($pdf, $pedidoData, $marginTop);
        }
    
        // Mostrar el PDF
        return response($pdf->Output('S', 'nota_venta_tickets.pdf'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="nota_venta_tickets.pdf"');
    }
}
