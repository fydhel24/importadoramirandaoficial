@extends('adminlte::page')

@section('title', 'Detalles del Proveedor')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-user-tie mr-2"></i>Detalles del Proveedor</h1>
        <div>
            <a href="{{ route('proveedores.edit', $proveedor->id) }}" class="btn btn-warning mr-2">
                <i class="fas fa-edit mr-1"></i>Editar
            </a>
            <a href="{{ route('proveedores.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-7">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">Información del Proveedor</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach ([ 
                            'Nombre del Proveedor' => $proveedor->nombre,
                            'Código de Factura' => $proveedor->codigo_factura,
                            'Fecha de Registro' => date('d/m/Y', strtotime($proveedor->fecha_registro)),
                        ] as $label => $value)
                            <div class="col-md-6 mb-3">
                                <div class="info-box bg-light shadow-sm rounded p-3">
                                    <div class="info-box-content">
                                        <span class="info-box-text text-muted">{{ $label }}</span>
                                        <span class="info-box-number font-weight-bold">{{ $value }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        @if($proveedor->foto_factura)
                            <div class="col-md-6 mb-3">
                                <div class="info-box bg-light shadow-sm rounded p-3">
                                    <div class="info-box-content">
                                        <span class="info-box-text text-muted">Factura</span>
                                        <a href="{{ Storage::url($proveedor->foto_factura) }}" target="_blank" class="btn btn-info btn-sm">
                                            Ver Factura
                                        </a>
                                        
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="col-md-6 mb-3">
                            <div class="info-box bg-light shadow-sm rounded p-3">
                                <div class="info-box-content">
                                    <span class="info-box-text text-muted">Estado</span>
                                    <span class="info-box-number">
                                        <span class="badge badge-{{ $proveedor->estado == 'Pagado' ? 'success' : 'warning' }} p-2">
                                            <i class="fas {{ $proveedor->estado == 'Pagado' ? 'fa-check-circle' : 'fa-clock' }} mr-1"></i>
                                            {{ $proveedor->estado == 'Pagado' ? 'Pagado' : 'Saldo pendiente' }}
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-lg mt-4">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">Información Financiera</h3>
                </div>
                <div class="card-body">
                    @php
                        $pagosTotales = $proveedor->pagos()->sum('monto_pago') + $proveedor->pago_inicial;
                        $saldoPendiente = max(0, $proveedor->deuda_total - $pagosTotales);
                        $porcentajePagado = $proveedor->deuda_total > 0 ? round(($pagosTotales / $proveedor->deuda_total) * 100) : 0;
                    @endphp

                    <div class="row">
                        @foreach ([ 
                            'Deuda Total' => $proveedor->deuda_total,
                            'Pago Inicial' => $proveedor->pago_inicial,
                            'Total Pagado' => $pagosTotales,
                            'Saldo Pendiente' => $saldoPendiente
                        ] as $label => $value)
                            <div class="col-md-6 mb-3">
                                <div class="info-box bg-light shadow-sm rounded p-3">
                                    <div class="info-box-content">
                                        <span class="info-box-text text-muted">{{ $label }}</span>
                                        <span class="info-box-number font-weight-bold">${{ number_format($value, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <h5 class="mt-3 mb-2">Progreso de Pago</h5>
                    <div class="progress mb-3">
                        <div class="progress-bar bg-success progress-bar-striped" role="progressbar" style="width: {{ $porcentajePagado }}%" aria-valuenow="{{ $porcentajePagado }}" aria-valuemin="0" aria-valuemax="100">
                            {{ $porcentajePagado }}%
                        </div>
                    </div>

                    @if($saldoPendiente > 0)
                        <div class="text-center mt-4">
                            <a href="{{ route('pagos.create', ['proveedor_id' => $proveedor->id]) }}" class="btn btn-success">
                                <i class="fas fa-money-bill-wave mr-1"></i>Registrar Nuevo Pago
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
