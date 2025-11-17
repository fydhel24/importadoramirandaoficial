<?php

namespace App\Http\Controllers;

use App\Models\Sucursale;
use App\Http\Requests\SucursaleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SucursaleController extends Controller
{
    public function __construct()
    {
        // Protege las rutas con middleware de permisos
        $this->middleware('can:sucursales.index')->only('index');
        $this->middleware('can:sucursales.create')->only('create', 'store');
        $this->middleware('can:sucursales.edit')->only('edit', 'update');
        $this->middleware('can:sucursales.destroy')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        // Listar todas las sucursales con paginación
        $sucursales = Sucursale::paginate();

        return view('sucursale.index', compact('sucursales'))
            ->with('i', ($request->input('page', 1) - 1) * $sucursales->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $sucursale = new Sucursale(); // Nueva sucursal para crear

        return view('sucursale.create', compact('sucursale'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SucursaleRequest $request): RedirectResponse
    {
        // Validar los datos
        $data = $request->validated();

        // Manejar la carga del logo (si se sube una imagen)
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            $data['logo'] = $logoPath;
        }

        // Crear la sucursal con los datos validados
        Sucursale::create($data);

        return Redirect::route('sucursales.index')
            ->with('success', 'Sucursal creada con éxito.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $sucursale = Sucursale::findOrFail($id);

        return view('sucursale.show', compact('sucursale'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $sucursale = Sucursale::findOrFail($id);

        return view('sucursale.edit', compact('sucursale'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SucursaleRequest $request, Sucursale $sucursale): RedirectResponse
    {
        // Validar los datos
        $data = $request->validated();

        // Manejar la carga del logo (si se sube una nueva imagen)
        if ($request->hasFile('logo')) {
            // Eliminar la imagen anterior si existe
            if ($sucursale->logo) {
                Storage::delete('public/' . $sucursale->logo);
            }

            // Almacenar la nueva imagen
            $logoPath = $request->file('logo')->store('logos', 'public');
            $data['logo'] = $logoPath;
        }

        // Actualizar la sucursal con los nuevos datos
        $sucursale->update([
            'nombre'    => $data['nombre'],
            'direccion' => $data['direccion'],
            'celular'   => $data['celular'],
            'estado'    => $data['estado'],
            'logo'      => $data['logo'] ?? $sucursale->logo, // Solo cambiar el logo si hay uno nuevo
        ]);

        return Redirect::route('sucursales.index')
            ->with('success', 'Sucursal actualizada con éxito');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): RedirectResponse
    {
        $sucursale = Sucursale::findOrFail($id);

        // Eliminar el logo si existe
        if ($sucursale->logo) {
            Storage::delete('public/' . $sucursale->logo);
        }

        // Eliminar la sucursal
        $sucursale->delete();

        return Redirect::route('sucursales.index')
            ->with('success', 'Sucursal eliminada con éxito');
    }
}
