@extends('adminlte::page')

@section('title', 'Stock de Sucursales')

@section('content_header')
    <h1 class="text-center">Administrar Stock en Sucursales</h1>
@stop

@section('content')
    <div class="container">
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h2 class="card-title">Stock en Sucursales</h2>
            </div>
            <div class="card-body">
                <!-- Filtro por categoría -->
                <form method="GET" action="{{ route('report.stock') }}">
                    <div class="form-row">
                        <div class="col-md-3">
                            <label for="categoria">Categoría</label>
                            <select name="id_categoria" id="categoria" class="form-control">
                                <option value="">Selecciona una categoría</option>
                                @foreach ($categorias as $categoria)
                                    <option value="{{ $categoria->id }}"
                                        {{ request('id_categoria') == $categoria->id ? 'selected' : '' }}>
                                        {{ $categoria->categoria }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary mt-4">Filtrar</button>
                        </div>
                    </div>
                </form>
                <!-- Grupo de botones de acciones -->
                <div class="mb-3 d-flex flex-wrap gap-2">
                    <a href="{{ route('report.stocklog') }}" class="btn btn-outline-info">
                        <i class="fas fa-history mr-1"></i> Cambios de Stock
                    </a>

                    <a href="{{ route('auditorias.inventario') }}" class="btn btn-outline-warning">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Productos con detalle faltante
                    </a>
                </div>


            </div>
            <div class="card-body">
                <!-- Tabla de Productos -->
                <div class="table-responsive">
                    <table id="products-report-table" class="table table-bordered table-striped table-hover table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>Id de producto</th>
                                <th>Nombre del Producto</th>
                                <th>Stock Incial</th>
                                <th>Stock en Almacén</th>
                                @foreach ($sucurnombre as $sucursal)
                                    <th>{{ $sucursal->nombre }}</th> <!-- Mostrar el nombre de la sucursal -->
                                @endforeach
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Los datos se cargarán automáticamente desde DataTables -->
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Modal de Auditoría -->
            <div class="modal fade" id="auditoriaModal" tabindex="-1" role="dialog" aria-labelledby="auditoriaModalLabel"
                aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <form id="form-auditoria">
                        @csrf
                        <div class="modal-content">
                            <div class="modal-header bg-warning">
                                <h5 class="modal-title" id="auditoriaModalLabel">Registrar Detalle</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" id="auditoria_producto_id" name="producto_id">

                                <div class="form-group">
                                    <label>Producto:</label>
                                    <input type="text" id="auditoria_producto_nombre" class="form-control" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="sucursal_id">Sucursal</label>
                                    <select name="sucursal_id" id="auditoria_sucursal_id" class="form-control" required>
                                        <option value="">Seleccione una sucursal</option>
                                        @foreach ($sucurnombre as $sucursal)
                                            <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="stock_sistema">Stock Actual en sistema</label>
                                    <input type="number" name="stock_sistema" id="auditoria_stock_sistema"
                                        class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="stock_real">Stock Real en Fisico</label>
                                    <input type="number" name="stock_real" id="auditoria_stock_real" class="form-control"
                                        required>
                                </div>
                                <div class="form-group">
                                    <label for="comentario">Detalle</label>
                                    <textarea name="comentario" id="auditoria_comentario" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-success">Guardar Detalle</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            var table = $('#products-report-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('report.stock') }}',
                    data: function(d) {
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.categoria_id = $('#categoria').val();
                    }
                },
                columns: [{
                        data: 'id',
                        className: 'text-center'
                    },
                    {
                        data: 'nombre',
                        className: 'text-center'
                    },
                    {
                        data: 'precio_descuento'
                    },
                    {
                        data: 'stock',
                        render: function(data, type, row) {
                            return `<input type="number" class="form-control form-control-sm stock-almacen-input" 
                                        data-product-id="${row.id}" 
                                        value="${data || 0}" />`;
                        },
                        className: 'text-center'
                    },
                    @foreach ($sucursales as $sucursalId)
                        {
                            data: 'stocks.{{ $sucursalId }}',
                            render: function(data, type, row) {
                                return `<input type="number" class="form-control form-control-sm stock-input" 
                                            data-product-id="${row.id}" 
                                            data-sucursal-id="{{ $sucursalId }}" 
                                            value="${data || 0}" />`;
                            },
                            className: 'text-center'
                        },
                    @endforeach {
                        data: null,
                        render: function(data, type, row) {
                            return `
                <button class="btn btn-warning btn-sm open-auditoria-modal" 
                    data-product-id="${row.id}" 
                    data-product-name="${row.nombre}">
                    <i class="fas fa-clipboard-check"></i> Agregar detalle
                </button>`;
                        },
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                language: {
                    decimal: ",",
                    thousands: ".",
                    processing: "Procesando...",
                    lengthMenu: "Mostrar _MENU_ registros",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    infoEmpty: "No hay registros disponibles",
                    infoFiltered: "(filtrado de _MAX_ registros)",
                    search: "Buscar:",
                    zeroRecords: "No se encontraron registros",
                    emptyTable: "No hay datos disponibles en la tabla",
                    paginate: {
                        first: "Primero",
                        previous: "Anterior",
                        next: "Siguiente",
                        last: "Último"
                    }
                },
                columnDefs: [{
                        targets: 0,
                        searchable: true
                    }, {
                        targets: 1,
                        searchable: true
                    },
                    {
                        targets: '_all',
                        searchable: false
                    }
                ]
            });

            // Detectar cambios en los inputs de stock y actualizar el valor
            $('#products-report-table').on('change', '.stock-input', function() {
                var input = $(this);
                var productId = input.data('product-id');
                var sucursalId = input.data('sucursal-id');
                var newValue = input.val();

                // Realizar la solicitud AJAX para actualizar el stock
                $.ajax({
                    url: '{{ route('report.updateStock') }}',
                    method: 'POST',
                    data: {
                        product_id: productId,
                        sucursal_id: sucursalId,
                        new_value: newValue,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Stock actualizado con éxito',
                                showConfirmButton: false,
                                timer: 1000
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'No existe el producto en esta sucursal',
                                text: '¡Agrega el producto!',
                                confirmButtonText: 'Aceptar'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    location.reload();
                                }
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Hubo un error al actualizar el stock o el campo esta vacio');
                        location.reload();
                    }
                });
            });

            // Detectar cambios en los inputs de stock del almacén
            $('#products-report-table').on('change', '.stock-almacen-input', function() {
                var input = $(this);
                var productId = input.data('product-id');
                var newValue = input.val();

                $.ajax({
                    url: '{{ route('report.updateAlmacenStock') }}',
                    method: 'POST',
                    data: {
                        product_id: productId,
                        new_value: newValue,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Stock de almacén actualizado con éxito',
                                showConfirmButton: false,
                                timer: 1000
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al actualizar el stock de almacén',
                                text: '¡Inténtalo de nuevo!',
                                confirmButtonText: 'Aceptar'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    location.reload();
                                }
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Hubo un error al actualizar el stock de almacén');
                        location.reload();
                    }
                });
            });
            // Abrir modal de auditoría
            // Abrir modal de auditoría
            $(document).on('click', '.open-auditoria-modal', function() {
                const productId = $(this).data('product-id');
                const productName = $(this).data('product-name');

                $('#auditoria_producto_id').val(productId);
                $('#auditoria_producto_nombre').val(productName);
                $('#auditoria_stock_sistema').val('');
                $('#auditoria_stock_real').val('');
                $('#auditoria_comentario').val('');
                $('#auditoria_sucursal_id').val('');

                $('#auditoriaModal').modal('show');
            });

            // Obtener el stock del sistema automáticamente al seleccionar una sucursal
            $('#auditoria_sucursal_id').on('change', function() {
                const sucursalId = $(this).val();
                const productoId = $('#auditoria_producto_id').val();

                if (sucursalId && productoId) {
                    $.ajax({
                        url: '{{ route('inventario.stock_actual') }}',
                        method: 'GET',
                        data: {
                            producto_id: productoId,
                            sucursal_id: sucursalId
                        },
                        success: function(response) {
                            $('#auditoria_stock_sistema').val(response.stock);
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al obtener el stock del sistema',
                                text: 'Verifica que el producto esté registrado en esta sucursal.'
                            });
                            $('#auditoria_stock_sistema').val(0); // Opcional
                        }
                    });
                }
            });


            // Enviar formulario de auditoría
            $('#form-auditoria').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    url: '{{ route('auditoria.detalle.store') }}', // Ruta que debes crear
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#auditoriaModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Auditoría registrada',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message ||
                                    'No se pudo registrar la auditoría'
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error del servidor',
                            text: 'Verifica los campos del formulario'
                        });
                    }
                });
            });

        });
    </script>
@endsection
