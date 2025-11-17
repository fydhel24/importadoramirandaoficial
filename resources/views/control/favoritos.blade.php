@extends('adminlte::page')

@section('title', 'Favoritos')

@section('content_header')
    <h1 class="text-center text-dark">Añadir Favoritos</h1>
@stop

@section('content')
    <div class="container mt-5">
        <h2 class="text-center mb-4 text-primary">Productos Disponibles</h2>

        <!-- Búsqueda de productos -->
        <div class="form-group position-relative mb-4">
            <input type="text" id="searchInput" class="form-control pl-5" placeholder="Buscar producto..."
                style="border-radius: 25px; padding-left: 40px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); transition: box-shadow 0.3s;">
            <i class="fas fa-search position-absolute" style="left: 10px; top: 10px; color: #6c757d;"></i>
            <ul id="suggestions" class="list-group position-absolute w-100 mt-1"
                style="z-index: 1000; display: none; border-radius: 5px; background-color: #f8f9fa;">
            </ul>
        </div>

        <!-- Tabla de productos -->
        <table class="table table-striped table-hover table-bordered" id="productosTable"
            style="border-radius: 8px; border: 1px solid #ddd;">
            <thead class="thead-dark" style="background-color: #343a40; color: white;">
                <tr>
                    <th>Nombre</th>
                    <th class="text-center">Favorito</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($productosDisponibles as $producto)
                    <tr class="producto-row" data-nombre="{{ strtolower($producto['nombre']) }}">
                        <td>{{ $producto['nombre'] }}</td>
                        <td class="text-center">
                            <button class="btn btn-sm toggle-favorite" data-producto-id="{{ $producto['id'] }}"
                                style="border-radius: 25px; padding: 8px 20px; transition: all 0.3s; min-width: 180px;"
                                data-favorito="{{ $producto['favorito'] }}">
                                @if ($producto['favorito'])
                                    <i class="fas fa-minus"></i> Quitar favorito
                                @else
                                    <i class="fas fa-plus"></i> Agregar favorito
                                @endif
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@stop

@section('css')
    <style>
        /* Estilos generales */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f6f9;
        }

        .container {
            max-width: 1200px;
        }

        .text-primary {
            color: #007bff !important;
        }

        .form-control {
            border-radius: 25px;
            font-size: 16px;
            padding: 10px 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
        }

        .table th {
            background-color: #343a40;
            color: white;
            font-weight: bold;
        }

        .table td {
            font-size: 16px;
            vertical-align: middle;
            padding: 10px;
        }

        .table-hover tbody tr:hover {
            background-color: #f1f1f1;
        }

        .table-bordered {
            border: 1px solid #ddd;
        }

        /* Estilo de botones */
        .toggle-favorite {
            min-width: 180px;
            padding: 8px 20px;
            border-radius: 25px;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 600;
        }

        .toggle-favorite i {
            margin-right: 8px;
        }

        .toggle-favorite:hover {
            transform: scale(1.05);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .toggle-favorite.btn-success {
            background-color: #28a745;
            color: white;
        }

        .toggle-favorite.btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .toggle-favorite.btn-success:hover {
            background-color: #218838;
        }

        .toggle-favorite.btn-danger:hover {
            background-color: #c82333;
        }

        /* Estilo para las sugerencias de búsqueda */
        #suggestions .list-group-item {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            cursor: pointer;
        }

        #suggestions .list-group-item:hover {
            background-color: #007bff;
            color: white;
        }
    </style>
@stop

@section('js')
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
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
                        $suggestions.append(
                            '<li class="list-group-item" style="border-radius: 5px;">' +
                            suggestion + '</li>');
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

            // Evento de clic para agregar o quitar un favorito
            $('.toggle-favorite').each(function() {
                var isFavorito = $(this).data('favorito');
                if (isFavorito) {
                    $(this).addClass('btn-danger').removeClass('btn-success');
                    $(this).html('<i class="fas fa-minus"></i> Quitar favorito');
                } else {
                    $(this).addClass('btn-success').removeClass('btn-danger');
                    $(this).html('<i class="fas fa-plus"></i> Agregar favorito');
                }
            });

            // Evento de clic para agregar o quitar un favorito
            $('.toggle-favorite').on('click', function() {
                var productoId = $(this).data('producto-id');
                var userId = {{ auth()->user()->id }};
                var isFavorito = $(this).find('i').hasClass(
                'fa-plus'); // Verifica si es para agregar o quitar

                // Determina la URL y el tipo de acción
                var url = isFavorito ?
                    '/control/sucursal/' + {{ $id }} + '/favoritos' :
                    '/control/sucursal/' + {{ $id }} + '/favoritos/quitar';

                var button = $(this);

                // Realizar la solicitud AJAX para agregar o quitar favorito
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        producto_id: productoId,
                        user_id: userId
                    },
                    success: function(response) {
                        Swal.fire({
                            title: '¡Éxito!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: 'Aceptar'
                        });

                        // Cambiar el texto y el icono del botón
                        if (isFavorito) {
                            button.html('<i class="fas fa-minus"></i> Quitar favorito');
                        } else {
                            button.html('<i class="fas fa-plus"></i> Agregar favorito');
                        }

                        // Actualizar visualmente el estado del botón en función del estado
                        button.toggleClass('btn-success btn-danger');
                        //location.reload();
                    },
                    error: function(xhr) {
                        var response = xhr.responseJSON;
                        Swal.fire({
                            title: 'Error',
                            text: response.message ||
                                'Hubo un error al actualizar el producto.',
                            icon: 'error',
                            confirmButtonText: 'Aceptar'
                        });
                    }
                });
            });
        });
    </script>
@stop
