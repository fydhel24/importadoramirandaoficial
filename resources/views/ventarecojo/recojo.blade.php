@extends('adminlte::page')

@section('title', 'Lista de Recojos')

@section('content_header')
<h1 class="text-center">Lista de Recojos</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Indicadores de estado -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="info-box bg-info">
                <span class="info-box-icon"><i class="fas fa-truck"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Recojos Activos</span>
                    <span class="info-box-number" id="total-recojos">0</span>
                    <span class="info-box-more">
                        <span class="badge badge-light" id="status-indicator">
                            <i class="fas fa-circle text-success"></i> Conectado
                        </span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Columna para la lista de recojos --}}
        <div class="col-md-3">
            <div class="card shadow-sm rounded">
                <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Recojos Recientes</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool text-white" id="refresh-btn" title="Actualizar">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <button type="button" class="btn btn-tool text-white" id="filter-btn" title="Filtrar">
                            <i class="fas fa-filter"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <!-- Filtros -->
                    <div class="p-3 border-bottom bg-light" id="filters-section" style="display: none;">
                        <div class="row mb-2">
                            <div class="col-12">
                                <input type="text" class="form-control form-control-sm" id="search-global" placeholder="Buscar recojo por ID o cliente...">
                            </div>
                        </div>
                        <div class="row">

                            <div class="col-md-6">
                                <select class="form-control form-control-sm" id="filter-estado">
                                    <option value="">Todos los estados</option>
                                    <option value="RECOJO">Pendiente</option>
                                    <option value="Completado">Completado</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de recojos -->
                    <div class="list-container" style="max-height: 600px; overflow-y: auto;">
                        <ul class="list-group list-group-flush" id="recogos-list">
                            <!-- Lista será poblada dinámicamente por JavaScript -->
                        </ul>
                        <div id="no-recojos" class="text-center py-4 text-muted" style="display: none;">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>No hay recojos que mostrar</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Columna para el panel de detalles --}}
        <div class="col-md-9">
            <div class="card shadow-sm rounded sticky-top" style="top: 20px;">
                <div class="card-header bg-gradient-info text-white d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0" id="detail-panel-title">Selecciona un Recojo</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool text-white" id="print-btn" title="Imprimir" style="display: none;">
                            <i class="fas fa-print"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body" id="detail-panel-body">
                    <div class="text-center py-5" id="no-selection-message">
                        <i class="fas fa-mouse-pointer fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Haz clic en un recojo de la lista para ver y editar sus detalles aquí.</p>
                    </div>

                    {{-- Formulario de detalles --}}
                    <div id="venta-details-form" style="display: none;">
                        <!-- Información general -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="alert alert-info" id="venta-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Estado:</strong> <span id="venta-estado-display"></span> |
                                    <strong>Fecha:</strong> <span id="venta-fecha-display"></span>
                                </div>
                            </div>
                        </div>

                        <form id="edit-venta-form" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="venta_id" id="form-venta-id">

                            <!-- Información del cliente -->
                            <div class="card card-outline card-primary mb-3">
                                <div class="card-header">
                                    <h5 class="card-title"><i class="fas fa-user"></i> Información del Cliente</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="form-group col-md-8">
                                            <label for="nombre_cliente">Nombre del Cliente <span class="text-danger">*</span></label>
                                            <input type="text" name="nombre_cliente" class="form-control" id="form-nombre_cliente" required>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="ci">CI/NIT</label>
                                            <input type="text" name="ci" class="form-control" id="form-ci">
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Información de la venta -->
                            <div class="card card-outline card-success mb-3">
                                <div class="card-header">
                                    <h5 class="card-title"><i class="fas fa-shopping-cart"></i> Información de la Venta</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label for="costo_total">Costo Total</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">Bs.</span>
                                                </div>
                                                <input type="number" step="0.01" name="costo_total" class="form-control bg-light" id="form-costo_total" readonly>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="sucursal">Sucursal</label>
                                            <input type="text" name="sucursal" class="form-control bg-light" id="form-sucursal" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Información de pago -->
                            <div class="card card-outline card-warning mb-3">
                                <div class="card-header">
                                    <h5 class="card-title"><i class="fas fa-money-bill-wave"></i> Información de Pago</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-3">
                                        <label>Tipo de Pago <span class="text-danger">*</span></label>
                                        <div class="btn-group btn-group-toggle d-flex" data-toggle="buttons">
                                            <label class="btn btn-outline-success flex-fill">
                                                <input type="radio" name="tipo_pago" class="tipo-pago-radio" value="Efectivo" id="form-tipo_pago_efectivo">
                                                <i class="fas fa-money-bill"></i> Efectivo
                                            </label>
                                            <label class="btn btn-outline-primary flex-fill">
                                                <input type="radio" name="tipo_pago" class="tipo-pago-radio" value="QR" id="form-tipo_pago_qr">
                                                <i class="fas fa-qrcode"></i> QR
                                            </label>
                                            <label class="btn btn-outline-info flex-fill">
                                                <input type="radio" name="tipo_pago" class="tipo-pago-radio" value="Efectivo y QR" id="form-tipo_pago_ambos">
                                                <i class="fas fa-coins"></i> Mixto
                                            </label>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-3">
                                            <label for="efectivo">Efectivo</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">Bs.</span>
                                                </div>
                                                <input type="number" step="0.01" name="efectivo" class="form-control" id="form-efectivo" min="0">
                                            </div>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="qr">QR</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">Bs.</span>
                                                </div>
                                                <input type="number" step="0.01" name="qr" class="form-control" id="form-qr" min="0">
                                            </div>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="pagado">Total Pagado</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">Bs.</span>
                                                </div>
                                                <input type="number" step="0.01" name="pagado" class="form-control bg-light" id="form-pagado" readonly>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="cambio">Cambio</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">Bs.</span>
                                                </div>
                                                <input type="number" step="0.01" class="form-control bg-light" id="form-cambio" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Productos -->
                            <div class="card card-outline card-secondary mb-3">
                                <div class="card-header">
                                    <h5 class="card-title"><i class="fas fa-list"></i> Productos en esta venta</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-sm mb-0" id="productos-table">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Producto</th>
                                                    <th>Cant.</th>
                                                    <th>Precio Unit.</th>
                                                    <th>Descuento</th>
                                                    <th>Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody id="productos-venta-list">
                                                <!-- Los productos se cargarán aquí -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones de acción -->
                            <div class="row">
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-secondary btn-block" id="cancel-btn">
                                        <i class="fas fa-times"></i> Cancelar
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-success btn-block" id="save-btn">
                                        <i class="fas fa-save"></i> Actualizar y Finalizar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading overlay -->
