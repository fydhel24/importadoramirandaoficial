<?php

namespace App\Http\Controllers;

use App\Models\Cupo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\CupoRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class CupoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Obtener todos los cupos sin filtrado o paginación
        $cupos = Cupo::all();

        // Revisar y actualizar el estado de cada cupo si es necesario
        foreach ($cupos as $cupo) {
            if ($cupo->fecha_fin && now()->greaterThanOrEqualTo($cupo->fecha_fin)) {
                if ($cupo->estado != 'Inactivo') {
                    $cupo->estado = 'Inactivo';
                    $cupo->save();
                }
            } else {
                if ($cupo->estado != 'Activo') {
                    $cupo->estado = 'Activo';
                    $cupo->save();
                }
            }
        }

        // Retornar la vista con todos los cupos
        return view('cupo.index', compact('cupos'));
    }






    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $cupo = new Cupo();

        return view('cupo.create', compact('cupo'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validación de los datos recibidos
        $request->validate([
            'codigo' => 'required|unique:cupos,codigo',
            'porcentaje' => 'required|numeric',
            'estado' => 'required|in:Activo,Inactivo', // Validación para asegurarse que 'estado' sea válido
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
        ]);

        // Crear un nuevo cupo
        $cupo = new Cupo();
        $cupo->codigo = $request->codigo;
        $cupo->porcentaje = $request->porcentaje;
        $cupo->estado = $request->estado;
        $cupo->fecha_inicio = $request->fecha_inicio;
        $cupo->fecha_fin = $request->fecha_fin;
        $cupo->id_user = $request->id_user;
        $cupo->save();

        return redirect()->route('cupos.index')->with('success', 'Cupo creado exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $cupo = Cupo::find($id);

        return view('cupo.show', compact('cupo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $cupo = Cupo::find($id);

        return view('cupo.edit', compact('cupo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validación de los datos recibidos
        $request->validate([
            'codigo' => 'required|unique:cupos,codigo,' . $id,
            'porcentaje' => 'required|numeric',
            'estado' => 'required|in:Activo,Inactivo', // Validación para asegurarse que 'estado' sea válido
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
        ]);

        // Actualizar el cupo
        $cupo = Cupo::findOrFail($id);
        $cupo->codigo = $request->codigo;
        $cupo->porcentaje = $request->porcentaje;
        $cupo->estado = $request->estado;
        $cupo->fecha_inicio = $request->fecha_inicio;
        $cupo->fecha_fin = $request->fecha_fin;
        $cupo->id_user = $request->id_user;
        $cupo->save();

        return redirect()->route('cupos.index')->with('success', 'Cupo actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): RedirectResponse
    {
        Cupo::find($id)->delete();

        return Redirect::route('cupos.index')
            ->with('success', 'Cupo deleted successfully');
    }

    public function checkCodeExistence($codigo)
    {
        $exists = Cupo::where('codigo', $codigo)->exists();
        return response()->json(['exists' => $exists]);
    }

    // Método para la API que devuelve todos los cupos
    public function cupos(Request $request)
    {
        // Obtener todos los registros de Cupo
        $cupos = Cupo::all();

        // Si quieres actualizar el estado de cada cupo (activo/inactivo), puedes hacerlo aquí
        foreach ($cupos as $cupo) {
            if ($cupo->fecha_fin && now()->greaterThanOrEqualTo($cupo->fecha_fin)) {
                if ($cupo->estado != 'Inactivo') {
                    $cupo->estado = 'Inactivo';
                    $cupo->save();
                }
            } else {
                if ($cupo->estado != 'Activo') {
                    $cupo->estado = 'Activo';
                    $cupo->save();
                }
            }
        }

        // Devolver los datos como respuesta JSON
        return response()->json($cupos);
    }
    public function cupones()
    {
        // Obtener todos los registros de Cupo
        $cupos = Cupo::all();

        // Si quieres actualizar el estado de cada cupo (activo/inactivo), puedes hacerlo aquí
        foreach ($cupos as $cupo) {
            if ($cupo->fecha_fin && now()->greaterThanOrEqualTo($cupo->fecha_fin)) {
                if ($cupo->estado != 'Inactivo') {
                    $cupo->estado = 'Inactivo';
                    $cupo->save();
                }
            } else {
                if ($cupo->estado != 'Activo') {
                    $cupo->estado = 'Activo';
                    $cupo->save();
                }
            }
        }

        // Devolver los datos como respuesta JSON
        return response()->json($cupos);
    }
    public function validarCodigo(Request $request)
    {
        // Obtener el código del cupón desde el request
        $codigo = $request->input('codigo');

        // Buscar el cupón por el código
        $cupo = Cupo::where('codigo', $codigo)->first();

        if ($cupo) {
            // Verificar si el cupón está activo
            if ($cupo->estado != 'Activo') {
                return response()->json([
                    'message' => 'El cupón no está activo.',
                ], 400); // 400 Bad Request
            }

            // Obtener la fecha actual
            $fechaActual = Carbon::now();

            // Verificar si la fecha actual está dentro del rango
            if ($fechaActual->between($cupo->fecha_inicio, $cupo->fecha_fin)) {
                return response()->json([
                    'message' => 'Cupo válido y activo',
                    'data' => $cupo
                ], 200); // 200 OK
            } else {
                return response()->json([
                    'message' => 'El cupón no está dentro del rango de fechas válidas.',
                ], 400); // 400 Bad Request
            }
        } else {
            // Si el código no existe
            return response()->json([
                'message' => 'Cupo no encontrado.',
            ], 404); // 404 Not Found
        }
    }
}
