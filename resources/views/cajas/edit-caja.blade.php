@extends('adminlte::page')

@section('title', 'Editar Caja')

@section('content_header')
    <h1>Editar Caja</h1>
@stop

@section('content')
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-dark">
                        <h4>Formulario de Edición de Caja</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('cajas.updateEdit', ['caja' => $caja->id, 'id' => $id]) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="fecha_apertura">Fecha Apertura</label>
                                        <input type="datetime-local" name="fecha_apertura" id="fecha_apertura"
                                            class="form-control" 
                                            value="{{ old('fecha_apertura', $caja->fecha_apertura->format('Y-m-d\TH:i')) }}"
                                            required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="id_user">Usuario Apertura</label>
                                        <select name="id_user" id="id_user" class="form-control" required>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}"
                                                    {{ old('id_user', $caja->id_user) == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="monto_inicial">Monto Inicial</label>
                                        <input type="number" name="monto_inicial" id="monto_inicial" class="form-control"
                                            value="{{ old('monto_inicial', $caja->monto_inicial) }}" step="0.01"
                                            required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="efectivo_inicial">Efectivo Inicial</label>
                                        <input type="number" name="efectivo_inicial" id="efectivo_inicial"
                                            class="form-control"
                                            value="{{ old('efectivo_inicial', $caja->efectivo_inicial) }}" step="0.01"
                                            required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="qr_inicial">QR Inicial</label>
                                        <input type="number" name="qr_inicial" id="qr_inicial" class="form-control"
                                            value="{{ old('qr_inicial', $caja->qr_inicial) }}" step="0.01" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Campo oculto para mantener la relación con la sucursal -->
                            <input type="hidden" name="sucursal_id" value="{{ $id }}">

                            <div class="form-group text-center mt-3">
                                <button type="submit" class="btn btn-info btn-lg w-100">Guardar Edición</button>
                            </div>
                            <div class="form-group text-center">
                                <a href="{{ route('cajas.index', ['id' => $id]) }}" 
                                    class="btn btn-secondary w-100">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
