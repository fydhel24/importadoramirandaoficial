<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proveedor;
use App\Models\PagoProveedor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use FPDF; // Importar la clase FPDF
use PDF;

class InformeController extends Controller
{
    public function index()
    {
        return view('informes.index');
    }
    
    public function pagosDiarios(Request $request)
    {
        $fecha = $request->input('fecha', date('Y-m-d'));
        
        $pagos = PagoProveedor::with('proveedor')
            ->whereDate('fecha_pago', $fecha)
            ->get();
            
        $total = $pagos->sum('monto_pago');
        
        return view('informes.pagos-diarios', compact('pagos', 'total', 'fecha'));
    }
    
    public function pagosMensuales(Request $request)
    {
        $mes = $request->input('mes', date('m'));
        $anio = $request->input('anio', date('Y'));
        
        $pagos = PagoProveedor::with('proveedor')
            ->whereMonth('fecha_pago', $mes)
            ->whereYear('fecha_pago', $anio)
            ->get();
            
        $pagosPorDia = $pagos->groupBy(function($item) {
            return Carbon::parse($item->fecha_pago)->format('d-m-Y');
        });
        
        $totalMes = $pagos->sum('monto_pago');
        
        // Para el gráfico
        $datosDiarios = [];
        foreach ($pagosPorDia as $dia => $pagosDelDia) {
            $datosDiarios[] = [
                'dia' => $dia,
                'total' => $pagosDelDia->sum('monto_pago')
            ];
        }
        
        return view('informes.pagos-mensuales', compact('pagos', 'pagosPorDia', 'totalMes', 'mes', 'anio', 'datosDiarios'));
    }
    
    public function proveedoresPagados()
    {
        $proveedores = Proveedor::where('estado', 'Pagado')->get();
        $totalPagado = $proveedores->sum('deuda_total');
        
        return view('informes.proveedores-pagados', compact('proveedores', 'totalPagado'));
    }
    
    public function proveedoresPendientes()
    {
        $proveedores = Proveedor::where('estado', 'Saldo pendiente')->get();
        $totalPendiente = $proveedores->sum('saldo_pendiente');
        
        return view('informes.proveedores-pendientes', compact('proveedores', 'totalPendiente'));
    }
    






}