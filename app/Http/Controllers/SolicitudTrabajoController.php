<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;


use App\Models\SolicitudTrabajo;
use Illuminate\Support\Facades\Storage;

class SolicitudTrabajoController extends Controller
{
    public function index() {
        $solicitudes = SolicitudTrabajo::latest()->get();
        return view('solicitudes.index', compact('solicitudes'));
    }

    public function create() {
        return view('solicitudes.create');
    }

    public function store(Request $request) {
        $request->validate([
            'nombre' => 'required',
            'ci' => 'required|unique:solicitudes_trabajo',
            'celular' => 'required',
            'cv_pdf' => 'nullable|mimes:pdf|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('cv_pdf')) {
            $path = $request->file('cv_pdf')->store('cv_pdfs', 'public');
        }

        SolicitudTrabajo::create([
            'nombre' => $request->nombre,
            'ci' => $request->ci,
            'celular' => $request->celular,
            'cv_pdf' => $path,
        ]);

        return redirect()->route('solicitudes.index')->with('success', 'Solicitud registrada');
    }

    public function edit(SolicitudTrabajo $solicitude) {
        return view('solicitudes.edit', compact('solicitude'));
    }

    public function update(Request $request, SolicitudTrabajo $solicitude) {
        $request->validate([
            'nombre' => 'required',
            'ci' => 'required|unique:solicitudes_trabajo,ci,' . $solicitude->id,
            'celular' => 'required',
            'cv_pdf' => 'nullable|mimes:pdf|max:2048',
        ]);

        $path = $solicitude->cv_pdf;
        if ($request->hasFile('cv_pdf')) {
            if ($path) Storage::disk('public')->delete($path);
            $path = $request->file('cv_pdf')->store('cv_pdfs', 'public');
        }

        $solicitude->update([
            'nombre' => $request->nombre,
            'ci' => $request->ci,
            'celular' => $request->celular,
            'cv_pdf' => $path,
        ]);

        return redirect()->route('solicitudes.index')->with('success', 'Solicitud actualizada');
    }

    public function destroy(SolicitudTrabajo $solicitude) {
        if ($solicitude->cv_pdf) Storage::disk('public')->delete($solicitude->cv_pdf);
        $solicitude->delete();
        return redirect()->route('solicitudes.index')->with('success', 'Solicitud eliminada');
    }

///////////API PARA DATOS//////////////7
// Registrar desde API
public function storeApi(Request $request)
{
    $request->validate([
        'nombre' => 'required|string|max:255',
        'ci' => 'required|string|unique:solicitudes_trabajo,ci',
        'celular' => 'required|string|max:20',
        'cv_pdf' => 'nullable|file|mimes:pdf|max:2048',
    ]);

    $path = null;
    if ($request->hasFile('cv_pdf')) {
        $path = $request->file('cv_pdf')->store('cv_pdfs', 'public');
    }

    $solicitud = SolicitudTrabajo::create([
        'nombre' => $request->nombre,
        'ci' => $request->ci,
        'celular' => $request->celular,
        'cv_pdf' => $path,
    ]);

    return response()->json([
        'message' => 'Solicitud registrada correctamente',
        'data' => $solicitud,
    ], 201);
}


// Listar desde API
public function indexApi()
{
    return response()->json([
        'message' => 'Lista de solicitudes',
        'data' => SolicitudTrabajo::latest()->get()
    ]);
}


}

