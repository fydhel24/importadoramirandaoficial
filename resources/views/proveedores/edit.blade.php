@extends('adminlte::page')

@section('title', 'Editar Proveedor')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('proveedores.index') }}">Proveedores</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-purple">
                <div class="card-header">
                    <h3 class="card-title">Formulario de Edición</h3>
                </div>
                
                <form action="{{ route('proveedores.update', $proveedor->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <!-- Nombre del Proveedor -->
                                <div class="form-group">
                                    <label for="nombre">Nombre del Proveedor <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                                           id="nombre" name="nombre" value="{{ old('nombre', $proveedor->nombre) }}" required>
                                    @error('nombre')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                
                                <!-- Código de Factura -->
                                <div class="form-group">
                                    <label for="codigo_factura">Código de Factura <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('codigo_factura') is-invalid @enderror" 
                                           id="codigo_factura" name="codigo_factura" value="{{ old('codigo_factura', $proveedor->codigo_factura) }}" required>
                                    @error('codigo_factura')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                
                                <!-- Pago Inicial -->
                                <div class="form-group">
                                    <label for="pago_inicial">Pago Inicial <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input type="number" step="0.01" min="0" class="form-control @error('pago_inicial') is-invalid @enderror" 
                                               id="pago_inicial" name="pago_inicial" value="{{ old('pago_inicial', $proveedor->pago_inicial) }}" required>
                                        @error('pago_inicial')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <!-- Deuda Total -->
                                <div class="form-group">
                                    <label for="deuda_total">Deuda Total <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input type="number" step="0.01" min="0" class="form-control @error('deuda_total') is-invalid @enderror" 
                                               id="deuda_total" name="deuda_total" value="{{ old('deuda_total', $proveedor->deuda_total) }}" required>
                                        @error('deuda_total')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                
                                <!-- Fecha de Registro -->
                                <div class="form-group">
                                    <label for="fecha_registro">Fecha de Registro <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('fecha_registro') is-invalid @enderror" 
                                           id="fecha_registro" name="fecha_registro" value="{{ old('fecha_registro', $proveedor->fecha_registro) }}" required>
                                    @error('fecha_registro')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                
                                <!-- Estado -->
                                <div class="form-group">
                                    <label for="estado">Estado <span class="text-danger">*</span></label>
                                    <select class="form-control @error('estado') is-invalid @enderror" id="estado" name="estado" required>
                                        <option value="Pagado" {{ old('estado', $proveedor->estado) == 'Pagado' ? 'selected' : '' }}>Pagado</option>
                                        <option value="Saldo pendiente" {{ old('estado', $proveedor->estado) == 'Saldo pendiente' ? 'selected' : '' }}>Saldo pendiente</option>
                                    </select>
                                    @error('estado')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                
                        <!-- Foto de Factura -->
                        <div class="form-group">
                            <label for="foto_factura">Foto de Factura</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('foto_factura') is-invalid @enderror" id="foto_factura" name="foto_factura">
                                    <label class="custom-file-label" for="foto_factura">Seleccionar archivo</label>
                                </div>
                            </div>
                            @error('foto_factura')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                
                            @if($proveedor->foto_factura)
                                <div class="mt-2">
                                    <img src="{{ Storage::url($proveedor->foto_factura) }}" alt="Factura" class="img-thumbnail" style="max-height: 200px;">
                                </div>
                            @endif
                        </div>
                    </div>
                
                    <div class="card-footer">
                        <button type="submit" class="btn btn-purple">Actualizar Proveedor</button>
                        <a href="{{ route('proveedores.index') }}" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
                
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
    <script>
        $(function () {
            bsCustomFileInput.init();
        });
    </script>
@endsection