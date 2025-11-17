@extends('adminlte::page')
@section('title', 'Capturas de Carpeta')
@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>{{ $carpeta->descripcion }}</h1>
            <p class="text-muted">
                <i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($carpeta->fecha)->format('d/m/Y') }}
            </p>
        </div>
        <div class="col-sm-6">
            <div class="float-right">
                <a href="{{ url()->previous() }}" class="btn btn-secondary mr-2">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addCapturaModal">
                    <i class="fas fa-plus"></i> Agregar Captura
                </button>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success animate__animated animate__fadeIn">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Buscador y Filtro para Capturas --}}
            <form action="{{ route('carpetas.show', $carpeta->id) }}" method="GET" class="mb-4" id="realtimeSearchForm">

                {{-- Fila superior: Buscador principal + botón mostrar fechas --}}
                <div class="form-row align-items-center mb-2">
                    <div class="col-md-9">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-primary text-white">
                                    <i class="fas fa-user"></i>
                                </span>
                            </div>
                            <input type="text" name="search_captura" id="searchCapturaInput" class="form-control"
                                placeholder="Buscar por nombre..." value="{{ request('search_captura') }}"
                                autocomplete="off">
                            <div id="searchResultsDropdown" class="dropdown-menu search-dropdown w-100"></div>
                        </div>
                    </div>

                    <div class="col-md-3 text-right">
                        <button class="btn btn-outline-info w-100" type="button" data-toggle="collapse"
                            data-target="#dateFilters">
                            <i class="fas fa-calendar-alt"></i> Mostrar Fechas
                        </button>
                    </div>
                </div>

                {{-- Segunda fila: 3 buscadores + limpiar --}}
                <div class="form-row align-items-center mb-2">
                    {{-- Buscador Fecha --}}
                    <div class="col-md-3">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-secondary text-white"><i class="fas fa-user"></i></span>
                            </div>
                            <input type="text" name="search_captura_1" id="searchCapturaInput1" class="form-control"
                                placeholder="Buscar por Nombre..." value="{{ request('search_captura_1') }}"
                                autocomplete="off">
                            <div id="searchResultsDropdown1" class="dropdown-menu search-dropdown w-100"></div>
                        </div>
                    </div>

                    {{-- Buscador Monto --}}
                    <div class="col-md-3">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-info text-white"><i class="fas fa-dollar-sign"></i></span>
                            </div>
                            <input type="text" name="search_captura_2" id="searchCapturaInput2" class="form-control"
                                placeholder="Buscar por monto..." value="{{ request('search_captura_2') }}"
                                autocomplete="off">
                            <div id="searchResultsDropdown2" class="dropdown-menu search-dropdown w-100"></div>
                        </div>
                    </div>

                    {{-- Buscador Adicional --}}
                    <div class="col-md-3">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-dark text-white"><i class="fas fa-calendar"></i></span>
                            </div>
                            <input type="text" name="search_captura_3" id="searchCapturaInput3" class="form-control"
                                placeholder="Buscar Fecha..." value="{{ request('search_captura_3') }}" autocomplete="off">
                            <div id="searchResultsDropdown3" class="dropdown-menu search-dropdown w-100"></div>
                        </div>
                    </div>

                    {{-- Botón Limpiar --}}
                    <div class="col-md-3 d-flex">
                        <a href="{{ route('carpetas.show', $carpeta->id) }}" class="btn btn-danger w-100">
                            <i class="fas fa-times"></i> Limpiar Filtros
                        </a>
                    </div>
                </div>

                {{-- Filtros de fecha colapsables --}}
                <div class="collapse mt-3" id="dateFilters">
                    <div class="card card-body border">
                        <div class="form-row">
                            <div class="col-md-6 mb-2">
                                <label for="startDateInput"><i class="fas fa-calendar-day"></i> Desde</label>
                                <input type="date" name="start_date" id="startDateInput" class="form-control"
                                    value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="endDateInput"><i class="fas fa-calendar-day"></i> Hasta</label>
                                <input type="date" name="end_date" id="endDateInput" class="form-control"
                                    value="{{ request('end_date') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </form>


            {{-- Fin Buscador y Filtro --}}

            <div class="gallery-container">
                <div class="gallery-controls mb-3">
                    <div class="btn-group">
                        <button type="button" class="btn btn-warning" id="editButton">
                            <i class="fas fa-edit"></i> Editar Captura Actual
                        </button>
                         <button type="button" class="btn btn-danger" id="deleteButton" hidden>
                            <i class="fas fa-trash"></i> Eliminar Captura Actual
                        </button> 
                        <button type="button" class="btn btn-primary" id="processAllOcrButton">
                            <i class="fas fa-robot"></i> Procesar y Guardar Texto de Imagenes
                        </button>
                        <button type="button" class="btn btn-success" id="processNewOcrButton">
                            <i class="fas fa-magic"></i> Guardar Texto de Nuevas Imágenes
                        </button>
                    </div>
                </div>

                <div class="gallery-image-container">
                    <div class="gallery-slider" id="gallerySlider">
                        {{-- Las imágenes se cargarán aquí dinámicamente --}}
                    </div>
                </div>

                <div class="gallery-navigation mt-3">
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-primary" id="prevButton">
                            <i class="fas fa-chevron-left"></i> Anterior
                        </button>
                        <span class="gallery-counter text-muted">
                            <strong><span id="currentIndexDisplay">0</span> de <span
                                    id="totalSlidesDisplay">0</span></strong>
                        </span>
                        <button type="button" class="btn btn-primary" id="nextButton">
                            Siguiente <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <div class="gallery-pagination mt-2 text-center">
                    <div class="btn-group" id="galleryPagination">
                        {{-- Los botones de paginación se cargarán aquí dinámicamente --}}
                    </div>
                </div>

                <div id="noCapturesMessage" class="alert alert-info animate__animated animate__fadeIn"
                    style="display: none;">
                    <i class="fas fa-info-circle"></i> No se encontraron capturas que coincidan con los filtros.
                </div>
            </div>
        </div>
    </div>

    {{-- Modal para agregar capturas --}}
    <div class="modal fade" id="addCapturaModal" tabindex="-1" role="dialog" aria-labelledby="addCapturaModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCapturaModalLabel">Agregar Capturas</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('capturas.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="carpeta_id" value="{{ $carpeta->id }}">
                        <div class="form-group">
                            <label for="foto_original">Seleccionar Fotos</label>
                            <input type="file" class="form-control-file @error('foto_original') is-invalid @enderror"
                                id="foto_original" name="foto_original[]" multiple accept="image/*">
                            @error('foto_original')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Puedes seleccionar múltiples fotos.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Capturas
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal para zoom de imagen --}}
    <div class="modal fade" id="imageZoomModal" tabindex="-1" role="dialog" aria-labelledby="imageZoomModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="imageZoomModalLabel">Vista de Imagen</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center d-flex align-items-center justify-content-center"
                    style="overflow: hidden;">
                    <img id="modalZoomImage" src="" class="img-fluid"
                        style="cursor: grab; max-height: 80vh;" draggable="false">
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center">
                    <button type="button" class="btn btn-secondary" id="zoomOutBtn">
                        <i class="fas fa-search-minus"></i> Alejar
                    </button>
                    <button type="button" class="btn btn-secondary" id="zoomInBtn">
                        <i class="fas fa-search-plus"></i> Acercar
                    </button>
                    <button type="button" class="btn btn-secondary" id="resetZoomBtn">
                        <i class="fas fa-sync-alt"></i> Restablecer
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <style>
        .gallery-container {
            min-height: 80vh;
            display: flex;
            flex-direction: column;
        }

        .gallery-image-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
            min-height: 400px;
        }

        .gallery-slider {
            position: relative;
            width: 100%;
            height: 100%;
        }

        .gallery-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .gallery-slide.active {
            opacity: 1;
            position: relative;
        }

        .gallery-image {
            max-width: 100%;
            max-height: 70vh;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out;
            cursor: pointer;
        }

        .gallery-controls {
            display: flex;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 10px;
        }

        .gallery-controls .btn-group {
            flex-wrap: wrap;
            gap: 5px;
        }

        .gallery-navigation {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }

        .gallery-counter {
            font-size: 1.2em;
            font-weight: bold;
            color: #495057;
        }

        .btn-group {
            gap: 5px;
        }

        .gallery-pagination .page-button {
            margin: 0 5px;
            font-size: 1rem;
            color: #007bff;
            border: none;
            background: none;
            cursor: pointer;
        }

        .gallery-pagination .page-button:hover {
            text-decoration: underline;
        }

        .gallery-pagination .page-button.active {
            font-weight: bold;
            color: #0056b3;
        }

        .gallery-pagination .btn-outline-secondary {
            font-size: 1rem;
            color: #495057;
        }

        .gallery-pagination .btn-outline-secondary:hover {
            color: #0056b3;
            background-color: #e2e6ea;
        }

        /* Estilos para el buscador en tiempo real */
        .search-input-container {
            position: relative;
            flex-grow: 1;
            max-width: 300px;
            margin-bottom: 10px;
        }

        .search-dropdown {
            position: absolute;
            z-index: 1000;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ced4da;
            border-top: none;
            border-radius: 0 0 .25rem .25rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, .15);
            background-color: white;
        }

        .search-dropdown .dropdown-item {
            cursor: pointer;
            padding: 8px 15px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #212529;
        }

        .search-dropdown .dropdown-item:hover {
            background-color: #e9ecef;
        }

        .search-dropdown.show {
            display: block;
        }

        /* Loading indicator */
        .loading-indicator {
            display: none;
            text-align: center;
            padding: 20px;
            color: #6c757d;
        }

        .loading-indicator.show {
            display: block;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .form-inline {
                flex-direction: column;
            }

            .search-input-container {
                max-width: 100%;
                width: 100%;
            }

            .input-group {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://unpkg.com/tesseract.js@4.0.0/dist/tesseract.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Configuración de los buscadores
            const searchInputs = [{
                    inputId: 'searchCapturaInput',
                    dropdownId: 'searchResultsDropdown',
                    param: 'search_captura'
                },
                {
                    inputId: 'searchCapturaInput1',
                    dropdownId: 'searchResultsDropdown1',
                    param: 'search_captura_1'
                },
                {
                    inputId: 'searchCapturaInput2',
                    dropdownId: 'searchResultsDropdown2',
                    param: 'search_captura_2'
                },
                {
                    inputId: 'searchCapturaInput3',
                    dropdownId: 'searchResultsDropdown3',
                    param: 'search_captura_3'
                }
            ];

            // Elementos del DOM
            const gallerySlider = document.getElementById('gallerySlider');
            const prevButton = document.getElementById('prevButton');
            const nextButton = document.getElementById('nextButton');
            const currentIndexDisplay = document.getElementById('currentIndexDisplay');
            const totalSlidesDisplay = document.getElementById('totalSlidesDisplay');
            const editButton = document.getElementById('editButton');
            const deleteButton = document.getElementById('deleteButton');
            const processAllOcrButton = document.getElementById('processAllOcrButton');
            const processNewOcrButton = document.getElementById('processNewOcrButton');
            const galleryPagination = document.getElementById('galleryPagination');
            const noCapturesMessage = document.getElementById('noCapturesMessage');
            const startDateInput = document.getElementById('startDateInput');
            const endDateInput = document.getElementById('endDateInput');

            // Zoom Modal elements
            const imageZoomModalElement = document.getElementById('imageZoomModal');
            const imageZoomModal = new bootstrap.Modal(imageZoomModalElement);
            const modalZoomImage = document.getElementById('modalZoomImage');
            const zoomInBtn = document.getElementById('zoomInBtn');
            const zoomOutBtn = document.getElementById('zoomOutBtn');
            const resetZoomBtn = document.getElementById('resetZoomBtn');

            // Variables de estado
            let currentCapturesData = [];
            let currentIndex = -1;
            let totalSlides = 0;
            let currentScale = 1;
            let currentX = 0;
            let currentY = 0;
            let isDragging = false;
            let startX, startY;

            // Timeouts para búsquedas
            let searchTimeouts = {};
            let realtimeSearchTimeout;

            // Configuración de Toastr
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "3000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };

            // Configurar event listeners para cada buscador
            searchInputs.forEach(({
                inputId,
                dropdownId,
                param
            }) => {
                const input = document.getElementById(inputId);
                const dropdown = document.getElementById(dropdownId);

                if (!input || !dropdown) {
                    console.warn(`No se encontró el elemento: ${inputId} o ${dropdownId}`);
                    return;
                }

                // Event listener para input
                input.addEventListener('input', function() {
                    const query = this.value.trim();

                    // Limpiar timeout anterior para este input específico
                    if (searchTimeouts[inputId]) {
                        clearTimeout(searchTimeouts[inputId]);
                    }

                    // Si la consulta es muy corta, ocultar dropdown y hacer búsqueda
                    if (query.length < 2) {
                        dropdown.innerHTML = '';
                        dropdown.classList.remove('show');
                        triggerRealtimeSearch();
                        return;
                    }

                    // Configurar timeout para sugerencias
                    searchTimeouts[inputId] = setTimeout(async () => {
                        try {
                            const response = await fetch(
                                `{{ route('carpetas.search_suggestions', $carpeta->id) }}?query=${encodeURIComponent(query)}`
                            );

                            if (!response.ok) {
                                throw new Error('Error en la respuesta del servidor');
                            }

                            const suggestions = await response.json();
                            dropdown.innerHTML = '';

                            if (suggestions && suggestions.length > 0) {
                                suggestions.forEach(suggestion => {
                                    const item = document.createElement('a');
                                    item.classList.add('dropdown-item');
                                    item.href = '#';
                                    item.textContent = suggestion;

                                    item.addEventListener('click', (e) => {
                                        e.preventDefault();
                                        input.value = suggestion;
                                        dropdown.classList.remove(
                                            'show');
                                        triggerRealtimeSearch();
                                    });

                                    dropdown.appendChild(item);
                                });
                                dropdown.classList.add('show');
                            } else {
                                dropdown.classList.remove('show');
                            }
                        } catch (error) {
                            console.error('Error fetching search suggestions:', error);
                            dropdown.classList.remove('show');
                        }
                    }, 300);

                    // Siempre disparar búsqueda en tiempo real
                    triggerRealtimeSearch();
                });

                // Event listener para focus
                input.addEventListener('focus', function() {
                    if (this.value.length >= 2 && dropdown.children.length > 0) {
                        dropdown.classList.add('show');
                    }
                });
            });

            // Event listener global para ocultar dropdowns
            document.addEventListener('click', function(e) {
                searchInputs.forEach(({
                    inputId,
                    dropdownId
                }) => {
                    const input = document.getElementById(inputId);
                    const dropdown = document.getElementById(dropdownId);

                    if (input && dropdown && !input.contains(e.target) && !dropdown.contains(e
                            .target)) {
                        dropdown.classList.remove('show');
                    }
                });
            });


            document.getElementById('searchCapturaInput2').addEventListener('keyup', function() {
                const term = this.value.trim();
                if (term.length < 2) return;

                fetch(`/carpetas/${carpetaId}/search-realtime?search_captura_2=${term}`)
                    .then(response => response.json())
                    .then(data => {
                        renderCaptures(data.capturas, term); // 🔥 Pasamos el término exacto
                    });
            });



            // Event listeners para filtros de fecha
            startDateInput.addEventListener('change', triggerRealtimeSearch);
            endDateInput.addEventListener('change', triggerRealtimeSearch);

            // Función para disparar búsqueda en tiempo real - CORREGIDA
            function triggerRealtimeSearch() {
                clearTimeout(realtimeSearchTimeout);

                realtimeSearchTimeout = setTimeout(async () => {
                    const params = new URLSearchParams();

                    // Recoger valores de todos los buscadores
                    searchInputs.forEach(({
                        inputId,
                        param
                    }) => {
                        const inputEl = document.getElementById(inputId);
                        if (inputEl && inputEl.value.trim()) {
                            params.append(param, inputEl.value.trim());
                        }
                    });

                    // Agregar filtros de fecha
                    if (startDateInput.value) params.append('start_date', startDateInput.value);
                    if (endDateInput.value) params.append('end_date', endDateInput.value);

                    try {
                        console.log('Realizando búsqueda con parámetros:', params.toString());

                        const response = await fetch(
                            `{{ route('carpetas.search_realtime', $carpeta->id) }}?${params.toString()}`
                        );

                        if (!response.ok) {
                            throw new Error(`Error HTTP: ${response.status}`);
                        }

                        const data = await response.json();
                        console.log('Datos recibidos:', data);

                        // Verificar que los datos tengan la estructura correcta
                        if (data && Array.isArray(data.capturas)) {
                            renderCaptures(data.capturas);
                        } else if (Array.isArray(data)) {
                            // Si la respuesta es directamente un array
                            renderCaptures(data);
                        } else {
                            console.error('Estructura de datos inesperada:', data);
                            renderCaptures([]);
                        }

                    } catch (error) {
                        console.error('Error al filtrar capturas:', error);
                        toastr.error('Error al filtrar capturas: ' + error.message);
                        renderCaptures([]); // Mostrar galería vacía en caso de error
                    }
                }, 500);
            }

            /**
             * Renderiza las capturas en el carrusel - MEJORADA
             */
            function renderCaptures(captures, termToHighlight = '') {
                console.log('Renderizando capturas:', captures);

                currentCapturesData = captures || [];
                totalSlides = currentCapturesData.length;

                // Limpiar contenido anterior
                gallerySlider.innerHTML = '';
                galleryPagination.innerHTML = '';

                if (totalSlides === 0) {
                    noCapturesMessage.style.display = 'block';
                    currentIndex = -1;
                    console.log('No hay capturas para mostrar');
                } else {
                    noCapturesMessage.style.display = 'none';

                    // // 🔍 Detectar término buscado desde cualquiera de los inputs
                    // let termToHighlight = '';
                    // ['searchCapturaInput', 'searchCapturaInput1', 'searchCapturaInput2', 'searchCapturaInput3']
                    // .forEach(id => {
                    //     const input = document.getElementById(id);
                    //     if (input && input.value.trim() !== '') {
                    //         termToHighlight = input.value.trim().toLowerCase();
                    //     }
                    // });

                    let foundIndex = 0;

                    currentCapturesData.forEach((captura, index) => {
                        console.log(`Procesando captura ${index}:`, captura);

                        // Crear slide
                        const slideDiv = document.createElement('div');
                        slideDiv.classList.add('gallery-slide');
                        slideDiv.dataset.capturaId = captura.id;
                        slideDiv.dataset.imageUrl = captura.foto_original_url;
                        slideDiv.dataset.hasText = captura.campo_texto_exists || false;

                        const imgElement = document.createElement('img');
                        imgElement.src = captura.foto_original_url;
                        imgElement.classList.add('img-fluid', 'gallery-image', 'zoomable-image');
                        imgElement.alt = `Captura ${captura.id}`;

                        // Manejar errores de carga de imagen
                        imgElement.onerror = function() {
                            console.error('Error cargando imagen:', this.src);
                            
                        };

                        // Zoom al hacer click
                        imgElement.addEventListener('click', function() {
                            resetZoom();
                            modalZoomImage.src = this.src;
                            imageZoomModal.show();
                        });

                        slideDiv.appendChild(imgElement);
                        gallerySlider.appendChild(slideDiv);

                        // Botón de paginación
                        const pageButton = document.createElement('button');
                        pageButton.classList.add('btn', 'btn-outline-secondary', 'page-button');
                        pageButton.dataset.index = index;
                        pageButton.textContent = index + 1;
                        pageButton.addEventListener('click', () => showSlide(index));
                        galleryPagination.appendChild(pageButton);

                        // ✅ Buscar coincidencia textual y posicionar en esa imagen
                        if (
                            termToHighlight &&
                            JSON.stringify(captura).toLowerCase().includes(termToHighlight) &&
                            foundIndex === 0
                        ) {
                            foundIndex = index;
                        }
                    });

                    currentIndex = foundIndex;
                }

                showSlide(currentIndex);
                updateButtons();
            }

            /**
             * Muestra una slide específica - MEJORADA
             */
            function showSlide(index) {
                const slides = document.querySelectorAll('.gallery-slide');

                if (slides.length === 0) {
                    currentIndex = -1;
                    updateButtons();
                    return;
                }

                // Validar índice
                if (index < 0) index = 0;
                if (index >= slides.length) index = slides.length - 1;

                // Ocultar todas las slides
                slides.forEach(slide => {
                    slide.classList.remove('active', 'animate__animated', 'animate__fadeIn');
                });

                // Mostrar slide actual
                if (slides[index]) {
                    slides[index].classList.add('active', 'animate__animated', 'animate__fadeIn');
                }

                currentIndex = index;
                updateButtons();

                // Configurar botones de acción
                const currentCapturaId = currentCapturesData[currentIndex] ? currentCapturesData[currentIndex].id :
                    null;

                if (currentCapturaId) {
                    editButton.onclick = () => {
                        window.location.href = `/capturas/${currentCapturaId}/edit`;
                    };

                    deleteButton.onclick = () => {
                        if (confirm('¿Estás seguro de eliminar esta captura?')) {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = `/capturas/${currentCapturaId}`;
                            form.innerHTML = `
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="_method" value="DELETE">
                            `;
                            document.body.appendChild(form);
                            form.submit();
                        }
                    };
                } else {
                    editButton.onclick = null;
                    deleteButton.onclick = null;
                }
            }

            /**
             * Actualiza el estado de los botones
             */
            function updateButtons() {
                prevButton.disabled = currentIndex <= 0 || totalSlides === 0;
                nextButton.disabled = currentIndex >= totalSlides - 1 || totalSlides === 0;

                currentIndexDisplay.textContent = (currentIndex === -1 ? 0 : currentIndex + 1);
                totalSlidesDisplay.textContent = totalSlides;

                const pageButtons = document.querySelectorAll('.page-button');
                pageButtons.forEach((button, index) => {
                    button.classList.toggle('active', index === currentIndex);
                });

                // Habilitar/deshabilitar botones de acción
                if (totalSlides === 0) {
                    editButton.disabled = true;
                    deleteButton.disabled = true;
                    processAllOcrButton.disabled = true;
                    processNewOcrButton.disabled = true;
                } else {
                    editButton.disabled = false;
                    deleteButton.disabled = false;
                    processAllOcrButton.disabled = false;

                    const hasNewImages = currentCapturesData.some(captura => captura.campo_texto_exists === false);
                    processNewOcrButton.disabled = !hasNewImages;
                }
            }

            // Navegación del carrusel
            prevButton.addEventListener('click', () => {
                if (currentIndex > 0) showSlide(currentIndex - 1);
            });

            nextButton.addEventListener('click', () => {
                if (currentIndex < totalSlides - 1) showSlide(currentIndex + 1);
            });

            // Navegación con teclado
            document.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowLeft' && currentIndex > 0) {
                    showSlide(currentIndex - 1);
                } else if (e.key === 'ArrowRight' && currentIndex < totalSlides - 1) {
                    showSlide(currentIndex + 1);
                }
            });

            // Funciones OCR (mantenidas igual)
            function procesarTexto(text) {
                const regex =
                    /(\d{2}\/\d{2}\/\d{4} \d{2}:\d{2})\s*[-<]\s*H\s*-\s*Abono\s*de\s*otro\s*banco\s*[QG]R\s*(\d+),([^,]+),([^,]+(?:\s*\.\.\.)?)?,?\s*([^,\n]*)\s*Cobro\s*[QG]R\s*(?:[^Bs]*?)(Bs\s*[\d,]+\.\d{2})/g;
                let movimientos = [];
                let match;

                while ((match = regex.exec(text)) !== null) {
                    movimientos.push({
                        "fecha_hora": match[1].trim(),
                        "numero_transaccion": match[2].trim(),
                        "banco": match[3].trim(),
                        "nombre": match[4].trim(),
                        "categoria": match[5].trim() || "Varios",
                        "tipo_movimiento": "Cobro QR",
                        "monto": match[6].trim()
                    });
                }

                return movimientos;
            }

            async function sendOcrResultsToServer(results, successMessage) {
                toastr.info('Enviando resultados al servidor...');

                try {
                    const response = await fetch('{{ route('carpetas.process_all_ocr', $carpeta->id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            capturas_data: results
                        })
                    });

                    const data = await response.json();

                    if (response.ok) {
                        toastr.success(successMessage || data.message);
                        triggerRealtimeSearch();
                    } else {
                        toastr.error('Error al guardar los resultados en el servidor: ' + (data.error ||
                            'Mensaje desconocido.'));
                        console.error('Error del servidor:', data);
                    }
                } catch (error) {
                    toastr.error('Error de comunicación con el servidor al guardar: ' + error.message);
                    console.error('Error de fetch al guardar:', error);
                } finally {
                    processAllOcrButton.disabled = false;
                    processNewOcrButton.disabled = false;
                }
            }

            // Event listeners para OCR (mantenidos igual)
            processAllOcrButton.addEventListener('click', async () => {
                if (totalSlides === 0) {
                    toastr.warning('No hay capturas para procesar.');
                    return;
                }

                if (!confirm(
                        'Esto procesará el OCR de TODAS las capturas visibles y guardará los resultados. ¿Deseas continuar?'
                    )) {
                    return;
                }

                processAllOcrButton.disabled = true;
                processNewOcrButton.disabled = true;
                toastr.info('Iniciando procesamiento OCR masivo. Esto puede tomar tiempo...');

                const results = [];
                let processedCount = 0;

                for (const capturaData of currentCapturesData) {
                    const capturaId = capturaData.id;
                    const imageUrl = capturaData.foto_original_url;

                    toastr.info(`Procesando captura ${++processedCount} de ${totalSlides}...`);

                    try {
                        const {
                            data: {
                                text
                            }
                        } = await Tesseract.recognize(
                            imageUrl,
                            'spa', {
                                logger: m => console.log(`OCR Captura ${capturaId}: `, m)
                            }
                        );

                        const processedData = procesarTexto(text);
                        let textToSave = text;

                        if (processedData.length > 0) {
                            textToSave = JSON.stringify(processedData, null, 2);
                            toastr.success(`OCR de captura ${capturaId} completado y estructurado.`);
                        } else {
                            toastr.warning(
                                `OCR de captura ${capturaId} completado, pero no se encontraron movimientos estructurados. Se guardará el texto plano.`
                            );
                        }

                        results.push({
                            id: capturaId,
                            text_data: textToSave
                        });
                    } catch (err) {
                        toastr.error(`Error al procesar OCR para captura ${capturaId}: ${err.message}`);
                        console.error(`Error de Tesseract para captura ${capturaId}:`, err);
                        results.push({
                            id: capturaId,
                            text_data: 'Error al procesar OCR: ' + err.message
                        });
                    }
                }

                sendOcrResultsToServer(results,
                    'Textos de OCR de todas las capturas guardados exitosamente.');
            });

            processNewOcrButton.addEventListener('click', async () => {
                const newImageCaptures = currentCapturesData.filter(captura => captura
                    .campo_texto_exists === false);

                if (newImageCaptures.length === 0) {
                    toastr.warning('No hay nuevas imágenes sin texto guardado para procesar.');
                    return;
                }

                if (!confirm(
                        `Se procesarán ${newImageCaptures.length} nuevas imágenes sin texto guardado. ¿Deseas continuar?`
                    )) {
                    return;
                }

                processAllOcrButton.disabled = true;
                processNewOcrButton.disabled = true;
                toastr.info(
                    `Iniciando procesamiento OCR para ${newImageCaptures.length} nuevas imágenes. Esto puede tomar tiempo...`
                );

                const results = [];
                let processedCount = 0;

                for (const capturaData of newImageCaptures) {
                    const capturaId = capturaData.id;
                    const imageUrl = capturaData.foto_original_url;

                    toastr.info(
                        `Procesando nueva captura ${++processedCount} de ${newImageCaptures.length}...`
                    );

                    try {
                        const {
                            data: {
                                text
                            }
                        } = await Tesseract.recognize(
                            imageUrl,
                            'spa', {
                                logger: m => console.log(`OCR Nueva Captura ${capturaId}: `, m)
                            }
                        );

                        const processedData = procesarTexto(text);
                        let textToSave = text;

                        if (processedData.length > 0) {
                            textToSave = JSON.stringify(processedData, null, 2);
                            toastr.success(
                                `OCR de nueva captura ${capturaId} completado y estructurado.`);
                        } else {
                            toastr.warning(
                                `OCR de nueva captura ${capturaId} completado, pero no se encontraron movimientos estructurados. Se guardará el texto plano.`
                            );
                        }

                        results.push({
                            id: capturaId,
                            text_data: textToSave
                        });
                    } catch (err) {
                        toastr.error(
                            `Error al procesar OCR para nueva captura ${capturaId}: ${err.message}`);
                        console.error(`Error de Tesseract para nueva captura ${capturaId}:`, err);
                        results.push({
                            id: capturaId,
                            text_data: 'Error al procesar OCR: ' + err.message
                        });
                    }
                }

                sendOcrResultsToServer(results,
                    'Textos de OCR de nuevas capturas guardados exitosamente.');
            });

            // Funcionalidad de zoom (mantenida igual)
            modalZoomImage.style.transformOrigin = '0 0';

            function applyTransform() {
                modalZoomImage.style.transform = `translate(${currentX}px, ${currentY}px) scale(${currentScale})`;
            }

            function resetZoom() {
                currentScale = 1;
                currentX = 0;
                currentY = 0;
                applyTransform();
                modalZoomImage.style.cursor = 'zoom-in';
            }

            zoomInBtn.addEventListener('click', () => {
                currentScale = Math.min(currentScale + 0.2, 5);
                applyTransform();
                modalZoomImage.style.cursor = 'grab';
            });

            zoomOutBtn.addEventListener('click', () => {
                currentScale = Math.max(currentScale - 0.2, 1);
                if (currentScale === 1) resetZoom();
                else applyTransform();
            });

            resetZoomBtn.addEventListener('click', resetZoom);

            // Funcionalidad de arrastre
            modalZoomImage.addEventListener('mousedown', (e) => {
                if (currentScale > 1) {
                    isDragging = true;
                    startX = e.clientX - currentX;
                    startY = e.clientY - currentY;
                    modalZoomImage.style.cursor = 'grabbing';
                }
            });

            modalZoomImage.addEventListener('mousemove', (e) => {
                if (!isDragging) return;
                e.preventDefault();
                currentX = e.clientX - startX;
                currentY = e.clientY - startY;

                const imgRect = modalZoomImage.getBoundingClientRect();
                const containerRect = modalZoomImage.parentElement.getBoundingClientRect();
                const scaledWidth = imgRect.width * currentScale;
                const scaledHeight = imgRect.height * currentScale;
                const limitX = Math.max(0, (scaledWidth - containerRect.width) / 2);
                const limitY = Math.max(0, (scaledHeight - containerRect.height) / 2);

                currentX = Math.max(Math.min(currentX, limitX), -limitX);
                currentY = Math.max(Math.min(currentY, limitY), -limitY);

                applyTransform();
            });

            modalZoomImage.addEventListener('mouseup', () => {
                isDragging = false;
                if (currentScale > 1) {
                    modalZoomImage.style.cursor = 'grab';
                }
            });

            modalZoomImage.addEventListener('mouseleave', () => {
                isDragging = false;
                if (currentScale > 1) {
                    modalZoomImage.style.cursor = 'grab';
                }
            });

            modalZoomImage.addEventListener('wheel', (e) => {
                e.preventDefault();
                const scaleAmount = 0.1;
                const oldScale = currentScale;

                if (e.deltaY < 0) {
                    currentScale = Math.min(currentScale + scaleAmount, 5);
                } else {
                    currentScale = Math.max(currentScale - scaleAmount, 1);
                }

                const rect = modalZoomImage.getBoundingClientRect();
                const mouseX = e.clientX - rect.left;
                const mouseY = e.clientY - rect.top;

                currentX = currentX - (mouseX / oldScale) * (currentScale - oldScale);
                currentY = currentY - (mouseY / oldScale) * (currentScale - oldScale);

                applyTransform();

                if (currentScale === 1) resetZoom();
                else modalZoomImage.style.cursor = 'grab';
            });

            // Inicialización - MEJORADA
            const initialCaptures = [
                @foreach ($capturas as $captura)
                    {
                        id: {{ $captura->id }},
                        foto_original_url: "{{ asset('storage/' . $captura->foto_original) }}",
                        campo_texto_exists: {{ !empty($captura->campo_texto) ? 'true' : 'false' }},
                    },
                @endforeach
            ];

            console.log('Capturas iniciales:', initialCaptures);
            renderCaptures(initialCaptures);
        });
    </script>
@stop
