<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\PagoProveedorController;

// Rutas para la gestión de proveedores
Route::resource('proveedores', ProveedorController::class);

// Rutas para la gestión de pagos de proveedores
Route::resource('pagos', PagoProveedorController::class)->except(['edit', 'update', 'destroy']);

?>

@extends('adminlte::page')

@section('title', 'Gestión de Proveedores')

{{-- @section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-user-tie mr-2"></i>Gestión de Proveedores</h1>
        <a href="{{ route('proveedores.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i>Nuevo Proveedor
        </a>
    </div>
@stop --}}

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-user-tie mr-2"></i>Gestión de Proveedores</h1>
        <div>
            <a href="{{ route('informes.index') }}" class="btn btn-info mr-2">
                <i class="fas fa-chart-line mr-1"></i>Informes
            </a>
            <a href="{{ route('proveedores.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i>Nuevo Proveedor
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            
            @if($proveedores->isEmpty())
                <div class="alert alert-info">
                    <i class="icon fas fa-info-circle"></i> No hay proveedores registrados actualmente.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>Nombre</th>
                                <th>Código Factura</th>
                                <th>Fecha Registro</th>
                                <th>Deuda Total</th>
                                <th>Saldo Pendiente</th>
                                <th>Estado</th>
                                <th width="150px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($proveedores as $proveedor)
                                <tr>
                                    <td>{{ $proveedor->nombre }}</td>
                                    <td>{{ $proveedor->codigo_factura }}</td>
                                    <td>{{ $proveedor->fecha_registro->format('d/m/Y') }}</td>
                                    <td>${{ number_format($proveedor->deuda_total, 2) }}</td>
                                    <td>${{ number_format($proveedor->saldo_pendiente, 2) }}</td>
                                    <td>
                                        <span class="badge {{ $proveedor->estado == 'Pagado' ? 'badge-success' : 'badge-warning' }}">
                                            <i class="fas {{ $proveedor->estado == 'Pagado' ? 'fa-check-circle' : 'fa-clock' }} mr-1"></i>{{ $proveedor->estado }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('proveedores.show', $proveedor) }}" class="btn btn-xs btn-info" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('proveedores.edit', $proveedor) }}" class="btn btn-xs btn-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($proveedor->pagos->isEmpty())
                                                <!-- Botón para eliminar proveedor -->
<!-- Botón para eliminar proveedor -->
<button type="button" class="btn btn-xs btn-danger" data-toggle="modal" data-target="#deleteModal-{{ $proveedor->id }}" title="Eliminar">
    <i class="fas fa-trash"></i>
</button>

<!-- Modal de confirmación -->
<div class="modal fade" id="deleteModal-{{ $proveedor->id }}" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-center">¿Estás seguro de que deseas eliminar a este proveedor?</p>
                <p class="text-center text-warning"><strong>Esta acción es irreversible.</strong></p>
            </div>
            <div class="modal-footer justify-content-center">
                <!-- Formulario de eliminación -->
                <form action="{{ route('proveedores.destroy', $proveedor->id) }}" method="POST" class="w-100">
                    @csrf
                    @method('DELETE')
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


                                            @else
                                                <button class="btn btn-xs btn-secondary" disabled title="No se puede eliminar con pagos asociados">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                            @if($proveedor->saldo_pendiente > 0)
                                                <a href="{{ route('pagos.create', ['proveedor_id' => $proveedor->id]) }}" class="btn btn-xs btn-success" title="Registrar pago">
                                                    <i class="fas fa-money-bill-wave"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-4">
                    {{ $proveedores->links() }}
                </div>
            @endif
        </div>
    </div>
@stop

@section('css')
    <style>
        .table td, .table th {
            vertical-align: middle;
        }
    </style>
    <style>
        .modal-content {
            border-radius: 15px; /* Bordes redondeados */
            box-shadow: 0 4px 8px rgba(0,0,0,0.2); /* Sombra para dar profundidad */
        }

        .modal-header {
            background-color: #f8d7da; /* Fondo rojo claro */
            border-bottom: 2px solid #f5c6cb; /* Ligeramente más oscuro */
        }

        .modal-title {
            color: #721c24; /* Texto rojo */
            font-weight: bold;
        }

        .modal-body {
            font-size: 16px;
            color: #495057; /* Color más suave para el texto */
        }

        .modal-footer button {
            width: 48%; /* Botones ocupan la mitad del ancho */
        }

        .modal-footer .btn-outline-secondary {
            background-color: #e2e3e5; /* Color suave de fondo */
        }
    </style>
@stop

@section('js')

<script>
    $(document).ready(function () {
        // Auto-cierre del modal después de la eliminación si se desea
        $('form').on('submit', function () {
            $('#deleteModal').modal('hide');
        });
    });
</script>

    <script>
       $(document).ready(function () {
            setTimeout(function () {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
    </script>
@stop
