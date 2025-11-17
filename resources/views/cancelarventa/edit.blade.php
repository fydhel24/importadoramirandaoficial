@extends('adminlte::page')

@section('title', 'Editar Venta')

@section('content')
<div class="container-fluid">
    <!-- Información de la Venta Original -->
    <div class="card">
        <div class="card-header bg-primary">
            <h3 class="card-title"><i class="fas fa-edit"></i> Editar Venta #{{ $venta->id }}</h3>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label>Cliente:</label>
                    <p class="font-weight-bold">{{ $venta->nombre_cliente }}</p>
                </div>
                <div class="col-md-3">
                    <label>CI/NIT:</label>
                    <p class="font-weight-bold">{{ $venta->ci }}</p>
                </div>
                <div class="col-md-3">
                    <label>Fecha Original:</label>
                    <p class="font-weight-bold">{{ $venta->fecha }}</p>
                </div>
                <div class="col-md-3">
                    <label>Total Original:</label>
                    <p class="font-weight-bold" id="total-original">Bs. {{ number_format($venta->costo_total, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Productos Actuales -->
    <div class="card">
        <div class="card-header bg-info">
            <h3 class="card-title"><i class="fas fa-shopping-cart"></i> Productos de la Venta</h3>
        </div>
        <div class="card-body">
            <table class="table table-striped" id="productos-actuales">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unit.</th>
                        <th>Subtotal</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($venta->ventaProductos as $ventaProducto)
                    <tr id="producto-{{ $ventaProducto->id }}">
                        <td>{{ $ventaProducto->producto->nombre }}</td>
                        <td>{{ $ventaProducto->cantidad }}</td>
                        <td>Bs. {{ number_format($ventaProducto->precio_unitario, 2) }}</td>
                        <td>Bs. {{ number_format($ventaProducto->cantidad * $ventaProducto->precio_unitario, 2) }}</td>
                        <td>
                            <button type="button" class="btn btn-warning btn-sm" 
                                    onclick="agregarADevolucion({{ $ventaProducto->id }}, '{{ $ventaProducto->producto->nombre }}', {{ $ventaProducto->precio_unitario }}, {{ $ventaProducto->cantidad }})">
                                <i class="fas fa-exchange-alt"></i> Cambio del mismo Prod.
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" 
                                    onclick="cancelarProducto({{ $ventaProducto->id }}, '{{ $ventaProducto->producto->nombre }}')">
                                <i class="fas fa-times"></i> Cancelar Venta
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Productos a Devolver -->
    <div class="card mt-3">
        <div class="card-header bg-warning">
            <h3 class="card-title"><i class="fas fa-exchange-alt"></i> Productos a Devolver</h3>
        </div>
        <div class="card-body">
            <table class="table table-striped" id="productos-devolucion">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unit.</th>
                        <th>Subtotal</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-right font-weight-bold">Total a Devolver:</td>
                        <td class="font-weight-bold" id="total-devolucion">Bs. 0.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Productos Cancelados -->
    <div class="card mt-3">
        <div class="card-header bg-danger">
            <h3 class="card-title"><i class="fas fa-times"></i> Productos Cancelados</h3>
        </div>
        <div class="card-body">
            <table class="table table-striped" id="productos-cancelados">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unit.</th>
                        <th>Subtotal</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-right font-weight-bold">Total Cancelado:</td>
                        <td class="font-weight-bold" id="total-cancelado">Bs. 0.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Botones de Acción -->
    <div class="row mt-3">
        <div class="col-md-6">
            <button type="button" class="btn btn-success btn-lg btn-block" onclick="guardarCambios()">
                <i class="fas fa-save"></i> Guardar Cambios y Salir
            </button>
        </div>
        <div class="col-md-6">
            <button type="button" class="btn btn-danger btn-lg btn-block" onclick="ejecutarCambios()">
                <i class="fas fa-check-circle"></i> Ejecutar Cambios
            </button>
        </div>
    </div>
</div>

<!-- Modal para editar cantidad -->
<div class="modal fade" id="modalEditarCantidad" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Editar Cantidad</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formEditarCantidad">
                    <input type="hidden" id="id_venta_producto_editar">
                    <input type="hidden" id="tipo_operacion"> <!-- This line is missing -->
                    <div class="form-group">
                        <label>Cantidad a devolver:</label>
                        <input type="number" class="form-control" id="cantidad_editar" min="1">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" onclick="guardarEdicionCantidad()">
                    <i class="fas fa-check"></i> Guardar Cambios
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
// Variables globales
let productosADevolver = [];
let productosCancelados = [];
let totalDevolucion = 0;
let totalCancelado = 0;

// Función para agregar un producto a la tabla de devoluciones
// Función para agregar un producto a la tabla de devoluciones
function agregarADevolucion(ventaProductoId, nombre, precioUnitario, cantidadMaxima) {
    // Verificar si el producto ya está en la lista
    let productoExistente = productosADevolver.find(p => p.ventaProductoId === ventaProductoId);
    let productoCancelado = productosCancelados.find(p => p.ventaProductoId === ventaProductoId);

    if (productoExistente) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'Este producto ya está en la lista de devoluciones',
            confirmButtonText: 'Aceptar'
        });
        return;
    }
    
    if (productoCancelado) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'Este producto ya está en la lista de cancelaciones',
            confirmButtonText: 'Aceptar'
        });
        return;
    }

    // Mostrar modal para ingresar la cantidad
    $('#id_venta_producto_editar').val(ventaProductoId);
    $('#cantidad_editar').attr('max', cantidadMaxima);
    $('#cantidad_editar').val(1);
    $('#tipo_operacion').val('devolucion');
    $('#modalEditarCantidad .modal-title').text('Cantidad a Devolver');
    $('#modalEditarCantidad').modal('show');
}

