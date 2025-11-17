<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\Semana;
use Carbon\Carbon; // Para manejar fechas

class ClienteController extends Controller
{
    public function store(Request $request)
    {
        // Validar la solicitud (ajusta las reglas según tus necesidades)
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'ci' => 'required|string|max:255',
            'celular' => 'required|string|max:255',
            'destino' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
            'estado' => 'required|string|max:255',
            'cantidad_productos' => 'required|integer',
            'detalle' => 'nullable|string',
            'productos' => 'nullable|string',
            'monto_deposito' => 'required|numeric',
            'monto_enviado_pagado' => 'required|numeric',
        ]);

        // Obtener el último ID de semana
        $ultimaSemana = Semana::latest('id')->first();

        // Añadir el ID de semana y la fecha actual
        $validatedData['id_semana'] = $ultimaSemana ? $ultimaSemana->id : null;
        $validatedData['fecha'] = Carbon::now();

        // Crear el pedido
        Pedido::create($validatedData);

        // Redirigir con mensaje de éxito
        return redirect()->route('aa.index')->with('success', 'El pedido ha sido creado exitosamente.');
    }
}
