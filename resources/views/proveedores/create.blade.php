@extends('adminlte::page')

@section('title', 'Registrar Nuevo Proveedor')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-user-plus mr-2"></i>Registrar Nuevo Proveedor</h1>
        <a href="{{ route('proveedores.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i>Volver
        </a>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('proveedores.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nombre">Nombre del Proveedor <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" value="{{ old('nombre') }}" required>
                            @error('nombre')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="codigo_factura">Código de Factura <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('codigo_factura') is-invalid @enderror" id="codigo_factura" name="codigo_factura" value="{{ old('codigo_factura') }}" required>
                            @error('codigo_factura')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="deuda_total">Deuda Total <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                </div>
                                <input type="number" class="form-control @error('deuda_total') is-invalid @enderror" id="deuda_total" name="deuda_total" value="{{ old('deuda_total') }}" step="0.01" min="0" required>
                                @error('deuda_total')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="pago_inicial">Pago Inicial <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                </div>
                                <input type="number" class="form-control @error('pago_inicial') is-invalid @enderror" id="pago_inicial" name="pago_inicial" value="{{ old('pago_inicial', 0) }}" step="0.01" min="0" required>
                                @error('pago_inicial')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fecha_registro">Fecha de Registro <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('fecha_registro') is-invalid @enderror" id="fecha_registro" name="fecha_registro" value="{{ old('fecha_registro', date('Y-m-d')) }}" required>
                            @error('fecha_registro')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="estado">Estado <span class="text-danger">*</span></label>
                            <select class="form-control @error('estado') is-invalid @enderror" id="estado" name="estado" required>
                                <option value="Saldo pendiente" {{ old('estado') == 'Saldo pendiente' ? 'selected' : '' }}>Saldo pendiente</option>
                                <option value="Pagado" {{ old('estado') == 'Pagado' ? 'selected' : '' }}>Pagado</option>
                            </select>
                            @error('estado')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="foto_factura">Foto de la Factura</label>
                    <div class="input-group">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input @error('foto_factura') is-invalid @enderror" id="foto_factura" name="foto_factura" accept="image/jpeg,image/png,image/jpg">
                            <label class="custom-file-label" for="foto_factura">Seleccionar archivo</label>
                        </div>
                    </div>
                    <small class="form-text text-muted">Formatos aceptados: JPG, JPEG, PNG. Tamaño máximo: 2MB</small>
                    @error('foto_factura')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group text-right mt-4">
                    <button type="reset" class="btn btn-default">
                        <i class="fas fa-undo mr-1"></i>Restablecer
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Guardar Proveedor
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Actualizar nombre de archivo en input file
        $('input[type="file"]').change(function(e) {
            var fileName = e.target.files[0].name;
            $('.custom-file-label').html(fileName);
        });
        
        // Función para actualizar el estado automáticamente basado en montos
        function actualizarEstado() {
            const deudaTotal = parseFloat($('#deuda_total').val()) || 0;
            const pagoInicial = parseFloat($('#pago_inicial').val()) || 0;
            
            if (pagoInicial >= deudaTotal && deudaTotal > 0) {
                $('#estado').val('Pagado');
            } else {
                $('#estado').val('Saldo pendiente');
            }
        }
        
        $('#deuda_total, #pago_inicial').change(actualizarEstado);
    });
</script>
@stop