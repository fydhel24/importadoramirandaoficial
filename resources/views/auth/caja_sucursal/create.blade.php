@extends('adminlte::page')

@section('title', 'Crear Reporte de Caja Sucursal')

@section('content')
    <div class="container">
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h2 class="card-title">Crear Reporte de Caja Sucursal - Importadora Miranda</h2>
            </div>
            <div class="card-body">
                <form action="{{ route('caja_sucursal.create') }}" method="GET">
                    @csrf
                    <div class="form-group">
                        <label for="fecha_inicio" class="mr-2 font-weight-bold">Fecha Inicio:</label>
                        <input type="datetime-local" id="fecha_inicio" name="fecha_inicio" class="form-control"
                            value="{{ old('fecha_inicio', $fechaInicio) }}">
                    </div>
                    <div class="form-group">
                        <label for="fecha_fin" class="mr-2 font-weight-bold">Fecha Fin:</label>
                        <input type="datetime-local" id="fecha_fin" name="fecha_fin" class="form-control"
                            value="{{ old('fecha_fin', $fechaFin) }}">
                    </div>
                    <button type="submit" class="btn btn-primary font-weight-bold">
                        <i class="fas fa-save"></i> Filtrar
                    </button>
                </form>


                <h3 class="mt-4">Resultados Filtrados:</h3>
                <form action="{{ route('caja_sucursal.store') }}" method="POST">
                    @csrf
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Sucursales</th>
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
                                            value="{{ old('sucursales.' . $sucursal->id . '.qr_oficial', 0) }}">
                                    </td>
                                    <td>
                                        <input type="number" name="sucursales[{{ $sucursal->id }}][efectivo_oficial]"
                                            class="form-control"
                                            value="{{ old('sucursales.' . $sucursal->id . '.efectivo_oficial', 0) }}">
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

                    <!-- Campo para ingresar detalles -->
                    <div class="form-group mt-3">
                        <label for="detalle" class="font-weight-bold">Detalle:</label>
                        <textarea id="detalle" name="detalle" class="form-control" rows="3" placeholder="Ingrese detalles aquí..."></textarea>
                    </div>

                    <!-- Campos ocultos para las fechas -->
                    <input type="hidden" name="fecha_inicio"
                        value="{{ \Carbon\Carbon::parse($fechaInicio)->toDateTimeString() }}">
                    <input type="hidden" name="fecha_fin"
                        value="{{ \Carbon\Carbon::parse($fechaFin)->toDateTimeString() }}">

                    <!-- Mostrar las fechas seleccionadas -->
                    <div class="alert alert-info">
                        <strong>Fecha Inicio:</strong> {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y H:i') }} <br>
                        <strong>Fecha Fin:</strong> {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y H:i') }}
                    </div>

                    <!-- Botón para guardar los datos -->
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </form>

            </div>
        </div>
    </div>
@endsection
