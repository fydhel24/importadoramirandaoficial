@extends('adminlte::page')

@section('title', 'Editar Solicitud')

@section('content_header')
    <h1>Editar Solicitud</h1>
@stop

@section('content')
    <div class="container">
        <form action="{{ route('solicitudes.update', $solicitude->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="nombre" class="form-control" value="{{ $solicitude->nombre }}" required>
            </div>

            <div class="form-group">
                <label>CI</label>
                <input type="text" name="ci" class="form-control" value="{{ $solicitude->ci }}" required>
            </div>

            <div class="form-group">
                <label>Celular</label>
                <input type="text" name="celular" class="form-control" value="{{ $solicitude->celular }}" required>
            </div>

            <div class="form-group">
                <label>CV en PDF</label>
                <input type="file" name="cv_pdf" accept="application/pdf" class="form-control">
                @if ($solicitude->cv_pdf)
                    <p>Archivo actual: <a href="{{ asset('storage/' . $solicitude->cv_pdf) }}" target="_blank">Ver CV</a></p>
                @endif
            </div>

            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Actualizar</button>
            <a href="{{ route('solicitudes.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
@stop
