@extends('adminlte::page')

@section('title', 'Registrar Pago')

@section('content_header')
    <h1><i class="fas fa-money-bill-wave mr-2"></i>Registrar Pago</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        
                    
        <form action="{{ route('pagos.store') }}" method="POST" enctype="multipart/form-data">    @csrf
            @if(isset($proveedor_id))
                <input type="hidden" name="proveedor_id" value="{{ $proveedor_id }}">
            @endif

            <div class="row">
                <div class="col-md-6">
                    @if(!isset($proveedor_id))
                        <div class="form-group">
                            <label for="proveedor_id">Proveedor</label>
                            <select name="proveedor_id" class="form-control @error('proveedor_id') is-invalid @enderror" required>
                                <option value="">Seleccione un proveedor</option>
                                @foreach($proveedores as $proveedor)
                                    <option value="{{ $proveedor->id }}" data-saldo="{{ $proveedor->saldo_pendiente }}">
                                        {{ $proveedor->nombre }} - Saldo: ${{ number_format($proveedor->saldo_pendiente, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('proveedor_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    @endif

                    <div class="form-group">
                        <label for="foto_factura">Foto de Factura</label>
                        <input type="file" name="foto_factura" class="form-control-file" accept="image/*">
                    </div>
                    

                    <div class="form-group">
                        <label for="monto_pago">Monto a Pagar</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" step="0.01" name="monto_pago" 
                                class="form-control @error('monto_pago') is-invalid @enderror" 
                                required value="{{ old('monto_pago') }}">
                        </div>
                        @error('monto_pago')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="fecha_pago">Fecha de Pago</label>
                        <input type="date" name="fecha_pago" 
                            class="form-control @error('fecha_pago') is-invalid @enderror"
                            value="{{ old('fecha_pago', date('Y-m-d')) }}" required>
                        @error('fecha_pago')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>Guardar Pago
                        </button>
                        <a href="{{ route('proveedores.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times mr-1"></i>Cancelar
                        </a>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Nota:</strong> El pago se registrará y actualizará automáticamente el saldo pendiente del proveedor.
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    $('select[name="proveedor_id"]').change(function() {
        const saldoPendiente = $(this).find('option:selected').data('saldo');
        $('input[name="monto_pago"]').attr('max', saldoPendiente);
    });
});
</script>
@stop