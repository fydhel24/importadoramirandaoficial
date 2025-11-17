@extends('adminlte::page')
@section('adminlte_css')
    <link rel="stylesheet" type="text/css"
        href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css" />

    <link rel="stylesheet" type="text/css"
        href="https://cdn.datatables.net/v/bs4-4.6.0/jszip-2.5.0/dt-1.11.3/b-2.0.1/b-colvis-2.0.1/b-html5-2.0.1/b-print-2.0.1/r-2.2.9/datatables.min.css" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Incluye el CSS de Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        /* Estilos para la tabla */


        /* Estilos para los checkboxes */
        .dt-checkbox input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin: 0 auto;
            display: block;
        }

        .row-select {
            /* Color para el checkbox de selección */
            accent-color: #007bff;
            /* Azul */
        }

        /* Estilos generales para los checkboxes */
        .form-check-input {
            width: 40px;
            /* Aumento del tamaño */
            height: 40px;
            /* Aumento del tamaño */
            appearance: none;
            /* Desactiva el estilo predeterminado del checkbox */
            background-color: #fff;
            border: 2px solid #ccc;
            border-radius: 4px;
            position: relative;
            cursor: pointer;
        }

        /* Cuando el checkbox está seleccionado */
        .form-check-input:checked {
            background-color: #007bff;
            /* Color base cuando está seleccionado */
        }

        /* Colores específicos para cada tipo de checkbox utilizando clases */
        .lapaz-edit:checked {
            background-color: #dc3545;
            /* Rojo */
            border-color: #dc3545;
        }

        .enviado-edit:checked {
            background-color: #28a745;
            /* Verde */
            border-color: #28a745;
        }

        .extra-edit:checked {
            background-color: #ffc107;
            /* Amarillo */
            border-color: #ffc107;
        }

        .extra1-edit:checked {
            background-color: #17a2b8;
            /* Cyan */
            border-color: #17a2b8;
        }

        .extra2-edit:checked {
            background-color: #7f1673;
            /* Cyan */
            border-color: #7f1673;
        }

        .extra3-edit:checked {
            background-color: #0f0cc3;
            /* Cyan */
            border-color: #17a0f0cc32b8;
        }

        /* Colores cuando el checkbox está desmarcado */
        .lapaz-edit {
            border-color: #dc3545;
        }

        .enviado-edit {
            border-color: #28a745;
        }

        .extra-edit {
            border-color: #ffc107;
        }

        .extra1-edit {
            border-color: #17a2b8;
        }

        .extra2-edit {
            border-color: #7f1673;
        }

        .extra3-edit {
            border-color: #0f0cc3;
        }

        /* Añadir un círculo o cuadrado a los checkboxes seleccionados */
        .form-check-input:checked::before {
            content: '';
            position: absolute;
            top: 4px;
            left: 4px;
            width: 10px;
            height: 10px;
            background-color: #fff;
            border-radius: 50%;
            /* Círculo dentro del checkbox */
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-control {
            display: block;
            width: 100%;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-control:focus {
            color: #495057;
            background-color: #fff;
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .invalid-feedback {
            display: none;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: #dc3545;
        }

        .is-invalid~.invalid-feedback,
        .is-invalid~.invalid-tooltip,
        .is-valid~.valid-feedback,
        .is-valid~.valid-tooltip {
            display: block;
        }

        .styled-table tbody tr.enviado-row {
            background-color: #28a745;
            /* Verde */
        }

        /* Estilo para los labels grandes */
        .large-label {
            font-size: 1.5em;
            /* Aumenta el tamaño del texto */
            font-weight: bold;
            /* Hace que el texto sea más grueso */
            color: #333;
            /* Puedes cambiar el color si lo deseas */
        }

        .large-button {
            font-size: 1.5em;
            /* Aumenta el tamaño de la fuente */
            padding: 15px 30px;
            /* Espaciado interno */
        }

        .selected-filters {
            margin-top: 15px;
            /* Aumenta el margen superior */
        }

        .selected-span {
            display: inline-block;
            margin-top: 10px;
            /* Aumenta el margen superior */
            margin-right: 15px;
            /* Aumenta el margen derecho */
        }

        .selected-span .badge {
            font-size: 18px;
            /* Aumenta el tamaño de la fuente */
            padding: 8px 15px;
            /* Aumenta el padding */
        }

        .selected-span .remove {
            cursor: pointer;
            margin-left: 15px;
            /* Aumenta el margen entre el texto y la "x" */
        }


        .hidden-checkbox {
            display: none;
        }
    </style>
@endsection

@section('content')
    <style>
        .code-header {
            white-space: nowrap;
            padding: 10px 5px;
            /* Invertimos el padding para dar más espacio vertical */
            /* min-width: 40px; */
            text-align: center;
            writing-mode: vertical-lr;
            /* Opción moderna */
            transform: rotate(0deg);
            /* Rotación estándar */
            transform-origin: center center;
            height: 100px;
            /* Ajusta según sea necesario */
        }

        /* Ajuste adicional para la tabla */
        .table-responsive {
            overflow-x: auto;
        }

        /* Afecta solo a esta sección */
        .seccion-productos-personalizada {
            font-size: 14px;
        }

        .seccion-productos-personalizada .lista-productos-personalizada {
            max-height: 100px;
            overflow-y: auto;
        }

        .seccion-productos-personalizada .producto-item {
            font-size: 14px;
            color: #212529;
            /* Bootstrap text-dark */
            padding-left: 0;
            margin-bottom: 2px;
        }

        .seccion-productos-personalizada .texto-sin-productos {
            font-size: 14px;
            color: #6c757d;
            /* Bootstrap text-muted */
        }

        .seccion-productos-personalizada .texto-cantidad {
            font-size: 15px;
            font-weight: bold;
            color: #0d6efd;
            /* Bootstrap primary */
        }

        .seccion-productos-personalizada .texto-monto {
            font-size: 15px;
            font-weight: bold;
            color: #198754;
            /* Bootstrap success */
        }
    </style>
    <div class="container-fluid">

        <form method="POST" action="{{ route('envios.storecuaderno') }}" id="create-envio-form" class="needs-validation">
            @csrf
            <div class="border rounded p-4" style="background-color: #f8f9fa;">
                <div class="form-row align-items-center">

                    <!-- Número de Celular -->
                    <div class="col mb-2">
                        <label for="celular" class="mr-2 large-label">Ingrese el Número de Celular:</label>
                        <input type="text" class="form-control" id="celular" name="celular" required>
                        @error('celular')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Botón Crear Envío -->
                    <div class="col-auto mb-2">
                        <button type="submit" class="btn btn-primary mt-4 large-button">Crear Envío</button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Filtros -->
        <div class="border rounded p-4" style="background-color: #f8f9fa;">
            <form id="filters-form" class="mb-3">
                @csrf
                <div class="form-row align-items-center">

                    <!-- Filtro Select -->
                    <div class="col mb-2">
                        <!-- LP Checkbox -->
                        <!-- LP Checkbox -->
                        <div class="col mb-2 hidden-checkbox">
                            <input type="checkbox" id="lapaz" name="lapaz" value="1"
                                class="form-check-input lapaz-edit">
                            <label for="lapaz" class="mr-2 large-label">La Paz</label>
                        </div>

                        <!-- EV Checkbox -->
                        <div class="col mb-2 hidden-checkbox">
                            <input type="checkbox" id="enviado" name="enviado" value="1"
                                class="form-check-input enviado-edit">
                            <label for="enviado" class="mr-2 large-label">Enviado</label>
                        </div>

                        <!-- Extra Checkbox -->
                        <div class="col mb-2 hidden-checkbox">
                            <input type="checkbox" id="extra" name="extra" value="1"
                                class="form-check-input extra-edit">
                            <label for="extra" class="mr-2 large-label">Extra 1</label>
                        </div>

                        <!-- Extra1 Checkbox -->
                        <div class="col mb-2 hidden-checkbox">
                            <input type="checkbox" id="extra1" name="extra1" value="1"
                                class="form-check-input extra1-edit">
                            <label for="extra1" class="mr-2 large-label">Extra 2</label>
                        </div>


                        <select id="filters_select" name="filters" class="form-control">
                            <option value="">Seleccione un filtro</option>
                            <option value="lapaz">La Paz</option>
                            <option value="enviado">Enviado</option>
                            <option value="extra">Extra 1</option>
                            <option value="extra1">Extra 2</option>
                        </select>
                        <!-- Contenedor de los filtros seleccionados -->
                        <div id="filters_container" class="selected-filters">
                            <!-- Los filtros seleccionados aparecerán aquí -->
                        </div>
                    </div>

                    <!-- Fecha y hora de inicio -->
                    <div class="col mb-2">
                        <label for="start_date">Fecha y Hora de Inicio</label>
                        <input type="text" class="form-control flatpickr" id="start_date" name="start_date" readonly />
                    </div>

                    <!-- Fecha y hora de fin -->
                    <div class="col mb-2">
                        <label for="end_date">Fecha y Hora de Fin</label>
                        <input type="text" class="form-control flatpickr" id="end_date" name="end_date" readonly />
                    </div>

                    <!-- Botón Filtrar -->
                    <div class="col-auto mb-2">
                        <button type="button" class="btn btn-primary mt-4 large-button" id="filtrar">Filtrar</button>
                    </div>

                </div>
            </form>
        </div>




        <script>
            document.addEventListener('DOMContentLoaded', function() {

                const filtersSelect = document.getElementById('filters_select');
                const filtersContainer = document.getElementById('filters_container');

                // Diccionario para mapear los filtros con sus checkboxes
                const filterCheckboxes = {
                    'lapaz': document.getElementById('lapaz'),
                    'enviado': document.getElementById('enviado'),
                    'extra': document.getElementById('extra'),
                    'extra1': document.getElementById('extra1')
                };

                // Función para manejar la selección de filtros
                function handleSelectChange() {
                    const selectedValue = filtersSelect.value;

                    // Verificar si el filtro ya está seleccionado
                    if (selectedValue && !isFilterAlreadySelected(selectedValue)) {
                        // Crear el span para el filtro seleccionado
                        const selectedSpan = document.createElement('span');
                        selectedSpan.classList.add('badge', 'badge-success', 'selected-span');
                        selectedSpan.textContent = selectedValue.charAt(0).toUpperCase() + selectedValue.slice(
                            1); // Primera letra en mayúsculas

                        // Crear el botón 'x' para eliminar el filtro
                        const removeButton = document.createElement('span');
                        removeButton.classList.add('remove');
                        removeButton.textContent = 'x';
                        removeButton.addEventListener('click', function() {
                            removeFilter(selectedSpan, selectedValue);
                        });

                        // Agregar el botón 'x' al span
                        selectedSpan.appendChild(removeButton);

                        // Agregar el span al contenedor de filtros
                        filtersContainer.appendChild(selectedSpan);

                        // Marcar el checkbox correspondiente
                        filterCheckboxes[selectedValue].checked = true;

                        // Restablecer el select
                        filtersSelect.value = '';
                    }
                }

                // Función para verificar si el filtro ya está seleccionado
                function isFilterAlreadySelected(filterValue) {
                    const existingFilters = filtersContainer.querySelectorAll('.selected-span');
                    for (let filter of existingFilters) {
                        if (filter.textContent.toLowerCase().includes(filterValue.toLowerCase())) {
                            return true; // El filtro ya está seleccionado
                        }
                    }
                    return false; // El filtro no está seleccionado
                }

                // Función para eliminar un filtro
                function removeFilter(selectedSpan, filterValue) {
                    filtersContainer.removeChild(selectedSpan);

                    // Desmarcar el checkbox correspondiente
                    filterCheckboxes[filterValue].checked = false;
                }

                // Agregar event listener al select
                filtersSelect.addEventListener('change', handleSelectChange);

            });
        </script>

        <div class="table-responsive">
            <table class="table table-bordered table-striped mt-4 styled-table" id="envios-table">
                <button id="update-semana" class="btn btn-sm btn-primary" data-toggle="modal"
                    data-target="#semanaModal">Actualizar Semana</button>
                <div class="mt-2 mb-4">
                    <strong id="totalRows" class="text-primary">Total de filas: 0</strong>
                </div>
                <thead class="linear-gradient">
                    <tr>

                        <th><input type="checkbox" id="select-all"></th>
                        {{-- <th>id</th> --}}
                        <th class="code-header">LA PAZ</th>
                        <th class="code-header">ENVIADO</th>
                        <th class="code-header">P. LISTO</th>
                        <th class="code-header">P. PENDIENTE</th>
                        <th class="code-header">EXTRA 3</th>
                        <th class="code-header">EXTRA 4</th>
                        <th>DATOS</th>
                        <th>Productos</th>
                        {{-- <th>Departamento</th>
                        <th>Monto de Pago</th> --}}
                        <th>Detalle / Descripción</th> <!-- Unificar Detalle y Descripción -->
                        <th>Id Pedido</th>
                        <th>Estado</th>
                        <th>Fecha de Creación</th>
                        {{--                         
                        <th>cantidad</th>
                        <th>costo</th> --}}
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <div class="modal fade" id="semanaModal" tabindex="-1" role="dialog" aria-labelledby="semanaModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="semanaModalLabel">Actualizar Semana</h5>

                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <h4 style="display: none;"> Envíos Seleccionados:</h4>
                        <ul id="selected-envios" style="display: none;"></ul>
                        <h4>Seleccionar Semana:</h4>
                        <select id="semana-select" class="form-control">
                            @foreach ($semanas as $semana)
                                <option value="{{ $semana->id }}">{{ $semana->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="send-semana">Enviar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal para editar productos -->
    <div class="modal fade" id="editProductModal" tabindex="-1" role="dialog" aria-labelledby="editProductModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProductModalLabel">Editar Productos del Envío</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Contenedor para mostrar la lista de productos -->
                    <div class="form-group">
                        <label>Productos Existentes:</label>
                        <ul id="productList" class="list-group">
                            <!-- Los productos se cargarán aquí dinámicamente -->
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="saveChanges">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </div>
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
                        <input id="pedidoIdNew" name="pedidoId" class="hidden-checkbox">
                        <input id="envioIdNew" name="envioId" class="hidden-checkbox">
                        <!-- Campo oculto para el id de envio -->
                        <div class="form-group">
                            <label for="id_producto_new">Producto</label>
                            <select class="form-control select2" id="id_producto_new" name="id_producto">
                                @foreach ($productos as $producto)
                                    <option value="{{ $producto['id'] }}">{{ $producto['nombre'] }} (Stock:
                                        {{ $producto['cantidad_sucursal'] }})</option>
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
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
@endsection

@section('adminlte_js')
    <!-- DataTables JS & CSS (CDN) -->
    <link href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.4.0/css/responsive.dataTables.min.css" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.0/js/dataTables.responsive.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/font-awesome@5.15.4/js/all.min.js"></script>
    <!-- JavaScript para calcular el total en la modal -->




    <script>
        // Añade esto a tu archivo JavaScript
        $(document).ready(function() {
            // Manejar clic en botón de confirmar pedido
            $(document).on('click', '.confirm-pedido', function(e) {
                e.preventDefault();

                var pedidoId = $(this).data('id');
                var button = $(this);

                // Mostrar confirmación
                if (confirm('¿Estás seguro de confirmar este pedido?')) {
                    // Realizar petición AJAX para confirmar el pedido
                    $.ajax({
                        url: '/pedidos/' + pedidoId + '/confirm',
                        type: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                // Mostrar mensaje de éxito
                                alert(response.success);

                                // Actualizar los botones en la fila
                                var parentRow = button.closest('tr');
                                var buttonCell = button.parent();

                                // Reemplazar los botones con los de estado confirmado
                                buttonCell.html(`
                            <button class="btn btn-danger btn-sm" disabled>Confirmado</button>
                            <a href="#" class="btn btn-warning btn-sm return-pedido" data-id="${pedidoId}">
                                <i class="fas fa-undo"></i> Devolver Pedido
                            </a>
                        `);

                                // Actualizar la tabla si es necesario
                                if (typeof dataTable !== 'undefined') {
                                    dataTable.ajax.reload(null, false);
                                }
                            } else if (response.warning) {
                                // Mostrar advertencia si hay problemas de stock
                                alert(response.warning);
                            }
                        },
                        error: function(xhr) {
                            alert('Error al confirmar el pedido: ' + xhr.responseText);
                        }
                    });
                }
            });

            // Manejar clic en botón de devolver pedido
            $(document).on('click', '.return-pedido', function(e) {
                e.preventDefault();

                var pedidoId = $(this).data('id');
                var button = $(this);

                // Mostrar confirmación
                if (confirm('¿Estás seguro de devolver este pedido al inventario?')) {
                    // Realizar petición AJAX para devolver el pedido
                    $.ajax({
                        url: '/pedidos/' + pedidoId + '/devolver',
                        type: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')

                        },
                        success: function(response) {
                            if (response.success) {
                                // Mostrar mensaje de éxito
                                alert(response.success);

                                // Actualizar los botones en la fila
                                var parentRow = button.closest('tr');
                                var buttonCell = button.parent();

                                // Reemplazar los botones con los de estado por confirmar
                                buttonCell.html(`
                            <button class="btn btn-success btn-sm confirm-pedido" data-id="${pedidoId}">
                                <i class="fas fa-check"></i> Pedido por confirmar
                            </button>
                        `);

                                // Actualizar la tabla si es necesario
                                if (typeof dataTable !== 'undefined') {
                                    dataTable.ajax.reload(null, false);
                                }
                            }
                        },
                        error: function(xhr) {
                            alert('Error al devolver el pedido: ' + xhr.responseText);
                        }
                    });
                }
            });
        });
    </script>



    <script>
        function setPedidoAndEnvioIds(id_pedido, id_envio) {
            const addProductFormNew = document.getElementById('addProductFormNew');
            const pedidoIdInputNew = document.getElementById('pedidoIdNew');
            const envioIdInputNew = document.getElementById('envioIdNew');

            // Asignar los valores a los campos ocultos
            pedidoIdInputNew.value = id_pedido !== null ? id_pedido : ''; // Si id_pedido es null, lo dejamos vacío
            envioIdInputNew.value = id_envio !== null ? id_envio : ''; // Si id_envio es null, lo dejamos vacío

            // Establecer la acción del formulario con ambos IDs
            addProductFormNew.action =
                `{{ route('envio.envioproductos', ['id_pedido' => ':id_pedido', 'id_envio' => ':id_envio']) }}`
                .replace(':id_pedido', id_pedido)
                .replace(':id_envio', id_envio);
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

                                const envioId = document.getElementById('envioIdNew').value;

                                // Llamada para obtener datos actualizados del pedido
                                $.ajax({
                                    type: 'GET',
                                    url: '{{ route('envios.getPedidoData') }}',
                                    data: {
                                        id_pedido: pedidoId,
                                        id_envio: envioId
                                    },
                                    success: function(data) {
                                        console.log(
                                            'Datos actualizados del pedido recibidos:',
                                            data);

                                        if (data) {
                                            $('#productos_' + envioId).text(data
                                                .productos || 'Sin productos');
                                            $('#cantidad_productos_' + envioId)
                                                .text(data.cantidad_productos ||
                                                    '0');
                                            $('#monto_deposito_' + envioId).text(
                                                data.monto_deposito || '0.00');

                                            $('#monto_deposito_' + envioId).text(
                                                data.monto_deposito || '0.00');
                                        }

                                        // Limpiar formulario
                                        $('#id_producto_new').val(null).trigger(
                                            'change');
                                        $('#cantidad_new').val('');
                                        $('#precio_new').val('');
                                        $('#total_new').val('');

                                        // Recargar tabla si deseas sincronizar backend
                                        $('#envios-table').DataTable().ajax.reload(
                                            null, false);
                                    },
                                    error: function(xhr, status, error) {
                                        console.error(
                                            'Error al obtener datos del pedido:',
                                            error);
                                        console.error('Respuesta del servidor:', xhr
                                            .responseText);
                                    }
                                });

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
        // Inicializa los campos de fecha y hora con flatpickr
        $(document).ready(function() {
            $(".flatpickr").flatpickr({
                enableTime: true, // Habilita la selección de hora
                time_24hr: true, // Usa el formato de 24 horas
                dateFormat: "Y-m-d H:i", // Establece el formato de fecha y hora (ejemplo: 2025-01-09 14:30)
            });
        });
    </script>

    <script>
        // Configuración de AJAX con CSRF
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $(document).ready(function() {

            $(document).on('change', '.enviado-edit', function() {
                var row = $(this).closest('tr');
                if ($(this).is(':checked')) {
                    row.addClass('enviado-row');
                } else {
                    row.removeClass('enviado-row');
                }
            });

            // Ensure initial state is correct
            $('#envios-table').on('draw.dt', function() {
                $('.enviado-edit').each(function() {
                    var row = $(this).closest('tr');
                    if ($(this).is(':checked')) {
                        row.addClass('enviado-row');
                    } else {
                        row.removeClass('enviado-row');
                    }
                });
            });


            let selectedRows = []; // Array para trackear los IDs de las filas seleccionadas
            // Trackear el estado de los checkboxes
            $(document).on('change', '.row-select', function() {
                var id = $(this).data('id');
                if ($(this).is(':checked')) {
                    if (!selectedRows.includes(id)) {
                        selectedRows.push(id);
                    }
                } else {
                    selectedRows = selectedRows.filter(row => row !== id);
                }
                console.log('IDs seleccionados:',
                    selectedRows); // Mostrar los IDs seleccionados en la consola
            });

            // Restaurar el estado de los checkboxes al recargar la tabla
            $('#envios-table').on('draw.dt', function() {
                $('.row-select').each(function() {
                    var id = $(this).data('id');
                    if (selectedRows.includes(id)) {
                        $(this).prop('checked', true);
                    } else {
                        $(this).prop('checked', false);
                    }
                });
            });

            // Manejo del botón para abrir la modal y mostrar los envíos seleccionados
            $('#update-semana').on('click', function() {
                // Mostrar los envíos seleccionados en la modal
                var enviosList = '';
                selectedRows.forEach(function(id) {
                    var row = $('#envios-table').DataTable().row('.row-select[data-id="' + id +
                        '"]').data();
                    if (row) {
                        enviosList += `<li>ID: ${id} - Celular: ${celular}</li>`;
                    } else {
                        // Si no se encuentra la fila en la tabla actual, solo mostrar el ID
                        enviosList += `<li style="display: none;">ID: ${id}</li>`;
                    }
                });
                $('#selected-envios').html(enviosList);
            });


            // Manejo del botón para enviar los datos al controlador
            // Manejo del botón para enviar los datos al controlador
            $('#send-semana').on('click', function() {
                var idSemana = $('#semana-select').val();

                if (selectedRows.length > 0 && idSemana) {
                    $.ajax({
                        type: 'POST',
                        url: '{{ route('envios.actualizarSemana') }}',
                        data: {
                            envios: selectedRows, // Enviar todos los IDs seleccionados
                            id_semana: idSemana
                        },
                        success: function(response) {
                            if (response.success) {
                                alert('Semana actualizada correctamente');
                                $('#semanaModal').modal('hide');
                            } else {
                                alert('Error al actualizar la semana: ' + response.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error al actualizar la semana:', xhr.status, xhr
                                .responseText, status, error);
                            alert(
                                'Error al actualizar la semana. Consulta la consola para más detalles.'
                            );
                        }
                    });
                } else {
                    alert('Debe seleccionar una semana y tener envíos seleccionados.');
                }
            });

            // Inicializar Flatpickr para las fechas y horas con formato de 24 horas
            $('#start_date').flatpickr({
                enableTime: true, // Habilita la selección de hora
                time_24hr: true, // Usa el formato de 24 horas
                dateFormat: "Y-m-d H:i", // Establece el formato de fecha y hora (ejemplo: 2025-01-09 14:30)
            });

            $('#end_date').flatpickr({
                enableTime: true, // Habilita la selección de hora
                time_24hr: true, // Usa el formato de 24 horas
                dateFormat: "Y-m-d H:i", // Establece el formato de fecha y hora (ejemplo: 2025-01-09 14:30)
            });


            // Inicializar Select2 para los filtros de estado


            var table = $('#envios-table').DataTable({
                processing: true,
                serverSide: true,

                stateSave: true,
                ajax: {
                    url: "{{ route('envios.data') }}",
                    data: function(d) {
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.lapaz = $('#lapaz').prop('checked') ? 1 :
                            ''; // Si el checkbox está marcado, se pasa el valor
                        d.enviado = $('#enviado').prop('checked') ? 1 :
                            ''; // Si el checkbox está marcado, se pasa el valor
                        d.extra = $('#extra').prop('checked') ? 1 :
                            ''; // Si el checkbox extra está marcado, se pasa el valor
                        d.extra1 = $('#extra1').prop('checked') ? 1 :
                            ''; // Si el checkbox extra1 está marcado, se pasa el valor
                        d.extra2 = $('#extra2').prop('checked') ? 1 :
                            ''; // Si el checkbox extra1 está marcado, se pasa el valor
                        d.extra3 = $('#extra3').prop('checked') ? 1 :
                            ''; // Si el checkbox extra1 está marcado, se pasa el valor
                        d.celular = $('#celular').val();
                        //d.id = $('#id').val();

                    }
                },
                pageLength: 10, // 👈 cantidad inicial de filas
                lengthMenu: [10, 25, 50, 100, 200], // 👈 opciones disponibles


                columns: [


                    {
                        data: null,
                        name: 'id',
                        orderable: false,
                        searchable: true,
                        className: "dt-body-center dt-checkbox", // Centro la celda y agrega la clase para el checkbox
                        render: function(data, type, row) {
                            return '<input type="checkbox" class="form-check-input row-select" data-id="' +
                                row.id + '"><label>' + row.id + '</label>';

                        }
                    },

                    {
                        data: 'lapaz',
                        name: 'lapaz',
                        orderable: false,
                        searchable: false,
                        className: "dt-checkbox", // Agrega la clase para el checkbox
                        render: function(data, type, row) {
                            return '<input type="checkbox" class="form-check-input lapaz-edit" ' + (
                                data ? 'checked' : '') + ' data-id="' + row.id + '">';
                        }
                    },
                    {
                        data: 'enviado',
                        name: 'enviado',
                        orderable: false,
                        searchable: false,
                        className: "dt-checkbox", // Agrega la clase para el checkbox
                        render: function(data, type, row) {
                            return '<input type="checkbox" class="form-check-input enviado-edit" ' +
                                (data ? 'checked' : '') + ' data-id="' + row.id + '">';
                        }
                    },
                    {
                        data: 'extra',
                        name: 'extra',
                        orderable: false,
                        searchable: false,
                        className: "dt-checkbox", // Agrega la clase para el checkbox
                        render: function(data, type, row) {
                            return '<input type="checkbox" class="form-check-input extra-edit" ' + (
                                data ? 'checked' : '') + ' data-id="' + row.id + '">';
                        }
                    },




                    {
                        data: 'extra1',
                        name: 'extra1',
                        orderable: false,
                        searchable: false,
                        className: "dt-checkbox", // Agrega la clase para el checkbox
                        render: function(data, type, row) {
                            return '<input type="checkbox" class="form-check-input extra1-edit" ' +
                                (data ? 'checked' : '') + ' data-id="' + row.id + '">';
                        }
                    },
                    {
                        data: 'extra2',
                        name: 'extra2',
                        orderable: false,
                        searchable: false,
                        className: "dt-checkbox", // Agrega la clase para el checkbox
                        render: function(data, type, row) {
                            return '<input type="checkbox" class="form-check-input extra2-edit" ' +
                                (data ? 'checked' : '') + ' data-id="' + row.id + '">';
                        }
                    },
                    {
                        data: 'extra3',
                        name: 'extra3',
                        orderable: false,
                        searchable: false,
                        className: "dt-checkbox", // Agrega la clase para el checkbox
                        render: function(data, type, row) {
                            return '<input type="checkbox" class="form-check-input extra3-edit" ' +
                                (data ? 'checked' : '') + ' data-id="' + row.id + '">';
                        }
                    },




                    {
                        data: null,
                        name: 'celular',
                        className: "dt-input",
                        render: function(data, type, row) {
                            return `
                                <div>
                                    <label>Celular:</label>
                                    <input type="text" class="form-control celular-edit" value="${row.celular}" data-id="${row.id}">
                                    <label>Departamento:</label>
                                    <input type="text" class="form-control departamento-edit" value="${row.departamento}" data-id="${row.id}">
                                    <label>Monto de Pago:</label>
                                    <input type="text" class="form-control monto_de_pago-edit" value="${row.monto_de_pago}" data-id="${row.id}">
                                </div>
                            `;
                        }
                    },


                    {
                        data: 'productos',
                        name: 'productos',
                        render: function(data, type, row) {
                            const addButtonHtml = `
                            <a href="#" class="btn btn-sm btn-primary mb-2" data-toggle="modal" data-target="#addProductModalNew" onclick="setPedidoAndEnvioIds(${row.id_pedido || null}, ${row.id})">
                                <i class="fas fa-plus"></i>
                            </a>`;

                            const productos = data ? (typeof data === 'string' ? data.split(',') :
                                data) : [];

                            const productosHtml = productos.length > 0 ?
                                `<div class="lista-productos-personalizada">
                                    ${productos.map(p => `<div class="producto-item">${p.trim()}</div>`).join('')}
                               </div>` :
                                `<span class="texto-sin-productos">Sin productos</span>`;

                            const cantidadHtml =
                                `<span id="cantidad_productos_${row.id}" class="texto-cantidad">${row.cantidad_productos || 'N/A'}</span>`;
                            const montoHtml =
                                `<span id="monto_deposito_${row.id}" class="texto-monto">${row.monto_deposito || 'N/A'}</span>`;

                            return `
                            <div class="seccion-productos-personalizada d-flex flex-column">
                                ${addButtonHtml}
                                <div><strong>Productos:</strong><br>${productosHtml}</div>
                                <div><strong>Cantidad:</strong> ${cantidadHtml}</div>
                                <div><strong>Costo:</strong> ${montoHtml}</div>
                            </div>`;
                        }
                    },




                    {
                        // Unificar Detalle y Descripción
                        data: null,
                        name: null,
                        render: function(data, type, row) {
                            return `
            <div>
                <label>Detalle:</label>

                <textarea class="form-control detalle-edit" data-id="${row.id}">${data.detalle}</textarea>
                <label>Descripción:</label>
                 <textarea class="form-control descripcion-edit" data-id="${row.id}">${data.descripcion}</textarea>
            </div>
        `;
                        }
                    },




                    {
                        data: 'id_pedido',
                        name: 'id_pedido',
                        render: function(data, type, row) {
                            return `
                    <input type="text" value="${data}" id="id_pedido_autocomplete_${row.id}" class="form-control" readonly>
                    <input type="text" class="form-control" id="id_pedido_search" data-id="${row.id}">
                    <div id="pedidoSearchResults${row.id}" class="mt-2"></div>
                `;
                        }
                    },



                    {
                        data: null,
                        name: 'estado',
                        render: function(data, type, row) {
                            const estadoRaw = row.pedido?.estado;
                            const estado = estadoRaw?.trim().toUpperCase();

                            if (!estado) {
                                return '<span class="badge bg-secondary text-dark"><i class="fas fa-question-circle"></i> Sin estado</span>';
                            }

                            let badgeClass = 'bg-secondary text-dark';
                            let icon = '<i class="fas fa-question-circle"></i>';

                            if (estado === 'PAGADO') {
                                badgeClass = 'bg-success text-white'; // Letras blancas para PAGADO
                                icon = '<i class="fas fa-check-circle"></i>';
                            } else if (estado === 'POR COBRAR') {
                                badgeClass = 'bg-warning text-dark';
                                icon = '<i class="fas fa-clock"></i>';
                            }

                            return `<span class="badge ${badgeClass}">${icon} ${estado}</span>`;
                        }
                    },



                    {
                        data: 'fecha_hora_creada',
                        name: 'fecha_hora_creada',
                        className: "dt-input", // Agrega la clase para el input
                        render: function(data, type, row) {
                            return '<input type="datetime" class="form-control fecha_hora_creada-edit" value="' +
                                data + '" data-id="' + row.id + '">';
                        }
                    },

                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            var id_pedido = row.id_pedido;
                            var id = row.id;
                            // Botón de editar si hay id_pedido


                            var buttonHTML = `
      <button class="btn btn-sm btn-danger delete-envio" data-id="${row.id}">
        <i class="fas fa-trash-alt"></i>
      </button>
    `;

                            //                         // Si el id_pedido no es nulo, agrega el botón para agregar producto
                            //                         buttonHTML += `
                        //   ${
                        //     `<a href="#" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addProductModalNew" onclick="setPedidoAndEnvioIds(${row.id_pedido}, ${row.id})">
                            //               <i class="fas fa-plus"></i>
                            //             </a>` 
                        //   }
                        // `;
                            // Botón de editar si hay id_pedido
                            buttonHTML += `
    ${row.id_pedido ? 
        `<a href="/orden/cuaderno/edit/${row.id}/${row.id_pedido}" class="btn btn-light btn-sm" style="color: #007bff; border-color: #007bff; background-color: #f0f8ff;">
                                                                        <i class="fas fa-edit"></i>
                                                                    </a>` :
        `<button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editProductModal" onclick="openEditModal(${row.id})">
                                                                        <i class="fas fa-edit"></i>
                                                                    </button>`}
`;


                            // Botón de nota de venta si id_pedido y lapaz existen
                            buttonHTML += `
      ${row.id_pedido  ? 
        `<a href="/nota-venta/${row.id_pedido}" class="btn btn-light btn-sm" style="color: #007bff; border-color: #007bff; background-color: #f0f8ff;">
                                                                                              <i class="fa fa-file-invoice"></i>
                                                                                            </a>`
      : ''}
    `;
                            // Estado del pedido con botones adecuados
                            if (row.id_pedido) {
                                buttonHTML += `
        ${row.pedido.estado_pedido === 'confirmado' ?
          `<button class="btn btn-danger btn-sm" disabled>
                                                                                                <i class="fas fa-check-circle">confirmado</i>
                                                                                              </button>
                                                                                              <a href="#" class="btn btn-warning btn-sm return-pedido" data-id="${row.id_pedido}">
                                                                                                <i class="fas fa-undo"></i>
                                                                                              </a>`
        : `<button class="btn btn-success btn-sm confirm-pedido" data-id="${row.id_pedido}">
                                                                                                <i class="fas fa-check">por confirmar</i>
                                                                                              </button>`
        }
      `;
                            }
                            return buttonHTML;
                        }
                    },


                ],
                drawCallback: function(settings) {
                    var totalRows = settings.json.recordsTotal;
                    $('#totalRows').text('Total de filas: ' + totalRows);
                    $('#envios-table tbody tr').each(function() {
                        var row = table.row(this).data();
                        var id = row.id;
                        var id_pedido = row.id_pedido;
                        var id_envio = row.id; // Usamos el `id` de la fila como el `id_envio`

                        // Si existe un id_pedido, hacemos la solicitud con id_pedido
                        if (id_pedido) {
                            $.ajax({
                                url: "{{ route('envios.getPedidoData') }}",
                                type: "GET",
                                data: {
                                    id_pedido: id_pedido // Enviamos id_pedido
                                },
                                success: function(response) {
                                    if (response) {
                                        $('#productos_' + id).text(response
                                            .productos || 'No seleccionado');
                                        $('#cantidad_productos_' + id).text(response
                                            .cantidad_productos ||
                                            'No seleccionado');
                                        $('#monto_deposito_' + id).text(response
                                            .monto_deposito || 'No seleccionado'
                                        );
                                    }
                                },
                                error: function() {
                                    $('#productos_' + id).text('No seleccionado');
                                    $('#cantidad_productos_' + id).text(
                                        'No seleccionado');
                                    $('#monto_deposito_' + id).text(
                                        'No seleccionado');
                                }
                            });
                        } else if (id_envio) {
                            // Si no existe id_pedido, usamos id_envio
                            $.ajax({
                                url: "{{ route('envios.getPedidoData') }}",
                                type: "GET",
                                data: {
                                    id_envio: id_envio // Enviamos id_envio
                                },
                                success: function(response) {
                                    if (response) {
                                        $('#productos_' + id).text(response
                                            .productos || 'No seleccionado');
                                        $('#cantidad_productos_' + id).text(response
                                            .cantidad_productos ||
                                            'No seleccionado');
                                        $('#monto_deposito_' + id).text(response
                                            .monto_deposito || 'No seleccionado'
                                        );
                                    }
                                },
                                error: function() {
                                    $('#productos_' + id).text('No seleccionado');
                                    $('#cantidad_productos_' + id).text(
                                        'No seleccionado');
                                    $('#monto_deposito_' + id).text(
                                        'No seleccionado');
                                }
                            });
                        } else {
                            // Si no hay id_pedido ni id_envio
                            $('#productos_' + id).text('No seleccionado');
                            $('#cantidad_productos_' + id).text('No seleccionado');
                            $('#monto_deposito_' + id).text('No seleccionado');
                        }
                    });
                }


            });

            $('#celular').on('keyup', function() {
                table.ajax.reload(); // Recarga los datos de la tabla con el nuevo filtro
            });
            // Filtrar cuando el botón es presionado
            $('#filtrar').on('click', function() {
                table.draw();
            });
            // Función para seleccionar todas las filas
            $('#select-all').on('change', function() {
                var checkboxes = $('.row-select');
                if ($(this).is(':checked')) {
                    checkboxes.prop('checked', true);
                    selectedRows = checkboxes.map(function() {
                        return $(this).data('id');
                    }).get();
                } else {
                    checkboxes.prop('checked', false);
                    selectedRows = [];
                }
                console.log('IDs seleccionados:', selectedRows);
            });



            // Actualización de campos al cambiar (por ejemplo, en inputs)
            $(document).on('change',
                '.celular-edit, .departamento-edit, .monto_de_pago-edit, .descripcion-edit, .lapaz-edit, .enviado-edit, .extra-edit, .extra1-edit, .extra2-edit, .extra3-edit, .fecha-hora-enviado-edit, .detalle-edit, .estado-edit, .confirmacion-edit',
                function() {
                    var id = $(this).data('id'); // ID del envío
                    var classList = $(this).attr('class').split(' '); // Obtenemos la lista de clases
                    var campo = classList.find(function(c) {
                        return c.includes('-edit'); // Encuentra la clase que contiene '-edit'
                    }).replace('-edit', ''); // Extraemos el nombre del campo eliminando '-edit'

                    var valor = $(this).val(); // Nuevo valor del campo

                    // Si es un checkbox, obtenemos el estado (1 o 0)
                    if ($(this).is(':checkbox')) {
                        valor = $(this).is(':checked') ? 1 : 0;
                    }

                    // Enviar actualización al servidor
                    $.ajax({
                        type: 'PUT',
                        url: '{{ route('envios.update', 'id') }}'.replace('id', id),
                        data: {
                            _method: 'PUT', // Aseguramos que sea una actualización
                            campo: campo, // Enviamos el nombre del campo (ej. 'departamento')
                            valor: valor // Enviamos el valor del campo
                        },
                        success: function(response) {
                            if (response.success) {
                                console.log('Campo actualizado con éxito');
                            } else {
                                console.log('Error al actualizar el campo:', response
                                    .message); // Mostrar el mensaje de error
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error al actualizar el campo:', xhr.status, xhr
                                .responseText, status, error);
                            alert(
                                'Error al actualizar el campo. Consulta la consola para más detalles.'
                            );
                        }
                    });

                    // Trackear el estado de los checkboxes
                    if (campo === 'confirmacion') {
                        if (valor === 1) {
                            selectedRows.push(id);
                        } else {
                            selectedRows = selectedRows.filter(row => row !== id);
                        }
                    }
                });

            // Manejo de la paginación y búsqueda
            $(document).on('click', '.pagination a', function(event) {
                event.preventDefault();
                var page = $(this).attr('href').split('page=')[1];
                var searchTerm = $('#search').val(); // Obtiene el término de búsqueda actual
                $('#envios-table').DataTable().ajax.reload(null,
                    false); // Recargar la tabla sin cambiar la página actual
            });

            $('#search').on('input', function() {
                var searchTerm = $(this).val();
                $('#envios-table').DataTable().search(searchTerm).draw(); // Buscar y recargar la tabla
            });

            // Eliminar un envío
            $(document).on('click', '.delete-envio', function() {
                var id = $(this).data('id');
                if (confirm('¿Estás seguro de eliminar este registro?')) {
                    $.ajax({
                        type: 'DELETE',
                        url: '{{ route('envios.destroy', 'id') }}'.replace('id', id),
                        success: function(response) {
                            $('#envios-table').DataTable().ajax
                                .reload(); // Refrescar la tabla después de la eliminación
                        }
                    });
                }
            });

            // Botón para eliminar múltiples filas seleccionadas
            $('#delete-selected').on('click', function() {
                if (selectedRows.length > 0) {
                    if (confirm('¿Estás seguro de eliminar estos registros?')) {
                        $.ajax({
                            type: 'DELETE',
                            url: '{{ route('envios.destroyMultiple') }}',
                            data: {
                                ids: selectedRows
                            },
                            success: function(response) {
                                $('#envios-table').DataTable().ajax
                                    .reload(); // Refrescar la tabla después de la eliminación
                                selectedRows = [];
                            }
                        });
                    }
                } else {
                    alert('No hay filas seleccionadas');
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            var isPedidoSelected = false; // Bandera para verificar si un pedido ya fue seleccionado

            // Búsqueda del ID de Pedido en la tabla
            $(document).on('input', '#id_pedido_search', function() {
                var searchQuery = $(this)
                    .val(); // Captura el valor que el usuario escribe en el campo de búsqueda
                var id = $(this).data('id'); // Obtiene el ID asociado al campo de búsqueda
                console.log(id); // Verifica si se obtiene correctamente el ID de la fila

                // Si ya se ha seleccionado un pedido, no se realiza ninguna búsqueda
                if (isPedidoSelected) {
                    console.log('Búsqueda desactivada porque ya se seleccionó un pedido');
                    return; // No hacer nada si ya se ha seleccionado un pedido
                }

                console.log('Valor de búsqueda:', searchQuery); // Log de lo que se está buscando

                if (searchQuery.length >
                    2) { // Solo hacer la búsqueda si el término tiene más de 2 caracteres
                    $.ajax({
                        type: 'GET',
                        url: '{{ route('envios.searchPedido') }}', // Ruta al controlador de búsqueda
                        data: {
                            search: searchQuery, // El término de búsqueda
                            id: id // Para identificar el envío y mostrar los resultados específicos
                        },
                        success: function(data) {
                            console.log('Resultados de búsqueda:',
                                data); // Log de los resultados que regresa el servidor

                            // Mostrar los resultados de la búsqueda
                            var resultsHTML = '';
                            if (data.length > 0) {
                                resultsHTML = data.map(function(pedido) {
                                    return '<div class="search-result" data-id="' + id +
                                        '" data-id_pedido="' + pedido.id +
                                        '" data-nombre="' + pedido.nombre +
                                        '" data-celular="' + pedido.celular + '">' +
                                        pedido.id + ' - ' + pedido.nombre + ' (' +
                                        pedido.celular + ')</div>';
                                }).join('');
                            } else {
                                resultsHTML = '<p>No se encontraron resultados</p>';
                            }

                            $('#pedidoSearchResults' + id).html(
                                resultsHTML
                            ); // Mostrar los resultados específicos para esta fila
                        },
                        error: function(xhr, status, error) {
                            console.error("Error en la solicitud AJAX:",
                                error); // Manejar error en la solicitud
                        }
                    });
                } else {
                    $('#pedidoSearchResults' + id).html(''); // Limpiar los resultados si no hay búsqueda
                }
            });

            // Selección de un pedido desde los resultados de búsqueda
            $(document).on('click', '.search-result', function() {
                var id_pedido = $(this).data('id_pedido');
                var nombre = $(this).data('nombre');
                var celular = $(this).data('celular');
                var id = $(this).data('id'); // Obtener el ID correcto de la fila

                console.log('Pedido seleccionado:', id, id_pedido, nombre,
                    celular); // Log del pedido seleccionado

                // Actualizar el valor del campo de texto con el ID seleccionado
                $('#id_pedido_search' + id).val(
                    id_pedido); // Actualizamos el valor del campo de búsqueda visible con el ID del pedido

                // Actualizar el campo adicional (id_pedido_autocomplete) con el ID seleccionado
                $('#id_pedido_autocomplete_' + id).val(id_pedido).data('id',
                    id); // Actualizamos el valor y data-id del campo de autocompletado con el ID del pedido

                // Actualizar el campo oculto con el ID seleccionado
                $('#id_pedido_hidden' + id).val(id_pedido); // Guardamos el ID en el campo oculto

                // Marcar como que ya se seleccionó un pedido
                isPedidoSelected = true;

                console.log('Campo autocompletado con ID:', id_pedido); // Log del valor autocompletado

                var id = $(this).data('id'); // ID del envío
                var valor = $(this).data('id_pedido'); // Nuevo valor del campo

                // var campo: 'id_pedido', // Nombre del campo de fecha-hora


                // Verificamos los datos que vamos a enviar
                console.log('Datos enviados al servidor:');
                console.log('ID: ' + id);
                console.log('Valor: ' + valor);

                // Enviar actualización al servidor
                $.ajax({
                    type: 'PUT',
                    url: '{{ route('envios.update', 'id') }}'.replace('id', id),
                    data: {
                        _method: 'PUT', // Aseguramos que sea una actualización
                        campo: 'id_pedido', // Enviamos el nombre del campo (ej. 'departamento')
                        valor: valor // Enviamos el valor del campo
                    },
                    success: function(response) {
                        if (response.success) {
                            console.log('Campo actualizado con éxito');
                        } else {
                            console.log('Error al actualizar el campo:', response
                                .message); // Mostrar el mensaje de error
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al actualizar el campo:', xhr.status, xhr
                            .responseText, status, error);
                        alert(
                            'Error al actualizar el campo. Consulta la consola para más detalles.'
                        );
                    }
                });
                // Hacer una llamada AJAX para obtener los datos del pedido seleccionado
                $.ajax({
                    type: 'POST',
                    url: '{{ route('envio.store-product', ['id_pedido' => ':id_pedido', 'id_envio' => ':id_envio']) }}'
                        .replace(':id_pedido', id_pedido)
                        .replace(':id_envio', id),
                    success: function(response) {
                        console.log('Datos actualizados correctamente', response);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al actualizar los datos:', error);
                    }
                });
                $.ajax({
                    type: 'GET',
                    url: '{{ route('envios.getPedidoData') }}', // Ruta al controlador para obtener los datos del pedido
                    data: {
                        id_pedido: id_pedido
                    },
                    success: function(data) {
                        // Si se obtiene correctamente la información del pedido, actualizar las columnas correspondientes
                        $('#productos_' + id).text(data
                            .productos); // Actualizar la columna de productos
                        $('#cantidad_productos_' + id).text(data
                            .cantidad_productos); // Actualizar la columna de productos
                        $('#monto_deposito_' + id).text(data
                            .monto_deposito); // Actualizar la columna de productos

                    },
                    error: function(xhr, status, error) {
                        console.error("Error al obtener los datos del pedido:", error);
                    }
                });
                // Hacer una llamada AJAX para actualizar la orden con el id_pedido y id_envio

                // Limpiar y ocultar los resultados de búsqueda
                $('#pedidoSearchResults' + id).html(''); // Limpiar los resultados de búsqueda
                $('#pedidoSearchResults' + id).hide(); // Ocultar la lista de resultados

                // Desactivar el campo de búsqueda para evitar que se dispare de nuevo
                $('#id_pedido_search' + id).prop('disabled', true); // Desactivar el campo de búsqueda
            });

            // Reactivar la búsqueda si el usuario hace clic nuevamente en el campo de búsqueda
            $(document).on('focus', '#id_pedido_search', function() {
                var id = $(this).data('id');
                // Si un pedido ya fue seleccionado, permite volver a buscar
                if (isPedidoSelected) {
                    console.log('Reactivando la búsqueda porque el campo fue clickeado');
                    isPedidoSelected = false; // Reactivar la búsqueda
                    $(this).val(''); // Limpiar el campo de búsqueda
                    $('#pedidoSearchResults' + id).html(''); // Limpiar los resultados de búsqueda
                    $('#pedidoSearchResults' + id).show(); // Mostrar la lista de resultados
                    $(this).prop('disabled', false); // Habilitar el campo para buscar nuevamente
                }
            });

            // Limpiar la búsqueda si el usuario borra el valor en el campo de búsqueda
            $(document).on('input', '#id_pedido_search', function() {
                if ($(this).val() === '') {
                    console.log('El campo de búsqueda fue borrado, reactivando la búsqueda');
                    isPedidoSelected = false; // Si se borra el campo, permitir nueva búsqueda
                    $(this).prop('disabled', false); // Volver a habilitar la búsqueda
                    $('#pedidoSearchResults' + $(this).data('id')).show(); // Mostrar la lista de resultados
                }
            });

            // Actualización de campos al cambiar (como en inputs)
            $(document).on('change', '[id^="id_pedido_autocomplete_"]', function() {
                var id = $(this).data('id'); // ID del envío
                var valor = $(this).val(); // Nuevo valor del campo

                console.log('Datos enviados al servidor para actualización:');
                console.log('ID: ' + id);
                console.log('Campo: id_pedido');
                console.log('Valor: ' + valor);

                // Enviar actualización al servidor
                $.ajax({
                    type: 'PUT',
                    url: '{{ route('envios.update', 'id') }}'.replace('id', id),
                    data: {
                        _method: 'PUT', // Aseguramos que sea una actualización
                        campo: 'id_pedido', // Enviamos el nombre del campo (en este caso id_pedido_autocomplete)
                        valor: valor // Enviamos el valor del campo
                    },
                    success: function(response) {
                        if (response.success) {
                            console.log('Campo actualizado con éxito');
                        } else {
                            console.log('Error al actualizar el campo:', response
                                .message); // Mostrar el mensaje de error
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al actualizar el campo:', xhr.status, xhr
                            .responseText, status, error);
                        alert(
                            'Error al actualizar el campo. Consulta la consola para más detalles.'
                        );
                    }
                });
            });
        });
    </script>
    <script>
        function openEditModal(envioId) {
            // Realizar una solicitud AJAX para obtener los datos del envío
            $.ajax({
                url: `/envios/${envioId}/productos`, // Ruta para obtener los productos del envío
                type: 'GET',
                success: function(data) {
                    // Llenar la lista de productos existentes
                    const productList = $('#productList');
                    productList.empty(); // Limpiar la lista antes de llenarla
                    if (data.productos && data.productos.length > 0) {
                        data.productos.forEach(producto => {
                            productList.append(`
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${producto.nombre}</strong><br>
                                <label>Cantidad:</label>
                                <input type="number" class="form-control d-inline-block w-25 edit-cantidad" 
                                    data-id="${producto.id}" value="${producto.cantidad}" min="1">
                                <label>Precio:</label>
                                <input type="number" class="form-control d-inline-block w-25 edit-precio" 
                                    data-id="${producto.id}" value="${producto.precio}" step="0.01">
                            </div>
                            <button class="btn btn-danger btn-sm delete-product" data-id="${producto.id}">
                                Eliminar
                            </button>
                        </li>
                    `);
                        });
                    } else {
                        productList.append(
                            '<li class="list-group-item text-muted">No hay productos asociados.</li>');
                    }
                },
                error: function(xhr) {
                    console.error('Error al obtener los datos del envío:', xhr.responseText);
                }
            });
        }
        $('#saveChanges').on('click', function() {
            const editedProducts = [];
            const deletedProducts = [];

            // Recorre cada producto en la lista y recopila los datos editados
            $('#productList .list-group-item').each(function() {
                const productId = $(this).find('.edit-cantidad').data('id');
                const cantidad = $(this).find('.edit-cantidad').val();
                const precio = $(this).find('.edit-precio').val();

                if ($(this).data('deleted')) {
                    // Si el producto está marcado como eliminado, agrégalo a la lista de eliminados
                    deletedProducts.push(productId);
                } else {
                    // Si no está eliminado, agrégalo a la lista de editados
                    editedProducts.push({
                        id: productId,
                        cantidad: cantidad,
                        precio: precio,
                    });
                }
            });

            // Enviar los datos al servidor mediante AJAX
            $.ajax({
                url: '/envios/guardar-productos', // Ruta para guardar los cambios
                type: 'POST',
                data: {
                    productos: editedProducts,
                    eliminados: deletedProducts,
                    _token: $('meta[name="csrf-token"]').attr('content'), // CSRF token
                },
                success: function(response) {
                    if (response.success) {
                        // Actualizar la tabla sin recargar la página
                        $('#envios-table').DataTable().ajax.reload(null,
                            false); // Recargar la tabla sin cambiar de página

                        // Mostrar un mensaje de éxito
                        Swal.fire({
                            title: 'Éxito',
                            text: 'Cambios guardados con éxito.',
                            icon: 'success',
                            confirmButtonText: 'Aceptar'
                        });

                        // Cerrar el modal
                        $('#editProductModal').modal('hide');

                        // Eliminar el fondo oscuro del modal si persiste
                        $('.modal-backdrop').remove();
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: 'Error al guardar los cambios.',
                            icon: 'error',
                            confirmButtonText: 'Aceptar'
                        });
                    }
                },
                error: function(xhr) {
                    console.error('Error al guardar los cambios:', xhr.responseText);
                    Swal.fire({
                        title: 'Error',
                        text: 'Ocurrió un error al guardar los cambios. Tienes que tener al menos un producto.',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                },
            });
        });
        $(document).ready(function() {
            // Manejar el clic en el botón "Eliminar"
            $(document).on('click', '.delete-product', function() {
                const productId = $(this).data('id'); // Obtener el ID del producto
                const listItem = $(this).closest('li'); // Obtener el elemento de la lista

                if (confirm('¿Estás seguro de eliminar este producto?')) {
                    // Marcar el producto como eliminado temporalmente
                    listItem.addClass('deleted-product'); // Agregar una clase para ocultarlo visualmente
                    listItem.find('.edit-cantidad, .edit-precio').prop('disabled',
                        true); // Deshabilitar los campos
                    listItem.hide(); // Ocultar el elemento de la lista
                    listItem.data('deleted', true); // Marcarlo como eliminado en los datos del elemento
                }
            });
        });
    </script>
@endsection
