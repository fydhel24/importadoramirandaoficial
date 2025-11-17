
@extends('adminlte::page')

@section('title', 'Panel de Administración')

@section('content_header')
    <h1></h1>
@stop

@section('content')

<h1>Selecciona un mes para descargar el reporte de pedidos</h1>
    <form action="{{ route('reporte.descargar') }}" method="POST">
        @csrf
        <label for="mes">Mes:</label>
        <input type="month" name="mes" id="mes" required>
        <button type="submit">Descargar Reporte de Mes</button>
    </form>
@stop