<div id="loading-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999;">
    <div class="d-flex justify-content-center align-items-center h-100">
        <div class="text-center text-white">
            <div class="spinner-border" role="status">
                <span class="sr-only">Cargando...</span>
            </div>
            <p class="mt-2">Procesando...</p>
        </div>
    </div>
</div>
@stop

@push('css')
<style>
    .recojo-item {
        border-left: 4px solid transparent;
        transition: all 0.3s ease;
    }

    .recojo-item:hover {
        border-left-color: #007bff;
        background-color: #f8f9fa;
        transform: translateX(2px);
    }

    .recojo-item.active {
        background-color: #e7f3ff;
        border-left-color: #007bff;
        font-weight: bold;
    }

    .recojo-item.new-item {
        animation: highlightNew 2s ease-in-out;
    }

    @keyframes highlightNew {
        0% {
            background-color: #d4edda;
        }

        100% {
            background-color: transparent;
        }
    }

    .card-header.bg-gradient-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        border-bottom: 2px solid #0056b3;
    }

    .card-header.bg-gradient-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        border-bottom: 2px solid #138496;
    }

    .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
    }

    .btn-group-toggle .btn {
        transition: all 0.3s ease;
    }

    .btn-group-toggle .btn.active {
        transform: scale(1.05);
    }

    .sticky-top {
        position: sticky;
        top: 20px;
        z-index: 1020;
    }

    .list-container {
        scrollbar-width: thin;
        scrollbar-color: #007bff #f1f1f1;
    }

    .list-container::-webkit-scrollbar {
        width: 6px;
    }

    .list-container::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .list-container::-webkit-scrollbar-thumb {
        background: #007bff;
        border-radius: 3px;
    }

    .invalid-feedback {
        display: block;
    }

    .card-outline {
        border-top: 3px solid;
    }

    .card-outline.card-primary {
        border-top-color: #007bff;
    }

    .card-outline.card-success {
        border-top-color: #28a745;
    }

    .card-outline.card-warning {
        border-top-color: #ffc107;
    }

    .card-outline.card-secondary {
        border-top-color: #6c757d;
    }

    .info-box {
        box-shadow: 0 0 1px rgba(0, 0, 0, .125), 0 1px 3px rgba(0, 0, 0, .2);
    }

    #status-indicator {
        font-size: 0.875rem;
    }

    .text-success {
        color: #28a745 !important;
    }

    .text-danger {
        color: #dc3545 !important;
    }
