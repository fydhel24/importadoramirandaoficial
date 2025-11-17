{{-- filepath: d:\proyecto-importadora\13-03-2025\resources\views\envios\productosMalEstado.blade.php --}}
@extends('adminlte::page')

@section('title', 'Productos en Mal Estado')

@section('content')
    <div class="container">
        <h1 class="my-4 text-center text-danger">Productos en Mal Estado</h1>
        <!-- Botón para abrir el Modal de Recepción de Productos en Mal Estado -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <button type="button" class="btn btn-danger btn-lg shadow" data-toggle="modal"
                data-target="#recepcionMalEstadoModal">
                <i class="fas fa-ambulance me-2"></i> Recepción de Productos en Mal Estado
            </button>

            <button id="generarPDF" class="btn btn-outline-danger shadow">
                <i class="fas fa-file-pdf me-2"></i> Generar PDF
            </button>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <label for="fecha_inicio">Fecha y hora de inicio</label>
                <input type="datetime-local" id="fecha_inicio" class="form-control">
            </div>
            <div class="col-md-3">
                <label for="fecha_fin">Fecha y hora de fin</label>
                <input type="datetime-local" id="fecha_fin" class="form-control">
            </div>
            <div class="col-md-3">
                <label for="usuario_destino">Usuario Receptor</label>
                <select id="usuario_destino" class="form-control">
                    <option value="">Todos</option>
                    @foreach ($usuariosorigen as $usuario)
                        <option value="{{ $usuario->id }}">{{ $usuario->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button id="filtrar" class="btn btn-primary w-100">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
            </div>
        </div>



        <table id="productosMalEstadoTable" class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Sucursal Origen</th>
                    <th>Sucursal Destino</th>
                    <th>Usuario Recepcion</th>
                    <th>Fecha de Recepcion</th>
                    <th>Estado</th>
                    <th>Productos</th>
                    <th>Acciones</th>
                </tr>

            </thead>
        </table>
        <!-- Modal de Recepción de Productos en Mal Estado -->
        <div class="modal fade" id="recepcionMalEstadoModal" tabindex="-1" role="dialog"
            aria-labelledby="recepcionMalEstadoModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <!-- Encabezado del Modal -->
                    <div class="modal-header">
                        <h5 class="modal-title" id="recepcionMalEstadoModalLabel">Recepción de Productos en Mal Estado</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <!-- Cuerpo del Modal -->
                    <div class="modal-body">
                        <form id="form-recepcion-mal-estado">
                            <!-- Selección de la Sucursal de Origen -->
                            <div class="form-group">
                                <label for="sucursal_origen_mal_estado">Sucursal de Origen</label>
                                <select class="form-control" id="sucursal_origen_mal_estado" name="sucursal_origen">
                                    <option value="" disabled selected>Seleccione la Sucursal de Origen</option>
                                    @foreach ($sucursales as $sucursal)
                                        <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Campo de búsqueda de productos -->
                            <!-- Campo de búsqueda de productos -->
                            <div
                                class="form-group>
                                    <label for="buscador_producto_mal_estado">
                                Buscar Producto</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="buscador_producto_mal_estado"
                                        placeholder="Escribe para buscar...">
                                    <div class="input-group-append">
                                        <button type="button" id="btn-limpiar-busqueda-mal-estado"
                                            class="btn btn-outline-secondary" style="display: none;">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <div id="lista_resultados_mal_estado" class="list-group mt-2" style="display: none;">
                                    <!-- Los productos encontrados se mostrarán aquí -->
                                </div>

                                <div class="form-row align-items-end">
                                    <!-- Campo de cantidad -->
                                    <div class="form-group col-md-6">
                                        <label for="cantidad">Cantidad</label>
                                        <input type="number" class="form-control" id="cantidad" min="1"
                                            value="">
                                    </div>

                                    <!-- Botón de agregar -->
                                    <div class="form-group col-md-6">
                                        <label>&nbsp;</label> <!-- Espacio para alinear el botón con el input -->
                                        <button type="button" id="btn-agregar-producto-mal-estado"
                                            class="btn btn-info btn-block">
                                            <i class="fas fa-plus"></i> Agregar
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- Lista de Productos Agregados -->
                            <div class="form-group">
                                <label>Productos Agregados:</label>
                                <div id="lista_productos_mal_estado" class="list-group">
                                    <!-- Se agregarán los productos solicitados aquí -->
                                </div>
                            </div>
                        </form>

                    </div>
                    <!-- Pie del Modal -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" id="btn-guardar-recepcion-mal-estado">Guardar
                            Recepción</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    {{-- <script>
        $(document).ready(function() {
            $('#productosMalEstadoTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('envios.productosMalEstado') }}",
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
                        searchable: false
                    },
                    {
                        data: 'productos',
                        name: 'productos',
                        orderable: false,
                        searchable: true
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
        });
    </script> --}}
    <script>
        $(document).ready(function() {
            let tabla = $('#productosMalEstadoTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('envios.productosMalEstado') }}",
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
                        searchable: false
                    },
                    {
                        data: 'productos',
                        name: 'productos',
                        orderable: false,
                        searchable: true
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

            // Evento para botón de filtrar
            $('#filtrar').on('click', function() {
                tabla.ajax.reload();
            });
        });
    </script>
    <script>
        $('#generarPDF').on('click', function() {
            const fechaInicio = $('#fecha_inicio').val();
            const fechaFin = $('#fecha_fin').val();
            const usuarioDestino = $('#usuario_destino').val();

            let query = `?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&usuario_destino=${usuarioDestino}`;

            window.open("{{ route('envios.productosMalEstadoPDF') }}" + query, '_blank');
        });
    </script>


    <script>
        $(document).ready(function() {
            let productosDisponiblesMalEstado = []; // Almacena los productos cargados para el modal de mal estado
            let productoSeleccionadoMalEstado = null; // Almacena el producto seleccionado

            // Cuando se cambia la sucursal en el modal de mal estado, se cargan los productos disponibles
            $('#sucursal_origen_mal_estado').change(function() {
                const sucursalId = $(this).val();
                if (!sucursalId) {
                    $('#lista_resultados_mal_estado').html('')
                        .hide(); // Oculta la lista si no hay sucursal seleccionada
                    return;
                }

                // Realiza la petición AJAX para obtener productos por sucursal
                $.ajax({
                    url: `/productos/sucursal/${sucursalId}`, // Asegúrate de que esta URL coincida con tu ruta
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log("Productos obtenidos para mal estado:", response);
                        productosDisponiblesMalEstado =
                            response; // Almacena los productos para filtrar
                    },
                    error: function(xhr, status, error) {
                        console.error("Error al cargar productos para mal estado:", status,
                            error);
                        alert('Error al cargar los productos de la sucursal.');
                    },
                });
            });

            // Búsqueda en tiempo real en el modal de mal estado
            $('#buscador_producto_mal_estado').on('input', function() {
                const busqueda = $(this).val().toLowerCase(); // Texto de búsqueda
                const resultados = productosDisponiblesMalEstado.filter(function(inventario) {
                    return inventario.producto.nombre.toLowerCase().includes(busqueda);
                });

                // Muestra los resultados en la lista
                mostrarResultadosMalEstado(resultados);
            });

            // Función para mostrar los resultados de la búsqueda en el modal de mal estado
            function mostrarResultadosMalEstado(resultados) {
                const lista = $('#lista_resultados_mal_estado');
                lista.empty(); // Limpia la lista anterior

                if (resultados.length > 0) {
                    resultados.forEach(function(inventario) {
                        const producto = inventario.producto;
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

            // Seleccionar un producto de la lista en el modal de mal estado
            $(document).on('click', '#lista_resultados_mal_estado .list-group-item', function() {
                const productoId = $(this).data('product-id');
                const productoTexto = $(this).text();
                const stock = $(this).data('stock');

                // Almacena el producto seleccionado
                productoSeleccionadoMalEstado = {
                    id: productoId,
                    nombre: productoTexto,
                    stock: stock,
                };

                // Llena el campo de búsqueda con el producto seleccionado
                $('#buscador_producto_mal_estado').val(productoTexto);

                // Oculta la lista de resultados
                $('#lista_resultados_mal_estado').hide();

                // Muestra el botón de "x"
                $('#btn-limpiar-busqueda-mal-estado').show();
            });

            // Ocultar la lista de resultados al hacer clic fuera en el modal de mal estado
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#buscador_producto_mal_estado, #lista_resultados_mal_estado')
                    .length) {
                    $('#lista_resultados_mal_estado').hide();
                }
            });

            // Mostrar u ocultar el botón de "x" según si hay texto en el input del modal de mal estado
            $('#buscador_producto_mal_estado').on('input', function() {
                if ($(this).val().trim() !== '') {
                    $('#btn-limpiar-busqueda-mal-estado').show();
                } else {
                    $('#btn-limpiar-busqueda-mal-estado').hide();
                }
            });

            // Limpiar el input y ocultar el botón de "x" cuando se hace clic en él en el modal de mal estado
            $('#btn-limpiar-busqueda-mal-estado').click(function() {
                $('#buscador_producto_mal_estado').val('').trigger(
                    'input'); // Limpia el input y dispara el evento input
                $('#lista_resultados_mal_estado').hide(); // Oculta la lista de resultados
                $(this).hide(); // Oculta el botón de "x"
                productoSeleccionadoMalEstado = null; // Limpia el producto seleccionado
            });

            // Agregar producto a la lista
            $('#btn-agregar-producto-mal-estado').click(function() {
                const requestedQuantity = parseInt($('#cantidad').val());

                // Verifica si hay un producto seleccionado
                if (!productoSeleccionadoMalEstado) {
                    alert('Por favor, seleccione un producto.');
                    return;
                }

                // Verifica si la cantidad es válida
                if (!requestedQuantity || requestedQuantity < 1) {
                    alert('Ingrese una cantidad válida.');
                    return;
                }

                // Verifica si la cantidad solicitada supera el stock disponible
                if (requestedQuantity > productoSeleccionadoMalEstado.stock) {
                    alert(
                        `La cantidad solicitada supera el stock disponible (${productoSeleccionadoMalEstado.stock}).`
                    );
                    return;
                }

                // Evita agregar el mismo producto dos veces
                if ($('#lista_productos_mal_estado').find(
                        `[data-product-id="${productoSeleccionadoMalEstado.id}"]`).length > 0) {
                    alert('El producto ya ha sido agregado.');
                    return;
                }

                // Crea el elemento en la lista de productos
                const html = `<div class="list-group-item d-flex justify-content-between align-items-center" data-product-id="${productoSeleccionadoMalEstado.id}">
                    <span>${productoSeleccionadoMalEstado.nombre} - Cantidad: ${requestedQuantity}</span>
                    <button type="button" class="btn btn-danger btn-sm btn-eliminar-producto">Eliminar</button>
                    <input type="hidden" name="productos[${productoSeleccionadoMalEstado.id}][cantidad]" value="${requestedQuantity}">
                </div>`;
                $('#lista_productos_mal_estado').append(html);

                // Reinicia los campos de selección e input
                $('#buscador_producto_mal_estado').val('');
                $('#cantidad').val('1');
                productoSeleccionadoMalEstado = null; // Limpia el producto seleccionado
                $('#btn-limpiar-busqueda-mal-estado').hide(); // Oculta el botón de "x"
            });

            // Eliminar producto de la lista
            $(document).on('click', '.btn-eliminar-producto', function() {
                $(this).closest('.list-group-item').remove();
            });
        });
        $('#btn-guardar-recepcion-mal-estado').click(function() {
            const sucursalOrigen = $('#sucursal_origen_mal_estado').val();
            if (!sucursalOrigen) {
                alert('Seleccione la sucursal origen.');
                return;
            }
            if ($('#lista_productos_mal_estado').children().length === 0) {
                alert('Agregue al menos un producto a la recepción.');
                return;
            }

            // Construir manualmente el objeto de datos
            const productos = {};
            $('#lista_productos_mal_estado .list-group-item').each(function() {
                const productId = $(this).data('product-id');
                const cantidad = $(this).find('input[name^="productos"]').val();
                productos[productId] = {
                    cantidad: parseInt(cantidad),
                };
            });

            // Crear el objeto de datos para enviar
            const data = {
                sucursal_origen: sucursalOrigen,
                productos: productos,
            };

            // Enviar la solicitud al servidor
            $.ajax({
                url: '/envios/recepcion-mal-estado', // Ruta para guardar la recepción
                type: 'POST',
                data: JSON.stringify(data),
                contentType: 'application/json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                success: function(response) {
                    console.log('Recepción guardada:', response);

                    // Mostrar SweetAlert de éxito
                    Swal.fire({
                        title: '¡Éxito!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'Aceptar',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Recargar la página o actualizar la tabla de recepciones
                            location.reload(); // Recarga la página
                        }
                    });

                    // Cerrar el modal
                    $('#recepcionMalEstadoModal').modal('hide');
                },
                error: function(xhr, status, error) {
                    console.error('Error al guardar la recepción:', status, error);
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
                            text: 'Error al guardar la recepción.',
                            icon: 'error',
                            confirmButtonText: 'Aceptar',
                        });
                    }
                },
            });
        });
    </script>
    <script>
        $(document).on('click', '.confirmar-mal-estado', function() {
            const id = $(this).data('id');
            const estado = $(this).data('estado'); // ← importante para saber qué hacer
            const yaConfirmado = estado === 'mal estado confirmado';

            // Configuración dinámica de la alerta
            const swalTitle = yaConfirmado ? '¿Desea generar el reporte?' : '¿Estás seguro?';
            const swalText = yaConfirmado ?
                "Esta acción generará el reporte PDF." :
                "Esta acción confirmará el estado como 'mal estado confirmado'.";

            const confirmButtonText = yaConfirmado ? 'Generar reporte' : 'Sí, confirmar';

            Swal.fire({
                title: swalTitle,
                text: swalText,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: confirmButtonText,
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const url = yaConfirmado ?
                        `/envios/generar-reporte-mal-estado/${id}` // Solo genera PDF
                        :
                        `/envios/confirmar-mal-estado/${id}`; // Cambia estado y genera PDF

                    $.ajax({
                        url: url,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                // Generar PDF y abrir en nueva pestaña
                                const pdfWindow = window.open('', '_blank');
                                pdfWindow.document.write(
                                    `<iframe width='100%' height='100%' src='${response.reportUrl}'></iframe>`
                                );

                                // Recargar solo si se confirmó estado
                                if (!yaConfirmado) {
                                    window.location.reload();
                                }
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire(
                                'Error',
                                xhr.responseJSON.message ||
                                'Hubo un error en la operación.',
                                'error'
                            );
                        }
                    });
                }
            });
        });
    </script>

    <script>
        $(document).on('click', '.revertir-recepcion', function() {
            const id = $(this).data('id');

            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción revertirá la recepción y devolverá las cantidades al inventario.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, revertir',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/envios/revertir-recepcion/${id}`,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire('Éxito', response.message, 'success').then(() => {
                                location.reload(); // Recargar la página
                            });
                        },
                        error: function(xhr) {
                            Swal.fire('Error', xhr.responseJSON.message ||
                                'Hubo un error al revertir la recepción.', 'error');
                        }
                    });
                }
            });
        });
    </script>

@endsection
