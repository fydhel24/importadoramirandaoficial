<?php

namespace App\Http\Controllers;

use App\Models\Carpeta;
use App\Models\Sucursale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Añadir para usar expresiones de base de datos
use Carbon\Carbon; // Añadir para manejo de fechas
class CarpetaController extends Controller
{
public function index(Request $request)
{
    $rol = strtolower(auth()->user()->getRoleNames()->first());
    $sucursales = Sucursale::all();
    $sucursalUsuario = auth()->user()->sucursales->first();

    // Mostrar solo las tarjetas si es admin y no eligió sucursal aún
    if ($rol === 'admin' && !$request->filled('sucursal_id')) {
        return view('carpetas.admin_tarjetas', compact('sucursales'));
    }

    $query = Carpeta::query();

    if ($rol === 'admin' && $request->filled('sucursal_id')) {
        $query->where('sucursal_id', $request->sucursal_id);
    } elseif ($rol !== 'admin') {
        $idsSucursales = auth()->user()->sucursales->pluck('id');
        $query->whereIn('sucursal_id', $idsSucursales);
    }

    if ($request->filled('search')) {
        $query->where('descripcion', 'like', '%' . $request->search . '%');
    }

    $carpetas = $query->orderBy('id', 'desc')->paginate(12);

    return view('carpetas.index', compact('carpetas', 'sucursales', 'rol', 'sucursalUsuario'));
}

public function create()
{
    $user = auth()->user();
    $rol = $user->getRoleNames()->first();
    $sucursales = $rol === 'Admin' ? \App\Models\Sucursale::all() : [];
    $sucursalUsuario = $user->sucursal_id;

    return view('carpetas.create', compact('sucursales', 'rol', 'sucursalUsuario'));
}
public function store(Request $request)
{
    $rol = strtolower(auth()->user()->getRoleNames()->first());
    $redirectSucursalId = null;

    if ($rol === 'admin') {
        $validated = $request->validate([
            'sucursal_id' => 'required|exists:sucursales,id',
            'descripcion' => 'required|string|max:255',
            'fecha' => 'required|date',
        ]);
        $redirectSucursalId = $validated['sucursal_id'];
    } else {
        $sucursalUsuario = auth()->user()->sucursales->first();
        if (!$sucursalUsuario) {
            return back()->with('error', 'No tiene sucursal asignada');
        }

        $validated = $request->validate([
            'descripcion' => 'required|string|max:255',
            'fecha' => 'required|date',
        ]);

        $validated['sucursal_id'] = $sucursalUsuario->id;
        $redirectSucursalId = $sucursalUsuario->id;
    }

    Carpeta::create($validated);

    return redirect()->route('carpetas.index', ['sucursal_id' => $redirectSucursalId])
        ->with('success', 'Carpeta creada correctamente.');
}

    /* public function show(Carpeta $carpeta, Request $request)
    {
        $capturasQuery = $carpeta->capturas();

        // Búsqueda por campo_texto dentro de las capturas (nombre y monto)
        if ($request->has('search_captura') && $request->search_captura != '') {
            $searchTerm = $request->search_captura;

            $capturasQuery->where(function ($query) use ($searchTerm) {
                // Intenta buscar en el JSON
                $query->whereRaw("JSON_EXTRACT(campo_texto, '$[*].nombre') LIKE ?", ['%"%' . $searchTerm . '%"%'])
                    ->orWhereRaw("JSON_EXTRACT(campo_texto, '$[*].monto') LIKE ?", ['%"%' . $searchTerm . '%"%'])
                    // Si no es JSON o no hay coincidencia en el JSON, busca en el texto plano
                    ->orWhere('campo_texto', 'like', '%' . $searchTerm . '%');
            });
        }

        // Filtro por fecha (rango) para las capturas, basado en created_at del registro de captura
        if ($request->has('start_date') && $request->start_date != '') {
            $capturasQuery->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date != '') {
            $capturasQuery->whereDate('created_at', '<=', $request->end_date);
        }

        // Ordenar las capturas
        $capturas = $capturasQuery->orderBy('created_at', 'asc')->get();

        $currentCaptura = null;
        $currentIndex = -1;

        // Determinar la captura actual para mostrar en el carrusel
        // Prioriza la captura de la URL, luego la primera si existe y no hay búsqueda,
        // o la primera que coincida con la búsqueda.
        $requestedCapturaId = $request->query('captura');
        if ($requestedCapturaId) {
            $currentCaptura = $capturas->firstWhere('id', $requestedCapturaId);
        }

        if (!$currentCaptura && $capturas->isNotEmpty()) {
            $currentCaptura = $capturas->first();
        }

        if ($currentCaptura) {
            $currentIndex = $capturas->search(function ($item) use ($currentCaptura) {
                return $item->id === $currentCaptura->id;
            });
        }

        return view('carpetas.show', compact(
            'carpeta',
            'capturas',
            'currentCaptura',
            'currentIndex'
        ));
    }
*/
   public function edit(Carpeta $carpeta)
{
    $sucursales = auth()->user()->hasRole('admin')
        ? \App\Models\Sucursale::all()
        : [];

    return view('carpetas.edit', compact('carpeta', 'sucursales'));
}


