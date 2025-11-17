@extends('adminlte::page')

@section('title', 'Editar Carpeta')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Editar Carpeta</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-right">
                <a href="{{ route('carpetas.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('carpetas.update', $carpeta) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <input type="text" class="form-control @error('descripcion') is-invalid @enderror" 
                           id="descripcion" name="descripcion" value="{{ old('descripcion', $carpeta->descripcion) }}" required>
                    @error('descripcion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="fecha">Fecha</label>
                    <input type="date" class="form-control @error('fecha') is-invalid @enderror" 
                           id="fecha" name="fecha" value="{{ old('fecha', $carpeta->fecha) }}" required>
                    @error('fecha')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop 