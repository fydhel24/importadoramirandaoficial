@extends('adminlte::page')

@section('title', 'Editar Pedido')

@section('content_header')
    <h1>Editar Pedido</h1>
@stop

@section('content')
    <!-- Incluir Bootstrap CSS en el encabezado -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Formulario de Edición de Pedido</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('orden.update', $pedido->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id_semana" value="{{ $pedido->id_semana }}">

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                                    id="nombre" name="nombre" value="{{ old('nombre', $pedido->nombre) }}" required>
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="ci" class="form-label">CI</label>
                                <input type="text" class="form-control @error('ci') is-invalid @enderror" id="ci"
                                    name="ci" value="{{ old('ci', $pedido->ci) }}" required>
                                @error('ci')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="celular" class="form-label">Celular</label>
                                <input type="text" class="form-control @error('celular') is-invalid @enderror"
                                    id="celular" name="celular" value="{{ old('celular', $pedido->celular) }}" required>
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
                                    id="destino" name="destino" value="{{ old('destino', $pedido->destino) }}" required>
                                @error('destino')
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
                                            @if (old('estado', $pedido->estado) == 'PAGADO') checked @endif>
                                        <label class="form-check-label" for="pagado">PAGADO</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input @error('estado') is-invalid @enderror" type="radio"
                                            name="estado" id="por_cobrar" value="POR COBRAR"
                                            @if (old('estado', $pedido->estado) == 'POR COBRAR') checked @endif>
                                        <label class="form-check-label" for="por_cobrar">POR COBRAR</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>

                    <div class="mb-3">
                        <label for="detalle" class="form-label">Detalle</label>
                        <textarea class="form-control @error('detalle') is-invalid @enderror" id="detalle" name="detalle" required>{{ old('detalle', $pedido->detalle) }}</textarea>
                        @error('detalle')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="row">

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
                                @foreach ($pedido->pedidoProductos as $producto)
                                    <li class="list-group-item">
                                        Producto: {{ $producto->producto->nombre }} - Cantidad: {{ $producto->cantidad }}
                                        -
                                        Precio Total: {{ $producto->precio }}
                                        <button type="button" class="btn btn-danger btn-sm float-end"
                                            onclick="eliminarProducto({{ $loop->index }})">
                                            Eliminar
                                        </button>
                                        <button type="button" class="btn btn-primary btn-sm float-end"
                                            onclick="editarProducto({{ $loop->index }})">
                                            Editar
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cantidad_productos" class="form-label">Cantidad de Productos</label>
                                <input type="number" class="form-control @error('cantidad_productos') is-invalid @enderror"
                                    id="cantidad_productos" name="cantidad_productos"
                                    value="{{ old('cantidad_productos', $pedido->cantidad_productos) }}" required>
                                @error('cantidad_productos')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="monto_deposito" class="form-label">Monto Depósito</label>
                                <input type="text" readonly class="form-control bg-light @error('monto_deposito') is-invalid @enderror"
                                    id="monto_deposito" name="monto_deposito"
                                    value="{{ old('monto_deposito', $pedido->monto_deposito) }}">

                                @error('monto_deposito')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="garantia" class="form-label">Garantía</label>
                            <select name="garantia" id="garantia" class="form-select @error('garantia') is-invalid @enderror">
                                <option value="">Seleccione una opción</option>
                                <option value="sin garantia" {{ old('garantia', $pedido->garantia) == 'sin garantia' ? 'selected' : '' }}>Sin garantía</option>
                                <option value="con garantia" {{ old('garantia', $pedido->garantia) == 'con garantia' ? 'selected' : '' }}>Con garantía</option>
                            </select>
                            @error('garantia')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    

                    {{-- Selección de forma de pago --}}
<div class="row mt-4">
    <div class="col-md-6">
        <label for="forma_pago" class="form-label">Forma de Pago</label>
        <select id="forma_pago" class="form-select" onchange="actualizarFormaPago()">
            <option value="">Seleccione una opción</option>
            <option value="efectivo">Solo Efectivo</option>
            <option value="qr">Solo QR</option>
            <option value="ambos">Efectivo y QR</option>
        </select>
    </div>
    
</div>

{{-- Campos condicionales: efectivo y QR --}}
<div class="row mt-3">
    <div class="col-md-6" id="campo_efectivo" style="display: none;">
        <label for="efectivo" class="form-label">Monto en Efectivo</label>
        <input type="number" step="0.01" name="efectivo" id="efectivo" class="form-control"
            value="{{ old('efectivo', $pedido->efectivo ?? 0) }}">
    </div>

    <div class="col-md-6" id="campo_qr" style="display: none;">
        <label for="transferencia_qr" class="form-label">Monto por QR</label>
        <input type="number" step="0.01" name="transferencia_qr" id="transferencia_qr" class="form-control"
            value="{{ old('transferencia_qr', $pedido->transferencia_qr ?? 0) }}">
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
                    <input type="hidden" id="productos" name="productos"
                        value="{{ json_encode($pedido->pedidoProductos->map(function ($p) {return ['id_producto' => $p->id_producto, 'nombre' => $p->producto->nombre, 'cantidad' => $p->cantidad, 'precio' => $p->precio];})->toArray()) }}">
                    <div class="row">
                       
                        
                    </div>

                    <button type="submit" class="btn btn-primary" id="submitButton"><i class="fas fa-save"></i>
                        Guardar</button>
                    <a href="{{ route('orden.pedidos', $pedido->id_semana) }}" class="btn btn-secondary"><i
                            class="fas fa-times"></i>
                        Cancelar</a>
                </form>
            </div>
        </div>
    </div>

@section('js')
<script>
    function actualizarFormaPago() {
        const formaPago = document.getElementById('forma_pago').value;
        const campoEfectivo = document.getElementById('campo_efectivo');
        const campoQR = document.getElementById('campo_qr');

        const inputEfectivo = document.getElementById('efectivo');
        const inputQR = document.getElementById('transferencia_qr');

        // Reset: ocultar y limpiar ambos
        campoEfectivo.style.display = 'none';
        campoQR.style.display = 'none';

        // Modo efectivo
        if (formaPago === 'efectivo') {
            campoEfectivo.style.display = 'block';
            inputQR.value = 0; // Limpiar QR
        }

        // Modo QR
        if (formaPago === 'qr') {
            campoQR.style.display = 'block';
            inputEfectivo.value = 0; // Limpiar efectivo
        }

        // Modo ambos
        if (formaPago === 'ambos') {
            campoEfectivo.style.display = 'block';
            campoQR.style.display = 'block';
        }

        actualizarMonto();
    }

    function actualizarMonto() {
        const efectivo = parseFloat(document.getElementById('efectivo').value) || 0;
        const qr = parseFloat(document.getElementById('transferencia_qr').value) || 0;
        const total = efectivo + qr;
        document.getElementById('monto_deposito').value = total.toFixed(2);
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Eventos en inputs
        document.getElementById('efectivo').addEventListener('input', actualizarMonto);
        document.getElementById('transferencia_qr').addEventListener('input', actualizarMonto);
        document.getElementById('forma_pago').addEventListener('change', actualizarFormaPago);

        // Inicialización automática con lógica
        const efectivo = parseFloat(document.getElementById('efectivo').value) || 0;
        const qr = parseFloat(document.getElementById('transferencia_qr').value) || 0;

        if (efectivo > 0 && qr > 0) {
            document.getElementById('forma_pago').value = 'ambos';
        } else if (efectivo > 0) {
            document.getElementById('forma_pago').value = 'efectivo';
        } else if (qr > 0) {
            document.getElementById('forma_pago').value = 'qr';
        }

        actualizarFormaPago();
    });
</script>


    <script>
        // Mostrar la vista previa de la imagen cuando se selecciona una nueva imagen
        document.getElementById('foto_comprobante').addEventListener('change', function(event) {
            var input = event.target;
            var preview = document.getElementById('foto-preview');
            var container = document.getElementById('foto-preview-container');

            // Si se ha seleccionado un archivo, mostrar la vista previa
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    container.style.display = 'block'; // Asegurarse de que el contenedor sea visible
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                // Si no hay archivo seleccionado, esconder la vista previa
                container.style.display = 'none';
            }
        });

        // Mostrar el botón de "Agregar Imagen" y ocultar el de "Eliminar Imagen" cuando se presione el botón "Eliminar Imagen"
        document.getElementById('eliminar-imagen').addEventListener('click', function() {
            // Cambiar el valor del campo oculto para marcar que la imagen fue eliminada
            document.getElementById('foto_comprobante_eliminada').value = "true";

            // Ocultar la imagen actual y mostrar el campo de carga de imagen
            document.getElementById('foto-preview-container').style.display = 'none';
            document.getElementById('foto_comprobante').style.display = 'block';
            document.getElementById('foto_comprobante').value = ''; // Limpiar el campo de archivo
        });
    </script>
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
                const file = event.target.files;
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
        let productosSeleccionados = JSON.parse(document.getElementById('productos').value || '[]');

        // Inicializar la lista visual de productos seleccionados
        actualizarListaProductos();

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
            editarNombreInput.value = producto.nombre; // Aquí se asigna el nombre del producto
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
    const totalCantidadInput = document.getElementById('cantidad_productos');
    const montoDepositoInput = document.getElementById('monto_deposito');
    const detalleInput = document.getElementById('detalle'); // Nuevo: capturamos el campo detalle

    productoList.innerHTML = ''; // Limpiar la lista antes de volver a llenarla

    let totalCantidad = 0; // Para la cantidad total
    let totalPrecio = 0;   // Para el monto total
    let detalleText = '';  // Nuevo: texto acumulativo del detalle

    // Recorrer productos seleccionados
    productosSeleccionados.forEach((producto, index) => {
        const li = document.createElement('li');
        li.classList.add('list-group-item');
        li.innerHTML =
            `Producto: ${producto.nombre} - Cantidad: ${producto.cantidad} - Precio Total: ${producto.precio}
            <button type="button" class="btn btn-danger btn-sm float-end" onclick="eliminarProducto(${index})">
                Eliminar
            </button>
            <button type="button" class="btn btn-primary btn-sm float-end" onclick="editarProducto(${index})">
                Editar
            </button>`;
        productoList.appendChild(li);

        totalCantidad += parseInt(producto.cantidad);
        totalPrecio += parseFloat(producto.precio);

        // Construir el texto del detalle
        detalleText += `- ${producto.nombre} (Cantidad: ${producto.cantidad})\n`;
    });

    // Actualizar campos de cantidad y monto total
    totalCantidadInput.value = totalCantidad;
    montoDepositoInput.value = totalPrecio.toFixed(2);

    // Actualizar el detalle automáticamente
    detalleInput.value = detalleText.trim();

    // Actualizar campo oculto productos
    document.getElementById('productos').value = JSON.stringify(productosSeleccionados);
}

        // Función para eliminar un producto de la lista
        function eliminarProducto(index) {
            // Eliminar producto del array
            const producto = productosSeleccionados[index];
            const pedidoId = document.querySelector('input[name="id_semana"]')
                .value; // Asumiendo que id_semana es el ID del pedido

            // Enviar solicitud AJAX para eliminar el producto
            fetch('/pedido-producto/eliminar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        id_pedido: pedidoId,
                        id_producto: producto.id_producto,
                        cantidad: producto.cantidad
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.message === 'Producto eliminado con éxito') {
                        // Eliminar el producto de la lista local
                        productosSeleccionados.splice(index, 1);
                        actualizarListaProductos();
                    } else {
                        alert('Error al eliminar el producto');
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Función para manejar el envío del formulario
        document.getElementById('submitButton').addEventListener('click', function(event) {
            // Obtener los productos seleccionados (decodificar el JSON)
            const productos = JSON.parse(document.getElementById('productos').value);

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
        // Función para filtrar las opciones del select
        document.addEventListener('DOMContentLoaded', function() {
            const productoSearch = document.getElementById('productoSearch');

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

            
        });
    </script>

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.6.17/dist/sweetalert2.min.css" rel="stylesheet">

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.6.17/dist/sweetalert2.all.min.js"></script>

    <!-- Incluir Bootstrap JavaScript antes de cerrar el body -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

@endsection

@endsection
