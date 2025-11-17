@extends('adminlte::page')

@section('title', 'Abrir Caja')

@section('content_header')
    <h1>Abrir Caja</h1>
@stop

@section('content')
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4>Abrir Caja</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('cajas.store', ['id' => $id]) }}" method="POST">
                            @csrf

                            <input type="hidden" name="sucursal_id" value="{{ $id }}">


                            <div class="form-group">
                                <label for="fecha_apertura">Fecha Apertura</label>
                                <input type="datetime-local" name="fecha_apertura" id="fecha_apertura" class="form-control"
                                    value="{{ old('fecha_apertura', now()->format('Y-m-d\TH:i')) }}" required>
                            </div>

                            <div class="form-group">
                                <label for="monto_inicial">Monto Inicial</label>
                                <input type="number" name="monto_inicial" id="monto_inicial" class="form-control"
                                    step="0.01" required>
                            </div>

                            <div class="form-group">
                                <label for="id_user">Usuario</label>
                                <select name="id_user" id="id_user" class="form-control" required>
                                    <option value="">Selecciona un usuario</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}"
                                            {{ old('id_user', $loggedUser->id) == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group text-center">
                                <button type="submit" class="btn btn-success btn-lg w-100">Abrir Caja</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