// Función para ejecutar cambios (devoluciones y cancelaciones)
function ejecutarCambios() {
    if (productosADevolver.length === 0 && productosCancelados.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'No hay cambios para ejecutar',
            confirmButtonText: 'Aceptar'
        });
        return;
    }

    Swal.fire({
        icon: 'question',
        title: '¿Ejecutar Cambios?',
        text: '¿Está seguro de ejecutar las devoluciones y cancelaciones?',
        showCancelButton: true,
        confirmButtonText: 'Sí, ejecutar',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar indicador de carga
            Swal.fire({
                title: 'Procesando...',
                text: 'Por favor espere mientras se procesan los cambios',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: '{{ route("cancelarventa.ejecutar-cambios") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    venta_id: '{{ $venta->id }}',
                    productos_devolucion: productosADevolver,
                    productos_cancelados: productosCancelados
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'Cambios ejecutados correctamente',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                    let errorMsg = 'Ocurrió un error al ejecutar los cambios';
                    
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMsg = xhr.responseJSON.error;
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg,
                        confirmButtonText: 'Aceptar'
                    });
                }
            });
        }
    });
}

// Función para cancelar un producto de la venta
function cancelarProducto(ventaProductoId, nombre) {
    // Verificar si el producto ya está en alguna lista
    let productoExistente = productosADevolver.find(p => p.ventaProductoId === ventaProductoId);
    let productoCancelado = productosCancelados.find(p => p.ventaProductoId === ventaProductoId);

    if (productoExistente) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'Este producto ya está en la lista de devoluciones',
            confirmButtonText: 'Aceptar'
        });
        return;
    }
    
    if (productoCancelado) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'Este producto ya está en la lista de cancelaciones',
            confirmButtonText: 'Aceptar'
        });
        return;
    }

    // Obtener el producto de la lista original
    const productoRow = $(`#producto-${ventaProductoId}`);
    const cantidadMaxima = parseInt(productoRow.find('td:eq(1)').text());
    
    // Mostrar modal para ingresar la cantidad
    $('#id_venta_producto_editar').val(ventaProductoId);
    $('#cantidad_editar').attr('max', cantidadMaxima);
    $('#cantidad_editar').val(1);
    $('#tipo_operacion').val('cancelacion'); // Make sure this line exists
    $('#modalEditarCantidad .modal-title').text('Cantidad a Cancelar');
    $('#modalEditarCantidad').modal('show');
}
// Función para guardar la edición de la cantidad
function guardarEdicionCantidad() {
    const ventaProductoId = parseInt($('#id_venta_producto_editar').val());
    const cantidad = parseInt($('#cantidad_editar').val());
    const tipoOperacion = $('#tipo_operacion').val();
    
    console.log('Tipo de operación:', tipoOperacion);
    console.log('ID producto:', ventaProductoId);
    console.log('Cantidad:', cantidad);
    // Obtener el producto de la lista original
    const productoRow = $(`#producto-${ventaProductoId}`);
    const nombre = productoRow.find('td:eq(0)').text();
    const precioUnitario = parseFloat(productoRow.find('td:eq(2)').text().replace('Bs. ', '').replace(',', ''));
    const cantidadMaxima = parseInt(productoRow.find('td:eq(1)').text());
    
    // Validar cantidad
    if (isNaN(cantidad) || cantidad <= 0) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Debe ingresar una cantidad válida',
            confirmButtonText: 'Aceptar'
        });
        return;
    }
    
    if (cantidad > cantidadMaxima) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: `La cantidad máxima permitida es ${cantidadMaxima}`,
            confirmButtonText: 'Aceptar'
        });
        return;
    }

    if (tipoOperacion === 'devolucion') {
        // Agregar a la lista de productos a devolver
        productosADevolver.push({
            ventaProductoId: ventaProductoId,
            nombre: nombre,
            cantidad: cantidad,
            precioUnitario: precioUnitario
        });

        // Agregar fila a la tabla de devoluciones
        $('#productos-devolucion tbody').append(`
            <tr id="devolucion-${ventaProductoId}">
                <td>${nombre}</td>
                <td>${cantidad}</td>
                <td>Bs. ${precioUnitario.toFixed(2)}</td>
                <td>Bs. ${(cantidad * precioUnitario).toFixed(2)}</td>
                <td>
                    <button class="btn btn-danger btn-sm" onclick="quitarDevolucion(${ventaProductoId})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `);

        // Actualizar total de devoluciones
        actualizarTotalDevoluciones();
    } else if (tipoOperacion === 'cancelacion') {
        // Agregar a la lista de productos cancelados
        productosCancelados.push({
            ventaProductoId: ventaProductoId,
            nombre: nombre,
            cantidad: cantidad,
            precioUnitario: precioUnitario
        });

        // Agregar fila a la tabla de productos cancelados
        $('#productos-cancelados tbody').append(`
            <tr id="cancelado-${ventaProductoId}">
                <td>${nombre}</td>
                <td>${cantidad}</td>
                <td>Bs. ${precioUnitario.toFixed(2)}</td>
                <td>Bs. ${(cantidad * precioUnitario).toFixed(2)}</td>
                <td>
                    <button class="btn btn-danger btn-sm" onclick="quitarCancelacion(${ventaProductoId})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `);

        // Actualizar total cancelado
        actualizarTotalCancelado();
    }

    // Cerrar modal
    $('#modalEditarCantidad').modal('hide');
}

