@extends('adminlte::page')
@section('title', 'Ver Registro')

@section('content')
    <div class="card">
        <div class="card-body">
            <h3>Registro #{{ $registro->id }}</h3>
            <p><strong>Celular:</strong> {{ $registro->celular }}</p>
            <p><strong>Persona:</strong> {{ $registro->persona }}</p>
            <p><strong>Departamento:</strong> {{ $registro->departamento }}</p>
            <p><strong>Producto:</strong> {{ $registro->producto->nombre ?? '' }}</p>
            <p><strong>Estado:</strong> {{ ucfirst($registro->estado) }}</p>
            <p><strong>Descripción del Problema:</strong> {{ $registro->descripcion_problema }}</p>
            <p><strong>Fecha Registro:</strong> {{ $registro->fecha_inscripcion }}</p>
            <p><strong>Fecha Cambio de Estado:</strong> {{ $registro->fecha_cambio_estado ?? '-' }}</p>
            <a href="{{ route('prodregistromalestado.index') }}" class="btn btn-secondary">Volver</a>
        </div>
    </div>
@stop
