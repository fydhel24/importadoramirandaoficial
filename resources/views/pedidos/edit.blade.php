<!-- resources/views/pedidos/edit.blade.php -->

@extends('adminlte::page')

@section('title', 'Panel de Administración')

@section('content_header')
    <h1></h1>
@stop

@section('content')
    <h1>Editar Pedido {{ $pedido->id }}</h1>

    <form action="{{ route('pedidos.update', $pedido->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-body">
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" value="{{ $pedido->nombre }}" required>
                </div>

                <div class="form-group">
                    <label for="ci">CI:</label>
                    <input type="text" id="ci" name="ci" class="form-control" value="{{ $pedido->ci }}" required>
                </div>

                <div class="form-group">
                    <label for="celular">Celular:</label>
                    <input type="text" id="celular" name="celular" class="form-control" value="{{ $pedido->celular }}" required>
                </div>

                <div class="form-group">
                    <label for="destino">Destino:</label>
                    <input type="text" id="destino" name="destino" class="form-control" value="{{ $pedido->destino }}" required>
                </div>

                <div class="form-group">
                    <label for="direccion">Dirección:</label>
                    <input type="text" id="direccion" name="direccion" class="form-control" value="{{ $pedido->direccion }}" required>
                </div>

                <div class="form-group">
                    <label for="estado">Estado:</label>
                    <input type="text" id="estado" name="estado" class="form-control" value="{{ $pedido->estado }}" required>
                </div>

                <div class="form-group">
                    <label for="cantidad_productos">Cantidad de Productos:</label>
                    <input type="number" id="cantidad_productos" name="cantidad_productos" class="form-control" value="{{ $pedido->cantidad_productos }}" required>
                </div>

                <div class="form-group">
                    <label for="detalle">Detalle:</label>
                    <textarea id="detalle" name="detalle" class="form-control" required>{{ $pedido->detalle }}</textarea>
                </div>

                <div class="form-group">
                    <label for="productos">Productos:</label>
                    <textarea id="productos" name="productos" class="form-control" required>{{ $pedido->productos }}</textarea>
                </div>

                <div class="form-group">
                    <label for="monto_deposito">Monto Depósito:</label>
                    <input type="number" id="monto_deposito" name="monto_deposito" class="form-control" value="{{ $pedido->monto_deposito }}" required>
                </div>

                <div class="form-group">
                    <label for="monto_enviado_pagado">Monto Enviado Pagado:</label>
                    <input type="number" id="monto_enviado_pagado" name="monto_enviado_pagado" class="form-control" value="{{ $pedido->monto_enviado_pagado }}" required>
                </div>

                <div class="form-group">
                    <label for="fecha">Fecha:</label>
                    <input type="date" id="fecha" name="fecha" class="form-control" value="{{ $pedido->fecha }}" required>
                </div>

                <div class="form-group">
                    <label for="id_semana">Semana:</label>
                    <select id="id_semana" name="id_semana" class="form-control" required>
                        @foreach($semanas as $semana)
                            <option value="{{ $semana->id }}" {{ $semana->id == $pedido->id_semana ? 'selected' : '' }}>{{ $semana->nombre }} ({{ $semana->fecha }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="foto_comprobante">Foto Comprobante:</label>
                    <input type="file" id="foto_comprobante" name="foto_comprobante" class="form-control">
                    @if ($pedido->foto_comprobante)
                        <img src="{{ asset('storage/fotos_comprobantes/' . $pedido->foto_comprobante) }}" alt="Foto Comprobante" style="width: 100px; height: auto;">
                    @endif
                </div>

                <div class="form-group">
                    <label for="codigo">Código:</label>
                    <input type="text" id="codigo" name="codigo" class="form-control" value="{{ $pedido->codigo }}" required>
                </div>

                <button type="submit" class="btn btn-primary">Actualizar Pedido</button>
            </div>
        </div>
    </form>
@stop