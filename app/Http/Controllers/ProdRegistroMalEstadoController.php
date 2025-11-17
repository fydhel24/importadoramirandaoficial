<?php

namespace App\Http\Controllers;

use App\Models\ProdRegistroMalEstado;
use App\Models\Producto;
use Illuminate\Http\Request;

class ProdRegistroMalEstadoController extends Controller
{
    public function index()
    {
        $registros = ProdRegistroMalEstado::with('producto')->paginate(10);
        return view('registros.index', compact('registros'));
    }

    public function create()
    {
        $productos = Producto::all();
        return view('registros.create', compact('productos'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'celular' => 'required|string',
            'persona' => 'required|string',
            'departamento' => 'required|string',
            'producto_id' => 'required|exists:productos,id',
            'descripcion_problema' => 'nullable|string',
        ]);

        $data['estado'] = 'mal'; // Por defecto
        $data['fecha_inscripcion'] = now();

        // Inicializar checkboxes
        foreach (['de_la_paz','enviado','extra1','extra2','extra3','extra4','extra5'] as $checkbox) {
            $data[$checkbox] = $request->input($checkbox, 0);
        }

        ProdRegistroMalEstado::create($data);

        return redirect()->route('prodregistromalestado.index')->with('success', 'Registro creado correctamente.');
    }

    public function show(ProdRegistroMalEstado $registro)
    {
        return view('registros.show', compact('registro'));
    }

    public function edit(ProdRegistroMalEstado $registro)
    {
        $productos = Producto::all();
        return view('registros.edit', compact('registro', 'productos'));
    }

    public function update(Request $request, ProdRegistroMalEstado $registro)
    {
        $data = $request->validate([
            'celular' => 'required|string',
            'persona' => 'required|string',
            'departamento' => 'required|string',
            'producto_id' => 'required|exists:productos,id',
            'estado' => 'required|string',
            'descripcion_problema' => 'nullable|string',
        ]);

        if ($registro->estado == 'mal' && $data['estado'] == 'bueno') {
            $data['fecha_cambio_estado'] = now();
        }

        $registro->update($data);

        return redirect()->route('prodregistromalestado.index')->with('success', 'Registro actualizado correctamente.');
    }

    public function destroy(ProdRegistroMalEstado $registro)
    {
        $registro->delete();
        return redirect()->route('prodregistromalestado.index')->with('success', 'Registro eliminado correctamente.');
    }

    // Actualizaciones en tiempo real
    public function toggleCheck(Request $request, $id)
    {
        $registro = ProdRegistroMalEstado::findOrFail($id);
        $field = $request->field;
        $value = $request->value;

        if (in_array($field, ['de_la_paz','enviado','extra1','extra2','extra3','extra4','extra5'])) {
            $registro->$field = $value;
            $registro->save();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Campo inválido']);
    }

    public function updateDescripcion(Request $request, $id)
    {
        $registro = ProdRegistroMalEstado::findOrFail($id);
        $registro->descripcion_problema = $request->descripcion_problema;
        $registro->save();
        return response()->json(['success' => true]);
    }

    public function updateEstado(Request $request, $id)
    {
        $registro = ProdRegistroMalEstado::findOrFail($id);
        if ($registro->estado === 'mal' && $request->estado === 'bueno') {
            $registro->fecha_cambio_estado = now();
        }
        $registro->estado = $request->estado;
        $registro->save();
        return response()->json(['success' => true]);
    }
    public function buscarProductos(Request $request)
{
    $term = $request->get('term', '');
    $productos = Producto::where('nombre', 'like', "%{$term}%")->get();

    return response()->json($productos);
}


}
