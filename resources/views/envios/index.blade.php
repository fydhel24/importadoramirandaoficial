@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.min.css">
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
            display: inline-block;
            padding: 12px 20px;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            color: #fff;
            border-radius: 30px;
            text-decoration: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
        }

        .btn-custom i {
            margin-right: 8px;
        }

        .btn-primary {
            background: linear-gradient(90deg, rgba(0, 123, 255, 1) 0%, rgba(0, 86, 179, 1) 100%);
            border: none;
        }

        .btn-success {
            background: linear-gradient(90deg, rgba(40, 167, 69, 1) 0%, rgba(27, 123, 50, 1) 100%);
            border: none;
        }

        .btn-info {
            background: linear-gradient(90deg, rgba(23, 162, 184, 1) 0%, rgba(18, 120, 136, 1) 100%);
            border: none;
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }

        .btn-custom:active {
            transform: translateY(1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .bg-rosado {
    background-color: #ff69b4; /* Color rosado */
    color: white; /* Texto en blanco */
}
    </style>
    <!-- DataTables CSS -->

    <link href="https://unpkg.com/slim-select@latest/dist/slimselect.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.4.0/css/responsive.dataTables.min.css" rel="stylesheet">
    <!-- CSS de Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection
@section('js')
    <!-- jQuery si no está incluido ya -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- JS de Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
    <script src="//code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="//cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="//cdn.datatables.net/responsive/2.4.0/js/dataTables.responsive.min.js"></script>
    <script src="//unpkg.com/slim-select@latest/dist/slimselect.min.js"></script>
    <script>
        $(document).ready(function() {
            // Filtrar productos y mostrar el select
            $('#productoSearch').on('input', function() {
                var searchTerm = $(this).val().toLowerCase();
                var hasResults = false;

                $('#productoSelect').show().find('option').each(function() {
                    var optionText = $(this).text().toLowerCase();
                    if (optionText.includes(searchTerm)) {
                        $(this).show();
                        hasResults = true;
                    } else {
                        $(this).hide();
                    }
                });

                if (!hasResults) {
                    $('#productoSelect').hide();
                }
            });

            // Seleccionar la opción y bloquear el input con opción de borrar
            $('#productoSelect').on('change', function() {
                var selectedText = $('#productoSelect option:selected').text();
                $('#productoSearch').val(selectedText).prop('disabled', true);
                $('#productoSelect').hide();
                $('#clearProductoBtn').show(); // Mostrar la "X" para borrar
            });

            // Limpiar selección del producto
            $('#clearProductoBtn').on('click', function() {
                $('#productoSearch').val('').prop('disabled', false);
                $('#productoSelect').val('').hide();
                $(this).hide(); // Ocultar la "X"
            });

            // Agregar producto y limpiar selección
            $('#agregarProductoBtn').click(function() {
                var productoId = $('#productoSelect').val();
                var productoNombre = $('#productoSelect option:selected').data('nombre');
                var productoStock = $('#productoSelect option:selected').data('stock');
                var cantidad = $('#cantidadInput').val();

                if (productoId === '') {
                    alert('Seleccione un producto');
                    return;
                }
                if (cantidad === '' || cantidad <= 0) {
                    alert('Ingrese una cantidad válida');
                    return;
                }
                if (parseInt(cantidad) > parseInt(productoStock)) {
                    alert('La cantidad no puede superar el stock disponible (' + productoStock + ')');
                    return;
                }

                var row = `
            <tr data-producto-id="${productoId}">
                <td>${productoNombre}</td>
                <td>${cantidad}</td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm eliminarProductoBtn">Eliminar</button>
                    <input type="hidden" name="productos[${productoId}][cantidad]" value="${cantidad}">
                    <input type="hidden" name="productos[${productoId}][id_producto]" value="${productoId}">
                </td>
            </tr>
        `;

                if ($('#productosTable tbody').find('tr[data-producto-id="' + productoId + '"]').length ==
                    0) {
                    $('#productosTable tbody').append(row);

                    // Limpiar el campo de búsqueda y ocultar la "X"
                    $('#productoSearch').val('').prop('disabled', false);
                    $('#productoSelect').val('').hide();
                    $('#clearProductoBtn').hide();
                    $('#cantidadInput').val('');
                } else {
                    alert('El producto ya ha sido agregado.');
                }
            });

            // Eliminar producto
            $(document).on('click', '.eliminarProductoBtn', function() {
                $(this).closest('tr').remove();
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            const table = $('#enviosTable').DataTable({

                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('envios.index') }}",
                    data: function(d) {
                        d.fecha_inicio = $('#fecha_inicio').val();
                        d.fecha_fin = $('#fecha_fin').val();
                    }
                },
                columns: [{
                        data: 'producto.nombre',
                        name: 'producto.nombre'
                    },
                    {
                        data: 'sucursalOrigen.nombre',
                        name: 'sucursalOrigen.nombre'
                    },
                    {
                        data: 'sucursale.nombre',
                        name: 'sucursale.nombre'
                    },
                    {
                        data: 'cantidad',
                        name: 'cantidad'
                    },
                    {
                        data: 'user.name',
                        name: 'user.name'
                    },
                    {
                        data: 'userDestino.name',
                        name: 'userDestino.name'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'acciones',
                        orderable: false,
                        searchable: false
                    }
                ],
                responsive: true,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/Spanish.json"
                }
            });

            // Manejar el evento del formulario de filtros
            $('#filtros-form').on('submit', function(e) {
                e.preventDefault();
                table.ajax.reload();
            });
        });

        function showRevertirModal(envioId, maxCantidad) {
            const form = document.getElementById('revertirForm');
            form.action = "{{ url('envios/revertir') }}/" + envioId;
            document.getElementById('cantidadRevertir').max = maxCantidad;
            $('#revertirModal').modal('show');
        }
    </script>
