@extends('adminlte::page')

@section('title', 'Crear Pedido')

@section('content_header')
    <h1>Crear Nuevo Pedido</h1>
@stop

@section('content')
    <!-- Incluir Bootstrap CSS en el encabezado -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Formulario de Pedido</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('orden.store') }}" method="POST" enctype="multipart/form-data"> <!-- Añadir enctype -->
                    @csrf
                    <input type="hidden" name="id_semana" value="{{ $id }}">

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                                    id="nombre" name="nombre" value="{{ old('nombre') }}" required>
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="ci" class="form-label">CI</label>
                                <input type="text" class="form-control @error('ci') is-invalid @enderror" id="ci"
                                    name="ci" value="{{ old('ci') }}" required>
                                @error('ci')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="celular" class="form-label">Celular</label>
                                <input type="text" class="form-control @error('celular') is-invalid @enderror"
                                    id="celular" name="celular" value="{{ old('celular') }}" required>
                                @error('celular')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="destino" class="form-label">Destino</label>
                                <input type="text" class="form-control @error('destino') is-invalid @enderror"
                                    id="destino" name="destino" value="{{ old('destino') }}" required>
                                @error('destino')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="direccion" class="form-label">Dirección</label>
                                <textarea type="text" class="form-control @error('direccion') is-invalid @enderror" id="direccion" name="direccion"
                                    value="{{ old('direccion') }}" required> </textarea>
                                @error('direccion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <div>
                                    @error('estado')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-check">
                                        <input class="form-check-input @error('estado') is-invalid @enderror" type="radio"
                                            name="estado" id="pagado" value="PAGADO"
                                            @if (old('estado') == 'PAGADO' || (old('estado') === null && isset($model) && $model->estado == 'PAGADO')) checked @endif>
                                        <label class="form-check-label" for="pagado">PAGADO</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input @error('estado') is-invalid @enderror" type="radio"
                                            name="estado" id="por_cobrar" value="POR COBRAR"
                                            @if (old('estado') == 'POR COBRAR' || (old('estado') === null && isset($model) && $model->estado == 'POR COBRAR')) checked @endif>
                                        <label class="form-check-label" for="por_cobrar">POR COBRAR</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="codigo" class="form-label">Código</label>
                                    <input type="text" class="form-control @error('codigo') is-invalid @enderror"
                                        id="codigo" name="codigo" value="{{ old('codigo') }}">
                                    @error('codigo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="monto_enviado_pagado" class="form-label">Monto Enviado/Pagado</label>
                                    <input type="number"
                                        class="form-control @error('monto_enviado_pagado') is-invalid @enderror"
                                        id="monto_enviado_pagado" name="monto_enviado_pagado"
                                        value="{{ old('monto_enviado_pagado', 0) }}" required>
                                    @error('monto_enviado_pagado')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <!-- Detalle del pedido -->
                        <div class="mb-3">
                            <label for="detalle" class="form-label">Detalle</label>
                            <textarea class="form-control @error('detalle') is-invalid @enderror" id="detalle" name="detalle" required>{{ old('detalle') }}</textarea>
                            @error('detalle')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <!-- Botón para agregar productos -->
                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-warning btn-lg" data-bs-toggle="modal"
                            data-bs-target="#productoModal">
                            <i class="fas fa-plus"></i> Agregar Producto
                        </button>
                    </div>

                    <!-- Mostrar los productos seleccionados -->
                    <div id="productosSeleccionados" class="mt-3">
                        <h5>Productos Seleccionados</h5>
                        <ul class="list-group" id="productoList">
                            <!-- Aquí se mostrarán los productos seleccionados -->
                        </ul>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cantidad_productos" class="form-label">Cantidad de Productos</label>
                                <input type="number"
                                    class="form-control @error('cantidad_productos') is-invalid @enderror"
                                    id="cantidad_productos" name="cantidad_productos"
                                    value="{{ old('cantidad_productos', 0) }}" required>
                                @error('cantidad_productos')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <!-- Monto deposito -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="monto_deposito" class="form-label">Monto Depósito</label>
                                <input type="text" class="form-control @error('monto_deposito') is-invalid @enderror"
                                    id="monto_deposito" name="monto_deposito" value="{{ old('monto_deposito', 0) }}"
                                    required>
                                @error('monto_deposito')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fecha" class="form-label">Fecha</label>
                                <input type="date" class="form-control @error('fecha') is-invalid @enderror"
                                    id="fecha" name="fecha" value="{{ old('fecha', date('Y-m-d')) }}" required>
                                @error('fecha')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="foto_comprobante" class="form-label">Foto Comprobante</label>
                                <input type="file"
                                    class="form-control @error('foto_comprobante') is-invalid @enderror"
                                    id="foto_comprobante" name="foto_comprobante" accept="image/*">
                                @error('foto_comprobante')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <img id="foto-preview" src="#" alt="Vista previa"
                                style="display: none; width: 100px; height: auto;">
                        </div>
                    </div>


                    <!-- Modal para seleccionar productos -->
                    <div class="modal fade" id="productoModal" tabindex="-1" aria-labelledby="productoModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="productoModalLabel">Seleccionar Producto</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="productoSearch" class="form-label">Buscar Producto</label>
                                        <input type="text" id="productoSearch" class="form-control"
                                            placeholder="Escribe para buscar productos..." list="sugerencias_productos">
                                        <datalist id="sugerencias_productos">
                                            @foreach ($productos as $producto)
                                                <option value="{{ $producto->nombre }}" data-id="{{ $producto->id }}"
                                                    data-precio="{{ $producto->precio }}" data-cantidad="1">
                                            @endforeach
                                        </datalist>
                                    </div>

                                    <div class="mb-3">
                                        <label for="cantidad" class="form-label">Cantidad</label>
                                        <input type="number" id="cantidad" class="form-control" value="1"
                                            min="1" oninput="actualizarPrecio()">
                                    </div>

                                    <div class="mb-3">
                                        <label for="precio" class="form-label">Precio</label>
                                        <input type="number" id="precio" class="form-control" value="0"
                                            step="0.01" oninput="precioManualEditado()">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Cancelar</button>
                                    <button type="button" class="btn btn-primary"
                                        onclick="agregarProducto()">Agregar</button>
                                </div>
                            </div>
                        </div>
                    </div>



                    <!-- Modal para editar productos -->
                    <div class="modal fade" id="editarProductoModal" tabindex="-1"
                        aria-labelledby="editarProductoModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editarProductoModalLabel">Editar Producto</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="editarNombre" class="form-label">Nombre</label>
                                        <input type="text" id="editarNombre" class="form-control" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label for="editarCantidad" class="form-label">Cantidad</label>
                                        <input type="number" id="editarCantidad" class="form-control" min="1"
                                            oninput="actualizarPrecioEditar()">
                                    </div>
                                    <div class="mb-3">
                                        <label for="editarPrecio" class="form-label">Precio</label>
                                        <input type="number" id="editarPrecio" class="form-control" step="0.01"
                                            oninput="precioManualEditadoEditar()">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Cancelar</button>
                                    <button type="button" class="btn btn-primary"
                                        onclick="guardarEdicionProducto()">Guardar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Campo oculto para enviar los productos al backend como JSON -->
                    <input type="hidden" id="productos" name="productos">


                    <button type="submit" class="btn btn-primary" id="submitButton"><i class="fas fa-save"></i>
                        Guardar</button>
                    <a href="{{ route('orden.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i>
                        Cancelar</a>
                </form>
            </div>
        </div>
    </div>

@section('js')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Convierte a mayúsculas todos los campos de texto y áreas de texto
            document.querySelectorAll('input[type="text"], textarea').forEach(function(element) {
                element.addEventListener('input', function() {
                    this.value = this.value.toUpperCase();
                });
            });

            // Muestra la vista previa de la imagen seleccionada
            const fotoInput = document.getElementById('foto_comprobante');
            const fotoPreview = document.getElementById('foto-preview');

            fotoInput.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        fotoPreview.src = e.target.result;
                        fotoPreview.style.display = 'block'; // Muestra la imagen
                    }
                    reader.readAsDataURL(file);
                } else {
                    fotoPreview.style.display = 'none'; // Oculta la imagen si no hay archivo
                }
            });
        });
    </script>

    <script>
        // Array para almacenar los productos seleccionados
        let productosSeleccionados = [];
        // Función para indicar que el usuario ha editado manualmente el precio
        let precioManual = false; // Bandera para saber si el precio ha sido modificado manualmente

        // Función para abrir el modal y restablecer el estado del precio
        function abrirModalProducto() {
            precioManual = false; // Restablecer la bandera a false cada vez que se abra el modal
            const productoInput = document.getElementById('productoSearch');
            const cantidadInput = document.getElementById('cantidad');
            const precioInput = document.getElementById('precio');

            // Restablecer los valores del formulario al abrir el modal
            productoInput.value = '';
            cantidadInput.value = 1; // Restablecer la cantidad a 1
            precioInput.value = 0; // Restablecer el precio a 0
        }

        // Función para obtener el producto seleccionado desde el datalist
        function getSelectedProduct() {
            const productoInput = document.getElementById('productoSearch');
            const productosList = document.getElementById('productosList');
            const selectedOption = Array.from(productosList.options).find(option => option.value === productoInput.value);

            if (selectedOption) {
                return {
                    id: selectedOption.dataset.id,
                    nombre: selectedOption.value,
                    precio: selectedOption.dataset.precio
                };
            } else {
                return null;
            }
        }

        // Detectar selección de producto
        const buscarInput = document.getElementById('productoSearch');
        const sugerencias = document.getElementById('sugerencias_productos');
        const cantidadInput = document.getElementById('cantidad');
        const precioInput = document.getElementById('precio');

        let selectedProduct = null;

        buscarInput.addEventListener('input', function() {
            const nombreSeleccionado = buscarInput.value;
            const optionSeleccionada = Array.from(sugerencias.options).find(option => option.value ===
                nombreSeleccionado);

            if (optionSeleccionada) {
                const precio = parseFloat(optionSeleccionada.dataset.precio).toFixed(2);
                const cantidad = optionSeleccionada.dataset.cantidad || 1;

                // Actualizar los campos de cantidad y precio
                cantidadInput.value = cantidad;
                precioInput.value = precio;

                // Guardar el producto seleccionado
                selectedProduct = {
                    id: optionSeleccionada.dataset.id,
                    nombre: nombreSeleccionado,
                    precio: parseFloat(optionSeleccionada.dataset.precio)
                };
            } else {
                cantidadInput.value = 1;
                precioInput.value = 0;
                selectedProduct = null;
            }
        });

        // Función para obtener el producto seleccionado
        function getSelectedProduct() {
            return selectedProduct;
        }

        // Función para actualizar el precio cuando se selecciona un producto o se cambia la cantidad
        function actualizarPrecio() {
            const producto = getSelectedProduct();
            if (producto) {
                const cantidad = parseInt(cantidadInput.value) || 1; // Asegurarse de que la cantidad sea al menos 1
                const precioTotal = producto.precio * cantidad;

                // Actualizar el campo de precio con el valor calculado
                precioInput.value = precioTotal.toFixed(2); // Formato a dos decimales
            }
        }

        // Función para agregar un producto a la lista de productos seleccionados
        function agregarProducto() {
            const buscarInput = document.getElementById('productoSearch');
            const cantidadInput = document.getElementById('cantidad');
            const precioInput = document.getElementById('precio');

            const nombreSeleccionado = buscarInput.value;
            const optionSeleccionada = Array.from(sugerencias.options).find(option => option.value === nombreSeleccionado);

            if (!optionSeleccionada) {
                alert('Producto no válido. Selecciona uno de la lista.');
                return;
            }

            const id = optionSeleccionada.dataset.id;
            const nombre = nombreSeleccionado;

            // Verificar si el producto ya está en la lista
            if (productosSeleccionados.find(p => p.id_producto === id)) {
                alert('El producto ya está agregado.');
                return;
            }

            // Agregar producto a la lista
            productosSeleccionados.push({
                id_producto: id,
                nombre: nombre,
                cantidad: cantidadInput.value,
                precio: precioInput.value
            });

            // Actualizar la lista visual de productos seleccionados
            actualizarListaProductos();

            // Limpiar los campos
            buscarInput.value = '';
        }


        // Llamar esta función cuando se abra el modal
        document.getElementById('productoModal').addEventListener('shown.bs.modal', abrirModalProducto);
        // Función para indicar que el usuario ha editado manualmente el precio
        function precioManualEditado() {
            precioManual = true; // Marcar que el precio fue editado manualmente
        }
        // Función para abrir la modal y preparar los campos para editar
        function editarProducto(index) {
            const producto = productosSeleccionados[index];
            const editarNombreInput = document.getElementById('editarNombre');
            const editarCantidadInput = document.getElementById('editarCantidad');
            const editarPrecioInput = document.getElementById('editarPrecio');

            // Rellenar los campos con los datos del producto
            editarNombreInput.value = producto.nombre;
            editarCantidadInput.value = producto.cantidad;
            editarPrecioInput.value = producto.precio;

            // Abrir la modal
            const editarProductoModal = document.getElementById('editarProductoModal');
            const modal = new bootstrap.Modal(editarProductoModal);
            modal.show();

            // Guardar el índice del producto para usarlo al guardar los cambios
            window.editarIndex = index;
        }

        // Función para actualizar el precio cuando se cambia la cantidad en la modal de edición
        function actualizarPrecioEditar() {
            const editarCantidadInput = document.getElementById('editarCantidad');
            const editarPrecioInput = document.getElementById('editarPrecio');
            const producto = productosSeleccionados[window.editarIndex];

            // Obtener el precio base del producto
            const precioBase = parseFloat(producto.precio) / parseInt(producto.cantidad);

            // Calcular el nuevo precio total
            const cantidad = parseInt(editarCantidadInput.value) || 1;
            const precioTotal = precioBase * cantidad;

            // Actualizar el campo de precio
            editarPrecioInput.value = precioTotal.toFixed(2);
        }

        // Función para indicar que el usuario ha editado manualmente el precio en la modal de edición
        function precioManualEditadoEditar() {
            window.precioManualEditar = true;
        }

        // Función para guardar los cambios del producto editado
        function guardarEdicionProducto() {
            const editarCantidadInput = document.getElementById('editarCantidad');
            const editarPrecioInput = document.getElementById('editarPrecio');

            // Obtener los nuevos valores
            const nuevaCantidad = editarCantidadInput.value;
            const nuevoPrecio = editarPrecioInput.value;

            // Actualizar el producto en la lista
            productosSeleccionados[window.editarIndex].cantidad = nuevaCantidad;
            productosSeleccionados[window.editarIndex].precio = nuevoPrecio;

            // Actualizar la lista visual de productos seleccionados
            actualizarListaProductos();

            // Cerrar la modal
            const editarProductoModal = document.getElementById('editarProductoModal');
            const modal = bootstrap.Modal.getInstance(editarProductoModal);
            modal.hide();
        }

        // Función para actualizar la lista visual de productos seleccionados
        function actualizarListaProductos() {
            const productoList = document.getElementById('productoList');
            const totalCantidadSpan = document.getElementById('totalCantidad');
            const cantidadProductosInput = document.getElementById('cantidad_productos');
            productoList.innerHTML = ''; // Limpiar la lista antes de volver a llenarla

            let totalCantidad = 0; // Variable para almacenar el total de la cantidad de productos

            // Recorrer los productos seleccionados y mostrarlos en la lista
            productosSeleccionados.forEach((producto, index) => {
                const li = document.createElement('li');
                li.classList.add('list-group-item');
                li.innerHTML = `
            Producto: ${producto.nombre} - Cantidad: ${producto.cantidad} - Precio Total: ${producto.precio}
            <button type="button" class="btn btn-danger btn-sm float-end" onclick="eliminarProducto(${index})">
                Eliminar
            </button>
            <button type="button" class="btn btn-primary btn-sm float-end" onclick="editarProducto(${index})">
            Editar
            </button>
        `;
                productoList.appendChild(li);

                // Sumar la cantidad del producto actual al total
                totalCantidad += parseInt(producto.cantidad);
            });

            // Actualizar el total de la cantidad de productos en el input
            cantidadProductosInput.value = totalCantidad;

            // Actualizar el total de la cantidad de productos en la sección de productos seleccionados
            if (totalCantidadSpan) {
                totalCantidadSpan.textContent = totalCantidad;
            }

            // Actualizar el campo oculto con la lista de productos seleccionados (en formato JSON)
            document.getElementById('productos').value = JSON.stringify(productosSeleccionados);

            // Actualizar el detalle y el monto deposito
            actualizarDetalleYMontos();
        }

        // Función para actualizar el detalle y el monto deposito
        function actualizarDetalleYMontos() {
            const detalleTextarea = document.getElementById('detalle');
            const montoDepositoInput = document.getElementById('monto_deposito');

            // Obtener los nombres de los productos y la sumatoria de precios
            let productosNombres = '';
            let totalPrecio = 0;
            productosSeleccionados.forEach(producto => {
                const productoNombre =
                    `Producto: ${producto.nombre}\n`;
                productosNombres += productoNombre;
                totalPrecio += parseFloat(producto.precio);
            });

            // Actualizar el textarea de detalle
            //detalleTextarea.value = productosNombres;

            // Actualizar el input de monto deposito
            montoDepositoInput.value = totalPrecio.toFixed(2);
        }

        // Función para eliminar un producto de la lista
        function eliminarProducto(index) {
            // Eliminar producto del array
            productosSeleccionados.splice(index, 1);

            // Actualizar la lista visuals
            actualizarListaProductos();
        }
        document.addEventListener('DOMContentLoaded', function() {
            const productoSelect = document.getElementById('producto');
            const productoSearch = document.getElementById('productoSearch');

            // Función para filtrar las opciones del select
            function filterOptions(searchTerm) {
                const options = productoSelect.options;
                for (let i = 0; i < options.length; i++) {
                    const option = options[i];
                    if (option.value === '' || option.text.toLowerCase().includes(searchTerm.toLowerCase())) {
                        option.style.display = 'block';
                    } else {
                        option.style.display = 'none';
                    }
                }
            }

            // Agregar evento de búsqueda
            productoSearch.addEventListener('input', function() {
                const searchTerm = this.value.trim();
                filterOptions(searchTerm);
            });
        });

        // Función para manejar el envío del formulario
        document.getElementById('submitButton').addEventListener('click', function(event) {
            // Obtener los productos seleccionados (decodificar el JSON)
            const productos = JSON.parse(document.getElementById('productos').value || '[]');

            if (productos.length === 0) {
                // Si no hay productos, muestra una alerta con SweetAlert2
                Swal.fire({
                    icon: 'error',
                    title: '¡Alerta!',
                    text: 'Debes tener al menos un producto seleccionado. Agrega un producto.',
                    confirmButtonText: 'Aceptar'
                });
                // Detiene el envío del formulario
                event.preventDefault();
            }
        });
    </script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
@endsection

@endsection
