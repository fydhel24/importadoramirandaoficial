@extends('adminlte::page')

@section('title', 'Panel de Administración')

@section('content_header')
    <h1>neuvo al Panel de Administración</h1>
@stop

@section('content')
<a href="">asdasd</a>
<div class="container">
    <div class="row">
        @foreach($semanas as $semana)
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ $semana->nombre }}</h5>
                    <p class="card-text">{{ $semana->fecha }}</p>
                    <a href="{{ route('orden.pedidos', $semana->id) }}" class="btn btn-primary">Ver Pedidos</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
