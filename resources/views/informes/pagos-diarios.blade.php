<!-- resources/views/informes/pagos-diarios.blade.php -->
@extends('adminlte::page')

@section('title', 'Pagos Diarios')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-calendar-day mr-2"></i>Pagos Diarios</h1>
        <a href="{{ route('informes.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i>Volver
        </a>
    </div>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <form action="{{ route('informes.pagos-diarios') }}" method="GET" class="form-inline">
            <div class="form-group mr-3">
                <label for="fecha" class="mr-2">Seleccionar fecha:</label>
                <input type="date" name="fecha" id="fecha" class="form-control" value="{{ $fecha }}">
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search mr-1"></i>Consultar
            </button>
        </form>
    </div>
    <div class="card-body">
        <h3 class="text-center mb-4">Informe de Pagos: {{ date('d/m/Y', strtotime($fecha)) }}</h3>
        
        @if($pagos->isEmpty())
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle mr-2"></i>No hay pagos registrados para esta fecha.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>Proveedor</th>
                            <th>Monto</th>
                            <th>Código Factura</th>
                            <th>Hora de Registro</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pagos as $pago)
                        <tr>
                            <td>{{ $pago->proveedor->nombre }}</td>
                            <td>${{ number_format($pago->monto_pago, 2) }}</td>
                            <td>{{ $pago->proveedor->codigo_factura }}</td>
                            <td>{{ $pago->created_at->format('H:i:s') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Total</th>
                            <th colspan="3">${{ number_format($total, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            {{-- <div class="mt-4 text-center">
                <a href="{{ route('informes.pagos-diarios', ['fecha' => $fecha, 'print' => true]) }}" class="btn btn-info" target="_blank">
                    <i class="fas fa-print mr-1"></i>Imprimir Informe
                </a>
            </div> --}}
        @endif
    </div>
</div>
@stop