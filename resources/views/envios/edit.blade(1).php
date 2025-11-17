@extends('adminlte::page')

@section('content')
    <div class="card shadow-lg border-0 rounded">
        <div class="card-header bg-gradient-primary text-white text-center">
            <h3 class="card-title">
                <i class="fas fa-shipping-fast"></i> Editar Envío de Producto a Sucursal
            </h3>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('envios.update', $historial->id) }}" method="POST" id="envio-form">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Sucursal Destino -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_sucursal" class="fw-bold text-primary">Sucursal Destino</label>
                            <select name="id_sucursal" id="id_sucursal" class="form-control" required
                                onchange="cargarUsuarios(this.value);">
                                <option value="" disabled>Selecciona una sucursal</option>
                                @foreach ($sucursales as $sucursal)
                                    <option value="{{ $sucursal->id }}"
                                        {{ $historial->id_sucursal == $sucursal->id ? 'selected' : '' }}>
                                        {{ $sucursal->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Agregar Productos -->
                <div class="form-group">
                    <label for="nuevo_producto" class="fw-bold text-primary">Agregar Producto</label>
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
                            <button type="button" id="agregar-producto" class="btn btn-success">
                                <i class="fas fa-plus"></i> Agregar
                            </button>
                        </div>
                    </div>
                    <small id="stock-disponible" class="mt-2 text-info"></small>
                </div>

                <!-- Productos Agregados -->
                <h5 class="mt-4 fw-bold text-primary">Productos Agregados:</h5>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="lista-productos">
                            @foreach ($historial->productos as $producto)
                                <tr data-id="{{ $producto->id_producto }}"
                                    data-cantidad-antes="{{ $producto->cantidad_antes }}">
                                    <td>{{ $producto->producto->nombre }}</td>
                                    <td>{{ $producto->cantidad }} unidades</td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm eliminar-producto">
                                            <i class="fas fa-trash-alt"></i> Eliminar
                                        </button>
                                        <input type="hidden" name="productos[{{ $producto->id_producto }}][cantidad]"
                                            value="{{ $producto->cantidad }}" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>

                <!-- Botón para enviar -->
                <div class="form-group text-center mt-4">
                    <button type="submit" id="enviar-productos" class="btn btn-lg btn-primary px-4 shadow">
                        <i class="fas fa-check"></i> Actualizar Envío
                    </button>
                </div>
            </form>
        </div>
    </div>

@section('js')
    <!-- Cargar CSS de Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Cargar jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Cargar JS de Select2 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inicializar Select2
            $('.select2').select2();

            // Actualizar stock disponible al seleccionar un producto
            $('#nuevo_producto').on('change', function() {
                const stock = $(this).find(':selected').data('stock');
                $('#stock-disponible').text(`Stock disponible: ${stock}`);
            });

            // Agregar producto a la lista
            $('#agregar-producto').click(function() {
                const productoId = $('#nuevo_producto').val();
                const cantidad = $('#cantidad_producto').val();
                const productoNombre = $('#nuevo_producto option:selected').text();

                if (!productoId || !cantidad || cantidad <= 0) {
                    alert("Selecciona un producto y una cantidad válida.");
                    return;
                }

                if ($(`#lista-productos tr[data-id="${productoId}"]`).length) {
                    alert("Este producto ya ha sido agregado.");
                    return;
                }

                $('#lista-productos').append(`
        <tr data-id="${productoId}" data-cantidad-antes="0">
            <td>${productoNombre}</td>
            <td>${cantidad} unidades</td>
            <td>
                <button type='button' class='btn btn-danger btn-sm eliminar-producto'>
                    <i class="fas fa-trash-alt"></i> Eliminar
                </button>
                <input type='hidden' name='productos[${productoId}][cantidad]' value='${cantidad}' />
            </td>
        </tr>
    `);

                // Limpiar campos
                $('#nuevo_producto').val(null).trigger('change');
                $('#cantidad_producto').val('');
            });

            // Eliminar producto de la lista
            $(document).on('click', '.eliminar-producto', function() {
                $(this).closest('tr').remove();
            });
        });

        function cargarUsuarios(sucursalId) {
            if (sucursalId) {
                $.ajax({
                    url: '/usuarios/sucursal/' + sucursalId,
                    type: 'GET',
                    success: function(data) {
                        $('#id_usuario').empty().append(
                            '<option value="" disabled selected>Selecciona un usuario</option>');
                        $.each(data, function(index, usuario) {
                            $('#id_usuario').append(
                                `<option value="${usuario.id}">${usuario.name}</option>`);
                        });
                    },
                    error: function() {
                        alert('Error al cargar los usuarios.');
                    }
                });
            } else {
                $('#id_usuario').empty().append('<option value="" disabled selected>Selecciona un usuario</option>');
            }
        }
    </script>
@endsection
@endsection
