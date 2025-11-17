@extends('adminlte::page')

@section('title', 'Panel de Administración')

@section('content_header')
    <h1>Realizar Inventario</h1>
@stop

@section('content')
<div class="container">
    <h2>Productos Disponibles</h2>

    <!-- Búsqueda de productos -->
    <div class="form-group position-relative">
        <input type="text" id="searchInput" class="form-control pl-5" placeholder="Buscar producto..." />
        <i class="fas fa-search position-absolute" style="left: 10px; top: 10px;"></i>
        <ul id="suggestions" class="list-group position-absolute w-100" style="z-index: 1000; display: none;"></ul>
    </div>

    <table class="table table-striped table-bordered" id="productosTable">
        <thead class="thead-dark">
            <tr>
                <th>Nombre</th>
                <th>Stock </th>
                <th>Agregar Cantidad</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productosDisponibles as $producto)
                @php
                    $isStockLow = $producto['Stock'] <= 0;
                @endphp
                <tr class="producto-row" data-nombre="{{ strtolower($producto['nombre']) }}">
                    <td>{{ $producto['nombre'] }}</td>
                    <td>
                        <span class="{{ $isStockLow ? 'text-danger' : '' }}">
                            {{ $producto['Stock'] }}
                            @if ($isStockLow)
                                <i class="fas fa-exclamation-circle text-danger" title="Stock agotado o bajo">¡Stock agotado o bajo!</i>
                            @endif
                        </span>
                    </td>
        
                    <td>
                        @if ($producto['Stock'] > 0)
                            <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalAgregarCantidad"
                                    data-id="{{ $producto['id'] }}" data-nombre="{{ $producto['nombre'] }}"
                                    data-stock-restante="{{ $producto['Stock'] }}">
                                <i class="fas fa-plus"></i> Agregar Cantidad
                            </button>
                        @else
                            <button class="btn btn-light btn-sm" disabled>
                                <i class="fas fa-times"></i> Sin stock disponible
                            </button>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Modal para agregar cantidad -->
    <div class="modal fade" id="modalAgregarCantidad" tabindex="-1" role="dialog" aria-labelledby="modalAgregarLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Agregar Cantidad de <span id="productoNombre"></span></h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formAgregarCantidad" action="{{ route('control.inventario', $id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="id_producto" id="id_producto">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="cantidad">Cantidad</label>
                            <input type="number" name="cantidad" id="cantidad" class="form-control" min="1" required>
                            <small id="stockRestanteMsg" class="form-text text-muted"></small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-success">Agregar <i class="fas fa-check"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Mensajes de errores y éxito -->
    @if($errors->any())
        <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
            <strong>Error:</strong> {{ $errors->first() }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if(session('success'))
        <div class="alert alert-success mt-3 alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
</div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            // Manejar la apertura del modal
            $('#modalAgregarCantidad').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget); // El botón que activó el modal
                var id = button.data('id'); // ID del producto
                var nombre = button.data('nombre');
                var stockRestante = button.data('stock-restante'); // Stock restante del producto

                var modal = $(this);
                modal.find('#id_producto').val(id);
                modal.find('#productoNombre').text(nombre);
                modal.find('#stockRestanteMsg').text('Stock disponible: ' + stockRestante);

                // Validar la cantidad en base al stock restante
                $('#formAgregarCantidad').on('submit', function(event) {
                    var cantidad = $('#cantidad').val();
                    if (cantidad > stockRestante) {
                        event.preventDefault(); // Detener el envío del formulario
                        alert('La cantidad solicitada supera el stock disponible.');
                    }
                });
            });

            // Filtrar productos por nombre
            $('#searchInput').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                var rows = $('#productosTable tbody tr');
                rows.each(function() {
                    var nombre = $(this).data('nombre');
                    if (nombre.indexOf(value) > -1) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });

                // Mostrar sugerencias
                var $suggestions = $('#suggestions');
                $suggestions.empty();
                if (value.length > 0) {
                    rows.each(function() {
                        if ($(this).is(':hidden')) return;
                        var suggestion = $(this).find('td:first').text();
                        $suggestions.append('<li class="list-group-item">' + suggestion + '</li>');
                    });
                    $suggestions.show();
                } else {
                    $suggestions.hide();
                }
            });

            // Ocultar sugerencias al hacer clic
            $(document).on('click', '#suggestions .list-group-item', function() {
                $('#searchInput').val($(this).text());
                $('#suggestions').hide();
            });
        });
    </script>
    <style>
        .producto-row:hover {
            background-color: #f9f9f9;
        }
        #suggestions {
            max-height: 200px;
            overflow-y: auto;
        }
        .text-danger {
            font-weight: bold;
        }
    </style>
@stop