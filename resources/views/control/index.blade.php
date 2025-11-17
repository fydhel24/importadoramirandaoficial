@extends('adminlte::page')

@section('title', 'Panel de Administración')

@section('content_header')
    <h1>Sucursales</h1>
@stop

@section('content')
@if(session()->has('success'))
    <div class="alert alert-success">
        {{ session()->get('success') }}
    </div>
@endif

<div class="container">
    <h2>Sucursales</h2>
    <div class="row">
        @foreach($sucursales as $sucursal)
            <div class="col-md-4 mb-4">
                {{-- Widget de perfil para cada sucursal --}}
                <x-adminlte-profile-widget name="{{ $sucursal->nombre }}" desc="Sucursal" class="elevation-4"
                    img="https://picsum.photos/id/{{ $loop->index + 1 }}/100" layout-type="classic">

                    {{-- Mostrar ubicación --}}
                    <x-adminlte-profile-row-item title="Direrccion" text="{{ $sucursal->direccion ?? 'Ubicación no disponible' }}"/>

                    {{-- Mostrar cantidad de productos en esta sucursal --}}
                    <x-adminlte-profile-row-item title="Cantidad de productos " text="{{ $sucursal->inventarios->sum('cantidad') }} productos disponibles"/>

                    {{-- Botón Contactar --}}
                    <x-adminlte-profile-row-item title="Contactar" class="text-center border-bottom">
                        <button class="btn btn-red btn-sm">Contactar</button>
                    </x-adminlte-profile-row-item>

                    @can('control.productos')
                    <x-adminlte-profile-row-item title="Ver Productos" url="{{ route('control.productos', $sucursal->id) }}" icon="fas fa-box" size=6/>
                        <x-adminlte-profile-row-item title="Generar Reporte" url="{{ route('sucursal.reporte.productos', $sucursal->id) }}" icon="fas fa-file-pdf" size=6 />

                        @endcan
                </x-adminlte-profile-widget>
            </div>
        @endforeach
    </div>
</div>
@stop

@section('css')
    <style>
        /* Personaliza tus estilos aquí si es necesario */
        .card {
            margin-bottom: 20px; /* Espaciado entre tarjetas */
            transition: transform 0.2s; /* Animación al pasar el mouse */
        }
        .card:hover {
            transform: scale(1.05); /* Efecto de zoom */
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Puedes agregar scripts aquí si es necesario
    </script>
@stop

