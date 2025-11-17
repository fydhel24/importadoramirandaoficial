@extends('adminlte::page')

@section('plugins.Select2', true)
@section('plugins.Sweetalert2', true)
@section('plugins.Datatables', true)

@section('css')
    <style>
        #lista-productos {
            max-height: 250px;
            /* Altura máxima para la lista */
            overflow-y: auto;
            /* Habilita el desplazamiento si hay demasiados productos */
        }

        .list-group-item {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 5px;
            background-color: #f9f9f9;
            transition: all 0.3s ease-in-out;
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


        /* Estilo general de la tarjeta */
        .card {
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border: none;
        }

        /* Encabezado de la tarjeta */
        .card-header {
            background-color: #007bff;
            color: white;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            text-align: center;
            font-size: 1.25rem;
            font-weight: bold;
        }

        /* TÃ­tulo dentro del encabezado */
        .card-title {
            margin: 0;
        }

        /* Estilo de los formularios */
        .form-group label {
            font-weight: 600;
            color: #495057;
        }

        /* Bordes redondeados y estilos para los select y input */
        .form-control {
            border-radius: 8px;
            border: 1px solid #ced4da;
            box-shadow: none;
            transition: all 0.3s ease;
        }

        /* Estilo de enfoque para los campos de formulario */
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
        }

        /* Estilo del botÃģn de enviar */
        .btn-success {
            background-color: #28a745;
            border: none;
            font-weight: bold;
            font-size: 1.1rem;
            padding: 10px;
            border-radius: 8px;
            transition: background-color 0.3s;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        /* Icono en el botÃģn de enviar */
        .btn-success i {
            margin-right: 5px;
        }

        /* Estilo para Select2 */
        .select2-container--default .select2-selection--single {
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: .5rem .75rem;
            height: calc(2.5em + .75rem + 2px);
        }

        /* Texto dentro de Select2 */
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
                <i class="fas fa-exchange-alt"></i> Transferencia de Productos entre Sucursales
            </h3>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('envios.storeTransfer') }}" method="POST" id="transfer-form">
                @csrf

                <div class="form-group">
                    <label for="sucursal_origen" class="font-weight-bold">Sucursal Origen</label>
                    <select name="sucursal_origen" id="sucursal_origen" class="form-control rounded" required
                        onchange=" cargarProductos(this.value);">
                        <option value="" disabled selected>Selecciona una sucursal de origen</option>
                        @foreach ($sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="sucursal_destino" class="font-weight-bold">Sucursal Destino</label>
                    <select name="sucursal_destino" class="form-control rounded" required
                        onchange="cargarUsuarios(this.value);">
                        <option value="" disabled selected>Selecciona una sucursal de destino</option>
                        @foreach ($sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="id_usuario" class="font-weight-bold">Usuario Destino</label>
                    <select name="id_usuario" id="id_usuario" class="form-control rounded" required>
                        <option value="" disabled selected>Selecciona un usuario</option>
                    </select>
                </div>



                <div class="form-group">
                    <label for="nuevo_producto" class="font-weight-bold">Agregar Producto</label>
                    <div class="input-group">
                        <select id="nuevo_producto" class="form-control select2 buscador">
                            <option value="" disabled selected>Selecciona un producto...</option>
                            @foreach ($productos as $producto)
                                <option value="{{ $producto->id }}">{{ $producto->nombre }}</option>
                            @endforeach
                        </select>
                        <input type="number" id="cantidad_producto" class="form-control" placeholder="Cantidad"
                            min="1" />
                        <div class="input-group-append">
                            <button type="button" id="agregar-producto" class="btn btn-success">Agregar</button>
                        </div>
                    </div>
                </div>
                <h5 class="mt-4 font-weight-bold text-primary">Productos Agregados:</h5>
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <ul id="lista-productos" class="list-group">
                            <!-- Los productos se agregarán dinámicamente aquí -->
                        </ul>

                    </div>
                </div>
                <input type="hidden" name="productos_json" id="productos_json" />

                <!-- BotÃģn para enviar productos -->
                <div class="form-group text-center">
                    <button type="submit" id="enviar-productos" class="btn btn-success px-4">
                        <i class="fas fa-paper-plane"></i> Transferir Productos
                    </button>
                </div>

            </form>
        </div>
    </div>
@endsection

@section('js')
    <script>
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

            $('#nuevo_producto').change(function() {
                const productoId = $(this).val();
                const sucursalOrigenId = $('#sucursal_origen')
                    .val(); // Suponiendo que tienes un campo para seleccionar la sucursal de origen

                if (productoId && sucursalOrigenId) {
                    obtenerStockSucursalOrigen(productoId, sucursalOrigenId);
                } else {
                    $('#stock-disponible').html('');
                }
            });

            // Función para obtener el stock disponible de la sucursal de origen
            function obtenerStockSucursalOrigen(productoId, sucursalOrigenId) {
                $.get(`/envios/obtener-stock-origen/${productoId}/${sucursalOrigenId}`, function(data) {
                    const stockDisponible = data.stock;
                    $('#stock-disponible').html(
                        `Stock disponible en sucursal de origen: ${stockDisponible}`);
                });
            }

            $('#agregar-producto').click(function() {
                const productoId = $('#nuevo_producto').val();
                const cantidad = $('#cantidad_producto').val();
                const sucursalOrigenId = $('#sucursal_origen')
                    .val(); // Suponiendo que tienes un campo para seleccionar la sucursal de origen

                if (productoId && cantidad && sucursalOrigenId) {
                    // Verificar si el producto ya existe
                    if (productosArray.some(p => p.id_producto == productoId)) {
                        alert("Este producto ya fue agregado.");
                        return;
                    }

                    // Obtener el stock disponible en la sucursal de origen
                    $.get(`/envios/obtener-stock-origen/${productoId}/${sucursalOrigenId}`, function(data) {
                        const stockDisponible = data.stock;

                        if (stockDisponible === 0) {
                            alert("No hay suficiente stock de este producto.");
                            return;
                        }

                        // Verificar que la cantidad a agregar no exceda el stock disponible
                        if (cantidad > stockDisponible) {
                            alert("La cantidad solicitada excede el stock disponible.");
                            return;
                        }

                        const productoNombre = $('#nuevo_producto option:selected').text();

                        // Agregar visualmente
                        $('#lista-productos').append(`
                <li class="list-group-item" data-id="${productoId}">
                    ${productoNombre} - Cantidad: ${cantidad}
                    <button type="button" class="btn btn-danger btn-sm eliminar-producto">Eliminar</button>
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

            // Eliminar producto de la lista
            $(document).on('click', '.eliminar-producto', function() {
                const item = $(this).parent();
                const idProducto = item.data('id');

                // Eliminar del array y del DOM
                const index = productosArray.findIndex(p => p.id_producto == idProducto);
                if (index > -1) productosArray.splice(index, 1);
                item.remove();
            });

            $('#transfer-form').on('submit', function(event) {
                event.preventDefault();

                const formData = new FormData(this);

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,

                    success: function(response) {
                        if (response.success) {
                            const pdfData = response.reportUrl.split(',')[1];

                            const byteCharacters = atob(pdfData);

                            const byteNumbers = new Array(byteCharacters.length);

                            for (let i = 0; i < byteCharacters.length; i++) {
                                byteNumbers[i] = byteCharacters.charCodeAt(i);
                            }

                            const byteArray = new Uint8Array(byteNumbers);

                            const blob = new Blob([byteArray], {
                                type: 'application/pdf'
                            });

                            const url = window.URL.createObjectURL(blob);

                            window.open(url, '_blank');

                            window.location.href = response.redirectUrl;
                        } else {
                            alert(response.message || 'Error al procesar el envío.');
                        }

                    },
                    error: function(xhr) {
                        alert(
                            'Hubo un error al procesar el envío. Por favor, intenta de nuevo.'
                        );
                    }
                });
            });

        });
    </script>
    <script>
        function cargarUsuarios(sucursalId) {
            if (sucursalId) {
                $.ajax({
                    url: '/usuarios/sucursal/' + sucursalId,
                    type: 'GET',
                    success: function(data) {
                        $('#id_usuario').empty(); // Limpiar el select de usuarios
                        $('#id_usuario').append(
                            '<option value="" disabled selected>Selecciona un usuario</option>'
                        ); // Opción por defecto

                        // Agregar las opciones de usuario al select
                        $.each(data, function(index, usuario) {
                            $('#id_usuario').append('<option value="' + usuario.id + '">' + usuario
                                .name + '</option>');
                        });
                    },
                    error: function() {
                        alert('Error al cargar los usuarios.');
                    }
                });
            } else {
                $('#id_usuario').empty(); // Limpiar si no hay sucursal seleccionada
                $('#id_usuario').append('<option value="" disabled selected>Selecciona un usuario</option>');
            }
        }
    </script>
    <script>
        function cargarProductos(sucursalId) {
            if (sucursalId) {
                $.ajax({
                    url: '/productos/sucursal/' + sucursalId,
                    type: 'GET',
                    success: function(data) {
                        $('#nuevo_producto').empty(); // Limpiar el select de productos
                        $('#nuevo_producto').append(
                            '<option value="" disabled selected>Selecciona un producto...</option>'
                        ); // Opción por defecto

                        // Agregar las opciones de producto al select
                        $.each(data, function(index, inventario) {
                            $('#nuevo_producto').append('<option value="' + inventario.producto.id +
                                '">' + inventario.producto.nombre +
                                ' (Stock de la Sucursal de Origen: ' + inventario
                                .cantidad + ')</option>');
                        });
                    },
                    error: function() {
                        alert('Error al cargar los productos.');
                    }
                });
            } else {
                $('#nuevo_producto').empty(); // Limpiar si no hay sucursal seleccionada
                $('#nuevo_producto').append('<option value="" disabled selected>Selecciona un producto...</option>');
            }
        }
    </script>
@endsection
