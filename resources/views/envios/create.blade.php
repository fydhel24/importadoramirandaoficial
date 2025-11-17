@extends('adminlte::page')

@section('plugins.Select2', true)
@section('plugins.Sweetalert2', true)
@section('plugins.Datatables', true)

@section('css')
    <style>
        #lista-productos {
            max-height: 200px;
            /* Controla la altura máxima */
            overflow-y: auto;
            /* Habilita el desplazamiento si hay muchos productos */
            padding: 0;
        }

        #lista-productos li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 5px;
            background-color: #f9f9f9;
            transition: background-color 0.3s ease;
        }

        .list-group-item:hover {
            background-color: #f0f8ff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .eliminar-producto {
            background-color: #e74c3c;
            border: none;
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .eliminar-producto:hover {
            background-color: #c0392b;
        }


        .card {
            border-radius: 12px;
            background: #f8f9fa;
        }

        .form-group label {
            color: #343a40;
            font-size: 1.1rem;
        }

        .form-control {
            border-radius: 8px;
            font-size: 1rem;
        }

        .select2-container--default .select2-selection--single {
            border-radius: 8px;
        }

        .btn-success {
            font-size: 1.1rem;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
        }

        .card-header {
            font-size: 1.3rem;
            padding: 1rem;
            background: linear-gradient(45deg, #007bff, #5a5dfd);
        }

        /* Contenedor del buscador */
        .form-group label {
            color: #343a40;
            font-size: 1.1rem;
        }

        .input-group .form-control {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
            height: calc(2.5em + .75rem + 2px);
        }

        /* Estilo para el botÃ³n de bÃºsqueda */
        .input-group-append .btn {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
            background-color: #007bff;
            color: white;
            border: none;
        }

        .input-group-append .btn:hover {
            background-color: #0056b3;
        }

        /* Icono de bÃºsqueda */
        .input-group-append .btn i {
            font-size: 1.2rem;
        }

        /* Estilos adicionales para Select2 */
        .select2-container--default .select2-selection--single {
            border: 1px solid #ced4da;
            border-radius: 8px;
            padding: .5rem .75rem;
            height: calc(2.5em + .75rem + 2px);
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #495057;
            font-size: 1rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100%;
        }
    </style>
@endsection

@section('content')
    <div class="card shadow-lg border-0">
        <div class="card-header bg-primary text-white text-center">
            <h3 class="card-title">
                <i class="fas fa-shipping-fast"></i> Nueva Solicitud de Producto a Sucursal 1
            </h3>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('envios.store') }}" method="POST" id="envio-form">
                @csrf
                <div class="form-group">
                    <label for="nuevo_producto" class="font-weight-bold">Agregar Producto</label>
                    <div class="input-group">
                        <select id="nuevo_producto" class="form-control select2 buscador">
                            <option value="" disabled selected>Selecciona un producto...</option>
                            @foreach ($productos as $producto)
                                @php
                                    // Buscar el inventario para la Sucursal 1
                                    $inventario = $producto->inventarios
                                        ->where('id_sucursal', $idSucursalOrigen)
                                        ->first();
                                    $stockSucursal1 = $inventario ? $inventario->cantidad : 0;
                                @endphp
                                <option value="{{ $producto->id }}" data-stock="{{ $stockSucursal1 }}">
                                    {{ $producto->nombre }} (Stock disponible en Sucursal 1: {{ $stockSucursal1 }})
                                </option>
                            @endforeach
                        </select>
                        <input type="number" id="cantidad_producto" class="form-control" placeholder="Cantidad"
                            min="1" />
                        <div class="input-group-append">
                            <button type="button" id="agregar-producto" class="btn btn-success">Agregar</button>
                        </div>
                    </div>
                    <!-- Mostrar el stock disponible -->
                    <div id="stock-disponible" class="mt-2 text-info"></div>
                </div>

                <!-- Campo oculto para enviar los productos -->
                <h5 class="mt-4 font-weight-bold text-primary">Productos Agregados:</h5>
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <ul id="lista-productos" class="list-group">
                            <!-- Los productos serán agregados dinámicamente aquí -->
                        </ul>
                    </div>
                </div>
                <input type="hidden" name="productos_json" id="productos_json" />

                <div class="form-group text-center">
                    <button type="submit" id="enviar-productos" class="btn btn-success px-4">
                        <i class="fas fa-paper-plane"></i> Solicitar Productos
                    </button>
                </div>
            </form>
        </div>
    </div>

