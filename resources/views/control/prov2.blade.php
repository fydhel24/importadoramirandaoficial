@extends('adminlte::page')

@section('title', 'ventas')

@section('content_header')
    <style>
        /* Variables CSS para colores modernos */
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --danger-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --dark-gradient: linear-gradient(135deg, #4b6cb7 0%, #182848 100%);
            --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --border-radius: 15px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Estilos del header */
        .modern-header {
            background: var(--primary-gradient);
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--card-shadow);
            color: white;
        }

        .modern-header h1 {
            font-weight: 700;
            font-size: 2.5rem;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        #clock {
            font-size: 2.5rem;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            background: rgba(255, 255, 255, 0.2);
            padding: 1rem 2rem;
            border-radius: 50px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        /* Barra de búsqueda moderna */
        .modern-search {
            position: relative;
            margin: 2rem 0;
        }

        .modern-search input {
            width: 100%;
            padding: 1.5rem 1.5rem 1.5rem 4rem;
            border: none;
            border-radius: 50px;
            background: white;
            box-shadow: var(--card-shadow);
            font-size: 1.1rem;
            transition: var(--transition);
        }

        .modern-search input:focus {
            outline: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .modern-search i {
            position: absolute;
            left: 1.5rem;
            top: 50%;
            transform: translateY(-50%);
            color: #667eea;
            font-size: 1.2rem;
        }

        /* Botón del carrito moderno */
        .modern-cart-btn {
            background: var(--success-gradient);
            border: none;
            padding: 1rem 2rem;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            margin-bottom: 2rem;
        }

        .modern-cart-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            color: white;
            text-decoration: none;
        }

        .cart-counter {
            background: rgba(255, 255, 255, 0.3);
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-weight: 700;
            min-width: 30px;
            text-align: center;
        }

        /* Cards de productos modernos */
        .product-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            margin-bottom: 2rem;
            overflow: hidden;
            position: relative;
            animation: fadeInUp 0.6s ease-out;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .product-header {
            height: 200px;
            background-size: cover;
            background-position: center;
            position: relative;
            display: flex;
            align-items: flex-end;
            padding: 1.5rem;
        }

        .product-title {
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            font-weight: 600;
            font-size: 1.1rem;
            backdrop-filter: blur(10px);
        }

        .product-avatar {
            position: absolute;
            bottom: -30px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: var(--card-shadow);
        }

        .product-body {
            padding: 3rem 1.5rem 1.5rem;
            text-align: center;
        }

        .product-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .stat-item {
            text-align: center;
            padding: 1rem;
            background: #f8f9ff;
            border-radius: 12px;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #667eea;
            display: block;
        }

        .stat-label {
            font-size: 0.8rem;
            color: #6c757d;
            text-transform: uppercase;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        .modern-btn {
            background: var(--success-gradient);
            border: none;
            padding: 1rem 2rem;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            width: 100%;
            transition: var(--transition);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .modern-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            color: white;
        }

        .modern-btn.out-of-stock {
            background: var(--danger-gradient);
            cursor: not-allowed;
            pointer-events: none;
        }

        .out-of-stock-card {
            opacity: 0.7;
            position: relative;
        }

        .out-of-stock-card::before {
            content: "AGOTADO";
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: #ff4757;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.8rem;
            z-index: 10;
        }

        /* Modal moderno */
        .modal-content {
            border-radius: var(--border-radius);
            border: none;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            background: var(--primary-gradient);
            color: white;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            padding: 1.5rem 2rem;
        }

        .modal-title {
            font-weight: 700;
            font-size: 1.5rem;
        }

        .modal-body {
            padding: 2rem;
            overflow-y: auto;
            max-height: 80vh;
        }

        /* Tabla moderna */
        .modern-table {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--card-shadow);
        }

        .modern-table thead {
            background: var(--primary-gradient);
            color: white;
        }

        .modern-table th {
            padding: 1.5rem 1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
        }

        .modern-table td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #eee;
        }

        .modern-table tbody tr:hover {
            background-color: #f8f9ff;
        }

        /* Formularios modernos */
        .modern-form-group {
            margin-bottom: 2rem;
        }

        .modern-form-group label {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.75rem;
            display: block;
            font-size: 0.95rem;
        }

        .modern-input {
            width: 100%;
            padding: 1rem 1.5rem;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 1rem;
            transition: var(--transition);
            background: white;
        }

        .modern-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        /* Radio buttons modernos */
        .modern-radio-group {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .modern-radio {
            position: relative;
            flex: 1;
        }

        .modern-radio input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .modern-radio label {
            display: block;
            padding: 1rem 1.5rem;
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
            font-weight: 600;
        }

        .modern-radio input[type="radio"]:checked+label {
            background: var(--primary-gradient);
            color: white;
            border-color: transparent;
        }

        /* Paginación moderna */
        .modern-pagination {
            display: flex;
            justify-content: center;
            margin: 3rem 0;
        }

        .modern-pagination ul {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0;
            gap: 0.5rem;
        }

        .modern-pagination .page-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: white;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
        }

        .modern-pagination .page-link:hover {
            background: var(--primary-gradient);
            color: white;
            transform: translateY(-2px);
        }

        .modern-pagination .page-item.active .page-link {
            background: var(--primary-gradient);
            color: white;
        }

        /* Botones del modal */
        .modal-footer .modern-btn {
            border-radius: 12px;
            padding: 0.75rem 2rem;
            width: auto;
        }

        .btn-secondary-modern {
            background: #6c757d;
            border: none;
        }

        .btn-danger-modern {
            background: var(--danger-gradient);
            border: none;
        }

        .btn-success-modern {
            background: var(--success-gradient);
            border: none;
        }

        /* QR Modal */
        .qr-container {
            text-align: center;
            padding: 2rem;
        }

        .qr-container img {
            max-width: 300px;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
        }

        /* Responsivo */
        @media (max-width: 768px) {
            .modern-header {
                text-align: center;
            }

            .modern-header h1 {
                font-size: 2rem;
                margin-bottom: 1rem;
            }

            #clock {
                font-size: 1.8rem;
                padding: 0.75rem 1.5rem;
            }

            .product-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .modern-radio-group {
                flex-direction: column;
            }

            .modal-dialog {
                margin: 1rem;
            }

            .modern-table th,
            .modern-table td {
                padding: 0.75rem;
                font-size: 0.85rem;
            }
        }



        /* Loading states */
        .loading {
            position: relative;
            pointer-events: none;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
    </style>
    <div class="modern-header d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1>Venta de productos de {{ $sucur->nombre }}</h1>
        </div>
        <div id="clock"></div>
    </div>
@stop
<style>
    .modal-body {
        overflow-y: auto;
        max-height: 80vh;
    }

    .modal-dialog {
        max-width: 90%;
        margin: 1.75rem auto;
    }
</style>

@section('content')

    <div id="lista-cursos" class="container">
        <div class="d-flex flex-wrap justify-content-end">
            <!-- Carrito -->
            <a class="modern-cart-btn" href="#" id="mostrar-carrito" data-toggle="modal" data-target="#carritoModal">
                <i class="fas fa-shopping-cart"></i> Carrito Vendedor <span class="cart-counter"
                    id="carrito-contador">0</span>
            </a>
        </div>


        <!-- Buscador -->
        <div class="modern-search">
            <i class="fas fa-search"></i>
            <input type="text" id="search" class="form-control" placeholder="Buscar producto..." />
        </div>
        <br>
        <br>
        <!-- Buscador -->

        <div class="row" id="product-list">
            <!-- Aquí se cargarán los productos dinámicamente con AJAX -->
        </div>

        <!-- Paginación -->
        <div class="modern-pagination">
            <ul id="pagination" class="pagination"></ul>
        </div>
        <div id="pagination-links" class="pagination-gutter">
            <!-- Los links de paginación se cargarán aquí dinámicamente -->
        </div>



        {{-- 
        modal carrito --}}
        <form id="venta-form" method="POST" action="{{ route('control.finmoderno') }}" target="_blank">
            @csrf
            {{-- linea de codigo agregado --}}
            <input type="hidden" id="sucursal_id" value="{{ auth()->user()->sucursal_id }}">


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
                                <table id="lista-carrito" class="modern-table">
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
                                <div class="col-md-4">
                                    <div class="modern-form-group">
                                        <label for="cliente">Cliente</label>
                                        <input type="text" name="nombre_cliente" id="cliente" class="modern-input"
                                            placeholder="Ingrese nombre del cliente" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="modern-form-group">
                                        <label for="ci">CI / NIT</label>
                                        <input type="text" name="ci" id="ci" class="modern-input"
                                            placeholder="Ingrese CI del cliente">
                                    </div>
                                </div>
                                <div class="col-md-4" style="display:none;">
                                    <div class="modern-form-group">
                                        <label for="vendedorSearch">Vendedor</label>
                                        <input type="text" id="vendedorSearch" class="modern-input" required
                                            placeholder="Escribe para buscar vendedor..." list="sugerencias_vendedores">
                                        <datalist id="sugerencias_vendedores">
                                            @foreach ($users as $user)
                                                <option value="{{ $user->name }}" data-id="{{ $user->id }}">
                                            @endforeach
                                        </datalist>

                                        <!-- Campo oculto para almacenar el ID del vendedor -->
                                        <input type="hidden" name="id_user" id="id_user">
                                    </div>
                                </div>

                                <script>
                                    // Espera que el documento esté completamente cargado
                                    document.addEventListener('DOMContentLoaded', function() {
                                        // Selecciona el primer vendedor
                                        let firstOption = document.querySelector('#sugerencias_vendedores option:first-child');
                                        if (firstOption) {
                                            // Asigna el valor del nombre del primer vendedor al input
                                            document.getElementById('vendedorSearch').value = firstOption.value;
                                            // Asigna el ID del primer vendedor al campo oculto
                                            document.getElementById('id_user').value = firstOption.getAttribute('data-id');
                                        }
                                    });
                                </script>


                            </div>
                            <div class="row" style="display:none;">
                                <div class="col-md-4">
                                    <div class="modern-form-group">
                                        <label for="descuento">Descuento (Bs)</label>
                                        <input type="number" name="descuento" id="descuento" class="modern-input"
                                            placeholder="Ingrese descuento" value="0">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="modern-form-group">
                                        <label for="monto-total">Total Sin Descuento</label>
                                        <input type="text" id="monto-total" name="monto_total_sin_descuento"
                                            class="modern-input" value="0">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="modern-form-group">
                                        <label for="total-a-pagar">Monto Total a Pagar</label>
                                        <input type="text" name="costo_total" id="total-a-pagar" class="modern-input"
                                            value="0">
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="display:none;">
                                <div class="col-md-12">
                                    <div class="modern-form-group">
                                        <label for="tipo_pago">Método de Pago</label>
                                        <div class="modern-radio-group">
                                            <div class="modern-radio">
                                                <input class="form-check-input" type="radio" name="tipo_pago"
                                                    value="Efectivo" id="efectivo" checked>
                                                <label for="efectivo">Efectivo</label>
                                            </div>
                                            <div class="modern-radio">
                                                <input class="form-check-input" type="radio" name="tipo_pago"
                                                    value="QR" id="transferencia_bancaria">
                                                <label for="transferencia_bancaria">Transferencia QR</label>
                                            </div>
                                            <div class="modern-radio">
                                                <input class="form-check-input" type="radio" name="tipo_pago"
                                                    value="Efectivo y QR" id="pago_efectivo_qr">
                                                <label for="pago_efectivo_qr">Pago Efectivo y QR</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <!-- Campo Monto Pagado -->
                            <div class="row" id="monto-pagado-container"style="display:none;">
                                <div class="col-md-6">
                                    <div class="modern-form-group">
                                        <label for="pagado" id="monto-pagado-label">Monto Pagado</label>
                                        <input type="number" id="pagado" class="form-control"
                                            placeholder="Ingrese monto pagado" value="0">
                                    </div>
                                </div>
                                <div class="col-md-6" id="pagos-efectivo-qr" style="display:none;">
                                    <div class="modern-form-group">
                                        <label for="pagado_qr">Monto Pagado por QR</label>
                                        <input type="number" id="pagado_qr" class="form-control"
                                            placeholder="Ingrese monto pagado por QR">
                                    </div>
                                </div>
                            </div>
                            <div class="row"style="display:none;">
                                <div class="col-md-6">
                                    <div class="modern-form-group">
                                        <label for="cambio">Cambio</label>
                                        <input type="text" id="cambio" class="form-control" readonly>
                                    </div>
                                </div>
                                <div class="modern-form-group">
                                    <button type="button" class="btn btn-info" id="ver-qr" data-toggle="modal"
                                        data-target="#qrModal" data-dismiss="modal">
                                        Ver QR
                                    </button>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="modern-form-group">
                                        <label for="compra_producto"><strong>Compra del Producto</strong></label>
                                        <div class="modern-radio-group">
                                            <div class="modern-radio">
                                                <input class="form-check-input" type="radio" name="garantia"
                                                    value="con garantia" id="con_garantia">
                                                <label for="con_garantia">Con Garantía</label>
                                            </div>
                                            <div class="modern-radio">
                                                <input class="form-check-input" type="radio" name="garantia"
                                                    value="sin garantia" id="sin_garantia" checked>
                                                <label for="sin_garantia">Sin Garantía</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="modern-btn btn-secondary-modern"
                                data-dismiss="modal">Cerrar</button>
                            <a href="#" id="vaciar-carrito-fvc" class="modern-btn btn-danger-modern">Vaciar</a>
                            <button type="submit" class="modern-btn btn-success-modern">Confirmar venta</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>


        <!-- Agregar el script para mostrar los campos -->
        <script>
            // Mostrar campos correctamente según el tipo de pago seleccionado
            document.querySelectorAll('input[name="tipo_pago"]').forEach(function(radio) {
                radio.addEventListener('change', function() {
                    var montoPagadoLabel = document.getElementById('monto-pagado-label');
                    var pagosEfctivoQr = document.getElementById('pagos-efectivo-qr');

                    // Restablecer la visibilidad de los campos antes de cambiar el comportamiento
                    pagosEfctivoQr.style.display = 'none';
                    montoPagadoLabel.textContent = 'Monto Pagado';

                    // Mostrar los campos adicionales según el tipo de pago seleccionado
                    if (this.value === "Efectivo") {
                        montoPagadoLabel.textContent = 'Monto Pagado Efectivo';
                    } else if (this.value === "QR") {
                        montoPagadoLabel.textContent = 'Monto Pagado por QR';
                    } else if (this.value === "Efectivo y QR") {
                        montoPagadoLabel.textContent = 'Monto Pagado Efectivo';
                        pagosEfctivoQr.style.display = 'block';
                    }
                });
            });
        </script>
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
        <!-- Modal cantidad -->
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
                        <div class="modern-form-group">
                            <input type="number" id="cantidad-input" class="modern-input" placeholder="Cantidad"
                                min="1" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="modern-btn btn-secondary-modern"
                            data-dismiss="modal">Cancelar</button>
                        <button type="button" id="confirmar-cantidad" class="modern-btn btn-primary">Agregar al
                            carrito</button>
                    </div>
                </div>
            </div>
        </div>
        <ul id="pagination" class="pagination justify-content-center"></ul>

    </div> <!-- .container -->


    <script>
        // Detectar la selección del vendedor
        const vendedorInput = document.getElementById('vendedorSearch');
        const idUserInput = document.getElementById('id_user'); // El campo oculto para el ID del vendedor
        let selectedVendedor = null;

        vendedorInput.addEventListener('input', function() {
            const nombreSeleccionado = vendedorInput.value;
            const optionSeleccionada = Array.from(document.getElementById('sugerencias_vendedores').options).find(
                option => option.value === nombreSeleccionado);

            if (optionSeleccionada) {
                // Guardar el vendedor seleccionado
                selectedVendedor = {
                    id: optionSeleccionada.dataset.id,
                    nombre: nombreSeleccionado
                };

                // Asignar el ID del vendedor al campo oculto
                idUserInput.value = selectedVendedor.id;
            } else {
                selectedVendedor = null;
                idUserInput.value = ''; // Limpiar el campo oculto si no hay selección
            }
        });
    </script>


    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.all.min.js"></script>
    <script>
        $(document).ready(function() {
            // Función para cargar productos con AJAX y paginación
            function fetchProductos(page = 1, search = '') {
                $.ajax({
                    url: '{{ route('control.productos', ['id' => $id]) }}',
                    method: 'GET',
                    data: {
                        page: page,
                        search: search,
                    },
                    success: function(response) {
                        // Cargar productos en el contenedor #product-list
                        var html = '';
                        response.productos.data.forEach(function(producto) {
                            html += `
                    <div class="col-md-4 ${producto.producto.stock_sucursal <= 0 ? 'out-of-stock-card' : ''}">
                        <div class="product-card">

                            <!-- Modern header con fondo o color -->
                            <div class="product-header" style="${producto.producto.fotos && producto.producto.fotos.length > 0 ? 
                                `background-image: url('{{ asset('storage/') }}/${producto.producto.fotos[0].foto}'); background-size: cover; background-position: center;` : 
                                'background-color: #ccc;'}">
                                <div class="product-title">${producto.producto.nombre}</div>
                            </div>

                            

                            
                            <!-- Estadísticas del producto (moderno) -->
                            <div class="product-body">
                                <div class="product-stats">
                                    <div class="stat-item">
                                        <span class="stat-value">${producto.producto.precio}</span>
                                        <span class="stat-label">PRECIO</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-value">${producto.producto.categoria ? producto.producto.categoria.categoria : 'N/A'}</span>
                                        <span class="stat-label">CATEGORÍA</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-value">${producto.producto.marca ? producto.producto.marca.marca : 'N/A'}</span>
                                        <span class="stat-label">MARCA</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-value">${producto.producto.stock_actual}</span>
                                        <span class="stat-label">ALMACÉN</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-value">${producto.producto.stock_sucursal}</span>
                                        <span class="stat-label">SUCURSAL</span>
                                    </div>
                                </div>

                                

                                <!-- Botón acción moderno -->
                                <a href="#"
                                   class="btn btn-block agregar-carrito modern-btn ${producto.producto.stock_sucursal <= 0 ? 'out-of-stock' : 'btn-success'}"
                                   data-id="${producto.producto.id}"
                                   data-nombre="${producto.producto.nombre}"
                                   data-precio="${producto.producto.precio}"
                                   data-stock-sucursal="${producto.producto.stock_sucursal}"
                                   data-toggle="modal"
                                   data-target="#cantidadModal">
                                    ${producto.producto.stock_sucursal <= 0 ? 'PRODUCTO NO DISPONIBLE, AGREGUE CANTIDAD DEL PRODUCTO' : 'Vender'}
                                </a>
                            </div>
                        </div>
                    </div>
                `;
                        });

                        // Actualizar listado
                        $('#product-list').html(html);

                        // Actualizar paginación
                        var paginationLinks = '';
                        for (var i = 1; i <= response.productos.last_page; i++) {
                            paginationLinks += `
                    <li class="page-item ${i === response.productos.current_page ? 'active' : ''}">
                        <a href="#" class="page-link" data-page="${i}">${i}</a>
                    </li>
                `;
                        }
                        $('#pagination').html(paginationLinks);
                    }
                });
            }


            // Cargar los productos al cargar la página
            fetchProductos();

            // Búsqueda de productos
            $('#search').on('keyup', function() {
                var search = $(this).val();
                fetchProductos(1, search);
            });

            // Paginación con AJAX
            $(document).on('click', '.page-link', function(e) {
                e.preventDefault();
                var page = $(this).data('page');
                var search = $('#search').val();
                fetchProductos(page, search);
            });

            // Bind eventos de clic a los botones agregar-carrito
            $(document).on('click', '.agregar-carrito', function(e) {
                e.preventDefault();
                productoSeleccionado = {
                    id: $(this).data('id'),
                    nombre: $(this).data('nombre'),
                    precio: parseFloat($(this).data('precio')),
                    stockSucursal: parseInt($(this).data('stock-sucursal'))
                };

                // Verificar si el producto ya está en el carrito
                const productoExistente = carrito.find(item => item.id === productoSeleccionado.id);
                if (productoExistente) {
                    Swal.fire({
                        title: 'Alerta',
                        text: 'Ya tienes este producto en el carrito. Dirigiéndote al carrito...',
                        icon: 'info',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        mostrarCarrito();
                    });
                    return;
                }

                // Limpiar el input de cantidad antes de abrir el modal
                document.getElementById('cantidad-input').value = '';

                // Abrir el modal de cantidad
                $('#cantidadModal').modal('show');
            });

        });
    </script>


    <script>
        let carrito = [];
        let carritoContador = document.getElementById('carrito-contador');
        let listaCarrito = document.querySelector('#lista-carrito tbody');
        let productoSeleccionado = null;

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
            const totalSinDescuento = parseFloat(document.getElementById('total-a-pagar').value) || 0;

            // Restar el descuento solo si se ingresa un valor
            const totalAPagar = totalSinDescuento + descuento;
            document.getElementById('monto-total').value = totalAPagar.toFixed(2);

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

            const totalAPagar = totalSinDescuento + descuento;
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


        // Al iniciar, carga el carrito desde localStorage basado en el ID de la sucursal
        document.addEventListener('DOMContentLoaded', function() {
            const sucursalId = {{ $id }}; // Asegúrate de que este valor sea el ID de la sucursal actual
            const storedCarrito = localStorage.getItem(`carrito-${sucursalId}`);
            if (storedCarrito) {
                carrito = JSON.parse(storedCarrito);
                actualizarCarritoContador();
                mostrarCarrito();
            }
        });
        // Función para mostrar el carrito
        function mostrarCarrito() {
            listaCarrito.innerHTML = '';

            if (carrito.length === 0) {
                listaCarrito.innerHTML = '<tr><td colspan="6" class="text-center">El carrito está vacío</td></tr>';
                document.getElementById('monto-total').value = '0.00';
                document.getElementById('total-a-pagar').value = '0.00'; // Resetear total a pagar
                return;
            }

            let totalAPagar = 0;

            carrito.forEach((item, index) => {
                const total = item.precio * item.cantidad;
                totalAPagar += total;

                // Agregar fila editable para precio, cantidad y total
                listaCarrito.innerHTML += `
                <tr>
                    <td>${item.id}</td>
                    <td>${item.nombre}</td>
                    <td><input type="number" class="form-control precio-input" value="${item.precio.toFixed(2)}" data-index="${index}" step="0.01"></td>
                    <td><input type="number" class="form-control cantidad-input" value="${item.cantidad}" max="${item.stockSucursal}" data-index="${index}" step="1"></td>
                    <td><input type="number" class="form-control total-input" value="${total.toFixed(2)}" data-index="${index}" step="0.01" ></td>
                    <td><button class="btn btn-danger btn-sm eliminar" data-id="${item.id}">Eliminar</button></td>
                </tr>
                `;
            });

            // Actualizar el total a pagar
            document.getElementById('monto-total').value = totalAPagar.toFixed(2);
            document.getElementById('cambio').value = ''; // Limpiar cambio
            document.getElementById('total-a-pagar').value = totalAPagar.toFixed(2); // Total a pagar sin descuento

            // Agregar eventos para actualizar datos al modificar precio, cantidad o total
            // Agregar eventos para actualizar datos al modificar precio, cantidad o total

            // Actualizar los totales generales cuando el campo pierda el foco (evento blur)
            document.querySelectorAll('.precio-input, .cantidad-input, .total-input').forEach(input => {
                input.addEventListener('blur', function() {
                    const index = this.getAttribute('data-index');
                    let nuevoPrecio, nuevaCantidad, nuevoTotal;

                    // Validar y limpiar el valor de entrada
                    const valor = this.value.trim();

                    if (this.classList.contains('precio-input')) {
                        nuevoPrecio = parseFloat(this.value); // Cambiar a parseFloat
                        carrito[index].precio = nuevoPrecio;
                        nuevaCantidad = carrito[index].cantidad;
                        nuevoTotal = nuevoPrecio * nuevaCantidad;
                    } else if (this.classList.contains('cantidad-input')) {
                        nuevaCantidad = parseFloat(this
                            .value); // Cambiar a parseFloat para permitir decimales en la cantidad
                        if (nuevaCantidad > 0) {
                            carrito[index].cantidad = nuevaCantidad;
                            nuevoPrecio = carrito[index].precio;
                            nuevoTotal = nuevoPrecio * nuevaCantidad;
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: 'La cantidad debe ser mayor que cero.',
                                icon: 'error',
                                confirmButtonText: 'Aceptar'
                            });
                            this.value = carrito[index].cantidad;
                            return;
                        }
                    } else if (this.classList.contains('total-input')) {
                        nuevoTotal = parseFloat(this
                            .value); // Cambiar a parseFloat para aceptar decimales en total
                        nuevaCantidad = carrito[index].cantidad;

                        if (nuevaCantidad > 0) {
                            nuevoPrecio = nuevoTotal / nuevaCantidad;
                            carrito[index].precio = nuevoPrecio;
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: 'La cantidad debe ser mayor que cero para calcular el precio.',
                                icon: 'error',
                                confirmButtonText: 'Aceptar'
                            });
                            this.value = (carrito[index].precio * nuevaCantidad).toFixed(2);
                            return;
                        }
                    }

                    // Actualizar los campos relacionados
                    const fila = listaCarrito.querySelectorAll('tr')[index];
                    fila.querySelector('.precio-input').value = carrito[index].precio.toFixed(2);
                    fila.querySelector('.cantidad-input').value = carrito[index].cantidad.toFixed(2);
                    fila.querySelector('.total-input').value = nuevoTotal.toFixed(2);

                    // Actualizar los totales generales
                    actualizarTotales();
                });

                // Evitar que se elimine el producto cuando se presiona Enter en los campos editables
                input.addEventListener('keydown', function(event) {
                    if (event.key === 'Enter') {
                        event
                            .preventDefault(); // Evita la acción por defecto de la tecla Enter (que podría hacer que el producto se elimine)
                    }
                });

            });

            $('#carritoModal').modal('show');

            // Almacenar el carrito en localStorage con el ID de la sucursal
            const sucursalId = {{ $id }};
            localStorage.setItem(`carrito-${sucursalId}`, JSON.stringify(carrito));
        }


        // Función para actualizar los totales generales
        function actualizarTotales() {
            let totalAPagar = 0;
            carrito.forEach(item => {
                totalAPagar += item.precio * item.cantidad;
            });

            document.getElementById('monto-total').value = totalAPagar.toFixed(2);
            document.getElementById('total-a-pagar').value = totalAPagar.toFixed(2); // Total a pagar sin descuento

            // Calcular cambio si hay un monto pagado
            calcularCambio();

            // Almacenar el carrito en localStorage con el ID de la sucursal
            const sucursalId = {{ $id }};
            localStorage.setItem(`carrito-${sucursalId}`, JSON.stringify(carrito));
        }


        document.getElementById('vaciar-carrito-fvc').addEventListener('click', function(e) {
            e.preventDefault();
            carrito = [];
            actualizarCarritoContador();
            mostrarCarrito();
            // Limpiar el carrito en localStorage

            const sucursalId = {{ $id }};
            localStorage.removeItem(`carrito-${sucursalId}`);
        });

        /* listaCarrito.addEventListener('click', function(e) {
            if (e.target.classList.contains('eliminar')) {
                const id = e.target.getAttribute('data-id');
                carrito = carrito.filter(item => item.id !== id);
                actualizarCarritoContador();
                mostrarCarrito();
                // Actualizar el carrito en localStorage
                const sucursalId = {{ $id }};
                localStorage.setItem(`carrito-${sucursalId}`, JSON.stringify(carrito));
            }
        }); */
        listaCarrito.addEventListener('click', function(e) {
            if (e.target.classList.contains('eliminar')) {
                const id = parseInt(e.target.getAttribute('data-id')); // Convertir a número
                carrito = carrito.filter(item => item.id !== id); // Filtrar productos que no coincidan con el id

                actualizarCarritoContador();
                mostrarCarrito();

                // Actualizar el carrito en localStorage
                const sucursalId =
                    {{ $id }}; // Este es un valor dinámico, asegúrate de que esté definido en el backend
                localStorage.setItem(`carrito-${sucursalId}`, JSON.stringify(carrito));
            }
        });

        document.getElementById('venta-form').addEventListener('submit', function(event) {
            event.preventDefault(); // Evitar el envío predeterminado del formulario

            // Obtener el id de sucursal (por ejemplo, desde un valor en el backend o en un campo oculto)
            const sucursalId =
                {{ $id }}; // Asumimos que $id es el ID de la sucursal disponible desde el backend

            // Verificar si la caja está abierta para esa sucursal
            fetch(`/verificar-caja-abierta/${sucursalId}`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        Swal.fire({
                            title: 'Error',
                            text: data.error,
                            icon: 'error',
                            confirmButtonText: 'Aceptar'
                        });
                        return; // No enviar el formulario si la caja no está abierta
                    }

                    // La caja está abierta, continuar con la lógica de venta
                    const user = document.getElementById('id_user').value;
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

                    // Recoger productos del carrito con precios y cantidades editados
                    const productos = [];
                    const rows = document.querySelectorAll('#lista-carrito tbody tr');
                    rows.forEach(row => {
                        const id = row.querySelector('td:nth-child(1)').innerText; // ID del producto
                        const nombre = row.querySelector('td:nth-child(2)')
                            .innerText; // Nombre del producto
                        const precio = parseFloat(row.querySelector('td:nth-child(3) input')
                            .value); // Precio editable
                        const cantidad = parseInt(row.querySelector('td:nth-child(4) input')
                            .value); // Cantidad editable
                        const total = parseFloat(row.querySelector('td:nth-child(5) input')
                            .value); // Total editable

                        productos.push({
                            id: id,
                            nombre: nombre,
                            precio: precio,
                            cantidad: cantidad,
                            total: total
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

                    // Agregar monto pagado (QR)
                    const pagadoqrInput = document.getElementById('pagado_qr');
                    const inputPagadoqr = document.createElement('input');
                    inputPagadoqr.type = 'hidden';
                    inputPagadoqr.name = 'pagado_qr';
                    inputPagadoqr.value = pagadoqrInput.value || '0'; // Usa 0 si no hay valor
                    this.appendChild(inputPagadoqr);

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
                    inputSucursal.value = sucursalId; // Aquí ya tomas el id de sucursal
                    this.appendChild(inputSucursal);

                    // Enviar el formulario usando fetch
                    fetch(this.action, {
                            method: 'POST',
                            body: new FormData(this),
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content')
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Obtener el valor del radio seleccionado
                                let garantia = document.querySelector('input[name="garantia"]:checked');
                                let tipoGarantia = garantia ? garantia.value :
                                    'sin_garantia'; // Si no hay selección, por defecto 'sin_garantia'

                                // Redirigir para descargar el PDF
                                const url = '{{ route('nota.pdf') }}?nombre_cliente=' + encodeURIComponent(
                                        clienteNombre) +
                                    '&costo_total=' + encodeURIComponent(costoTotal.toFixed(2)) +
                                    '&ci=' + encodeURIComponent(inputCI.value) +
                                    '&id_user=' + encodeURIComponent(user) +
                                    '&productos=' + encodeURIComponent(JSON.stringify(productos)) +
                                    '&descuento=' + encodeURIComponent(descuentoInput.value || '0') +
                                    '&pagado=' + encodeURIComponent(pagadoInput.value || '0') +
                                    '&pagadoqr=' + encodeURIComponent(pagadoqrInput.value || '0') +
                                    '&cambio=' + encodeURIComponent(cambioInput.value || '0') +
                                    '&tipo_pago=' + encodeURIComponent(tipoPago) +
                                    '&garantia=' + encodeURIComponent(tipoGarantia) + // Agregar garantía
                                    '&id_sucursal=' + encodeURIComponent(
                                        sucursalId); // Aquí agregamos el id_sucursal

                                // Abrir la URL en una nueva pestaña
                                //window.open(url, '_blank');
                                // Limpiar el carrito y los campos del formulario
                                Swal.fire({
                                    title: 'Venta Realizada',
                                    text: 'La venta se ha realizado con éxito.',
                                    icon: 'success',
                                    confirmButtonText: 'Aceptar'
                                });

                                carrito = [];
                                localStorage.removeItem(`carrito-${sucursalId}`);
                                document.getElementById('monto-total').value = '0.00';
                                document.getElementById('total-a-pagar').value = '0.00';
                                document.getElementById('cambio').value = '0.00';
                                document.getElementById('cliente').value = '';
                                document.getElementById('ci').value = '';
                                document.getElementById('descuento').value = '0';
                                document.getElementById('pagado').value = '';
                                document.getElementById('pagado_qr').value = '';
                                listaCarrito.innerHTML =
                                    '<tr><td colspan="6" class="text-center">El carrito está vacío</td></tr>';
                                carritoContador.innerText = '0';

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

                            //window.location.reload();
                        });
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Hubo un problema Con la Conexion a Internet, Verifica tu conexion',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                });
        });
    </script>
    <script>
        // Function to filter products
        /* function filterProducts() {
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
        } */

        // Add event listener to the search input
        //document.getElementById('search-input').addEventListener('input', filterProducts);
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Function to update the clock
            function updateClock() {
                const now = new Date();
                const hours = now.getHours().toString().padStart(2, '0');
                const minutes = now.getMinutes().toString().padStart(2, '0');
                const seconds = now.getSeconds().toString().padStart(2, '0');

                document.getElementById('clock').innerText = `${hours}:${minutes}:${seconds}`;
            }

            // Update the clock every second
            updateClock();
            setInterval(updateClock, 1000);
        });
    </script>
@stop
