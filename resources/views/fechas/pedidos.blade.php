@extends('adminlte::page')

@section('title', 'Pedidos por Semana')

@section('content_header')
    <h1>Pedidos para la Semana Seleccionada</h1>
@stop

@section('content')
<div class="container">
    <h1>Pedidos de la Semana {{ $id }}</h1>
    <a href="{{ route('orden.create', $id) }}" class="btn btn-success">Crear Pedido</a>
    <table class="table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>CI</th>
                <th>Celular</th>
                <th>Destino</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedidos as $pedido)
            <tr>
                <td>{{ $pedido->nombre }}</td>
                <td>{{ $pedido->ci }}</td>
                <td>{{ $pedido->celular }}</td>
                <td>{{ $pedido->destino }}</td>
                <td>
                    <a href="{{ route('orden.edit', $pedido->id) }}" class="btn btn-warning">Editar</a>
                    <form action="{{ route('orden.destroy', $pedido->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                    
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection