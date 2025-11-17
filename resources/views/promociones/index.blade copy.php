@extends('adminlte::page')
@section('adminlte_css')
    <style>
        .btn-futuristic {
            background: linear-gradient(135deg, #1d3557, #457b9d);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            transition: transform 0.3s, background 0.3s;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-futuristic:hover {
            transform: scale(1.1);
            background: linear-gradient(135deg, #457b9d, #1d3557);
        }

        .card {
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            background: linear-gradient(135deg, #f1faee, #a8dadc);
            transition: transform 0.3s;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
        }

        .card-header {
            background: linear-gradient(135deg, #1d3557, #457b9d);
            color: white;
            font-weight: bold;
            text-align: center;
        }

        .list-group-item {
            background: rgba(255, 255, 255, 0.8);
            border: none;
            border-radius: 10px;
            margin-bottom: 5px;
        }

        .modal-content {
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
            background: linear-gradient(135deg, #f1faee, #a8dadc);
        }

        .modal-header {
            background: linear-gradient(135deg, #1d3557, #457b9d);
            color: white;
            border-bottom: none;
        }

        .form-control {
            border-radius: 10px;
            border: 1px solid #457b9d;
            padding: 10px;
            transition: box-shadow 0.3s;
        }

        .form-control:focus {
            box-shadow: 0 0 10px rgba(69, 123, 157, 0.5);
            border-color: #1d3557;
        }
    </style>
@endsection

@section('content')
    <div class="container">
        <h1 class="mb-4">Promociones</h1>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('promociones.create') }}" class="btn btn-primary btn-futuristic">Crear Promoción</a>
            <input type="text" id="buscar_promocion" class="form-control w-50" placeholder="Buscar promoción...">
        </div>
        <div class="row" id="lista_promociones">
            @foreach ($promociones as $promocion)
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title m-0">{{ $promocion->nombre }}</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Precio:</strong> Bs {{ $promocion->precio_promocion }}</p>
                            <p><strong>Estado:</strong>
                                <span class="badge {{ $promocion->estado ? 'bg-success' : 'bg-danger' }}">
                                    {{ $promocion->estado ? 'Activo' : 'Inactivo' }}
                                </span>
                            </p>
                            <p><strong>Sucursal:</strong> {{ $promocion->sucursal->nombre }}</p>
                            <p><strong>Creado por:</strong> {{ $promocion->usuario->name }}</p>
                            <ul class="list-group list-group-flush">
                                @foreach ($promocion->productos as $producto)
                                    @php
                                        $inventario = $producto->inventarioEnSucursal($promocion->id_sucursal);
                                        $stockDisponible = $inventario ? $inventario->cantidad : 0;
                                    @endphp
                                    <li class="list-group-item">
                                        {{ $producto->nombre }}
                                        (Cantidad: {{ $producto->pivot->cantidad }},
                                        Precio Unitario: Bs {{ number_format($producto->pivot->precio_unitario, 2) }},
                                        Stock Disponible: {{ $stockDisponible }})
                                    </li>
                                @endforeach
                            </ul>

                            <div class="mt-3 d-flex justify-content-between">
                                <button class="btn btn-futuristic btn-sm open-modal" data-id="{{ $promocion->id }}"
                                    data-precio="{{ $promocion->precio_promocion }}"
                                    data-sucursal="{{ $promocion->sucursal->id }}"
                                    data-productos="{{ $promocion->productos->map(function ($producto) {
                                            return [
                                                'id' => $producto->id,
                                                'nombre' => $producto->nombre,
                                                'cantidad' => $producto->pivot->cantidad,
                                                'precio' => $producto->pivot->precio_unitario,
                                            ];
                                        })->toJson() }}">
                                    <i class="fas fa-shopping-cart"></i> Vender
                                </button>
                                <a href="{{ route('promociones.edit', $promocion->id) }}"
                                    class="btn btn-futuristic btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <form action="{{ route('promociones.destroy', $promocion->id) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-futuristic btn-danger btn-sm">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
<!-- Modal de Venta -->
<div class="modal fade" id="venderModal" tabindex="-1" aria-labelledby="venderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="ventaForm" method="POST" action="{{ route('finpromocion') }}">
                @csrf
                <input type="hidden" name="id_promocion" id="id_promocion">
                <input type="hidden" name="productos" id="productos">
                <input type="hidden" name="id_sucursal" id="id_sucursal">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Venta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Form fields here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-futuristic btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-futuristic btn-success" onclick="confirmarVenta()">Confirmar Venta</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <!-- Modal de Venta -->
    <div class="modal fade" id="venderModal" tabindex="-1" aria-labelledby="venderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('finpromocion') }}">
                    @csrf
                    <input type="hidden" name="id_promocion" id="id_promocion">
                    <input type="hidden" name="productos" id="productos">
                    <input type="hidden" name="id_sucursal" id="id_sucursal">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar Venta</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="cliente">Cliente</label>
                            <input type="text" name="nombre_cliente" id="cliente" class="form-control"
                                placeholder="Ingrese el nombre del cliente" required>
                        </div>
                        <div class="form-group">
                            <label for="ci">CI / NIT</label>
                            <input type="text" name="ci" id="ci" class="form-control"
                                placeholder="Ingrese el CI o NIT del cliente">
                        </div>
                        <div class="form-group">
                            <label for="monto-total">Total a Pagar</label>
                            <input type="text" id="monto-total" name="costo_total" class="form-control" readonly>
                        </div>
                        <div class="form-group">
                            <label for="monto-pagado">Monto Pagado</label>
                            <input type="number" id="monto-pagado" name="monto_pagado" class="form-control"
                                placeholder="Ingrese el monto pagado" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="monto-cambio">Monto de Cambio</label>
                            <input type="text" id="monto-cambio" name="monto_cambio" class="form-control" readonly>
                        </div>

                        <div class="form-group">
                            <label for="tipo_pago">Método de Pago</label>
                            <select name="tipo_pago" id="tipo_pago" class="form-control" required>
                                <option value="Efectivo">Efectivo</option>
                                <option value="QR">Transferencia QR</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Productos:</label>
                            <ul id="productos-promocion" class="list-group"></ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-futuristic btn-secondary"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-futuristic btn-success">Confirmar Venta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

@endsection
@section('adminlte_js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('venderModal');
            const productosInput = document.getElementById('productos');
            const productosPromocionList = document.getElementById('productos-promocion');
            const totalSinDescuento = document.getElementById('monto-total');
            const montoPagadoInput = document.getElementById('monto-pagado');
            const montoCambioInput = document.getElementById('monto-cambio');
            const idPromocionInput = document.getElementById('id_promocion');
            const idSucursalInput = document.getElementById('id_sucursal');
            const buscarPromocionInput = document.getElementById('buscar_promocion');
            const listaPromociones = document.getElementById('lista_promociones');

            document.querySelectorAll('.open-modal').forEach(button => {
                button.addEventListener('click', function() {
                    const promocionId = this.getAttribute('data-id');
                    const sucursalId = this.getAttribute('data-sucursal');
                    const precioPromocion = parseFloat(this.getAttribute(
                        'data-precio')); // Obtener el precio total de la promoción
                    const productosRelacionados = JSON.parse(this.getAttribute('data-productos'));

                    productosPromocionList.innerHTML = ''; // Limpiar la lista de productos
                    productosRelacionados.forEach(producto => {
                        const {
                            id,
                            nombre,
                            cantidad,
                            precio
                        } = producto;

                        if (cantidad && precio) {
                            const totalProducto = precio * cantidad;

                            const listItem = document.createElement('li');
                            listItem.classList.add('list-group-item');
                            listItem.innerHTML = `
                        <div class="d-flex justify-content-between">
                            <span>${nombre} (Cantidad: ${cantidad})</span>
                            <span>Bs ${totalProducto.toFixed(2)}</span>
                        </div>
                    `;
                            productosPromocionList.appendChild(listItem);
                        }
                    });

                    productosInput.value = JSON.stringify(
                        productosRelacionados); // Guardar los productos en un campo oculto
                    totalSinDescuento.value = precioPromocion.toFixed(
                        2); // Usar el precio total de la promoción
                    montoPagadoInput.value = ''; // Limpiar campos de monto al abrir el modal
                    montoCambioInput.value = ''; // Limpiar campos de cambio al abrir el modal
                    idPromocionInput.value = promocionId; // Guardar el ID de la promoción
                    idSucursalInput.value = sucursalId; // Guardar el ID de la sucursal

                    const bootstrapModal = new bootstrap.Modal(modal); // Mostrar el modal
                    bootstrapModal.show();
                });
            });

            // Calcular el cambio automáticamente
            montoPagadoInput.addEventListener('input', function() {
                const montoPagado = parseFloat(montoPagadoInput.value) || 0;
                const total = parseFloat(totalSinDescuento.value) || 0;

                const cambio = montoPagado - total; // Calcular el cambio
                montoCambioInput.value = cambio >= 0 ? cambio.toFixed(2) :
                    'Monto insuficiente'; // Mostrar el cambio o mensaje de error
            });

            // Funcionalidad del buscador
            buscarPromocionInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase(); // Obtener el término de búsqueda en minúsculas

                listaPromociones.querySelectorAll('.col-md-4').forEach(card => {
                    const nombrePromocion = card.querySelector('.card-title').textContent
                        .toLowerCase(); // Obtener el nombre de la promoción
                    if (nombrePromocion.includes(searchTerm)) {
                        card.style.display = 'block'; // Mostrar la tarjeta si coincide
                    } else {
                        card.style.display = 'none'; // Ocultar la tarjeta si no coincide
                    }
                });
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const confirmarVentaBtn = document.getElementById('confirmar-venta');
            const ventaForm = document.querySelector('form[action="{{ route('finpromocion') }}"]');

            confirmarVentaBtn.addEventListener('click', function() {
                console.log('El botón fue clickeado');
                const formData = new FormData(ventaForm);

                // Abrir una pestaña nueva de inmediato para evitar bloqueos del navegador
                const nuevaPestaña = window.open('', '_blank');

                // Enviar datos a la ruta `finpromocion`
                fetch('{{ route('finpromocion') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                        },
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Generar la URL para la nota de venta
                            const params = new URLSearchParams({
                                nombre_cliente: formData.get('nombre_cliente'),
                                costo_total: formData.get('costo_total'),
                                ci: formData.get('ci'),
                                productos: formData.get('productos'),
                                monto_pagado: formData.get('monto_pagado'),
                                monto_cambio: formData.get('monto_cambio'),
                                tipo_pago: formData.get('tipo_pago'),
                                id_sucursal: formData.get('id_sucursal'),
                            });

                            console.log('Parametros generados:', params
                                .toString()); // Ver los parámetros generados

                            const url = `{{ route('notaPromocion') }}?${params.toString()}`;


                            // Verificar la URL generada
                            console.log('URL generada:', url); // Esto debería mostrar la URL
                            // Redirigir la nueva pestaña a la URL de la nota de venta
                            // Redirigir la nueva pestaña a la URL de la nota de venta
                            nuevaPestaña.location.href = url;

                            // Cerrar el modal y limpiar el formulario
                            const bootstrapModal = bootstrap.Modal.getInstance(document.getElementById(
                                'venderModal'));
                            bootstrapModal.hide();

                            ventaForm.reset(); // Reiniciar el formulario
                        } else {
                            nuevaPestaña.close(); // Cerrar la pestaña si ocurre un error
                            Swal.fire({
                                title: 'Error',
                                text: data.message || 'Ocurrió un error al procesar la venta.',
                                icon: 'error',
                                confirmButtonText: 'Aceptar',
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        nuevaPestaña.close(); // Cerrar la pestaña si ocurre un error
                        Swal.fire({
                            title: 'Error',
                            text: 'Ocurrió un error al procesar la venta.',
                            icon: 'error',
                            confirmButtonText: 'Aceptar',
                        });
                    });
            });
        });
    </script>
@endsection
