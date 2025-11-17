
@extends('adminlte::page')

@section('title', 'Panel de Administración')

@section('content_header')
    <h1>Bienvenido al Panel de Administración</h1>
@stop

@section('content')
    <h1>{{ isset($pedido) ? 'Editar Pedido' : 'Crear Nuevo Pedido' }}</h1>
    <form action="{{ isset($pedido) ? route('pedidos.update', $pedido->id) : route('pedidos.store') }}" method="POST">
        @csrf
        @if(isset($pedido))
            @method('PUT')
        @endif

        <div class="row padding-1 p-1">
            <div class="col-md-12">
                <div class="form-group mb-2 mb20">
                    <label for="nombre" class="form-label">{{ __('Nombre') }}</label>
                    <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $pedido->nombre ?? '') }}" id="nombre" placeholder="Nombre">
                    {!! $errors->first('nombre', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                </div>
                <div class="form-group mb-2 mb20">
                    <label for="ci" class="form-label">{{ __('Ci') }}</label>
                    <input type="text" name="ci" class="form-control @error('ci') is-invalid @enderror" value="{{ old('ci', $pedido->ci ?? '') }}" id="ci" placeholder="Ci">
                    {!! $errors->first('ci', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                </div>
                <div class="form-group mb-2 mb20">
                    <label for="celular" class="form-label">{{ __('Celular') }}</label>
                    <input type="text" name="celular" class="form-control @error('celular') is-invalid @enderror" value="{{ old('celular', $pedido->celular ?? '') }}" id="celular" placeholder="Celular">
                    {!! $errors->first('celular', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                </div>
                <div class="form-group mb-2 mb20">
                    <label for="destino" class="form-label">{{ __('Destino') }}</label>
                    <input type="text" name="destino" class="form-control @error('destino') is-invalid @enderror" value="{{ old('destino', $pedido->destino ?? '') }}" id="destino" placeholder="Destino">
                    {!! $errors->first('destino', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                </div>
                <div class="form-group mb-2 mb20">
                    <label for="direccion" class="form-label">{{ __('Direccion') }}</label>
                    <input type="text" name="direccion" class="form-control @error('direccion') is-invalid @enderror" value="{{ old('direccion', $pedido->direccion ?? '') }}" id="direccion" placeholder="Direccion">
                    {!! $errors->first('direccion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                </div>
                <div class="form-group mb-2 mb20">
                    <label for="estado" class="form-label">{{ __('Estado') }}</label>
                    <input type="text" name="estado" class="form-control @error('estado') is-invalid @enderror" value="{{ old('estado', $pedido->estado ?? '') }}" id="estado" placeholder="Estado">
                    {!! $errors->first('estado', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                </div>
                <div class="form-group mb-2 mb20">
                    <label for="cantidad_productos" class="form-label">{{ __('Cantidad Productos') }}</label>
                    <input type="text" name="cantidad_productos" class="form-control @error('cantidad_productos') is-invalid @enderror" value="{{ old('cantidad_productos', $pedido->cantidad_productos ?? '') }}" id="cantidad_productos" placeholder="Cantidad Productos">
                    {!! $errors->first('cantidad_productos', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                </div>
                <div class="form-group mb-2 mb20">
                    <label for="detalle" class="form-label">{{ __('Detalle') }}</label>
                    <input type="text" name="detalle" class="form-control @error('detalle') is-invalid @enderror" value="{{ old('detalle', $pedido->detalle ?? '') }}" id="detalle" placeholder="Detalle">
                    {!! $errors->first('detalle', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                </div>
                <div class="form-group mb-2 mb20">
                    <label for="productos" class="form-label">{{ __('Productos') }}</label>
                    <input type="text" name="productos" class="form-control @error('productos') is-invalid @enderror" value="{{ old('productos', $pedido->productos ?? '') }}" id="productos" placeholder="Productos">
                    {!! $errors->first('productos', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                </div>
                <div class="form-group mb-2 mb20">
                    <label for="monto_deposito" class="form-label">{{ __('Monto Deposito') }}</label>
                    <input type="text" name="monto_deposito" class="form-control @error('monto_deposito') is-invalid @enderror" value="{{ old('monto_deposito', $pedido->monto_deposito ?? '') }}" id="monto_deposito" placeholder="Monto Deposito">
                    {!! $errors->first('monto_deposito', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                </div>
                <div class="form-group mb-2 mb20">
                    <label for="monto_enviado_pagado" class="form-label">{{ __('Monto Enviado Pagado') }}</label>
                    <input type="text" name="monto_enviado_pagado" class="form-control @error('monto_enviado_pagado') is-invalid @enderror" value="{{ old('monto_enviado_pagado', $pedido->monto_enviado_pagado ?? '') }}" id="monto_enviado_pagado" placeholder="Monto Enviado Pagado">
                    {!! $errors->first('monto_enviado_pagado', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                </div>
                <div class="form-group mb-2 mb20">
                    <label for="fecha" class="form-label">{{ __('Fecha') }}</label>
                    <input type="date" name="fecha" class="form-control @error('fecha') is-invalid @enderror" value="{{ old('fecha', $pedido->fecha ?? '') }}" id="fecha" placeholder="Fecha">
                    {!! $errors->first('fecha', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                </div>
                <div class="form-group mb-2 mb20">
                    <label for="id_semana" class="form-label">{{ __('Semana') }}</label>
                    <select name="id_semana" class="form-control @error('id_semana') is-invalid @enderror" id="id_semana">
                        @foreach ($semanas as $semana)
                            <option value="{{ $semana->id }}" {{ old('id_semana', $pedido->id_semana ?? '') == $semana->id ? 'selected' : '' }}>
                                {{ $semana->nombre }}
                            </option>
                        @endforeach
                    </select>
                    {!! $errors->first('id_semana', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                </div>
            </div>
            <div class="col-md-12 mt20 mt-2">
                <button type="submit" class="btn btn-primary">{{ isset($pedido) ? 'Actualizar' : 'Guardar' }}</button>
            </div>
        </div>
        @if(isset($pedido))
            <input type="hidden" name="id_semana" value="{{ old('id_semana', $pedido->id_semana) }}">
        @endif
    </form>
    <a href="{{ route('pedidos.index') }}">Volver</a>
    @endsection
