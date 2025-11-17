<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\Semana;
use Carbon\Carbon;

class ClienteController extends Controller
{
    public function store(Request $request)
    {
        // Validar la solicitud
        //dd($request->all());
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'ci' => 'required|string|max:255',
            'celular' => 'required|string|max:255',
            'destino' => 'required|string|max:255', // Añadido aquí
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
        $validatedData['id_semana'] = $ultimaSemana ? $ultimaSemana->id : null;
        $validatedData['fecha'] = Carbon::now();

        // Crear el pedido
        Pedido::create($validatedData);

        // Redirigir con mensaje de éxito
        return redirect()->route('cliente.index')->with('success', 'El pedido ha sido creado exitosamente.');
    }
    public function storeFast(Request $request)
    {
        // Validar la solicitud (solo los campos necesarios)
        $validatedData = $request->validate([
            'celular' => 'required|string|max:255',
            'destino' => 'required|string|max:255',
            'monto_deposito' => 'required|numeric',
            'productos' => 'required|string|max:255',
        ]);

        // Definir valores por defecto
        $validatedData['nombre'] = 'Pendiente';
        $validatedData['ci'] = '0';
        $validatedData['direccion'] = 'Pendiente';
        $validatedData['estado'] = 'Pendiente';
        $validatedData['cantidad_productos'] = 0;
        $validatedData['detalle'] = ''; // Asignar un valor por defecto
        $validatedData['monto_enviado_pagado'] = 0;

        // Buscar la semana con ID 14
        $semana = Semana::find(42);
        $validatedData['id_semana'] = $semana ? $semana->id : null;
        $validatedData['fecha'] = Carbon::now();

        // Crear el pedido
        Pedido::create($validatedData);

        // Redirigir con mensaje de éxito
        return redirect()->route('formulario.index')->with('success', 'El pedido rápido ha sido creado exitosamente.');
    }
     public function storenuevo(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ci' => 'required|string|max:255',
            'celular' => 'required|string|max:255',
            'destino' => 'required|string|max:255', // Añadido aquí
            'direccion' => 'required|string|max:255',
            'estado' => 'required|string|max:255',
            'cantidad_productos' => 'required|integer',
            'detalle' => 'nullable|string',
            'productos' => 'nullable|string',
            'monto_deposito' => 'required|numeric',
            'monto_enviado_pagado' => 'required|numeric',
            'foto_comprobante' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        $fotoPath = $request->file('foto_comprobante')
            ? $request->file('foto_comprobante')->store('fotos', 'public')
            : null;

        // Obtener el último ID de semana
        $ultimaSemana = Semana::latest('id')->first();
        $validatedData['id_semana'] = $ultimaSemana ? $ultimaSemana->id : null;
        $validatedData['fecha'] = Carbon::now();

        // Crear el pedido
        // Combinar todos los datos
        $dataToStore = array_merge($request->all(), [
            'foto_comprobante' => $fotoPath,
            'fecha' => $validatedData['fecha'],
            'id_semana' => $validatedData['id_semana'],
        ]);

        // Crear el pedido
    $pedido = Pedido::create($dataToStore);
        // Redirigir con mensaje de éxito
        // Redirigir a la vista con mensaje de éxito
        // Redirigir con mensaje de éxito y el ID del pedido
    return response()->json([
        'success' => true,
        'message' => 'El pedido ha sido creado exitosamente.',
        'orderId' => $pedido->id,
    ]);
    }

    public function getPedidosPorSemana()
    {
        // Obtener todas las semanas con sus pedidos
        $semanas = Semana::with('pedidos')->get();

        // Devolver la respuesta JSON con las semanas y sus pedidos
        return response()->json([
            'success' => true,
            'data' => $semanas,
            'message' => 'Pedidos de todas las semanas obtenidos exitosamente.'
        ], 200);
    }
}
