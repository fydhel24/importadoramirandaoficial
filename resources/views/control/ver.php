@extends('adminlte::page')

@section('title', 'ventas')

@section('content_header')
    <h1>Venta de productos</h1>
@stop
<style>
    .modal-body {
        overflow-y: auto;
        max-height: 80vh;
        /* Ajusta el alto máximo del cuerpo del modal */
    }

    .modal-dialog {
        max-width: 90%;
        /* Ajusta el ancho máximo del diálogo del modal */
        margin: 1.75rem auto;
        /* Ajusta la posición del diálogo del modal */
    }
</style>
@section('content')

    <div id="lista-cursos" class="container">
        <div class="d-flex flex-wrap justify-content-end">
            <a class="btn btn-info ml-2 mb-2" href="#" id="mostrar-carrito" data-toggle="modal"
                data-target="#carritoModal">
                <i class="fas fa-shopping-cart"></i> Carrito Vendedor (<span id="carrito-contador">0</span>)
            </a>
            @can('control.inventario.form')
                <a class="btn btn-primary ml-2 mb-2" href="{{ route('control.inventario.form', $id) }}">
                    <i class="fas fa-plus"></i> Agregar Cantidad de Productos a Sucursal
                </a>
            @endcan
            <!-- Search input field -->
            <div class="ml-2 mb-2">
                <input type="text" id="search-input" class="form-control" placeholder="Buscar productos">
            </div>
        </div>


        <br>
        <br>
        <br>

        <div class="row" id="product-list">
            @foreach ($productos as $producto)
                <div class="col-md-4 {{ $producto->producto->stock_sucursal <= 0 ? 'out-of-stock-card' : '' }}">
                    <div class="card card-widget widget-user shadow-lg">
                        @if (isset($producto->producto->fotos) && $producto->producto->fotos->isNotEmpty())
                            <div class="widget-user-header text-white"
                                style="background: url('{{ asset('storage/' . $producto->producto->fotos->first()->foto) }}') center center; background-size: cover;">
                                <h3 class="widget-user-username nombre-producto"
                                    style="text-shadow: 2px 2px 4px rgba(7, 7, 7, 0.5); font-size: 1.5em; font-weight: bold;">
                                    {{ $producto->producto->nombre }}
                                </h3>
                            </div>
                            <div class="widget-user-image">
                                <img class="img-circle"
                                    src="{{ asset('storage/' . $producto->producto->fotos->first()->foto) }}" alt="Producto"
                                    loading="lazy" style="width: 128px; height: 128px; object-fit: cover;">
                            </div>
                        @else
                            <div class="widget-user-header text-white" style="background-color: #ccc;">
                                <h3 class="widget-user-username nombre-producto"
                                    style="text-shadow: 2px 2px 4px rgba(7, 7, 7, 0.5); font-size: 1.5em; font-weight: bold;">
                                    {{ $producto->producto->nombre }}
                                </h3>
                            </div>
                            <div class="widget-user-image">
                                <img class="img-circle" src="{{ asset('path/to/default/image.jpg') }}" alt="Producto"
                                    loading="lazy" style="width: 128px; height: 128px; object-fit: cover;">
                            </div>
                        @endif

                        <br>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-sm-4 border-right">
                                    <div class="description-block">
                                        <h5 class="description-header">${{ $producto->producto->precio }}</h5>
                                        <span class="description-text">PRECIO</span>
                                    </div>
                                </div>

                                <div class="col-sm-4 border-right">
                                    @if ($producto->producto->categoria)
                                        <div class="description-block">
                                            <h5 class="description-header">{{ $producto->producto->categoria->categoria }}
                                            </h5>
                                            <span class="description-text">CATEGORÍA</span>
                                        </div>
                                    @else
                                        <div class="description-block">
                                            <h5 class="description-header">No categoría</h5>
                                            <span class="description-text">CATEGORÍA</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="col-sm-4">
                                    @if ($producto->producto->marca)
                                        <div class="description-block">
                                            <h5 class="description-header">{{ $producto->producto->marca->marca }}</h5>
                                            <span class="description-text">MARCA</span>
                                        </div>
                                    @else
                                        <div class="description-block">
                                            <h5 class="description-header">No marca</h5>
                                            <span class="description-text">MARCA</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Nueva sección: Stock Actual -->
                                <div class="col-sm-4">
                                    <div class="description-block">
                                        <h5 class="description-header">{{ $producto->producto->stock_actual }}</h5>
                                        <span class="description-text">TOTAL EN ALMACEN</span>
                                    </div>
                                </div>

                                <!-- Nueva sección: Stock en la sucursal -->
                                <div class="col-sm-4">
                                    <div class="description-block">
                                        <h5 class="description-header">{{ $producto->producto->stock_sucursal }}</h5>
                                        <span class="description-text">TOTAL EN SUCURSAL</span>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <a href="#"
                            class="btn btn-block agregar-carrito {{ $producto->producto->stock_sucursal <= 0 ? 'out-of-stock' : 'btn-success' }}"
                            data-id="{{ $producto->producto->id }}" data-nombre="{{ $producto->producto->nombre }}"
                            data-precio="{{ $producto->producto->precio }}"
                            data-stock-sucursal="{{ $producto->producto->stock_sucursal }}">
                            {{ $producto->producto->stock_sucursal <= 0 ? 'PRODUCTO NO DISPONIBLE, AGREGUE CANTIDAD DEL PRODUCTO' : 'Vender' }}
                        </a>
                    </div> <!-- .card -->
                </div>
            @endforeach
        </div> <!-- .row -->
        <style>
            .out-of-stock-card {
                background-color: #ffcccc;
                /* Fondo rojo claro */
            }

            .out-of-stock-card .card-footer,
            .out-of-stock-card .widget-user-header,
            .out-of-stock-card .widget-user-image,
            .out-of-stock-card .description-block {
                opacity: 0.7;
                /* Opacidad para indicar que no hay stock */
            }

            .out-of-stock-card .widget-user-header {
                background-color: #ff0000;
                /* Fondo rojo para la cabecera */
                color: #ffffff;
                /* Texto blanco para la cabecera */
            }

            .out-of-stock-card .btn {
                background-color: #ff0000;
                /* Botón rojo */
                color: #ffffff;
                /* Texto blanco para el botón */
                cursor: not-allowed;
                /* Cursor de no permitido */
                pointer-events: none;
                /* Bloquear eventos del mouse */
            }

            .out-of-stock {
                background-color: #ff0000;
                /* Rojo */
                color: #ffffff;
                /* Blanco */
                cursor: not-allowed;
                /* Cursor de no permitido */
                pointer-events: none;
                /* Bloquear eventos del mouse */
            }
        </style>
        {{-- 
        modal carrito --}}
        <form id="venta-form" method="POST" action="{{ route('control.fin') }}" target="_blank">
            @csrf
            <div id="carritoModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="carritoModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-xl" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="carritoModalLabel">Productos en el Carrito</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive">
                                <table id="lista-carrito" class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Precio</th>
                                            <th>Cantidad</th>
                                            <th>Total</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <!-- Resto del código -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="cliente">Cliente</label>
                                        <input type="text" name="nombre_cliente" id="cliente" class="form-control"
                                            placeholder="Ingrese nombre del cliente" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="ci">CI / NIT</label>
                                        <input type="text" name="ci" id="ci" class="form-control"
                                            placeholder="Ingrese CI del cliente">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="descuento">Descuento (Bs)</label>
                                        <input type="number" name="descuento" id="descuento" class="form-control"
                                            placeholder="Ingrese descuento" value="0">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="monto-total">Total Sin Descuento</label>
                                        <input type="text" id="monto-total" name="monto_total_sin_descuento"
                                            class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="total-a-pagar">Monto Total a Pagar</label>
                                        <input type="text" name="costo_total" id="total-a-pagar"
                                            class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="pagado">Monto Pagado</label>
                                        <input type="number" id="pagado" class="form-control"
                                            placeholder="Ingrese monto pagado">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="cambio">Cambio</label>
                                        <input type="text" id="cambio" class="form-control" readonly>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <button type="button" class="btn btn-info" id="ver-qr" data-toggle="modal"
                                        data-target="#qrModal" data-dismiss="modal">
                                        Ver QR
                                    </button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="tipo_pago">Método de Pago</label>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tipo_pago"
                                                        value="Efectivo" id="efectivo">
                                                    <label class="form-check-label" for="efectivo">Efectivo</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tipo_pago"
                                                        value="QR" id="transferencia_bancaria">
                                                    <label class="form-check-label"
                                                        for="transferencia_bancaria">Transferencia QR</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            <a href="#" id="vaciar-carrito-fvc" class="btn btn-danger">Vaciar</a>
                            <button type="submit" class="btn btn-success">Confirmar venta</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <!-- Modal para el QR -->
        <!-- Modal para el QR -->
        <div id="qrModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="qrModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="qrModalLabel">Código QR</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body text-center">
                        <img src="{{ asset('images/QR.jpeg') }}" alt="Código QR"
                            style="max-width: 100%; max-height: 100%;">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"
                            id="cerrar-qr-modal">Cerrar</button>
                        <button type="button" class="btn btn-primary" id="regresar-carrito" data-toggle="modal"
                            data-target="#carritoModal" data-dismiss="modal">
                            Regresar al Carrito
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de cantidad -->
        <div id="cantidadModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="cantidadModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cantidadModalLabel">Ingrese la cantidad</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="number" id="cantidad-input" class="form-control" placeholder="Cantidad"
                            min="1" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="button" id="confirmar-cantidad" class="btn btn-primary">Agregar al
                            carrito</button>
                    </div>
                </div>
            </div>
        </div>

    </div> <!-- .container -->

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let carrito = [];
        let carritoContador = document.getElementById('carrito-contador');
        let listaCarrito = document.querySelector('#lista-carrito tbody');
        let productoSeleccionado = null;

        document.querySelectorAll('.agregar-carrito').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                productoSeleccionado = {
                    id: this.getAttribute('data-id'),
                    nombre: this.getAttribute('data-nombre'),
                    precio: parseFloat(this.getAttribute('data-precio')),
                    stockSucursal: parseInt(this.getAttribute(
                        'data-stock-sucursal')) // Utiliza el atributo data-stock-sucursal
                };

                document.getElementById('cantidad-input').value = '';
                $('#cantidadModal').modal('show');
            });
        });


        document.getElementById('confirmar-cantidad').addEventListener('click', function() {
            const cantidad = parseInt(document.getElementById('cantidad-input').value);
            if (cantidad > 0) {
                if (cantidad > productoSeleccionado.stockSucursal) {
                    Swal.fire({
                        title: 'Error',
                        html: `
                    <p style="font-size: 18px; font-weight: bold;">La cantidad ingresada (${cantidad}) excede el stock en la sucursal que es (${productoSeleccionado.stockSucursal}).</p>
                    <p style="color: red; font-size: 26px;">Agregue cantidad del Producto a la Sucursal.</p>
                `,
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                    return;
                }

                const productoExistente = carrito.find(item => item.id === productoSeleccionado.id);
                if (productoExistente) {
                    productoExistente.cantidad += cantidad;
                } else {
                    carrito.push({
                        id: productoSeleccionado.id,
                        nombre: productoSeleccionado.nombre,
                        precio: productoSeleccionado.precio,
                        cantidad: cantidad,
                        stockSucursal: productoSeleccionado
                            .stockSucursal // Guardar el stock en la sucursal para futuras validaciones
                    });
                }

                Swal.fire({
                    title: 'Éxito',
                    text: `${productoSeleccionado.nombre} agregado al carrito`,
                    icon: 'success',
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    // Abre el modal del carrito después de que el usuario acepte el mensaje
                    mostrarCarrito();
                });

                actualizarCarritoContador();
                $('#cantidadModal').modal('hide');
            } else {
                Swal.fire({
                    title: 'Error',
                    text: 'Cantidad inválida',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
            }
        });

        function actualizarCarritoContador() {
            carritoContador.innerText = carrito.length;
        }

        document.getElementById('mostrar-carrito').addEventListener('click', function(e) {
            e.preventDefault();
            mostrarCarrito();
        });

        // Evento para calcular el total a pagar con descuento
        document.getElementById('descuento').addEventListener('input', function() {
            const descuento = parseFloat(this.value) || 0;
            const totalSinDescuento = parseFloat(document.getElementById('monto-total').value) || 0;

            // Restar el descuento solo si se ingresa un valor
            const totalAPagar = totalSinDescuento - descuento;
            document.getElementById('total-a-pagar').value = totalAPagar.toFixed(2);

            // Calcular cambio si hay un monto pagado
            calcularCambio();
        });

        // Evento para calcular el cambio basado en el monto pagado
        document.getElementById('pagado').addEventListener('input', function() {
            calcularCambio();
        });

        // Evento para actualizar el total a pagar cuando se modifica el total sin descuento
        document.getElementById('monto-total').addEventListener('input', function() {
            const totalSinDescuento = parseFloat(this.value) || 0;
            const descuento = parseFloat(document.getElementById('descuento').value) || 0;

            const totalAPagar = totalSinDescuento - descuento;
            document.getElementById('total-a-pagar').value = totalAPagar.toFixed(2);

            // Calcular cambio si hay un monto pagado
            calcularCambio();
        });

        // Evento para actualizar el total a pagar cuando se modifica el descuento
        document.getElementById('total-a-pagar').addEventListener('input', function() {
            const totalAPagar = parseFloat(this.value) || 0;
            const pagado = parseFloat(document.getElementById('pagado').value) || 0;

            const cambio = pagado - totalAPagar;
            document.getElementById('cambio').value = cambio >= 0 ? cambio.toFixed(2) : '0.00';
        });

        // Función para calcular el cambio
        function calcularCambio() {
            const pagado = parseFloat(document.getElementById('pagado').value) || 0;
            const totalAPagar = parseFloat(document.getElementById('total-a-pagar').value) || 0;
            const cambio = pagado - totalAPagar;

            document.getElementById('cambio').value = cambio >= 0 ? cambio.toFixed(2) : '0.00';
        }

        // Actualizar el total a pagar cuando se muestra el carrito
        // Función para mostrar el carrito
        // Función para mostrar el carrito
        function mostrarCarrito() {
            listaCarrito.innerHTML = '';

            if (carrito.length === 0) {
                listaCarrito.innerHTML = '<tr><td colspan="5" class="text-center">El carrito está vacío</td></tr>';
                document.getElementById('monto-total').value = '0.00';
                document.getElementById('total-a-pagar').value = '0.00'; // Resetear total a pagar
                return;
            }

            let totalAPagar = 0;

            carrito.forEach((item, index) => {
                const total = item.precio * item.cantidad;
                totalAPagar += total;

                // Agregar fila editable para el precio y total
                listaCarrito.innerHTML += `
                <tr> 
                    <td>${item.id}</td> 
                    <td>${item.nombre}</td>
                    <td><input type="number" class="form-control precio-input" value="${item.precio.toFixed(2)}" data-index="${index}"></td>
                    <td>${item.cantidad}</td>
                    <td><input type="number" class="form-control total-input" value="${total.toFixed(2)}" data-index="${index}"></td> 
                    <td><button class="btn btn-danger btn-sm eliminar" data-id="${item.id}">Eliminar</button></td>
                </tr>
            `;
            });

            // Actualizar el total a pagar
            document.getElementById('monto-total').value = totalAPagar.toFixed(2);
            document.getElementById('cambio').value = ''; // Limpiar cambio
            document.getElementById('total-a-pagar').value = totalAPagar.toFixed(2); // Total a pagar sin descuento

            // Agregar evento para actualizar el total a pagar cuando se edite un campo de precio o total
            document.querySelectorAll('.precio-input, .total-input').forEach(input => {
                input.addEventListener('input', function() {
                    const index = this.getAttribute('data-index');
                    const nuevoPrecio = parseFloat(this.value);
                    if (this.classList.contains('precio-input')) {
                        carrito[index].precio = nuevoPrecio;
                        const nuevoTotal = nuevoPrecio * carrito[index].cantidad;
                        listaCarrito.querySelectorAll(`[data-index="${index}"] .total-input`).value =
                            nuevoTotal.toFixed(2);
                    } else {
                        carrito[index].total = nuevoPrecio;
                    }
                    actualizarTotales();
                });
            });

            $('#carritoModal').modal('show');
        }

        // Función para actualizar los totales
        function actualizarTotales() {
            let totalAPagar = 0;
            carrito.forEach(item => {
                totalAPagar += item.total || (item.precio * item.cantidad);
            });

            document.getElementById('monto-total').value = totalAPagar.toFixed(2);
            document.getElementById('total-a-pagar').value = totalAPagar.toFixed(2); // Total a pagar sin descuento

            // Calcular cambio si hay un monto pagado
            calcularCambio();
        }

        // Función para calcular el cambio

        // Función para actualizar los totales
        function actualizarTotales() {
            let totalAPagar = 0;
            carrito.forEach(item => {
                totalAPagar += item.total || (item.precio * item.cantidad);
            });

            document.getElementById('monto-total').value = totalAPagar.toFixed(2);
            document.getElementById('total-a-pagar').value = totalAPagar.toFixed(2); // Total a pagar sin descuento

            // Calcular cambio si hay un monto pagado
            calcularCambio();
        }

        // Función para calcular el cambio





        document.getElementById('vaciar-carrito-fvc').addEventListener('click', function(e) {
            e.preventDefault();
            carrito = [];
            actualizarCarritoContador();
            mostrarCarrito();
        });

        listaCarrito.addEventListener('click', function(e) {
            if (e.target.classList.contains('eliminar')) {
                const id = e.target.getAttribute('data-id');
                carrito = carrito.filter(item => item.id !== id);
                actualizarCarritoContador();
                mostrarCarrito();
            }
        });
        document.getElementById('venta-form').addEventListener('submit', function(event) {
            event.preventDefault(); // Evitar el envío predeterminado del formulario

            // Validaciones
            const clienteNombre = document.getElementById('cliente').value.trim();
            const costoTotal = parseFloat(document.getElementById('total-a-pagar').value);

            const ci = document.getElementById('ci').value; // Obtiene el valor como una cadena

            if (!clienteNombre) {
                Swal.fire({
                    title: 'Error',
                    text: 'El nombre del cliente es obligatorio.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
                return;
            }

            if (isNaN(costoTotal) || costoTotal <= 0) {
                Swal.fire({
                    title: 'Error',
                    text: 'El monto total debe ser mayor que cero.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
                return;
            }

            // Recoger productos del carrito con precios editados
            const productos = [];
            const rows = document.querySelectorAll('#lista-carrito tbody tr');
            rows.forEach(row => {
                const id = row.querySelector('td:nth-child(1)').innerText; // ID del producto
                const cantidad = row.querySelector('td:nth-child(4)').innerText; // Cantidad
                const precio = row.querySelector('td:nth-child(3) input').value; // Precio editable
                const total = row.querySelector('td:nth-child(5) input').value; // Total editable

                productos.push({
                    id: id,
                    cantidad: cantidad,
                    precio: parseFloat(precio),
                    total: parseFloat(total)
                });
            });

            // Get the selected payment method
            const tipoPagoInput = document.querySelector('input[name="tipo_pago"]:checked');
            const tipoPago = tipoPagoInput ? tipoPagoInput.value : null;

            // Crear campos ocultos para el formulario
            const inputProductos = document.createElement('input');
            inputProductos.type = 'hidden';
            inputProductos.name = 'productos';
            inputProductos.value = JSON.stringify(productos);
            this.appendChild(inputProductos);

            // Agregar otros campos al formulario
            const inputCliente = document.createElement('input');
            inputCliente.type = 'hidden';
            inputCliente.name = 'nombre_cliente';
            inputCliente.value = clienteNombre;
            this.appendChild(inputCliente);

            const inputCostoTotal = document.createElement('input');
            inputCostoTotal.type = 'hidden';
            inputCostoTotal.name = 'costo_total';
            inputCostoTotal.value = costoTotal.toFixed(2);
            this.appendChild(inputCostoTotal);

            // Agregar el campo CI
            const inputCI = document.createElement('input');
            inputCI.type = 'hidden';
            inputCI.name = 'ci'; // Asegúrate de que el nombre sea correcto
            inputCI.value = ci;
            this.appendChild(inputCI);

            // Agregar el campo descuento
            const descuentoInput = document.getElementById('descuento');
            const inputDescuento = document.createElement('input');
            inputDescuento.type = 'hidden';
            inputDescuento.name = 'descuento';
            inputDescuento.value = descuentoInput.value || '0'; // Usa 0 si no hay valor
            this.appendChild(inputDescuento);

            // Agregar monto pagado
            const pagadoInput = document.getElementById('pagado');
            const inputPagado = document.createElement('input');
            inputPagado.type = 'hidden';
            inputPagado.name = 'pagado';
            inputPagado.value = pagadoInput.value || '0'; // Usa 0 si no hay valor
            this.appendChild(inputPagado);

            // Agregar el cambio (si es necesario)
            const cambioInput = document.getElementById('cambio');
            const inputCambio = document.createElement('input');
            inputCambio.type = 'hidden';
            inputCambio.name = 'cambio';
            inputCambio.value = cambioInput.value || '0'; // Usa 0 si no hay valor
            this.appendChild(inputCambio);

            // Agregar el tipo de pago
            const inputTipoPago = document.createElement('input');
            inputTipoPago.type = 'hidden';
            inputTipoPago.name = 'tipo_pago';
            inputTipoPago.value = tipoPago;
            this.appendChild(inputTipoPago);

            // **Aquí es donde agregas el id_sucursal como un campo oculto**
            const inputSucursal = document.createElement('input');
            inputSucursal.type = 'hidden';
            inputSucursal.name = 'id_sucursal';
            inputSucursal.value =
                {{ $id }}; // El ID de sucursal que está pasando desde el backend (controlador productos)
            this.appendChild(inputSucursal);

            // Enviar el formulario usando fetch
            fetch(this.action, {
                    method: 'POST',
                    body: new FormData(this),
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Redirigir para descargar el PDF
                        const url = '{{ route('nota.pdf') }}?nombre_cliente=' + encodeURIComponent(
                                clienteNombre) +
                            '&costo_total=' + encodeURIComponent(costoTotal.toFixed(2)) +
                            '&ci=' + encodeURIComponent(inputCI.value) +
                            '&productos=' + encodeURIComponent(JSON.stringify(productos)) +
                            '&descuento=' + encodeURIComponent(descuentoInput.value || '0') +
                            '&pagado=' + encodeURIComponent(pagadoInput.value || '0') +
                            '&cambio=' + encodeURIComponent(cambioInput.value || '0') +
                            '&tipo_pago=' + encodeURIComponent(tipoPago);

                        // Abrir la URL en una nueva pestaña
                        window.open(url, '_blank');

                        window.location.reload();

                    } else {
                        // Manejar error
                        Swal.fire({
                            title: 'Error',
                            text: data.message || 'Ocurrió un error en la venta',
                            icon: 'error',
                            confirmButtonText: 'Aceptar'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Ocurrió un error al procesar la venta',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });

                    window.location.reload();
                });
        });
    </script>





    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Function to filter products
        function filterProducts() {
            const searchInput = document.getElementById('search-input');
            const filter = searchInput.value.toUpperCase();
            const productList = document.getElementById('product-list');
            const cards = productList.getElementsByTagName('div');

            for (let i = 0; i < cards.length; i++) {
                const card = cards[i];
                if (card.classList.contains('col-md-4')) {
                    const productName = card.querySelector('.nombre-producto').textContent;
                    if (productName.toUpperCase().indexOf(filter) > -1) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                }
            }
        }

        // Add event listener to the search input
        document.getElementById('search-input').addEventListener('input', filterProducts);
        // Evento para cerrar el modal del carrito y abrir el modal del QR
        document.getElementById('ver-qr').addEventListener('click', function() {
            $('#carritoModal').modal('hide');
            $('#qrModal').modal('show');
            // Mantener el foco dentro del modal del QR
            $('#qrModal').on('shown.bs.modal', function() {
                $(this).find('.modal-body').focus();
            });
        });

        // Evento para cerrar el modal del QR y abrir el modal del carrito
        document.getElementById('cerrar-qr-modal').addEventListener('click', function() {
            $('#qrModal').modal('hide');
            $('#carritoModal').modal('show');
            // Mantener el foco dentro del modal del carrito
            $('#carritoModal').on('shown.bs.modal', function() {
                $(this).find('.modal-body').focus();
            });
        });

        // Evento para regresar al carrito desde el modal del QR
        document.getElementById('regresar-carrito').addEventListener('click', function() {
            $('#qrModal').modal('hide');
            $('#carritoModal').modal('show');
            // Mantener el foco dentro del modal del carrito
            $('#carritoModal').on('shown.bs.modal', function() {
                $(this).find('.modal-body').focus();
            });
        });
    </script>
@stop
