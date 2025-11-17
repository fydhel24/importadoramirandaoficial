<!-- resources/views/informes/proveedores-pagados.blade.php -->
@extends('adminlte::page')

@section('title', 'Proveedores Pagados')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-check-circle mr-2"></i>Proveedores Pagados</h1>
        <a href="{{ route('informes.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i>Volver
        </a>
    </div>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <h3 class="text-center mb-4">Informe de Proveedores con Pago Completo</h3>
        
        @if($proveedores->isEmpty())
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle mr-2"></i>No hay proveedores con pagos completados.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>Proveedor</th>
                            <th>Código Factura</th>
                            <th>Fecha Registro</th>
                            <th>Deuda Total</th>
                            <th>Último Pago</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($proveedores as $proveedor)
                        <tr>
                            <td>{{ $proveedor->nombre }}</td>
                            <td>{{ $proveedor->codigo_factura }}</td>
                            <td>{{ date('d/m/Y', strtotime($proveedor->fecha_registro)) }}</td>
                            <td>${{ number_format($proveedor->deuda_total, 2) }}</td>
                            <td>
                                @if($proveedor->pagos->count() > 0)
                                    {{ $proveedor->pagos->sortByDesc('fecha_pago')->first()->fecha_pago->format('d/m/Y') }}
                                @else
                                    {{ date('d/m/Y', strtotime($proveedor->fecha_registro)) }} (Pago inicial)
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('proveedores.show', $proveedor->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye mr-1"></i>Ver detalles
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3">Total de pagos completados</th>
                            <th>${{ number_format($totalPagado, 2) }}</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            {{-- <div class="mt-4 text-center">
                <a href="{{ route('informes.proveedores-pagados', ['print' => true]) }}" class="btn btn-info" target="_blank">
                    <i class="fas fa-print mr-1"></i>Imprimir Informe
                </a>
            </div> --}}
        @endif
    </div>
</div>
@stop
