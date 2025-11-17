<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\Sucursale;
use App\Models\User;
use App\Models\Venta;
use Fpdf\Fpdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;


class CajaController extends Controller
{
    public function index(Request $request, $id)
    {
        $sucursal = Sucursale::find($id);
        if ($request->ajax()) {
            $cajas = Caja::with(['user', 'userCierre'])
                ->where('sucursal_id', $id) // Filtrar por sucursal
                ->orderBy('id', 'desc');
            return DataTables::of($cajas)
                ->addColumn('usuario_apertura', fn($caja) => $caja->user->name)
                ->addColumn('usuario_cierre', fn($caja) => $caja->id_user_cierre ? $caja->userCierre->name : 'Sin Cierre')
                ->addColumn('acciones', function ($caja) {
                    return '
                    <a href="' . route('cajas.edit', ['caja' => $caja->id, 'id' => $caja->sucursal_id]) . '" class="btn btn-warning btn-sm">
                        <i class="fas fa-lock"></i>Cerrar Caja
                    </a>
                    <a href="' . route('cajas.editCaja', ['caja' => $caja->id, 'id' => $caja->sucursal_id]) . '" 
   class="btn btn-success btn-sm">
    <i class="fas fa-edit"></i>Editar
</a>

                    <form action="' . route('cajas.destroy', $caja->id) . '" method="POST" style="display:inline;">
                        ' . csrf_field() . method_field('DELETE') . '
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'¿Seguro que deseas eliminar esta caja?\')">
                            <i class="fas fa-trash"></i>Eliminar
                        </button>
                    </form>
                    


        <a href="' . route('cajas.report', $caja->id) . '" class="btn btn-info btn-sm">
            <i class="fas fa-file-pdf"></i>Reporte
        </a>';
                })
                ->rawColumns(['acciones'])
                ->make(true);
        }

        $cajaAbierta = Caja::whereNull('fecha_cierre')
            ->where('sucursal_id', $id) // Filtrar por sucursal
            ->first();
        return view('cajas.index', compact('cajaAbierta', 'id', 'sucursal'));
    }


public function generateIndividualPdf($id)
    {
        // Cargar la caja con los usuarios de apertura y cierre
        $caja = Caja::with(['user', 'userCierre', 'sucursal'])->findOrFail($id);

        // Obtener las fechas de apertura y cierre
        $fechaApertura = Carbon::parse($caja->fecha_apertura);
        $fechaCierre = Carbon::parse($caja->fecha_cierre ?? now());

        // Filtrar las ventas con estado 'NORMAL', dentro del rango de fechas de la caja, y de la misma sucursal
        $ventas = Venta::with('user.sucursales')
            ->where('estado', 'NORMAL') // Solo ventas con estado 'NORMAL'
            ->whereBetween('fecha', [$fechaApertura, $fechaCierre]) // Filtrar por fechas
            ->whereHas('sucursal', function ($query) use ($caja) {
                // Asegurarse de que la venta esté en la misma sucursal que la caja
                $query->where('id', $caja->sucursal_id);
            })
            ->get();

        // Calcular los totales de las ventas
        $totalEfectivo = $ventas->where('tipo_pago', 'Efectivo')->sum('costo_total');
        $totalQr = $ventas->where('tipo_pago', 'QR')->sum('costo_total');
        $montoTotal = $ventas->sum('costo_total');

        // Inicializar el PDF
        $pdf = new Fpdf('P', 'mm', 'A4');
        $pdf->AddPage();

        // Establecer la fuente y los colores
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetTextColor(50, 50, 50);
        $pdf->Cell(0, 8, "Reporte de Caja", 0, 1, 'C');
        $pdf->Ln(5);

        // Agregar logos con tamaño optimizado
        $pdf->Image(public_path('images/logo_old_2.png'), 10, 10, 20);
        $pdf->Image(public_path('images/logo_old_2.png'), 175, 10, 20);

        // Encabezado elegante con título centralizado
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetTextColor(30, 144, 255); // Azul moderno
        $pdf->Cell(0, 8, "Importadora Miranda", 0, 1, 'C');
        $pdf->Ln(10);

        // Información de la caja
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetFillColor(30, 144, 255); // Azul moderno
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 8, "Informacion de la Caja", 1, 1, 'C', true);

