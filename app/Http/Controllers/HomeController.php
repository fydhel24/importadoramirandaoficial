<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Semana;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Http\Request;
use FPDF;
use Barryvdh\DomPDF\Facade as PDF;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Obtener la fecha actual (mes y año)
        $currentYear = now()->year;
        $currentMonth = now()->month;

        // Obtener los usuarios con los roles 'Vendedor' y 'Vendedor Antiguo'
        $roles = ['Vendedor', 'Vendedor Antiguo'];
        $vendedores = User::role($roles)->get(); // Obtener todos los usuarios con los roles especificados

        // Obtener las ventas realizadas solo por los usuarios con los roles 'Vendedor' o 'Vendedor Antiguo' para el mes y año actuales
        $ventasPorUsuario = Venta::whereIn('id_user', $vendedores->pluck('id'))
            ->whereYear('created_at', $currentYear)  // Filtrar por el año actual
            ->whereMonth('created_at', $currentMonth) // Filtrar por el mes actual
            ->selectRaw('id_user, COUNT(*) as total_ventas')
            ->groupBy('id_user') // Agrupar solo por usuario
            ->with('user')  // Relacionar con el modelo User para obtener el nombre del usuario
            ->get();

        // Preparar los datos para el gráfico
        $labels = $ventasPorUsuario->map(function ($venta) {
            return $venta->user->name;  // Nombre de cada usuario
        })->toArray();

        $data = $ventasPorUsuario->map(function ($venta) {
            return $venta->total_ventas;  // Total de ventas por usuario en el mes actual
        })->toArray();

        // Pasar los datos a la vista
        return view('home', compact('ventasPorUsuario', 'labels', 'data', 'currentYear', 'currentMonth'));
    }
}
