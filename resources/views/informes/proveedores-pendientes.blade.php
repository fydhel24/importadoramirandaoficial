<!-- resources/views/informes/proveedores-pendientes.blade.php -->
@extends('adminlte::page')

@section('title', 'Proveedores con Saldo Pendiente')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-exclamation-circle mr-2"></i>Proveedores con Saldo Pendiente</h1>
        <a href="{{ route('informes.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i>Volver
        </a>
    </div>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <h3 class="text-center mb-4">Informe de Proveedores con Saldo Pendiente</h3>
        
        @if($proveedores->isEmpty())
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle mr-2"></i>No hay proveedores con saldo pendiente.
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
                            <th>Saldo Pendiente</th>
                            <th>% Pagado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($proveedores as $proveedor)
                        @php
                            $pagado = $proveedor->deuda_total - $proveedor->saldo_pendiente;
                            $porcentaje = $proveedor->deuda_total > 0 ? round(($pagado / $proveedor->deuda_total) * 100) : 0;
                        @endphp
                        <tr>
                            <td>{{ $proveedor->nombre }}</td>
                            <td>{{ $proveedor->codigo_factura }}</td>
                            <td>{{ date('d/m/Y', strtotime($proveedor->fecha_registro)) }}</td>
                            <td>${{ number_format($proveedor->deuda_total, 2) }}</td>
                            <td>${{ number_format($proveedor->saldo_pendiente, 2) }}</td>
                            <td>
                                <div class="progress">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $porcentaje }}%">
                                        {{ $porcentaje }}%
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('proveedores.show', $proveedor->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('pagos.create', ['proveedor_id' => $proveedor->id]) }}" class="btn btn-sm btn-success">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4">Total pendiente de pago</th>
                            <th>${{ number_format($totalPendiente, 2) }}</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            {{-- <div class="mt-4 text-center">
                <a href="{{ route('informes.proveedores-pendientes', ['print' => true]) }}" class="btn btn-info" target="_blank">
                    <i class="fas fa-print mr-1"></i>Imprimir Informe
                </a>
            </div> --}}
        @endif
    </div>
</div>
@stop