@extends('adminlte::page')

@section('title', 'Análisis Predictivo')

@section('content_header')
    <h1 class="text-center" style="font-family: 'Cinzel', serif; font-size: 3em; color: #8E44AD;">
        Análisis Predictivo
    </h1>
@stop

@section('content')

    <div class="row mb-4">
        <div class="col-md-4">
            <form method="GET" action="{{ route('analisis.index') }}" id="form-producto-filtro">
                <div class="form-group">
                    <label for="search_input">Buscar Producto:</label>
                    <div class="input-group">
                        {{-- Campo de texto para la búsqueda en tiempo real --}}
                        {{-- El valor inicial es el nombre del producto si ya está filtrado --}}
                        <input type="text" class="form-control" id="search_input" placeholder="Escribe para buscar..."
                            autocomplete="off"
                            value="{{ $productoId && $productos->first() ? $productos->first()->nombre : '' }}">

                        {{-- Campo oculto que enviará el ID seleccionado (el filtro) --}}
                        <input type="hidden" name="producto_id" id="producto_id_hidden" value="{{ $productoId ?? '' }}">

                        <div class="input-group-append">
                            {{-- Botón para limpiar el filtro --}}
                            <button class="btn btn-default" type="button" id="clear_search"
                                style="display: {{ $productoId ? 'inline-block' : 'none' }};">X</button>
                        </div>
                    </div>
                </div>
            </form>

            {{-- Contenedor para mostrar los resultados de la búsqueda AJAX --}}
            {{-- La posición 'w-100' hace que ocupe todo el ancho de su columna padre (col-md-4) --}}
            <div id="search_results" class="list-group position-absolute w-25"
                style="z-index: 1000; min-width: 300px; display: none;">
                {{-- Aquí se insertarán los resultados dinámicamente --}}
            </div>

            @if ($productoId)
                <div class="alert alert-success mt-2">
                    Producto filtrado: **{{ $productos->first()->nombre ?? 'N/A' }}**. Presiona 'X' para limpiar.
                </div>
            @endif

        </div>
    </div>

    <div class="row mb-4">
        @foreach ($cards as $title => $data)
            <div class="col-md-4">
                <div class="card text-white bg-{{ $data['color'] }} mb-3">
                    <div class="card-header">{{ $title }}</div>
                    <div class="card-body">
                        <h5 class="card-title">{{ $data['titulo'] }}</h5>
                        <p class="card-text">{{ $data['descripcion'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if ($productoId)
        @php
            $productoSeleccionado = $productos->first();
            $demandaProyectada = array_sum(array_column($predicciones, 'yhat'));
        @endphp

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        Información del Producto: {{ $productoSeleccionado->nombre }}
                    </div>
                    <div class="card-body">
                        <p><strong>Total Vendido Hasta Ahora:</strong> {{ $totalVendido }}</p>
                        <p><strong>Demanda Proyectada Próximos 7 días:</strong> {{ round($demandaProyectada) }}</p>

                        @php
                            $ultimoError = \App\Models\Prediccion::where('producto_id', $productoSeleccionado->id)
                                ->orderByDesc('created_at')
                                ->value('error_promedio');
                            $ultimaConfiabilidad = \App\Models\Prediccion::where(
                                'producto_id',
                                $productoSeleccionado->id,
                            )
                                ->orderByDesc('created_at')
                                ->value('confiabilidad');
                        @endphp

                        @if ($ultimoError)
                            <p><strong>Error Promedio:</strong> {{ $ultimoError }}</p>
                        @endif

                        @if ($ultimaConfiabilidad !== null)
                            <p>
                                <strong>Nivel de Confiabilidad:</strong>
                                <span
                                    class="@if ($ultimaConfiabilidad >= 80) text-success
                                     @elseif($ultimaConfiabilidad >= 50) text-warning
                                     @else text-danger @endif">
                                    {{ $ultimaConfiabilidad }}%
                                </span>
                            </p>
                        @endif
                        <h5>Ventas Último Mes:</h5>
                        <ul>
                            @foreach ($ventasUltimoMes as $venta)
                                <li>{{ $venta->fecha }}: {{ $venta->cantidad }} unidades</li>
                            @endforeach
                            @if (count($ventasUltimoMes) == 0)
                                <li>No hay ventas registradas en el último mes.</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    @endif

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    Gráfico de Ventas
                </div>
                <div class="card-body">
                    <canvas id="graficoVentas"></canvas>
                </div>
            </div>
        </div>
    </div>

    @if (count($alertas) > 0)
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="alert alert-danger">
                    <h5>Alerta:</h5>
                    <ul>
                        @foreach ($alertas as $alerta)
                            <li>{{ $alerta }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // ---------------------- BUSCADOR ----------------------
        $(document).ready(function() {
            let searchTimeout;
            const searchInput = $('#search_input');
            const searchResults = $('#search_results');
            const productoIdHidden = $('#producto_id_hidden');
            const clearButton = $('#clear_search');

            $(document).on('click', function(e) {
                if (!$(e.target).closest('#search_input, #search_results').length) {
                    searchResults.empty().hide();
                }
            });

            searchInput.on('keyup', function() {
                const term = $(this).val();
                clearTimeout(searchTimeout);

                if (term.length < 2) {
                    searchResults.empty().hide();
                    return;
                }

                searchTimeout = setTimeout(function() {
                    $.ajax({
                        url: "{{ route('analisis.searchProductos') }}",
                        method: 'GET',
                        data: {
                            term: term
                        },
                        success: function(data) {
                            searchResults.empty();
                            if (data.length > 0) {
                                let html = '';
                                $.each(data, function(index, producto) {
                                    html += `<a href="#" class="list-group-item list-group-item-action"
                                            data-id="${producto.id}" data-nombre="${producto.nombre}">
                                            ${producto.nombre}
                                         </a>`;
                                });
                                searchResults.html(html).show();
                            } else {
                                searchResults.html(
                                    '<a href="#" class="list-group-item list-group-item-secondary disabled">No se encontraron productos</a>'
                                ).show();
                            }
                        }
                    });
                }, 300);
            });

            searchResults.on('click', 'a.list-group-item-action', function(e) {
                e.preventDefault();
                const selectedId = $(this).data('id');
                const selectedName = $(this).data('nombre');

                productoIdHidden.val(selectedId);
                searchInput.val(selectedName);
                searchResults.empty().hide();
                clearButton.show();
                $('#form-producto-filtro').submit();
            });

            clearButton.on('click', function() {
                productoIdHidden.val('');
                searchInput.val('');
                clearButton.hide();
                $('#form-producto-filtro').submit();
            });
        });
    </script>

    <script>
        // ---------------------- ALERTAS ----------------------
        @if (count($alertas) > 0)
            @foreach ($alertas as $alerta)
                Swal.fire({
                    icon: 'warning',
                    title: 'Alerta',
                    text: "{{ $alerta }}",
                    timer: 5000,
                    timerProgressBar: true,
                });
            @endforeach
        @endif
    </script>

    <script>
        // ---------------------- GRÁFICOS ----------------------
        const ctxVentas = document.getElementById('graficoVentas').getContext('2d');

        @if ($productoId)
            // ----------- CUANDO SE FILTRA UN PRODUCTO -----------
            const ventasHistoricas = @json($ventasHistoricasJS);
            const predicciones = @json($prediccionesJS);

            const fechasHistoricas = ventasHistoricas.map(v => v.fecha);
            const cantidadesHistoricas = ventasHistoricas.map(v => v.cantidad);
            const fechasPred = predicciones.map(p => p.fecha);
            const cantidadesPred = predicciones.map(p => p.yhat);

            // 📈 GRAFICO HISTÓRICO
            new Chart(ctxVentas, {
                type: 'line',
                data: {
                    labels: fechasHistoricas,
                    datasets: [{
                        label: "Histórico de Ventas",
                        data: cantidadesHistoricas,
                        borderColor: "#3498db",
                        backgroundColor: "rgba(52, 152, 219, 0.2)",
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Histórico de Ventas del Producto'
                        }
                    }
                }
            });

            // 📊 NUEVO GRAFICO SOLO DE PREDICCIÓN
            // 📊 NUEVO GRÁFICO SOLO DE PREDICCIÓN CON INTERVALO DE CONFIANZA
            const predContainer = document.createElement('div');
            predContainer.classList.add('card', 'border-danger', 'mt-4');
            predContainer.innerHTML = `
<div class="card-header bg-danger text-white">Predicción Próximos 7 Días</div>
<div class="card-body"><canvas id="graficoPrediccion"></canvas></div>
`;
            document.querySelector('.card.border-info').after(predContainer);

            const ctxPred = document.getElementById('graficoPrediccion').getContext('2d');

            // Extraer valores del JSON
            const yhat = predicciones.map(p => p.yhat);
            const yhatLower = predicciones.map(p => p.yhat_lower);
            const yhatUpper = predicciones.map(p => p.yhat_upper);

            new Chart(ctxPred, {
                type: 'line',
                data: {
                    labels: fechasPred,
                    datasets: [{
                            label: "Predicción",
                            data: yhat,
                            borderColor: "#e74c3c",
                            borderWidth: 2,
                            tension: 0.3,
                            fill: false
                        },
                        {
                            label: "Límite Superior",
                            data: yhatUpper,
                            borderColor: "rgba(231, 76, 60, 0.1)",
                            borderWidth: 1,
                            fill: '+1'
                        },
                        {
                            label: "Límite Inferior",
                            data: yhatLower,
                            borderColor: "rgba(231, 76, 60, 0.1)",
                            borderWidth: 1,
                            backgroundColor: "rgba(231, 76, 60, 0.15)",
                            fill: '-1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Proyección de Ventas (7 días) con Intervalo de Confianza'
                        },
                        legend: {
                            display: true
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        @else
            // ----------- CUANDO NO HAY FILTRO (3 PRODUCTOS) -----------
            const ventasHistoricasJS = @json($ventasHistoricasJS);
            const prediccionesJS = @json($prediccionesJS);

            // 📈 GRAFICO HISTÓRICO
            const datasetsHist = [];
            let allDates = new Set();
            Object.values(ventasHistoricasJS).forEach(vs => vs.forEach(v => allDates.add(v.fecha)));
            const labelsHist = Array.from(allDates).sort();

            Object.keys(ventasHistoricasJS).forEach((nombre, i) => {
                const map = {};
                ventasHistoricasJS[nombre].forEach(v => map[v.fecha] = v.cantidad);
                const data = labelsHist.map(f => map[f] ?? null);
                datasetsHist.push({
                    label: `${nombre} - Histórico`,
                    data: data,
                    borderColor: `hsl(${i * 90},70%,50%)`,
                    backgroundColor: `hsla(${i * 90},70%,50%,0.2)`,
                    tension: 0.3
                });
            });

            new Chart(ctxVentas, {
                type: 'line',
                data: {
                    labels: labelsHist,
                    datasets: datasetsHist
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Historico de Ventas (productos destacados)'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // 📊 GRAFICO DE PREDICCIONES SEPARADO
            const predContainer = document.createElement('div');
            predContainer.classList.add('card', 'border-danger', 'mt-4');
            predContainer.innerHTML = `
            <div class="card-header bg-danger text-white">Predicciones Próximos 7 Días</div>
            <div class="card-body"><canvas id="graficoPrediccion"></canvas></div>
        `;
            document.querySelector('.card.border-info').after(predContainer);

            const ctxPred = document.getElementById('graficoPrediccion').getContext('2d');
            const datasetsPred = [];
            let allDatesPred = new Set();
            Object.values(prediccionesJS).forEach(vs => vs.forEach(p => allDatesPred.add(p.fecha)));
            const labelsPred = Array.from(allDatesPred).sort();

            Object.keys(prediccionesJS).forEach((nombre, i) => {
                const map = {};
                prediccionesJS[nombre].forEach(p => map[p.fecha] = p.yhat);
                const data = labelsPred.map(f => map[f] ?? null);
                datasetsPred.push({
                    label: `${nombre} - Predicción`,
                    data: data,
                    borderColor: `hsl(${i * 90},70%,40%)`,
                    backgroundColor: `hsla(${i * 90},70%,40%,0.1)`,

                    tension: 0.3
                });
            });

            new Chart(ctxPred, {
                type: 'line',
                data: {
                    labels: labelsPred,
                    datasets: datasetsPred
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Predicción de Ventas (Top 3 Productos)'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        @endif
    </script>
@stop