// Función para quitar un producto de la lista de devoluciones
function quitarDevolucion(ventaProductoId) {
    productosADevolver = productosADevolver.filter(p => p.ventaProductoId !== ventaProductoId);
    $(`#devolucion-${ventaProductoId}`).remove();
    actualizarTotalDevoluciones();
}

// Función para quitar un producto de la lista de cancelados
function quitarCancelacion(ventaProductoId) {
    productosCancelados = productosCancelados.filter(p => p.ventaProductoId !== ventaProductoId);
    $(`#cancelado-${ventaProductoId}`).remove();
    actualizarTotalCancelado();
}

// Función para actualizar el total de devoluciones
function actualizarTotalDevoluciones() {
    totalDevolucion = productosADevolver.reduce((total, producto) => {
        return total + (producto.cantidad * producto.precioUnitario);
    }, 0);
    $('#total-devolucion').text(`Bs. ${totalDevolucion.toFixed(2)}`);
}

// Función para actualizar el total cancelado
function actualizarTotalCancelado() {
    totalCancelado = productosCancelados.reduce((total, producto) => {
        return total + (producto.cantidad * producto.precioUnitario);
    }, 0);
    $('#total-cancelado').text(`Bs. ${totalCancelado.toFixed(2)}`);
}

// Función para guardar cambios y salir
function guardarCambios() {
    Swal.fire({
        icon: 'question',
        title: '¿Guardar Cambios?',
        text: '¿Está seguro de guardar los cambios y salir?',
        showCancelButton: true,
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '{{ route("cancelarventa.index") }}';
        }
    });
}


</script>
@endpush