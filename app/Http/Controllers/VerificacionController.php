<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use Illuminate\Http\Request;

class VerificacionController extends Controller
{
    // 1. Solo muestra la vista
    public function index()
    {
        return view('verificacion.index');
    }

    // 2. Devuelve las últimas 20 ventas con sus productos (cargadas eager)
    public function validar()
    {
    $ventas = Venta::with(['ventaProductos.producto', 'user']) // <-- añadido 'user'
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get()
            ->map(function ($venta) {
                $venta->cambio = $venta->pagado - ($venta->efectivo + $venta->qr);
                return $venta;
            });

        return response()->json($ventas);
    }
}
