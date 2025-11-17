@extends('adminlte::page')

@section('title', 'Nueva Carpeta')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Nueva Carpeta</h1>
        </div>
        <div class="col-sm-6">
            <a href="{{ route('carpetas.index') }}" class="btn btn-secondary float-right">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('carpetas.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <input type="text" class="form-control @error('descripcion') is-invalid @enderror" 
                           id="descripcion" name="descripcion" value="{{ old('descripcion') }}" required>
                    @error('descripcion')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="fecha">Fecha</label>
                    <input type="date" class="form-control @error('fecha') is-invalid @enderror" 
                           id="fecha" name="fecha" value="{{ old('fecha', date('Y-m-d')) }}" required>
                    @error('fecha')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </form>
        </div>
    </div>
@stop 