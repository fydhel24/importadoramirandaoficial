@extends('adminlte::page')

@section('title', 'Gestión de Promociones')

@section('adminlte_css')
{{-- SweetAlert2 CSS --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
{{-- Font Awesome para iconos --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
{{-- Select2 CSS --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Estilos generales */
    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        background-color: #f0f2f5;
        color: #333;
    }

    .container {
        max-width: 1300px; /* Ancho máximo para el contenido */
    }

    h1 {
        color: #1d3557;
        font-weight: 600;
        margin-bottom: 30px;
        text-align: center;
    }

    /* Botones y formularios */
    .btn-futuristic {
        background: linear-gradient(135deg, #457b9d, #1d3557);
        color: white;
        border: none;
        border-radius: 8px; /* Bordes más suaves */
        padding: 12px 25px;
        font-size: 15px;
        font-weight: bold;
        text-transform: uppercase;
        transition: all 0.3s ease-in-out;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        display: inline-flex;
        align-items: center;
        gap: 8px; /* Espacio entre icono y texto */
    }

    .btn-futuristic:hover {
        transform: translateY(-3px);
        background: linear-gradient(135deg, #1d3557, #457b9d);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
    }

    .btn-futuristic i {
        font-size: 16px;
    }

    .form-control {
        border-radius: 8px;
        border: 1px solid #a8dadc;
        padding: 12px 15px;
        transition: all 0.3s ease-in-out;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .form-control:focus {
        border-color: #457b9d;
        box-shadow: 0 0 0 0.25rem rgba(69, 123, 157, 0.25);
        outline: none;
    }

    /* Select2 specific styles to make it match other inputs */
    .select2-container .select2-selection--single {
        height: 44px; /* Make it same height as other form-control inputs */
        border-radius: 8px !important;
        border: 1px solid #a8dadc !important;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05) !important;
        transition: all 0.3s ease-in-out !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 42px; /* Vertically align text */
        padding-left: 15px; /* Match padding of other inputs */
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px; /* Vertically align arrow */
        right: 10px;
    }

    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #457b9d !important;
        box-shadow: 0 0 0 0.25rem rgba(69, 123, 157, 0.25) !important;
    }

    /* Make dropdown results wider/taller if needed */
    .select2-container--default .select2-results__option {
        padding: 10px 15px;
    }

    /* Cards de Promoción */
    .card {
        border: none;
        border-radius: 15px; /* Bordes más redondeados */
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1); /* Sombra más pronunciada */
        background-color: #ffffff;
        transition: all 0.3s ease-in-out;
        display: flex; /* Para mejor control del layout interno */
        flex-direction: column;
        height: 100%; /* Asegura que todas las cards en una fila tengan la misma altura */
    }

    .card:hover {
        transform: translateY(-5px) scale(1.01);
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.2);
    }

    .card-header {
        background: linear-gradient(90deg, #1d3557, #457b9d);
        color: white;
        font-weight: 700;
        text-align: center;
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
        padding: 15px 20px;
        border-bottom: none; /* Eliminar borde inferior */
    }

    .card-body {
        padding: 25px;
        display: flex;
        flex-direction: column;
        flex-grow: 1; /* Permite que el body ocupe el espacio restante */
    }

    .card-body p {
        margin-bottom: 8px;
        font-size: 15px;
    }

    .card-body strong {
        color: #1d3557;
    }

    .badge {
        padding: 6px 10px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge.bg-success { background-color: #28a745 !important; }
    .badge.bg-danger { background-color: #dc3545 !important; }

    .list-group-flush .list-group-item {
        background-color: #f8f9fa; /* Fondo más claro para items de lista */
        border-bottom: 1px solid #e9ecef;
        padding: 10px 15px;
        font-size: 14px;
        border-radius: 0; /* Sin bordes redondeados internos */
    }
    .list-group-flush .list-group-item:first-child { border-top-left-radius: 8px; border-top-right-radius: 8px;}
    .list-group-flush .list-group-item:last-child { border-bottom-left-radius: 8px; border-bottom-right-radius: 8px; border-bottom: none;}


    .card-actions {
        margin-top: auto; /* Empuja los botones al final de la card */
        padding-top: 15px;
        border-top: 1px solid #eee;
        display: flex;
        justify-content: space-around;
        gap: 10px; /* Espacio entre los botones */
        flex-wrap: wrap; /* Permite que los botones se envuelvan en pantallas pequeñas */
    }
    .card-actions .btn {
        flex: 1 1 auto; /* Permite que los botones crezcan y se encojan */
        min-width: 100px; /* Ancho mínimo para cada botón */
    }


    /* Modal de Venta */
    .modal-content {
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        background-color: #ffffff;
    }

    .modal-header {
        background: linear-gradient(90deg, #1d3557, #457b9d);
        color: white;
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
        padding: 20px;
        border-bottom: none;
    }

    .modal-title {
        font-weight: 600;
        font-size: 1.5rem;
    }

    .modal-body {
        padding: 30px;
    }

    .modal-footer {
        border-top: none;
        padding: 20px 30px;
        justify-content: space-between; /* Botones a los extremos */
    }

    .modal-footer .btn {
        min-width: 120px;
    }

    /* Alertas */
    .swal2-popup {
        border-radius: 15px !important;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2) !important;
    }
    .swal2-title {
        color: #1d3557 !important;
    }
    .swal2-success .swal2-success-ring {
        border-color: #457b9d !important;
    }
    .swal2-success [class^=swal2-success-line] {
        background-color: #457b9d !important;
    }
    .swal2-warning {
        border-color: #f39c12 !important;
    }
    .swal2-error {
        border-color: #e74c3c !important;
    }

    /* Responsividad */
    @media (max-width: 768px) {
        h1 {
            font-size: 2rem;
            margin-bottom: 20px;
        }
        .btn-futuristic {
            padding: 10px 20px;
            font-size: 13px;
        }
        .card-actions {
            flex-direction: column; /* Apila los botones en móviles */
            gap: 8px;
        }
        .card-actions .btn {
            width: 100%; /* Ocupa todo el ancho disponible */
        }
        .form-control {
            padding: 10px 12px;
        }
        .modal-body {
            padding: 20px;
        }
    }
</style>
@endsection

@section('content')
<div class="container">
    <h1><i class="fas fa-tags"></i> Gestión de Promociones</h1>

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <a href="{{ route('promociones.create') }}" class="btn btn-futuristic">
            <i class="fas fa-plus-circle"></i> Crear Nueva Promoción
        </a>
        <input type="text" id="buscar_promocion" class="form-control flex-grow-1" placeholder="Buscar promoción por nombre...">
    </div>

    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="lista_promociones">
        @forelse ($promociones as $promocion)
        <div class="col">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title m-0">{{ $promocion->nombre }}</h5>
                </div>
                <div class="card-body">
                    <p><strong>Precio Promoción:</strong> <span class="badge bg-info text-white">Bs {{ number_format($promocion->precio_promocion, 2) }}</span></p>
                    <p><strong>Estado:</strong>
                        <span class="badge {{ $promocion->estado ? 'bg-success' : 'bg-danger' }}">
                            {{ $promocion->estado ? 'Activo' : 'Inactivo' }}
                        </span>
                    </p>
                    <p><strong>Sucursal:</strong> {{ $promocion->sucursal->nombre ?? 'N/A' }}</p>
                    <p><strong>Creado por:</strong> {{ $promocion->usuario->name ?? 'N/A' }}</p>

                    <h6 class="mt-3 mb-2 text-primary">Productos Incluidos:</h6>
                    <ul class="list-group list-group-flush mb-3">
                        @forelse ($promocion->productos as $producto)
                            @php
                            $inventario = $producto->inventarioEnSucursal($promocion->id_sucursal);
                            $stockDisponible = $inventario ? $inventario->cantidad : 0;
                            @endphp
                            <li class="list-group-item">
                                <strong>{{ $producto->nombre }}</strong><br>
                                Cantidad: {{ $producto->pivot->cantidad }}, Precio Unitario: Bs {{ number_format($producto->pivot->precio_unitario, 2) }}<br>
                                <span class="text-muted">Stock en Sucursal ({{ $promocion->sucursal->nombre ?? 'N/A' }}): {{ $stockDisponible }}</span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">No hay productos asociados a esta promoción.</li>
                        @endforelse
                    </ul>

                    <div class="card-actions">
                        <button class="btn btn-futuristic btn-success open-modal"
                            data-bs-toggle="modal" data-bs-target="#venderModal"
                            data-id="{{ $promocion->id }}"
                            data-precio="{{ $promocion->precio_promocion }}"
                            data-sucursal="{{ $promocion->sucursal->id }}"
                            data-productos="{{ $promocion->productos->map(function ($producto) {
                                            return [
                                                'id' => $producto->id,
                                                'nombre' => $producto->nombre,
                                                'cantidad' => $producto->pivot->cantidad,
                                                'precio_unitario' => $producto->pivot->precio_unitario,
                                            ];
                                        })->toJson() }}">
                            <i class="fas fa-shopping-cart"></i> Vender
                        </button>
                        <a href="{{ route('promociones.edit', $promocion->id) }}"
                            class="btn btn-futuristic btn-warning">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <form action="{{ route('promociones.destroy', $promocion->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-futuristic btn-danger">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center" role="alert">
                    No se encontraron promociones. ¡Crea una nueva!
                </div>
            </div>
        @endforelse
    </div>
</div>

<div class="modal fade" id="venderModal" tabindex="-1" aria-labelledby="venderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="ventaForm" method="POST"> {{-- No action, lo manejamos con fetch --}}
                @csrf
                {{-- Campos ocultos para los datos de la promoción --}}
                <input type="hidden" name="id_promocion" id="id_promocion">
                <input type="hidden" name="productos" id="productos">
                <input type="hidden" name="id_sucursal" id="id_sucursal">
                <input type="hidden" name="costo_total" id="costo_total_hidden">
                <input type="hidden" name="estado" value="recogido"> {{-- Estado fijo --}}
                <input type="hidden" name="tipo_pago" value="Efectivo"> {{-- Tipo de pago fijo --}}
                <input type="hidden" name="monto_pagado" id="monto_pagado_hidden"> {{-- Se asignará costo_total --}}
                <input type="hidden" name="monto_cambio" value="0"> {{-- Siempre 0 --}}


                <div class="modal-header">
                    <h5 class="modal-title" id="venderModalLabel"><i class="fas fa-cash-register"></i> Confirmar Venta de Promoción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="cliente"><i class="fas fa-user"></i> Nombre del Cliente:</label>
                        <input type="text" name="nombre_cliente" id="cliente" class="form-control"
                            placeholder="Ej: Juan Pérez" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="ci"><i class="fas fa-id-card"></i> CI / NIT:</label>
                        <input type="text" name="ci" id="ci" class="form-control"
                            placeholder="Opcional: N° de CI o NIT">
                    </div>
                    <div class="form-group mb-3">
                        <label for="vendedor_id"><i class="fas fa-handshake"></i> Vendedor Responsable:</label>
                        {{-- Select2 will enhance this select box --}}
                        <select name="id_user" id="vendedor_id" class="form-control" required>
                            <option value="">-- Seleccione un vendedor --</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-4">
                        <label for="monto-total-display"><i class="fas fa-dollar-sign"></i> Total a Pagar:</label>
                        <input type="text" id="monto-total-display" class="form-control form-control-lg text-end fw-bold" readonly style="color: #28a745; font-size: 1.5rem;">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-box"></i> Productos en esta promoción:</label>
                        <ul id="productos-promocion" class="list-group">
                            {{-- Los productos se cargarán aquí vía JS --}}
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-futuristic btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times-circle"></i> Cancelar
                    </button>
                    <button type="button" id="confirmar-venta" class="btn btn-futuristic btn-success">
                        <i class="fas fa-check-circle"></i> Confirmar Venta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('adminlte_js')
{{-- SweetAlert2 JS --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
{{-- jQuery (Select2 depends on jQuery) --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
{{-- Bootstrap JavaScript (Popper.js es una dependencia de Bootstrap 5) --}}
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.min.js"></script>
{{-- Select2 JS --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Select2 on the vendor select box
        $('#vendedor_id').select2({
            dropdownParent: $('#venderModal'), // Important for modals to position correctly
            placeholder: "-- Seleccione un vendedor --",
            allowClear: true, // Allows clearing the selection
            width: '100%' // Make it take full width of parent
        });

        const modal = document.getElementById('venderModal');
        const productosInput = document.getElementById('productos');
        const productosPromocionList = document.getElementById('productos-promocion');
        const costoTotalHiddenInput = document.getElementById('costo_total_hidden');
        const montoTotalDisplay = document.getElementById('monto-total-display');
        const idPromocionInput = document.getElementById('id_promocion');
        const idSucursalInput = document.getElementById('id_sucursal');
        const buscarPromocionInput = document.getElementById('buscar_promocion');
        const listaPromociones = document.getElementById('lista_promociones');
        const vendedorSelect = document.getElementById('vendedor_id');
        const montoPagadoHiddenInput = document.getElementById('monto_pagado_hidden');

        // Evento para cuando el modal se muestra
        modal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; // Botón que activó el modal

            const promocionId = button.getAttribute('data-id');
            const sucursalId = button.getAttribute('data-sucursal');
            const precioPromocion = parseFloat(button.getAttribute('data-precio'));
            const productosRelacionados = JSON.parse(button.getAttribute('data-productos'));

            // Clear and populate the product list in the modal
            productosPromocionList.innerHTML = '';
            if (productosRelacionados.length > 0) {
                productosRelacionados.forEach(producto => {
                    const { nombre, cantidad, precio_unitario } = producto;
                    if (cantidad && precio_unitario) {
                        const totalProducto = precio_unitario * cantidad;
                        const listItem = document.createElement('li');
                        listItem.classList.add('list-group-item');
                        listItem.innerHTML = `
                            <div class="d-flex justify-content-between">
                                <span>${nombre} (Cant: ${cantidad})</span>
                                <span>Bs ${totalProducto.toFixed(2)}</span>
                            </div>
                        `;
                        productosPromocionList.appendChild(listItem);
                    }
                });
            } else {
                productosPromocionList.innerHTML = '<li class="list-group-item text-muted">No hay productos definidos para esta promoción.</li>';
            }


            // Assign values to hidden and display inputs
            productosInput.value = JSON.stringify(productosRelacionados);
            costoTotalHiddenInput.value = precioPromocion.toFixed(2);
            montoTotalDisplay.value = `Bs ${precioPromocion.toFixed(2)}`; // Format for display
            idPromocionInput.value = promocionId;
            idSucursalInput.value = sucursalId;

            // IMPORTANT: Assign costo_total to monto_pagado_hidden here
            montoPagadoHiddenInput.value = precioPromocion.toFixed(2);

            // Clear user input fields when opening the modal
            document.getElementById('cliente').value = '';
            document.getElementById('ci').value = '';
            // Reset Select2 selection
            $('#vendedor_id').val('').trigger('change'); // This is how you reset Select2
        });

        // Search functionality for promotions
        buscarPromocionInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            listaPromociones.querySelectorAll('.col').forEach(cardCol => {
                const cardTitle = cardCol.querySelector('.card-title');
                if (cardTitle) {
                    const nombrePromocion = cardTitle.textContent.toLowerCase();
                    if (nombrePromocion.includes(searchTerm)) {
                        cardCol.style.display = 'block';
                    } else {
                        cardCol.style.display = 'none';
                    }
                }
            });
        });

        // Event for the confirm sale button
        const confirmarVentaBtn = document.getElementById('confirmar-venta');
        if (confirmarVentaBtn) {
            confirmarVentaBtn.addEventListener('click', function(event) {
                event.preventDefault();
                confirmarVenta();
            });
        } else {
            console.error("The button with ID 'confirmar-venta' was not found.");
        }

        function confirmarVenta() {
            console.log("confirmarVenta function called.");

            const ventaForm = document.getElementById('ventaForm');
            const formData = new FormData(ventaForm);

            // Debugging: Show FormData content before sending
            console.log("FormData created. Content:");
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }

            // Validate that a vendor is selected and client name is entered
            if (!formData.get('id_user')) {
                Swal.fire({
                    title: 'Vendedor Requerido',
                    text: 'Por favor, seleccione un vendedor para continuar con la venta.',
                    icon: 'warning',
                    confirmButtonText: 'Aceptar',
                });
                return;
            }
            if (!formData.get('nombre_cliente').trim()) {
                Swal.fire({
                    title: 'Nombre de Cliente Requerido',
                    text: 'Por favor, ingrese el nombre del cliente.',
                    icon: 'warning',
                    confirmButtonText: 'Aceptar',
                });
                return;
            }

            // First fetch: send data to `finpromocion`
            fetch('{{ route('finpromocion') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(errorData => {
                            throw new Error(errorData.message || 'Error al enviar los datos a finpromocion.');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log("Data sent to finpromocion successfully. Response:", data);

                    // Show success alert
                    Swal.fire({
                        title: '¡Venta Confirmada!',
                        text: data.message || 'La venta se ha registrado con éxito.',
                        icon: 'success',
                        confirmButtonText: 'Aceptar',
                    }).then(() => {
                        // Clear the form and close the modal
                        const bootstrapModalInstance = bootstrap.Modal.getInstance(modal);
                        if (bootstrapModalInstance) {
                            console.log("Closing modal...");
                            bootstrapModalInstance.hide();
                        } else {
                            console.warn("No Bootstrap Modal instance found to close.");
                        }
                        ventaForm.reset(); // Reset native form fields
                        $('#vendedor_id').val('').trigger('change'); // Reset Select2 field
                        console.log("Form reset.");

                        // Optional: Reload the page if you need to update visible stock without F5
                        // window.location.reload();
                    });
                })
                .catch(error => {
                    console.error('Error in sale process (finpromocion):', error);
                    Swal.fire({
                        title: 'Error Crítico',
                        text: 'An error occurred while registering the sale: ' + error.message,
                        icon: 'error',
                        confirmButtonText: 'Aceptar',
                    });
                });
        }
    });
</script>
@endsection