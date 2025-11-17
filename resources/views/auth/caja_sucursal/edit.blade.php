@extends('adminlte::page')

@section('title', 'Editar Reporte de Caja Sucursal')

@section('content')
    <div class="container">
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h2 class="card-title">Editar Reporte de Caja Sucursal - Importadora Miranda</h2>
            </div>
            <div class="card-body">
                <form action="{{ route('caja_sucursal.update', $fechaSucursal->id) }}" method="POST">
                    @csrf
                    @method('PUT') <!-- Usamos el método PUT para actualizar -->

                    <div class="form-group" hidden>
                        <label for="fecha_inicio" class="mr-2 font-weight-bold">Fecha Inicio:</label>
                        <input type="datetime-local" id="fecha_inicio" name="fecha_inicio" class="form-control"
                            value="{{ old('fecha_inicio', \Carbon\Carbon::parse($fechaSucursal->fecha_inicio)->format('Y-m-d\TH:i')) }}">
                    </div>

                    <div class="form-group" hidden>
                        <label for="fecha_fin" class="mr-2 font-weight-bold">Fecha Fin:</label>
                        <input type="datetime-local" id="fecha_fin" name="fecha_fin" class="form-control"
                            value="{{ old('fecha_fin', \Carbon\Carbon::parse($fechaSucursal->fecha_fin)->format('Y-m-d\TH:i')) }}">
                    </div>

                    <!-- Mostrar las fechas seleccionadas como texto -->
                    <div class="alert alert-info">
                        <strong>Fecha Inicio:</strong>
                        {{ \Carbon\Carbon::parse($fechaSucursal->fecha_inicio)->format('d/m/Y H:i') }} <br>
                        <strong>Fecha Fin:</strong>
                        {{ \Carbon\Carbon::parse($fechaSucursal->fecha_fin)->format('d/m/Y H:i') }}
                    </div>

                    <!-- Detalles -->
                    <div class="form-group">
                        <label for="detalle" class="font-weight-bold">Detalle:</label>
                        <textarea id="detalle" name="detalle" class="form-control" rows="3">{{ old('detalle', $fechaSucursal->detalle) }}</textarea>
                    </div>

                    <h3 class="mt-4">Sucursales y Totales:</h3>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Sucursal</th>
                                <th>Total Vendido</th>
                                <th>Total QR</th>
                                <th>Total Efectivo</th>
                                <th>QR Oficial</th>
                                <th>Efectivo Oficial</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sucursales as $sucursal)
                                <tr>
                                    <td>{{ $sucursal->nombre }}</td>
                                    <td>{{ number_format($totalesPorSucursal[$sucursal->id]['total_vendido'] ?? 0, 2) }}
                                    </td>
                                    <td>{{ number_format($totalesPorSucursal[$sucursal->id]['total_qr'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($totalesPorSucursal[$sucursal->id]['total_efectivo'] ?? 0, 2) }}
                                    </td>
                                    <td>
                                        <input type="number" name="sucursales[{{ $sucursal->id }}][qr_oficial]"
                                            class="form-control"
                                            value="{{ old('sucursales.' . $sucursal->id . '.qr_oficial', $totalesPorSucursal[$sucursal->id]['qr_oficial'] ?? 0) }}">
                                    </td>
                                    <td>
                                        <input type="number" name="sucursales[{{ $sucursal->id }}][efectivo_oficial]"
                                            class="form-control"
                                            value="{{ old('sucursales.' . $sucursal->id . '.efectivo_oficial', $totalesPorSucursal[$sucursal->id]['efectivo_oficial'] ?? 0) }}">
                                    </td>
                                    <input type="hidden" name="sucursales[{{ $sucursal->id }}][total_vendido]"
                                        value="{{ $totalesPorSucursal[$sucursal->id]['total_vendido'] ?? 0 }}">
                                    <input type="hidden" name="sucursales[{{ $sucursal->id }}][total_qr]"
                                        value="{{ $totalesPorSucursal[$sucursal->id]['total_qr'] ?? 0 }}">
                                    <input type="hidden" name="sucursales[{{ $sucursal->id }}][total_efectivo]"
                                        value="{{ $totalesPorSucursal[$sucursal->id]['total_efectivo'] ?? 0 }}">
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Botón para guardar los cambios -->
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Actualizar
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
