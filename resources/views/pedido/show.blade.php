@extends('adminlte::page')

@section('title', 'Panel de Administración')

@section('content_header')
    <h1>Bienvenido al Panel de Administración</h1>
@stop
@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Pedido</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('pedidos.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                                <div class="form-group mb-2 mb20">
                                    <strong>Nombre:</strong>
                                    {{ $pedido->nombre }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Ci:</strong>
                                    {{ $pedido->ci }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Celular:</strong>
                                    {{ $pedido->celular }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Destino:</strong>
                                    {{ $pedido->destino }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Direccion:</strong>
                                    {{ $pedido->direccion }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Estado:</strong>
                                    {{ $pedido->estado }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Cantidad Productos:</strong>
                                    {{ $pedido->cantidad_productos }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Detalle:</strong>
                                    {{ $pedido->detalle }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Productos:</strong>
                                    {{ $pedido->productos }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Monto Deposito:</strong>
                                    {{ $pedido->monto_deposito }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Monto Enviado Pagado:</strong>
                                    {{ $pedido->monto_enviado_pagado }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Fecha:</strong>
                                    {{ $pedido->fecha }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Id Semana:</strong>
                                    {{ $pedido->id_semana }}
                                </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
