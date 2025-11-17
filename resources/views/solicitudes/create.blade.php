@extends('adminlte::page')

@section('title', 'Nueva Solicitud')

@section('content_header')
    <h1>Nueva Solicitud de Trabajo</h1>
@stop

@section('content')
    <div class="container">
        <form action="{{ route('solicitudes.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="nombre" class="form-control" required>
            </div>

            <div class="form-group">
                <label>CI</label>
                <input type="text" name="ci" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Celular</label>
                <input type="text" name="celular" class="form-control" required>
            </div>

            <div class="form-group">
                <label>CV en PDF</label>
                <input type="file" name="cv_pdf" accept="application/pdf" class="form-control">
            </div>

            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Guardar</button>
            <a href="{{ route('solicitudes.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
@stop
