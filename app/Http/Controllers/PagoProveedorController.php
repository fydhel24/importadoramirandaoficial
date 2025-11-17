<?php

namespace App\Http\Controllers;

// app/Http/Controllers/PagoProveedorController.php

use App\Models\Proveedor;
use App\Models\PagoProveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Add this import


class PagoProveedorController extends Controller
{
    public function index()
    {
        $pagos = PagoProveedor::with('proveedor')
            ->latest()
            ->paginate(10);
        return view('pagos.index', compact('pagos'));
    }

    public function create(Request $request)
    {
        $proveedores = Proveedor::where('estado', 'Saldo pendiente')->get();
        $proveedor_id = $request->query('proveedor_id');
        return view('pagos.create', compact('proveedores', 'proveedor_id'));
    }

    public function store(Request $request)
    {
        try {
            // Now you can use Log::info(), Log::error(), etc.
            Log::info('Received payment data:', $request->all());

            $validated = $request->validate([
                'proveedor_id' => 'required|exists:proveedores,id',
                'monto_pago' => 'required|numeric|min:0.01',
                'fecha_pago' => 'required|date',
                'foto_factura' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            // Check if file was uploaded
            if ($request->hasFile('foto_factura')) {
                Log::info('File uploaded:', [
                    'original_name' => $request->file('foto_factura')->getClientOriginalName(),
                    'size' => $request->file('foto_factura')->getSize()
                ]);
            }

            DB::beginTransaction();

            $proveedor = Proveedor::findOrFail($validated['proveedor_id']);
            
            // Handle file upload
            $fotoFacturaPath = null;
            if ($request->hasFile('foto_factura')) {
                $fotoFacturaPath = $request->file('foto_factura')->store('facturas', 'public');
                Log::info('Stored file path: ' . $fotoFacturaPath);
            }

            $pago = PagoProveedor::create([
                'proveedor_id' => $validated['proveedor_id'],
                'monto_pago' => $validated['monto_pago'],
                'fecha_pago' => $validated['fecha_pago'],
                'saldo_restante' => $proveedor->saldo_pendiente - $validated['monto_pago'],
                'foto_factura' => $fotoFacturaPath
            ]);

            // Actualizar estado del proveedor si el saldo es 0
            if ($pago->saldo_restante <= 0) {
                $proveedor->update(['estado' => 'Pagado']);
            }

            DB::commit();
            return redirect()->route('proveedores.index')
                ->with('success', 'Pago registrado correctamente');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Payment creation error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return back()
                ->with('error', 'Error al guardar el pago: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(PagoProveedor $pago)
    {
        $pago->load('proveedor');
        return view('pagos.show', compact('pago'));
    }
}