        $pdf->SetFont('Arial', '', 10);
        $pdf->SetFillColor(255, 255, 255); // Fondo blanco
        $pdf->SetTextColor(0, 0, 0);

        // Detalles de la caja
        $pdf->Cell(30, 8, "Sucursal: ", 1, 0, 'L');
        $pdf->Cell(0, 8, $caja->sucursal->nombre ?? 'No definida', 1, 1, 'L');
        $pdf->Cell(30, 8, "Fecha Apertura: ", 1, 0, 'L');
        $pdf->Cell(0, 8, $caja->fecha_apertura, 1, 1, 'L');
        $pdf->Cell(30, 8, "Fecha Cierre: ", 1, 0, 'L');
        $pdf->Cell(0, 8, $caja->fecha_cierre ?? 'No disponible', 1, 1, 'L');
        $pdf->Cell(30, 8, "Usuario Apertura: ", 1, 0, 'L');
        $pdf->Cell(0, 8, $caja->user->name ?? 'No disponible', 1, 1, 'L');
        $pdf->Cell(30, 8, "Usuario Cierre: ", 1, 0, 'L');
        $pdf->Cell(0, 8, $caja->userCierre->name ?? 'No disponible', 1, 1, 'L');
        $pdf->Ln(10); // Espacio adicional

