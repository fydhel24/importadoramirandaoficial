@extends('adminlte::page')

@section('title', 'Panel de Administración')

@section('content_header')
    <h1>Bienvenido al Panel de Administración</h1>
@stop


@section('content')
<div class="container">
    <h1>Crear Pedido</h1>
    <form action="{{ route('orden.store') }}" method="POST">
        @csrf
        <input type="hidden" name="id_semana" value="{{ $id }}">
        <div class="form-group">
            <label for="nombre">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required>
        </div>
        <div class="form-group">
            <label for="ci">CI</label>
            <input type="text" class="form-control" id="ci" name="ci" required>
        </div>
        <div class="form-group">
            <label for="celular">Celular</label>
            <input type="text" class="form-control" id="celular" name="celular" required>
        </div>
        <div class="form-group">
            <label for="destino">Destino</label>
            <input type="text" class="form-control" id="destino" name="destino" required>
        </div>
        <div class="form-group">
            <label for="direccion">Dirección</label>
            <input type="text" class="form-control" id="direccion" name="direccion" required>
        </div>
        <div class="form-group">
            <label for="estado">Estado</label>
            <input type="text" class="form-control" id="estado" name="estado" required>
        </div>
        <div class="form-group">
            <label for="cantidad_productos">Cantidad de Productos</label>
            <input type="number" class="form-control" id="cantidad_productos" name="cantidad_productos" required>
        </div>
        <div class="form-group">
            <label for="detalle">Detalle</label>
            <textarea class="form-control" id="detalle" name="detalle"></textarea>
        </div>
        <div class="form-group">
            <label for="productos">Productos</label>
            <textarea class="form-control" id="productos" name="productos"></textarea>
        </div>
        <div class="form-group">
            <label for="monto_deposito">Monto Depósito</label>
            <input type="number" class="form-control" id="monto_deposito" name="monto_deposito" required>
        </div>
        <div class="form-group">
            <label for="monto_enviado_pagado">Monto Enviado/Pagado</label>
            <input type="number" class="form-control" id="monto_enviado_pagado" name="monto_enviado_pagado" required>
        </div>
        <div class="form-group">
            <label for="fecha">Fecha</label>
            <input type="date" class="form-control" id="fecha" name="fecha" required>
        </div>
        <button type="submit" class="btn btn-primary">Guardar</button>
    </form>
</div>
@endsection

