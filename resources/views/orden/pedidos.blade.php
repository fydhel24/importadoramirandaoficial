@extends('adminlte::page')

@section('title', 'Pedidos por Semana')

@section('content_header')
    <h1>Pedidos para la Semana Seleccionada</h1>
@stop

@section('content')
    <div class="container-fluid mt-4">

        <div class="row">
            <div class="col-lg-4 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ number_format($totalMontoDeposito, 2) }} BS</h3>
                        <p>Monto Total de Depósitos</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ number_format($totalMontoEnviado, 2) }} BS</h3>
                        <p>Monto Total Enviado/Pagado</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ number_format($totalDiferencia, 2) }} BS</h3>
                        <p>Diferencia (Depósito - Enviado/Pagado)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                </div>
            </div>

            <!-- Agrega más tarjetas según sea necesario -->
        </div>
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Pedidos de {{ $semana->nombre }}</h2>
                <div class="card-tools d-flex flex-wrap justify-content-between">
                    <button type="button" class="btn btn-secondary" id="cambiarEstadoBtn">
                        <i class="fas fa-exchange-alt"></i> Cambiar Estado
                    </button>
                    <div class="btn-group mb-2">
                        <a href="#" class="btn btn-primary btn-sm me-2" id="generarNotaVentaBtn">
                            <i class="fas fa-file-pdf"></i> Generar Nota de Venta
                        </a>
                        <a href="#" class="btn btn-warning btn-sm me-2" id="generarPdfSeleccionadosBtn1">
                            <i class="fas fa-file-pdf"></i> Generar Reporte BCP por pedido
                        </a>
                        <a href="#" class="btn btn-danger btn-sm" id="generarPdfSeleccionadosBtn2">
                            <i class="fas fa-file-pdf"></i> Generar Reporte de Fichas por pedido
                        </a>
                        <a href="#" class="btn btn-warning btn-sm me-2" id="generarPdfSeleccionadosBtn3">
                            <i class="fas fa-file-pdf"></i> Confirmar Resumen BCP
                        </a>

                        <a href="#" class="btn btn-secondary btn-sm me-2" id="generarPdfSeleccionadosBtn30">
                            <i class="fas fa-file-pdf"></i> Resumen BCP respaldo
                        </a>
                    </div>

                    <div class="btn-group mb-2">
                        @can('orden.create')
                            <a href="{{ route('orden.create', $id) }}" class="btn btn-primary btn-sm me-2">
                                <i class="fas fa-plus"></i> Crear Pedido
                            </a>
                        @endcan
                        <a href="{{ route('orden.pdf', $id) }}" class="btn btn-success btn-sm me-2">
                            <i class="fas fa-file-pdf"></i> BCP
                        </a>
                        <a href="{{ route('orden.pdf.generate', $id) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-file-alt"></i> Reporte de Fichas
                        </a>
                    </div>

                    <!-- Botón para abrir el modal -->
                    <button type="button" class="btn btn-primary mb-2" data-toggle="modal" data-target="#destinoModal"
                        style="display: none;">
                        Seleccionar Destinos
                    </button>
                </div>

            </div>
            <div class="card-body">
                @if (session('success'))
                    <div id="success-alert" class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if (session('error'))
                    <div id="error-alert" class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <!-- Buscador -->
                <div class="mb-3">
                    <input type="text" id="searchInput" class="form-control" placeholder="Buscar en la tabla...">
                </div>
                <style>
                    .small-text {
                        font-size: 12px;
                        color: #080808;
                    }

                    .table td input[type="text"],
                    .table td input[type="number"],
                    .table td textarea {
                        width: 100%;
                        font-size: 12px;
                        padding: 5px;
                        color: #080808;

                    }

                    @media (max-width: 768px) {

                        .table td input {
                            font-size: 10px;
                        }
                    }

                    .table td {
                        padding: 1px;
                    }

                    .table td img {
                        max-width: 100%;
                        height: auto;
                    }

                    .table-responsive {
                        overflow-x: auto;
                        -webkit-overflow-scrolling: touch;
                    }

                    @media (max-width: 576px) {

                        .table td,
                        .table th {
                            font-size: 10px;
                        }

                        .table th {
                            padding: 5px;
                        }
                    }
                </style>
                <div class="table-responsive">
                    <table id="data_table" class="table table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>CI</th>
                                <th>Celular</th>
                                <th>Destino</th>
                                <th>Dirección</th>
                                <th>Detalle</th>
                                <th>Productos</th>
                                <th>Cantidad</th>
                                <th>Monto Depósito</th>
                                <th>Código</th>
                                <th>Estado</th>
                                {{-- <th>Foto Comprobante</th> --}}
                                <th>Acciones</th>
                                <th>confirma</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            @forelse($pedidos as $pedido)
                                <tr id="{{ $pedido->id }}">
                                    <td><input type="checkbox" class="pedido-checkbox" name="pedidos[]"
                                            value="{{ $pedido->id }}" data-estado="{{ $pedido->estado }}"
                                            data-estado-pedido="{{ $pedido->estado_pedido }}">
                                    </td>
                                    <td>{{ $pedido->id }}</td>
                                    <td>
                                        <textarea class="form-control" name="nombre" data-id="{{ $pedido->id }}">{{ $pedido->nombre }}</textarea>
                                    </td>
                                    <td>
                                        <textarea class="form-control" name="ci" data-id="{{ $pedido->id }}">{{ $pedido->ci }}</textarea>
                                    </td>
                                    <td>
                                        <textarea class="form-control" name="celular" data-id="{{ $pedido->id }}">{{ $pedido->celular }}</textarea>
                                    </td>
                                    <td>
                                        <textarea class="form-control" name="destino" data-id="{{ $pedido->id }}">{{ $pedido->destino }}</textarea>
                                    </td>

                                    <td>
                                        <textarea class="form-control" name="direccion" data-id="{{ $pedido->id }}">{{ $pedido->direccion }}</textarea>
                                    </td>
                                    <td>
                                        <textarea class="form-control" name="detalle" data-id="{{ $pedido->id }}">{{ $pedido->detalle }}</textarea>
                                    </td>

                                    <td class="small-text">{{ $pedido->productos }}</td>

                                    <td><input type="number" class="form-control"
                                            value="{{ $pedido->cantidad_productos }}" name="cantidad_productos"
                                            data-id="{{ $pedido->id }}"></td>
                                    <td><input type="number" class="form-control"
                                            value="{{ number_format($pedido->monto_deposito, 2) }}" name="monto_deposito"
                                            data-id="{{ $pedido->id }}"></td>
                                    <td><input type="text" class="form-control" value="{{ $pedido->codigo }}"
                                            name="codigo" data-id="{{ $pedido->id }}"></td>
                                    <td>
                                        <span
                                            class="badge 
                                                    @if ($pedido->estado == 'PAGADO') bg-success
                                                    @elseif($pedido->estado == 'POR COBRAR')
                                                        bg-warning
                                                    @else
                                                        bg-secondary @endif
                                                    text-dark">
                                            @if ($pedido->estado == 'PAGADO')
                                                <i class="fas fa-check-circle"></i>
                                            @elseif($pedido->estado == 'POR COBRAR')
                                                <i class="fas fa-clock"></i>
                                            @else
                                                <i class="fas fa-question-circle"></i>
                                            @endif
                                            {{ $pedido->estado }}
                                        </span>
                                    </td>


                                    {{-- <td>
                                        @if ($pedido->foto_comprobante)
                                            <img src="{{ asset('storage/' . $pedido->foto_comprobante) }}"
                                                alt="Comprobante" style="width: 100px; height: auto;">
                                        @else
                                            Sin foto
                                        @endif
                                    </td> --}}
                                    <td>
                                        @can('orden.edit')
                                            <a href="{{ route('orden.edit', $pedido->id) }}" class="btn btn-light btn-sm"
                                                style="color: #007bff; border-color: #007bff; background-color: #f0f8ff;">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('nota.venta', ['pedidoId' => $pedido->id]) }}"
                                                class="btn btn-primary">
                                                <i class="fa fa-file-invoice" aria-hidden="true"></i>
                                            </a>
                                        @endcan

                                        @can('orden.destroy')
                                            <form action="{{ route('orden.destroy', $pedido->id) }}" method="POST"
                                                style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-light btn-sm"
                                                    style="color: #dc3545; border-color: #dc3545; background-color: #f8d7da;">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        @endcan

                                    </td>


                                    <td>
                                        <?php if($pedido->estado_pedido === 'confirmado'): ?>
                                        <button class="btn btn-danger btn-sm" disabled>Confirmado</button>

                                        <!-- Nuevo botón de devolución -->
                                        <a href="#" class="btn btn-warning btn-sm return-pedido"
                                            data-id="<?php echo e($pedido->id); ?>">
                                            <i class="fas fa-undo"></i> Devolver Pedido
                                        </a>
                                        <?php else: ?>
                                        <button class="btn btn-success btn-sm confirm-pedido"
                                            data-id="<?php echo e($pedido->id); ?>">
                                            <i class="fas fa-check"></i> Pedido por confirmar
                                        </button>
                                        <?php endif; ?>

                                        <a href="#" class="btn btn-primary btn-sm" data-toggle="modal"
                                            data-target="#addProductModalNew"
                                            onclick="setPedidoIdNew(<?php echo e($pedido->id); ?>)">
                                            <i class="fas fa-plus"></i> Agregar Producto
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="17" class="text-center">No hay pedidos para esta semana.</td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="destinoModal" tabindex="-1" role="dialog" aria-labelledby="destinoModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="destinoModalLabel">Seleccionar Destinos</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="destinoForm">
                        @foreach (['Cochabamba', 'Santa Cruz', 'Oruro', 'Sucre', 'Tarija', 'Potosi', 'Trinidad', 'Cobija', 'Provincias', 'Camiri', 'Robore', 'Puerto Suarez', 'Riberalta', 'Rurrenabaque', 'Yacuiba', 'Tupiza', 'Villamontes', 'Bermejo', 'Zona Central', 'El Alto', 'Zona Miraflores', 'Zona Sur', 'Zona Sopocachi'] as $destino)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="destinos[]"
                                    value="{{ $destino }}" id="{{ $destino }}">
                                <label class="form-check-label" for="{{ $destino }}">
                                    {{ $destino }}
                                </label>
                            </div>
                        @endforeach
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-success" id="generarBcpBtn">Generar BCP</button>
                    <button type="button" class="btn btn-info" id="generarReporteFichaBtn">Generar Reporte de
                        Fichas</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Nueva Modal para agregar productos -->
    <div class="modal fade" id="addProductModalNew" tabindex="-1" role="dialog"
        aria-labelledby="addProductModalNewLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalNewLabel">Agregar Producto al Pedido</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addProductFormNew" action="" method="POST">
                        @csrf
                        <input type="hidden" id="pedidoIdNew" name="pedidoId">
                        <div class="form-group">
                            <label for="id_producto_new">Producto</label>
                            <select class="form-control select2" id="id_producto_new" name="id_producto">
                                @foreach ($productos as $producto)
                                    <option value="{{ $producto->id }}">{{ $producto->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="cantidad_new">Cantidad</label>
                            <input type="number" class="form-control" id="cantidad_new" name="cantidad" min="1"
                                required>
                        </div>
                        <div class="form-group">
                            <label for="precio_new">Precio</label>
                            <input type="number" class="form-control" id="precio_new" name="precio" step="0.01"
                                required>
                            <small id="precioHelpNew" class="form-text text-muted">El precio se multiplicará por la
                                cantidad.</small>
                        </div>
                        <div class="form-group">
                            <label for="total_new">Total</label>
                            <input type="text" class="form-control" id="total_new" readonly>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" form="addProductFormNew" class="btn btn-primary">Agregar Producto</button>
                </div>
            </div>
        </div>
    </div>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    @section('js')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/font-awesome@5.15.4/js/all.min.js"></script>
        <!-- JavaScript para calcular el total en la modal -->

        <script>
            $(document).ready(function() {
                $('.return-pedido').on('click', function(e) {
                    e.preventDefault();

                    const pedidoId = $(this).data('id');

                    Swal.fire({
                        title: '¿Está seguro?',
                        text: '¿Desea devolver este pedido? Esta acción restaurará el stock de los productos.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, devolver',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: `/pedidos/devolver/${pedidoId}`,
                                type: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            title: '¡Éxito!',
                                            text: response.success,
                                            icon: 'success',
                                            confirmButtonText: 'Aceptar'
                                        }).then(() => {
                                            location.reload();
                                        });
                                    } else if (response.error) {
                                        Swal.fire({
                                            title: 'Error',
                                            text: response.error,
                                            icon: 'error',
                                            confirmButtonText: 'Aceptar'
                                        });
                                    }
                                },
                                error: function(xhr) {
                                    console.error('Error en la solicitud:', xhr);
                                    let errorMessage =
                                        'Ha ocurrido un error al procesar la solicitud. Consulta la consola para más detalles.';

                                    if (xhr.responseJSON && xhr.responseJSON.error) {
                                        errorMessage = 'Error: ' + xhr.responseJSON.error;
                                    }

                                    Swal.fire({
                                        title: 'Error',
                                        text: errorMessage,
                                        icon: 'error',
                                        confirmButtonText: 'Aceptar'
                                    });
                                }
                            });
                        }
                    });
                });
            });
        </script>

        <script>
            function setPedidoIdNew(id) {
                const addProductFormNew = document.getElementById('addProductFormNew');
                const pedidoIdInputNew = document.getElementById('pedidoIdNew');
                pedidoIdInputNew.value = id;
                addProductFormNew.action = `{{ route('orden.store-product', '') }}/${id}`;
            }

            $.fn.modal.Constructor.prototype.enforceFocus = function() {};

            document.addEventListener('DOMContentLoaded', function() {
                $('#id_producto_new').select2({
                    placeholder: 'Seleccione un producto',
                    minimumInputLength: 3, // Mínimo de 3 letras para la búsqueda
                    maximumSelectionLength: 5, // Limitar a una sola selección
                    language: {
                        noResults: function() {
                            return 'No se encontraron resultados';
                        }
                    },
                    dropdownParent: $('#addProductModalNew') // Anclar el menú desplegable al modal
                }).on("select2:opening", function() {
                    $("#addProductModalNew").removeAttr("tabindex");
                }).on("select2:close", function() {
                    $("#addProductModalNew").attr("tabindex", "-1");
                });

                const cantidadInputNew = document.getElementById('cantidad_new');
                const precioInputNew = document.getElementById('precio_new');
                const totalInputNew = document.getElementById('total_new');

                cantidadInputNew.addEventListener('input', calcularTotalNew);
                precioInputNew.addEventListener('input', calcularTotalNew);

                function calcularTotalNew() {
                    const cantidad = parseFloat(cantidadInputNew.value) || 0;
                    const precio = parseFloat(precioInputNew.value) || 0;
                    totalInputNew.value = (cantidad * precio).toFixed(2);
                }

                // Función para actualizar los campos cantidad y monto en la tabla
                function actualizarCamposTabla(id, cantidad, precioTotal) {
                    // Buscar los campos de cantidad y monto_deposito en la tabla
                    const cantidadField = document.querySelector(`input[data-id="${id}"][name="cantidad_productos"]`);
                    const montoField = document.querySelector(`input[data-id="${id}"][name="monto_deposito"]`);

                    if (cantidadField && montoField) {
                        // Actualizar la cantidad
                        let cantidadActual = parseFloat(cantidadField.value) || 0;
                        cantidadField.value = (cantidadActual + cantidad).toFixed(2);

                        // Actualizar el monto_deposito
                        let montoActual = parseFloat(montoField.value) || 0;
                        montoField.value = (montoActual + precioTotal).toFixed(2);
                    }
                }

                // Agregar un evento para enviar el formulario y actualizar los campos de la tabla
                const form = document.getElementById('addProductFormNew');
                form.addEventListener('submit', function(event) {
                    event.preventDefault(); // Evitar el comportamiento predeterminado del formulario

                    // Obtener los valores del formulario
                    const cantidad = parseFloat(cantidadInputNew.value) || 0;
                    const precio = parseFloat(precioInputNew.value) || 0;
                    const precioTotal = cantidad * precio;

                    // Obtener el ID del pedido
                    const pedidoId = document.getElementById('pedidoIdNew').value;

                    // Enviar el formulario utilizando AJAX
                    fetch(form.action, {
                            method: 'POST',
                            body: new FormData(form),
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Actualizar los campos de cantidad y monto en la tabla
                                actualizarCamposTabla(pedidoId, cantidad, precioTotal);

                                // Mostrar la alerta de éxito con SweetAlert2
                                Swal.fire({
                                    title: 'Éxito',
                                    text: 'Producto agregado exitosamente.',
                                    icon: 'success',
                                    confirmButtonText: 'Aceptar'
                                }).then(() => {
                                    // Recargar la página después de la alerta
                                    window.location.reload();
                                });
                            } else {
                                // Mostrar la alerta de error con SweetAlert2
                                Swal.fire({
                                    title: 'Error',
                                    text: 'Hubo un error al agregar el producto.',
                                    icon: 'error',
                                    confirmButtonText: 'Aceptar'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error al agregar producto:', error);

                            // Mostrar la alerta de error con SweetAlert2
                            Swal.fire({
                                title: 'Error',
                                text: 'Hubo un error en el proceso.',
                                icon: 'error',
                                confirmButtonText: 'Aceptar'
                            });
                        });
                });
            });
        </script>



        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('searchInput');
                const tableBody = document.getElementById('tableBody');

                searchInput.addEventListener('input', function() {
                    const filter = searchInput.value.toLowerCase();
                    const rows = tableBody.getElementsByTagName('tr');

                    Array.from(rows).forEach(row => {
                        const cells = row.getElementsByTagName('td');
                        let match = false;

                        Array.from(cells).forEach(cell => {
                            if (cell.textContent.toLowerCase().includes(filter)) {
                                match = true;
                            }
                        });

                        row.style.display = match ? '' : 'none';
                    });
                });

                // Tiempo en milisegundos antes de ocultar las alertas (por ejemplo, 5 segundos = 5000 milisegundos)
                const alertTimeout = 5000;

                // Ocultar alerta de éxito
                const successAlert = document.getElementById('success-alert');
                if (successAlert) {
                    setTimeout(() => {
                        $(successAlert).alert('close');
                    }, alertTimeout);
                }

                // Ocultar alerta de error
                const errorAlert = document.getElementById('error-alert');
                if (errorAlert) {
                    setTimeout(() => {
                        $(errorAlert).alert('close');
                    }, alertTimeout);
                }
            });
        </script>
        <script>
            document.getElementById('generarBcpBtn').addEventListener('click', function() {
                const destinosSeleccionados = getSelectedDestinos();
                if (destinosSeleccionados.length > 0) {
                    const idSemana =
                        {{ $id }}; // Suponiendo que tienes el ID de la semana disponible en la vista
                    const url =
                        `{{ route('orden.pdf.nuevo', '') }}/${idSemana}?destinos=${destinosSeleccionados.join(',')}`;
                    window.location.href = url;
                } else {
                    alert('Por favor, selecciona al menos un destino.');
                }
            });

            document.getElementById('generarReporteFichaBtn').addEventListener('click', function() {
                const destinosSeleccionados = getSelectedDestinos();
                if (destinosSeleccionados.length > 0) {
                    const idSemana = {{ $id }};
                    const url =
                        `{{ route('orden.pdf.generate', '') }}/${idSemana}?destinos=${destinosSeleccionados.join(',')}`;
                    window.location.href = url;
                } else {
                    alert('Por favor, selecciona al menos un destino.');
                }
            });

            function getSelectedDestinos() {
                const selectedDestinos = [];
                const checkboxes = document.querySelectorAll('input[name="destinos[]"]:checked');
                checkboxes.forEach((checkbox) => {
                    selectedDestinos.push(checkbox.value);
                });
                return selectedDestinos;
            };
        </script>
        <script>
            document.getElementById('selectAll').addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.pedido-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });

            document.addEventListener('DOMContentLoaded', function() {
                const generarPdfBtn = document.getElementById('generarPdfSeleccionadosBtn1');

                if (generarPdfBtn) {
                    generarPdfBtn.addEventListener('click', function() {
                        const selectedPedidos = getSelectedPedidos();
                        const idSemana = {{ $id }}; // Asegúrate de que esta variable esté definida

                        if (selectedPedidos.length > 0) {
                            const url =
                                `{{ route('orden.pdf.nuevo', '') }}/${idSemana}?pedidos=${selectedPedidos.join(',')}`;
                            window.location.href = url;
                        } else {
                            alert('Por favor, selecciona al menos un pedido.');
                        }
                    });
                }

                function getSelectedPedidos() {
                    const selectedIds = [];
                    const checkboxes = document.querySelectorAll('input[name="pedidos[]"]:checked');
                    checkboxes.forEach((checkbox) => {
                        selectedIds.push(checkbox.value);
                    });
                    return selectedIds;
                }
            });
            /* document.addEventListener('DOMContentLoaded', function() {
                const generarPdfBtn = document.getElementById('generarPdfSeleccionadosBtn3');

                if (generarPdfBtn) {
                    generarPdfBtn.addEventListener('click', function() {
                        const selectedPedidos = getSelectedPedidos();
                        const idSemana = {{ $id }}; // Asegúrate de que esta variable esté definida

                        if (selectedPedidos.length > 0) {
                            // Primero, confirmamos todos los pedidos seleccionados
                            Swal.fire({
                                title: 'Confirmar pedidos seleccionados',
                                text: '¿Estás seguro de confirmar todos los pedidos seleccionados?',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: 'Sí, confirmar',
                                cancelButtonText: 'Cancelar'
                            }).then((result) => {
                                if (result.value) {
                                    // Enviar solicitud AJAX para confirmar los pedidos seleccionados
                                    $.ajax({
                                        url: "{{ route('orden.confirmarSeleccionados') }}", // Ruta que manejará la confirmación en el backend
                                        type: 'PATCH',
                                        data: {
                                            "_token": "{{ csrf_token() }}",
                                            "pedidos": selectedPedidos
                                        },
                                        success: function(data) {
                                            if (data.success) {
                                                Swal.fire(
                                                    'Confirmados!',
                                                    'Los pedidos seleccionados han sido confirmados.',
                                                    'success'
                                                ).then(() => {
                                                    // Abrir el PDF en una nueva pestaña
                                                    const url =
                                                        `{{ route('orden.pdf.bcResumen', '') }}/${idSemana}?pedidos=${selectedPedidos.join(',')}`;
                                                    window.open(url,
                                                    '_blank'); // Abrir en una nueva pestaña

                                                    // Recargar la página actual para reflejar los cambios
                                                    location.reload();
                                                });
                                            } else {
                                                Swal.fire(
                                                    'Error',
                                                    'Hubo un problema al confirmar los pedidos.',
                                                    'error'
                                                );
                                            }
                                        },
                                        error: function() {
                                            Swal.fire(
                                                'Error',
                                                'Hubo un error al intentar confirmar los pedidos.',
                                                'error'
                                            );
                                        }
                                    });
                                }
                            });
                        } else {
                            alert('Por favor, selecciona al menos un pedido.');
                        }
                    });
                }

                function getSelectedPedidos() {
                    const selectedIds = [];
                    const checkboxes = document.querySelectorAll('input[name="pedidos[]"]:checked');
                    checkboxes.forEach((checkbox) => {
                        selectedIds.push(checkbox.value);
                    });
                    return selectedIds;
                }
            }); */

            document.addEventListener('DOMContentLoaded', function() {
                const generarPdfBtn = document.getElementById('generarPdfSeleccionadosBtn30');

                if (generarPdfBtn) {
                    generarPdfBtn.addEventListener('click', function() {
                        const selectedPedidos = getSelectedPedidos();
                        const idSemana = {{ $id }}; // Asegúrate de que esta variable esté definida

                        if (selectedPedidos.length > 0) {
                            const url =
                                `{{ route('orden.pdf.bcResumen', '') }}/${idSemana}?pedidos=${selectedPedidos.join(',')}`;
                            window.location.href = url;
                        } else {
                            alert('Por favor, selecciona al menos un pedido.');
                        }
                    });
                }

                function getSelectedPedidos() {
                    const selectedIds = [];
                    const checkboxes = document.querySelectorAll('input[name="pedidos[]"]:checked');
                    checkboxes.forEach((checkbox) => {
                        selectedIds.push(checkbox.value);
                    });
                    return selectedIds;
                }
            });
            document.addEventListener('DOMContentLoaded', function() {
                const generarPdfBtn = document.getElementById('generarPdfSeleccionadosBtn3');

                if (generarPdfBtn) {
                    generarPdfBtn.addEventListener('click', function() {
                        const selectedPedidos = getSelectedPedidos();
                        const idSemana = {{ $id }}; // Asegúrate de que esta variable esté definida

                        if (selectedPedidos.length > 0) {
                            // Verificamos si alguno de los pedidos seleccionados ya está confirmado
                            const pedidosYaConfirmados = selectedPedidos.some(pedidoId => {
                                const pedidoElement = document.querySelector(
                                    `.confirm-pedido[data-id="${pedidoId}"]`);
                                return pedidoElement && pedidoElement.disabled;
                            });

                            if (pedidosYaConfirmados) {
                                Swal.fire(
                                    'Error',
                                    'Uno o más de los pedidos seleccionados ya están confirmados. No se pueden volver a confirmar.',
                                    'error'
                                );
                                return; // Evita continuar con el proceso si hay pedidos confirmados
                            }

                            // Confirmar todos los pedidos seleccionados
                            Swal.fire({
                                title: 'Confirmar pedidos seleccionados',
                                text: '¿Estás seguro de confirmar todos los pedidos seleccionados?',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: 'Sí, confirmar',
                                cancelButtonText: 'Cancelar'
                            }).then((result) => {
                                if (result.value) {
                                    // Enviar solicitud AJAX para confirmar los pedidos seleccionados
                                    $.ajax({
                                        url: "{{ route('orden.confirmarSeleccionados') }}", // Ruta que manejará la confirmación en el backend
                                        type: 'PATCH',
                                        data: {
                                            "_token": "{{ csrf_token() }}",
                                            "pedidos": selectedPedidos
                                        },
                                        success: function(data) {
                                            if (data.warning) {
                                                // Mostrar alerta si hay advertencia de stock
                                                Swal.fire(
                                                    'Advertencia',
                                                    data.warning,
                                                    'warning'
                                                ).then(() => {
                                                    // Recargar la página al cerrar la alerta de advertencia
                                                    location.reload();
                                                });
                                            } else if (data.success) {
                                                Swal.fire(
                                                    'Confirmados!',
                                                    'Los pedidos seleccionados han sido confirmados con éxito.',
                                                    'success'
                                                ).then(() => {
                                                    // Abrir el PDF en una nueva pestaña
                                                    const url =
                                                        `{{ route('orden.pdf.bcResumen', '') }}/${idSemana}?pedidos=${selectedPedidos.join(',')}`;
                                                    window.open(url,
                                                        '_blank'
                                                    ); // Abrir en una nueva pestaña

                                                    // Recargar la página actual para reflejar los cambios
                                                    location.reload();
                                                });
                                            }
                                        },
                                        error: function() {
                                            Swal.fire(
                                                'Error',
                                                'Hubo un error al intentar confirmar los pedidos.',
                                                'error'
                                            );
                                        }
                                    });
                                }
                            });
                        } else {
                            alert('Por favor, selecciona al menos un pedido.');
                        }
                    });
                }

                function getSelectedPedidos() {
                    const selectedIds = [];
                    const checkboxes = document.querySelectorAll('input[name="pedidos[]"]:checked');
                    checkboxes.forEach((checkbox) => {
                        selectedIds.push(checkbox.value);
                    });
                    return selectedIds;
                }
            });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const generarPdfBtn = document.getElementById('generarPdfSeleccionadosBtn2');

                if (generarPdfBtn) {
                    generarPdfBtn.addEventListener('click', function() {
                        const selectedPedidos = getSelectedPedidos();
                        const idSemana =
                            {{ $id }}; // Asegúrate de que esta variable esté definida en tu vista

                        if (selectedPedidos.length > 0) {
                            const url =
                                `{{ route('orden.pdf.nuevo.generate', '') }}/${idSemana}?pedidos=${selectedPedidos.join(',')}`;
                            window.location.href = url;
                        } else {
                            alert('Por favor, selecciona al menos un pedido.');
                        }
                    });
                }

                function getSelectedPedidos() {
                    const selectedIds = [];
                    const checkboxes = document.querySelectorAll('input[name="pedidos[]"]:checked');
                    checkboxes.forEach((checkbox) => {
                        selectedIds.push(checkbox.value);
                    });
                    return selectedIds;
                }
            });
            document.addEventListener('DOMContentLoaded', function() {
                const generarNotaVentaBtn = document.getElementById('generarNotaVentaBtn');

                if (generarNotaVentaBtn) {
                    generarNotaVentaBtn.addEventListener('click', function() {
                        const selectedPedidos = getSelectedPedidos();
                        const idSemana =
                            {{ $id }}; // Asegúrate de que esta variable esté definida en tu vista

                        if (selectedPedidos.length > 0) {
                            // Actualiza la URL con la ruta correcta
                            const url =
                                `{{ route('nota.imprimirSeleccionados') }}?semana=${idSemana}&pedidos=${selectedPedidos.join(',')}`;
                            window.location.href = url;
                        } else {
                            alert('Por favor, selecciona al menos un pedido.');
                        }
                    });
                }

                function getSelectedPedidos() {
                    const selectedIds = [];
                    const checkboxes = document.querySelectorAll('input[name="pedidos[]"]:checked');
                    checkboxes.forEach((checkbox) => {
                        selectedIds.push(checkbox.value);
                    });
                    return selectedIds;
                }
            });
        </script>
        <script>
            // confirmar pedido individual revisar si va funcionar 
            $(document).ready(function() {
                $('.confirm-pedido').click(function(event) {
                    event.preventDefault();
                    var id = $(this).data('id');
                    var row = $(this).closest('tr');

                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: "Una vez confirmado, no podrás revertir este cambio.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, confirmar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.value) {
                            $.ajax({
                                url: "{{ route('orden.confirm', '') }}/" + id,
                                type: 'PATCH',
                                data: {
                                    "_token": "{{ csrf_token() }}"
                                },
                                success: function(data) {
                                    if (data.warning) {
                                        // Mostrar alerta si hay advertencia de stock
                                        Swal.fire(
                                            'Advertencia',
                                            data.warning,
                                            'warning'
                                        ).then(() => {
                                            // Recargar la página al cerrar la alerta de advertencia
                                            location.reload();
                                        });
                                    } else if (data.success) {
                                        Swal.fire(
                                            'Confirmado!',
                                            'El pedido ha sido confirmado con éxito.',
                                            'success'
                                        ).then(() => {
                                            // Recargar la página al cerrar la alerta de éxito
                                            location.reload();
                                        });
                                    }
                                }
                            });
                        }
                    });
                });
            });
            document.addEventListener('DOMContentLoaded', function() {
                const cambiarEstadoBtn = document.getElementById('cambiarEstadoBtn');

                if (cambiarEstadoBtn) {
                    cambiarEstadoBtn.addEventListener('click', function() {
                        const selectedPedidos = getSelectedPedidos();
                        if (selectedPedidos.length > 0) {
                            Swal.fire({
                                title: '¿Estás seguro?',
                                text: "Esto cambiará el estado de los pedidos seleccionados.",
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: 'Sí, cambiar estado',
                                cancelButtonText: 'Cancelar'
                            }).then((result) => {
                                if (result.value) {
                                    $.ajax({
                                        url: "{{ route('orden.cambiarEstado') }}",
                                        type: 'PATCH',
                                        data: {
                                            "_token": "{{ csrf_token() }}",
                                            "pedidos": selectedPedidos,
                                            "estado": getNuevoEstado(
                                                selectedPedidos
                                            ) // Enviamos el nuevo estado basado en los estados actuales
                                        },
                                        success: function(data) {
                                            if (data.success) {
                                                Swal.fire(
                                                    'Estado cambiado!',
                                                    'Los pedidos han sido actualizados con éxito.',
                                                    'success'
                                                ).then(() => {
                                                    // Recargar la página al cerrar la alerta de éxito
                                                    location.reload();
                                                });
                                            } else {
                                                Swal.fire(
                                                    'Error',
                                                    'No se pudo cambiar el estado de los pedidos.',
                                                    'error'
                                                );
                                            }
                                        }
                                    });
                                }
                            });
                        } else {
                            alert('Por favor, selecciona al menos un pedido.');
                        }
                    });
                }

                // Obtener los pedidos seleccionados
                function getSelectedPedidos() {
                    const selectedIds = [];
                    const checkboxes = document.querySelectorAll('input[name="pedidos[]"]:checked');
                    checkboxes.forEach((checkbox) => {
                        selectedIds.push(checkbox.value);
                    });
                    return selectedIds;
                }

                // Determinar el nuevo estado basado en los estados actuales de los pedidos seleccionados
                function getNuevoEstado(selectedPedidos) {
                    const estados = [];

                    // Recolectamos todos los estados de los pedidos seleccionados
                    selectedPedidos.forEach((pedidoId) => {
                        const checkbox = document.querySelector(`input[name="pedidos[]"][value="${pedidoId}"]`);
                        if (checkbox) {
                            const estadoActual = checkbox.getAttribute('data-estado');
                            estados.push(estadoActual);
                        }
                    });

                    // Crear un objeto con los estados que van a cambiar
                    const cambios = {};

                    // Recorremos los estados y cambiamos cada uno al estado opuesto
                    estados.forEach(estado => {
                        if (estado === 'PAGADO') {
                            cambios[estado] = 'POR COBRAR'; // Si es PAGADO, cambiar a POR COBRAR
                        } else if (estado === 'POR COBRAR') {
                            cambios[estado] = 'PAGADO'; // Si es POR COBRAR, cambiar a PAGADO
                        }
                    });

                    // Ahora devolvemos el objeto 'cambios', con el estado opuesto para cada uno
                    return cambios;
                }

            });
        </script>


        <!-- Tu script personalizado para hacer editable la tabla -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const tableBody = document.getElementById('tableBody');
                const rows = tableBody.getElementsByTagName('tr');

                Array.from(rows).forEach(row => {
                    const inputs = row.querySelectorAll(
                        'input, textarea'); // Asegúrate de seleccionar también los <textarea>
                    Array.from(inputs).forEach(input => {
                        input.addEventListener('blur', function() {
                            const id = this.getAttribute('data-id');
                            const name = this.getAttribute('name');
                            const value = this.value;

                            // Enviar la solicitud AJAX
                            $.ajax({
                                url: "{{ route('orden.updatep', '') }}/" + id,
                                type: 'PATCH',
                                data: {
                                    "_token": "{{ csrf_token() }}",
                                    [name]: value
                                },
                                success: function(data) {
                                    if (data.success) {
                                        console.log('Campo actualizado con éxito');
                                    } else {
                                        console.log('Error al actualizar el campo');
                                    }
                                }
                            });
                        });
                    });
                });

                // Agregar evento para guardar al hacer clic en otro lugar
                document.addEventListener('click', function(event) {
                    if (event.target.tagName !== 'INPUT' && event.target.tagName !==
                        'TEXTAREA') { // Asegúrate de que también funcione al salir de un <textarea>
                        const activeElement = document.activeElement;
                        if (activeElement && (activeElement.tagName === 'INPUT' || activeElement.tagName ===
                                'TEXTAREA')) {
                            const id = activeElement.getAttribute('data-id');
                            const name = activeElement.getAttribute('name');
                            const value = activeElement.value;

                            // Enviar la solicitud AJAX
                            $.ajax({
                                url: "{{ route('orden.updatep', '') }}/" + id,
                                type: 'PATCH',
                                data: {
                                    "_token": "{{ csrf_token() }}",
                                    [name]: value
                                },
                                success: function(data) {
                                    if (data.success) {
                                        console.log('Campo actualizado con éxito');
                                    } else {
                                        console.log('Error al actualizar el campo');
                                    }
                                }
                            });
                        }
                    }
                });
            });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const checkboxes = document.querySelectorAll('.pedido-checkbox');
                const localStorageKey = 'selectedPedidos';

                const loadSelectedPedidos = () => {
                    const stored = localStorage.getItem(localStorageKey);
                    return stored ? JSON.parse(stored) : [];
                };

                const saveSelectedPedidos = (selected) => {
                    localStorage.setItem(localStorageKey, JSON.stringify(selected));
                };

                const restoreCheckboxes = () => {
                    let selected = loadSelectedPedidos();
                    let updated = false;

                    checkboxes.forEach((checkbox) => {
                        const estadoPedido = checkbox.dataset.estadoPedido;
                        const id = checkbox.value;

                        if (estadoPedido === 'confirmado') {
                            // Se desmarca automáticamente si está confirmado
                            if (checkbox.checked || selected.includes(id)) {
                                checkbox.checked = false;
                                selected = selected.filter(pid => pid !== id);
                                updated = true;
                            }
                        } else if (selected.includes(id)) {
                            checkbox.checked = true;
                        }
                    });

                    if (updated) {
                        saveSelectedPedidos(selected);
                    }
                };

                checkboxes.forEach((checkbox) => {
                    checkbox.addEventListener('change', function() {
                        const id = this.value;
                        let selected = loadSelectedPedidos();

                        // El usuario puede marcar o desmarcar manualmente incluso si está confirmado
                        if (this.checked) {
                            if (!selected.includes(id)) {
                                selected.push(id);
                            }
                        } else {
                            selected = selected.filter(pid => pid !== id);
                        }

                        saveSelectedPedidos(selected);
                    });
                });

                const selectAllCheckbox = document.getElementById('selectAll');
                if (selectAllCheckbox) {
                    selectAllCheckbox.addEventListener('change', function() {
                        let selected = loadSelectedPedidos();

                        checkboxes.forEach((checkbox) => {
                            const estadoPedido = checkbox.dataset.estadoPedido;
                            const id = checkbox.value;

                            // Evita seleccionar los "confirmados" con "Seleccionar todos"
                            if (estadoPedido === 'confirmado') {
                                checkbox.checked = false;
                                selected = selected.filter(pid => pid !== id);
                                return;
                            }

                            checkbox.checked = this.checked;

                            if (this.checked && !selected.includes(id)) {
                                selected.push(id);
                            } else if (!this.checked) {
                                selected = selected.filter(pid => pid !== id);
                            }
                        });

                        saveSelectedPedidos(selected);
                    });
                }

                restoreCheckboxes();
            });
        </script>

    @endsection
@endsection