@section('js')
    <script>
        // Mostrar mensajes de éxito o error
        @if (session('success'))
            alert("{{ session('success') }}");
        @endif

        @if ($errors->any())
            alert("{{ implode('\\n', $errors->all()) }}");
        @endif

        $(document).ready(function() {
            $('.select2').select2({
                placeholder: 'Selecciona un producto',
                allowClear: true,
            });

            const productosArray = [];

            // Función para obtener el stock de un producto
            function obtenerStock(idProducto) {
                $.get("{{ url('/productos') }}/" + idProducto + "/stock", function(data) {
                    const stockDisponible = data.stock;
                    $('#stock-disponible').html(`Stock disponible: ${stockDisponible}`);

                    // Cambiar el color del fondo según el stock disponible
                    if (stockDisponible > 0) {
                        $('#stock-disponible').css('background-color', 'green').css('color', 'white');
                    } else {
                        $('#stock-disponible').css('background-color', 'red').css('color', 'white');
                    }
                });
            }
            $('#agregar-producto').click(function() {
                const productoId = $('#nuevo_producto').val();
                const cantidad = $('#cantidad_producto').val();
console.log(cantidad);
    const stockDisponible = $('#nuevo_producto option:selected').data('stock');

    // Guardamos el stock en una constante
    const stock = parseInt(stockDisponible);

    console.log('Stock disponible:', stock); // Aquí puedes usar esta constante según lo necesites

                if (productoId && cantidad) {
                    // Verificar si el producto ya existe
                    if (productosArray.some(p => p.id_producto == productoId)) {
                        alert("Este producto ya fue agregado.");
                        return;
                    }

                    // Obtener el stock disponible para verificar
                    $.get("{{ url('/productos') }}/" + productoId + "/stock", function(data) {
                        const stockDisponible = stock;

                        if (stockDisponible === 0) {
                            alert("No hay suficiente stock de este producto.");
                            return;
                        }

                        if (cantidad > stockDisponible) {
                            alert("La cantidad solicitada excede el stock disponible.");
                            return;
                        }

                        const productoNombre = $('#nuevo_producto option:selected').text();

                        // Agregar visualmente con estilos mejorados
                        $('#lista-productos').append(`
                <li class="list-group-item d-flex justify-content-between align-items-center" data-id="${productoId}">
                    <span class="font-weight-bold">${productoNombre}</span>
                    <span class="badge badge-primary badge-pill">${cantidad} unidades</span>
                    <button type="button" class="btn btn-danger btn-sm eliminar-producto">
                        <i class="fas fa-trash-alt"></i> Eliminar
                    </button>
                    <input type="hidden" name="productos[${productoId}][id_producto]" value="${productoId}" />
                    <input type="hidden" name="productos[${productoId}][cantidad]" value="${cantidad}" />
                </li>
            `);

                        productosArray.push({
                            id_producto: productoId,
                            cantidad
                        });

                        // Limpiar campos
                        $('#nuevo_producto').val(null).trigger('change');
                        $('#cantidad_producto').val('');
                        $('#stock-disponible').html('');
                    });
                } else {
                    alert("Selecciona un producto y una cantidad.");
                }
            });

            // Eliminar producto de la lista con estilos actualizados
            $(document).on('click', '.eliminar-producto', function() {
                const item = $(this).parent();
                const idProducto = item.data('id');

                // Eliminar del array y del DOM
                const index = productosArray.findIndex(p => p.id_producto == idProducto);
                if (index > -1) productosArray.splice(index, 1);
                item.fadeOut(300, function() {
                    $(this).remove();
                });
            });
        });
    </script>
    <script>
        function cargarProductos() {
            $.ajax({
                url: '/productos/almacen', // Endpoint que obtiene productos del almacén principal
                type: 'GET',
                success: function(data) {
                    $('#nuevo_producto').empty(); // Limpiar el select de productos
                    $('#nuevo_producto').append(
                        '<option value="" disabled selected>Selecciona un producto...</option>'
                    ); // Opción por defecto

                    // Agregar las opciones de producto al select
                    $.each(data, function(index, producto) {
                        $('#nuevo_producto').append('<option value="' + producto.id +
                            '">' + producto.nombre + ' (Stock: ' + producto.stock + ')</option>');
                    });
                },
                error: function() {
                    alert('Error al cargar los productos.');
                }
            });
        }
    </script>
@endsection
@endsection