    public function update(Request $request, Carpeta $carpeta)
{
    $request->validate([
        'descripcion' => 'required|string|max:255',
        'fecha' => 'required|date',
        'sucursal_id' => 'nullable|exists:sucursales,id',
    ]);

    $data = $request->all();

    if (!auth()->user()->hasRole('admin')) {
        unset($data['sucursal_id']); // evitar que un no admin cambie la sucursal
    }

    $carpeta->update($data);

    return redirect()->route('carpetas.index')
        ->with('success', 'Carpeta actualizada exitosamente.');
}

    public function destroy(Carpeta $carpeta)
    {
        // Eliminar todas las capturas asociadas
        foreach ($carpeta->capturas as $captura) {
            // Eliminar el archivo físico si existe
            if (file_exists(public_path('storage/' . $captura->foto_original))) {
                unlink(public_path('storage/' . $captura->foto_original));
            }
            $captura->delete();
        }

        $carpeta->delete();

        return redirect()->route('carpetas.index')
            ->with('success', 'Carpeta eliminada exitosamente.');
    }

    public function search(Request $request)
    {
        $search = $request->input('search');

        $carpetas = Carpeta::when($search, function ($query) use ($search) {
            return $query->where('descripcion', 'like', "%{$search}%");
        })->paginate(12);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('carpetas._list', compact('carpetas'))->render()
            ]);
        }

        return view('carpetas._list', compact('carpetas'));
    }
    public function show(Carpeta $carpeta, Request $request)
    {
        // La lógica de show ya no filtra activamente, solo prepara la vista inicial.
        // El filtrado en tiempo real se hará con AJAX en searchRealtime.
        $capturas = $carpeta->capturas()->orderBy('created_at', 'asc')->get();

        $currentCaptura = null;
        $currentIndex = -1;

        if ($capturas->isNotEmpty()) {
            $currentCaptura = $capturas->first();
            $currentIndex = 0; // Siempre inicia en la primera si hay capturas
        }

        return view('carpetas.show', compact(
            'carpeta',
            'capturas', // Todavía pasamos todas las capturas inicialmente
            'currentCaptura',
            'currentIndex'
        ));
    }

    /**
     * Provee sugerencias de búsqueda para nombres y montos.
     * Mejora para manejar mejor los casos no JSON y evitar errores 500.
     */
    public function searchSuggestions(Request $request, Carpeta $carpeta)
    {
        $searchTerm = $request->query('query');
        $suggestions = [];

        if (empty($searchTerm)) {
            return response()->json([]);
        }

        $capturas = $carpeta->capturas()
            ->where(function ($query) use ($searchTerm) {
                $query->where('campo_texto', 'like', '%' . $searchTerm . '%'); // Búsqueda genérica primero
            })
            ->limit(20) // Un poco más para tener de donde sacar
            ->get();

        foreach ($capturas as $captura) {
            $text = $captura->campo_texto;
            try {
                $data = json_decode($text, true);
                if (is_array($data)) {
                    foreach ($data as $item) {
                        if (isset($item['nombre']) && stripos($item['nombre'], $searchTerm) !== false) {
                            $suggestions[] = $item['nombre'];
                        }
                        if (isset($item['monto']) && stripos($item['monto'], $searchTerm) !== false) {
                            $suggestions[] = $item['monto'];
                        }
                    }
                } else {
                    // Si no es JSON o decodificación falla, buscar en texto plano
                    if (stripos($text, $searchTerm) !== false) {
                        $suggestions[] = $searchTerm; // Podrías tomar un fragmento, pero para sugerencia es mejor el término buscado
                    }
                }
            } catch (\Exception $e) {
                // Esto maneja errores si json_decode falla por un formato de JSON inválido
                if (stripos($text, $searchTerm) !== false) {
                    $suggestions[] = $searchTerm;
                }
            }
        }

        // Eliminar duplicados y tomar los primeros 10
        $suggestions = array_values(array_unique($suggestions));
        if (count($suggestions) > 10) {
            $suggestions = array_slice($suggestions, 0, 10);
        }

        return response()->json($suggestions);
    }

    /**
     * Realiza la búsqueda de capturas y devuelve los datos para el carrusel en tiempo real.
     */
    public function searchRealtime(Request $request, Carpeta $carpeta)
{
    $capturas = $carpeta->capturas()
        ->when($request->filled('search_captura'), function ($query) use ($request) {
            $term = $request->search_captura;
            $query->where(function ($subQ) use ($term) {
                $subQ->whereRaw("JSON_EXTRACT(campo_texto, '$[*].nombre') LIKE ?", ['%"%' . $term . '%"%'])
                     ->orWhereRaw("JSON_EXTRACT(campo_texto, '$[*].monto') LIKE ?", ['%"%' . $term . '%"%'])
                     ->orWhere('campo_texto', 'like', '%' . $term . '%');
            });
        })
        ->when($request->filled('search_captura_1'), function ($query) use ($request) {
            $term = $request->search_captura_1;
            $query->where(function ($subQ) use ($term) {
                $subQ->whereRaw("JSON_EXTRACT(campo_texto, '$[*].nombre') LIKE ?", ['%"%' . $term . '%"%'])
                     ->orWhereRaw("JSON_EXTRACT(campo_texto, '$[*].monto') LIKE ?", ['%"%' . $term . '%"%'])
                     ->orWhere('campo_texto', 'like', '%' . $term . '%');
            });
        })
        ->when($request->filled('search_captura_2'), function ($query) use ($request) {
            $term = $request->search_captura_2;
            $query->where(function ($subQ) use ($term) {
                $subQ->whereRaw("JSON_EXTRACT(campo_texto, '$[*].nombre') LIKE ?", ['%"%' . $term . '%"%'])
                     ->orWhereRaw("JSON_EXTRACT(campo_texto, '$[*].monto') LIKE ?", ['%"%' . $term . '%"%'])
                     ->orWhere('campo_texto', 'like', '%' . $term . '%');
            });
        })
        ->when($request->filled('search_captura_3'), function ($query) use ($request) {
            $term = $request->search_captura_3;
            $query->where(function ($subQ) use ($term) {
                $subQ->whereRaw("JSON_EXTRACT(campo_texto, '$[*].nombre') LIKE ?", ['%"%' . $term . '%"%'])
                     ->orWhereRaw("JSON_EXTRACT(campo_texto, '$[*].monto') LIKE ?", ['%"%' . $term . '%"%'])
                     ->orWhere('campo_texto', 'like', '%' . $term . '%');
            });
        })
        ->when($request->start_date, fn($q) => $q->whereDate('created_at', '>=', $request->start_date))
        ->when($request->end_date, fn($q) => $q->whereDate('created_at', '<=', $request->end_date))
        ->get()
        ->map(function ($captura) {
            return [
                'id' => $captura->id,
                'foto_original_url' => asset('storage/' . $captura->foto_original),
                'campo_texto_exists' => !empty($captura->campo_texto),
                'campo_texto' => $captura->campo_texto, // Se puede usar para encontrar la imagen buscada
            ];
        });

    return response()->json(['capturas' => $capturas]);
}

}