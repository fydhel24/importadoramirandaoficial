<?php

namespace App\Http\Controllers;

use App\Models\Nota;
use Carbon\Carbon;
use FPDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotaController extends Controller
{



    public function imprimirNota($id)
    {
        // Buscar la nota por ID
        $nota = Nota::findOrFail($id);

        // Crear el PDF con tamaño personalizado (80mm de ancho, más alto para mejor presentación)
        $ancho_mm = 80;
        $alto_mm = 200;
        $pdf = new FPDF('P', 'mm', [$ancho_mm, $alto_mm]);
        $pdf->AddPage();

        // Agregar el logo centrado
        $logoPath = public_path('images/logo.png');
        if (file_exists($logoPath)) {
            $pdf->Image($logoPath, ($ancho_mm - 30) / 2, 5, 30);
            $pdf->Ln(25);
        }

        // Encabezado de la empresa
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 8, utf8_decode('Nota Calendario'), 0, 1, 'C');

        // Línea separadora
        $pdf->SetDrawColor(0, 0, 0); // Negro
        $pdf->Line(5, $pdf->GetY(), $ancho_mm - 5, $pdf->GetY());
        $pdf->Ln(2);

        // Fecha de impresión
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->Cell(0, 6, 'Impreso el: ' . Carbon::now()->format('d/m/Y H:i'), 0, 1, 'C');
        $pdf->Ln(2);

        // Línea separadora
        $pdf->Line(5, $pdf->GetY(), $ancho_mm - 5, $pdf->GetY());
        $pdf->Ln(3);

        // Título de la nota
        $pdf->SetFont('Arial', 'B', 13);
        $pdf->Cell(0, 8, utf8_decode($nota->titulo), 0, 1, 'C');
        $pdf->Ln(2);

        // Datos principales
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(20, 7, 'Fecha:', 0, 0);
        $pdf->Cell(0, 7, Carbon::parse($nota->fecha)->format('d/m/Y H:i'), 0, 1);

        $pdf->Cell(20, 7, 'Usuario:', 0, 0);
        $pdf->Cell(0, 7, utf8_decode($nota->user->name), 0, 1);
        $pdf->Ln(2);

        // Línea separadora
        $pdf->Line(5, $pdf->GetY(), $ancho_mm - 5, $pdf->GetY());
        $pdf->Ln(3);

        // Contenido de la nota
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 7, 'Contenido:', 0, 1);
        $pdf->SetFont('Arial', '', 11);
        $pdf->MultiCell(0, 6, utf8_decode($nota->nota), 0, 'L');

        // Línea final y mensaje
        $pdf->Ln(5);
        $pdf->Line(5, $pdf->GetY(), $ancho_mm - 5, $pdf->GetY());
        $pdf->Ln(3);

       
        // Generar y retornar el PDF
        $pdfContent = $pdf->Output('', 'S');

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="nota_' . $nota->id . '.pdf"',
        ]);
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


    // Muestra la vista principal con las notas (index)
    public function index()
    {
        // Obtener todas las notas
        $notas = Nota::with('user')->get();

        // Formatear las notas para que FullCalendar las entienda
        $events = $notas->map(function ($nota) {
            return [
                'id' => $nota->id,
                'title' => $nota->titulo,
                'start' => $nota->fecha,
                'description' => $nota->nota,
                'user' => $nota->user->name, // Incluir el nombre del usuario
                'allDay' => true, // Considerar eventos de todo el día
                'color' => $nota->color,
            ];
        });

        // Pasar las notas formateadas a la vista
        return view('notas.index', compact('events'));
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'nota' => 'required|string',
            'fecha' => 'required|date_format:Y-m-d H:i:s', // Asegura que incluye hora
            'color' => 'required|string',
        ]);

        $userName = Auth::user()->name;

        $nota = Nota::create([
            'titulo' => $request->titulo,
            'nota' => $request->nota,
            'fecha' => $request->fecha, // Guardando fecha y hora
            'color' => $request->color,
            'user_id' => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Nota creada exitosamente',
            'titulo' => $nota->titulo,
            'fecha' => $nota->fecha,
            'nota' => $nota->nota,
            'color' => $nota->color,
            'user' => $userName,
        ]);
    }

    public function update(Request $request, $id)
    {
        // Validar los datos recibidos, excluyendo la fecha
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'nota' => 'required|string',
            'color' => 'required|string',
        ]);

        // Buscar la nota por ID
        $nota = Nota::findOrFail($id);

        // Actualizar los campos de la nota
        $nota->titulo = $request->titulo;
        $nota->nota = $request->nota;
        $nota->color = $request->color;
        $nota->save();

        // Devolver una respuesta JSON con un mensaje de éxito
        return response()->json([
            'message' => 'Nota actualizada exitosamente',
            'titulo' => $nota->titulo,
            'nota' => $nota->nota,
            'color' => $nota->color,
        ]);
    }


    public function destroy($id)
    {
        // Buscar la nota por ID
        $nota = Nota::findOrFail($id);

        // Eliminar la nota
        $nota->delete();

        // Responder con un mensaje de éxito
        return response()->json(['message' => 'Nota eliminada exitosamente']);
    }
}
