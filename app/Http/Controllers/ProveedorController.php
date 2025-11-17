<?php

namespace App\Http\Controllers;

use App\Models\PagoProveedor;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProveedorController extends Controller
{
    public function index()
    {
        $proveedores = Proveedor::with('pagos')
            ->latest()
            ->paginate(10);
        return view('proveedores.index', compact('proveedores'));
    }

    public function create()
    {
        return view('proveedores.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo_factura' => 'required|string|max:50|unique:proveedores',
            'pago_inicial' => 'required|numeric|min:0',
            'deuda_total' => 'required|numeric|min:0',
            'fecha_registro' => 'required|date',
            'estado' => 'required|in:Pagado,Saldo pendiente',
            'foto_factura' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
    
        try {
            DB::beginTransaction();
    
            if ($request->hasFile('foto_factura')) {
                $path = $request->file('foto_factura')->store('facturas', 'public');
                $validated['foto_factura'] = $path;
            }
    
            // Crear el proveedor
            $proveedor = Proveedor::create($validated);
    
            // Si hay pago inicial, registrarlo como primer pago
            if ($validated['pago_inicial'] > 0) {
                PagoProveedor::create([
                    'proveedor_id' => $proveedor->id,
                    'monto_pago' => $validated['pago_inicial'],
                    'fecha_pago' => $validated['fecha_registro'],
                    'saldo_restante' => $validated['deuda_total'] - $validated['pago_inicial']
                ]);
            }
    
            // Actualizar estado basado en pago inicial
            if ($validated['pago_inicial'] >= $validated['deuda_total']) {
                $proveedor->update(['estado' => 'Pagado']);
            }
    
            DB::commit();
            return redirect()->route('proveedores.index')
                ->with('success', 'Proveedor registrado correctamente');
    
        } catch (\Exception $e) {
            DB::rollback();
            if (isset($path)) {
                Storage::disk('public')->delete($path);
            }
            return back()->with('error', 'Error al registrar el proveedor: ' . $e->getMessage());
        }
    }
    
    public function show($id)
    {
        // Obtienes el proveedor
        $proveedor = Proveedor::findOrFail($id);
        
        // Obtienes los pagos del proveedor
        $pagos = PagoProveedor::where('proveedor_id', $id)->get();
        
        // Calcular lo que ha pagado, lo que debe y lo que falta
        $totalPagado = $pagos->sum('monto');
        $totalDeuda = $proveedor->deuda; // Asumiendo que tienes un campo 'deuda' en el proveedor
        $saldoRestante = $totalDeuda - $totalPagado;
        
        return view('proveedores.show', compact('proveedor', 'totalPagado', 'totalDeuda', 'saldoRestante'));
    }
    

    public function edit($id)
    {
        $proveedor = Proveedor::findOrFail($id);
        return view('proveedores.edit', compact('proveedor'));
    }
    

    public function update(Request $request, $id)
    {
        $proveedor = Proveedor::findOrFail($id);
    
        // Validación de datos
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo_factura' => 'required|string|max:255',
            'pago_inicial' => 'required|numeric|min:0',
            'deuda_total' => 'required|numeric|min:0',
            'fecha_registro' => 'required|date',
            'estado' => 'required|string|in:Pagado,Saldo pendiente',
            'foto_factura' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',  // Validación para el archivo
        ]);
    
        // Actualizar proveedor
        $proveedor->nombre = $request->nombre;
        $proveedor->codigo_factura = $request->codigo_factura;
        $proveedor->pago_inicial = $request->pago_inicial;
        $proveedor->deuda_total = $request->deuda_total;
        $proveedor->fecha_registro = $request->fecha_registro;
        $proveedor->estado = $request->estado;
    
        // Si se sube un nuevo archivo de factura
        if ($request->hasFile('foto_factura')) {
            // Eliminar el archivo viejo si existe
            if ($proveedor->foto_factura && Storage::exists('public/' . $proveedor->foto_factura)) {
                Storage::delete('public/' . $proveedor->foto_factura);
            }
    
            // Subir el nuevo archivo
            $proveedor->foto_factura = $request->file('foto_factura')->store('facturas', 'public');
        }
    
        // Guardar cambios
        $proveedor->save();
    
        // Redirigir con un mensaje
        return redirect()->route('proveedores.index')->with('success', 'Proveedor actualizado con éxito');
    }
    

    public function destroy($id)
{
    $proveedor = Proveedor::findOrFail($id);

    // Eliminar proveedor
    $proveedor->delete();

    return redirect()->route('proveedores.index')->with('success', 'Proveedor eliminado correctamente.');
}

}
