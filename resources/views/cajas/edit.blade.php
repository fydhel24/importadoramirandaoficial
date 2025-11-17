@extends('adminlte::page')

@section('title', 'Cierre Caja')

@section('content_header')
    <h1>Cerrar Caja</h1>
@stop

@section('content')
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4>Formulario de Cierre de Caja</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('cajas.update', ['caja' => $caja->id, 'id' => $caja->sucursal_id]) }}"
                            method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row text-center">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="fecha_apertura">Fecha Apertura</label>
                                        <input type="datetime-local" name="fecha_apertura" id="fecha_apertura"
                                            class="form-control" value="{{ $caja->fecha_apertura->format('Y-m-d\TH:i') }}"
                                            required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="fecha_cierre">Fecha Cierre</label>
                                        <input type="datetime-local" name="fecha_cierre" id="fecha_cierre"
                                            class="form-control" value="{{ $caja->fecha_cierre->format('Y-m-d\TH:i') }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="id_user">Usuario Apertura</label>
                                        <select name="id_user" id="id_user" class="form-control" required>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}"
                                                    {{ $caja->id_user == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="id_user_cierre">Usuario Cierre</label>
                                        <select name="id_user_cierre" id="id_user_cierre" class="form-control" required>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}"
                                                    {{ $caja->id_user == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row text-center">

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="monto_inicial">Monto Inicial</label>
                                            <input type="number" name="monto_inicial" id="monto_inicial"
                                                class="form-control"
                                                value="{{ old('monto_inicial', $caja->monto_inicial) }}" step="0.01"
                                                required>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="efectivo_inicial">Efectivo Inicial</label>
                                            <input type="number" name="efectivo_inicial" id="efectivo_inicial"
                                                class="form-control"
                                                value="{{ old('efectivo_inicial', $caja->efectivo_inicial) }}"
                                                step="0.01" required>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="qr_inicial">QR Inicial</label>
                                            <input type="number" name="qr_inicial" id="qr_inicial" class="form-control"
                                                value="{{ old('qr_inicial', $caja->qr_inicial) }}" step="0.01" required>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="monto_vendido">Monto Total Vendido</label>
                                            <input type="number" id="monto_vendido" class="form-control"
                                                value="{{ old('monto_total', $montoTotal) }}" step="0.01" readonly>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="total_efectivo">Total Efectivo</label>
                                            <input type="number" name="total_efectivo" id="total_efectivo"
                                                class="form-control" value="{{ old('total_efectivo', $totalEfectivo) }}"
                                                step="0.01" required readonly>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="total_qr">Total QR</label>
                                            <input type="number" name="total_qr" id="total_qr" class="form-control"
                                                value="{{ old('total_qr', $totalQr) }}" step="0.01" required readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="monto_total">Monto Total</label>
                                        <input type="number" name="monto_total" id="monto_total" class="form-control"
                                            step="0.01" required readonly>
                                    </div>
                                </div>

                            </div>
                            <!-- Campo oculto para mantener la relación con la sucursal -->
                            <input type="hidden" name="sucursal_id" value="{{ $caja->sucursal_id }}">
                            <div class="form-group text-center mt-3">
                                <button type="submit" class="btn btn-success btn-lg w-100">Guardar</button>
                            </div>
                            <div class="form-group text-center">
                                <a href="{{ route('cajas.index', ['id' => $caja->sucursal_id]) }}"
                                    class="btn btn-secondary w-100">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const montoInicial = document.getElementById("monto_inicial");
            const montoVendido = document.getElementById("monto_vendido");
            const montoTotal = document.getElementById("monto_total");

            function calcularMontoTotal() {
                let inicial = parseFloat(montoInicial.value) || 0;
                let vendido = parseFloat(montoVendido.value) || 0;
                montoTotal.value = (vendido + inicial).toFixed(2);
            }

            // Escuchar cambios en el campo de monto inicial
            montoInicial.addEventListener("input", calcularMontoTotal);

            // Calcular al inicio en caso de que haya valores cargados
            calcularMontoTotal();
        });
    </script>

@stop
