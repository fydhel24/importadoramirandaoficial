<!-- resources/views/informes/pagos-mensuales.blade.php -->
@extends('adminlte::page')

@section('title', 'Pagos Mensuales')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-calendar-alt mr-2"></i>Pagos Mensuales</h1>
        <a href="{{ route('informes.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i>Volver
        </a>
    </div>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <form action="{{ route('informes.pagos-mensuales') }}" method="GET" class="form-inline">
            <div class="form-group mr-3">
                <label for="mes" class="mr-2">Mes:</label>
                <select name="mes" id="mes" class="form-control">
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $mes == $i ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="form-group mr-3">
                <label for="anio" class="mr-2">Año:</label>
                <select name="anio" id="anio" class="form-control">
                    @for($i = date('Y'); $i >= date('Y')-5; $i--)
                        <option value="{{ $i }}" {{ $anio == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search mr-1"></i>Consultar
            </button>
        </form>
    </div>
    <div class="card-body">
        <h3 class="text-center mb-4">Informe de Pagos: {{ date('F Y', mktime(0, 0, 0, $mes, 1, $anio)) }}</h3>
        
        @if($pagos->isEmpty())
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle mr-2"></i>No hay pagos registrados para este mes.
            </div>
        @else
            <div class="row">
                <div class="col-md-8">
                    <canvas id="graficoMensual" height="300"></canvas>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-success">
                        <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total del Mes</span>
                            <span class="info-box-number">${{ number_format($totalMes, 2) }}</span>
                        </div>
                    </div>
                    
                    <div class="info-box bg-info">
                        <span class="info-box-icon"><i class="fas fa-calendar-check"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Días con Pagos</span>
                            <span class="info-box-number">{{ $pagosPorDia->count() }}</span>
                        </div>
                    </div>
                    
                    <div class="info-box bg-warning">
                        <span class="info-box-icon"><i class="fas fa-calculator"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Promedio Diario</span>
                            <span class="info-box-number">
                                ${{ number_format($pagosPorDia->count() > 0 ? $totalMes / $pagosPorDia->count() : 0, 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <h4 class="mt-4">Detalle de Pagos por Día</h4>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>Cantidad de Pagos</th>
                            <th>Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pagosPorDia as $dia => $pagosDia)
                        <tr>
                            <td>{{ $dia }}</td>
                            <td>{{ $pagosDia->count() }}</td>
                            <td>${{ number_format($pagosDia->sum('monto_pago'), 2) }}</td>
                            <td>
                                <a href="{{ route('informes.pagos-diarios', ['fecha' => date('Y-m-d', strtotime($dia))]) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye mr-1"></i>Ver detalle
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            
            <!-- En resources/views/informes/pagos-mensuales.blade.php, modifica la sección de botones -->

            {{-- <div class="mt-4 text-center">
                <div class="btn-group">
                    <a href="{{ route('informes.pagos-mensuales', ['mes' => $mes, 'anio' => $anio, 'print' => true]) }}" class="btn btn-info" target="_blank">
                        <i class="fas fa-print mr-1"></i>Imprimir Informe
                    </a>
                    <a href="{{ route('informes.pagos-mensuales.pdf', ['mes' => $mes, 'anio' => $anio]) }}" class="btn btn-danger">
                        <i class="fas fa-file-pdf mr-1"></i>Descargar PDF
                    </a>
                </div>
            </div> --}}



        @endif
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Solo crear gráfico si hay datos
        @if(count($datosDiarios) > 0)
        const ctx = document.getElementById('graficoMensual').getContext('2d');
        const myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_column($datosDiarios, 'dia')) !!},
                datasets: [{
                    label: 'Total de pagos por día ($)',
                    data: {!! json_encode(array_column($datosDiarios, 'total')) !!},
                    backgroundColor: 'rgba(40, 167, 69, 0.6)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value;
                            }
                        }
                    }
                }
            }
        });
        @endif
    });
</script>
@stop