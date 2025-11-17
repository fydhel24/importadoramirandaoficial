<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Semana;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Redirect;

class PedidoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Obtener todos los pedidos sin paginación
        $pedidos = Pedido::select('id', 'nombre', 'ci', 'celular', 'destino', 'direccion', 'estado', 'cantidad_productos', 'detalle', 'productos', 'monto_deposito', 'monto_enviado_pagado', 'fecha', 'id_semana', 'codigo')->get();
        
        // Puedes obtener las semanas si las necesitas para la vista
        $semanas = Semana::all();
        
        return view('pedido.index', compact('pedidos', 'semanas'));
    }
    

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $semanas = Semana::all();
        return view('pedido.create', compact('semanas'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required',
            'ci' => 'required',
            'celular' => 'required',
            'destino' => 'required',
            'direccion' => 'required',
            'estado' => 'required',
            'cantidad_productos' => 'required',
            'detalle' => 'required',
            'productos' => 'required',
            'monto_deposito' => 'required',
            'monto_enviado_pagado' => 'required',
            'fecha' => 'required',
            'id_semana' => 'required',
            'foto_comprobante' => 'required|image',
            'codigo' => 'required',
        ]);

        $pedido = new Pedido();
        $pedido->nombre = $request->nombre;
        $pedido->ci = $request->ci;
        $pedido->celular = $request->celular;
        $pedido->destino = $request->destino;
        $pedido->direccion = $request->direccion;
        $pedido->estado = $request->estado;
        $pedido->cantidad_productos = $request->cantidad_productos;
        $pedido->detalle = $request->detalle;
        $pedido->productos = $request->productos;
        $pedido->monto_deposito = $request->monto_deposito;
        $pedido->monto_enviado_pagado = $request->monto_enviado_pagado;
        $pedido->fecha = $request->fecha;
        $pedido->id_semana = $request->id_semana;
        $pedido->codigo = $request->codigo;

        if ($request->hasFile('foto_comprobante')) {
            $filePath = $request->file('foto_comprobante')->store('public/fotos_comprobantes');
            $pedido->foto_comprobante = basename($filePath);
        }

        $pedido->save();

        return redirect()->route('pedidos.index')
            ->with('success', 'Pedido creado con éxito.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $pedido = Pedido::find($id);
        $semanas = Semana::all();
        return view('pedido.show', compact('pedido', 'semanas'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $pedido = Pedido::find($id);
        $semanas = Semana::all();
        return view('pedido.edit', compact('pedido', 'semanas'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required',
            'ci' => 'required',
            'celular' => 'required',
            'destino' => 'required',
            'direccion' => 'required',
            'estado' => 'required',
            'cantidad_productos' => 'required',
            'detalle' => 'required',
            'productos' => 'required',
            'monto_deposito' => 'required',
            'monto_enviado_pagado' => 'required',
            'fecha' => 'required',
            'id_semana' => 'required',
            'codigo' => 'required',
        ]);

        $pedido = Pedido::find($id);
        $pedido->nombre = $request->nombre;
        $pedido->ci = $request->ci;
        $pedido->celular = $request->celular;
        $pedido->destino = $request->destino;
        $pedido->direccion = $request->direccion;
        $pedido->estado = $request->estado;
        $pedido->cantidad_productos = $request->cantidad_productos;
        $pedido->detalle = $request->detalle;
        $pedido->productos = $request->productos;
        $pedido->monto_deposito = $request->monto_deposito;
        $pedido->monto_enviado_pagado = $request->monto_enviado_pagado;
        $pedido->fecha = $request->fecha;
        $pedido->id_semana = $request->id_semana;
        $pedido->codigo = $request->codigo;

        if ($request->hasFile('foto_comprobante')) {
            if ($pedido->foto_comprobante) {
                Storage::delete('public/fotos_comprobantes/' . $pedido->foto_comprobante);
            }
            $filePath = $request->file('foto_comprobante')->store('public/fotos_comprobantes');
            $pedido->foto_comprobante = basename($filePath);
        }

        $pedido->save();

        return redirect()->route('pedidos.index')
            ->with('success', 'Pedido actualizado con éxito.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $pedido = Pedido::find($id);
        if ($pedido->foto_comprobante) {
            Storage::delete('public/fotos_comprobantes/' . $pedido->foto_comprobante);
        }
        $pedido->delete();
        return redirect()->route('pedidos.index')
            ->with('success', 'Pedido eliminado con éxito.');
    }
}