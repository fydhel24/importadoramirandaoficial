@extends('adminlte::page')

@section('title', 'Agregar Capturas')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Agregar Capturas</h1>
            @if(isset($carpeta))
                <p class="text-muted">Carpeta: {{ $carpeta->descripcion }}</p>
            @endif
        </div>
        <div class="col-sm-6">
            <a href="{{ isset($carpeta) ? route('carpetas.show', $carpeta) : route('carpetas.index') }}" class="btn btn-secondary float-right">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('capturas.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if(isset($carpeta))
                    <input type="hidden" name="carpeta_id" value="{{ $carpeta->id }}">
                @endif

                <div class="form-group">
                    <label for="foto_original">Seleccionar Fotos</label>
                    <input type="file" class="form-control-file @error('foto_original') is-invalid @enderror" 
                           id="foto_original" name="foto_original[]" multiple accept="image/*">
                    @error('foto_original')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Puedes seleccionar múltiples fotos.</small>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Capturas
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
    <style>
        .card {
            border-top: 3px solid #007bff;
        }
    </style>
@stop 