</style>
@endpush

@push('js')
<script>

$(document).ready(function() {
    // Variables globales
    const $detailPanelTitle = $('#detail-panel-title');
    const $noSelectionMessage = $('#no-selection-message');
    const $ventaDetailsForm = $('#venta-details-form');
    const $editVentaForm = $('#edit-venta-form');
    const $recogosList = $('#recogos-list');
    const $loadingOverlay = $('#loading-overlay');
    const $statusIndicator = $('#status-indicator');
    const $totalRecojos = $('#total-recojos');
    const $printBtn = $('#print-btn');
    const $refreshBtn = $('#refresh-btn');
    const $filterBtn = $('#filter-btn');
    const $filtersSection = $('#filters-section');
    const $searchGlobal = $('#search-global');
    const $filterEstado = $('#filter-estado');
    const $noRecojos = $('#no-recojos');

    let currentVentaId = null;
    let displayedVentaIds = new Set();
    let isPollingActive = true;
    let pollingInterval = null;
    let isFormDirty = false;
    let debounceTimeout = null;
    let ventasData = []; // Array para almacenar todos los datos de las ventas

    // Cargar venta seleccionada desde localStorage
    function loadSelectedVenta() {
        const savedVentaId = localStorage.getItem('selectedVentaId');
        const savedVentaDetails = localStorage.getItem('selectedVentaDetails');
        const savedFormState = localStorage.getItem('formState');
        if (savedVentaId && savedVentaDetails) {
            try {
                const venta = JSON.parse(savedVentaDetails);
                currentVentaId = parseInt(savedVentaId);
                renderVentaDetails(venta);
                if (savedFormState) {
                    const formState = JSON.parse(savedFormState);
                    restoreFormState(formState);
                }
                return venta;
            } catch (e) {
                console.error('Error parsing saved venta details:', e);
                clearStorage();
            }
        }
        return null;
    }

    // Guardar venta seleccionada en localStorage
    function saveSelectedVenta(venta) {
        if (venta) {
            localStorage.setItem('selectedVentaId', venta.id);
            localStorage.setItem('selectedVentaDetails', JSON.stringify(venta));
        } else {
            clearStorage();
        }
    }

    // Guardar estado del formulario
    function saveFormState() {
        const formState = {
            nombre_cliente: $('#form-nombre_cliente').val(),
            ci: $('#form-ci').val(),
            tipo_pago: $('input[name="tipo_pago"]:checked').val(),
            efectivo: $('#form-efectivo').val(),
            qr: $('#form-qr').val(),
            pagado: $('#form-pagado').val(),
            cambio: $('#form-cambio').val()
        };
        localStorage.setItem('formState', JSON.stringify(formState));
        isFormDirty = true;
    }

    // Restaurar estado del formulario
    function restoreFormState(formState) {
        $('#form-nombre_cliente').val(formState.nombre_cliente);
        $('#form-ci').val(formState.ci);
        if (formState.tipo_pago) {
            $('input[name="tipo_pago"]').parent().removeClass('active');
            const tipoPagoRadio = $(`input[name="tipo_pago"][value="${formState.tipo_pago}"]`);
            tipoPagoRadio.prop('checked', true).parent().addClass('active');
        }
        $('#form-efectivo').val(formState.efectivo);
        $('#form-qr').val(formState.qr);
        $('#form-pagado').val(formState.pagado);
        $('#form-cambio').val(formState.cambio);
        togglePagoFields();
    }

    // Limpiar localStorage
    function clearStorage() {
        localStorage.removeItem('selectedVentaId');
        localStorage.removeItem('selectedVentaDetails');
        localStorage.removeItem('formState');
        isFormDirty = false;
    }

    // Función para mostrar/ocultar loading
    function showLoading(show = true) {
        if (show) {
            $loadingOverlay.fadeIn(200);
        } else {
            $loadingOverlay.fadeOut(200);
        }
    }

    // Función para actualizar el estado de conexión
    function updateConnectionStatus(connected) {
        if (connected) {
            $statusIndicator.html('<i class="fas fa-circle text-success"></i> Conectado');
        } else {
            $statusIndicator.html('<i class="fas fa-circle text-danger"></i> Desconectado');
        }
    }

    // Función para validar formulario
    function validateForm() {
        let isValid = true;
        const $requiredFields = $editVentaForm.find('[required]');

        $requiredFields.each(function() {
            const $field = $(this);
            $field.removeClass('is-invalid');

            if (!$field.val().trim()) {
                $field.addClass('is-invalid');
                $field.siblings('.invalid-feedback').text('Este campo es obligatorio');
                isValid = false;
            }
        });

        // Validar tipo de pago
        if (!$('input[name="tipo_pago"]:checked').length) {
            Swal.fire({
                icon: 'warning',
                title: 'Tipo de pago requerido',
                text: 'Por favor selecciona un tipo de pago',
                timer: 3000
            });
            isValid = false;
        }

        // Validar montos de pago
        const costoTotal = parseFloat($('#form-costo_total').val()) || 0;
        const totalPagado = parseFloat($('#form-pagado').val()) || 0;

        if (totalPagado < costoTotal) {
            Swal.fire({
                icon: 'warning',
                title: 'Pago insuficiente',
                text: `El monto pagado (Bs. ${totalPagado.toFixed(2)}) es menor al costo total (Bs. ${costoTotal.toFixed(2)})`,
                timer: 4000
            });
            isValid = false;
        }

        return isValid;
    }

    // Función para renderizar detalles de la venta
    function renderVentaDetails(venta, restoreState = false) {
        $detailPanelTitle.text(`Recojo #${venta.id} - ${venta.nombre_cliente || 'N/A'}`);
        $noSelectionMessage.hide();
        $ventaDetailsForm.show();
        $printBtn.show();

        // Información general
        $('#venta-estado-display').text(venta.estado || 'N/A');
        $('#venta-fecha-display').text(new Date(venta.created_at).toLocaleString('es-BO'));

        // Llenar formulario
        $('#form-venta-id').val(venta.id);
        $editVentaForm.attr('action', `/ventas/${venta.id}`);
        $('#form-costo_total').val(parseFloat(venta.costo_total || 0).toFixed(2));
        $('#form-sucursal').val(venta.sucursal ? venta.sucursal.nombre : 'N/A');

        // Solo actualizar campos editables si no se está restaurando el estado
        if (!restoreState) {
            $('#form-nombre_cliente').val(venta.nombre_cliente || '');
            $('#form-ci').val(venta.ci || '');
            $('input[name="tipo_pago"]').parent().removeClass('active');
            const tipoPagoRadio = $(`input[name="tipo_pago"][value="${venta.tipo_pago}"]`);
            tipoPagoRadio.prop('checked', true).parent().addClass('active');
            $('#form-efectivo').val(parseFloat(venta.efectivo || 0).toFixed(2));
            $('#form-qr').val(parseFloat(venta.qr || 0).toFixed(2));
            calcularTotales();
        }

        // Productos
        renderProductos(venta.venta_productos);
        togglePagoFields();
    }

    // Función para renderizar productos
    function renderProductos(ventaProductos) {
        const $productosVentaList = $('#productos-venta-list');
        $productosVentaList.empty();

        if (ventaProductos && ventaProductos.length > 0) {
            ventaProductos.forEach(vp => {
                const producto = vp.producto || {
                    nombre: 'Producto eliminado'
                };
                const subtotal = (vp.cantidad * vp.precio_unitario) - vp.descuento;

                $productosVentaList.append(`
                    <tr>
                        <td>
                            <strong>${producto.nombre}</strong>
                            ${producto.codigo ? `<br><small class="text-muted">Código: ${producto.codigo}</small>` : ''}
                        </td>
                        <td>${vp.cantidad}</td>
                        <td>Bs. ${parseFloat(vp.precio_unitario).toFixed(2)}</td>
                        <td>Bs. ${parseFloat(vp.descuento).toFixed(2)}</td>
                        <td><strong>Bs. ${subtotal.toFixed(2)}</strong></td>
                    </tr>
                `);
            });
        } else {
            $productosVentaList.append(`
                <tr>
                    <td colspan="5" class="text-center text-muted py-3">
                        <i class="fas fa-inbox"></i> No hay productos asociados a esta venta
                    </td>
                </tr>
            `);
        }
    }

    // Función para manejar campos de pago
    function togglePagoFields() {
        const tipoPago = $('input[name="tipo_pago"]:checked').val();
        const $efectivoInput = $('#form-efectivo');
        const $qrInput = $('#form-qr');

        $efectivoInput.prop('disabled', false).removeClass('bg-light');
        $qrInput.prop('disabled', false).removeClass('bg-light');

        if (tipoPago === 'Efectivo') {
            $qrInput.prop('disabled', true).addClass('bg-light').val('0.00');
        } else if (tipoPago === 'QR') {
            $efectivoInput.prop('disabled', true).addClass('bg-light').val('0.00');
        }

        calcularTotales();
    }

    // Función para calcular totales en tiempo real
    function calcularTotales() {
        const tipoPago = $('input[name="tipo_pago"]:checked').val();
        const costoTotal = parseFloat($('#form-costo_total').val()) || 0;
        let efectivo = parseFloat($('#form-efectivo').val()) || 0;
        let qr = parseFloat($('#form-qr').val()) || 0;

        if (tipoPago === 'Efectivo') {
            qr = 0;
            $('#form-qr').val('0.00');
        } else if (tipoPago === 'QR') {
            efectivo = 0;
            $('#form-efectivo').val('0.00');
        }

        const totalPagado = efectivo + qr;
        const cambio = Math.max(0, totalPagado - costoTotal);

        $('#form-pagado').val(totalPagado.toFixed(2));
        $('#form-cambio').val(cambio.toFixed(2));

        if (totalPagado >= costoTotal) {
            $('#form-pagado').removeClass('text-danger').addClass('text-success');
            $('#form-cambio').removeClass('text-danger').addClass('text-success');
        } else {
            $('#form-pagado').removeClass('text-success').addClass('text-danger');
            $('#form-cambio').removeClass('text-success').addClass('text-danger');
        }
    }

    // Función para filtrar recojos - CORREGIDA
    function filterRecojos() {
        const searchGlobalTerm = $searchGlobal.val().toLowerCase().trim();
        const estadoFilter = $filterEstado.val();
        let visibleCount = 0;

        console.log('Filtros aplicados:', { searchGlobalTerm, estadoFilter });

        // Filtrar datos originales
        const filteredVentas = ventasData.filter(venta => {
            const cliente = String(venta.nombre_cliente || '').toLowerCase();
            const ventaId = String(venta.id || '');
            const ci = String(venta.ci || '').toLowerCase();
            const estado = String(venta.estado || '');

            const matchesGlobal = !searchGlobalTerm ||
                ventaId.includes(searchGlobalTerm) ||
                cliente.includes(searchGlobalTerm) ||
                ci.includes(searchGlobalTerm);
            
            const matchesEstado = !estadoFilter || estado === estadoFilter;

            return matchesGlobal && matchesEstado;
        });

        // Limpiar y regenerar lista
        $recogosList.empty();
        
        if (filteredVentas.length === 0) {
            $noRecojos.show();
            $totalRecojos.text('0');
        } else {
            $noRecojos.hide();
            
            filteredVentas.forEach(venta => {
                const $listItem = createRecojoListItem(venta, false);
                $recogosList.append($listItem);
                
                // Mantener selección activa si está entre los filtrados
                if (currentVentaId && venta.id === currentVentaId) {
                    $listItem.addClass('active');
                }
            });
            
            visibleCount = filteredVentas.length;
            $totalRecojos.text(visibleCount);
        }

        console.log('Elementos visibles después del filtro:', visibleCount);
    }

    // Función para actualizar recojos - CORREGIDA
    function fetchNewRecojos() {
        if (!isPollingActive) return;

        //showLoading(true);
        $.ajax({
            url: '/ventas/detalles',
            type: 'GET',
            dataType: 'json',
            timeout: 10000,
            success: function(response) {
                updateConnectionStatus(true);

                if (!response.ventas) {
                    console.error('Respuesta inválida del servidor');
                    showLoading(false);
                    return;
                }

                console.log('Datos recibidos del servidor:', response.ventas);

                // Actualizar datos globales
                const oldVentasData = [...ventasData];
                ventasData = response.ventas;
                const newVentaIds = new Set(ventasData.map(venta => venta.id));

                // Detectar nuevas ventas
                const oldVentaIds = new Set(oldVentasData.map(venta => venta.id));
                const newVentas = ventasData.filter(venta => !oldVentaIds.has(venta.id));

                // Mostrar notificación para nuevas ventas
                newVentas.forEach(venta => {
                    showNewVentaNotification(venta);
                });

                // Actualizar IDs mostrados
                displayedVentaIds = newVentaIds;

                // Verificar si la venta actual aún existe
                if (currentVentaId && !newVentaIds.has(currentVentaId)) {
                    clearSelection();
                } else if (currentVentaId && !isFormDirty) {
                    // Actualizar datos de la venta actual
                    const updatedVenta = ventasData.find(v => v.id === currentVentaId);
                    if (updatedVenta) {
                        saveSelectedVenta(updatedVenta);
                        renderVentaDetails(updatedVenta, true);
                    }
                }

                // Aplicar filtros
                filterRecojos();
                showLoading(false);
            },
            error: function(xhr, status, error) {
                showLoading(false);
                updateConnectionStatus(false);

                if (status === 'timeout') {
                    showErrorNotification('Tiempo de espera agotado. Reintentando...');
                } else if (xhr.status === 0) {
                    showErrorNotification('Sin conexión a internet');
                } else {
                    showErrorNotification('Error de servidor');
                }
            }
        });
    }

    // Función para crear elemento de lista - CORREGIDA
    function createRecojoListItem(venta, isNew = false) {
        const fecha = new Date(venta.created_at);
        const fechaFormateada = fecha.toLocaleDateString('es-BO') + ' ' + fecha.toLocaleTimeString('es-BO', {
            hour: '2-digit',
            minute: '2-digit'
        });

        const $listItem = $(`
            <li class="list-group-item list-group-item-action d-flex justify-content-between align-items-center recojo-item ${isNew ? 'new-item' : ''}"
                data-venta-id="${venta.id}"
                style="cursor: pointer;">
                <div class="d-flex align-items-center">
                    <div class="mr-2">
                        <i class="fas fa-box text-primary"></i>
                        ${isNew ? '<i class="fas fa-star text-warning ml-1" title="Nuevo"></i>' : ''}
                    </div>
                    <div>
                        <strong>#${venta.id}</strong> - ${venta.nombre_cliente || 'N/A'}
                        <br>
                        <small class="text-muted">
                            <i class="fas fa-id-card"></i> CI: ${venta.ci || 'N/A'}
                            <br>
                            <i class="fas fa-clock"></i> ${fechaFormateada}
                        </small>
                    </div>
                </div>
                <div class="text-right">
                    <span class="badge badge-${venta.estado === 'Completado' ? 'success' : 'warning'}">
                        ${venta.estado || 'N/A'}
                    </span>
                    <br>
                    <small class="text-muted">Bs. ${parseFloat(venta.costo_total || 0).toFixed(2)}</small>
                </div>
            </li>
        `);

        // Almacenar datos completos de la venta en el elemento
        $listItem.data('venta-details', venta);

        return $listItem;
    }

    // Función para mostrar notificación de nueva venta
    function showNewVentaNotification(venta) {
        Swal.fire({
            icon: 'info',
            title: '¡Nuevo Recojo!',
            text: `Se ha registrado un nuevo recojo: #${venta.id} - ${venta.nombre_cliente || 'N/A'}`,
            timer: 3000,
            timerProgressBar: true,
            toast: true,
            position: 'top-end',
            showConfirmButton: false
        });

        if (typeof Audio !== 'undefined') {
            try {
                const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmMcBjiR1/LNeSsFJHfH8N2QQAoUXrTp66hVFApGn+DyvmMcBjiR1/LNeSsFJHfH8N2QQAoUXrTp66hVFApGn+DyvmMcBjiR1/LNeSsFJHfH8N2QQAoUXrTp66hVFApGn+DyvmMcBjiR1/LNeSsFJHfH8N2QQAoUXrTp66hVFApGn+DyvmMcBjiR1/LNeSsFJHfH8N2QQAoUXrTp66hVFApGn+DyvmMcBjiR1/LNeSsFJHfH8N2QQAoUXrTp66hVFApGn+DyvmMcBjiR1/LNeSsFJHfH8N2QQAoUXrTp66hVFApGn+DyvmMcBjiR1/LNeSsFJHfH8N2QQAoUXrTp66hVFApGn+DyvmMcBjiR1/LNeSsFJHfH8N2QQAoUXrTp66hVFApGn+DyvmMcBjiR1/LNeSsFJHfH8N2QQAoUXrTp66hVFA==');
                audio.play().catch(() => {});
            } catch (e) {
                console.log('No se pudo reproducir el sonido de notificación');
            }
        }
    }

    // Función para mostrar notificación de error
    function showErrorNotification(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: message,
            timer: 3000,
            timerProgressBar: true,
            toast: true,
            position: 'top-end',
            showConfirmButton: false
        });
    }

    // Función para limpiar selección
    function clearSelection() {
        currentVentaId = null;
        $recogosList.find('li').removeClass('active');
        $detailPanelTitle.text('Selecciona un Recojo');
        $noSelectionMessage.show();
        $ventaDetailsForm.hide();
        $printBtn.hide();
        clearStorage();
    }

    // Event Listeners

    // Clic en elemento de lista - CORREGIDO
    $recogosList.on('click', 'li', function() {
        if (isFormDirty) {
            Swal.fire({
                title: '¿Descartar cambios?',
                text: 'Tienes cambios no guardados en el formulario. ¿Deseas descartarlos?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, descartar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    selectVenta($(this));
                }
            });
        } else {
            selectVenta($(this));
        }
    });

    function selectVenta($item) {
        $recogosList.find('li').removeClass('active');
        $item.addClass('active');

        const ventaDetails = $item.data('venta-details');
        if (ventaDetails) {
            currentVentaId = ventaDetails.id;
            saveSelectedVenta(ventaDetails);
            renderVentaDetails(ventaDetails);
            isFormDirty = false;
            localStorage.removeItem('formState');
        }
    }

    // Cambio en tipo de pago
    $editVentaForm.on('change', '.tipo-pago-radio', function() {
        togglePagoFields();
        saveFormState();
    });

    // Cambio en campos de pago
    $editVentaForm.on('input', '#form-nombre_cliente, #form-ci, #form-efectivo, #form-qr', function() {
        calcularTotales();
        saveFormState();
    });

    // Envío del formulario
    $editVentaForm.on('submit', function(e) {
        e.preventDefault();

        if (!validateForm()) {
            return;
        }

        const form = $(this);
        const ventaId = $('#form-venta-id').val();
        const url = form.attr('action');

        Swal.fire({
            title: '¿Confirmar actualización?',
            text: 'Se actualizará la información del recojo y se marcará como completado.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, actualizar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoading();

                $.ajax({
                    url: url,
                    type: 'PUT',
                    data: form.serialize(),
                    success: function(response) {
                        showLoading(false);

                        Swal.fire({
                            icon: 'success',
                            title: '¡Actualizado!',
                            text: response.message || 'Recojo actualizado correctamente',
                            timer: 2000,
                            timerProgressBar: true
                        });

                        if (response.venta) {
                            // Actualizar datos en ventasData
                            const ventaIndex = ventasData.findIndex(v => v.id === response.venta.id);
                            if (ventaIndex !== -1) {
                                ventasData[ventaIndex] = response.venta;
                            }
                            
                            saveSelectedVenta(response.venta);
                            renderVentaDetails(response.venta);
                        }

                        if (response.pdfUrl) {
                            setTimeout(() => {
                                window.open(response.pdfUrl, '_blank');
                            }, 1000);
                        }

                        isFormDirty = false;
                        localStorage.removeItem('formState');

                        setTimeout(() => {
                            fetchNewRecojos();
                        }, 3000);
                    },
                    error: function(xhr) {
                        showLoading(false);

                        let errorMessage = 'Hubo un error al actualizar la venta.';

                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = xhr.responseJSON.errors;
                            errorMessage = 'Errores de validación:<br>';
                            for (const field in errors) {
                                errorMessage += `• ${errors[field][0]}<br>`;
                            }
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: errorMessage,
                            timer: 5000
                        });
                    }
                });
            }
        });
    });

    // Botón cancelar
    $('#cancel-btn').on('click', function() {
        if (isFormDirty) {
            Swal.fire({
                title: '¿Descartar cambios?',
                text: 'Tienes cambios no guardados en el formulario. ¿Deseas descartarlos?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, descartar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    clearSelection();
                }
            });
        } else {
            clearSelection();
        }
    });

    // Botón imprimir
    $printBtn.on('click', function() {
        if (currentVentaId) {
            window.open(`/ventas/${currentVentaId}/pdf`, '_blank');
        }
    });

    // Botón actualizar
    $refreshBtn.on('click', function() {
        $(this).addClass('fa-spin');
        fetchNewRecojos();
        setTimeout(() => {
            $(this).removeClass('fa-spin');
        }, 1000);
    });

    // Botón filtros
    $filterBtn.on('click', function() {
        $filtersSection.slideToggle();
    });

    // Filtros en tiempo real con debounce - CORREGIDO
    $searchGlobal.on('input', function() {
        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(() => {
            console.log('Ejecutando filtro por búsqueda:', $(this).val());
            filterRecojos();
        }, 300);
    });

    $filterEstado.on('change', function() {
        console.log('Ejecutando filtro por estado:', $(this).val());
        filterRecojos();
    });

    // Control de polling
    $(window).on('focus', function() {
        isPollingActive = true;
        if (!pollingInterval) {
            pollingInterval = setInterval(fetchNewRecojos, 5000);
        }
    });

    $(window).on('blur', function() {
        isPollingActive = false;
        if (pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null;
        }
    });

    // Iniciar polling y carga inicial
    loadSelectedVenta();
    fetchNewRecojos();
    pollingInterval = setInterval(fetchNewRecojos, 5000);

    // Cleanup al cerrar página
    $(window).on('beforeunload', function() {
        isPollingActive = false;
        if (pollingInterval) {
            clearInterval(pollingInterval);
        }
        if (isFormDirty) {
            return 'Tienes cambios no guardados. ¿Seguro que quieres salir?';
        }
    });

    // Inicialización
    console.log('Sistema de recojos inicializado correctamente');
});

</script>
@endpush