        // Totales generales
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetFillColor(30, 144, 255);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 8, "Totales Generales", 1, 1, 'C', true);

        $pdf->SetFont('Arial', '', 9);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);

        // Añadir los montos iniciales (monto_inicial, efectivo_inicial, qr_inicial)
        $pdf->Cell(45, 8, "Monto Inicial:", 1, 0, 'L');
        $pdf->Cell(50, 8, number_format($caja->monto_inicial, 2), 1, 0, 'C');
        $pdf->Cell(45, 8, "Total Monto:", 1, 0, 'L');
        $pdf->Cell(50, 8, number_format($montoTotal, 2), 1, 1, 'C');

        $pdf->Cell(45, 8, "Efectivo Inicial:", 1, 0, 'L');
        $pdf->Cell(50, 8, number_format($caja->efectivo_inicial, 2), 1, 0, 'C');
        $pdf->Cell(45, 8, "Total Efectivo:", 1, 0, 'L');
        $pdf->Cell(50, 8, number_format($totalEfectivo, 2), 1, 1, 'C');

        $pdf->Cell(45, 8, "QR Inicial:", 1, 0, 'L');
        $pdf->Cell(50, 8, number_format($caja->qr_inicial, 2), 1, 0, 'C');
        $pdf->Cell(45, 8, "Total QR:", 1, 0, 'L');
        $pdf->Cell(50, 8, number_format($totalQr, 2), 1, 1, 'C');

        $pdf->Ln(10); // Espacio adicional

        // * Resumen de las ventas *
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetFillColor(30, 144, 255);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 8, "Listado de las Ventas", 1, 1, 'C', true);
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Ln(5); // Espacio adicional

        // Establecer fuente en negrita para el encabezado
        $pdf->SetFont('Arial', 'B', 9);

        // Establecer el color de fondo y de texto para la fila de encabezado
        $pdf->SetFillColor(0, 123, 255); // Color de fondo (puedes usar el color que prefieras)
        $pdf->SetTextColor(255, 255, 255); // Color de texto (blanco para contraste)

        // Imprimir las celdas de la fila de encabezado en negrita
        $pdf->Cell(15, 8, "ID Venta", 1, 0, 'C', true);  // 'true' activa el fondo de la celda
        $pdf->Cell(30, 8, "Fecha", 1, 0, 'C', true);
        $pdf->Cell(25, 8, "Cliente", 1, 0, 'C', true);
        $pdf->Cell(30, 8, "Usuario", 1, 0, 'C', true);
        $pdf->Cell(30, 8, "Tipo de Pago", 1, 0, 'C', true);
        $pdf->Cell(20, 8, "Costo Total", 1, 0, 'C', true);
        $pdf->Cell(20, 8, "Efectivo", 1, 0, 'C', true);
        $pdf->Cell(20, 8, "QR", 1, 1, 'C', true);

        // Restaurar los colores originales después del encabezado
        $pdf->SetTextColor(0, 0, 0); // Restablecer color de texto a negro
        $pdf->SetFillColor(255, 255, 255); // Fondo blanco para las celdas de datos

        // Restaurar la fuente a normal para el resto del contenido
        $pdf->SetFont('Arial', '', 9);

        // Recorrer las ventas
        foreach ($ventas as $venta) {
            $pdf->Cell(15, 8, $venta->id, 1, 0, 'C');
            $pdf->Cell(30, 8, Carbon::parse($venta->fecha)->format('d-m-Y H:i'), 1, 0, 'C');
            $pdf->Cell(25, 8, utf8_decode($venta->nombre_cliente), 1, 0, 'C');
            $pdf->Cell(30, 8, utf8_decode($venta->user->name), 1, 0, 'C');
            $pdf->Cell(30, 8, $venta->tipo_pago, 1, 0, 'C');
            $pdf->Cell(20, 8, number_format($venta->costo_total, 2), 1, 0, 'C');
            $pdf->Cell(20, 8, number_format($venta->efectivo, 2), 1, 0, 'C');
            $pdf->Cell(20, 8, number_format($venta->qr, 2), 1, 1, 'C');
        }

        // Salida del PDF
        return response($pdf->Output('S', "reporte_caja_$id.pdf"))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="reporte_caja_' . $id . '.pdf"');
    }

    public function sucursales()
    {
        // Obtener todas las sucursales
        $sucursales = Sucursale::all();

        // Verificar si ya existe una caja abierta en cada sucursal
        $sucursalesConCajaAbierta = Caja::whereNull('fecha_cierre')->pluck('sucursal_id')->toArray();

        // Pasar esta información a la vista
        return view('cajas.sucursales', compact('sucursales', 'sucursalesConCajaAbierta'));
    }


    public function abrirTodas()
    {
        // Obtener todas las sucursales donde no hay una caja abierta
        $sucursales = Sucursale::all();

        foreach ($sucursales as $sucursal) {
            // Verificar si ya existe una caja abierta en la sucursal
            $cajaAbierta = Caja::whereNull('fecha_cierre')->where('sucursal_id', $sucursal->id)->first();

            if (!$cajaAbierta) {
                // Si no hay una caja abierta, crear una nueva
                Caja::create([
                    'fecha_apertura' => now(),
                    'monto_inicial' => 0,  // Puedes ajustar el monto inicial según tu lógica
                    'id_user' => auth()->id(),  // El usuario que está abriendo la caja
                    'sucursal_id' => $sucursal->id,
                ]);
            }
        }

        return redirect()->route('cajas.sucursales')->with('success', 'Cajas abiertas en todas las sucursales donde no había una abierta.');
    }

    public function create($id)
    {
        // Verificar si ya existe una caja abierta en la misma sucursal
        $cajaAbierta = Caja::whereNull('fecha_cierre')->where('sucursal_id', $id)->first();

        if ($cajaAbierta) {
            return redirect()->route('cajas.index', ['id' => $id])
                ->with('error', 'Ya existe una caja abierta en esta sucursal. No se puede crear una nueva hasta que se cierre la actual.');
        }

        // Filtrar los usuarios con los roles Admin y Vendedor Antiguo, y estado activo
        $users = User::role(['Admin', 'Vendedor Antiguo'])->where('status', 'active')->get();
        $loggedUser = auth()->user();

        return view('cajas.create', compact('users', 'loggedUser', 'id'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'fecha_apertura' => 'required|date',
            'id_user' => 'required|exists:users,id', // Verifica que el id_user existe en la base de datos
            'monto_inicial' => 'required|numeric',
            'sucursal_id' => 'required|exists:sucursales,id', // Verifica que la sucursal existe
        ]);

        // Crear la caja con el ID de la sucursal
        Caja::create([
            'fecha_apertura' => $request->fecha_apertura,
            'monto_inicial' => $request->monto_inicial,
            'id_user' => $request->id_user,
            'sucursal_id' => $request->sucursal_id, // Guardar la sucursal
        ]);

        return redirect()->route('cajas.index', ['id' => $request->sucursal_id])
            ->with('success', 'Caja creada con éxito');
    }
    

    public function editCaja(Caja $caja, $id)
    {
        // Obtener los usuarios con los roles específicos
        $users = User::role(['Admin', 'Vendedor Antiguo'])->get();

        // Convertir la fecha de apertura a Carbon
        $caja->fecha_apertura = \Carbon\Carbon::parse($caja->fecha_apertura);

        // Pasar la sucursal a la vista SIN sobrescribir $id
        return view('cajas.edit-caja', compact('caja', 'users', 'id'));
    }

    public function updateEdit(Request $request, Caja $caja, $id)
    {
        $request->validate([
            'fecha_apertura' => 'required|date',
            'id_user' => 'required|exists:users,id',
            'monto_inicial' => 'required|numeric',
            'efectivo_inicial' => 'required|numeric',
            'qr_inicial' => 'required|numeric',
            'sucursal_id' => 'required|exists:sucursales,id',
        ]);

        // Actualizar solo los valores editables sin cerrar la caja
        $caja->update([
            'fecha_apertura' => $request->fecha_apertura,
            'id_user' => $request->id_user,
            'monto_inicial' => $request->monto_inicial,
            'efectivo_inicial' => $request->efectivo_inicial,
            'qr_inicial' => $request->qr_inicial,
        ]);

        return redirect()->route('cajas.index', ['caja' => $caja->id, 'id' => $id])
            ->with('success', 'Caja actualizada correctamente sin cerrarla.');
    }


    public function edit(Caja $caja)
    {
        // Filtrar los usuarios con los roles Admin y Vendedor Antiguo
        $users = User::role(['Admin', 'Vendedor Antiguo'])->get();

        // Convertir las fechas a Carbon
        $fechaApertura = \Carbon\Carbon::parse($caja->fecha_apertura);
        $fechaCierre = $caja->fecha_cierre ? \Carbon\Carbon::parse($caja->fecha_cierre) : now();

        // Filtrar las ventas con estado 'NORMAL' de la misma sucursal entre la fecha de apertura y cierre
        $ventas = Venta::where('id_sucursal', $caja->sucursal_id)
            ->where('estado', 'NORMAL') // Solo ventas con estado 'NORMAL'
            ->whereBetween('fecha', [$fechaApertura, $fechaCierre])
            ->get();

        // Asignar los valores de fecha de apertura y cierre para la vista
        $caja->fecha_apertura = \Carbon\Carbon::parse($caja->fecha_apertura);
        $caja->fecha_cierre = \Carbon\Carbon::parse($caja->fecha_cierre);

        // Calcular los totales basados solo en las ventas con estado 'NORMAL'
        $montoTotal = $ventas->sum('costo_total');
        $totalEfectivo = $ventas->sum('efectivo');
        $totalQr = $ventas->sum('qr');

        // Si ya hay valores en la caja, mantenerlos
        $montoTotal = $caja->monto_total ?? $montoTotal;
        $totalEfectivo = $caja->total_efectivo ?? $totalEfectivo;
        $totalQr = $caja->total_qr ?? $totalQr;

        // Pasar la sucursal a la vista
        $id = $caja->sucursal_id;

        return view('cajas.edit', compact('caja', 'users', 'montoTotal', 'totalEfectivo', 'totalQr', 'id'));
    }

    public function update(Request $request, Caja $caja)
    {
        $request->validate([
            'fecha_cierre' => 'nullable|date',
            'id_user' => 'required|exists:users,id',
            'id_user_cierre' => 'required|exists:users,id',
            'monto_inicial' => 'required|numeric',
            'efectivo_inicial' => 'required|numeric',
            'qr_inicial' => 'required|numeric',
            'sucursal_id' => 'required|exists:sucursales,id',
        ]);

        // Obtener la fecha de apertura desde la caja (NO modificarla)
        $fechaApertura = \Carbon\Carbon::parse($caja->fecha_apertura);

        // Determinar la fecha de cierre
        $fechaCierre = $request->fecha_cierre ? \Carbon\Carbon::parse($request->fecha_cierre) : ($caja->fecha_cierre ?? now());

        // Obtener todas las ventas de la sucursal dentro del rango de fechas
        $ventas = Venta::where('id_sucursal', $caja->sucursal_id)
            ->whereBetween('fecha', [$fechaApertura, $fechaCierre])
            ->get();

        // Calcular los nuevos totales
        $montoTotal = $ventas->sum('costo_total');
        $totalEfectivo = $ventas->sum('efectivo');
        $totalQr = $ventas->sum('qr');

        // Actualizar solo los valores que deben cambiar (NO modificar fecha_apertura)
        $caja->update([
            'fecha_apertura' => $fechaApertura,
            'fecha_cierre' => $fechaCierre,
            'id_user_cierre' => $request->id_user_cierre,
            'monto_inicial' => $request->monto_inicial, // 🔹 Ahora se guardará
            'efectivo_inicial' => $request->efectivo_inicial, // 🔹 Ahora se guardará
            'qr_inicial' => $request->qr_inicial, // 🔹 Ahora se guardará
            'monto_total' => $montoTotal,
            'total_efectivo' => $totalEfectivo,
            'total_qr' => $totalQr,
        ]);

        return redirect()->route('cajas.index', ['id' => $request->sucursal_id])
            ->with('success', 'Caja cerrada con éxito y montos recalculados correctamente.');
    }




    public function verificarCajaAbierta($sucursalId)
    {
        $cajaAbierta = Caja::whereNull('fecha_cierre')
            ->where('sucursal_id', $sucursalId)  // Filtra por la sucursal
            ->first();

        if (!$cajaAbierta) {
            return response()->json(['error' => 'No se puede procesar la venta, ya que la caja no está abierta en esta Sucursal.'], 400);
        }

        return response()->json(['success' => 'Caja abierta.'], 200);
    }

    public function cerrarTodas()
    {
        // Obtener todas las cajas abiertas
        $cajasAbiertas = Caja::whereNull('fecha_cierre')->get();

        // Obtener el usuario que está realizando el cierre
        $userCierre = auth()->user();
        

        foreach ($cajasAbiertas as $caja) {
            // Filtrar las ventas de la misma sucursal entre la fecha de apertura y cierre
            $ventas = Venta::where('id_sucursal', $caja->sucursal_id)
                ->where('fecha', '>=', $caja->fecha_apertura)
                ->get();

            // Calcular los totales basados en las ventas de la sucursal
            $montoTotal = $ventas->sum('costo_total');
            $totalEfectivo = $ventas->sum('efectivo');
            $totalQr = $ventas->sum('qr');
            $fechaApertura = \Carbon\Carbon::parse($caja->fecha_apertura);

            // Actualizar la caja, asignando el usuario que la cierra
            $caja->update([
                'fecha_apertura'=>$fechaApertura,
                'fecha_cierre' => now(),
                'id_user_cierre' => $userCierre->id,  // Guardar el ID del usuario que cierra la caja
                'monto_total' => $montoTotal,
                'total_efectivo' => $totalEfectivo,
                'total_qr' => $totalQr,
            ]);
        }

        return redirect()->route('cajas.sucursales')
            ->with('success', 'Todas las cajas han sido cerradas correctamente.');
    }
    public function destroy(Caja $caja)
    {
        $id = $caja->sucursal_id; // Suponiendo que 'sucursal_id' es el campo que necesitas
        $caja->delete();

        return redirect()->route('cajas.index', ['id' => $id])->with('success', 'Caja eliminada con éxito');
    }
}
