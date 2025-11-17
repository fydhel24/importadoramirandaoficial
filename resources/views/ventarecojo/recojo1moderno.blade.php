@extends('adminlte::page')

@section('title', 'Lista de Recojos')

@section('content_header')
    <h1 class="text-center">Lista de Recojos Cola</h1>
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

        <!-- Barra de búsqueda principal -->
        <div class="card shadow-sm rounded mb-3">
            <div class="card-header bg-gradient-success text-white d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">
                    <i class="fas fa-search mr-2"></i>Buscar Recojos
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool text-white" id="clear-search" title="Limpiar búsqueda">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8 mb-2">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white">
                                    <i class="fas fa-search text-primary"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control form-control-lg" id="search-global"
                                placeholder="Buscar por CODIGO  CLIENTE o CI">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button" id="search-btn">
                                    <i class="fas fa-search mr-1"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12">
                        <div class="d-flex flex-wrap gap-2" id="search-tags">
                            <!-- Tags de búsqueda activa aparecerán aquí -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de recojos en pantalla completa -->
        <div class="card shadow-sm rounded">
            <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">
                    <i class="fas fa-list mr-2"></i>Recojos Recientes
                    <small class="ml-2 opacity-75" id="results-count">(0 resultados)</small>
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool text-white" id="refresh-btn" title="Actualizar">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <button type="button" class="btn btn-tool text-white" id="toggle-filters" title="Filtros avanzados">
                        <i class="fas fa-sliders-h"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <!-- Filtros avanzados -->
                <div class="p-3 border-bottom bg-light" id="advanced-filters" style="display: none;">
                    <h6 class="mb-3 text-muted">
                        <i class="fas fa-filter mr-1"></i>Filtros Avanzados
                    </h6>
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <label class="small text-muted mb-1">Rango de fecha</label>
                            <input type="date" class="form-control form-control-sm" id="filter-fecha">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small text-muted mb-1">Monto mínimo</label>
                            <input type="number" class="form-control form-control-sm" id="filter-monto-min" 
                                placeholder="Bs. mínimo">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small text-muted mb-1">Monto máximo</label>
                            <input type="number" class="form-control form-control-sm" id="filter-monto-max" 
                                placeholder="Bs. máximo">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small text-muted mb-1">Ordenar por</label>
                            <select class="form-control form-control-sm" id="filter-orden">
                                <option value="recientes">Más recientes</option>
                                <option value="antiguos">Más antiguos</option>
                                <option value="monto-alto">Monto mayor</option>
                                <option value="monto-bajo">Monto menor</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Lista de recojos -->
                <div class="list-container" style="height: calc(100vh - 400px); overflow-y: auto;">
                    <ul class="list-group list-group-flush" id="recogos-list">
                        <!-- Lista será poblada dinámicamente por JavaScript -->
                    </ul>
                    <div id="no-recojos" class="text-center py-4 text-muted" style="display: none;">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>No hay recojos que mostrar</p>
                    </div>
                    <div id="no-results" class="text-center py-4 text-muted" style="display: none;">
                        <i class="fas fa-search fa-2x mb-2"></i>
                        <p>No se encontraron recojos con los criterios de búsqueda</p>
                        <button class="btn btn-sm btn-outline-primary mt-2" id="reset-search">
                            <i class="fas fa-times mr-1"></i> Limpiar búsqueda
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading overlay -->
    <div id="loading-overlay"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999;">
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
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .recojo-item.active {
            background-color: #e7f3ff;
            border-left-color: #007bff;
            font-weight: 500;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .recojo-item.new-item {
            animation: highlightNew 2s ease-in-out;
        }

        .recojo-item.highlight-match {
            background-color: #fff3cd;
            border-left-color: #ffc107;
        }

        @keyframes highlightNew {
            0% {
                background-color: #d4edda;
            }

            100% {
                background-color: #ffffff;
            }
        }

        .card-header.bg-gradient-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border-bottom: 2px solid #0056b3;
            border-radius: 8px 8px 0 0;
        }

        .card-header.bg-gradient-success {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
            border-bottom: 2px solid #1e7e34;
            border-radius: 8px;
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
            background: rgba(255, 255, 255, 0.1);
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
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .badge {
            border-radius: 12px;
            padding: 6px 12px;
            font-weight: 500;
        }

        .text-success {
            color: #28a745 !important;
        }

        .text-danger {
            color: #dc3545 !important;
        }

        .search-tag {
            background: #e7f3ff;
            border: 1px solid #007bff;
            border-radius: 15px;
            padding: 4px 12px;
            font-size: 0.8rem;
            color: #007bff;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .search-tag .close {
            font-size: 1rem;
            cursor: pointer;
            opacity: 0.7;
        }

        .search-tag .close:hover {
            opacity: 1;
        }

        .input-group-text {
            border-radius: 6px 0 0 6px;
        }

        #search-btn {
            border-radius: 0 6px 6px 0;
        }
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
            const $toggleFilters = $('#toggle-filters');
            const $advancedFilters = $('#advanced-filters');
            const $searchGlobal = $('#search-global');
            const $filterEstado = $('#filter-estado');
            const $noRecojos = $('#no-recojos');
            const $noResults = $('#no-results');
            const $resultsCount = $('#results-count');
            const $searchBtn = $('#search-btn');
            const $clearSearch = $('#clear-search');
            const $resetSearch = $('#reset-search');
            const $searchTags = $('#search-tags');
            const $filterFecha = $('#filter-fecha');
            const $filterMontoMin = $('#filter-monto-min');
            const $filterMontoMax = $('#filter-monto-max');
            const $filterOrden = $('#filter-orden');

            let ventasData = [];
            let isPollingActive = true;
            let pollingInterval = null;
            let debounceTimeout = null;
            let currentSearchTerm = '';
            let currentFilters = {};

           

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
                const url = `/recojoproductocola/${idventa}`;
                window.open(url, '_blank');
            };

            // Función para crear elemento de lista
            function createRecojoListItem(venta, isNew = false, searchTerm = '') {
                const fecha = new Date(venta.created_at);
                const fechaFormateada = fecha.toLocaleDateString('es-BO') + ' ' + fecha.toLocaleTimeString(
                'es-BO', {
                    hour: '2-digit',
                    minute: '2-digit'
                });

                // Resaltar coincidencias en el texto
                const highlightText = (text, term) => {
                    if (!term || !text) return text;
                    const regex = new RegExp(`(${term})`, 'gi');
                    return String(text).replace(regex, '<mark class="bg-warning px-1 rounded">$1</mark>');
                };

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
                        <strong>${highlightText('#' + venta.id, searchTerm)}</strong> - ${highlightText(venta.nombre_cliente || 'N/A', searchTerm)}
                        <br>
                        <small class="text-muted">
                            <i class="fas fa-id-card"></i> CI: ${highlightText(venta.ci || 'N/A', searchTerm)}
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

            // Función para actualizar tags de búsqueda
            function updateSearchTags() {
                $searchTags.empty();
                
                if (currentSearchTerm) {
                    $searchTags.append(`
                        <div class="search-tag">
                            <span>Búsqueda: "${currentSearchTerm}"</span>
                            <span class="close" onclick="clearSearchTerm()">&times;</span>
                        </div>
                    `);
                }

                if (currentFilters.estado) {
                    $searchTags.append(`
                        <div class="search-tag">
                            <span>Estado: ${currentFilters.estado}</span>
                            <span class="close" onclick="clearFilter('estado')">&times;</span>
                        </div>
                    `);
                }

                if (currentFilters.fecha) {
                    $searchTags.append(`
                        <div class="search-tag">
                            <span>Fecha: ${currentFilters.fecha}</span>
                            <span class="close" onclick="clearFilter('fecha')">&times;</span>
                        </div>
                    `);
                }

                if (currentFilters.montoMin) {
                    $searchTags.append(`
                        <div class="search-tag">
                            <span>Mín: Bs. ${currentFilters.montoMin}</span>
                            <span class="close" onclick="clearFilter('montoMin')">&times;</span>
                        </div>
                    `);
                }

                if (currentFilters.montoMax) {
                    $searchTags.append(`
                        <div class="search-tag">
                            <span>Máx: Bs. ${currentFilters.montoMax}</span>
                            <span class="close" onclick="clearFilter('montoMax')">&times;</span>
                        </div>
                    `);
                }
            }

            // Funciones globales para limpiar filtros
            window.clearSearchTerm = function() {
                currentSearchTerm = '';
                $searchGlobal.val('');
                filterRecojos();
            };

            window.clearFilter = function(filterName) {
                switch(filterName) {
                    case 'estado':
                        currentFilters.estado = '';
                        $filterEstado.val('');
                        break;
                    case 'fecha':
                        currentFilters.fecha = '';
                        $filterFecha.val('');
                        break;
                    case 'montoMin':
                        currentFilters.montoMin = '';
                        $filterMontoMin.val('');
                        break;
                    case 'montoMax':
                        currentFilters.montoMax = '';
                        $filterMontoMax.val('');
                        break;
                }
                filterRecojos();
            };

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
                        const audio = new Audio(
                            'data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmMcBjiR1/LNeSsFJHfH8N2QQAoUXrTp66hVFApGn+DyvmMcBjiR1/LNeSsFJHfH8N2QQAoUXrTp66hVFApGn+DyvmMcBjiR1/LNeSsFJHfH8N2QQAoUXrTp66hVFApGn+DyvmMcBjiR1/LNeSsFJHfH8N2QQAoUXrTp66hVFApGn+DyvmMcBjiR1/LNeSsFJHfH8N2QQAoUXrTp66hVFApGn+DyvmMcBjiR1/LNeSsFJHfH8N2QQAoUXrTp66hVFApGn+DyvmMcBjiR1/LNeSsFJHfH8N2QQAoUXrTp66hVFApGn+DyvmMcBjiR1/LNeSsFJHfH8N2QQAoUXrTp66hVFApGn+DyvmMcBjiR1/LNeSsFJHfH8N2QQAoUXrTp66hVFA=='
                            );
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
                currentSearchTerm = $searchGlobal.val().toLowerCase().trim();
                currentFilters.estado = $filterEstado.val();
                currentFilters.fecha = $filterFecha.val();
                currentFilters.montoMin = $filterMontoMin.val();
                currentFilters.montoMax = $filterMontoMax.val();
                currentFilters.orden = $filterOrden.val();

                updateSearchTags();

                let visibleCount = 0;

                const filteredVentas = ventasData.filter(venta => {
                    // Búsqueda global
                    if (currentSearchTerm) {
                        const searchFields = [
                            String(venta.id || ''),
                            String(venta.nombre_cliente || '').toLowerCase(),
                            String(venta.ci || '').toLowerCase(),
                            String(venta.telefono || '').toLowerCase(),
                            String(venta.direccion || '').toLowerCase()
                        ];
                        
                        const matchesSearch = searchFields.some(field => 
                            field.includes(currentSearchTerm)
                        );
                        if (!matchesSearch) return false;
                    }

                    // Filtro por estado
                    if (currentFilters.estado && venta.estado !== currentFilters.estado) {
                        return false;
                    }

                    // Filtro por fecha
                    if (currentFilters.fecha) {
                        const ventaFecha = new Date(venta.created_at).toISOString().split('T')[0];
                        if (ventaFecha !== currentFilters.fecha) {
                            return false;
                        }
                    }

                    // Filtro por monto
                    const costo = parseFloat(venta.costo_total || 0);
                    if (currentFilters.montoMin && costo < parseFloat(currentFilters.montoMin)) {
                        return false;
                    }
                    if (currentFilters.montoMax && costo > parseFloat(currentFilters.montoMax)) {
                        return false;
                    }

                    return true;
                });

                // Ordenar resultados
                filteredVentas.sort((a, b) => {
                    switch(currentFilters.orden) {
                        case 'antiguos':
                            return new Date(a.created_at) - new Date(b.created_at);
                        case 'monto-alto':
                            return parseFloat(b.costo_total || 0) - parseFloat(a.costo_total || 0);
                        case 'monto-bajo':
                            return parseFloat(a.costo_total || 0) - parseFloat(b.costo_total || 0);
                        default: // recientes
                            return new Date(b.created_at) - new Date(a.created_at);
                    }
                });

                $recogosList.empty();

                if (filteredVentas.length === 0) {
                    if (ventasData.length === 0) {
                        $noRecojos.show();
                        $noResults.hide();
                    } else {
                        $noRecojos.hide();
                        $noResults.show();
                    }
                    $totalRecojos.text('0');
                    $resultsCount.text('(0 resultados)');
                } else {
                    $noRecojos.hide();
                    $noResults.hide();
                    filteredVentas.forEach(venta => {
                        const $listItem = createRecojoListItem(venta, false, currentSearchTerm);
                        $recogosList.append($listItem);
                    });
                    visibleCount = filteredVentas.length;
                    $totalRecojos.text(visibleCount);
                    $resultsCount.text(`(${visibleCount} resultado${visibleCount !== 1 ? 's' : ''})`);
                }
            }

            // Función para actualizar recojos
            function fetchNewRecojos() {
                if (!isPollingActive) return;

                //showLoading(true);
                $.ajax({
                    url: '/ventas/modernocola',
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

            $toggleFilters.on('click', function() {
                $advancedFilters.slideToggle();
                $(this).find('i').toggleClass('fa-sliders-h fa-times');
            });

            $searchBtn.on('click', function() {
                filterRecojos();
            });

            $clearSearch.on('click', function() {
                $searchGlobal.val('');
                currentSearchTerm = '';
                filterRecojos();
            });

            $resetSearch.on('click', function() {
                $searchGlobal.val('');
                $filterEstado.val('');
                $filterFecha.val('');
                $filterMontoMin.val('');
                $filterMontoMax.val('');
                $filterOrden.val('recientes');
                currentSearchTerm = '';
                currentFilters = {};
                filterRecojos();
            });

            $searchGlobal.on('input', function() {
                clearTimeout(debounceTimeout);
                debounceTimeout = setTimeout(() => {
                    filterRecojos();
                }, 300);
            });

            $searchGlobal.on('keypress', function(e) {
                if (e.which === 13) { // Enter key
                    filterRecojos();
                }
            });

            $filterEstado.on('change', function() {
                filterRecojos();
            });

            $filterFecha.on('change', function() {
                filterRecojos();
            });

            $filterMontoMin.on('change', function() {
                filterRecojos();
            });

            $filterMontoMax.on('change', function() {
                filterRecojos();
            });

            $filterOrden.on('change', function() {
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