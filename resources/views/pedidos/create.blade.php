<!-- resources/views/pedidos/create.blade.php -->

@extends('adminlte::page')

@section('title', 'Panel de Administración')

@section('content_header')
    <h1></h1>
@stop

@section('content')
    <h1>Crear Nuevo Pedido</h1>

    <form action="{{ route('pedidos.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="card">
            <div class="card-body">
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="ci">CI:</label>
                    <input type="text" id="ci" name="ci" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="celular">Celular:</label>
                    <input type="text" id="celular" name="celular" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="destino">Destino:</label>
                    <input type="text" id="destino" name="destino" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="direccion">Dirección:</label>
                    <input type="text" id="direccion" name="direccion" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="estado">Estado:</label>
                    <input type="text" id="estado" name="estado" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="cantidad_productos">Cantidad de Productos:</label>
                    <input type="number" id="cantidad_productos" name="cantidad_productos" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="detalle">Detalle:</label>
                    <textarea id="detalle" name="detalle" class="form-control" required></textarea>
                </div>

                <div class="form-group">
                    <label for="productos">Productos:</label>
                    <textarea id="productos" name="productos" class="form-control" required></textarea>
                </div>

                <div class="form-group">
                    <label for="monto_deposito">Monto Depósito:</label>
                    <input type="number" id="monto_deposito" name="monto_deposito" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="monto_enviado_pagado">Monto Enviado Pagado:</label>
                    <input type="number" id="monto_enviado_pagado" name="monto_enviado_pagado" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="fecha">Fecha:</label>
                    <input type="date" id="fecha" name="fecha" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="id_semana">Semana:</label>
                    <select id="id_semana" name="id_semana" class="form-control" required>
                        @foreach($semanas as $semana)
                            <option value="{{ $semana->id }}">{{ $semana->nombre }} ({{ $semana->fecha }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="foto_comprobante">Foto Comprobante:</label>
                    <input type="file" id="foto_comprobante" name="foto_comprobante" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="codigo">Código:</label>
                    <input type="text" id="codigo" name="codigo" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary">Crear Pedido</button>
            </div>
        </div>
    </form>
@stop