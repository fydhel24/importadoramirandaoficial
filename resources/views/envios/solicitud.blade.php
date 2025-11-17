@extends('adminlte::page')

@section('css')
    <style>
        /* Card styles */
        .card {
            border: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
        }

        .card-header {
            background: linear-gradient(135deg, #4e73df, #df3383);


            border-radius: 15px 15px 0 0;
        }


        .card-body {
            background-color: #ffffff;
            border-radius: 0 0 15px 15px;
        }

        label {
            font-weight: bold;
            color: #000;
        }

        .btn-custom {
            background-color: #28a745;
            /* Color verde */
            color: white;
            /* Color del texto */
            padding: 10px 20px;
            /* Espaciado interno */
            border: none;
            /* Sin borde */
            border-radius: 5px;
            /* Bordes redondeados */
            font-size: 16px;
            /* Tamaño de fuente */
            text-decoration: none;
            /* Sin subrayado */
            transition: background-color 0.3s, box-shadow 0.3s;
            /* Transición suave */
        }

        .btn-custom:hover {
            background-color: #218838;
            /* Color más oscuro al pasar el cursor */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            /* Sombra al pasar el cursor */
        }

        .btn-custom i {
            margin-right: 5px;
            /* Espacio entre el icono y el texto */
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f9f9f9;
        }

        .thead-dark th {
            background-color: #343a40;
            color: white;
        }

        .form-control {
            border-radius: 0.25rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .pagination {
            justify-content: center;
        }

        .pagination .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
        }



        /* Estilos para el campo de búsqueda */
        #buscador_producto {
            border-radius: 4px 0 0 4px;
            /* Esquinas redondeadas a la izquierda */
        }

        #btn-limpiar-busqueda {
            border-radius: 0 4px 4px 0;
            /* Esquinas redondeadas a la derecha */
        }

        /* Estilos para la lista de resultados */
        #lista_resultados {
            position: absolute;
            z-index: 1000;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 5px;
        }

        .list-group-item {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }

        .list-group-item:hover {
            background-color: #f8f9fa;
        }

        /* Estilos para el botón de agregar */
        #btn-agregar-producto {
            height: 38px;
            /* Ajusta la altura para que coincida con los otros campos */
        }

        /* Ajustes para los campos de cantidad y botón */
        .form-group.col-md-3 {
            padding-right: 0;
            /* Elimina el padding derecho para alinear mejor los campos */
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- CSS de Select2 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />


    <link href="https://unpkg.com/slim-select@latest/dist/slimselect.css" rel="stylesheet">
    <!-- DataTables JS & CSS (CDN) -->
    <link href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.4.0/css/responsive.dataTables.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="container">
        <div class="card-header">
            <h1 class="my-4 text-center text-primary font-weight-bold">Solicitud de Productos</h1>

            <!-- Filtros -->
            <div class="filters mb-4 p-4 shadow bg-light rounded">
                <form action="{{ route('envios.solicitud') }}" method="GET">
                    <i class="fas fa-filter"></i> Filtros de Búsqueda

                    <div class="form-row">

                        <div class="form-group col-md-4">
                            <label for="fecha_inicio">Fecha Inicio</label>
                            <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio"
                                value="{{ request()->input('fecha_inicio') }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="fecha_fin">Fecha Fin</label>
                            <input type="date" class="form-control" name="fecha_fin" id="fecha_fin"
                                value="{{ request()->input('fecha_fin') }}">
                        </div>

                        <!-- Filtro por Usuario Destino -->
                        <div class="form-group col-md-4">
                            <label for="usuario_destino">Usuario Destino</label>
                            <select name="usuario_destino" id="usuario_destino" class="form-control">
                                <option value="">Seleccione Usuario</option>
                                @foreach ($usuariosDestino as $usuario)
                                    <option
                                        value="{{ $usuario->id }}"{{ request()->input('usuario_destino') == $usuario->id ? 'selected' : '' }}>
                                        {{ $usuario->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Buscar</button>


                </form>
            </div>


            <!-- Contenedor para los botones -->
            <div class="text-center mb-4">
                <div class="d-flex justify-content-center gap-3">
                    <!-- Botón para crear nueva solicitud -->
                    <a href="{{ route('envios.create') }}" class="btn btn-success btn-lg">
                        <i class="fas fa-plus-circle"></i> Solicitud de Productos
                    </a>

                    <!-- Botón para abrir el Modal de Solicitud entre Sucursales -->
                    <button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#solicitudModal">
                        <i class="fas fa-exchange-alt"></i> Solicitud Entre Sucursales
                    </button>
                </div>
            </div>
        </div>


        <!-- Modal de Solicitud entre Sucursales -->
        <div class="modal fade" id="solicitudModal" tabindex="-1" role="dialog" aria-labelledby="solicitudModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <!-- Encabezado del Modal -->
                    <div class="modal-header">
                        <h5 class="modal-title" id="solicitudModalLabel">Solicitud Entre Sucursales</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <!-- Cuerpo del Modal -->
                    <div class="modal-body">
                        <form id="form-solicitud-entre-sucursales">
                            <!-- Selección de la Sucursal Origen -->
                            <div class="form-group">
                                <label for="sucursal_origen">Sucursal Origen</label>
                                <select class="form-control" id="sucursal_origen" name="sucursal_origen">
                                    <option value="" disabled selected>Seleccione Sucursal Origen</option>
                                    @foreach ($sucursales as $sucursal)
                                        <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Selección de Producto y cantidad -->
                            <div class="form-row align-items-end">
                                <!-- Campo de búsqueda de productos -->
                                <div class="form-group col-md-6">
                                    <label for="buscador_producto">Buscar Producto</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="buscador_producto"
                                            placeholder="Escribe para buscar...">
                                        <div class="input-group-append">
                                            <button type="button" id="btn-limpiar-busqueda"
                                                class="btn btn-outline-secondary" style="display: none;">
                                                <i class="fas fa-times"></i> <!-- Icono de "x" (necesitas FontAwesome) -->
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Lista de resultados de la búsqueda -->
                                    <div id="lista_resultados" class="list-group mt-2" style="display: none;">
                                        <!-- Los productos encontrados se mostrarán aquí -->
                                    </div>
                                </div>

                                <!-- Campo de cantidad -->
                                <div class="form-group col-md-3">
                                    <label for="cantidad_solicitud">Cantidad</label>
                                    <input type="number" class="form-control" id="cantidad_solicitud" min="1"
                                        value="">
                                </div>

                                <!-- Botón de agregar -->
                                <div class="form-group col-md-3">
                                    <label>&nbsp;</label> <!-- Espacio para alinear el botón -->
                                    <button type="button" id="btn-agregar-producto" class="btn btn-success btn-block">
                                        <i class="fas fa-plus"></i> Agregar
                                    </button>
                                </div>
                            </div>

                            <!-- Lista de Productos Agregados -->
                            <div class="form-group">
                                <label>Productos Agregados:</label>
                                <div id="lista_productos" class="list-group">
                                    <!-- Se agregarán los productos solicitados aquí -->
                                </div>
                            </div>
                        </form>
                    </div>
                    <!-- Pie del Modal -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="btn-guardar-solicitud">Guardar
                            Solicitud</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Historial -->
        <div class="card-body">
            <i class="fas fa-table"></i> Registro de Solicitud de Productos

            <div class="table-responsive">
                <table id="solicitudesTable" class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Sucursal Origen</th>
                            <th>Sucursal Destino</th>
                            <th>Usuario Origen</th>
                            <th>Usuario Destino</th>
                            <th>Fecha de Envío</th>
                            <th>Estado</th>
                            <th>Productos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>

        </div>


    </div>
@endsection


@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <!-- Inicializar DataTables -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.0/js/dataTables.responsive.min.js"></script>

    <!-- Importación de SlimSelect -->
    <script src="https://unpkg.com/slim-select@latest/dist/slimselect.min.js"></script>

    <!-- Script para manejar el modal -->
    <!-- Script para manejar el modal -->
    <script>
        $(document).ready(function() {


            let productosDisponibles = []; // Almacenará los productos cargados
            let productoSeleccionado = null; // Almacenará el producto seleccionado

            // Cuando se cambia la sucursal, se cargan los productos disponibles
            $('#sucursal_origen').change(function() {
                var sucursalId = $(this).val();
                if (!sucursalId) {
                    $('#lista_resultados').html('')
                        .hide(); // Oculta la lista si no hay sucursal seleccionada
                    return;
                }

                // Realiza la petición AJAX para obtener productos por sucursal
                $.ajax({
                    url: '/productos/sucursal/' +
                        sucursalId, // Asegúrate de que esta URL coincida con tu ruta
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log("Productos obtenidos:", response);
                        productosDisponibles = response; // Almacena los productos para filtrar
                    },
                    error: function(xhr, status, error) {
                        console.error("Error al cargar productos:", status, error);
                        alert('Error al cargar los productos de la sucursal.');
                    },
                });
            });

            // Búsqueda en tiempo real
            $('#buscador_producto').on('input', function() {
                var busqueda = $(this).val().toLowerCase(); // Texto de búsqueda
                var resultados = productosDisponibles.filter(function(inventario) {
                    return inventario.producto.nombre.toLowerCase().includes(busqueda);
                });

                // Muestra los resultados en la lista
                mostrarResultados(resultados);
            });

            // Función para mostrar los resultados de la búsqueda
            function mostrarResultados(resultados) {
                var lista = $('#lista_resultados');
                lista.empty(); // Limpia la lista anterior

                if (resultados.length > 0) {
                    resultados.forEach(function(inventario) {
                        var producto = inventario.producto;
                        lista.append(
                            `<div class="list-group-item" data-product-id="${producto.id}" data-stock="${inventario.cantidad}">
                        ${producto.nombre} (Stock: ${inventario.cantidad})
                    </div>`
                        );
                    });
                    lista.show(); // Muestra la lista
                } else {
                    lista.hide(); // Oculta la lista si no hay resultados
                }
            }

            // Seleccionar un producto de la lista
            $(document).on('click', '#lista_resultados .list-group-item', function() {
                var productoId = $(this).data('product-id');
                var productoTexto = $(this).text();
                var stock = $(this).data('stock');

                // Almacena el producto seleccionado
                productoSeleccionado = {
                    id: productoId,
                    nombre: productoTexto,
                    stock: stock,
                };

                // Llena el campo de búsqueda con el producto seleccionado
                $('#buscador_producto').val(productoTexto);

                // Oculta la lista de resultados
                $('#lista_resultados').hide();

                // Muestra el botón de "x"
                $('#btn-limpiar-busqueda').show();
            });

            // Ocultar la lista de resultados al hacer clic fuera
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#buscador_producto, #lista_resultados').length) {
                    $('#lista_resultados').hide();
                }
            });

            // Mostrar u ocultar el botón de "x" según si hay texto en el input
            $('#buscador_producto').on('input', function() {
                if ($(this).val().trim() !== '') {
                    $('#btn-limpiar-busqueda').show();
                } else {
                    $('#btn-limpiar-busqueda').hide();
                }
            });

            // Limpiar el input y ocultar el botón de "x" cuando se hace clic en él
            $('#btn-limpiar-busqueda').click(function() {
                $('#buscador_producto').val('').trigger(
                    'input'); // Limpia el input y dispara el evento input
                $('#lista_resultados').hide(); // Oculta la lista de resultados
                $(this).hide(); // Oculta el botón de "x"
                productoSeleccionado = null; // Limpia el producto seleccionado
            });

            // Agregar producto a la lista
            $('#btn-agregar-producto').click(function() {
                var sucursalId = $('#sucursal_origen').val();
                var requestedQuantity = parseInt($('#cantidad_solicitud').val());

                // Verifica si hay un producto seleccionado
                if (!productoSeleccionado) {
                    alert('Por favor, seleccione un producto.');
                    return;
                }

                // Verifica si la cantidad es válida
                if (!requestedQuantity || requestedQuantity < 1) {
                    alert('Ingrese una cantidad válida.');
                    return;
                }

                // Verificar el stock actual mediante AJAX
                $.ajax({
                    url: '/envios/obtener-stock-origen/' + productoSeleccionado.id + '/' +
                        sucursalId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        var availableStock = data.stock;
                        if (requestedQuantity > availableStock) {
                            alert('La cantidad solicitada supera el stock disponible (' +
                                availableStock + ').');
                            return;
                        }

                        // Evita agregar el mismo producto dos veces
                        if ($('#lista_productos').find('[data-product-id="' +
                                productoSeleccionado.id + '"]').length > 0) {
                            alert('El producto ya ha sido agregado.');
                            return;
                        }

                        // Crea el elemento en la lista de productos
                        var html = `<div class="list-group-item d-flex justify-content-between align-items-center" data-product-id="${productoSeleccionado.id}">
                              <span>${productoSeleccionado.nombre} - Cantidad: ${requestedQuantity}</span>
                              <button type="button" class="btn btn-danger btn-sm btn-eliminar-producto">Eliminar</button>
                              <input type="hidden" name="productos[${productoSeleccionado.id}][cantidad]" value="${requestedQuantity}">
                            </div>`;
                        $('#lista_productos').append(html);

                        // Reinicia los campos de selección e input
                        $('#buscador_producto').val('');
                        $('#cantidad_solicitud').val('1');
                        productoSeleccionado = null; // Limpia el producto seleccionado
                        $('#btn-limpiar-busqueda').hide(); // Oculta el botón de "x"
                    },
                    error: function(xhr, status, error) {
                        console.error("Error al verificar stock:", status, error);
                        alert('Error al verificar el stock del producto.');
                    },
                });
            });

            // Eliminar producto de la lista
            $(document).on('click', '.btn-eliminar-producto', function() {
                $(this).closest('.list-group-item').remove();
            });

            $('#btn-guardar-solicitud').click(function() {
                var sucursalOrigen = $('#sucursal_origen').val();
                if (!sucursalOrigen) {
                    alert('Seleccione la sucursal origen.');
                    return;
                }
                if ($('#lista_productos').children().length === 0) {
                    alert('Agregue al menos un producto a la solicitud.');
                    return;
                }

                // Construir manualmente el objeto de datos
                var productos = {};
                $('#lista_productos .list-group-item').each(function() {
                    var productId = $(this).data('product-id');
                    var cantidad = $(this).find('input[name^="productos"]').val();
                    productos[productId] = {
                        cantidad: parseInt(cantidad)
                    };
                });

                // Crear el objeto de datos para enviar
                var data = {
                    sucursal_origen: sucursalOrigen,
                    productos: productos,
                };

                // Enviar la solicitud al servidor
                $.ajax({
                    url: '/envios/solicitud-entre-sucursales',
                    type: 'POST',
                    data: JSON.stringify(data),
                    contentType: 'application/json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    },
                    success: function(response) {
                        console.log("Solicitud guardada:", response);

                        // Mostrar SweetAlert de éxito
                        Swal.fire({
                            title: '¡Éxito!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: 'Aceptar',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Recargar la página o actualizar la tabla de solicitudes
                                location.reload(); // Recarga la página
                                // O bien, si usas DataTables:
                                // $('#tabla-solicitudes').DataTable().ajax.reload();
                            }
                        });

                        // Cerrar el modal
                        $('#solicitudModal').modal('hide');
                    },
                    error: function(xhr, status, error) {
                        console.error("Error al guardar la solicitud:", status, error);
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            Swal.fire({
                                title: 'Error',
                                text: xhr.responseJSON.message,
                                icon: 'error',
                                confirmButtonText: 'Aceptar',
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: 'Error al guardar la solicitud.',
                                icon: 'error',
                                confirmButtonText: 'Aceptar',
                            });
                        }
                    },
                });
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            const table = $('#solicitudesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('envios.solicitud') }}",
                    data: function(d) {
                        d.fecha_inicio = $('#fecha_inicio').val();
                        d.fecha_fin = $('#fecha_fin').val();
                        d.usuario_destino = $('#usuario_destino').val();
                    }
                },
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'sucursal_origen_nombre',
                        name: 'sucursalOrigen.nombre'
                    },
                    {
                        data: 'sucursal_destino.nombre',
                        name: 'sucursalDestino.nombre'
                    },
                    {
                        data: 'usuario_origen.name',
                        name: 'usuarioOrigen.name'
                    },
                    {
                        data: 'usuario_destino.name',
                        name: 'usuarioDestino.name'
                    },
                    {
                        data: 'fecha_envio',
                        name: 'fecha_envio'
                    },
                    {
                        data: 'estado',
                        name: 'estado',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'productos',
                        name: 'productos',
                        orderable: false,
                        searchable: true,
                    },
                    {
                        data: 'acciones',
                        name: 'acciones',
                        orderable: false,
                        searchable: false
                    }
                ],
                responsive: true,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/Spanish.json"
                }
            });

            $('#filter-button').on('click', function() {
                table.ajax.reload();
            });

            $(document).on('click', '.confirmar-envio', function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: '¿Confirmar envío?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí',
                    cancelButtonText: 'No',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/envios/confirmar/${id}`,
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    // Abrir el PDF en una nueva pestaña
                                    const pdfWindow = window.open('', '_blank');
                                    pdfWindow.document.write(
                                        `<iframe width='100%' height='100%' src='${response.reportUrl}'></iframe>`
                                    );

                                    // Redirigir para actualizar el estado
                                    window.location.href = response.redirectUrl;
                                } else {
                                    Swal.fire('¡Error!', response.message, 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error',
                                    'Ocurrió un error al confirmar el envío.',
                                    'error');
                            },
                        });
                    }
                });
            });
            // ELIMINAR SOLICITUD
        $(document).on('click', '.eliminar-solicitud', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/envios/eliminar/${id}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Eliminado', response.message, 'success');
                                table.ajax.reload();
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'Ocurrió un error al eliminar la solicitud.', 'error');
                        },
                    });
                }
            });
        });
        });
    </script>
@endsection
