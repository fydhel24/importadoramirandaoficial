@extends('adminlte::page')

@section('title', 'Lista de Recojos')

@section('content_header')
<h1 class="text-center">Lista de Recojos</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Indicadores de estado -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="info-box bg-gradient-info shadow-sm">
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

    <!-- Lista de recojos en pantalla completa -->
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
            <div class="list-container" style="height: calc(100vh - 250px); overflow-y: auto;">
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
        background: #ffffff;
        margin-bottom: 2px;
    }

    .recojo-item:hover {
        border-left-color: #007bff;
        background-color: #f8f9fa;
        transform: translateX(4px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .recojo-item.active {
        background-color: #e7f3ff;
        border-left-color: #007bff;
        font-weight: 500;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }

    .recojo-item.new-item {
        animation: highlightNew 2s ease-in-out;
    }

    @keyframes highlightNew {
        0% { background-color: #d4edda; }
        100% { background-color: #ffffff; }
    }

    .card-header.bg-gradient-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        border-bottom: 2px solid #0056b3;
        border-radius: 8px 8px 0 0;
    }

    .info-box.bg-gradient-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        border-radius: 8px;
        transition: transform 0.2s ease;
    }

    .info-box:hover {
        transform: translateY(-2px);
    }

    .form-control {
        border-radius: 6px;
        transition: all 0.2s ease;
    }

    .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
    }

    .btn-tool {
        transition: all 0.2s ease;
    }

    .btn-tool:hover {
        transform: scale(1.1);
        background: rgba(255,255,255,0.1);
    }

    .list-container {
        scrollbar-width: thin;
        scrollbar-color: #007bff #f1f1f1;
        width: 100%;
    }

    .list-container::-webkit-scrollbar {
        width: 8px;
    }

    .list-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .list-container::-webkit-scrollbar-thumb {
        background: #007bff;
        border-radius: 4px;
    }

    .card {
        border: none;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .badge {
        border-radius: 12px;
        padding: 6px 12px;
        font-weight: 500;
    }

    .text-success { color: #28a745 !important; }
    .text-danger { color: #dc3545 !important; }
</style>
@endpush

@push('js')
<script>
$(document).ready(function() {
    // Variables globales
    const $recogosList = $('#recogos-list');
    const $loadingOverlay = $('#loading-overlay');
    const $statusIndicator = $('#status-indicator');
    const $totalRecojos = $('#total-recojos');
    const $refreshBtn = $('#refresh-btn');
    const $filterBtn = $('#filter-btn');
    const $filtersSection = $('#filters-section');
    const $searchGlobal = $('#search-global');
    const $filterEstado = $('#filter-estado');
    const $noRecojos = $('#no-recojos');

    let ventasData = [];
    let isPollingActive = true;
    let pollingInterval = null;
    let debounceTimeout = null;

    // Función para mostrar/ocultar loading
    function showLoading(show = true) {
        $loadingOverlay.fadeIn(show ? 200 : 0).fadeOut(show ? 0 : 200);
    }

    // Función para actualizar el estado de conexión
    function updateConnectionStatus(connected) {
        $statusIndicator.html(connected ?
            '<i class="fas fa-circle text-success"></i> Conectado' :
            '<i class="fas fa-circle text-danger"></i> Desconectado'
        );
    }

    // Función para abrir nueva ventana
    window.openNewWindow = function(idventa) {
        localStorage.clear();
        const url = `/recojoproducto/${idventa}`;
        window.open(url, '_blank');
    };

    // Función para crear elemento de lista
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
            showConfirmButton: false,
            background: '#d4edda',
            customClass: {
                popup: 'animated fadeInDown faster',
                title: 'font-weight-bold',
                content: 'text-muted'
            }
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
            showConfirmButton: false,
            background: '#f8d7da',
            customClass: {
                popup: 'animated fadeInDown faster',
                title: 'font-weight-bold',
                content: 'text-muted'
            }
        });
    }

    // Función para filtrar recojos
    function filterRecojos() {
        const searchGlobalTerm = $searchGlobal.val().toLowerCase().trim();
        const estadoFilter = $filterEstado.val();
        let visibleCount = 0;

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

        $recogosList.empty();
        
        if (filteredVentas.length === 0) {
            $noRecojos.show();
            $totalRecojos.text('0');
        } else {
            $noRecojos.hide();
            filteredVentas.forEach(venta => {
                const $listItem = createRecojoListItem(venta, false);
                $recogosList.append($listItem);
            });
            visibleCount = filteredVentas.length;
            $totalRecojos.text(visibleCount);
        }
    }

    // Función para actualizar recojos
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

                const oldVentaIds = new Set(ventasData.map(venta => venta.id));
                ventasData = response.ventas;
                const newVentas = ventasData.filter(venta => !oldVentaIds.has(venta.id));

                newVentas.forEach(venta => {
                    showNewVentaNotification(venta);
                });

                filterRecojos();
                showLoading(false);
            },
            error: function(xhr, status) {
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

    // Event Listeners
    $recogosList.on('click', 'li', function() {
        const ventaId = $(this).data('venta-id');
        if (ventaId) {
            window.openNewWindow(ventaId);
        }
    });

    $refreshBtn.on('click', function() {
        $(this).addClass('fa-spin');
        fetchNewRecojos();
        setTimeout(() => {
            $(this).removeClass('fa-spin');
        }, 1000);
    });

    $filterBtn.on('click', function() {
        $filtersSection.slideToggle();
    });

    $searchGlobal.on('input', function() {
        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(() => {
            filterRecojos();
        }, 300);
    });

    $filterEstado.on('change', function() {
        filterRecojos();
    });

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
    fetchNewRecojos();
    pollingInterval = setInterval(fetchNewRecojos, 5000);

    // Cleanup al cerrar página
    $(window).on('beforeunload', function() {
        isPollingActive = false;
        if (pollingInterval) {
            clearInterval(pollingInterval);
        }
    });
});
</script>
@endpush