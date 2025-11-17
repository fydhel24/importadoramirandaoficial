<!-- Extending the AdminLTE layout -->
@extends('adminlte::page')

<!-- Setting the page title -->
@section('title', 'Carrito de Ventas Moderno')

<!-- Content Header -->
@section('content_header')
    <h1 class="text-3xl font-bold text-gray-800 mb-4">
        <i class="fas fa-shopping-cart mr-3 text-blue-500"></i>
        Carrito de Ventas
    </h1>
@stop

<!-- Main Content -->
@section('content')
    <div class="container-fluid py-6">
        <div class="row">
            <!-- Productos Section -->
            <div class="col-12">
                <div class="card shadow-2xl rounded-xl overflow-hidden border-0">
                    <div class="card-header bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 p-6">
                        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                            <h3 class="card-title text-white text-2xl font-bold flex items-center">
                                <i class="fas fa-box-open mr-3"></i>
                                Productos Disponibles
                            </h3>
                            <div class="relative w-full md:w-auto">
                                <input type="text" id="searchInput"
                                    class="form-control pl-12 pr-4 py-3 rounded-full bg-white/90 backdrop-blur-sm text-gray-800 focus:ring-4 focus:ring-blue-300 focus:border-blue-400 shadow-lg transition-all duration-300 w-full md:w-80"
                                    placeholder="Buscar productos...">
                                <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-500">
                                    <i class="fas fa-search text-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-6 bg-gradient-to-br from-gray-50 to-gray-100">
                        <div id="productosContainer"
                            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                            <!-- Productos se cargarán aquí via AJAX -->
                            <div class="col-span-full text-center py-12">
                                <div class="animate-pulse">
                                    <i class="fas fa-spinner fa-spin text-4xl text-blue-500 mb-4"></i>
                                    <p class="text-gray-600">Cargando productos...</p>
                                </div>
                            </div>
                        </div>
                        <div id="paginationContainer" class="flex justify-center mt-8">
                            <!-- Paginación se cargará aquí -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cart Button with Item Count -->
        <div class="fixed bottom-6 right-6 z-50">
            <button id="openCartModal"
                class="cart-button relative bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold py-4 px-6 rounded-full shadow-2xl transition-all duration-300 transform hover:scale-110 hover:shadow-green-500/25">
                <i class="fas fa-shopping-cart mr-2 text-lg"></i>
                <span class="hidden sm:inline">Carrito</span>
                <span id="cartItemCount"
                    class="cart-item-count absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-6 w-6 flex items-center justify-center shadow-lg animate-pulse">0</span>
            </button>
        </div>

        <!-- Cart Modal -->
        <div class="modal fade" id="cartModal" tabindex="-1" role="dialog" aria-labelledby="cartModalLabel"
            aria-hidden="true">
            <div class="modal-dialog w-[1000px] modal-dialog-centered modal-dialog-scrollable">
                <!-- Aquí cambiamos el ancho -->
                <div class="modal-content rounded-2xl shadow-2xl border-0 overflow-hidden">
                    <div class="modal-header bg-gradient-to-r from-green-600 via-emerald-600 to-teal-600 p-6 border-0">
                        <h5 class="modal-title text-white text-2xl font-bold flex items-center" id="cartModalLabel">
                            <i class="fas fa-shopping-bag mr-3"></i>
                            Carrito de Compras
                        </h5>
                        <button type="button"
                            class="close text-white opacity-75 hover:opacity-100 transition-opacity duration-200"
                            data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true" class="text-2xl">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-6 bg-gradient-to-br from-gray-50 to-white max-h-[80vh] overflow-y-auto">
                        <!-- Aquí puedes ajustar el alto también -->
                        <div id="carritoItems" class="space-y-4 mb-6">
                            <div class="text-center text-gray-500 py-8">
                                <i class="fas fa-shopping-cart text-4xl mb-4 opacity-50"></i>
                                <p class="text-lg">El carrito está vacío</p>
                            </div>
                        </div>

                        <!-- Formulario de Venta -->
                        <form id="ventaForm" class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
                            <h6 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                                <i class="fas fa-file-invoice-dollar mr-3 text-blue-500"></i>
                                Información de la Venta
                            </h6>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="form-group">
                                    <label for="cliente" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-user mr-2 text-blue-500"></i>Cliente *
                                    </label>
                                    <input type="text"
                                        class="form-control rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                        id="cliente" name="nombre_cliente" required>
                                </div>
                                <div class="form-group">
                                    <label for="userid" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-id-card mr-2 text-blue-500"></i>iduser / NIT
                                    </label>
                                    <input type="text"
                                        class="form-control rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                        id="userid" name="userid">
                                </div>
                                <div class="form-group">
                                    <label for="ci" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-id-card mr-2 text-blue-500"></i>CI / NIT
                                    </label>
                                    <input type="text"
                                        class="form-control rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                        id="ci" name="ci">
                                </div>
                                <div class="form-group">
                                    <label for="user" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-id-card mr-2 text-blue-500"></i>user
                                    </label>
                                    <input type="text"
                                        class="form-control rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                        id="user" name="user">
                                </div>
                                <div class="form-group">
                                    <label for="descuento" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-percentage mr-2 text-green-500"></i>Descuento (Bs)
                                    </label>
                                    <input type="number"
                                        class="form-control rounded-lg border-gray-300 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200"
                                        id="descuento" name="descuento" value="0" min="0" step="0.01">
                                </div>

                                <div class="form-group">
                                    <label for="tipoPago" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-credit-card mr-2 text-purple-500"></i>Método de Pago *
                                    </label>
                                    <select
                                        class="form-control rounded-lg border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200"
                                        id="tipoPago" name="tipo_pago" required>
                                        <option value="">Seleccionar método</option>
                                        <option value="Efectivo">💵 Efectivo</option>
                                        <option value="QR">📱 Transferencia QR</option>
                                        <option value="Efectivo y QR">💳 Pago Efectivo y QR</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Campos de pago dinámicos -->
                            <div id="pagoEfectivo" class="hidden mt-4">
                                <label for="montoPagado" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-money-bill-wave mr-2 text-green-500"></i>Monto Pagado en Efectivo
                                </label>
                                <input type="number"
                                    class="form-control rounded-lg border-gray-300 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200"
                                    id="montoPagado" name="pagado" step="0.01">
                            </div>

                            <div id="pagoQR" class="hidden mt-4">
                                <label for="montoPagadoQR" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-qrcode mr-2 text-blue-500"></i>Monto Pagado QR
                                </label>
                                <input type="number"
                                    class="form-control rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                    id="montoPagadoQR" name="pagado_qr" step="0.01">
                            </div>

                            <div id="qrCode" class="hidden mt-4" style="display: none;">
                                <label for="qrCodeInput" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-hashtag mr-2 text-indigo-500"></i>Código QR
                                </label>
                                <input type="text"
                                    class="form-control rounded-lg border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200"
                                    id="qrCodeInput" name="qr_code" placeholder="Ingrese el código QR" value="1111">
                            </div>

                            <!-- Totales -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                                <div class="form-group">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-calculator mr-2 text-blue-500"></i>Total Sin Descuento
                                    </label>
                                    <input type="text"
                                        class="form-control rounded-lg bg-blue-50 border-blue-200 text-blue-800 font-semibold"
                                        id="totalSinDescuento" readonly>
                                </div>

                                <div class="form-group">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-money-check-alt mr-2 text-green-500"></i>Monto Total a Pagar
                                    </label>
                                    <input type="text"
                                        class="form-control rounded-lg bg-green-50 border-green-200 text-green-800 font-bold text-lg"
                                        id="montoTotal" readonly>
                                </div>
                            </div>

                            <div id="cambioDiv" class="hidden mt-4">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-coins mr-2 text-yellow-500"></i>Cambio
                                </label>
                                <input type="text"
                                    class="form-control rounded-lg bg-yellow-50 border-yellow-200 text-yellow-800 font-semibold"
                                    id="cambio" readonly>
                            </div>

                            <!-- Garantía -->
                            <div class="mt-6">
                                <label class="block text-sm font-semibold text-gray-700 mb-3">
                                    <i class="fas fa-shield-alt mr-2 text-purple-500"></i>Compra del Producto
                                </label>
                                <div class="flex space-x-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="garantia" id="conGarantia"
                                            value="con garantia">
                                        <label class="form-check-label font-medium text-gray-700" for="conGarantia">
                                            ✅ Con Garantía
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="garantia" id="sinGarantia"
                                            value="sin garantia" checked>
                                        <label class="form-check-label font-medium text-gray-700" for="sinGarantia">
                                            ❌ Sin Garantía
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="flex flex-col sm:flex-row gap-4 mt-8">
                                <button type="button"
                                    class="btn btn-info flex-1 py-3 rounded-lg bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-semibold transition-all duration-300 transform hover:scale-105 shadow-lg"
                                    id="verQR">
                                    <i class="fas fa-qrcode mr-2"></i>Ver QR
                                </button>
                                <button type="submit"
                                    class="btn btn-success flex-1 py-3 rounded-lg bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold transition-all duration-300 transform hover:scale-105 shadow-lg">
                                    <i class="fas fa-check-circle mr-2"></i>Finalizar Venta
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>


        <!-- QR Modal -->
        <div class="modal fade" id="qrModal" tabindex="-1" role="dialog" aria-labelledby="qrModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-2xl shadow-2xl border-0">
                    <div class="modal-header bg-gradient-to-r from-indigo-600 to-purple-600 p-6 border-0">
                        <h5 class="modal-title text-white text-xl font-bold flex items-center" id="qrModalLabel">
                            <i class="fas fa-qrcode mr-3"></i>Código QR para Pago
                        </h5>
                        <button type="button"
                            class="close text-white opacity-75 hover:opacity-100 transition-opacity duration-200"
                            data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true" class="text-2xl">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body text-center p-8 bg-gradient-to-br from-gray-50 to-white">
                        <div id="qrContainer">
                            <p class="text-gray-700 mb-6 text-lg">Escanea este código para realizar el pago</p>
                            <div
                                class="w-56 h-56 bg-gradient-to-br from-gray-100 to-gray-200 mx-auto flex items-center justify-center rounded-2xl shadow-inner">
                                <i class="fas fa-qrcode fa-6x text-gray-400"></i>
                            </div>
                            <p class="text-sm text-gray-500 mt-4">Código QR generado automáticamente</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* Product Card Styling */
        .producto-card {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
        }

        .producto-card:hover {
            transform: translateY(-12px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1), 0 8px 16px rgba(0, 0, 0, 0.1);
            border-color: #3b82f6;
        }

        .producto-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 12px 12px 0 0;
            transition: transform 0.3s ease;
        }

        .producto-card:hover .producto-img {
            transform: scale(1.05);
        }

        /* Cart Item Styling */
        .carrito-item {
            border-bottom: 1px solid #e5e7eb;
            padding: 16px 0;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-radius: 8px;
            margin-bottom: 8px;
            padding: 16px;
        }

        .carrito-item:hover {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* Quantity and Price Input Buttons */
        .btn-cantidad {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 2px solid transparent;
        }

        .btn-cantidad:hover {
            transform: scale(1.15);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .cantidad-input,
        .precio-input {
            width: 80px;
            text-align: center;
            border-radius: 8px;
            transition: all 0.3s ease;
            border: 2px solid #e5e7eb;
        }

        .cantidad-input:focus,
        .precio-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Cart Button Styling */
        .cart-button {
            position: relative;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            filter: drop-shadow(0 10px 20px rgba(34, 197, 94, 0.3));
        }

        .cart-button:hover {
            transform: scale(1.1) translateY(-2px);
            filter: drop-shadow(0 15px 30px rgba(34, 197, 94, 0.4));
        }

        .cart-item-count {
            position: absolute;
            top: -10px;
            right: -10px;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            font-size: 11px;
            font-weight: bold;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);
            border: 2px solid white;
        }

        /* Modal Styling */
        .modal {
            backdrop-filter: blur(10px);
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .modal-dialog {
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        /* Form Styling */
        .form-control {
            transition: all 0.3s ease;
            border-width: 2px;
        }

        .form-control:focus {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* Button Styling */
        .btn {
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: none;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }

        /* Responsive improvements */
        @media (max-width: 768px) {
            .modal-dialog {
                margin: 0.5rem;
                max-width: calc(100% - 1rem);
            }

            .producto-card {
                margin-bottom: 1rem;
            }

            .cart-button {
                padding: 12px 16px;
                font-size: 14px;
            }
        }

        /* Loading animation */
        .loading-spinner {
            border: 4px solid #f3f4f6;
            border-top: 4px solid #3b82f6;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Variables globales
            let carrito = [];
            let sucursalId = {{ $id ?? 1 }};
            let ventaId = {{ $idventa ?? 'null' }};
            let usuario = null;
            let sucursal = null;
            let searchTimeout;

            // Cargar productos al inicio
            cargarProductos();

            // Verificar si hay una venta existente
            if (ventaId && ventaId !== 'null') {
                cargarVentaExistente(ventaId);
            }

            // Búsqueda en tiempo real
            $('#searchInput').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    cargarProductos($(this).val());
                }, 300);
            });

            // Abrir modal del carrito
            $('#openCartModal').click(function() {
                $('#cartModal').modal('show');
            });

            // Eventos del formulario
            $('#descuento').on('input', calcularTotales);
            $('#tipoPago').change(function() {
                mostrarCamposPago($(this).val());
            });

            $('#montoPagado, #montoPagadoQR').on('input', calcularCambio);
            $('#verQR').click(function() {
                $('#qrModal').modal('show');
            });

            $('#ventaForm').submit(function(e) {
                e.preventDefault();
                finalizarVenta();
            });

            // Función para cargar productos
            function cargarProductos(search = '') {
                let url = `/recojo/nueva/${sucursalId}/${ventaId}`;
                if (search) {
                    url += `?search=${encodeURIComponent(search)}`;
                }

                $.get(url)
                    .done(function(response) {
                        usuario = response.usuario;
                        sucursal = response.sucursale;

                        if (!usuario || !usuario.id) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error de Autenticación',
                                text: 'No se pudo cargar el usuario. Por favor, inicia sesión nuevamente.',
                                confirmButtonColor: '#3b82f6'
                            });
                            return;
                        }

                        console.log('Sucursal:', sucursal.nombre);
                        console.log('Vendedor:', usuario.name);
                        mostrarProductos(response.productos);
                    })
                    .fail(function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de Carga',
                            text: 'No se pudieron cargar los productos. Intenta nuevamente.',
                            confirmButtonColor: '#ef4444'
                        });
                    });
            }

            // Función para mostrar productos
            function mostrarProductos(productos) {
                let html = '';

                if (productos.data.length === 0) {
                    html = `
                <div class="col-span-full text-center py-12">
                    <div class="fade-in">
                        <i class="fas fa-box-open text-6xl text-gray-400 mb-4"></i>
                        <h3 class="text-2xl font-bold text-gray-600 mb-2">No se encontraron productos</h3>
                        <p class="text-gray-500">Intenta con otros términos de búsqueda</p>
                    </div>
                </div>
            `;
                } else {
                    productos.data.forEach(function(item) {
                        const producto = item.producto;
                        const imagen = producto.fotos.length > 0 ?
                            `/storage/${producto.fotos[0].foto}` :
                            '/img/no-image.png';

                        const stockBadge = item.cantidad > 10 ?
                            '<span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Disponible</span>' :
                            item.cantidad > 0 ?
                            '<span class="badge badge-warning"><i class="fas fa-exclamation-triangle mr-1"></i>Últimas unidades</span>' :
                            '<span class="badge badge-danger"><i class="fas fa-times-circle mr-1"></i>Agotado</span>';

                        const precioOriginal = producto.precio_original > producto.precio_descuento ?
                            `<p class="text-sm text-gray-400 line-through">Bs ${parseFloat(producto.precio_original).toFixed(2)}</p>` :
                            '';

                        html += `
            <div class="product-card card overflow-hidden fade-in group">
                <div class="relative overflow-hidden">
                    <div class="aspect-w-3 aspect-h-2 bg-gray-100">
                        <img src="${imagen}" class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-105" alt="${producto.nombre}">
                    </div>
                    <div class="absolute top-3 right-3">
                        ${stockBadge}
                    </div>
                    <div class="absolute top-3 left-3">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                            <i class="fas fa-tag mr-1"></i>Oferta
                        </span>
                    </div>
                </div>
                
                <div class="p-4">
                    <div class="mb-3">
                        <h6 class="font-semibold text-gray-800 mb-1 line-clamp-2 group-hover:text-indigo-600 transition-colors">${producto.nombre}</h6>
                        <p class="text-xs text-gray-500 line-clamp-2 mb-2">${producto.descripcion.substring(0, 70)}${producto.descripcion.length > 70 ? '...' : ''}</p>
                    </div>

                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-box text-gray-400 text-sm"></i>
                            <span class="text-sm text-gray-600">${item.cantidad} disponibles</span>
                        </div>
                        <div class="text-right">
                            <p class="text-xl font-bold text-gray-800">Bs ${parseFloat(producto.precio_descuento).toFixed(2)}</p>
                            ${precioOriginal}
                        </div>
                    </div>

                    <button class="btn-primary w-full py-2.5 rounded-lg font-medium transition-all duration-300 transform hover:scale-[1.02] agregar-carrito flex items-center justify-center space-x-2" 
                            ${item.cantidad <= 0 ? 'disabled' : ''} 
                            data-producto='${JSON.stringify(producto)}' 
                            data-cantidad-disponible="${item.cantidad}">
                        ${item.cantidad <= 0 ? 
                            '<span><i class="fas fa-ban mr-1"></i>No disponible</span>' : 
                            '<span><i class="fas fa-plus-circle mr-1"></i>Añadir al carrito</span>'
                        }
                    </button>
                </div>
            </div>
        `;
                    });
                }
                $('#productosContainer').html(html);

                $('.agregar-carrito').click(function() {
                    const producto = JSON.parse($(this).attr('data-producto'));
                    const cantidadDisponible = parseInt($(this).attr('data-cantidad-disponible'));
                    agregarAlCarrito(producto, cantidadDisponible);
                });
            }

            // Función para agregar al carrito
            function agregarAlCarrito(producto, cantidadDisponible) {
                const itemExistente = carrito.find(item => item.id === producto.id);

                if (itemExistente) {
                    if (itemExistente.cantidad < cantidadDisponible) {
                        itemExistente.cantidad++;
                        actualizarContadorCarrito();
                        mostrarCarrito();
                        calcularTotales();
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Advertencia',
                            text: 'No hay más stock disponible',
                            timer: 1500
                        });
                    }
                } else {
                    const item = {
                        id: producto.id,
                        nombre: producto.nombre,
                        precio: parseFloat(producto.precio_descuento),
                        cantidad: 1,
                        cantidadDisponible: cantidadDisponible
                    };
                    carrito.push(item);
                    actualizarContadorCarrito();
                    mostrarCarrito();
                    calcularTotales();
                }
            }

            // Función para actualizar contador de carrito
            function actualizarContadorCarrito() {
                const totalItems = carrito.reduce((sum, item) => sum + item.cantidad, 0);
                $('#cartItemCount').text(totalItems);
            }

            // Función para mostrar carrito
            function mostrarCarrito() {
                if (carrito.length === 0) {
                    $('#carritoItems').html('<p class="text-center text-gray-500 py-6">El carrito está vacío</p>');
                    return;
                }

                let html = '';
                carrito.forEach(function(item, index) {
                    html += `
                <div class="carrito-item flex justify-between items-center">
                    <div>
                        <h6 class="font-semibold text-gray-800">${item.nombre}</h6>
                        <p class="text-sm text-gray-600">Subtotal: Bs ${(item.precio * item.cantidad).toFixed(2)}</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button class="btn-cantidad bg-gray-200 hover:bg-gray-300 text-gray-700" onclick="cambiarCantidad(${index}, -1)">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" class="cantidad-input border-gray-300 rounded-lg" value="${item.cantidad}" min="1" max="${item.cantidadDisponible}" onchange="actualizarCantidad(${index}, this.value)">
                        <button class="btn-cantidad bg-gray-200 hover:bg-gray-300 text-gray-700" onclick="cambiarCantidad(${index}, 1)">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button class="btn-cantidad bg-red-500 hover:bg-red-600 text-white" onclick="eliminarDelCarrito(${index})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="mt-2">
                    <label class="text-sm text-gray-700">Precio (Bs)</label>
                    <input type="number" class="precio-input border-gray-300 rounded-lg" value="${item.precio.toFixed(2)}" min="0" step="0.01" onchange="actualizarPrecio(${index}, this.value)">
                </div>
            `;
                });

                $('#carritoItems').html(html);
            }

            // Funciones del carrito
            window.cambiarCantidad = function(index, cambio) {
                const item = carrito[index];
                const nuevaCantidad = item.cantidad + cambio;

                if (nuevaCantidad <= 0) {
                    eliminarDelCarrito(index);
                } else if (nuevaCantidad <= item.cantidadDisponible) {
                    item.cantidad = nuevaCantidad;
                    actualizarContadorCarrito();
                    mostrarCarrito();
                    calcularTotales();
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Advertencia',
                        text: 'No hay más stock disponible',
                        timer: 1500
                    });
                }
            };

            window.actualizarCantidad = function(index, nuevaCantidad) {
                const item = carrito[index];
                const cantidad = parseInt(nuevaCantidad);

                if (isNaN(cantidad) || cantidad <= 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'La cantidad debe ser mayor a 0',
                        timer: 1500
                    });
                    mostrarCarrito();
                    return;
                }

                if (cantidad <= item.cantidadDisponible) {
                    item.cantidad = cantidad;
                    actualizarContadorCarrito();
                    mostrarCarrito();
                    calcularTotales();
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Advertencia',
                        text: 'No hay suficiente stock disponible',
                        timer: 1500
                    });
                    mostrarCarrito();
                }
            };

            window.actualizarPrecio = function(index, nuevoPrecio) {
                const item = carrito[index];
                const precio = parseFloat(nuevoPrecio);

                if (isNaN(precio) || precio < 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'El precio debe ser un valor válido',
                        timer: 1500
                    });
                    mostrarCarrito();
                    return;
                }

                item.precio = precio;
                mostrarCarrito();
                calcularTotales();
            };

            window.eliminarDelCarrito = function(index) {
                carrito.splice(index, 1);
                actualizarContadorCarrito();
                mostrarCarrito();
                calcularTotales();
            };

            // Calcular totales
            function calcularTotales() {
                let totalSinDescuento = 0;
                carrito.forEach(function(item) {
                    totalSinDescuento += item.precio * item.cantidad;
                });

                const descuento = parseFloat($('#descuento').val()) || 0;
                const montoTotal = totalSinDescuento - descuento;

                $('#totalSinDescuento').val(`Bs ${totalSinDescuento.toFixed(2)}`);
                $('#montoTotal').val(`Bs ${montoTotal.toFixed(2)}`);

                calcularCambio();
            }

            // Mostrar campos de pago
            function mostrarCamposPago(tipoPago) {
                $('#pagoEfectivo, #pagoQR, #cambioDiv, #qrCode').addClass('hidden');

                if (tipoPago === 'Efectivo') {
                    $('#pagoEfectivo, #cambioDiv').removeClass('hidden');
                } else if (tipoPago === 'QR') {
                    $('#pagoQR, #qrCode').removeClass('hidden');
                } else if (tipoPago === 'Efectivo y QR') {
                    $('#pagoEfectivo, #pagoQR, #cambioDiv, #qrCode').removeClass('hidden');
                }
            }

            // Calcular cambio
            function calcularCambio() {
                const montoTotal = parseFloat($('#montoTotal').val().replace('Bs ', '')) || 0;
                const montoPagado = parseFloat($('#montoPagado').val()) || 0;
                const montoPagadoQR = parseFloat($('#montoPagadoQR').val()) || 0;
                const cambio = (montoPagado + montoPagadoQR) - montoTotal;

                $('#cambio').val(cambio >= 0 ? `Bs ${cambio.toFixed(2)}` : 'Bs 0.00');
            }

            // Finalizar venta
            function finalizarVenta() {
                if (carrito.length === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'El carrito está vacío',
                        timer: 1500
                    });
                    return;
                }

                const cliente = $('#cliente').val().trim();
                const tipoPago = $('#tipoPago').val();
                const qrCode = $('#qrCodeInput').val().trim();

                if (!cliente || !tipoPago) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Complete todos los campos requeridos',
                        timer: 1500
                    });
                    return;
                }

                if ((tipoPago === 'QR' || tipoPago === 'Efectivo y QR') && !qrCode) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'El código QR es requerido para pagos QR',
                        timer: 1500
                    });
                    return;
                }

                const montoTotal = parseFloat($('#montoTotal').val().replace('Bs ', '')) || 0;
                const montoPagado = parseFloat($('#montoPagado').val()) || 0;
                const montoPagadoQR = parseFloat($('#montoPagadoQR').val()) || 0;

                if (tipoPago === 'Efectivo' && montoPagado < montoTotal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'El monto pagado en efectivo es menor al total',
                        timer: 1500
                    });
                    return;
                }
                if (tipoPago === 'QR' && montoPagadoQR < montoTotal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'El monto pagado por QR es menor al total',
                        timer: 1500
                    });
                    return;
                }
                if (tipoPago === 'Efectivo y QR' && (montoPagado + montoPagadoQR) < montoTotal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'La suma de los montos pagados es menor al total',
                        timer: 1500
                    });
                    return;
                }

                if (!usuario || !usuario.id) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Usuario no autenticado. Por favor, inicia sesión nuevamente.',
                        timer: 2000
                    });
                    return;
                }

                const totalSinDescuento = parseFloat($('#totalSinDescuento').val().replace('Bs ', '')) || 0;
                const descuento = parseFloat($('#descuento').val()) || 0;
                const montoTotalFinal = totalSinDescuento - descuento;

                const user = $('#userid').val().trim();
                const datos = {
                    nombre_cliente: cliente,
                    ci: $('#ci').val().trim(),
                    costo_total: montoTotalFinal,
                    descuento: descuento,
                    tipo_pago: tipoPago,
                    garantia: $('input[name="garantia"]:checked').val(),
                    id_sucursal: sucursalId,
                    id_user: user,
                    pagado: montoPagado,
                    pagado_qr: montoPagadoQR,
                    qr_code: qrCode,
                    productos: JSON.stringify(carrito.map(item => ({
                        id: item.id,
                        nombre: item.nombre,
                        cantidad: item.cantidad,
                        precio: item.precio
                    })))
                };

                $.ajax({
                    url: `/fin/finmodernoActualizar/${ventaId}`,
                    method: 'POST',
                    data: datos,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: 'Venta finalizada correctamente',
                                timer: 1500
                            }).then(function() {
                                generarPDF(datos);
                                carrito = [];
                                actualizarContadorCarrito();
                                mostrarCarrito();
                                $('#ventaForm')[0].reset();
                                $('#descuento').val(0);
                                calcularTotales();
                                Alpine.store('cartModal').toggle();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Error al finalizar la venta',
                                timer: 1500
                            });
                        }
                    },
                    error: function(xhr) {
                        let mensaje = 'Error al procesar la venta';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            mensaje = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: mensaje,
                            timer: 1500
                        });
                    }
                });
            }

            // Generar PDF
            function generarPDF(datosVenta) {
                const montoTotal = datosVenta.costo_total;
                const montoPagado = datosVenta.pagado + (datosVenta.pagado_qr || 0);
                const cambio = montoPagado - montoTotal;

                const productosParaPDF = carrito.map(item => ({
                    id: item.id,
                    nombre: item.nombre,
                    cantidad: item.cantidad,
                    precio: item.precio,
                    total: (item.cantidad * item.precio).toFixed(2)
                }));

                const parametros = new URLSearchParams({
                    nombre_cliente: datosVenta.nombre_cliente,
                    ci: datosVenta.ci || '',
                    costo_total: datosVenta.costo_total,
                    descuento: datosVenta.descuento,
                    tipo_pago: datosVenta.tipo_pago,
                    pagado: datosVenta.pagado,
                    pagado_qr: datosVenta.pagado_qr || 0,
                    qr_code: datosVenta.qr_code || '',
                    cambio: cambio.toFixed(2),
                    garantia: datosVenta.garantia,
                    id_user: datosVenta.id_user,
                    pagadoqr: datosVenta.pagado_qr,
                    productos: JSON.stringify(productosParaPDF)
                });

                const urlPDF = `/nota/productosnuevos/pdf/?${parametros.toString()}`;
                window.open(urlPDF, '_blank', 'width=800,height=600');
            }

            // Cargar venta existente
            function cargarVentaExistente(idVenta) {
                $.get(`/ventas/detalles/${idVenta}`)
                    .done(function(response) {
                        const venta = response.venta;
                        const nombreUsuario = response
                            .nombre_usuario; // Usamos nombre_usuario, que es la propiedad correcta

                        $('#cliente').val(venta.nombre_cliente);
                        $('#ci').val(venta.ci || '');
                        $('#descuento').val(venta.descuento || 0);
                        $('#tipoPago').val(venta.tipo_pago);
                        $('#user').val(nombreUsuario); // Asignamos nombre_usuario al campo 'user'

                        $('#userid').val(venta.id_user); // Asignamos nombre_usuario al campo 'user'

                        if (venta.garantia === 'con garantia') {
                            $('#conGarantia').prop('checked', true);
                        } else {
                            $('#sinGarantia').prop('checked', true);
                        }

                        mostrarCamposPago(venta.tipo_pago);

                        if (venta.efectivo) {
                            $('#montoPagado').val(venta.efectivo);
                        }
                        if (venta.qr) {
                            $('#montoPagadoQR').val(venta.qr);
                        }
                        if (venta.qr_code) {
                            $('#qrCodeInput').val(venta.qr_code);
                        }

                        carrito = [];
                        venta.venta_productos.forEach(function(ventaProducto) {
                            const producto = ventaProducto.producto;
                            carrito.push({
                                id: producto.id,
                                nombre: producto.nombre,
                                precio: parseFloat(ventaProducto.precio_unitario),
                                cantidad: ventaProducto.cantidad,
                                cantidadDisponible: ventaProducto.cantidad + 100
                            });
                        });

                        actualizarContadorCarrito();
                        mostrarCarrito();
                        calcularTotales();

                        Swal.fire({
                            icon: 'info',
                            title: 'Venta Cargada',
                            text: 'Se han cargado los datos de la venta existente',
                            timer: 2000
                        });
                    })
                    .fail(function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al cargar la venta existente',
                            timer: 1500
                        });
                    });
            }
        });
    </script>
@stop