@endsection

@section('content')
    <div class="container">
        <div class="card-header">
            <h2 class="text-center font-weight-bold text-primary mb-4">Envíos de Productos a Sucursales 1</h2>
            <div class="filters mb-4 p-4 shadow bg-light rounded">

                <i class="fas fa-filter"></i> Filtros de Búsqueda

                <form method="GET" action="{{ route('envios.index') }}" class="form-inline justify-content-center">
                    <div class="form-group mx-2">
                        <label for="fecha_inicio" class="mr-2">Desde</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control"
                            value="{{ request('fecha_inicio') }}">
                    </div>
                    <div class="form-group mx-2">
                        <label for="fecha_fin" class="mr-2">Hasta</label>
                        <input type="date" name="fecha_fin" id="fecha_fin" class="form-control"
                            value="{{ request('fecha_fin') }}">
                    </div>
                    <button type="submit" class="btn btn-primary mx-2">Filtrar</button>
                    <a href="{{ route('envios.report', ['fecha_inicio' => request('fecha_inicio'), 'fecha_fin' => request('fecha_fin')]) }}"
                        class="btn btn-success mx-2" target="_blank">
                        <i class="fas fa-file-pdf"></i> Generar Reporte
                    </a>
                </form>

            </div>
        </div>

        <div class="text-center mb-4">
            <a href="{{ route('envios.solicitar') }}" class="btn-custom btn-primary">
                <i class="fas fa-plus-circle"></i> Nuevo Envío de Productos
            </a>

            <a href="{{ route('envios.transfer') }}" class="btn-custom btn-info">
                <i class="fas fa-exchange-alt"></i> Enviar Productos entre Sucursales
            </a>
            <!-- Nuevo Botón para enviar a sucursal 1 -->
            <button type="button" class="btn-custom btn-success" data-toggle="modal" data-target="#enviarSucursal1Modal">
                <i class="fas fa-truck"></i> Enviar a Sucursal 1
            </button>
        </div>

    </div>

    <div class="card-body">
        <i class="fas fa-table"></i> Envios Registradas
        <div class="table-responsive">
            <table id="enviosTable" class="table table-striped table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>Producto</th>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th>Cantidad</th>
                        <th>Usuario Origen</th>
                        <th>Usuario Destino</th>
                        <th>Fecha de Envío</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    </div>

    <!-- Modal para enviar a Sucursal 1 -->
    <div class="modal fade" id="enviarSucursal1Modal" tabindex="-1" role="dialog"
        aria-labelledby="enviarSucursal1ModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="enviarSucursal1Form" method="POST" action="{{ route('envios.sendSucursal1') }}">
                    @csrf
                    <div class="modal-header bg-rosado text-white">
                        <h5 class="modal-title" id="enviarSucursal1ModalLabel">Enviar Productos a Sucursal 1</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Buscador, Cantidad y Botones de acción -->
                        <div class="form-group">
                            <label for="productoSearch">Buscar Producto</label>
                            <div class="d-flex align-items-center">
                                <!-- Input de búsqueda -->
                                <input type="text" id="productoSearch" class="form-control"
                                    placeholder="Buscar producto..." autocomplete="off">

                                <!-- Botón de limpieza -->
                                <button type="button" id="clearProductoBtn" class="btn btn-secondary ml-2"
                                    style="display: none;">✖</button>

                                <!-- Input de cantidad -->
                                <input type="number" id="cantidadInput" class="form-control ml-2" min="1"
                                    placeholder="Cantidad" style="width: 100px;">

                                <!-- Botón para agregar producto -->
                                <button type="button" id="agregarProductoBtn"
                                    class="btn btn-success ml-2">Agregar</button>
                            </div>

                            <!-- Select para la lista de productos filtrados -->
                            <select id="productoSelect" class="form-control mt-2" size="5"
                                style="position: absolute; z-index: 1000; display: none;">
                                @foreach ($productosAlmacen as $producto)
                                    <option value="{{ $producto->id }}" data-nombre="{{ $producto->nombre }}"
                                        data-stock="{{ $producto->stock }}">
                                        {{ $producto->nombre }} (Stock: {{ $producto->stock }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tabla con los productos a enviar -->
                        <table class="table table-bordered table-striped" id="productosTable">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Las filas se agregarán dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Enviar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="revertirModal" tabindex="-1" aria-labelledby="revertirModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="revertirForm" method="POST" action="{{ route('envios.revertir', 0) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="revertirModalLabel">Revertir Envío</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Indica la cantidad que deseas revertir para este envío:</p>
                        <input type="number" id="cantidadRevertir" name="cantidad" class="form-control" min="1"
                            required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Revertir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
