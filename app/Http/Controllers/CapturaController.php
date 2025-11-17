<?php

namespace App\Http\Controllers;

use App\Models\Captura;
use App\Models\Carpeta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class CapturaController extends Controller
{
    // Muestra todas las capturas con DataTables
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $capturas = Captura::with('carpeta')->get();
            return DataTables::of($capturas)
                ->addColumn('action', function ($row) {
                    // Usamos JavaScript para insertar el ID de manera dinámica
                    return '
                    <button class="btn btn-primary btn-sm" onclick="editFoto(' . $row->id . ')">Modificar</button>
                    <button class="btn btn-danger btn-sm" onclick="deleteFoto(' . $row->id . ')">Eliminar</button>';
                })
                ->addColumn('carpeta', function ($row) {
                    return $row->carpeta ? $row->carpeta->descripcion : 'Sin carpeta';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('capturas.index');
    }

    public function create(Request $request)
    {
        $carpeta_id = $request->query('carpeta_id');
        $carpeta = $carpeta_id ? Carpeta::find($carpeta_id) : null;
        return view('capturas.create', compact('carpeta'));
    }

    public function edit($id)
    {
        $captura = Captura::with('carpeta')->findOrFail($id);
        return view('capturas.edit', compact('captura'));
    }

    // Función para agregar una nueva captura (foto)
    public function store(Request $request)
    {
        $request->validate([
            'foto_original' => 'required|array',
            'foto_original.*' => 'image|mimes:jpeg,png,jpg,gif',
            'carpeta_id' => 'required|exists:carpetas,id',
        ]);

        $capturas = [];

        // Iterar sobre cada archivo y guardarlo
        foreach ($request->file('foto_original') as $foto) {
            // Almacenar cada foto
            $fotoPath = $foto->store('capturas', 'public');

            // Crear un nuevo registro para cada foto
            $captura = Captura::create([
                'foto_original' => $fotoPath,
                'carpeta_id' => $request->carpeta_id,
            ]);
            $capturas[] = $captura;
        }

        return redirect()->route('carpetas.show', $request->carpeta_id)
            ->with('success', 'Fotos agregadas exitosamente.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'foto_original' => 'required|string', // Esperamos un string base64 de la imagen
        ]);

        $captura = Captura::findOrFail($id);

        // Obtener la imagen base64 y convertirla a un archivo
        $imageData = $request->input('foto_original');
        $image = str_replace('data:image/png;base64,', '', $imageData);
        $image = str_replace(' ', '+', $image); // Reemplazar espacios con +

        $imageName = 'captura_' . time() . '.png'; // Nombre de la nueva imagen
        $folderPath = base_path('public_html/storage/capturas');
        $imagePath = $folderPath . '/' . $imageName;

        // Crear la carpeta si no existe
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0775, true);
        }

        // Guardar la imagen en el servidor
        file_put_contents($imagePath, base64_decode($image));

        // Eliminar la imagen anterior si existe
        $oldImagePath = base_path('public_html/storage/' . $captura->foto_original);
        if (file_exists($oldImagePath)) {
            unlink($oldImagePath);
        }

        // Actualizar la captura con la nueva imagen
        $captura->foto_original = 'capturas/' . $imageName;
        $captura->save();

        return redirect()->route('carpetas.show', $captura->carpeta_id)
            ->with('success', 'Foto modificada exitosamente.');
    }


    public function destroy($id)
    {
        try {
            // Buscar la captura por ID
            $captura = Captura::findOrFail($id);
            $carpeta_id = $captura->carpeta_id;

            // Eliminar el archivo de almacenamiento (si existe)
            if ($captura->foto_original) {
                $imagePath = base_path('public_html/storage/' . $captura->foto_original);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            // Eliminar el registro de la base de datos
            $captura->delete();

            // Redirigir a la vista de la carpeta con mensaje de éxito
            return redirect()->route('carpetas.show', $carpeta_id)
                ->with('success', 'La foto fue eliminada exitosamente.');
        } catch (\Exception $e) {
            // Redirigir con mensaje de error
            return redirect()->back()
                ->with('error', 'Hubo un error al eliminar la foto.');
        }
    }
    public function searchSuggestions(Request $request, Carpeta $carpeta)
    {
        $searchTerm = $request->query('query');
        $suggestions = [];

        if (empty($searchTerm)) {
            return response()->json([]);
        }

        $capturas = $carpeta->capturas()
            ->where(function ($query) use ($searchTerm) {
                // Buscar en nombres JSON
                $query->whereRaw("JSON_EXTRACT(campo_texto, '$[*].nombre') LIKE ?", ['%"%' . $searchTerm . '%"%'])
                    // Buscar en montos JSON
                    ->orWhereRaw("JSON_EXTRACT(campo_texto, '$[*].monto') LIKE ?", ['%"%' . $searchTerm . '%"%'])
                    // Buscar en texto plano
                    ->orWhere('campo_texto', 'like', '%' . $searchTerm . '%');
            })
            ->limit(10) // Limitar el número de sugerencias
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
                        // Incluir montos como sugerencias si el término coincide con parte de un monto
                        if (isset($item['monto']) && stripos($item['monto'], $searchTerm) !== false) {
                            $suggestions[] = $item['monto'];
                        }
                    }
                }
            } catch (\Exception $e) {
                // Si no es JSON válido, buscar en el texto plano directamente
                if (stripos($text, $searchTerm) !== false) {
                    // Tomar un fragmento del texto si es muy largo
                    $startPos = stripos($text, $searchTerm);
                    $snippet = substr($text, max(0, $startPos - 20), 40 + strlen($searchTerm));
                    $suggestions[] = '...' . trim($snippet) . '...';
                }
            }
        }

        // Eliminar duplicados y tomar los primeros N
        $suggestions = array_values(array_unique($suggestions));
        if (count($suggestions) > 10) {
            $suggestions = array_slice($suggestions, 0, 10);
        }

        return response()->json($suggestions);
    }

    public function processAllOcrAndSave(Request $request, Carpeta $carpeta)
    {
        // Esta función procesará el OCR en el frontend y enviará los resultados
        // o si prefieres procesar el OCR en el backend, necesitarías una librería PHP de OCR
        // para este ejemplo, el frontend sigue siendo responsable de la parte de OCR
        // y solo usaremos este endpoint para guardar los datos.

        $request->validate([
            'capturas_data' => 'required|array',
            'capturas_data.*.id' => 'required|exists:capturas,id',
            'capturas_data.*.text_data' => 'required|string|nullable', // Aceptar string vacío
        ]);

        foreach ($request->input('capturas_data') as $capturaData) {
            $captura = Captura::find($capturaData['id']);
            if ($captura) {
                $captura->campo_texto = $capturaData['text_data'];
                $captura->save();
            }
        }

        return response()->json(['message' => 'Textos de OCR guardados exitosamente para todas las capturas.']);
    }
}
